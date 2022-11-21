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
	public function setup() {
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Version is part of URL.
		\wp_register_script(
			'pronamic-pay-mollie',
			'https://js.mollie.com/v1/mollie.js',
			[],
			null,
			false
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
	 * Mollie profile ID.
	 */
	private ?string $profile_id;

	public function set_profile_id( $profile_id ) {
		$this->profile_id = $profile_id;
	}

	/**
	 * Get HTML attributes.
	 *
	 * @return array<string, string>
	 */
	protected function get_html_attributes() : array {
		$attributes = parent::get_html_attributes();

		$attributes['class']                  = $this->get_id();
		$attributes['data-mollie-profile-id'] = $this->profile_id;

		return $attributes;
	}

	/**
	 * Render field.
	 *
	 * @return string
	 */
	public function render() : string {
		\wp_enqueue_script( 'pronamic-pay-mollie-components' );

		$element = new Element( 'div', $this->get_html_attributes() );

		return '<style>
		.wrapper {
		display: flex;
		margin: 40px auto 0;
		width: 80%;
		}

		.wrapper form {
		width: 100%;
		}

		.row {
		display: flex;
		width: 100%;
		}

		label {
		opacity: 0.6;
		font-size: 18px;
		padding-bottom: 7px;
		padding-top: 13px;
		font-weight: 500;
		display: block;
		}

		.mollie-component {
		background: #fff;
		box-shadow: 0px 1px 0px rgba(0, 0, 0, 0.1), 0px 2px 4px rgba(0, 0, 0, 0.1),
		0px 4px 8px rgba(0, 0, 0, 0.05);
		border-radius: 4px;
		padding: 13px;
		border: 1px solid transparent;
		transition: 0.15s border-color cubic-bezier(0.4, 0, 0.2, 1);
		font-weight: 500;
		}

		.mollie-component.has-focus {
		border-color: #0077ff;
		transition: 0.3s border-color cubic-bezier(0.4, 0, 0.2, 1);
		}

		.mollie-component.is-invalid {
		border-color: #ff1717;
		transition: 0.3s border-color cubic-bezier(0.4, 0, 0.2, 1);
		}

		.field-error {
		font-size: 12px;
		margin-top: 2px;
		color: #ff1717;
		font-weight: 400;
		}

		button.submit-button {
		width: 100%;
		border: 0;
		background: #0077ff;
		box-shadow: 0px 1px 0px rgba(0, 0, 0, 0.1), 0px 2px 4px rgba(0, 0, 0, 0.1),
		0px 4px 8px rgba(0, 0, 0, 0.05);
		border-radius: 4px;
		padding: 14px;
		color: #ffffff;
		font-weight: 600;
		font-size: 18px;
		opacity: 0.9;
		font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
		Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
		outline: 0;
		transition: 0.3s opacity cubic-bezier(0.4, 0, 0.2, 1);
		}

		button.submit-button:hover {
		opacity: 1;
		}

		.form-fields {
		margin-bottom: 24px;
		}

		.form-group {
		width: 100%;
		}

		.form-group--expiry-date {
		margin-right: 8px;
		}

		.form-group--verification-code {
		margin-left: 8px;
		}
		</style>' . $element->render();
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
