<?php
class ER_Shortcodes {
    public function __construct() {
        add_shortcode('email_referral_dashboard', [ $this, 'dashboard' ]);
        add_shortcode('er_registration_form', [ $this, 'registration_form' ]);
    }

    public function dashboard() {
        ob_start();
        require ER_PATH . 'templates/dashboard.php';
        return ob_get_clean();
    }

    public function registration_form() {
        ob_start();
        require ER_PATH . 'templates/registration-form.php';
        return ob_get_clean();
    }
}
