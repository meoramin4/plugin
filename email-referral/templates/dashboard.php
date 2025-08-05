<?php
if (!is_user_logged_in()) {
    echo '<div class="er-dashboard-login">' . __('Please log in to access your referral dashboard.', 'email-referral') . '</div>';
    return;
}

$current_user = wp_get_current_user();
$products = ER_DB::get_all_plugin_products();

// Load all referrals for this user, grouped by product and slot
$user_referrals = ER_DB::get_user_referral_slots_grouped($current_user->ID);

// Helper to check if product is in cart
function er_is_product_in_cart($wc_product_id) {
    if (!function_exists('WC') || !WC()->cart) return false;
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $wc_product_id) {
            return true;
        }
    }
    return false;
}
?>
<style>
.er-referral-product-box {
    border: 1px solid #e2e2e2;
    border-radius: 10px;
    margin: 30px 0;
    padding: 28px 28px 20px 28px;
    background: #fff;
    box-shadow: 0 2px 12px #f0f0f0;
    max-width: 600px;
}
.er-product-img {
    width: 90px;
    height: 90px;
    object-fit: cover;
    margin-bottom: 15px;
    border-radius: 9px;
    background: #f5f5f5;
}
.er-referral-slot-row {
    display: flex; align-items: center; gap: 12px; margin-bottom: 14px;
}
.er-referral-slot-row input[type=email] {
    min-width: 220px;
}
.er-status {
    font-size: 0.98em; font-weight: 600; padding: 3px 13px; border-radius: 13px;
    margin-left: 6px;
}
.verified { color: #237804; background: #d9f7be; border: 1px solid #b7eb8f; }
.pending { color: #ad8b00; background: #fffbe6; border: 1px solid #ffe58f; }
.failed { color: #cf1322; background: #fff1f0; border: 1px solid #ffa39e; }
.er-unlock-btn.unlocked { background: #389e0d; border-color: #b7eb8f; color: #fff; margin-top: 10px; }
.er-unlock-btn.locked { background: #eaeaea; border-color: #ddd; color: #aaa; margin-top: 10px; cursor: not-allowed; }
.er-change-email-btn {
    background: #e67e22;
    color: #fff;
    padding: 5px 13px;
    border-radius: 4px;
    border: none;
    font-size: 0.95em;
    cursor: pointer;
}
.er-change-email-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}
</style>

<div class="er-dashboard-products">
<?php foreach ($products as $product):
    $slots = isset($user_referrals[$product->id]) ? $user_referrals[$product->id] : [];
    $verified_count = 0;
    $wc_product_id = isset($product->wc_product_id) ? intval($product->wc_product_id) : 0;
    $has_used_discount = ER_DB::has_used_referral_discount($current_user->ID, $product->id);
    $is_in_cart = er_is_product_in_cart($wc_product_id);
?>
    <div class="er-referral-product-box" data-product-id="<?php echo esc_attr($product->id); ?>">
        <?php if ($product->image_id): ?>
            <img src="<?php echo esc_url(wp_get_attachment_url($product->image_id)); ?>" class="er-product-img" alt="Product" />
        <?php endif; ?>
        <h3><?php echo esc_html($product->name); ?></h3>
        <p><?php echo esc_html($product->description); ?></p>
        <p>
            <del><?php echo wc_price($product->price); ?></del>
            <strong><?php echo wc_price($product->final_price); ?></strong>
        </p>
        <div class="er-referral-slots">
        <?php for ($i=0; $i<5; $i++):
            $ref = isset($slots[$i]) ? $slots[$i] : null;
            $email = $ref && $ref->email ? $ref->email : '';
            $verified = $ref && $ref->verified ? true : false;
            $status = '';
            $status_class = '';
            $referral_id = $ref && isset($ref->id) ? $ref->id : '';
            if ($email) {
                if ($verified) {
                    $status = __('Verified', 'email-referral');
                    $status_class = 'verified';
                    $verified_count++;
                } else {
                    $status = __('Pending', 'email-referral');
                    $status_class = 'pending';
                }
            }
        ?>
            <div class="er-referral-slot-row" data-slot="<?php echo $i; ?>" data-referral-id="<?php echo esc_attr($referral_id); ?>">
                <input type="email"
                       class="er-referral-slot-email"
                       value="<?php echo esc_attr($email); ?>"
                       placeholder="<?php esc_attr_e('Enter email', 'email-referral'); ?>"
                       <?php echo $email ? 'readonly' : ''; ?> />
                <span class="er-status <?php echo $status_class; ?>" style="<?php echo $email ? '' : 'display:none;'; ?>"><?php echo $status; ?></span>
                <?php if ($email && !$verified): ?>
                    <button class="er-change-email-btn"><?php _e('Change Email', 'email-referral'); ?></button>
                    <button class="button er-referral-slot-submit" style="display:none;"><?php _e('Send', 'email-referral'); ?></button>
                <?php elseif (!$email): ?>
                    <button class="button er-referral-slot-submit"><?php _e('Send', 'email-referral'); ?></button>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
        </div>
        <?php if ($verified_count === 5): ?>
            <?php if ($has_used_discount): ?>
                <button class="button er-unlock-btn locked" disabled><?php _e('Discount Used', 'email-referral'); ?></button>
            <?php elseif ($is_in_cart): ?>
                <button class="button er-unlock-btn locked" disabled><?php _e('Already in Cart', 'email-referral'); ?></button>
            <?php elseif (class_exists('ER_WooCommerce') && method_exists('ER_WooCommerce', 'get_discount_checkout_url')): ?>
                <a href="<?php echo esc_url(ER_WooCommerce::get_discount_checkout_url($product)); ?>" class="button er-unlock-btn unlocked"><?php _e('Unlock Discount', 'email-referral'); ?></a>
            <?php else: ?>
                <button class="button er-unlock-btn locked" disabled><?php _e('Discount URL not available', 'email-referral'); ?></button>
            <?php endif; ?>
        <?php else: ?>
            <button class="button er-unlock-btn locked" disabled><?php _e('Unlock Discount', 'email-referral'); ?></button>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
</div>
<script>
jQuery(function($){
    // Normal send for new referrals
    $('.er-dashboard-products').on('click', '.er-referral-slot-submit', function(e){
        e.preventDefault();
        var btn = $(this);
        var row = btn.closest('.er-referral-slot-row');
        var card = btn.closest('.er-referral-product-box');
        var product_id = card.data('product-id');
        var slot = row.data('slot');
        var email_input = row.find('.er-referral-slot-email');
        var email = email_input.val().trim();
        var status_span = row.find('.er-status');
        var referral_id = row.data('referral-id');
        // If input is not readonly and referral_id exists, trigger change AJAX
        if (referral_id && !email_input.prop('readonly')) {
            if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
                status_span.text('<?php echo esc_js(__('Please enter a valid email.', 'email-referral')); ?>')
                    .removeClass().addClass('er-status failed').show();
                return;
            }
            btn.prop('disabled', true);
            status_span.text('<?php echo esc_js(__('Updating...', 'email-referral')); ?>')
                .removeClass().addClass('er-status pending').show();
            $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                action: 'er_change_referral_email',
                referral_id: referral_id,
                product_id: product_id,
                email: email,
                nonce: '<?php echo wp_create_nonce('er_referral_nonce'); ?>'
            }, function(res){
                btn.prop('disabled', false);
                if (res.success) {
                    email_input.val(email).prop('readonly', true);
                    btn.hide();
                    row.find('.er-change-email-btn').show();
                    status_span.text('<?php echo esc_js(__('Pending', 'email-referral')); ?>')
                        .removeClass().addClass('er-status pending').show();
                } else {
                    status_span.text(res.data && res.data.message ? res.data.message : 'Error')
                        .removeClass().addClass('er-status failed').show();
                }
            });
            return;
        }
        // Otherwise, normal create AJAX for empty slot
        if (!email || !/^\S+@\S+\.\S+$/.test(email)) {
            status_span.text('<?php echo esc_js(__('Please enter a valid email.', 'email-referral')); ?>')
                .removeClass().addClass('er-status failed').show();
            return;
        }
        btn.prop('disabled', true);
        status_span.text('<?php echo esc_js(__('Sending...', 'email-referral')); ?>')
            .removeClass().addClass('er-status pending').show();
        $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
            action: 'er_submit_referral_email',
            product_id: product_id,
            slot: slot,
            email: email,
            nonce: '<?php echo wp_create_nonce('er_referral_nonce'); ?>'
        }, function(res){
            btn.prop('disabled', false);
            if (res.success) {
                email_input.val(email).prop('readonly', true);
                btn.hide();
                status_span.text('<?php echo esc_js(__('Pending', 'email-referral')); ?>')
                    .removeClass().addClass('er-status pending').show();
            } else {
                status_span.text(res.data && res.data.message ? res.data.message : 'Error')
                    .removeClass().addClass('er-status failed').show();
            }
        });
    });

    // Change email handler for pending referrals
    $('.er-dashboard-products').on('click', '.er-change-email-btn', function(e){
        e.preventDefault();
        var btn = $(this);
        var row = btn.closest('.er-referral-slot-row');
        var email_input = row.find('.er-referral-slot-email');
        var status_span = row.find('.er-status');
        email_input.prop('readonly', false).val('').focus();
        btn.hide();
        status_span.text('').hide();
        row.find('.er-referral-slot-submit').show();
    });
});
</script>