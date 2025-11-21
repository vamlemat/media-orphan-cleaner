<?php
/*
Plugin Name: Media Orphan Cleaner
Description: Escanea y permite borrar imágenes huérfanas (Elementor + JetEngine + JetFormBuilder + WooCommerce).
Version: 1.0.0
Author: Tu Nombre
*/

if (!defined('ABSPATH')) {
    exit;
}

define('MOC_VERSION', '1.0.0');
define('MOC_PATH', plugin_dir_path(__FILE__));
define('MOC_URL', plugin_dir_url(__FILE__));

require_once MOC_PATH . 'includes/class-moc-scanner.php';
require_once MOC_PATH . 'includes/class-moc-admin.php';

function moc_bootstrap() {
    $scanner = new MOC_Scanner();
    new MOC_Admin($scanner);
}
add_action('plugins_loaded', 'moc_bootstrap');
