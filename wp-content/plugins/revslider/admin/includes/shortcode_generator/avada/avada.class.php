<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Avada / Fusion Builder integration.
 *
 * Avada ships its own Slider Revolution element, so this extends that one rather than adding a second.
 * What it extends it with is deliberately small: the element's params are the [sr7] attributes themselves,
 * and everything that edits them is the shared machinery - the module card, the pickers, quick edit and
 * one settings modal (CONTRACT B6). The only control of Avada's own left here is whether the live builder
 * renders the module or a card in its place.
 */
class RevSliderAvada {

	const STYLE_HANDLE = 'sr7-avada-css';

	/** the handle Fusion enqueues an element's custom settings view under */
	const VIEW_HANDLE = 'rev_slider_custom_settings_view';

	public static $registered;

	/** @return void */
	public static function init($registered) {
		self::$registered = $registered;
		if(self::is_avada_available() === false) return;

		add_filter('fusion_builder_map', ['RevSliderAvada', 'filter_revolution_slider_element'], 20);
		add_action('wp_enqueue_scripts', ['RevSliderAvada', 'enqueue_live_builder_styles'], 20);
		add_action('fusion_builder_enqueue_live_scripts', [ 'RevSliderAvada', 'enqueue_live_builder_assets' ], 5);
	}

	/** @return array */
	public static function filter_revolution_slider_element($module) {
		if(!is_array($module) || self::is_target_element($module) === false) return $module;

		$module['preview'] = self::template_path('sr7-preview.php');
		$module['icon'] = 'sr7--avada--icon';
		$module['preview_id'] = 'fusion-builder-block-module-sr7-avada-preview-template';
		//One view for both builders: Fusion falls back to this script when an element declares no separate
		//front end one, and both look the view up under the same name.
		$module['custom_settings_view_name'] = 'ModuleSettingsSR7View';
		$module['custom_settings_view_js'] = self::asset_url('js/sr7-avada.js');
		$module['admin_enqueue_css'] = self::asset_url('css/sr7-avada.css');
		$module['remove_from_atts'] = ['sr7_card'];	//somewhere to draw the card, never an attribute
		$module['params'] = self::extend_element_params($module['params'] ?? []);

		return $module;
	}

	/** @return void */
	public static function enqueue_live_builder_styles() {
		if(self::is_avada_available() === false) return;
		if(self::is_builder_frame() === false && self::is_preview_frame() === false) return;

		wp_enqueue_style('sr7-avada-live', self::asset_url( 'css/sr7-avada-live.css' ), [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/avada/assets/css/sr7-avada-live.css'));
	}

	/** @return void */
	public static function enqueue_live_builder_assets() {
		wp_enqueue_style(self::STYLE_HANDLE, self::asset_url( 'css/sr7-avada.css' ), [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/avada/assets/css/sr7-avada.css'));

		wp_register_script('tpgsap', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tpgsap.js', [], RevSliderFunctions::asset_time('public/js/libs/tpgsap.js'), true);
		wp_register_script('tp-tools', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tptools.js', [], RevSliderFunctions::asset_time('public/js/libs/tptools.js'), true);
		wp_register_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], RevSliderFunctions::asset_time('admin/assets/js/tools/tools.js'), true);
		wp_register_script('sr7-tools-shortcode', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/shortcode.js', [], RevSliderFunctions::asset_time('admin/assets/js/tools/shortcode.js'), true);

		//Fusion enqueues the settings view by this handle later on, with no dependencies of its own.
		//Registering it here first is what puts the shared module in front of it.
		wp_register_script(self::VIEW_HANDLE, self::asset_url('js/sr7-avada.js'), ['jquery', 'tp-tools', 'tpgsap', 'revbuilder-backend', 'sr7-tools-shortcode'], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/avada/assets/js/sr7-avada.js'), true);
		wp_localize_script(self::VIEW_HANDLE, 'SR7AvadaLiveData', ['registered' => self::$registered]);

		if(!RevSliderBuilders::user_may_edit()) return;
		//The live builder is a front end page: nothing there has told the JavaScript where to call or where
		//the plugin lives. One definition of that, shared with every other integration (B5).
		wp_localize_script('tp-tools', 'SR7ShortcodeData', RevSliderBuilders::client_config());
	}

	/**
	 * Our options, added to Avada's own element.
	 *
	 * Avada's element has exactly one control of its own - a dropdown of slider aliases - and it is hidden
	 * here rather than removed, because it is the field the alias lives in. Everything else the element
	 * carries is storage: the shortcode's own attributes, and what the card shows about the module. Per
	 * D1, none of it is presented as a control.
	 *
	 * @return array
	 */
	protected static function extend_element_params($params) {
		if(!is_array($params)) $params = [];

		$existing = [];
		foreach($params as $key => $param) {
			if(!is_array($param) || !isset($param['param_name'])) continue;

			$existing[$param['param_name']] = true;
			if($param['param_name'] === 'alias') {
				//A dropdown cannot hold an alias it has not been told about - a module imported from a
				//template a moment ago is not in it - and there is nothing left for the user to pick from
				//here anyway. A plain field takes whatever the picker chose.
				$params[$key]['type']	= 'textfield';
				$params[$key]['value']	= '';
				$params[$key]['hidden']	= true;
				unset($params[$key]['description']);
			}
		}

		foreach(self::get_custom_params() as $param) {
			if(isset($existing[$param['param_name']])) continue;
			$params[] = $param;
		}

		return $params;
	}

	/** @return array */
	protected static function get_custom_params() {
		$params = [
			//where sr7-avada.js draws the module card, and with it every way into the module
			[
				'type'			=> 'info',
				'param_name'	=> 'sr7_card',
				'content'		=> '<div class="sr--avada--card"></div>'
			],
			[
				'type'			=> 'radio_button_set',
				'heading'		=> esc_attr__( 'Live Preview Render', 'revslider' ),
				'param_name'	=> 'live_preview',
				'value'			=> [
					'yes'	=> esc_attr__( 'Yes', 'revslider' ),
					'no'	=> esc_attr__( 'No', 'revslider' ),
				],
				'default'		=> 'yes'
			]
		];

		//How the module is embedded, and what the card knows about it. Both are written by the shared
		//machinery and neither is edited here, so they are stored rather than shown.
		$storage = ['usage', 'modal', 'zindex', 'fullwidth', 'fullheight', 'offset', 'wrapperid', 'class',
			'title', 'm_id', 'type', 'slides', 'image', 'color', 'not_found', 'premium'];

		foreach($storage as $name) {
			$params[] = [
				'type'			=> 'textfield',
				'param_name'	=> $name,
				'value'			=> '',
				'hidden'		=> true
			];
		}

		return $params;
	}

	/** @return bool */
	protected static function is_target_element($module) {
		return ($module['shortcode'] ?? '') === 'rev_slider';
	}

	/** @return bool */
	protected static function is_avada_available() {
		return defined('FUSION_BUILDER_VERSION') || class_exists('FusionBuilder') || function_exists('fusion_builder_map');
	}

	/** @return bool */
	protected static function is_builder_frame() {
		return function_exists('fusion_is_builder_frame') && fusion_is_builder_frame();
	}

	/** @return bool */
	protected static function is_preview_frame() {
		return function_exists('fusion_is_preview_frame') && fusion_is_preview_frame();
	}

	/** @return string */
	protected static function asset_url($path) {
		return RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/avada/assets/' . ltrim($path, '/');
	}

	/** @return string */
	protected static function template_path($file) {
		return RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/avada/templates/' . ltrim($file, '/');
	}
}
