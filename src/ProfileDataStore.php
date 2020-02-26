<?php
/**
 * Mollie profile data store.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie profile data store
 * Description:
 * Copyright: 2005-2020 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 3.0.0
 * @since   3.0.0
 */
class ProfileDataStore {
	public function get_profile( $profile ) {
		global $wpdb;

		$id = $profile->get_id();

		if ( null === $id ) {
			return null;
		}

		$query = $wpdb->prepare( "SELECT * FROM $wpdb->pronamic_pay_mollie_profiles WHERE mollie_id = %s LIMIT 1;", $id );

		$data = $wpdb->get_row( $query );

		return $data;
	}

	public function save_profile( $profile, $data = array(), $format = array() ) {
		global $wpdb;

		$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $wpdb->pronamic_pay_mollie_profiles WHERE mollie_id = %s", $profile->get_id() ) );

		$data['email']   = $profile->email;
		$format['email'] = '%s';

		$data['name']   = $profile->name;
		$format['name'] = '%s';

		if ( null === $id ) {
			$data['mollie_id']   = $profile->get_id();
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
}
