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

/**
 * Mollie customer
 *
 * @link    https://docs.mollie.com/reference/v2/customers-api/create-customer
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   2.1.0
 */
class Customer {
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
	 * Get array.
	 *
	 * @return array<string>
	 */
	public function get_array() {
		$array = array(
			'name'   => $this->get_name(),
			'email'  => $this->get_email(),
			'locale' => $this->get_locale(),
		);

		/*
		 * Array filter will remove values NULL, FALSE and empty strings ('')
		 */
		$array = array_filter( $array );

		return $array;
	}

	/**
	 * Create customer from object.
	 *
	 * @param object $object Object.
	 * @return Customer
	 */
	public static function from_object( $object ) {
		$customer = new self();

		if ( property_exists( $object, 'id' ) ) {
			$customer->set_id( $object->id );
		}

		if ( property_exists( $object, 'mode' ) ) {
			$customer->set_mode( $object->mode );
		}

		if ( property_exists( $object, 'name' ) ) {
			$customer->set_name( $object->name );
		}

		if ( property_exists( $object, 'email' ) ) {
			$customer->set_email( $object->email );
		}

		if ( property_exists( $object, 'locale' ) ) {
			$customer->set_locale( $object->locale );
		}

		return $customer;
	}
}
