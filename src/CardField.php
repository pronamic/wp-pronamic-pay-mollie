<?php
/**
 * Mollie card field
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
 * Mollie card field class
 */
class CardField extends Field {
	/**
	 * Gateway.
	 *
	 * @var Gateway
	 */
	private $gateway;

	/**
	 * Construct card field.
	 *
	 * @param string  $id      ID.
	 * @param Gateway $gateway Gateway.
	 */
	public function __construct( $id, Gateway $gateway ) {
		parent::__construct( $id );

		$this->gateway = $gateway;
	}

	/**
	 * Get element.
	 *
	 * @return Element|null
	 */
	protected function get_element() {
		try {
			$profile_id = $this->gateway->get_profile_id();
		} catch ( \Exception $e ) {
			return null;
		}

		if ( null === $profile_id ) {
			return null;
		}

		$locale_transformer = new LocaleTransformer();

		\wp_enqueue_script( 'pronamic-pay-mollie' );

		\wp_enqueue_style( 'pronamic-pay-mollie' );

		$element = new Element( 'div' );

		$element->children[] = new Element(
			'div',
			[
				'class'                  => 'pronamic-pay-mollie-card-field',
				'data-mollie-profile-id' => $profile_id,
				'data-mollie-options'    => \wp_json_encode(
					[
						'locale'   => $locale_transformer->transform_wp_to_mollie( \get_locale() ),
						'testmode' => ( 'test' === $this->gateway->get_mode() ),
					]
				),
			]
		);

		$element->children[] = new Element(
			'input',
			[
				'id'   => $this->get_id(),
				'name' => $this->get_id(),
				'type' => 'hidden',
			]
		);

		return $element;
	}

	/**
	 * Serialize to JSON.
	 *
	 * @return array<string, string>
	 */
	public function jsonSerialize(): array {
		$data = parent::jsonSerialize();

		$data['type'] = 'html';

		return $data;
	}
}
