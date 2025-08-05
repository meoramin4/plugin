<div class="wrap er-section">
    <h1><?php _e('Referral Settings', 'email-referral'); ?></h1>
    <?php if(isset($_GET['settings_saved'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Settings saved.','email-referral'); ?></p>
        </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('er_settings_save', 'er_settings_nonce'); ?>
        <label><?php _e('Default Sender Email','email-referral'); ?></label>
        <input type="email" name="er_sender_email" value="<?php echo esc_attr(get_option('er_sender_email')); ?>" />
        <label><?php _e('Sender Logo','email-referral'); ?></label>
        <input type="file" name="er_sender_logo" accept="image/*" />
        <?php
        if($logo_id = get_option('er_sender_logo')) {
            echo wp_get_attachment_image($logo_id, 'thumbnail');
        }
        ?>
        <label><?php _e('Graph Type','email-referral'); ?></label>
        <select name="er_graph_type">
            <option value="line" <?php selected(get_option('er_graph_type'), 'line'); ?>>Line</option>
            <option value="bar" <?php selected(get_option('er_graph_type'), 'bar'); ?>>Bar</option>
        </select>
        <button type="submit" class="er-btn"><?php _e('Save Settings','email-referral'); ?></button>
    </form>
    <hr/>
    <h2><?php _e('Delete Referral Product','email-referral'); ?></h2>
    <form method="post">
        <select name="delete_product_id">
            <?php
            if (class_exists('ER_DB') && method_exists('ER_DB', 'get_all_products')) {
                $products = ER_DB::get_all_products();
                if (!empty($products)) {
                    foreach($products as $prod) {
                        echo '<option value="' . esc_attr($prod->id) . '">' . esc_html($prod->name) . '</option>';
                    }
                } else {
                    echo '<option disabled>' . __('No referral products found.','email-referral') . '</option>';
                }
            } else {
                echo '<option disabled>' . __('Referral DB class or method missing.','email-referral') . '</option>';
            }
            ?>
        </select>
        <button type="submit" class="er-btn" name="delete_product"><?php _e('Delete','email-referral'); ?></button>
    </form>
    <hr/>
    <h2><?php _e('Assign Referral Product to User','email-referral'); ?></h2>
    <form method="get" action="admin.php">
        <input type="hidden" name="page" value="er_products" />
        <select name="user_id">
            <?php
            if (class_exists('ER_DB') && method_exists('ER_DB', 'get_all_users')) {
                $users = ER_DB::get_all_users();
                if (!empty($users)) {
                    foreach($users as $user) {
                        echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->user_email) . '</option>';
                    }
                } else {
                    echo '<option disabled>' . __('No users found.','email-referral') . '</option>';
                }
            } else {
                echo '<option disabled>' . __('Referral DB class or method missing.','email-referral') . '</option>';
            }
            ?>
        </select>
        <button type="submit" class="er-btn"><?php _e('Go','email-referral'); ?></button>
    </form>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['er_settings_nonce']) && wp_verify_nonce($_POST['er_settings_nonce'], 'er_settings_save')) {
    update_option('er_sender_email', sanitize_email($_POST['er_sender_email']));
    if (!empty($_FILES['er_sender_logo']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $logo_id = media_handle_upload('er_sender_logo', 0);
        if (is_numeric($logo_id)) {
            update_option('er_sender_logo', $logo_id);
        }
    }
    update_option('er_graph_type', sanitize_text_field($_POST['er_graph_type']));
    // Optionally handle delete product
    if (!empty($_POST['delete_product_id']) && isset($_POST['delete_product'])) {
        // You should call your delete function here.
        // ER_DB::delete_product($_POST['delete_product_id']);
    }
    wp_redirect(admin_url('admin.php?page=er_settings&settings_saved=1'));
    exit;
}
?>