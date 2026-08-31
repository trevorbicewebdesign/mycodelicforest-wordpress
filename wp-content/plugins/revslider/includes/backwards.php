<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * backwards compatibility prior 6.0.0 code
 * @START
 *
 * none of the shims below are used anywhere inside the plugin - they exist purely so third party themes and
 * older addons written against the pre 6.0 API keep working. Every entry point now reports itself through
 * _deprecated_function(), which is silent in production and only surfaces with WP_DEBUG. That makes it
 * possible to actually find out whether anything still calls them, instead of carrying them forever because
 * nobody can tell.
 **/

if(!function_exists('rs_deprecated_shim')){
	/**
	 * report the use of a pre 6.0 compatibility shim
	 *
	 * @param string $old what the caller used, e.g. 'RevSliderBase::check_file_in_zip()'
	 * @param string $new what to use instead
	 * @return void
	 */
	function rs_deprecated_shim($old, $new){
		if(!function_exists('_deprecated_function')) return;

		_deprecated_function($old, '6.0.0', $new);
	}
}

class RevSliderBase {

	/** @return mixed */
	public static function check_file_in_zip($d_path, $image, $alias, $alreadyImported = false){
		rs_deprecated_shim('RevSliderBase::check_file_in_zip()', 'RevSliderFunctions::check_file_in_zip()');

		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');

		return $f->check_file_in_zip($d_path, $image, $alias, $alreadyImported, $add_path = false);
	}
}

class RevSliderFunctionsWP {
	/** @return string */
	public static function getImageUrlFromPath($url){
		rs_deprecated_shim('RevSliderFunctionsWP::getImageUrlFromPath()', 'RevSliderFunctions::get_image_url_from_path()');

		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		return $f->get_image_url_from_path($url);
	}

	/** @return int|false */
	public static function get_image_id_by_url($image_url){
		rs_deprecated_shim('RevSliderFunctionsWP::get_image_id_by_url()', 'RevSliderFunctions::get_image_id_by_url()');

		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		return $f->get_image_id_by_url($image_url);
	}
}

class RevSliderOperations {
	/** @return array */
	public function getGeneralSettingsValues(){
		rs_deprecated_shim('RevSliderOperations::getGeneralSettingsValues()', 'RevSliderFunctions::get_global_settings()');

		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		return $f->get_global_settings();
	}
}

class RevSlider extends RevSliderSlider {
	public function __construct(){
		rs_deprecated_shim('RevSlider', 'RevSliderSlider');

		parent::__construct();
	}
}

class UniteFunctionsRev extends RevSliderFunctions {
	public function __construct(){
		rs_deprecated_shim('UniteFunctionsRev', 'RevSliderFunctions');

		parent::__construct();
	}
}

class RevSliderWpml extends RevSliderFunctions {
	public function __construct(){
		rs_deprecated_shim('RevSliderWpml', 'RevSliderFunctions');

		parent::__construct();
	}
}

if(!function_exists('set_revslider_as_theme')){
	/** @return void no-op, kept so old theme code calling it does not fatal */
	function set_revslider_as_theme(){
	}
}

/**
 * backwards compatibility prior 6.0.0 code
 * @END
 **/