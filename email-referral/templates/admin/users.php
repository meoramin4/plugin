<div class="wrap er-section">
    <h1><?php _e('Referral Users', 'email-referral'); ?></h1>
    <table class="er-table">
        <thead>
            <tr>
                <th><?php _e('User ID','email-referral'); ?></th>
                <th><?php _e('Email','email-referral'); ?></th>
                <th><?php _e('Referrals Sent','email-referral'); ?></th>
                <th><?php _e('Referrals Completed','email-referral'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach(ER_DB::get_all_users_with_referrals() as $user): ?>
            <tr>
                <td><?php echo esc_html($user->ID); ?></td>
                <td><?php echo esc_html($user->user_email); ?></td>
                <td><?php echo esc_html(ER_DB::count_referrals_sent($user->ID)); ?></td>
                <td><?php echo esc_html(ER_DB::count_referrals_completed($user->ID)); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>