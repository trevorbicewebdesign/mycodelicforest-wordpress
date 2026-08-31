<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Slider export into a .zip package.
 *
 * Collects the slider data as JSON plus every referenced image, video, animation and navigation skin, and
 * streams the archive to the browser. Attachment ids are stripped because they mean nothing on the target
 * site - only URLs are exported, and is_safe_export_path() makes sure nothing outside wp-content is packed.
 */
class RevSliderSliderExport extends RevSliderSlider {
	
	private $used_media			= [];
	private $used_navigations	= [];
	private $slider_id;
	private $slider_title;
	public  $slider_alias;
	private $slider_params;
	private $slider_settings;
	private $slider_slides		= [];
	private $export_data;
	private $navigation_data	= false;
	private $animations_data	= '';
	public  $usepcl				= false;
	public  $zip;
	public  $pclzip;
	
	public function __construct($title = 'export'){
		parent::__construct();
		$title					= preg_replace("/[^A-Za-z0-9 ]/", '', $title);
		$wp_upload_dir			= wp_upload_dir();

		$this->directories['single']	= $this->get_upload_path();
		$this->directories['url']		= $this->get_val($wp_upload_dir, 'baseurl');
		$this->directories['dir']		= $this->get_val($wp_upload_dir, 'basedir');
		$this->directories['zip_url']	= $this->get_val($wp_upload_dir, 'baseurl').'/'.$title.'.zip';
		$this->directories['zip_path']	= $this->get_val($wp_upload_dir, 'basedir').'/'.$title.'.zip';
	}
	
	/**
	 * return the used images, for SEO
	 * TODO: Used by external SEO plugins?
	 * @return array
	 */
	public function get_used_images(){
		return $this->used_media;
	}
	
