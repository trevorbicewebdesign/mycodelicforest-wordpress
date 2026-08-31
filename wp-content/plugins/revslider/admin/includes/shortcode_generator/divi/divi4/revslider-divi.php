<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

if ( ! function_exists( 'sr7_initialize_extension' ) ):
/**
 * Creates the extension's main class instance.
 *
 * @since 1.0.0
 * @return void
 */
function sr7_initialize_extension() {
	if (defined('ET_CORE_VERSION') && version_compare(ET_CORE_VERSION, '5', ">=")) return;
	require_once RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/divi4/includes/RevsliderDivi.php';
}
add_action( 'divi_extensions_init', 'sr7_initialize_extension' );
endif;
