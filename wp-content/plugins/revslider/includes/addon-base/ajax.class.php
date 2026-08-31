<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * AddOn ajax dispatch + lang delivery.
 * Routes revslider_do_ajax calls for this AddOn: the built in 'lang' get and 'template'
 * operations, plus the AddOn's own operations via the per-addon revslider_addon_<title>_ajax filter.
 **/
class RevSliderAddonAjax extends RevSliderFunctions {

	private $slug;
	private $title;
	private $path;		//absolute addon path (trailing slash) - to load the generated i18n-strings.php
	private $lang		= false;	//callable returning the addon lang array
	private $templates	= false;	//RevSliderAddonTemplates instance when the addon opts into template CRUD

	public function __construct($base, $args){
		$this->slug		= $base->slug;
		$this->title	= $base->title;
		$this->path		= $base->path;
		$this->lang		= $this->get_val($args, 'lang', false);

		if($this->get_val($args, 'templates', false) !== false) $this->templates = new RevSliderAddonTemplates($base);

		if(is_admin()){
			//revslider_do_ajax is consumed via apply_filters (it expects a return), so register it as a filter
			add_filter('revslider_do_ajax', [$this, 'do_ajax'], 10, 5);
			add_filter('revslider_api_get_addon_lang', [$this, 'get_addon_lang']);
		}
	}

	/** @return array the addon's JS translation strings, generated plus hand maintained */
	public function get_lang(){
		//generated JS strings (auto-extracted from SR7.t() calls) + the registered 'lang' hand-list
		//(dynamic keys / slug mappings). Hand-list merged last so it overrides generated.
		$lang = is_callable($this->lang) ? (array) call_user_func($this->lang) : [];
		$gen  = $this->path . 'admin/includes/i18n-strings.php';
		if(is_file($gen)) $lang = array_merge((array) include $gen, $lang);
		return $lang;
	}

	/** @return array */
	public function get_addon_lang($data){
		$data[$this->title] = $this->get_lang();
		return $data;
	}

	/** @return mixed the response for this addon's ajax operation, or $return when it is not ours */
	public function do_ajax($return = '', $action = '', $operation = '', $suboperation = '', $data = []){
		if($action !== $this->slug) return $return;

		if($operation === 'lang' && $suboperation === 'get') return ['data' => $this->get_lang()];
		if($operation === 'template' && $this->templates !== false) return $this->templates->handle_ajax($suboperation);

		//the addon owns its custom operations through a natural per-addon filter - scales as more ops are added, no growing callback
		return apply_filters('revslider_addon_'.$this->title.'_ajax', $return, $operation, $suboperation, $data);
	}
}
