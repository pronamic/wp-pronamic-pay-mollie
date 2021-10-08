<?php
/**
 * Mollie gateway test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use DateTimeImmutable;
use Pronamic\WordPress\Http\Factory;
use Pronamic\WordPress\Http\Response;
use Pronamic\WordPress\Http\Request;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Subscriptions\Subscription;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionInterval;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionPhase;
use Pronamic\WordPress\Pay\Subscriptions\SubscriptionsDataStoreCPT;
use WP_Http;
use WP_UnitTestCase;

/**
 * Gateway test.
 *
 * @author Reüel van der Steege
 * @version 2.0.9
 */
class GatewayTest extends WP_UnitTestCase {
	/**
	 * Gateway
	 *
	 * @var Gateway
	 */
	private $gateway;

	/**
	 * Config ID.
	 *
	 * @var int
	 */
	private $config_id = 1;

	/**
	 * Setup gateway test.
	 */
	public function setUp() {
		parent::setUp();

		$this->factory = new Factory();

		$this->factory->fake(
			'https://api.mollie.com/v2/methods',
			function( Request $request ) {
				$file = __DIR__ . '/../http/api-mollie-com-v2-methods.http';

				$body = $request->body();

				if ( \is_array( $body ) && \array_key_exists( 'sequenceType', $body ) ) {
					$sequence_type = $body['sequenceType'];

					$file = __DIR__ . \sprintf( '/../http/api-mollie-com-v2-methods-%s.http', $sequence_type );
				}

				return Response::array_from_file( $file );
			}
		);

		$this->set_gateway(
			array(
				'id'   => $this->config_id,
				'mode' => Gateway::MODE_TEST,
			)
		);
	}

