<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

use Elementor\Controls_Manager;

/**
 * The Slider Revolution widget for Elementor. Extends Elementor's shortcode widget: the controls pick a
 * slider, render() emits the shortcode (or the rendered slider when live preview is on).
 */
class RevSliderElementorWidget extends \Elementor\Widget_Shortcode {

	protected const SVG = [
		'SR7Icon' => '<svg width="36" height="36" viewBox="0 0 36 36"><g id="NEW_SR_LOGO" data-name="NEW SR LOGO" transform="translate(8 7.493)"><rect id="Rectangle_16" data-name="Rectangle 16" width="36" height="36" rx="5" transform="translate(-8 -7.493)" fill="#5c24ff"/><path id="Path_1" data-name="Path 1" d="M46.311,18.755l2.807,2.81a.2.2,0,0,1-.14.337H40.089a.121.121,0,0,1-.137-.137l-.006-8.912a.188.188,0,0,1,.322-.134l2.938,2.933a.109.109,0,0,0,.185-.006A6.5,6.5,0,0,0,43.9,7.7a6.271,6.271,0,0,0-5.255-2.944q-.123.009-.123-.117l0-3.991a.121.121,0,0,1,.148-.137c5.283.394,9.36,3.746,10.293,8.929a10.885,10.885,0,0,1-2.231,8.781.516.516,0,0,1-.168.145l-.211.114Q46.146,18.589,46.311,18.755Z" transform="translate(-28.331 -0.8)" fill="#fff"/><path id="Path_2" data-name="Path 2" d="M0,1.745V1.46l8.975.014a.124.124,0,0,1,.14.14l0,8.627a.251.251,0,0,1-.431.177L5.974,7.688a.133.133,0,0,0-.225.009C2.037,12.144,5,18.126,10.521,18.714a.168.168,0,0,1,.148.168v3.766a.123.123,0,0,1-.145.14A10.328,10.328,0,0,1,3.94,20.36Q-1.717,15.456.79,7.893A9.566,9.566,0,0,1,2.844,4.8a.151.151,0,0,0-.006-.234Z" transform="translate(-0.8 -1.481)" fill="#fff"/></g></svg>',
		'EditIcon' => '<svg width="24" height="16.076" viewBox="0 0 24 16.076"><path d="M70.12-722.121l9.609-9.609a.257.257,0,0,0,.078-.189.257.257,0,0,0-.078-.189L78.6-733.234a.257.257,0,0,0-.189-.078.257.257,0,0,0-.189.078l-9.609,9.609Zm-7.258,2.093a6.921,6.921,0,0,1-3.875-1.148,3.381,3.381,0,0,1-1.295-2.838,3.39,3.39,0,0,1,1.475-2.85,7.982,7.982,0,0,1,4.1-1.325,4.944,4.944,0,0,0,1.892-.456,1.063,1.063,0,0,0,.631-.961,1.408,1.408,0,0,0-.853-1.3,8.2,8.2,0,0,0-2.8-.644l.158-1.724a8.373,8.373,0,0,1,3.947,1.15,2.9,2.9,0,0,1,1.28,2.518,2.6,2.6,0,0,1-1.058,2.183,5.794,5.794,0,0,1-3.071.969,6.949,6.949,0,0,0-2.976.774,1.869,1.869,0,0,0-.992,1.666,1.771,1.771,0,0,0,.826,1.6,5.9,5.9,0,0,0,2.679.646Zm7.529.091L66.432-723.9l10.8-10.79a1.618,1.618,0,0,1,1.2-.506,1.676,1.676,0,0,1,1.2.506l1.557,1.557a1.644,1.644,0,0,1,.512,1.2,1.644,1.644,0,0,1-.512,1.2Zm-3.864.8a.694.694,0,0,1-.69-.2.694.694,0,0,1-.2-.689l.8-3.864,3.959,3.959Z" transform="translate(-57.693 735.192)"></path></svg>',
		'SelectIcon' => '<svg width="20" height="14.884" viewBox="0 0 20 14.884"><path d="M81.86-785.116a1.791,1.791,0,0,1-1.314-.547A1.791,1.791,0,0,1,80-786.977V-798.14a1.792,1.792,0,0,1,.547-1.314A1.792,1.792,0,0,1,81.86-800h5.581l1.86,1.86h7.442a1.792,1.792,0,0,1,1.314.547,1.792,1.792,0,0,1,.547,1.314H88.535l-1.86-1.86H81.86v11.163l2.233-7.442H100l-2.4,7.977a1.814,1.814,0,0,1-.686.965,1.846,1.846,0,0,1-1.1.36Zm1.953-1.86h12l1.674-5.581h-12Zm0,0,1.674-5.581Zm-1.953-9.3v0Z" transform="translate(-80 800)"></path></svg>',
		'EyeIcon' => '<svg width="16" height="14.564" viewBox="0 0 16 14.564"><path d="M12.709,12.859l-.8-.8a1.8,1.8,0,0,0-.491-2.145,1.942,1.942,0,0,0-2.091-.436l-.8-.8a2.2,2.2,0,0,1,.691-.291A3.364,3.364,0,0,1,10,8.3a3.073,3.073,0,0,1,3.091,3.091,3.179,3.179,0,0,1-.1.791,2.4,2.4,0,0,1-.282.682ZM15.055,15.2l-.727-.727a8.527,8.527,0,0,0,1.555-1.464,5.843,5.843,0,0,0,.973-1.627A7.257,7.257,0,0,0,14.127,8.2a7.14,7.14,0,0,0-3.945-1.173,8.732,8.732,0,0,0-1.564.145,5.8,5.8,0,0,0-1.255.345l-.836-.855A8.244,8.244,0,0,1,8.155,6.15a8.991,8.991,0,0,1,1.936-.218,8.208,8.208,0,0,1,4.755,1.482A8.592,8.592,0,0,1,18,11.386a8.982,8.982,0,0,1-1.218,2.127A8.782,8.782,0,0,1,15.055,15.2Zm1.055,4.109-3.055-3a7.064,7.064,0,0,1-1.436.391A9.613,9.613,0,0,1,10,16.841a8.336,8.336,0,0,1-4.818-1.482A8.685,8.685,0,0,1,2,11.386,8.25,8.25,0,0,1,3.009,9.541,10.335,10.335,0,0,1,4.582,7.823L2.291,5.532l.764-.782L16.818,18.514ZM5.327,8.586a6.691,6.691,0,0,0-1.3,1.291,5.985,5.985,0,0,0-.9,1.509,7.281,7.281,0,0,0,2.791,3.191,7.77,7.77,0,0,0,4.227,1.173,9.519,9.519,0,0,0,1.182-.073,2.824,2.824,0,0,0,.873-.218L11.036,14.3a1.9,1.9,0,0,1-.491.136,3.544,3.544,0,0,1-.545.045,3.006,3.006,0,0,1-2.182-.891,2.958,2.958,0,0,1-.909-2.2,3.313,3.313,0,0,1,.045-.545,2.226,2.226,0,0,1,.136-.491ZM10.873,11.168ZM8.764,12.223Z" transform="translate(-2 -4.75)"></path></svg>',
		'WideDeviceIcon' => '<svg width="24" height="14" viewBox="0 0 24 14"><path  d="M11,16H3a3,3,0,0,1-3-3V8A3,3,0,0,1,3,5H21a3,3,0,0,1,3,3v5a3,3,0,0,1-3,3H13v1h2a1,1,0,0,1,0,2H9a1,1,0,0,1,0-2h2ZM3,7H21a1,1,0,0,1,1,1v5a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V8A1,1,0,0,1,3,7Z" transform="translate(0 -5)" fill-rule="evenodd"/></svg>',
		'DesktopDeviceIcon' => '<svg width="22" height="18" viewBox="0 0 22 18"><path d="M11,17H4a3,3,0,0,1-3-3V6A3,3,0,0,1,4,3H20a3,3,0,0,1,3,3v8a3,3,0,0,1-3,3H13v2h3a1,1,0,0,1,0,2H8a1,1,0,0,1,0-2h3ZM4,5H20a1,1,0,0,1,1,1v8a1,1,0,0,1-1,1H4a1,1,0,0,1-1-1V6A1,1,0,0,1,4,5Z" transform="translate(-1 -3)" fill-rule="evenodd"/></svg>',
		'NotetbookDeviceIcon' => '<svg width="22" height="16" viewBox="0 0 22 16"><path d="M3,6A2,2,0,0,1,5,4H19a2,2,0,0,1,2,2v8a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2ZM5,6H19v8H5Z" transform="translate(-1 -4)" fill-rule="evenodd"/><path  d="M2,18a1,1,0,0,0,0,2H22a1,1,0,0,0,0-2Z" transform="translate(-1 -4)"/></svg>',
		'TabletDeviceIcon' => '<svg width="20" height="24" viewBox="0 0 20 24"><path d="M16,23.955a.817.817,0,0,0,.6-.232.846.846,0,0,0,0-1.173.885.885,0,0,0-1.194,0,.846.846,0,0,0,0,1.173A.817.817,0,0,0,16,23.955ZM7.667,26a1.622,1.622,0,0,1-1.181-.477A1.564,1.564,0,0,1,6,24.364V3.636a1.564,1.564,0,0,1,.486-1.159A1.622,1.622,0,0,1,7.667,2H24.333a1.622,1.622,0,0,1,1.181.477A1.564,1.564,0,0,1,26,3.636V24.364a1.564,1.564,0,0,1-.486,1.159A1.622,1.622,0,0,1,24.333,26Zm0-4.091v2.455H24.333V21.909Zm0-1.636H24.333V6.091H7.667Zm0-15.818H24.333V3.636H7.667Zm0,0v0Zm0,17.455v0Z" transform="translate(-6 -2)"/></svg>',
		'MobileDeviceIcon' => '<svg width="14" height="20" viewBox="0 0 14 20"><path d="M13,16H11v2h2Z" transform="translate(-5 -2)"/><path  d="M5,4A2,2,0,0,1,7,2H17a2,2,0,0,1,2,2V20a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2ZM7,4H17V20H7Z" transform="translate(-5 -2)" fill-rule="evenodd"/></svg>',
	];
    protected const DEVICES = ["w","d","n","t","m"];
    protected const SIDES = ['top', 'right', 'bottom', 'left'];
	protected const DEVICE_ICONS = [
		'w' => 'WideDeviceIcon',
		'd' => 'DesktopDeviceIcon',
		'n' => 'NotetbookDeviceIcon',
		't' => 'TabletDeviceIcon',
		'm' => 'MobileDeviceIcon'
	];

