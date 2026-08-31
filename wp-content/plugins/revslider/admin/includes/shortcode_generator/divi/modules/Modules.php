<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

namespace MEE\Modules;

if(!defined('ABSPATH')) exit();

use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

add_action(
	'init',
	function () {
		if ( ! class_exists( ModuleRegistration::class ) ) {
			return;
		}

		require_once RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/modules/RevsliderModule/RevsliderModule.php';

		$module_class = '\MEE\Modules\RevsliderModule\RevsliderModule';

		$module_class::register();
	},
	20
);
