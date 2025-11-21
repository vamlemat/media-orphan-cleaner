<?php
if (!defined('ABSPATH')) {
    exit;
}

class MOC_Admin {
    private $scanner;
    private $option_key = 'moc_settings';
    private $orphans_option = 'moc_last_orphans';
    private $backup_option = 'moc_backup';
    private $logs_option = 'moc_last_logs';

    public function __construct($scanner) {
        $this->scanner = $scanner;

        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        add_action('wp_ajax_moc_start_scan', array($this, 'ajax_start_scan'));
        add_action('wp_ajax_moc_scan_batch', array($this, 'ajax_scan_batch'));

        add_action('admin_post_moc_delete', array($this, 'handle_delete'));
        add_action('admin_post_moc_export_csv', array($this, 'handle_export_csv'));
        add_action('admin_post_moc_restore_backup', array($this, 'handle_restore_backup'));
        add_action('admin_post_moc_clear_errors', array($this, 'handle_clear_errors'));
        add_action('admin_post_moc_clear_logs', array($this, 'handle_clear_logs'));
        
        // Limpiar logs antiguos automáticamente
        add_action('admin_init', array($this, 'cleanup_old_logs'));
    }

    public function add_menu() {
        // Añadir menú principal
        add_menu_page(
            'Media Orphan Cleaner',
            'Orphan Cleaner',
            'manage_options',
            'media-orphan-cleaner',
            array($this, 'render_page'),
            'dashicons-images-alt2',
            25  // Posición: después de "Biblioteca" (20)
        );
        
        // Submenú: Scanner
        add_submenu_page(
            'media-orphan-cleaner',
            'Escanear Imágenes',
            'Scanner',
            'manage_options',
            'media-orphan-cleaner',
            array($this, 'render_page')
        );
        
        // Submenú: Logs
        add_submenu_page(
            'media-orphan-cleaner',
            'Logs y Debug',
            'Logs',
            'manage_options',
            'moc-logs',
            array($this, 'render_logs_page')
        );
        
        // Submenú: Configuración
        add_submenu_page(
            'media-orphan-cleaner',
            'Configuración',
            'Configuración',
            'manage_options',
            'moc-settings',
            array($this, 'render_settings_page')
        );
        
        // Nota: El plugin de testing se añade aquí automáticamente si está activo
    }

    public function register_settings() {
        register_setting('moc_settings_group', $this->option_key, array($this, 'sanitize_settings'));

        add_settings_section(
            'moc_main_section',
            'Ajustes de escaneo',
            '__return_false',
            'media-orphan-cleaner'
        );

        add_settings_field(
            'jetengine_meta_keys',
            'Meta keys extra de JetEngine',
            array($this, 'render_jetengine_field'),
            'media-orphan-cleaner',
            'moc_main_section'
        );

        add_settings_field(
            'dry_run',
            'Modo prueba (Dry Run)',
            array($this, 'render_dry_run_field'),
            'media-orphan-cleaner',
            'moc_main_section'
        );

        add_settings_field(
            'enable_backup',
            'Backup antes de eliminar',
            array($this, 'render_backup_field'),
            'media-orphan-cleaner',
            'moc_main_section'
        );
    }

