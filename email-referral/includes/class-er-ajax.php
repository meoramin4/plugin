<?php

if (!defined('ABSPATH')) exit;

class ER_Ajax {

    public function __construct() {
        add_action('wp_ajax_er_save_referral', [$this, 'save_referral']);
        add_action('wp_ajax_nopriv_er_save_referral', [$this, 'save_referral']);

        add_action('wp_ajax_er_submit_referral_email', [$this, 'submit_referral_email']);
        add_action('wp_ajax_nopriv_er_submit_referral_email', [$this, 'submit_referral_email']);

        // NEW: Change referral email AJAX
        add_action('wp_ajax_er_change_referral_email', [$this, 'change_referral_email']);
        add_action('wp_ajax_nopriv_er_change_referral_email', [$this, 'change_referral_email']);
    }

    public function save_referral() {
        check_ajax_referer('er_nonce', 'nonce');

        $user_id    = get_current_user_id();
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $slot       = isset($_POST['slot']) ? intval($_POST['slot']) : 0;
        $email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (!$user_id || !$product_id || !$slot || !is_email($email)) {
            wp_send_json_error(['message' => __('Invalid data.', 'email-referral')]);
        }

        // Duplicate check (block all duplicates, whether verified or pending)
        global $wpdb;
        $referral_table = $wpdb->prefix . 'er_referrals';
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $referral_table WHERE user_id = %d AND product_id = %d AND email = %s",
            $user_id, $product_id, $email
        ));
        if ($existing > 0) {
            wp_send_json_error(['message' => __('This email has already been invited for this product.', 'email-referral')]);
        }

        $saved = ER_DB::save_referral($user_id, $product_id, $slot, $email);

        if ($saved) {
            wp_send_json_success(['message' => __('Referral saved successfully.', 'email-referral')]);
        } else {
            wp_send_json_error(['message' => __('Failed to save referral.', 'email-referral')]);
        }
    }

    public function submit_referral_email() {
        check_ajax_referer('er_referral_nonce', 'nonce');

        $user_id    = get_current_user_id();
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $slot       = isset($_POST['slot']) ? intval($_POST['slot']) : 0;
        $email      = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

        if (!$user_id || !$product_id || !is_email($email)) {
            wp_send_json_error(['message' => __('Invalid data.', 'email-referral')]);
        }

        // Duplicate check (block all duplicates, whether verified or pending)
        global $wpdb;
        $referral_table = $wpdb->prefix . 'er_referrals';
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $referral_table WHERE user_id = %d AND product_id = %d AND email = %s",
            $user_id, $product_id, $email
        ));
        if ($existing > 0) {
            wp_send_json_error(['message' => __('This email has already been invited for this product.', 'email-referral')]);
        }

        // Step 1: Generate a referral token
        $referral_token = wp_generate_password(20, false, false);

        // Step 2: Save referral with token
        // Insert referral row with token (adjust as needed for your DB logic)
        $wpdb->insert($referral_table, [
            'user_id' => $user_id,
            'product_id' => $product_id,
            'slot' => $slot,
            'email' => $email,
            'referral_token' => $referral_token,
            'verified' => 0,
            'registered_user_id' => 0
        ]);
        $saved = $wpdb->insert_id;

        if ($saved) {
            // Step 3: Build registration link
            $registration_page_url = 'https://blueviolet-nightingale-806611.hostingersite.com/affiliate-registration/';
            $registration_link = add_query_arg('token', $referral_token, $registration_page_url);

            $product = get_post($product_id);
            ER_Email::send_referral_email($email, $registration_link, $product);

            wp_send_json_success(['message' => __('Referral sent successfully.', 'email-referral')]);
        } else {
            wp_send_json_error(['message' => __('Failed to save referral.', 'email-referral')]);
        }
    }

    // NEW: AJAX endpoint to change referral email for a slot
    public function change_referral_email() {
        check_ajax_referer('er_referral_nonce', 'nonce');
        $referral_id = isset($_POST['referral_id']) ? intval($_POST['referral_id']) : 0;
        $new_email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $user_id = get_current_user_id();
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        if (!$referral_id || !$product_id || !is_email($new_email)) {
            wp_send_json_error(['message' => __('Invalid data.', 'email-referral')]);
        }
        global $wpdb;
        $referral_table = $wpdb->prefix . 'er_referrals';

        // Check if this referral is verified, don't allow change
        $verified = $wpdb->get_var($wpdb->prepare("SELECT verified FROM $referral_table WHERE id = %d", $referral_id));
        if ($verified) {
            wp_send_json_error(['message' => __('Cannot change a verified referral.', 'email-referral')]);
        }
        // Check if this email is already used for this user/product
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $referral_table WHERE user_id = %d AND product_id = %d AND email = %s AND id != %d",
            $user_id, $product_id, $new_email, $referral_id
        ));
        if ($existing > 0) {
            wp_send_json_error(['message' => __('This email has already been invited for this product.', 'email-referral')]);
        }
        // Update the referral slot
        $wpdb->update($referral_table, ['email' => $new_email, 'verified' => 0], ['id' => $referral_id]);
        wp_send_json_success(['message' => __('Referral email updated.', 'email-referral')]);
    }
}

// === DELETE PRODUCT HANDLER ===
add_action('wp_ajax_er_delete_product', 'er_delete_product');

function er_delete_product() {
    check_ajax_referer('er_admin_nonce', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => __('Unauthorized', 'email-referral')]);
    }

    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error(['message' => __('Invalid product ID', 'email-referral')]);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'er_products';

    // Get WooCommerce product ID before deleting
    $wc_id = $wpdb->get_var($wpdb->prepare("SELECT wc_product_id FROM $table WHERE id = %d", $product_id));
    if ($wc_id) {
        wp_delete_post($wc_id, true);
    }

    $deleted = $wpdb->delete($table, ['id' => $product_id]);

    if ($deleted) {
        wp_send_json_success(['message' => __('Product deleted', 'email-referral')]);
    } else {
        wp_send_json_error(['message' => __('Failed to delete product', 'email-referral')]);
    }
}