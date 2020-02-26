<?php
/**
 * Mollie customer data store.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie customer data store
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 3.0.0
 * @since   3.0.0
 */
class CustomerDataStore {
	/**
	 * Get customer data for specified Mollie customer.
	 *
	 * @param Customer $customer Mollie customer.
	 * @return object|null
	 */
	public function get_customer( Customer $customer ) {
		global $wpdb;

		$id = $customer->get_id();

		if ( null === $id ) {
			return null;
		}

		$query = $wpdb->prepare( "SELECT * FROM $wpdb->pronamic_pay_mollie_customers WHERE mollie_id = %s LIMIT 1;", $id );

		$data = $wpdb->get_row( $query );

		return $data;
	}

	/**
	 * Insert Mollie customer.
	 *
	 * @param Customer $customer Customer.
	 * @param array $data   Data.
	 * @param array $format Format.
	 */
	public function insert_customer( Customer $customer, $data = array(), $format = array()  ) {
		global $wpdb;

		$mollie_id = $customer->get_id();

		if ( empty( $mollie_id ) ) {
			throw new \Exception( 'Can not insert Mollie customer with empty ID.' );
		}

		$data['mollie_id'] = $mollie_id;
		$format['mollie_id'] = '%s';

		$data['test_mode'] = ( 'test' === $customer->get_mode() );
		$format['test_mode'] = '%d';

		$data['email']     = $customer->get_email();
		$format['email']     = '%s';

		$result = $wpdb->insert(
			$wpdb->pronamic_pay_mollie_customers,
			$data,
			$format
		);

		if ( false === $result ) {
			throw new \Exception(
				sprintf(
					'Could not insert Mollie customer ID: %s, error: %s.',
					$mollie_id,
					$wpdb->last_error
				)
			);
		}

		$id = $wpdb->insert_id;

		return $id;
	}

	/**
	 * Connect Mollie customer to WordPress user.
	 *
	 * @param Customer $customer Mollie customer.
	 * @param \WP_User $user     WordPress user.
	 */
	public function connect_mollie_customer_to_wp_user( $customer, \WP_User $user ) {
		global $wpdb;

		$query = $wpdb->prepare( "
			INSERT IGNORE INTO $wpdb->pronamic_pay_mollie_customer_users (
				customer_id,
				user_id
			)
			SELECT
				mollie_customer.id AS mollie_customer_id,
				wp_user.ID AS wp_user_id
			FROM
				$wpdb->pronamic_pay_mollie_customers AS mollie_customer
					JOIN
				$wpdb->users AS wp_user
			WHERE
				mollie_customer.mollie_id = %s
					AND
				wp_user.ID = %d
			;
			",
			$customer->get_id(),
			$user->ID
		);
echo $query;exit;
		$result = $wpdb->query( $query );

		if ( false === $result ) {
			\WP_CLI::error(
				sprintf(
					'Database error: %s.',
					$wpdb->last_error
				)
			);
		}

		return $result;
	}
}
