<?php

function _modper_get_params_compte() {
    
    global $wpdb;
    $prefixe = $wpdb->prefix;

    $params_compte = array(
        'mondialrelay_code_client' => '',
        'mondialrelay_code_marque' => '',
        'mondialrelay_cle_privee' => ''
    );
    
    foreach( $params_compte as $key => $param ) {
        $params_compte[$key] = get_option( $key, 1 );
    }
    
    return $params_compte;
}


function _modper_get_meta_champ( $id, $key, $count = null ) {
    
    global $wpdb;
    $prefixe = $wpdb->prefix;

    if ($count)
        $value = $wpdb->get_var( "SELECT COUNT(*) FROM " . $prefixe . "postmeta WHERE post_id ='$id' AND meta_key = '$key'" );
    else
        $value = $wpdb->get_var( "SELECT meta_value FROM " . $prefixe . "postmeta WHERE post_id ='$id' AND meta_key = '$key'" );
        
    return $value;
}


function _modper_get_params_expediteur() {
    
    global $wpdb;
    $prefixe = $wpdb->prefix;

    $params_expediteur = array(
        'mondialrelay_vendeur_adresse1' => '',
        'mondialrelay_vendeur_adresse2' => '',
        'mondialrelay_vendeur_adresse3' => '',
        'mondialrelay_vendeur_adresse4' => '',
        'mondialrelay_vendeur_cp' => '',
        'mondialrelay_vendeur_ville' => '',
        'mondialrelay_vendeur_pays' => '',
        'mondialrelay_vendeur_tel' => '',
        'mondialrelay_vendeur_tel2' => '',
        'mondialrelay_vendeur_email' => ''
    );
    
    foreach( $params_expediteur as $key => $param ) {
        $params_expediteur[ $key ] = get_option( $key, 1 );
    }
    
    $params_expediteur[ 'mondialrelay_vendeur_langage' ] =  _modper_get_langue($params_expediteur[ 'mondialrelay_vendeur_pays' ]);
    $params_expediteur[ 'mondialrelay_vendeur_adresse1' ] = remove_accents($params_expediteur[ 'mondialrelay_vendeur_adresse1' ]);
    $params_expediteur[ 'mondialrelay_vendeur_adresse2' ] = remove_accents($params_expediteur[ 'mondialrelay_vendeur_adresse2' ]);
    $params_expediteur[ 'mondialrelay_vendeur_adresse3' ] = remove_accents($params_expediteur[ 'mondialrelay_vendeur_adresse3' ]);
    $params_expediteur[ 'mondialrelay_vendeur_adresse4' ] = remove_accents($params_expediteur[ 'mondialrelay_vendeur_adresse4' ]);
    $params_expediteur[ 'mondialrelay_vendeur_ville' ] = remove_accents($params_expediteur[ 'mondialrelay_vendeur_ville' ]);
    
    return $params_expediteur;
}


function _modper_get_params_destinataire( $order_id ) {

    global $wpdb;
    $prefixe = $wpdb->prefix;

    $params_destinataire = array(
        '_shipping_last_name' => '',
        '_shipping_first_name' => '',
        '_shipping_company' => '',
        '_billing_email' => '',
        '_billing_phone' => '',
        '_shipping_address_1' => '',
        '_shipping_address_2' => '',
        '_shipping_city' => '',
        '_shipping_postcode' => '',
        '_shipping_country' => ''
    );
    
    foreach($params_destinataire as $key => $param) {
        $params_destinataire[$key] = $wpdb->get_var( "SELECT meta_value FROM " . $prefixe . "postmeta WHERE post_id = '" . $order_id . "' AND meta_key = '$key'" );
    }
    
    $params_destinataire[ '_shipping_langage' ] = _modper_get_langue( $params_destinataire[ '_shipping_country' ] );
    $params_destinataire[ '_shipping_full_name' ] = $params_destinataire[ '_shipping_first_name' ] . ' ' . $params_destinataire['_shipping_last_name' ];
    $params_destinataire[ '_shipping_full_name' ] = remove_accents( $params_destinataire[ '_shipping_full_name' ] );

    $billing_country = get_post_meta( $order_id, '_billing_country', true );

    $params_destinataire['_billing_phone'] = _modper_phone_validation( $billing_country,$params_destinataire[ '_billing_phone' ] );
    
    return $params_destinataire;
}