	/**
	 * Set gateway.
	 *
	 * @param array $args Config settings arguments.
	 */
	private function set_gateway( $args = array() ) {
		$config = new Config();

		foreach ( $args as $key => $value ) {
			$config->{$key} = $value;

			if ( 'id' === $key ) {
				$this->config_id = $value;
			}
		}

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

		$this->assertInternalType( 'array', $issuers );

		// Check issuers array structure.
		if ( ! empty( $issuers ) ) {
			$this->assertInternalType( 'array', $issuers[0] );
			$this->assertArrayHasKey( 'options', $issuers[0] );
		}

		// Check gateway error if issuers is empty.
		if ( empty( $issuers ) ) {
			$this->assertInstanceOf( 'WP_Error', $this->gateway->error );
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
		$payment_methods = new PaymentMethods();

		// Check if payment method is known.
		$methods_reflection = new \ReflectionClass( get_class( $payment_methods ) );
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
		$payment_methods = new PaymentMethods();

		$methods_reflection = new \ReflectionClass( get_class( $payment_methods ) );
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
		$filter_home_url = function( $url ) use ( $home_url ) {
			return $home_url;
		};

		add_filter( 'home_url', $filter_home_url );

		$this->assertEquals( $expected, $this->gateway->get_webhook_url() );

		remove_filter( 'home_url', $filter_home_url );
	}

	/**
	 * Webhook URL data provider.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-includes/rest-api.php#L329-L400
	 * @return array
	 */
	public function webhook_url_provider() {
		$home_url = 'https://example.org/';

		$filter_home_url = function( $url ) use ( $home_url ) {
			return $home_url;
		};

		add_filter( 'home_url', $filter_home_url );

		$webhook_url = \rest_url( Integration::REST_ROUTE_NAMESPACE . '/webhook' );

		remove_filter( 'home_url', $filter_home_url );

		return array(
			array( $home_url, $webhook_url ),
			array( 'https://localhost/', null ),
			array( 'https://example.dev/', null ),
			array( 'https://example.local/', null ),
		);
	}

	/**
	 * Test get Mollie Customer ID for payment.
	 *
	 * @dataProvider get_customer_id_for_payment_provider
	 *
	 * @param int    $user_id                   Payment WordPress user ID.
	 * @param string $subscription_customer_id  Subscription Mollie customer ID.
	 * @param string $first_payment_customer_id First payment Mollie customer ID.
	 * @param bool   $expected                  Expected Mollie Customer ID.
	 */
	public function test_get_customer_id_for_payment( $user_id, $subscription_customer_id, $first_payment_customer_id, $expected ) {
		// Customer.
		$customer = new Customer();

		$customer->set_user_id( $user_id );

		// New payment.
		$payment                         = new Payment();
		$payment->config_id              = 1;
		$payment->subscription_source_id = null;

		$payment->set_customer( $customer );

		// Subscription.
		$subscription = new Subscription();

		$subscription->set_customer( $customer );

		$subscription->add_phase(
			new SubscriptionPhase(
				$subscription,
				new DateTimeImmutable(),
				new SubscriptionInterval( 'P30D' ),
				new Money( '10' )
			)
		);

		$payment->add_period( $subscription->new_period() );

		$payment->subscription = $subscription;

		pronamic_pay_plugin()->payments_data_store->create( $payment );

		// Set customer ID meta.
		$payment->set_meta( 'mollie_customer_id', $first_payment_customer_id );

		$payment->subscription->set_meta( 'mollie_customer_id', $subscription_customer_id );

		// Get customer ID for payment.
		$this->factory->fake( 'https://api.mollie.com/v2/customers/cst_8wmqcHMN4U', __DIR__ . '/../http/api-mollie-com-v2-customers-cst_8wmqcHMN4U.http' );
		$this->factory->fake( 'https://api.mollie.com/v2/customers/cst_8wmqcHMN4U_first', __DIR__ . '/../http/api-mollie-com-v2-customers-cst_8wmqcHMN4U_first.http' );
		$this->factory->fake( 'https://api.mollie.com/v2/customers/cst_8wmqcHMN4U_subscription', __DIR__ . '/../http/api-mollie-com-v2-customers-cst_8wmqcHMN4U_subscription.http' );

		$customer_id = $this->gateway->get_customer_id_for_payment( $payment );

		$this->assertEquals( $expected, $customer_id );
	}

	/**
	 * Data provider for getting Mollie Customer ID for payment.
	 *
	 * @return array
	 */
	public function get_customer_id_for_payment_provider() {
		$customer_id      = 'cst_8wmqcHMN4U';
		$cst_subscription = sprintf( '%s_subscription', $customer_id );
		$cst_first        = sprintf( '%s_first', $customer_id );

		return array(
			array( null, null, null, false ),
			array( true, null, null, false ),
			array( false, null, null, false ),
			array( 0, null, null, false ),
			array( 10, null, null, false ),
			array( 1, null, null, false ),
			array( 1, null, $cst_first, $cst_first ),
			array( 1, $cst_subscription, null, $cst_subscription ),
			array( 1, $cst_subscription, $cst_first, $cst_subscription ),
			array( '1', $cst_subscription, $cst_first, $cst_subscription ),
		);
	}

	/**
	 * Test copy customer id to wp user.
	 *
	 * @param int    $config_id   Payment gateway ID.
	 * @param int    $user_id     WordPress user ID.
	 * @param string $customer_id Mollie Customer ID.
	 * @param bool   $expected    Expected value.
	 *
	 * @dataProvider provider_copy_customer_id_to_wp_user
	 */
	public function test_copy_customer_id_to_wp_user( $config_id, $user_id, $customer_id, $expected ) {
		if ( $this->config_id !== $config_id ) {
			$this->set_gateway(
				array(
					'id'   => $config_id,
					'mode' => Gateway::MODE_TEST,
				)
			);
		}

		// New payment.
		$payment            = new Payment();
		$payment->config_id = 1;

		$customer = new Customer();
		$customer->set_user_id( $user_id );

		$payment->subscription = new Subscription();
		$payment->subscription->set_id( 1 );
		$payment->subscription->set_customer( $customer );

		$subscriptions_data_store = new SubscriptionsDataStoreCPT();
		$subscriptions_data_store->update( $payment->subscription );

		$payment->subscription->set_meta( 'mollie_customer_id', $customer_id );

		$this->gateway->copy_customer_id_to_wp_user( $payment );

		// Get customer ID from user meta.
		$user_customer_ids = $this->gateway->get_customer_ids_for_user( $user_id );

		$this->assertInternalType( 'array', $user_customer_ids );

		if ( is_string( $expected ) ) {
			$this->assertContains( $expected, $user_customer_ids );
		}
	}

	/**
	 * Data provider for test copying Mollie customer ID to WordPress user.
	 *
	 * @return array
	 */
	public function provider_copy_customer_id_to_wp_user() {
		return array(
			// Config ID not equal to payment config ID.
			array( 0, 1, 'cst_8wmqcHMN4U', null ),

			// Valid WordPress user ID and Mollie customer ID.
			array( 1, 1, 'cst_8wmqcHMN4U', 'cst_8wmqcHMN4U' ),

			// Invalid WordPress user ID and Mollie customer ID.
			array( 1, 0, 0, false ),
			array( 1, null, null, false ),
			array( 1, true, true, false ),
			array( 1, false, false, false ),
			array( 1, '', '', false ),

			// Valid WordPress user ID and invalid Mollie customer ID.
			array( 1, 1, 0, false ),
			array( 1, 1, null, false ),
			array( 1, 1, true, false ),
			array( 1, 1, false, false ),
			array( 1, 1, '', false ),

			// Invalid WordPress user ID and valid Mollie customer ID.
			array( 1, 0, 'cst_8wmqcHMN4U', false ),
			array( 1, null, 'cst_8wmqcHMN4U', false ),
			array( 1, true, 'cst_8wmqcHMN4U', false ),
			array( 1, false, 'cst_8wmqcHMN4U', false ),
			array( 1, '', 'cst_8wmqcHMN4U', false ),
		);
	}
}
