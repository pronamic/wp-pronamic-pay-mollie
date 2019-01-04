<?php
/**
 * Mollie settings.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Core\GatewaySettings;

/**
 * Title: Mollie gateway settings
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.5
 * @since   1.0.0
 */
class Settings extends GatewaySettings {
	/**
	 * API key meta key.
	 *
	 * @var string
	 */
	const API_KEY_META_KEY = '_pronamic_gateway_mollie_api_key';

	/**
	 * Settings constructor.
	 */
	public function __construct() {
		add_filter( 'pronamic_pay_gateway_sections', array( $this, 'sections' ) );
		add_filter( 'pronamic_pay_gateway_fields', array( $this, 'fields' ) );
	}

	/**
	 * Settings sections.
	 *
	 * @param array $sections Sections.
	 *
	 * @return array
	 */
	public function sections( array $sections ) {
		// General.
		$sections['mollie'] = array(
			'title'       => __( 'Mollie', 'pronamic_ideal' ),
			'methods'     => array( 'mollie' ),
			'description' => __( 'Account details are provided by the payment provider after registration. These settings need to match with the payment provider dashboard.', 'pronamic_ideal' ),
		);

		// Transaction feedback.
		$sections['mollie_feedback'] = array(
			'title'       => __( 'Transaction feedback', 'pronamic_ideal' ),
			'methods'     => array( 'mollie' ),
			'description' => __( 'Payment status updates will be processed without any additional configuration. The <em>Webhook URL</em> is being used to receive the status updates.', 'pronamic_ideal' ),
		);

		return $sections;
	}

	/**
	 * Settings fields.
	 *
	 * @param array $fields Fields.
	 *
	 * @return array
	 */
	public function fields( array $fields ) {
		// API Key.
		$fields[] = array(
			'filter'   => FILTER_SANITIZE_STRING,
			'section'  => 'mollie',
			'meta_key' => self::API_KEY_META_KEY,
			'title'    => _x( 'API Key', 'mollie', 'pronamic_ideal' ),
			'type'     => 'text',
			'classes'  => array( 'regular-text', 'code' ),
			'methods'  => array( 'mollie' ),
			'tooltip'  => __( 'API key as mentioned in the payment provider dashboard', 'pronamic_ideal' ),
		);

		// Transaction feedback.
		$fields[] = array(
			'section' => 'mollie',
			'title'   => __( 'Transaction feedback', 'pronamic_ideal' ),
			'type'    => 'description',
			'html'    => sprintf(
				'<span class="dashicons dashicons-yes"></span> %s',
				__( 'Payment status updates will be processed without any additional configuration.', 'pronamic_ideal' )
			),
		);

		// Webhook.
		$fields[] = array(
			'section'  => 'mollie_feedback',
			'title'    => __( 'Webhook URL', 'pronamic_ideal' ),
			'type'     => 'text',
			'classes'  => array( 'large-text', 'code' ),
			'value'    => add_query_arg( 'mollie_webhook', '', home_url( '/' ) ),
			'readonly' => true,
			'methods'  => array( 'mollie' ),
			'tooltip'  => __( 'The Webhook URL as sent with each transaction to receive automatic payment status updates on.', 'pronamic_ideal' ),
		);

		return $fields;
	}

	/**
	 * Save post.
	 *
	 * @param array $data Data to save.
	 *
	 * @return array
	 */
	public function save_post( $data ) {
		// Set mode based on API key.
		if ( isset( $data[ self::API_KEY_META_KEY ], $data['_pronamic_gateway_mode'] ) ) {
			$api_key = trim( $data[ self::API_KEY_META_KEY ] );

			if ( empty( $api_key ) ) {
				$mode = $data['_pronamic_gateway_mode'];
			} elseif ( 'live_' === substr( $api_key, 0, 5 ) ) {
				$mode = Gateway::MODE_LIVE;
			} else {
				$mode = Gateway::MODE_TEST;
			}

			$data['_pronamic_gateway_mode'] = $mode;
		}

		return $data;
	}
}
