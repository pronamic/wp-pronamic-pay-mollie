<?php
/**
 * Mollie Components field
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Mollie
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use Pronamic\WordPress\Html\Element;
use Pronamic\WordPress\Pay\Fields\Field;

/**
 * HTML field class
 */
class ComponentsField extends Field {
	/**
	 * Mollie profile ID.
	 */
	private ?string $profile_id;

	/**
	 * Setup field.
	 */
	public function setup(): void {
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Version is part of URL.
		\wp_register_script(
			'pronamic-pay-mollie',
			'https://js.mollie.com/v1/mollie.js',
			[],
			null,
			false
		);

		$file = '../css/components.css';

		\wp_register_style(
			'pronamic-pay-mollie-components',
			\plugins_url( $file, __FILE__ ),
			[],
			\hash_file( 'crc32b', __DIR__ . '/' . $file ),
		);

		$file = '../js/dist/components.js';

		\wp_register_script(
			'pronamic-pay-mollie-components',
			\plugins_url( $file, __FILE__ ),
			[ 'pronamic-pay-mollie' ],
			\hash_file( 'crc32b', __DIR__ . '/' . $file ),
			true
		);
	}

	/**
	 * Set Mollie profile ID.
	 *
	 * @param string $profile_id Mollie profile ID.
	 */
	public function set_profile_id( string $profile_id ): void {
		$this->profile_id = $profile_id;
	}

	/**
	 * Get element.
	 * 
	 * @return Element|null
	 */
	protected function get_element() {
		\wp_enqueue_script( 'pronamic-pay-mollie-components' );

		\wp_enqueue_style( 'pronamic-pay-mollie-components' );

		$locale_transformer = new LocaleTransformer();

		$element = new Element(
			'div',
			[
				'class'                  => $this->get_id(),
				'data-mollie-profile-id' => $this->profile_id,
				'data-mollie-locale'     => $locale_transformer->transform_wp_to_mollie( \get_locale() ),
				'data-mollie-testmode'   => true,
			]
		);

		return $element;
	}

	/**
	 * Serialize to JSON.
	 */
	public function jsonSerialize() : array {
		$data = parent::jsonSerialize();

		$data['type'] = 'html';

		return $data;
	}
}
