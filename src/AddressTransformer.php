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
	 * Transform from WordPress Pay core address.
	 *
	 * @param WordPressAddress $address Address.
	 * @return MollieAddress
	 * @throws InvalidArgumentException Throws exception on invalid arguments.
	 */
	public function transform_wp_to_mollie( WordPressAddress $address ): MollieAddress {
		$name = $address->get_name();

		$mollie_address = new MollieAddress();

		$mollie_address->given_name        = null === $name ? null : $name->get_first_name();
		$mollie_address->family_name       = null === $name ? null : $name->get_last_name();
		$mollie_address->organization_name = $address->get_company_name();
		$mollie_address->street_and_number = $address->get_line_1();
		$mollie_address->street_additional = $address->get_line_2();
		$mollie_address->postal_code       = $address->get_postal_code();
		$mollie_address->email             = $address->get_email();

		$phone   = $address->get_phone();
		$country = $address->get_country_code();

		$mollie_address->phone = $this->format_phone( $phone, $country );

		$mollie_address->city    = $address->get_city();
		$mollie_address->region  = $address->get_region();
		$mollie_address->country = $country;

		return $mollie_address;
	}
}
