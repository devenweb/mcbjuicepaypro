<?php
/**
 * Front-end Functions for MCB Juice QR Payment Gateway Pro.
 *
 * This file contains functions related to the front-end display of the plugin,
 * primarily for displaying the dynamic QR code on the thank you page.
 *
 * @package MCB_Juice_QR_Payment_Gateway_Pro
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Displays a dynamic QR code on the WooCommerce thank you page.
 *
 * This function generates a QR code based on order details (amount, currency, order ID)
 * and displays it to the user for payment. It uses an external QR code generation service.
 *
 * @param int $order_id The ID of the WooCommerce order.
 * @return void
 */
function mcb_juice_qr_display_dynamic_qr_code($order_id) {
    $gateway = new MCB_Juice_QR_Payment_Gateway_Premium();
    if ($gateway->qr_code_type !== 'dynamic') {
        return;
    }
    $order = wc_get_order($order_id);
    if ($order) {
        $amount = $order->get_total();
        $currency = $order->get_currency();
        $payload = "Pay {$amount} {$currency} for order #{$order_id}";
        $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($payload) . "&size=200x200";
        echo '<h3>' . __('Scan to Pay', 'mcb-juice-qr-gateway') . '</h3>';
        echo '<img src="' . esc_url($qr_code_url) . '" alt="Dynamic QR Code" style="max-width: 200px;">';
    }
}
