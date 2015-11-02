<?php

/**
 * Title: Mollie gateway settings
 * Description:
 * Copyright: Copyright (c) 2005 - 2015
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.2.0
 * @since 1.2.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_GatewaySettings extends Pronamic_WP_Pay_Admin_GatewaySettings {
	public function __construct() {
		add_filter( 'pronamic_pay_gateway_sections', array( $this, 'sections' ) );
		add_filter( 'pronamic_pay_gateway_fields', array( $this, 'fields' ) );
	}

	public function sections( array $sections ) {
		// iDEAL
		$sections['mollie'] = array(
			'title'   => __( 'Mollie', 'pronamic_ideal' ),
			'methods' => array( 'mollie' ),
		);

		// Return
		return $sections;
	}

	public function fields( array $fields ) {
		// API Key
		$fields[] = array(
			'section'     => 'mollie',
			'meta_key'    => '_pronamic_gateway_mollie_api_key',
			'title'       => _x( 'API Key', 'mollie', 'pronamic_ideal' ),
			'type'        => 'text',
			'classes'     => array( 'regular-text', 'code' ),
			'methods'     => array( 'mollie' ),
		);

		// Webhook
		$fields[] = array(
			'section'     => 'mollie',
			'title'       => __( 'Webhook', 'pronamic_ideal' ),
			'type'        => 'text',
			'classes'     => array( 'large-text', 'code' ),
			'value'       => add_query_arg( 'mollie_webhook', '', home_url( '/' ) ),
			'readonly'    => true,
			'methods'     => array( 'mollie' ),
		);

		// Return
		return $fields;
	}
}
