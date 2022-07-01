<?php
/**
 * Mollie order request test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Address as Core_Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Number\Number;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Order request test
 *
 * @author  Reüel van der Steege
 * @version 4.3.0
 * @since   4.3.0
 */
class OrderRequestTest extends TestCase {
	/**
	 * Order request.
	 *
	 * @var OrderRequest
	 */
	private $request;

	/**
	 * Setup.
	 */
	public function set_up() : void {
		parent::set_up();

		$lines = new Lines();

		$lines->new_line(
			'Test product',
			1,
			new Amount( 'EUR', '100.00' ),
			new Amount( 'EUR', '121.00' ),
			Number::from_mixed( '21.00' ),
			new Amount( 'EUR', '21.00' )
		);

		$request = new OrderRequest(
			new Amount( 'EUR', '121.00' ),
			'12345',
			$lines,
			'nl_NL'
		);

		$billing_address = new Core_Address();

		$name = new ContactName();

		$name->set_first_name( 'Remco' );
		$name->set_last_name( 'Tolsma' );

		$billing_address->set_name( $name );
		$billing_address->set_company_name( 'Pronamic' );
		$billing_address->set_email( 'info@pronamic.nl' );
		$billing_address->set_line_1( 'Burgemeester Wuiteweg 39b' );
		$billing_address->set_city( 'Drachten' );
		$billing_address->set_country_code( 'NL' );
		$billing_address->set_postal_code( '9203 KA' );
		$billing_address->set_region( 'Friesland' );
		$billing_address->set_phone( '085 40 11 580' );

		$request->set_billing_address( Address::from_wp_address( $billing_address ) );

		$request->redirect_url = 'https://example.com/mollie-redirect/';
		$request->webhook_url  = 'https://example.com/mollie-webhook/';
		$request->method       = Methods::KLARNA_PAY_LATER;

		$this->request = $request;
	}

	/**
	 * Test order request.
	 *
	 * @return void
	 */
	public function test_order_request() {
		$this->assertEquals(
			(object) [
				'amount'         => (object) [
					'currency' => 'EUR',
					'value'    => '121.00',
				],
				'orderNumber'    => '12345',
				'lines'          => [
					(object) [
						'name'        => 'Test product',
						'quantity'    => 1,
						'unitPrice'   => (object) [
							'currency' => 'EUR',
							'value'    => '100.00',
						],
						'totalAmount' => (object) [
							'currency' => 'EUR',
							'value'    => '121.00',
						],
						'vatRate'     => '21.00',
						'vatAmount'   => (object) [
							'currency' => 'EUR',
							'value'    => '21.00',
						],
					],
				],
				'locale'         => 'nl_NL',
				'billingAddress' => (object) [
					'organizationName' => 'Pronamic',
					'givenName'        => 'Remco',
					'familyName'       => 'Tolsma',
					'email'            => 'info@pronamic.nl',
					'phone'            => '085 40 11 580',
					'streetAndNumber'  => 'Burgemeester Wuiteweg 39b',
					'postalCode'       => '9203 KA',
					'city'             => 'Drachten',
					'region'           => 'Friesland',
					'country'          => 'NL',
				],
				'redirectUrl'    => 'https://example.com/mollie-redirect/',
				'webhookUrl'     => 'https://example.com/mollie-webhook/',
				'method'         => Methods::KLARNA_PAY_LATER,
			],
			$this->request->jsonSerialize()
		);
	}
}
