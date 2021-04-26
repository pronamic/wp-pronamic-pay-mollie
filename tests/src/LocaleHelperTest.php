<?php
/**
 * Mollie locale test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie locale helper tests
 * Description:
 * Copyright: 2005-2021 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.1.0
 * @since   1.0.0
 * @see     https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class LocaleHelperTest extends \PHPUnit_Framework_TestCase {
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
		return array(
			// English.
			array( 'en_US', Locales::EN_US ),
			array( 'en_us', Locales::EN_US ),
			array( 'en_GB', null ),
			array( 'EN', null ),
			array( 'en', null ),

			// Dutch.
			array( 'nl_NL', Locales::NL_NL ),
			array( 'NL', null ),
			array( 'nl', null ),

			// Frisian.
			array( 'FY', null ),
			array( 'fy', null ),

			// Other.
			array( 'not existing locale', null ),
			array( null, null ),
		);
	}
}
