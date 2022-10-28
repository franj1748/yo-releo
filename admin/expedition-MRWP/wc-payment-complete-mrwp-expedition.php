<?php
// Al crear una orden se crea una etiqueta de Mondial Relay
function _mrwp_so_payment_complete( $order_id  ) {
    
    global $wpdb;
    $prefixe = $wpdb->prefix;

    // Obtener el modo de depuración
    $mondialrelay_debug = get_option( 'mondialrelay_debug', false );

    // Obtener los parámetros de Mondial Relay de seguridad de la cuenta
    $params_compte = _modper_get_params_compte();

    // Servicio web Mondial Relay
 	$client = new SoapClient( 'https://api.mondialrelay.com/Web_Services.asmx?WSDL' );

    // Obtener el punto Mondial Relay
    $id_mondial_relay = _modper_get_meta_champ($order_id, 'Mondial Relay Parcel Shop ID');

    if ((isset($id_mondial_relay) && $id_mondial_relay)) {
        $explode_id_mondial_relay = explode('-', $id_mondial_relay);
        $params_livraison = array(
            'liv_rel_pays_mondial_relay' => '',
            'liv_rel_mondial_relay' => ''
        );
        $params_livraison['liv_rel_pays_mondial_relay'] = $explode_id_mondial_relay[0];
        $params_livraison['liv_rel_mondial_relay'] = $explode_id_mondial_relay[1];
    } else {
        $params_livraison['liv_rel_pays_mondial_relay'] = '';
        $params_livraison['liv_rel_mondial_relay'] = '';
    }

    // Obtener los parámetros del remitente
    $params_expediteur = _modper_get_params_expediteur();

    // Obtener los parámetros del destinatario
    $params_destinataire = _modper_get_params_destinataire( $order_id );

    // Eliminar acentos
    foreach($params_destinataire as $key => $champ) {
        $params_destinataire[$key] = remove_accents($champ);
    }

    // Dividir dirección si tiene más de 32 caracteres
    $numchar = strlen($params_destinataire['_shipping_address_1']);
    if ($numchar > 32 ) {
        $numcut = strrpos($params_destinataire['_shipping_address_1'],' ', -($numchar - 30));
        $params_destinataire['_shipping_address_2'] = substr($params_destinataire['_shipping_address_1'], $numcut + 1, $numchar);
        $params_destinataire['_shipping_address_1'] = substr($params_destinataire['_shipping_address_1'], 0, $numcut);
    }

    if (strlen($params_destinataire['_shipping_company']) > 32 ) {
        $params_destinataire['_shipping_company'] = substr($params_destinataire['_shipping_company'], 0, 31);
    }

	// Eliminar las comillas (') en el parámetro Dest_Ad3 para el servicio web de Mondial Relay
 	$params_destinataire['_shipping_address_1'] = str_replace("&#039;", " ", $params_destinataire['_shipping_address_1']);

    // Eliminar códigos CRLF y caracteres especiales para el servicio web Mondial Relay
    $elim = array("'", "°", "’", "¨", "«", "»", "(", ")", "+", "''", '"');

    $params_destinataire['_shipping_full_name'] = str_replace("\n","", $params_destinataire['_shipping_full_name']);
    $params_destinataire['_shipping_full_name'] = str_replace("\r","", $params_destinataire['_shipping_full_name']);
    $params_destinataire['_shipping_full_name'] = str_replace($elim, " ", $params_destinataire['_shipping_full_name']);

    $params_destinataire['_shipping_company'] = str_replace("\n","", $params_destinataire['_shipping_company']);
    $params_destinataire['_shipping_company'] = str_replace("\r","", $params_destinataire['_shipping_company']);
    $params_destinataire['_shipping_company'] = str_replace("°"," ", $params_destinataire['_shipping_company']);
    $params_destinataire['_shipping_company'] = str_replace($elim, " ", $params_destinataire['_shipping_company']);

    $params_destinataire['_shipping_company'] = preg_replace("/@/", ' ', $params_destinataire['_shipping_company']);

    $params_destinataire['_shipping_address_1'] = str_replace("\n","", $params_destinataire['_shipping_address_1']);
    $params_destinataire['_shipping_address_1'] = str_replace("\r","", $params_destinataire['_shipping_address_1']);
    $params_destinataire['_shipping_address_1'] = str_replace($elim, " ", $params_destinataire['_shipping_address_1']);

    $params_destinataire['_shipping_address_2'] = str_replace("\n","", $params_destinataire['_shipping_address_2']);
    $params_destinataire['_shipping_address_2'] = str_replace("\r","", $params_destinataire['_shipping_address_2']);
    $params_destinataire['_shipping_address_2'] = str_replace($elim, " ", $params_destinataire['_shipping_address_2']);

    $params_destinataire['_shipping_city'] = str_replace("\n","", $params_destinataire['_shipping_city']);
    $params_destinataire['_shipping_city'] = str_replace("\r","", $params_destinataire['_shipping_city']);
    $params_destinataire['_shipping_city'] = str_replace($elim, " ", $params_destinataire['_shipping_city']);

    $params_destinataire['_shipping_postcode'] = str_replace("\n","", $params_destinataire['_shipping_postcode']);
    $params_destinataire['_shipping_postcode'] = str_replace("\r","", $params_destinataire['_shipping_postcode']);
    $params_destinataire['_shipping_postcode'] = str_replace($elim, " ", $params_destinataire['_shipping_postcode']);

    $params_destinataire['_shipping_country'] = str_replace("\n","", $params_destinataire['_shipping_country']);
    $params_destinataire['_shipping_country'] = str_replace("\r","", $params_destinataire['_shipping_country']);
    $params_destinataire['_shipping_country'] = str_replace($elim, " ", $params_destinataire['_shipping_country']);

    // Obtener los parámetros del pedido
    $params_commande = _modper_get_params_commande( $order_id );
    $params_commande['ModeCol'] = 'CCC';
    $params_commande['ModeLiv'] = _modper_get_meta_champ( $order_id, 'Mondial Relay Shipping Code' );
    $params_commande['NDossier'] = $order_id;

    // Obtener tipo de seguro
    $assurance = intval( get_option( 'mondialrelay_assurance', 1 ) );

    // Obtener los parámetros del punto de recogida
    $params_collecte = _modper_get_params_collecte();

    // Parámetros de la expedición
    $params = array(
        'Enseigne'        => $params_compte['mondialrelay_code_client']
        ,'ModeCol'        => $params_commande['ModeCol']
        ,'ModeLiv'        => $params_commande['ModeLiv']
        ,'NDossier'       => $params_commande['NDossier']
        ,'NClient'        => $params_commande['NClient']
        ,'Expe_Langage'   => $params_expediteur['mondialrelay_vendeur_langage']
        ,'Expe_Ad1'       => $params_expediteur['mondialrelay_vendeur_adresse1']
        ,'Expe_Ad2'       => $params_expediteur['mondialrelay_vendeur_adresse2']
        ,'Expe_Ad3'       => $params_expediteur['mondialrelay_vendeur_adresse3']
        ,'Expe_Ad4'       => $params_expediteur['mondialrelay_vendeur_adresse4']
        ,'Expe_Ville'     => $params_expediteur['mondialrelay_vendeur_ville']
        ,'Expe_CP'        => $params_expediteur['mondialrelay_vendeur_cp']
        ,'Expe_Pays'      => $params_expediteur['mondialrelay_vendeur_pays']
        ,'Expe_Tel1'      => $params_expediteur['mondialrelay_vendeur_tel']
        ,'Expe_Tel2'      => $params_expediteur['mondialrelay_vendeur_tel2']
        ,'Expe_Mail'      => $params_expediteur['mondialrelay_vendeur_email']
        ,'Dest_Langage'   => $params_destinataire['_shipping_langage']
        ,'Dest_Ad1'       => $params_destinataire['_shipping_full_name']
        ,'Dest_Ad2'       => $params_destinataire['_shipping_company']
        ,'Dest_Ad3'       => $params_destinataire['_shipping_address_1']
        ,'Dest_Ad4'       => $params_destinataire['_shipping_address_2']
        ,'Dest_Ville'     => $params_destinataire['_shipping_city']
        ,'Dest_CP'        => $params_destinataire['_shipping_postcode']
        ,'Dest_Pays'      => $params_destinataire['_shipping_country']
        ,'Dest_Tel1'      => $params_destinataire['_billing_phone']
        ,'Dest_Mail'      => $params_destinataire['_billing_email']
        ,'Poids'          => $params_commande['Poids']
        ,'Longueur'       => $params_commande['Longueur']
        ,'Taille'         => $params_commande['Taille']
        ,'NbColis'        => $params_commande['NbColis']
        ,'CRT_Valeur'     => $params_commande['CRT_Valeur']
        ,'CRT_Devise'     => $params_commande['CRT_Devise']
        ,'Exp_Valeur'     => $params_commande['Exp_Valeur']
        ,'Exp_Devise'     => $params_commande['Exp_Devise']
        ,'COL_Rel_Pays'	  => $params_collecte['col_rel_pays_mondial_relay']
        ,'COL_Rel'	 	  => $params_collecte['col_rel_mondial_relay']
        ,'LIV_Rel_Pays'   => $params_livraison['liv_rel_pays_mondial_relay']
        ,'LIV_Rel'        => $params_livraison['liv_rel_mondial_relay']
        ,'TAvisage'       => $params_commande['TAvisage']
        ,'TReprise'       => $params_commande['TReprise']
        ,'Montage'        => $params_commande['Montage']
        ,'TRDV'           => $params_commande['TRDV']
        //,'Instructions'   => $params_commande['Instructions']
        ,'Instructions'   => ''
        ,'Assurance'      => $assurance
    );

    // Generación de la clave de seguridad
    $code = implode("", $params);
    $code .= $params_compte[ 'mondialrelay_cle_privee' ];
    $params[ "Security" ] = strtoupper( md5( $code ) );

    // Creación de la expedición

    // Si el número de envío ya existe, entonces se recibe un mensaje de error; de lo contrario, se crea el envío
    $expe_exist = _modper_get_meta_champ( $order_id, 'ExpeditionNum' ); 
    
    if ( $expe_exist ) {
        $callback = 421; // Ya existe un envío para este pedido
    } else {
        try{
            $expedition = $client->WSI2_CreationExpedition($params)->WSI2_CreationExpeditionResult;
            $callback = $expedition->STAT;
        } catch( Exception $e ){
            $debug_except .= $e;
            $callback = 789; // Error de red SOAP
        }
    }
    
    // Código de estado
    if ($callback != 0) {
        
        // Obtener los parámetros del correo electrónico: Todos los shortcode de la plantilla
        _modper_get_email_params_error( $order_id, $params_destinataire, $callback );
        
        // Obtener la plantilla de correo HTML con los datos necesarios incrustados
        $email = _modper_get_params_emails_error( $order_id );
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        $destinataire = array('franciscoj2079@gmail.com', 'adrvilgui@gmail.com');
        
        // Enviar correo al admin con los datos del error
        $mail = wp_mail( $destinataire, $email['sujet'], $email['message'], $headers );
        
    }
    elseif ($callback == 0) {

        // Añadir los parámetros de la expedición Mondial Relay en la base de datos
        $champs_bdd = _modper_get_expedition_champs($expedition, $params_commande['NbColis']);
        _modper_insert_meta_champs($order_id, $champs_bdd);
        
        // Obtener el número de expedición de la orden creada
        $expedition_num = $expedition->ExpeditionNum;
        
        // Obtener los parámetros del correo electrónico: Todos los shortcode de la plantilla y la url paral a descarga de la etiqueta
        _modper_get_email_params( $order_id, $params_destinataire, $expedition_num, $params_commande['Poids'] );
        _modper_get_etiquette( $params_compte, $id_mondial_relay, $params_destinataire, $expedition_num, $order_id );
        
        // Obtener la plantilla de correo HTML con los datos necesarios incrustados
        $email = _modper_get_params_emails( $order_id );
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Creación de ganchos después de la actualización de la base de datos
        do_action('MRWP_after_expedition_creation', $order_id);

        // Actualizar el estado del pago
        $checkout_status = get_option( 'mondialrelay_state_order', '' );
        if ( $checkout_status ) {
            $order = new WC_Order( $order_id );
            if ( ! empty( $order ) ) {
                $order->update_status( 'wc-completed' );
            }
        }

        $destinataire = _modper_get_meta_champ( $order_id, '_seller_email' );
        // Enviar el correo electrónico de seguimiento al vendedor
        $mail = wp_mail( $destinataire, $email['sujet'], $email['message'], $headers );

        // Creación de ganchos después de la actualización de la base de datos
        do_action('MRWP_after_order_status_updated', $order_id);
    }

}

