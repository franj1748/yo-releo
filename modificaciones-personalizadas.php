<?php

/**
 * @package           modificaciones_personalizadas
 * @author            Francisco Elis
 * @copyright         2022 Acceso Web
 * @license           GPL-2.0-or-later
 * @link              https://github.com/franj1748/modificaciones_personalizadas.git
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Modificaciones personalizadas
 * Plugin URI:        https://github.com/franj1748/modificaciones_personalizadas.git
 * Description:       Personalice su instalación de WordPress, en el backend o el frontend, sin manipular los archivos del núcleo.     
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Francisco Elis
 * Author URI:        https://www.linkedin.com/in/francisco-elis-24506b209
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       modificaciones_personalizadas
 */

// Si este archivo se llama directamente por cualquier otra instancia que no sea WordPress, abortar.
if (!defined('WPINC')){
	die;
}

/**
 * Definición de las constantes para la versión del plugin y la ruta completa.
 */
define( 'MODPER_VERSION', '1.0.0' );
define('MODPER_RUTA', plugin_dir_path(__FILE__));

/**
* Activación del plugin.
*/
function _modper_activate_plugin(){
    
	require_once MODPER_RUTA.'includes/class-modificaciones-personalizadas-activator.php';
}

/**
* Desactivación del plugin.
*/
function _modper_deactivate_plugin(){
    
	require_once MODPER_RUTA.'includes/class-modificaciones-personalizadas-deactivator.php';
}

register_activation_hook( __FILE__, '_modper_activate_plugin');
register_deactivation_hook( __FILE__, '_modper_deactivate_plugin');

/**
 * Archivos requeridos al iniciar el plugin. 
 */
function _modper_plugin_init(){

  // Public Area
  require_once MODPER_RUTA.'public/modificaciones-personalizadas-shortcode.php';
  require_once MODPER_RUTA.'public/product-the-same-cat-cart.php';
  
  // Admin area
  require_once MODPER_RUTA.'admin/update-user-meta.php';
  require_once MODPER_RUTA.'admin/peticiones-ajax.php';
  require_once MODPER_RUTA.'admin/class-mp-shortcode-products.php';
  require_once MODPER_RUTA.'admin/partials/register-plugin-settings.php';
  require_once MODPER_RUTA.'admin/expedition-MRWP/wc-payment-complete-mrwp-expedition.php';
  require_once MODPER_RUTA.'admin/expedition-MRWP/functions-mrwp-expedition.php';
  
}
add_action( 'init', '_modper_plugin_init' );


/*
* Modificadores WooCommerce
*/
/*
* Sustitución de función de plantilla de WooCommerce para mostrar solamente la pestaña de descripción en la página de prodcuto individual del sub modal. 
*/
function woocommerce_output_product_data_tabs() {
    
	$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

    if (!empty( $product_tabs)){
        ?>
            <div class="woocommerce-tabs wc-tabs-wrapper">
        			<div class="woocommerce-Tabs-panel woocommerce-Tabs-panel--description panel entry-content wc-tab" id="tab-description" role="tabpanel" aria-labelledby="tab-title-description">
        				<?php
        				if (isset($product_tabs['description']['callback'])){
        					call_user_func($product_tabs['description']['callback'], 'description', $product_tabs['description']);
        				}
        				?>
        			</div>
        		<?php do_action( 'woocommerce_product_after_tabs' ); ?>
        	</div>
        <?php 
    }
}

