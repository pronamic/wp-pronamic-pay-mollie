<?php
/**
 * Mollie gateway test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Pay\Gateways\Mollie\Config;
use Pronamic\WordPress\Pay\Gateways\Mollie\Gateway;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use WP_UnitTestCase;

class GatewayTest extends WP_UnitTestCase {
	/**
	 * Gateway
	 *
	 * @var Gateway
	 */
	private $gateway;

	/**
	 * Setup gateway test.
	 */
	public function setUp() {
		parent::setUp();

		$config = new Config();

		$config->id   = 1;
		$config->mode = 'test';

		$this->gateway = new Gateway( $config );
	}

	/**
	 * Test get issuers type.
	 */
	public function test_get_issuers_type() {
		$issuers = $this->gateway->get_issuers();

		$this->assertInternalType( 'array', $issuers );
	}

	/**
	 * Test for issuers array structure.
	 *
	 * @depends test_get_issuers_type
	 */
	public function test_get_issuers_structure() {
		$issuers = $this->gateway->get_issuers();

		// Check issuers array structure.
		if ( ! empty( $issuers ) ) {
			$this->assertInternalType( 'array', $issuers[0] );
			$this->assertArrayHasKey( 'options', $issuers[0] );
		}
	}

	/**
	 * Test if gateway error is set when there are no issuers.
	 */
	public function test_get_issuers_error() {
		$issuers = $this->gateway->get_issuers();

		// Assert instance of WP_Error.
		if ( empty( $issuers ) ) {
			$error = $this->gateway->get_error();

			$this->assertInstanceOf( 'WP_Error', $error );
		}
	}

	/**
	 * Test supported payment methods array type.
	 */
	public function test_get_supported_payment_methods_type() {
		$supported = $this->gateway->get_supported_payment_methods();

		// Assert payment methods array type.
		$this->assertInternalType( 'array', $supported );
	}

	/**
	 * Test if supported payment methods are valid core payment methods.
	 *
	 * @depends test_get_supported_payment_methods_type
	 */
	public function test_get_supported_payment_methods_valid() {
		// Check if payment method is known.
		$methods_reflection = new \ReflectionClass( __NAMESPACE__ . '\Core\PaymentMethods' );
		$methods            = $methods_reflection->getConstants();

		$supported = $this->gateway->get_supported_payment_methods();

		foreach ( $supported as $method ) {
			$this->assertContains( $method, $methods );
		}
	}

	/**
	 * Test available payment methods array type.
	 */
	public function test_get_available_payment_methods_type() {
		$available = $this->gateway->get_available_payment_methods();

		$this->assertInternalType( 'array', $available );
	}

	/**
	 * Test if available payment methods are valid core payment methods.
	 *
	 * @depends test_get_available_payment_methods_type
	 */
	public function test_get_available_payment_methods_valid() {
		// Check if available payment methods are known.
		$methods_reflection = new \ReflectionClass( __NAMESPACE__ . '\Core\PaymentMethods' );
		$methods            = $methods_reflection->getConstants();

		$available = $this->gateway->get_available_payment_methods();

		foreach ( $available as $method ) {
			$this->assertContains( $method, $methods );
		}
	}

	/**
	 * Test webhook url.
	 *
	 * @param string      $home_url Home URL.
	 * @param string|null $expected Expected value.
	 *
	 * @dataProvider webhook_url_provider
	 */
	public function test_webhook_url( $home_url, $expected ) {
		$this->home_url = $home_url;

		add_filter( 'home_url', array( $this, 'home_url' ), 10, 1 );

		$this->assertEquals( $expected, $this->gateway->get_webhook_url() );
	}

	/**
	 * Webhook URL data provider.
	 *
	 * @return array
	 */
	public function webhook_url_provider() {
		return array(
			array( 'https://example.org/', 'https://example.org/?mollie_webhook' ),
			array( 'https://localhost/', null ),
			array( 'https://example.dev/', null ),
			array( 'https://example.local/', null ),
		);
	}

	/**
	 * Filter `home_url` callback.
	 *
	 * @return mixed
	 */
	public function home_url() {
		return $this->home_url;
	}

	/**
	 * Test copy customer id to wp user.
	 *
	 * @param $user_id
	 * @param $customer_id
	 * @param $expected
	 *
	 * @dataProvider test_copy_customer_id_to_wp_user_provider
	 */
	public function test_copy_customer_id_to_wp_user( $user_id, $customer_id, $expected ) {
		// New payment.
		$payment            = new Payment();
		$payment->config_id = 1;

		$payment->subscription          = new Subscription();
		$payment->subscription->user_id = $user_id;
		$payment->subscription->set_id( 1 );

		$subscriptions_data_store = new Subscriptions\SubscriptionsDataStoreCPT();
		$subscriptions_data_store->update( $payment->subscription );

		$payment->subscription->set_meta( 'mollie_customer_id', $customer_id );

		$this->gateway->copy_customer_id_to_wp_user( $payment );

		// Get customer ID from user meta.
		$user_customer_id = $this->gateway->get_customer_id_by_wp_user_id( $user_id );

		$this->assertEquals( $expected, $user_customer_id );
	}

	/**
	 * Data provider for test copying Mollie customer ID to WordPress user.
	 *
	 * @return array
	 */
	public function test_copy_customer_id_to_wp_user_provider() {
		return array(
			// Invalid WordPress user ID and valid Mollie customer ID.
			array( 1, 'cst_8wmqcHMN4U', 'cst_8wmqcHMN4U' ),

			// Invalid WordPress user ID and Mollie customer ID.
			array( 0, 0, false ),
			array( null, null, false ),
			array( true, true, false ),
			array( false, false, false ),
			array( '', '', false ),

			// Valid WordPress user ID and invalid Mollie customer ID.
			array( 1, 0, false ),
			array( 1, null, false ),
			array( 1, true, false ),
			array( 1, false, false ),
			array( 1, '', false ),

			// Invalid WordPress user ID and valid Mollie customer ID.
			array( 0, 'cst_8wmqcHMN4U', false ),
			array( 0, 'cst_8wmqcHMN4U', false ),
			array( 0, 'cst_8wmqcHMN4U', false ),
			array( 0, 'cst_8wmqcHMN4U', false ),
			array( 0, 'cst_8wmqcHMN4U', false ),
		);
	}
}
