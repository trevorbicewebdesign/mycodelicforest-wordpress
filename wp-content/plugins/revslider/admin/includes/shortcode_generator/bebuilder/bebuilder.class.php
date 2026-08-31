<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * BeTheme / BeBuilder integration: loads the slider assets inside the live builder so a slider previews
 * correctly while editing.
 */
class RevSliderBeBuilder {

	const SCRIPT_HANDLE = 'sr7-bebuilder';
	const STYLE_HANDLE = 'sr7-bebuilder-css';

	/** @return void */
	public static function init() {
		add_action('admin_enqueue_scripts', ['RevSliderBeBuilder', 'enqueue_admin_assets']);
		add_action('mfn_header_enqueue', ['RevSliderBeBuilder', 'enqueue_live_builder_assets'], 20);
		add_action('mfn_footer_enqueue', ['RevSliderBeBuilder', 'render_live_builder_files'], 20);
	}

	/** @return void */
	public static function enqueue_admin_assets($hook_suffix) {
		if(self::is_bebuilder_available() === false || self::is_live_builder_request() === true) return;
		if(!in_array($hook_suffix, ['post.php', 'post-new.php'], true)) return;

		self::enqueue_assets();
	}

	/** @return void */
	public static function enqueue_live_builder_assets() {
		if(self::is_bebuilder_available() === false || self::is_live_builder_request() === false) return;

		self::enqueue_assets();
	}

	/** @return void */
	public static function render_live_builder_files() {
		if(self::is_bebuilder_available() === false || self::is_live_builder_request() === false) return;
	}

	/** @return void */
	protected static function enqueue_assets() {
		RevSliderShortcodeWizard::add_scripts();
		wp_enqueue_script('tpgsap', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tpgsap.js', [], RevSliderFunctions::asset_time('public/js/libs/tpgsap.js'), ['strategy' => 'async']);
		wp_enqueue_script('_tpt', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tptools.js', [], RevSliderFunctions::asset_time('public/js/libs/tptools.js'), ['strategy' => 'async']);
		wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], RevSliderFunctions::asset_time('admin/assets/js/tools/tools.js'), false);
		wp_enqueue_script('sr7-tools-shortcode', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/shortcode.js', [], RevSliderFunctions::asset_time('admin/assets/js/tools/shortcode.js'), true);
		wp_enqueue_style(self::STYLE_HANDLE, RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/bebuilder/assets/css/sr7-bebuilder.css', [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/bebuilder/assets/css/sr7-bebuilder.css'));
		wp_enqueue_script(self::SCRIPT_HANDLE, RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/bebuilder/assets/js/sr7-bebuilder.js', ['revbuilder-backend', 'sr7-tools-shortcode'], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/bebuilder/assets/js/sr7-bebuilder.js'), true);
		wp_localize_script(self::SCRIPT_HANDLE, 'SR7ShortcodeData', [
			'ajaxurl'		=> admin_url('admin-ajax.php'),
			'plugin_url'	=> RS_PLUGIN_URL_CLEAN,
			'wp_upload_url'	=> rtrim(wp_upload_dir()['baseurl'] ?? '', '/') . '/',
		]);
	}

	/** @return bool */
	protected static function is_bebuilder_available() {
		return defined('MFN_THEME_VERSION') || class_exists('MfnVisualBuilder') || function_exists('mfn_opts_get');
	}

	/** @return bool */
	protected static function is_live_builder_request() {
		$action = sanitize_key(wp_unslash($_GET['action'] ?? ''));

		return $action === apply_filters('betheme_slug', 'mfn') . '-live-builder';
	}
}