    public function __construct($data = [], $args = null) {
    	parent::__construct($data, $args);

      	add_action('elementor/frontend/after_register_scripts', function () {
			wp_register_script('sr7-elementor-preview', RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/elementor/assets/js/sr7-elementor-preview.js', ["elementor-frontend"], RevSliderFunctions::asset_time('admin/includes/shortcode_generator/elementor/assets/js/sr7-elementor-preview.js'), true);
		});
    }

    public function get_style_depends() {
        return ['sr7-elementor-shortcode-css'];
    }

   	public function get_script_depends() {
       return ['sr7-elementor-preview'];
   	}	

	/** @return string */
	public function get_name() {
		
		return 'slider_revolution';
		
	}

	/** @return string */
	public function get_title() {
		
		return 'Slider Revolution 7';
		
	}

	/** @return string */
	public function get_icon() {
		
		return 'eicon-sync';
		
	}

	/** @return array */
	public function get_categories() {
		
		return ['general'];
		
	}

	/** @return void */
	public function rs_register_controls() {

		$this->start_controls_section(
			'sr_module',
			[
				'label'     => __('Module Info', 'revslider'),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'collapsed' => false,
			]
		);
		// Hidden fields to store module data
		$fields = ['alias', 'shortcode', 'title', 'moduleId', 'slideId', 'slides', 'type',  'image', 'color', 'popup', 'offset', 'layoutOverride', 'notFound', 'premium', 'registered'];
		foreach($fields as $field) {
			$this->add_control(
				$field,
				[
					'type' => \Elementor\Controls_Manager::HIDDEN,
					'default' => ''
				]
			);
		};
		$this->add_control(
			'sr_module_logo',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => '<div class="sr--elementor--module--logo"></div>',
			]
		);
		$this->add_control(
			'sr_module_info_html',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => '<div class="sr--elementor--module--info"></div>',
			]
		);
		$this->add_control(
			'sr_buttons',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => '<div class="sr--elementor--buttons">'
					.'<button type="button" class="elementor-button elementor-button-default" data-event="sr7.selectModule">' . self::SVG['SelectIcon'] . __('Select Module', 'revslider') . '</button>'
					.'<button type="button" class="elementor-button elementor-button-default" data-event="sr7.editModule">' . self::SVG['EditIcon'] . __('Edit', 'revslider') . '</button>'
					.'</div>',
			]
		);
		$this->add_control(
			'live_preview',
			[
				'label' => __('Live Preview Render', 'revslider'),
				'type'  => Controls_Manager::SWITCHER,
				'default' => 'yes'
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'sr_module_layout',
			[
				'label'     => __('Module Layout', 'revslider'),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'collapsed' => true,
			]
		);
		$this->add_control(
			'layout_override',
			[
				'label' => __('Override Module Layout', 'revslider'),
				'type'  => Controls_Manager::SWITCHER,
			]
		);
		$this->add_control(
			'fullwidth',
			[
				'label'     => __('Full Width', 'revslider'),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [
					'layout_override' => 'yes',
				],
			]
		);
		$this->add_control(
			'fullheight',
			[
				'label'     => __('Full Height', 'revslider'),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [
					'layout_override' => 'yes',
				],
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'sr_popup',
			[
				'label'     => __('Use as Modal', 'revslider'),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'collapsed' => true,
			]
		);
		$this->add_control(
			'modal',
			[
				'label' => __('Insert Module as Modal (Popup)', 'revslider'),
				'type'  => Controls_Manager::SWITCHER,
			]
		);
		$this->add_control(
			'popup_cookie_use',
			[
				'label' => __('1 Time Per Session', 'revslider'),
				'type'  => Controls_Manager::SWITCHER,
			]
		);
		$this->add_control(
			'popup_cookie_value',
			[
				'label' => __('Session (hours)', 'revslider'),
				'type'  => Controls_Manager::NUMBER,
				'min'   => 0,
				'max'   => 1000,
				'default' => 24,
				'description' => __('Relating on Pop Up after Time and Scroll Position', 'revslider')
			]
		);
		$this->add_control(
			'popup_time_use',
			[
				'label' => __('Pop Up after Time', 'revslider'),
				'type'  => Controls_Manager::SWITCHER,
			]
		);
		$this->add_control(
			'popup_time_value',
			[
				'label' => __('After (ms)', 'revslider'),
				'type'  => Controls_Manager::NUMBER,
				'min'   => 0,
				'max'   => 200000,
				'default' => '2000ms',
				'description' => __('Relating on Pop Up after Time and Scroll Position', 'revslider')
			]
		);
		$this->add_control(
			'popup_scroll_use',
			[
				'label' => __('Pop Up at Scroll Position', 'revslider'),
				'type'  => Controls_Manager::SWITCHER,
			]
		);
		$this->add_control(
			'popup_scroll_type',
			[
				'label'   => __('Based On', 'revslider'),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'offset'    => __('Offset', 'revslider'),
					'container' => __('Container', 'revslider'),
				],
				'default' => 'offset',
			]
		);
		$this->add_control(
			'popup_scroll_offset',
			[
				'label' => __('Offset', 'revslider'),
				'type'  => Controls_Manager::NUMBER,
				'min'   => -1000,
				'max'   => 200000,
				'default' => '2000px',
			]
		);
		$this->add_control(
			'popup_scroll_container',
			[
				'label' => __('Container', 'revslider'),
				'type'  => Controls_Manager::TEXT,
			]
		);
		$this->add_control(
			'popup_event_use',
			[
				'label' => __('Pop Up by Events', 'revslider'),
				'type'  => Controls_Manager::SWITCHER,
			]
		);
		$this->add_control(
			'popup_event_name',
			[
				'label' => __('Listen to', 'revslider'),
				'type'  => Controls_Manager::TEXT,
				'description' => __('i.e.:', 'revslider') . '<code></code>'
			]
		);
		$this->add_control(
			'popup_hash_use',
			[
				'label' => __('Pop Up on URL Hash', 'revslider'),
				'type'  => Controls_Manager::SWITCHER,
			]
		);
		$this->add_control(
			'popup_hash_info',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => '<div class="sr--popup--hash--preview"></div>',
			]
		);
		$this->add_control(
			'popup_note',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => __("Modals can also be triggered by Layer Actions. See more details in ", 'revslider')
            		. '<a href="https://www.themepunch.com/slider-revolution/lightbox-modal/" target="_blank">' . __("Modal Documentation", 'revslider') . '</a>',
			]
		);
		$this->end_controls_section();
