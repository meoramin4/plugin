<?php
class ER_Public {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
        add_action('init', [$this, 'add_shortcodes']);
        add_action('init', [$this, 'add_registration_endpoint']);
        add_action('wp_ajax_er_submit_referral', [$this, 'ajax_submit_referral']);
        add_action('wp_ajax_nopriv_er_submit_referral', [$this, 'ajax_submit_referral']);
    }

    public function enqueue_public_assets() {
        wp_enqueue_style('er-public-css', ER_URL . 'assets/public.css', [], null);
    }

    public function add_shortcodes() {
        add_shortcode('email_referral_dashboard', [$this, 'dashboard_shortcode']);
    }

    public function dashboard_shortcode() {
        ob_start();
        require ER_PATH . 'templates/dashboard.php';
        return ob_get_clean();
    }

    public function add_registration_endpoint() {
        add_rewrite_rule('^referral-register/?', 'index.php?er_register=1', 'top');
        add_rewrite_tag('%er_register%', '1');
        add_action('template_redirect', function () {
            if (get_query_var('er_register')) {
                include ER_PATH . 'templates/registration-form.php';
                exit;
            }
        });
    }

    public function ajax_submit_referral() {
        wp_send_json_success(['message' => __('Stub: Referral AJAX called', 'email-referral')]);
    }
}