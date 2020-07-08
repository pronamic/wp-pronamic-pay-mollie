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

/**
 * Payment request test
 *
 * @author  Remco Tolsma
 * @version 2.1.4
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
		$request = new PaymentRequest(
			new Amount( 'EUR', '100.00' ),
			'Test'
		);

		$request->redirect_url  = 'https://example.com/mollie-redirect/';
		$request->webhook_url   = 'https://example.com/mollie-webhook/';
		$request->method        = Methods::IDEAL;
		$request->locale        = Locales::NL_NL;
		$request->issuer        = 'ideal_INGBNL2A';
		$request->customer_id   = 'cst_8wmqcHMN4U';
		$request->sequence_type = Sequence::FIRST;
		$request->set_metadata( 'meta' );

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

	/**
	 * Test billing email.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 * @link https://help.mollie.com/hc/en-us/articles/115000711569-What-is-bank-transfer-and-how-does-it-work-
	 */
	public function test_billing_email() {
		$request = new PaymentRequest(
			new Amount( 'EUR', '100.00' ),
			'Test'
		);

		$request->set_billing_email( 'john@example.com' );

		$this->assertEquals( 'john@example.com', $request->get_billing_email() );

		$this->assertEquals(
			array(
				'amount'       => $request->amount->get_json(),
				'description'  => 'Test',
				'billingEmail' => 'john@example.com',
			),
			$request->get_array()
		);
	}

	/**
	 * Test billing metadata.
	 *
	 * @link https://docs.mollie.com/reference/v2/payments-api/create-payment
	 */
	public function test_metadata() {
		$request = new PaymentRequest(
			new Amount( 'EUR', '100.00' ),
			'Test'
		);

		$metadata = (object) array(
			'vat_number'   => 'NL123456789B01',
			'edd_order_id' => 1234,
			'gf_entry_id'  => 5678,
		);

		$request->set_metadata( $metadata );

		$this->assertEquals( $metadata, $request->get_metadata() );

		$this->assertEquals(
			array(
				'amount'      => $request->amount->get_json(),
				'description' => 'Test',
				'metadata'    => $metadata,
			),
			$request->get_array()
		);
	}

	/**
	 * Test recurring parameters.
	 */
	public function test_recurring_parameters() {
		$request = new PaymentRequest(
			new Amount( 'EUR', '100.00' ),
			'Test'
		);

		$request->set_mandate_id( 'mdt_h3gAaD5zP' );
		$request->set_sequence_type( 'recurring' );

		$this->assertEquals( 'mdt_h3gAaD5zP', $request->get_mandate_id() );
		$this->assertEquals( 'recurring', $request->get_sequence_type() );
	}
}
