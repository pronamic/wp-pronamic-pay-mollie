<?php
/**
 * Mollie Connect.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\DateTime\DateTime;

/**
 * Title: Mollie Connect
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.1.0
 * @since   2.1.0
 */
class Connect {
	/**
	 * WordPress Pay Mollie Connect API URL.
	 */
	const WP_PAY_MOLLIE_CONNECT_API_URL = 'https://mollie-connect.wp-pay.org/';

	/**
	 * Config ID.
	 *
	 * @var int
	 */
	private $config_id;

	/**
	 * Access token.
	 *
	 * @var string|null
	 */
	private $access_token;

	/**
	 * Access token valid until.
	 *
	 * @var string|null
	 */
	private $access_token_valid_until;

	/**
	 * Refresh token.
	 *
	 * @var string|null
	 */
	private $refresh_token;

	/**
	 * Connect constructor.
	 *
	 * @param int $config_id Config ID.
	 */
	public function __construct( $config_id ) {
		$this->config_id = $config_id;
	}

	/**
	 * Get refresh token.
	 *
	 * @return string
	 */
	public function get_refresh_token() {
		return $this->refresh_token;
	}

	/**
	 * Set refresh token.
	 *
	 * @param string $refresh_token Refresh token.
	 * @return void
	 */
	public function set_refresh_token( $refresh_token ) {
		$this->refresh_token = $refresh_token;
	}

	/**
	 * Get access token.
	 *
	 * @return string|null
	 */
	public function get_access_token() {
		return $this->access_token;
	}

	/**
	 * Set access token.
	 *
	 * @param string|null $access_token Access token.
	 * @return void
	 */
	public function set_access_token( $access_token ) {
		$this->access_token = $access_token;
	}

	/**
	 * Get access token valid until.
	 *
	 * @return string|null
	 */
	public function get_access_token_valid_until() {
		return $this->access_token_valid_until;
	}

	/**
	 * Set access token valid until.
	 *
	 * @param string|null $access_token_valid_until Access token valid until.
	 * @return void
	 */
	public function set_access_token_valid_until( $access_token_valid_until ) {
		$this->access_token_valid_until = $access_token_valid_until;
	}

	/**
	 * Check if access token is valid.
	 *
	 * @return bool True if valid, false otherwise.
	 */
	public function is_access_token_valid() {
		if ( empty( $this->access_token ) ) {
			return false;
		}

		return \strtotime( $this->access_token_valid_until ) > \time();
	}

	/**
	 * Maybe handle Mollie oAuth authorization.
	 *
	 * @param string $code       Authorization code.
	 * @param object $state_data State data.
	 * @return void
	 * @throws Error
	 */
	public function handle_authorization( $code, $state_data ) {
		if ( empty( $code ) || empty( $state_data ) ) {
			return;
		}

		if ( ! is_object( $state_data ) ) {
			return;
		}

		if ( ! isset( $state_data->post_id ) ) {
			return;
		}

		$config_id = $state_data->post_id;

		if ( ! \current_user_can( 'edit_post', $config_id ) ) {
			return;
		}

		$data = $this->request_access_token( $code );

		// Update access token.
		if ( isset( $data->access_token ) ) {
			\update_post_meta( $config_id, '_pronamic_gateway_mollie_access_token', $data->access_token );
		}

		// Update access token validity.
		if ( isset( $data->expires_in ) ) {
			$timestamp = time() + $data->expires_in;

			$access_token_valid_until = gmdate( DateTime::MYSQL, $timestamp );

			\update_post_meta( $config_id, '_pronamic_gateway_mollie_access_token_valid_until', $access_token_valid_until );
		}

		// Update refresh token.
		if ( isset( $data->refresh_token ) ) {
			\update_post_meta( $config_id, '_pronamic_gateway_mollie_refresh_token', $data->refresh_token );
		}

		\wp_update_post(
			array(
				'ID'          => $config_id,
				'post_status' => 'publish',
			)
		);

		// Redirect.
		$url = \get_edit_post_link( $config_id, 'raw' );

		\wp_safe_redirect( $url );

		exit;
	}

	/**
	 * Get access token.
	 *
	 * @param string $authorization_code Authorization code.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 */
	private function request_access_token( $authorization_code ) {
		$data = array(
			'grant_type' => 'authorization_code',
			'code'       => $authorization_code,
		);

		return $this->send_token_request( $data );
	}

	/**
	 * Maybe refresh access token.
	 *
	 * @return void
	 */
	public function maybe_refresh_access_token() {
		if ( $this->is_access_token_valid() ) {
			return;
		}

		$refresh_token = $this->get_refresh_token();

		if ( empty( $refresh_token ) ) {
			return;
		}

		$response = $this->refresh_access_token();

		// Update access token.
		if ( isset( $response->access_token ) ) {
			$this->set_access_token( $response->access_token );

			\update_post_meta( $this->config_id, '_pronamic_gateway_mollie_access_token', $this->get_access_token() );
		}

		// Update access token validity.
		if ( isset( $response->expires_in ) ) {
			$timestamp = time() + $response->expires_in;

			$access_token_valid_until = gmdate( DateTime::MYSQL, $timestamp );

			$this->set_access_token_valid_until( $access_token_valid_until );

			\update_post_meta( $this->config_id, '_pronamic_gateway_mollie_access_token_valid_until', $this->get_access_token_valid_until() );
		}
	}

	/**
	 * Refresh access token.
	 *
	 * @return object
	 */
	private function refresh_access_token() {
		// Refresh access token.
		$data = array(
			'grant_type'    => 'refresh_token',
			'refresh_token' => $this->get_refresh_token(),
		);

		return $this->send_token_request( $data );
	}

	/**
	 * Send request with the specified action and parameters
	 *
	 * @param array<string, string|object|null> $data      Request data.
	 * @return object
	 * @throws Error Throws Error when Mollie error occurs.
	 * @throws \Exception Throws exception when error occurs.
	 */
	private function send_token_request( array $data = array() ) {
		// Request.
		$url = self::WP_PAY_MOLLIE_CONNECT_API_URL . 'oauth2/tokens';

		$response = wp_remote_request(
			$url,
			array(
				'method' => 'POST',
				'body'   => \wp_json_encode( $data ),
			)
		);

		if ( $response instanceof \WP_Error ) {
			throw new \Exception( $response->get_error_message() );
		}

		// Body.
		$body = wp_remote_retrieve_body( $response );

		$data = json_decode( $body );

		// JSON error.
		$json_error = \json_last_error();

		if ( \JSON_ERROR_NONE !== $json_error ) {
			throw new \Exception(
				\sprintf( 'JSON: %s', \json_last_error_msg() ),
				$json_error
			);
		}

		// Object.
		if ( ! \is_object( $data ) ) {
			$code = \wp_remote_retrieve_response_code( $response );

			throw new \Exception(
				\sprintf( 'Could not JSON decode Mollie response to an object (HTTP Status Code: %s).', $code ),
				\intval( $code )
			);
		}

		// Mollie error from JSON response.
		if ( isset( $data->status, $data->title, $data->detail ) ) {
			throw new Error(
				$data->status,
				$data->title,
				$data->detail
			);
		}

		return $data;
	}
}
