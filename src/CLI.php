<?php
/**
 * CLI
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: CLI
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 3.0.0
 * @since   3.0.0
 * @link    https://github.com/woocommerce/woocommerce/blob/3.9.0/includes/class-wc-cli.php
 */
class CLI {
	/**
	 * Construct CLI.
	 */
	public function __construct() {
		\WP_CLI::add_command(
			'pronamic-pay mollie organizations synchronize',
			function( $args, $assoc_args ) {
				$this->wp_cli_organizations_synchronize( $args, $assoc_args );
			}
		);

		\WP_CLI::add_command(
			'pronamic-pay mollie customers synchronize',
			function( $args, $assoc_args ) {
				$this->wp_cli_customers_synchronize( $args, $assoc_args );
			}
		);

		\WP_CLI::add_command(
			'pronamic-pay mollie customers connect-wp-users',
			function( $args, $assoc_args ) {
				$this->wp_cli_customers_connect_wp_users( $args, $assoc_args );
			}
		);
	}

	/**
	 * CLI organizations synchronize.
	 *
	 * @link https://docs.mollie.com/reference/v2/organizations-api/current-organization
	 * @param array $args       Arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function wp_cli_organizations_synchronize( $args, $assoc_args ) {
		\WP_CLI::error( 'Command not implemented yet.' );
	}

	/**
	 * Insert or update Mollie profile.
	 *
	 * @param object $object Mollie profile object.
	 * @param array  $data   Data.
	 * @param array  $format Format.
	 * @return int Mollie proflie ID.
	 */
	private function insert_or_update_profile( $object, $data = array(), $format = array() ) {
		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->pronamic_pay_mollie_profiles WHERE mollie_id = %s", $object->id ) );

		$data['email']   = $object->email;
		$format['email'] = '%s';

		$data['name']   = $object->name;
		$format['name'] = '%s';

		if ( null === $id ) {
			$data['mollie_id']   = $object->id;
			$foramt['mollie_id'] = '%s';

			$result = $wpdb->insert(
				$wpdb->pronamic_pay_mollie_profiles,
				$data,
				$format
			);

			if ( false === $result ) {
				\WP_CLI::error(
					sprintf(
						'Database error: %s.',
						$wpdb->last_error
					)
				);
			}

			$id = $wpdb->insert_id;
		} else {
			$result = $wpdb->update(
				$wpdb->pronamic_pay_mollie_profiles,
				$data,
				array(
					'id' => $id,
				),
				$format,
				array(
					'id' => '%d'
				)
			);

			if ( false === $result ) {
				\WP_CLI::error(
					sprintf(
						'Database error: %s.',
						$wpdb->last_error
					)
				);
			}
		}

		return $id;
	}

	/**
	 * Insert or update Mollie customer.
	 *
	 * @param object $object Mollie customer object.
	 * @param array  $data   Data.
	 * @param array  $format Format.
	 * @return int Mollie customer ID.
	 */
	private function insert_or_update_customer( $object, $data = array(), $format = array() ) {
		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->pronamic_pay_mollie_customers WHERE mollie_id = %s", $object->id ) );

		$data['test_mode']   = ( 'test' === $object->mode );
		$format['test_mode'] = '%d';

		$data['email']   = $object->email;
		$format['email'] = '%s';

		$data['name']   = $object->name;
		$format['name'] = '%s';

		if ( null === $id ) {
			$data['mollie_id']   = $object->id;
			$foramt['mollie_id'] = '%s';

			$result = $wpdb->insert(
				$wpdb->pronamic_pay_mollie_customers,
				$data,
				$format
			);

			if ( false === $result ) {
				\WP_CLI::error(
					sprintf(
						'Database error: %s.',
						$wpdb->last_error
					)
				);
			}

			$id = $wpdb->insert_id;
		} else {
			$result = $wpdb->update(
				$wpdb->pronamic_pay_mollie_customers,
				$data,
				array(
					'id' => $id,
				),
				$format,
				array(
					'id' => '%d'
				)
			);

			if ( false === $result ) {
				\WP_CLI::error(
					sprintf(
						'Database error: %s.',
						$wpdb->last_error
					)
				);
			}
		}

		return $id;
	}

