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

            'live_preview' => [
                'label'           => esc_html__( 'Live Preview Render', 'revslider' ),
                'description'     => esc_html__( 'Render the selected module on the builder canvas instead of its card.', 'revslider' ),
                'type'            => 'yes_no_button',
                'options'         => [
                    'on'  => esc_html__( 'Yes', 'revslider' ),
                    'off' => esc_html__( 'No', 'revslider' ),
                ],
                'default'         => 'on',
                'toggle_slug'     => 'module_info',
            ],

            //Storage, not a control. A module saved before the grammar gained wrapperid (B3) keeps its id
            //here, and the module info field folds it into the shortcode the next time it is saved.
            'wrapperid' => [
                'label'           => esc_html__( 'Module Wrapper ID', 'revslider' ),
                'type'            => 'hidden',
                'default'         => '',
                'toggle_slug'     => 'module_info',
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

        //The depth used to be written here too, from a property name that does not exist and under a CSS
        //property that is not one - so it never applied. The shortcode has always carried it, and core emits
        //the wrapper for it now (B3), so only the wrapper id is left, and only until the module is saved once.
        $html = '<div class="revslider"';
        $html .= $this->props['wrapperid'] ? ' id="' . esc_attr($this->props['wrapperid']) . '"' : "";
        $html .= '>';
        $html .= do_shortcode( et_pb_fix_shortcodes( str_replace( ['&#91;', '&#93;'], ['[', ']'], $shortcode ), true ) );
        $html .= '</div>';
        return $html;
	}
}

new RevsliderModule;
