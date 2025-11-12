<?php
/**
 * Address transformer test
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2025 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Address as MollieAddress;
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

		$this->assertNotNull( $mollie_address );
		$this->assertEquals( $phone_e164, $mollie_address->phone );
	}

	/**
	 * Test transform with incomplete address.
	 */
	public function test_transform_incomplete_address() {
		$name = new ContactName();

		$name->set_first_name( 'John' );
		$name->set_last_name( 'Doe' );

		$pronamic_address = new PronamicAddress();

		$pronamic_address->set_name( $name );
		$pronamic_address->set_email( 'john.doe@example.com' );
		$pronamic_address->set_phone( '1234567890' );
		$pronamic_address->set_country_code( 'NL' );

		$transformer = new AddressTransformer();

		$mollie_address = $transformer->transform_wp_to_mollie( $pronamic_address );

		$this->assertNotNull( $mollie_address );
		$this->assertNull( $mollie_address->street_and_number );
		$this->assertNull( $mollie_address->postal_code );
		$this->assertNull( $mollie_address->city );
		$this->assertNull( $mollie_address->country );
	}

	/**
	 * Test transform with complete address.
	 */
	public function test_transform_complete_address() {
		$name = new ContactName();

		$name->set_first_name( 'John' );
		$name->set_last_name( 'Doe' );

		$pronamic_address = new PronamicAddress();

		$pronamic_address->set_name( $name );
		$pronamic_address->set_email( 'john.doe@example.com' );
		$pronamic_address->set_phone( '1234567890' );
		$pronamic_address->set_line_1( 'Kleine Kerkstraat 1' );
		$pronamic_address->set_postal_code( '8911 DK' );
		$pronamic_address->set_city( 'Leeuwarden' );
		$pronamic_address->set_country_code( 'NL' );

		$transformer = new AddressTransformer();

		$mollie_address = $transformer->transform_wp_to_mollie( $pronamic_address );

		$this->assertNotNull( $mollie_address );
		$this->assertEquals( 'Kleine Kerkstraat 1', $mollie_address->street_and_number );
		$this->assertEquals( '8911 DK', $mollie_address->postal_code );
		$this->assertEquals( 'Leeuwarden', $mollie_address->city );
		$this->assertEquals( 'NL', $mollie_address->country );
	}

	/**
	 * Test transform with no email and no address.
	 */
	public function test_transform_no_email_no_address() {
		$pronamic_address = new PronamicAddress();

		$transformer = new AddressTransformer();

		$mollie_address = $transformer->transform_wp_to_mollie( $pronamic_address );

		$this->assertNull( $mollie_address );
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
