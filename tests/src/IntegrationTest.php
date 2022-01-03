<?php
/**
 * Mollie integration test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Payments\Payment;
use WP_UnitTestCase;

/**
 * Integration test.
 *
 * @author Reüel van der Steege
 * @version 2.0.5
 */
class IntegrationTest extends WP_UnitTestCase {
	/**
	 * Integration.
	 *
	 * @var Integration
	 */
	public $integration;

	/**
	 * Setup.
	 */
	public function setUp() {
		$this->integration = new Integration();
	}

	/**
	 * Test get ID.
	 */
	public function test_get_id() {
		$this->integration->set_id( 'test-id' );

		$this->assertEquals( 'test-id', $this->integration->get_id() );
	}

	/**
	 * Test get name function
	 */
	public function test_get_name() {
		$this->integration->set_name( 'Test Name' );

		$this->assertEquals( 'Test Name', $this->integration->get_name() );
	}

	/**
	 * Test settings.
	 */
	public function test_get_settings() {
		$settings = $this->integration->get_settings();

		$this->assertInternalType( 'array', $settings );
	}

	/**
	 * Test payment provider URL.
	 */
	public function test_payment_provider_url() {
		$filter = sprintf(
			'pronamic_payment_provider_url_%s',
			$this->integration->get_id()
		);

		if ( ! has_filter( $filter, array( $this->integration, 'payment_provider_url' ) ) ) {
			return;
		}

		// New payment.
		$payment = new Payment();

		$payment->set_transaction_id( 'test_99' );

		// Get provider URL.
		$url = $this->integration->payment_provider_url( '', $payment );

		// Validate.
		$is_valid = ( false !== filter_var( $url, FILTER_VALIDATE_URL ) );

		$this->assertTrue( $is_valid );
	}

	/**
	 * Test config.
	 */
	public function test_config() {
		$config = $this->integration->get_config( 99 );

		$this->assertInstanceOf( __NAMESPACE__ . '\Config', $config );
		$this->assertEquals( 99, $config->id );
	}

	/**
	 * Test gateway.
	 */
	public function test_gateway() {
		$gateway = $this->integration->get_gateway( 99 );

		$this->assertInstanceOf( __NAMESPACE__ . '\Gateway', $gateway );
	}

	/**
	 * Test settings.
	 */
	public function test_settings() {
		$this->assertInternalType( 'array', $this->integration->get_settings_fields() );
	}
}
