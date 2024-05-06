<?php
/**
 * CLI
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Client;
use Pronamic\WordPress\Mollie\Customer;
use Pronamic\WordPress\Mollie\Profile;

/**
 * CLI class
 *
 * @link    https://github.com/woocommerce/woocommerce/blob/3.9.0/includes/class-wc-cli.php
 */
class CLI {
	/**
	 * Customer data store.
	 *
	 * @var CustomerDataStore
	 */
	protected $customer_data_store;

	/**
	 * Profile data store.
	 *
	 * @var ProfileDataStore
	 */
	protected $profile_data_store;

	/**
	 * Construct CLI.
	 */
	public function __construct() {
		\WP_CLI::add_command(
			'pronamic-pay mollie organizations synchronize',
			function () {
				$this->wp_cli_organizations_synchronize();
			},
			[
				'shortdesc' => 'Synchronize Mollie organizations to WordPress (not implemented yet).',
			]
		);

		\WP_CLI::add_command(
			'pronamic-pay mollie customers synchronize',
			function () {
				$this->wp_cli_customers_synchronize();
			},
			[
				'shortdesc' => 'Synchronize Mollie customers to WordPress.',
			]
		);

		\WP_CLI::add_command(
			'pronamic-pay mollie customers connect-wp-users',
			function () {
				$this->wp_cli_customers_connect_wp_users();
			},
			[
				'shortdesc' => 'Connect Mollie customers to WordPress users by email.',
			]
		);

		\WP_CLI::add_command(
			'pronamic-pay mollie payments list',
			function ( $args, $assoc_args ) {
				$this->wp_cli_payments( $args, $assoc_args );
			},
			[
				'shortdesc' => 'Mollie payments.',
			]
		);

		\WP_CLI::add_command(
			'pronamic-pay mollie payments cancel',
			function ( $args, $assoc_args ) {
				$this->wp_cli_payments_cancel( $args, $assoc_args );
			},
			[
				'shortdesc' => 'Cancel Mollie payments.',
			]
		);

		// Data Stores.
		$this->profile_data_store  = new ProfileDataStore();
		$this->customer_data_store = new CustomerDataStore();
	}

	/**
	 * CLI organizations synchronize.
	 *
	 * @link https://docs.mollie.com/reference/v2/organizations-api/current-organization
	 * @return void
	 */
	public function wp_cli_organizations_synchronize() {
		\WP_CLI::error( 'Command not implemented yet.' );
	}

