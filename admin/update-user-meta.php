<?php
// Al registrar un nuevo usuario.
add_action( 'user_register', '_modper_register_user_vendor', 10, 1 );
function _modper_register_user_vendor( $user_id ) {
 
    // Se crea la opción en la tabla user_meta del tipo de cartera.
    update_user_meta($user_id, '_vendor_payment_mode', 'wps_wallet');
 
}