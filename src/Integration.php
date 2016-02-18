<?php

/**
 * Title: Mollie integration
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.4
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Integration extends Pronamic_WP_Pay_Gateways_AbstractIntegration {
	public function __construct() {
		$this->id            = 'mollie';
		$this->name          = 'Mollie';
		$this->dashboard_url = 'http://www.mollie.nl/';
		$this->provider      = 'mollie';

		// Actions
		$function = array( 'Pronamic_WP_Pay_Gateways_Mollie_Listener', 'listen' );

		if ( ! has_action( 'wp_loaded', $function ) ) {
			add_action( 'wp_loaded', $function );
		}
	}

	public function get_config_factory_class() {
		return 'Pronamic_WP_Pay_Gateways_Mollie_ConfigFactory';
	}

	public function get_config_class() {
		return 'Pronamic_WP_Pay_Gateways_Mollie_Config';
	}

	public function get_settings_class() {
		return 'Pronamic_WP_Pay_Gateways_Mollie_Settings';
	}

	public function get_gateway_class() {
		return 'Pronamic_WP_Pay_Gateways_Mollie_Gateway';
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
}
