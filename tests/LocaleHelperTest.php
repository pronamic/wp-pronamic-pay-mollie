<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie locale helper tests
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.0
 * @see https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class LocaleHelperTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @dataProvider locale_matrix_provider
	 */
	public function test_get_locale( $locale, $expected ) {
		$mollie_locale = LocaleHelper::transform( $locale );

		$this->assertEquals( $expected, $mollie_locale );
	}

	public function locale_matrix_provider() {
		return array(
			// English
			array( 'en_US', Locales::EN ),
			array( 'en_GB', Locales::EN ),
			array( 'EN', Locales::EN ),
			array( 'en', Locales::EN ),
			// Dutch
			array( 'nl_NL', Locales::NL ),
			array( 'NL', Locales::NL ),
			array( 'nl', Locales::NL ),
			// Frisian
			array( 'FY', null ),
			array( 'fy', null ),
			// Other
			array( 'not existing locale', null ),
		);
	}
}
