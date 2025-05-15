<?php
/**
 * Address transformer test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Address as PronamicAddress;
use Pronamic\WordPress\Pay\ContactName;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Address transformer test class
 */
class AddressTransformerTest extends TestCase {
	/**
	 * Test transform.
	 *
	 * @param string $phone      Phone number.
	 * @param string $phone_e164 Phone number E164.
	 *
	 * @dataProvider transform_provider
	 */
	public function test_transform( $phone, $phone_e164 ) {
		$name = new ContactName();

		$name->set_first_name( 'John' );
		$name->set_last_name( 'Doe' );

		$pronamic_address = new PronamicAddress();

		$pronamic_address->set_name( $name );
		$pronamic_address->set_email( 'john.doe@example.com' );
		$pronamic_address->set_phone( $phone );
		$pronamic_address->set_line_1( 'Kleine Kerkstraat 1' );
		$pronamic_address->set_postal_code( '8911 DK' );
		$pronamic_address->set_city( 'Leeuwarden' );
		$pronamic_address->set_country_code( 'NL' );

		$transformer = new AddressTransformer();

		$mollie_address = $transformer->transform_wp_to_mollie( $pronamic_address );

		$this->assertEquals( $pronamic_address->get_name()->get_first_name(), $mollie_address->given_name );
		$this->assertEquals( $pronamic_address->get_name()->get_last_name(), $mollie_address->family_name );
		$this->assertEquals( $pronamic_address->get_email(), $mollie_address->email );
		$this->assertEquals( $phone_e164, $mollie_address->phone );
		$this->assertEquals( $pronamic_address->get_line_1(), $mollie_address->street_and_number );
		$this->assertEquals( $pronamic_address->get_postal_code(), $mollie_address->postal_code );
		$this->assertEquals( $pronamic_address->get_city(), $mollie_address->city );
		$this->assertEquals( $pronamic_address->get_country_code(), $mollie_address->country );
	}

	/**
	 * Test transform with email only.
	 */
	public function test_transform_email() {
		$pronamic_address = new PronamicAddress();
		$transformer      = new AddressTransformer();

		$pronamic_address->set_email( 'john.doe@example.com' );

		$mollie_address = $transformer->transform_wp_to_mollie( $pronamic_address );

		$this->assertEquals( $pronamic_address->get_email(), $mollie_address->email );

		$json = $mollie_address->jsonSerialize();

		$this->assertObjectNotHasProperty( 'streetAndNumber', $json );
		$this->assertObjectNotHasProperty( 'postalCode', $json );
		$this->assertObjectNotHasProperty( 'city', $json );
		$this->assertObjectNotHasProperty( 'country', $json );
	}

	/**
	 * Test transform with postal address.
	 */
	public function test_transform_postal() {
		$pronamic_address = new PronamicAddress();
		$transformer      = new AddressTransformer();

		$pronamic_address->set_line_1( 'Kleine Kerkstraat 1' );
		$pronamic_address->set_postal_code( '8911 DK' );
		$pronamic_address->set_city( 'Leeuwarden' );
		$pronamic_address->set_country_code( 'NL' );

		$mollie_address = $transformer->transform_wp_to_mollie( $pronamic_address );

		$this->assertEquals( $pronamic_address->get_line_1(), $mollie_address->street_and_number );
		$this->assertEquals( $pronamic_address->get_postal_code(), $mollie_address->postal_code );
		$this->assertEquals( $pronamic_address->get_city(), $mollie_address->city );
		$this->assertEquals( $pronamic_address->get_country_code(), $mollie_address->country );

		$json = $mollie_address->jsonSerialize();

		$this->assertObjectNotHasProperty( 'email', $json );
	}

	/**
	 * Test transform of invalid address.
	 */
	public function test_invalid_transform() {
		$pronamic_address = new PronamicAddress();
		$transformer      = new AddressTransformer();

		$this->expectException( \InvalidArgumentException::class );

		$mollie_address = $transformer->transform_wp_to_mollie( $pronamic_address );
	}

	/**
	 * Transform provider.
	 *
	 * @return array
	 */
	public function transform_provider() {
		return [
			[ '1234567890', '+311234567890' ],
			[ '12 34 56 78 90', '+311234567890' ],
			[ '+321234567890', '+321234567890' ],
			[ '+491234567890', '+491234567890' ],
		];
	}
}
