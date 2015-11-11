<?php

class Pronamic_WP_Pay_Gateways_Mollie_Integration {
	public function __construct() {
		$this->id            = 'mollie';
		$this->name          = 'Mollie';
		$this->dashboard_url = 'http://www.mollie.nl/';
		$this->provider      = 'mollie';
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
}
