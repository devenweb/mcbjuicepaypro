<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue admin scripts
function mcb_juice_qr_admin_scripts($hook) {
    // Only load on our plugin's settings page
    if ('woocommerce_page_wc-settings' !== $hook || !isset($_GET['section']) || 'mcb_juice_qr_gateway_premium' !== $_GET['section']) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('mcb-juice-qr-admin', plugin_dir_url(__FILE__) . '../assets/js/admin-scripts.js', array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'mcb_juice_qr_admin_scripts');

// Add settings link to plugin page
function mcb_juice_qr_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=mcb_juice_qr_gateway_premium') . '">' . __('Settings', 'mcb-juice-qr-gateway') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(MCB_JUICE_QR_PAYMENT_GATEWAY_PREMIUM_FILE), 'mcb_juice_qr_add_settings_link');
