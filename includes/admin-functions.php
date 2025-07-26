<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Enqueue admin scripts for media uploader and conditional fields
 */
function mcb_juice_qr_admin_scripts($hook, $gateway_id) {
    if ('woocommerce_page_wc-settings' !== $hook || !isset($_GET['section']) || $_GET['section'] !== $gateway_id) {
        return;
    }

    wp_enqueue_media();
    $script = "
        jQuery(document).ready(function($) {
            // QR Code uploader
            $('.upload_qr_code_button').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var input = button.closest('tr').find('input[type=\"text\"]');
                var mediaUploader = wp.media({
                    title: 'Select QR Code Image',
                    button: { text: 'Use this image' },
                    multiple: false,
                    library: { type: 'image' }
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    input.val(attachment.url);
                });
                mediaUploader.open();
            });

            // Bank Logo uploader
            $('.upload_bank_logo_button').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var input = button.closest('tr').find('input[type=\"text\"]');
                var mediaUploader = wp.media({
                    title: 'Select Bank Logo',
                    button: { text: 'Use this image' },
                    multiple: false,
                    library: { type: 'image' }
                });
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    input.val(attachment.url);
                });
                mediaUploader.open();
            });

            // Conditional API fields
            var verificationTypeField = $('#woocommerce_mcb_juice_qr_gateway_premium_verification_type');
            var apiFields = $('#woocommerce_mcb_juice_qr_gateway_premium_api_url, #woocommerce_mcb_juice_qr_gateway_premium_api_key').closest('tr');

            function toggleApiFields() {
                if (verificationTypeField.val() === 'automated') {
                    apiFields.show();
                } else {
                    apiFields.hide();
                }
            }

            verificationTypeField.on('change', toggleApiFields);
            toggleApiFields();
        });
    ";
    wp_add_inline_script('jquery', $script);
}
add_action('admin_enqueue_scripts', function($hook) {
    mcb_juice_qr_admin_scripts($hook, 'mcb_juice_qr_gateway_premium');
}, 10, 2);
