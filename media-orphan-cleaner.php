<?php
/*
Plugin Name: Media Orphan Cleaner
Plugin URI: https://github.com/vamlemat/media-orphan-cleaner
Description: Escanea y permite borrar imágenes huérfanas en la biblioteca de medios. Compatible con WooCommerce, Elementor, JetEngine, ACF, Widgets y más.
Version: 1.2.0-beta
Author: vamlemat
Author URI: https://github.com/vamlemat
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: media-orphan-cleaner
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
*/

if (!defined('ABSPATH')) {
    exit;
}

define('MOC_VERSION', '1.2.0-beta');
define('MOC_PATH', plugin_dir_path(__FILE__));
define('MOC_URL', plugin_dir_url(__FILE__));

require_once MOC_PATH . 'includes/class-moc-scanner.php';
require_once MOC_PATH . 'includes/class-moc-admin.php';

function moc_bootstrap() {
    $scanner = new MOC_Scanner();
    new MOC_Admin($scanner);
}
add_action('plugins_loaded', 'moc_bootstrap');
