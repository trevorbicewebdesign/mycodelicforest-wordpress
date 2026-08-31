<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Slider import from a .zip package.
 *
 * Unpacks the archive, recreates slider, slides, layer animations and navigation skins, and moves the
 * bundled media into the WordPress media library - rewriting every reference (image URLs, colour ids,
 * modal and CSS/JS ids) from the source site's ids to the ones created here. Also handles v6 packages by
 * running them through the v6 migration first.
 */
class RevSliderSliderImport extends RevSliderSlider {
	public $old_slider_alias;
	public $old_slider_id	= '';
	public $real_slider_id	= '';
	public $import_zip		= false;
	public $exists			= false;
	public $slider_raw_data	= '';
	public $slider_data		= [];
	public $slides_data		= [];
	public $imported		= [];
	public $is_template		= false;
	public $navigation_map	= [];
	public $file_names		= ['slider' => 'slider_data.json', 'static-css' => 'static-captions.css', 'navigation' => 'navigation.json', 'animation' => 'animations.json'];
	public $file_names_v6	= ['slider' => 'slider_export.txt', 'static-css' => 'styles.txt', 'navigation' => 'navigation.txt', 'animation' => 'custom_animations.txt'];
	public $remove_path;
	public $download_path;
	public $slider_id;
	public $mode			= 7;

	public function __construct(){
		parent::__construct();
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		
		$slider_id = $this->get_post_var('sliderid');
		if(empty($slider_id)){
			$data		= $this->get_request_var('data', '', false);
			$slider_id	= $this->get_val($data, 'sliderid');
		}
		$this->download_path	= $this->get_temp_path('rstemp');
		$this->remove_path		= $this->download_path;
		$this->slider_id		= $slider_id;
		$this->exists			= !empty($this->slider_id);
	}
	
	/**
	 * import slider from multipart form
	 * @return array ['success' => bool, …] with the new slider id, or an error message
	 */
	public function import_slider($update_animation = true, $exact_filepath = false, $is_template = false, $single_slide = false, $update_navigation = true){ //single_slide needed for download comp from themes
		global $wpdb;

		WP_Filesystem();
		
		try{
			if($this->exists) $this->init_by_id($this->slider_id);
			
			if($this->inited === false){
				$this->slider_id = '';
				// check if we have a zip file for non-completed import
				$import_file = get_option('revslider-import-file', '');
				if (!empty($import_file)){
					$exact_filepath = $import_file;
				}
				$exec = $this->unzip_slider($exact_filepath);
				if($exec !== true){
					$this->clear_files();
					return $exec;
				}
				$this->exists = false;
			}
			
			$this->is_template = $is_template;
			
			//read all files needed
			$error = $this->check_template();
			
			if(is_array($error)) return $error;
			
			$this->set_slider_data_raw();
			$this->set_animations();
			
			$this->set_navigations($update_navigation);
			$this->process_slider_raw_data();
			if($this->exists && $this->mode === 7) $this->delete_all_slides(); //delete current slides
			
			$this->process_slide_data();
			$this->process_layer_data();
			
			if($this->mode === 6){
				$upgrade = new RevSliderPluginUpdateV6();
				$upgrade->set_import(true);
				$upgrade->set_mode('object');
				$slider = new RevSliderSliderImport();
				$slider->init_by_data($this->slider_data);
				$slider->set_slides($this->slides_data);
				if(!empty($this->slider_data['static_slides'])){
					$slider->_static_slide = new RevSliderSlide();
					$slider->_static_slide->static_slide = true;
					$slider->_static_slide->init_by_data($this->slider_data['static_slides']);
				}

				$slider = $upgrade->upgrade_slider_to_latest_v6($slider);
				$slider->slider_id	= $slider->get_id();
				$slider->map		= $this->map;

				$slider->v6_update_modal_ids();
				$slider->v6_update_css_and_javascript_ids($this->old_slider_id, $this->slider_id, $this->map);
				$slider->update_color_ids($this->map, false);
				
				if($is_template !== false) $slider->set_param('pakps', true);
			}else{ //V7
				//do the update routines
				$slider = new RevSliderSliderImport();
				$slider->init_by_id($this->slider_id);
				
				$upgrade = new RevSliderPluginUpdate();
				$upgrade->set_import(true);
				$upgrade->upgrade_slider_to_latest($slider);
				
				//reinit because we just updated data which is outside of the $slider object
				$slider = new RevSliderSliderImport();
				$slider->init_by_id($this->slider_id);
				
				$slider->update_modal_ids([$this->old_slider_id => $this->slider_id], $this->map);
				$slider->update_css_and_javascript_ids($this->old_slider_id, $this->slider_id, $this->map);
				$slider->update_color_ids($this->map);

				if($is_template !== false) $slider->update_params(['prem' => true]);
			}

			//add a static slide if no such found in imported slider
			$new_static = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . RevSliderFront::TABLE_SLIDES ." WHERE slider_id = %s AND static = 1", $this->slider_id), ARRAY_A);
			if(!$new_static){
				$new_slide = new RevSliderSlide();
				$new_slide->create_slide($this->slider_id, '', true);
			}

			$this->real_slider_id = $this->slider_id;

			$this->clear_files();

			do_action('revslider_slider_imported', $this->slider_id);
			
