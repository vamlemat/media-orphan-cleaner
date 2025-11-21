<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

delete_option('moc_settings');
delete_option('moc_last_orphans');
