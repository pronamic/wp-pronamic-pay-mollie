<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie statuses constants tests
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.0.0
 * @see https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class StatusesTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @dataProvider status_matrix_provider
	 */
	public function test_transform( $mollie_status, $expected ) {
		$status = Statuses::transform( $mollie_status );

		$this->assertEquals( $expected, $status );
	}

	public function status_matrix_provider() {
		return array(
			array( Statuses::OPEN, \Pronamic_WP_Pay_Statuses::OPEN ),
			array( Statuses::CANCELLED, \Pronamic_WP_Pay_Statuses::CANCELLED ),
			array( Statuses::PAID_OUT, \Pronamic_WP_Pay_Statuses::SUCCESS ),
			array( Statuses::PAID, \Pronamic_WP_Pay_Statuses::SUCCESS ),
			array( Statuses::EXPIRED, \Pronamic_WP_Pay_Statuses::EXPIRED ),
			array( 'not existing status', null ),
		);
	}
}
