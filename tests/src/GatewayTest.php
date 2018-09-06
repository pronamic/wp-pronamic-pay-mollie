<?php
/**
 * GatewayTest.php.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Gateways\Mollie\Config;
use Pronamic\WordPress\Pay\Gateways\Mollie\Gateway;
use Pronamic\WordPress\Pay\Payments\Payment;

class GatewayTest extends \WP_UnitTestCase {
	/**
	 * Gateway
	 *
	 * @var Gateway
	 */
	private $gateway;

	public function setUp() {
		$config = new Config();

		$this->gateway = new Gateway( $config );
	}

	public function test_get_supported_payment_methods() {
		$supported = $this->gateway->get_supported_payment_methods();

		// Assert payment methods array type.
		$this->assertInternalType( 'array', $supported );

		// Check if payment method is known.
		$payment_methods_reflection = new \ReflectionClass( __NAMESPACE__ . '\Core\PaymentMethods' );
		$payment_methods            = $payment_methods_reflection->getConstants();

		foreach ( $supported as $method ) {
			$this->assertContains( $method, $payment_methods );
		}
	}

	public function test_get_issuers() {
		$issuers = $this->gateway->get_issuers();

		$this->assertInternalType( 'array', $issuers );

		if ( ! is_array( $issuers ) ) {
			return;
		}

		// Check issuers array format.
		if ( ! empty( $issuers ) ) {
			$this->assertInternalType( 'array', $issuers[0] );
			$this->assertArrayHasKey( 'options', $issuers[0] );
		}

		// Assert instance of WP_Error.
		if ( empty( $issuers ) ) {
			$error = $this->gateway->get_error();

			$this->assertInstanceOf( 'WP_Error', $error );
		}
	}

	public function test_get_available_payment_methods() {
		$available = $this->gateway->get_available_payment_methods();

		$this->assertInternalType( 'array', $available );

		// Check if payment method is known.
		$payment_methods_reflection = new \ReflectionClass( __NAMESPACE__ . '\Core\PaymentMethods' );
		$payment_methods            = $payment_methods_reflection->getConstants();

		foreach ( $available as $method ) {
			$this->assertContains( $method, $payment_methods );
		}
	}

	/**
	 * Test webhook url.
	 *
	 * @param $home_url
	 * @param $expected
	 *
	 * @dataProvider webhook_home_url_provider
	 */
	public function test_webhook_url( $home_url, $expected ) {
		$this->home_url = $home_url;

		add_filter( 'home_url', array( $this, 'home_url' ), 10, 1 );

		$this->assertEquals( $expected, $this->gateway->get_webhook_url() );
	}

	public function webhook_home_url_provider() {
		return array(
			array( 'https://example.org/', 'https://example.org/?mollie_webhook' ),
			array( 'https://localhost/', null ),
			array( 'https://example.dev/', null ),
			array( 'https://example.local/', null ),
		);
	}

	public function home_url() {
		return $this->home_url;
	}
}
