<?php
class ER_Email {
    public static function send_referral_email($to, $registration_link, $product) {
        // Subject of the email
        $subject = 'You have been invited for a special deal!';

        // Email body
        $message = "
            <html>
            <body>
                <p>Hi,</p>
                <p>I wanted to share something awesome with you — I recently joined NZmotorparts, a fantastic company that offers high-quality motor parts at great prices. Whether you're into upgrades, replacements, or just maintaining your ride. <strong>{$product->name}</strong>.</p>
                <p><a href='{$registration_link}' style='display:inline-block;padding:10px 20px;background:#0073aa;color:#fff;text-decoration:none;border-radius:5px;'>Click here to register</a></p>
            </body>
            </html>
        ";

        // Email headers
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        // Fetch the sender email from the plugin settings
        $from_email = get_option('er_sender_email');

        if (!empty($from_email) && is_email($from_email)) {
            $headers[] = "From: Your Site <{$from_email}>";
        }

        // Send the email
        $sent = wp_mail($to, $subject, $message, $headers);

        // Log error if sending fails
        if (!$sent) {
            error_log("Referral email failed to send to {$to}");
        }
    }
}
