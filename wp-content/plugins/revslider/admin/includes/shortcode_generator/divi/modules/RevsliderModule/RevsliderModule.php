<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace MEE\Modules\RevsliderModule;

if(!defined('ABSPATH')) exit();

require_once RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/modules/RevsliderModule/RevsliderModuleTrait/ModuleClassnamesTrait.php';
require_once RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/modules/RevsliderModule/RevsliderModuleTrait/ModuleScriptDataTrait.php';
require_once RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/modules/RevsliderModule/RevsliderModuleTrait/ModuleStylesTrait.php';
require_once RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/modules/RevsliderModule/RevsliderModuleTrait/RenderCallbackTrait.php';

use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Minimal RevsliderModule registration entry point.
 */
class RevsliderModule {
	use RevsliderModuleTrait\RenderCallbackTrait;

	/**
	 * Register the module with Divi 5.
	 * @return void registers the module with Divi 5
	 */
	public static function register(): void {
		$module_json_folder_path = RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/modules-json/revslider-module/';

		ModuleRegistration::register_module(
			$module_json_folder_path,
			[
				'render_callback' => [ self::class, 'render_callback' ],
			]
		);
	}
}
