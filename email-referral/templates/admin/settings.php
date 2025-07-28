<div class="wrap er-section">
    <h1><?php _e('Referral Settings', 'email-referral'); ?></h1>
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('er_settings_save', 'er_settings_nonce'); ?>
        <label><?php _e('Default Sender Email','email-referral'); ?></label>
        <input type="email" name="er_sender_email" value="<?php echo esc_attr(get_option('er_sender_email')); ?>" />
        <label><?php _e('Sender Logo','email-referral'); ?></label>
        <input type="file" name="er_sender_logo" accept="image/*" />
        <?php if($logo_id = get_option('er_sender_logo')) echo wp_get_attachment_image($logo_id, 'thumbnail'); ?>
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
            <?php foreach(ER_DB::get_all_products() as $prod): ?>
                <option value="<?php echo $prod->id; ?>"><?php echo esc_html($prod->name); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="er-btn"><?php _e('Delete','email-referral'); ?></button>
    </form>
    <hr/>
    <h2><?php _e('Assign Referral Product to User','email-referral'); ?></h2>
    <form method="get" action="admin.php">
        <input type="hidden" name="page" value="er_products" />
        <select name="user_id">
            <?php foreach(ER_DB::get_all_users() as $user): ?>
                <option value="<?php echo $user->ID; ?>"><?php echo esc_html($user->user_email); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="er-btn"><?php _e('Go','email-referral'); ?></button>
    </form>
</div>
<?php
// Handle settings save securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['er_settings_nonce']) && wp_verify_nonce($_POST['er_settings_nonce'], 'er_settings_save')) {
    update_option('er_sender_email', sanitize_email($_POST['er_sender_email']));
    if (!empty($_FILES['er_sender_logo']['tmp_name'])) {
        // Use media_handle_upload for image, omitted for brevity
    }
    update_option('er_graph_type', sanitize_text_field($_POST['er_graph_type']));
    // Handle delete product, assign product, etc...
}
?>