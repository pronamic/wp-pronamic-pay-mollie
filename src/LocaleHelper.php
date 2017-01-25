<?php

/**
 * Title: Mollie locale helper
 * Description:
 * Copyright: Copyright (c) 2005 - 2017
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.0
 */
class Pronamic_WP_Pay_Mollie_LocaleHelper {
	/**
	 * Get Mollie locale by the specified WordPress locale.
	 *
	 * @return string|null
	 */
	public static function transform( $locale ) {
		// Supported locales
		$supported = array(
			Pronamic_WP_Pay_Mollie_Locales::DE,
			Pronamic_WP_Pay_Mollie_Locales::EN,
			Pronamic_WP_Pay_Mollie_Locales::FR,
			Pronamic_WP_Pay_Mollie_Locales::ES,
			Pronamic_WP_Pay_Mollie_Locales::NL,
		);

		// Sub string
		$locale = substr( $locale, 0, 2 );

		// Lower case
		$locale = strtolower( $locale );

		// Is supported?
		if ( in_array( $locale, $supported, true ) ) {
			return $locale;
		}

		return null;
	}
}
