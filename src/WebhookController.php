<?php
/**
 * Webhook controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Plugin;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Webhook controller
 *
 * @link https://docs.mollie.com/guides/webhooks
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class WebhookController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );

		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
	}

	/**
	 * REST API init.
	 *
	 * @link https://docs.mollie.com/overview/webhooks
	 * @link https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
	 * @link https://developer.wordpress.org/reference/hooks/rest_api_init/
	 *
	 * @return void
	 */
	public function rest_api_init() {
		\register_rest_route(
			Integration::REST_ROUTE_NAMESPACE,
			'/webhook',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_mollie_webhook' ),
				'args'                => array(
					'id' => array(
						'description' => \__( 'Mollie transaction ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					),
				),
				'permission_callback' => '__return_true',
			)
		);

		\register_rest_route(
			Integration::REST_ROUTE_NAMESPACE,
			'/webhook/(?P<payment_id>\d+)',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'rest_api_mollie_webhook_payment' ),
				'args'                => array(
					'payment_id' => array(
						'description' => \__( 'Payment ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					),
					'id'         => array(
						'description' => \__( 'Mollie transaction ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					),
				),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * REST API Mollie webhook handler.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return object
	 */
	public function rest_api_mollie_webhook( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );

		if ( empty( $id ) ) {
			return $this->rest_api_mollie_webhook_payment( $request );
		}

		$payment = \get_pronamic_payment_by_transaction_id( $id );

		if ( null !== $payment ) {
			$request->set_param( 'payment_id', $payment->get_id() );
		}

		return $this->rest_api_mollie_webhook_payment( $request );
	}

	/**
	 * REST API Mollie webhook handler.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return object
	 */
	public function rest_api_mollie_webhook_payment( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );

		/**
		 * Result.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
		 */
		$response = new WP_REST_Response(
			array(
				'success' => true,
				'id'      => $id,
			)
		);

		$response->add_link( 'self', rest_url( $request->get_route() ) );

		/**
		 * Payment.
		 */
		$payment_id = $request->get_param( 'payment_id' );

		if ( empty( $payment_id ) ) {
			return $response;
		}

		$payment = \get_pronamic_payment( $payment_id );

		if ( null === $payment ) {
			/**
			 * How to handle unknown IDs?
			 *
			 * To not leak any information to malicious third parties, it is recommended
			 * to return a 200 OK response even if the ID is not known to your system.
			 *
			 * @link https://docs.mollie.com/guides/webhooks#how-to-handle-unknown-ids
			 */
			return $response;
		}

		// Add note.
		$note = \sprintf(
			/* translators: %s: payment provider name */
			\__( 'Webhook requested by %s.', 'pronamic_ideal' ),
			\__( 'Mollie', 'pronamic_ideal' )
		);

		$payment->add_note( $note );

		// Log webhook request.
		\do_action( 'pronamic_pay_webhook_log_payment', $payment );

		// Update payment.
		Plugin::update_payment( $payment, false );

		return $response;
	}

	/**
	 * WordPress loaded, check for deprecated webhook call.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-includes/rest-api.php#L277-L309
	 * @return void
	 */
	public function wp_loaded() {
		if ( ! filter_has_var( INPUT_GET, 'mollie_webhook' ) ) {
			return;
		}

		if ( ! filter_has_var( INPUT_POST, 'id' ) ) {
			return;
		}

		\rest_get_server()->serve_request( '/pronamic-pay/mollie/v1/webhook' );

		exit;
	}
}
