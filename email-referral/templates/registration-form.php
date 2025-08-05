<?php
global $wpdb;
$table = $wpdb->prefix . 'er_referrals';

$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
$referral = $token ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE referral_token = %s LIMIT 1", $token)) : null;
$error = '';
$show_form = true;

// If form submitted, handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['er_register_nonce']) && wp_verify_nonce($_POST['er_register_nonce'], 'er_register')) {
    $user_login = sanitize_user($_POST['user_login']);
    $user_email = sanitize_email($_POST['user_email']);
    $user_pass  = $_POST['user_pass'];
    $user_pass2 = $_POST['user_pass2'];

    // Validate token and fetch referral again
    $referral = $token ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE referral_token = %s LIMIT 1", $token)) : null;

    if (!$user_login || !$user_email || !$user_pass || !$user_pass2) {
        $error = 'All fields are required.';
    } elseif ($user_pass !== $user_pass2) {
        $error = 'Passwords do not match.';
    } elseif (!$referral) {
        $error = 'Invalid or expired referral link.';
    } elseif (username_exists($user_login) || email_exists($user_email)) {
        $error = 'Username or email already taken.';
    } else {
        $user_id = wp_create_user($user_login, $user_pass, $user_email);
        if (is_wp_error($user_id)) {
            $error = $user_id->get_error_message();
        } else {
            // Optional: assign default role (WordPress defaults to subscriber)
            // $user = new WP_User($user_id);
            // $user->set_role('subscriber');

            // Mark referral as verified and tie user
            update_user_meta($user_id, 'referred_by', $referral->user_id);
            update_user_meta($user_id, 'referred_product', $referral->product_id);
            $wpdb->update($table, [
                'verified' => true,
                'registered_user_id' => $user_id
            ], ['id' => $referral->id]);

            // Optionally send notification email
            wp_new_user_notification($user_id, null, 'user');

            echo '<div class="notice notice-success"><p>Registration successful! You can now <a href="' . esc_url(wp_login_url()) . '">log in</a>.</p></div>';
            $show_form = false;
        }
    }
}

if ($error) {
    echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
}

if ($show_form) :
?>
<form method="post">
    <p>
        <label for="user_login">Username</label><br>
        <input type="text" name="user_login" id="user_login" required>
    </p>
    <p>
        <label for="user_email">Email</label><br>
        <input type="email" name="user_email" id="user_email" required>
    </p>
    <p>
        <label for="user_pass">Password</label><br>
        <input type="password" name="user_pass" id="user_pass" required>
    </p>
    <p>
        <label for="user_pass2">Confirm Password</label><br>
        <input type="password" name="user_pass2" id="user_pass2" required>
    </p>
    <input type="hidden" name="er_register_nonce" value="<?php echo esc_attr(wp_create_nonce('er_register')); ?>">
    <button type="submit">Register</button>
</form>
<?php endif; ?>