<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * WPBakery (Visual Composer) integration: registers the slider as a WPBakery element.
 */
class RevSliderWpbakeryShortcode {

    public static function visual_composer_include(){

        // VC is enabled
        if(defined('WPB_VC_VERSION') && function_exists('vc_map')){
            vc_map(
                [
                    'name' => __('Slider Revolution 7', 'revslider'),
                    'base' => 'sr7',
                    'icon' => 'icon-wpb-revslider',
                    'category' => __('Content', 'revslider'),
                    'show_settings_on_create' => false,
                    'js_view' => 'VcSliderRevolution7',
                    'admin_enqueue_js' => RS_PLUGIN_URL . 'admin/includes/shortcode_generator/wpbakery/assets/js/sr7-wpbakery.js',
                    'front_enqueue_js' => RS_PLUGIN_URL . 'admin/includes/shortcode_generator/wpbakery/assets/js/sr7-wpbakery.js',
                    'params' => [
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Modal', 'revslider'),
                            'param_name' => 'modal',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Title', 'revslider'),
                            'param_name' => 'slidertitle',
                            'admin_label' => true,
                            'value' => ''
                        ],                        
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Alias', 'revslider'),
                            'param_name' => 'alias',
                            'admin_label' => true,
                            'save_always' => true,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Offset', 'revslider'),
                            'param_name' => 'offset',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Layout', 'revslider'),
                            'param_name' => 'fullwidth',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Layout', 'revslider'),
                            'param_name' => 'fullheight',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('z-Index', 'revslider'),
                            'param_name' => 'zindex',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Usage', 'revslider'),
                            'param_name' => 'usage',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Module Id', 'revslider'),
                            'param_name' => 'moduleid',
                            'admin_label' => false,
                            'value' => ''
                        ],
                    ]
                ]
            );

            // Register old shortcode for backwards compatibility
            vc_map(
                [
                    'name' => __('Slider Revolution 6 (Legacy)', 'revslider'),
                    'base' => 'rev_slider',
                    'icon' => 'icon-wpb-revslider',
                    'category' => __('Content', 'revslider'),
                    'show_settings_on_create' => false,
                    'js_view' => 'VcSliderRevolution6',
                    'admin_enqueue_js' => RS_PLUGIN_URL . 'admin/includes/shortcode_generator/wpbakery/assets/js/sr6-wpbakery.js',
                    'front_enqueue_js' => RS_PLUGIN_URL . 'admin/includes/shortcode_generator/wpbakery/assets/js/sr6-wpbakery.js',
                    'params' => [
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Modal', 'revslider'),
                            'param_name' => 'modal',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Title', 'revslider'),
                            'param_name' => 'slidertitle',
                            'admin_label' => true,
                            'value' => ''
                        ],                        
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Alias', 'revslider'),
                            'param_name' => 'alias',
                            'admin_label' => true,
                            'save_always' => true,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Offset', 'revslider'),
                            'param_name' => 'offset',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Layout', 'revslider'),
                            'param_name' => 'fullwidth',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Layout', 'revslider'),
                            'param_name' => 'fullheight',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('z-Index', 'revslider'),
                            'param_name' => 'zindex',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Usage', 'revslider'),
                            'param_name' => 'usage',
                            'admin_label' => false,
                            'value' => ''
                        ],
                        [
                            'type' => 'rev_slider_shortcode',
                            'heading' => __('Module Id', 'revslider'),
                            'param_name' => 'moduleid',
                            'admin_label' => false,
                            'value' => ''
                        ],
                    ]
                ]
            );
        }
    }
}
