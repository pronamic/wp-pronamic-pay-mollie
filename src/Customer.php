<?php
/**
 * Mollie customer.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use JsonSerializable;

/**
 * Customer class
 *
 * @link https://docs.mollie.com/reference/v2/customers-api/create-customer
 */
class Customer implements JsonSerializable {
	/**
	 * ID.
	 *
	 * @var string|null
	 */
	private $id;

	/**
	 * Mode.
	 *
	 * @var string|null
	 */
	private $mode;

	/**
	 * Name.
	 *
	 * @var string|null
	 */
	private $name;

	/**
	 * Email.
	 *
	 * @var string|null
	 */
	private $email;

	/**
	 * Locale.
	 *
	 * @var string|null
	 */
	public $locale;

	/**
	 * Construct Mollie customer.
	 *
	 * @param string|null $id Mollie customer ID.
	 */
	public function __construct( $id = null ) {
		$this->set_id( $id );
	}

	/**
	 * Get ID.
	 *
	 * @return string|null
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param string|null $id ID.
	 * @return void
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Get mode.
	 *
	 * @return string|null
	 */
	public function get_mode() {
		return $this->mode;
	}

	/**
	 * Set mode.
	 *
	 * @param string|null $mode Mode.
	 * @return void
	 */
	public function set_mode( $mode ) {
		$this->mode = $mode;
	}

	/**
	 * Get name.
	 *
	 * @return string|null
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Set name.
	 *
	 * @param string|null $name Name.
	 * @return void
	 */
	public function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Get email.
	 *
	 * @return string|null
	 */
	public function get_email() {
		return $this->email;
	}

	/**
	 * Set email.
	 *
	 * @param string|null $email Email.
	 * @return void
	 */
	public function set_email( $email ) {
		$this->email = $email;
	}

	/**
	 * Get locale.
	 *
	 * @return string|null
	 */
	public function get_locale() {
		return $this->locale;
	}

	/**
	 * Set locale.
	 *
	 * @param string|null $locale Locale.
	 * @return void
	 */
	public function set_locale( $locale ) {
		$this->locale = $locale;
	}

	/**
	 * JSON serialize.
	 *
	 * @return mixed
	 */
	public function jsonSerialize() {
		$object_builder = new ObjectBuilder();

		$object_builder->set_optional( 'name', $this->get_name() );
		$object_builder->set_optional( 'email', $this->get_email() );
		$object_builder->set_optional( 'locale', $this->get_locale() );

		return $object_builder->jsonSerialize();
	}

	/**
	 * Create customer from object.
	 *
	 * @param object $object Object.
	 * @return Customer
	 */
	public static function from_object( $object ) {
		$customer = new self();

		$object_access = new ObjectAccess( $object );

		$customer->set_id( $object_access->get_optional( 'id' ) );
		$customer->set_mode( $object_access->get_optional( 'mode' ) );
		$customer->set_name( $object_access->get_optional( 'name' ) );
		$customer->set_email( $object_access->get_optional( 'email' ) );
		$customer->set_locale( $object_access->get_optional( 'locale' ) );

		return $customer;
	}
}