	/**
	 * export slider from data, output a file for download
	 * @return void builds the zip, sends it to the browser and ends the request
	 */
	public function export_slider($id = 0){
		//slider needs to be initialized :)
		if($id > 0) $this->init_by_id($id);
		
		$slider = $this;
		$upd	= new RevSliderPluginUpdate();
		if(version_compare($slider->get_param(['settings', 'version']), $this->get_options(['update', 'latest-version'], '6.0.0'), '<')){
			$slider_id = $slider->get_id();
			$upd->upgrade_slider_to_latest($slider);
			$slider->init_by_id($slider_id);
		}
		
		$slider = apply_filters('revslider_export_slider_update', $slider, $id);
		$slider->set_parameters();
		$slider->remove_image_ids();	
		$slider->add_used_media();
		$slider->add_used_navigations();	
		$slider->modify_used_data();	
		$slider->serialize_export_data();
		$slider->serialize_navigation_data();
		
		$slider->create_export_zip();
		$slider->add_images_videos_to_zip();
		$slider->add_slider_export_to_zip();
		$slider->add_animations_to_zip();
		//$slider->add_styles_to_zip();
		$slider->add_navigation_to_zip();
		$slider->add_static_styles_to_zip();
		$slider->add_info_to_zip();
		$slider->close_export_zip();
		$slider->push_zip_to_client();
		$slider->delete_export_zip();

		exit;
	}
	
	
	/**
	 * set slides and slider parameters
	 * @return void
	 **/
	public function set_parameters(){
		$this->slider_id		= $this->get_id();
		$this->slider_title		= $this->get_title();
		$this->slider_alias		= $this->get_alias();
		$this->slider_params	= $this->get_params();
		$this->slider_settings	= $this->get_settings();
		$this->slider_slides	= $this->get_slides_for_export();
	}
	
	
	/**
	 * remove the image_id as its not needed in export
	 * @return void attachment ids are meaningless on the target site, so only the URLs are exported
	 **/
	public function remove_image_ids(){
		//remove from slider params
		if(!empty($this->get_val($this->slider_params, 'imgs', []))){
			foreach($this->slider_params['imgs'] as $k => $img){
				if($this->get_val($img, 'lib_id', false) !== false) unset($this->slider_params['imgs'][$k]['lib_id']);
			}
		}

		foreach($this->slider_slides ?? [] as $k => $s){
			if($this->get_val($this->slider_slides[$k], ['params', 'image', 'lib_id'], false) !== false)			unset($this->slider_slides[$k]['params']['image']['lib_id']);
			if($this->get_val($this->slider_slides[$k], ['params', 'video', 'poster', 'lib_id'], false) !== false)	unset($this->slider_slides[$k]['params']['video']['poster']['lib_id']);
			if($this->get_val($this->slider_slides[$k], ['params', 'thumb', 'srcId'], false) !== false)				unset($this->slider_slides[$k]['params']['thumb']['srcId']);

			$layers = $this->get_val($this->slider_slides[$k], 'layers', []);
			foreach($layers ?? [] as $l => $layer){
				if($this->get_val($layer, ['content', 'lib_id'], false) !== false)			 unset($this->slider_slides[$k]['layers'][$l]['content']['lib_id']);
				if($this->get_val($layer, ['content', 'poster', 'lib_id'], false) !== false) unset($this->slider_slides[$k]['layers'][$l]['content']['poster']['lib_id']);
				if($this->get_val($layer, ['bg', 'image', 'lib_id'], false) !== false)		 unset($this->slider_slides[$k]['layers'][$l]['bg']['image']['lib_id']);
				if($this->get_val($layer, ['bg', 'image_cache'], false) !== false)		 	 unset($this->slider_slides[$k]['layers'][$l]['bg']['image_cache']);
			}
		}
	}
	
	
	/**
	 * add all used images
	 * @return void
	 **/
	public function add_used_media() {
		// --- SLIDER ---
		foreach($this->image_path['slider'] ?? [] as $media_path){
			$img = $this->array_get_path($this->slider_params ?? [], $media_path, false);
			if(is_array($img)){
				foreach($img as $v){
					if(is_string($v) && $v !== '') $this->used_media[$v] = true;
				}
			}else{
				if(is_string($img) && $img !== '') $this->used_media[$img] = true;
			}
		}

		// --- SLIDES + LAYERS ---
		foreach($this->slider_slides ?? [] as $slide){
			$params = $this->get_val($slide, 'params', []);
			$layers = $this->get_val($slide, 'layers', []);

			// Slides params
			foreach($this->image_path['slides'] ?? [] as $media_path){
				$img = $this->array_get_path($params, $media_path, false);
				if(is_array($img)){
					foreach($img as $v){
						if(is_string($v) && $v !== '') $this->used_media[$v] = true;
					}
				}else{
					if(is_string($img) && $img !== '') $this->used_media[$img] = true;
				}
			}

			// Layers
			foreach($layers ?? [] as $layer){
				foreach ($this->image_path['layers'] ?? [] as $media_path) {
					$img = $this->array_get_path($layer, $media_path, false);
					if(is_array($img)){
						foreach($img as $v){
							if (is_string($v) && $v !== '') $this->used_media[$v] = true;
						}
					}else{
						if(is_string($img) && $img !== '') $this->used_media[$img] = true;
					}
				}
			}
		}
	}
	
	
	/**
	 * add navigations if not default animation
	 * @return void
	 **/
	public function add_used_navigations(){
		$nav = new RevSliderNavigation();
		
		$navigations = $nav->get_all_navigations(false, true);
		
		$slider_navs = [
			['nav', 'arrows', 't'],
			['nav', 'bullets', 't'],
			['nav', 'thumbs', 't'],
			['nav', 'tabs', 't'],
			['nav', 'scrubber', 't']
		];

		foreach($slider_navs as $nav){
			$_nav = $this->get_val($this->slider_params, $nav, false);
			if($_nav !== false) $this->used_navigations[$_nav] = true;
		}
	}
	
	/**
	 * modify the used stuff data
	 * @return void
	 **/
	public function modify_used_data(){
		$this->used_media = apply_filters('sr_exportSlider_usedMedia', $this->used_media, $this->slider_slides, $this->slider_params);
	}
	
	
	/**
	 * serialize the export data
	 * @return void
	 **/
	public function serialize_export_data(){
		$data = [
			'id'	 => $this->slider_id,
			'title'	 => $this->slider_title,
			'alias'	 => $this->slider_alias,
			'params' => $this->slider_params,
			'slides' => $this->slider_slides,
			'settings' => $this->slider_settings
		];
		
		$data = apply_filters('revslider_exportSlider_export_data', $data, $this);
		
		$this->export_data = json_encode($data, JSON_UNESCAPED_SLASHES);
		$this->export_data = $this->fix_double_slashes_in_urls($this->export_data);
	}

	/**
	 * this will fix double // in an JSON only for URLs
	 * @return string
	 **/
	public function fix_double_slashes_in_urls($json){
		return preg_replace_callback(
			'~"(https?://[^"]+)"~i',
			function($m) {
				// Fix all "//" that are NOT directly after ":" (so "http://" stays)
				$url = preg_replace('~(?<!:)/{2,}~', '/', $m[1]);

				return '"' . $url . '"';
			},
			$json
		);
	}
	
