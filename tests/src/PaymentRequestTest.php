<?php
/**
 * Mollie payment request test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Money\Money;

/**
 * Payment request test
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
 */
class PaymentRequestTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Payment request.
	 *
	 * @var PaymentRequest
	 */
	private $request;

	/**
	 * Setup.
	 */
	public function setUp() {
		$request = new PaymentRequest();

		$amount = new Amount( 'EUR', '100.00' );

		$request->amount        = $amount;
		$request->description   = 'Test';
		$request->redirect_url  = 'https://example.com/mollie-redirect/';
		$request->webhook_url   = 'https://example.com/mollie-webhook/';
		$request->method        = Methods::IDEAL;
		$request->meta_data     = 'meta';
		$request->locale        = Locales::NL_NL;
		$request->issuer        = 'ideal_INGBNL2A';
		$request->customer_id   = 'cst_8wmqcHMN4U';
		$request->sequence_type = Sequence::FIRST;

		$this->request = $request;
	}
	/**
	 * Test payment request.
	 */
	public function test_payment_request() {
		$this->assertEquals(
			array(
				'amount'       => $this->request->amount->get_json(),
				'description'  => 'Test',
				'redirectUrl'  => 'https://example.com/mollie-redirect/',
				'webhookUrl'   => 'https://example.com/mollie-webhook/',
				'method'       => 'ideal',
				'metadata'     => 'meta',
				'locale'       => 'nl_NL',
				'issuer'       => 'ideal_INGBNL2A',
				'customerId'   => 'cst_8wmqcHMN4U',
				'sequenceType' => 'first',
			),
			$this->request->get_array()
		);
	}

	/**
	 * Test due date.
	 *
	 * @throws \Exception Throws exception on date error.
	 */
	public function test_due_date() {
		$due_date = new \DateTime( '+12 days' );

		$this->request->set_due_date( $due_date );

		$this->assertEquals(
			array(
				'amount'       => $this->request->amount->get_json(),
				'description'  => 'Test',
				'redirectUrl'  => 'https://example.com/mollie-redirect/',
				'webhookUrl'   => 'https://example.com/mollie-webhook/',
				'method'       => 'ideal',
				'metadata'     => 'meta',
				'locale'       => 'nl_NL',
				'issuer'       => 'ideal_INGBNL2A',
				'dueDate'      => $due_date->format( 'Y-m-d' ),
				'customerId'   => 'cst_8wmqcHMN4U',
				'sequenceType' => 'first',
			),
			$this->request->get_array()
		);
	}
}
