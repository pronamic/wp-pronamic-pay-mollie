<?php
/**
 * Mollie locale helper.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

/**
 * Title: Mollie locale helper
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Remco Tolsma
 * @version 2.0.9
 * @since   1.0.0
 */
class LocaleHelper {
	/**
	 * Get Mollie locale by the specified WordPress locale.
	 *
	 * @param string|null $locale Locale string (en_US) to transform to Mollie locale.
	 * @return string|null
	 */
	public static function transform( $locale ) {
		if ( ! \is_string( $locale ) ) {
			return null;
		}

		/**
		 * Supported locales.
		 *
		 * @var array<int, string>
		 */
		$supported = array(
			Locales::EN_US,
			Locales::NL_NL,
			Locales::NL_BE,
			Locales::FR_FR,
			Locales::FR_BE,
			Locales::DE_DE,
			Locales::DE_AT,
			Locales::DE_CH,
			Locales::ES_ES,
			Locales::CA_ES,
			Locales::PT_PT,
			Locales::IT_IT,
			Locales::NB_NO,
			Locales::SV_SE,
			Locales::FI_FI,
			Locales::DA_DK,
			Locales::IS_IS,
			Locales::HU_HU,
			Locales::PL_PL,
			Locales::LV_LV,
			Locales::LT_LT,
		);

		// Lowercase.
		$locale = \strtolower( $locale );

		// Is supported?
		$supported_lowercase = \array_map( 'strtolower', $supported );

		$search = \array_search( $locale, $supported_lowercase, true );

		// Locale not supported.
		if ( false === $search ) {
			return null;
		}

		/**
		 * As with all internal PHP functions as of 5.3.0, `array_search()`
		 * returns `NULL` if invalid parameters are passed to it.
		 *
		 * @link https://www.php.net/array_search
		 */
		if ( \array_key_exists( $search, $supported ) ) {
			return $supported[ $search ];
		}

		return null;
	}
}
