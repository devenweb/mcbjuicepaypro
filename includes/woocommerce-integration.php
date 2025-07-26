<?php
/**
 * WooCommerce Integration for MCB Juice QR Payment Gateway Pro.
 *
 * This file defines the WooCommerce payment gateway class for MCB Juice QR,
 * handling payment processing, settings, and display on the checkout page.
 *
 * @package MCB_Juice_QR_Payment_Gateway_Pro
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * MCB_Juice_QR_Payment_Gateway_Premium class.
 *
 * Implements the MCB Juice QR payment gateway for WooCommerce.
 * This class handles the integration of the payment method, including its settings,
 * payment processing logic, and display on the checkout and thank you pages.
 */
class MCB_Juice_QR_Payment_Gateway_Premium extends WC_Payment_Gateway {
    /**
     * Constructor for the gateway.
     *
     * Sets up the gateway properties, initializes form fields and settings,
     * loads options, and hooks into WooCommerce actions.
     */
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
        $this->api_verification  = $this->get_option('api_verification');
        $this->api_url           = $this->get_option('api_url');
        $this->api_key           = $this->get_option('api_key');

        // Set the icon for the bank logo
        $this->icon = $this->bank_logo;

        // Hooks
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_thankyou_' . $this->id, 'mcb_juice_qr_display_dynamic_qr_code');
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
            'api_verification' => [
                'title'   => __('Enable API Verification', 'mcb-juice-qr-gateway'),
                'type'    => 'checkbox',
                'label'   => __('Enable real-time payment verification via API.', 'mcb-juice-qr-gateway'),
                'default' => 'no',
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
                <p class="qr-code-preview" style="margin-top: 10px;">
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
                <p class="bank-logo-preview" style="margin-top: 10px;">
                    <img src="<?php echo $bank_logo; ?>" style="max-width: 200px; display: block;">
                </p>
            <?php endif; ?>
            <p class="description"><?php _e('Select a bank logo from the media library (recommended size: 50x50px).', 'mcb-juice-qr-gateway'); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Processes the admin options for the gateway.
     *
     * This method is responsible for validating and saving the settings
     * configured in the WooCommerce payment gateway settings page.
     * It includes validation for API URL and API Key if API verification is enabled.
     *
     * @return bool True if options are processed successfully, false otherwise.
     */
    public function process_admin_options() {
        $api_verification = $this->get_option('api_verification');
        if ($api_verification === 'yes') {
            $api_url = $_POST['woocommerce_mcb_juice_qr_gateway_premium_api_url'] ?? '';
            $api_key = $_POST['woocommerce_mcb_juice_qr_gateway_premium_api_key'] ?? '';
            if (empty($api_url) || empty($api_key)) {
                WC_Admin_Settings::add_error(__('API URL and API Key are required for API verification.', 'mcb-juice-qr-gateway'));
                return false;
            }
        }
        return parent::process_admin_options();
    }

    /**
     * Processes the payment for the given order.
     *
     * This method is called when a customer places an order using this payment gateway.
     * It sets the order status to 'on-hold', reduces stock levels, and schedules
     * an API verification event if enabled.
     *
     * @param int $order_id The ID of the order to process.
     * @return array An array containing the result and redirect URL.
     */
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        $order->update_status('on-hold', __('Awaiting QR payment confirmation.', 'mcb-juice-qr-gateway'));
        wc_reduce_stock_levels($order_id);

        if ($this->api_verification === 'yes') {
            wp_schedule_single_event(time() + 60, 'mcb_juice_qr_verify_payment', [$order_id]);
        }

        return [
            'result'   => 'success',
            'redirect' => $order->get_checkout_order_received_url(),
        ];
    }

    /**
     * Displays the payment fields on the checkout page.
     *
     * This method outputs the description of the payment gateway and,
     * if configured, displays a static QR code image or a message for dynamic QR code generation.
     *
     * @return void
     */
    public function payment_fields() {
        echo wpautop(wp_kses_post($this->description));
        if ($this->qr_code_type === 'static' && $this->qr_code_url) {
            echo '<img src="' . esc_url($this->qr_code_url) . '" alt="QR Code" style="max-width: 200px;">';
        } elseif ($this->qr_code_type === 'dynamic') {
            echo '<p>' . __('Please place your order to generate a QR code.', 'mcb-juice-qr-gateway') . '</p>';
        }
    }
}