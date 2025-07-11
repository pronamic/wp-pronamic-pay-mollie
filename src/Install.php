<?php
/**
 * Mollie install.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Upgrades\Upgrade;

/**
 * Install class
 */
class Install extends Upgrade {
	/**
	 * Execute.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/4.0.0/includes/class-wc-install.php#L272-L306
	 * @return void
	 */
	public function execute() {
		$this->create_tables();
		$this->add_foreign_keys();
		$this->convert_user_meta();
	}

	/**
	 * Create tables.
	 *
	 * @link https://github.com/woocommerce/woocommerce/blob/4.0.0/includes/class-wc-install.php#L630-L720
	 * @return void
	 */
	private function create_tables() {
		global $wpdb;

		/**
		 * Requirements.
		 */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		/**
		 * Table options.
		 *
		 * In MySQL 5.6, InnoDB is the default MySQL storage engine. Unless you
		 * have configured a different default storage engine,  issuing a
		 * CREATE TABLE statement without an ENGINE= clause creates an InnoDB
		 * table.
		 *
		 * @link https://dev.mysql.com/doc/refman/5.6/en/innodb-introduction.html
		 *
		 * If a storage engine is specified that is not available, MySQL uses
		 * the default engine instead. Normally, this is MyISAM. For example,
		 * if a table definition includes the ENGINE=INNODB option but the MySQL
		 * server does not support INNODB tables, the table is created as a
		 * MyISAM table.
		 *
		 * @link https://dev.mysql.com/doc/refman/5.6/en/create-table.html
		 */
		$table_options = 'ENGINE=InnoDB ' . $wpdb->get_charset_collate();

		/**
		 * Queries.
		 *
		 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/schema.php
		 */
		$queries = "
			CREATE TABLE $wpdb->pronamic_pay_mollie_organizations (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				mollie_id varchar(40) NOT NULL,
				name varchar(128) DEFAULT NULL,
				email varchar(100) DEFAULT NULL,
				PRIMARY KEY  ( id ),
				UNIQUE KEY mollie_id ( mollie_id )
			) $table_options;
			CREATE TABLE $wpdb->pronamic_pay_mollie_profiles (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				mollie_id varchar(40) NOT NULL,
				organization_id bigint(20) unsigned DEFAULT NULL,
				name varchar(128) DEFAULT NULL,
				email varchar(100) DEFAULT NULL,
				api_key_test varchar(35) DEFAULT NULL,
				api_key_live varchar(35) DEFAULT NULL,
				PRIMARY KEY  ( id ),
				UNIQUE KEY mollie_id ( mollie_id ),
				KEY organization_id ( organization_id )
			) $table_options;
			CREATE TABLE $wpdb->pronamic_pay_mollie_customers (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				mollie_id varchar(40) NOT NULL,
				organization_id bigint(20) unsigned DEFAULT NULL,
				profile_id bigint(20) unsigned DEFAULT NULL,
				test_mode tinyint(1) NOT NULL,
				email varchar(100) DEFAULT NULL,
				name varchar(255) DEFAULT NULL,
				PRIMARY KEY  ( id ),
				UNIQUE KEY mollie_id ( mollie_id ),
				KEY organization_id ( organization_id ),
				KEY profile_id ( profile_id ),
				KEY test_mode ( test_mode ),
				KEY email ( email )
			) $table_options;
			CREATE TABLE $wpdb->pronamic_pay_mollie_customer_users (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				customer_id bigint(20) unsigned NOT NULL,
				user_id bigint(20) unsigned NOT NULL,
				PRIMARY KEY  ( id ),
				UNIQUE KEY customer_user ( customer_id, user_id )
			) $table_options;
		";

		/**
		 * Execute.
		 *
		 * @link https://developer.wordpress.org/reference/functions/dbdelta/
		 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-admin/includes/upgrade.php#L2538-L2915
		 */
		\dbDelta( $queries );
	}

	/**
	 * Add foreign keys.
	 *
	 * @return void
	 */
	private function add_foreign_keys() {
		global $wpdb;

		/**
		 * Foreign keys.
		 *
		 * @link https://core.trac.wordpress.org/ticket/19207
		 * @link https://dev.mysql.com/doc/refman/5.6/en/create-table-foreign-keys.html
		 */
		$data = [
			(object) [
				'table' => $wpdb->pronamic_pay_mollie_profiles,
				'name'  => 'fk_profile_organization_id',
				'query' => "
					ALTER TABLE  $wpdb->pronamic_pay_mollie_profiles
					ADD CONSTRAINT fk_profile_organization_id
					FOREIGN KEY ( organization_id )
					REFERENCES $wpdb->pronamic_pay_mollie_organizations ( id )
					ON DELETE RESTRICT
					ON UPDATE RESTRICT
					;
				",
			],
			(object) [
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
			],
			(object) [
				'table' => $wpdb->pronamic_pay_mollie_customers,
				'name'  => 'fk_customer_profile_id',
				'query' => "
					ALTER TABLE $wpdb->pronamic_pay_mollie_customers
					ADD CONSTRAINT fk_customer_profile_id
					FOREIGN KEY ( profile_id )
					REFERENCES $wpdb->pronamic_pay_mollie_profiles ( id )
					ON DELETE RESTRICT
					ON UPDATE RESTRICT
					;
				",
			],
			(object) [
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
			],
			(object) [
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
			],
		];

		foreach ( $data as $item ) {
			try {
				$this->add_foreign_key( $item );
			} catch ( \Exception ) {
				// Foreign keys are not strictly required.
				continue;
			}
		}
	}

