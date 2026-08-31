<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * One slide of a slider.
 *
 * Holds $params (slide settings) and $layers (the elements drawn on it). Slides exist in three flavours:
 * stored slides from the slides table, the slider's single "static" slide that stays visible across all
 * slides, and virtual slides built at render time from a post or a social stream item - the
 * init_by_post_data()/init_by_stream_data() family clones a template slide and fills its layer texts with
 * that item's data.
 *
 * Like RevSliderSlider this reads and writes the *_preview tables while the editor shows unsaved drafts.
 */
class RevSliderSlide extends RevSliderFunctions {
	
	private $id;
	private $slider_id;
	private $slider;
	private $order;
	private $layers = [];
	private $bglayer = [];
	public $params;
	public $children = [];
	public $image_id;
	public $image_url;
	public $image_path;
	public $image_filename;
	private $image_thumb;
	public $image_filepath;
	public $settings;
	public $post_data;
	private $template_id;
	public $ignore_alt = false;
	public $img_sizes = [];
	public $table_slides;
	
	public $static_slide = false;
	public $inited = false;
	
	/**
	 * used to determinate if we need to init the layers of the Slides
	 * can cause heavy ram usage on slider overview page if we have 100+ Sliders
	 **/
	public $init_layer = true;
	
	public function __construct(){
		parent::__construct();
		$this->set_special_table_mode();
	}

	/**
	 * point $table_slides at the preview table while the editor renders unsaved drafts, and at the legacy
	 * v6 table while a migration is running
	 * @return void
	 */
	public function set_special_table_mode(){
		global $SR_GLOBALS;

		if($SR_GLOBALS['preview_mode'] === true){
			$this->table_slides = RevSliderFront::TABLE_SLIDES_PREVIEW;
			return;
		}
		if($SR_GLOBALS['v6'] === true && RevSliderPluginUpdateV6::do_v6_tables_exist()){
			$this->table_slides = RevSliderFront::TABLE_SLIDES_V6;
			return;
		}

		$this->table_slides = RevSliderFront::TABLE_SLIDES;
	}

	/**
	 * get the current slide id
	 * @return int|string
	 */
	public function get_id(){
		return $this->id;
	}
	
	/**
	 * set slide ID
	 * @param int $id
	 * @return void
	 */
	public function set_id($id){
		$this->id = $id;
	}
	
	/**
	 * get slide title
	 * @return string
	 */
	public function get_title(){
		return $this->get_param('title', 'Slide');
	}
	
	/**
	 * get the slider id of the current slide
	 * @return int
	 */
	public function get_slider_id(){
		return $this->slider_id;
	}
	
	/**
	 * returns if the Slide is a static slide or not
	 * the static slide is the one overlay that stays visible while the slider cycles
	 * @return bool
	 */
	public function is_static_slide(){
		return $this->static_slide;
	}
	
	/**
	 * get array of children id's
	 * child slides belong to a parent slide in the carousel/child layouts
	 * @return array
	 */
	public function get_child_ids(){
		$ids		= [];
		$children	= $this->get_children();
		foreach($children ?? [] as $child){
			$ids[] = $child->get_id();
		}
		
		return $ids;
	}
	
	/**
	 * get slide order
	 * @return int
	 */
	public function get_order(){
		return $this->order;
	}
	
	/**
	 * get slide settings
	 * @since: 5.0
	 * @return array
	 */
	public function get_settings(){
		return apply_filters('revslider_slide_get_settings', apply_filters('revslider_slide_getSettings', $this->settings, $this), $this);
	}
	
	/**
	 * set slide params
	 * in memory only - save_params() writes them back
	 * @return void
	 */
	public function set_params($params){
		$this->params = $params;
	}
	
	/**
	 * get slide params
	 * @return array
	 */
	public function get_params(){
		return apply_filters('revslider_slide_get_params', apply_filters('revslider_slide_getParams', $this->params, $this), $this);
	}
	
	/**
	 * get slide layers
	 * @param bool $modified run them through modify_layers() first
	 * @return array
	 */
	public function get_layers($modified = false){
		$layers = apply_filters('revslider_get_layers', apply_filters('revslider_getLayers', $this->layers, $this), $this);
		if($modified !== false) $layers = $this->modify_layers($layers);

		return $layers;
	}

	/**
	 * used to modify layers only for frontend output
	 * as of now it is unused as qTranslate is gone but may be needed later on
	 * @return array
	 */
	public function modify_layers($layers = []){
		return $layers; //currently only returning layers, if changes need to be done remove this part and change layers before returning them

		$layers = (empty($layers)) ? $this->layers : $layers;

		foreach($layers ?? [] as $k => $layer){
			$layers[$k] = $layer;
		}

		return $layers;
	}
	
	/**
	 * set layers from client, do not normalize as this results in loosing the order
	 * @since: 5.0
	 * @return void
	 */
	public function set_layers_raw($layers){
		$this->layers = $layers;
	}
	
	/**
	 * get thumb url
	 * @return string
	 */
	public function get_thumb_url(){
		return (!empty($this->image_thumb)) ? $this->image_thumb : $this->image_url;
	}
	
	/**
	 * resolve a layer's uid to its numeric id
	 * since: 5.0
	 * @return int|false
	 */
	public function get_layer_id_by_uid($uid, $static_slide){
		if(strpos($uid, 'static-') !== false){
			$uid = str_replace('static-', '', $uid);
			$layers = $static_slide->get_layers();
			foreach($layers ?? [] as $l){
				$nuid = $this->get_val($l, 'uid');
				if($nuid == $uid) return $this->get_val($l, ['attributes', 'id']);
			}
		}else{
			foreach($this->layers ?? [] as $l){
				$nuid = $this->get_val($l, 'uid');
				if($uid == $nuid) return $this->get_val($l, ['attributes', 'id']);
			}
		}
		
		return '';
	}
	
	
	/**
	 * get slider param
	 * reads a setting from the *parent slider*, loading it once and caching it on this object
	 * @return mixed
	 */
	private function get_slider_param($slider_id, $name, $default, $validate = null){
		
		if(empty($this->slider)){
			$this->slider = new RevSliderSlider();
			$this->slider->init_by_id($slider_id);
		}
		
		return $this->slider->get_param($name, $default);
	}
	
	/**
	 * get the overview data of a slide
	 * @since: 6.1.2
	 * @return array
	 */
	public function get_overview_data(){
		return [
			'bg'		=> $this->get_overview_image_attributes_v7(),
			'function'	=> 'slide',
			'id'		=> $this->get_id(),
			'order'		=> $this->get_order(),
			'parent'	=> $this->get_slider_id(),
			'state'		=> $this->get_param(['publish', 'state'], 'published'),
			'title'		=> $this->get_title(),
		];
	}
	
