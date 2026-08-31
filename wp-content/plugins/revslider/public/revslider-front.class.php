<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Front end bootstrap and the plugin's database schema.
 *
 * Registers the front end scripts, the admin bar entry and the integrations with consent, SEO and caching
 * plugins. It also owns the table definitions: create_tables() runs on activation and on admin_init, and
 * only does anything while the stored table version is behind CURRENT_TABLE_VERSION.
 */
class RevSliderFront extends RevSliderFunctions {
	const TABLE_SLIDER			 = 'revslider_sliders7';
	const TABLE_SLIDES			 = 'revslider_slides7';
	const TABLE_SLIDER_PREVIEW	 = 'revslider_preview_sliders7';
	const TABLE_SLIDES_PREVIEW	 = 'revslider_preview_slides7';
	const TABLE_LAYER_ANIMATIONS = 'revslider_layer_animations';
	const TABLE_NAVIGATIONS		 = 'revslider_navigations';
	
	//obsolete tables, V6 are needed to check for proper upgrade
	const TABLE_SLIDER_V6		 = 'revslider_sliders';
	const TABLE_SLIDES_V6		 = 'revslider_slides';
	const TABLE_CSS_V6			 = 'revslider_css';
	const TABLE_STATIC_SLIDES	 = 'revslider_static_slides';
	const TABLE_SETTINGS		 = 'revslider_settings'; //existed prior 5.0 and still needed for updating from 4.x to any version after 5.x
	
	//deliberately NOT bumped for the charset/index/alias improvements in create_tables(): create_tables() only
	//runs while the stored table version is lower than this one, so leaving it at 1.0.17 means installations
	//that are already on 1.0.17 never touch their schema again. they have been running fine for years and an
	//ALTER TABLE on a live LONGTEXT table is not worth the risk. New installs start at 1.0.0 and therefore get
	//the improved definitions right away.
	const CURRENT_TABLE_VERSION	 = '1.0.17';
	
	public function __construct(){
		parent::__construct();
		add_action('wp_enqueue_scripts', [$this, 'add_actions']);
		add_action('plugins_loaded', [$this, 'add_cookie_actions']);
	}

	/** @return void */
	public static function welcome_screen_activate(){
		set_transient('_revslider_welcome_screen_activation_redirect', true, 60);
	}

	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @return void
	 */
	public static function add_meta_generator(){
		echo apply_filters('revslider_meta_generator', '<meta name="generator" content="Powered by Slider Revolution ' . RS_REVISION . ' - responsive, Mobile-Friendly Slider Plugin for WordPress with comfortable drag and drop interface." />' . "\n");
	}

