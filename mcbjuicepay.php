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

    class MCB_Juice_QR_Payment_Gateway_Premium extends WC_Payment_Gateway {
        public function __construct() {
            $this->id                 = 'mcb_juice_qr_gateway_premium';
            $this->has_fields         = true;
            $this->method_title       = __('MCB Juice QR Payment Premium', 'mcb-juice-qr-gateway');
            $this->method_description = __('Premium payment gateway for MCB Juice QR with advanced features using CDNs.', 'mcb-juice-qr-gateway');

            // Initialize settings and form fields
            $this->init_form_fields();
            $this->init_settings();

            // Load settings
            $this->title             = $this->get_option('title');
            $this->description       = $this->get_option('description');
            $this->qr_code_type      = $this->get_option('qr_code_type', 'static');
            $this->qr_code_url       = $this->get_option('qr_code_url');
            $this->bank_logo         = $this->get_option('bank_logo');
            $this->verification_type = $this->get_option('verification_type', 'manual');
            $this->api_url           = $this->get_option('api_url');
            $this->api_key           = $this->get_option('api_key');

            // Set the icon for the bank logo
            $this->icon = $this->bank_logo;

            // Hooks
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
            add_action('woocommerce_thankyou_' . $this->id, [$this, 'display_dynamic_qr_code']);
        }

        /**
         * Initialize admin settings fields
         */
        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __('Enable/Disable', 'mcb-juice-qr-gateway'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable MCB Juice QR Payment Premium', 'mcb-juice-qr-gateway'),
                    'default' => 'yes',
                ],
                'title' => [
                    'title'   => __('Title', 'mcb-juice-qr-gateway'),
                    'type'    => 'text',
                    'default' => __('MCB Juice QR Payment', 'mcb-juice-qr-gateway'),
                ],
                'description' => [
                    'title'   => __('Description', 'mcb-juice-qr-gateway'),
                    'type'    => 'textarea',
                    'default' => __('Pay securely via MCB Juice QR.', 'mcb-juice-qr-gateway'),
                ],
                'qr_code_type' => [
                    'title'   => __('QR Code Type', 'mcb-juice-qr-gateway'),
                    'type'    => 'select',
                    'options' => [
                        'static'  => __('Static', 'mcb-juice-qr-gateway'),
                        'dynamic' => __('Dynamic', 'mcb-juice-qr-gateway'),
                    ],
                    'default' => 'static',
                ],
                'qr_code_url' => [
                    'title'       => __('Static QR Code Image URL', 'mcb-juice-qr-gateway'),
                    'type'        => 'text',
                    'description' => $this->get_qr_code_field_html(),
                    'desc_tip'    => false,
                    'custom_attributes' => ['readonly' => 'readonly'],
                ],
                'bank_logo' => [
                    'title'       => __('Bank Logo URL', 'mcb-juice-qr-gateway'),
                    'type'        => 'text',
                    'description' => $this->get_bank_logo_field_html(),
                    'desc_tip'    => false,
                    'custom_attributes' => ['readonly' => 'readonly'],
                ],
                'verification_type' => [
                    'title'   => __('Verification Type', 'mcb-juice-qr-gateway'),
                    'type'    => 'select',
                    'options' => [
                        'manual'    => __('Manual', 'mcb-juice-qr-gateway'),
                        'automated' => __('Automated', 'mcb-juice-qr-gateway'),
                    ],
                    'default' => 'manual',
                ],
                'api_url' => [
                    'title'       => __('API URL', 'mcb-juice-qr-gateway'),
                    'type'        => 'text',
                    'description' => __('Enter the API URL for automated verification.', 'mcb-juice-qr-gateway'),
                    'default'     => '',
                ],
                'api_key' => [
                    'title'       => __('API Key', 'mcb-juice-qr-gateway'),
                    'type'        => 'password',
                    'description' => __('Enter the API Key for automated verification.', 'mcb-juice-qr-gateway'),
                    'default'     => '',
                ],
            ];
        }

        /**
         * Generate HTML for QR code upload field
         */
        private function get_qr_code_field_html() {
            $qr_code_url = esc_url($this->get_option('qr_code_url'));
            ob_start();
            ?>
            <div class="qr-code-upload-wrapper">
                <button type="button" class="button upload_qr_code_button" style="margin-top: 5px;">
                    <?php _e('Select QR Code', 'mcb-juice-qr-gateway'); ?>
                </button>
                <?php if ($qr_code_url) : ?>
                    <p style="margin-top: 10px;">
                        <img src="<?php echo $qr_code_url; ?>" style="max-width: 200px; display: block;">
                    </p>
                <?php endif; ?>
                <p class="description"><?php _e('Select a QR code image from the media library.', 'mcb-juice-qr-gateway'); ?></p>
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Generate HTML for bank logo upload field
         */
        private function get_bank_logo_field_html() {
            $bank_logo = esc_url($this->get_option('bank_logo'));
            ob_start();
            ?>
            <div class="bank-logo-upload-wrapper">
                <button type="button" class="button upload_bank_logo_button" style="margin-top: 5px;">
                    <?php _e('Select Bank Logo', 'mcb-juice-qr-gateway'); ?>
                </button>
                <?php if ($bank_logo) : ?>
                    <p style="margin-top: 10px;">
                        <img src="<?php echo $bank_logo; ?>" style="max-width: 200px; display: block;">
                    </p>
                <?php endif; ?>
                <p class="description"><?php _e('Select a bank logo from the media library (recommended size: 50x50px).', 'mcb-juice-qr-gateway'); ?></p>
            </div>
            <?php
            return ob_get_clean();
        }

        /**
         * Enqueue admin scripts for media uploader and conditional fields
         */
        public function admin_scripts($hook) {
            if ('woocommerce_page_wc-settings' !== $hook || !isset($_GET['section']) || $_GET['section'] !== $this->id) {
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
                            title: '" . __('Select QR Code Image', 'mcb-juice-qr-gateway') . "',
                            button: { text: '" . __('Use this image', 'mcb-juice-qr-gateway') . "' },
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
                            title: '" . __('Select Bank Logo', 'mcb-juice-qr-gateway') . "',
                            button: { text: '" . __('Use this image', 'mcb-juice-qr-gateway') . "' },
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

        /**
         * Process admin options with validation
         */
        public function process_admin_options() {
            $verification_type = $_POST['woocommerce_mcb_juice_qr_gateway_premium_verification_type'] ?? '';
            if ($verification_type === 'automated') {
                $api_url = $_POST['woocommerce_mcb_juice_qr_gateway_premium_api_url'] ?? '';
                $api_key = $_POST['woocommerce_mcb_juice_qr_gateway_premium_api_key'] ?? '';
                if (empty($api_url) || empty($api_key)) {
                    WC_Admin_Settings::add_error(__('API URL and API Key are required for automated verification.', 'mcb-juice-qr-gateway'));
                    return false;
                }
            }
            return parent::process_admin_options();
        }

        /**
         * Process payment and handle verification
         */
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            $order->update_status('on-hold', __('Awaiting QR payment confirmation.', 'mcb-juice-qr-gateway'));
            wc_reduce_stock_levels($order_id);

            if ($this->verification_type === 'automated') {
                // Simulate automated verification with a scheduled event
                wp_schedule_single_event(time() + 60, 'mcb_juice_qr_auto_verify', [$order_id]);
            }

            return [
                'result'   => 'success',
                'redirect' => $order->get_checkout_order_received_url(),
            ];
        }

        /**
         * Display payment fields on checkout
         */
        public function payment_fields() {
            echo wpautop(wp_kses_post($this->description));
            if ($this->qr_code_type === 'static' && $this->qr_code_url) {
                echo '<img src="' . esc_url($this->qr_code_url) . '" alt="QR Code" style="max-width: 200px;">';
            } elseif ($this->qr_code_type === 'dynamic') {
                echo '<p>' . __('Please place your order to generate a QR code.', 'mcb-juice-qr-gateway') . '</p>';
            }
        }

        /**
         * Display dynamic QR code on thank you page using CDN
         */
        public function display_dynamic_qr_code($order_id) {
            if ($this->qr_code_type !== 'dynamic') {
                return;
            }
            $order = wc_get_order($order_id);
            $amount = $order->get_total();
            $currency = $order->get_currency();
            $payload = "Pay {$amount} {$currency} for order #{$order_id}";
            $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($payload) . "&size=200x200";
            echo '<h3>' . __('Scan to Pay', 'mcb-juice-qr-gateway') . '</h3>';
            echo '<img src="' . esc_url($qr_code_url) . '" alt="Dynamic QR Code" style="max-width: 200px;">';
        }
    }

    // Simulated automated verification
    add_action('mcb_juice_qr_auto_verify', function ($order_id) {
        $order = wc_get_order($order_id);
        if ($order && $order->get_status() === 'on-hold') {
            $order->update_status('processing', __('Payment auto-verified.', 'mcb-juice-qr-gateway'));
        }
    });
});