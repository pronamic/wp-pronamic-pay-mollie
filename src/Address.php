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

/**
 * Address class
 *
 * @link https://docs.mollie.com/reference/v2/orders-api/create-order
 * @link https://docs.mollie.com/overview/common-data-types#address-object
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