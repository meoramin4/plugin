<?php
if (!class_exists('ER_WooCommerce')) {
    class ER_WooCommerce {
        public function __construct() {
            add_action('woocommerce_before_calculate_totals', [$this, 'apply_discount_if_unlocked'], 11);
        }

        public function apply_discount_if_unlocked($cart) {
            if (is_admin() && !defined('DOING_AJAX')) return;
            if (!is_user_logged_in()) return;
            $user_id = get_current_user_id();
            foreach ($cart->get_cart() as $cart_item) {
                $product_id = $cart_item['product_id'];
                // Use your custom DB method to get referral product info
                $ref_product = ER_DB::get_product_by_wc_product($product_id);
                if ($ref_product) {
                    $verified = ER_DB::get_verified_count($user_id, $ref_product->id);
                    if ($verified >= 5) {
                        $cart_item['data']->set_price($ref_product->final_price);
                    }
                }
            }
        }
    }
}