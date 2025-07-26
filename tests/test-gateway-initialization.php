<?php
/**
 * Class Test_MCB_Juice_QR_Gateway_Initialization
 *
 * @package MCB_Juice_QR_Payment_Gateway_Pro
 */

/**
 * Test cases for MCB Juice QR Gateway Initialization.
 */
class Test_MCB_Juice_QR_Gateway_Initialization extends WP_UnitTestCase {

    /**
     * Test if the MCB Juice QR Payment Gateway is registered.
     */
    public function test_gateway_is_registered() {
        // Ensure WooCommerce is active for the test.
        if (!class_exists('WooCommerce')) {
            $this->markTestSkipped('WooCommerce is not active.');
        }

        // Call the function that initializes the gateway.
        mcb_juice_qr_init_gateway();

        // Get all registered payment gateways.
        $gateways = WC_Payment_Gateways::instance()->payment_gateways();

        // Assert that our gateway is among them.
        $this->assertArrayHasKey('mcb_juice_qr_gateway_premium', $gateways,
            'MCB Juice QR Payment Gateway Premium should be registered.');

        // Optionally, check if the registered gateway is an instance of our class.
        $this->assertInstanceOf('MCB_Juice_QR_Payment_Gateway_Premium', $gateways['mcb_juice_qr_gateway_premium'],
            'Registered gateway should be an instance of MCB_Juice_QR_Payment_Gateway_Premium.');
    }
}
