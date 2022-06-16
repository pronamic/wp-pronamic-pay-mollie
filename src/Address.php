<?php
/**
 * Address
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use InvalidArgumentException;
use Pronamic\WordPress\Pay\Address as Core_Address;
use Pronamic\WordPress\Pay\Core\Util;

/**
 * Address
 *
 * @author  Reüel van der Steege
 * @version 4.3.0
 * @since   4.3.0
 */
/**
 * Address class
 *
 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment#payment-method-specific-parameters
 */
class Address {
	/**
	 * The person’s organization, if applicable.
	 *
	 * @var string|null
	 */
	private ?string $organization_name = null;

	/**
	 * The title of the person, for example Mr. or Mrs.
	 *
	 * @var string|null
	 */
	private ?string $title = null;

	/**
	 * The given name (first name) of the person.
	 *
	 * @var string
	 */
	private string $given_name;

	/**
	 * Organization name.
	 *
	 * @var string
	 */
	private string $family_name;

	/**
	 * The email address of the person.
	 *
	 * @var string
	 */
	private string $email;

	/**
	 * The phone number of the person. Some payment methods require this information. If
	 * you have it, you should pass it so that your customer does not have to enter it again
	 * in the checkout. Must be in the E.164 format. For example +31208202070.
	 *
	 * @link https://en.wikipedia.org/wiki/E.164
	 * @var string|null
	 */
	private ?string $phone = null;

	/**
	 * Street and number.
	 *
	 * @var string
	 */
	private string $street_and_number;

	/**
	 * Additional street details.
	 *
	 * @var string|null
	 */
	private ?string $street_additional = null;

	/**
	 * Postal code.
	 *
	 * @var string|null
	 */
	private ?string $postal_code = null;

	/**
	 * City.
	 *
	 * @var string
	 */
	private string $city;

	/**
	 * Region.
	 *
	 * @var string|null
	 */
	private ?string $region = null;

	/**
	 * The country of the address in ISO 3166-1 alpha-2 format.
	 *
	 * @link https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
	 * @var string
	 */
	private string $country;

	/**
	 * Construct address.
	 *
	 * @param string $given_name        Given name.
	 * @param string $family_name       Family name.
	 * @param string $email             Email address.
	 * @param string $street_and_number Street and house number.
	 * @param string $city              City.
	 * @param string $country           Country.
	 * @throws InvalidArgumentException Throws exception on invalid arguments.
	 */
	public function __construct( string $given_name, string $family_name, string $email, string $street_and_number, string $city, string $country ) {
		/*
		 * The two-character country code of the address.
		 *
		 * The permitted country codes are defined in ISO-3166-1 alpha-2 (e.g. 'NL').
		 */
		if ( 2 !== strlen( $country ) ) {
			throw new InvalidArgumentException(
				sprintf(
					'Given country `%s` not ISO 3166-1 alpha-2 value.',
					$country
				)
			);
		}

		// Ok.
		$this->given_name        = $given_name;
		$this->family_name       = $family_name;
		$this->email             = $email;
		$this->street_and_number = $street_and_number;
		$this->city              = $city;
		$this->country           = $country;
	}

	/**
	 * Get organization_name.
	 *
	 * @return string|null
	 */
	public function get_organization_name(): ?string {
		return $this->organization_name;
	}

	/**
	 * Set organization_name.
	 *
	 * @param string|null $organization_name Organization name.
	 */
	public function set_organization_name( ?string $organization_name ): void {
		$this->organization_name = $organization_name;
	}

	/**
	 * Get title.
	 *
	 * @return mixed
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Set title.
	 *
	 * @param string|null $title Title.
	 */
	public function set_title( ?string $title ): void {
		$this->title = $title;
	}

	/**
	 * Get given name.
	 *
	 * @return string
	 */
	public function get_given_name() : string {
		return $this->given_name;
	}

	/**
	 * Get family name.
	 *
	 * @return string
	 */
	public function get_family_name() : string {
		return $this->family_name;
	}

	/**
	 * Get family name.
	 *
	 * @return string
	 */
	public function get_email() : string {
		return $this->email;
	}

	/**
	 * Get phone number.
	 *
	 * @var string
	 */
	public function get_phone() : ?string {
		return $this->phone;
	}

	/**
	 * Set phone.
	 *
	 * @param string|null $phone Phone number.
	 */
	public function set_phone( ?string $phone ) : void {
		$this->phone = $phone;
	}

	/**
	 * Get street and number.
	 *
	 * @return string
	 */
	public function get_street_and_number() : string {
		return $this->street_and_number;
	}

	/**
	 * Get street additional.
	 *
	 * @return string|null
	 */
	public function get_street_additional(): ?string {
		return $this->street_additional;
	}

	/**
	 * Set street additional.
	 *
	 * @param string|null $street_additional Additional street details.
	 */
	public function set_street_additional( ?string $street_additional ): void {
		$this->street_additional = $street_additional;
	}

	/**
	 * Get postal code.
	 */
	public function get_postal_code() : ?string {
		return $this->postal_code;
	}

	/**
	 * Set postal code.
	 *
	 * @param string|null $postal_code Postal code.
	 */
	public function set_postal_code( ?string $postal_code ) : void {
		$this->postal_code = $postal_code;
	}

	/**
	 * Get city.
	 */
	public function get_city() : string {
		return $this->city;
	}

