<?php
/**
 * Address transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
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
	 * Transform from WordPress Pay core address.
	 *
	 * @param WordPressAddress $address Address.
	 * @return MollieAddress
	 * @throws InvalidArgumentException Throws exception on invalid arguments.
	 */
	public function transform_wp_to_mollie( WordPressAddress $address ): MollieAddress {
		$name = $address->get_name();

		$given_name        = null === $name ? null : $name->get_first_name();
		$family_name       = null === $name ? null : $name->get_last_name();
		$email             = $address->get_email();
		$street_and_number = $address->get_line_1();
		$postal_code       = $address->get_postal_code();
		$city              = $address->get_city();
		$country           = $address->get_country_code();

		if (
			null === $email
				&&
			\in_array( null, [ $street_and_number, $postal_code, $city, $country ], true )
		) {
			throw new InvalidArgumentException( 'Mollie requires an email or postal address.' );
		}

		$mollie_address = new MollieAddress(
			(string) $given_name,
			(string) $family_name,
			(string) $email,
			(string) $street_and_number,
			(string) $city,
			(string) $country
		);

		$phone = $address->get_phone();

		if ( null !== $phone ) {
			$phone_util = PhoneNumberUtil::getInstance();

			$phone_number_object = $phone_util->parse( $phone, $country );

			$phone = $phone_util->format( $phone_number_object, PhoneNumberFormat::E164 );
		}

		$mollie_address->organization_name = $address->get_company_name();
		$mollie_address->phone             = $phone;
		$mollie_address->street_additional = $address->get_line_2();
		$mollie_address->postal_code       = $address->get_postal_code();
		$mollie_address->region            = $address->get_region();

		return $mollie_address;
	}
}
