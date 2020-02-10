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
		\WP_CLI::add_command( 'pronamic-pay mollie organizations synchronize', function( $args, $assoc_args ) {
			$this->wp_cli_organizations_synchronize( $args, $assoc_args );
		} );

		\WP_CLI::add_command( 'pronamic-pay mollie customers synchronize', function( $args, $assoc_args ) {
			$this->wp_cli_customers_synchronize( $args, $assoc_args );
		} );

		\WP_CLI::add_command( 'pronamic-pay mollie customers connect-wp-users', function( $args, $assoc_args ) {
			$this->wp_cli_customers_connect_wp_users( $args, $assoc_args );
		} );
	}

	/**
	 * CLI organizations synchronize.
	 *
	 * @link https://docs.mollie.com/reference/v2/organizations-api/current-organization
	 */
	public function wp_cli_organizations_synchronize( $args, $assoc_args ) {
		\WP_CLI::error( 'Command not implemented yet.' );
	}

	/**
	 * CLI customers synchronize.
	 *
	 * @link https://docs.mollie.com/reference/v2/customers-api/list-customers
	 */
	public function wp_cli_customers_synchronize( $args, $assoc_args ) {
		\WP_CLI::error( 'Command not implemented yet.' );
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
