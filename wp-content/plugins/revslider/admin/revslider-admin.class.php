<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2023 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Admin bootstrap: registers the menu pages, enqueues the editor assets and hosts the WordPress side
 * integrations (plugins screen, admin bar, privacy policy text, update checks).
 *
 * $user_role holds the *capability* required to use the plugin - set_user_role() maps the configured role
 * name onto it, and every permission check in the plugin reads it from here.
 */
class RevSliderAdmin extends RevSliderFunctionsAdmin {
	public $user_role		 = 'administrator';
	public $global_settings  = [];
	public $screens		 	 = []; //holds all RevSlider Relevant screens in it
	public $pages			 = ['revslider']; //, 'revslider_navigation', 'rev_addon', 'revslider_global_settings'
	public $view			 = 'slider';
	public $allowed_views	 = ['markups', 'editor', 'sliders', 'slider', 'slide', 'update']; //holds pages, that are allowed to be included
	public $path_views;

	public function __construct(){
		$this->global_settings	= $this->get_global_settings();
		$this->path_views		= RS_PLUGIN_PATH . 'admin/views/';

		$this->set_current_page();
		$this->set_user_role();
		$this->do_update_checks();
		$this->add_actions();
		$this->add_filters();

		parent::__construct();
	}

	/** @return void */
	public function add_filters(){
		//add_filter('admin_body_class', [$this, 'modify_admin_body_class']);
		add_filter('plugin_locale', [$this, 'change_lang'], 10, 2);
	}

	/** @return void */
	public function add_actions(){
		global $pagenow;

		$cache = RevSliderGlobals::instance()->get('RevSliderCache');
		
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles'], 98);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 98);

		add_action('plugins_loaded', [$this, 'load_plugin_textdomain']);
		add_action('admin_head', [$this, 'hide_notices'], 1);
		add_action('admin_menu', [$this, 'add_admin_pages']);
		add_action('admin_init', [$this, 'display_external_redirects']);
		add_action('admin_head', [$this, 'add_js_menu_open_blank']);
		add_action('enqueue_block_editor_assets', ['RevSliderShortcodeWizard', 'add_slider_meta_box_assets']);		
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts_global'], 99);
		
		add_action('save_post', [$cache, 'check_for_post_transient_deletion']);
		add_action('future_to_publish', [$cache, 'check_for_post_transient_deletion']);
		add_action('publish_post', [$cache, 'check_for_post_transient_deletion']);
		add_action('publish_future_post', [$cache, 'check_for_post_transient_deletion']);
		
		if(isset($pagenow) && $pagenow == 'plugins.php'){
			add_action('admin_notices', [$this, 'add_plugins_page_notices']);
			if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === false){
				add_filter('plugin_action_links_' . RS_PLUGIN_SLUG_PATH, [$this, 'add_plugin_action_links']);
			}
		}
		
		//group SR7 add-ons in the WP plugins list (data-level filters only, fail-safe by design)
		add_filter('all_plugins', [$this, 'hide_addon_plugins'], 999);
		add_filter('views_plugins', [$this, 'add_addon_plugins_view'], 999);
		add_filter('plugin_row_meta', [$this, 'add_addon_row_meta'], 10, 2);

		add_action('admin_init', [$this, 'merge_addon_notices'], 99);
		add_action('admin_init', [$this, 'add_suggested_privacy_content'], 15);
		
		
		$instagram = RevSliderGlobals::instance()->get('RevSliderInstagram');
		$instagram->add_actions();

