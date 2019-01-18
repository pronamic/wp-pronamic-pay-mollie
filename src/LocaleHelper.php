<?php

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie locale helper
 * Description:
 * Copyright: 2005-2019 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.0
 * @since   1.0.0
 */
class LocaleHelper {
	/**
	 * Get Mollie locale by the specified WordPress locale.
	 *
	 * @param string $locale Locale string (en_US) to transform to Mollie locale.
	 *
	 * @return string|null
	 */
	public static function transform( $locale ) {
		if ( ! is_string( $locale ) ) {
			return null;
		}

		// Supported locales.
		$supported = array(
			Locales::DE,
			Locales::EN,
			Locales::FR,
			Locales::ES,
			Locales::NL,
		);

		// Sub string.
		$locale = substr( $locale, 0, 2 );

		// Lower case.
		$locale = strtolower( $locale );

		// Is supported?
		if ( in_array( $locale, $supported, true ) ) {
			return $locale;
		}

		return null;
	}
}