	/**
	 * serialize the navigation data
	 * @return void
	 **/
	public function serialize_navigation_data(){
		if(!empty($this->used_navigations)){
			$nav = new RevSliderNavigation();
			$this->navigation_data = $nav->export_navigation($this->used_navigations);
			if($this->navigation_data !== false) $this->navigation_data = json_encode($this->navigation_data);
		}
	}
	
	/**
	 * create the blank zip file to be used further on
	 * @return void
	 **/
	public function create_export_zip(){
		$this->usepcl = false;
		
		if(file_exists($this->directories['zip_path'])) @unlink($this->directories['zip_path']); //delete file to start with a fresh one
		
		if(class_exists('ZipArchive')){
			$this->zip = new ZipArchive;
			$success = $this->zip->open($this->directories['zip_path'], ZIPARCHIVE::CREATE | ZipArchive::OVERWRITE);
			
			if($success !== true) $this->throw_error(__("Can't create zip file: ", 'revslider').$this->directories['zip_path']);
		}else{
			//fallback to pclzip
			require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
			
			$this->pclzip = new PclZip($this->directories['zip_path']);
			$this->usepcl = true;
		}
	}
	
	
	/**
	 * push images and videos to the zip file
	 * @return void
	 **/
	public function add_images_videos_to_zip($root = false){

		if(empty($this->used_media)) return;

		$upload_dir			= $this->get_upload_path();
		$upload_dir_multi	= wp_upload_dir();
		$cont_url			= $this->get_val($upload_dir_multi, 'baseurl');
		$cont_url2			= (strpos($cont_url, 'http://') !== false) ? str_replace('http://', 'https://', $cont_url) : str_replace('https://', 'http://', $cont_url);
		$cont_url_no_www	= str_replace('www.', '', $cont_url);
		$cont_url2_no_www	= str_replace('www.', '', $cont_url2);
		
		foreach($this->used_media ?? [] as $file => $val){
			//replace double // except http:// && https://
			$file			= preg_replace(['#^(https?://)#', '#(?<!:)//#'], ['$1', '/'], $file);
			$add_path		= ($root === false) ? 'media/' : '';
			$add_structure	= ($root === false) ? 'media/'.$file : $file;

			if(strpos($file, 'http') !== false || substr($file, 0, 2) === '//' || substr($file, 0, 4) === '\/\/'){
				//check if we are in objects folder, if yes take the original image into the zip-
				$checkpath = str_replace([$cont_url.'/', $cont_url_no_www.'/', $cont_url2.'/', $cont_url2_no_www.'/'], '', $file);
				$add_checkpath = ($root === false) ? 'media/'.$checkpath : $checkpath;
				if($root === true){
					$add_checkpath = explode('/', $add_checkpath);
					$add_checkpath = end($add_checkpath);
				}
				
				//first check addons
				$addon_replace = false;
				foreach($this->directories['plugin'] ?? [] as $plugin_path => $plugin_slug){
					if(strpos($checkpath, $plugin_path) === false) continue;
					$remove = true;
					$addon_replace = str_replace($plugin_path, $plugin_slug.'/', $checkpath);
					break;
				}
				if($addon_replace === false){ //not from addon, continue to add
					$remove = $this->add_file_to_zip($checkpath, $add_checkpath, $add_path);
				}

				//check if file is in revslider plugin folder
				$replace_with = ($addon_replace !== false) ? $addon_replace : $checkpath;
				
				if($remove){
					$remove_array = [
						json_encode($cont_url . '/' . $checkpath, JSON_UNESCAPED_SLASHES),
						json_encode($cont_url . '//' . $checkpath, JSON_UNESCAPED_SLASHES),
						json_encode($cont_url_no_www . '/' . $checkpath, JSON_UNESCAPED_SLASHES),
						json_encode($cont_url_no_www . '//' . $checkpath, JSON_UNESCAPED_SLASHES)
					];

					if($addon_replace !== false) $remove_array[] = json_encode($checkpath, JSON_UNESCAPED_SLASHES);

					//multibyte fix: using json_encode() on $checkpath to transform multibyte in path to \u...
					$this->export_data = str_replace(
						$remove_array,
						json_encode($replace_with, JSON_UNESCAPED_SLASHES),
						$this->export_data
					);
				}
			}else{
				$this->add_file_to_zip($file, $add_structure, $add_path);
			}
		}
	}

