<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Payment request test
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class PaymentRequestTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test payment request.
	 */
	public function test_payment_request() {
		$request = new PaymentRequest();

		$request->amount         = 100.00;
		$request->description    = 'Test';
		$request->redirect_url   = 'https://example.com/mollie-redirect/';
		$request->webhook_url    = 'https://example.com/mollie-webhook/';
		$request->method         = Methods::IDEAL;
		$request->meta_data      = 'meta';
		$request->locale         = Locales::NL;
		$request->issuer         = 'ideal_INGBNL2A';
		$request->customer_id    = 'cst_8wmqcHMN4U';
		$request->recurring_type = Recurring::FIRST;

		$this->assertEquals( array(
			'amount'        => '100.00',
			'description'   => 'Test',
			'redirectUrl'   => 'https://example.com/mollie-redirect/',
			'webhookUrl'    => 'https://example.com/mollie-webhook/',
			'method'        => 'ideal',
			'metadata'      => 'meta',
			'locale'        => 'nl',
			'issuer'        => 'ideal_INGBNL2A',
			'customerId'    => 'cst_8wmqcHMN4U',
			'recurringType' => 'first',
		), $request->get_array() );
	}
}