add_action( 'woocommerce_payment_complete', '_mrwp_so_payment_complete' );


/*add_post_meta( $order_id, '_acstatut1exp', $expedition );
add_post_meta( $order_id, '_acstatut1', $callback );*/


function _modper_get_email_params( $order_id, $params_destinataire, $expedition_num, $weight ){
    
    //Obtener datos de la orden
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
    $post = get_post( $book->get_id() );
    $id_user = $post->post_author;
    $seller = get_userdata( $id_user );
    $seller_full_name = $seller->first_name.' '.$seller->last_name;
    $email = $seller->user_email;
    
    // Obtener datos del comprador para ubicar el nombre de usuario para el enlace del mensaje
    $buyer_id = $order->customer_id;
	$buyer_username = get_userdata( $buyer_id )->user_login;
    
    // Obtener fecha de creación y expiración de la orden 
	$order_created = $order->date_created->date( "d-m-Y" );
    $mod_date = strtotime( $order_created."+ 10 days" );
    $order_expiration = date( "d-m-Y", $mod_date );
    
    // Cambiar el valor del peso del pedido a uno más entendible en el email
    if( $weight == '999' ){
        
        $weight = 'Hasta 1 Kg';
        
    }else{
        
        $weight = 'Hasta 2 Kg';
    }
    
    
    // Parámetros para el correo electrónico
    $params = Array(
        'seller' => $seller_full_name,
        'buyer' => $params_destinataire[ '_shipping_full_name' ],
        'buyer_username' => $buyer_username,
        'name_book' => $book->name,
        'expiration' => $order_expiration,
        'shipping_address' => _modper_get_meta_champ( $order_id, 'Mondial Relay Parcel Shop Address' ),
        'expedition_num' => $expedition_num,
        'order' => $order_id,
        'weight' => $weight
    );
    
    add_post_meta( $order_id, '_seller', $params[ 'seller' ] );
    add_post_meta( $order_id, '_buyer', $params[ 'buyer' ] );
    add_post_meta( $order_id, '_buyer_username', $params[ 'buyer_username' ] );
    add_post_meta( $order_id, '_name_book', $params[ 'name_book' ] );
    add_post_meta( $order_id, '_expiration', $params[ 'expiration' ] );
    add_post_meta( $order_id, '_shipping_address', $params[ 'shipping_address' ] );
    add_post_meta( $order_id, '_expedition_num', $params[ 'expedition_num' ] );
    add_post_meta( $order_id, '_order', $params[ 'order' ] );
    add_post_meta( $order_id, '_weight', $params[ 'weight' ] );
    add_post_meta( $order_id, '_seller_email', $email );
    
}