/*
* Sustitución de función de plantilla de WooCommerce para reducir el tamaño del titulo del producto. 
*/
function woocommerce_template_loop_product_title() {
    
	echo '<h2 class="' . esc_attr( apply_filters( 'woocommerce_product_loop_title_classes', 'woocommerce-loop-product__title' ) ) . '" style="min-height: 15%; font-size: 15px;">' . get_the_title() . '</h2>'; 
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/* -->*/

function twentytwenty_add_meta_tags() {
    
    // homepage
    echo '<meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no; user-scalable=0"/>';
}
add_action( 'wp_head', 'twentytwenty_add_meta_tags');


/**
 * Incrustar scripts JS en el panel de administración general y en la página de opciones del plugin.
 */
function _modper_add_script_wp_admin(){
    
    wp_enqueue_script('js-admin-modper', plugins_url('admin/js/modificaciones-personalizadas-admin.js', __FILE__));
    wp_enqueue_script('sweetalert-admin-modper', plugins_url('admin/js/sweetalert.min.js', __FILE__));
    
    if( get_current_screen()->base == 'settings_page_modificaciones_personalizadas' ){
        
        wp_enqueue_script('js-admin-modper-plugin', plugins_url('admin/js/modificaciones-personalizadas-admin-plugin.js', __FILE__));
        wp_localize_script('js-admin-modper-plugin', 'ajax_mondial', 
        [
            'ajaxurl_mondial' => admin_url('admin-ajax.php'), 
            'nonce' => wp_create_nonce('modper-ajax-mondial-nonce')
        ]);
        
    }
}
add_action('admin_footer', '_modper_add_script_wp_admin');

/**
 * Incrustar scripts JS en el frontend.
 */
function _modper_add_script_wp_public(){
    
	wp_register_script('js-user-modper', plugins_url( 'public/js/modificaciones-personalizadas-public.js', __FILE__ ), array(), '1.0.0', true); 
	wp_enqueue_script( 'js-user-modper');
  // Enviar cualquier variable PHP a JavaScript al registrar el archivo. 
  $options = get_option('modper_options');
	wp_localize_script ( 'js-user-modper' ,  'MODPER_const' ,  
    array ( 
      'tab'   => __ ($options['modper_tab']),
    ) 
  ) ; 
}
add_action( 'wp_enqueue_scripts', '_modper_add_script_wp_public');

/**
 * Incrustar scripts JS en el frontend para manejar la petición ajax de la página de producto en el suub modal. 
 */
function _modper_add_script_wp_public_ajax(){
    
    wp_register_script('js-user-modper_ajax', plugins_url( 'public/js/modificaciones-personalizadas-public-ajax.js', __FILE__ ), array(), '1.0.0', true); 
    wp_enqueue_script( 'js-user-modper_ajax');
    
    wp_localize_script('js-user-modper_ajax', 'ajax_const', 
    [
        'ajaxurl' => admin_url('admin-ajax.php'), 
        'nonce' => wp_create_nonce('modper-ajax-nonce')
    ]);
}
add_action( 'wp_enqueue_scripts', '_modper_add_script_wp_public_ajax');

/**
 * Incrustar estilos al panel de administración.
*/
add_action('admin_enqueue_scripts', '_modper_add_style_wp_admin');
function _modper_add_style_wp_admin(){
    
    wp_enqueue_style('style-admin-modper', plugins_url('admin/css/modificaciones-personalizadas-admin.css', __FILE__));
    
}

/**
 * Incrustar estilos al frontend
*/
add_action('wp_enqueue_scripts', '_modper_add_style_wp_public');
function _modper_add_style_wp_public(){
    
    wp_register_style( 'style-user-modper', plugins_url( 'public/css/modificaciones-personalizadas-public.css', __FILE__ ), array(), '20120208', 'all' );
    wp_enqueue_style( 'style-user-modper' );
    
}

/* Incluir cdn de Bootstrsp */
/* Bootstrap CSS */
function bootstrap_css() {
    
	wp_enqueue_style( 'bootstrap_css', 
    'https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css', 
    array(), 
    '5.2.0'
  ); 
  
}
add_action( 'wp_enqueue_scripts', 'bootstrap_css');

/* Bootstrap JS y dependencia popper */
/*function bootstrap_js() {
	wp_enqueue_script( 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js', 
    array('jquery','popper_js'), 
    '5.2.0', 
		true
	); 
}
add_action( 'wp_enqueue_scripts', 'bootstrap_js');*/