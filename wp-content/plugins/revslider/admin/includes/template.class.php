<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Module templates from the ThemePunch template shop.
 *
 * Keeps the catalogue (list + top downloads) in the rs-templates option, refreshed from the server every
 * few days, and downloads a template's zip on demand into uploads/revslider/templates/. Preview images are
 * fetched lazily by _check_file_path(). Everything except browsing the catalogue needs a valid licence.
 */
class RevSliderTemplate extends RevSliderFunctions {
	
	private $templates_list			= 'revslider/get-list.php';
	private $templates_top_list		= 'revslider/get-top-list.php';
	private $templates_download		= 'revslider/download.php';
	public $templates_server_path	= '/revslider/images/';
	public $templates_server_path_video	= '/revslider/video/';
	public $templates_path			= '/revslider/templates/';
	private $templates_basedir		= '';
	private $templates_baseurl		= '';
	public $template_slides			= [];
	public $template_slides_data	= [];
	const SHOP_VERSION				= '3.0';

	public function __construct(){
		parent::__construct();

		$upload_dir = wp_upload_dir();
		$this->templates_basedir = $upload_dir['basedir'] . $this->templates_path;
		$this->templates_baseurl = $upload_dir['baseurl'] . $this->templates_path;
	}
	
	/**
	 * Download template by UID (also validates if download is legal)
	 * @since: 5.0.5
	 * @return string|array|false path to the downloaded zip, ['error' => msg] on failure
	 */
	public function _download_template($uid){
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === false) return ['error' => __("Please activate your Slider Revolution plugin to download templates", 'revslider')];

		$rslb	= RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		$uid	= esc_attr($this->clear_uid($uid));
		$code	= ($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === false) ? '' : $this->get_options(['system', 'license'], '');
		
		// Check folder permission and define file location
		if(!wp_mkdir_p($this->templates_basedir)) return ['error' => __("Can't write into the uploads folder of WordPress, please change permissions and try again!", 'revslider')];
		
		$data = [
			'code'			=> urlencode($code),
			'shop_version'	=> urlencode(self::SHOP_VERSION),
			'version'		=> urlencode(RS_REVISION),
			'uid'			=> urlencode($uid),
			'product'		=> urlencode(RS_PLUGIN_SLUG)
		];
		
		$request = $rslb->call_url($this->templates_download, $data, 'templates');
		
		if(is_wp_error($request)) return ['error' => __("Can't connect to the ThemePunch servers, please check your webserver settings", 'revslider')];

		if($response = $this->get_val($request, 'body')){
			if($response !== 'invalid' && $this->get_val($request, ['response', 'code']) == '200'){
				//add stream as a zip file
				$file = $this->templates_basedir . $uid.'.zip';
				@mkdir(dirname($file));
				return (@file_put_contents($file, $response) !== false) ? $file : ['error' => __("Can't write the file into the uploads folder of WordPress, please change permissions and try again!", 'revslider')];
			}

			return ['error' =>  __('Template could not be found / license key is invalid', 'revslider')];
		}
		
