<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

require_once(dirname(__FILE__).'/addon-base/notices.class.php');
require_once(dirname(__FILE__).'/addon-base/updates.class.php');
require_once(dirname(__FILE__).'/addon-base/assets.class.php');
require_once(dirname(__FILE__).'/addon-base/media.class.php');
require_once(dirname(__FILE__).'/addon-base/templates.class.php');
require_once(dirname(__FILE__).'/addon-base/ajax.class.php');

/**
 * Shared base for all SR7 AddOns (Harmonization Contract C9) - the AddOns Manager.
 * Its job is to register and validate one AddOn, then orchestrate the single shared services
 * (updates, assets, media, ajax, notices) instead of every AddOn copying a Base/SliderFront/Update trio.
 *
 * Registration/validation, in use detection and the panel/modal maps live here; everything else is
 * delegated to includes/addon-base/*.class.php so each concern is one Single Responsibility class.
 *
 * AddOns call RevSliderAddonBase::register([...]) on plugins_loaded and keep their old
 * bundled Base only as Fallback for Cores without this class.
 **/
class RevSliderAddonBase extends RevSliderFunctions {

	private static $addons = [];
	private static $last_error = '';	//notice code of the last failed register() ('add_notice_version' | 'add_notice_activation'), so the AddOn can show the matching notice

	public $slug;		//revslider-snow-addon
	public $title;		//snow
	public $path;		//absolute plugin path, trailing slash
	public $url;		//plugin url, trailing slash
	public $version;

	private $modals	= [];	//suboperation => file relative to path, validated on registration
	private $panels	= [];

	private $updates;		//RevSliderAddonUpdates
	private $assets;		//RevSliderAddonAssets
	private $media;			//RevSliderAddonMedia
	private $ajax;			//RevSliderAddonAjax

	/**
	 * Register an AddOn with the core Base. Returns the instance, or false if Requirements fail (AddOn falls back to its own Notice handling)
	 * @return RevSliderAddonBase|false the addon instance, false when registration failed
	 **/
	public static function register($args){
		self::$last_error = '';
		$slug = isset($args['slug']) ? $args['slug'] : '';
		if($slug === '') return false;
		if(isset(self::$addons[$slug])) return self::$addons[$slug];

		//same Gates the copied Base classes had: core version + license. On failure record the reason so the AddOn can show the matching notice (false return kept for back compat)
		$min_core = isset($args['min_core']) ? $args['min_core'] : false;
		if($min_core !== false && !version_compare(RevSliderGlobals::SLIDER_REVISION, $min_core, '>=')){
			self::$last_error = 'add_notice_version';
			return false;
		}

		//register() is static (no instance yet - the gate decides whether to build one), so reach the shared RevSliderFunctions singleton for the license check
		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		if($f->_truefalse($f->get_options(['system', 'valid'], 'false')) === false){
			self::$last_error = 'add_notice_activation';
			return false;
		}

		self::$addons[$slug] = new self($args);

		return self::$addons[$slug];
	}

	/** @return RevSliderAddonBase|false */
	public static function get_addon($slug){
		return isset(self::$addons[$slug]) ? self::$addons[$slug] : false;
	}

	/**
	 * notice code of the last failed register() ('' on success) - the AddOn reads it right after its own
	 * register() call to show the matching RevSliderAddonNotices
	 * @return string the reason the last register() call failed
	 */
	public static function get_register_error(){
		return self::$last_error;
	}

	private function __construct($args){
		$this->slug		= $args['slug'];
		$this->title	= $this->get_val($args, 'title', str_replace(['revslider-', '-addon'], '', $this->slug));
		$this->path		= trailingslashit($this->get_val($args, 'path'));
		$this->url		= trailingslashit($this->get_val($args, 'url'));
		$this->version	= $this->get_val($args, 'version', '1.0.0');
		$this->modals	= $this->validate_map($this->get_val($args, 'modals', []));
		$this->panels	= $this->validate_map($this->get_val($args, 'panels', []));

		//shared services - one Single Responsibility class each, configured from the same $args
		$this->updates	= new RevSliderAddonUpdates($this);
		$this->assets	= new RevSliderAddonAssets($this, $args);
		$this->media	= new RevSliderAddonMedia($this, $args);
		$this->ajax		= new RevSliderAddonAjax($this, $args);

		add_action('after_setup_theme', [$this, 'load_textdomain']);

		if(is_admin()){
			add_filter('revslider_modify_modal_map', [$this, 'check_modal'], 10, 2);
			add_filter('revslider_modify_panel_map', [$this, 'check_panel'], 10, 2);
			//NOTE: addon translation strings (generated i18n-strings.php + 'lang' hand-list) reach
			//SR7.LANG via the existing lazy path — get_lang() (addon-base/ajax.class.php) feeds both
			//revslider_api_get_addon_lang and the 'lang.get' ajax, which tptools merges with
			//SR7.LANG = _tpt.extend(SR7.LANG, data). No separate page-load hook needed here.
		}
	}

	/** @return void */
	public function load_textdomain(){
		load_plugin_textdomain('rs_'.$this->title, false, $this->path.'languages/');
	}

	/**
	 * Map entries must be namespaced with the AddOn title and point to existing files, everything else made the old shared path overwrite a latent fatal
	 * @return array the entries of $map whose files actually exist
	 **/
	private function validate_map($map){
		$valid = [];
		foreach($map ?? [] as $suboperation => $file){
			if(strpos($suboperation, $this->title) === false) continue;
			if(!file_exists($this->path.$file)) continue;
			$valid[$suboperation] = $file;
		}
		return $valid;
	}

	/** @return array */
	public function check_modal($panels, $suboperation){
		if(!isset($this->modals[$suboperation])) return $panels;
		$panels['path'] = $this->path;
		$panels['map'][$suboperation] = $this->modals[$suboperation];
		return $panels;
	}

	/** @return array */
	public function check_panel($panels, $suboperation){
		if(!isset($this->panels[$suboperation])) return $panels;
		$panels['path'] = $this->path;
		$panels['map'][$suboperation] = $this->panels[$suboperation];
		return $panels;
	}

	/**
	 * JSON aware in use Detection: explicit u flags first, then a real key walk for layer/slide level usage.
	 * Replaces the old strpos over the serialized Slider which false positive triggered on any text mentioning the AddOn.
	 * Shared authority used by the assets service for script delivery + html export.
	 * @return bool true when any slider actually uses this addon
	 **/
	public function is_in_use($slider){
		if(empty($slider)) return false;

		$u = $this->get_val($slider, ['settings', 'addOns', $this->title, 'u'], null);
		if($u === null) $u = $this->get_val($slider, ['params', 'addOns', $this->title, 'u'], null);
		if($u === true) return true;
		if($u === false) return false;

		//maybe some deeper element (slide/layer) uses the addon
		return $this->addon_key_exists(json_decode(json_encode($slider), true));
	}

	/** @return bool */
	private function addon_key_exists($node){
		if(!is_array($node)) return false;
		if(isset($node['addOns'][$this->title])) return true;
		foreach($node as $v){
			if(is_array($v) && $this->addon_key_exists($v)) return true;
		}
		return false;
	}
}
