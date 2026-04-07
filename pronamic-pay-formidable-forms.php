<?php
/**
 * Plugin Name: Pronamic Pay Formidable Forms Add-On
 * Plugin URI: https://www.pronamic.eu/plugins/pronamic-pay-formidable-forms/
 * Description: Extend the Pronamic Pay plugin with Formidable Forms support to receive payments through a variety of payment providers.
 *
 * Version: 4.4.4
 * Requires at least: 4.7
 * Requires PHP: 7.4
 *
 * Author: Pronamic
 * Author URI: https://www.pronamic.eu/
 *
 * Text Domain: pronamic-pay-formidable-forms
 * Domain Path: /languages/
 *
 * License: GPL-3.0-or-later
 *
 * Requires Plugins: pronamic-ideal, formidable
 * Depends: wp-pay/core
 *
 * GitHub URI: https://github.com/pronamic/wp-pronamic-pay-formidable-forms
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\FormidableForms
 */

declare(strict_types=1);

namespace Pronamic\WordPress\Pay\Extensions\FormidableForms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload.
 */
$autoload_path = __DIR__ . '/vendor/autoload_packages.php';

if ( \file_exists( $autoload_path ) ) {
	require_once $autoload_path;
}

/**
 * Bootstrap.
 */
\add_filter(
	'pronamic_pay_plugin_integrations',
	function ( $integrations ) {
		foreach ( $integrations as $integration ) {
			if ( $integration instanceof Extension ) {
				return $integrations;
			}
		}

		$integrations[] = new Extension();

		return $integrations;
	}
);
