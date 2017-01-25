<?php

/**
 * Title: Mollie integration
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.11
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Integration extends Pronamic_WP_Pay_Gateways_AbstractIntegration {
	/**
	 * Dashboard URL.
	 *
	 * @var string
	 */
	var $dashboard_url = 'http://www.mollie.nl/beheer/';

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
		$function = array( 'Pronamic_WP_Pay_Gateways_Mollie_Listener', 'listen' );

		if ( ! has_action( 'wp_loaded', $function ) ) {
			add_action( 'wp_loaded', $function );
		}

		if ( is_admin() ) {
			add_action( 'show_user_profile', array( $this, 'user_profile' ) );
			add_action( 'edit_user_profile', array( $this, 'user_profile' ) );
		}

		add_filter( 'pronamic_payment_provider_url_mollie', array( $this, 'payment_provider_url' ), 10, 2 );
	}

	public function get_config_factory_class() {
		return 'Pronamic_WP_Pay_Gateways_Mollie_ConfigFactory';
	}

	public function get_settings_class() {
		return 'Pronamic_WP_Pay_Gateways_Mollie_Settings';
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
	public function user_profile( $user ) {
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
