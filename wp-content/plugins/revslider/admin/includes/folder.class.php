<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 * @since	  6.0
 */

if(!defined('ABSPATH')) exit();

/**
 * Folders in the module overview.
 *
 * A folder is a row in the slider table with type = 'folder'; it reuses the slider record for title and
 * alias, and keeps the ids of the modules it contains in settings['children']. Folders can be nested by
 * listing another folder's id as a child.
 */
class RevSliderFolder extends RevSliderSlider {
	
	public $folder = false;
	
	/**
	 * Initialize A slider as a Folder
	 * loads the row into $this (id, title, alias, settings, params)
	 * @return bool false when no folder with that id exists
	 **/
	public function init_folder_by_id($id){
		global $wpdb, $SR_GLOBALS;
		
		$folder = $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . RevSliderFront::TABLE_SLIDER ." WHERE `id` = %s AND `type` = 'folder'", $id), ARRAY_A);
		
		if(empty($folder)) return false;
	
		$this->id		= $this->get_val($folder, 'id');
		$this->title	= $this->get_val($folder, 'title');
		$this->alias	= $this->get_val($folder, 'alias');
		$this->settings = (array)json_decode($this->get_val($folder, 'settings', ''));
		$this->params	= (array)json_decode($this->get_val($folder, 'params', ''));
		$this->folder	= true;

		return true;
	}
	
	
	/**
	 * Get all Folders from the Slider Table
	 * @return RevSliderFolder[] one initialized instance per folder
	 **/
	public function get_folders(){
		global $wpdb;
		
		$folders = [];
		$entries = $wpdb->get_results("SELECT `id` FROM " . $wpdb->prefix . RevSliderFront::TABLE_SLIDER . " WHERE `type` = 'folder'", ARRAY_A);
		
		foreach($entries ?? [] as $folder){
			$slider		= new RevSliderFolder();
			$folder_id	= $this->get_val($folder, 'id');
			
			$slider->init_folder_by_id($folder_id);
			$folders[] = $slider;
		}
		
		return $folders;
	}
	
	
	/**
	 * one folder as a raw database row, without initializing an object
	 * @return array|null
	 **/
	public function get_folder_by_id($id){
		global $wpdb;
		
		return $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . RevSliderFront::TABLE_SLIDER ." WHERE `type` = 'folder' AND `id` = %s", $id), ARRAY_A);
	}
	
	
	/**
	 * Create a new Slider as a Folder
	 * the title gets a counter appended until its alias is unique
	 * @param string $alias  display title of the new folder
	 * @param int    $parent optional folder id to nest the new folder into
	 * @return RevSliderFolder|false $this on success
	 **/
	public function create_folder($alias = 'New Folder', $parent = 0){
		global $wpdb;
		
		$title  = esc_html($alias);
		$alias  = sanitize_title($title);
		$temp	= $title;
		$folder = false;
		$ti		= 1;
		while($this->alias_exists($alias)){ //set a new alias and title if its existing in database
			$title = $temp . ' ' . $ti;
			$alias = sanitize_title($title);
			$ti++;
		}
		
		//check if Slider with title and/or alias exists, if yes change both to stay unique
		$done = $wpdb->insert($wpdb->prefix . RevSliderFront::TABLE_SLIDER, ['title' => $title, 'alias' => $alias, 'type' => 'folder']);
		if($done == false) return $folder;
	
		$this->init_folder_by_id($wpdb->insert_id);
		$folder = $this;
		if(intval($parent) === 0) return $folder;
	
		$slider		= new RevSliderFolder();
		$slider->init_folder_by_id($parent);
		$children	= $slider->get_children();
		$children	= (!is_array($children)) ? [] : $children;
		$children[] = $this->get_id();
		$slider->add_slider_to_folder($children, $parent);

		return $folder;
	}
	
	
	/**
	 * Add a Slider ID to a Folder
	 * @param array|int $children    module/folder ids to place inside the folder
	 * @param bool      $replace_all true replaces the child list, false merges into it
	 * @return bool|int true/rows updated, false when the folder does not exist
	 **/
	public function add_slider_to_folder($children, $folder_id, $replace_all = true){
		global $wpdb;
		
		$response	= false;
		$folder		= $wpdb->get_row($wpdb->prepare("SELECT * FROM ". $wpdb->prefix . RevSliderFront::TABLE_SLIDER ." WHERE `id` = %s AND `type` = 'folder'", $folder_id), ARRAY_A);
		
		if(empty($folder)) return $response;
	
		$settings = json_decode($this->get_val($folder, 'settings'), true);
		if(!isset($settings['children'])) $settings['children'] = [];
		
		if($replace_all){
			$settings['children'] = (array)$children;
		}else{
			$children = (array)$children;
			if(!is_array($settings['children'])) (array)$settings['children'];

			foreach($children ?? [] as $child){
				if(!in_array($child, $settings['children'])) $settings['children'][] = $child;
			}
		}
		$response = $wpdb->update($wpdb->prefix . RevSliderFront::TABLE_SLIDER, ['settings' => json_encode($settings)], ['id' => $folder_id]);
		
		return ($response == false && empty($wpdb->last_error)) ? true : $response;
	}
	
	
	/**
	 * Get the Children of the folder (if any exist)
	 * @return array ids of the contained modules and folders
	 **/
	public function get_children(){
		return $this->get_val($this->settings, 'children', []);
	}
	
	/**
	 * replace the child list in $this->settings - call update_settings() to persist it
	 * @since: 6.1.4
	 * @return mixed
	 **/
	public function set_children($children){
		return $this->set_val($this->settings, 'children', $children);
	}
}