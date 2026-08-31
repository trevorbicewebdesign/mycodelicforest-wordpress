<?php
/*
Plugin Name: Slider Revolution
Plugin URI: https://www.sliderrevolution.com/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=info
Description: Slider Revolution - More than just a WordPress Slider
Author: ThemePunch
Text Domain: revslider
Domain Path: /languages
Version: 7.1.6
Author URI: https://themepunch.com/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=info
*/

// If this file is called directly, abort.
if(!defined('WPINC')){ die; }

if(class_exists('RevSliderFront')){
	die('ERROR: It looks like you have more than one instance of Slider Revolution installed. Please remove additional instances for this plugin to work again.');
}

define('RS_REVISION',			'7.1.6');
define('RS_PLUGIN_PATH',		plugin_dir_path(__FILE__));
define('RS_PLUGIN_SLUG_PATH',	plugin_basename(__FILE__));
define('RS_PLUGIN_FILE_PATH',	__FILE__);
define('RS_PLUGIN_SLUG',		apply_filters('set_revslider_slug', 'revslider'));
define('RS_PLUGIN_URL',			get_sr_plugin_url());
define('RS_PLUGIN_URL_CLEAN',	str_replace(['http://', 'https://'], '//', RS_PLUGIN_URL));
define('RS_DEMO',				false);
define('RS_TP_TOOLS',			'7.1.0'); //holds the version of the tp-tools script, load only the latest!

global $SR_GLOBALS;

$SR_GLOBALS = [
	'addon_notice_merged'	=> 0,
	'animations'			=> [],
	'collections'			=> [
		'css'	=> [],
		'ids'	=> [],
		'js'	=> ['revapi' => [], 'js' => [], 'minimal' => '', 'stream' => []],
		'trans'	=> [],
		'nav'	=> ['arrows' => [], 'thumbs' => [], 'bullets' => [], 'tabs' => [], 'scrubber' => []]
	],
	'deprecated'			=> [],
	'fonts'					=> ['queue' => [], 'loaded' => [], 'custom' => []],
	'header_js'				=> false,
	'icon_sets'				=> [
		'Materialicons' => ['css' => false, 'parsed' => false],
		'FontAwesome'	=> ['css' => false, 'parsed' => false],
		'PeIcon'		=> ['css' => false, 'parsed' => false],
		'RevIcon'		=> ['css' => false, 'parsed' => false]
	],
	'data_init'				=> true,
	'js_init'				=> false,
	'loaded_by_editor'		=> false,
	'preview_mode'			=> false,
	'markup_export'			=> false,
	'modules'				=> ['module','page','slide','layer','draw','animate','transitions','srtools','canvas','defaults','carousel','navigation','media','modifiers'],
	'save_post'				=> false,
	'serial'				=> 0,
	'sliders'				=> [],
	'v6'					=> false,
	'v6db'					=> 'unknown',
	'yt_api_loaded'			=> false,
	//file extensions that must never end up on disk through an import. deduplicated and grouped by platform -
	//the flat list before this carried 'shtml' 3x, 'jsp'/'jspx' 3x and 'asp', 'aspx', 'cgi', 'pl', 'swf',
	//'htaccess', 'phps' twice each. test against it with RevSliderFunctions::is_bad_extension(), which does an
	//O(1) hash lookup instead of walking ~70 entries for every single file inside an imported zip.
	'bad_extensions'		=> [
		//php
		'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'pht', 'phtm', 'phtml', 'pgif', 'phar', 'hphp', 'inc', 'ctp', 'module',
		//asp / .net
		'asp', 'aspx', 'asa', 'ashx', 'asmx', 'aspq', 'axd', 'cer', 'config', 'cshtm', 'cshtml', 'rem', 'soap', 'vbhtm', 'vbhtml',
		//java / jsp
		'jsp', 'jspx', 'jspf', 'jsw', 'jsv', 'jar', 'wss', 'do', 'action',
		//coldfusion
		'cfm', 'cfml', 'cfc', 'dbm',
		//server parsed markup
		'shtml', 'xhtml', 'html', 'htm', 'yaws',
		//scripts
		'pl', 'cgi', 'sh', 'py', 'rb', 'ps1', 'psm1', 'vbs', 'js',
		//binaries
		'exe', 'dll', 'sys', 'com', 'bat', 'cmd', 'msi', 'reg', 'scr', 'pif',
		//archives (nested archives are not unpacked, so they must not be written either)
		'zip', 'rar', '7z',
		//config / plugins
		'htaccess', 'ini', 'swf'
	],
	'mime_types'			=> [
		'image'	=> ['jpg|jpeg|jpe' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'bmp' => 'image/bmp', 'webp' => 'image/webp', 'svg' => 'image/svg+xml'],
		'video'	=> ['mpeg|mpg|mpe' => 'video/mpeg', 'mp4|m4v' => 'video/mp4', 'ogv' => 'video/ogg', 'webm' => 'video/webm', 'mp3' => 'audio/mpeg']
	],
	'options' => [],
];

