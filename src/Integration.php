<?php
/**
 * Mollie integration.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\AbstractGatewayIntegration;
use Pronamic\WordPress\Pay\Payments\Payment;
use WP_User;

/**
 * Title: Mollie integration
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
 */
class Integration extends AbstractGatewayIntegration {
	/**
	 * Register URL.
	 *
	 * @var string
	 */
	public $register_url;

	/**
	 * Construct and intialize Mollie integration.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'id'            => 'mollie',
				'name'          => 'Mollie',
				'url'           => 'http://www.mollie.com/en/',
				'product_url'   => \__( 'https://www.mollie.com/en/pricing', 'pronamic_ideal' ),
				'dashboard_url' => 'https://www.mollie.com/dashboard/',
				'provider'      => 'mollie',
				'supports'      => array(
					'payment_status_request',
					'recurring_direct_debit',
					'recurring_credit_card',
					'recurring',
					'webhook',
					'webhook_log',
					'webhook_no_config',
				),
			)
		);

		parent::__construct( $args );

		// Actions.
		$function = array( __NAMESPACE__ . '\Listener', 'listen' );

		if ( ! has_action( 'wp_loaded', $function ) ) {
			add_action( 'wp_loaded', $function );
		}

		if ( is_admin() ) {
			$function = array( __CLASS__, 'user_profile' );

			if ( ! has_action( 'show_user_profile', $function ) ) {
				add_action( 'show_user_profile', $function );
			}

			if ( ! has_action( 'edit_user_profile', $function ) ) {
				add_action( 'edit_user_profile', $function );
			}
		}

		// Filters.
		$function = array( $this, 'next_payment_delivery_date' );

		if ( ! \has_filter( 'pronamic_pay_subscription_next_payment_delivery_date', $function ) ) {
			\add_filter( 'pronamic_pay_subscription_next_payment_delivery_date', $function, 10, 2 );
		}

		$function = array( $this, 'maybe_handle_oauth_authorization' );

		if ( ! \has_action( 'init', $function ) ) {
			\add_filter( 'init', $function );
		}

		add_filter( 'pronamic_payment_provider_url_mollie', array( $this, 'payment_provider_url' ), 10, 2 );

		// Tables.
		$this->register_tables();

		// Upgrades.
		$upgrades = $this->get_upgrades();

		$upgrades->add( new Upgrade300() );

		/**
		 * CLI.
		 *
		 * @link https://github.com/woocommerce/woocommerce/blob/3.9.0/includes/class-woocommerce.php#L453-L455
		 */
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$this->cli = new CLI();
		}
	}

	/**
	 * Register tables.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-includes/wp-db.php#L894-L937
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
	 * @param int|null $config_id Config ID.
	 * @return array<int, array<string, array<int, string>|int|string|true>>
	 */
	public function get_settings_fields( $config_id = null ) {
		$fields = array();

		$api_key = \get_post_meta( $config_id, '_pronamic_gateway_mollie_api_key', true );

		/*
		 * Mollie Connect.
		 */
		$fields[] = array(
			'section'  => 'general',
			'type'     => 'html',
			'callback' => function( $field ) use ( $config_id ) {
				$this->field_mollie_connect( $field, $config_id );
			},
		);

		if ( ! empty( $api_key ) ) {
			// API Key.
			$fields[] = array(
				'section'  => 'general',
				'filter'   => FILTER_SANITIZE_STRING,
				'methods'  => array( 'mollie-deprecated' ),
				'meta_key' => '_pronamic_gateway_mollie_api_key',
				'title'    => _x( 'API Key', 'mollie', 'pronamic_ideal' ),
				'type'     => 'text',
				'classes'  => array( 'regular-text', 'code' ),
				'tooltip'  => __( 'API key as mentioned in the payment provider dashboard', 'pronamic_ideal' ),
			);
		}

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
			'value'    => add_query_arg( 'mollie_webhook', '', home_url( '/' ) ),
			'readonly' => true,
			'tooltip'  => __( 'The Webhook URL as sent with each transaction to receive automatic payment status updates on.', 'pronamic_ideal' ),
		);

		return $fields;
	}

	/**
	 * Field mollie connect.
	 *
	 * @param array $field     Setting field.
	 * @param int   $config_id Config ID.
	 * @return void
	 */
	public function field_mollie_connect( $field, $config_id ) {
		// Authorize URL.
		$state = array(
			'redirect_uri' => \home_url(),
			'post_id'      => $config_id,
		);

		$authorize_url = \add_query_arg(
			array(
				'state' => \base64_encode( \wp_json_encode( $state ) ),
			),
			Connect::WP_PAY_MOLLIE_CONNECT_API_URL . 'oauth2/authorize/'
		);

		// Check connection.
		$connect = new Connect( $config_id );

		$connect->set_refresh_token( $this->get_meta( $config_id, 'mollie_refresh_token' ) );
		$connect->set_access_token( $this->get_meta( $config_id, 'mollie_access_token' ) );
		$connect->set_access_token_valid_until( $this->get_meta( $config_id, 'mollie_access_token_valid_until' ) );

		if ( $connect->is_access_token_valid() ) {
			printf(
				/* translators: %s: reconnect HTML link */
				\esc_html__( 'Connected with Mollie (%s)', 'pronamic_ideal' ),
				sprintf(
					'<a href="%1$s" title="%2$s">%2$s</a>',
					\esc_url( $authorize_url ),
					\esc_html__( 'reconnect', 'pronamiic_ideal' )
				)
			);

			return;
		}

		?>

		<p>
			<a href="<?php echo \esc_url( $authorize_url ); ?>" title="<?php echo \esc_attr_x( 'Connect via Mollie', 'mollie', 'pronamic_ideal' ); ?>">
				<?php

				printf(
					'<img src="%s" alt="%s" />',
					\esc_url( \plugins_url( '../images/mollie_connect.svg', __FILE__ ) ),
					\esc_html_x( 'Connect via Mollie', 'mollie', 'pronamic_ideal' )
				);

				?>
			</a>
		</p>

		<p>
			<em><?php esc_html_e( 'Click the Connect via Mollie button to authorize a Mollie account.', 'pronamic_ideal' ); ?></em>
		</p>

		<?php
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
	 * User profile.
	 *
	 * @since 1.1.6
	 * @link https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
	 * @param WP_User $user WordPress user.
	 * @return void
	 */
	public static function user_profile( $user ) {
		include __DIR__ . '/../views/html-admin-user-profile.php';
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

		$config->id                       = intval( $post_id );
		$config->api_key                  = $this->get_meta( $post_id, 'mollie_api_key' );
		$config->mode                     = $this->get_meta( $post_id, 'mode' );
		$config->due_date_days            = $this->get_meta( $post_id, 'mollie_due_date_days' );
		$config->refresh_token            = $this->get_meta( $post_id, 'mollie_refresh_token' );
		$config->access_token             = $this->get_meta( $post_id, 'mollie_access_token' );
		$config->access_token_valid_until = $this->get_meta( $post_id, 'mollie_access_token_valid_until' );

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
	 * @param \DateTime $next_payment_delivery_date Next payment delivery date.
	 * @param Payment   $payment                    Payment.
	 * @return \DateTime
	 */
	public function next_payment_delivery_date( \DateTime $next_payment_delivery_date, Payment $payment ) {
		$config_id = $payment->get_config_id();

		if ( null === $config_id ) {
			return $next_payment_delivery_date;
		}

		// Check gateway.
		$gateway_id = \get_post_meta( $config_id, '_pronamic_gateway_id', true );

		if ( 'mollie' !== $gateway_id ) {
			return $next_payment_delivery_date;
		}

		// Check direct debit payment method.
		$method = $payment->get_method();

		if ( null === $method ) {
			return $next_payment_delivery_date;
		}

		if ( ! PaymentMethods::is_direct_debit_method( $method ) ) {
			return $next_payment_delivery_date;
		}

		// Check subscription.
		$subscription = $payment->get_subscription();

		if ( null === $subscription ) {
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

	/**
	 * Maybe handle Mollie oAuth authorization.
	 *
	 * @return void
	 */
	public function maybe_handle_oauth_authorization() {
		if ( ! filter_has_var( \INPUT_GET, 'code' ) ) {
			return;
		}

		if ( ! filter_has_var( \INPUT_GET, 'state' ) ) {
			return;
		}

		$code  = filter_input( \INPUT_GET, 'code', \FILTER_SANITIZE_STRING );
		$state = filter_input( \INPUT_GET, 'state', \FILTER_SANITIZE_STRING );

		if ( empty( $code ) || empty( $state ) ) {
			return;
		}

		$state_data = json_decode( base64_decode( $state ) );

		if ( ! is_object( $state_data ) ) {
			return;
		}

		if ( ! isset( $state_data->post_id ) ) {
			return;
		}

		if ( \filter_has_var( \INPUT_GET, 'error' ) && \filter_has_var( \INPUT_GET, 'error_description' ) ) {
			die( \esc_html( \filter_input( \INPUT_GET, 'error_description' ) ) );
		}

		$connect = new Connect( $state_data->post_id );

		$connect->handle_authorization( $code, $state_data );
	}
}