	/** @return bool */
	public function add_file_to_zip($file, $zip_add = '', $pcl_add = ''){
		$dir = (is_file($this->directories['single'].$file)) ? $this->directories['single'] : false;
		$dir = ($dir === false && is_file($this->directories['dir'].'/'.$file)) ? $this->directories['dir'].'/' : $dir;
		//$dir = ($dir === false && is_file($this->directories['plugin'].'/'.$file)) ? $this->directories['dir'].'/' : $dir;
		
		if($dir === false) return false;

		if($this->usepcl){
			$this->pclzip->add($dir.$file, PCLZIP_OPT_REMOVE_PATH, $dir, PCLZIP_OPT_ADD_PATH, $pcl_add);
			return true;
		}
		
		$this->zip->addFile($dir.$file, $zip_add);
		return true;
	}
	
	
	/**
	 * push the slider, slides and layer data to the zip
	 * @return void
	 **/
	public function add_slider_export_to_zip($filename = 'slider_data.json'){
		if($this->usepcl){
			$list = $this->pclzip->add([[PCLZIP_ATT_FILE_NAME => $filename, PCLZIP_ATT_FILE_CONTENT => $this->export_data]]);
			if($list == 0) die("ERROR : '".$this->pclzip->errorInfo(true)."'");
			return;
		}

		$this->zip->addFromString($filename, $this->export_data);
	}
	
	
	/**
	 * push the custom animations to the zip
	 * @return void
	 **/
	public function add_animations_to_zip(){
		if(empty(trim($this->animations_data))) return;
	
		if($this->usepcl){
			$list = $this->pclzip->add([[PCLZIP_ATT_FILE_NAME => 'animations.json', PCLZIP_ATT_FILE_CONTENT => $this->animations_data]]);
			if($list == 0) die("ERROR : '".$this->pclzip->errorInfo(true)."'");
			return;
		}

		$this->zip->addFromString('animations.json', $this->animations_data); //add custom animations
	}
	
	
	/**
	 * push the custom navigations to the zip
	 * @return void
	 **/
	public function add_navigation_to_zip(){
		if(empty(trim($this->navigation_data))) return;
	
		if($this->usepcl){
			$list = $this->pclzip->add([[PCLZIP_ATT_FILE_NAME => 'navigation.json', PCLZIP_ATT_FILE_CONTENT => $this->navigation_data]]);
			if($list == 0) die("ERROR : '".$this->pclzip->errorInfo(true)."'");
			return;
		}

		$this->zip->addFromString('navigation.json', $this->navigation_data);
	}
	
	
	/**
	 * push the static styles to the zip
	 * @return void
	 **/
	public function add_static_styles_to_zip(){
		$static_css = $this->get_static_css();
		if(empty(trim($static_css))) return;

		if($this->usepcl){
			$list = $this->pclzip->add([[PCLZIP_ATT_FILE_NAME => 'static-captions.css',PCLZIP_ATT_FILE_CONTENT => $static_css]]);
			if($list == 0) die("ERROR : '".$this->pclzip->errorInfo(true)."'");
			return;
		}

		$this->zip->addFromString("static-captions.css", $static_css); //add slider settings
	}
	
	
	/**
	 * push the info.cfg to the zip
	 * allow for slider packs the automatic creation of the info.cfg
	 * @return void
	 **/
	public function add_info_to_zip(){
		if(apply_filters('revslider_slider_pack_export', false) === false) return;
	
		if($this->usepcl){
			$list = $this->pclzip->add([[PCLZIP_ATT_FILE_NAME => 'info.cfg', PCLZIP_ATT_FILE_CONTENT => md5($this->alias)]]);
			if($list == 0) die("ERROR : '".$this->pclzip->errorInfo(true)."'");
			return;
		}
		$this->zip->addFromString('info.cfg', md5($this->alias)); //add slider settings
	}
	
	
	/**
	 * close the zip if we are not in pcl
	 * @return void
	 **/
	public function close_export_zip(){
		if(!$this->usepcl) $this->zip->close();
	}
	
	
	/**
	 * send the zip to the client browser
	 * @return void sends the download headers and streams the file
	 **/
	public function push_zip_to_client(){
		$exportname = (!empty($this->slider_alias)) ? $this->slider_alias.'.zip' : 'slider_export.zip';
		
		header('Content-type: application/zip');
		header('Content-Disposition: attachment; filename='.$exportname);
		header('Pragma: no-cache');
		header('Expires: 0');
		readfile($this->directories['zip_path']);
	}
	
	
	/**
	 * delete the export zip file, ignoring errors
	 * @return void
	 **/
	public function delete_export_zip(){
		@unlink($this->directories['zip_path']);
	}
	
	
	/**
	 * Export a Zip with video, thumbnail and layergroup for import
	 * @dev function
	 * @return string
	 **/
	public function export_layer_group($videoid, $thumbid, $layers){
		$this->create_export_zip();
		
		$this->slider_alias = 'layergroup';
		$this->used_media[$this->get_url_attachment_image($thumbid)] = true;
		$this->used_media[$this->get_url_attachment_image($videoid)] = true;
		$this->add_images_videos_to_zip(true);
		$this->export_data = stripslashes($layers);
		$this->add_slider_export_to_zip('layers.json');
		$this->close_export_zip();
		
		return $this->directories['zip_url'];
	}

