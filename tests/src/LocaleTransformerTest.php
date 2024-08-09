<?php
/**
 * Mollie locale test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Locales as MollieLocale;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Title: Mollie locale helper tests
 * Description:
 * Copyright: 2005-2024 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 * @see     https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class LocaleTransformerTest extends TestCase {
	/**
	 * Test transform.
	 *
	 * @param string $locale   Locale.
	 * @param string $expected Expected locale.
	 * @return void
	 * @dataProvider locale_matrix_provider
	 */
	public function test_get_locale( $locale, $expected ) {
		$transformer = new LocaleTransformer();

		$mollie_locale = $transformer->transform_wp_to_mollie( $locale );

		$this->assertEquals( $expected, $mollie_locale );
	}

	/**
	 * Locale data provider.
	 *
	 * @return array<int,array<int,string|null>>
	 */
	public function locale_matrix_provider() {
		return [
			// English.
			[ 'en_US', MollieLocale::EN_US ],
			[ 'en_us', MollieLocale::EN_US ],
			[ 'en_GB', MollieLocale::EN_GB ],
			[ 'en_AU', null ],
			[ 'EN', null ],
			[ 'en', null ],

			// Dutch.
			[ 'nl_NL', MollieLocale::NL_NL ],
			[ 'NL', MollieLocale::NL_NL ],
			[ 'nl', MollieLocale::NL_NL ],
			[ 'nl_BE', MollieLocale::NL_BE ],
			[ 'be', null ],

			// Frisian.
			[ 'FY', null ],
			[ 'fy', null ],

			// Other.
			[ 'not existing locale', null ],
			[ null, null ],
		];
	}
}