	/**
	 * Add all actions that the frontend needs here
	 * @return void registers the front end scripts and styles
	 **/
	public function add_scripts(){
		global $wp_scripts;
		if(version_compare($this->get_val($wp_scripts, ['registered', 'tp-tools', 'ver'], '1.0'), RS_TP_TOOLS, '<')){
			wp_deregister_script('tp-tools');
			wp_dequeue_script('tp-tools');
		}
		wp_enqueue_script('tp-tools', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tptools.js', [], $this->asset_time('public/js/libs/tptools.js'), ['strategy' => 'async']);
		wp_enqueue_script('sr7', RS_PLUGIN_URL_CLEAN . 'public/js/sr7.js', [], $this->asset_time('public/js/sr7.js'), ['strategy' => 'async']);			
		wp_enqueue_style('sr7css', RS_PLUGIN_URL_CLEAN . 'public/css/sr7.css', [], $this->asset_time('public/css/sr7.css'));
		
		do_action('sr_front_add_scripts', $this);
	}

	/**
	 * Add all filters to block removal of SR7 modules from cookie banners
	 * @return void registers the consent plugin filters
	 **/
	public function add_cookie_actions(){
		/**
		 * Real Cookie Banner
		 * Let Real Cookie Banner IGNORE Slider Revolution inline scripts
		 * so SR7 can initialize even without consent.
		 */
		add_filter('RCB/Blocker/InlineScript/IsBlocked', function($isBlocked, $script){
			//SR7 JSON always contains "SR7.JSON["
			if(strpos($script, 'SR7.JSON[') === false) return $isBlocked;
		
			//Prefer official API: disable blocking for this script
			if(method_exists($isBlocked, 'disableBlocking')){
				$isBlocked->disableBlocking();
				return $isBlocked;
			}

			//Fallback: clear all block data (also deactivates blocking)
			if(method_exists($isBlocked, 'setBlocked')) $isBlocked->setBlocked([]);
			if(method_exists($isBlocked, 'setBlockedExpressions')) $isBlocked->setBlockedExpressions([]);

			return $isBlocked;
		}, 10, 2);

		/**
		 * Complianz
		 * Treat the SR7 inline JSON script as functional in Complianz
		 * so it will NOT be blocked / converted to type="text/plain".
		 * $total_match contains the full <script> ... </script> match
		 * $found = true if Complianz thinks this is a 3rd-party script
		 */
		add_filter('cmplz_service_category', function($class, $total_match, $found){
			//Only touch SR7 inline scripts
			if($found && strpos($total_match, 'SR7.JSON[') !== false) return 'functional'; // Mark as "functional" so Complianz doesn't prior-block it

			return $class;
		}, 10, 3);

		/**
		 * CookieYes
		 * Add data-wcc="necessary" to ALL <script> tags inside Slider Revolution output
		 **/
		//if(is_plugin_active('cookie-law-info/cookie-law-info.php')){
		
		add_filter('revslider_html_output', function ($content, $slider_obj){
			if(!defined('CLI_VERSION')) return $content;

			return preg_replace(
				'/<script\b(?![^>]*data-wcc)/i',
				'<script data-wcc="necessary"',
				$content
			);
		}, 10, 2);

		/**
		 * Cookiebot
		 **/
		add_filter('revslider_html_output', function($content, $slider_obj){

			if(!defined('CYBOT_COOKIEBOT_PLUGIN_URL')) return $content;

			// Only touch SR7 scripts, not everything
			if(false === strpos($content, 'SR7.JSON[')) return $content;

			// For inline SR7 script tags, add data-cookieconsent="ignore"
			// so Cookiebot’s *auto* blocker will not touch them.
			// (This is the official way to tell Cookiebot to ignore a script.) :contentReference[oaicite:0]{index=0}
			$content = preg_replace(
				'/<script\b(?![^>]*data-cookieconsent)/i',
				'<script data-cookieconsent="ignore"',
				$content
			);

			return $content;
		}, 10, 2 );

		/**
		 * Cookiebot
		 **/
		add_filter('revslider_html_output', function($content, $slider_obj){

			if(!defined('BORLABS_COOKIE_CACHE_PATH')) return $content;

			// Only touch SR7 scripts, not everything
			if(false === strpos($content, 'SR7.JSON[')) return $content;

			// For inline SR7 script tags, add data-cookieconsent="ignore"
			// so Cookiebot’s *auto* blocker will not touch them.
			// (This is the official way to tell Cookiebot to ignore a script.) :contentReference[oaicite:0]{index=0}
			 $content = preg_replace(
				'/<script\b(?![^>]*data-sr7-essential)/i',
				'<script data-sr7-essential="1"',
				$content
			);

			return $content;
		}, 10, 2 );

		/**
		 * Borlabs Cookie
		 */
		add_filter('revslider_js_add_header_scripts_js', function($content){
			if(!defined('BORLABS_COOKIE_VERSION')) return $content;

			return str_replace(
				'<script',
				'<script data-borlabs-cookie-script-blocker-ignore',
				$content
			);
		});
		
	}

	/**
	 * Add custom HTML to the style tags
	 * @return string
	 **/
	public function add_html_to_style_tags($html, $handle) {
		// Check if it's the stylesheet you want to modify
		if ($handle !== 'sr7pagecsslp') return $html;

		$html = str_replace("rel='stylesheet'", "rel='preload'", $html);		
		$html = str_replace('/>', 'as="style" fetchpriority="low" onload="this.rel=\'stylesheet\'" />', $html);
		$html = str_replace("rel='stylesheet'", "rel='preload'", $html);
		$html = str_replace('/>', 'as="style" fetchpriority="high" onload="this.rel=\'stylesheet\'" />', $html);

		return $html;
	}
	  
	/** @return bool */
	public function add_actions(){
		global $SR_GLOBALS;
		
		$fonts		= RevSliderGlobals::instance()->get('RevSliderFonts');
		$global	 	= $this->get_global_settings();
		$inc_global	= $this->_truefalse($this->get_val($global, 'inclAll', true));		
		$inc_footer = $this->_truefalse($this->get_val($global, ['script', 'footer'], true));
		$widget	 	= is_active_widget(false, false, 'rev-slider-widget', true);
		
		$load = false;
		$load = apply_filters('revslider_include_libraries', $load);
		$load = ($SR_GLOBALS['preview_mode'] === true) ? true : $load;
		$load = ($inc_global === true) ? true : $load;
		$load = (self::has_shortcode('rev_slider') === true || self::has_shortcode('sr7') === true) ? true : $load;
		$load = ($widget !== false) ? true : $load;
		
		if($inc_global === false){
			$output = new RevSlider7Output();
			$output->set_add_to($this->get_val($global, 'incl', []));
			$add_to = $output->check_add_to(true);
			$load	= ($add_to === true) ? true : $load;
		}

		add_action('wp_before_admin_bar_render', [$this, 'add_admin_menu_nodes']);
		add_action('wp_footer', [$this, 'add_admin_bar'], 99);
		
		if($load === false) return false;

		$this->add_scripts();

		add_action('wp_head', [$this, 'js_add_header_scripts'], 99);
		add_action('wp_head', [$this, 'load_header_fonts']);
		add_filter('style_loader_tag', [$this, 'add_html_to_style_tags'], 10, 2);
		add_action('wp_footer', [$fonts, 'load_google_fonts']);
		add_action('wp_footer', [$this, 'add_deprecation_warnings']);
		add_action('wp_head', ['RevSliderFront', 'add_meta_generator']);
	}

	/** @return bool */
	public function js_add_header_scripts(){
		global $SR_GLOBALS;
		if($SR_GLOBALS['header_js'] === true) return false;

		$global = $this->get_global_settings();

		$breakpoints = [];
		$breakpoints[] = intval($this->get_val($global, ['size', 'desktop'], '1240'));
		$breakpoints[] = intval($this->get_val($global, ['size', 'notebook'], '1024'));
		$breakpoints[] = intval($this->get_val($global, ['size', 'tablet'], '778'));
		$breakpoints[] = intval($this->get_val($global, ['size', 'mobile'], '480'));
		$ytnc	 = $this->_truefalse($this->get_val($global, 'ytnc', false));
		$fSUVW	 = $this->_truefalse($this->get_val($global, 'fSUVW', false));		
		$libs	 = [];
		$css	 = [];
		$modules = $this->get_val($SR_GLOBALS, 'modules', []);
		//Dev by default; the release build rewrites this line to 'false' (compiler gulp-frontend.js "silent-update-devMode").
		//It must NOT be inferred from three.js any more: three.js used to be concatenated into webgl.js and deleted, so "three.js is
		//missing" happened to mean "this is a release build". THREE now ships as its own file (2D pages skip ~200KB), so that signal
		//is gone — three.js exists in BOTH dev and release, and keying devMode off it would make every release run in dev mode.
		$devMode = 'false';
		if($SR_GLOBALS['markup_export'] === false) $modules[] = 'migration';

		if(file_exists(RS_PLUGIN_PATH . 'public/js/libs/three.js'))		$libs[] = "'THREE'";
		if(file_exists(RS_PLUGIN_PATH . 'public/js/libs/webgl.js'))		$libs[] = "'WEBGL'";
		if(file_exists(RS_PLUGIN_PATH . 'public/js/libs/effects.js'))	$libs[] = "'EFFECTS'";
		if(file_exists(RS_PLUGIN_PATH . 'public/js/libs/tpgsap.js'))	$libs[] = "'tpgsap'";
		if(file_exists(RS_PLUGIN_PATH . 'public/css/sr7.lp.css'))		$css[] = "'csslp'";
		if(file_exists(RS_PLUGIN_PATH . 'public/css/sr7.btns.css'))		$css[] = "'cssbtns'";
		if(file_exists(RS_PLUGIN_PATH . 'public/css/sr7.filters.css'))	$css[] = "'cssfilters'";
		if(file_exists(RS_PLUGIN_PATH . 'public/css/sr7.nav.css'))		$css[] = "'cssnav'";
		if(file_exists(RS_PLUGIN_PATH . 'public/css/sr7.media.css'))	$css[] = "'cssmedia'";
		
		$script = '<script>' . "\n"; // data-type="SR7-content"
		$script .= "	window._tpt			??= {};" . "\n";
		$script .= "	window.SR7			??= {};" . "\n";
		$script .= "	_tpt.R				??= {};" . "\n";
		$script .= "	_tpt.R.fonts		??= {};" . "\n";
		$script .= "	_tpt.R.fonts.customFonts??= {};" . "\n";
		$script .= "	SR7.devMode			=  ".$devMode.";" . "\n";
		$script .= "	SR7.F 				??= {};" . "\n";
		$script .= "	SR7.G				??= {};" . "\n";
		$script .= "	SR7.LIB				??= {};" . "\n";
		$script .= "	SR7.E				??= {};" . "\n";
		$script .= "	SR7.E.gAddons		??= {};" . "\n";
		$script .= "	SR7.E.php 			??= {};" . "\n";
		foreach($this->get_basic_metas() as $meta => $meta_val){
			$script .= "	SR7.E.php[". json_encode($meta) ."] = ". json_encode($meta_val) .";" . "\n";
		}
		$script .= "	SR7.E.nonce			= '". wp_create_nonce('RevSlider_Front') ."';" . "\n";
		$script .= "	SR7.E.ajaxurl		= '". admin_url('admin-ajax.php') ."';" . "\n";
		$script .= "	SR7.E.resturl		= '". get_rest_url() ."';" . "\n";
		$script .= "	SR7.E.slug_path		= '". str_replace(["\n", "\r"], '', RS_PLUGIN_SLUG_PATH) ."';" . "\n";
		$script .= "	SR7.E.slug			= '". str_replace(["\n", "\r"], '', RS_PLUGIN_SLUG) ."';" . "\n";
		$script .= "	SR7.E.plugin_url	= '". str_replace(["\n", "\r"], '', RS_PLUGIN_URL) ."';" . "\n";
		$script .= "	SR7.E.wp_plugin_url = '". str_replace(["\n", "\r"], '', WP_PLUGIN_URL) . "/" ."';" . "\n";
		$script .= "	SR7.E.revision		= '". RS_REVISION ."';" . "\n";
		//DEV-only cache-bust: with devMode on, the loader (tptools.js ~21: ver = assetver ?? revision) re-fetches the individual
		//runtime JS every reload so source edits appear without a build. Release / devMode=false leaves assetver unset -> static revision.
		if($devMode === 'true') $script .= "	SR7.E.assetver		= '". time() ."';" . "\n";
		$script .= "	SR7.E.latest_revision = '". $this->get_options(['system', 'version'], RS_REVISION) ."';" . "\n";
		$script .= "	SR7.E.fontBaseUrl	= '". ($this->get_val($global, 'fontdownload') === 'off' ? $this->modify_fonts_url('https://fonts.googleapis.com/css2?family=') : '') ."';" . "\n";
		$script .= "	SR7.G.breakPoints 	= [".implode(',', $breakpoints)."];" . "\n";
		$script .= "	SR7.G.fSUVW 		= ".(($fSUVW === true) ? 'true' : 'false').";" . "\n";
		if($this->get_val($global, ['gdpr', 'filter'], 'none') !== 'none'){
			$script .= "	SR7.G.gdprfilter	= '".$this->get_val($global, ['gdpr', 'filter'], 'none')."';". "\n";
			$script .= "	SR7.G.gdprcategory	= '". $this->get_val($global, ['gdpr', 'category'], 'marketing')."';". "\n";
		}
		$script .= "	SR7.E.modules 		= ['".implode("','", $modules)."'];" . "\n";
		if(!empty($libs))	$script .= '	SR7.E.libs 			= [' . implode(',', $libs) . '];' . "\n";
		if(!empty($css))	$script .= '	SR7.E.css 			= [' . implode(',', $css) . '];' . "\n";
		$script .= "	SR7.E.resources		??= {};" . "\n";
		$script .= "	SR7.E.ytnc			= ".(($ytnc === false) ? 'false' : 'true').";" . "\n";

		$script = apply_filters('revslider_js_add_header_scripts_js', $script);
		
		// Add Page Handler Inline Script
		if($this->get_val($global, ['getTec', 'core'], 'MIX') !== 'REST' || $SR_GLOBALS['markup_export'] === true){
			$script .= '	SR7.JSON			??= {};' ."\n";
		}
		if($SR_GLOBALS['markup_export'] === false) $script .= (file_get_contents(RS_PLUGIN_PATH . 'public/js/page.js'));

		$script .= '</script>' . "\n";
		echo apply_filters('revslider_js_add_header_scripts', $script);
		
		$SR_GLOBALS['header_js'] = true;
	}

	/** @return string */
	public function load_v7_slider(){
		global $SR_GLOBALS, $post;
		
		$used_slider	= [];
		$forced_slides	= [];
		$id				= (isset($post->ID)) ? $post->ID : '';
		//$all_shortcodes	= $this->get_shortcode_from_page($id);
		//$all_shortcodes = array_unique(array_merge($all_shortcodes, $SR_GLOBALS['sliders']));
		$all_shortcodes = $SR_GLOBALS['sliders'];
		$script			= '';

		if(empty($all_shortcodes)) return $script;

		$serial		= $SR_GLOBALS['serial'];
		$collection	= $SR_GLOBALS['collections']['ids'];
		$SR_GLOBALS['collections']['ids'] = [];
		if($SR_GLOBALS['serial'] > 0){
			$SR_GLOBALS['serial'] = 0;
		}
		$global		= $this->get_global_settings();
		$mode		= $this->get_val($global, ['getTec', 'core'], 'MIX');
		foreach($all_shortcodes ?? [] as $alias){
			$slider	= new RevSliderSlider();
			if(!$slider->check_alias($alias)) continue;
			
			$slider->init_by_alias($alias, false);
			if($slider->inited === false) continue;

			$dl = $slider->get_param('deepLinks', []);
			foreach($dl ?? [] as $slide_id){
				if(!in_array($slide_id, $forced_slides)) $forced_slides[] = $slide_id;
			}
			
			$used_slider[] = $slider;
		}

		foreach($used_slider ?? [] as $slider){
			$SR_GLOBALS['serial']++;
			$sid		= $slider->get_id();
			$slider_id	= $slider->get_param('id', '');
			$html_id	= (trim($slider_id) !== '') ? $slider_id : 'SR7_'.$sid.'_'.$SR_GLOBALS['serial'];
			$html_id	= $this->set_html_id_v7($html_id, true);
			$sticky     = $slider->get_param('sticky', '');
			if($sticky !== 'top' && $sticky !== 'bottom') $sticky = ($slider->get_param('fixed', false) !== false) ? 'top' : 'none';
			$full		= ($sticky !== 'none' || $slider->get_param('fixed', false) !== false || in_array($slider->get_param('type', ''), ['scene', 'hero', 'carousel'])) ? true : false;
			if($mode === 'MIX' && $SR_GLOBALS['serial'] > 2) $full = false; //we only print $forced_slides from now on
			$full		= ($slider->is_stream_post()) ? true : $full; //check if we are stream/post, these need to always write all layers (B-7235530610)

			if($SR_GLOBALS['markup_export'] === true){
				$script .= "	SR7.JSON['".$html_id."'] = 'assets/".$html_id.".json';"."\n";
			}else{
				$data	= $slider->get_full_slider_JSON(false, $full, [], $forced_slides);
				$data	= apply_filters('sr_load_slider_json', $data, $this);

				if($mode === 'MIX' && $SR_GLOBALS['serial'] > 2 && !empty($forced_slides)){ //we check if slides are in the forced_slides list, if not then we ignore
					$print = false;
					foreach($data['slides'] ?? [] as $slide){
						if(!in_array($this->get_val($slide, 'id'), $forced_slides)) continue;
						$print = true;
						break;
					}
					if($print === false) continue; //do not pront any information about the slider
				}

				//load dom data directly
				$script .= "	SR7.JSON['".$html_id."'] = ".json_encode($data).";"."\n";
			}

			if($mode === 'MIX' && $SR_GLOBALS['serial'] >= 2 && empty($forced_slides)) break;
		}
		$SR_GLOBALS['collections']['ids'] = $collection; //reset html ids here, so that they are later on empty when the page is parsed
		$SR_GLOBALS['serial'] = $serial; //reset back to what it was before

		return $script;
	}

	/**
	 * print in header
	 * @return void
	 **/
	public function load_header_fonts(){
		$global = $this->get_global_settings();
		if($this->get_val($global, ['fonts', 'download'], 'off') !== 'off') return;
		if($this->_truefalse($this->get_val($global,['fonts','dpc'], false)) === true) return;

		echo '<link rel="preconnect" href="https://fonts.googleapis.com">'."\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>'."\n";
	}

	/**
	 * adds deprecation warnings of functions that will cease to exist in v7
	 * @return void prints the collected deprecation notices as an HTML comment
	 */
	public function add_deprecation_warnings(){
		global $SR_GLOBALS;
		if(empty($SR_GLOBALS['deprecated'])) return;

		echo '<script>';
		echo 'window.SR7 ??= {};'."\n";
		echo 'SR7.E ??= {};'."\n";
		echo 'SR7.E.php ??= {};'."\n";
		echo 'SR7.E.php.warnings	= '.json_encode($SR_GLOBALS['deprecated']).';';
		echo '</script>'."\n";

	}

	/**
	 * the V7 tables this plugin owns (unprefixed)
	 *
	 * @return array
	 **/
	public static function get_own_tables(){
		return [
			self::TABLE_SLIDER,
			self::TABLE_SLIDES,
			self::TABLE_LAYER_ANIMATIONS,
			self::TABLE_NAVIGATIONS,
			self::TABLE_SLIDER_PREVIEW,
			self::TABLE_SLIDES_PREVIEW,
		];
	}

	/**
	 * Check V7 Tables
	 *
	 * @return array - array of tables that are missing
	 **/
	public static function check_tables(){
		global $wpdb;

		$return = [];
		$tables = self::get_own_tables();

		foreach ($tables as $table) {
			$table_name = $wpdb->prefix . $table;
			$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
			if ($table_exists != $table_name) {
				$return[] = $table;
			}
		}
		
		return $return;
	}

	/**
	 * Create V7 Tables
	 * @return void
	 **/
	public static function create_tables(){
		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$table_version = $f->get_options(['system', 'table'], '1.0.0');

		if(version_compare($table_version, self::CURRENT_TABLE_VERSION, '<')){
			global $wpdb;

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			//without this the tables inherit the MySQL *server* default (often latin1 or utf8mb3) instead of the
			//charset the site actually runs on. that silently mangles emoji/CJK in titles and params, and makes
			//every JOIN against wp_posts fail with "Illegal mix of collations". this only affects tables that
			//get *created* here - dbDelta never changes the charset of an existing table, and converting one
			//after the fact is intentionally out of scope (see CURRENT_TABLE_VERSION).
			$charset_collate = $wpdb->get_charset_collate();

			//`alias` is the column every front-end shortcode lookup goes through, so a real VARCHAR(191) with a
			//plain index beats a TEXT column with a prefix index. only for tables that do not exist yet though:
			//on an existing table this would be an ALTER that could truncate aliases longer than 191 chars.
			list($alias_def, $alias_idx)				 = self::alias_column_definition(self::TABLE_SLIDER);
			list($alias_def_preview, $alias_idx_preview) = self::alias_column_definition(self::TABLE_SLIDER_PREVIEW);

			//V7 tables
			$sql = "CREATE TABLE " . $wpdb->prefix . self::TABLE_SLIDER . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  title tinytext NOT NULL,
			  $alias_def,
			  params LONGTEXT NOT NULL,
			  settings text NULL,
			  type VARCHAR(191) NOT NULL DEFAULT '',
			  INDEX `type_index` (`type`(8)),
			  $alias_idx
			) $charset_collate;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . $wpdb->prefix . self::TABLE_SLIDES . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  slider_id int NOT NULL,
			  slide_order int not NULL,
			  params LONGTEXT NOT NULL,
			  layers LONGTEXT NOT NULL,
			  settings text NULL,
			  static VARCHAR(191) NOT NULL DEFAULT '',
			  INDEX `slider_id_index` (`slider_id`),
			  INDEX `idx_order_static_slider` (`slide_order`, `static`, `slider_id`)
			) $charset_collate;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . $wpdb->prefix . self::TABLE_LAYER_ANIMATIONS . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  handle TEXT NOT NULL,
			  params TEXT NOT NULL,
			  settings text NULL
			) $charset_collate;";
			dbDelta($sql);

