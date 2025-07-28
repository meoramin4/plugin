<?php
class ER_API {
    public function __construct() {
        add_action('wp_ajax_er_delete_product', [$this, 'handle_delete_product']);
        add_action('wp_ajax_er_submit_referral_email', [$this, 'handle_submit_referral_email']);
        add_action('wp_ajax_nopriv_er_submit_referral_email', [$this, 'handle_submit_referral_email']);
    }

    public function handle_delete_product() {
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'er_admin_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
        }
        if (!current_user_can('delete_posts')) {
            wp_send_json_error(['message' => 'No permission.']);
        }

        $plugin_product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if (!$plugin_product_id) {
            wp_send_json_error(['message' => 'No product ID provided.']);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'er_products';
        $wc_product_id = $wpdb->get_var($wpdb->prepare("SELECT wc_product_id FROM $table WHERE id = %d", $plugin_product_id));
        $success = true;

        if ($wc_product_id) {
            $deleted = wp_delete_post($wc_product_id, true);
            if (!$deleted) $success = false;
        }

        $wpdb->delete($table, ['id' => $plugin_product_id]);

        if ($success) {
            wp_send_json_success(['message' => 'Product deleted.']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete product.']);
        }
    }

    public function handle_submit_referral_email() {
        if (empty($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'er_referral_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce.']);
        }

        $email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $slot       = isset($_POST['slot']) ? intval($_POST['slot']) : 0;

        if (!$email || !is_email($email) || !$slot) {
            wp_send_json_error(['message' => 'Please provide a valid email and slot.']);
        }

        $referrer = wp_get_current_user();
        $token = wp_generate_password(20, false, false);

        global $wpdb;
        $table = $wpdb->prefix . 'er_referrals';

        // Only allow one referral per slot per user/product
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE referrer_id = %d AND product_id = %d AND slot = %d",
            $referrer->ID, $product_id, $slot
        ));
        if ($exists) {
            wp_send_json_error(['message' => 'You have already sent a referral for this slot.']);
        }

        $wpdb->insert($table, [
            'referrer_id' => $referrer->ID,
            'product_id' => $product_id,
            'slot' => $slot,
            'referral_email' => $email,
            'referral_token' => $token,
            'created_at' => current_time('mysql'),
            'status' => 'pending',
        ]);

        // Build registration link
        $registration_url = add_query_arg([
            'token' => $token
        ], home_url('/referral-register/'));

        // Send email
        $sender_email = get_option('er_sender_email');
        if (!$sender_email || !is_email($sender_email)) {
            $sender_email = get_bloginfo('admin_email');
        }

        $subject = __('You have been invited!', 'email-referral');
        // Custom message as you requested:
        $message = sprintf(
            "Hi,<br><br>
            I wanted to personally invite you to join NZmotorsports! It’s a platform I’ve been enjoying, and I think you’ll love it too.<br><br>
            Just click the link below to get started:<br>
            ?? <a href=\"%s\">%s</a>",
            esc_url($registration_url),
            esc_html($registration_url)
        );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . esc_attr(get_bloginfo('name')) . ' <' . esc_attr($sender_email) . '>',
        ];

        $sent = wp_mail($email, $subject, $message, $headers);

        if ($sent) {
            wp_send_json_success(['message' => 'Referral email sent!']);
        } else {
            wp_send_json_error(['message' => 'Email could not be sent. Please check mail settings.']);
        }
    }
}