<div class="wrap er-section">
    <h1><?php _e('Referral Users', 'email-referral'); ?></h1>
    <?php
    // Display total user count
    global $wpdb;
    $user_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users}");
    ?>
    <p><?php printf(__('Total Users: <strong>%d</strong>', 'email-referral'), intval($user_count)); ?></p>
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
            <?php
            try {
                // Get all WP users directly so all are displayed
                $wp_users = $wpdb->get_results("SELECT ID, user_email FROM {$wpdb->users} ORDER BY ID ASC");
                if (!empty($wp_users)) {
                    foreach($wp_users as $user) {
                        $sent = (class_exists('ER_DB') && method_exists('ER_DB', 'count_referrals_sent')) ? ER_DB::count_referrals_sent($user->ID) : 0;
                        $completed = (class_exists('ER_DB') && method_exists('ER_DB', 'count_referrals_completed')) ? ER_DB::count_referrals_completed($user->ID) : 0;
                        echo "<tr>
                            <td>" . esc_html($user->ID) . "</td>
                            <td>" . esc_html($user->user_email) . "</td>
                            <td>" . esc_html($sent) . "</td>
                            <td>" . esc_html($completed) . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>" . __('No users found.','email-referral') . "</td></tr>";
                }
            } catch (Exception $e) {
                echo "<tr><td colspan='4'>" . __('Error loading users: ','email-referral') . esc_html($e->getMessage()) . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>