		$facebook = RevSliderGlobals::instance()->get('RevSliderFacebook');
		$facebook->add_actions();
	}

	/**
	 * Collect SR7 add-on plugin files from a plugins array. An entry only counts as
	 * add-on when BOTH the folder follows the revslider-* pattern AND the plugin
	 * headers identify it as a ThemePunch "Slider Revolution ..." plugin, so other
	 * plugins can never be affected. Returns an empty array on any unexpected input.
	 * @return array plugin files of every installed AddOn
	 **/
	public function get_addon_plugin_files($plugins){
		$addons = array();
		if(!is_array($plugins)) return $addons;
		foreach($plugins as $file => $data){
			if(!is_string($file) || !is_array($data)) continue;
			if($file === RS_PLUGIN_SLUG_PATH) continue; //never the main plugin
			if(strpos($file, 'revslider-') !== 0) continue; //folder pattern
			$name	= $this->get_val($data, 'Name', '');
			$author	= $this->get_val($data, 'Author', '');
			if(stripos($name, 'Slider Revolution') !== 0) continue; //header check
			if(stripos($author, 'ThemePunch') === false) continue; //header check
			$addons[] = $file;
		}
		return $addons;
	}

	/**
	 * Whether add-on grouping is active. Disabled by the "groupAddons" global
	 * setting, by the reveal query argument, or when the main plugin is not the
	 * one running (paranoia guard) - any failure means "do not hide anything".
	 * @return bool true when AddOns should be hidden from the plugins screen
	 **/
	public function hide_addons_active(){
		try{
			if(!empty($_GET['sr7_show_addons'])) return false;
			if($this->_truefalse($this->get_val($this->global_settings, 'groupAddons', true)) !== true) return false;
			return true;
		}catch(Exception $e){
			return false;
		}
	}

	/**
	 * all_plugins filter: remove SR7 add-ons from the WP plugins list. Add-ons with
	 * a pending update stay visible so update workflows are never blocked. On any
	 * error the untouched list is returned - the fallback is always the WP default.
	 * @return array $plugins without the AddOn entries
	 **/
	public function hide_addon_plugins($plugins){
		try{
			if(!is_array($plugins) || !$this->hide_addons_active()) return $plugins;
			$updates = get_site_transient('update_plugins');
			$pending = (is_object($updates) && isset($updates->response) && is_array($updates->response)) ? $updates->response : array();
			foreach($this->get_addon_plugin_files($plugins) as $file){
				if(isset($pending[$file])) continue; //keep add-ons with available updates visible
				unset($plugins[$file]);
			}
			return $plugins;
		}catch(Exception $e){
			return $plugins;
		}
	}

	/**
	 * views_plugins filter: add a "Slider Revolution Add-ons (N)" link next to
	 * All/Active/Inactive that reveals the grouped add-ons.
	 * @return array adds the "Slider Revolution AddOns" filter link to the plugins screen
	 **/
	public function add_addon_plugins_view($views){
		try{
			if(!is_array($views) || !function_exists('get_plugins')) return $views;
			$addons = $this->get_addon_plugin_files(get_plugins());
			$count  = count($addons);
			if($count === 0 || $this->_truefalse($this->get_val($this->global_settings, 'groupAddons', true)) !== true) return $views;
			$current = !empty($_GET['sr7_show_addons']);
			$url = esc_url(add_query_arg('sr7_show_addons', '1', self_admin_url('plugins.php')));
			$views['sr7addons'] = '<a href="' . $url . '"' . ($current ? ' class="current" aria-current="page"' : '') . '>' . sprintf(__('Slider Revolution Add-ons %s', 'revslider'), '<span class="count">(' . $count . ')</span>') . '</a>';
			return $views;
		}catch(Exception $e){
			return $views;
		}
	}

	/**
	 * plugin_row_meta filter: note on the main Slider Revolution row how many
	 * add-ons are grouped, with a reveal link.
	 * @return array
	 **/
	public function add_addon_row_meta($meta, $file){
		try{
			if($file !== RS_PLUGIN_SLUG_PATH || !is_array($meta)) return $meta;
			if(!$this->hide_addons_active() || !function_exists('get_plugins')) return $meta;
			$count = count($this->get_addon_plugin_files(get_plugins()));
			if($count === 0) return $meta;
			$url = esc_url(add_query_arg('sr7_show_addons', '1', self_admin_url('plugins.php')));
			$meta[] = sprintf(__('%1$s add-ons grouped &ndash; %2$s', 'revslider'), $count, '<a href="' . $url . '">' . __('show in list', 'revslider') . '</a>');
			return $meta;
		}catch(Exception $e){
			return $meta;
		}
	}

	/**
	 * RTL assets are skipped when the "Use Editor in LTR Mode" global setting is active,
	 * so an RTL admin can run the SR7 backend in its default LTR layout.
	 * @return bool load the right-to-left stylesheets for the editor
	 **/
	public function use_rtl_assets(){
		return is_rtl() && $this->_truefalse($this->get_val($this->global_settings, 'editorForceLTR', false)) !== true;
	}

	/** @return void */
	public function enqueue_admin_styles(){
		wp_enqueue_style('revslider-base-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/base.css', [], $this->asset_time('admin/assets/css/base.css'));
		wp_enqueue_style('revslider-editor-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/editor.css', [], $this->asset_time('admin/assets/css/editor.css'));
		wp_enqueue_style('revslider-timeline-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/timeline.css', [], $this->asset_time('admin/assets/css/timeline.css'));
		wp_enqueue_style('revslider-preset-browser-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/preset-browser.css', [], $this->asset_time('admin/assets/css/preset-browser.css'));
		wp_enqueue_style('revslider-library-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/library.css', [], $this->asset_time('admin/assets/css/library.css'));
		wp_enqueue_style('revslider-toolbars-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/forms.css', [], $this->asset_time('admin/assets/css/forms.css'));
		wp_enqueue_style('revslider-colorpicker-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/colorpicker.css', [], $this->asset_time('admin/assets/css/colorpicker.css'));
		if(is_rtl()){
			wp_enqueue_style('revslider-base-rtl-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/base-rtl.css', [], $this->asset_time('admin/assets/css/base-rtl.css'));
			wp_enqueue_style('revslider-editor-rtl-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/editor-rtl.css', [], $this->asset_time('admin/assets/css/editor-rtl.css'));
			wp_enqueue_style('revslider-library-rtl-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/library-rtl.css', [], $this->asset_time('admin/assets/css/library-rtl.css'));
			wp_enqueue_style('revslider-forms-rtl-css', RS_PLUGIN_URL_CLEAN . 'admin/assets/css/forms-rtl.css', [], $this->asset_time('admin/assets/css/forms-rtl.css'));
		}
	}

	/** @return void */
	public function enqueue_admin_scripts(){
		$view = $this->get_val($_GET, 'view');
		//cache-bust admin scripts on every load while developing
		$rs_script_ver = (defined('WP_DEBUG') && WP_DEBUG) ? RS_REVISION . '-' . time() : RS_REVISION;

		if(empty($view)){
			wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], $this->asset_time('admin/assets/js/tools/tools.js'), false);
			$page = $this->get_val($_GET, 'page');
			if('revslider' === $page) wp_enqueue_script('revbuilder-dashboard', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/dashboard.js', [], $this->asset_time('admin/assets/js/dashboard.js'), false);
		}
		if('markups' === $view){
			wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], $this->asset_time('admin/assets/js/tools/tools.js'), false);
			wp_enqueue_script('revbuilder-colorpicker', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/colorpicker.js', [], $this->asset_time('admin/assets/js/tools/colorpicker.js'), false);
		}
		if(in_array($view, ['editor', 'slide'])){
			wp_enqueue_script('sr7', RS_PLUGIN_URL_CLEAN . 'public/js/sr7.js', [], $this->asset_time('public/js/sr7.js'), false);
			wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], $this->asset_time('admin/assets/js/tools/tools.js'), false);
			wp_enqueue_script('revbuilder-editor', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/editor/editor.js', [], $this->asset_time('admin/assets/js/editor/editor.js'), false);
		}
	}

	/**
	 * include/display the previously set page
	 * only allow certain pages to be showed
	 * @return void includes the view for the current editor page
	 **/
	public function display_admin_page(){
		try{
			if(!in_array($this->view, $this->allowed_views)) $this->throw_error(__('Bad Request', 'revslider'));

			switch($this->view){ //switch URLs to corresponding php files
				case 'markups':
					$view = 'markups';
				break;
				case 'slide':
				case 'editor':
					$view = '/editor/editor';
				break;
				case 'sliders':
				default:
					$view = 'dashboard';
				break;
			}

			$this->validate_filepath($this->path_views . $view . '.php', 'View');

			require($this->path_views . 'header.php');
			require($this->path_views . $view . '.php');
			require($this->path_views . 'footer.php');

		}catch(Exception $e){
			$this->show_error($this->view, $e->getMessage());
		}
	}

	/**
	 * set the page that should be shown
	 * @return void
	 **/
	private function set_current_page(){
		$this->view = $this->get_get_var('view', 'sliders');
	}

	/**
	 * set the user role, to restrict plugin usage to certain groups
	 * @return void resolves the configured role into the capability every admin check uses
	 **/
	public function set_user_role(){
		$this->user_role = $this->get_val($this->global_settings, 'permission', 'administrator');
		if($this->user_role === 'admin') $this->user_role = 'administrator';
		if(!in_array($this->user_role, ['author', 'editor', 'administrator'])) $this->user_role = 'administrator';
		
		switch($this->user_role){
			case 'author':
				$this->user_role = 'edit_published_posts';
			break;
			case 'editor':
				$this->user_role = 'edit_pages';
			break;
			default:
			case 'admin':
			case 'administrator':
				$this->user_role = 'manage_options';
			break;
		}
	}

	/**
	 * check if we need to search for updates, if yes. Do them
	 * @return void
	 **/
	private function do_update_checks(){
		/* @var RevSliderObjectLibrary $library */
		$library	= RevSliderGlobals::instance()->get('RevSliderObjectLibrary');
		$library->_get_list( isset($_REQUEST['update_object_library']) );
		
		$template	= new RevSliderTemplate();
		$template->_get_template_list( isset($_REQUEST['update_shop']) );

		$upgrade = new RevSliderUpdate(RS_REVISION);
		$upgrade->force = in_array($this->get_val($_REQUEST, 'checkforupdates', 'false'), ['true', true], true);
		$upgrade->_retrieve_version_info();
		$upgrade->add_update_checks();
	}
		
	/**
	 * enqueue all admin scripts
	 * @return void assets needed on every admin page, not just ours
	 **/
	public function enqueue_admin_scripts_global(){
		global $pagenow;
		if(!in_array($this->get_val($_GET, 'page'), $this->pages) && !$this->is_edit_page() && (!isset($pagenow) || $pagenow !== 'plugins.php')) return;

		wp_enqueue_script(['updates']);

		//include all media upload scripts
		$this->add_media_upload_includes();

		global $wp_scripts;
		$view = $this->get_val($_GET, 'view');
		
		/**
		 * dequeue tp-tools to make sure that always the latest is loaded
		 **/
		if(version_compare($this->get_val($wp_scripts, ['registered', 'tp-tools', 'ver'], '1.0'), RS_TP_TOOLS, '<')){
			wp_deregister_script('tp-tools');
			wp_dequeue_script('tp-tools');
		}

		if(in_array($view, ['', 'markups','slide','editor']) && ($this->get_val($_GET, 'page') === 'revslider' || $this->get_val($_GET, 'page') === 'revslider6')){ //overview page
			wp_enqueue_script('_tpt', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tptools.js', [], $this->asset_time('public/js/libs/tptools.js'));
		}
		
		wp_localize_script('revbuilder-backend', 'SRLANG = {}; window.SR7 ??= {}; SR7.LANG', $this->get_javascript_multilanguage()); //Load multilanguage for JavaScript

		if ( ! wp_script_is('revbuilder-admin', 'registered') ) {
			wp_register_script('revbuilder-admin', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/compatibility.js', [], $this->asset_time('admin/assets/js/compatibility.js'));
			wp_enqueue_script('revbuilder-admin');
		}
	}
	
	
	/**
	 * add all js and css needed for media upload
	 * @return void
	 */
	protected static function add_media_upload_includes(){
		if(function_exists('wp_enqueue_media')) wp_enqueue_media();

		wp_enqueue_script('thickbox');
		wp_enqueue_script('media-upload');
		wp_enqueue_style('thickbox');
	}
	
	/**
	 * Load the plugin text domain for translation.
	 * @return void
	 */
	public function load_plugin_textdomain(){
		$desired_locale = $this->get_val($this->global_settings, 'lang', 'default');
		$desired_locale = (!$desired_locale || $desired_locale === 'default') ? get_locale() : $desired_locale;
		
		if(file_exists(RS_PLUGIN_PATH . 'languages/revslider-'.$desired_locale.'.mo')) load_textdomain('revslider', RS_PLUGIN_PATH . 'languages/revslider-'.$desired_locale.'.mo');
		if(file_exists(RS_PLUGIN_PATH . 'languages/revsliderhelp-'.$desired_locale.'.mo')) load_textdomain('revsliderhelp', dirname(RS_PLUGIN_SLUG_PATH) . '/languages/revsliderhelp-'.$desired_locale.'.mo');
	}

	

	/**
	 * return the user role
	 * @return string the capability required to use the plugin
	 **/
	public function get_user_role(){
		return $this->user_role;
	}

	/**
	 * add the admin pages to the WordPress backend
	 * @since: 6.0
	 * @return void
	 **/
	public function add_admin_pages(){
		//$this->screens[] = add_menu_page('Slider Revolution', 'Slider Revolution', $this->user_role, 'revslider', [$this, 'display_admin_page'], 'dashicons-update');

		$tp_premium = $this->_truefalse($this->get_options(['system', 'valid'], 'false'));
		$tp_ticket = ($tp_premium !== true) ? ' class="revslider_premium"' : '';

		//v7 screens
		$this->screens[] = add_menu_page('Slider Revolution', 'Slider Revolution', $this->user_role, 'revslider', null, 'dashicons-update');
		$this->screens[] = add_submenu_page('revslider', __('Slider Revolution - Overview', 'revslider'), __('Plugin Dashboard', 'revslider'), $this->user_role, 'revslider', [$this, 'display_admin_page']);		
		$this->screens[] = add_submenu_page('revslider', '', __('<div id="revslider_manual_link">Getting Started</div>', 'revslider'), $this->user_role, 'revslider-documentation', [$this, 'display_external_redirects']);
		$this->screens[] = add_submenu_page('revslider', '', __('<div id="revslider_helpcenter_link">Help Center</div>', 'revslider'), $this->user_role, 'revslider-help-center', [$this, 'display_external_redirects']);
		$this->screens[] = add_submenu_page('revslider', '', __('<div id="revslider_templates_link">Templates</div>', 'revslider'), $this->user_role, 'revslider-templates', [$this, 'display_external_redirects']);
		$this->screens[] = add_submenu_page('revslider', '', __('<div id="revslider_ticket_link"'. $tp_ticket .'>Premium Support</div>', 'revslider'), $this->user_role, 'revslider-ticket', [$this, 'display_external_redirects']);
		
		if($tp_premium !== true){
			$this->screens[] = add_submenu_page('revslider', '', '<div id="revslider_premium_link"><span class="dashicons dashicons-star-filled" style="font-size: 17px"></span> '.__('Go Premium', 'revslider')."</div>", $this->user_role, 'revslider-buy-license', [$this, 'display_external_redirects']);
		}
	}

	/**
 	 * opens the external sliderrevolution.com menu URLs in a blank tab
 	 * @since 6.5.11
 	 */
	  public function add_js_menu_open_blank() {
		echo '<script>
				document.addEventListener("DOMContentLoaded", function() {
					const selectors = ["#revslider_manual_link", "#revslider_helpcenter_link", "#revslider_templates_link", "#revslider_ticket_link", "#revslider_premium_link"];

					selectors.forEach(function(sel) {
						const el = document.querySelector(sel);
						if (el && el.parentElement) {
						el.parentElement.setAttribute("target", "_blank");
						}
					});
				});
			</script>';
	}

	/**
	 * redirect to external URLs
	 * @since 6.5.10
	 * @return void redirects the pseudo menu entries (docs, help center, …) to their URLs
	 */
	public function display_external_redirects() {
		$page = $this->get_val($_GET, 'page');
		if(empty($page)) return;

		$tp_premium = $this->get_options(['system', 'valid'], 'false');

		switch($page){
			case 'revslider-buy-license':
				wp_redirect('https://account.sliderrevolution.com/portal/premium-slider-revolution/?utm_source=admin&utm_medium=menu&utm_campaign=srusers&utm_content=gopremium');
				exit;
			break;
			case 'revslider-documentation':
				wp_redirect('https://www.sliderrevolution.com/editor-tour/?utm_source=admin&utm_medium=menu&utm_campaign=srusers&utm_content=usedocumentation&premium='.$tp_premium);
				exit;
			break;
			case 'revslider-help-center':
				wp_redirect('https://www.sliderrevolution.com/help-center?utm_source=admin&utm_medium=menu&utm_campaign=srusers&utm_content=help&premium='.$tp_premium);
				exit;
			break;
			case 'revslider-templates':
				wp_redirect('https://www.sliderrevolution.com/examples?utm_source=admin&utm_medium=menu&utm_campaign=srusers&utm_content=templates&premium='.$tp_premium);
				exit;
			break;
			case 'revslider-ticket':
				wp_redirect('https://support.sliderrevolution.com?utm_source=admin&utm_medium=menu&utm_campaign=srusers&utm_content=support&premium='.$tp_premium);
				exit;
			break;
			default:
		}
	}



	/**
	 * we dont want to show notices in our plugin
	 * @return void removes foreign admin notices from our own screens
	 **/
	public function hide_notices(){
		if(in_array($this->get_val($_GET, 'page'), $this->pages)){
			remove_all_actions('admin_notices');
		}
	}

	

	/**
	 * Add Classes to the WordPress body
	 * @since    6.0
	 * @param string $classes
	 * @return string
	 */
	/*function modify_admin_body_class($classes){
		$classes .= ($this->get_val($_GET, 'page') == 'revslider' && $this->get_val($_GET, 'view') == 'slide') ? ' rs-builder-mode' : '';
		$classes .= ($this->_truefalse($this->get_val($this->global_settings, 'highContrast', false)) === true && $this->get_val($_GET, 'page') === 'revslider') ? ' rs-high-contrast' : '';
		
		return $classes;
	}*/
	
	/**
	 * Change the language of the Slider Backend even if WordPress is set to be a different language
	 * @since: 6.1.6
	 * @return mixed swaps the translation for a string when the editor language differs from the site
	 **/
	public function change_lang($locale, $domain = ''){
		return (in_array($domain, ['revslider', 'revsliderhelp'], true)) ? $this->get_val($this->global_settings, 'lang', 'default') : $locale;
	}

	/**
	 * merge the revslider addon notices into one bigger notice
	 * @since: 2.2.0
	 * @return void
	 **/
	public function merge_addon_notices(){
		global $wp_filter;
		
		if(!isset($wp_filter['admin_notices'])) return;
		if(!isset($wp_filter['admin_notices']->callbacks)) return;
		
		global $SR_GLOBALS;
		$slugs = [
			'Revslider_404_Addon_Verify', 'RsAddOnBackupNotice', 'RsAddOnBeforeAfterNotice', 'RsAddOnBubblemorphNotice', 'Revslider_Domain_Switch_Addon_Verify',
			'RsAddOnDuotoneNotice', 'RsAddOnExplodinglayersNotice', 'Revslider_Featured_Addon_Verify', 'RsAddOnFilmstripNotice', 'Revslider_Gallery_Addon_Verify',
			'RsAddOnLiquideffectNotice', 'Revslider_Login_Addon_Verify', 'Revslider_Maintenance_Addon_Verify', 'RsAddOnMousetrapNotice', 'RsAddOnPaintbrushNotice',
			'RsAddOnPanoramaNotice', 'RsAddOnParticlesNotice', 'RsAddOnPolyfoldNotice', 'Revslider_Prev_Next_Addon_Verify', 'RsAddOnRefreshNotice',
			'Revslider_Related_Posts_Addon_Verify', 'RsAddOnRevealerNotice', 'RsAddOnShapebuilderNotice', 'Revslider_Sharing_Addon_Verify', 'RsAddOnSliceyNotice',
			'RsAddOnSnowNotice', 'RsAddOnSunbeamNotice', 'RsAddOnTypewriterNotice', 'Revslider_Weather_Addon_Verify', 'Revslider_Whiteboard_Addon_Verify'
		];
	
		foreach($wp_filter['admin_notices']->callbacks as $k => $o){
			if(!empty($o)){
				foreach($o as $ok => $f){
					if(!isset($f['function'])) continue;
					if(!is_array($f['function'])) continue;
					if(!isset($f['function'][0])) continue;
					if(!is_object($f['function'][0])) continue;
					
					
					$class = get_class($f['function'][0]);
					if(in_array($class, $slugs, true)){
						unset($wp_filter['admin_notices']->callbacks[$k][$ok]);
						$SR_GLOBALS['addon_notice_merged']++;
					}
				}
			}
		}

		//if($SR_GLOBALS['addon_notice_merged'] > 0) add_action('admin_notices', [$this, 'add_addon_plugins_page_notices']);
	}
	
	/**
	 * add addon merged notices
	 * @since: 6.2.0
	 **/
	/*public function add_addon_plugins_page_notices(){
		?>
		<div class="error below-h2 soc-notice-wrap revaddon-notice" style="display: none;">
			<p><?php echo __('Action required for Slider Revolution AddOns: Please <a href="https://www.sliderrevolution.com/manual-section/manual/getting-started/quick-setup/" target="_blank" rel="noopener">install</a>/<a href="https://www.sliderrevolution.com/manual-section/manual/getting-started/quick-setup/register-plugin/" target="_blank" rel="noopener">activate</a>/<a href="https://www.sliderrevolution.com/manual-section/manual/getting-started/quick-setup/update-plugin/" target="_blank" rel="noopener">update</a> Slider Revolution</a>', 'revslider'); ?><span data-addon="rs-addon-notice" data-noticeid="rs-addon-merged-notices" style="float: right; cursor: pointer" class="revaddon-dismiss-notice dashicons dashicons-dismiss"></span></p>
		</div>
		<?php
	}*/

	/**
	 * add plugin notices to the Slider Revolution Plugin at the overview page of plugins
	 * @return void
	 **/
	public static function add_plugins_page_notices(){
		$f		 = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$plugins = get_plugins();

		foreach($plugins ?? [] as $plugin_id => $plugin){
			$slug = dirname($plugin_id);
			if(empty($slug) || $slug !== 'revslider') continue;
			
			if($f->_truefalse($f->get_options(['system', 'valid'], 'false')) === false && version_compare($f->get_options(['system', 'version'], RS_REVISION), $plugin['Version'], '>')){
				add_action('after_plugin_row_' . $plugin_id, ['RevSliderAdmin', 'show_purchase_notice'], 10, 3);
				add_action('admin_footer', ['RevSliderAdmin', 'add_ajax_footer_functionality']);
			}

			break;
		}
	}

	/**
	 * Show message for activation benefits
	 * @return void
	 **/
	public static function show_purchase_notice($plugin_file, $plugin_data, $plugin_status){
		$f					= RevSliderGlobals::instance()->get('RevSliderFunctions');
		$wp_list_table		= _get_list_table( 'WP_Plugins_List_Table' );
		$rs_latest_version	= $f->get_options(['system', 'version'], RS_REVISION);
		$revision			= str_replace('.', '-', $rs_latest_version);
		?>
		<tr class="plugin-update-tr active">
            <td colspan="<?php echo $wp_list_table->get_column_count(); ?>" class="plugin-update colspanchange">
                <div class="update-message notice inline notice-warning notice-alt">
				<p><?php _e('There is a new version (<a href="https://www.sliderrevolution.com/documentation/changelog/?utm_source=admin&utm_medium=wpplugins&utm_campaign=srusers&utm_content=updateinfo#'.$revision.'" target="_blank">'.$rs_latest_version.'</a>) of Slider Revolution available. To update directly <a href="javascript:;" onclick="SR7.B.showRegisterSliderInfo();">register your license key now</a> or <a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=wpplugins&utm_campaign=srusers&utm_content=updateinfo" target="_blank">purchase a new license key</a> to access <a href="https://www.sliderrevolution.com/premium-slider-revolution/?utm_source=admin&utm_medium=wpplugins&utm_campaign=srusers&utm_content=updateinfo" target="_blank">all premium features</a>.', 'revslider'); ?></p>
                </div>
			</td>
        </tr>
		<style>tr[data-slug="slider-revolution"] td, tr[data-slug="slider-revolution"] th { box-shadow: none!important} #revslider-update{display: none;}</style>
		<?php
	}
	
	/**
	 * add a go premium button to the plugins page for Slider Revolution
	 * @return array
	 **/
	public function add_plugin_action_links($links){
		$links['go_premium'] = '<a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=buykey" target="_blank" style="color: #F7345E; font-weight: 700;">'.__('Go Premium', 'revslider').'</a>';

		return $links;
	}

	/**
	 * Add the suggested privacy policy text to the policy postbox.
	 * @return void registers our text for WordPress' privacy policy guide
	 */
	public function add_suggested_privacy_content() {
		if(function_exists('wp_add_privacy_policy_content')){
			$content = $this->get_default_privacy_content();
			wp_add_privacy_policy_content(__('Slider Revolution'), $content);
		}
	}
	
	/**
	 * Return the default suggested privacy policy content.
	 *
	 * @return string The default policy content.
	 */
	public function get_default_privacy_content(){
		return __('<h2>In case you’re using Google Web Fonts (default) or playing videos or sounds via YouTube or Vimeo in Slider Revolution we recommend to add the corresponding text phrase to your privacy police:</h2>
		<h3>YouTube</h3> <p>Our website uses plugins from YouTube, which is operated by Google. The operator of the pages is YouTube LLC, 901 Cherry Ave., San Bruno, CA 94066, USA.</p> <p>If you visit one of our pages featuring a YouTube plugin, a connection to the YouTube servers is established. Here the YouTube server is informed about which of our pages you have visited.</p> <p>If you\'re logged in to your YouTube account, YouTube allows you to associate your browsing behavior directly with your personal profile. You can prevent this by logging out of your YouTube account.</p> <p>YouTube is used to help make our website appealing. This constitutes a justified interest pursuant to Art. 6 (1) (f) DSGVO.</p> <p>Further information about handling user data, can be found in the data protection declaration of YouTube under <a href="https://www.google.de/intl/de/policies/privacy" target="_blank" rel="noopener">https://www.google.de/intl/de/policies/privacy</a>.</p>
		<h3>Vimeo</h3> <p>Our website uses features provided by the Vimeo video portal. This service is provided by Vimeo Inc., 555 West 18th Street, New York, New York 10011, USA.</p> <p>If you visit one of our pages featuring a Vimeo plugin, a connection to the Vimeo servers is established. Here the Vimeo server is informed about which of our pages you have visited. In addition, Vimeo will receive your IP address. This also applies if you are not logged in to Vimeo when you visit our plugin or do not have a Vimeo account. The information is transmitted to a Vimeo server in the US, where it is stored.</p> <p>If you are logged in to your Vimeo account, Vimeo allows you to associate your browsing behavior directly with your personal profile. You can prevent this by logging out of your Vimeo account.</p> <p>For more information on how to handle user data, please refer to the Vimeo Privacy Policy at <a href="https://vimeo.com/privacy" target="_blank" rel="noopener">https://vimeo.com/privacy</a>.</p>
		<h3>Google Web Fonts</h3> <p>For uniform representation of fonts, this page uses web fonts provided by Google. When you open a page, your browser loads the required web fonts into your browser cache to display texts and fonts correctly.</p> <p>For this purpose your browser has to establish a direct connection to Google servers. Google thus becomes aware that our web page was accessed via your IP address. The use of Google Web fonts is done in the interest of a uniform and attractive presentation of our plugin. This constitutes a justified interest pursuant to Art. 6 (1) (f) DSGVO.</p> <p>If your browser does not support web fonts, a standard font is used by your computer.</p> <p>Further information about handling user data, can be found at <a href="https://developers.google.com/fonts/faq" target="_blank" rel="noopener">https://developers.google.com/fonts/faq</a> and in Google\'s privacy policy at <a href="https://www.google.com/policies/privacy/" target="_blank" rel="noopener">https://www.google.com/policies/privacy/</a>.</p>
		<h3>SoundCloud</h3><p>On our pages, plugins of the SoundCloud social network (SoundCloud Limited, Berners House, 47-48 Berners Street, London W1T 3NF, UK) may be integrated. The SoundCloud plugins can be recognized by the SoundCloud logo on our site.</p>
			<p>When you visit our site, a direct connection between your browser and the SoundCloud server is established via the plugin. This enables SoundCloud to receive information that you have visited our site from your IP address. If you click on the “Like” or “Share” buttons while you are logged into your SoundCloud account, you can link the content of our pages to your SoundCloud profile. This means that SoundCloud can associate visits to our pages with your user account. We would like to point out that, as the provider of these pages, we have no knowledge of the content of the data transmitted or how it will be used by SoundCloud. For more information on SoundCloud’s privacy policy, please go to https://soundcloud.com/pages/privacy.</p><p>If you do not want SoundCloud to associate your visit to our site with your SoundCloud account, please log out of your SoundCloud account.</p>', 'revslider');
	}

	
	/**
	 * echo json ajax response as error
	 * @return void
	 */
	public function ajax_response_error($message, $data = null){
		$this->ajax_response(false, $message, $data);
	}

	/**
	 * echo ajax success response with redirect instructions
	 * @return void
	 */
	public function ajax_response_redirect($message, $url){
		$data = ['is_redirect' => true, 'redirect_url' => $url];

		$this->ajax_response(true, $message, $data);
	}

	/**
	 * echo json ajax response, without message, only data
	 * @return void
	 */
	public function ajax_response_data($data){
		$data = (gettype($data) == 'string') ? ['data' => $data] : $data;

		$this->ajax_response(true, '', $data);
	}

	/**
	 * echo ajax success response
	 * @return void
	 */
	public function ajax_response_success($message, $data = null){
		$this->ajax_response(true, $message, $data);
	}

	/**
	 * echo json ajax response
	 * @return void echoes the JSON body and ends the request
	 */
	private function ajax_response($success, $message, $data = null){

		$response = [
			'success' => $success,
			'message' => $message,
		];

		if(!empty($data)){
			if(gettype($data) == 'string') $data = ['data' => $data];
			$response = array_merge($response, $data);
		}

		echo json_encode($response);

		wp_die();
	}

	/**
	 * show an nice designed error
	 * @return void
	 **/
	public function show_error($view, $message){
		echo '<div class="rs-error">';
		echo __('Slider Revolution encountered the following error: ', 'revslider');
		echo esc_attr($view);
		echo ' - Error: <span>';
		echo esc_attr($message);
		echo '</span>';
		echo '</div>';
		exit;
	}
	
	
	/**
	 * validate that some file exists, if not - throw error
	 * @return bool
	 */
	public function validate_filepath($filepath, $prefix = null){
		if( file_exists( $filepath ) ) return true;
		
		$prefix	 = ($prefix == null) ? 'File' : $prefix;
		$message = $prefix.' '.esc_attr($filepath).' not exists!';
		
		$this->throw_error($message);
	}
	
	
	/**
	 * esc attr recursive
	 * @since: 6.0
	 * @return mixed
	 */
	public static function esc_js_deep($value){
		return is_array($value) ? array_map(['RevSliderAdmin', 'esc_js_deep'], $value) : esc_js($value);
	}
}
