<?php

/**
 * Title: Mollie statuses constants tests
 * Description:
 * Copyright: Copyright (c) 2005 - 2014
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0.0
 * @see https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class Pronamic_WP_Pay_Mollie_StatusesTest extends PHPUnit_Framework_TestCase {
	/**
	 * @dataProvider statusMatrixProvider
	 */
	public function testTransform( $mollieStatus, $expected ) {
		$status = Pronamic_WP_Pay_Mollie_Statuses::transform( $mollieStatus );

		$this->assertEquals( $expected, $status );
	}

	public function statusMatrixProvider() {
		return array(
			array( Pronamic_WP_Pay_Mollie_Statuses::OPEN, Pronamic_WP_Pay_Statuses::OPEN ),
			array( Pronamic_WP_Pay_Mollie_Statuses::CANCELLED, Pronamic_WP_Pay_Statuses::CANCELLED ),
			array( Pronamic_WP_Pay_Mollie_Statuses::PAID_OUT, Pronamic_WP_Pay_Statuses::SUCCESS ),
			array( Pronamic_WP_Pay_Mollie_Statuses::PAID, Pronamic_WP_Pay_Statuses::SUCCESS ),
			array( Pronamic_WP_Pay_Mollie_Statuses::EXPIRED, Pronamic_WP_Pay_Statuses::EXPIRED ),
		);
    }
}
