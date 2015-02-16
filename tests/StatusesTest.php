<?php

/**
 * Title: Mollie statuses constants tests
 * Description:
 * Copyright: Copyright (c) 2005 - 2015
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0.0
 * @see https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class Pronamic_WP_Pay_Mollie_StatusesTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider status_matrix_provider
	 */
	public function test_transform( $mollie_status, $expected ) {
		$status = Pronamic_WP_Pay_Mollie_Statuses::transform( $mollie_status );

		$this->assertEquals( $expected, $status );
	}

	public function status_matrix_provider() {
		return array(
			array( Pronamic_WP_Pay_Mollie_Statuses::OPEN, Pronamic_WP_Pay_Statuses::OPEN ),
			array( Pronamic_WP_Pay_Mollie_Statuses::CANCELLED, Pronamic_WP_Pay_Statuses::CANCELLED ),
			array( Pronamic_WP_Pay_Mollie_Statuses::PAID_OUT, Pronamic_WP_Pay_Statuses::SUCCESS ),
			array( Pronamic_WP_Pay_Mollie_Statuses::PAID, Pronamic_WP_Pay_Statuses::SUCCESS ),
			array( Pronamic_WP_Pay_Mollie_Statuses::EXPIRED, Pronamic_WP_Pay_Statuses::EXPIRED ),
			array( 'not existing status', null ),
		);
	}
}
