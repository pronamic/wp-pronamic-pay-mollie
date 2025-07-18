<?php
/**
 * Webhook controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Plugin;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Webhook controller class
 *
 * @link https://docs.mollie.com/guides/webhooks
 */
class WebhookController {
	/**
	 * Setup.
	 *
	 * @return void
	 */
	public function setup() {
		\add_action( 'rest_api_init', $this->rest_api_init( ... ) );

		\add_action( 'wp_loaded', $this->wp_loaded( ... ) );
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
	private function rest_api_init() {
		\register_rest_route(
			Integration::REST_ROUTE_NAMESPACE,
			'/webhook',
			[
				'methods'             => 'POST',
				'callback'            => $this->rest_api_mollie_webhook( ... ),
				'args'                => [
					'id' => [
						'description' => \__( 'Mollie transaction ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					],
				],
				'permission_callback' => fn() => true,
			]
		);

		\register_rest_route(
			Integration::REST_ROUTE_NAMESPACE,
			'/webhook/(?P<payment_id>\d+)',
			[
				'methods'             => 'POST',
				'callback'            => $this->rest_api_mollie_webhook_payment( ... ),
				'args'                => [
					'payment_id' => [
						'description' => \__( 'Payment ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					],
					'id'         => [
						'description' => \__( 'Mollie transaction ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					],
				],
				'permission_callback' => fn() => true,
			]
		);

		\register_rest_route(
			Integration::REST_ROUTE_NAMESPACE,
			'/payments/webhook/(?P<payment_id>\d+)',
			[
				'methods'             => 'POST',
				'callback'            => $this->rest_api_mollie_webhook_payment( ... ),
				'args'                => [
					'payment_id' => [
						'description' => \__( 'Payment ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					],
					'id'         => [
						'description' => \__( 'Mollie transaction ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					],
				],
				'permission_callback' => fn() => true,
			]
		);

		\register_rest_route(
			Integration::REST_ROUTE_NAMESPACE,
			'/orders/webhook/(?P<payment_id>\d+)',
			[
				'methods'             => 'POST',
				'callback'            => $this->rest_api_mollie_webhook_order( ... ),
				'args'                => [
					'payment_id' => [
						'description' => \__( 'Payment ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					],
					'id'         => [
						'description' => \__( 'Mollie order ID.', 'pronamic_ideal' ),
						'type'        => 'string',
						'required'    => true,
					],
				],
				'permission_callback' => fn() => true,
			]
		);
	}

	/**
	 * REST API Mollie webhook handler.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return object
	 */
	private function rest_api_mollie_webhook( WP_REST_Request $request ) {
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
	 * REST API Mollie payment webhook handler.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return object
	 */
	private function rest_api_mollie_webhook_payment( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );

		/**
		 * Result.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
		 */
		$response = new WP_REST_Response(
			[
				'success' => true,
				'id'      => $id,
			]
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
		$note = \__( 'Payment webhook requested by Mollie.', 'pronamic_ideal' );

		$payment->add_note( $note );

		// Log webhook request.
		\do_action( 'pronamic_pay_webhook_log_payment', $payment );

		// Update payment.
		Plugin::update_payment( $payment, false );

		return $response;
	}

	/**
	 * REST API Mollie order webhook handler.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return object
	 */
	private function rest_api_mollie_webhook_order( WP_REST_Request $request ) {
		$id = $request->get_param( 'id' );

		/**
		 * Result.
		 *
		 * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
		 */
		$response = new WP_REST_Response(
			[
				'success' => true,
				'id'      => $id,
			]
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
		$note = \__( 'Order webhook requested by Mollie.', 'pronamic_ideal' );

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
	private function wp_loaded() {
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
