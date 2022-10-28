<?php

// Permite sólo un producto (o un número predefinido de productos) por categoría en el carrito
function _modper_we_cantidad_permitida_por_categoria_en_carrito( $passed, $product_id) {
 
    $max_num_products = 1;//productos permitidos por categoría
    $running_qty = 0;
 
    $restricted_product_cats = array(
        'contemporanea',
        'terror',
        'cienciaficcion',
        'aventuras',
        'uncategorized',
        'historica',
        'narrativafantastica',
        'romantica',
        'thriller',
    );
 
    // Obtiene el slug de la categoría de producto actual en un array
    $product_cats_object = get_the_terms( $product_id, 'product_cat' );
    foreach($product_cats_object as $obj_prod_cat) $current_product_cats[]=$obj_prod_cat->slug;
 
 
    // Itera a través de cada artículo del carrito
    foreach (WC()->cart->get_cart() as $cart_item_key=>$cart_item ){
 
        // Restringe el $max_num_products de cada categoría
        //if( has_term( $current_product_cats, 'product_cat', $cart_item['product_id'] )) {
 
        // Restringe el $max_num_products entre las categorías con productos restringidos
        if( array_intersect($restricted_product_cats, $current_product_cats) && has_term( $restricted_product_cats, 'product_cat', $cart_item['product_id'] )) {
 
            // count(selected category) quantity
            $running_qty += (int) $cart_item['quantity'];
 
            // No se permiten más de los productos permitidos en el carrito
            if( $running_qty >= $max_num_products ) {
                $passed = true; //agrega el nuevo producto al carrito, sustituyendolo por el anterior.
                wc_add_notice( sprintf( 'Debido a que sólo está permitido %s '.($max_num_products>1?'libros':'libro').' por orden, el anterior se ha reemplazado por el nuevo libro seleccionado. Verifica el detalle de tu pedido antes de realizar el pago.',  $max_num_products ), 'error' );
                WC()->cart->remove_cart_item($cart_item_key); 
            }
        }
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', '_modper_we_cantidad_permitida_por_categoria_en_carrito', 10, 2 );