/*
		$this->start_controls_section(
			'sr_offsets',
			[
				'label'     => __('Block Offsets', 'revslider'),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'collapsed' => true,
			]
		);
		$offsetMatrixHTML = '';
		foreach(self::DEVICES as $device) {
			$offsetMatrixHTML .= '<div class="sr--offset--row">';
			$offsetMatrixHTML .= self::SVG[self::DEVICE_ICONS[$device]];
			$offsetMatrixHTML .= '<div class="sr--offset--inputs">';
			foreach(self::SIDES as $side) {
				$offsetMatrixHTML .= '<input type="text" class="sr--offset--input sr--offset--' . $side . '" data-device="' . esc_attr($device) . '" data-side="' . esc_attr($side) . '" name="offset[' . esc_attr($device) . '][' . esc_attr($side) . ']" placeholder="0" value="0" disabled="disabled" />';
			}
			$offsetMatrixHTML .= '</div>';
			$offsetMatrixHTML .= '<input type="checkbox" class="sr--offset--toggle" data-device="' . esc_attr($device) . '" name="offset[' . esc_attr($device) . '].use" />';
			$offsetMatrixHTML .= '</div>';
		}
		$this->add_control(
			'offset_ui',
			[
				'type' => Controls_Manager::RAW_HTML,
				'raw'  => '<div class="sr--elementor--module--offset sr--offset--panel">' . $offsetMatrixHTML . '</div>',
			]
		);
		$this->end_controls_section();
*/
		$this->start_controls_section(
			'sr_depth',
			[
				'label'     => __('Block Depth', 'revslider'),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'collapsed' => true,
			]
		);
		$this->add_control(
			'zindex',
			[
				'label' => __('Z-Index', 'revslider'),
				'type'  => Controls_Manager::NUMBER,
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'sr_advanced',
			[
				'label'     => __('Advanced', 'revslider'),
				'tab'       => Controls_Manager::TAB_CONTENT,
				'collapsed' => true,
			]
		);
		$this->add_control(
			'cssclasses',
			[
				'label' => __('Additional CSS class', 'revslider'),
				'type'  => Controls_Manager::TEXT
			]
		);
		$this->add_control(
			'wrapperid',
			[
				'label' => __('Module Wrapper IDs', 'revslider'),
				'type'  => Controls_Manager::TEXT
			]
		);
		$this->end_controls_section();
		
	}

	/** @return void */
	protected function register_controls() {
		$this->rs_register_controls();
	}

	/** @return void echoes the slider (or just the shortcode while editing) */
	protected function render() {

		$shortcode = $this->get_settings_for_display( 'shortcode' );
		$livePreview = $this->get_settings_for_display( 'live_preview' );
		$alias = $this->get_settings_for_display( 'alias' );

		if (strpos($shortcode, 'alias="' . esc_attr($alias) . '"') === false && $alias) {
			$shortcode = preg_replace('/alias="[^"]*"/', 'alias="' . esc_attr($alias) . '"', $shortcode);
		}

		if (\Elementor\Plugin::$instance->editor->is_edit_mode() && $livePreview != "yes") {
			
			$notFound = $this->get_settings_for_display('notFound') == "yes";
			$premium = $this->get_settings_for_display('premium') == "yes";
			$registered = $this->get_settings_for_display('registered') == "yes";

			$alias = $this->get_settings_for_display('alias');
			$title = $this->get_settings_for_display('title');
			if (!$title) $title = $this->get_settings_for_display('revslidertitle');

			$blockTitle = $title ? $title : ($alias ? $alias : __('No Module Selected', 'revslider'));

			$backgroundImage = $this->get_settings_for_display('image');
			$backgroundImage = 'url(' . ($backgroundImage && is_string($backgroundImage) ? esc_url($backgroundImage) : RS_PLUGIN_URL_CLEAN . "admin/assets/images/sr7placeholder.webp") . ')';

			$backgroundColor = $this->get_settings_for_display('color');
			$backgroundColor = $backgroundColor && is_string($backgroundColor) ? esc_attr($backgroundColor) : 'inherit';

			$forceRender = $title && !$alias ? ' sr--block--force--reload' : '';

			echo '<div class="sr--block--wrap' . $forceRender . '">';
			echo '<div class="sr--block--head">';
			echo '<div class="sr--block--logo">' . self::SVG['SR7Icon'] .  '</div>';
			echo '<div class="sr--block--title">' . esc_html($blockTitle) . '</div>';
			//echo '<button type="button" class="elementor-button elementor-button-default sr--block--button sr--block--preview--select">' . self::SVG['SelectIcon'] . __('Select Module', 'revslider') . '</button>';
			if ($notFound) {
				echo '<div class="sr--block--notfound sr--block--label sr--block--error">' . __('Module Not Found', 'revslider') . '</div>';
			} else {
				//echo '<button type="button" class="elementor-button elementor-button-default sr--block--button sr--block--preview--edit">' . self::SVG['EditIcon'] . __('Edit Module', 'revslider') . '</button>';
			}
			echo '</div>';
			if ($alias && !$notFound) {
				echo '<div class="revslider sr--block--preview" style="background-image:' . $backgroundImage . ';background-color:' . $backgroundColor . ';">';
				if ($premium) {
					if ($registered) {
						echo '<div class="sr--block--label sr--block--premium sr--show--onreg">' . __('Premium Template', 'revslider') . '</div>';
					} else {
						echo '<div class="sr--block--label sr--block--error sr--show--onnotreg sr--hide--on--parent--hover">' . self::SVG['EyeIcon'] . __('Premium Template', 'revslider') . '</div>';
						echo '<div class="sr--block--label sr--block--error sr--show--onnotreg sr--show--on--parent--hover">' . self::SVG['EyeIcon'] . __('Register License to Unlock', 'revslider') . '</div>';
					}
				}
				echo '<div class="revslider sr--block--thumb" style="background-image:' . $backgroundImage . ';background-color:' . $backgroundColor . ';">';
				echo '</div>';
			}
			echo '</div>';

		} else {

			$className = $this->get_settings_for_display('cssclasses');
			$className = "wp-block-themepunch-revslider revslider" . ($className ? " " . esc_attr($className) : "");

			$wrapperid = $this->get_settings_for_display('wrapperid');
			$wrapperid = $wrapperid ? ' id="' . esc_attr($wrapperid) . '"' : "";

			$zindex = $this->get_settings_for_display( 'zindex' );
			$style = $zindex ? ' style="z-index:'.esc_attr($zindex).';"' : '';

			if (\Elementor\Plugin::$instance->editor->is_edit_mode() && $livePreview == "yes") {
				$m = "SR7.M['SR7_" . $this->get_settings_for_display('moduleId') . "_1']";
				echo "<script>if (SR7?.M && $m) delete $m;</script>";
			}

			echo '<div class="' . $className . '"' . $wrapperid . $style . '>' . ($livePreview == "yes" ? do_shortcode($shortcode) : $shortcode) . '</div>';

			if (\Elementor\Plugin::$instance->editor->is_edit_mode() && $livePreview == "yes") {
				echo "<script>SR7.E.elementorBackend = true; SR7.F.init();</script>";
			}
		}
	}
	
}

/**
 * function _register_controls() is deprecated since 3.1.0 of Elementor
 **/
/**
 * Same widget for Elementor below 3.1.0, where controls were registered through _register_controls()
 * instead of register_controls().
 */
class RevSliderElementorWidgetPre310 extends RevSliderElementorWidget {
	/** @return void */
	protected function _register_controls() {
		$this->rs_register_controls();
	}
}
