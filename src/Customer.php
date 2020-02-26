<?php
/**
 * Mollie customer.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Mollie customer
 *
 * @link    https://docs.mollie.com/reference/v2/customers-api/create-customer
 * @author  Remco Tolsma
 * @version 3.0.0
 * @since   3.0.0
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
	 * @var string
	 */
	public $locale;

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
	 */
	public function set_locale( $locale ) {
		$this->locale = $locale;
	}

	/**
	 * Get array.
	 *
	 * @return array
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
}