//include framework files
require_once(RS_PLUGIN_PATH . 'includes/globals.class.php');
require_once(RS_PLUGIN_PATH . 'includes/data.class.php');
require_once(RS_PLUGIN_PATH . 'includes/functions.class.php');
require_once(RS_PLUGIN_PATH . 'includes/addon-base.class.php');
require_once(RS_PLUGIN_PATH . 'includes/cache.class.php');
require_once(RS_PLUGIN_PATH . 'includes/optimizer.class.php');

require_once(RS_PLUGIN_PATH . 'includes/fonts.class.php');
require_once(RS_PLUGIN_PATH . 'includes/colorpicker.class.php');
require_once(RS_PLUGIN_PATH . 'includes/navigation.class.php');
require_once(RS_PLUGIN_PATH . 'includes/object-library.class.php');
require_once(RS_PLUGIN_PATH . 'includes/page-effects.class.php'); //Page Effects framework — addons register via RevSliderPageEffects::register_type()
require_once(RS_PLUGIN_PATH . 'admin/includes/loadbalancer.class.php');
require_once(RS_PLUGIN_PATH . 'admin/includes/widget.class.php');
require_once(RS_PLUGIN_PATH . 'admin/includes/upgrade_sr6.class.php');
require_once(RS_PLUGIN_PATH . 'admin/includes/upgrade.class.php');
require_once(RS_PLUGIN_PATH . 'includes/extension.class.php');
require_once(RS_PLUGIN_PATH . 'includes/favorite.class.php');
require_once(RS_PLUGIN_PATH . 'includes/aq-resizer.class.php');
require_once(RS_PLUGIN_PATH . 'includes/page-template.class.php');

require_once(RS_PLUGIN_PATH . 'includes/external/facebook.class.php');
require_once(RS_PLUGIN_PATH . 'includes/external/flickr.class.php');
require_once(RS_PLUGIN_PATH . 'includes/external/instagram.class.php');
require_once(RS_PLUGIN_PATH . 'includes/external/vimeo.class.php');
require_once(RS_PLUGIN_PATH . 'includes/external/youtube.class.php');

require_once(RS_PLUGIN_PATH . 'includes/slider.class.php');
require_once(RS_PLUGIN_PATH . 'includes/slide.class.php');
require_once(RS_PLUGIN_PATH . 'includes/output.class.php');
require_once(RS_PLUGIN_PATH . 'public/revslider-front.class.php');

require_once(RS_PLUGIN_PATH . 'includes/api.class.php');

require_once(RS_PLUGIN_PATH . 'includes/em-integration.class.php');
require_once(RS_PLUGIN_PATH . 'includes/woocommerce.class.php');
require_once(RS_PLUGIN_PATH . 'includes/backwards.php');
require_once(RS_PLUGIN_PATH . 'includes/jetpack.class.php');

require_once(RS_PLUGIN_PATH . 'admin/includes/ai.class.php');

//divi
require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/shortcode_generator.class.php');
require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/revslider-divi.php');

