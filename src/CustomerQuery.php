<?php
/**
 * Mollie customer query.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Customer query class
 */
class CustomerQuery {
	/**
	 * Query arguments.
	 *
	 * @var array<string, int>
	 */
	private $args;

	/**
	 * Construct customer query.
	 *
	 * @param array<string, int> $args Query arguments.
	 */
	public function __construct( $args = [] ) {
		$this->args = \wp_parse_args(
			$args,
			[
				'user_id'         => null,
				'organization_id' => null,
			]
		);
	}

	/**
	 * Get customers.
	 *
	 * @return array<object>
	 */
	public function get_customers() {
		global $wpdb;

		$where = '1 = 1';

		if ( array_key_exists( 'user_id', $this->args ) ) {
			$where .= $wpdb->prepare( ' AND mollie_customer_user.user_id = %d', $this->args['user_id'] );
		}

		$query = "
			SELECT
				mollie_customer.mollie_id,
				mollie_customer.test_mode,
				mollie_customer.name,
				mollie_customer.email
			FROM
				$wpdb->pronamic_pay_mollie_customer_users AS mollie_customer_user
					INNER JOIN
				$wpdb->pronamic_pay_mollie_customers AS mollie_customer
						ON mollie_customer_user.customer_id = mollie_customer.id
			WHERE
				 $where
			;
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared.
		return $wpdb->get_results( $query );
	}
}
