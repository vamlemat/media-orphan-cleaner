<?php
if (!defined('ABSPATH')) {
    exit;
}

class MOC_Scanner {
    private $batch_size = 200;
    private $total_images = 0;
    private $logs = array();
    private $enable_logging = true;
    private $content_batch_size = 500;

    public function get_batch_size() {
        return $this->batch_size;
    }

    public function get_total_images() {
        return $this->total_images;
    }

    public function get_logs() {
        return $this->logs;
    }

    private function log($message, $data = null) {
        if (!$this->enable_logging) {
            return;
        }
        $entry = array(
            'time' => current_time('mysql'),
            'message' => $message,
        );
        if ($data !== null) {
            $entry['data'] = $data;
        }
        $this->logs[] = $entry;
    }

    public function cleanup_old_transients() {
        global $wpdb;
        $pattern = $wpdb->esc_like('_transient_moc_') . '%';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            )
        );
        $this->log('Transients antiguos limpiados');
    }

    public function start_scan($extra_meta_keys = array()) {
        $this->cleanup_old_transients();
        
        $scan_id = wp_generate_uuid4();
        $this->log('Iniciando escaneo', array('scan_id' => $scan_id));

        $used_ids = $this->compute_used_image_ids($extra_meta_keys);
        $this->log('IDs en uso calculados', array('total' => count($used_ids)));
        
        // Detectar attachments con post_parent inválido
        $invalid_parent_ids = $this->get_attachments_with_invalid_parent();
        $this->log('Attachments con parent inválido detectados', array('total' => count($invalid_parent_ids)));
        
        $used_map = array();
        foreach ($used_ids as $id) {
            $used_map[(int)$id] = true;
        }

        $this->total_images = $this->count_all_images();
        $this->log('Total de imágenes en biblioteca', array('total' => $this->total_images));

        set_transient("moc_used_$scan_id", $used_map, HOUR_IN_SECONDS);
        set_transient("moc_invalid_parent_$scan_id", $invalid_parent_ids, HOUR_IN_SECONDS);
        set_transient("moc_offset_$scan_id", 0, HOUR_IN_SECONDS);
        set_transient("moc_orphans_$scan_id", array(), HOUR_IN_SECONDS);
        set_transient("moc_logs_$scan_id", $this->logs, HOUR_IN_SECONDS);

        return $scan_id;
    }

    public function scan_next_batch($scan_id) {
        $used_map = get_transient("moc_used_$scan_id");
        $invalid_parent_ids = get_transient("moc_invalid_parent_$scan_id");
        $offset   = (int)get_transient("moc_offset_$scan_id");
        $orphans  = get_transient("moc_orphans_$scan_id");
        $this->logs = get_transient("moc_logs_$scan_id");

        if (!is_array($used_map) || !is_array($orphans)) {
            return array(
                'done' => true,
                'orphans' => array(),
                'invalid_parent_ids' => array(),
                'offset' => 0,
                'total' => 0,
                'total_size' => 0,
                'logs' => $this->logs,
            );
        }

        if (!is_array($invalid_parent_ids)) {
            $invalid_parent_ids = array();
        }

        $query = new WP_Query(array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'fields'         => 'ids',
            'posts_per_page' => $this->batch_size,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ));

        $ids = $query->posts;

        foreach ($ids as $att_id) {
            $att_id = (int)$att_id;
            if (!isset($used_map[$att_id])) {
                $orphans[] = $att_id;
            }
        }

        $offset += count($ids);

        set_transient("moc_offset_$scan_id", $offset, HOUR_IN_SECONDS);
        set_transient("moc_orphans_$scan_id", $orphans, HOUR_IN_SECONDS);
        set_transient("moc_logs_$scan_id", $this->logs, HOUR_IN_SECONDS);

        $done = empty($ids);

        $total_size = 0;
        if ($done) {
            $total_size = $this->calculate_total_size($orphans);
            
            // Separar orphans con parent inválido
            $orphans_invalid_parent = array_intersect($orphans, $invalid_parent_ids);
            
            $this->log('Escaneo completado', array(
                'huérfanas' => count($orphans),
                'con_parent_inválido' => count($orphans_invalid_parent),
                'tamaño_mb' => round($total_size / 1024 / 1024, 2)
            ));
            
            set_transient("moc_logs_$scan_id", $this->logs, HOUR_IN_SECONDS);
            
            delete_transient("moc_used_$scan_id");
            delete_transient("moc_invalid_parent_$scan_id");
            delete_transient("moc_offset_$scan_id");
            delete_transient("moc_orphans_$scan_id");
        }

        return array(
            'done'       => $done,
            'orphans'    => $orphans,
            'invalid_parent_ids' => $invalid_parent_ids,
            'offset'     => $offset,
            'total'      => $this->total_images,
            'total_size' => $total_size,
            'logs'       => $this->logs,
        );
    }

    public function calculate_total_size($attachment_ids) {
        $total_bytes = 0;
        
        foreach ($attachment_ids as $att_id) {
            $file_path = get_attached_file($att_id);
            if ($file_path && file_exists($file_path)) {
                $total_bytes += filesize($file_path);
                
                $metadata = wp_get_attachment_metadata($att_id);
                if (isset($metadata['sizes']) && is_array($metadata['sizes'])) {
                    $upload_dir = wp_upload_dir();
                    $base_dir = dirname($file_path);
                    
                    foreach ($metadata['sizes'] as $size) {
                        if (isset($size['file'])) {
                            $size_file = $base_dir . '/' . $size['file'];
                            if (file_exists($size_file)) {
                                $total_bytes += filesize($size_file);
                            }
                        }
                    }
                }
            }
        }
        
        return $total_bytes;
    }

    private function count_all_images() {
        $q = new WP_Query(array(
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => 'image',
            'fields'         => 'ids',
            'posts_per_page' => 1,
        ));
        return (int)$q->found_posts;
    }

    /**
     * Detecta attachments que tienen post_parent apuntando a posts que ya no existen
     * Caso: Productos/posts eliminados directamente de BD sin desvincular imágenes
     * 
     * @return array Array de IDs de attachments con parent inválido
     */
    private function get_attachments_with_invalid_parent() {
        global $wpdb;
        
        // Buscar attachments con post_parent > 0 cuyo parent no existe en wp_posts
        $invalid_parent_ids = $wpdb->get_col(
            "SELECT a.ID 
            FROM {$wpdb->posts} a
            LEFT JOIN {$wpdb->posts} p ON a.post_parent = p.ID
            WHERE a.post_type = 'attachment'
            AND a.post_mime_type LIKE 'image%'
            AND a.post_parent > 0
            AND p.ID IS NULL"
        );
        
        return array_map('intval', $invalid_parent_ids);
    }

    private function compute_used_image_ids($extra_meta_keys) {
        global $wpdb;

        $used = array();

        $meta_keys = array(
            '_thumbnail_id',
            '_product_image_gallery',
            '_elementor_data',
        );

        if (is_array($extra_meta_keys)) {
            foreach ($extra_meta_keys as $k) {
                $k = sanitize_key($k);
                if ($k !== '') {
                    $meta_keys[] = $k;
                }
            }
        }
        $meta_keys = array_unique($meta_keys);

        $placeholders = implode(',', array_fill(0, count($meta_keys), '%s'));
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ($placeholders)",
                $meta_keys
            )
        );

        foreach ($rows as $row) {
            $used = array_merge($used, $this->extract_attachment_ids_from_value($row->meta_value));
        }

        $term_ids = $wpdb->get_col(
            "SELECT DISTINCT meta_value FROM {$wpdb->termmeta} WHERE meta_key = 'thumbnail_id'"
        );
        foreach ($term_ids as $tv) {
            $used[] = (int)$tv;
        }

        $used = array_merge($used, $this->extract_ids_from_post_content());

        $used = array_merge($used, $this->extract_ids_from_site_options());
        $used = array_merge($used, $this->extract_ids_from_widgets());
        $used = array_merge($used, $this->extract_ids_from_customizer());
        $used = array_merge($used, $this->extract_ids_from_acf());

        $used = array_unique(array_filter(array_map('intval', $used)));
        return $used;
    }

    private function extract_ids_from_post_content() {
        global $wpdb;
        $ids = array();
        
        $offset = 0;
        $batch = $this->content_batch_size;
        
        $this->log('Iniciando extracción de post_content paginado');
        
        while (true) {
            $content_rows = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT post_content FROM {$wpdb->posts}
                     WHERE post_status IN ('publish','private','draft')
                       AND post_content REGEXP 'wp-image-[0-9]+|\"media(Id|_id)\"\\s*:\\s*[0-9]+'
                     LIMIT %d OFFSET %d",
                    $batch,
                    $offset
                )
            );
            
            if (empty($content_rows)) {
                break;
            }
            
            foreach ($content_rows as $content) {
                $ids = array_merge($ids, $this->extract_attachment_ids_from_value($content));
            }
            
            $offset += $batch;
            $this->log("Batch de contenido procesado", array('offset' => $offset, 'encontrados' => count($content_rows)));
        }
        
        $this->log('Extracción de post_content completada', array('total_ids' => count($ids)));
        return $ids;
    }

    private function extract_ids_from_widgets() {
        $ids = array();
        $widgets = get_option('widget_media_image', array());
        
        foreach ($widgets as $widget) {
            if (is_array($widget) && isset($widget['attachment_id'])) {
                $ids[] = (int)$widget['attachment_id'];
            }
        }
        
        $sidebars = wp_get_sidebars_widgets();
        foreach ($sidebars as $sidebar => $widget_ids) {
            if (is_array($widget_ids)) {
                foreach ($widget_ids as $widget_id) {
                    $widget_data = get_option('widget_' . $widget_id);
                    if ($widget_data) {
                        $ids = array_merge($ids, $this->extract_attachment_ids_from_value($widget_data));
                    }
                }
            }
        }
        
        $this->log('IDs de Widgets extraídos', array('total' => count($ids)));
        return $ids;
    }

    private function extract_ids_from_customizer() {
        $ids = array();
        
        $customizer_data = get_option('theme_mods_' . get_option('stylesheet'));
        if (is_array($customizer_data)) {
            $ids = array_merge($ids, $this->extract_attachment_ids_from_value($customizer_data));
        }
        
        $all_options = wp_load_alloptions();
        foreach ($all_options as $key => $value) {
            if (strpos($key, '_theme_mods_') !== false) {
                $ids = array_merge($ids, $this->extract_attachment_ids_from_value($value));
            }
        }
        
        $this->log('IDs de Customizer extraídos', array('total' => count($ids)));
        return $ids;
    }

    private function extract_ids_from_acf() {
        global $wpdb;
        $ids = array();
        
        $acf_rows = $wpdb->get_results(
            "SELECT meta_value FROM {$wpdb->postmeta}
             WHERE meta_key LIKE '%_field_%' OR meta_key LIKE 'acf_%'"
        );
        
        foreach ($acf_rows as $row) {
            $ids = array_merge($ids, $this->extract_attachment_ids_from_value($row->meta_value));
        }
        
        $this->log('IDs de ACF extraídos', array('total' => count($ids)));
        return $ids;
    }

    private function extract_ids_from_site_options() {
        $ids = array();

        $site_icon = get_option('site_icon');
        if (is_numeric($site_icon)) {
            $ids[] = (int)$site_icon;
        }

        $custom_logo = get_theme_mod('custom_logo');
        if (is_numeric($custom_logo)) {
            $ids[] = (int)$custom_logo;
        }

        $theme_mods = get_option('theme_mods_' . get_option('stylesheet'));
        if (!empty($theme_mods)) {
            $ids = array_merge($ids, $this->extract_attachment_ids_from_value($theme_mods));
        }

        return $ids;
    }

    private function extract_attachment_ids_from_value($raw_value) {
        $ids = array();

        if (empty($raw_value) && $raw_value !== '0') {
            return $ids;
        }

        $value = maybe_unserialize($raw_value);

        if (is_numeric($value)) {
            $ids[] = (int)$value;
            return $ids;
        }

        if (is_string($value)) {
            $trim = trim($value);

            if (preg_match('/^\d+(,\d+)*$/', $trim)) {
                $parts = explode(',', $trim);
                foreach ($parts as $p) {
                    $ids[] = (int)$p;
                }
                return $ids;
            }

            $json = json_decode($trim, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return array_merge($ids, $this->extract_ids_recursive($json));
            }

            preg_match_all('/wp-image-(\d+)/', $trim, $m1);
            if (!empty($m1[1])) {
                foreach ($m1[1] as $m) {
                    $ids[] = (int)$m;
                }
            }

            preg_match_all('/"id"\s*:\s*(\d+)/', $trim, $m2);
            if (!empty($m2[1])) {
                foreach ($m2[1] as $m) {
                    $ids[] = (int)$m;
                }
            }

            preg_match_all('/"media(Id|_id)"\s*:\s*(\d+)/', $trim, $m3);
            if (!empty($m3[2])) {
                foreach ($m3[2] as $m) {
                    $ids[] = (int)$m;
                }
            }

            return $ids;
        }

        if (is_array($value) || is_object($value)) {
            return array_merge($ids, $this->extract_ids_recursive($value));
        }

        return $ids;
    }

    private function extract_ids_recursive($data) {
        $ids = array();

        if (is_array($data)) {
            foreach ($data as $key => $val) {
                if ($key === 'id' && is_numeric($val)) {
                    $ids[] = (int)$val;
                }
                if (is_array($val) || is_object($val)) {
                    $ids = array_merge($ids, $this->extract_ids_recursive($val));
                }
            }
        } elseif (is_object($data)) {
            foreach (get_object_vars($data) as $key => $val) {
                if ($key === 'id' && is_numeric($val)) {
                    $ids[] = (int)$val;
                }
                if (is_array($val) || is_object($val)) {
                    $ids = array_merge($ids, $this->extract_ids_recursive($val));
                }
            }
        }

        return $ids;
    }
}