	/**
	 * get the id of the static slide
	 * @param int $slider_id
	 * @return mixed
	 */
	public function get_static_slide_id($slider_id){
		if(empty($slider_id)) return false;

		global $wpdb, $SR_GLOBALS;

		if($SR_GLOBALS['v6'] === true && RevSliderPluginUpdateV6::do_v6_tables_exist()){
			$slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_STATIC_SLIDES . " WHERE slider_id = %d", $slider_id), ARRAY_A);
		}else{
			$slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id = %d AND static = 1", $slider_id), ARRAY_A);
		}
		
		return (empty($slide)) ? false : $this->get_val($slide, 'id', false);
	}

	/**
	 * combine get_static_slide_id & init_by_id into one function to avoid duplicated queries
	 * @since: 6.4.6
	 * @param int $slider_id
	 * @return bool
	 */
	public function init_static_slide_by_slider_id($slider_id){
		if(empty($slider_id)) return false;

		global $wpdb, $SR_GLOBALS;
		
		if($SR_GLOBALS['v6'] === true && RevSliderPluginUpdateV6::do_v6_tables_exist()){
			$slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_STATIC_SLIDES . " WHERE slider_id = %d", $slider_id), ARRAY_A);
		}else{
			$slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id = %d AND static = 1", $slider_id), ARRAY_A);
		}
		
		if(empty($slide)) return false;

		$this->init_by_data($slide);
		$this->set_val($this->params, ['static', 'isstatic'], true);
		$this->static_slide	= true;
		
		return true;
	}
	
	/**
	 * Check if Slide Exists with given ID
	 * @since: 5.0
	 * @param int $slideid
	 * @return bool
	 */
	public function exist_by_id($slideid, $static = false){
		global $wpdb, $SR_GLOBALS;
		
		try{
			if($SR_GLOBALS['v6'] === true && RevSliderPluginUpdateV6::do_v6_tables_exist()){
				$table = ($static !== false) ? RevSliderFront::TABLE_STATIC_SLIDES : $this->table_slides;
				$record = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . $table ." WHERE id = %d", $slideid), ARRAY_A);
			}else{
				$addition = ($static !== false) ? " AND static = 1" : '';
				$record = $wpdb->get_row($wpdb->prepare("SELECT id FROM ". $wpdb->prefix . $this->table_slides ." WHERE id = %d".$addition, $slideid), ARRAY_A);
			}
		}catch(Exception $e){
			return false;
		}
		
		return (empty($record)) ? false : true;
	}
	
	/**
	 * initialize a slide by id
	 * @return void
	 * @param int $slide_id
	 */
	public function init_by_id($slide_id){
		global $wpdb, $SR_GLOBALS;

		try{
			$addition = '';
			if(strpos($slide_id, 'static_') !== false){
				$this->static_slide	= true;
				$slide_id = str_replace('static_', '', $slide_id);
				$addition = " AND static = 1";

				$this->validate_numeric($slide_id, 'Static Slide ID');
			}else{
				$this->static_slide	= false;
				$this->validate_numeric($slide_id, 'Slide ID');
			}

			if($SR_GLOBALS['v6'] === true && RevSliderPluginUpdateV6::do_v6_tables_exist()){
				$table = ($this->static_slide !== false) ? RevSliderFront::TABLE_STATIC_SLIDES : $this->table_slides;
				$slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . $table ." WHERE id = %d", $slide_id), ARRAY_A);
			}else{
				$slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . $this->table_slides . " WHERE id = %d".$addition, $slide_id), ARRAY_A);
			}

			$this->init_by_data($slide);
			
		}catch(Exception $e){
			echo $e->getMessage();
			exit;
		}
	}
	
	/**
	 * init by another slide
	 * copies another slide's data into this object - the basis for building virtual post/stream slides
	 * @param RevSliderSlide $slide
	 * @return void
	 */
	public function init_by_slide(RevSliderSlide $slide){
		$slide = apply_filters('revslider_slide_initBySlide', $slide, $this);
		
		$this->id			= 'template';
		$this->template_id	= $slide->get_id();
		$this->slider_id	= $slide->get_slider_id();
		$this->order		= $slide->get_order();
		$this->image_url	= $slide->image_url;
		$this->image_id		= $slide->image_id;
		$this->image_thumb	= $slide->get_thumb_url();
		$this->image_path	= $slide->image_path;
		$this->image_filename = $slide->image_filename;
		$this->params		= $slide->get_params();
		$this->layers		= $slide->get_layers();
		$this->settings		= $slide->get_settings();
		$this->children		= $slide->children;
		$this->inited		= true;	
	}
	
	/**
	 * load the slide, creating it first when it does not exist yet
	 * @return bool
	 */
	public function check_create_slide($slide_id, $slider_id, $static = false){
		if($this->exist_by_id($slide_id)) return $slide_id;

		$slide_id	= $this->create_slide($slider_id, '', $static, $slide_id);

		return ($this->exist_by_id($slide_id)) ? $slide_id : false;
	}

	/**
	 * @param string $tag_key
	 * @param string $default
	 * @return void
	 */
	public function filter_layer_tags($tag_key, $default){
		$allowed_tags = $this->get_allowed_layer_tags();
		foreach($this->layers ?? [] as $key => $layer){
			if(empty($layer[$tag_key]) || in_array($layer[$tag_key], $allowed_tags)) continue;
			$this->layers[$key][$tag_key] = $default;
		}
	}

	/**
	 * drop layer attributes that are not on the allow list, so imported data cannot inject markup
	 * @return void
	 */
	public function filter_layer_attributes_v7(){
		foreach($this->layers ?? [] as $key => $layer){
			if(!isset($layer['attr']) || empty($layer['attr'])) continue;
			foreach($layer['attr'] ?? [] as $aKey => $aVal){
				if(empty($aVal)) continue;
				switch($aKey){
					case 'aO':
					case 't':
					case 'tO':
						$this->layers[$key]['attr'][$aKey] = sanitize_text_field($aVal);
					break;
					case 'class':
					case 'wrapClass':
					case 'iClass':
						$this->layers[$key]['attr'][$aKey] = (is_array($aVal)) ? implode(' ', $this->filter_class_name(explode(' ', $aVal))) : $aVal;
					break;
					case 'id':
					case 'wrapId':
					case 'rel':
						$this->layers[$key]['attr'][$aKey] = $this->filter_class_name($aVal);
					break;
				}
			}
		}
	}

	/**
	 * v7 data is structured a little different, hence we need a different process of saving
	 * @return int|string the slide id, or an error message
	 **/
	public function save_slide_v7($slide_id, $data, $slider_id = false, $version = RS_REVISION){
		$static = ($this->get_val($data, ['slide', 'global'], false) === true) ? true : false;
		
		if($slider_id !== false) $slide_id	= $this->check_create_slide($slide_id, $slider_id, $static);
		if($slide_id === false) return false;
		$this->init_by_id($slide_id);
		
		$this->settings['version'] = $this->get_val($data, 'version', $this->get_val($this->settings, 'version', $version));
		$this->params = $this->get_val($data, 'slide', []);
		$this->layers = $this->get_val($data, 'layers', []);
		$this->filter_layer_tags('tag', 'sr7-layer');
		$this->filter_layer_attributes_v7();

		$this->save_params();
		$this->save_layers();
		$this->save_settings();

		//we need to update the order field
		$order = $this->get_val($this->params, 'order');
		$this->change_slide_order($slide_id, $order);

		//backup Addon fix for Page/Post Slide saving
		if(class_exists('\ThemePunch\Backup\Base') && !isset($data['session_id'])) $data['session_id'] = substr(md5(wp_rand()), 0, 7); //wp_rand() uses a stronger RNG than rand()
		
		// needed for backups addon
		do_action('revslider_slide_updateSlideFromData_post', false, $data, $this);
		do_action('save_slide_v7', false, $data, $this);
		
		return true;
	}
	
	
	/**
	 * Merge settings of a Slide by the given data
	 * partial save used by the editor's advanced panels
	 * @return int|string the slide id, or an error message
	 * @since: 6.1.2
	 **/
	public function save_slide_advanced($slide_id, $data, $slider_id){
		if(!$this->exist_by_id($slide_id)) return false;
		
		$this->init_by_id($slide_id);
		
		$params = $this->get_val($data, 'params', []);
		if(empty($params)) $params = $this->get_val($data, 'settings', []);
		$params = $this->json_decode_slashes($params);
		$version = $this->get_val($params, 'version', $this->get_val($this->settings, 'version', RS_REVISION));
		if(!empty($params)){
			if(isset($params['version'])) unset($params['version']);
			$this->params = array_replace_recursive($this->params, $params);
			$this->save_params();
		}
		
		$layers = $this->get_val($data, 'layers', []);
		$layers = $this->json_decode_slashes($layers);
		if(!empty($layers)){
			$this->layers = array_replace_recursive($this->layers, $layers);
			$this->save_layers();
		}
		
		$settings = $this->get_val($data, 'settings', []);
		$settings = $this->json_decode_slashes($settings);
		if(!empty($settings)){
			$settings['version'] = $version;
			$this->settings = array_replace_recursive($this->settings, $settings);
			$this->save_settings();
		}
		
		return true;
	}
	
	
	/**
	 * delete a slide by its ID
	 * @return bool
	 */
	public function delete_slide_by_id($slide_id){
		global $wpdb;
		$return = $wpdb->delete($wpdb->prefix . RevSliderFront::TABLE_SLIDES, ['id' => $slide_id]);
		$wpdb->delete($wpdb->prefix . RevSliderFront::TABLE_SLIDES_PREVIEW, ['id' => $slide_id]);

		do_action('revslider_slide_deleteSlide', $slide_id);
		
		return $return;
	}
	
	
	/**
	 * duplicate slide by its ID and push it to given Slider ID
	 * @return int|false the new slide id
	 **/
	public function duplicate_slide_by_id($slide_id, $slider_id){
		global $wpdb;
		
		$done	= false;
		$slide	= $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . $this->table_slides ." WHERE id = %s", $slide_id), ARRAY_A);
		if(empty($slide)) return false;
	
		$slider	= new RevSliderSlider();
		$slider->init_by_id($slider_id);

		$max_order	= $wpdb->get_row($wpdb->prepare("SELECT slide_order FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id = %s AND static != 1 ORDER BY slide_order DESC LIMIT 0,1", $slider_id));
		$order		= $this->get_val($max_order, 'slide_order', 0);

		$slide['slider_id'] 	= $slider_id;
		$slide['slide_order']	= $order + 1;

		$params = json_decode($slide['params'], true);
		$params['id'] = '';
		$slide['params'] = json_encode($params);
		
		unset($slide['id']);

		return ($wpdb->insert($wpdb->prefix . $this->table_slides, $slide)) ? $wpdb->insert_id : false;
	}
	
	
	/**
	 * change slide_order of a slide
	 * @return void
	 * @param int $slide_id
	 * @param string $slide_order
	 */
	public function change_slide_order($slide_id, $slide_order, $full = false){
		global $wpdb;
		
		$wpdb->update($wpdb->prefix . $this->table_slides, ['slide_order' => $slide_order], ['id' => $slide_id]);

		if(!$full) return;

		$this->set_val($this->params, 'order', $slide_order);
		$this->save_params();
	}
	
	/**
	 * the slide's background as a pseudo layer, so the renderer can treat it like any other layer
	 * @return array
	 */
	public function get_bg_layer(){
		if(!empty($this->bglayer)) return $this->bglayer;

		$layers = $this->get_layers();

		// Check if $layers is iterable
		if (!is_array($layers) && !($layers instanceof Traversable)) {
			// Handle the error, could be a return or logging
			return null; // or any appropriate default value
		}
		
		foreach($layers ?? [] as $layer){
			if($this->get_val($layer, 'rTo') !== 'slide') continue;
			if($this->get_val($layer, 'subtype') !== 'slidebg') continue;

			$this->bglayer = $layer;
			break;
		}

		return $this->bglayer;
	}

	/**
	 * write the background pseudo layer back into the slide params
	 * @return void
	 */
	public function update_bg_layer($bglayer){
		$this->bglayer = $bglayer;

		$layers = $this->get_layers();
		foreach($layers ?? [] as $key => $layer){
			if($this->get_val($layer, 'rTo') !== 'slide') continue;
			if($this->get_val($layer, 'subtype') !== 'slidebg') continue;

			$layers[$key] = $bglayer;
			break;
		}
		
		$this->set_layers_raw($layers);
	}

	/**
	 * check slide settings, if the slide should be printed or not
	 * evaluates the slide's publish state, schedule and visibility conditions
	 * @return bool
	 */
	public function check_use_slide(){
		if($this->get_param(['publish', 'state'], 'published') === 'unpublished') return false;
		
		$ts			= current_time('timestamp');
		$schedule	= $this->get_param(['publish', 'sch'], false);
		$date_from	= $this->get_param(['publish', 'from'], '');
		$date_to	= $this->get_param(['publish', 'to'], '');
		if($schedule === false) return true;

		if($date_from != '' && $ts < strtotime($date_from)) return false;
		if($date_to != '' && $ts > strtotime($date_to)) return false;
		return true;
	}

	/**
	 * init post data in v7 style
	 * turn one post into a virtual slide: clone the template slide, then fill its layers with the post data
	 * @return void
	 */
	public function init_by_post_data_v7($post, RevSliderSlide $template, $slider_id){
		global $SR_GLOBALS;

		$post_id	= $this->get_val($post, 'id');
		$temp_id_v7 = intval(get_post_meta($post_id, 'slide_template_v7', true));
		$this->post_data = apply_filters('revslider_slide_initByPostData', $post, $template, $slider_id, $this);

		if($temp_id_v7 > 0){
			try{
				$local = new RevSliderSlide();
				if($local->exist_by_id($temp_id_v7)){
					$local->init_by_id($temp_id_v7);
					$this->init_by_slide($local);
				}else{
					$this->init_by_slide($template);
				}
			}catch(Exception $e){
				$this->init_by_slide($template);
			}
		}else{
			$this->init_by_slide($template);
		}
		
		$this->id				= $this->template_id; //PK TODO in pure v7: $this->template_id is not needed, change init_by_slide() to add it directly into $this->id
		$this->params['title']	= $this->get_val($post, 'title');
		
		if($this->get_val($this->params, ['seo', 'set'], false) == true && $this->get_val($this->params, ['seo', 'type'], 'regular') == 'regular'){
			$this->params['seo']['link'] = str_replace(['%link%', '{{link}}'], $this->get_val($post, 'link'), $this->params['seo']['link']);
			$this->params['seo']['link'] = str_replace('-', '_REVSLIDER_', $this->params['seo']['link']);
			//process meta tags:
			$matches = [];
			preg_match_all('/(?:%meta:(\w+)%|{{meta:(\w+)}})/', $this->params['seo']['link'], $matches);
			if(isset($matches[0]) && !empty($matches[0])){
				foreach($matches[0] as $key => $match){
					$meta = str_replace(['%meta:', '%', '{{meta:', '}}'], ['', '', '', ''], $match);
					$this->params['seo']['link'] = str_replace($match, $this->get_val($post, ['meta', $meta]), $this->params['seo']['link']);
				}
			}
			$this->params['seo']['link'] = str_replace('_REVSLIDER_', '-', $this->params['seo']['link']);
		}
		
		if(!in_array($this->get_val($this->params, ['bg', 'type'], 'trans'), ['trans', 'solid'], true)){
			if($this->get_val($this->params, ['bg', 'imageFromStream'], false) === true){ //if image is choosen, use featured image as background // && $this->get_val($this->params, ['bg', 'type']) == 'image'
				$this->image_url = $this->get_val($post, 'media');
				$thumbnail_url = $this->get_val($post, 'thumb');
				if(!empty($thumbnail_url)){
					if(!isset($this->params['thumb'])) $this->params['thumb'] = [];
					$this->params['thumb']['src'] = $thumbnail_url;
				}
			}
		}

		//replace placeholders in layers:
		$this->set_layers_by_stream_post_v7($post, $slider_id);
	}

	/**
	 * init slide by post data
	  * @removed in 6.2.18 -> @change 6.2.16: $template_id will not be written if the current post id is not the post_id to prevent malfunctioning
	 * @return void
	 */
	public function init_by_post_data($data, RevSliderSlide $template, $slider_id){
		$post_id	 = $this->get_val($data, 'ID');
		$template_id = get_post_meta($post_id, 'slide_template_v7', true);
		//only change the template if we are in the post itself, not if we are in another revslider that is post based!
		$template_id = ($template_id == '') ? 'default' : $template_id;
	
		$this->post_data = apply_filters('revslider_slide_initByPostData', $data, $template, $slider_id, $this);
		
		if(!empty($template_id) && is_numeric($template_id)){ //init by local template, if this fails, init by global (slider) template
			try{
				/*
				we have to add this slide for the static slide to be available in certain cases
				check if slide exists
				*/
				$local = new RevSliderSlide();
				if($local->exist_by_id($template_id)){
					$local->init_by_id($template_id);
					$this->init_by_slide($local);
				}else{
					$this->init_by_slide($template);
				}
			}catch(Exception $e){
				$this->init_by_slide($template);
			}
			
		}else{
			//init by global template
			$this->init_by_slide($template);
		}
		
		//set some slide params
		$this->id				= $post_id;
		$this->params['title']	= $this->get_val($data, 'post_title');
		
		if($this->get_val($this->params, ['seo', 'set'], false) == true && $this->get_val($this->params, ['seo', 'type'], 'regular') == 'regular'){
			$link = get_permalink($post_id);
			$this->params['seo']['link'] = str_replace(['%link%', '{{link}}'], $link, $this->params['seo']['link']);
			$this->params['seo']['link'] = str_replace('-', '_REVSLIDER_', $this->params['seo']['link']);
			
			//process meta tags:
			$matches = [];
			preg_match('/%meta:\w+%/', $this->params['seo']['link'], $matches);
			if(!empty($matches)){
				foreach($matches as $match){
					$meta = str_replace('%meta:', '', $match);
					$meta = str_replace('%', '', $meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$meta_val = get_post_meta($post_id, $meta, true);
					$this->params['seo']['link'] = str_replace($match, $meta_val, $this->params['seo']['link']);
				}
			}
			
			$matches = [];
			preg_match('/{{meta:\w+}}/', $this->params['seo']['link'], $matches);
			if(!empty($matches)){
				foreach($matches as $match){
					$meta = str_replace('{{meta:', '', $match);
					$meta = str_replace('}}', '',$meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$meta_val = get_post_meta($post_id, $meta, true);
					$this->params['seo']['link'] = str_replace($match, $meta_val, $this->params['seo']['link']);
				}
			}
			$this->params['seo']['link'] = str_replace('_REVSLIDER_', '-', $this->params['seo']['link']);
		}
		$this->params['publish']['state'] = ($data['post_status'] == 'publish') ? 'published' : $this->params['publish']['state'] = 'unpublished';
		
		if(!in_array($this->get_val($this->params, ['bg', 'type'], 'trans'), ['trans', 'solid'], true)){
			if($this->get_val($this->params, ['bg', 'imageFromStream'], false) === true){ //if image is choosen, use featured image as background // && $this->get_val($this->params, ['bg', 'type']) == 'image'
				$tid = get_post_thumbnail_id($post_id);
				
				if(!empty($tid)){
					$this->set_image_by_image_id($tid);
					
					//set the thumbnail image
					$thumbnail_url = wp_get_attachment_image_src($tid, 'thumbnail');
					if($thumbnail_url !== false){
						if(!isset($this->params['thumb'])) $this->params['thumb'] = [];
						$this->params['thumb']['src'] = $this->get_val($thumbnail_url, 0);
					}
				}
			}
		}
		//replace placeholders in layers:
		$this->set_layers_by_post($data, $slider_id);
	}
	
	/**
	 * replacing placeholders in v7 layers
	 * @return array the layers with their %placeholders% resolved
	 */
	public function set_layers_by_stream_post_v7($post, $slider_id){
		$translation = ['publish' => 'date', 'modified' => 'date_modified', 'author' => 'author_name', 'comments' => 'num_comments', 'catlistraw' => 'catlist_raw']; //translation is needed, as $post has different keys now than before
		$replace	 = ['catlist' => 'catlistraw'];
		$post		 = apply_filters('revslider_set_layers_by_stream_post', $post, $slider_id, $this);
		if(empty($this->layers)) return;
		if(empty($this->img_sizes)) $this->img_sizes = $this->get_all_image_sizes();

		$layers = json_encode($this->layers, true);
		foreach($post as $from => $to){
			if(is_array($to)){
				foreach($to ?? [] as $_from => $_to){
					if(is_array($_to)){
						//check if we are content || excerpt (excerpt with words|chars is written in meta)
						if($from === 'content'){
							foreach($_to ?? [] as $num => $text){
								$text = (empty($text)) ? '' : addcslashes($text, "\\\"\0");
								$layers = str_replace(['{{'.$from.':'.$_from.':'.$num.'}}', '%'.$from.':'.$_from.':'.$num.'%'], $text, $layers);
							}
						}elseif($from === 'meta'){
							foreach($_to ?? [] as $num => $text){
								if($_from !== 'excerpt') continue;
								foreach($text ?? [] as $_num => $_text){
									$_text = (empty($_text)) ? '' : addcslashes($_text, "\\\"\0");
									$layers = str_replace(['{{'.$_from.':'.$num.':'.$_num.'}}', '%'.$_from.':'.$num.':'.$_num.'%'], $_text, $layers);
								}
							}
						}
					}else{
						$_to = (empty($_to)) ? '' : addcslashes($_to, "\\\"\0");
						$_from = ($from === 'meta') ? 'meta:'.$_from : $_from;
						$layers = str_replace(['{{'.$_from.'}}', '%'.$_from.'%'], $_to, $layers);
					}
				}
			}else{
				if(isset($replace[$from])) $from = $replace[$from];
				if(strpos($from, '.*?') !== false){
					$contents = preg_match_all('/{{'.$from.'}}/', $layers, $matches);
					foreach($matches[0] ?? [] as $content) {
						$to = (empty($to)) ? '' : addsaddcslashes($to, "\\\"\0");
						$layers = str_replace($content, $to, $layers);
					}
				}else{
					$to = (empty($to)) ? '' : addcslashes($to, "\\\"\0");
					$layers = str_replace(['{{'.$from.'}}', '%'.$from.'%'], $to, $layers);
					if(isset($translation[$from])) $layers = str_replace(['{{'.$translation[$from].'}}', '%'.$translation[$from].'%'], $to, $layers);
				}
			}
		}

		foreach($this->img_sizes ?? [] as $img_handle => $img_name){
			$layers = str_replace(['{{featured_image_url_'.$img_handle.'}}', '%featured_image_url_'.$img_handle.'%'],  $this->get_val($post, ['img_urls', $img_handle, 'url'], ''), $layers);
			$layers = str_replace(['{{featured_image_'.$img_handle.'}}', '%featured_image_'.$img_handle.'%'], $this->get_val($post, ['img_urls', $img_handle, 'tag'], ''), $layers);
			
			//fix for using the lowercase name instead of the handle
			$img_name = str_replace(' ', '_', strtolower($img_name));
			$layers = str_replace(['{{featured_image_url_'.$img_name.'}}', '%featured_image_url_'.$img_name.'%'],  $this->get_val($post, ['img_urls', $img_handle, 'url'], ''), $layers);
			$layers = str_replace(['{{featured_image_'.$img_name.'}}', '%featured_image_'.$img_name.'%'], $this->get_val($post, ['img_urls', $img_handle, 'tag'], ''), $layers);
		}
		
		$layers = preg_replace('/\[rev_slider.*?\]|\[\/rev_slider\]|\[sr7.*?\]|\[\/sr7\]/', '', $layers, -1); //remove shortcode in case of recursion
		$layers = str_replace(["\r\n", "\r", "\n"], '\n', $layers);
		$layers = json_decode($layers, true);

		if(!empty($layers)) $this->layers = $layers;
	}

	/**
	 * replace layer placeholders by post data
	 * @return void
	 */
	private function set_layers_by_post($post, $slider_id){
		$post = apply_filters('revslider_slide_setLayersByPostData_pre', $post, $slider_id, $this);
		$ignore_taxonomies = apply_filters('revslider_slide_ignore_taxonomies', ['post_tag', 'translation_priority', 'language', 'post_translations'], $this);

		//check if we are woocommerce or not
		$post_id		= $this->get_val($post, 'ID');
		$slider_source	= $this->get_slider_param($slider_id, 'source', []);
		$source_type	= $this->get_slider_param($slider_id, 'sourcetype', 'gallery');
		$lazyload		= ($this->get_slider_param($slider_id, ['general', 'lazyLoad'], false) != 'none') ? true : false;
		$class			= 'tp-rs-img';
		$class			.= ($lazyload === true) ? ' rs-lazyload' : '';
		$excerpt_limit	= ($source_type == 'woocommerce' || $source_type == 'woo') ? $this->get_val($slider_source, ['woo', 'excerptLimit'], 55) : $this->get_val($slider_source, ['post', 'excerptLimit'], 55);
		if(strpos($excerpt_limit, 'chars') !== false){
			$type			= 'chars';
			$excerpt_limit	= str_replace('chars', '', $excerpt_limit);
		}else{
			$type			= 'words';
			$excerpt_limit	= str_replace('char', '', $excerpt_limit); //char is a fallback from before 6.3.4
			$excerpt_limit	= str_replace('words', '', $excerpt_limit);
		}
		
		$excerpt_limit	= (int)$excerpt_limit;
		$excerpt_limit	= $this->get_excerpt_by_id($post_id, $excerpt_limit, $type);
		
		$date		= $this->get_val($post, 'post_date_gmt');
		$date_mod	= $this->get_val($post, 'post_modified');
		$author		= $this->get_val($post, 'post_author');
		$curauth	= get_user_by('ID', $author);
		
		$cats		= $this->get_val($post, ['source', 'post', 'category']);
		$full		= false;
		if(empty($cats)){
			$cats = [];
			$post_type =  $this->get_val($post, 'post_type');
			$taxonomies = get_object_taxonomies($post_type);
			
			if(!empty($taxonomies)){
				foreach($taxonomies as $ptt){
					if(in_array($ptt, $ignore_taxonomies, true)) continue;
					$temp_cats = get_the_terms($post_id, $ptt);
					if(!empty($temp_cats)){
						$cats = array_merge($cats, $temp_cats);
						$full = true;
					}
				}
			}
		}
		$img_sizes	= $this->get_all_image_sizes();
		$ptid		= get_post_thumbnail_id($post_id);
		
		$attr		= [
			'title'			=> $this->get_val($post, 'post_title'),
			'alias'			=> $this->get_val($post, 'post_name'),
			'content'		=> $this->get_val($post, 'post_content'),
			'link'			=> get_permalink($post_id),
			'excerpt'		=> $excerpt_limit,
			'postDate'		=> $this->convert_post_date($date),
			'dateModified'	=> $this->convert_post_date($date_mod),
			'authorName'	=> get_the_author_meta('display_name', $author),
			'authorID'		=> $author,
			'authorPage'	=> $curauth->user_url,
			'authorPostsPage' => get_author_posts_url($author),
			'catlist'		=> $this->get_categories_html($cats, null, $post_id, $full),
			'catlist_raw'	=> strip_tags($this->get_categories_html($cats, null, $post_id, $full)),
			'taglist'		=> get_the_tag_list('', ', ', '', $post_id),
			'numComments'	=> $this->get_val($post, 'comment_count'),
			'img_urls'		=> []
		];
		
		foreach($img_sizes as $img_handle => $img_name){
			$featured_image_url = wp_get_attachment_image_src($ptid, $img_handle);
			if($featured_image_url !== false){
				$attr['img_urls'][$img_handle] = [
					'url' => $featured_image_url[0],
					'tag' => '<img class="'.$class.'" src="'.$featured_image_url[0].'" width="'.$featured_image_url[1].'" height="'.$featured_image_url[2].'" alt="'.esc_attr($this->get_val($attr, 'title')).'" data-no-retina />'
				];
			}
		}
		
		$attr = apply_filters('revslider_slide_setLayersByPostData_post', $attr, $post, $slider_id, $this);
		
		if(!empty($this->layers)){
			foreach($this->layers as $key => $layer){
				$text = $this->get_val($layer, 'text');
				$text = apply_filters('revslider_mod_meta', $text, $post['ID'], $post); //option to add your own filter here to modify meta to your likings
				$text = $this->set_post_data($text, $attr, $post['ID']);

				$layer['text'] = $text;
				
				$actions = $this->get_val($layer, ['actions', 'action'], []);
				if(!empty($actions)){
					foreach($actions as $a_k => $action){
						$ilink = $this->get_val($action, 'image_link');
						if(!empty($ilink)){
							$ilink = $this->set_post_data($ilink, $attr, $post['ID']);
							$this->set_val($layer, ['actions', 'action', $a_k, 'image_link'], $ilink);
						}
					}
				}
				
				/**
				 * check if we should add the featured image
				 * as the image, as the layer is image
				 * and has set to use the stream image
				 **/
				if($this->get_val($layer, 'type', 'text') === 'image' && $this->get_val($layer, ['media', 'imageFromStream'], false) === true){
					$featured_image_url = wp_get_attachment_image_src($ptid, 'full');
					if(!empty($featured_image_url)){
						$this->set_val($layer, ['media', 'imageUrl'], $this->get_val($featured_image_url, 0));
					}
				}
				
				$this->layers[$key] = $layer;
			}
		}
		
		for($mi = 0; $mi < 10; $mi++){ //set params to the post data
			$pa = $this->get_param(['info', 'params', $mi, 'v'], '');
			$pa = $this->set_post_data($pa, $attr, $post['ID']);
			$this->set_param(['info', 'params', $mi, 'v'], $pa);
		}
		
		$param_list = [['attributes', 'alt'], ['attributes', 'class'], ['attributes', 'data']];
		foreach($param_list as $p){ //set params to the stream data
			$pa = $this->get_param($p, '');
			$pa = $this->set_post_data($pa, $attr, $post['ID']);
			$this->set_param($p, $pa);
		}
	}
	
	
	/**
	 * get excerpt from post id
	 * falls back to a shortened post content when the post has no excerpt of its own
	 * @return string
	 */
	public function get_excerpt_by_id($id, $limit = 55, $type = 'words'){
		$post	 = get_post($id);
		$excerpt = trim($post->post_excerpt);
		$excerpt = (empty($excerpt)) ? $post->post_content : $excerpt;
		$excerpt = str_replace(['<br/>', '<br />'], '', strip_tags($excerpt, '<b><br><i><strong><small>'));
		if($type === 'words'){
			$excerpt = $this->get_text_intro($excerpt, $limit);
		}else{
			$excerpt = $this->get_text_intro_chars($excerpt, $limit);
		}

		return apply_filters('revslider_getExcerptById', $excerpt, $post, $limit);
	}
	
	/**
	 * replace placeholders with post data
	 * resolves %title%, %excerpt%, %meta:key%, %featured_image_*% and friends in one layer's text
	 * @return string
	 **/
	public function set_post_data($text, $attr, $post_id){
		$img_sizes = $this->get_all_image_sizes();
		
		//remove rev_slider shortcodes from content ( no inception ;)
		$content = $this->get_val($attr, 'content');
		$content = preg_replace('/\\[rev_slider.*?\\]/', '', $content, -1);
		$content = str_replace('[/rev_slider]', '', $content);
		
		//add filter for addon metas
		$text = apply_filters('rev_slider_insert_meta', $text, $post_id);

		$text = $this->resolve_basic_metas($text);
		$text = str_replace(['%title%', '{{title}}'], $this->get_val($attr, 'title'), $text);
		$text = str_replace(['%excerpt%', '{{excerpt}}'], $this->get_val($attr, 'excerpt'), $text);
		$text = str_replace(['%alias%', '{{alias}}'], $this->get_val($attr, 'alias'), $text);
		$text = str_replace(['%content%', '{{content}}'], $content, $text);
		$text = str_replace(['%link%', '{{link}}'], $this->get_val($attr, 'link'), $text);
		$text = str_replace(['%date%', '{{date}}'], $this->get_val($attr, 'postDate'), $text);
		$text = str_replace(['%date_modified%', '{{date_modified}}'], $this->get_val($attr, 'dateModified'), $text);
		$text = str_replace(['%author_name%', '{{author_name}}'], $this->get_val($attr, 'authorName'), $text);
		$text = str_replace(['%author_posts%', '{{author_posts}}'], $this->get_val($attr, 'authorPostsPage'), $text);
		$text = str_replace(['%author_website%', '{{author_website}}'], $this->get_val($attr, 'authorPage'), $text);
		$text = str_replace(['%num_comments%', '{{num_comments}}'], $this->get_val($attr, 'numComments'), $text);
		$text = str_replace(['%catlist%', '{{catlist}}'], $this->get_val($attr, 'catlist'), $text);
		$text = str_replace(['%catlist_raw%', '{{catlist_raw}}'], $this->get_val($attr, 'catlist_raw'), $text);
		$text = str_replace(['%taglist%', '{{taglist}}'], $this->get_val($attr, 'taglist'), $text);
		$text = str_replace(['%id%', '{{id}}'], $post_id, $text);
		
		if(!empty($img_sizes)){
			foreach($img_sizes as $img_handle => $img_name){
				$text = str_replace(['%featured_image_url_'.$img_handle.'%', '{{featured_image_url_'.$img_handle.'}}'],  $this->get_val($attr, ['img_urls', $img_handle, 'url'], ''), $text);
				$text = str_replace(['%featured_image_'.$img_handle.'%', '{{featured_image_'.$img_handle.'}}'], $this->get_val($attr, ['img_urls', $img_handle, 'tag'], ''), $text);
				
				//fix for using the lowercase name instead of the handle
				$img_name = strtolower($img_name);
				$img_name = str_replace(' ', '_', $img_name);
				$text = str_replace(['%featured_image_url_'.$img_name.'%', '{{featured_image_url_'.$img_name.'}}'],  $this->get_val($attr, ['img_urls', $img_handle, 'url'], ''), $text);
				$text = str_replace(['%featured_image_'.$img_name.'%', '{{featured_image_'.$img_name.'}}'], $this->get_val($attr, ['img_urls', $img_handle, 'tag'], ''), $text);
			}
		}

		//process meta tags:
		$text = str_replace('-', '_REVSLIDER_', $text);
		
		$arrMatches = [];
		preg_match_all('/%meta:\w+%/', $text, $arrMatches);
		
		if(!empty($arrMatches)){
			foreach($arrMatches as $matched){
				foreach($matched as $match){
					$meta = str_replace('%meta:', '', $match);
					$meta = str_replace('%', '',$meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$metaValue = get_post_meta($post_id, $meta, true);
					
					$text = str_replace($match, $metaValue, $text);	
				}
			}
		}
		
		$arrMatches = [];
		preg_match_all('/{{meta:\w+}}/', $text, $arrMatches);

		if(!empty($arrMatches)){
			foreach($arrMatches as $matched){
				foreach($matched as $match) {
					$meta = str_replace('{{meta:', '', $match);
					$meta = str_replace('}}', '',$meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$metaValue = get_post_meta($post_id,$meta,true);
					
					$text = str_replace($match,$metaValue,$text);	
				}
			}
		}
		
		$search_keys = ['content' => $content, 'title' => $this->get_val($attr, 'title'), 'excerpt' => $this->get_val($attr, 'excerpt')];
		foreach($search_keys as $sk => $svalue){
			$arrMatches = [];
			preg_match_all("/{{".$sk.":\w+[\:]\w+}}/", $text, $arrMatches);
			if(!empty($arrMatches)){
				foreach($arrMatches as $matched){
					foreach($matched as $match) {
						//now check length and type
						
						$meta = str_replace('{{'.$sk.':', '', $match);
						$meta = str_replace('}}', '',$meta);
						$meta = str_replace('_REVSLIDER_', '-', $meta);
						$vals = explode(':', $meta);
						
						if(count($vals) !== 2) continue; //not correct values
						$vals[1] = intval($vals[1]); //get real number
						if($vals[1] === 0 || $vals[1] < 0) continue; //needs to be at least 1 
						
						if($vals[0] == 'words'){
							$metaValue = explode(' ', strip_tags($svalue), $vals[1]+1);
							if(is_array($metaValue) && count($metaValue) > $vals[1]) array_pop($metaValue);
							$metaValue = implode(' ', $metaValue);
						}elseif($vals[0] == 'chars'){
							$metaValue = mb_substr(strip_tags($svalue), 0, $vals[1]);
						}else{
							continue;
						}
						
						$text = str_replace($match, $metaValue, $text);	
					}
				}
			}
		}

		$arrMatches = [];
		preg_match_all("/{{author_avatar:\w+}}/", $text, $arrMatches);
		if(!empty($arrMatches)){
			foreach($arrMatches as $matched){
				foreach($matched as $match) {
					//now check length and type
					
					$meta = str_replace('{{author_avatar:', '', $match);
					$meta = str_replace('}}', '', $meta);
					$meta = str_replace('_REVSLIDER_', '-', $meta);
					$vals = explode(':', $meta);
					
					if(count($vals) !== 1) continue; //not correct values
					$vals[0] = intval($vals[0]); //get real number
					if($vals[0] === 0 || $vals[0] < 0) continue; //needs to be at least 1 
					
					$avatar = get_avatar_url($this->get_val($attr, 'authorID'), ['size'=> $vals[0]]);
					
					$text = str_replace($match, $avatar, $text);	
				}
			}
		}
		
		$text = str_replace('_REVSLIDER_','-',$text);
		
		//replace event's template (skip the EM_Event build when the layer has no event_ placeholder)
		if(strpos($text, 'event_') !== false && RevSliderEventsManager::isEventsExists()){
			$ed = RevSliderEventsManager::get_event_post_data($post_id);
			if(!empty($ed)){
				foreach($ed as $ek => $ev){
					if($ek == 'start_date' || $ek == 'end_date') $ev = $this->convert_post_date($ev);
					$text = str_replace(['%event_'.$ek.'%', '{{event_'.$ek.'}}'], $ev, $text);
				}
			}
		}
		
		$text = apply_filters('sr_modify_layer_text', $text, $post_id, $this);
		
		return $text;
	}
	
	
	/**
	 * init slide by stream data
	 * same as init_by_post_data(), but for one item of a social stream
	 * @return void
	 */
	public function init_by_stream_data($data, $template, $slider_id, $sourcetype, $additions){
		$a = apply_filters('revslider_slide_initByStreamData', ['post_data' => $data, 'template' => $template, 'slider_id' => $slider_id, 'sourcetype' => $sourcetype, 'additions' => $additions], $this);
		
		$this->post_data = [];
		$this->post_data = (array)$a['post_data'];
		
		//init by global template
		$this->init_by_slide($a['template']);

		switch($a['sourcetype']){
			case 'facebook':
				$this->init_by_facebook($a['slider_id'], $a['additions']);
			break;
			case 'twitter':
				$this->throw_error(__('Twitter Stream is no longer available, for further information, please check https://www.sliderrevolution.com/faq/why-are-we-dropping-twitter-api-integration/', 'revslider'));
			break;
			case 'instagram':
				$this->init_by_instagram($a['slider_id'], $a['additions']);
			break;
			case 'flickr':
				$this->init_by_flickr($a['slider_id'], $a['additions']);
			break;
			case 'youtube':
				$this->init_by_youtube($a['slider_id'], $a['additions']);
			break;
			case 'vimeo':
				$this->init_by_vimeo($a['slider_id'], $a['additions']);
			break;
			default:
				$return = apply_filters('revslider_slide_initByStreamData_sourceType', false, $a, $this);
				
				if($return === false) $this->throw_error(__('Source must be from Stream', 'revslider'));
			break;
		}
		
		if($this->get_val($this->params, ['bg', 'type']) == 'image'){
			$this->params['bg']['image'] = $this->image_url;
		}
	}
	
	
	/**
	 * init the data for facebook
	 * @return void fills $post_data and resolves the layer placeholders
	 * @since: 5.0
	 * @change: 5.1.1 Facebook Album
	 */
	private function init_by_facebook($slider_id, $additions){
		$this->post_data = apply_filters('revslider_slide_initByFacebook_pre', $this->post_data, $slider_id, $additions, $this);
		
		//set some slide params
		$this->id = $this->get_val($this->post_data, 'id');
		$this->set_param('title', $this->get_val($this->post_data, 'name'));
		$this->set_param(['publish', 'state'], 'published');
		
		if($this->get_val($this->params, ['seo', 'set'], false) && $this->get_val($this->params, ['seo', 'type'], 'regular') == 'regular'){
			$link = $this->get_val($this->post_data, 'link');
			$this->set_param(['seo', 'link'], str_replace(['%link%', '{{link}}'], $link, $this->params['seo']['link']));
		}
		
		if($this->get_val($this->params, ['bg', 'type']) == 'image'){ //if image is choosen, use featured image as background
			if($this->get_val($additions, 'fb_type') == 'album'){
				$image_array = $this->get_val($this->post_data, 'images');
				$this->image_url	=  isset($image_array[0]['source']) ? $image_array[0]['source'] : $this->get_val($this->post_data, 'picture', $this->image_thumb);
				$this->image_thumb	= $this->get_val($this->post_data, 'picture', $this->image_thumb);
			}else{
				$this->image_url	= $this->get_val($this->post_data, 'full_picture', $this->image_thumb);
				$this->image_thumb	= $this->get_val($this->post_data, 'picture', $this->image_thumb);
			}
			
			$this->image_url = (empty($this->image_url)) ? RS_PLUGIN_URL_CLEAN.'public/assets/sources/facebook.png' : $this->image_url;
			$this->image_url = (is_ssl()) ? str_replace('http://', 'https://', $this->image_url) : $this->image_url;
			$this->image_filename = basename($this->image_url);
		}
		
		$this->post_data = apply_filters('revslider_slide_initByFacebook_post', $this->post_data, $slider_id, $additions, $this);
		
		$this->set_layers_by_stream($slider_id, 'facebook', $additions); //replace placeholders in layers
	}
	
	
	/**
	 * init the data for instagram
	 * @return void fills $post_data and resolves the layer placeholders
	 * @since: 5.0
	 */
	private function init_by_instagram($slider_id, $additions = []){
		$this->post_data = apply_filters('revslider_slide_initByInstagram_pre', $this->post_data, $slider_id, $this);

		//set some slide params
		$this->id = $this->get_val($this->post_data, 'id');
		$caption = $this->get_val($this->post_data, 'caption');
		$link	 = $this->get_val($this->post_data, 'link');
		$link	 = (empty($link)) ? 'https://www.instagram.com/p/' . $this->get_val($this->post_data, 'shortcode') : $link;
		$this->set_param('title', $this->get_val($caption, 'text'));
		$this->set_param(['publish', 'state'], 'published');

		if($this->get_val($this->params, ['seo', 'set'], false) && $this->get_val($this->params, ['seo', 'type'], 'regular') == 'regular'){
			$this->set_param(['seo', 'link'], str_replace(['%link%', '{{link}}'], $link, $this->params['seo']['link']));
		}
		
		if(in_array($this->get_val($this->params, ['bg', 'type']), ['html5', 'trans', 'image', 'streaminstagram', 'streaminstagramboth'], true)){ //if image is choosen, use featured image as background
			$is			= [];
			$img_sizes	= $this->get_all_image_sizes('instagram');
			$img_res	= $this->get_val($this->params, ['bg', 'imageSourceType'], reset($img_sizes));
			$img_res	= (!isset($img_sizes[$img_res])) ? key($img_sizes) : $img_res;
			$this->image_id	= $this->get_val($this->post_data, 'id');
			
			$imgs		= $this->get_val($this->post_data, 'images', []);
			foreach($imgs as $k => $im){
				$is[$k] = $im->url;
			}

			$this->image_url = $this->get_val($this->post_data, 'display_url');
			$this->image_thumb = $this->get_val($this->post_data, 'thumbnail_src', $this->image_thumb);
			
			$this->image_url = (empty($this->image_url)) ? RS_PLUGIN_URL_CLEAN . 'public/assets/sources/instagram.png' : $this->image_url;

			$this->image_url = (is_ssl()) ? str_replace('http://', 'https://', $this->image_url) : $this->image_url;
			$this->image_filename = basename($this->image_url);

		}
		
		$videos = $this->get_val($this->post_data, ['videos', 'standard_resolution', 'url']);
		if(!empty($videos)){
			$this->set_param('slide_bg_instagram', $videos); //set video for background video
			$this->set_param(['bg', 'mpeg'], $videos); //set video for background video
		}
		
		$this->post_data = apply_filters('revslider_slide_initByInstagram_post', $this->post_data, $slider_id, $this);
		
		$this->set_layers_by_stream($slider_id, 'instagram', $additions); //replace placeholders in layers
	}
	
	
	/**
	 * init the data for flickr
	 * @return void fills $post_data and resolves the layer placeholders
	 * @since: 5.0
	 * @update: 6.1.7 
	 */
	private function init_by_flickr($slider_id, $additions){
		$this->post_data = apply_filters('revslider_slide_initByFlickr_pre', $this->post_data, $slider_id, $this);
		$this->id		 = $this->get_val($this->post_data, 'id');
		$this->set_param('title', $this->get_val($this->post_data, 'title'));
		$this->set_param(['publish', 'state'], 'published');
		
		if($this->get_val($this->params, ['seo', 'set'], false) && $this->get_val($this->params, ['seo', 'type'], 'regular') == 'regular'){
			$link = 'http://flic.kr/p/'.$this->base_encode($this->get_val($this->post_data, 'id'));
			$this->set_param(['seo', 'link'], str_replace(['%link%', '{{link}}'], $link, $this->params['seo']['link']));
		}
		
		if(in_array($this->get_val($this->params, ['bg', 'type']), ['html5', 'image'], true)){ //if image is choosen, use featured image as background
			//facebook check which image size is choosen
			$img_sizes	= $this->get_all_image_sizes('flickr');
			$img_res	= $this->get_val($this->params, ['bg', 'imageSourceType'], reset($img_sizes));
			$this->image_id	= $this->get_val($this->post_data, 'id');
			
			if(!isset($img_sizes[$img_res])) $img_res = key($img_sizes);
			
			$is = @[
				'original'	 => $this->get_val($this->post_data, 'url_o'),
				'large' 	 => $this->get_val($this->post_data, 'url_l'),
				'medium-800' => $this->get_val($this->post_data, 'url_c'),
				'medium-640' => $this->get_val($this->post_data, 'url_z'),
				'medium' 	 => $this->get_val($this->post_data, 'url_m'),
				'small-320'  => $this->get_val($this->post_data, 'url_n'),
				'small' 	 => $this->get_val($this->post_data, 'url_s'),
				'thumbnail'  => $this->get_val($this->post_data, 'url_t'),
				'square' 	 => $this->get_val($this->post_data, 'url_sq'),
				'large-square' => $this->get_val($this->post_data, 'url_q')
			];
			
			$this->image_url	= $this->get_val($is, $img_res, '');
			if(empty($this->image_url)){
				foreach($is as $img_res){
					$this->image_url = $img_res;
					if(!empty($img_res)) break;
				}
			}
			$this->image_thumb	= $this->get_val($is, 'thumbnail', $this->image_thumb);
			$this->image_url	= (empty($this->image_url)) ? RS_PLUGIN_URL_CLEAN.'public/assets/sources/flickr.png' : $this->image_url;
			$this->image_url	= (is_ssl()) ? str_replace("http://", "https://", $this->image_url) : $this->image_url;
			$this->image_filename = basename($this->image_url);
		}
		
		$this->post_data = apply_filters('revslider_slide_initByFlickr_post', $this->post_data, $slider_id, $this);
		
		$this->set_layers_by_stream($slider_id, 'flickr', $additions); //replace placeholders in layers
	}
	
	
	/**
	 * init the data for youtube
	 * @return void fills $post_data and resolves the layer placeholders
	 * @since: 5.0
	 */
	private function init_by_youtube($slider_id, $additions){
		$this->post_data = apply_filters('revslider_slide_initByYoutube_pre', $this->post_data, $slider_id, $additions, $this);
		
		//set some slide params
		$snippet	= $this->get_val($this->post_data, 'snippet');
		$resource	= $this->get_val($snippet, 'resourceId');
		$link_raw	= ($additions['yt_type'] == 'channel') ? $this->get_val($this->post_data, 'id') : $this->get_val($snippet, 'resourceId');
		$link		= $this->get_val($link_raw, 'videoId');
		
		$this->set_param(['bg', 'youtube'], $link); //set video for background video
		
		if($this->get_val($this->params, ['seo', 'set'], false) && $this->get_val($this->params, ['seo', 'type'], 'regular') == 'regular'){
			if($link !== '') $link = '//youtube.com/watch?v='.$link;
			$this->set_param(['seo', 'link'], str_replace(['%link%', '{{link}}'], $link, $this->params['seo']['link']));
		}
		
		switch($this->get_val($additions, 'yt_type')){
			case 'channel':
				$this->id = $this->get_val($this->post_data, ['id', 'videoId']);
			break;
			case 'playlist':
				$this->id = $this->get_val($resource, 'videoId');
			break;
		}
		
		$this->id = ($this->id == '') ? 'not-found' : $this->id;
		
		$this->set_param('title', $this->get_val($snippet, 'title'));
		$this->set_param(['publish', 'state'], 'published');
		
		$bg_type = $this->get_val($this->params, ['bg', 'type']);
		
		if(in_array($bg_type, ['trans', 'image', 'streamyoutube', 'streamyoutubeboth', 'youtube', 'streamvimeo', 'streamvimeoboth', 'vimeo'], true)){ //if image is choosen, use featured image as background
			//facebook check which image size is choosen
			$img_sizes	= $this->get_all_image_sizes('youtube');
			$img_res	= $this->get_val($this->params, ['bg', 'imageSourceType'], reset($img_sizes));
			$this->image_id	= $this->get_val($resource, 'videoId');
			$thumbs		= $this->get_val($snippet, 'thumbnails');
			$is			= [];
			if(!empty($thumbs)){
				foreach($thumbs as $name => $vals){
					$is[$name] = $this->get_val($vals, 'url');
				}
			}
			
			if(!isset($img_sizes[$img_res])) $img_res = key($img_sizes);
			
			$this->image_url = $this->get_val($is, $img_res, '');
			$this->image_url = (empty($this->image_url)) ? $this->get_val($is, 'default', '') : $this->image_url;
			$this->image_thumb = $this->get_val($is, 'medium', $this->image_thumb);
			
			$this->image_url = (empty($this->image_url)) ? RS_PLUGIN_URL_CLEAN.'public/assets/sources/youtube.png' : $this->image_url;
			$this->image_url = (is_ssl()) ? str_replace('http://', 'https://', $this->image_url) : $this->image_url;
			
			if($this->get_param(['thumb', 'src'], '') === ''){
				$this->set_param(['thumb', 'src'], $this->image_thumb);
			}
			
			$this->image_filename = basename($this->image_url);
		}
		
		$this->post_data = apply_filters('revslider_slide_initByYoutube_post', $this->post_data, $slider_id, $additions, $this);
		
		//replace placeholders in layers:
		$this->set_layers_by_stream($slider_id, 'youtube', $additions);
	}
	
	
	/**
	 * init the data for vimeo
	 * @return void fills $post_data and resolves the layer placeholders
	 * @since: 5.0
	 */
	private function init_by_vimeo($slider_id, $additions){
		$this->post_data = apply_filters('revslider_slide_initByVimeo_pre', $this->post_data, $slider_id, $additions, $this);
		
		$this->id = $this->get_val($this->post_data, 'id');
		$this->set_param(['publish', 'state'], 'published');
		$this->set_param('title', $this->get_val($this->post_data, 'title'));
		
		if($this->get_val($this->params, ['seo', 'set'], false) && $this->get_val($this->params, ['seo', 'type'], 'regular') == 'regular'){
			$link = $this->get_val($this->post_data, 'url');
			$this->params['seo']['link'] = str_replace(['%link%', '{{link}}'], $link, $this->params['seo']['link']);
		}
		
		$this->set_param(['bg', 'vimeo'], $this->get_val($this->post_data, 'url'));
		if($this->get_val($this->params, ['bg', 'imageFromStream'], false) === true && in_array($this->get_val($this->params, ['bg', 'type']), ['trans', 'image', 'streamvimeo', 'streamvimeoboth', 'vimeo'], true)){ //if image is choosen, use featured image as background
			//vimeo check which image size is choosen
			$img_sizes	= $this->get_all_image_sizes('vimeo');
			$img_res	= $this->get_val($this->params, ['bg', 'imageSourceType'], reset($img_sizes));
			$img_res	= (!isset($img_sizes[$img_res])) ? key($img_sizes) : $img_res;
			
			$is			= [];
			$this->image_id = $this->get_val($this->post_data, 'id');

			foreach($img_sizes as $handle => $name){
				$is[$handle] = $this->get_val($this->post_data, $handle);
			}
			
			$this->image_url = $this->get_val($is, $img_res, '');
			$this->image_url = (empty($this->image_url)) ? RS_PLUGIN_URL_CLEAN.'public/assets/sources/vimeo.png' : $this->image_url;
			$this->image_url = (is_ssl()) ? str_replace("http://", "https://", $this->image_url) : $this->image_url;
			$this->image_thumb = $this->get_val($is, 'thumbnail', $this->image_thumb);
			$this->image_filename = basename($this->image_url);
		}
		
		$this->post_data = apply_filters('revslider_slide_initByVimeo_post', $this->post_data, $slider_id, $additions, $this);
		
		//replace placeholders in layers:
		$this->set_layers_by_stream($slider_id, 'vimeo', $additions);
	}
	
	
	/**
	 * prepare and fill the stream data
	 * dispatches to the init_by_<network> method and normalizes the result
	 * @return array
	 **/
	public function set_stream_data($text, $attr, $stream_type, $additions = [], $is_action = false){
		$img_sizes = $this->get_all_image_sizes($stream_type);
		$_img_sizes = $this->get_all_image_sizes();
	

		$_img_s = [];
		if(!empty($_img_sizes)){
			foreach($_img_sizes as $k => $v){
				$v = str_replace(' ', '_', strtolower($v));
				$_img_s[$v] = $v;
			}
		}
		$img_sizes	= array_merge($img_sizes, $_img_sizes, $_img_s);
		$content	= $this->get_val($attr, 'content');

		$text = apply_filters('revslider_slide_set_stream_data_pre', $text, $attr, $stream_type, $additions, $is_action, $img_sizes);
		$text = str_replace(['%title%', '{{title}}'], $this->get_val($attr, 'title'), $text);
		$text = str_replace(['%excerpt%', '{{excerpt}}', '%description%', '{{description}}'], $this->get_val($attr, 'excerpt'), $text);
		$text = str_replace(['%alias%', '{{alias}}'], $this->get_val($attr, 'alias'), $text);
		$text = str_replace(['%content%', '{{content}}'], $content, $text);
		$text = str_replace(['%link%', '{{link}}'], $this->get_val($attr, 'link'), $text);
		$text = str_replace(['%date_published%', '{{date_published}}', '%date%', '{{date}}'], $this->get_val($attr, 'date'), $text);
		$text = str_replace(['%date_modified%', '{{date_modified}}'], $this->get_val($attr, 'date_modified'), $text);
		$text = str_replace(['%author_name%', '{{author_name}}'], $this->get_val($attr, 'author_name'), $text);
		$text = str_replace(['%num_comments%', '{{num_comments}}'], $this->get_val($attr, 'num_comments'), $text);
		$text = str_replace(['%catlist%', '{{catlist}}'], $this->get_val($attr, 'catlist'), $text);
		$text = str_replace(['%catlist_raw%', '{{catlist_raw}}'], $this->get_val($attr, 'catlist_raw'), $text);
		$text = str_replace(['%taglist%', '{{taglist}}'], $this->get_val($attr, 'taglist'), $text);
		$text = str_replace(['%likes%', '{{likes}}'], $this->get_val($attr, 'likes'), $text);
		$text = str_replace(['%retweet_count%', '{{retweet_count}}'], $this->get_val($attr, 'retweet_count'), $text);
		$text = str_replace(['%favorite_count%', '{{favorite_count}}'], $this->get_val($attr, 'favorite_count'), $text);
		$text = str_replace(['%views%', '{{views}}'], $this->get_val($attr, 'views'), $text);
		
		$arrMatches = [];
		preg_match_all("/{{content:\w+[\:]\w+}}/", $text, $arrMatches);
		foreach($arrMatches as $matched){
			foreach($matched as $match) {
				//now check length and type
				
				$meta = str_replace("{{content:", "", $match);
				$meta = str_replace("}}","",$meta);
				$meta = str_replace('_REVSLIDER_', '-', $meta);
				$vals = explode(':', $meta);
				
				if(count($vals) !== 2) continue; //not correct values
				$vals[1] = intval($vals[1]); //get real number
				if($vals[1] === 0 || $vals[1] < 0) continue; //needs to be at least 1 
				
				if($vals[0] == 'words'){
					$metaValue = explode(' ', strip_tags($content), $vals[1]+1);
					if(is_array($metaValue) && count($metaValue) > $vals[1]) array_pop($metaValue);
					$metaValue = implode(' ', $metaValue);
				}elseif($vals[0] == 'chars'){
					$metaValue = mb_substr(strip_tags($content), 0, $vals[1]);
				}else{
					continue;
				}
				
				$text = str_replace($match, $metaValue, $text);	
			}
		}
		
		switch($stream_type){
			case 'facebook':
				foreach($img_sizes as $img_handle => $img_name){
					if($this->get_val($additions, 'fb_type') == 'album'){
						$text = str_replace(['%featured_image_url_'.$img_handle.'%', '{{featured_image_url_'.$img_handle.'}}', '%image_url_'.$img_handle.'%', '{{image_url_'.$img_handle.'}}'], $this->get_val($attr, ['img_urls', $img_handle, 'url']), $text);
						$text = str_replace(['%featured_image_'.$img_handle.'%', '{{featured_image_'.$img_handle.'}}', '%image_'.$img_handle.'%', '{{image_'.$img_handle.'}}'], $this->get_val($attr, ['img_urls', $img_handle, 'tag']), $text);
					}else{
						$text = str_replace(['%featured_image_url_'.$img_handle.'%', '{{featured_image_url_'.$img_handle.'}}', '%image_url_'.$img_handle.'%', '{{image_url_'.$img_handle.'}}'], $this->get_val($attr, ['img_urls', 'url']), $text);
						$text = str_replace(['%featured_image_'.$img_handle.'%', '{{featured_image_'.$img_handle.'}}', '%image_'.$img_handle.'%', '{{image_'.$img_handle.'}}'], $this->get_val($attr, ['img_urls', 'tag']), $text);
					}
				}
			break;
			case 'youtube':
			case 'vimeo':
			case 'instagram':
			case 'flickr':
				foreach($img_sizes as $img_handle => $img_name){
					$text = str_replace(['%featured_image_url_'.$img_handle.'%', '{{featured_image_url_'.$img_handle.'}}', '%image_url_'.$img_handle.'%', '{{image_url_'.$img_handle.'}}'], $this->get_val($attr, ['img_urls', $img_handle, 'url']), $text);
					$text = str_replace(['%featured_image_'.$img_handle.'%', '{{featured_image_'.$img_handle.'}}', '%image_'.$img_handle.'%', '{{image_'.$img_handle.'}}'], $this->get_val($attr, ['img_urls', $img_handle, 'tag']), $text);
				}
			break;
		}
		
		return apply_filters('revslider_slide_set_stream_data_post', $text, $attr, $stream_type, $additions, $is_action, $img_sizes);
	}
	
	
	/**
	 * replace layer placeholders by stream data
	 * @since: 5.0
	 * @return void
	 */
	private function set_layers_by_stream($slider_id, $stream_type, $additions = []){
		$a				= apply_filters('revslider_slide_setLayersByStreamData_pre', ['layers' => $this->layers, 'params' => $this->params], $slider_id, $stream_type, $additions, $this);
		$this->params	= $this->get_val($a, 'params');
		$this->layers	= $this->get_val($a, 'layers');
		$additions['lazyload'] = ($this->get_slider_param($slider_id, ['general', 'lazyLoad'], false) != 'none') ? true : false;
		$attr			= $this->return_stream_data($stream_type, $additions);
		
		if(!empty($this->layers)){
			foreach($this->layers as $key => $layer){
				$text = $this->get_val($layer, 'text');
				$text = apply_filters('revslider_mod_stream_meta', $text, $slider_id, $stream_type, $this->post_data); //option to add your own filter here to modify meta to your likings
				$layer['text'] = $this->set_stream_data($text, $attr, $stream_type, $additions);
				
				//set link actions to the stream data
				$actions = $this->get_val($layer, ['actions', 'action']);
				if(!empty($actions)){
					foreach($actions as $a_k => $action){
						$ilink = $this->get_val($action, 'image_link');
						if(!empty($ilink)){
							$ilink = $this->set_stream_data($ilink, $attr, $stream_type, $additions, true);
							$this->set_val($layer, ['actions', 'action', $a_k, 'image_link'], $ilink);
						}
					}
				}
				
				$layer_type = $this->get_val($layer, 'type', 'text');
				if(($layer_type === 'image' && $this->get_val($layer, ['media', 'imageFromStream'], false) === true) || (in_array($layer_type, ['shape', 'row', 'group'], true) && $this->get_val($layer, ['idle', 'bgFromStream'], false) === true)){
					$featured_image_url = $this->get_val($attr, 'stream_image_url', '');
					if(!empty($featured_image_url)) $this->set_val($layer, ['media', 'imageUrl'], $featured_image_url);
				}
				$this->layers[$key] = $layer;
			}
		}
		
		//set params to the stream data
		for($mi = 0; $mi < 10; $mi++){
			$pa = $this->get_param(['info', 'params', $mi, 'v'], '');
			$pa = $this->set_stream_data($pa, $attr, $stream_type, $additions);
			
			$this->set_param(['info', 'params', $mi, 'v'], $pa);
		}
		
		$param_list = [['attributes', 'alt'], ['attributes', 'class'], ['attributes', 'data']];
		//set params to the stream data
		foreach($param_list as $p){
			$pa = $this->get_param($p, '');
			$pa = $this->set_stream_data($pa, $attr, $stream_type, $additions);
			$this->set_param($p, $pa);
		}
		
		$a = apply_filters('revslider_slide_setLayersByStreamData_post', ['layers' => $this->layers, 'params' => $this->params], $slider_id, $stream_type, $additions, $this);
		
		$this->params = $this->get_val($a, 'params');
		$this->layers = $this->get_val($a, 'layers');
	}
	
	
	/**
	 * returns the data of the selected stream
	 * the payload the front end fetches for a stream slider, so items can be loaded without a page reload
	 * @return array
	 **/
	public function return_stream_data($stream_type, $additions = []){
		$img_sizes = $this->get_all_image_sizes($stream_type);
		$attr = [];
		$attr = apply_filters('revslider_slide_return_stream_data_pre', $attr, $stream_type, $additions, $img_sizes);
		$ll = ($this->get_val($additions, 'lazyload', false) === true) ? ' rs-lazyload' : '';
		$class = ' class="tp-rs-img'.$ll.'"';
		
		switch($stream_type){
			case 'facebook':
				if($this->get_val($additions, 'fb_type') == 'album'){
					$image_array = $this->get_val($this->post_data, 'images');
					$this->image_url	= isset($image_array[0]['source']) ? $image_array[0]['source'] : $this->get_val($this->post_data, 'picture', $this->image_thumb);
					$this->image_thumb	= $this->get_val($this->post_data, 'picture', $this->image_thumb);

					$fb_img_thumbnail = $this->get_val($this->post_data, 'picture');

					$image_array = $this->get_val($this->post_data, 'images');
					$fb_img	= isset($image_array[0]['source']) ? $image_array[0]['source'] : $this->get_val($this->post_data, 'picture');
					
					$attr1 = [
						'title'		=> $this->get_val($this->post_data, 'name'),
						'content'	=> $this->get_val($this->post_data, 'name'),
						'link'		=> $this->get_val($this->post_data, 'link'),
						'date'		=> $this->convert_post_date($this->get_val($this->post_data, 'created_time'), true),
						'date_modified'	=> $this->convert_post_date($this->get_val($this->post_data, 'updated_time'), true),
						'author_name'	=> $this->get_val($this->post_data, ['from', 'name']),
						'likes'		=> intval($this->get_val($this->post_data, ['likes', 'summary', 'total_count'])),
						'stream_image_url' => $fb_img,
						'img_urls'	=> [
							'full'	=> [
								'url' => $fb_img,
								'tag' => '<img'.$class.' src="'.$fb_img.'" alt="'.esc_attr($this->get_val($this->post_data, 'name')).'" data-no-retina />'
							],
							'thumbnail' => [
								'url' => $fb_img_thumbnail,
								'tag' => '<img'.$class.' src="'.$fb_img_thumbnail.'" alt="'.esc_attr($this->get_val($this->post_data, 'name')).'" data-no-retina />'
							]
						]
					];
				}else{
					$this->image_url	= $this->get_val($this->post_data, 'full_picture', $this->image_thumb);
					$this->image_thumb	= $this->get_val($this->post_data, 'picture', $this->image_thumb);

					$fb_img_thumbnail = $this->get_val($this->post_data, 'picture');
					$fb_img = $this->get_val($this->post_data, 'full_picture');

					$attr1 = [
						'title'		=> $this->get_val($this->post_data, 'message'),
						'content'	=> $this->get_val($this->post_data, 'message'),
						'link'		=> $this->get_val($this->post_data, 'permalink_url'),
						'date'		=> $this->convert_post_date($this->get_val($this->post_data, 'created_time'), true),
						'date_modified'	=> $this->convert_post_date($this->get_val($this->post_data, 'updated_time'), true),
						'author_name'	=> $this->get_val($this->post_data, ['from', 'name']),
						'likes'		=> intval($this->get_val($this->post_data, ['likes', 'summary', 'total_count'])),
						'stream_image_url' => $fb_img,
						'img_urls'	=> [
							'full'	=> [
								'url' => $fb_img,
								'tag' => '<img'.$class.' src="'.$fb_img.'" alt="'.esc_attr($this->get_val($this->post_data, 'message')).'" data-no-retina />'
							],
							'thumbnail' => [
								'url' => $fb_img_thumbnail,
								'tag' => '<img'.$class.' src="'.$fb_img_thumbnail.'" alt="'.esc_attr($this->get_val($this->post_data, 'message')).'" data-no-retina />'
							]
						]
					];
				}
			break;
			case 'instagram':
				$caption	= $this->get_val($this->post_data, ['edge_media_to_caption', 'edges', 0, 'node', 'text']);
				$link		= $this->get_val($this->post_data, 'link');
				$attr1		= [
					'title'		=> $caption,
					'content'	=> $caption,
					'link'		=> (empty($link)) ? 'https://www.instagram.com/p/'. $this->get_val($this->post_data, 'shortcode') : $link,
					'date'		=> $this->convert_post_date($this->get_val($this->post_data, 'taken_at_timestamp'), true),
					'author_name' => $this->get_val($additions, 'instagram_user'), //$this->get_val($this->post_data, 'user_info', '')
				];
				
				$inst_img = $this->get_val($this->post_data, 'display_url', '');
				$inst_thumb = $this->get_val($this->post_data, 'thumbnail_src', '');
				$attr1['img_urls'] = [];
				if(!empty($inst_img)){
					$attr1['stream_image_url'] = $inst_img;
					$attr1['img_urls']['original'] = [
						'url' => $inst_img, 
						'tag' => '<img'.$class.' src="'.$inst_img.'" width="'.$this->get_val($this->post_data, ['dimensions', 'width']).'" height="'.$this->get_val($this->post_data, ['dimensions', 'height']).'" alt="'.esc_attr($this->get_val($attr1, 'title')).'" data-no-retina />'
					];
					$attr1['img_urls']['original_size'] = [
						'url' => $inst_img, 
						'tag' => '<img'.$class.' src="'.$inst_img.'" width="'.$this->get_val($this->post_data, ['dimensions', 'width']).'" height="'.$this->get_val($this->post_data, ['dimensions', 'height']).'" alt="'.esc_attr($this->get_val($attr1, 'title')).'" data-no-retina />'
					];
					$attr1['img_urls']['large'] = [
						'url' => $inst_img, 
						'tag' => '<img'.$class.' src="'.$inst_img.'" width="'.$this->get_val($this->post_data, ['dimensions', 'width']).'" height="'.$this->get_val($this->post_data, ['dimensions', 'height']).'" alt="'.esc_attr($this->get_val($attr1, 'title')).'" data-no-retina />'
					];
				}
				if(!empty($inst_thumb)){
					$attr1['stream_image_url'] = (!isset($attr1['stream_image_url'])) ? $inst_thumb : $attr1['stream_image_url'];
					$attr1['img_urls']['thumb'] = [
						'url' => $inst_thumb,
						'tag' => '<img'.$class.' src="'.$inst_thumb.'" width="'.$this->get_val($this->post_data, ['dimensions', 'width']).'" height="'.$this->get_val($this->post_data, ['dimensions', 'height']).'" alt="'.esc_attr($this->get_val($attr1, 'title')).'" data-no-retina />'
					];
					$attr1['img_urls']['thumbnail'] = [
						'url' => $inst_thumb,
						'tag' => '<img'.$class.' src="'.$inst_thumb.'" width="'.$this->get_val($this->post_data, ['dimensions', 'width']).'" height="'.$this->get_val($this->post_data, ['dimensions', 'height']).'" alt="'.esc_attr($this->get_val($attr1, 'title')).'" data-no-retina />'
					];
				}
			break;
			case 'flickr':
				$attr1 = [
					'title'		=> $this->get_val($this->post_data, 'title'),
					'content'	=> $this->get_val($this->post_data, ['description', '_content']),
					'date'		=> $this->convert_post_date($this->get_val($this->post_data, 'datetaken')),
					'author_name' => $this->get_val($this->post_data, 'ownername'),
					'link'		=> 'http://flic.kr/p/'.$this->base_encode($this->get_val($this->post_data, 'id')),
					'views'		=> $this->get_val($this->post_data, 'views'),
					'stream_image_url' => $this->get_val($this->post_data, 'url_o'),
					'img_urls'	=> [
						'square' 	 => ['url' => $this->get_val($this->post_data, 'url_sq'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_sq').'" width="'.$this->get_val($this->post_data, 'width_sq').'" height="'.$this->get_val($this->post_data, 'height_sq').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'large-square' => ['url' => $this->get_val($this->post_data, 'url_q'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_q').'" width="'.$this->get_val($this->post_data, 'width_q').'" height="'.$this->get_val($this->post_data, 'height_q').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'thumbnail'  => ['url' => $this->get_val($this->post_data, 'url_t'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_t').'" width="'.$this->get_val($this->post_data, 'width_t').'" height="'.$this->get_val($this->post_data, 'height_t').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'small' 	 => ['url' => $this->get_val($this->post_data, 'url_s'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_s').'" width="'.$this->get_val($this->post_data, 'width_s').'" height="'.$this->get_val($this->post_data, 'height_s').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'small-320'  => ['url' => $this->get_val($this->post_data, 'url_n'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_n').'" width="'.$this->get_val($this->post_data, 'width_n').'" height="'.$this->get_val($this->post_data, 'height_n').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'medium' 	 => ['url' => $this->get_val($this->post_data, 'url_m'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_m').'" width="'.$this->get_val($this->post_data, 'width_m').'" height="'.$this->get_val($this->post_data, 'height_m').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'medium-640' => ['url' => $this->get_val($this->post_data, 'url_z'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_z').'" width="'.$this->get_val($this->post_data, 'width_z').'" height="'.$this->get_val($this->post_data, 'height_z').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'medium-800' => ['url' => $this->get_val($this->post_data, 'url_c'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_c').'" width="'.$this->get_val($this->post_data, 'width_c').'" height="'.$this->get_val($this->post_data, 'height_c').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'large' 	 => ['url' => $this->get_val($this->post_data, 'url_l'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_l').'" width="'.$this->get_val($this->post_data, 'width_l').'" height="'.$this->get_val($this->post_data, 'height_l').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />'],
						'original'	 => ['url' => $this->get_val($this->post_data, 'url_o'), 'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, 'url_o').'" width="'.$this->get_val($this->post_data, 'width_o').'" height="'.$this->get_val($this->post_data, 'height_o').'" alt="'.esc_attr($this->get_val($this->post_data, 'title')).'" data-no-retina />']
					]
				];
			break;
			case 'youtube':
				$attr1 = [
					'title'		=> $this->get_val($this->post_data, ['snippet', 'title']),
					'excerpt'	=> $this->get_val($this->post_data, ['snippet', 'description']),
					'content'	=> $this->get_val($this->post_data, ['snippet', 'description']),
					'date'		=> $this->convert_post_date($this->get_val($this->post_data, ['snippet', 'publishedAt']))
				];
				
				if($this->get_val($additions, 'yt_type') == 'channel'){
					$link_raw = $this->get_val($this->post_data, 'id');
					$attr1['link'] = $this->get_val($link_raw, 'videoId');
					if($attr1['link'] !== '') $attr1['link'] = '//youtube.com/watch?v='.$attr1['link'];
				}else{
					$link_raw = $this->get_val($this->post_data, 'resourceId');
					$attr1['link'] = $this->get_val($link_raw, 'videoId');
					if($attr1['link'] !== '') $attr1['link'] = '//youtube.com/watch?v='.$attr1['link'];
				}
				
				$thumbs = $this->get_val($this->post_data, ['snippet', 'thumbnails']);
				$attr1['img_urls'] = [];
				if(!empty($thumbs)){
					foreach($thumbs as $name => $vals){
						$attr1['stream_image_url'] = (!isset($attr1['stream_image_url'])) ? $this->get_val($vals, 'url') : $attr1['stream_image_url'];
						$attr1['img_urls'][$name] = [
							'url' => $this->get_val($vals, 'url'),
						];
						switch($this->get_val($additions, 'yt_type')){
							case 'channel':
								$attr1['img_urls'][$name]['tag'] = '<img'.$class.' src="'.$this->get_val($vals, 'url').'" alt="'.esc_attr($this->get_val($attr1, 'title')).'" data-no-retina />';
							break;
							case 'playlist':
								$attr1['img_urls'][$name]['tag'] = '<img'.$class.' src="'.$this->get_val($vals, 'url').'" width="'.$this->get_val($vals, 'width').'" height="'.$this->get_val($vals, 'height').'" alt="'.esc_attr($this->get_val($attr1, 'title')).'" data-no-retina />';
							break;
						}
					}
				}
			break;
			case 'vimeo':
				$attr1 = [
					'title'		=> $this->get_val($this->post_data, 'title'),
					'excerpt'	=> $this->get_val($this->post_data, 'description'),
					'content'	=> $this->get_val($this->post_data, 'description'),
					'date'		=> $this->convert_post_date($this->get_val($this->post_data, 'upload_date')),
					'likes'		=> $this->get_val($this->post_data, 'stats_number_of_likes'),
					'views'		=> $this->get_val($this->post_data, 'stats_number_of_plays'),
					'num_comments'	=> $this->get_val($this->post_data, 'stats_number_of_comments'),
					'link'		=> $this->get_val($this->post_data, 'url'),
					'author_name'	=> $this->get_val($this->post_data, 'user_name'),
					'img_urls'	=> []
				];
				
				if(!empty($img_sizes)){
					foreach($img_sizes as $name => $vals){
						$attr1['stream_image_url'] = (!isset($attr1['stream_image_url'])) ? $this->get_val($this->post_data, $name) : $attr1['stream_image_url'];
						$attr1['img_urls'][$name] = [
							'url' => $this->get_val($this->post_data, $name),
							'tag' => '<img'.$class.' src="'.$this->get_val($this->post_data, $name).'" alt="'.esc_attr($this->get_val($attr1, 'title')).'" data-no-retina />'
						];
					}
				}
			break;
		}
		
		$attr = (isset($attr1)) ? array_merge($attr, $attr1) : $attr;
		
		return apply_filters('revslider_slide_return_stream_data_post', $attr, $stream_type, $additions, $img_sizes);
	}

	/**
	 * save layers to the database
	 * @return void
	 * @since: 5.0
	 */
	public function save_layers(){
		global $wpdb;
		
		$this->layers	= apply_filters('revslider_slide_saveLayers', $this->layers, $this->static_slide, $this);
		
		$wpdb->update($wpdb->prefix . $this->table_slides, ['layers' => json_encode($this->layers)], ['id' => $this->id]);
	}
	
	
	/**
	 * set parameter
	 * @param array|string $name a single key, or a path of keys
	 * @since: 5.0
	 * @return void
	 */
	public function set_param($name, $value){
		if(is_array($name)){
			$params = &$this->params;
			foreach($name as $i => $key){
				if(!isset($params[$key])) $params[$key] = [];
				if(is_array($params)){
					$params = &$params[$key];
				}elseif(is_object($params)){
					$params = &$params->$key;
				}
			}
			$params = $value;
		}else{
			$this->params[$name] = $value;
		}
	}

	/**
	 * get parameter from params array. if no default, then the param is a must!
	 * @return mixed
	 */
	public function get_param($name, $default = ''){
		if(!is_array($name)){
			return $this->get_val($this->params, $name, $default);
		}else{
			$a = $this->params;
			foreach($name as $k => $v){
				$a = $this->get_val($a, $v, $default);
			}
			
			return $a;
		}
	}
	
	
	/**
	 * save params to the database
	 * @return void
	 * @since: 5.0
	 */
	public function save_params(){
		global $wpdb;
		
		$this->params = apply_filters('revslider_slide_saveParams', $this->params, $this->static_slide, $this);

		if(!empty($this->params)){
			if($this->current_user_can_not('administrator')){
				$actions = $this->get_val($this->params, 'actions', []);
				if(!empty($actions)){
					foreach($actions ?? [] as $k => $action){
						if($this->get_val($action, 'a') !== 'callback') continue;
						unset($actions[$k]);
					}

					$this->params['actions'] = $actions;
				}
			}
		}

		$wpdb->update($wpdb->prefix . $this->table_slides, ['params' => json_encode($this->params)], ['id' => $this->id]);
		$this->invalidate_group_cache();
	}
	
	
	/**
	 * save settigns to the database
	 * @return void
	 * @since: 6.0
	 */
	public function save_settings(){
		global $wpdb;
		
		$this->settings = apply_filters('revslider_slide_save_settings', $this->settings, $this->static_slide, $this);

		$wpdb->update($wpdb->prefix . $this->table_slides, ['settings' => json_encode($this->settings)], ['id' => $this->id]);
	}
	
	
	/**
	 * get children array
	 * @return RevSliderSlide[]
	 */
	public function get_children(){
		if($this->children === null){
			$slider = new RevSliderSlider();
			$slider->init_by_id($this->slider_id);
			$this->children = $slider->get_slide_children($this->id);
		}
		
		return apply_filters('revslider_slide_getArrChildren', $this->children, $this);
	}
	
	
	/**
	 * create the slide (from image)
	 * @return int|false the new slide id
	 */
	public function create_slide($slider_id, $obj = '', $static = false, $id = false){
		global $wpdb;
		
		//get max order
		$slider		= new RevSliderSlider();
		$slider->init_by_id($slider_id);
		$max_order	= $slider->get_max_order();
		$order		= $max_order + 1;
		$params		= [];
		$settings	= [];
		
		$insert = [
			'params'	=> json_encode($params),
			'slider_id'	=> $slider_id,
			'layers'	=> '',
			'settings'	=> json_encode($settings),
			'static'	=> $static
		];
		
		if($id !== false) $insert['id'] = $id;
		if(!$static) $insert['slide_order'] = $order;
		
		$insert	= apply_filters('revslider_slide_createSlide', $insert, $slider_id, $static, $this);
		$done	= $wpdb->insert($wpdb->prefix . $this->table_slides, $insert);
		
		return ($done) ? $wpdb->insert_id : false;
	}
	
	
	/**
	 * init a static slide
	 * @return void
	 */
	public function init_by_static_id($slide_id){
		global $wpdb, $SR_GLOBALS;
		
		$this->validate_numeric($slide_id, 'Slide ID');
		if($SR_GLOBALS['v6'] === true && RevSliderPluginUpdateV6::do_v6_tables_exist()){
			$slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . RevSliderFront::TABLE_STATIC_SLIDES ." WHERE id = %d", $slide_id), ARRAY_A);
		}else{
			$slide = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides ." WHERE id = %d AND static = 1", $slide_id), ARRAY_A);
		}

		$this->init_by_data($slide);
	}
	
	
	/**
	 * initialize slide by the given data (database entry)
	 * decodes the params/layers JSON of a fetched row into this object
	 * @return void
	 */
	public function init_by_data($slide){
		$slide			 = apply_filters('revslider_slide_init_by_data', apply_filters('revslider_slide_initByData', $slide));
		$this->id		 = $this->get_val($slide, 'id');
		$this->slider_id = $this->get_val($slide, 'slider_id');
		$this->order	 = $this->get_val($slide, 'slide_order', '');
		$this->params	 = $this->get_val($slide, 'params');
		$this->params	 = (!is_array($this->params)) ? (array)json_decode($this->params, true) : $this->params;
		$this->layers	 = ($this->init_layer && !is_array($slide['layers'])) ? $this->layers = json_decode($this->get_val($slide, 'layers'), true) : $this->get_val($slide, 'layers');
		$this->layers	 = (empty($this->layers)) ? [] : $this->layers;
		$this->settings	 = $this->get_val($slide, 'settings');
		$this->settings	 = (!is_array($this->settings)) ? (array)json_decode($this->settings, true) : $this->settings;
		$this->params['version'] = $this->get_val($this->settings, 'version', $this->get_val($this->params, 'version'));
		$bglayer	 = $this->get_bg_layer();
		$bglayertype = 'trans';
		$bglayertype = (!empty($this->get_val($bglayer, ['bg', 'image']))) ? 'image' : $bglayertype;
		$bglayertype = (!empty($this->get_val($bglayer, ['bg', 'video']))) ? 'video' : $bglayertype;
		$image_url	 = ($bglayertype === 'video') ? $this->get_val($bglayer, ['bg', $bglayertype, 'poster', 'src']) : $this->get_val($bglayer, ['bg', $bglayertype, 'src']);
		$this->image_id	 = $this->get_val($bglayer, ['bg', $bglayertype, 'lib_id']);
		$image_resolution = $this->get_val($bglayer, ['bg', $bglayertype, 'lib_size'], 'full');
		
		/**
		 * fix for [{0:'a',1:'b'}] structures that can occur
		 **/
		$t_keys = ['duration', 'easeIn', 'easeOut', 'rotation', 'slots', 'transition'];
		foreach($t_keys as $tk){
			$tlc = $this->get_val($this->params, ['timeline', $tk, 0]);
			if(is_object($tlc) || is_array($tlc)){
				$a = [];
				if(!empty($this->params['timeline'][$tk][0])){
					foreach($this->params['timeline'][$tk][0] as $tkv){
						$a[] = $tkv;
					}
				}
				$this->params['timeline'][$tk] = $a;
			}
		}
		
		//get image url and thumb url
		if($image_resolution !== 'full' || $image_url === ''){
			if(!empty($this->image_id)){
				$image_url = $this->get_url_attachment_image($this->image_id, $image_resolution);
				if(empty($image_url)){
					$image_url = ($bglayertype === 'video') ? $this->get_val($bglayer, ['bg', $bglayertype, 'poster', 'src']) : $this->get_val($bglayer, ['bg', $bglayertype, 'src']);
					$this->image_id	= $this->get_image_id_by_url($image_url);
					if($this->image_id !== false){
						$image_url = $this->get_url_attachment_image($this->image_id, $image_resolution);
						$this->set_val($this->params, ['bg', 'imageId'], $this->image_id);
						$this->save_params();
					}
				}
				
				$this->image_thumb = $this->get_url_attachment_image($this->image_id, 'medium');
			}else{
				$this->image_id	= $this->get_image_id_by_url($image_url);
				
				if($this->image_id !== false && $this->image_id !== null){
					//save the image ID in the Slide Settings
					if($bglayertype !== 'trans'){
						$this->set_val($bglayer, ['bg', $bglayertype, 'lib_id'], $this->image_id);
						$this->update_bg_layer($bglayer);
					}
					$image_url = $this->get_url_attachment_image($this->image_id, $image_resolution);
				}
			}
		}
		
		$image_url			= (is_ssl()) ? str_replace('http://', 'https://', $image_url) : $image_url;
		$this->image_url	= $image_url;
		$this->image_path	= $this->get_image_path_from_url($this->image_url);
		$real_path			= $this->get_content_path().$this->image_path;
		$this->image_path	= (file_exists($real_path) == false || is_file($real_path) == false) ? '' : $this->image_path;
		$this->image_filename = basename($this->image_url);
		$this->inited		= true;
	}
	
	
	/**
	 * get all slides from given Slider for the library
	 * @return array
	 * @since: 6.0
	 **/
	public function get_slides_for_library($slider_ids = [], $get_static_slide = false){
		global $wpdb;
		
		$slides		= [];
		$addition	= ($get_static_slide === false) ? " AND static = false" : '';

		if(!empty($slider_ids)){
			foreach($slider_ids as $sid){
				$cur_slides = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . RevSliderFront::TABLE_SLIDES ." WHERE slider_id = %s".$addition, $sid), ARRAY_A);
				
				if(!empty($cur_slides)){
					foreach($cur_slides as $c_slide){
						$slides[] = $c_slide;
					}
				}
			}
		}
		
		if(empty($slides)) return $slides;
	
		$i = 1;
		foreach($slides as $key => $slide){
			$_img = $this->get_val($slide, 'img');
			if(!empty($_img)) $slides[$key]['img']	= $this->_check_file_path($slides[$key]['img'], true);
			$params = $this->json_decode_slashes($this->get_val($slide, 'params', []));
			$params = (empty($params)) ? [] : $params;
			
			$slides[$key]['parent']	= $this->get_val($slide, 'slider_id');
			$slides[$key]['installed'] = $this->get_val($slide, 'id');
			
			if($this->get_val($slide, 'static', false) === true){
				$slides[$key]['title'] = __('Global Layers', 'revslider');
				$slides[$key]['bg'] = ['type' => 'image', 'src' => RS_PLUGIN_URL_CLEAN.'admin/assets/images/sources/static.png', 'style' => []];
			}else{
				if($this->get_val($params, 'title', false) === false) $params['title'] = 'Slide '.$i;
				$slides[$key]['title']	= $this->get_val($params, 'title');
				
				$rslide = new RevSliderSlide();
				$rslide->init_by_id($slide['id']);
				$image = $rslide->get_overview_image_attributes_v7('gallery');
				$slides[$key]['bg'] = [];
				if(!empty($image)){
					$slides[$key]['bg'] = $image;
				}
			}
			
			unset($slides[$key]['params']);
			unset($slides[$key]['layers']);
			unset($slides[$key]['settings']);
			$i++;
		}
		
		return $slides;
	}
	
	/**
	 * get all slides of all given slider_ids raw
	 * @return array
	 **/
	public function get_all_slides_raw($slider_ids){
		$first_slides	= $this->get_slides_by_slider_id($slider_ids, false, false, false, false, true);
		$slide_ids		= $this->get_slide_ids_by_slider_id($slider_ids, false);
		return ['first_slides' => $first_slides, 'slide_ids' => $slide_ids];
	}
	
	/**
	 * get all slide ids from the slider list
	 * @return array
	 * @since: 6.3.10
	 **/
	public function get_slide_ids_by_slider_id($slider_ids, $static = true){
		global $wpdb, $SR_GLOBALS;

		$addition = ($static === false && $SR_GLOBALS['v6'] === false) ? 'AND static != 1' : '';
		if(is_array($slider_ids) && !empty($slider_ids)){
			$in  = str_repeat('%d,', count($slider_ids) - 1) . '%d';
			$slides = $wpdb->get_results($wpdb->prepare("SELECT `id`, `slider_id`, `slide_order` FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id IN(".$in.") ".$addition." ORDER BY slider_id,slide_order ASC", $slider_ids), ARRAY_A);
		}elseif(!empty($slider_ids) && 'all' !== $slider_ids){
			$slides = $wpdb->get_results($wpdb->prepare("SELECT `id`, `slider_id`, `slide_order` FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id = %d ".$addition." ORDER BY slide_order ASC", $slider_ids), ARRAY_A);
		}else{
			$slides = $wpdb->get_results("SELECT `id`, `slider_id`, `slide_order` FROM ".$wpdb->prefix . $this->table_slides . " WHERE 1=1 ".$addition." ORDER BY slide_order ASC", ARRAY_A);
		}

		return $slides;
	}
	
	/**
	 * the rows of the given slide ids, straight from the database
	 * @return array
	 */
	public function get_slides_by_slide_ids_raw($slide_ids, $layers){
		global $wpdb;

		if(is_array($slide_ids) && !empty($slide_ids)){
			$in  = str_repeat('%d,', count($slide_ids) - 1) . '%d';
			$slides_data_sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE id IN(".$in.") ORDER BY slider_id,slide_order ASC", $slide_ids);
		}else{
			$slides_data_sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE id = %d ORDER BY slide_order ASC", $slide_ids);
		}

		$slides = $wpdb->get_results($slides_data_sql, ARRAY_A);

		return (empty($slides)) ? [] : $this->process_slides_raw($slides, $layers);
	}

	public function get_slides_raw($slider_id, $layers = false){
		global $wpdb;
		
		if(is_array($slider_id) && !empty($slider_id)){
			$in  = str_repeat('%d,', count($slider_id) - 1) . '%d';
			$slides_data_sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id IN(".$in.") ORDER BY slider_id,slide_order ASC", $slider_id);
		}else{
			$slides_data_sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id = %d ORDER BY slide_order ASC", $slider_id);
		}

		$slides = $wpdb->get_results($slides_data_sql, ARRAY_A);
		
		return (empty($slides)) ? [] : $this->process_slides_raw($slides, $layers);
	}

	/**
	 * decode the JSON columns of raw slide rows and drop the layers when they were not requested
	 * @return array
	 */
	public function process_slides_raw($slides, $layers){
		$_slides = [];
		foreach($slides ?? [] as $slide){
			$params = json_decode($this->get_val($slide, 'params'), true);
			$global = $this->get_val($slide, 'static');
			$global = (empty($global)) ? false : $this->_truefalse($global);
			$id		= (int)$this->get_val($slide, 'id');
			$order	= (int)$this->get_val($slide, 'slide_order');

			$params['id'] = $id;
			$params['global'] = $global;
			$params['order'] = $order;

			$_slides[$id] = [
				'id'		=> $id,
				'global'	=> $global,
				'order'		=> $order,
				'settings'	=> $params,
				'sid'		=> $this->get_val($slide, 'slider_id')
			];
			if($layers) $_slides[$slide['id']]['layers'] = json_decode($this->get_val($slide, 'layers'), true);
		}

		return $_slides;
	}

	/**
	 * get all slides from specific slider id,
	 * @return RevSliderSlide[]
	 * $wpml is deprecated and should always be false!
	 **/
	public function get_slides_by_slider_id($slider_id, $published = false, $wpml = false, $first = false, $init_layer = true, $fetch_single = false){
		global $wpdb, $SR_GLOBALS;

		$slides		= [];
		//$children	= [];
		$first_sql	= ($fetch_single === true) ? " AND `slide_order` = 1" : '';
		$addition	= ($SR_GLOBALS['v6'] === false && in_array($SR_GLOBALS['v6db'], [false, 'unknown'])) ? " AND (static = 0 OR static = '')" : "";

		if(is_array($slider_id) && !empty($slider_id)){
			$in  = str_repeat('%d,', count($slider_id) - 1) . '%d';
			$slides_data_sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id IN(".$in.")".$first_sql." ". $addition ." ORDER BY slider_id,slide_order ASC", $slider_id);
		}elseif($slider_id === 'all'){
			$slides_data_sql = "SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE 1 = 1 ". $addition ." ".$first_sql." ORDER BY slider_id ASC";
		}else{
			$slides_data_sql = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix . $this->table_slides . " WHERE slider_id = %d".$first_sql." ". $addition ." ORDER BY slide_order ASC", $slider_id);
		}
		
		$cache_key = $this->get_wp_cache_key('get_slides_by_slider_id', $slides_data_sql);
		$slides_data = wp_cache_get($cache_key, self::CACHE_GROUP);
		if(false === $slides_data){
			$slides_data = $wpdb->get_results($slides_data_sql, ARRAY_A);
			wp_cache_set($cache_key, $slides_data, self::CACHE_GROUP);
		}

		/**
		 * front-end only: warm the post + meta cache for every media item referenced by lib_id across
		 * all slides in a single query, so the per-slide wp_get_attachment_image_src() lookups in
		 * init_by_data() hit the object cache instead of querying once per attachment (avoids N+1).
		 * Skipped when only the first slide is needed (no N+1 possible). Priming extra/invalid ids is
		 * harmless - it only warms cache and never changes output.
		 */
		if($first === false && !is_admin() && function_exists('_prime_post_caches')){
			$prime_ids = [];
			foreach($slides_data ?? [] as $sd){
				if(empty($sd['layers']) || !is_string($sd['layers'])) continue;
				if(preg_match_all('/"lib_id"\s*:\s*"?(\d+)/', $sd['layers'], $m)){
					foreach($m[1] as $lid){
						$lid = (int)$lid;
						if($lid > 0) $prime_ids[$lid] = true;
					}
				}
			}
			if(!empty($prime_ids)) _prime_post_caches(array_keys($prime_ids), false, true);
		}

		foreach($slides_data ?? [] as $slide_data){
			$slide	= new RevSliderSlide();
			$slide->init_layer = $init_layer;
			$slide->init_by_data($slide_data);
			$state = $slide->get_param(['publish', 'state'], 'published');

			if($first && $state == 'unpublished') continue;
			if($published == true && $state == 'unpublished') continue;
			
			$slides[$slide->get_id()] = $slide;
			
			if($first) break; //we only want the first slide
		}

		return $slides;
	}
	
	
	/**
	 * get params for export
	 * @return array
	 */
	public function get_params_for_export(){
		return apply_filters('revslider_slide_getParamsForExport', apply_filters('revslider_slide_getParamsForExport', $this->params, $this), $this);
	}
	
	
	/**
	 * modify layer links for export
	 * @return array
	 */
	public function get_layers_for_export(){
		$layers = [];
		foreach($this->layers ?? [] as $lid => $layer){
			$img_url = $this->get_val($layer, ['content', 'src']);

			$layer['content'] = (array)$this->get_val($layer, 'content');
			$layer['content']['src'] = (empty($img_url)) ? '' : $this->get_image_path_from_url($img_url);
			
			$layers[$lid] = $layer;
		}
		
		return apply_filters('revslider_slide_get_layers_for_export', apply_filters('revslider_slide_getLayersForExport', $layers, $this), $this);
	}
	
	public function get_overview_image_attributes_v7(){
		$image	= $this->check_valid_image($this->get_param(['thumb', 'admin'], false));
		$image	= ($image == false || $image == '') ? $this->check_valid_image($this->get_param(['thumb', 'src'], false)) : $image;
		$image	= (in_array($this->get_param(['bg', 'type'], 'trans'), ['image', 'html5', 'youtube', 'vimeo']) && ($image == false || $image == '')) ? $this->check_valid_image($this->get_param(['bg', 'image'], false)) : $image;
		$image	= (empty($image)) ? $this->get_param(['thumb', 'default'], []) : $image;
		
		return ($image !== false) ? $image : '';
	}

	/**
	 * get the image attributes needed to show preview images
	 * bg: { type (color, image,transparent),  src: (image source)...}
	 * this is the V6 version!
	 * @return array
	 **/
	public function get_overview_image_attributes_v6($layouttype){
		$image		= ['color' => '', 'src' => ''];
		$thumb_src	= $this->check_valid_image($this->get_param(['thumb', 'customAdminThumbSrc'], false));
		$thumb_src	= ($thumb_src == false || $thumb_src == '') ? $this->check_valid_image($this->get_param(['thumb', 'customThumbSrc'], false)) : $thumb_src;				
		$bgtype 	= $this->get_param(['bg', 'type'], 'solid');
		$thumb_src	= (($bgtype == 'image' || $bgtype == 'html5' || $bgtype == 'youtube' || $bgtype == 'vimeo') && ($thumb_src == false || $thumb_src == '')) ? $this->check_valid_image($this->get_param(['bg', 'image'], false)) : $thumb_src;
		$image['src'] = ($thumb_src !== false) ? $thumb_src : '';
		$bg			= $this->get_param('bg', []);
		
		if($thumb_src === false){
			if($bgtype == 'trans'){
				$image['color']	= 'transparent';
			}elseif($bgtype == 'solid'){
				$image['color']	= $this->get_val($bg, 'color', '#ffffff');
				$image['src']	= '';
			}elseif($image['src'] == '' || !pathinfo($image['src'], PATHINFO_EXTENSION)){
				$image['src'] = '';
				//first check background slide 
				if(!empty($bg)){
					$_src = RS_PLUGIN_URL_CLEAN.'public/assets/sources/';
					if(in_array($layouttype, ['posts', 'specific_posts', 'specific_post', 'woocommerce', 'facebook', 'twitter', 'instagram', 'flickr', 'youtube', 'vimeo'])){
						if(in_array($layouttype, ['posts', 'specific_posts', 'specific_post'])){
							$image['src'] = $_src.'post.png';
						}elseif($layouttype === 'woocommerce'){
							$image['src'] = $_src.'woo.png';
						}else{
							$image['src'] = $_src. $layouttype .'.png';
						}
					}
				}else{
					$image = $this->get_image_attributes_v6($layouttype);
				}
			}
		}
		
		return (empty($image['color'])) ? $image['src'] : $image;
	}
	
	
	/**
	 * get the image attributes needed to show preview images
	 * @return array
	 **/
	public function get_image_attributes_v6($layouttype){
		//1 admin thumbnail
		//2 nav thumbnail
		//3 check the background type
		$type	= $this->get_param(['bg', 'type'], 'transparent');
		$fit	= $this->get_param(['bg', 'fit'], 'cover');
		$position = $this->get_param(['bg', 'position'], 'center top');
		$thumb_on = $this->get_param(['thumb', 'customAdminThumbSrc'], '');
		$th		= $this->get_param(['thumb', 'customThumbSrc'], '');
		$thumb	= '';
		$color	= '';
		
		if($type == 'trans' || $type == 'transparent'){
			$thumb = '';
			$color = 'transparent';
			$thumb = ($thumb_on !== '' && pathinfo($th, PATHINFO_EXTENSION)) ? $th : $thumb;
		}else{
			$_src = RS_PLUGIN_URL_CLEAN.'public/assets/sources/';
			switch($layouttype){
				case 'gallery':
					$image_id = $this->get_param(['bg', 'imageId']);
					if(empty($image_id)){
						$thumb = $this->get_param(['bg', 'image']);
						$image_id = $this->get_image_id_by_url($thumb);
						if($image_id !== false){
							$thumb = $this->get_url_attachment_image($image_id, 'medium');
						}
					}else{
						$thumb = $this->get_url_attachment_image($image_id, 'medium');
					}
					$thumb = ($thumb_on != '' && pathinfo($th, PATHINFO_EXTENSION)) ? $th : $thumb;
				break;
				case 'posts':
					$thumb = $_src.'post.png';
				break;
				case 'woocommerce':
					$thumb = $_src.'woo.png';
				break;
				case 'facebook':
					$thumb = $_src.'facebook.png';
				break;
				case 'twitter':
					$thumb = $_src.'twitter.png';
				break;
				case 'instagram':
					$thumb = $_src.'instagram.png';
				break;
				case 'flickr':
					$thumb = $_src.'flickr.png';
				break;
				case 'youtube':
					$thumb = $_src.'youtube.png';
				break;
				case 'vimeo':
					$thumb = $_src.'vimeo.png';
				break;
			}
			
			if($thumb == '' || !pathinfo($thumb, PATHINFO_EXTENSION)) $thumb = $this->get_param(['bg', 'image']);
			
			if($type == 'solid' && $thumb_on == ''){
				$color =  $this->get_param(['bg', 'color'], 'transparent');
				$thumb = '';
			}
		}
		
		return apply_filters('revslider_slide_get_image_attributes', [
			'color'	=> $color,
			'src'	=> $thumb,
		], $this);
	}
	
	
	/**
	 * get all used fonts in the current Slide
	 * @return array
	 * @since: 5.1.0
	 */
	public function get_used_fonts($full = false){
		$fonts		= [];
		$all_fonts	= $this->get_font_familys();
		
		if(!empty($this->layers)){
			foreach($this->layers as $key => $layer){
				$font = $this->get_val($layer, ['idle', 'fontFamily'], 'Roboto');
				
				$_fonts = [];
				$_fonts[$font] = [
					'fontWeight' => $this->get_val($layer, ['idle', 'fontWeight'], '400'),
					'fontStyle'	=> $this->get_val($layer, ['idle', 'fontStyle'], ''),
					'addition'	=> [],
				];
				
				//$text = strtolower(str_replace(' ', '', $this->get_val($layer, 'text', '')));
				$text = $this->get_val($layer, 'text', '');
				
				//search for font family
				//search for font weight
				preg_match_all('/<[^>]+((?<=style=").*?(?=")|(?<=style=\').*?(?=\'))/i', $text, $matches);
				if(isset($matches[1])) $matches = $matches[1];
				
				if(!empty($matches)){
					foreach($matches as $match){
						$match = explode(';', $match);
						if(empty($match)) continue;
						$found = [];
						foreach($match as $m => $v){
							if(empty($v)) continue;
							$_match = explode(':', $v);
							if(empty($_match)) continue;
							
							$style = trim(strtolower($this->get_val($_match, 0)));
							$style_value = trim($this->get_val($_match, 1));
							if($style === 'font-family'){
								$found['font-family'] = $style_value;
							}elseif($style === 'font-weight'){
								$found['font-weight'] = $style_value;
							}elseif($style === 'font-style'){
								$found['font-style'] = $style_value;
							}
						}
						if(!empty($found)){
							$use_font = $font;
							if(isset($found['font-family'])){
								if(!isset($_fonts[$found['font-family']])){
									$_fonts[$found['font-family']] = ['fontWeight' => [], 'fontStyle'	=> false, 'addition' => []];
								}
								$use_font = $found['font-family'];
							}
							
							if(isset($found['font-weight'])){
								if(strtolower($found['font-weight']) === 'bold') $found['font-weight'] = '600';
								if(!in_array($found['font-weight'], $_fonts[$use_font]['addition'])){
									$_fonts[$use_font]['addition'][] = $found['font-weight'];
								}
							}
							
							if(isset($found['font-style'])){
								if(!in_array($found['font-style'], $_fonts[$use_font]['addition'])){
									//$_fonts[$use_font]['addition'][] = $found['font-style'];
									$_fonts[$use_font]['fontStyle'] = true;
								}
							}
						}
					}
				}
				
				if(!empty($_fonts)){
					foreach($_fonts as $font => $_font_values){
						foreach($all_fonts as $f){
							if(strtolower(str_replace(['"', "'", ' '], '', $f['label'])) == strtolower(str_replace(['"', "'", ' '], '', $font)) && ($f['type'] == 'googlefont' || $f['type'] === 'custom' && isset($f['url']))){
								
								if(!isset($fonts[$f['label']])){
									$fonts[$f['label']] = ['variants' => [], 'subsets' => []];
								}
								
								if($full){ //if full, add all.
									//switch the variants around here!
									$mv = [];
									if(!empty($f['variants'])){
										foreach($f['variants'] as $fvk => $fvv){
											$mv[$fvv] = $fvv;
										}
									}
									$fonts[$f['label']] = ['variants' => $mv, 'subsets' => $f['subsets']];
								}else{ //Otherwise add only current font-weight plus italic or not
									$fw = $this->normalize_device_settings($this->get_val($_font_values, 'fontWeight', '400'), ['d' => true, 'n' => true, 't' => true, 'm' => true], 'array', ['400']);
									$fs = $this->get_val($_font_values, 'fontStyle', '');
									
									$_addition = $this->get_val($_font_values, 'addition');
									if(!empty($_addition) && is_array($_addition)){
										foreach($_addition as $_add){
											$fw[$_add] = $_add;
										}
									}
									
									if($fs == true){
										foreach($fw as $mf => $w){
											//we check if italic is available at all for the font!
											if($w == '400'){
												if(array_search('italic', $f['variants']) !== false)
													$fw[$mf] = 'italic';
											}else{
												if(array_search($w.'italic', $f['variants']) !== false){
													$fw[$mf.'italic'] = $w.'italic';
												}
											}
										}
									}
									
									
									foreach($fw as $mf => $w){
										$fonts[$f['label']]['variants'][$w] = true;
									}
									
									if(isset($f['subsets'])){
										$fonts[$f['label']]['subsets'] = $f['subsets']; //subsets always get added, needs to be done then by the Slider Settings
									}
								}

								if($f['type'] === 'custom'){
									$fonts[$f['label']]['url'] = $f['url'];
									$fonts[$f['label']]['load'] = (isset($f['frontend']) && $f['frontend'] === true) ? true : false;
								}
								
								break;
							}
						}
					}
				}
			}
		}
			
		return apply_filters('revslider_slide_getUsedFonts', $fonts, $this);
	}
	
	
	/**
	 * set slide image by image id
	 * @return bool
	 */
	private function set_image_by_image_id($id){
		$id			= apply_filters('revslider_slide_setImageByImageID', $id, $this);
		$resolution = $this->get_val($this->params, ['bg', 'imageSourceType'], 'full');
		
		$this->image_id		= $id;
		$this->image_url	= $this->get_url_attachment_image($id, $resolution);
		$this->image_url	= (is_ssl()) ? str_replace('http://', 'https://', $this->image_url) : $this->image_url;
		$this->image_thumb	= $this->get_url_attachment_image($id, 'medium');
		
		if(empty($this->image_url)) return(false);
		
		if(!isset($this->params['bg'])) $this->params['bg'] = [];
		$this->params['bg']['type'] = 'image';
		$this->params['bg']['image'] = $this->image_url;
		
		$this->image_path	= $this->get_image_path_from_url($this->image_url);
		$real_path			= $this->get_content_path().$this->image_path;
		
		$this->image_path	= (file_exists($real_path) == false || is_file($real_path) == false) ? '' : $this->image_path;
		
		$this->image_filename = basename($this->image_url);
	}
	
	
	/**
	 * set the image by image id
	 * @return bool
	 * @since: 5.0
	 */
	public function set_image_by_id($imageID, $size = 'full'){
		$a = apply_filters('revslider_slide_setImageByID', ['imageID' => $imageID, 'size' => $size], $this);
		
		$url = $this->get_url_attachment_image($a['imageID'], $a['size']);
		
		if(empty($url)) return false;
	
		$this->image_id			= $a['imageID'];
		$this->image_url		= $url;
		$this->image_thumb		= $this->get_url_attachment_image($a['imageID'], 'medium');
		$this->image_filename	= basename($this->image_url);
		$this->image_filepath	= $this->get_image_path_from_url($this->image_url);
		$real_path				= $this->get_content_path().$this->image_filepath;
		$this->image_filepath	= (file_exists($real_path) == false || is_file($real_path) == false) ? '' : $this->image_filepath;
			
		return true;
	}
	
	
	/**
	 * get categories by id's
	 * @return array
	 */
	public function get_categories_by_id($ids, $tax = null){
		if(empty($ids)) [];
		
		$string_ids = (is_string($ids)) ? $ids : implode(',', $ids);
		$args		= ['include' => $string_ids, 'number' => 10000];
		if(!empty($tax)) $args['taxonomy'] = (is_string($tax)) ? explode(',', $tax) : $tax;
		$cats		= get_categories($args);
		
		return (!empty($cats)) ? $this->class_to_array($cats) : $cats;
	}
	
	
	/**
	 * Encode the flickr ID for URL (base58)
	 * @return string
	 * @since    1.0.0
	 * @param    string    $num 	flickr photo id
	 */
	public function base_encode($num, $alphabet = '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ'){
		$base_count = strlen($alphabet);
		$encoded = '';
		while($num >= $base_count){
			$div = $num / $base_count;
			$mod = intval($num - ($base_count * intval($div)));
			$encoded = $alphabet[$mod] . $encoded;
			$num = intval($div);
		}
		
		return ($num) ? $alphabet[$num] . $encoded : $encoded;
	}
	
	
	/**
	 * add "a" tags to links within a text
	 * @since: 5.0
	 * @param string $text
	 * @return string
	 */
	public function add_wrap_around_url($text){
		$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
		// Check if there is a url in the text
		if(preg_match($reg_exUrl, $text, $url)){
			// make the urls hyper links
			return preg_replace($reg_exUrl, '<a href="'.$url[0].'" rel="nofollow" target="_blank">'.$url[0].'</a>', $text);
		}

		// if no urls in the text just return the text
		return $text;
	}
	
	
	/**
	 * get wp-content path
	 * @return string
	 */
	public function get_content_path(){
		if(is_multisite()) return (!defined('BLOGUPLOADDIR')) ? ABSPATH.'wp-content/' : BLOGUPLOADDIR;
		return (!defined('WP_CONTENT_DIR')) ? WP_CONTENT_DIR.'/' : ABSPATH.'wp-content/'; //FIX FOR PHP5
	}
	
	
	/**
	 * get image relative path from image url (from upload)
	 * @param string $url
	 */
	public function get_image_path_from_url($url){
		return str_replace($this->get_base_url(), '', $url);
	}
}
