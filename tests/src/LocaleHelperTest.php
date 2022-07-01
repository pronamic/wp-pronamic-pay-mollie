<?php
/**
 * Mollie locale test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: Mollie locale helper tests
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 * @see     https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class LocaleHelperTest extends TestCase {
	/**
	 * Test transform.
	 *
	 * @param string $locale   Locale.
	 * @param string $expected Expected locale.
	 *
	 * @dataProvider locale_matrix_provider
	 */
	public function test_get_locale( $locale, $expected ) {
		$mollie_locale = LocaleHelper::transform( $locale );

		$this->assertEquals( $expected, $mollie_locale );
	}

	/**
	 * Locale data provider.
	 *
	 * @return array
	 */
	public function locale_matrix_provider() {
		return [
			// English.
			[ 'en_US', Locales::EN_US ],
			[ 'en_us', Locales::EN_US ],
			[ 'en_GB', null ],
			[ 'EN', null ],
			[ 'en', null ],

			// Dutch.
			[ 'nl_NL', Locales::NL_NL ],
			[ 'NL', null ],
			[ 'nl', null ],

			// Frisian.
			[ 'FY', null ],
			[ 'fy', null ],

			// Other.
			[ 'not existing locale', null ],
			[ null, null ],
		];
	}
}
