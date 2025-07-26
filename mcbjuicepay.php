<?php
/*
Plugin Name:       MCB Juice QR Payment Gateway Pro
Plugin URI:        https://devenweb.com/product/mcb-juice-qr-payment-gateway-premium/
Description:       A WooCommerce payment gateway for MCB Juice QR payments with proper checkout display.
Version:           1.2.3
Author:            Deven Pawaray
Author URI:        https://devenweb.com
Text Domain:       mcbjuicepaylite
Domain Path:       /languages 
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.8
Tested up to:      6.5
Requires PHP:      7.4
WC requires at least: 7.0
WC tested up to:   8.9
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

// Include the functions and class files
require_once plugin_dir_path(__FILE__) . 'includes/admin-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/front-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/woocommerce-integration.php';

// Register the payment gateway
add_filter('woocommerce_payment_gateways', function ($gateways) {
    $gateways[] = 'MCB_Juice_QR_Payment_Gateway_Premium';
    return $gateways;
});

// Load the gateway class
add_action('plugins_loaded', function () {
    if (!class_exists('WC_Payment_Gateway')) {
        return; // Exit if WooCommerce is not loaded
    }

    add_action('mcb_juice_qr_verify_payment', function ($order_id) {
        $order = wc_get_order($order_id);
        if (!$order || $order->get_status() !== 'on-hold') {
            return;
        }

        $gateway_settings = get_option('woocommerce_mcb_juice_qr_gateway_premium_settings');
        $api_url = $gateway_settings['api_url'] ?? '';
        $api_key = $gateway_settings['api_key'] ?? '';
        $api_verification = $gateway_settings['api_verification'] ?? 'no';

        if ('yes' !== $api_verification || empty($api_url) || empty($api_key)) {
            return;
        }

        $url = esc_url_raw($api_url);

        $body = [
            'order_id' => $order_id,
            'amount'   => $order->get_total(),
            'currency' => $order->get_currency(),
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json; charset=utf-8',
        ];

        $args = [
            'body'    => json_encode($body),
            'headers' => $headers,
            'timeout' => 60,
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $order->add_order_note(sprintf(__('API Error: %s', 'mcb-juice-qr-gateway'), $error_message));
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);

        if ($response_code === 200 && isset($data['status']) && $data['status'] === 'success') {
            $order->payment_complete();
            $order->add_order_note(__('Payment successfully verified via API.', 'mcb-juice-qr-gateway'));
        } else {
            $error_message = $data['message'] ?? __('Unknown API error', 'mcb-juice-qr-gateway');
            $order->add_order_note(sprintf(__('API Verification Failed: %s (Status code: %d)', 'mcb-juice-qr-gateway'), $error_message, $response_code));
        }
    });
});
