<?php
/**
 * Mollie statuses test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Payments\PaymentStatus;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: Mollie statuses constants tests
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 * @see     https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class StatusesTest extends TestCase {
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
		return [
			[ Statuses::AUTHORIZED, PaymentStatus::AUTHORIZED ],
			[ Statuses::OPEN, PaymentStatus::OPEN ],
			[ Statuses::CANCELED, PaymentStatus::CANCELLED ],
			[ Statuses::PAID, PaymentStatus::SUCCESS ],
			[ Statuses::EXPIRED, PaymentStatus::EXPIRED ],
			[ Statuses::FAILED, PaymentStatus::FAILURE ],
			[ 'not existing status', null ],
		];
	}
}
