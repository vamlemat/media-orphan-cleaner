<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Limpiar options
delete_option('moc_settings');
delete_option('moc_last_orphans');
delete_option('moc_backup');
delete_option('moc_last_logs');

// Limpiar transients huérfanos
global $wpdb;
$pattern = $wpdb->esc_like('_transient_moc_') . '%';
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $pattern
    )
);
