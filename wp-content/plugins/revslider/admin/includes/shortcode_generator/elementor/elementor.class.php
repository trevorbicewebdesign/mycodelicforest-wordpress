<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Elementor integration: registers the Slider Revolution widget and loads the editor/preview styles.
 */
class RevSliderElementor {
	
	/** @return void */
	public static function init() {
		
		$min_elementor_version = '2.0.0';
		$min_php_version = '7.0';
	
		// Check if Elementor installed and activated
		if(!did_action('elementor/loaded')) return;
		
		// Check for required Elementor version
		if(!version_compare(ELEMENTOR_VERSION, $min_elementor_version, '>=' )) return;
		
		// Check for required PHP version
		if(version_compare(PHP_VERSION, $min_php_version, '<')) return;
		
		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$is_elementor_edit_page = is_admin() && $f->get_val($_GET, 'action') === 'elementor' && $f->get_val($_GET, 'post', 0);
		
		// Add Plugin actions
		if(version_compare(PHP_VERSION, '3.5.0', '<')){
			add_action('elementor/widgets/widgets_registered', ['RevSliderElementor', 'init_elementor_widgets']);
		}else{
			add_action('elementor/widgets/register', ['RevSliderElementor', 'init_elementor_widgets']);
		}
		
		// Register Widget Styles/Scripts
		add_action('elementor/preview/enqueue_styles', ['RevSliderElementor', 'add_preview_styles']);
		add_action('elementor/editor/after_enqueue_styles', ['RevSliderElementor', 'add_editor_styles']);
		if($is_elementor_edit_page) add_action('elementor/editor/after_enqueue_scripts', ['RevSliderElementor', 'add_scripts']);
	}

	/** @return void */
	public static function add_preview_styles() {
		wp_enqueue_style('sr7-elementor-preview-css', RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/elementor/assets/css/sr7-elementor-preview.css', [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/elementor/assets/css/sr7-elementor-preview.css'));
		wp_enqueue_style('sr7-elementor-preview-font-css', 'https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap', [], RS_REVISION);
	}

	/** @return void */
	public static function add_editor_styles() {
		RevSliderShortcodeWizard::add_styles(true);
		wp_enqueue_style('revslider-base-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/base.css', [], RevSliderFunctions::asset_time('admin/assets/css/base.css'));
		wp_enqueue_style('sr7-elementor-editor-css', RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/elementor/assets/css/sr7-elementor-editor.css', [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/elementor/assets/css/sr7-elementor-editor.css'));
	}
	
	/** @return void */
	public static function add_scripts() {
		RevSliderShortcodeWizard::add_scripts(true);
		wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], RevSliderFunctions::asset_time('admin/assets/js/tools/tools.js'), false);
		wp_enqueue_script('sr7-elementor-editor', RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/elementor/assets/js/sr7-elementor-editor.js', [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/elementor/assets/js/sr7-elementor-editor.js'), ['strategy' => 'async']);
	}
	
	/** @return void registers the Slider Revolution widget with Elementor */
	public static function init_elementor_widgets() {
		
		// Include Widget files
		require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/elementor/elementor-widget.class.php');

		// Register widget
		$widgets_manager = \Elementor\Plugin::instance()->widgets_manager;
		if(version_compare(ELEMENTOR_VERSION, '3.1.0', '<=')){
			$widgets_manager->register_widget_type( new RevSliderElementorWidgetPre310() );
		}elseif(version_compare(ELEMENTOR_VERSION, '3.5.0', '<')){
			$widgets_manager->register_widget_type( new RevSliderElementorWidget() );
		}else{
			$widgets_manager->register( new RevSliderElementorWidget() );
		}

	}
	
}
