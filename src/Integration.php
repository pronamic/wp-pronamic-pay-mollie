<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Gateways\Common\AbstractIntegration;

/**
 * Title: Mollie integration
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class Integration extends AbstractIntegration {
	/**
	 * Dashboard URL.
	 *
	 * @var string
	 */
	public $dashboard_url = 'http://www.mollie.nl/beheer/';

	/**
	 * Construct and intialize Mollie integration.
	 */
	public function __construct() {
		$this->id          = 'mollie';
		$this->name        = 'Mollie';
		$this->url         = 'http://www.mollie.com/en/';
		$this->product_url = __( 'https://www.mollie.com/en/pricing', 'pronamic_ideal' );
		$this->provider    = 'mollie';

		// Actions
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

	public function get_config_factory_class() {
		return __NAMESPACE__ . '\ConfigFactory';
	}

	public function get_settings_class() {
		return __NAMESPACE__ . '\Settings';
	}

	/**
	 * Get required settings for this integration.
	 *
	 * @see https://github.com/wp-premium/gravityforms/blob/1.9.16/includes/fields/class-gf-field-multiselect.php#L21-L42
	 * @since 1.1.3
	 * @return array
	 */
	public function get_settings() {
		$settings = parent::get_settings();

		$settings[] = 'mollie';

		return $settings;
	}

	/**
	 * User profile.
	 *
	 * @since 1.1.6
	 * @see https://github.com/WordPress/WordPress/blob/4.5.2/wp-admin/user-edit.php#L578-L600
	 */
	public static function user_profile( $user ) {
		include dirname( __FILE__ ) . '/../views/html-admin-user-profile.php';
	}

	/**
	 * Payment provider URL.
	 *
	 * @param string  $url
	 * @param Payment $payment
	 * @return string
	 */
	public function payment_provider_url( $url, $payment ) {
		return sprintf(
			'https://www.mollie.com/dashboard/payments/%s',
			$payment->get_transaction_id()
		);
	}
}
