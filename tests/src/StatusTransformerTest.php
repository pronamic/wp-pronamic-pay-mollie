<?php
/**
 * Mollie statuses test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Pay\Payments\PaymentStatus as WordPressStatus;
use Pronamic\WordPress\Mollie\Statuses as MollieStatus;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: Mollie statuses constants tests
 * Description:
 * Copyright: 2005-2026 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 * @see     https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class StatusTransformerTest extends TestCase {
	/**
	 * Test transform.
	 *
	 * @param string $mollie_status Mollie status.
	 * @param string $expected      Expected value.
	 *
	 * @dataProvider status_matrix_provider
	 */
	public function test_transform( $mollie_status, $expected ) {
		$transformer = new StatusTransformer();

		$status = $transformer->transform_mollie_to_wp( $mollie_status );

		$this->assertEquals( $expected, $status );
	}

	/**
	 * Status data provider.
	 *
	 * @return array
	 */
	public function status_matrix_provider() {
		return [
			[ MollieStatus::AUTHORIZED, WordPressStatus::AUTHORIZED ],
			[ MollieStatus::OPEN, WordPressStatus::OPEN ],
			[ MollieStatus::CANCELED, WordPressStatus::CANCELLED ],
			[ MollieStatus::PAID, WordPressStatus::SUCCESS ],
			[ MollieStatus::EXPIRED, WordPressStatus::EXPIRED ],
			[ MollieStatus::FAILED, WordPressStatus::FAILURE ],
			[ 'not existing status', null ],
		];
	}
}
