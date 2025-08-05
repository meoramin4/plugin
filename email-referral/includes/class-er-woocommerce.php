<?php
if (!class_exists('ER_WooCommerce')) {
    class ER_WooCommerce {
        public function __construct() {
            add_action('woocommerce_before_calculate_totals', [$this, 'apply_discount_if_unlocked'], 11);
            // NEW: Track order completion to prevent repeat discount
            add_action('woocommerce_order_status_completed', [$this, 'record_referral_discount_usage']);
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
                    // NEW: Check if already used discount
                    $already_used = ER_DB::has_used_referral_discount($user_id, $ref_product->id);
                    if ($verified >= 5 && !$already_used) {
                        $cart_item['data']->set_price($ref_product->final_price);
                    }
                }
            }
        }

        // NEW: Record discount usage after order is completed
        public function record_referral_discount_usage($order_id) {
            $order = wc_get_order($order_id);
            $user_id = $order->get_user_id();
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $ref_product = ER_DB::get_product_by_wc_product($product_id);
                if ($ref_product) {
                    $verified = ER_DB::get_verified_count($user_id, $ref_product->id);
                    if ($verified >= 5 && !ER_DB::has_used_referral_discount($user_id, $ref_product->id)) {
                        global $wpdb;
                        $table = $wpdb->prefix . 'er_referral_purchases';
                        $wpdb->insert($table, [
                            'user_id' => $user_id,
                            'product_id' => $ref_product->id,
                            'order_id' => $order_id,
                            'date_used' => current_time('mysql')
                        ]);
                    }
                }
            }
        }

        // Discount checkout URL function (no changes needed)
        public static function get_discount_checkout_url($product) {
            $wc_product_id = isset($product->wc_product_id) ? intval($product->wc_product_id) : 0;
            if (!$wc_product_id) return '';
            $checkout_url = add_query_arg('add-to-cart', $wc_product_id, wc_get_checkout_url());
            return $checkout_url;
        }
    }
}