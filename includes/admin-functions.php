<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue scripts for the admin settings page
function mcb_juice_qr_admin_page_scripts($hook) {
    // Only load on our plugin's settings page
    if ('woocommerce_page_wc-settings' !== $hook || !isset( $_GET['section'] ) || 'mcb_juice_qr_gateway_premium' !== $_GET['section'] ) {
        return;
    }

    wp_enqueue_media();
    
    // JavaScript for the media uploader
    $script = "
        jQuery(document).ready(function($) {
            function initMediaUploader(buttonClass, inputName, previewClass) {
                $(document).on('click', buttonClass, function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var input = $('input[name=\"' + inputName + '\"]');
                    var preview = button.siblings(previewClass);

                    var mediaUploader = wp.media({
                        title: 'Select or Upload Image',
                        button: {
                            text: 'Use this image'
                        },
                        multiple: false
                    });

                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        input.val(attachment.url);
                        preview.html('<img src=\"' + attachment.url + '\" style=\"max-width: 200px;\" />');
                    });

                    mediaUploader.open();
                });
            }

            initMediaUploader('.upload_qr_code_button', 'woocommerce_mcb_juice_qr_gateway_premium_qr_code_url', '.qr-code-preview');
            initMediaUploader('.upload_bank_logo_button', 'woocommerce_mcb_juice_qr_gateway_premium_bank_logo', '.bank-logo-preview');
        });
    ";
    
    wp_add_inline_script('jquery', $script);
}
add_action('admin_enqueue_scripts', 'mcb_juice_qr_admin_page_scripts');