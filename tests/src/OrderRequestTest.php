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

use WP_UnitTestCase;

/**
 * Order request test
 *
 * @author  Reüel van der Steege
 * @version 4.3.0
 * @since   4.3.0
 */
class OrderRequestTest extends WP_UnitTestCase {
	/**
	 * Order request.
	 *
	 * @var OrderRequest
	 */
	private $request;

	/**
	 * Setup.
	 */
	public function set_up() {
		parent::set_up();

		$lines = new Lines();

		$lines->new_line(
			'Test product',
			1,
			new Amount( 'EUR', '100.00' ),
			new Amount( 'EUR', '121.00' ),
			'21.00',
			new Amount( 'EUR', '21.00' )
		);

		$request = new OrderRequest(
			new Amount( 'EUR', '121.00' ),
			'12345',
			$lines,
			'nl_NL'
		);

		$billing_address = new Address(
			'Remco',
			'Tolsma',
			'info@pronamic.nl',
			'Burgemeester Wuiteweg 39b',
			'Drachten',
			'NL'
		);

		$billing_address->set_organization_name( 'Pronamic' );
		$billing_address->set_postal_code( '9203 KA' );
		$billing_address->set_region( 'Friesland' );
		$billing_address->set_phone( '085 40 11 580' );

		$request->set_billing_address( $billing_address );

		$request->redirect_url = 'https://example.com/mollie-redirect/';
		$request->webhook_url  = 'https://example.com/mollie-webhook/';
		$request->method       = Methods::KLARNA_PAY_LATER;

		$this->request = $request;
	}

	/**
	 * Test order request.
	 */
	public function test_order_request() {
		$this->assertEquals(
			[
				'amount'         => $this->request->get_amount()->get_json(),
				'orderNumber'    => '12345',
				'lines'          => $this->request->get_lines()->get_json(),
				'locale'         => 'nl_NL',
				'billingAddress' => $this->request->get_billing_address()->get_json(),
				'redirectUrl'    => 'https://example.com/mollie-redirect/',
				'webhookUrl'     => 'https://example.com/mollie-webhook/',
				'method'         => Methods::KLARNA_PAY_LATER,
			],
			$this->request->get_array()
		);
	}
}
