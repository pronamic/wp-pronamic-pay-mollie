<?php
/**
 * Mollie integration.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\DateTime\DateTime;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\AbstractGatewayIntegration;
use Pronamic\WordPress\Pay\Payments\Payment;
use Pronamic\WordPress\Pay\Subscriptions\Subscription as CoreSubscription;

/**
 * Title: Mollie integration
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.4
 * @since   1.0.0
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
	 * Construct and intialize Mollie integration.
	 *
	 * @param array<string, array> $args Arguments.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'id'                     => 'mollie',
				'name'                   => 'Mollie',
				'version'                => '2.1.0',
				'url'                    => 'http://www.mollie.com/en/',
				'product_url'            => \__( 'https://www.mollie.com/en/pricing', 'pronamic_ideal' ),
				'dashboard_url'          => 'https://www.mollie.com/dashboard/',
				'provider'               => 'mollie',
				'supports'               => array(
					'payment_status_request',
					'recurring_direct_debit',
					'recurring_credit_card',
					'recurring',
					'refunds',
					'webhook',
					'webhook_log',
					'webhook_no_config',
				),
				'version_option_name'    => 'pronamic_pay_mollie_version',
				'db_version_option_name' => 'pronamic_pay_mollie_db_version',
			)
		);

		parent::__construct( $args );

		// Filters.
		$function = array( $this, 'next_payment_delivery_date' );

		if ( ! \has_filter( 'pronamic_pay_subscription_next_payment_delivery_date', $function ) ) {
			\add_filter( 'pronamic_pay_subscription_next_payment_delivery_date', $function, 10, 2 );
		}

		add_filter( 'pronamic_payment_provider_url_mollie', array( $this, 'payment_provider_url' ), 10, 2 );

		// Tables.
		$this->register_tables();

		/**
		 * Install.
		 */
		new Install( $this );

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
		$fields = array();

		// API Key.
		$fields[] = array(
			'section'  => 'general',
			'filter'   => FILTER_SANITIZE_STRING,
			'meta_key' => '_pronamic_gateway_mollie_api_key',
			'title'    => _x( 'API Key', 'mollie', 'pronamic_ideal' ),
			'type'     => 'text',
			'classes'  => array( 'regular-text', 'code' ),
			'tooltip'  => __( 'API key as mentioned in the payment provider dashboard', 'pronamic_ideal' ),
		);

		// Due date days.
		$fields[] = array(
			'section'     => 'advanced',
			'filter'      => \FILTER_SANITIZE_NUMBER_INT,
			'meta_key'    => '_pronamic_gateway_mollie_due_date_days',
			'title'       => _x( 'Due date days', 'mollie', 'pronamic_ideal' ),
			'type'        => 'number',
			'min'         => 1,
			'max'         => 100,
			'classes'     => array( 'regular-text' ),
			'tooltip'     => __( 'Number of days after which a bank transfer payment expires.', 'pronamic_ideal' ),
			'description' => sprintf(
				/* translators: 1: <code>1</code>, 2: <code>100</code>, 3: <code>12</code> */
				__( 'Minimum %1$s and maximum %2$s days. Default: %3$s days.', 'pronamic_ideal' ),
				sprintf( '<code>%s</code>', '1' ),
				sprintf( '<code>%s</code>', '100' ),
				sprintf( '<code>%s</code>', '12' )
			),
		);

		// Webhook.
		$fields[] = array(
			'section'  => 'feedback',
			'title'    => __( 'Webhook URL', 'pronamic_ideal' ),
			'type'     => 'text',
			'classes'  => array( 'large-text', 'code' ),
			'value'    => rest_url( self::REST_ROUTE_NAMESPACE . '/webhook' ),
			'readonly' => true,
			'tooltip'  => __( 'The Webhook URL as sent with each transaction to receive automatic payment status updates on.', 'pronamic_ideal' ),
		);

		return $fields;
	}

	/**
	 * Save post.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_post_meta/
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_post( $post_id ) {
		$api_key = get_post_meta( $post_id, '_pronamic_gateway_mollie_api_key', true );

		if ( ! is_string( $api_key ) ) {
			return;
		}

		$api_key_prefix = substr( $api_key, 0, 4 );

		switch ( $api_key_prefix ) {
			case 'live':
				update_post_meta( $post_id, '_pronamic_gateway_mode', Gateway::MODE_LIVE );

				return;
			case 'test':
				update_post_meta( $post_id, '_pronamic_gateway_mode', Gateway::MODE_TEST );

				return;
		}
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
			'https://www.mollie.com/dashboard/payments/%s',
			$transaction_id
		);
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
		$config->mode          = $this->get_meta( $post_id, 'mode' );
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
		return new Gateway( $this->get_config( $post_id ) );
	}

	/**
	 * Next payment delivery date.
	 *
	 * @param DateTime         $next_payment_delivery_date Next payment delivery date.
	 * @param CoreSubscription $subscription               Subscription.
	 * @return DateTime
	 */
	public function next_payment_delivery_date( DateTime $next_payment_delivery_date, CoreSubscription $subscription ) {
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
		$method = $subscription->payment_method;

		if ( null === $method ) {
			return $next_payment_delivery_date;
		}

		if ( ! PaymentMethods::is_direct_debit_method( $method ) ) {
			return $next_payment_delivery_date;
		}

		// Base delivery date on next payment date.
		$next_payment_date = $subscription->get_next_payment_date();

		if ( null === $next_payment_date ) {
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
				$next_payment_delivery_date->modify( '-3 days' );

				break;
			case 'Saturday':
				$next_payment_delivery_date->modify( '-2 days' );

				break;
			case 'Sunday':
				$next_payment_delivery_date->modify( '-3 days' );

				break;
			default:
				$next_payment_delivery_date->modify( '-1 day' );

				break;
		}

		$next_payment_delivery_date->setTime( 0, 0, 0 );

		return $next_payment_delivery_date;
	}
}
