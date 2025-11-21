<?php
/*
Plugin Name: MOC Test Data Generator
Description: Genera datos de prueba para Media Orphan Cleaner - SOLO PARA TESTING
Version: 1.0
Author: vamlemat
Author URI: https://github.com/vamlemat
License: GPL v2 or later
Text Domain: moc-test-generator
*/

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'moc_test_add_menu');

function moc_test_add_menu() {
    add_management_page(
        'MOC Test Generator',
        'MOC Test Generator',
        'manage_options',
        'moc-test-generator',
        'moc_test_render_page'
    );
}

function moc_test_render_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    if (isset($_POST['moc_generate']) && check_admin_referer('moc_test_generate')) {
        $results = moc_test_generate_data();
        echo '<div class="notice notice-success"><p>✅ ' . esc_html($results['message']) . '</p></div>';
    }
    
    if (isset($_POST['moc_cleanup']) && check_admin_referer('moc_test_cleanup')) {
        $results = moc_test_cleanup_data();
        echo '<div class="notice notice-success"><p>🗑️ ' . esc_html($results['message']) . '</p></div>';
    }
    
    ?>
    <div class="wrap">
        <h1>🧪 Media Orphan Cleaner - Generador de Datos de Prueba</h1>
        
        <div class="notice notice-warning">
            <p><strong>⚠️ ADVERTENCIA:</strong> Este plugin es solo para entornos de TESTING. No usar en producción.</p>
        </div>
        
        <h2>Generar Datos de Prueba</h2>
        <p>Esto creará:</p>
        <ul style="list-style: disc; margin-left: 30px;">
            <li>✅ 5 imágenes usadas en posts (con clase wp-image-X)</li>
            <li>✅ 3 imágenes en galerías de WooCommerce</li>
            <li>✅ 2 imágenes en meta fields de JetEngine</li>
            <li>✅ 1 imagen en widget de sidebar</li>
            <li>❌ 10 imágenes HUÉRFANAS (no usadas en ningún lugar)</li>
        </ul>
        
        <form method="post">
            <?php wp_nonce_field('moc_test_generate'); ?>
            <p>
                <button type="submit" name="moc_generate" class="button button-primary">
                    🚀 Generar Datos de Prueba
                </button>
            </p>
        </form>
        
        <hr>
        
        <h2>Limpiar Datos de Prueba</h2>
        <p>Elimina todas las imágenes generadas por este script.</p>
        
        <form method="post">
            <?php wp_nonce_field('moc_test_cleanup'); ?>
            <p>
                <button type="submit" name="moc_cleanup" class="button button-secondary"
                        onclick="return confirm('¿Eliminar todos los datos de prueba?');">
                    🗑️ Limpiar Datos de Prueba
                </button>
            </p>
        </form>
    </div>
    <?php
}

