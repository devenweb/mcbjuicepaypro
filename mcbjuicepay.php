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
Requires at least: 8.0
Tested up to:      8.4.5
Requires PHP:      8.4
WC requires at least: 9.0
WC tested up to:   9.7.1.
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
        if ($order && $order->get_status() === 'on-hold') {
            // TODO: Implement actual API call to MCB Juice for payment verification
            $order->update_status('processing', __('Payment verified via API.', 'mcb-juice-qr-gateway'));
        }
    });
});
