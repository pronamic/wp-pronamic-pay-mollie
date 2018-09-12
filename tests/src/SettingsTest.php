<?php
/**
 * Mollie settings test.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2018 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay
 */

namespace Pronamic\WordPress\Pay\Gateways\Mollie;

use WP_UnitTestCase;

/**
 * Settings test.
 *
 * @author Reüel van der Steege
 * @version 2.0.5
 */
class SettingsTest extends WP_UnitTestCase {
	/**
	 * Test construct.
	 */
	public function test_construct() {
		$settings = new Settings();

		$this->assertTrue( has_filter( 'pronamic_pay_gateway_sections' ), array( $settings, 'sections' ) );
		$this->assertTrue( has_filter( 'pronamic_pay_gateway_fields' ), array( $settings, 'fields' ) );
	}

	/**
	 * Test sections.
	 */
	public function test_sections() {
		$settings = new Settings();

		$sections = array();

		$sections = $settings->sections( $sections );

		$this->assertInternalType( 'array', $sections );
	}

	/**
	 * Test fields.
	 */
	public function test_fields() {
		$settings = new Settings();

		$fields = array();

		$fields = $settings->fields( $fields );

		$this->assertInternalType( 'array', $fields );
	}
}
