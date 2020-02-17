<?php
/**
 * Upgrade 3.0.0
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Upgrades\Upgrade;

/**
 * Upgrade 3.0.0
 *
 * @author  Remco Tolsma
 * @version 3.0.0
 * @since   3.0.0
 */
class Upgrade300 extends Upgrade {
	/**
	 * Construct 3.0.0 upgrade.
	 */
	public function __construct() {
		parent::__construct( '3.0.1' );
	}

	/**
	 * Execute.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-includes/wp-db.php#L992-L1072
	 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/schema.php#L25-L344
	 * @link https://developer.wordpress.org/reference/functions/dbdelta/
	 * @link https://github.com/wp-premium/gravityforms/blob/2.4.16/includes/class-gf-upgrade.php#L518-L531
	 * @throws \Exception Throws exception when database update query fails.
	 */
	public function execute() {
		global $wpdb;

		/**
		 * Requirements.
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * Other.
		 */
		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * Queries.
		 */
		$queries = "
			CREATE TABLE $wpdb->pronamic_pay_mollie_organizations (
				id BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				mollie_id VARCHAR( 16 ) NOT NULL,
				name VARCHAR( 128 ) DEFAULT NULL,
				email VARCHAR( 100 ) DEFAULT NULL,
				PRIMARY KEY  ( id ),
				UNIQUE KEY mollie_id ( mollie_id )
			) $charset_collate;
			CREATE TABLE $wpdb->pronamic_pay_mollie_customers (
				id BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				mollie_id VARCHAR( 16 ) NOT NULL,
				organization_id BIGINT( 20 ) UNSIGNED DEFAULT NULL,
				test_mode BOOL NOT NULL,
				email VARCHAR( 100 ) DEFAULT NULL,
				name VARCHAR( 255 ) DEFAULT NULL,
				PRIMARY KEY  ( id ),
				UNIQUE KEY mollie_id ( mollie_id ),
				KEY organization_id ( organization_id ),
				KEY test_mode ( test_mode ),
				KEY email ( email )
			) $charset_collate;
			CREATE TABLE $wpdb->pronamic_pay_mollie_customer_users (
				id BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT,
				customer_id BIGINT( 20 ) UNSIGNED NOT NULL,
				user_id BIGINT( 20 ) UNSIGNED NOT NULL,
				PRIMARY KEY  ( id ),
				UNIQUE KEY customer_user ( customer_id, user_id )
			) $charset_collate;
		";

		/**
		 * Execute.
		 *
		 * @link https://developer.wordpress.org/reference/functions/dbdelta/
		 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/upgrade.php#L2538-L2915
		 */
		\dbDelta( $queries );

		/**
		 * Foreign keys.
		 *
		 * @link https://core.trac.wordpress.org/ticket/19207
		 * @link https://dev.mysql.com/doc/refman/5.6/en/create-table-foreign-keys.html
		 */
		$data = array(
			(object) array(
				'table' => $wpdb->pronamic_pay_mollie_customers,
				'name'  => 'fk_customer_organization_id',
				'query' => "
					ALTER TABLE $wpdb->pronamic_pay_mollie_customers
					ADD CONSTRAINT fk_customer_organization_id
					FOREIGN KEY ( organization_id )
					REFERENCES $wpdb->pronamic_pay_mollie_organizations ( id )
					ON DELETE RESTRICT
					ON UPDATE RESTRICT
					;
				",
			),
			(object) array(
				'table' => $wpdb->pronamic_pay_mollie_customer_users,
				'name'  => 'fk_customer_id',
				'query' => "
					ALTER TABLE $wpdb->pronamic_pay_mollie_customer_users
					ADD CONSTRAINT fk_customer_id
					FOREIGN KEY customer_id ( customer_id )
					REFERENCES $wpdb->pronamic_pay_mollie_customers ( id )
					ON DELETE RESTRICT
					ON UPDATE RESTRICT
					;
				",
			),
			(object) array(
				'table' => $wpdb->pronamic_pay_mollie_customer_users,
				'name'  => 'fk_customer_user_id',
				'query' => "
					ALTER TABLE $wpdb->pronamic_pay_mollie_customer_users
					ADD CONSTRAINT fk_customer_user_id
					FOREIGN KEY user_id ( user_id )
					REFERENCES $wpdb->users ( id )
					ON DELETE CASCADE
					ON UPDATE CASCADE
					;
				",
			),
		);

		foreach ( $data as $item ) {
			/**
			 * Check if foreign key exists
			 *
			 * @link https://github.com/woocommerce/woocommerce/blob/3.9.0/includes/class-wc-install.php#L663-L681
			 */
			$result = $wpdb->get_var(
				$wpdb->prepare(
					"
				SELECT COUNT(*)
				FROM information_schema.TABLE_CONSTRAINTS
				WHERE CONSTRAINT_SCHEMA = %s
				AND CONSTRAINT_NAME = %s
				AND CONSTRAINT_TYPE = 'FOREIGN KEY'
				AND TABLE_NAME = %s
				",
					$wpdb->dbname,
					$item->name,
					$item->table
				)
			);

			if ( null === $result ) {
				throw new \Exception(
					\sprintf(
						'Could not count foreign keys: %s, database error: %s.',
						$$item->name,
						$wpdb->last_error
					)
				);
			}

			$number_constraints = \intval( $result );

			if ( 0 === $number_constraints ) {
				$result = $wpdb->query( $item->query );

				if ( false === $result ) {
					throw new \Exception(
						\sprintf(
							'Could not add foreign key: %s, database error: %s.',
							$item->name,
							$wpdb->last_error
						)
					);
				}
			}
		}

		/**
		 * Convert user meta.
		 */
		$this->convert_user_meta();
	}

	/**
	 * Convert user meta.
	 *
	 * @throws \Exception Throws exception when database update query fails.
	 */
	private function convert_user_meta() {
		global $wpdb;

		$query = "
			INSERT IGNORE INTO $wpdb->pronamic_pay_mollie_customers (
				mollie_id,
				test_mode
			)
			SELECT
				meta_value AS mollie_id,
				'_pronamic_pay_mollie_customer_id_test' = meta_key AS test_mode
			FROM
				$wpdb->usermeta
			WHERE
				meta_key IN (
					'_pronamic_pay_mollie_customer_id',
					'_pronamic_pay_mollie_customer_id_test'
				)
					AND
				meta_value != ''
			;
		";

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			throw new Exception(
				sprintf(
					'Could not convert user meta, database error: %s.',
					$wpdb->last_error
				)
			);
		}

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
				$wpdb->usermeta AS wp_user_meta
						ON wp_user_meta.meta_value = mollie_customer.mollie_id
					INNER JOIN
				$wpdb->users AS wp_user
						ON wp_user_meta.user_id = wp_user.ID
			WHERE
				wp_user_meta.meta_key IN (
					'_pronamic_pay_mollie_customer_id',
					'_pronamic_pay_mollie_customer_id_test'
				)
					AND
				wp_user_meta.meta_value != ''
			;
		";

		$result = $wpdb->query( $query );

		if ( false === $result ) {
			throw new Exception(
				sprintf(
					'Could not convert user meta, database error: %s.',
					$wpdb->last_error
				)
			);
		}
	}
}