	/**
	 * Get state or province.
	 */
	public function get_region() : ?string {
		return $this->region;
	}

	/**
	 * Set region.
	 *
	 * @param string|null $region Region.
	 */
	public function set_region( ?string $region ) : void {
		$this->region = $region;
	}

	/**
	 * Get country.
	 */
	public function get_country() : string {
		return $this->country;
	}

	/**
	 * Create address from object.
	 *
	 * @param mixed $json JSON.
	 * @return Address
	 * @throws InvalidArgumentException Throws invalid argument exception when JSON is not an object.
	 */
	public static function from_json( $json ) : Address {
		if ( ! is_object( $json ) ) {
			throw new InvalidArgumentException( 'JSON value must be an object.' );
		}

		if ( ! property_exists( $json, 'givenName' ) ) {
			throw new InvalidArgumentException( 'Object must contain `givenName` property.' );
		}

		if ( ! property_exists( $json, 'familyName' ) ) {
			throw new InvalidArgumentException( 'Object must contain `familyName` property.' );
		}

		if ( ! property_exists( $json, 'email' ) ) {
			throw new InvalidArgumentException( 'Object must contain `email` property.' );
		}

		if ( ! property_exists( $json, 'streetAndNumber' ) ) {
			throw new InvalidArgumentException( 'Object must contain `streetAndNumber` property.' );
		}

		if ( ! property_exists( $json, 'city' ) ) {
			throw new InvalidArgumentException( 'Object must contain `city` property.' );
		}

		if ( ! property_exists( $json, 'country' ) ) {
			throw new InvalidArgumentException( 'Object must contain `country` property.' );
		}

		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		$address = new self(
			$json->givenName,
			$json->familyName,
			$json->email,
			$json->streetAndNumber,
			$json->city,
			$json->country,
		);

		if ( property_exists( $json, 'organizationName' ) ) {
			$address->organization_name = $json->organizationName;
		}

		if ( property_exists( $json, 'title' ) ) {
			$address->title = $json->title;
		}

		if ( property_exists( $json, 'phone' ) ) {
			$address->phone = $json->phone;
		}

		if ( property_exists( $json, 'streetAdditional' ) ) {
			$address->street_additional = $json->streetAdditional;
		}

		if ( property_exists( $json, 'postalCode' ) ) {
			$address->postal_code = $json->postalCode;
		}

		if ( property_exists( $json, 'region' ) ) {
			$address->region = $json->region;
		}

		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Mollie JSON object.

		return $address;
	}

	/**
	 * Transform from WordPress Pay core address.
	 *
	 * @param Core_Address $address Address.
	 * @return Address
	 * @throws InvalidArgumentException Throws exception on invalid arguments.
	 */
	public static function from_wp_address( Core_Address $address ): Address {
		$name = $address->get_name();

		$given_name        = null === $name ? null : $name->get_first_name();
		$family_name       = null === $name ? null : $name->get_last_name();
		$email             = $address->get_email();
		$street_and_number = $address->get_line_1();
		$city              = $address->get_city();
		$country           = $address->get_country_code();

		if ( empty( $given_name ) ) {
			throw new InvalidArgumentException( 'Given name can not be empty.' );
		}

		if ( empty( $family_name ) ) {
			throw new InvalidArgumentException( 'Family name can not be empty.' );
		}

		if ( empty( $email ) ) {
			throw new InvalidArgumentException( 'Email address can not be empty.' );
		}

		if ( empty( $street_and_number ) ) {
			throw new InvalidArgumentException( 'Street and number can not be empty.' );
		}

		if ( empty( $city ) ) {
			throw new InvalidArgumentException( 'City can not be empty.' );
		}

		if ( empty( $country ) ) {
			throw new InvalidArgumentException( 'Country can not be empty.' );
		}

		$mollie_address = new self( $given_name, $family_name, $email, $street_and_number, $city, $country );

		$mollie_address->organization_name = $address->get_company_name();
		$mollie_address->phone             = $address->get_phone();
		$mollie_address->street_additional = $address->get_line_2();
		$mollie_address->postal_code       = $address->get_postal_code();
		$mollie_address->region            = $address->get_region();

		return $mollie_address;
	}

	/**
	 * Get JSON.
	 *
	 * @return object
	 */
	public function get_json() {
		$properties = $this->filter_null(
			[
				'organizationName' => $this->organization_name,
				'title'            => $this->title,
				'givenName'        => $this->given_name,
				'familyName'       => $this->family_name,
				'email'            => $this->email,
				'phone'            => $this->phone,
				'streetAndNumber'  => $this->street_and_number,
				'streetAdditional' => $this->street_additional,
				'postalCode'       => $this->postal_code,
				'city'             => $this->city,
				'region'           => $this->region,
				'country'          => $this->country,
			]
		);

		$object = (object) $properties;

		return $object;
	}


	/**
	 * Filter null.
	 *
	 * @param array<int|string, mixed> $array Array to filter null values from.
	 * @return array<int|string, mixed>
	 */
	private function filter_null( array $array ) : array {
		return array_filter( $array, [ $this, 'is_not_null' ] );
	}

	/**
	 * Check if value is not null.
	 *
	 * @param mixed $value Value.
	 * @return boolean True if value is not null, false otherwise.
	 */
	private function is_not_null( $value ) : bool {
		return ( null !== $value );
	}
}
