<?php
/*
Plugin Name: Email Referral
Description: A referral system where users invite others via email to unlock product discounts.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit;
}

// === Constants ===
define('ER_PATH', plugin_dir_path(__FILE__));
define('ER_URL', plugin_dir_url(__FILE__));

// === Includes ===
require_once ER_PATH . 'includes/class-er-db.php';
require_once ER_PATH . 'includes/class-er-ajax.php';
require_once ER_PATH . 'includes/class-er-email.php';
require_once ER_PATH . 'includes/class-er-public.php';
require_once ER_PATH . 'includes/class-er-admin.php';
require_once ER_PATH . 'includes/class-er-api.php';
require_once ER_PATH . 'includes/class-er-shortcodes.php';
require_once ER_PATH . 'includes/class-er-woocommerce.php';

// === Init AJAX Handler (IMPORTANT) ===
new ER_Ajax();

// === Activation Hook ===
register_activation_hook(__FILE__, ['ER_DB', 'activate']);

// === Init Plugin Classes ===
add_action('init', function () {
    new ER_Shortcodes();
});

// === Admin Only: Load ER_Admin Class ===
if (is_admin()) {
    new ER_Admin();
}

// === Enqueue Frontend Assets ===
function er_enqueue_assets() {
    wp_enqueue_style('er-public', ER_URL . 'assets/public.css');

    if (is_page('customer-dashboard')) {
        wp_enqueue_script('er-dashboard', ER_URL . 'assets/js/dashboard.js', ['jquery'], '1.0', true);
        wp_localize_script('er-dashboard', 'erData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('er_referral_nonce'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'er_enqueue_assets');