	/** @return array [allowed, absolute path, entry name inside the zip] - keeps the export from leaving the content directory */
	public function is_safe_export_path($relative, $baseDir, array $allowedExts){
		// Normalize & decode once
		$relative = wp_normalize_path($relative);
		$relative = rawurldecode($relative); // catch %2e%2e etc.

		// Remember if it started with a slash
		$hadLeadingSlash = ($relative !== '' && $relative[0] === '/');

		// Strip a single leading slash for internal checks
		if($hadLeadingSlash) {
			$relative = ltrim($relative, '/');
		}

		// Basic sanity: forbid empties, Windows drive letters, UNC, or schemes
		if($relative === ''
			|| preg_match('~^[A-Za-z]:[\\\\/]~', $relative)         // C:\ or C:/
			|| preg_match('~^\\\\\\\\~', $relative)                 // \\server\share
			|| preg_match('~^[A-Za-z][A-Za-z0-9+.-]*:~', $relative) // file:, php:, etc.
		){
			return [false, null, null];
		}

		// Collapse duplicate slashes and remove harmless "./" segments
		$relative = preg_replace('~/+~', '/', $relative);
		$relative = preg_replace('~(^|/)\\./~', '$1', $relative);

		// Forbid traversal & null bytes & control chars
		if(strpos($relative, "\0") !== false) return [false, null, null];
		if(preg_match('~(^|/)\.\.(?:/|$)~', $relative)) return [false, null, null];
		if(preg_match('~[[:cntrl:]]~', $relative)) return [false, null, null];

		$baseDir = rtrim(wp_normalize_path($baseDir), '/');

		// Resolve and ensure inside base
		$full = realpath($baseDir . '/' . $relative);
		if($full === false) return [false, null, null];

		$fullNorm = wp_normalize_path($full);
		if(strpos($fullNorm, $baseDir . '/') !== 0 && $fullNorm !== $baseDir){
			return [false, null, null];
		}

		// Extension allowlist
		$ext = strtolower(pathinfo($fullNorm, PATHINFO_EXTENSION));
		if(!in_array($ext, $allowedExts, true)) return [false, null, null];

		// Produce clean ZIP entry path
		$zipEntry = str_replace('\\', '/', $relative);
		$zipEntry = ltrim($zipEntry, './');

		// Always add back the leading slash for export consistency
		if($hadLeadingSlash) {
			$zipEntry = '/' . ltrim($zipEntry, '/');
		}

		return [true, $fullNorm, $zipEntry];
	}

	/**
	 * Convert an http(s) or protocol-relative URL (or a path) into a path relative to WP_CONTENT_DIR.
	 * Returns [bool $ok, string|null $contentRel].
	 * @return array [success, path relative to wp-content]
	 */
	public function to_content_relative($input){
		// normalize slashes
		$norm = wp_normalize_path($input);

		// If it's a URL, parse and extract the path (handles http, https, and //host)
		if(preg_match('~^(?:https?:)?//~i', $norm)){
			$parts = @parse_url($norm);
			if(!is_array($parts) || empty($parts['path'])) return [false, null];
			$norm = wp_normalize_path($parts['path']); // like /wp3/wp-content/plugins/...
		}

		// If it still includes domain-less prefix like //host/path (very rare after parse) – strip leading slashes
		if(substr($norm, 0, 2) === '//') $norm = substr($norm, 2);

		// We only allow anything under /wp-content/
		$pos = strpos($norm, '/wp-content/');
		if($pos === false){
			// Case: already content-relative (plugins/..., themes/..., uploads/...)
			if(preg_match('~^(?:plugins|themes|uploads)/~', ltrim($norm, '/'))){
				$rel = ltrim($norm, '/');
				return [true, '/' . $rel];
			}

			// Case: starts with "/" (like /revslider/...), treat as uploads-relative
			if(substr($norm, 0, 1) === '/'){
				$rel = ltrim($norm, '/');
				return [true, '/uploads/' . $rel];
			}

			return [false, null];
		}

		// Make relative to wp-content/
		$contentRel = substr($norm, $pos + strlen('/wp-content/'));
		$contentRel = ltrim($contentRel, '/');

		return ($contentRel !== '') ? [true, '/' . $contentRel] : [false, null];
	}
}