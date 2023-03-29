<?php
/**
 * Address transformer
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Gateways\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

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
		$city              = $address->get_city();
		$country           = $address->get_country_code();

		if ( null === $given_name ) {
			throw new InvalidArgumentException( 'Mollie requires a given name in an address.' );
		}

		if ( null === $family_name ) {
			throw new InvalidArgumentException( 'Mollie requires a family name in an address.' );
		}

		if ( null === $email ) {
			throw new InvalidArgumentException( 'Mollie requires an email in an address.' );
		}

		if ( null === $street_and_number ) {
			throw new InvalidArgumentException( 'Mollie requires a street and number in an address.' );
		}

		if ( null === $city ) {
			throw new InvalidArgumentException( 'Mollie requires a city in an address.' );
		}

		if ( null === $country ) {
			throw new InvalidArgumentException( 'Mollie requires a country in an address.' );
		}

		$mollie_address = new MollieAddress( $given_name, $family_name, $email, $street_and_number, $city, $country );

		$mollie_address->organization_name = $address->get_company_name();
		$mollie_address->phone             = $address->get_phone();
		$mollie_address->street_additional = $address->get_line_2();
		$mollie_address->postal_code       = $address->get_postal_code();
		$mollie_address->region            = $address->get_region();

		return $mollie_address;
	}
}