function _modper_get_email_params_error( $order_id, $params_destinataire, $callback ){
    
    //Obtener datos de la orden
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
    $post = get_post( $book->get_id() );
    $id_seller = $post->post_author;
    $seller = get_userdata( $id_seller );
    $seller_full_name = $seller->first_name.' '.$seller->last_name;
    $email = $seller->user_email;
    
    //Obtener datos del comprador
    $buyer_id = $order->customer_id;
    
    // Parámetros para el correo electrónico
    $params = Array(
        'order' => $order_id,
        'error_code' => $callback,
        'error_message' => _modper_statut($callback),
        'buyer' => $params_destinataire[ '_shipping_full_name' ],
        'buyer_id' => $buyer_id,
        'name_book' => $book->name,
        'seller' => $seller_full_name,
        'seller_id' => $id_seller,
        'seller_email' => $email,
        'shipping_address' => _modper_get_meta_champ( $order_id, 'Mondial Relay Parcel Shop Address' )
    );
    
    add_post_meta( $order_id, '_order_error', $params[ 'order' ] );
    add_post_meta( $order_id, '_error_code', $params[ 'error_code' ] );
    add_post_meta( $order_id, '_error_message', $params[ 'error_message' ] );
    add_post_meta( $order_id, '_buyer_error', $params[ 'buyer' ] );
    add_post_meta( $order_id, '_buyer_id_error', $params[ 'buyer_id' ] );
    add_post_meta( $order_id, '_name_book_error', $params[ 'name_book' ] );
    add_post_meta( $order_id, '_seller_error', $params[ 'seller' ] );
    add_post_meta( $order_id, '_seller_id_error', $params[ 'seller_id' ] );
    add_post_meta( $order_id, '_seller_email_error', $params[ 'seller_email' ] );
    add_post_meta( $order_id, '_shipping_address_error', $params[ 'shipping_address' ] );
    
}


