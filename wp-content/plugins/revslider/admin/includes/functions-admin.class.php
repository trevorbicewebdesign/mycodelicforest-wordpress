<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Admin-only helpers on top of RevSliderFunctions: the data the editor's library and overview screens
 * need, media uploads, custom animations, and the system requirements check.
 */
class RevSliderFunctionsAdmin extends RevSliderFunctions {
	
	/**
	 * get the full object of: 
	 * +- Slider Templates
	 * +- Created Slider
	 * +- Object Library Images
	 * - Object Library Videos
	 * +- SVG
	 * +- Font Icons
	 * - layers
	 * @return array everything the editor's library dialog shows: templates, modules, objects, icons
	 **/
	public function get_full_library($include = ['all'], $tmp_slide_uid = [], $refresh_from_server = false, $get_static_slide = false, $page = false){
		$include	= (array)$include;
		$template	= new RevSliderTemplate();
		/* @var RevSliderObjectLibrary $library */
		$library	= RevSliderGlobals::instance()->get('RevSliderObjectLibrary');
		$slide		= new RevSliderSlide();
		$object		= [];
		$tmp_slide_uid = ($tmp_slide_uid !== false) ? (array)$tmp_slide_uid : [];

		if($refresh_from_server === true){ //refresh list from server
			if(in_array('all', $include) || in_array('moduletemplates', $include)){ 
				$template->_get_template_list(true);
				$object['moduletemplates']['tags'] = $object['moduletemplates']['tags'] ?? $template->get_template_categories();
				asort($object['moduletemplates']['tags']);
			}
			if(in_array('all', $include) || in_array('layers', $include) || in_array('videos', $include) || in_array('images', $include) || in_array('objects', $include)){
				$library->_get_list(true);
			}
			if(in_array('all', $include) || in_array('layers', $include)){
				$object['layers']['tags'] = $object['layers']['tags'] ?? $library->get_objects_categories('4');
				asort($object['layers']['tags']);
			}
			if(in_array('all', $include) || in_array('videos', $include)){
				$object['videos']['tags'] = $object['videos']['tags'] ?? $library->get_objects_categories('3');
				asort($object['videos']['tags']);
			}
			if(in_array('all', $include) || in_array('images', $include)){
				$object['images']['tags'] = $object['images']['tags'] ?? $library->get_objects_categories('2');
				asort($object['images']['tags']);
			}
			if(in_array('all', $include) || in_array('objects', $include)){
				$object['objects']['tags'] = $object['objects']['tags'] ?? $library->get_objects_categories('1');
				asort($object['objects']['tags']);
			}
			$object = apply_filters('revslider_get_full_library_refresh', $object, $include, $tmp_slide_uid, $refresh_from_server, $get_static_slide, $this);
		}

		if(in_array('moduletemplates', $include) || in_array('all', $include))		$object['moduletemplates']['items']		 = $object['moduletemplates']['items'] ?? $template->get_tp_template_sliders_for_library($refresh_from_server, $page);
		if(in_array('moduletemplateslides', $include) || in_array('all', $include))	$object['moduletemplateslides']['items'] = $object['moduletemplateslides']['items'] ?? $template->get_tp_template_slides_for_library($tmp_slide_uid);
		if(in_array('modules', $include) || in_array('all', $include))				$object['modules']['items']				 = $object['modules']['items'] ?? $this->get_slider_overview();
		if(in_array('moduleslides', $include) || in_array('all', $include))			$object['moduleslides']['items']		 = $object['moduleslides']['items'] ?? $slide->get_slides_for_library($tmp_slide_uid, $get_static_slide);
		if(in_array('svgs', $include) || in_array('all', $include))					$object['svgs']['items']				 = $object['svgs']['items'] ?? $library->get_svg_sets_full();
		if(in_array('svgcustom', $include) || in_array('all', $include))			$object['svgcustom']['items']			 = $object['svgcustom']['items'] ?? $library->get_custom_svgs();
		if(in_array('icons', $include) || in_array('all', $include))				$object['icons']['items']				 = $object['icons']['items'] ?? $library->get_font_icons();
		if(in_array('layers', $include) || in_array('all', $include))				$object['layers']['items']				 = $object['layers']['items'] ?? $library->load_objects('4');
		if(in_array('videos', $include) || in_array('all', $include))				$object['videos']['items']				 = $object['videos']['items'] ?? $library->load_objects('3');
		if(in_array('images', $include) || in_array('all', $include))				$object['images']['items']				 = $object['images']['items'] ?? $library->load_objects('2');
		if(in_array('objects', $include) || in_array('all', $include))				$object['objects']['items']				 = $object['objects']['items'] ?? $library->load_objects('1');

		return apply_filters('revslider_get_full_library', $object, $include, $tmp_slide_uid, $refresh_from_server, $get_static_slide, $this);
	}
	
	
	/**
	 * get the short library with categories and how many elements exist
	 * @return array only the category lists of the library, for the filter bar
	 **/
	public function get_short_library($sliders = false){
		$template = new RevSliderTemplate();
		/* @var RevSliderObjectLibrary $library */
		$library = RevSliderGlobals::instance()->get('RevSliderObjectLibrary');
		$sliders = ($sliders === false) ? $this->get_slider_overview() : $sliders;
		
		$slider_cat = [];
		foreach($sliders ?? [] as $slider){
			$tags = $this->get_val($slider, 'tags', []);
			foreach($tags ?? [] as $tag){
				if(trim($tag) !== '' && !isset($slider_cat[$tag])) $slider_cat[$tag] = ucwords($tag);
			}
		}

		$m_templates = $template->get_template_categories();
		$svgs		 = $library->get_svg_categories();
		$icons		 = $library->get_font_tags();
		//walk the objects array once (bucketed per type) instead of calling get_objects_categories() 4x
		$obj_cats	 = $library->get_all_objects_categories();
		$objects	 = $this->get_val($obj_cats, '1', []);
		asort($m_templates);
		asort($slider_cat);
		asort($svgs);
		asort($icons);
		asort($objects);
		$tags		 = [
			'moduletemplates' => ['tags' => $m_templates],
			'modules'	=> ['tags' => $slider_cat],
			'svgs'		=> ['tags' => $svgs],
			'icons'		=> ['tags' => $icons],
			'layers'	=> ['tags' => $this->get_val($obj_cats, '4', [])],
			'videos'	=> ['tags' => $this->get_val($obj_cats, '3', [])],
			'images'	=> ['tags' => $this->get_val($obj_cats, '2', [])],
			'objects'	=> ['tags' => $objects]
		];

		$custom = $library->get_custom_tags();
		foreach($custom ?? [] as $tag_name => $tag_value){
			$tags[$tag_name] = ['tags' => $tag_value];
		}
		
		return apply_filters('revslider_get_short_library', $tags, $library, $this);
	}
	
