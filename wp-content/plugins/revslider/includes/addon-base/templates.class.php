<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Custom Template CRUD for the Template AddOns (particles family).
 * Not a common AddOn part, so it lives outside RevSliderAddonBase: the Manager only wires it
 * up for AddOns that opt in (config key 'templates'), and the Ajax service delegates to it.
 * Stores per AddOn under the revslider_addon_<title>_templates option.
 **/
/**
 * Optional per-AddOn template store: lets an AddOn save, list and delete its own presets in a dedicated
 * option. Only created when the AddOn opts in via the 'templates' argument of register().
 */
class RevSliderAddonTemplates extends RevSliderFunctions {

	private $slug;
	private $title;
	private $option;

	public function __construct($base){
		$this->slug		= $base->slug;
		$this->title	= $base->title;
		$this->option	= 'revslider_addon_'.$this->title.'_templates';
	}

	/**
	 * Handle a template suboperation coming through the AddOn ajax operation 'template'
	 * @return array the ajax payload for the editor
	 **/
	public function handle_ajax($suboperation){
		if($suboperation === 'save'){
			$new_id = $this->save_template($this->get_val($_REQUEST, 'data', []));
			if(empty($new_id)) return __(ucfirst($this->title).' Template could not be saved', $this->slug);
			return ['message' => __(ucfirst($this->title).' Template saved', $this->slug), 'data' => ['id' => $new_id]];
		}
		if($suboperation === 'delete'){
			$done = $this->delete_template($this->get_val($_REQUEST, 'data', []));
			return ($done) ? __(ucfirst($this->title).' Template deleted', $this->slug) : __(ucfirst($this->title).' Template could not be deleted', $this->slug);
		}
		if($suboperation === 'get') return ['data' => $this->get_templates()];
		if($suboperation === 'list'){
			$list = $this->get_templates();
			foreach($list ?? [] as $k => $v){
				if(isset($list[$k]['preset'])) unset($list[$k]['preset']);
			}
			return ['data' => $list];
		}

		return '';
	}

	/** @return array */
	private function get_templates(){
		$temps = get_option($this->option, []);
		foreach($temps ?? [] as $k => $temp){
			if(isset($temp['preset'])) $temps[$k]['preset'] = (!is_array($temp['preset'])) ? json_decode($temp['preset'], true) : $temp['preset'];
		}
		return $temps;
	}

	/** @return string|int the template id, '' on failure */
	private function save_template($template){
		$custom = $this->get_templates();
		if(!is_array($custom) || empty($custom)){
			$custom = [];
			$new_id = $this->get_val($template, 'id', 1);
		}else{
			$new_id = $this->get_val($template, 'id', max(array_keys($custom)) + 1);
		}

		if(isset($template['newid'])){ //rename
			if(isset($custom[$new_id])) unset($custom[$new_id]);
			$new_id = $template['newid'];
		}

		if(!isset($custom[$new_id])) $custom[$new_id] = [];
		$custom[$new_id]['title']	= $this->get_val($template, ['obj', 'title']);
		$custom[$new_id]['preset']	= stripslashes($this->get_val($template, ['obj', 'preset']));

		return (update_option($this->option, $custom)) ? $new_id : '';
	}

	/** @return bool */
	private function delete_template($template){
		if(!isset($template['id'])) return false;
		$custom = $this->get_templates();
		unset($custom[$template['id']]);
		return update_option($this->option, $custom);
	}
}
