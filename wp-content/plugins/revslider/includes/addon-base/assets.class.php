<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * AddOn script + resource delivery. Builds the filetime cache busted SR7.E.resources list,
 * prints it for the editor/front, queues fonts, and bundles the AddOn scripts into HTML exports.
 * Delivery is resource manager based; only tag detected AddOns (sharing) print an eager tag.
 **/
/**
 * Asset handling for one AddOn: registers its editor and front end scripts/styles with the core, so an
 * AddOn does not enqueue anything itself. What ends up on the page depends on whether a slider on that
 * page actually uses the AddOn.
 */
class RevSliderAddonAssets extends RevSliderFunctions {

	private $base;
	private $slug;
	private $title;
	private $path;
	private $url;
	private $script_files	= [];		//handle => file relative to path
	private $eager			= false;	//print the script tag always when in use (only for addons like sharing which get detected by their tag)
	private $base_url_entry	= false;	//extra SR7.E.resources entry holding the plugin url (shader/asset folders like hmURL)
	private $fonts			= [];		//font names queued into $SR_GLOBALS when the addon is in use
	private $script_enqueued = false;

	public function __construct($base, $args){
		$this->base			= $base;
		$this->slug			= $base->slug;
		$this->title		= $base->title;
		$this->path			= $base->path;
		$this->url			= $base->url;
		$this->script_files	= $this->get_val($args, 'scripts', [$this->title => 'public/js/'.$this->title.'.js']);
		$this->eager		= $this->get_val($args, 'eager', false);
		$this->base_url_entry = $this->get_val($args, 'base_url_entry', false);
		$this->fonts		= $this->get_val($args, 'fonts', []);

		//AddOns that ship their own front class (dual min/unmin or extra css sets - lottie/depthforge/weather) pass scripts=>false to own asset delivery + export themselves; core stays out so the same scripts/resources/export are not emitted twice
		if($this->script_files === false){
			$this->script_files = [];
			return;
		}

		add_filter('revslider_add_slider_base', [$this, 'enqueue_header_scripts']);
		add_filter('sr_get_full_slider_JSON', [$this, 'add_modal_scripts'], 10, 2);
		add_filter('revslider_export_html_file_inclusion', [$this, 'add_addon_files'], 10, 2);
		add_filter('sr_get_addon_data', [$this, 'add_addon_data'], 10, 3);

		if(is_admin() && $this->get_val($_GET, 'page') === 'revslider') add_action('admin_footer', [$this, 'add_header_scripts_return']);
	}

	/**
	 * Script urls with filetime cache busting, so dev uploads and releases both bust browser caches
	 * @return array
	 **/
	public function get_script_list(){
		$list = [];
		foreach($this->script_files ?? [] as $handle => $file){
			if(!file_exists($this->path.$file)){
				$min = str_replace('.js', '.min.js', $file);
				if(!file_exists($this->path.$min)) continue;
				$file = $min;
			}
			$list[$handle] = $this->url.$file.'?ver='.filemtime($this->path.$file);
		}
		return $list;
	}

	/**
	 * Scripts plus the optional bare Directory Url Entry (Shader/Asset Folders like hmURL) for all SR7.E.resources Delivery Paths
	 * @return array
	 **/
	public function get_resource_list(){
		$list = $this->get_script_list();
		if($this->base_url_entry !== false) $list[$this->base_url_entry] = $this->url;
		return $list;
	}

	/** @return array */
	public function add_modal_scripts($obj, $slider){
		if(!$this->base->is_in_use($slider)) return $obj;
		foreach($this->get_resource_list() ?? [] as $handle => $script){
			$obj['addOns'][$handle] = $script;
		}
		return $obj;
	}

	/** @return RevSliderSlider the slider, unchanged - this is a filter used for its side effect */
	public function enqueue_header_scripts($slider){
		if($this->script_enqueued) return $slider;
		if(empty($slider)) return $slider;
		if(!$this->base->is_in_use($slider)) return $slider;

		$this->script_enqueued = true;
		add_action('revslider_pre_add_js', [$this, 'add_header_scripts']);

		if(!empty($this->fonts)){
			global $SR_GLOBALS;
			foreach($this->fonts as $font) $SR_GLOBALS['fonts']['queue'][$font] = true;
		}

		//Resource manager loads the files on demand. Only tag detected AddOns (sharing) still print an eager tag
		if($this->eager){
			$list = $this->get_script_list();
			$main = $this->get_val($list, $this->title, reset($list));
			if(!empty($main)) wp_enqueue_script($this->slug, $main, [], null, ['strategy' => 'async']);
		}

		return $slider;
	}

	/** @return void */
	public function add_header_scripts($script = ''){
		echo $this->add_header_scripts_return(false);
	}

	/** @return string */
	public function add_header_scripts_return($tags = ''){
		if($tags !== false){
			if($this->script_enqueued) return;
			$this->script_enqueued = true;
		}

		$list = $this->get_resource_list();
		if(empty($list)) return '';

		$tab	= ($tags !== false) ? '' : '	';
		$nl		= (count($list) > 1 || $tags === false) ? "\n" : '';
		$html	= ($tags !== false) ? '<script>'.$nl : '';
		foreach($list ?? [] as $handle => $script){
			$html .= $tab.'SR7.E.resources.'.$handle.' = "'.$script.'";'.$nl;
		}
		$html	.= ($tags !== false) ? '</script>'."\n" : '';

		if($tags === false) return $html;

		echo $html;
	}

	/** @return array */
	public function add_addon_data($data, $handle, $check_handle = true){
		if($check_handle && $handle !== $this->slug) return $data;

		$resources = $this->get_resource_list();
		if(empty($data)) return $resources;

		$data->resources = $resources;

		return $data;
	}

	/** @return string */
	public function add_addon_files($html, $export){
		$output = $export->slider_output;
		if(!$this->base->is_in_use($output->slider)) return $html;

		foreach($this->get_script_list() ?? [] as $handle => $script){
			$script = explode('?', $script)[0];
			$ext = pathinfo($script, PATHINFO_EXTENSION) ?: 'js';	//derive folder/extension per handle so a registered css (public/css) exports as css, not forced into public/js as .js
			$export_path = $this->slug.'/public/'.$ext.'/';
			$target = $export_path.$handle.'.'.$ext;
			$script_path = str_replace($this->url, $this->path, $script);
			if(!$export->usepcl){
				$export->zip->addFile($script_path, $target);
			}else{
				$export->pclzip->add($script_path, PCLZIP_OPT_REMOVE_PATH, $this->path.'public/'.$ext.'/', PCLZIP_OPT_ADD_PATH, $export_path);
			}
			$html = str_replace([$script, str_replace('/', '\/', $script)], [$target, str_replace('/', '\/', $target)], $html);
		}

		return $html;
	}
}