	/**
	 * get the elements library
	 * @return array
	 **/
	public function get_elements_library(){
		/* @var RevSliderObjectLibrary $library */
		$library = RevSliderGlobals::instance()->get('RevSliderObjectLibrary');
		$icons	 = $library->get_font_tags();
		asort($icons);
		$tags = [
			'icons'	    => ['tags' => $icons],
			'layers'	=> ['tags' => $library->get_objects_categories('4')],
			'videos'	=> ['tags' => $library->get_objects_categories('3')],
			'images'	=> ['tags' => $library->get_objects_categories('2')]
		];
		return $tags;
	}
	
	/**
	 * Get Sliders data for the overview page
	 * @return array
	 **/
	public function get_slider_overview(){
		global $SR_GLOBALS;
		$SR_GLOBALS['data_init'] = false;

		$rs_folder	= new RevSliderFolder();
		$rs_slider	= new RevSliderSlider();
		
		$_sliders	= $rs_slider->get_sliders(false);
		$folders	= $rs_folder->get_folders();		
		$_sliders 	= array_merge($_sliders, $folders);
		$data		= [];
		
		$updv6		= new RevSliderPluginUpdateV6();
		$_slider_ids_v6 = $updv6->slider_v6_has_no_v7(); //check if v6 sliders are not migrated properly
		
		$_sliders_v6 = [];
		if(!empty($_slider_ids_v6)){
			$SR_GLOBALS['v6'] = true;
			foreach($_slider_ids_v6 as $v6_slider_id){
				$rs_slider	= new RevSliderSlider();
				$rs_slider->init_by_id($v6_slider_id);
				$_sliders_v6[]	= $rs_slider;
			}
			$SR_GLOBALS['v6'] = false;
		}

		$slider_combined = [
			['sliders' => $_sliders, 'type' => 'v7', 'ids' => 'all'],
			['sliders' => $_sliders_v6, 'type' => 'v6', 'ids' => $_slider_ids_v6]
		];

		foreach($slider_combined as $sliders){
			if(empty($sliders['sliders'])) continue;
			
			$SR_GLOBALS['v6'] = ($sliders['type'] === 'v6') ? true : false;

			$rs_slide	= new RevSliderSlide();
			$_slides_raw = $rs_slide->get_all_slides_raw($sliders['ids']);
			$slides_raw = $this->get_val($_slides_raw, 'first_slides', []);
			$slides_ids = $this->get_val($_slides_raw, 'slide_ids', []);
			
			foreach($sliders['sliders'] ?? [] as $k => $slider){
				$slide_ids	= [];
				$slides		= [];
				$sid		= $slider->get_id();
				foreach($slides_raw ?? [] as $s => $r){
					if($r->get_slider_id() !== $sid) continue;
					
					foreach($slides_ids as $_s => $_sv){
						if($this->get_val($_sv, 'slider_id') === $sid){
							$slide_ids[] = $this->get_val($_sv, 'id');
							unset($slides_ids[$_s]);
						}
					}
					$slides[] = $r;
					unset($slides_raw[$s]);
				}
				if(empty($slide_ids)) $slide_ids = false;
				
				$slides = (empty($slides)) ? false : $slides;
				
				$slider->init_layer = false;
				if($SR_GLOBALS['v6']){
					$_slider = $slider->get_overview_data_v6(false, $slides, $slide_ids); 
				}else{
					$_slider = $slider->get_overview_data(false, $slides, $slide_ids); 
				}
				$data[] = $_slider;
				unset($sliders[$k]);
			}
		}

		$SR_GLOBALS['v6'] = false;
		$SR_GLOBALS['data_init'] = true;
		
		return $data;
	}
	
	
	/**
	 * insert custom animations
	 * @return int|mixed the new animation id
	 */
	public function insert_animation($animation, $type){
		$handle = $this->get_val($animation, 'name', false);
		$result = false;
		
		if($handle !== false && trim($handle) !== ''){
			global $wpdb;
			
			//check if handle exists
			$arr = [
				'handle'	=> $this->get_val($animation, 'name'),
				'params'	=> json_encode($animation),
				'settings'	=> $type
			];
			
			$result = $wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS, $arr);
		}

