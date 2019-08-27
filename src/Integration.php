<?php
/**
 * Mollie integration.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Gateways\Common\AbstractIntegration;
use Pronamic\WordPress\Pay\Payments\Payment;
use WP_User;

/**
 * Title: Mollie integration
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Integration extends AbstractIntegration {
	/**
	 * Construct and intialize Mollie integration.
	 */
	public function __construct() {
		$this->id            = 'mollie';
		$this->name          = 'Mollie';
		$this->url           = 'http://www.mollie.com/en/';
		$this->product_url   = __( 'https://www.mollie.com/en/pricing', 'pronamic_ideal' );
		$this->dashboard_url = 'https://www.mollie.com/dashboard/';
		$this->register_url  = 'https://www.mollie.com/nl/signup/665327';
		$this->provider      = 'mollie';
		$this->supports      = array(
			'payment_status_request',
			'webhook',
			'webhook_log',
			'webhook_no_config',
		);

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

		add_filter( 'pronamic_payment_provider_url_mollie', array( $this, 'payment_provider_url' ), 10, 2 );
	}

	/**
	 * Get settings fields.
	 *
	 * @return array
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
	 * Save post.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_post_meta/
	 *
	 * @param int $post_id Post ID.
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
	 * @param WP_User $user WordPress user.
	 *
	 * @since 1.1.6
	 * @link https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
	 */
	public static function user_profile( $user ) {
		include __DIR__ . '/../views/html-admin-user-profile.php';
	}

	/**
	 * Payment provider URL.
	 *
	 * @param string  $url     Payment provider URL.
	 * @param Payment $payment Payment.
	 *
	 * @return string
	 */
	public function payment_provider_url( $url, Payment $payment ) {
		return sprintf(
			'https://www.mollie.com/dashboard/payments/%s',
			$payment->get_transaction_id()
		);
	}
	/**
	 * Get configuration by post ID.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return Config
	 */
	public function get_config( $post_id ) {
		$config = new Config();

		$config->id      = intval( $post_id );
		$config->api_key = $this->get_meta( $post_id, 'mollie_api_key' );
		$config->mode    = $this->get_meta( $post_id, 'mode' );

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
}
