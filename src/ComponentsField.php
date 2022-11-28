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
	 * Get HTML attributes.
	 *
	 * @return array<string, string>
	 */
	protected function get_html_attributes() : array {
		$attributes = parent::get_html_attributes();

		$locale_transformer = new LocaleTransformer();

		$attributes['class']                  = $this->get_id();
		$attributes['data-mollie-profile-id'] = $this->profile_id;
		$attributes['data-mollie-locale']     = $locale_transformer->transform_wp_to_mollie( \get_locale() );
		$attributes['data-mollie-testmode']   = true;

		return $attributes;
	}

	/**
	 * Render field.
	 */
	public function render() : string {
		if ( ! $this->should_render() ) {
			return '';
		}

		\wp_enqueue_script( 'pronamic-pay-mollie-components' );

		\wp_enqueue_style( 'pronamic-pay-mollie-components' );

		$element = new Element( 'div', $this->get_html_attributes() );

		return $element->render();
	}

	/**
	 * Should render component.
	 *
	 * @return bool
	 */
	private function should_render(): bool {
		$post_id = \get_the_ID();

		$should_render = [
			// Payment gateway test meta box.
			'pronamic_gateway' === \get_post_type( $post_id ),

			// WooCommerce.
			\did_action( 'woocommerce_checkout_order_review' ) || \did_action( 'woocommerce_checkout_update_order_review' ),
		];

		return \in_array( true, $should_render, true );
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
