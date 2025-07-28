<div class="wrap er-section">
    <h1><?php _e('Referral Overview', 'email-referral'); ?></h1>
    <table class="er-table">
        <thead>
            <tr>
                <th><?php _e('Referral ID','email-referral'); ?></th>
                <th><?php _e('User','email-referral'); ?></th>
                <th><?php _e('Product','email-referral'); ?></th>
                <th><?php _e('Email','email-referral'); ?></th>
                <th><?php _e('Verified','email-referral'); ?></th>
                <th><?php _e('Date','email-referral'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $referrals = ER_DB::get_all_referrals();
            if (empty($referrals)) : ?>
                <tr><td colspan="6"><?php _e('No referrals found.','email-referral'); ?></td></tr>
            <?php
            else:
            foreach($referrals as $ref):
                $user = get_userdata($ref->user_id);
                $user_email = $user ? $user->user_email : __('(Unknown)','email-referral');
                $product = ER_DB::get_product_name($ref->product_id);
                $product_name = $product ? $product : __('(Unknown)','email-referral');
            ?>
            <tr>
                <td><?php echo esc_html($ref->id); ?></td>
                <td><?php echo esc_html($user_email); ?></td>
                <td><?php echo esc_html($product_name); ?></td>
                <td><?php echo esc_html($ref->email); ?></td>
                <td><?php echo $ref->verified ? '<span class="er-success">Yes</span>' : '<span class="er-error">No</span>'; ?></td>
                <td><?php echo esc_html($ref->created_at); ?></td>
            </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>