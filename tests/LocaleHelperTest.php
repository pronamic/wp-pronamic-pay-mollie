<?php

/**
 * Title: Mollie locale helper tests
 * Description:
 * Copyright: Copyright (c) 2005 - 2015
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.1.0
 * @see https://www.mollie.nl/support/documentatie/betaaldiensten/ideal/en/
 */
class Pronamic_WP_Pay_Mollie_LocaleHelperTest extends PHPUnit_Framework_TestCase {
	/**
	 * Test transform.
	 *
	 * @dataProvider locale_matrix_provider
	 */
	public function test_get_locale( $locale, $expected ) {
		$mollie_locale = Pronamic_WP_Pay_Mollie_LocaleHelper::transform( $locale );

		$this->assertEquals( $expected, $mollie_locale );
	}

	public function locale_matrix_provider() {
		return array(
			// English
			array( 'en_US', Pronamic_WP_Pay_Mollie_Locales::EN ),
			array( 'en_GB', Pronamic_WP_Pay_Mollie_Locales::EN ),
			array( 'EN', Pronamic_WP_Pay_Mollie_Locales::EN ),
			array( 'en', Pronamic_WP_Pay_Mollie_Locales::EN ),
			// Dutch
			array( 'nl_NL', Pronamic_WP_Pay_Mollie_Locales::NL ),
			array( 'NL', Pronamic_WP_Pay_Mollie_Locales::NL ),
			array( 'nl', Pronamic_WP_Pay_Mollie_Locales::NL ),
			// Frisian
			array( 'FY', null ),
			array( 'fy', null ),
			// Other
			array( 'not existing locale', null ),
		);
	}
}
