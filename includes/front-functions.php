<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Display dynamic QR code on thank you page using CDN
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
