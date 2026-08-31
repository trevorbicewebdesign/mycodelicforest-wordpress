<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Shared foundation for the page builder integrations.
 *
 * Provides the slider picker dialog, the classic editor button and the JavaScript that inserts a
 * [rev_slider] shortcode - reused by the builder specific classes (Elementor, Avada, BeBuilder, WPBakery,
 * Divi, Gutenberg) instead of each implementing its own.
 */
class RevSliderShortcodeWizard extends RevSliderFunctions {

	/** @return void */
	public static function enqueue_scripts(){
		global $pagenow;

		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$action = $f->get_val($_GET, 'action');
		if($action === 'elementor') return;

		// only add scripts if native WordPress editor, Gutenberg or Visual Composer
		// Elementor has its own hooks for adding scripts
		
		if($action === 'edit' || in_array($pagenow, ['post-new.php', 'site-editor.php', 'widgets.php']) || $f->get_val($_GET, 'vc_action', '') === 'vc_inline'){
			self::add_scripts();
		}
	}

	/** @return void */
	public static function add_styles(){}

	/** @return void */
	public static function add_scripts($elementor = false, $divi = false) {
		global $SR_GLOBALS;
		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$action = $f->get_val($_GET, 'action');
		if($elementor && $action !== 'elementor') return;

		require_once(RS_PLUGIN_PATH . 'admin/includes/functions-admin.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/template.class.php');
		require_once(RS_PLUGIN_PATH . 'admin/includes/folder.class.php');
		require_once(RS_PLUGIN_PATH . 'public/revslider-front.class.php');

		//check user permissions
		if(!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;

		// checks for built-in gutenberg version
		$current_screen = function_exists('get_current_screen') ? get_current_screen() : '';
		$is_gutenberg = !empty($current_screen) && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor();

		if(!$elementor && !$divi){
			//verify the post type
			global $typenow, $pagenow;

			if($pagenow !== 'site-editor.php'){
				$post_types = get_post_types();
				if(empty($post_types) || !is_array($post_types)) $post_types = ['post', 'page'];
				if(!in_array($typenow, $post_types) && $pagenow !== 'widgets.php') return;
			}
			
			// checks for old plugin version
			if(!$is_gutenberg) $is_gutenberg = function_exists('is_gutenberg_page') && is_gutenberg_page();

			// gutenberg
			if(!$is_gutenberg){
				add_filter('mce_external_plugins', ['RevSliderShortcodeWizard', 'add_tinymce_shortcode_editor_plugin']);
				add_filter('mce_buttons', ['RevSliderShortcodeWizard', 'add_tinymce_shortcode_editor_button']);
			}

			if($pagenow !== 'site-editor.php') self::add_styles(); //the styles need to be added through the block editor filter in site editor
		}
		
		//add v7 scripts/css
		$rs_front	= RevSliderGlobals::instance()->get('RevSliderFront');
		$rs_fonts	= RevSliderGlobals::instance()->get('RevSliderFonts');
		$rs_output	= RevSliderGlobals::instance()->get('RevSlider7Output');
		wp_enqueue_script('sr7', RS_PLUGIN_URL_CLEAN . 'public/js/sr7.js', [], self::asset_time('public/js/sr7.js'), ['strategy' => 'async']);	
		wp_enqueue_script('sr7page', RS_PLUGIN_URL_CLEAN . 'public/js/page.js', [], self::asset_time('public/js/page.js'), ['strategy' => 'async']);

		if (!$is_gutenberg) wp_enqueue_style('sr7css', RS_PLUGIN_URL_CLEAN . 'public/css/sr7.css', [], self::asset_time('public/css/sr7.css'));


		wp_enqueue_script('_tpt', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tptools.js', [], self::asset_time('public/js/libs/tptools.js'), ['strategy' => 'async']);		
		add_action('wp_footer', [$rs_fonts, 'load_google_fonts']);
		add_action('wp_footer', [$rs_output, 'add_js'], 100);

		$rsaf = new RevSliderFunctionsAdmin();
		$rsa = $rsaf->get_short_library();
		if(!empty($rsa)) $obj = $rsaf->json_encode_client_side($rsa);

		$favs = $rsaf->get_options(['favorites', 'favorites'], []);
		$favs = !empty($favs) ? $rsaf->json_encode_client_side($favs) : false;
		
		$rs_color_picker_presets = RSColorpicker::get_color_presets();
		
		$global = $rs_front->get_global_settings();
		echo $rs_front->js_add_header_scripts();

		echo self::get_shortcode_javascript();
		?>
		<script>
			var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>';
		</script>
		<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
		<?php
	}

	/** @return string the JS the page builders use to insert a slider shortcode */
	public static function get_shortcode_javascript($from_api = false){
		//check user permissions
		if(!current_user_can('edit_posts') && !current_user_can('edit_pages')) return;

		$upload_dir = wp_upload_dir();
		$sr_upload_url = !empty($upload_dir['baseurl']) ? rtrim($upload_dir['baseurl'], '/') . '/' : '';

		ob_start();
		?>
		<script>
			window.SR7			??= {};
			SR7.LIB				??= {};
			SR7.E				??= {gAddons:{}};
			SR7.E.registered	= <?php echo (self::is_registered() !== true) ? 'false' : 'true'; ?>;
			SR7.E.resources		??= {};
			SR7.E.modules		??= [];
			SR7.E.wp_upload_url	= "<?php echo esc_js($sr_upload_url); ?>";
			<?php if (!$from_api) { ?>
				SR7.E.block_nonce 	= "<?php echo wp_create_nonce('revslider_actions'); ?>";
			<?php } ?>
			SR7.LANG ??= {};
			SR7.LANG = Object.assign(SR7.LANG, <?php echo wp_json_encode([
				'Please wait...' => __('Please wait...', 'revslider'),   // was __() (no echo) -> shipped empty; fixed
				'Premium'        => __('Premium', 'revslider'),
				'Slider'         => __('Slider', 'revslider'),
				'Hero'           => __('Hero', 'revslider'),
				'Carousel'       => __('Carousel', 'revslider'),
				'Per Pages'      => __('Per Page', 'revslider'),
				'All Items'      => __('All Items', 'revslider'),
				'Show all items' => __('Show all items', 'revslider'),
			]); ?>);
		</script>
		<?php
		return ob_get_clean();
	}

	/** @return bool */
	public static function is_registered(){
		require_once(RS_PLUGIN_PATH . 'admin/includes/functions-admin.class.php');
		$rsaf = new RevSliderFunctionsAdmin();
		return $rsaf->_truefalse($rsaf->get_options(['system', 'valid'], 'false'));
	}

	/** @return string the slider picker dialog shared by all builders */
	public static function get_shortcode_extended_markup(){
		ob_start();
		?>
		<script>
			window.SrSp = window.SrSp || class SrSp extends HTMLElement {
				static get observedAttributes() {return ['h', 'w'];}
				constructor() {super();}
				connectedCallback() {this.updateDimensions();}
				attributeChangedCallback(name, oldValue, newValue) {this.updateDimensions();}
				updateDimensions() {
					const height = this.getAttribute('h');
					const width = this.getAttribute('w');
					if (width !== null) {
					this.style.display = 'inline-block';
					this.style.width = `${width}px`;
					this.style.height = height ? `${height}px` : 'auto';
					} else {
					this.style.display = 'block';
					this.style.height = height ? `${height}px` : 'auto';
					}
				}
			}
			if (!customElements.get('sr-sp')) customElements.define('sr-sp', SrSp);
		</script>
		<?php
		return ob_get_clean();
	}	

	/**
	 * add script tinymce shortcode script
	 * @since: 5.1.1
	 * @return array
	 */
	public static function add_tinymce_shortcode_editor_plugin($plugin_array){
		//this is an OLD js from sr6. needs to be updated or removed
		//$plugin_array['revslider_sc_button'] = RS_PLUGIN_URL . 'admin-sr6/assets/js/shortcode_generator/tinymce.js';

		return $plugin_array;
	}

	/**
	 * Add button to tinymce
	 * @since: 5.1.1
	 * @return array
	 */
	public static function add_tinymce_shortcode_editor_button($buttons){
		array_push($buttons, 'revslider_sc_button');

		return $buttons;
	}

	/**
	 * add wildcards metabox variables to posts
	 * @var $post_types: null = all, post = only posts
	 * @return void
	 */
	public static function add_slider_meta_box($post_types = null){
		try {
			$post_types = [];
			register_post_meta('', 'rs_blank_template', [
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string'
			]);
			register_post_meta('', 'rs_page_bg_color', [
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string'
			]);
			register_post_meta( '', 'slide_template_v7', [
				'show_in_rest'   => true,
				'single'         => true,
				'type'           => 'string'
			]);
		} catch (Exception $e){}
	}

	/**
	 * add wildcards metabox variables to posts
	 * @var $post_types: null = all, post = only posts
	 * @return void
	 */
	public static function add_slider_meta_box_assets($post_types = null){
		try {
			wp_enqueue_script('slider_revolution_metabox_js', RS_PLUGIN_URL_CLEAN . 'admin/includes/meta_box/build/index.js', ['wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-core-data', 'wp-data'], self::asset_time('admin/includes/meta_box/build/index.js'));
			wp_enqueue_style('slider_revolution_metabox_css', RS_PLUGIN_URL_CLEAN . 'admin/includes/meta_box/build/index.css', self::asset_time('admin/includes/meta_box/build/index.css'));
		} catch (Exception $e){}
	}

	/**
	 * on save post meta. Update metaboxes data from post, add it to the post meta 
	 * @return int|false
	 */
	public static function on_updated_post_meta($meta_id, $post_id, $meta_key, $meta_value){
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;
		if(empty($post_id)) return false;
		if(function_exists('is_user_logged_in') && !is_user_logged_in() || !current_user_can('edit_post', $post_id)) return $post_id;

		if ($meta_key === "slide_template_v7") {
			if (in_array($meta_value, ['', 'default'])) {
				delete_post_meta($post_id, $meta_key);
			}
		} else if ($meta_key === "rs_page_bg_color") {
			if(strtolower($meta_value) === '#ffffff'){
				delete_post_meta($post_id, $meta_key);
			}
		} else if ($meta_key === "rs_blank_template") {
			if(empty($meta_value) && get_post_meta($post_id, '_wp_page_template', true) == '../public/views/revslider-page-template.php'){
				update_post_meta($post_id, '_wp_page_template', '');
			}
			if(!empty($meta_value) &&  $meta_value == 'on'){
				update_post_meta($post_id, '_wp_page_template', '../public/views/revslider-page-template.php');
			}
		}
	}

	/**
	 * Enqueue styles for WP Bakery
	 * @return void
	 */
	public static function enqueue_wpbakery_styles() {
		wp_enqueue_style('slider_revolution_wpbakery_css', RS_PLUGIN_URL_CLEAN . 'admin/includes/shortcode_generator/wpbakery/assets/css/sr7-wpbakery.css', [], self::asset_time('admin/includes/shortcode_generator/wpbakery/assets/css/sr7-wpbakery.css'));
	}	

}
