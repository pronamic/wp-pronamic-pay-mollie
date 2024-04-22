<?php
/**
 * Mollie gateway test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
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
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Gateway test.
 *
 * @author Reüel van der Steege
 * @version 2.0.9
 */
class GatewayTest extends TestCase {
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
	 * HTTP factory.
	 * 
	 * @var Factory
	 */
	private $factory;

	/**
	 * Setup gateway test.
	 */
	public function set_up() {
		parent::set_up();

		$this->factory = new Factory();

		$this->factory->fake( 'https://api.mollie.com/v2/methods/ideal?include=issuers', __DIR__ . '/../http/api-mollie-com-v2-methods-ideal.http' );

		$this->factory->fake( 'https://api.mollie.com/v2/methods?includeWallets=applepay&resource=payments&sequenceType=oneoff', __DIR__ . '/../http/api-mollie-com-v2-methods-oneoff.http' );
		$this->factory->fake( 'https://api.mollie.com/v2/methods?includeWallets=applepay&resource=payments&sequenceType=recurring', __DIR__ . '/../http/api-mollie-com-v2-methods-recurring.http' );
		$this->factory->fake( 'https://api.mollie.com/v2/methods?includeWallets=applepay&resource=payments&sequenceType=first', __DIR__ . '/../http/api-mollie-com-v2-methods-recurring.http' );
		$this->factory->fake( 'https://api.mollie.com/v2/methods?includeWallets=applepay&resource=orders&sequenceType=oneoff', __DIR__ . '/../http/api-mollie-com-v2-methods-oneoff.http' );
		$this->factory->fake( 'https://api.mollie.com/v2/methods?includeWallets=applepay&resource=orders&sequenceType=recurring', __DIR__ . '/../http/api-mollie-com-v2-methods-recurring.http' );
		$this->factory->fake( 'https://api.mollie.com/v2/methods?includeWallets=applepay&resource=orders&sequenceType=first', __DIR__ . '/../http/api-mollie-com-v2-methods-recurring.http' );

		$this->set_gateway(
			[
				'id' => $this->config_id,
			]
		);
	}

	/**
	 * Set gateway.
	 *
	 * @param array $args Config settings arguments.
	 */
	private function set_gateway( $args = [] ) {
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
	 * Test webhook url.
	 *
	 * @param string      $home_url Home URL.
	 * @param Payment     $payment  Payment.
	 * @param string|null $expected Expected value.
	 *
	 * @dataProvider webhook_url_provider
	 */
	public function test_webhook_url( $home_url, Payment $payment, $expected ) {
		$filter_home_url = function ( $url ) use ( $home_url ) {
			$url = $home_url;

			return $url;
		};

		add_filter( 'home_url', $filter_home_url );

		$this->assertEquals( $expected, $this->gateway->get_webhook_url( $payment ) );

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

		$filter_home_url = function ( $url ) use ( $home_url ) {
			$url = $home_url;

			return $url;
		};

		add_filter( 'home_url', $filter_home_url );

		// Payments resource.
		$payment = new Payment();

		$payment->set_id( 1 );

		$payments_webhook_url = \rest_url( Integration::REST_ROUTE_NAMESPACE . '/payments/webhook/1' );

		// Orders resource.
		$order_payment = new Payment();

		$order_payment->set_id( 1 );
		$order_payment->set_payment_method( PaymentMethods::KLARNA_PAY_LATER );
		$order_payment->set_source( 'memberpress_transaction' );

		$order_payment_webhook_url = \rest_url( Integration::REST_ROUTE_NAMESPACE . '/orders/webhook/1' );

		remove_filter( 'home_url', $filter_home_url );

		return [
			[ $home_url, $order_payment, $order_payment_webhook_url ],
			[ $home_url, $payment, $payments_webhook_url ],
			[ 'https://localhost/', $payment, null ],
			[ 'https://example.dev/', $payment, null ],
			[ 'https://example.local/', $payment, null ],
		];
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
	 *
	 * @group require-database
	 */
	public function test_get_customer_id_for_payment( $user_id, $subscription_customer_id, $first_payment_customer_id, $expected ) {
		// Customer.
		$customer = new Customer();

		$customer->set_user_id( $user_id );

		// New payment.
		$payment = new Payment();

		$payment->config_id = 1;

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

		pronamic_pay_plugin()->payments_data_store->create( $payment );

		// Set customer ID meta.
		$payment->set_meta( 'mollie_customer_id', $first_payment_customer_id );

		$subscription->set_meta( 'mollie_customer_id', $subscription_customer_id );

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

		return [
			[ null, null, null, false ],
			[ 0, null, null, false ],
			[ 10, null, null, false ],
			[ 20, null, null, false ],
			[ 21, $cst_subscription, null, $cst_subscription ],
			[ 22, $cst_subscription, $cst_first, $cst_subscription ],
		];
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
	 * @group require-database
	 */
	public function test_copy_customer_id_to_wp_user( $config_id, $user_id, $customer_id, $expected ) {
		if ( $this->config_id !== $config_id ) {
			$this->set_gateway(
				[
					'id' => $config_id,
				]
			);
		}

		// New payment.
		$payment = new Payment();

		$payment->config_id = 1;

		$customer = new Customer();
		$customer->set_user_id( $user_id );

		$subscription = new Subscription();
		$subscription->set_customer( $customer );

		$subscriptions_data_store = new SubscriptionsDataStoreCPT();
		$subscriptions_data_store->update( $subscription );

		$subscription->set_meta( 'mollie_customer_id', $customer_id );

		$payment->add_subscription( $subscription );

		$this->gateway->copy_customer_id_to_wp_user( $payment );

		// Get customer ID from user meta.
		$user_customer_ids = $this->gateway->get_customer_ids_for_user( $user_id );

		$this->assertIsArray( $user_customer_ids );

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
		return [
			// Config ID not equal to payment config ID.
			[ 0, 1, 'cst_8wmqcHMN4U', null ],

			// Valid WordPress user ID and Mollie customer ID.
			[ 1, 1, 'cst_8wmqcHMN4U', 'cst_8wmqcHMN4U' ],

			// Invalid WordPress user ID and Mollie customer ID.
			[ 1, 0, 0, false ],
			[ 1, null, null, false ],
			[ 1, true, true, false ],
			[ 1, false, false, false ],
			[ 1, '', '', false ],

			// Valid WordPress user ID and invalid Mollie customer ID.
			[ 1, 1, 0, false ],
			[ 1, 1, null, false ],
			[ 1, 1, true, false ],
			[ 1, 1, false, false ],
			[ 1, 1, '', false ],

			// Invalid WordPress user ID and valid Mollie customer ID.
			[ 1, 0, 'cst_8wmqcHMN4U', false ],
			[ 1, null, 'cst_8wmqcHMN4U', false ],
			[ 1, true, 'cst_8wmqcHMN4U', false ],
			[ 1, false, 'cst_8wmqcHMN4U', false ],
			[ 1, '', 'cst_8wmqcHMN4U', false ],
		];
	}
}
