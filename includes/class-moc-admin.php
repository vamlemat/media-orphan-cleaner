<?php
if (!defined('ABSPATH')) {
    exit;
}

class MOC_Admin {
    private $scanner;
    private $option_key = 'moc_settings';
    private $orphans_option = 'moc_last_orphans';

    public function __construct($scanner) {
        $this->scanner = $scanner;

        add_action('admin_menu', array($this, 'add_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        add_action('wp_ajax_moc_start_scan', array($this, 'ajax_start_scan'));
        add_action('wp_ajax_moc_scan_batch', array($this, 'ajax_scan_batch'));

        add_action('admin_post_moc_delete', array($this, 'handle_delete'));
    }

    public function add_menu() {
        add_management_page(
            'Media Orphan Cleaner',
            'Media Orphan Cleaner',
            'manage_options',
            'media-orphan-cleaner',
            array($this, 'render_page')
        );
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

    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $orphans = get_option($this->orphans_option, array());
        ?>
        <div class="wrap moc-wrap">
            <h1>Media Orphan Cleaner</h1>

            <form method="post" action="options.php" class="moc-settings">
                <?php
                settings_fields('moc_settings_group');
                do_settings_sections('media-orphan-cleaner');
                submit_button('Guardar ajustes');
                ?>
            </form>

            <hr>

            <h2>Escaneo</h2>
            <p>
                Este escaneo detecta imágenes no usadas en:
                WooCommerce (destacadas/galerías/categorías), Elementor (incl. templates),
                JetEngine (meta keys configuradas), JetFormBuilder/Gutenberg (bloques),
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

            <?php if (!empty($orphans)): ?>
                <hr>
                <h2>Posibles huérfanas (<?php echo count($orphans); ?>)</h2>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('moc_delete_nonce'); ?>
                    <input type="hidden" name="action" value="moc_delete">

                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th style="width:40px;"></th>
                                <th>ID</th>
                                <th>Archivo</th>
                                <th>Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orphans as $att_id): ?>
                            <?php
                            $att_id = (int)$att_id;
                            $url = wp_get_attachment_url($att_id);
                            ?>
                            <tr>
                                <td><input type="checkbox" name="delete_ids[]" value="<?php echo esc_attr($att_id); ?>"></td>
                                <td><?php echo esc_html($att_id); ?></td>
                                <td><?php echo esc_html($url); ?></td>
                                <td><?php echo wp_get_attachment_image($att_id, array(80, 80)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>

                    <p>
                        <button class="button button-danger"
                                onclick="return confirm('¿Seguro? Esto borra archivos físicos y tamaños.');">
                            Borrar seleccionadas
                        </button>
                    </p>
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

        $settings = get_option($this->option_key, array());
        $extra_keys_raw = isset($settings['jetengine_meta_keys']) ? $settings['jetengine_meta_keys'] : '';
        $extra_keys = array_filter(array_map('sanitize_key', preg_split('/\r\n|\r|\n/', (string)$extra_keys_raw)));

        $scan_id = $this->scanner->start_scan($extra_keys);

        wp_send_json_success(array(
            'scan_id' => $scan_id,
            'total'   => $this->scanner->get_total_images(),
            'batch'   => $this->scanner->get_batch_size(),
        ));
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
        }

        wp_send_json_success($result);
    }

    public function handle_delete() {
        if (!current_user_can('manage_options')) {
            wp_die('No autorizado');
        }
        check_admin_referer('moc_delete_nonce');

        $delete_ids = isset($_POST['delete_ids']) ? array_map('intval', (array)$_POST['delete_ids']) : array();
        foreach ($delete_ids as $att_id) {
            if ($att_id > 0) {
                wp_delete_attachment($att_id, true);
            }
        }

        update_option($this->orphans_option, array(), false);
        wp_redirect(admin_url('tools.php?page=media-orphan-cleaner'));
        exit;
    }
}
