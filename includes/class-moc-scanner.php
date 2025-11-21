<?php
if (!defined('ABSPATH')) {
    exit;
}

class MOC_Scanner {
    private $batch_size = 200;
    private $total_images = 0;

    public function get_batch_size() {
        return $this->batch_size;
    }

    public function get_total_images() {
        return $this->total_images;
    }

    public function start_scan($extra_meta_keys = array()) {
        $scan_id = wp_generate_uuid4();

        $used_ids = $this->compute_used_image_ids($extra_meta_keys);
        $used_map = array();
        foreach ($used_ids as $id) {
            $used_map[(int)$id] = true;
        }

        $this->total_images = $this->count_all_images();

        set_transient("moc_used_$scan_id", $used_map, HOUR_IN_SECONDS);
        set_transient("moc_offset_$scan_id", 0, HOUR_IN_SECONDS);
        set_transient("moc_orphans_$scan_id", array(), HOUR_IN_SECONDS);

        return $scan_id;
    }

    public function scan_next_batch($scan_id) {
        $used_map = get_transient("moc_used_$scan_id");
        $offset   = (int)get_transient("moc_offset_$scan_id");
        $orphans  = get_transient("moc_orphans_$scan_id");

        if (!is_array($used_map) || !is_array($orphans)) {
            return array(
                'done' => true,
                'orphans' => array(),
                'offset' => 0,
                'total' => 0,
            );
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

        $done = empty($ids);

        if ($done) {
            delete_transient("moc_used_$scan_id");
            delete_transient("moc_offset_$scan_id");
            delete_transient("moc_orphans_$scan_id");
        }

        return array(
            'done'    => $done,
            'orphans' => $orphans,
            'offset'  => $offset,
            'total'   => $this->total_images,
        );
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

        $content_rows = $wpdb->get_col(
            "SELECT post_content FROM {$wpdb->posts}
             WHERE post_status IN ('publish','private','draft')
               AND post_content REGEXP 'wp-image-[0-9]+|\"media(Id|_id)\"\\s*:\\s*[0-9]+'"
        );
        foreach ($content_rows as $content) {
            $used = array_merge($used, $this->extract_attachment_ids_from_value($content));
        }

        $used = array_merge($used, $this->extract_ids_from_site_options());

        $used = array_unique(array_filter(array_map('intval', $used)));
        return $used;
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
