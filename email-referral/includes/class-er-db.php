<?php

if (!defined('ABSPATH')) exit;

class ER_DB {

    public static function activate() {
        self::create_tables();
    }

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $referrals_table = $wpdb->prefix . 'er_referrals';
        $products_table = $wpdb->prefix . 'er_products';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql1 = "CREATE TABLE $referrals_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            slot TINYINT UNSIGNED NOT NULL,
            email VARCHAR(100) NOT NULL,
            referral_token VARCHAR(64),
            verified BOOLEAN DEFAULT FALSE,
            registered_user_id BIGINT UNSIGNED DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $sql2 = "CREATE TABLE $products_table (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wc_product_id BIGINT UNSIGNED DEFAULT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            final_price DECIMAL(10,2) NOT NULL,
            discount DECIMAL(10,2) DEFAULT 0.00,
            image_id BIGINT UNSIGNED,
            product_code VARCHAR(100) DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql1);
        dbDelta($sql2);
    }

    public static function save_referral($user_id, $product_id, $slot, $email) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'er_referrals';
        $token = wp_generate_password(20, false);

        $result = $wpdb->insert(
            $table_name,
            [
                'user_id'         => $user_id,
                'product_id'      => $product_id,
                'slot'            => $slot,
                'email'           => $email,
                'referral_token'  => $token,
                'verified'        => false,
                'created_at'      => current_time('mysql'),
            ]
        );

        return $result !== false;
    }

    public static function get_user_referral_slots_grouped($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'er_referrals';

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table_name WHERE user_id = %d ORDER BY product_id, slot",
                $user_id
            )
        );

        $grouped = [];

        foreach ($results as $row) {
            if (!isset($grouped[$row->product_id])) {
                $grouped[$row->product_id] = [];
            }
            $grouped[$row->product_id][$row->slot] = $row;
        }

        return $grouped;
    }

    public static function get_all_referrals() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'er_referrals';
        return $wpdb->get_results("SELECT * FROM $table_name");
    }

    public static function get_product_name($product_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'er_products';
        return $wpdb->get_var(
            $wpdb->prepare("SELECT name FROM $table_name WHERE id = %d", $product_id)
        );
    }

    public static function get_all_products() {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        $query = new WP_Query($args);
        $products = [];

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $product = wc_get_product(get_the_ID());

                if ($product && is_a($product, 'WC_Product')) {
                    $products[] = [
                        'ID'    => $product->get_id(),
                        'title' => $product->get_name(),
                        'price' => $product->get_price(),
                        'image' => get_the_post_thumbnail_url($product->get_id(), 'thumbnail')
                    ];
                }
            }
            wp_reset_postdata();
        }

        return $products;
    }

    public static function get_all_plugin_products() {
        global $wpdb;
        $table = $wpdb->prefix . 'er_products';
        return $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
    }

    public static function get_wc_product_id_by_plugin_id($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'er_products';
        return $wpdb->get_var($wpdb->prepare("SELECT wc_product_id FROM $table WHERE id = %d", $id));
    }

    // NEW: Fix for admin error - get all users with referrals
    public static function get_all_users_with_referrals() {
        global $wpdb;
        $referrals_table = $wpdb->prefix . 'er_referrals';
        $users_table = $wpdb->prefix . 'users';

        // Get all users who have at least one referral
        $results = $wpdb->get_results(
            "SELECT u.ID as user_id, u.user_email as email, 
                    COUNT(r.id) as referrals_sent, 
                    SUM(r.verified) as referrals_completed
             FROM $users_table u
             LEFT JOIN $referrals_table r ON r.user_id = u.ID
             GROUP BY u.ID
             ORDER BY referrals_sent DESC"
        );

        return $results;
    }
}