	/**
	 * Add specified foreign key.
	 *
	 * @param object $item Foreign key data.
	 * @return void
	 * @throws \Exception Throws exception when adding foreign key fails.
	 * @throws \InvalidArgumentException Throws invalid argument exception if item misses required `table`, `name` or `query` property.
	 */
	private function add_foreign_key( $item ) {
		global $wpdb;

		if ( ! \property_exists( $item, 'table' ) ) {
			throw new \InvalidArgumentException( 'Foreign key item must contain `table` property.' );
		}

		if ( ! \property_exists( $item, 'name' ) ) {
			throw new \InvalidArgumentException( 'Foreign key item must contain `name` property.' );
		}

		if ( ! \property_exists( $item, 'query' ) ) {
			throw new \InvalidArgumentException( 'Foreign key item must contain `query` property.' );
		}

		/**
		 * Suppress errors.
		 *
		 * We suppress errors because adding foreign keys to for example
		 * a `$wpdb->users` MyISAM table will trigger the following error:
		 *
		 * "Error in query (1005): Can't create table '●●●●●●●●. # Sql-●●●●●●●●●●' (errno: 150)"
		 *
		 * @link https://github.com/WordPress/WordPress/blob/5.3/wp-includes/wp-db.php#L1544-L1559
		 */
		$suppress_errors = $wpdb->suppress_errors( true );

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
					\esc_html( $item->name ),
					\esc_html( $wpdb->last_error )
				)
			);
		}

		$number_constraints = \intval( $result );

		if ( 0 === $number_constraints ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared.
			$result = $wpdb->query( $item->query );

			$wpdb->suppress_errors( $suppress_errors );

			if ( false === $result ) {
				throw new \Exception(
					\sprintf(
						'Could not add foreign key: %s, database error: %s.',
						\esc_html( $item->name ),
						\esc_html( $wpdb->last_error )
					)
				);
			}
		}

		$wpdb->suppress_errors( $suppress_errors );
	}

	/**
	 * Convert user meta.
	 *
	 * @return void
	 * @throws \Exception Throws exception when database update query fails.
	 */
	private function convert_user_meta() {
		global $wpdb;

		$query = "
			INSERT INTO $wpdb->pronamic_pay_mollie_customers (
				mollie_id,
				test_mode
			)
			SELECT
				usermeta.meta_value AS mollie_id,
				'_pronamic_pay_mollie_customer_id_test' = usermeta.meta_key AS test_mode
			FROM
				$wpdb->usermeta AS usermeta
					LEFT JOIN
				$wpdb->pronamic_pay_mollie_customers AS mollie_customer
						ON CAST( usermeta.meta_value AS BINARY ) = CAST( mollie_customer.mollie_id AS BINARY )
			WHERE
				usermeta.meta_key IN (
					'_pronamic_pay_mollie_customer_id',
					'_pronamic_pay_mollie_customer_id_test'
				)
					AND
				usermeta.meta_value != ''
					AND
				mollie_customer.id IS NULL
			;
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared.
		$result = $wpdb->query( $query );

		if ( false === $result ) {
			throw new \Exception(
				\sprintf(
					'Could not convert user meta, database error: %s.',
					\esc_html( $wpdb->last_error )
				)
			);
		}

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
				$wpdb->usermeta AS wp_user_meta
						ON CAST( wp_user_meta.meta_value AS BINARY ) = CAST( mollie_customer.mollie_id AS BINARY )
					INNER JOIN
				$wpdb->users AS wp_user
						ON wp_user_meta.user_id = wp_user.ID
					LEFT JOIN
				$wpdb->pronamic_pay_mollie_customer_users AS mollie_customer_user
						ON ( mollie_customer_user.customer_id = mollie_customer.id AND mollie_customer_user.user_id = wp_user.ID )
			WHERE
				wp_user_meta.meta_key IN (
					'_pronamic_pay_mollie_customer_id',
					'_pronamic_pay_mollie_customer_id_test'
				)
					AND
				wp_user_meta.meta_value != ''
					AND
				mollie_customer_user.id IS NULL
			;
		";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared.
		$result = $wpdb->query( $query );

		if ( false === $result ) {
			throw new \Exception(
				\sprintf(
					'Could not convert user meta, database error: %s.',
					\esc_html( $wpdb->last_error )
				)
			);
		}
	}
}
