<?php
/*
Plugin Name: Acceso Privado a Páginas
Description: Este plugin permite restringir el acceso a ciertas páginas solo a usuarios con el rol 'Privado'.
Version: 4.2.1
Author: Nxvermore
*/

// 1. Crear el rol 'Privado' al activar el plugin
register_activation_hook(__FILE__, 'ap_crear_rol_privado');
function ap_crear_rol_privado() {
    // Si el rol 'Privado' ya existe, lo borra y lo vuelve a crear
    remove_role('privado');
    add_role('privado', 'Privado', ['read' => true]);
}

// 2. Agregar un campo de metabox para establecer una página como 'Privada'
add_action('add_meta_boxes', 'ap_agregar_metabox_privado');
function ap_agregar_metabox_privado() {
    add_meta_box(
        'ap_privado_metabox',
        'Acceso Privado',
        'ap_privado_metabox_callback',
        'page',
        'side',
        'high'
    );
}

function ap_privado_metabox_callback($post) {
    // Verifica si la opción 'privado' está marcada
    $es_privado = get_post_meta($post->ID, '_ap_es_privado', true);
    ?>
    <label for="ap_es_privado">
        <input type="checkbox" name="ap_es_privado" id="ap_es_privado" value="1" <?php checked($es_privado, '1'); ?> />
        Establecer como Privado
    </label>
    <?php
}

// 3. Guardar el estado de la opción de privacidad
add_action('save_post', 'ap_guardar_estado_privado');
function ap_guardar_estado_privado($post_id) {
    if (isset($_POST['ap_es_privado'])) {
        update_post_meta($post_id, '_ap_es_privado', '1');
    } else {
        delete_post_meta($post_id, '_ap_es_privado');
    }
}

// 4. Restringir el acceso a las páginas privadas
add_action('template_redirect', 'ap_restringir_acceso_paginas_privadas');
function ap_restringir_acceso_paginas_privadas() {
    if (is_page()) {
        global $post;
        $es_privado = get_post_meta($post->ID, '_ap_es_privado', true);

        // Si la página es privada y el usuario no tiene el rol 'Privado', muestra un mensaje de acceso denegado
        if ($es_privado && !current_user_can('privado')) {
            wp_die('No tienes acceso al contenido');
        }
    }
}