function _modper_get_params_commande( $order_id ) {
    
    global $wpdb;
    $prefixe = $wpdb->prefix;

    $params_commande = array(
        'ModeCol' => 'CCC',
        'Poids' => _modper_get_meta_champ( $order_id, 'Mondial Relay Parcel Weight' ),
        'NbColis' => '1',
        'CRT_Valeur' => '0',
        'NDossier' => $order_id,
        'NClient' => '',
        'Longueur' => '',
        'Taille' => '',
        'CRT_Devise' => '',
        'Exp_Valeur' => '',
        'Exp_Devise' => '',
        'TAvisage' => '',
        'TReprise' => '',
        'Montage' => '',
        'TRDV' => '',
        'Instructions' => '',
    );
    
    foreach( $params_commande as $key => $param ) {
        
        if ( isset( $_POST[ $key ] ) )
            $params_commande[ $key ] = $_POST[ $key ];
            
    }
    
    return $params_commande;
}

function _modper_get_params_collecte() {
    
    $params_collecte = array(
        'col_rel_pays_mondial_relay' => '',
        'col_rel_mondial_relay' => ''
    );
    
    return $params_collecte;
}


function _modper_get_expedition_champs( $expedition, $nb_colis ) {
    
    $champs = array(
        'ExpeditionNum' => $expedition->ExpeditionNum,
        'TRI_AgenceCode' => $expedition->TRI_AgenceCode,
        'TRI_Groupe' => $expedition->TRI_Groupe,
        'TRI_Navette' => $expedition->TRI_Navette,
        'TRI_Agence' => $expedition->TRI_Agence,
        'TRI_TourneeCode' => $expedition->TRI_TourneeCode,
        'TRI_LivraisonMode' => $expedition->TRI_LivraisonMode,
        'ExpeditionStatus' => '800',
        'ExpeditionStatusDate' => time(),
    );
    
    // Añadir el código de barras
    if ( $nb_colis == 1 ) {
        $champs[ 'CodesBarres' ] = $expedition->CodesBarres->string;
    }
    elseif ( $nb_colis > 1 ) {
        $i = 0;
        while ( $i < $nb_colis ) {
            $champs[ 'CodesBarres_' . $i ] = $expedition->CodesBarres->string[$i];
            $i++;
        }
    }
    
    return $champs;
}


function _modper_insert_meta_champs( $order_id, $champs ) {
    
    global $wpdb;
    $prefixe = $wpdb->prefix;

    foreach ( $champs as $key => $champ ) {
        
        $wpdb->insert( $prefixe . 'postmeta', array(
            'meta_id'     => NULL,
            'post_id'    => '' . $order_id . '',
            'meta_key'   => '' . $key . '',
            'meta_value'    => '' . $champ . ''
        ));
        
    }
    
    return 1;
}

function _modper_get_params_emails( $order_id ) {
    
    ob_start();
    include( 'email-templates/email-template-order.php' );
    $email[ 'message' ] = ob_get_contents();
    
    $email[ 'message' ] = _modper_parse_shortcodes( $order_id, $email[ 'message' ] );
    
    $email[ 'sujet' ] = 'Has vendido un libro en Yoreleo.';
    ob_end_clean();

    return $email;
}

function _modper_get_params_emails_error( $order_id ) {
    
    ob_start();
    include( 'email-templates/email-template-error.php' );
    $email[ 'message' ] = ob_get_contents();
    
    $email[ 'message' ] = _modper_parse_shortcodes_error( $order_id, $email[ 'message' ] );
    
    $email[ 'sujet' ] = 'Error generando la etiqueta de envío.';
    ob_end_clean();

    return $email;
}

/*///////////////////////*/
//Helpers


function _modper_parse_shortcodes( $order_id, $texte ) {
    
    global $wpdb;
    $prefixe = $wpdb->prefix;
    
    $shortcodes = _modper_get_shortcodes_email( $order_id  );
    
    foreach ( $shortcodes as $shortcode => $valeur ) {
        $texte = str_replace('[' . $shortcode . ']', $valeur, $texte);
    }
    
    $texte = stripslashes( $texte );
    
    return $texte;
}

