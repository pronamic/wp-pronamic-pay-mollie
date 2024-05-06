<?php
/**
 * Mollie customer data store.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Customer;

/**
 * Customer data store class
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
	public function get_or_insert_customer( Customer $customer, $data = [], $format = [] ) {
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
	public function insert_customer( Customer $customer, $data = [], $format = [] ) {
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
					\esc_html( $mollie_id ),
					\esc_html( $wpdb->last_error )
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
	public function update_customer( Customer $customer, $data = [], $format = [] ) {
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
			[
				'mollie_id' => $mollie_id,
			],
			$format,
			[
				'mollie_id' => '%s',
			]
		);

		if ( false === $result ) {
			throw new \Exception(
				sprintf(
					'Could not update Mollie customer ID: %s, error: %s.',
					\esc_html( $mollie_id ),
					\esc_html( $wpdb->last_error )
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
	public function save_customer( Customer $customer, $data = [], $format = [] ) {
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
	 * @return void
	 * @throws \Exception Throws exception on error.
	 */
	public function connect_mollie_customer_to_wp_user( $customer, \WP_User $user ) {
		global $wpdb;

		$customer_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM $wpdb->pronamic_pay_mollie_customers WHERE mollie_id = %s;",
				$customer->get_id()
			)
		);

		if ( null === $customer_id ) {
			return;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->pronamic_pay_mollie_customer_users WHERE customer_id = %d AND user_id = %d;",
				$customer_id,
				$user->ID
			)
		);

		if ( null !== $row ) {
			return;
		}

		$data = [
			'customer_id' => $customer_id,
			'user_id'     => $user->ID,
		];

		$format = [
			'customer_id' => '%d',
			'user_id'     => '%d',
		];

		$result = $wpdb->insert(
			$wpdb->pronamic_pay_mollie_customer_users,
			$data,
			$format
		);

		if ( false === $result ) {
			throw new \Exception(
				\sprintf(
					'Database error: %s, Data: %s.',
					\esc_html( $wpdb->last_error ),
					\esc_html( (string) \wp_json_encode( $data ) )
				)
			);
		}
	}
}