    public function sanitize_settings($settings) {
        $out = array();
        $raw = isset($settings['jetengine_meta_keys']) ? (string)$settings['jetengine_meta_keys'] : '';
        $lines = preg_split('/\r\n|\r|\n/', $raw);

        $clean = array();
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                $clean[] = sanitize_key($line);
            }
        }

        $out['jetengine_meta_keys'] = implode("\n", array_unique($clean));
        $out['dry_run'] = !empty($settings['dry_run']);
        $out['enable_backup'] = !empty($settings['enable_backup']);
        return $out;
    }

    public function render_jetengine_field() {
        $settings = get_option($this->option_key, array());
        $val = isset($settings['jetengine_meta_keys']) ? $settings['jetengine_meta_keys'] : '';
        ?>
        <textarea name="<?php echo esc_attr($this->option_key); ?>[jetengine_meta_keys]"
                  rows="6" cols="60"
                  placeholder="Ej:\nimagen_portada\ngaleria_proyecto"><?php
            echo esc_textarea($val);
        ?></textarea>
        <p class="description">
            Añade aquí los <strong>slugs/meta keys</strong> de JetEngine que sean campos imagen/galería.
            Una por línea.
        </p>
        <?php
    }

    public function render_dry_run_field() {
        $settings = get_option($this->option_key, array());
        $checked = !empty($settings['dry_run']) ? 'checked' : '';
        ?>
        <label>
            <input type="checkbox" 
                   name="<?php echo esc_attr($this->option_key); ?>[dry_run]" 
                   value="1" <?php echo $checked; ?>>
            Solo mostrar resultados sin eliminar nada (recomendado para testing)
        </label>
        <?php
    }

    public function render_backup_field() {
        $settings = get_option($this->option_key, array());
        $checked = !empty($settings['enable_backup']) ? 'checked' : '';
        ?>
        <label>
            <input type="checkbox" 
                   name="<?php echo esc_attr($this->option_key); ?>[enable_backup]" 
                   value="1" <?php echo $checked; ?>>
            Guardar IDs de imágenes eliminadas para poder restaurarlas
        </label>
        <?php
    }

    public function enqueue_assets($hook) {
        if ($hook !== 'tools_page_media-orphan-cleaner') {
            return;
        }

        wp_enqueue_style('moc-admin', MOC_URL . 'assets/admin.css', array(), MOC_VERSION);
        wp_enqueue_script('moc-admin', MOC_URL . 'assets/admin.js', array('jquery'), MOC_VERSION, true);

        wp_localize_script('moc-admin', 'MOC_Ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('moc_ajax_nonce'),
        ));
    }

    public function cleanup_old_logs() {
        // Solo ejecutar una vez al día
        $last_cleanup = get_option('moc_last_log_cleanup', 0);
        if (time() - $last_cleanup < DAY_IN_SECONDS) {
            return;
        }
        
        $logs = get_option($this->logs_option, array());
        if (empty($logs)) {
            return;
        }
        
        // Verificar si el primer log tiene más de 1 día
        $first_log = reset($logs);
        if (isset($first_log['time'])) {
            $log_timestamp = strtotime($first_log['time']);
            if (time() - $log_timestamp > DAY_IN_SECONDS) {
                delete_option($this->logs_option);
            }
        }
        
        update_option('moc_last_log_cleanup', time(), false);
    }
    
    public function render_logs_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $logs = get_option($this->logs_option, array());
        $scan_errors = get_option('moc_scan_errors', array());
        $log_age = '';
        
        // Calcular edad del log
        if (!empty($logs)) {
            $first_log = reset($logs);
            if (isset($first_log['time'])) {
                $log_timestamp = strtotime($first_log['time']);
                $hours = floor((time() - $log_timestamp) / 3600);
                if ($hours < 1) {
                    $log_age = 'hace menos de 1 hora';
                } elseif ($hours < 24) {
                    $log_age = 'hace ' . $hours . ' hora' . ($hours > 1 ? 's' : '');
                } else {
                    $days = floor($hours / 24);
                    $log_age = 'hace ' . $days . ' día' . ($days > 1 ? 's' : '');
                }
            }
        }
        ?>
        <div class="wrap moc-wrap">
            <h1>📊 Logs y Debug</h1>
            
            <?php if (isset($_GET['logs_cleared'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p>✅ Logs eliminados correctamente.</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($scan_errors)): ?>
                <div class="notice notice-error">
                    <h3>⚠️ Errores Recientes</h3>
                    <?php foreach ($scan_errors as $error): ?>
                        <p><strong><?php echo esc_html($error['time']); ?>:</strong> <?php echo esc_html($error['message']); ?></p>
                    <?php endforeach; ?>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:10px;">
                        <?php wp_nonce_field('moc_clear_errors'); ?>
                        <input type="hidden" name="action" value="moc_clear_errors">
                        <button type="submit" class="button">Limpiar errores</button>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($logs)): ?>
                <div class="moc-logs-panel">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <h2 style="margin:0;">🔍 Último Escaneo <?php if ($log_age): ?><small style="color:#666; font-size:14px;">(<?php echo esc_html($log_age); ?>)</small><?php endif; ?></h2>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin:0;">
                            <?php wp_nonce_field('moc_clear_logs'); ?>
                            <input type="hidden" name="action" value="moc_clear_logs">
                            <button type="submit" class="button" onclick="return confirm('¿Borrar todos los logs?');">🗑️ Limpiar Logs</button>
                        </form>
                    </div>
                    <div class="moc-logs-content">
                        <?php foreach ($logs as $log): ?>
                            <div class="moc-log-entry">
                                <strong><?php echo esc_html($log['time']); ?>:</strong> 
                                <?php echo esc_html($log['message']); ?>
                                <?php if (isset($log['data'])): ?>
                                    <pre><?php echo esc_html(json_encode($log['data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="notice notice-info">
                    <p>No hay logs disponibles. Ejecuta un escaneo primero.</p>
                </div>
            <?php endif; ?>
            
            <div style="margin-top:20px;">
                <h3>ℹ️ Información del Sistema</h3>
                <table class="widefat">
                    <tr>
                        <td><strong>WordPress:</strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP:</strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Memoria PHP:</strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Max Execution Time:</strong></td>
                        <td><?php echo ini_get('max_execution_time'); ?>s</td>
                    </tr>
                    <tr>
                        <td><strong>GD Library:</strong></td>
                        <td><?php echo function_exists('gd_info') ? '✅ Instalada' : '❌ No instalada'; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Imágenes en biblioteca:</strong></td>
                        <td><?php 
                            $count = wp_count_posts('attachment');
                            echo isset($count->inherit) ? $count->inherit : 0;
                        ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap moc-wrap">
            <h1>⚙️ Configuración - Media Orphan Cleaner</h1>
            
            <form method="post" action="options.php" class="moc-settings">
                <?php
                settings_fields('moc_settings_group');
                do_settings_sections('media-orphan-cleaner');
                submit_button('Guardar configuración');
                ?>
            </form>
        </div>
        <?php
    }
    
    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $orphans = get_option($this->orphans_option, array());
        $logs = get_option($this->logs_option, array());
        $backup = get_option($this->backup_option, array());
        $settings = get_option($this->option_key, array());
        $dry_run = !empty($settings['dry_run']);
        ?>
        <div class="wrap moc-wrap">
            <h1>🧹 Media Orphan Cleaner <small style="font-size:14px;color:#666;">(v<?php echo MOC_VERSION; ?>)</small></h1>
            
            <?php if ($dry_run): ?>
                <div class="notice notice-warning">
                    <p><strong>⚠️ MODO PRUEBA ACTIVADO:</strong> No se eliminará nada. Desactiva esta opción para poder borrar imágenes.</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($backup)): ?>
                <div class="notice notice-info">
                    <p>
                        <strong>📦 Backup disponible:</strong> Se eliminaron <?php echo count($backup['ids']); ?> imágenes el <?php echo esc_html($backup['date']); ?>.
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                            <?php wp_nonce_field('moc_restore_nonce'); ?>
                            <input type="hidden" name="action" value="moc_restore_backup">
                            <button class="button" onclick="return confirm('¿Restaurar las <?php echo count($backup['ids']); ?> imágenes?');">Restaurar backup</button>
                        </form>
                    </p>
                </div>
            <?php endif; ?>

            <div class="notice notice-info">
                <p>
                    <strong>💡 Consejo:</strong> 
                    Ve a <a href="<?php echo admin_url('admin.php?page=moc-settings'); ?>">Configuración</a> 
                    para ajustar el modo dry-run y backup.
                    | <a href="<?php echo admin_url('admin.php?page=moc-logs'); ?>">Ver Logs</a>
                </p>
            </div>

            <hr>

            <h2>Escaneo</h2>
            <p>
                Este escaneo detecta imágenes no usadas en:
                <strong>WooCommerce</strong> (destacadas/galerías/categorías), <strong>Elementor</strong> (incl. templates),
                <strong>JetEngine</strong> (meta keys configuradas), <strong>JetFormBuilder/Gutenberg</strong> (bloques),
                <strong>Widgets</strong>, <strong>Customizer</strong>, <strong>ACF</strong>,
                y opciones del sitio (logo/site icon).
            </p>

            <button id="moc-start-scan" class="button button-primary">Iniciar escaneo</button>

            <div id="moc-progress" class="moc-progress" style="display:none;">
                <div class="moc-progress-bar">
                    <span class="moc-progress-fill" style="width:0%"></span>
                </div>
                <p class="moc-progress-text">0%</p>
            </div>

            <div id="moc-scan-result" class="notice notice-info" style="display:none;"></div>
            
            <div id="moc-logs" class="moc-logs" style="display:none;">
                <h3>🔍 Log de escaneo</h3>
                <div id="moc-logs-content" class="moc-logs-content"></div>
            </div>

            <?php if (!empty($orphans)): ?>
                <hr>
                <h2>Posibles huérfanas (<?php echo count($orphans); ?>)</h2>
                
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:10px;">
                    <?php wp_nonce_field('moc_export_nonce'); ?>
                    <input type="hidden" name="action" value="moc_export_csv">
                    <button type="submit" class="button">📄 Exportar CSV</button>
                </form>

                <?php if (!empty($logs)): ?>
                    <details class="moc-logs-details">
                        <summary>🔍 Ver log del último escaneo</summary>
                        <div class="moc-logs-content">
                            <?php foreach ($logs as $log): ?>
                                <div class="moc-log-entry">
                                    <strong><?php echo esc_html($log['time']); ?>:</strong> 
                                    <?php echo esc_html($log['message']); ?>
                                    <?php if (isset($log['data'])): ?>
                                        <pre><?php echo esc_html(json_encode($log['data'], JSON_PRETTY_PRINT)); ?></pre>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </details>
                <?php endif; ?>
                
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('moc_delete_nonce'); ?>
                    <input type="hidden" name="action" value="moc_delete">

                    <div style="margin-bottom:15px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <div style="display:flex; gap:5px;">
                            <button type="button" class="button" onclick="mocSelectAll()">
                                ☑️ Todas
                            </button>
                            <button type="button" class="button" onclick="mocSelectPhysical()">
                                ✅ Solo físicos
                            </button>
                            <button type="button" class="button" onclick="mocSelectGhosts()">
                                ⚠️ Solo fantasma
                            </button>
                            <button type="button" class="button" onclick="mocDeselectAll()">
                                ☐ Ninguna
                            </button>
                        </div>
                        
                        <div style="border-left:2px solid #ddd; padding-left:10px;">
                            <?php if ($dry_run): ?>
                                <button type="button" class="button button-secondary" disabled>
                                    🔒 Borrar deshabilitado (modo prueba activo)
                                </button>
                            <?php else: ?>
                                <button class="button button-danger" type="submit"
                                        onclick="return confirm('¿Seguro? Esto borra archivos físicos y sus tamaños.');">
                                    🗑️ Borrar seleccionadas
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th style="width:40px;"><input type="checkbox" id="moc-select-all"></th>
                                <th>ID</th>
                                <th>Archivo</th>
                                <th>Tamaño</th>
                                <th>Estado</th>
                                <th>Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        $orphans_with_file = 0;
                        $orphans_no_file = 0;
                        foreach ($orphans as $att_id): 
                            $att_id = (int)$att_id;
                            $url = wp_get_attachment_url($att_id);
                            $file_path = get_attached_file($att_id);
                            $has_physical_file = $file_path && file_exists($file_path);
                            
                            $size = 0;
                            if ($has_physical_file) {
                                $size = filesize($file_path);
                                $orphans_with_file++;
                            } else {
                                $orphans_no_file++;
                            }
                            $size_kb = round($size / 1024, 2);
                            
                            // Determinar nombre del archivo
                            if ($has_physical_file) {
                                $filename = basename($file_path);
                            } elseif ($url && !strpos($url, '?attachment_id=')) {
                                $filename = basename($url);
                            } else {
                                $filename = get_the_title($att_id);
                                if (empty($filename) || $filename === 'Auto Draft') {
                                    $filename = '(sin título)';
                                }
                            }
                            
                            // Estado del archivo
                            $status_icon = $has_physical_file ? '✅' : '⚠️';
                            $status_text = $has_physical_file ? 'OK' : 'Sin archivo físico';
                            $status_class = $has_physical_file ? 'moc-status-ok' : 'moc-status-no-file';
                            ?>
                            <tr class="<?php echo esc_attr($status_class); ?>">
                                <td><input type="checkbox" class="moc-checkbox" name="delete_ids[]" value="<?php echo esc_attr($att_id); ?>"></td>
                                <td><?php echo esc_html($att_id); ?></td>
                                <td>
                                    <?php if ($has_physical_file && $url): ?>
                                        <a href="<?php echo esc_url($url); ?>" target="_blank"><?php echo esc_html($filename); ?></a>
                                    <?php else: ?>
                                        <span style="color:#666;"><?php echo esc_html($filename); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($size_kb); ?> KB</td>
                                <td><span class="<?php echo esc_attr($status_class); ?>"><?php echo $status_icon; ?> <?php echo esc_html($status_text); ?></span></td>
                                <td>
                                    <?php if ($has_physical_file): ?>
                                        <?php echo wp_get_attachment_image($att_id, array(80, 80)); ?>
                                    <?php else: ?>
                                        <span style="color:#999;">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php
                    $total_size = $this->scanner->calculate_total_size($orphans);
                    $size_mb = round($total_size / 1024 / 1024, 2);
                    ?>
                    <div style="margin:15px 0; padding:10px; background:#f0f0f1; border-left:4px solid #2271b1;">
                        <p style="margin:5px 0;">
                            💾 <strong>Espacio a liberar:</strong> <?php echo esc_html($size_mb); ?> MB
                        </p>
                        <?php if ($orphans_no_file > 0): ?>
                            <p style="margin:5px 0; color:#d63638;">
                                ⚠️ <strong><?php echo $orphans_no_file; ?> registro(s) sin archivo físico</strong> 
                                (solo en base de datos, 0 KB)
                            </p>
                        <?php endif; ?>
                        <?php if ($orphans_with_file > 0): ?>
                            <p style="margin:5px 0;">
                                ✅ <strong><?php echo $orphans_with_file; ?> archivo(s) con datos físicos</strong>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top:15px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                        <div style="display:flex; gap:5px;">
                            <button type="button" class="button" onclick="mocSelectAll()">
                                ☑️ Todas
                            </button>
                            <button type="button" class="button" onclick="mocSelectPhysical()">
                                ✅ Solo físicos
                            </button>
                            <button type="button" class="button" onclick="mocSelectGhosts()">
                                ⚠️ Solo fantasma
                            </button>
                            <button type="button" class="button" onclick="mocDeselectAll()">
                                ☐ Ninguna
                            </button>
                        </div>
                        
                        <div style="border-left:2px solid #ddd; padding-left:10px;">
                            <?php if ($dry_run): ?>
                                <button type="button" class="button button-secondary" disabled>
                                    🔒 Borrar deshabilitado (modo prueba activo)
                                </button>
                            <?php else: ?>
                                <button class="button button-danger" type="submit"
                                        onclick="return confirm('¿Seguro? Esto borra archivos físicos y sus tamaños.');">
                                    🗑️ Borrar seleccionadas
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    public function ajax_start_scan() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No autorizado');
        }
        check_ajax_referer('moc_ajax_nonce', 'nonce');

        try {
            $settings = get_option($this->option_key, array());
            $extra_keys_raw = isset($settings['jetengine_meta_keys']) ? $settings['jetengine_meta_keys'] : '';
            $extra_keys = array_filter(array_map('sanitize_key', preg_split('/\r\n|\r|\n/', (string)$extra_keys_raw)));

            $scan_id = $this->scanner->start_scan($extra_keys);

            wp_send_json_success(array(
                'scan_id' => $scan_id,
                'total'   => $this->scanner->get_total_images(),
                'batch'   => $this->scanner->get_batch_size(),
            ));
        } catch (Exception $e) {
            $this->log_error($e->getMessage());
            wp_send_json_error('Error al iniciar escaneo: ' . $e->getMessage());
        }
    }
    
    private function log_error($message) {
        $errors = get_option('moc_scan_errors', array());
        $errors[] = array(
            'time' => current_time('mysql'),
            'message' => $message
        );
        
        // Mantener solo los últimos 10 errores
        if (count($errors) > 10) {
            $errors = array_slice($errors, -10);
        }
        
        update_option('moc_scan_errors', $errors, false);
    }
    
    public function handle_clear_errors() {
        if (!current_user_can('manage_options')) {
            wp_die('No autorizado');
        }
        check_admin_referer('moc_clear_errors');
        
        delete_option('moc_scan_errors');
        wp_redirect(admin_url('admin.php?page=moc-logs'));
        exit;
    }
    
    public function handle_clear_logs() {
        if (!current_user_can('manage_options')) {
            wp_die('No autorizado');
        }
        check_admin_referer('moc_clear_logs');
        
        delete_option($this->logs_option);
        delete_option('moc_last_log_cleanup');
        
        wp_redirect(add_query_arg('logs_cleared', '1', admin_url('admin.php?page=moc-logs')));
        exit;
    }

    public function ajax_scan_batch() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('No autorizado');
        }
        check_ajax_referer('moc_ajax_nonce', 'nonce');

        $scan_id = isset($_POST['scan_id']) ? sanitize_text_field($_POST['scan_id']) : '';
        if ($scan_id === '') {
            wp_send_json_error('scan_id inválido');
        }

        $result = $this->scanner->scan_next_batch($scan_id);

        if (isset($result['done']) && $result['done']) {
            update_option($this->orphans_option, $result['orphans'], false);
            if (!empty($result['logs'])) {
                update_option($this->logs_option, $result['logs'], false);
            }
        }

        wp_send_json_success($result);
    }

    public function handle_delete() {
        if (!current_user_can('manage_options')) {
            wp_die('No autorizado');
        }
        check_admin_referer('moc_delete_nonce');

        $settings = get_option($this->option_key, array());
        $dry_run = !empty($settings['dry_run']);
        
        if ($dry_run) {
            wp_redirect(add_query_arg('moc_error', 'dry_run', admin_url('tools.php?page=media-orphan-cleaner')));
            exit;
        }

        $delete_ids = isset($_POST['delete_ids']) ? array_map('intval', (array)$_POST['delete_ids']) : array();
        
        $enable_backup = !empty($settings['enable_backup']);
        if ($enable_backup && !empty($delete_ids)) {
            $backup_data = array(
                'ids' => $delete_ids,
                'date' => current_time('mysql'),
                'metadata' => array(),
            );
            
            foreach ($delete_ids as $att_id) {
                $backup_data['metadata'][$att_id] = array(
                    'url' => wp_get_attachment_url($att_id),
                    'file' => get_attached_file($att_id),
                    'metadata' => wp_get_attachment_metadata($att_id),
                    'post' => get_post($att_id),
                );
            }
            
            update_option($this->backup_option, $backup_data, false);
        }
        
        foreach ($delete_ids as $att_id) {
            if ($att_id > 0) {
                wp_delete_attachment($att_id, true);
            }
        }

        update_option($this->orphans_option, array(), false);
        wp_redirect(add_query_arg('moc_deleted', count($delete_ids), admin_url('tools.php?page=media-orphan-cleaner')));
        exit;
    }
    
    public function handle_export_csv() {
        if (!current_user_can('manage_options')) {
            wp_die('No autorizado');
        }
        check_admin_referer('moc_export_nonce');
        
        $orphans = get_option($this->orphans_option, array());
        
        if (empty($orphans)) {
            wp_redirect(admin_url('tools.php?page=media-orphan-cleaner'));
            exit;
        }
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=media-orphans-' . date('Y-m-d-His') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, array('ID', 'Archivo', 'URL', 'Tamaño (KB)', 'Fecha'));
        
        foreach ($orphans as $att_id) {
            $att_id = (int)$att_id;
            $url = wp_get_attachment_url($att_id);
            $file = get_attached_file($att_id);
            $size = file_exists($file) ? round(filesize($file) / 1024, 2) : 0;
            $post = get_post($att_id);
            $date = $post ? $post->post_date : '';
            
            fputcsv($output, array(
                $att_id,
                basename($file),
                $url,
                $size,
                $date
            ));
        }
        
        fclose($output);
        exit;
    }
    
    public function handle_restore_backup() {
        if (!current_user_can('manage_options')) {
            wp_die('No autorizado');
        }
        check_admin_referer('moc_restore_nonce');
        
        $backup = get_option($this->backup_option, array());
        
        if (empty($backup) || empty($backup['metadata'])) {
            wp_redirect(admin_url('tools.php?page=media-orphan-cleaner'));
            exit;
        }
        
        $restored = 0;
        foreach ($backup['metadata'] as $att_id => $data) {
            if (!empty($data['post'])) {
                $post_data = (array)$data['post'];
                unset($post_data['ID']);
                $new_id = wp_insert_post($post_data);
                
                if ($new_id && !is_wp_error($new_id)) {
                    if (!empty($data['metadata'])) {
                        wp_update_attachment_metadata($new_id, $data['metadata']);
                    }
                    $restored++;
                }
            }
        }
        
        delete_option($this->backup_option);
        
        wp_redirect(add_query_arg('moc_restored', $restored, admin_url('tools.php?page=media-orphan-cleaner')));
        exit;
    }
}