function moc_test_generate_data() {
    $upload_dir = wp_upload_dir();
    $test_ids = array();
    $errors = array();
    
    // Verificar si GD está disponible
    if (!function_exists('imagecreatetruecolor')) {
        return array(
            'success' => false,
            'message' => 'Error: La extensión GD de PHP no está instalada. Usando método alternativo...',
            'ids' => array()
        );
    }
    
    // Crear 21 imágenes de prueba (placeholder)
    for ($i = 1; $i <= 21; $i++) {
        $filename = 'test-image-' . $i . '-' . time() . '.jpg';
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        try {
            // Crear imagen de 100x100 píxeles
            $image = imagecreatetruecolor(100, 100);
            if ($image === false) {
                throw new Exception("No se pudo crear la imagen");
            }
            
            $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            imagefill($image, 0, 0, $color);
            
            // Añadir texto
            $text_color = imagecolorallocate($image, 255, 255, 255);
            imagestring($image, 5, 30, 45, "IMG $i", $text_color);
            
            imagejpeg($image, $file_path, 90);
            imagedestroy($image);
        } catch (Exception $e) {
            $errors[] = "Imagen $i: " . $e->getMessage();
            continue;
        }
        
        // Crear attachment
        $attachment_id = wp_insert_attachment(array(
            'post_mime_type' => 'image/jpeg',
            'post_title'     => 'Test Image ' . $i,
            'post_content'   => '',
            'post_status'    => 'inherit'
        ), $file_path);
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attachment_id, $file_path);
        wp_update_attachment_metadata($attachment_id, $attach_data);
        
        $test_ids[] = $attachment_id;
    }
    
    // Escenario 1: 5 imágenes en contenido de posts
    for ($i = 0; $i < 5; $i++) {
        $post_id = wp_insert_post(array(
            'post_title'   => 'Test Post ' . ($i + 1),
            'post_content' => 'Contenido con imagen <img class="wp-image-' . $test_ids[$i] . '" src="test.jpg">',
            'post_status'  => 'publish',
            'post_type'    => 'post',
        ));
    }
    
    // Escenario 2: 3 imágenes como featured image / galería WooCommerce
    if (class_exists('WooCommerce')) {
        for ($i = 5; $i < 8; $i++) {
            $product_id = wp_insert_post(array(
                'post_title'  => 'Test Product ' . ($i - 4),
                'post_type'   => 'product',
                'post_status' => 'publish',
            ));
            update_post_meta($product_id, '_thumbnail_id', $test_ids[$i]);
        }
    } else {
        // Si no hay WooCommerce, usar como featured en posts
        for ($i = 5; $i < 8; $i++) {
            $post_id = wp_insert_post(array(
                'post_title'  => 'Test Post Featured ' . ($i - 4),
                'post_status' => 'publish',
            ));
            update_post_meta($post_id, '_thumbnail_id', $test_ids[$i]);
        }
    }
    
    // Escenario 3: 2 imágenes en meta fields de JetEngine
    for ($i = 8; $i < 10; $i++) {
        $post_id = wp_insert_post(array(
            'post_title'  => 'Test JetEngine Post ' . ($i - 7),
            'post_status' => 'publish',
        ));
        update_post_meta($post_id, 'imagen_portada', $test_ids[$i]);
    }
    
    // Escenario 4: 1 imagen en widget (simulado)
    $widgets = get_option('widget_media_image', array());
    $widgets['moc_test'] = array(
        'attachment_id' => $test_ids[10],
        'url' => wp_get_attachment_url($test_ids[10]),
    );
    update_option('widget_media_image', $widgets);
    
    // Los IDs 11-20 quedan HUÉRFANOS (no usados)
    
    // Guardar IDs para limpieza posterior
    update_option('moc_test_ids', $test_ids, false);
    
    $message = 'Generadas ' . count($test_ids) . ' imágenes de prueba (10 huérfanas esperadas)';
    if (!empty($errors)) {
        $message .= ' - ' . count($errors) . ' errores: ' . implode(', ', $errors);
    }
    
    return array(
        'success' => count($test_ids) > 0,
        'message' => $message,
        'ids' => $test_ids,
        'errors' => $errors
    );
}

function moc_test_cleanup_data() {
    $test_ids = get_option('moc_test_ids', array());
    
    $deleted = 0;
    foreach ($test_ids as $att_id) {
        if (wp_delete_attachment($att_id, true)) {
            $deleted++;
        }
    }
    
    // Limpiar widget
    $widgets = get_option('widget_media_image', array());
    if (isset($widgets['moc_test'])) {
        unset($widgets['moc_test']);
        update_option('widget_media_image', $widgets);
    }
    
    // Buscar y eliminar posts de prueba
    $test_posts = get_posts(array(
        'post_type' => array('post', 'product'),
        'posts_per_page' => -1,
        's' => 'Test',
        'post_status' => 'any',
    ));
    
    foreach ($test_posts as $post) {
        if (strpos($post->post_title, 'Test') === 0) {
            wp_delete_post($post->ID, true);
        }
    }
    
    delete_option('moc_test_ids');
    
    return array(
        'success' => true,
        'message' => 'Eliminadas ' . $deleted . ' imágenes de prueba y posts asociados'
    );
}