function _modper_get_etiquette( $params_compte, $point_mondial_relay, $params_destinataire, $expedition_num, $order_id ) {
    
    // Obtener el punto Mondial Relay
    if ( ( isset( $point_mondial_relay ) && $point_mondial_relay ) ) {
        $explode_id_mondial_relay = explode( '-', $point_mondial_relay );
        $liv_rel_pays_mondial_relay = $explode_id_mondial_relay[0];
    } else {
        // Obtener los parámetros del destinatario
        $liv_rel_pays_mondial_relay = $params_destinataire[ '_shipping_langage' ];
    }

    // SOAP
    try{
        $client = new SoapClient( 'https://api.mondialrelay.com/Web_Services.asmx?WSDL' );
    } catch( Exception $e ){
        $debug_except .= $e;
        $client = '';
    }
    
    $params = array(
        'Enseigne'       => $params_compte[ 'mondialrelay_code_client' ],
        'Expeditions'    => $expedition_num,
        'Langue'         => $liv_rel_pays_mondial_relay,
    );

    // Generación de la clave de seguridad
    $code = implode( "", $params );
    $code .= $params_compte[ 'mondialrelay_cle_privee' ];
    $params["Security"] = strtoupper( md5( $code ) );

    try{
        $etiquette = $client->WSI3_GetEtiquettes($params)->WSI3_GetEtiquettesResult;
        $callback = $etiquette->STAT;
    } catch( Exception $e ){
        $debug_except .= $e;
        $callback = 789;
    }

    if ($callback == 0) {
        
        add_post_meta( $order_id, '_etiquette_uri', 'https://www.mondialrelay.fr'.$etiquette->URL_PDF_A4 );

    }else{
        add_post_meta( $order_id, '_accurietiquetaerror', $callback  );
    }

} 

















