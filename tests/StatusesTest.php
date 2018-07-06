<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie statuses constants tests
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.3
 * @since   1.0.0
 * @see     https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
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
			array( Statuses::OPEN, \Pronamic\WordPress\Pay\Core\Statuses::OPEN ),
			array( Statuses::CANCELLED, \Pronamic\WordPress\Pay\Core\Statuses::CANCELLED ),
			array( Statuses::PAID_OUT, \Pronamic\WordPress\Pay\Core\Statuses::SUCCESS ),
			array( Statuses::PAID, \Pronamic\WordPress\Pay\Core\Statuses::SUCCESS ),
			array( Statuses::EXPIRED, \Pronamic\WordPress\Pay\Core\Statuses::EXPIRED ),
			array( Statuses::FAILED, \Pronamic\WordPress\Pay\Core\Statuses::FAILURE ),
			array( 'not existing status', null ),
		);
	}
}