			return [
				'success' => true,
				'sliderID' => $this->slider_id,
				'map' => [
					'slider' => [
						'zip_to_template' => [$this->old_slider_id => $this->slider_id], //zip id to template id
						'aliases'         => [$this->old_slider_alias => $this->alias],
					],
					'slides' => $this->map
				],
				'v6' => ($this->mode === 6) ? $upgrade->prepare_upgrade_return_data($slider) : false
			];

		}catch(Exception $e){
			$this->clear_files();
			
			return ['success' => false, 'error' => $e->getMessage(), 'sliderID' => $this->slider_id];
		}
	}
	
	
	/**
	 * unzip an uploaded Slider
	 * @param mixed $exact_filepath
	 * @throws Exception
	 * @return mixed
	 */
	public function unzip_slider($exact_filepath = false){
		if($exact_filepath !== false){
			$path = $exact_filepath;
		}else{
			$import_file = $this->get_val($_FILES, 'import_file');
			$error		 = $this->get_val($import_file, 'error');
			if($error === UPLOAD_ERR_NO_FILE) $this->throw_error(__('No file sent.', 'revslider'));
			if(in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE])) $this->throw_error(__('Exceeded filesize limit.', 'revslider'));

			$path = $this->get_val($import_file, 'tmp_name');
		}
		
		if(is_array($path) && isset($path['error'])) $this->throw_error($path['error']);
		if(file_exists($path) == false) $this->throw_error(__('Import file not found', 'revslider'));
		
		WP_Filesystem();
		
		$this->check_bad_files($path);

		$file = unzip_file($path, $this->download_path);
		if(is_wp_error($file)){
			@define('FS_METHOD', 'direct'); //lets try direct.
			WP_Filesystem();  //WP_Filesystem() needs to be called again since now we use direct!
			
			$file = unzip_file($path, $this->download_path);
			if(is_wp_error($file)){
				$this->download_path = RS_PLUGIN_PATH.'rstemp/';
				$this->remove_path	 = $this->download_path;
				$file				 = unzip_file($path, $this->download_path);
				
				if(is_wp_error($file)){
					$this->download_path = str_replace(basename($path), '', $path);
					$file				 = unzip_file($path, $this->download_path);
				}
			}
		}
		
		if(is_wp_error($file)){
			$this->clear_files();
			return ['success' => false, 'error' => $file->get_error_message()];
		}
		
		$this->import_zip = true;

		return true;
	}
	
	
	/**
	 * set the Slider data in raw from the json
	 * @return void
	 **/
	public function set_slider_data_raw(){
		global $wp_filesystem;

		$this->slider_raw_data = ($wp_filesystem->exists($this->download_path.$this->file_names['slider'])) ? $wp_filesystem->get_contents($this->download_path.$this->file_names['slider']) : '';
		
		if(empty($this->slider_raw_data)){
			$this->slider_raw_data = ($wp_filesystem->exists($this->download_path.$this->file_names_v6['slider'])) ? $wp_filesystem->get_contents($this->download_path.$this->file_names_v6['slider']) : '';
			if(!empty($this->slider_raw_data)) $this->mode = 6; //we are v6
		}
		
		if(empty($this->slider_raw_data)) $this->throw_error(__('slider_data.json does not exist in root of zip!', 'revslider'));
	}
	
	
	/**
	 * set the Slider animations from custom_animations.txt and add/update them if needed in the database
	 * @return void
	 **/
	public function set_animations(){
		global $wp_filesystem, $wpdb;
		if($this->mode === 6){
			$animations	= ($wp_filesystem->exists($this->download_path.$this->file_names_v6['animation'])) ? $wp_filesystem->get_contents($this->download_path.$this->file_names_v6['animation']) : '';
		}else{
			$animations	= ($wp_filesystem->exists($this->download_path.$this->file_names['animation'])) ? $wp_filesystem->get_contents($this->download_path.$this->file_names['animation']) : '';
		}
		$animations = is_string($animations) ? json_decode($animations, true) : null; //guard instead of @-suppressing the PHP 8.1 null-to-string deprecation
		if(empty($animations)) return;

		foreach($animations ?? [] as $animation){
			$exist = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS." WHERE handle = %s", $animation['handle']), ARRAY_A);
			if(!empty($exist)){ //update the animation, get the ID
				$animation_id = $exist['id'];
			}else{ //insert the animation, get the ID
				//check if we are v5 or v6+
				$an = [
					'handle' => $this->get_val($animation, 'handle'),
					'params' => stripslashes(json_encode(str_replace("'", '"', $this->get_val($animation, 'params'))))
				];

				if(in_array($this->get_val($animation, 'settings'), ['in', 'out'])){
					$an['settings'] = $this->get_val($animation, 'settings');
				}

				$wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS, $an);

				$animation_id = $wpdb->insert_id;

				//and set the current customin-oldID and customout-oldID in slider raw data to the new ID from the animation
			}

			$this->slider_raw_data = str_replace(['customin-'.$animation['id'].'"', 'customout-'.$animation['id'].'"'], ['customin-'.$animation_id.'"', 'customout-'.$animation_id.'"'], $this->slider_raw_data);
		}
	}

	/**
	 * set the Slider navigatons from navigation.txt and add/update them if needed in the database
	 * @param bool $update_navigation
	 * @return void
	 */
	public function set_navigations($update_navigation){
		global $wp_filesystem, $wpdb;
		$upd = new RevSliderPluginUpdate();
		
		if($this->mode === 6){
			$navigations = ($wp_filesystem->exists($this->download_path.$this->file_names_v6['navigation'])) ? $wp_filesystem->get_contents($this->download_path.$this->file_names_v6['navigation']) : '';
		}else{
			$navigations = ($wp_filesystem->exists($this->download_path.$this->file_names['navigation'])) ? $wp_filesystem->get_contents($this->download_path.$this->file_names['navigation']) : '';
		}
		$navigations = is_string($navigations) ? json_decode($navigations, true) : null; //guard instead of @-suppressing the PHP 8.1 null-to-string deprecation

		if(empty($navigations)) return;
		
		foreach($navigations as $navigation){
			$_navigations[] = $navigation;
			
			if(!isset($navigation['type'])){ //translate navigations to v6 if they are v5
				$_navigations = [];
				$navigation['css']		= json_decode($navigation['css'], true);
				$navigation['markup']	= json_decode($navigation['markup'], true);
				$navigation['settings']	= json_decode($navigation['settings'], true);
				
				foreach($upd->navtypes as $navtype){
					if(isset($navigation['css'][$navtype]) && !empty($navigation['css'][$navtype]) || isset($navigation['markup'][$navtype]) && !empty($navigation['markup'][$navtype])){
						$_navigations[] = $upd->create_new_navigation_6_0($navigation, $navtype);
					}
				}
			}
			
			if(empty($_navigations)) continue;
		
			foreach($_navigations as $_navigation){
				$exist = $wpdb->get_row($wpdb->prepare("SELECT id FROM ".$wpdb->prefix . RevSliderFront::TABLE_NAVIGATIONS." WHERE handle = %s AND type = %s", [$this->get_val($_navigation, 'handle'), $this->get_val($_navigation, 'type')]), ARRAY_A);
				
				$old_nav_id = $this->get_val($_navigation, 'id', false);
				
				if($old_nav_id !== false) unset($_navigation['id']);
				
				foreach($_navigation ?? [] as $v => $s){
					if(is_array($s) || is_object($s)) $_navigation[$v] = json_encode($s);
				}
				
				$rh = $_navigation['handle'];
				$rt = $_navigation['type'];
				if(!empty($exist)){ //create new navigation, get the ID
					if($update_navigation){ //overwrite navigation if exists
						unset($_navigation['handle']);

						$upd = $wpdb->update($wpdb->prefix . RevSliderFront::TABLE_NAVIGATIONS, $_navigation, ['handle' => $rh, 'type' => $rt]);
							
						$insert_id = $this->get_val($exist, 'id', $wpdb->insert_id);
					}else{
						//insert with new handle. gmdate() instead of date(): the suffix is only there to make the
						//handle unique, so it must not depend on the ambient PHP timezone
						$suffix = gmdate('is');
						$_navigation['handle'] = $_navigation['handle'].'-'.$suffix;
						$_navigation['name'] = $_navigation['name'].'-'.$suffix;
						//for prior to version 6.0 sliders, the next line needs to stay
						$this->slider_raw_data	= str_replace($rh.'"', $_navigation['handle'].'"', $this->slider_raw_data);
						//for prior to version 6.0 sliders end
						$_navigation['css'] = str_replace('.'.$rh, '.'.$_navigation['handle'], $_navigation['css']); //change css class to the correct new class
						$wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_NAVIGATIONS, $_navigation);
						$insert_id = $wpdb->insert_id;
					}
				}else{
					$wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_NAVIGATIONS, $_navigation);
					$insert_id = $wpdb->insert_id;
				}
				
				if($old_nav_id !== false) $this->navigation_map[$old_nav_id] = $insert_id;
			}
		}
	}
	
	
	/**
	 * check if the slider is a template slider and if so, check further if uid is correct
	 * @return array|true checks the package against the installed add-ons and licence
	 **/
	public function check_template(){
		global $wp_filesystem;
		
		$uid_check = ($wp_filesystem->exists($this->download_path.'info.cfg')) ? $wp_filesystem->get_contents($this->download_path.'info.cfg') : '';

		if($this->is_template !== false){
			if($uid_check != $this->is_template) return ['success' => false, 'error' => __('Please select the correct zip file, checksum failed!', 'revslider')];

			return false;
		}

		//someone imported a template base Slider, check if it is existing in Base Sliders, if yes, check if it was imported
		if($uid_check === '') return false;
	
		$tmpl		 = new RevSliderTemplate();
		$tmpl_slider = $tmpl->get_tp_template_sliders();
		
		if(empty($tmpl_slider)) return false;

		foreach($tmpl_slider ?? [] as $tp_slider){
			if($tp_slider['uid'] != $uid_check) continue;

			$this->is_template = $uid_check;
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * initialize the raw data and turn it into a Slider
	 * @return void
	 **/
	public function process_slider_raw_data(){
		global $wpdb;

		$rsa    = RevSliderGlobals::instance()->get('RevSliderAddons');
		$a2m    = $rsa->get_addons_to_migrate();
		foreach ($a2m as $old => $new) {
			$old_short = str_replace(['revslider-', '-addon'], '', $old);
			$new_short = str_replace(['revslider-', '-addon'], '', $new);
			
			$this->slider_raw_data = str_replace('"' . $old . '"', '"' . $new . '"', $this->slider_raw_data);
			$this->slider_raw_data = str_replace('"' . $old_short . '"', '"' . $new_short . '"', $this->slider_raw_data);
		}
		
		$this->slider_data = is_string($this->slider_raw_data) ? json_decode($this->slider_raw_data, true) : null; //guard instead of @-suppressing the PHP 8.1 null-to-string deprecation
		
		if(empty($this->slider_data)){
			$this->clear_files();
			$this->throw_error(__('Wrong export slider file format! Please make sure that the uploaded file is a zip file with a correct slider_data.json file.', 'revslider'));
		}
		
		//update slider params
		$params = $this->get_val($this->slider_data, 'params');
		$params['imported'] = true; //set that we are an imported slider

		//check for missing addons
		$u        = $this->mode === 6 ? 'enable' : 'u';
		$addons   = $rsa->get_addon_list(true);
		$m_addons = $this->get_val($params, 'addOns', []);
		$missing  = [];
		foreach ($m_addons as $slug => $addon ) {
			if ($this->_truefalse($addon[$u])) {
				
				// slugs from different versions are not the same
				if (strpos($slug, 'revslider-') !== 0) {
					$slug = 'revslider-' . $slug . '-addon';
				}

				$slug = str_replace(['revslider-', '-addon'], '', $slug);
				if (!isset($addons[$slug]) || !$addons[$slug]->active) {
					$missing[] = $slug;
				}
			}
		}

		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === false){
			//check if we are a premium slider
			$premium = ($this->mode === 6) ? $this->get_val($params, 'pakps', false) : $this->get_val($params, 'prem', false);
			if($premium){
				$this->clear_files();
				$this->throw_error(__('Please register your Slider Revolution plugin to import premium templates', 'revslider'));
			}
			//check if there are missing addons
			if (!empty($missing)) {
				$this->clear_files();
				$this->throw_error(__('Please register your Slider Revolution plugin to install addons used in template.', 'revslider') . '('.implode(', ', $missing).')');
			}
		}

		//attempt to install missing addons
		if (!empty($missing)) {
			
			if (!empty($_FILES['import_file']['name'])) {
				// move zip to uploads and save its name to process it after redirect
				$upload_dir = wp_upload_dir();
				$tmp_file   = $upload_dir['basedir'] . '/revslider/tmp/' . $_FILES['import_file']['name'];
				wp_delete_file( $tmp_file );
				wp_mkdir_p( dirname( $tmp_file ) );
				@move_uploaded_file( $_FILES['import_file']['tmp_name'], $tmp_file );
				update_option( 'revslider-import-file', $tmp_file );
			} elseif (!$this->is_template) {
				// not a template, no imported file and still missing addons
				// throw an error
				$this->clear_files();
				$this->throw_error(__('Unable to install addon(s) used in template.', 'revslider') . '(' . implode(', ', $missing) . ')');
			}
			
			// install addons
			foreach ($missing as $slug) {
				if (true !== $rsa->install_addon('revslider-'.$slug.'-addon')){
					$this->clear_files();
					$this->throw_error(__('Unable to install addon used in template.', 'revslider') . '('.$slug.')');
				}
			}
			
			// redirect to the import again to correctly init all freshly installed addons
			if ($this->is_template) {
				$redirect_url = admin_url( 'admin-ajax.php?action=rs_ajax_action&client_action=template.import.slider&data%5Buid%5D='.$this->is_template.'&nonce='.wp_create_nonce('revslider_actions'));
			} else {
				$redirect_url = admin_url( 'admin-ajax.php?action=rs_ajax_action&client_action=slider.import&nonce='.wp_create_nonce('revslider_actions'));
			}
			wp_safe_redirect($redirect_url);
			exit();
		}

		$title = ($this->exists) ? $this->get_title() : $this->get_val($this->slider_data, 'title', 'Slider1');
		$alias = ($this->exists) ? $this->get_alias() : $this->get_val($this->slider_data, 'alias', 'slider1');

		$this->old_slider_id    = $this->get_val($this->slider_data, 'id', '');
		$this->old_slider_alias = $alias;
		
		if($this->mode === 6){
			unset($params['layout']['bg']['imageId']);
			unset($params['troubleshooting']['alternateURLId']);
		}
		
		if ($this->is_template !== false) {
			// download all template images
			$template        = new RevSliderTemplate();
			$template_image  = $template->get_template_image_by_uid($this->is_template);
			$template_slides = $template->get_slides_images_by_uid($this->is_template);
			//set the template image as the slider thumb image
			$params['thumb'] = $template_image;
		}
		
		$params = $this->process_images($params, 'slider', $alias);
		
		//remove post and woocommerce categories
		unset($params['source']['woo']['category']);
		unset($params['source']['post']['category']);

		//remap the navigations
		if(!empty($this->navigation_map)){
			$arrows	 = $this->get_val($params, ['nav', 'arrows', 'style'], false);
			$bullets = $this->get_val($params, ['nav', 'bullets', 'style'], false);
			$thumbs	 = $this->get_val($params, ['nav', 'thumbs', 'style'], false);
			$tabs	 = $this->get_val($params, ['nav', 'tabs', 'style'], false);
			$scrubber = $this->get_val($params, ['nav', 'scrubber', 'style'], false);
			
			if(isset($this->navigation_map[$arrows]))	$this->set_val($params, ['nav', 'arrows', 'style'], $this->navigation_map[$arrows]);
			if(isset($this->navigation_map[$bullets]))	$this->set_val($params, ['nav', 'bullets', 'style'], $this->navigation_map[$bullets]);
			if(isset($this->navigation_map[$thumbs]))	$this->set_val($params, ['nav', 'thumbs', 'style'], $this->navigation_map[$thumbs]);
			if(isset($this->navigation_map[$tabs]))		$this->set_val($params, ['nav', 'tabs', 'style'], $this->navigation_map[$tabs]);
			if(isset($this->navigation_map[$scrubber]))	$this->set_val($params, ['nav', 'scrubber', 'style'], $this->navigation_map[$scrubber]);
		}
		
		//update slider or create new
		if($this->exists){
			if($this->mode === 7){
				$wpdb->update($wpdb->prefix . RevSliderFront::TABLE_SLIDER, ['title' => $title, 'alias' => $alias, 'params' => json_encode($params)], ['id' => $this->slider_id]);
			}

			$this->title = $title;
			$this->alias = $alias;
		}else{
			//new slider
			//check if Slider with title and/or alias exists, if yes change both to stay unique
			$insert = ['title' => $title, 'alias' => $alias];
			$ti = 1;
			while($this->alias_exists($insert['alias'])){ //set a new alias and title if its existing in database
				$params['alias'] = $insert['alias'] = $alias . $ti;
				$params['title'] = $insert['title'] = $title . $ti;
				$ti++;
			}

			$params['uid']	= $this->is_template;

			$insert['settings'] = $this->get_val($this->slider_data, 'settings', []);
			if($this->get_val($insert, ['settings', 'version'], false) === false){
				$this->set_val($insert, ['settings', 'version'], $this->get_val($params, 'version', '1.0.0'));
			}

			$insert['settings'] = json_encode($insert['settings']);
			$insert['params'] = json_encode($params);

			if($this->mode === 6){
				//only create raw data, without $insert['params'] in the database, as they are not yet v7
				$this->slider_data['settings'] = $insert['settings'];
				$this->slider_data['params'] = $insert['params'];
				unset($insert['settings']);
				unset($insert['params']);
			}

			$wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_SLIDER, $insert);
			$this->slider_id = $wpdb->insert_id;
			if($this->mode === 6) $this->slider_data['id'] = $this->slider_id;

			$this->slider_data['title'] = $this->title = $insert['title'];
			$this->slider_data['alias'] = $this->alias = $insert['alias'];
		}

		//allow for updating the slider params
		$d = ['params' => $params, 'sliderParams' => $this->slider_data, 'imported' => $this->imported];
		$d = apply_filters('revslider_importSliderFromPost_modify_slider_data', $d, $this->download_path, $this);

		$params				= $d['params'];
		$this->slider_data	= $d['sliderParams'];
		$this->imported		= $d['imported'];
		if($this->mode === 7){
			$wpdb->update($wpdb->prefix . RevSliderFront::TABLE_SLIDER, ['params' => json_encode($params)], ['id' => $this->slider_id]);
		}
	}
	

	/**
	 * process the slide data, mapping and layers
	 * @return bool
	 **/
	public function process_slide_data(){
		$this->slides_data = $this->get_val($this->slider_data, 'slides');
		if(empty($this->slides_data)) return false;

		global $wpdb;

		$template	= new RevSliderTemplate();
		$alias		= $this->get_val($this->slider_data, 'alias');

		if($this->mode === 6){
			$static_slide = $this->get_val($this->slider_data, 'static_slides');
			if(!empty($static_slide)){
				$this->slides_data['static'] = (isset($static_slide[0])) ? $static_slide[0] : $static_slide;
				$this->slides_data['static']['static'] = 1;
				$static_id = $this->get_val($this->slides_data, ['static', 'id']);
				if(empty($static_id)) $this->set_val($this->slides_data, ['static', 'id'] , $this->slider_data['id'].'STATIC');
			}
		}

		foreach($this->slides_data as $slide_key => $slide){
			$params		= $this->get_val($slide, 'params');
			$layers		= $this->get_val($slide, 'layers', []);
			$settings	= $this->get_val($slide, 'settings', '');
			$static		= $this->get_val($slide, 'static');
			$id_in_params = $this->get_val($params, 'id');
			
			if($this->import_zip === true){ //we have a zip, check if exists
				if($this->mode === 6){
					if($this->get_val($params, ['bg', 'imageId'], false) !== false) unset($params['bg']['imageId']);
					if($this->get_val($params, ['thumb', 'customThumbSrcId'], false) !== false) unset($params['thumb']['customThumbSrcId']);
					if($this->get_val($params, ['thumb', 'customAdminThumbSrcId'], false) !== false) unset($params['thumb']['customAdminThumbSrcId']);

					//check if we are a template slider, if yes, use template slide image
					if($this->is_template !== false){
						if($this->get_val($params, ['thumb', 'customThumbSrc'], false) === false){
							if(!isset($params['thumb'])) $params['thumb'] = [];
							$params['thumb']['customThumbSrc'] = $template->get_slide_image_by_uid($this->is_template, $slide_key);
						}
						if($this->get_val($params, ['thumb', 'customAdminThumbSrc'], false) === false){
							if(!isset($params['thumb'])) $params['thumb'] = [];
							$params['thumb']['customAdminThumbSrc'] = $this->get_val($params, ['thumb', 'customThumbSrc']);
						}
					}
				}
				
				$params = $this->process_images($params, 'slides', $alias);
				$this->slides_data[$slide_key]['params'] = $params;
			}

			//convert layers images:
			if(!empty($layers)){
				foreach($layers as $layer_key => $layer){
					$layers[$layer_key]['text'] = stripslashes($this->get_val($layer, 'text'));

					if($this->import_zip !== true) continue;

					if($this->mode === 6){
						if($this->get_val($layer, ['media', 'imageId'], false) !== false) unset($layer['media']['imageId']);
						if($this->get_val($layer, ['media', 'posterId'], false) !== false) unset($layer['media']['posterId']);
						if($this->get_val($layer, ['idle', 'backgroundImageId'], false) !== false) unset($layer['idle']['backgroundImageId']);
					}

					$layers[$layer_key] = $this->process_images($layers[$layer_key], 'layers', $alias);
				}
			}

			$this->slides_data[$slide_key]['layers'] = $layers;
			
			$d = ['params' => $params, 'sliderParams' => $this->slider_data, 'layers' => $layers, 'settings' => $settings, 'imported' => $this->imported, 'static' => $static];
			$d = apply_filters('revslider_importSliderFromPost_modify_data', $d, 'normal', $this->download_path, $this);
			
			$this->slider_data = $d['sliderParams'];
			$this->imported	= $d['imported'];
			$params			= $d['params'];
			$layers			= $d['layers'];
			$settings		= $d['settings'];
			$static			= $d['static'];
			
			$my_layers	 = json_encode($layers);
			$my_layers	 = (empty($my_layers)) ? stripslashes(json_encode($layers)) : $my_layers;
			$my_params	 = json_encode($params);
			$my_params	 = (empty($my_params)) ? stripslashes(json_encode($params)) : $my_params;
			$my_settings = json_encode($settings);
			$my_settings = (empty($my_settings)) ? stripslashes(json_encode($settings)) : $my_settings;
			
			if($this->mode === 6){
				//only create raw data, without params, layers and settings, as they are not yet v7
				$ret = $wpdb->insert(
					$wpdb->prefix . RevSliderFront::TABLE_SLIDES,
					[
						'slider_id'	=> $this->slider_id,
						'slide_order' => $this->get_val($slide, 'slide_order'),
						'layers'	=> '',
						'params'	=> '',
						'settings'	=> '',
						'static'	=> $static,
					]
				);
				$this->slides_data[$slide_key]['id'] = $wpdb->insert_id;
			}else{
				//create new slide
				$ret = $wpdb->insert(
					$wpdb->prefix . RevSliderFront::TABLE_SLIDES,
					[
						'slider_id'	=> $this->slider_id,
						'slide_order' => $this->get_val($slide, 'slide_order'),
						'layers'	=> $my_layers,
						'params'	=> $my_params,
						'settings'	=> $my_settings,
						'static'	=> $static,
					]
				);
			}
			
			if(isset($slide['id']) || !empty($id_in_params)){
				$this->slides_data[$slide_key]['new_id'] = $wpdb->insert_id;
				$this->map[$slide['id']] = $wpdb->insert_id;
			}
			
			if($slide_key === 'static'){ //only occurs in v6
				//move to _static_slide!
				$this->set_val($slide, ['id'] , $wpdb->insert_id);
				$this->slider_data['static_slides'] = $this->slides_data[$slide_key];
				unset($this->slides_data[$slide_key]);
			}
		}
	}
	

	/**
	 * process layers, and update actions
	 * @return void
	 **/
	public function process_layer_data(){
		if(empty($this->map)) return;
		if(empty($this->slides_data)) return;

		return;

		foreach($this->slides_data as $slide){
			$this->_process_layer_data($slide);
		}
	}
	
	/**
	 * process layers from after 6.0
	 * @return void
	 **/
	public function _process_layer_data($slide){
		global $wpdb;
	}
	
	
	/**
	 * update the slide ids in the slider skins 
	 * @since: 6.2.3
	 * skins -> colors -> [] -> ref -> [] -> r & slide
	 * @return void
	 **/
	public function update_color_ids($map, $v7 = true){
		if(empty($map)) return;

		$skins = $this->get_param('skins', []);
		if(empty($skins)) return;
		if(!isset($skins['colors'])) return;
		if(empty($skins['colors'])) return;

		$update = false;
		foreach($skins['colors'] ?? [] as $k => $v){
			if(!isset($v['ref']) || empty($v['ref'])) continue;
		
			foreach($v['ref'] as $rk => $rv){
				$os = $this->get_val($rv, 'slide');
				
				if(!isset($map[$os])) continue;
			
				$update = true;
				$skins['colors'][$k]['ref'][$rk]['slide'] = (string)$map[$os];
				
				$r = explode('.', $this->get_val($rv, 'r'));
				if(!empty($r) && is_array($r)){
					$r[0] = $map[$os];
					$skins['colors'][$k]['ref'][$rk]['r'] = implode('.', $r);
				}
			}
		}

		if($update){
			$this->set_param('skins', $skins);
			if($v7) $this->update_params(['skins' => $skins]);
		}
	}


	/**
	 * @param  RevSliderSlide $slide
	 * @param  array          $map
	 * @return RevSliderSlide
	 */
	private function v6_update_slide_seo_link($slide, $map){
		if(version_compare($slide->get_param('version', '1.0.0'), '6.0.0', '<')) return $slide;

		$slidelink = $slide->get_param(['seo', 'slideLink'], false);
		if($slidelink !== false && isset($map[$slidelink])){
			$slide->set_param(['seo', 'slideLink'], $map[$slidelink]);
		}

		return $slide;
	}


	/**
	 * update the custom javascript section by removing the old api ID with the new api ID
	 * @return void
	 **/
	public function v6_update_css_and_javascript_ids($old_slider_id, $new_slider_id, $map){
		$js		= $this->get_param(['codes', 'javascript'], '');
		$js7	= $this->get_param(['codes', 'javascript7'], '');
		$css	= $this->get_param(['codes', 'css'], '');
		
		if(preg_match_all('/revapi[0-9]*/', $js, $results)){
			if(isset($results[0]) && !empty($results[0])){
				foreach($results[0] as $replace){
					$js = str_replace($replace, 'revapi'.$new_slider_id, $js);
				}
				$this->set_param(['codes', 'javascript'], $js);
			}
		}

		if(preg_match_all('/revapi[0-9]*/', $js7, $results)){
			if(isset($results[0]) && !empty($results[0])){
				foreach($results[0] as $replace){
					$js7 = str_replace($replace, 'revapi'.$new_slider_id, $js7);
				}
				$this->set_param(['codes', 'javascript7'], $js7);
			}
		}
		
		if(!empty($map)){
			if($css !== ''){
				$css = str_replace(
					['slider-'.$old_slider_id.'-', 'slider_'.$old_slider_id.'_', 'rrzt_'.$old_slider_id, 'rrzm_'.$old_slider_id, 'rrzb_'.$old_slider_id, '.slotholder', '.rs-background-video-layer', '.tp-static-layers', '.tp-parallax-wrap', '.rev_column_bg', '.tp-revslider-slidesli', 'active-revslide'],
					['slider-'.$new_slider_id.'-', 'slider_'.$new_slider_id.'_', 'rrzt_'.$new_slider_id, 'rrzm_'.$new_slider_id, 'rrzb_'.$new_slider_id, 'rs-sbg-wrap', 'rs-bgvideo', 'rs-static-layers', '.rs-parallax-wrap', 'rs-column-bg', 'rs-slide', 'active-rs-slide'],
					$css
				);
				
				foreach($map ?? [] as $old_slide_id => $new_slide_id){
					$css = str_replace('slide-'.$old_slide_id.'-', 'slide-'.$new_slide_id.'-', $css);
				}
				$this->set_param(['codes', 'css'], $css);
			}
			if($js !== ''){
				$js = str_replace(
					['slider-'.$old_slider_id.'-', 'slider_'.$old_slider_id.'_', 'rrzt_'.$old_slider_id, 'rrzm_'.$old_slider_id, 'rrzb_'.$old_slider_id, '.slotholder', '.rs-background-video-layer', '.tp-static-layers', 'if (obj.href!=undefined && obj.href.split("http").length<2 && obj.href!="#wp-toolbar")'],
					['slider-'.$new_slider_id.'-', 'slider_'.$new_slider_id.'_', 'rrzt_'.$new_slider_id, 'rrzm_'.$new_slider_id, 'rrzb_'.$new_slider_id, 'rs-sbg-wrap', 'rs-bgvideo', 'tp-static-layers', 'if (obj.href!=undefined && obj.href.split("http").length<2 && obj.href!="#wp-toolbar" && obj.href.split(\'./\').length<2 && obj.href.split(\'mailto:\').length<2)'],
					$js
				);
				
				foreach($map ?? [] as $old_slide_id => $new_slide_id){
					$js = str_replace('slide-'.$old_slide_id.'-', 'slide-'.$new_slide_id.'-', $js);
				}

				$this->set_param(['codes', 'javascript'], $js);
			}
			if($js7 !== ''){
				foreach($map ?? [] as $old_slide_id => $new_slide_id){
					$pattern = '/SR7_' . $old_slider_id . '_(.+?)-' . $old_slide_id . '/';
					$replacement = 'SR7_' . $new_slider_id . '_${1}-' . $new_slide_id;
					$js7 = preg_replace($pattern, $replacement, $js7);
				}

				$js7 = str_replace(['SR7_'.$old_slider_id.'_', 'SR7_'.$old_slider_id.'-'], ['SR7_'.$new_slider_id.'_', 'SR7_'.$new_slider_id.'-'], $js7);
				$this->set_param(['codes', 'javascript7'], $js7);
			}
			
			//check for all slides, if seo.slideLink needs to be changed
			foreach($this->slides ?? [] as $skey => $slide){
				$this->slides[$skey] = $this->v6_update_slide_seo_link($slide, $map);
			}
			if (!empty($this->_static_slide) && $this->_static_slide instanceof RevSliderSlide){
				$this->_static_slide = $this->v6_update_slide_seo_link($this->_static_slide, $map);
			}
		}
	}


	/**
	 * update the custom javascript section by removing the old api ID with the new api ID
	 * @return void
	 **/
	public function update_css_and_javascript_ids($old_slider_id, $new_slider_id, $map){
		$js = $this->get_param(['codes', 'js'], '');
		$css = $this->get_param(['codes', 'css'], '');
		
		$change = false;
	
		if(preg_match_all('/revapi[0-9]*/', $js, $results)){
			if(isset($results[0]) && !empty($results[0])){
				foreach($results[0] as $replace){
					$js = str_replace($replace, 'revapi'.$new_slider_id, $js);
				}
				$change = true;
			}
		}
		
		if(!empty($map)){
			if($css !== ''){
				foreach($map as $old_slide_id => $new_slide_id){
					$pattern = '/SR7_' . $old_slider_id . '_(.+?)-' . $old_slide_id . '/';
					$replacement = 'SR7_' . $new_slider_id . '_${1}-' . $new_slide_id;
					$css = preg_replace($pattern, $replacement, $css);
				}

				$css = str_replace(['SR7_'.$old_slider_id.'_', 'SR7_'.$old_slider_id.'-'], ['SR7_'.$new_slider_id.'_', 'SR7_'.$new_slider_id.'-'], $css);

				$change = true;
			}
			if($js !== ''){
				foreach($map as $old_slide_id => $new_slide_id){
					$pattern = '/SR7_' . $old_slider_id . '_(.+?)-' . $old_slide_id . '/';
					$replacement = 'SR7_' . $new_slider_id . '_${1}-' . $new_slide_id;
					$js = preg_replace($pattern, $replacement, $js);
				}

				$js = str_replace(['SR7_'.$old_slider_id.'_', 'SR7_'.$old_slider_id.'-'], ['SR7_'.$new_slider_id.'_', 'SR7_'.$new_slider_id.'-'], $js);

				$change = true;
			}
			
			//check for all slides, if seo.slideLink needs to be changed
			$this->init_layer = false;
			$slides = $this->get_slides();
			$static = $this->get_static_slide();
			if (!empty($static)) $slides[] = $static;
			foreach($slides ?? [] as $skey => $slide){
				$save_slide = false;
				$slidelink = $slide->get_param(['seo', 'slideLink'], false);
				if($slidelink !== false && isset($map[$slidelink])){
					$slide->set_param(['seo', 'slideLink'], $map[$slidelink]);
					$save_slide = true;
				}

				$old_slide_id = $slide->get_param('id');
				$slide_id = $slide->get_id();
				if(!empty($old_slide_id) || $slide_id !== $old_slide_id){
					$new_slide_id = (isset($map[$old_slide_id])) ? $map[$old_slide_id] : $slide_id;
					$slide->set_param('id', $new_slide_id);
					$save_slide = true;
				}

				if($save_slide) $slide->save_params();
			}
		}
		
		if($change === true) $this->update_params(['codes' => ['js' => $js, 'css' => $css]]);
	}
	
	
	/**
	 * import a media and return the imported path of it
	 * @param string $image
	 * @return string
	 **/
	public function import_media_from_zip($image){
		global $wp_filesystem;
		
		$media = '';
		$use_folder = ($this->mode === 6) ? 'images' : 'media';

		//import if exists in zip folder
		if(trim($image) === '' || strpos($image, 'http') !== false) return $media;
		if($this->import_zip !== true) return $media;
		//we have a zip, check if exists
		if(!$wp_filesystem->exists($this->download_path.$use_folder.'/'.$image)) return $media;

		if(!isset($this->imported[$use_folder.'/'.$image])){
			$import_image = $this->import_media($this->download_path.$use_folder.'/'.$image, $this->get_val($this->slider_data, 'alias', 'alias').'/');
			if($import_image['success']){
				$image = $import_image['path'];
				$this->imported[$use_folder.'/'.$image] = $image;
			}
		}else{
			$image = $this->imported[$use_folder.'/'.$image];
		}

		return $this->get_image_url_from_path($image);
	}

	/** @return string */
	public function process_images($params, $group = 'slider', $alias = ''){
		$use_path = ($this->mode === 6) ? $this->image_path_v6 : $this->image_path;
		foreach($use_path[$group] ?? [] as $slider_path){
			$val = $this->array_get_path($params, $slider_path, false);

			if($val === false || $val === null || $val === '') continue;
			if(strpos(json_encode($slider_path), '__ARRAY__') === false && is_array($val)) continue;

			// Transform closure: convert plugin slugs to paths, else resolve via zip.
			$transform = function ($v) use ($alias) {
				if($v === false || $v === null || $v === '') return $v;

				// Only transform strings; leave arrays/objects untouched unless your path specifies deeper keys.
				if(!is_string($v)) return $v;

				foreach($this->directories['plugin'] ?? [] as $plugin_path => $plugin_slug) {
					if (strpos($v, $plugin_slug) !== false) {
						return str_replace($plugin_slug, $plugin_path, $v);
					}
				}

				// addons might put full url to its image(s)
				// it is not caught by the loop above ( $this->directories['plugin'] )
				if (
					strpos($v, 'http') === 0
					&& strpos($v, 'wp-content/plugins/revslider-')
				) {
					// replace imported url with site url
					return plugins_url() . explode('wp-content/plugins', $v)[1];
				}

				$use_folder = ($this->mode === 6) ? 'images' : 'media';
				return $this->get_image_url_from_path(
					$this->check_file_in_zip($this->download_path, $v, $alias, $this->imported, false, $use_folder)
				);
			};

			// If path includes '__ARRAY__', $val is an array (parallel mapping). Otherwise scalar.
			$newVal = is_array($val)
				? array_map($transform, $val)
				: $transform($val);

			$this->array_set_path($params, $slider_path, $newVal);
		}

		return $params;
	}

	/**
	 * clear given folder if it can be deleted
	 * @return void removes the extracted package again
	 **/
	public function clear_files(){
		if(!isset($this->remove_path) || empty($this->remove_path) || !is_writable(dirname($this->remove_path))) return;
	
		global $wp_filesystem;
		WP_Filesystem();
		
		$wp_filesystem->delete($this->remove_path, true);

		$import_file = get_option('revslider-import-file', '');
		if (!empty($import_file)){
			$wp_filesystem->delete(dirname($import_file), true);
			update_option('revslider-import-file', '');
		}
	}


	/**
	 * @param  RevSliderSlide $slide
	 * @return RevSliderSlide
	 */
	private function v6_update_slide_modal_ids($slide){
		//change for WPML the parent IDs if necessary
		$parent_id = $this->get_val($slide, ['params', 'child', 'parentId'], $this->get_val($slide, ['params', 'child', 'parentID'], false));
		if(!in_array($parent_id, [false, ''], true) && isset($this->map[$parent_id])){
			$this->set_val($slide, ['params', 'child', 'parentId'], $this->map[$parent_id]);
		}

		$layers = $slide->get_layers();
		foreach($layers ?? [] as $lk => $layer){
			$actions = $this->get_val($layer, ['actions', 'action'], []);

			foreach($actions ?? [] as $a_k => $action){
				$jts = $this->get_val($action, 'jump_to_slide', '');
				if($jts !== '' && isset($this->map[$jts])){
					$this->set_val($layers[$lk], ['actions', 'action', $a_k, 'jump_to_slide'], $this->map[$jts]);
				}

				if(empty($this->map)) continue;

				$cb = $this->get_val($action, 'actioncallback', '');
				if($cb === '') continue;

				$cb = str_replace(['slider-'.$this->old_slider_id.'-', 'slider_'.$this->old_slider_id.'_'], ['slider-'.$this->slider_id.'-', 'slider_'.$this->slider_id.'_'], $cb);
				foreach($this->map ?? [] as $old_slide_id => $new_slide_id){
					$cb = str_replace('slide-'.$old_slide_id.'-', 'slide-'.$new_slide_id.'-', $cb);
					$this->set_val($layers[$lk], ['actions', 'action', $a_k, 'actioncallback'], $cb);
				}
			}

			/**
			 * check for wrong formatted false values in the reverseDirection
			 **/
			$_reverse_check = ['frame_0', 'frame_1', 'frame_999'];
			foreach($_reverse_check as $rc){
				$lr = $this->get_val($layer, ['timeline', 'frames', $rc, 'reverseDirection'], []);
				foreach($lr ?? [] as $lrk => $lrv){
					if($lrv === 'false') $this->set_val($layers[$lk], ['timeline', 'frames', $rc, 'reverseDirection', $lrk], false);
					if($lrv === 'true') $this->set_val($layers[$lk], ['timeline', 'frames', $rc, 'reverseDirection', $lrk], true);
				}
			}
		}
		$slide->set_layers_raw($layers);

		return $slide;
	}


	/**
	 * updates the ids in actions
	 * @return void
	 **/
	public function v6_update_modal_ids(){
		foreach($this->slides ?? [] as $key => $slide){
			$this->slides[$key] = $this->v6_update_slide_modal_ids($slide);
		}
		if (!empty($this->_static_slide) && $this->_static_slide instanceof RevSliderSlide){
			$this->_static_slide = $this->v6_update_slide_modal_ids($this->_static_slide);
		}
	}
}
