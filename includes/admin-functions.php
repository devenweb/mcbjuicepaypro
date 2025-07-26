<?php
/**
 * Admin Functions for MCB Juice QR Payment Gateway Pro.
 *
 * This file contains functions related to the administration area of the plugin,
 * including enqueuing admin scripts and adding settings links.
 *
 * @package MCB_Juice_QR_Payment_Gateway_Pro
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Enqueues admin-specific scripts for the MCB Juice QR Payment Gateway Pro plugin.
 *
 * This function ensures that the admin scripts are loaded only on the plugin's
 * WooCommerce settings page for the MCB Juice QR Gateway section.
 *
 * @param string $hook The current admin page hook.
 * @return void
 */
function mcb_juice_qr_admin_scripts($hook) {
    // Only load on our plugin's settings page
    if ('woocommerce_page_wc-settings' !== $hook || !isset($_GET['section']) || 'mcb_juice_qr_gateway_premium' !== $_GET['section']) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_script('mcb-juice-qr-admin', plugin_dir_url(__FILE__) . '../assets/js/admin-scripts.js', array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'mcb_juice_qr_admin_scripts');

/**
 * Adds a settings link to the plugin's action links on the plugins page.
 *
 * This function inserts a direct link to the plugin's settings page within the
 * list of action links displayed for the plugin on the WordPress plugins screen.
 *
 * @param array $links An array of existing plugin action links.
 * @return array The modified array of plugin action links with the settings link added.
 */
function mcb_juice_qr_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=mcb_juice_qr_gateway_premium') . '">' . __('Settings', 'mcb-juice-qr-gateway') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(MCB_JUICE_QR_PAYMENT_GATEWAY_PREMIUM_FILE), 'mcb_juice_qr_add_settings_link');
