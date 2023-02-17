<?php
/**
 * Mollie integration.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\AbstractGatewayIntegration;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Subscriptions\Subscription as CoreSubscription;

/**
 * Integration class
 */
class Integration extends AbstractGatewayIntegration {
	/**
	 * REST route namespace.
	 *
	 * @var string
	 */
	const REST_ROUTE_NAMESPACE = 'pronamic-pay/mollie/v1';

	/**
	 * Register URL.
	 *
	 * @var string
	 */
	public $register_url;

	/**
	 * Construct and initialize Mollie integration.
	 *
	 * @param array<string, string[]> $args Arguments.
	 */
	public function __construct( $args = [] ) {
		$args = wp_parse_args(
			$args,
			[
				'id'                  => 'mollie',
				'name'                => 'Mollie',
				'version'             => '2.1.0',
				'url'                 => 'https://www.mollie.com/en/',
				'product_url'         => \__( 'https://www.mollie.com/en/pricing', 'pronamic_ideal' ),
				'dashboard_url'       => 'https://my.mollie.com/dashboard/',
				'provider'            => 'mollie',
				'supports'            => [
					'payment_status_request',
					'recurring',
					'refunds',
					'webhook',
					'webhook_log',
					'webhook_no_config',
				],
				'version_option_name' => 'pronamic_pay_mollie_version',
			]
		);

		parent::__construct( $args );

		// Filters.
		$function = [ $this, 'next_payment_delivery_date' ];

		if ( ! \has_filter( 'pronamic_pay_subscription_next_payment_delivery_date', $function ) ) {
			\add_filter( 'pronamic_pay_subscription_next_payment_delivery_date', $function, 10, 2 );
		}

		$function = [ $this, 'http_request_args' ];

		if ( ! \has_filter( 'http_request_args', $function ) ) {
			// phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.http_request_args -- Timeout is not adjusted.
			\add_filter( 'http_request_args', $function, 10, 2 );
		}

		add_filter( 'pronamic_payment_provider_url_mollie', [ $this, 'payment_provider_url' ], 10, 2 );

		// Actions.
		$function = [ $this, 'scheduled_payment_start' ];

		if ( ! \has_action( 'pronamic_pay_mollie_payment_start', $function ) ) {
			\add_action( 'pronamic_pay_mollie_payment_start', $function, 10, 1 );
		}

		$function = [ $this, 'payment_fulfilled' ];

		if ( ! \has_action( 'pronamic_pay_payment_fulfilled', $function ) ) {
			\add_action( 'pronamic_pay_payment_fulfilled', $function, 10, 1 );
		}

		// Tables.
		$this->register_tables();

		// Upgrades.
		$version = $this->get_version();

		$upgrades = $this->get_upgrades();

		$upgrades->add( new Install( null === $version ? '1.0.0' : $version ) );

		/**
		 * Admin
		 */
		if ( \is_admin() ) {
			new Admin();
		}

		/**
		 * CLI.
		 *
		 * @link https://github.com/woocommerce/woocommerce/blob/3.9.0/includes/class-woocommerce.php#L453-L455
		 */
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			new CLI();
		}
	}

	/**
	 * Setup gateway integration.
	 *
	 * @return void
	 */
	public function setup() {
		// Check if dependencies are met and integration is active.
		if ( ! $this->is_active() ) {
			return;
		}

		// Webhook controller.
		$webhook_controller = new WebhookController();

		$webhook_controller->setup();
	}

	/**
	 * Register tables.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-includes/wp-db.php#L894-L937
	 * @return void
	 */
	private function register_tables() {
		global $wpdb;

		/**
		 * Tables.
		 */
		$wpdb->pronamic_pay_mollie_organizations  = $wpdb->base_prefix . 'pronamic_pay_mollie_organizations';
		$wpdb->pronamic_pay_mollie_profiles       = $wpdb->base_prefix . 'pronamic_pay_mollie_profiles';
		$wpdb->pronamic_pay_mollie_customers      = $wpdb->base_prefix . 'pronamic_pay_mollie_customers';
		$wpdb->pronamic_pay_mollie_customer_users = $wpdb->base_prefix . 'pronamic_pay_mollie_customer_users';
	}

	/**
	 * Get settings fields.
	 *
	 * @return array<int, array<string, callable|int|string|bool|array<int|string,int|string>>>
	 */
	public function get_settings_fields() {
		$fields = [];

		// API Key.
		$fields[] = [
			'section'  => 'general',
			'meta_key' => '_pronamic_gateway_mollie_api_key',
			'title'    => _x( 'API Key', 'mollie', 'pronamic_ideal' ),
			'type'     => 'text',
			'classes'  => [ 'regular-text', 'code' ],
			'tooltip'  => __( 'API key as mentioned in the payment provider dashboard', 'pronamic_ideal' ),
			'required' => true,
		];

		// Due date days.
		$fields[] = [
			'section'     => 'advanced',
			'filter'      => \FILTER_SANITIZE_NUMBER_INT,
			'meta_key'    => '_pronamic_gateway_mollie_due_date_days',
			'title'       => _x( 'Due date days', 'mollie', 'pronamic_ideal' ),
			'type'        => 'number',
			'min'         => 1,
			'max'         => 100,
			'classes'     => [ 'regular-text' ],
			'tooltip'     => __( 'Number of days after which a bank transfer payment expires.', 'pronamic_ideal' ),
			'description' => sprintf(
				/* translators: 1: <code>1</code>, 2: <code>100</code>, 3: <code>12</code> */
				__( 'Minimum %1$s and maximum %2$s days. Default: %3$s days.', 'pronamic_ideal' ),
				sprintf( '<code>%s</code>', '1' ),
				sprintf( '<code>%s</code>', '100' ),
				sprintf( '<code>%s</code>', '12' )
			),
		];

		// Webhook.
		$fields[] = [
			'section'  => 'feedback',
			'title'    => __( 'Webhook URL', 'pronamic_ideal' ),
			'type'     => 'text',
			'classes'  => [ 'large-text', 'code' ],
			'value'    => rest_url( self::REST_ROUTE_NAMESPACE . '/webhook' ),
			'readonly' => true,
			'tooltip'  => __( 'The Webhook URL as sent with each transaction to receive automatic payment status updates on.', 'pronamic_ideal' ),
		];

		return $fields;
	}

	/**
	 * Payment provider URL.
	 *
	 * @param string|null $url     Payment provider URL.
	 * @param Payment     $payment Payment.
	 * @return string|null
	 */
	public function payment_provider_url( $url, Payment $payment ) {
		$transaction_id = $payment->get_transaction_id();

		if ( null === $transaction_id ) {
			return $url;
		}

		return sprintf(
			'https://my.mollie.com/dashboard/payments/%s',
			$transaction_id
		);
	}

	/**
	 * Save post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_post( $post_id ) {
		$config = $this->get_config( $post_id );

		\delete_transient( 'pronamic_pay_mollie_payment_methods_' . \md5( (string) \wp_json_encode( $config ) ) );
		\delete_transient( 'pronamic_pay_ideal_issuers_' . \md5( (string) \wp_json_encode( $config ) ) );
	}

	/**
	 * Get configuration by post ID.
	 *
	 * @param int $post_id Post ID.
	 * @return Config
	 */
	public function get_config( $post_id ) {
		$config = new Config();

		$config->id            = intval( $post_id );
		$config->api_key       = $this->get_meta( $post_id, 'mollie_api_key' );
		$config->due_date_days = $this->get_meta( $post_id, 'mollie_due_date_days' );

		return $config;
	}

	/**
	 * Get gateway.
	 *
	 * @param int $post_id Post ID.
	 * @return Gateway
	 */
	public function get_gateway( $post_id ) {
		$gateway = new Gateway( $this->get_config( $post_id ) );

		$gateway->set_mode( $gateway->get_mode() );

		return $gateway;
	}

	/**
	 * Start scheduled payment.
	 *
	 * @param int $payment_id Payment ID.
	 * @return void
	 * @throws \Exception Throws exception after four failed attempts.
	 */
	public function scheduled_payment_start( $payment_id ) {
		// Check payment.
		$payment = \get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			return;
		}

		// Check gateway.
		$gateway = $payment->get_gateway();

		if ( null === $gateway ) {
			return;
		}

		// Attempt.
		$attempt = (int) $payment->get_meta( 'mollie_create_payment_attempt' );

		if ( $attempt > 4 ) {
			throw new \Exception( \sprintf( 'Could not create Mollie payment for %s after %s attempts.', $payment_id, $attempt ) );
		}

		// Start payment.
		$gateway->start( $payment );

		$payment->save();
	}

	/**
	 * Next payment delivery date.
	 *
	 * @param \DateTimeInterface $next_payment_delivery_date Next payment delivery date.
	 * @param CoreSubscription   $subscription               Subscription.
	 * @return \DateTimeInterface
	 */
	public function next_payment_delivery_date( \DateTimeInterface $next_payment_delivery_date, CoreSubscription $subscription ) {
		$config_id = $subscription->get_config_id();

		if ( null === $config_id ) {
			return $next_payment_delivery_date;
		}

		// Check gateway.
		$gateway_id = \get_post_meta( $config_id, '_pronamic_gateway_id', true );

		if ( 'mollie' !== $gateway_id ) {
			return $next_payment_delivery_date;
		}

		// Check direct debit payment method.
		$payment_method = $subscription->get_payment_method();

		if ( null === $payment_method ) {
			return $next_payment_delivery_date;
		}

		if ( ! in_array(
			$payment_method,
			[
				PaymentMethods::DIRECT_DEBIT,
				PaymentMethods::DIRECT_DEBIT_BANCONTACT,
				PaymentMethods::DIRECT_DEBIT_IDEAL,
				PaymentMethods::DIRECT_DEBIT_SOFORT,
			],
			true
		) ) {
			return $next_payment_delivery_date;
		}

		// Base delivery date on next payment date.
		$next_payment_date = $subscription->get_next_payment_date();

		if ( ! ( $next_payment_date instanceof \DateTimeImmutable ) ) {
			return $next_payment_delivery_date;
		}

		$next_payment_delivery_date = clone $next_payment_date;

		// Textual representation of the day of the week, Sunday through Saturday.
		$day_of_week = $next_payment_delivery_date->format( 'l' );

		/*
		 * Subtract days from next payment date for earlier delivery.
		 *
		 * @link https://help.mollie.com/hc/en-us/articles/115000785649-When-are-direct-debit-payments-processed-and-paid-out-
		 * @link https://help.mollie.com/hc/en-us/articles/115002540294-What-are-the-payment-methods-processing-times-
		 */
		switch ( $day_of_week ) {
			case 'Monday':
				$next_payment_delivery_date = $next_payment_delivery_date->modify( '-3 days' );

				break;
			case 'Saturday':
				$next_payment_delivery_date = $next_payment_delivery_date->modify( '-2 days' );

				break;
			case 'Sunday':
				$next_payment_delivery_date = $next_payment_delivery_date->modify( '-3 days' );

				break;
			default:
				$next_payment_delivery_date = $next_payment_delivery_date->modify( '-1 day' );

				break;
		}

		$next_payment_delivery_date = $next_payment_delivery_date->setTime( 0, 0, 0 );

		return $next_payment_delivery_date;
	}


	/**
	 * Get user agent value for requests to Mollie.
	 *
	 * @link https://github.com/pronamic/wp-pronamic-pay-mollie/issues/13
	 * @return string
	 */
	private function get_user_agent() {
		return implode(
			' ',
			[
				/**
				 * Pronamic Pay version.
				 *
				 * @link https://github.com/pronamic/pronamic-pay/issues/12
				 */
				'PronamicPay/' . \pronamic_pay_plugin()->get_version(),
				/**
				 * Pronamic - Mollie user agent token.
				 *
				 * @link https://github.com/pronamic/pronamic-pay/issues/12
				 */
				'uap/FyuVeDDqnKdzdry7',
				/**
				 * WordPress version.
				 *
				 * @link https://github.com/WordPress/WordPress/blob/f9db66d504fc72942515f6c0ed2b63aee7cef876/wp-includes/class-wp-http.php#L183-L192
				 */
				'WordPress/' . get_bloginfo( 'version' ) . '; ' . get_bloginfo( 'url' ),
			]
		);
	}

	/**
	 * Filters the arguments used in an HTTP request.
	 *
	 * @link https://developer.wordpress.org/reference/hooks/http_request_args/
	 * @link https://github.com/pronamic/wp-pronamic-pay-mollie/issues/13
	 * @param array  $args Arguments.
	 * @param string $url  URL.
	 */
	public function http_request_args( $args, $url ) {
		if ( ! \str_starts_with( $url, 'https://api.mollie.com/' ) ) {
			return $args;
		}

		$args['user-agent'] = $this->get_user_agent();

		return $args;
	}

	/**
	 * Payment fulfilled.
	 * 
	 * @param Payment $payment Payment.
	 * @return void
	 */
	public function payment_fulfilled( Payment $payment ): void {
		$gateway = $payment->get_gateway();

		if ( ! $gateway instanceof Gateway ) {
			return;
		}

		$gateway->maybe_create_shipment_for_payment( $payment );
	}
}
