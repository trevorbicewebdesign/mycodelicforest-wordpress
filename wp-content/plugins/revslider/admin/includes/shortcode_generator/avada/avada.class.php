<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Avada / Fusion Builder integration: extends Avada's own slider element with our options and makes the
 * slider render inside the live builder frame.
 */
class RevSliderAvada {

	const STYLE_HANDLE = 'sr7-avada-css';

	public static $registered;

	/** @return void */
	public static function init($registered) {
		self::$registered = $registered;
		if(self::is_avada_available() === false) return;

		add_filter('fusion_builder_map', ['RevSliderAvada', 'filter_revolution_slider_element'], 20);
		add_filter('do_shortcode_tag', ['RevSliderAvada', 'filter_shortcode_output'], 10, 4);
		add_action('wp_enqueue_scripts', ['RevSliderAvada', 'enqueue_live_builder_styles'], 20);
		add_action('fusion_builder_enqueue_live_scripts', [ 'RevSliderAvada', 'enqueue_live_builder_assets' ], 5);
		add_filter('pre_do_shortcode_tag', ['RevSliderAvada', 'modify_shortcode'], 5, 4);
	}

	/** @return array */
	public static function filter_revolution_slider_element($module) {
		if(!is_array($module) || self::is_target_element($module) === false) return $module;

		$module['preview'] = self::template_path('sr7-preview.php');
		$module['icon'] = 'sr7--avada--icon';
		$module['preview_id'] = 'fusion-builder-block-module-sr7-avada-preview-template';
		$module['custom_settings_view_name'] = 'ModuleSettingsSR7View';
		$module['custom_settings_view_js'] = self::asset_url('js/sr7-settings.js');
		$module['custom_settings_template_file'] = self::template_path('sr7-settings.php');
		$module['front_end_custom_settings_view_js'] = self::asset_url('js/sr7-front-end-settings.js');
		$module['front_end_custom_settings_template_file'] = self::template_path('sr7-front-end-settings.php');
		$module['admin_enqueue_css'] = self::asset_url('css/sr7-avada.css');
		$module['on_save'] = 'revSliderAvadaShortcodeFilter';
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

		wp_register_script('tpgsap', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tpgsap.js', [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/avada/assets/public/js/libs/tpgsap.js'), true);
		wp_register_script('tp-tools', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tptools.js', [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/avada/assets/public/js/libs/tptools.js'), true);
		wp_register_script('sr7-tools-shortcode', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/shortcode.js', [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/avada/assets/admin/assets/js/tools/shortcode.js'), true);
		wp_register_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/avada/assets/admin/assets/js/tools/tools.js'), true);
		wp_register_script('rev_slider_custom_settings_view', self::asset_url( 'js/sr7-front-end-settings.js' ), [ 'jquery', 'tp-tools', 'tpgsap', 'revbuilder-backend', 'sr7-tools-shortcode' ], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/avada/assets/js/sr7-front-end-settings.js'), true);
		wp_localize_script('rev_slider_custom_settings_view', 'SR7FusionBuilderText', self::get_builder_text_strings());

		wp_localize_script('rev_slider_custom_settings_view', 'SR7AvadaLiveData', [
			'registered' => self::$registered
		]);

		if(!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;
		wp_localize_script('tp-tools', 'SR7ShortcodeData', [
			'ajaxurl'		=> admin_url('admin-ajax.php'),
			'plugin_url'	=> RS_PLUGIN_URL_CLEAN,
			'block_nonce'	=> wp_create_nonce('revslider_actions'),
			'wp_upload_url'	=> rtrim(wp_upload_dir()['baseurl'] ?? '', '/') . '/',
		]);
	}

	/** @return array */
	protected static function get_builder_text_strings() {
		$text_strings = [
			'sr7_premium_template' => esc_html__('Premium Template', 'revslider'),
			'sr7_register_license_to_unlock' => esc_html__('Register License to Unlock', 'revslider')
		];
		return $text_strings;
	}

	/** @return array adds our own options to Avada's slider element */
	protected static function extend_element_params($params) {
		if(!is_array($params)) $params = [];

		$existing = [];
		foreach($params as $key => $param) {
			if(is_array($param) && isset($param['param_name'])) {
				$existing[$param['param_name']] = true;
				if ($param['param_name'] === 'alias') {
					$params[$key]['dependency'] =[
						[
							'element' 	=> 'usage',
							'value' 	=> 'hidden',
							'operator'	=> '==',
						]
					];
				}
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
		return [
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Live Preview Render', 'revslider' ),
				'param_name'  => 'live_preview',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					'no'  => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => 'yes',
				'dependency' => [
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]
				]				
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Override Module Layout', 'revslider' ),
				'param_name'  => 'layout_override',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					'no'  => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => 'no',
				'dependency' => [
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]
				]				
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Full Width', 'revslider' ),
				'param_name'  => 'fullwidth',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					'no'  => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => 'no',
				'dependency' => [
					[
						'element' 	=> 'layout_override',
						'value' 	=> 'yes',
						'operator'	=> '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]					
				]
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Full Height', 'revslider' ),
				'param_name'  => 'fullheight',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					'no'  => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => 'no',
				'dependency' => [
					[
						'element' 	=> 'layout_override',
						'value' 	=> 'yes',
						'operator'	=> '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]					
				]
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Modal', 'revslider' ),
				'param_name'  => 'modal',
				'default'     => '',
				'dependency'  => [
					[
						'element' 	=> 'usage',
						'value' 	=> 'hidden',
						'operator'	=> '==',
					]				
				],				
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Insert Module as Modal (Popup)', 'revslider' ),
				'param_name'  => 'usage',
				'value'       => [
					'modal' => esc_attr__( 'Yes', 'revslider' ),
					'no'    => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => 'no',
				'dependency'  => [
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],				
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( '1 Time Per Session', 'revslider' ),
				'param_name'  => 'popup_cookie_use',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					''    => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => '',
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Session (hours)', 'revslider' ),
				'description' => esc_attr__( 'Relating on Pop Up after Time and Scroll Position', 'revslider' ),
				'param_name'  => 'popup_cookie_value',
				'default'     => 24,
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element'  => 'popup_cookie_use',
						'value'    => 'yes',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Pop Up after Time', 'revslider' ),
				'param_name'  => 'popup_time_use',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					''    => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => '',
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'After (ms)', 'revslider' ),
				'description' => esc_attr__( 'Relating on Pop Up after Time and Scroll Position', 'revslider' ),
				'param_name'  => 'popup_time_value',
				'default'     => '2000ms',
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element'  => 'popup_time_use',
						'value'    => 'yes',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Pop Up at Scroll Position', 'revslider' ),
				'param_name'  => 'popup_scroll_use',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					''    => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => '',
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'select',
				'heading'     => esc_attr__( 'Based On', 'revslider' ),
				'param_name'  => 'popup_scroll_type',
				'value'       => [
					'offset'    => esc_attr__( 'Offset', 'revslider' ),
					'container' => esc_attr__( 'Container', 'revslider' ),
				],
				'default'     => 'offset',
				'dependency'  => [
					[
						'element'  => 'popup_scroll_use',
						'value'    => 'yes',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Offset', 'revslider' ),
				'param_name'  => 'popup_scroll_offset',
				'default'     => '2000px',
				'dependency'  => [
					[
						'element'  => 'popup_scroll_use',
						'value'    => 'yes',
						'operator' => '==',
					],
					[
						'element'  => 'popup_scroll_type',
						'value'    => 'offset',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Container', 'revslider' ),
				'param_name'  => 'popup_scroll_container',
				'value'       => '',
				'dependency'  => [
					[
						'element'  => 'popup_scroll_use',
						'value'    => 'yes',
						'operator' => '==',
					],
					[
						'element'  => 'popup_scroll_type',
						'value'    => 'container',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Pop Up by Events', 'revslider' ),
				'param_name'  => 'popup_event_use',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					''    => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => '',
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Listen to', 'revslider' ),
				'description' => __( 'i.e.: <code></code>', 'revslider' ),
				'param_name'  => 'popup_event_name',
				'value'       => '',
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element'  => 'popup_event_use',
						'value'    => 'yes',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Pop Up on URL Hash', 'revslider' ),
				'param_name'  => 'popup_hash_use',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'revslider' ),
					''    => esc_attr__( 'No', 'revslider' ),
				],
				'default'     => '',
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
				'description' => __( '<code>#popuplinkhere</code>', 'revslider' ),
			],
			[
				'type'        => 'info',
				'content'     => __( 'Modals can also be triggered by Layer Actions. See more details in <a href="https://www.themepunch.com/slider-revolution/lightbox-modal/" target="_blank">Modal Documentation</a>', 'revslider' ),
				'param_name'  => 'popup_note',
				'dependency'  => [
					[
						'element'  => 'usage',
						'value'    => 'modal',
						'operator' => '==',
					],
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type' => 'textfield',
				'heading' => esc_attr__('Z-Index', 'revslider'),
				'param_name' => 'zindex',
				'value' => '',
				'dependency'  => [
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
				'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
				'param_name'  => 'class',
				'value'       => '',
				'dependency'  => [
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
				'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
				'param_name'  => 'id',
				'value'       => '',
				'dependency'  => [
					[
						'element' 	=> 'alias',
						'value' 	=> '',
						'operator'	=> '!=',
					]				
				],
			],
			[
				'type' => 'textfield',
				'heading' => esc_attr__('Title', 'revslider'),
				'param_name' => 'title',
				'value' => '',
				'dependency'  => [
					[
						'element' 	=> 'usage',
						'value' 	=> 'hidden',
						'operator'	=> '==',
					]				
				],				
			],
			[
				'type' => 'textfield',
				'heading' => esc_attr__('Module ID', 'revslider'),
				'param_name' => 'm_id',
				'value' => '',
				'dependency'  => [
					[
						'element' 	=> 'usage',
						'value' 	=> 'hidden',
						'operator'	=> '==',
					]				
				],				
			],
			[
				'type' => 'textfield',
				'heading' => esc_attr__('Type', 'revslider'),
				'param_name' => 'type',
				'value' => '',
				'dependency'  => [
					[
						'element' 	=> 'usage',
						'value' 	=> 'hidden',
						'operator'	=> '==',
					]				
				],				
			],
			[
				'type' => 'textfield',
				'heading' => esc_attr__('Image', 'revslider'),
				'param_name' => 'image',
				'value' => '',
				'dependency'  => [
					[
						'element' 	=> 'usage',
						'value' 	=> 'hidden',
						'operator'	=> '==',
					]				
				],				
			],
			[
				'type' => 'textfield',
				'heading' => esc_attr__('Not Found', 'revslider'),
				'param_name' => 'not_found',
				'value' => '',
				'dependency'  => [
					[
						'element' 	=> 'usage',
						'value' 	=> 'hidden',
						'operator'	=> '==',
					]				
				],				
			],
			[
				'type' => 'textfield',
				'heading' => esc_attr__('Premium', 'revslider'),
				'param_name' => 'premium',
				'value' => '',
				'dependency'  => [
					[
						'element' 	=> 'usage',
						'value' 	=> 'hidden',
						'operator'	=> '==',
					]				
				],				
			]
		];
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
	public static function modify_shortcode($return, $tag, $attr, $m) {
		static $running = false;

		if ( $running || ('rev_slider' !== $tag && 'sr7' !== $tag) ) {
			return $return;
		}

		$running = true;

		$allowedAttrs = [
			'alias',
			'usage',
			'modal',
			'zindex',
			'fullwidth',
			'fullheight',
			'offset',
			'id',
			'class'
		];

		$sr7_atts = $attr;
		$shortcode = '[sr7';

		foreach ( $sr7_atts as $key => $value ) {
			if (in_array($key, ['fullwidth', 'fullheight'], true)) {
				$value = $value === 'yes' ? 'true' : '';
			}
			if ($key === 'usage' && $value !== 'modal') {
				$value = '';
			}

			if ( is_array( $value ) || is_object( $value ) || empty( $value ) || !in_array( $key, $allowedAttrs, true ) ) {
				continue;
			}

			$shortcode .= sprintf(
				' %s="%s"',
				sanitize_key( $key ),
				esc_attr( $value )
			);
		}

		$shortcode .= ']';

		$output = do_shortcode($shortcode);

		$running = false;

		return $output;
	}

	/** @return string */
	public static function filter_shortcode_output($output, $tag, $attr, $m) {
		unset($m);

		if(in_array($tag, ['rev_slider', 'sr7'], true) === false || is_array($attr) === false) return $output;

		$wrapper_id = sanitize_html_class((string) isset($attr['id']) ? $attr['id'] : '');
		$css_classes = sanitize_html_class((string) isset($attr['class']) ? $attr['class'] : '');

		if($wrapper_id === '' && $css_classes === '') return $output;

		$has_shortcode_wrapper = strpos($output, 'wp-block-themepunch-revslider') !== false;

		if($has_shortcode_wrapper) {

			$wrapped_output = preg_replace_callback(
				'/^\s*<div\b[^>]*\bclass=(["\'])[^"\']*\bwp-block-themepunch-revslider\b[^"\']*\1[^>]*>/i',
				static function($matches) use ($wrapper_id, $css_classes) {
					$opening_tag = $matches[0];

					if ($wrapper_id !== '' &&  !preg_match('/\bid=(["\'])/i', $opening_tag)) {
						$tag = preg_replace('/^(\s*<div\b)/i', '$1 id="' . esc_attr($wrapper_id) . '"', $opening_tag, 1);
					}
					
					if($css_classes !== '') {
						$tag = preg_replace('/(\bclass=(["\']))([^"\']*)\2/i', '$1$3 ' . esc_attr($css_classes) . '$2', $tag ?? $opening_tag, 1);
					}
					
					return $tag ?? $opening_tag;
				},
				$output,
				1
			);

			return is_string($wrapped_output) ? $wrapped_output : $output;
		}

		$class = 'wp-block-themepunch-revslider';
		if($css_classes !== '') {
			$class .= ' ' . $css_classes;
		}

		return '<div class="' . esc_attr($class) . '" id="' . esc_attr($wrapper_id) . '">' . $output . '</div>';
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