		return false;
	}
	
	
	/**
	 * Delete the Template file
	 * @return bool
	 */
	public function _delete_template($uid){
		return wp_delete_file($this->templates_basedir . esc_attr($this->clear_uid($uid)) . '.zip');
	}
	
	
	/**
	 * Get the Templatelist from servers
	 * throttled to once every four days unless $force is set
	 * @since: 5.0.5
	 * @return void
	 */
	public function _get_template_list($force = false){
		$rslb		= RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		$last_check	= $this->get_options(['timestamps', 'templates'], false);
		
		if($last_check == false){ //first time called
			$last_check = 172801;
			$this->update_option(['timestamps', 'templates'], time());
		}

		// Get latest Templates
		if(time() - $last_check <= 345600 && $force === false) return; //4 days

		$this->update_option(['timestamps', 'templates'], time());

		$hash = ($force === true) ? '' : $this->get_options(['hashes', 'templates'], '');
		$code = ($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === false) ? '' : $this->get_options(['system', 'license'], '');
		$data = [
			'code'		=> urlencode($code),
			'shop_version' => urlencode(self::SHOP_VERSION),
			'hash'		=> urlencode($hash),
			'version'	=> urlencode(RS_REVISION),
			'product'	=> urlencode(RS_PLUGIN_SLUG)
		];
		$request = $rslb->call_url($this->templates_list, $data, 'templates');

		if(!is_wp_error($request)){
			if($response = $this->maybe_unserialize_safe($request['body'])){ //remote body: never instantiate classes
				$templates = json_decode($response, true);
				if(is_array($templates)){
					if(isset($templates['hash'])) $this->update_option(['hashes', 'templates'], $templates['hash']);

					$templates = $this->do_compress($templates);
					$this->update_option(['new'], $templates, 'rs-templates');
				}
			}
		}
		
		$this->update_template_list();
		$this->_get_top_downloaded_list($force);
	}

	/**
	 * refresh the "most downloaded" catalogue, stored separately from the full list
	 * @return void
	 */
	public function _get_top_downloaded_list($force = false){
		$rslb = RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		$hash = ($force === true) ? '' : $this->get_options(['hashes', 'templates-top'], '');
		$code = ($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === false) ? '' : $this->get_options(['system', 'license'], '');
		$data = [
			'code'		=> urlencode($code),
			'shop_version' => urlencode(self::SHOP_VERSION),
			'hash'		=> urlencode($hash),
			'version'	=> urlencode(RS_REVISION),
			'product'	=> urlencode(RS_PLUGIN_SLUG)
		];
		$request = $rslb->call_url($this->templates_top_list, $data, 'templates');
		if(is_wp_error($request)) return;
	
		if($response = $this->maybe_unserialize_safe($request['body'])){ //remote body: never instantiate classes
			$toplist = json_decode($response, true);
			
			if(!is_array($toplist)) return;
			if(isset($toplist['hash'])) $this->update_option(['hashes', 'templates-top'], $toplist['hash']);

			$toplist = $this->do_compress($this->get_val($toplist, 'top'));
			
			$this->update_option(['top'], $toplist, 'rs-templates');
		}
	}
	
	
	/**
	 * Update the Templatelist, move new templates into the templates variable
	 * merges a freshly fetched list into the stored one, flagging new and updated entries and dropping
	 * templates the server no longer offers
	 * @since: 5.0.5
	 * @return void
	 */
	private function update_template_list(){
		$new = $this->do_uncompress($this->get_options(['new'], false, false, 'rs-templates'));
		$cur = $this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates'));
		$counter = 0;

		if($new !== false && !empty($new) && is_array($new)){
			if(empty($cur)){
				$cur = $new;
				$counter = (isset($cur['slider']) && is_array($cur['slider'])) ? count($cur['slider']) : $counter;
			}else{
				if(isset($new['slider']) && is_array($new['slider'])){
					if(isset($cur['slider']) && is_array($cur['slider']) && isset($new['slider']) && is_array($cur['slider'])){
						$_n = count($new['slider']);
						$_c = count($cur['slider']);
						$counter = ($_n > $_c) ? $_n - $_c : $counter;
					}
					
					foreach($new['slider'] as $n){
						$found = false;
						foreach($cur['slider'] ?? [] as $ck => $c){
							if($c['uid'] != $n['uid']) continue;
						
							if(version_compare($c['version'], $n['version'], '<')){
								$n['is_new'] = true;
								$n['push_image'] = true; //push to get new image and replace
								//remove slide data from option ['templates-data'] if existing, as new data arrived!
								$_alias = $this->get_val($c, 'alias');
								$this->delete_tp_template_slides_data($_alias);
							}

							if(isset($c['is_new'])) $n['is_new'] = true; //is_new will stay until update is done
							if(isset($n['new_slider'])) unset($n['new_slider']); //remove this again, as the new flag should be removed now
							$n['exists'] = true; //if this flag is not set here, the template will be removed from the list
							
							$cur['slider'][$ck] = $n;
							$found = true;
							
							break;
						}
						
						if(!$found){
							$n['exists']	 = true;
							$n['new_slider'] = true;
							$cur['slider'][] = $n;
						}
					}
					
					foreach($cur['slider'] as $ck => $c){ //remove no longer available Slider
						if(!isset($c['exists'])) unset($cur['slider'][$ck]);
						if(isset($c['exists'])) unset($cur['slider'][$ck]['exists']);
					}
					
					$cur['slides'] = $new['slides']; // push always all slides
				}
			}

			$cur = $this->do_compress($cur);
			$this->update_option(['new'], false, 'rs-templates');
			$this->update_option(['templates'], $cur, 'rs-templates');
		}
		
		$this->update_option(['counter'], $counter, 'rs-templates');
	}
	
	
	/**
	 * Remove the is_new attribute which shows the "update available" button
	 * @since: 5.0.5
	 * @return void
	 */
	public function remove_is_new($uid){
		$cur = $this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates'));
		
		if(is_array($cur) && isset($cur['slider']) && is_array($cur['slider'])){
			foreach($cur['slider'] as $ck => $c){
				if($c['uid'] != $uid) continue;

				unset($cur['slider'][$ck]['is_new']);
				break;
			}
		}
		
		$cur = $this->do_compress($cur);
		$this->update_option(['templates'], $cur, 'rs-templates');
	}


	/**
	 * Update the Images get them from Server and check for existance on each image
	 * @since: 5.0.5
	 * @param bool|string $img   a single image path, or false for all pending ones
	 * @param bool        $video fetch from the video folder instead
	 * @return void
	 */
	public function _update_images($img = false, $video = false){
		global $SR_GLOBALS;

		if(empty($this->template_slides)) $this->template_slides = $this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates'));
		$templates	= $this->template_slides;
		$media_path = ($video === false) ? $this->templates_server_path : $this->templates_server_path_video;
		$reload		= [];
		$loaded		= false;
		$compress	= false;
		$file		= false;
		$check_in	= ($video !== false) ? 'video' : 'img';
		$rslb   	= RevSliderGlobals::instance()->get('RevSliderLoadBalancer');

		if(wp_mkdir_p($this->templates_basedir) && !empty($templates) && is_array($templates)){
			if(!empty($templates['slider']) && is_array($templates['slider'])){
				$mime_types = array_merge($this->get_val($SR_GLOBALS, ['mime_types', 'image']), $this->get_val($SR_GLOBALS, ['mime_types', 'video']));

				foreach($templates['slider'] as $key => $temp){
					$temp_img = $this->get_val($temp, $check_in);
					if($img !== false){ //we want to download a certain image, check for it
						if($temp_img !== $img) continue;

						$file_type = wp_check_filetype($temp_img, $mime_types);
						if($this->get_val($file_type, 'ext', false) === false || $this->get_val($file_type, 'type', false) === false) continue;
					}

					$file = $this->templates_basedir . $temp_img;
					
					if(!file_exists($file) || isset($temp['push_image'])){
						$_file = $rslb->download_url($media_path . $temp_img, $file, 'templates', false, $this->templates_basedir);
						if(is_wp_error($_file)) continue;

						$loaded = $_file;
					
						$reload[$temp['alias']] = true;
						if(isset($temp['push_image'])){
							unset($templates['slider'][$key]['push_image']);
							$compress = true;
						}
					}
				}
			}

			if($loaded === false || (!empty($templates['slides']) && is_array($templates['slides']))){
				foreach($templates['slides'] ?? [] as $key => $temp){
					foreach($temp ?? [] as $k => $tvalues){
						if($img !== false){ //we want to download a certain image, check for it
							$temp_img = $this->get_val($tvalues, 'img');
							if($temp_img !== $img) continue;
							
							$file_type = wp_check_filetype($temp_img, $this->get_val($SR_GLOBALS, ['mime_types', 'image']));
							if($this->get_val($file_type, 'ext', false) === false || $this->get_val($file_type, 'type', false) === false) continue;
						}

						$slide_file = $this->templates_basedir . $tvalues['img'];
						
						//already loaded, do not load again
						if(file_exists($slide_file) && $file === $slide_file && $loaded !== false) continue;

						if(file_exists($slide_file) && !isset($reload[$key])) continue;

						$_slide_file = $rslb->download_url($this->templates_server_path . $tvalues['img'], $slide_file, 'templates', false, $this->templates_basedir);
						if(is_wp_error($_slide_file)) continue;
					}
				}
			}
		}

		$this->template_slides = $templates;
		if($compress){
			$templates = $this->do_compress($templates);
			$this->update_option(['templates'], $templates, 'rs-templates');
		}
	}
	
	
	/**
	 * get default ThemePunch default Slides
	 * @since: 5.0
	 * @return array slides per template, with their preview images resolved
	 */
	public function get_tp_template_slides($sliders = false){
		global $wpdb;
		
		$templates = [];
		if($sliders == false) $sliders = $this->get_tp_template_sliders();
		foreach($sliders ?? [] as $slider){
			$slides		= $this->get_tp_template_default_slides($slider['alias']);
			$cur_slides = $slides;
			
			if(!empty($cur_slides)){
				$i = 1;
				foreach($cur_slides as $key => $tmpl){
					if(isset($slides[$key]) && !empty($slides[$key]['img'])) $cur_slides[$key]['img']	= $this->_check_file_path($slides[$key]['img'], true, false);
					if($this->get_val($tmpl, 'title', false) === false) $cur_slides[$key]['title']		= 'Slide '.$i;
					$cur_slides[$key]['uid']	= $this->get_val($slider, 'uid');
					$cur_slides[$key]['parent']	= $this->get_val($slider, 'id');
					
					//addon requirements
					$cur_slides[$key]['plugin_require'] = $this->get_val($slider, 'plugin_require', []);
					
					$i++;
				}
			}
			
			$templates = array_merge($templates, $cur_slides);
		}
		
		return $templates;
	}
	
	
	/**
	 * get default ThemePunch default Slides
	 * @since: 5.0
	 * @return array
	 */
	public function get_tp_template_default_slides($slider_alias){
		if(empty($slider_alias)) return [];
		if(empty($this->template_slides)){
			$this->template_slides	= $this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates'));
		}
		
		$slides		= (is_array($this->template_slides) && isset($this->template_slides['slides']) && !empty($this->template_slides['slides'])) ? $this->template_slides['slides'] : [];
		if(!isset($slides[$slider_alias])) return [];

		$slides_data = $this->get_tp_template_slides_data($slider_alias);
		foreach($slides[$slider_alias] as $key => $v){
			$slides[$slider_alias][$key]['params'] = $slides_data[$key]['params'];
			$slides[$slider_alias][$key]['layers'] = $slides_data[$key]['layers'];
		}

		// Add global slide to result
		foreach($slides_data as $key => $v){
			if ($slides_data[$key]['static']) {
				$slides[$slider_alias][$key]['params'] = $slides_data[$key]['params'];
				$slides[$slider_alias][$key]['layers'] = $slides_data[$key]['layers'];
				$slides[$slider_alias][$key]['global'] = true;
				break;
			}
		}

		return $slides[$slider_alias];
	}

	/**
	 * check if data is available for the slides and push them
	 * if not download them and add them
	 * @return array
	 **/
	public function get_tp_template_slides_data($slider_alias){
		if(empty($slider_alias)) return [];
		if(empty($this->template_slides_data)){
			$this->template_slides_data	= $this->do_uncompress($this->get_options([], false, false, 'rs-templates-slides'));
		}

		if(isset($this->template_slides_data[$slider_alias])) return $this->template_slides_data[$slider_alias];
			
		$uid = $this->get_uid_by_alias($slider_alias);
		if($uid === false) return false; 
		
		//download by uid
		$template = $this->_download_template($uid);
		
		if($template === false || is_array($template) && isset($template['error'])) return false;
		
		$import = new RevSliderSliderImport();
		if($import->unzip_slider($template) !== true) return false;
		if($import->check_template() !== true) return false;

		$import->set_slider_data_raw();
		$import->slider_data = is_string($import->slider_raw_data) ? json_decode($import->slider_raw_data, true) : null; //guard instead of @-suppressing the PHP 8.1 null-to-string deprecation

		if(empty($import->slider_data)) return false;
		$slides_data = $this->get_val($import->slider_data, 'slides');
		$import->clear_files();

		if(empty($slides_data)) return false;

		$this->template_slides_data[$slider_alias] = $slides_data;
		
		$this->update_all_options($this->template_slides_data, 'rs-templates-slides');

		return $this->template_slides_data[$slider_alias];
	}

	/**
	 * remove slides data from the saved data, as new data has arrived
	 * @return bool
	 **/
	public function delete_tp_template_slides_data($slider_alias){
		if(empty($this->template_slides_data)){
			$this->template_slides_data	= $this->do_uncompress($this->get_options([], false, false, 'rs-templates-slides'));
		}

		if(!isset($this->template_slides_data[$slider_alias])) return true;

		unset($this->template_slides_data[$slider_alias]);

		$this->update_all_options($this->template_slides_data, 'rs-templates-slides');

		return true;
	}
	
	/**
	 * @return string|false the template uid belonging to an alias
	 */
	public function get_uid_by_alias($slider_alias){
		if(empty($this->template_slides)){
			$this->template_slides	= $this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates'));
		}
		$slider = (is_array($this->template_slides) && isset($this->template_slides['slider']) && !empty($this->template_slides['slider'])) ? $this->template_slides['slider'] : [];

		foreach($slider ?? [] as $_slider){
			if($this->get_val($_slider, 'alias') === $slider_alias) return $this->get_val($_slider, 'uid');
		}

		return false;
	}
	
	/**
	 * get default ThemePunch Sliders
	 * @since: 5.0
	 * @param string|false $uid limit the result to one template
	 * @return array the catalogue entries, enriched with install state
	 */
	public function get_tp_template_sliders($uid = false){
		global $wpdb;

		$plugin_list = [];
		$templates 	= $this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates'));
		$templates 	= $this->get_val($templates, 'slider', []);
		$top_list 	= $this->do_uncompress($this->get_options(['top'], [], false, 'rs-templates'));
		if(!is_array($top_list)) $top_list = [];
		if(empty($templates)) return $templates;

		$favorite = RevSliderGlobals::instance()->get('RevSliderFavorite');
		
		foreach($templates ?? [] as $k => $template){
			if($uid !== false && $uid !== $this->get_val($template, 'uid')){
				unset($templates[$k]);
				continue;
			}
			$templates[$k]['plugin_require'] = (isset($templates[$k]['plugin_require']) && !empty($templates[$k]['plugin_require'])) ? json_decode($templates[$k]['plugin_require'], true) : '';
			
			if(!empty($templates[$k]['plugin_require'])){
				foreach($templates[$k]['plugin_require'] as $pr => $plugin){
					$path = $this->get_val($plugin, 'path');
					if(!isset($plugin_list[$path])){
						$plugin_list[$path] = (is_plugin_active(esc_attr($path))) ? true : false;
					}
					$templates[$k]['plugin_require'][$pr]['installed'] = ($plugin_list[$path] === true) ? true : false;
					if(isset($plugin['path'])) unset($templates[$k]['plugin_require'][$pr]['path']);
					if(isset($plugin['url'])) unset($templates[$k]['plugin_require'][$pr]['url']);
					$templates[$k]['plugin_require'][$pr]['slug'] = str_replace(['revslider-', '-addon'], '', $this->get_val(explode('/', $path), 0));
				}
			}

			$tags	= $this->get_val($template, 'filter', []);
			$tags[]	= $this->get_val($template, 'cat');
			$templates[$k]['tags'] = $tags;

			if(isset($templates[$k]['filter'])) unset($templates[$k]['filter']);
			if(isset($templates[$k]['cat'])) unset($templates[$k]['cat']);
			
			if(!isset($templates[$k]['setup_notes'])){
				$templates[$k]['setup_notes'] = '<span class="ttm_content">Checkout our <a href="https://www.themepunch.com/revslider-doc/slider-revolution-documentation/" target="_blank" rel="noopener">Documentation</a> for basic Slider Revolution help.</span>';
			}

			$templates[$k]['hot'] = (in_array($this->get_val($template, 'uid'), $top_list)) ? true : false;
			
			$id = $this->get_val($template, 'id', 0);
			$templates[$k]['favorite'] = $favorite->is_favorite('moduletemplates', $id);
		}
		
		krsort($templates);
		
		return $templates;
	}
	
	
	/**
	 * get the template sliders for the get_full_library function
	 * trimmed down and paged (500 per page) so the editor's library stays responsive
	 * @since: 6.0
	 * @return array
	 */
	public function get_tp_template_sliders_for_library($leave_counter = false, $page = false){
		$remove = ['params', 'installed', 'active', 'width', 'height', 'zip', 'guide_img', 'guide_title', 'guide_url'];
		$templates = $this->get_tp_template_sliders();
		if($page !== false && intval($page) <= 0) $page = 1;
		$start	 = 500 * $page - 500;
		$current = 0;
		$added	 = 0;
		$max	 = 500;
		
		foreach($templates ?? [] as $k => $t){
			foreach($remove ?? [] as $r){
				if(isset($templates[$k][$r])) unset($templates[$k][$r]);
			}
			//pagination must run once per template, not once per removed key (it was nested in the $remove loop)
			if($page !== false){
				if($current < $start || $added >= $max){
					unset($templates[$k]);
				}else{
					$added++;
				}

				$current++;
			}
		}
		
		if(!$this->_truefalse($leave_counter)) $this->update_option(['counter'], 0, 'rs-templates'); //reset the counter

		return $templates;
	}
	
	
	/**
	 * get the template slides for the get_full_library function
	 * @since: 6.0
	 * @return array
	 */
	public function get_tp_template_slides_for_library($tmp_slide_uid){
		$tmp_slide_uid = (array)$tmp_slide_uid;
		if(!empty($tmp_slide_uid)){
			$templates = [];
			foreach($tmp_slide_uid ?? [] as $tmp_uid){
				$templates = $this->get_tp_template_sliders($tmp_uid);
			}
		}else{
			$templates = $this->get_tp_template_sliders();
		}
		
		return $this->get_tp_template_slides($templates);
	}
	
	
	/**
	 * check if image was uploaded, if yes, return path or url
	 * downloads the preview image from the server on first access
	 * @since: 5.0.5
	 * @param bool $url      return a public URL instead of a filesystem path
	 * @param bool $download allow fetching the image when it is missing
	 * @return string the resolved path/url, or $image unchanged when it could not be fetched
	 */
	public function _check_file_path($image, $url = false, $download = true, $video = false){
		$prefix = $url ? $this->templates_baseurl : $this->templates_basedir;
		$file   = $this->templates_basedir . $image;
		
		if(file_exists($file)) return $prefix . $image;
		if($download !== true) return $image;

		$this->_update_images($image, $video); //redownload image from server and store it

		return file_exists($file) ? $prefix . $image : $image;
	}
	
	
	/**
	 * Get all uids from a certain package, by one uid
	 * templates are sold in packages; this returns every template of the package the uid belongs to
	 * @since: 5.2.5
	 * @return array
	 */
	public function get_package_uids($uid, $sliders = false){
		if($sliders == false) $sliders = $this->get_tp_template_sliders();
		
		$uids = [];
		$package = false;

		foreach($sliders ?? [] as $slider){
			if($slider['uid'] != $uid) continue;
			if(isset($slider['package'])) $package = $slider['package'];
			break;
		}
		
		if($package !== false){
			$i = 0;
			$tuids = [];
			foreach($sliders ?? [] as $slider){
				if($this->get_val($slider, 'package') != $package) continue;
				if($this->get_val($slider, 'package_parent', 'false') == 'true') continue; //dont install parent package
				
				$tuids[] = [
					'uid' => $this->get_val($slider, 'uid'),
					'sid' => $this->get_val($slider, 'id'),
					'order' => $this->get_val($slider, 'package_order', 0)
				];
			}
		}

		if(!empty($tuids)){
			usort($tuids, [$this, 'sort_by_order']);
			foreach($tuids ?? [] as $uid){
				$uids[$uid['sid']] = $uid['uid'];
			}
		}
		
		return $uids;
	}
	
	
	/**
	 * get the template existing categories, merging filter and cat
	 * @return array
	 **/
	public function get_template_categories(){
		$cat		= [];
		$defaults	= $this->get_val($this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates')), 'slider', []);
	
		foreach($defaults ?? [] as $def){
			$d_cat		= $this->get_val($def, 'cat', '');
			if(trim($d_cat) !== '' && !isset($cat[$d_cat])) $cat[$d_cat] = ucfirst($d_cat);
			
			foreach($this->get_val($def, 'filter', []) ?? [] as $filter){
				if(trim($filter) !== '' && !isset($cat[$filter])) $cat[$filter] = ucfirst($filter);
			}
		}
		
		return $cat;
	}


	/**
	 * @param string $uid
	 * @return array
	 */
	public function get_template_by_uid($uid){
		$defaults	= $this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates'));
		$sliders	= $this->get_val($defaults, 'slider', []);
		$return		= [];
		
		foreach($sliders ?? [] as $slider){
			if($this->get_val($slider, 'uid') != $uid) continue;
			$return = $slider;
			break;
		}
		
		return $return;
	}


	/**
	 * @param string $uid
	 * @return array
	 */
	public function get_template_slides_by_uid($uid){
		$defaults	= $this->do_uncompress($this->get_options(['templates'], false, false, 'rs-templates'));
		$sliders	= $this->get_val($defaults, 'slider', []);
		$slides		= $this->get_val($defaults, 'slides', []);
		$return		= [];
		
		foreach($sliders ?? [] as $slider){
			if($this->get_val($slider, 'uid') != $uid) continue;

			$alias = $this->get_val($slider, 'alias');
			$return = $this->get_val($slides, $alias, []);
			break;
		}
		
		return $return;
	}
	
	
	/**
	 * get all slides images by template UID
	 * download missing images
	 * 
	 * @param string $uid
	 * @return array
	 **/
	public function get_slides_images_by_uid($uid){
		$return = [];
		$slides = $this->get_template_slides_by_uid($uid);
		
		foreach($slides as $sl){
			if (empty($sl['img'])) continue;
			$return[] = $this->_check_file_path($sl['img'], true, true);
		}
		
		return $return;
	}
	
	
	/**
	 * get the slide thumbnail
	 * download missing image
	 * 
	 * @param string $uid
	 * @param int    $slidenumber
	 * @return bool|string
	 **/
	public function get_slide_image_by_uid($uid, $slidenumber){
		$image  = false;
		$slides = $this->get_template_slides_by_uid($uid);
		if(!empty($slides)){
			$sl    = $this->get_val($slides, $slidenumber, []);
			$image = $this->get_val($sl, 'img');
		}
		
		return $image ? $this->_check_file_path($image, true, true) : $image;
	}
	
	/**
	 * get the template thumbnail
	 * download missing image
	 * 
	 * @param string $uid
	 * @return bool|string
	 **/
	public function get_template_image_by_uid($uid){
		$image  = false;
		$template = $this->get_template_by_uid($uid);
		if(!empty($template)){
			$image = $this->get_val($template, 'img');
		}
		
		return $image ? $this->_check_file_path($image, true, true) : $image;
	}
	
	
	/**
	 * clears the uid to make sure no illegal characters are in it
	 **/
	/**
	 * strip everything but letters, digits and spaces from a uid before it is used in a path or request
	 * @return string
	 */
	public function clear_uid($uid){
		return preg_replace("/[^a-zA-Z0-9\s]/", '', $uid);
	}
	
	/**
	 * usort callback ordering catalogue entries by their 'order' field
	 * @return int
	 */
	public function sort_by_order($a, $b) {
		return $a['order'] - $b['order'];
	}
}
