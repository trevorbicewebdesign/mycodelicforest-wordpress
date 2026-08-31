<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevsliderModule extends ET_Builder_Module {

	public $slug      	= 'revslider_divi';
	public $vb_support	= 'on';
	public $name		= '';
	public $icon_path	= '';
    public $plugin_dir_url = '';
    public $plugin_dir = '';
    public $required_divi_core_version = '4.9.0';

	protected $module_credits = [
		'module_uri' => '',
		'author'     => '',
		'author_uri' => '',
	];

    public function __construct( $name = 'revslider-divi', $args = [] ) {

		//compare divi version with required version
		if (!function_exists('_et_core_find_latest')) return;
		$divi_core_version = _et_core_find_latest('version');
		if (version_compare($divi_core_version, $this->required_divi_core_version) < 0) {
			return;
		}

		$this->plugin_dir     = RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/divi4/includes/modules/RevsliderModule/';
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );

        if(!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;

		if(!empty($_GET['et_fb'])){
		    //load revslider styles and scripts needed for shortcode wizard
            require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/shortcode_generator.class.php');
            RevSliderShortcodeWizard::add_styles();
            add_action( 'wp_enqueue_scripts', [$this, 'add_scripts']);
            
            // Add filter to prevent shortcode from being processed in visual builder preview
            add_filter('revslider_divi_shortcode_output', [$this, 'handle_visual_builder_preview'], 10, 1);
        }

        add_action('divi_visual_builder_assets_before_enqueue_app_window_scripts', [$this, 'add_app_scripts'], 10);
	}

	/** @return void */
	public function init() {
		$this->name = esc_html__( 'Slider Revolution', 'revslider' );
        $this->icon_path = RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/divi/divi4/includes/modules/RevsliderModule/images/sr7-logo.svg';
	}

    public function get_fields() {
        return [
            'meta' => [
                'label'           => esc_html__( 'Slider Module', 'revslider' ),
                'type'            => 'revslider_module_info',
                'toggle_slug'     => 'module_info',
                'default'         => '',
            ],
            'alias' => [
                'label'           => esc_html__( 'Slider Alias', 'revslider' ),
                'type'            => 'hidden',
                'default'         => '',
                'toggle_slug'     => 'module_info',
            ],
            'shortcode' => [
                'label'           => esc_html__( 'Slider Shortcode', 'revslider' ),
                'type'            => 'hidden',
                'default'         => '',
                'toggle_slug'     => 'module_info',
            ],
            'revslider_divi' => [
                'label'           => esc_html__( 'Old Slider Shortcode', 'revslider' ),
                'type'            => 'hidden',
                'default'         => '',
                'toggle_slug'     => 'module_info',
            ],            

            'layout_override' => [
                'label'           => esc_html__( 'Override Module Layout', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'layout',
                'show_if'         => [
                    'modal'          => 'off',
                ],                
            ],
            'fullwidth' => [
                'label'           => esc_html__( 'Full Width', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],                
                'default'         => 'off',
                'toggle_slug'     => 'layout',
                'show_if'         => [
                    'layout_override' => 'on',
                    'modal'          => 'off',
                ],
            ],
            'fullheight' => [
                'label'           => esc_html__( 'Full Height', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],                
                'default'         => 'off',
                'toggle_slug'     => 'layout',
                'show_if'         => [
                    'layout_override' => 'on',
                    'modal'          => 'off',
                ],
            ],

            'modal' => [
                'label'           => esc_html__( 'Insert Module as Modal (Popup)', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'popup',
            ],

            'popup_cookie_use' => [
                'label'           => esc_html__( '1 Time Per Session', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal' => 'on',
                ],
            ],

            'popup_cookie_value' => [
                'label'           => esc_html__( 'Session (hours)', 'revslider' ),
                'type'            => 'number',
                'default'         => 24,
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal'             => 'on',
                    'popup_cookie_use'  => 'on',
                ],
            ],

            'popup_time_use' => [
                'label'           => esc_html__( 'Pop Up after Time', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal' => 'on',
                ],
            ],
            'popup_time_value' => [
                'label'           => esc_html__( 'After (ms)', 'revslider' ),
                'type'            => 'number',
                'default'         => 2000,
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal'          => 'on',
                    'popup_time_use'=> 'on',
                ],
            ],

            'popup_scroll_use' => [
                'label'           => esc_html__( 'Pop Up at Scroll Position', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal' => 'on',
                ],
            ],
            'popup_scroll_type' => [
                'label'           => esc_html__( 'Based On', 'revslider' ),
                'type'            => 'select',
                'options'         => [
                    'offset'    => esc_html__( 'Offset', 'revslider' ),
                    'container' => esc_html__( 'Container', 'revslider' ),
                ],
                'default'         => 'offset',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal'            => 'on',
                    'popup_scroll_use'=> 'on',
                ],
            ],
            'popup_scroll_offset' => [
                'label'           => esc_html__( 'Offset (px)', 'revslider' ),
                'type'            => 'number',
                'default'         => 200,
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal'              => 'on',
                    'popup_scroll_use'  => 'on',
                    'popup_scroll_type' => 'offset',
                ],
            ],
            'popup_scroll_container' => [
                'label'           => esc_html__( 'Container Selector', 'revslider' ),
                'type'            => 'text',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal'              => 'on',
                    'popup_scroll_use'  => 'on',
                    'popup_scroll_type' => 'container',
                ],
            ],

            'popup_event_use' => [
                'label'           => esc_html__( 'Pop Up by Events', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal' => 'on',
                ],
            ],
            'popup_event_name' => [
                'label'           => esc_html__( 'Listen to Event', 'revslider' ),
                'type'            => 'text',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal'            => 'on',
                    'popup_event_use' => 'on',
                ],
            ],
            'popup_event_example' => [
                'label'           => esc_html__( 'Sample Listener', 'revslider' ),
                'type'            => 'text',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal'            => 'on',
                    'popup_event_use' => 'on',
                ],
            ],

            'popup_hash_use' => [
                'label'           => esc_html__( 'Pop Up on URL Hash', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],
                'default'         => 'off',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal' => 'on',
                ]
            ],
            'popup_hash_example' => [
                'label'           => esc_html__( 'Sample Hash URL', 'revslider' ),
                'type'            => 'text',
                'toggle_slug'     => 'popup',
                'show_if'         => [
                    'modal'            => 'on',
                    'popup_hash_use' => 'on',
                ],
            ],

            'offset' => [
                'label'           => esc_html__( 'Block Offset', 'revslider' ),
                'type'            => 'revslider_offset_grid',
                'toggle_slug'     => 'offset',
                'default'         => '',
                'show_if'         => [
                    'modal'          => 'off',
                ],                
            ],

            'zindex' => [
                'label'           => esc_html__( 'Z-Index', 'revslider' ),
                'type'            => 'number',
                'default'         => '',
                'toggle_slug'     => 'depth',
                'show_if'         => [
                    'modal'          => 'off',
                ],                
            ],

            'wrapperid' => [
                'label'           => esc_html__( 'Module Wrapper ID', 'revslider' ),
                'type'            => 'text',
                'toggle_slug'     => 'advanced',
                'show_if'         => [
                    'modal'          => 'off',
                ],                
            ]
        ];
    }

    public function get_settings_modal_toggles() {
        return [
            'general' => [
                'toggles' => [
                    'module_info' => [
                        'priority' => 1,
                        'title' => esc_html__( 'Module Info', 'revslider' ),
                    ],
                    'layout' => [
                        'priority' => 2,
                        'title' => esc_html__( 'Module Layout', 'revslider' ),
                    ],
                    'popup' => [
                        'priority' => 3,
                        'title' => esc_html__( 'Use as Modal', 'revslider' ),
                    ],
                    'offset' => [
                        'priority' => 4,
                        'title' => esc_html__( 'Block Offset', 'revslider' ),
                    ],
                    'depth' => [
                        'priority' => 5,
                        'title' => esc_html__( 'Block Depth', 'revslider' ),
                    ],
                    'advanced' => [
                        'priority' => 6,
                        'title' => esc_html__( 'Advanced', 'revslider' ),
                    ],
                ],
            ],
        ];
    }

    public function get_advanced_fields_config() {
        return [
            'main_content' => false,
            'link_options' => false,
            'background' => false,
            'borders' => false,
            'box_shadow' => false,
            'button' => false,
            'filters' => false,
            'fonts' => false,
            'margin_padding' => false,
            'max_width' => false,
        ];
    }

	/** @return void */
	public function add_scripts(){
        RevSliderShortcodeWizard::add_scripts(false, true);
        wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], RevSliderFunctions::asset_time('admin/assets/js/tools/tools.js'), false);
    }

    public function handle_visual_builder_preview( $output ) {
        // Return a safe preview instead of trying to render the shortcode
        return $output;
    }

	/** @return string the rendered slider markup for the Divi 4 module */
	public function render( $attrs, $content = null, $render_slug = '' ) {
        $shortcode = $this->props['shortcode'] ? $this->props['shortcode'] : $this->props['revslider_divi'];
        $html = '<div class="revslider"';
        $html .= $this->props['wrapperid'] ? ' id="' . esc_attr($this->props['wrapperid']) . '"' : "";
        $html .= $this->props['zindex'] ? ' style="zindex:' . esc_attr($this->props['wrapzindexerid']) . '"' : "";
        $html .= '>';
        $html .= do_shortcode( et_pb_fix_shortcodes( str_replace( ['&#91;', '&#93;'], ['[', ']'], $shortcode ), true ) );
        $html .= '</div>';
        return $html;
	}
}

new RevsliderModule;