function _modper_get_shortcodes_email( $order_id ) {
    
    $shortcodes = array(
        'seller' => '',
        'buyer' => '',
        'buyer_username' => '',
        'name_book' => '',
        'expiration' => '',
        'shipping_address' => '',
        'expedition_num' => '',
        'order' => '',
        'weight' => '',
        'etiquette_uri' => ''
    );
    
    $champs = array(
        'seller' => '_seller',
        'buyer' => '_buyer',
        'buyer_username' => '_buyer_username',
        'name_book' => '_name_book',
        'expiration' => '_expiration',
        'shipping_address' => '_shipping_address',
        'expedition_num' => '_expedition_num',
        'order' => '_order',
        'weight' => '_weight',
        'etiquette_uri' => '_etiquette_uri'
    );
    
    foreach ($champs as $champ => $valeur) {
        $shortcodes[$champ] = _modper_get_meta_champ( $order_id, $valeur );
    }
    
    return $shortcodes;
}

function _modper_parse_shortcodes_error( $order_id, $texte ) {
    
    global $wpdb;
    $prefixe = $wpdb->prefix;
    
    $shortcodes = _modper_get_shortcodes_email_error( $order_id  );
    
    foreach ( $shortcodes as $shortcode => $valeur ) {
        $texte = str_replace('[' . $shortcode . ']', $valeur, $texte);
    }
    
    $texte = stripslashes( $texte );
    
    return $texte;
}

function _modper_get_shortcodes_email_error( $order_id ) {
    
    $shortcodes = array(
        'order' => '',
        'error_code' => '',
        'error_message' => '',
        'buyer' => '',
        'buyer_id' => '',
        'name_book' => '',
        'seller' => '',
        'seller_id' => '',
        'seller_email' => '',
        'shipping_address' => ''
    );
    
    $champs = array(
        'order' => '_order_error',
        'error_code' => '_error_code',
        'error_message' => '_error_message',
        'buyer' => '_buyer_error',
        'buyer_id' => '_buyer_id_error',
        'name_book' => '_name_book_error',
        'seller' => '_seller_error',
        'seller_id' => '_seller_id_error',
        'seller_email' => '_seller_email_error',
        'shipping_address' => '_shipping_address_error'
    );
    
    foreach ($champs as $champ => $valeur) {
        $shortcodes[$champ] = _modper_get_meta_champ( $order_id, $valeur );
    }
    
    return $shortcodes;
}

function _modper_get_langue( $pays ) {
    
    switch ($pays) {
        case 'FR':
            return 'FR';
            break;
        // Mondial Relay Webservice no reconoce "BE" como idioma
        case 'BE':
            return 'FR';
            break;
        case 'ES':
            return 'ES';
            break;
        case 'DE':
            return 'EN';
            break;
        case 'IT':
            return 'EN';
            break;
        // Mondial Relay Webservice no reconoce "LU" como idioma
        case 'LU':
            return 'FR';
            break;
        case 'PT':
            return 'ES';
            break;
        case 'GB':
            return 'EN';
            break;
        case 'IE':
            return 'EN';
            break;
        case 'NL':
            return 'NL';
            break;
        case 'AT':
            return 'EN';
            break;
    }
    return 1;
}

function _modper_phone_validation($pays,$phone_num) {

    $phone_number = '';

  	if ( ! empty( $phone_num ) ) {
        $phone_number = trim($phone_num);
        
        if ( strlen( $phone_number ) > 2 && substr( $phone_number, 0, 2 ) !== '00' && substr( $phone_number, 0, 1 ) !== '+' ) {

            switch ($pays) {
                case 'FR':
                    if (substr( $phone_number, 0, 1 ) == '0') {
                        $phone_number = substr($phone_number,1);
                    }
                    $phone_number = '+33' . $phone_number;
                    break;

                case 'BE':
                    if (substr( $phone_number, 0, 1 ) == '0') {
                        $phone_number = substr($phone_number,1);
                    }
                    $phone_number = '+32' . $phone_number;
                    break;

                case 'ES':
                    $phone_number = '+34' . $phone_number;
                    break;

                case 'DE':
                    $phone_number = '+49' . $phone_number;
                    break;

                case 'IT':
                    $phone_number = '+39' . $phone_number;
                    break;
                
                case 'LU':
                    $phone_number = '+352' . $phone_number;
                    break;

                case 'PT':
                    $phone_number = '+351' . $phone_number;
                    break;

                case 'GB':
                    $phone_number = '+44' . $phone_number;
                    break;
                    
                case 'IE':
                    if (substr( $phone_number, 0, 1 ) == '0') {
                        $phone_number = substr($phone_number,1);
                    }
                    $phone_number = '+353' . $phone_number;
                    break;

                case 'NL':
                    if (substr( $phone_number, 0, 1 ) == '0') {
                        $phone_number = substr($phone_number,1);
                    }
                    $phone_number = '+31' . $phone_number;
                    break;

                case 'AT':
                    $phone_number = '+43' . $phone_number;
                    break;
            }
        } 
	}    
    return $phone_number;
}

