<?php

/**
  * Agregar menu del plugin en el sub_menu de Ajustes.
*/
function _modper_add_admin_menu(){
	add_options_page(
		'Modificaciones Personalizadas', // El texto que se mostrará en la barra de título del navegador.
		'Modificaciones Personalizadas', // El texto que se mostrará en el elemento del menú.
		'manage_options',                // Rol que el usuario debe tener para acceder a este menú
		'modificaciones_personalizadas', // Valor único que identifica este menú.
		'_modper_options_page'           // Función que se llama cuando se hace clic en el menú para mostrar la página de opciones.
	);
}

/**
 * Llama al archivo que contiene la página de opciones. 
 */
function _modper_options_page(){	
    require_once MODPER_RUTA.'admin/partials/modificaciones-personalizadas-admin-display.php';
}

/**
 * Registrar las opciones de configuración
 */
function _modper_register_plugin_settings(){

    // Si la opción no existe, se crea
    if(false == get_option('modper_options')){  
        add_option('modper_options');
    }

    // Agregar sección de opciones
    add_settings_section(
        'modper_general_settings_section',                    // Identificador de la sección de opciones.
        __('', 'modificaciones_personalizadas'), // Titulo
        '_modper_print_section_info',                         // Función Callback que imprime la descripción de la sección
        'modificaciones_personalizadas'                       // Página donde se mostraran las opciones. 
    );  

    // Agregar campo opción en la sección creada
    add_settings_field(
        'tab',                                        // Identificador de la sección de opciones.
        __('Nombre de la pestaña de libros publicados', 'modificaciones_personalizadas'), // Titulo
        '_modper_setting_tab_callback',              // Función Callback que imprime la descripción de la sección
        'modificaciones_personalizadas',              // Página donde se mostraran las opciones. 
        'modper_general_settings_section',            // Identificador de la sección a la que pertenece este campo de opción.       
        array(                                        // Arreglo de opciones
            'label_for' => 'modper_tab',
            // Puede contener clases (que se le agregan a la fila de la tabla donde aparece la opción) y atributos personalizados.
            //'class' => 'wporg_row',
            //'wporg_custom_data' => 'custom',
        )    
    );    
    
    // Agregar campo opción en la sección creada
    add_settings_field(
        'vender',                                        
        __('URL de la página para subir un libro', 'modificaciones_personalizadas'), 
        '_modper_setting_vender_callback',
        'modificaciones_personalizadas',
        'modper_general_settings_section', 
        array(                                
            'label_for' => 'modper_vender',
            // Puede contener clases (que se le agregan a la fila de la tabla donde aparece la opción) y atributos personalizados.
            //'class' => 'wporg_row',
            //'wporg_custom_data' => 'custom',
        )    
    ); 

    // Registrar la colección de opciones creada. 
    register_setting('modificaciones_personalizadas', 'modper_options', '_modper_validate_options_general');
}

/**
 * Validar el texto ingresado en los campos de opciones. 
 */
function _modper_validate_options_general($input){

    // Crea el arreglo para almacenar las opciones validadas.
    $output = array();
    // Recorre cada una de las opciones entrantes
    foreach($input as $key => $value){
        // Comprobar si la opción actual tiene un valor.  Si es así, se procesa.
        if(isset($input[$key])){
            // Elimina todas las etiquetas HTML y PHP y maneja correctamente las cadenas entre comillas.
            $output[$key] = strip_tags(stripslashes($input[$key]));
        } 
        
    }
    
    // Devuelve el arreglo procesando cualquier función adicional filtrada por esta acción.
    return apply_filters('_modper_validate_options_general', $output, $input);
}

/**
 * Imprimir la descripción de la sección de opciones. 
 */
function _modper_print_section_info($args){

    ?>
        <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Aquí podrá ingresar el nombre de la pestaña donde se visualizaran los libros del perfil de cada vendedor y la url de la página donde los usuarios pueden subir un libro.', 'modificaciones_personalizadas'); ?>
        </p>
    <?php

}

/**
 * Imprimir el campo de opción   
 * 
 * - el valor "label_for" se usa para el atributo "for" del <label>. 
 * - el valor "clase" (si se agrega) se usa para el atributo "clase" del <tr> que contiene el campo.
 * 
 * @param array $args
 */
function _modper_setting_tab_callback($args){

    // Obtener el arreglo de opciones para acceder luego a cada valor por separado, de la forma $options['id_de_opción']
    $options = get_option('modper_options');

    ?>
        <input class="w-100 regular-text" placeholder="Profile tab" type="text" id="<?php echo esc_attr($args["label_for"]); ?>" name="modper_options[<?php echo esc_attr($args["label_for"]); ?>]" value="<?php echo isset($options[$args["label_for"]]) ? (sanitize_text_field(esc_attr($options[$args["label_for"]]))) : (''); ?>" required/>
        <p class="description" id="input-description">En minúsculas y sin espacios.</p>
    <?php
    
}

/**
 * Imprimir el campo de opción   
 * 
 * - el valor "label_for" se usa para el atributo "for" del <label>. 
 * - el valor "clase" (si se agrega) se usa para el atributo "clase" del <tr> que contiene el campo.
 * 
 * @param array $args
 */
function _modper_setting_vender_callback($args){

    // Obtener el arreglo de opciones para acceder luego a cada valor por separado, de la forma $options['id_de_opción']
    $options = get_option('modper_options');

    ?>
        <input class="w-100 regular-text" placeholder="URL" type="text" id="<?php echo esc_attr($args["label_for"]); ?>" name="modper_options[<?php echo esc_attr($args["label_for"]); ?>]" value="<?php echo isset($options[$args["label_for"]]) ? (sanitize_text_field(esc_attr($options[$args["label_for"]]))) : (''); ?>" required/>
        <p class="description" id="input-description">Ejemplo: https://www.leogratis.com/vender/</p>
    <?php
    
}


// Registrar cada gancho de acción para el campo del plugin en el submenu y las opciones de la página de configuración.  
add_action('admin_menu', '_modper_add_admin_menu');
add_action('admin_init', '_modper_register_plugin_settings');


    