	/**
	 * CLI customers synchronize.
	 *
	 * @link https://docs.mollie.com/reference/v2/customers-api/list-customers
	 * @param array $args       Arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function wp_cli_customers_synchronize( $args, $assoc_args ) {
		global $post;
		global $wpdb;

		$query = new \WP_Query(
			array(
				'post_type'   => 'pronamic_gateway',
				'post_status' => 'publish',
				'nopaging'    => true,
				'meta_query'  => array(
					array(
						'key'   => '_pronamic_gateway_id',
						'value' => 'mollie',
					),
					array(
						'key'     => '_pronamic_gateway_mollie_api_key',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$api_key = get_post_meta( $post->ID, '_pronamic_gateway_mollie_api_key', true );

				\WP_CLI::log( $post->post_title );
				\WP_CLI::log( $api_key );
				\WP_CLI::log( '' );

				$client = new Client( $api_key );

				$urls = array(
					'https://api.mollie.com/v2/customers?limit=250',
				);

				$profile = $client->get_current_profile();

				$profile_id = $this->insert_or_update_profile(
					$profile,
					array(
						'api_key_live' => ( 'live_' === substr( $api_key, 0, 5 ) ) ? $api_key : null,
						'api_key_test' => ( 'test_' === substr( $api_key, 0, 5 ) ) ? $api_key : null,
					),
					array(
						'api_key_live' => '%s',
						'api_key_test' => '%s',
					)
				);

				while ( ! empty( $urls ) ) {
					$url = array_shift( $urls );

					\WP_CLI::log( $url );

					$response = $client->send_request( $url );

					if ( isset( $response->count ) ) {
						\WP_CLI::log(
							\sprintf(
								'Found %d customer(s).',
								$response->count
							)
						);
					}

					if ( isset( $response->_embedded->customers ) ) {
						\WP_CLI\Utils\format_items(
							'table',
							$response->_embedded->customers,
							array(
								'id',
								'mode',
								'name',
								'email',
								'locale',
							)
						);

						foreach ( $response->_embedded->customers as $object ) {
							$customer_id = $this->insert_or_update_customer(
								$object,
								array(
									'profile_id' => $profile_id,
								),
								array(
									'profile_id' => '%d',
								)
							);
						}
					}

					if ( isset( $response->_links->next->href ) ) {
						$urls[] = $response->_links->next->href;
					}
				}
			}

			\wp_reset_postdata();
		}
	}

	/**
	 * CLI connect Mollie customers to WordPress users.
	 *
	 * @link https://docs.mollie.com/reference/v2/customers-api/list-customers
	 * @link https://make.wordpress.org/cli/handbook/internal-api/wp-cli-add-command/
	 * @link https://developer.wordpress.org/reference/classes/wpdb/query/
	 * @param array $args       Arguments.
	 * @param array $assoc_args Associative arguments.
	 */
	public function wp_cli_customers_connect_wp_users( $args, $assoc_args ) {
		global $wpdb;

		$query = "
			INSERT IGNORE INTO $wpdb->pronamic_pay_mollie_customer_users (
				customer_id,
				user_id
			)
			SELECT
				mollie_customer.id AS mollie_customer_id,
				wp_user.ID AS wp_user_id
			FROM
				$wpdb->pronamic_pay_mollie_customers AS mollie_customer
					INNER JOIN
				$wpdb->users AS wp_user
						ON mollie_customer.email = wp_user.user_email
			;
		";

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			\WP_CLI::error(
				sprintf(
					'Database error: %s.',
					$wpdb->last_error
				)
			);
		}

		\WP_CLI::log(
			sprintf(
				'Connected %d users and Mollie customers.',
				$result
			)
		);
	}
}