try{
	RevSliderFunctions::set_memory_limit();

	/** @return string|false the rendered slider markup */
	function rev_slider_shortcode($args, $mid_content = null){

		//do not render in elementor preview iframe
		if(isset($_GET['elementor-preview'])) return false;

		//do not render on saving a post/page
		global $SR_GLOBALS;
		if($SR_GLOBALS['save_post']) return false;
		
		//skip shortcode generation if any of these functions found in backtrace 
		//function can be provided as array item without key
		//or as 'class' => 'function'
		$skip_functions = apply_filters(
			'rs_shortcode_skip_functions',
			[
				'WC_Structured_Data' => 'generate_product_data', // woocommerce
				'AIOSEO\Plugin\Common\Meta\Description' => 'getDescription', // all-in-one-seo
				//'Elementor\Core\Editor\Editor' => 'print_editor_template', // elementor
			]
		);

		//only pay for debug_backtrace() when a relevant integration is actually loaded:
		//string keys are class names - if none exist (and there are no keyless entries), skip the backtrace entirely
		$check_backtrace = false;
		foreach($skip_functions as $class => $func){
			if(!is_string($class) || class_exists($class, false)){ $check_backtrace = true; break; }
		}
		if($check_backtrace){
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			foreach($backtrace as $trace){
				foreach($skip_functions as $class => $func){
					if($trace['function'] == $func){
						//no class was provided, func matched, return
						if(!is_string($class)) return false;
						//class provided in key, compare with trace class
						if(isset($trace['class']) && $trace['class'] == $class) return false;
					}
				}
			}
		}
		
		$sc		= shortcode_atts(['alias' => '', 'fullheight' => '', 'fullwidth' => '', 'modal' => '', 'offset' => '', 'order' => '', 'settings' => '', 'skin' => '', 'usage' => '', 'zindex' => ''], $args, 'rev_slider');
		$sc		= array_map('wp_kses_post', $sc);
		$output = new RevSlider7Output();

		if(is_admin() && $output->_is_gutenberg_page()) return false;

		$slider_alias = ($sc['alias'] != '') ? $sc['alias'] : $output->get_val($args, 0); //backwards compatibility

		//this fixes an issue with the Visual Composer extension
		if(empty($slider_alias)){
			return (function_exists('is_user_logged_in') && is_user_logged_in()) ? '<div><img src="' . RS_PLUGIN_URL_CLEAN . 'admin/assets/images/sr_dark.png"></div>' : '';
		}

		$output->set_custom_order($sc['order']);
		$output->set_custom_settings($sc['settings']);
		$output->set_custom_skin($sc['skin']);

		$gallery_ids = $output->check_for_shortcodes($mid_content); //check for example on gallery shortcode and do stuff
		if($gallery_ids !== false) $output->set_gallery_ids($gallery_ids);

		ob_start();
		try{
			$output->set_usage($sc['usage']);
			$output->set_fullheight($sc['fullheight']);
			$output->set_fullwidth($sc['fullwidth']);
			$output->set_offset($sc['offset']);
			$sc['modal'] = (empty($sc['modal'])) ? 'true' : $sc['modal'];
			if($sc['usage'] === 'modal') $output->set_modal($sc['modal']);
			$slider	= $output->add_slider_to_stage($slider_alias);
			$content = ob_get_contents();
		}finally{
			ob_end_clean(); //close the buffer even if rendering throws, otherwise the partial markup leaks into the page
		}

		if(!empty($sc['zindex'])){
			$content = '<div class="wp-block-themepunch-revslider" style="z-index:'.esc_attr($sc['zindex']).';">'. $content .'</div>';
		}

		if(empty($slider)) return $content;
		$filter = $slider->get_param(['general', 'outPutFilter'], '');
		switch($filter){
			case 'compress':
				$content = str_replace(["\n", "\r"], '', $content);
				return $content;
			case 'echo':
				global $SR_GLOBALS;
				if($SR_GLOBALS['save_post']) return $content;
				echo $content; //bypass the filters
				return ''; //return nothing
			break;
		}

		return $content;
	}

	$optimizer  = RevSliderGlobals::instance()->get('RevSliderOptimizer');
	$SR_jetpack	= RevSliderGlobals::instance()->get('RevSliderJetPack');
	$SR_api		= RevSliderGlobals::instance()->get('RevSliderApi');
	$SR_ai		= RevSliderGlobals::instance()->get('RevSliderAI');
	$rslb		= RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
	$rslb->refresh_server_list();

	add_shortcode('rev_slider', 'rev_slider_shortcode');
	add_shortcode('sr7', 'rev_slider_shortcode');
	add_action('save_post', ['RevSliderFront', 'set_post_saving']);
	add_action('widgets_init', ['RevSliderWidget', 'register_widget']);

	add_action('init', ['RevSliderShortcodeWizard', 'add_slider_meta_box']);
	add_action('added_post_meta', ['RevSliderShortcodeWizard', 'on_updated_post_meta'], 10, 4);
	add_action('updated_post_meta', ['RevSliderShortcodeWizard', 'on_updated_post_meta'], 10, 4);

	if(is_admin()){
		require_once(RS_PLUGIN_PATH . 'admin/includes/license.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/addons.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/template.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/functions-admin.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/folder.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/import.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/export.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/export-html.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/newsletter.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/revslider-admin.class.php');
		require_once(RS_PLUGIN_PATH . 'includes/update.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/tracking.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/svg_sanitizer/subject.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/svg-sanitizer.class.php');
		$sr_track	= RevSliderGlobals::instance()->get('RevSliderTracking');
		$sr_addons	= RevSliderGlobals::instance()->get('RevSliderAddons');
		$sr_admin	= RevSliderGlobals::instance()->get('RevSliderAdmin');
	}else{
		$rev_slider_front = new RevSliderFront();

		//load the update class outside the admin so wp-cron's automatic updater
		//sees Slider Revolution in the update_plugins transient and can run
		//background auto-updates the same way it does for plugins hosted on wp.org
		require_once(RS_PLUGIN_PATH . 'includes/update.class.php');
		RevSliderUpdate::init_auto_updates();
	}
	
	register_activation_hook(__FILE__, ['RevSliderFront', 'create_tables']);
	register_activation_hook(__FILE__, ['RevSliderFront', 'welcome_screen_activate']);

	add_action('plugins_loaded', ['RevSliderPluginUpdateV6', 'do_update_checks_v6']);
	add_action('plugins_loaded', ['RevSliderPluginUpdate', 'do_update_checks']);
	add_action('plugins_loaded', ['RevSliderPluginUpdate', 'do_remove_addon_checks']);
	add_action('plugins_loaded', ['RevSliderPluginUpdate', 'do_update_addon_checks']);
	add_action('admin_init', ['RevSliderPluginUpdate', 'do_wpml_addon_check']);
	//schema work belongs to activation and to the admin - on 'plugins_loaded' it ran an option read plus a
	//version_compare on every front-end request as well, and a plugin *update* (which does not re-fire the
	//activation hook) is picked up by 'admin_init' anyway, on the first admin page load after the update.
	add_action('admin_init', ['RevSliderFront', 'create_tables']);
	add_action('plugins_loaded', ['RevSliderPageTemplate', 'get_instance']);
	add_action('plugins_loaded', ['RevSliderFront', 'add_post_editor']);
	add_action('wp_loaded', [$SR_ai, 'check_open_event_ids']);
	add_filter('wpseo_sitemap_entry', ['RevSliderFront', 'get_images_for_seo'], 10, 3);
	add_filter('rocket_rucss_inline_atts_exclusions', ['RevSliderFront', 'wp_rocket_inline_atts_exclusions']);
}catch(Throwable $e){
	//$trace = $e->getTraceAsString();
	echo esc_html__('Revolution Slider Error:', 'revslider') .' <b>'. esc_html($e->getMessage()) .'</b>';
}

