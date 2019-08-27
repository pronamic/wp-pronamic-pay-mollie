<?php
/**
 * Mollie statuses test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2019 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie statuses constants tests
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 * @see     https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class StatusesTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @param string $mollie_status Mollie status.
	 * @param string $expected      Expected value.
	 *
	 * @dataProvider status_matrix_provider
	 */
	public function test_transform( $mollie_status, $expected ) {
		$status = Statuses::transform( $mollie_status );

		$this->assertEquals( $expected, $status );
	}

	/**
	 * Status data provider.
	 *
	 * @return array
	 */
	public function status_matrix_provider() {
		return array(
			array( Statuses::AUTHORIZED, null ),
			array( Statuses::OPEN, \Pronamic\WordPress\Pay\Core\Statuses::OPEN ),
			array( Statuses::CANCELED, \Pronamic\WordPress\Pay\Core\Statuses::CANCELLED ),
			array( Statuses::ACTIVE, \Pronamic\WordPress\Pay\Core\Statuses::ACTIVE ),
			array( Statuses::PAID, \Pronamic\WordPress\Pay\Core\Statuses::SUCCESS ),
			array( Statuses::EXPIRED, \Pronamic\WordPress\Pay\Core\Statuses::EXPIRED ),
			array( Statuses::FAILED, \Pronamic\WordPress\Pay\Core\Statuses::FAILURE ),
			array( 'not existing status', null ),
		);
	}
}
