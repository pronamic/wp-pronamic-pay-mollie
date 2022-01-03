<?php
/**
 * Mollie customer data store.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie customer data store
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class CustomerDataStore {
	/**
	 * Get or insert customer.
	 *
	 * @param Customer                  $customer Customer.
	 * @param array<string, int|string> $data     Data.
	 * @param array<string, string>     $format   Format.
	 * @return int
	 * @throws \Exception Throws exception if Mollie customer ID could not be retrieved from existing customer.
	 */
	public function get_or_insert_customer( Customer $customer, $data = array(), $format = array() ) {
		$customer_data = $this->get_customer( $customer );

		if ( null !== $customer_data ) {
			if ( ! \property_exists( $customer_data, 'id' ) ) {
				throw new \Exception( 'Unable to get Mollie customer ID for existing customer.' );
			}

			return $customer_data->id;
		}

		return $this->insert_customer( $customer, $data, $format );
	}

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

		$data = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT
					*
				FROM
					$wpdb->pronamic_pay_mollie_customers
				WHERE
					mollie_id = %s
				LIMIT
					1
				;
				",
				$id
			)
		);

		return $data;
	}

	/**
	 * Insert Mollie customer.
	 *
	 * @param Customer                  $customer Customer.
	 * @param array<string, int|string> $data     Data.
	 * @param array<string, string>     $format   Format.
	 * @return int
	 * @throws \Exception Throws exception on error.
	 */
	public function insert_customer( Customer $customer, $data = array(), $format = array() ) {
		global $wpdb;

		$mollie_id = $customer->get_id();

		if ( empty( $mollie_id ) ) {
			throw new \Exception( 'Can not insert Mollie customer with empty ID.' );
		}

		$data['mollie_id']   = $mollie_id;
		$format['mollie_id'] = '%s';

		$data['test_mode']   = ( 'test' === $customer->get_mode() );
		$format['test_mode'] = '%d';

		$data['email']   = $customer->get_email();
		$format['email'] = '%s';

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
	 * Update Mollie customer.
	 *
	 * @param Customer                  $customer Customer.
	 * @param array<string, int|string> $data     Data.
	 * @param array<string, string>     $format   Format.
	 * @return int The number of rows updated.
	 * @throws \Exception Throws exception on error.
	 */
	public function update_customer( Customer $customer, $data = array(), $format = array() ) {
		global $wpdb;

		$mollie_id = $customer->get_id();

		if ( empty( $mollie_id ) ) {
			throw new \Exception( 'Can not update Mollie customer with empty ID.' );
		}

		$data['test_mode']   = ( 'test' === $customer->get_mode() );
		$format['test_mode'] = '%d';

		$data['email']   = $customer->get_email();
		$format['email'] = '%s';

		$result = $wpdb->update(
			$wpdb->pronamic_pay_mollie_customers,
			$data,
			array(
				'mollie_id' => $mollie_id,
			),
			$format,
			array(
				'mollie_id' => '%s',
			)
		);

		if ( false === $result ) {
			throw new \Exception(
				sprintf(
					'Could not update Mollie customer ID: %s, error: %s.',
					$mollie_id,
					$wpdb->last_error
				)
			);
		}

		return $result;
	}

	/**
	 * Save Mollie customer.
	 *
	 * @param Customer                  $customer Customer.
	 * @param array<string, int|string> $data     Data.
	 * @param array<string, string>     $format   Format.
	 * @return int
	 * @throws \Exception Throws exception if unable to update existing customer.
	 */
	public function save_customer( Customer $customer, $data = array(), $format = array() ) {
		$customer_data = $this->get_customer( $customer );

		if ( null !== $customer_data ) {
			if ( ! \property_exists( $customer_data, 'id' ) ) {
				throw new \Exception( 'Can not update Mollie customer without ID.' );
			}

			$this->update_customer( $customer, $data, $format );

			return $customer_data->id;
		}

		return $this->insert_customer( $customer, $data, $format );
	}

	/**
	 * Connect Mollie customer to WordPress user.
	 *
	 * @param Customer $customer Mollie customer.
	 * @param \WP_User $user     WordPress user.
	 * @return int Number of rows affected.
	 * @throws \Exception Throws exception on error.
	 */
	public function connect_mollie_customer_to_wp_user( $customer, \WP_User $user ) {
		global $wpdb;

		$result = $wpdb->query(
			$wpdb->prepare(
				"
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
			)
		);

		if ( false === $result ) {
			throw new \Exception(
				sprintf(
					'Database error: %s.',
					$wpdb->last_error
				)
			);
		}

		return $result;
	}
}