			//`handle` is looked up on its own (RevSliderNavigation::get_navigation_by_handle) and together with
			//`type` (import). a composite index on (handle, type) serves both through the leftmost-prefix rule.
			$sql = "CREATE TABLE " . $wpdb->prefix . self::TABLE_NAVIGATIONS . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  name VARCHAR(191) NOT NULL,
			  handle VARCHAR(191) NOT NULL,
			  type VARCHAR(191) NOT NULL,
			  css LONGTEXT NOT NULL,
			  markup LONGTEXT NOT NULL,
			  settings LONGTEXT NULL,
			  INDEX `handle_type_index` (`handle`, `type`)
			) $charset_collate;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . $wpdb->prefix . self::TABLE_SLIDER_PREVIEW . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  title tinytext NOT NULL,
			  $alias_def_preview,
			  params LONGTEXT NOT NULL,
			  settings text NULL,
			  type VARCHAR(191) NOT NULL DEFAULT '',
			  INDEX `type_index` (`type`(8)),
			  $alias_idx_preview
			) $charset_collate;";
			dbDelta($sql);

			$sql = "CREATE TABLE " . $wpdb->prefix . self::TABLE_SLIDES_PREVIEW . " (
			  id int(9) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  slider_id int NOT NULL,
			  slide_order int not NULL,
			  params LONGTEXT NOT NULL,
			  layers LONGTEXT NOT NULL,
			  settings text NULL,
			  static VARCHAR(191) NOT NULL DEFAULT '',
			  INDEX `slider_id_preview_index` (`slider_id`),
			  INDEX `idx_order_preview_static_slider` (`slide_order`, `static`, `slider_id`)
			) $charset_collate;";
			dbDelta($sql);

			$f->update_option(['system', 'table'], self::CURRENT_TABLE_VERSION);
			//$table_version = self::CURRENT_TABLE_VERSION;
		}
	}

	/**
	 * column definition + index for the `alias` column of a slider table.
	 *
	 * a table that does not exist yet is created with a real VARCHAR(191) and a plain index. an existing table
	 * keeps the legacy TEXT column and its prefix index: changing it would make dbDelta emit an ALTER TABLE,
	 * and aliases longer than 191 characters would be silently truncated by it. Installs that have been running
	 * for years are not worth that risk - the improvement applies to new installations.
	 *
	 * @param string $table unprefixed table name
	 * @return array [column definition, index definition]
	 **/
	private static function alias_column_definition($table){
		global $wpdb;

		$name	= $wpdb->prefix . $table;
		$exists	= ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $name)) === $name);

		return ($exists)
			? ['alias tinytext', 'INDEX `alias_index` (`alias`(191))']
			: ['alias varchar(191) DEFAULT NULL', 'INDEX `alias_index` (`alias`)'];
	}

	/**
	 * Add functionality to gutenberg, elementor, visual composer and so on
	 * @return void wires up the Gutenberg/Elementor/WPBakery integrations
	 **/
	public static function add_post_editor(){
		/**
		 * Page Editor Extensions
		 **/
		if(function_exists('is_user_logged_in') && is_user_logged_in()){
			//only include gutenberg for production
			if(is_admin() && defined('ABSPATH')){
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if(function_exists('is_plugin_active') && !is_plugin_active('revslider-gutenberg/plugin.php')){
					require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/gutenberg/gutenberg-block.php');
					new RevSliderGutenberg('gutenberg/');
				}
			}
			
			require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/shortcode_generator.class.php');

			//Shortcode Wizard Includes
			//WPB Functionality
			require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/wpbakery/wpbakery.class.php');
			add_action('vc_before_init', ['RevSliderWpbakeryShortcode', 'visual_composer_include']);
			add_action('vc_before_init', ['RevSliderShortcodeWizard', 'enqueue_wpbakery_styles']);
			add_action('admin_enqueue_scripts', ['RevSliderShortcodeWizard', 'enqueue_scripts']);

			// BeBuilder Functionality
			require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/bebuilder/bebuilder.class.php');
			RevSliderBeBuilder::init();

			// Avada Builder Functionality
			require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/avada/avada.class.php');
			RevSliderAvada::init(RevSliderShortcodeWizard::is_registered());
		}

		//Elementor Global
		require_once(RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/elementor/elementor.class.php');
		add_action('init', ['RevSliderElementor', 'init']);
	}

	/**
	 * sets the post saving value to true, so that the output echo will not be done
	 * @return void
	 **/
	public static function set_post_saving(){
		global $SR_GLOBALS;
		$SR_GLOBALS['save_post'] = true;
	}
	
	/**
	 * get the images from posts/pages for yoast seo
	 * @return string adds the slider images to the Yoast sitemap entry
	 **/
	public static function get_images_for_seo($url, $type, $user){
		if(in_array($type, ['user', 'term'], true)) return $url;
		if(!is_object($user) || !isset($user->ID)) return $url;
		
		$post = get_post($user->ID);
		if(is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'rev_slider') || has_shortcode($post->post_content, 'sr7'))){
			preg_match_all('/\[rev_slider.*alias=.(.*)"\]/', $post->post_content, $shortcodesold);
			preg_match_all('/\[sr7.*alias=.(.*)"\]/', $post->post_content, $shortcodes);
			$sc = [];
			if(isset($shortcodes[1]) && $shortcodes[1] !== '')		 $sc = array_merge($sc, $shortcodes[1]);
			if(isset($shortcodesold[1]) && $shortcodesold[1] !== '') $sc = array_merge($sc, $shortcodesold[1]);

			foreach($sc ?? [] as $s){
				if(strpos($s, '"') !== false){
					$s = explode('"', $s);
					$s = (isset($s[0])) ? $s[0] : '';
				}
				if(!RevSliderSlider::alias_exists($s)) continue;
				
				$sldr = new RevSliderSlider();
				$sldr->init_by_alias($s);
				$sldr->get_slides();
				$imgs = $sldr->get_images();
				if(!empty($imgs)){
					if(!isset($url['images'])) $url['images'] = [];
					foreach($imgs as $v){
						$url['images'][] = $v;
					}
				}
			}
		}
		
		return $url;
	}

	/**
	 * add admin nodes
	 * @since: 5.0.5
	 * @return void
	 */
	public function add_admin_menu_nodes(){
		if(!is_super_admin() || !is_admin_bar_showing()){
			return;
		}

		self::_add_node('<span class="rs-label">Slider Revolution</span>', false, admin_url('admin.php?page=revslider'), ['class' => 'revslider-menu'], 'revslider'); //<span class="wp-menu-image dashicons-before dashicons-update"></span>

		//add all nodes of all Slider
		$sl = new RevSliderSlider();
		$sliders = $sl->get_slider_for_admin_menu();

		if(!empty($sliders)){
			foreach ($sliders as $id => $slider){
				self::_add_node('<span class="rs-label" data-alias="' . esc_attr($slider['alias']) . '">' . esc_html($slider['title']) . '</span>', 'revslider', admin_url('admin.php?page=revslider&view=editor&module='.$id), ['class' => 'revslider-sub-menu'], esc_attr($slider['alias'])); //<span class="wp-menu-image dashicons-before dashicons-update"></span>
			}
		}
	}

	/**
	 * add admin node
	 * @since: 5.0.5
	 * @return void
	 */
	public static function _add_node($title, $parent = false, $href = '', $custom_meta = [], $id = ''){
		if(!is_super_admin() || !is_admin_bar_showing()){
			return;
		}

		$id = ($id == '') ? strtolower(str_replace(' ', '-', $title)) : $id;
		
		//links from the current host will open in the current window
		$meta = (strpos($href, site_url()) !== false) ? [] : ['target' => '_blank']; //external links open in new tab/window
		$meta = array_merge($meta, $custom_meta);
		
		global $wp_admin_bar;
		$wp_admin_bar->add_node(['parent'=> $parent, 'id' => $id, 'title' => $title, 'href' => $href, 'meta' => $meta]);
	}

	/**
	 * add admin menu points in ToolBar Top
	 * @since: 5.0.5
	 * @return void
	 */
	public function add_admin_bar(){
		if(!is_super_admin() || !is_admin_bar_showing()) return;
		?>
		<script>
			/** @return void */
			function rs_adminBarToolBarTopFunction() {
				var revSliderDefault = document.querySelector('#wp-admin-bar-revslider-default');
				var sr7Module = document.querySelectorAll('sr7-module');

				if (revSliderDefault && sr7Module.length > 0) {
					var aliases = [];

					sr7Module.forEach(function(element) {
						aliases.push(element.getAttribute('data-alias'));
					});

					if (aliases.length > 0) {
						revSliderDefault.querySelectorAll('li').forEach(function(li) {
							var rsLabel = li.querySelector('.ab-item .rs-label');
							var t = rsLabel ? rsLabel.getAttribute('data-alias') : undefined;
							t = t !== undefined && t !== null ? t.trim() : t;

							if (aliases.indexOf(t) === -1) {
								li.remove();
							}
						});
					}
				} else {
					var revSlider = document.querySelector('#wp-admin-bar-revslider');
					if (revSlider) {
						revSlider.remove();
					}
				}
			}
			
			var adminBarLoaded_once = false;

			if (document.readyState === "loading") {
				document.addEventListener('readystatechange', function() {
					if ((document.readyState === "interactive" || document.readyState === "complete") && !adminBarLoaded_once) {
						adminBarLoaded_once = true;
						rs_adminBarToolBarTopFunction();
					}
				});
			} else {
				adminBarLoaded_once = true;
				rs_adminBarToolBarTopFunction();
			}
		</script>
		<?php
	}

	/**
	 * prevent WP Rocket from removing our frontend css for font loading
	 * @return array keeps WP Rocket from stripping our inline script attributes
	 */
	public static function wp_rocket_inline_atts_exclusions($inline_atts_exclusions){
		$inline_atts_exclusions[] = "sr7-inline-css";	
		return $inline_atts_exclusions;
	}

	/**
	 * check the current post for the existence of a short code
	 * @return bool does this post contain the given shortcode?
	 */  
	public static function has_shortcode($shortcode = ''){ 
		if(empty($shortcode)) return false;
		if(!is_singular()) return false;
		
		$post = get_post(get_the_ID());  
		if(empty($post)) return false;
		if(!isset($post->post_content)) return false;

		return (stripos($post->post_content, '[' . $shortcode) !== false) ? true : false;
	}

}
