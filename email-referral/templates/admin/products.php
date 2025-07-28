<?php 
// Handle product creation
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && current_user_can('manage_options')) {
    $name = sanitize_text_field($_POST['product_name'] ?? '');
    $description = wp_kses_post($_POST['product_desc'] ?? '');
    $price = floatval($_POST['product_price'] ?? 0);
    $discount = floatval($_POST['product_discount'] ?? 0);
    $final_price = $price - $discount;
    $product_code = sanitize_text_field($_POST['product_code'] ?? '');
    $image_id = 0;

    if (!empty($_FILES['product_image']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_id = media_handle_upload('product_image', 0);
        if (!is_wp_error($attachment_id)) {
            $image_id = $attachment_id;
        }
    }

    $wc_product = [
        'post_title'   => $name,
        'post_content' => $description,
        'post_status'  => 'publish',
        'post_type'    => 'product',
    ];
    $wc_product_id = wp_insert_post($wc_product);

    if ($wc_product_id && !is_wp_error($wc_product_id)) {
        update_post_meta($wc_product_id, '_price', $final_price);
        update_post_meta($wc_product_id, '_regular_price', $price);
        if ($image_id) {
            set_post_thumbnail($wc_product_id, $image_id);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'er_products';
        $result = $wpdb->insert($table, [
            'wc_product_id' => $wc_product_id,
            'name' => $name,
            'description' => $description,
            'image_id' => $image_id,
            'price' => $price,
            'discount' => $discount,
            'final_price' => $final_price,
            'product_code' => $product_code,
            'created_at' => current_time('mysql'),
        ]);

        if ($result) {
            $msg = '<div class="notice notice-success">Product created successfully!</div>';
        } else {
            wp_delete_post($wc_product_id, true);
            $msg = '<div class="notice notice-error">Failed to create referral product.</div>';
        }
    } else {
        $msg = '<div class="notice notice-error">Failed to create WooCommerce product.</div>';
    }
}
?>

<div class="wrap er-section">
    <h1><?php _e('Referral Products', 'email-referral'); ?></h1>
    <?php if (!empty($msg)) echo $msg; ?>
    <form method="post" enctype="multipart/form-data">
        <h2><?php _e('Create New Referral Product', 'email-referral'); ?></h2>
        <input type="text" name="product_name" placeholder="Product Name" required />
        <textarea name="product_desc" placeholder="Description" required></textarea>
        <input type="number" name="product_price" step="0.01" placeholder="Original Price" required />
        <input type="number" name="product_discount" step="0.01" placeholder="Discount Amount" required />
        <input type="file" name="product_image" accept="image/*" />
        <input type="text" name="product_code" placeholder="Product Code" required />
        <button type="submit" class="er-btn"><?php _e('Create', 'email-referral'); ?></button>
    </form>
    <hr/>
    <h2><?php _e('Existing Referral Products', 'email-referral'); ?></h2>
    <table class="er-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID','email-referral'); ?></th>
                <th><?php _e('Name','email-referral'); ?></th>
                <th><?php _e('Code','email-referral'); ?></th>
                <th><?php _e('Price','email-referral'); ?></th>
                <th><?php _e('Discount','email-referral'); ?></th>
                <th><?php _e('Final Price','email-referral'); ?></th>
                <th><?php _e('Image','email-referral'); ?></th>
                <th><?php _e('Action','email-referral'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach(ER_DB::get_all_plugin_products() as $prod): ?>
            <tr>
                <td><?php echo esc_html($prod->id); ?></td>
                <td>
                    <?php echo esc_html($prod->name); ?><br/>
                    <?php if ($prod->wc_product_id): ?>
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $prod->wc_product_id . '&action=edit')); ?>" target="_blank" style="font-size:10px;">
                            <?php _e('Edit in WooCommerce', 'email-referral'); ?>
                        </a>
                    <?php endif; ?>
                </td>
                <td><?php echo esc_html($prod->product_code); ?></td>
                <td><?php echo wc_price($prod->price); ?></td>
                <td><?php echo wc_price($prod->discount); ?></td>
                <td><?php echo wc_price($prod->final_price); ?></td>
                <td><?php if ($prod->image_id) echo wp_get_attachment_image($prod->image_id, 'thumbnail'); ?></td>
                <td>
                    <button 
                        type="button"
                        class="er-btn er-delete-product" 
                        data-id="<?php echo esc_attr($prod->id); ?>">
                        <?php _e('Delete','email-referral'); ?>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>