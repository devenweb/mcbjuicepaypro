<?php
/**
 * Uninstall MCB Juice QR Payment Gateway Pro.
 *
 * This file is executed when the plugin is uninstalled.
 * It cleans up all plugin-related data from the database.
 *
 * @package MCB_Juice_QR_Payment_Gateway_Pro
 */

// If uninstall.php is not called by WordPress, die.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Delete plugin options.
delete_option('woocommerce_mcb_juice_qr_gateway_premium_settings');

// You might also want to delete any custom post types, taxonomies, or database tables
// created by the plugin here. For this plugin, it seems only options are stored.