	/**
	 * CLI customers synchronize.
	 *
	 * @link https://docs.mollie.com/reference/v2/customers-api/list-customers
	 * @return void
	 */
	public function wp_cli_customers_synchronize() {
		global $post;
		global $wpdb;

		$query = new \WP_Query(
			[
				'post_type'   => 'pronamic_gateway',
				'post_status' => 'publish',
				'nopaging'    => true,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Slow query allowed on CLI.
				'meta_query'  => [
					[
						'key'   => '_pronamic_gateway_id',
						'value' => 'mollie',
					],
					[
						'key'     => '_pronamic_gateway_mollie_api_key',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$api_key = get_post_meta( $post->ID, '_pronamic_gateway_mollie_api_key', true );

				\WP_CLI::log( $post->post_title );
				\WP_CLI::log( $api_key );
				\WP_CLI::log( '' );

				$client = new Client( $api_key );

				$urls = [
					'https://api.mollie.com/v2/customers?limit=250',
				];

				$profile = Profile::from_object( $client->get_current_profile() );

				$profile_id = $this->profile_data_store->save_profile(
					$profile,
					[
						'api_key_live' => \str_starts_with( $api_key, 'live_' ) ? $api_key : null,
						'api_key_test' => \str_starts_with( $api_key, 'test_' ) ? $api_key : null,
					],
					[
						'api_key_live' => '%s',
						'api_key_test' => '%s',
					]
				);

				while ( ! empty( $urls ) ) {
					$url = (string) array_shift( $urls );

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

					if ( \property_exists( $response, '_embedded' ) && isset( $response->_embedded->customers ) ) {
						\WP_CLI\Utils\format_items(
							'table',
							$response->_embedded->customers,
							[
								'id',
								'mode',
								'name',
								'email',
								'locale',
							]
						);

						foreach ( $response->_embedded->customers as $object ) {
							$customer = Customer::from_object( $object );

							$customer_id = $this->customer_data_store->save_customer(
								$customer,
								[
									'profile_id' => $profile_id,
								],
								[
									'profile_id' => '%d',
								]
							);
						}
					}

					if ( \property_exists( $response, '_links' ) && isset( $response->_links->next->href ) ) {
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
	 * @return void
	 */
	public function wp_cli_customers_connect_wp_users() {
		global $wpdb;

		$query = "
			INSERT INTO $wpdb->pronamic_pay_mollie_customer_users (
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
						ON CAST( mollie_customer.email AS BINARY ) = CAST( wp_user.user_email AS BINARY )
					LEFT JOIN
				$wpdb->pronamic_pay_mollie_customer_users AS mollie_customer_user
						ON ( mollie_customer_user.customer_id = mollie_customer.id AND mollie_customer_user.user_id = wp_user.ID )
			WHERE
				mollie_customer_user.id IS NULL
			;
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepare is OK.
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

	/**
	 * CLI Mollie payments.
	 *
	 * @param array<string> $args       Arguments.
	 * @param array<string> $assoc_args Associative arguments.
	 * @return void
	 */
	public function wp_cli_payments( $args, $assoc_args ) {
		$assoc_args = \wp_parse_args(
			$assoc_args,
			[
				'api_key' => null,
				'api_url' => 'https://api.mollie.com/v2/payments',
				'from'    => null,
				'limit'   => 250,
				'format'  => 'table',
			]
		);

		$api_key = $assoc_args['api_key'];
		$api_url = $assoc_args['api_url'];
		$from    = $assoc_args['from'];
		$limit   = $assoc_args['limit'];
		$format  = $assoc_args['format'];

		if ( empty( $api_key ) ) {
			\WP_CLI::error( 'This command requires an API key for authentication' );
		}

		$client = new Client( $api_key );

		$payments = [];

		$api_url = $assoc_args['api_url'];

		if ( null !== $limit ) {
			$api_url = \add_query_arg( 'limit', $limit, $api_url );
		}

		if ( null !== $from ) {
			$api_url = \add_query_arg( 'from', $from, $api_url );
		}

		$response = $client->send_request( $api_url );

		if ( \property_exists( $response, '_embedded' ) && isset( $response->_embedded->payments ) ) {
			foreach ( $response->_embedded->payments as $object ) {
				$payments[] = $object;
			}
		}

		$is_cancelable = \WP_CLI\Utils\get_flag_value( $assoc_args, 'is_cancelable' );

		if ( null !== $is_cancelable ) {
			$payments = \array_filter(
				$payments,
				function ( $payment ) {
					if ( ! \property_exists( $payment, 'isCancelable' ) ) {
						return false;
					}

					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie name.
					return $payment->isCancelable;
				}
			);
		}

		$data = $payments;

		if ( 'ids' === $format ) {
			$data = \wp_list_pluck( $payments, 'id' );
		}

		if ( empty( $data ) ) {
			if ( \property_exists( $response, '_links' ) && isset( $response->_links->next->href ) ) {
				\WP_CLI::log(
					\sprintf(
						'Number Payments: %s, Number Filtered Payments: %s, API URL: %s.',
						\property_exists( $response, '_embedded' ) ? \count( $response->_embedded->payments ) : 0,
						\count( $payments ),
						$api_url
					)
				);

				$automatic = \WP_CLI\Utils\get_flag_value( $assoc_args, 'automatic' );

				if ( $automatic ) {
					$new_assoc_args = $assoc_args;

					$new_assoc_args['api_url'] = $response->_links->next->href;

					$this->wp_cli_payments( $args, $new_assoc_args );
				}

				return;
			}
		}

		\WP_CLI\Utils\format_items(
			$format,
			$data,
			[
				'id',
				'createdAt',
				'mode',
				'description',
				'method',
			]
		);
	}

	/**
	 * CLI cancel Mollie payments.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/list-payments
	 * @link https://make.wordpress.org/cli/handbook/internal-api/wp-cli-add-command/
	 * @link https://developer.wordpress.org/reference/classes/wpdb/query/
	 * @param array<string> $args       Arguments.
	 * @param array<string> $assoc_args Associative arguments.
	 * @return void
	 */
	public function wp_cli_payments_cancel( $args, $assoc_args ) {
		$assoc_args = \wp_parse_args(
			$assoc_args,
			[
				'api_key' => null,
			]
		);

		$api_key = $assoc_args['api_key'];

		if ( empty( $api_key ) ) {
			\WP_CLI::error( 'This command requires an API key for authentication' );
		}

		if ( empty( $args ) ) {
			\WP_CLI::error( 'This command requires a transaction ID to cancel payments' );
		}

		$client = new Client( $api_key );

		foreach ( $args as $id ) {
			\WP_CLI::log(
				\sprintf(
					'Try to cancel payment `%s`…',
					$id
				)
			);

			$url = 'https://api.mollie.com/v2/payments/' . $id;

			\WP_CLI::log(
				\sprintf(
					'DELETE %s',
					$url
				)
			);

			$response = $client->send_request( $url, 'DELETE' );

			\WP_CLI::log(
				\sprintf(
					'- status = %s, createdAt = %s, canceledAt = %s',
					\property_exists( $response, 'status' ) ? $response->status : '',
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie name.
					\property_exists( $response, 'createdAt' ) ? $response->createdAt : '',
					// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie name.
					\property_exists( $response, 'canceledAt' ) ? $response->canceledAt : ''
				)
			);

			\WP_CLI::log( '' );
		}

		\WP_CLI::log( '' );

		\WP_CLI::log( 'If you want to cancel the next batch of payments you can run the following command:' );

		\WP_CLI::log( '' );

		\WP_CLI::log(
			\sprintf(
				'wp pronamic-pay mollie payments cancel $( wp pronamic-pay mollie payments list --api_key=%s --from=%s --is_cancelable --format=%s ) --api_key=%s',
				\escapeshellarg( $api_key ),
				\escapeshellarg( $id ),
				\escapeshellarg( 'ids' ),
				\escapeshellarg( $api_key )
			)
		);
	}
}
