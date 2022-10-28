<?php

require_once MODPER_RUTA.'admin/expedition-MRWP/functions-mrwp-expedition.php';
//require_once MODPER_RUTA.'public/modificaciones-personalizadas-shortcode.php';

// Devolver la página del producto según el id en el sub modal de complementos. 
add_action('wp_ajax_modper_ajax_readmore','_modper_ajax_enviar_contenido');
function _modper_ajax_enviar_contenido(){
    
    $nonce = sanitize_text_field($_POST['nonce']);
    
    if (!wp_verify_nonce($nonce, 'modper-ajax-nonce')){
        die ();
    }
    
    $url_product = $_POST['url_product'];
    $id = url_to_postid($url_product);
    
    $atts = array(
		'id'        => $id,
	);
	
	$args = array(
		'posts_per_page'      => 1,
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => 1,
		'no_found_rows'       => 1,
	);
	
	if ( isset( $atts['id'] ) ) {
		$args['p'] = absint( $atts['id'] );
	}
	
	add_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );
    $single_product = new WP_Query( $args );
    $preselected_id = $single_product->post->ID;
    $single_product->is_single = true;
    
    ob_start();

	global $wp_query;

	// Backup query object so following loops think this is a product page.
	$previous_wp_query = $wp_query;
	// @codingStandardsIgnoreStart
	$wp_query          = $single_product;
	// @codingStandardsIgnoreEnd

	wp_enqueue_script( 'wc-single-product' );

	while ( $single_product->have_posts() ) {
		$single_product->the_post()
		?>
		<div class="single-product" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">
			<?php wc_get_template_part( 'content', 'single-product' ); ?>
		</div>
		<?php
	}

	// Restore $previous_wp_query and reset post data.
	// @codingStandardsIgnoreStart
	$wp_query = $previous_wp_query;
	// @codingStandardsIgnoreEnd
	wp_reset_postdata();

    echo '<div class="woocommerce">' . ob_get_clean() . '</div>';
    wp_die();

}

// Devolver la url de la página del checkout.
add_action('wp_ajax_modper_ajax_checkout','_modper_ajax_enviar_url');
function _modper_ajax_enviar_url(){
    
    global $woocommerce;
    $checkout_page_url = wc_get_checkout_url();

    echo $checkout_page_url;
    wp_die();

}

// Enviar email a vendedor con los datos de la etiqueta fallida
add_action('wp_ajax_modper_ajax_mondial_relay','_modper_ajax_email_mondial_relay');
function _modper_ajax_email_mondial_relay(){
    
    $nonce = sanitize_text_field( $_POST[ 'nonce' ] );
    
    if ( ! wp_verify_nonce( $nonce, 'modper-ajax-mondial-nonce' ) ){
        die ();
    }
    
    // Obtener datos del AJAX
    $order_id = $_POST['order'];
    $seller_id = $_POST['seller'];
    $buyer_id = $_POST['buyer'];
    $etiquette_uri = $_POST['etiquette_uri'];
    
    // Verificar que exista un número de expedición para la orden dada
    $expe_exist = _modper_get_meta_champ( $order_id, 'ExpeditionNum' ); 
    
    if ( $expe_exist ) {
        
        // Globales y generales
        global $wpdb;
        $prefixe = $wpdb->prefix;
        $order = new WC_Order( $order_id );
        $items = $order->get_items();
    
        // Obtener el libro dentro de la orden
    	foreach ( $items as $item ) {
            $products[] = wc_get_product( $item[ 'product_id' ] );
        }
        
        foreach ( $products as $product ) {
            
            if( $product->get_categories() != 'Complementos' ){
                
                $book = $product;
                
            }
        }
        
        // Obtener el nombre y correo del vendedor del producto 
        $seller = get_userdata( $seller_id );
        $seller_full_name = $seller->first_name.' '.$seller->last_name;
        $seller_email = $seller->user_email;
        
        // Obtener datos del comprador para ubicar el nombre de usuario para el enlace del mensaje
        $buyer = get_userdata( $buyer_id );
        $buyer_full_name = $buyer->first_name.' '.$buyer->last_name;
    	$buyer_username = $buyer->user_login;
        
        // Obtener fecha de creación y expiración de la orden 
    	$order_created = $order->date_created->date( "d-m-Y" );
        $mod_date = strtotime( $order_created."+ 10 days" );
        $order_expiration = date( "d-m-Y", $mod_date );
        
        ob_start();
        include( 'expedition-MRWP/email-templates/email-template-order.php' );
        $email[ 'message' ] = ob_get_contents();
        
        $shortcodes = array(
            'seller' => $seller_full_name,
            'buyer' => $buyer_full_name,
            'buyer_username' => $buyer_username,
            'name_book' => $book->name,
            'expiration' => $order_expiration,
            'shipping_address' => _modper_get_meta_champ( $order_id, 'Mondial Relay Parcel Shop Address' ),
            'expedition_num' => _modper_get_meta_champ( $order_id, 'ExpeditionNum' ),
            'order' => $order_id,
            'weight' => _modper_get_meta_champ( $order_id, 'Mondial Relay Parcel Weight' ),
            'etiquette_uri' => $etiquette_uri
        );
        
        // Cambiar el valor del peso del pedido a uno más entendible en el email
        if( $shortcodes['weight'] == '999' ){
            
            $shortcodes['weight'] = 'Hasta 1 Kg';
            
        }else{
            
            $shortcodes['weight'] = 'Hasta 2 Kg';
        }
        
        foreach ( $shortcodes as $shortcode => $valeur ) {
            $email[ 'message' ] = str_replace('[' . $shortcode . ']', $valeur, $email[ 'message' ]);
        }
        
        $email[ 'message' ] = stripslashes( $email[ 'message' ] );
        
        $email[ 'sujet' ] = 'Has vendido un libro en Yoreleo.';
        ob_end_clean();
        
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $mail = wp_mail( $seller_email, $email['sujet'], $email['message'], $headers );
        
        wp_die();
        
    } else {
        
        echo 421;
        
    }

}













