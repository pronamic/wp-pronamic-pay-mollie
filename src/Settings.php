<?php

/**
 * Title: Mollie gateway settings
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.5
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Gateways_Mollie_Settings extends Pronamic_WP_Pay_GatewaySettings {
	public function __construct() {
		add_filter( 'pronamic_pay_gateway_sections', array( $this, 'sections' ) );
		add_filter( 'pronamic_pay_gateway_fields', array( $this, 'fields' ) );
	}

	public function sections( array $sections ) {
		// iDEAL
		$sections['mollie'] = array(
			'title'       => __( 'Mollie', 'pronamic_ideal' ),
			'methods'     => array( 'mollie' ),
			'description' => __( 'Account details are provided by the payment provider after registration. These settings need to match with the payment provider dashboard.', 'pronamic_ideal' ),
		);

		// Transaction eedback
		$sections['mollie_feedback'] = array(
			'title'       => __( 'Transaction feedback', 'pronamic_ideal' ),
			'methods'     => array( 'mollie' ),
			'description' => __( 'Payment status updates will be processed without any additional configuration. The <em>Webhook URL</em> is being used to receive the status updates.', 'pronamic_ideal' ),
		);

		// Return sections
		return $sections;
	}

	public function fields( array $fields ) {
		// API Key
		$fields[] = array(
			'filter'      => FILTER_SANITIZE_STRING,
			'section'     => 'mollie',
			'meta_key'    => '_pronamic_gateway_mollie_api_key',
			'title'       => _x( 'API Key', 'mollie', 'pronamic_ideal' ),
			'type'        => 'text',
			'classes'     => array( 'regular-text', 'code' ),
			'methods'     => array( 'mollie' ),
			'tooltip'     => __( 'API key as mentioned in the payment provider dashboard', 'pronamic_ideal' ),
		);

		// Transaction feedback
		$fields[] = array(
			'section'     => 'mollie',
			'title'       => __( 'Transaction feedback', 'pronamic_ideal' ),
			'type'        => 'description',
			'html'        => sprintf(
				'<span class="dashicons dashicons-yes"></span> %s',
				__( 'Payment status updates will be processed without any additional configuration.', 'pronamic_ideal' )
			),
		);

		// Webhook
		$fields[] = array(
			'section'     => 'mollie_feedback',
			'title'       => __( 'Webhook URL', 'pronamic_ideal' ),
			'type'        => 'text',
			'classes'     => array( 'large-text', 'code' ),
			'value'       => add_query_arg( 'mollie_webhook', '', home_url( '/' ) ),
			'readonly'    => true,
			'methods'     => array( 'mollie' ),
			'tooltip'     => __( 'The Webhook URL as sent with each transaction to receive automatic payment status updates on.', 'pronamic_ideal' ),
		);

		// Return fields
		return $fields;
	}
}