		return ($result) ? $wpdb->insert_id : $result;
	}
	
	
	/**
	 * update custom animations
	 * @return int|mixed
	 */
	public function update_animation($animation_id, $animation, $type){
		global $wpdb;
		
		$arr = [
			'handle'	=> $this->get_val($animation, 'name'),
			'params'	=> json_encode($animation),
			'settings'	=> $type
		];
		
		$result = $wpdb->update($wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS, $arr, ['id' => $animation_id]);
		
		return ($result) ? $animation_id : $result;
	}
	
	
	/**
	 * delete custom animations
	 * @param int $animation_id
	 * @return int|false
	 */
	public function delete_animation($animation_id){
		global $wpdb;
		
		return $wpdb->delete($wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS, ['id' => $animation_id]);
	}
	
	
	/**
	 * @since: 5.3.0
	 * create a page with revslider shortcodes included
	 * @return int the id of the created page
	 **/
	public static function create_slider_page($added, $modals = [], $additions = []){
		global $wp_version;
		
		$new_page_id = 0;
		
		if(!is_array($added)) return apply_filters('revslider_create_slider_page', $new_page_id, $added);
		
		$f = RevSliderGlobals::instance()->get('RevSliderFunctions');
		$content = '';
		
		//get alias of all new Sliders that got created and add them as a shortcode onto a page
		foreach($added ?? [] as $sid){
			$slider = new RevSliderSlider();
			$slider->init_by_id($sid);
			$alias = $slider->get_alias();
			if(empty($alias)) continue;
			
			$usage		= isset($modals[$sid]) ? ' usage="modal"' : '';
			$addition	= (isset($additions[$sid])) ? ' ' . $additions[$sid] : '';
			if(strpos($addition, 'usage=\"modal\"') !== false) $usage = ''; //remove as not needed two times
			
			if(version_compare($wp_version, '5.0', '>=')){ //add gutenberg code
				$ov_data = $slider->get_overview_data();
				$title	 = $slider->get_val($ov_data, 'title', '');
				$img	 = $slider->get_val($ov_data, ['bg', 'src'], '');
				$wrap_addition	= ($img !== '') ? ',"sliderImage":"'.$img.'"' : '';
				$div_addition	= ($title !== '') ? ' data-slidertitle="'.$title.'"' : '';
				
				$zindex_pos = strpos($addition, 'zindex=\"');
				if($zindex_pos !== false){
					$zindex = substr($addition, $zindex_pos + 9, strpos($addition, '\"', $zindex_pos + 9) - ($zindex_pos + 9));
					$div_addition .= ' style="z-index:'.$zindex.';"';
					$wrap_addition .= ',"zindex":"'.$zindex.'"';
				}

				$div_addition .= ' data-modal="'.(empty($usage) ? 'false' : 'true').'"';
				
				$content .= '<!-- wp:themepunch/revslider {"checked":true'.$wrap_addition.'} -->'."\n";
				$content .= '<div class="wp-block-themepunch-revslider revslider" '.$div_addition.'>';
			}
			
			$content .= '[rev_slider alias="'.$alias.'"'.$usage.$addition.'][/rev_slider]'; //this way we will reorder as last comes first
			
			if(version_compare($wp_version, '5.0', '>=')){ //add gutenberg code
				$content .= '</div>'."\n".'<!-- /wp:themepunch/revslider -->'."\n";
			}
		}
		
		if($content !== ''){
			$page_id = $f->get_options(['other', 'page-id'], 1);
			$new_page_id = wp_insert_post(
				[
					'post_title'    => wp_strip_all_tags('RevSlider Page '.$page_id), //$title
					'post_content'  => $content,
					'post_type'   	=> 'page',
					'post_status'   => 'draft',
					'page_template' => '../public/views/revslider-page-template.php'
				]
			);
			
			if(is_wp_error($new_page_id)) $new_page_id = 0; //fallback to 0
			
			$page_id++;
			$f->update_option(['other', 'page-id'], $page_id);
		}
		
		return apply_filters('revslider_create_slider_page', $new_page_id, $added);
	}
	
	/**
	 * add notices from ThemePunch
	 * @since: 4.6.8
	 * @return array
	 */
	public function get_notices(){
		$_n = [];
		$notices = (array)$this->get_options(['overview', 'notices'], []);
		$rs_valid = $this->_truefalse($this->get_options(['system', 'valid'], 'false'));
		if(empty($notices)) return $_n;

		$n_discarted = $this->get_options(['overview', 'notices-dc'], []);
		foreach($notices ?? [] as $notice){
			if(in_array($notice->code, $n_discarted)) continue;
			if(isset($notice->version) && version_compare($notice->version, RS_REVISION, '<=')) continue;
			if(isset($notice->registered)){ //if this is set, only show the notice if the plugin state is the same
				$registered = $this->_truefalse($notice->registered);
				if($registered !== $rs_valid) continue;
			}
			if(isset($notice->show_until) && $notice->show_until !== '0000-00-00 00:00:00'){
				if(strtotime($notice->show_until) < time()) continue;
			}

			$_n[] = $notice;
		}
		return $_n;
	}
	
	/**
	 * returns an object of current system values
	 * @return array each requirement with its current and required value
	 **/
	public function get_system_requirements(){
		global $wpdb;
		$dir	= wp_upload_dir();
		$basedir = $this->get_val($dir, 'basedir').'/';
		$ml		= ini_get('memory_limit');
		$mlb	= wp_convert_hr_to_bytes($ml);
		$umf	= ini_get('upload_max_filesize');
		$umfb	= wp_convert_hr_to_bytes($umf);
		$pms	= ini_get('post_max_size');
		$pmsb	= wp_convert_hr_to_bytes($pms);
		$map	= $wpdb->get_row("SHOW VARIABLES LIKE 'max_allowed_packet';");
		$map	= $this->get_val($map, 'Value', 0);
		

		$mlg  = ($mlb >= 268435456) ? true : false;
		$umfg = ($umfb >= 33554432) ? true : false;
		$pmsg = ($pmsb >= 33554432) ? true : false;
		$mapg = ($map >= 16777216) ? true : false;
		
		return [
			'memory_limit' => [
				'has' => size_format($mlb),
				'min' => size_format(268435456),
				'good'=> $mlg
			],
			'upload_max_filesize' => [
				'has' => size_format($umfb),
				'min' => size_format(33554432),
				'good'=> $umfg
			],
			'post_max_size' => [
				'has' => size_format($pmsb),
				'min' => size_format(33554432),
				'good'=> $pmsg
			],
			'max_allowed_packet' => [
				'has' => size_format($map),
				'min' => size_format(16777216),
				'good'=> $mapg
			],
			'upload_folder_writable'	=> wp_is_writable($basedir),
			'zlib_enabled'				=> function_exists('gzcompress') && function_exists('gzuncompress'),
			'object_library_writable'	=> wp_image_editor_supports(['methods' => ['resize', 'save']]),
			'server_connect'			=> $this->get_options(['system', 'connect'], false),
		];
	}
	
	/**
	 * import a media file uploaded through the browser to the media library
	 * @return array ['id' => attachment id] or ['error' => message]
	 **/
	public function import_upload_media(){
		global $SR_GLOBALS;
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		
		global $wp_filesystem;
		WP_Filesystem();
		
		$import_file = $this->get_val($_FILES, 'import_file');
		$error		 = $this->get_val($import_file, 'error');
		$return		 = ['error' => __('File not found', 'revslider')];
		
		switch($error){
			case UPLOAD_ERR_NO_FILE:
				return ['error' => __('No file sent', 'revslider')];
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return ['error' => __('Exceeded filesize limit', 'revslider')];
			default:
			break;
		}
		
		$path = $this->get_val($import_file, 'tmp_name');
		if(isset($path['error'])) return ['error' => $path['error']];
		
		if(file_exists($path) == false) return ['error' => __('File not found', 'revslider')];
		if($this->get_val($import_file, 'size') > wp_max_upload_size()) return ['error' => __('Exceeded filesize limit', 'revslider')];

		//sanitize_file_name() on top of basename(): basename() only strips directories, it leaves spaces,
		//control characters and unicode in place. wp_handle_upload() normalises here too.
		$file_name	= sanitize_file_name(basename($this->get_val($import_file, 'name')));
		if($file_name === '') return ['error' => __('Invalid file name', 'revslider')];

		$mime_types = array_merge($this->get_val($SR_GLOBALS, ['mime_types', 'image']), $this->get_val($SR_GLOBALS, ['mime_types', 'video']));
		$mime_types = apply_filters('revslider_import_media_allowed_mimes', $mime_types, $file_name);
		//mime_content_type() needs ext/fileinfo. Without it the call returns false and the check below would
		//silently fall back to trusting the file extension alone, so use WP's own detection as a second source.
		$file_mime	= function_exists('mime_content_type') ? mime_content_type($path) : false;
		if($file_mime === false){
			$wp_mime	= wp_get_image_mime($path);
			$file_mime	= ($wp_mime !== false) ? $wp_mime : '';
		}
		$file_type	= wp_check_filetype($file_name, $mime_types);
		if(!in_array($file_mime, $mime_types, true)){
			if(!empty($file_type['type']) && in_array($file_type['type'], $mime_types, true)) $file_mime = $file_type['type'];
			else return ['error' => __('WordPress doesn\'t allow this filetype', 'revslider')];
		}
		if($this->get_val($file_type, 'ext', false) === false || $this->get_val($file_type, 'type', false) === false) return ['error' => __('WordPress doesn\'t allow this filetype', 'revslider')];
		
		//sanitize the SVG while it is still the temporary upload, not after it has been moved into the public
		//uploads folder - otherwise the unfiltered file is reachable over HTTP for as long as the sanitizing
		//takes. PHP removes the temp file on its own if we bail out here.
		if($file_mime === 'image/svg+xml'){
			if(!class_exists('RevSliderSvgSanitizer')){
				require_once(RS_PLUGIN_PATH . 'admin/includes/svg_sanitizer/subject.class.php');
				require_once(RS_PLUGIN_PATH . 'admin/includes/svg-sanitizer.class.php');
			}
			$sanitizer	= new RevSliderSvgSanitizer();
			$clean		= $sanitizer->sanitize($wp_filesystem->get_contents($path));
			if(empty($clean) || !$wp_filesystem->put_contents($path, $clean, FS_CHMOD_FILE)){
				return ['error' => __('SVG could not be sanitized', 'revslider')];
			}
		}

		$upload_dir = wp_upload_dir();
		//seed the collision loop with the real target. It used to start from $path, the *temporary* upload,
		//which exists by definition - so the counter always fired once and every single upload was stored as
		//"1-<name>", even when nothing collided.
		$new_path	= trailingslashit($upload_dir['path']) . $file_name;
		$i			= 0;
		while(file_exists($new_path)){
			$i++;
			$new_path = trailingslashit($upload_dir['path']) . $i . '-' . $file_name;
		}

		if(!move_uploaded_file($path, $new_path)) return $return;

		$upload_id = wp_insert_attachment(
			[
				'guid'			 => $new_path, 
				'post_mime_type' => $file_mime,
				'post_title'	 => preg_replace( '/\.[^.]+$/', '', $file_name),
				'post_name'		 => sanitize_title_with_dashes(str_replace('_', '-', $file_name)),
				'post_content'	 => '',
				'post_status'	 => 'inherit'
			],
			$new_path
		);
		
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		
		@wp_update_attachment_metadata($upload_id, wp_generate_attachment_metadata($upload_id, $new_path));
		
		$img_dim = @wp_get_attachment_image_src($upload_id, 'full');
		$width	 = ($img_dim !== false) ? $this->get_val($img_dim, 1, '') : '';
		$height	 = ($img_dim !== false) ? $this->get_val($img_dim, 2, '') : '';
		
		return ['error' => false, 'id' => $upload_id, 'path' => wp_get_attachment_url($upload_id), 'width' => $width, 'height' => $height]; //$new_path
	}
	
	
	/**
	 * Create Multilanguage for JavaScript
	 * @return array the translation strings handed to the editor JS
	 */
	public function get_javascript_multilanguage(){
		//All SR7.LANG strings live in one place now: admin/includes/i18n-strings.php ($manual + $generated).
		$lang = (array) include RS_PLUGIN_PATH . 'admin/includes/i18n-strings.php';
		return apply_filters('revslider_get_javascript_multilanguage', $lang);
	}
	
	/**
	 * returns all image sizes that have the same aspect ratio, rounded on the second
	 * @since: 6.1.4
	 * @return array
	 **/
	public function get_same_aspect_ratio_images($images){
		$return = [];
		$images = (array)$images;
		/* @var RevSliderObjectLibrary $objlib */
		$objlib = RevSliderGlobals::instance()->get('RevSliderObjectLibrary');
		$upload_dir = wp_upload_dir();
		
		foreach($images ?? [] as $key => $image){
			//check if we are from object library
			if($objlib->_is_object($image)){
				$_img = $image;
				$image = $objlib->get_correct_size_url($image, 100, true);
				$objlib->_check_object_exist($image); //check to redownload if not downloaded yet
				
				$sizes = $objlib->get_sizes();
				$return[$key] = [];
				
				if(empty($sizes)) continue;
					
				foreach($sizes ?? [] as $size){
					$url = $objlib->get_correct_size_url($image, $size);
					$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url);
					$_size = getimagesize($file);
					$return[$key][$size] = [
						'url'	=> $url,
						'width'	=> $this->get_val($_size, 0),
						'height'=> $this->get_val($_size, 1),
						'size'	=> filesize($file)
					];
					
					if($_img === $url) $return[$key][$size]['default'] = true;
				}
				
				//$image = $objlib->get_correct_size_url($image, 100, true);
				$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image);
				$_size = getimagesize($file);
				$return[$key][100] = [
					'url'	=> $image,
					'width'	=> $this->get_val($_size, 0),
					'height'=> $this->get_val($_size, 1),
					'size'	=> filesize($file)
				];
				if($_img === $return[$key][100]['url']) $return[$key][100]['default'] = true;
			}else{
				$_img = (intval($image) === 0) ? $this->get_image_id_by_url($image) : $image;
				$img_data = wp_get_attachment_metadata($_img);
				
				if(empty($img_data)) continue;
				if(intval($this->get_val($img_data, 'width', 1)) === 0 || intval($this->get_val($img_data, 'height', 1)) === 0) continue;
				$return[$key] = [];
				$ratio = round($this->get_val($img_data, 'width', 1) / $this->get_val($img_data, 'height', 1), 2);
				$sizes = $this->get_val($img_data, 'sizes', []);
				$file = $upload_dir['basedir'] .'/'. $this->get_val($img_data, 'file');
				$return[$key]['orig'] = [
					'url'	=> $upload_dir['baseurl'] .'/'. $this->get_val($img_data, 'file'),
					'width'	=> $this->get_val($img_data, 'width'),
					'height'=> $this->get_val($img_data, 'height'),
					'size'	=> filesize($file)
				];
				if($image === $return[$key]['orig']['url']) $return[$key]['orig']['default'] = true;
			
				foreach($sizes ?? [] as $sn => $sv){
					if(intval($this->get_val($sv, 'width', 1)) === 0 || intval($this->get_val($sv, 'height', 1)) === 0) continue;
					$_ratio = round($this->get_val($sv, 'width', 1) / $this->get_val($sv, 'height', 1), 2);
					if($_ratio !== $ratio) continue;
						
					$i = wp_get_attachment_image_src($_img, $sn);
					if($i === false) continue;
					
					$file = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $this->get_val($i, 0));
					$return[$key][$sn] = [
						'url'	=> $this->get_val($i, 0),
						'width'	=> $this->get_val($sv, 'width'),
						'height'=> $this->get_val($sv, 'height'),
						'size'	=> filesize($file)
					];
					if($image === $return[$key][$sn]['url']) $return[$key][$sn]['default'] = true;
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * get all available languages from Slider Revolution
	 * @return array
	 **/
	public function get_available_languages(){
		$lang_codes = [
			'de_DE' => __('German', 'revslider'),
			'en_US' => __('English', 'revslider'),
			'fr_FR' => __('French', 'revslider'),
			'zh_CN' => __('Chinese', 'revslider')
		];
		
		$lang = get_available_languages(RS_PLUGIN_PATH.'languages/');
		$_lang = [];
		foreach($lang ?? [] as $k => $v){
			if(strpos($v, 'revsliderhelp-') !== false) continue;
			
			$_lc = str_replace('revslider-', '', $v);
			$_lang[$_lc] = (isset($lang_codes[$_lc])) ? $lang_codes[$_lc] : $_lc;
		}
		
		return $_lang;
	}

	/**
	 * function to check if the current page is a post/page in edit mode
	 * @return bool are we on a post/widget edit screen?
	 */
	public function is_edit_page(){
		if(!is_admin()) return false;
		global $pagenow;

		return in_array($pagenow, ['post.php', 'post-new.php', 'widgets.php']);
	}

	/**
	 * check if the WPML plugin is currently active
	 * @return bool
	 */
	public function is_wpml_active(){
		return defined('ICL_SITEPRESS_VERSION') || did_action('wpml_loaded');
	}

	/**
	 * get the active WPML languages for the editor, keyed by language code.
	 * returns an empty array when WPML is not active, so callers can skip output.
	 * used by the AI Translate add-on to auto-detect WPML and read its language list.
	 * @return array
	 */
	public function get_wpml_editor_languages(){
		if(!$this->is_wpml_active()) return [];

		$languages = apply_filters('wpml_active_languages', null, []);
		if(empty($languages) || !is_array($languages)) return [];

		$list = [];
		foreach($languages as $code => $data){
			if($code === 'all') continue;
			$list[$code] = [
				'title'  => $this->get_val($data, 'native_name', $this->get_val($data, 'translated_name', $code)),
				'active' => (bool)$this->get_val($data, 'active', false),
			];
		}

		return $list;
	}

}