/**
 * add RevSlider to the page/post
 * @return void
 */
function putRevSlider($data, $put_in = ''){
	add_revslider($data, $put_in);
}

/** @return bool|void */
function add_revslider($data, $put_in = ''){
	$output		= new RevSlider7Output();
	$g_values	= $output->get_global_settings();
	$add_to		= $output->get_val($g_values, 'incl', []);
	$output->set_add_to($add_to);
	if($output->check_add_to(true) == false && $output->_truefalse($output->get_val($g_values, 'inclAll', true)) == false){
		$output->print_error_message(__('If you want to use the PHP function "add_revslider" in your code please make sure to activate ', 'revslider').__('"Load JS Libraries Globally" ', 'revslider').__('and/or add the current page to the ', 'revslider').__('"Pages to Load SR JS Libraries" option ', 'revslider').__('in the "Global Settings" of Slider Revolution.', 'revslider'));
		return false;
	}

	ob_start();
	try{
		$output->set_add_to($put_in);
		$slider	 = $output->add_slider_to_stage($data);
		$content = ob_get_contents();
	}finally{
		ob_end_clean(); //close the buffer even if rendering throws
	}

	echo $content;
}

/** @return string the plugin URL with stray line breaks removed */
function get_sr_plugin_url(){
	$url = str_replace('index.php', '', plugins_url('index.php', __FILE__ ));
	if(strpos($url, 'http') === false){
		$site_url	= get_site_url();
		$url		= (substr($site_url, -1) === '/') ? substr($site_url, 0, -1). $url : $site_url. $url;
	}
	
	return str_replace([chr(10), chr(13)], '', $url);
}
