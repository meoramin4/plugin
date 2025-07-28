<?php
class ER_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function add_admin_menu() {
        // Main menu item under WooCommerce > Products
        add_menu_page(
            __('Email Referral', 'email-referral'), 
            __('Referral', 'email-referral'),
            'manage_woocommerce', // Give access to shop managers too
            'er_referral', 
            [$this, 'referral_page'], 
            'dashicons-share', 
            56 // Position it right below Products
        );

        // Submenu items
        add_submenu_page(
            'er_referral', 
            __('Referral', 'email-referral'), 
            __('Referral', 'email-referral'), 
            'manage_woocommerce', 
            'er_referral', 
            [$this, 'referral_page']
        );
        add_submenu_page(
            'er_referral', 
            __('Products', 'email-referral'), 
            __('Products', 'email-referral'), 
            'manage_woocommerce', 
            'er_products', 
            [$this, 'products_page']
        );
        add_submenu_page(
            'er_referral', 
            __('Users', 'email-referral'), 
            __('Users', 'email-referral'), 
            'manage_woocommerce', 
            'er_users', 
            [$this, 'users_page']
        );
        add_submenu_page(
            'er_referral', 
            __('Statistics', 'email-referral'), 
            __('Statistics', 'email-referral'), 
            'manage_woocommerce', 
            'er_statistics', 
            [$this, 'statistics_page']
        );
        add_submenu_page(
            'er_referral', 
            __('Settings', 'email-referral'), 
            __('Settings', 'email-referral'), 
            'manage_woocommerce', 
            'er_settings', 
            [$this, 'settings_page']
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'er_') !== false) {
            wp_enqueue_style('er-admin-css', ER_URL . 'assets/admin.css', [], null);
            wp_enqueue_script('er-admin-js', ER_URL . 'assets/js/admin.js', ['jquery'], null, true);
            wp_localize_script('er-admin-js', 'er_admin', [
                'nonce' => wp_create_nonce('er_admin_nonce'),
            ]);
        }
    }

    public function referral_page() {
        require ER_PATH . 'templates/admin/referral.php';
    }

    public function products_page() {
        require ER_PATH . 'templates/admin/products.php';
    }

    public function users_page() {
        require ER_PATH . 'templates/admin/users.php';
    }

    public function statistics_page() {
        require ER_PATH . 'templates/admin/statistics.php';
    }

    public function settings_page() {
        require ER_PATH . 'templates/admin/settings.php';
    }
}
