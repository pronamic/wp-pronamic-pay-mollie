<?php
/**
 * Address transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use InvalidArgumentException;
use Pronamic\WordPress\Mollie\Address as MollieAddress;
use Pronamic\WordPress\Pay\Address as WordPressAddress;

/**
 * Address transformer class
 */
class AddressTransformer {
	/**
	 * Format phone number to E.164 format.
	 *
	 * @param string|null $phone   Phone number.
	 * @param string|null $country Country code.
	 * @return string|null
	 */
	private function format_phone( ?string $phone, ?string $country ): ?string {
		if ( null === $phone ) {
			return null;
		}

		try {
			$phone_util = PhoneNumberUtil::getInstance();

			$phone_number_object = $phone_util->parse( $phone, $country );

			return $phone_util->format( $phone_number_object, PhoneNumberFormat::E164 );
		} catch ( \libphonenumber\NumberParseException ) {
			return null;
		}
	}

	/**
	 * Check if the address has a valid postal address.
	 *
	 * @param WordPressAddress $address Address.
	 * @return bool
	 */
	private function has_valid_postal_address( WordPressAddress $address ): bool {
		if ( empty( $address->get_line_1() ) ) {
			return false;
		}

		if ( empty( $address->get_postal_code() ) ) {
			return false;
		}

		if ( empty( $address->get_city() ) ) {
			return false;
		}

		if ( empty( $address->get_country_code() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Transform from WordPress Pay core address.
	 *
	 * @param WordPressAddress $address Address.
	 * @return MollieAddress|null
	 * @throws InvalidArgumentException Throws exception on invalid arguments.
	 */
	public function transform_wp_to_mollie( WordPressAddress $address ): ?MollieAddress {
		$has_valid_postal_address = $this->has_valid_postal_address( $address );
		$has_email                = ! empty( $address->get_email() );

		if ( ! $has_valid_postal_address && ! $has_email ) {
			return null;
		}

		$name = $address->get_name();

		$mollie_address = new MollieAddress();

		$mollie_address->given_name        = null === $name ? null : $name->get_first_name();
		$mollie_address->family_name       = null === $name ? null : $name->get_last_name();
		$mollie_address->organization_name = $address->get_company_name();
		$mollie_address->email             = $address->get_email();

		$phone   = $address->get_phone();
		$country = $address->get_country_code();

		$mollie_address->phone = $this->format_phone( $phone, $country );

		// A valid postal address consists of streetAndNumber, postalCode, city and country.
		// If these are not present, we can not send the address to Mollie, unless an e-mail address is present.
		// https://docs.mollie.com/reference/v2/orders-api/create-order
		if ( $has_valid_postal_address ) {
			$mollie_address->street_and_number = $address->get_line_1();
			$mollie_address->street_additional = $address->get_line_2();
			$mollie_address->postal_code       = $address->get_postal_code();
			$mollie_address->city              = $address->get_city();
			$mollie_address->region            = $address->get_region();
			$mollie_address->country           = $country;
		}

		return $mollie_address;
	}
}
