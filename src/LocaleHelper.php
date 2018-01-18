<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie locale helper
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Remco Tolsma
 * @version 1.1.0
 */
class LocaleHelper {
	/**
	 * Get Mollie locale by the specified WordPress locale.
	 *
	 * @return string|null
	 */
	public static function transform( $locale ) {
		// Supported locales
		$supported = array(
			Locales::DE,
			Locales::EN,
			Locales::FR,
			Locales::ES,
			Locales::NL,
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
