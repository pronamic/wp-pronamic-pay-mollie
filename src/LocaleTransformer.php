<?php
/**
 * Mollie transformer helper.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2023 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Mollie\Locales as MollieLocale;

/**
 * Locale transformer class
 */
class LocaleTransformer {
	/**
	 * Get Mollie locale by the specified WordPress locale.
	 *
	 * @param string|null $locale Locale string (en_US) to transform to Mollie locale.
	 * @return string|null
	 */
	public function transform_wp_to_mollie( $locale ) {
		if ( ! \is_string( $locale ) ) {
			return null;
		}

		/**
		 * Some browsers (Firefox) use language codes of only 2 characters, e.g. `nl` and `de`, which
		 * are not supported by Mollie. We therefore try `nl_nl` and `de_de` instead.
		 *
		 * @link https://github.com/pronamic/wp-pronamic-pay-mollie/issues/20
		 */
		if ( 2 === \strlen( $locale ) ) {
			$locale = $locale . '_' . $locale;
		}

		/**
		 * Supported locales.
		 *
		 * @var array<int, string>
		 */
		$supported = [
			MollieLocale::EN_GB,
			MollieLocale::EN_US,
			MollieLocale::NL_NL,
			MollieLocale::NL_BE,
			MollieLocale::FR_FR,
			MollieLocale::FR_BE,
			MollieLocale::DE_DE,
			MollieLocale::DE_AT,
			MollieLocale::DE_CH,
			MollieLocale::ES_ES,
			MollieLocale::CA_ES,
			MollieLocale::PT_PT,
			MollieLocale::IT_IT,
			MollieLocale::NB_NO,
			MollieLocale::SV_SE,
			MollieLocale::FI_FI,
			MollieLocale::DA_DK,
			MollieLocale::IS_IS,
			MollieLocale::HU_HU,
			MollieLocale::PL_PL,
			MollieLocale::LV_LV,
			MollieLocale::LT_LT,
		];

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
