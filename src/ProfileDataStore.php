<?php
/**
 * Mollie profile data store.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Profile;

/**
 * Profile data store class
 */
class ProfileDataStore {
	/**
	 * Get or insert profile.
	 *
	 * @param Profile       $profile Profile.
	 * @param array<string> $data    Data.
	 * @param array<string> $format  Format.
	 * @return int
	 * @throws \Exception Throws exception if Mollie profile ID could not be retrieved from existing profile.
	 */
	public function get_or_insert_profile( Profile $profile, $data = [], $format = [] ) {
		$profile_data = $this->get_profile( $profile );

		if ( null !== $profile_data ) {
			if ( ! \property_exists( $profile_data, 'id' ) ) {
				throw new \Exception( 'Unable to get Mollie profile ID for existing profile.' );
			}

			return $profile_data->id;
		}

		return $this->insert_profile( $profile, $data, $format );
	}

	/**
	 * Get profile data for specified Mollie profile.
	 *
	 * @param Profile $profile Profile.
	 * @return object|null
	 */
	public function get_profile( Profile $profile ) {
		global $wpdb;

		$id = $profile->get_id();

		if ( null === $id ) {
			return null;
		}

		$data = $wpdb->get_row(
			$wpdb->prepare(
				"
				SELECT
					*
				FROM
					$wpdb->pronamic_pay_mollie_profiles
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
	 * Insert Mollie profile.
	 *
	 * @param Profile       $profile Profile.
	 * @param array<string> $data    Data.
	 * @param array<string> $format  Format.
	 * @return int
	 * @throws \Exception Throws exception on error.
	 */
	public function insert_profile( Profile $profile, $data = [], $format = [] ) {
		global $wpdb;

		$mollie_id = $profile->get_id();

		if ( empty( $mollie_id ) ) {
			throw new \Exception( 'Can not insert Mollie profile with empty ID.' );
		}

		$data['mollie_id']   = $mollie_id;
		$format['mollie_id'] = '%s';

		$data['email']   = $profile->get_email();
		$format['email'] = '%s';

		$data['name']   = $profile->get_name();
		$format['name'] = '%s';

		$result = $wpdb->insert(
			$wpdb->pronamic_pay_mollie_profiles,
			$data,
			$format
		);

		if ( false === $result ) {
			throw new \Exception(
				\sprintf(
					'Could not insert Mollie profile ID: %s, error: %s.',
					\esc_html( $mollie_id ),
					\esc_html( $wpdb->last_error )
				)
			);
		}

		$id = $wpdb->insert_id;

		return $id;
	}

	/**
	 * Update Mollie profile.
	 *
	 * @param Profile       $profile Profile.
	 * @param array<string> $data    Data.
	 * @param array<string> $format  Format.
	 * @return int The number of rows updated.
	 * @throws \Exception Throws exception on error.
	 */
	public function update_profile( Profile $profile, $data = [], $format = [] ) {
		global $wpdb;

		$mollie_id = $profile->get_id();

		if ( empty( $mollie_id ) ) {
			throw new \Exception( 'Can not update Mollie profile with empty ID.' );
		}

		$data['email']   = $profile->get_email();
		$format['email'] = '%s';

		$data['name']   = $profile->get_name();
		$format['name'] = '%s';

		$result = $wpdb->update(
			$wpdb->pronamic_pay_mollie_profiles,
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
				\sprintf(
					'Could not update Mollie profile ID: %s, error: %s.',
					\esc_html( $mollie_id ),
					\esc_html( $wpdb->last_error )
				)
			);
		}

		return $result;
	}

	/**
	 * Save Mollie profile.
	 *
	 * @param Profile       $profile Profile.
	 * @param array<string> $data    Data.
	 * @param array<string> $format  Format.
	 * @return int
	 * @throws \Exception Throws exception if unable to update existing profile.
	 */
	public function save_profile( Profile $profile, $data = [], $format = [] ) {
		$profile_data = $this->get_profile( $profile );

		if ( null !== $profile_data ) {
			if ( ! \property_exists( $profile_data, 'id' ) ) {
				throw new \Exception( 'Can not update Mollie profile without ID.' );
			}

			$this->update_profile( $profile, $data, $format );

			return $profile_data->id;
		}

		return $this->insert_profile( $profile, $data, $format );
	}
}