function _modper_statut( $callback ) {
    switch ( $callback ) {
        case 1:
            return __("Signo inválido", 'modificaciones_personalizadas');
            break;
        case 2:
            return __("Número de cartel vacío o inexistente", 'modificaciones_personalizadas');
            break;
        case 3:
            return __("Número de cuenta minorista no válido", 'modificaciones_personalizadas');
            break;
        case 5:
            return __("Número de archivo no válido", 'modificaciones_personalizadas');
            break;
        case 7:
            return __("Número de cliente no válido", 'modificaciones_personalizadas');
            break;
        case 8:
            return __("Contraseña o hash no válido", 'modificaciones_personalizadas');
            break;
        case 9:
            return __("Ciudad no reconocida o duplicada", 'modificaciones_personalizadas');
            break;
        case 10:
            return __("Tipo de recogida no válido", 'modificaciones_personalizadas');
            break;
        case 11:
            return __("Número de retransmisión de recogida no válido", 'modificaciones_personalizadas');
            break;
        case 12:
            return __("País de retransmisión de colección no válido", 'modificaciones_personalizadas');
            break;
        case 13:
            return __("Tipo de envío no válido", 'modificaciones_personalizadas');
            break;
        case 14:
            return __("Número de relé de entrega no válido", 'modificaciones_personalizadas');
            break;
        case 15:
            return __("País de retransmisión de entrega no válido", 'modificaciones_personalizadas');
            break;
        case 20:
            return __("Peso del paquete no válido", 'modificaciones_personalizadas');
            break;
        case 21:
            return __("Tamaño (largo + alto) del paquete no válido", 'modificaciones_personalizadas');
            break;
        case 22:
            return __("Tamaño de paquete no válido", 'modificaciones_personalizadas');
            break;
        case 24:
            return __("Número de envío o seguimiento no válido", 'modificaciones_personalizadas');
            break;
        case 26:
            return __("Hora de montaje no válida", 'modificaciones_personalizadas');
            break;
        case 27:
            return __("Método de recogida o entrega no válido", 'modificaciones_personalizadas');
            break;
        case 28:
            return __("Modo de recopilación no válido", 'modificaciones_personalizadas');
            break;
        case 29:
            return __("Método de entrega no válido", 'modificaciones_personalizadas');
            break;
        case 30:
            return __("Dirección (L1) inválida", 'modificaciones_personalizadas');
            break;
        case 31:
            return __("Dirección (L2) no válida", 'modificaciones_personalizadas');
            break;
        case 33:
            return __("Dirección (L3) no válida", 'modificaciones_personalizadas');
            break;
        case 34:
            return __("Dirección (L4) no válida", 'modificaciones_personalizadas');
            break;
        case 35:
            return __("Ciudad inválida", 'modificaciones_personalizadas');
            break;
        case 36:
            return __("Codigo postal inválido", 'modificaciones_personalizadas');
            break;
        case 37:
            return __("País inválido", 'modificaciones_personalizadas');
            break;
        case 38:
            return __("Numero de telefono invalido", 'modificaciones_personalizadas');
            break;
        case 39:
            return __("Dirección de correo electrónico no válida", 'modificaciones_personalizadas');
            break;
        case 40:
            return __("Parámetros faltantes", 'modificaciones_personalizadas');
            break;
        case 42:
            return __("Importe CRT no válido", 'modificaciones_personalizadas');
            break;
        case 43:
            return __("Moneda CRT no válida", 'modificaciones_personalizadas');
            break;
        case 44:
            return __("Valor de paquete no válido", 'modificaciones_personalizadas');
            break;
        case 45:
            return __("Moneda del valor de paquete no válida", 'modificaciones_personalizadas');
            break;
        case 46:
            return __("Rango de número de envío agotado", 'modificaciones_personalizadas');
            break;
        case 47:
            return __("Nombre de paquete inválido", 'modificaciones_personalizadas');
            break;
        case 48:
            return __("Relevo de paquetes múltiples prohibido", 'modificaciones_personalizadas');
            break;
        case 49:
            return __("Acción invalidada", 'modificaciones_personalizadas');
            break;
        case 60:
            return __("Campo de texto libre no válido (este código de error no invalida la expedición)", 'modificaciones_personalizadas');
            break;
        case 61:
            return __("Top aviso invalidado", 'modificaciones_personalizadas');
            break;
        case 62:
            return __("Instrucción de entrega no válida", 'modificaciones_personalizadas');
            break;
        case 63:
            return __("Seguro inválido", 'modificaciones_personalizadas');
            break;
        case 64:
            return __("Hora de montaje no válida", 'modificaciones_personalizadas');
            break;
        case 65:
            return __("Cita superior no válida", 'modificaciones_personalizadas');
            break;
        case 66:
            return __("Recuperación superior no válida", 'modificaciones_personalizadas');
            break;
        case 67:
            return __("Latitud inválida", 'modificaciones_personalizadas');
            break;
        case 68:
            return __("Longitud inválida", 'modificaciones_personalizadas');
            break;
        case 69:
            return __("Código de signo no válido", 'modificaciones_personalizadas');
            break;
        case 70:
            return __("Número de Point Relais® no válido", 'modificaciones_personalizadas');
            break;
        case 71:
            return __("Naturaleza del punto de venta no válida", 'modificaciones_personalizadas');
            break;
        case 74:
            return __("Idioma inválido", 'modificaciones_personalizadas');
            break;
        case 78:
            return __("País de recogida no válido", 'modificaciones_personalizadas');
            break;
        case 79:
            return __("País de entrega no válido", 'modificaciones_personalizadas');
            break;
        case 80:
            return __("Código de seguimiento: paquete registrado", 'modificaciones_personalizadas');
            break;
        case 81:
            return __("Código de seguimiento: paquete en proceso en Mondial Relay", 'modificaciones_personalizadas');
            break;
        case 82:
            return __("Código de seguimiento: paquete entregado", 'modificaciones_personalizadas');
            break;
        case 83:
            return __("Código de seguimiento: anomalía", 'modificaciones_personalizadas');
            break;
        case 84:
            return __("(Código de seguimiento reservado)", 'modificaciones_personalizadas');
            break;
        case 85:
            return __("(Código de seguimiento reservado)", 'modificaciones_personalizadas');
            break;
        case 86:
            return __("(Código de seguimiento reservado)", 'modificaciones_personalizadas');
            break;
        case 87:
            return __("(Código de seguimiento reservado)", 'modificaciones_personalizadas');
            break;
        case 88:
            return __("(Código de seguimiento reservado)", 'modificaciones_personalizadas');
            break;
        case 89:
            return __("(Código de seguimiento reservado)", 'modificaciones_personalizadas');
            break;
        case 92:
            return __("Saldo insuficiente (cuentas prepagas)", 'modificaciones_personalizadas');
            break;
        case 93:
            return __("Ningún artículo devuelto por el plan de clasificación<br>Si recoge o entrega en un Point Relais®, verifique que los Point Relais® estén disponibles. Si realiza la entrega en su hogar, es probable que el código postal que proporcionó no exista.", 'modificaciones_personalizadas');
            break;
        case 94:
            return __("Paquete inexistente", 'modificaciones_personalizadas');
            break;
        case 95:
            return __("Cuenta de marca no activada", 'modificaciones_personalizadas');
            break;
        case 96:
            return __("Tipo de alférez incorrecto en Base", 'modificaciones_personalizadas');
            break;
        case 97:
            return __("Clave de seguridad no válida", 'modificaciones_personalizadas');
            break;
        case 98:
            return __("Error genérico (parámetros no válidos)<br>Este error oculta otro error de la lista de errores y solo puede ocurrir si la cuenta utilizada está en modo \"Producción\"", 'modificaciones_personalizadas');
            break;
        case 99:
            return __("Error de servicio genérico<br>Este error puede deberse a un problema técnico con el servicio.<br> Por favor notifique a Mondial Relay de este error, especificando la fecha y hora de la solicitud así como los parámetros enviados para realizar una verificación.", 'modificaciones_personalizadas');
            break;
        case 421:
            return __("Ya existe un envío para este pedido", 'modificaciones_personalizadas');
            break;
        case 789:
            return __("Error de red SOAP", 'modificaciones_personalizadas');
            break;
        default:
            return __("No hay información disponible sobre este error.", 'modificaciones_personalizadas');
            break;
    }
}














































    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    