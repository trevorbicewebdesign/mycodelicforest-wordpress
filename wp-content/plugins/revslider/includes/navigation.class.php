<?php
/**
 * @package   Revolution Slider
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Navigation skins (arrows, bullets, thumbs, tabs, scrubber).
 *
 * A skin is a CSS/markup template plus a set of "placeholders" - named values like colours or sizes that a
 * slider can override. Skins come from two places: the shipped defaults in includes/navigations.php
 * (marked 'factory' => true, read-only) and user-created ones in the navigations table. A "preset" is a
 * saved set of placeholder values for one skin.
 */
class RevSliderNavigation extends RevSliderFunctions {
	public $version = '6.0.0';

	/**
	 * load one skin by its numeric id, database first, then the shipped defaults
	 * @param int          $nav_id
	 * @param string|false $type   optional guard: return false if the skin is not of this type
	 * @return array|false the skin with decoded settings and a 'factory' flag
	 */
	public function init_by_id($nav_id, $type = false){
		$nav_id = intval($nav_id);
		if(intval($nav_id) == 0) return false;

		global $wpdb;
		$nav	= $wpdb->get_row($wpdb->prepare("SELECT `id`, `handle`, `type`, `css`, `markup`, `settings` FROM ".$wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS." WHERE `id` = %d", $nav_id), ARRAY_A);
		
		if(!empty($nav)){
			if($type !== false && $this->get_val($nav, 'type') !== $type) return false;

			$nav['settings'] = RevSliderFunctions::stripslashes_deep(json_decode($nav['settings'], true));
			if(!is_array($nav['settings'])) $nav['settings'] = json_decode($nav['settings'], true);
			$nav['factory'] = false;

			return $nav;
		}

		$def	= self::get_default_navigations();
		foreach($def ?? [] as $nav){
			if(intval($nav['id']) !== $nav_id) continue;
			if($type !== false && $this->get_val($nav, 'type') !== $type) return false;

			$nav['settings'] = RevSliderFunctions::stripslashes_deep(json_decode($nav['settings'], true));
			if(!is_array($nav['settings'])) $nav['settings'] = json_decode($nav['settings'], true);
			$nav['factory'] = true;

			return $this->add_default_presets($nav);
		}

		return false;
	}

	/**
	 * same as init_by_id(), but keyed on the skin handle
	 * @return array|false
	 */
	public function init_by_handle($handle, $type = false){
		global $wpdb;

		$nav	= $wpdb->get_row($wpdb->prepare("SELECT `id`, `handle`, `type`, `css`, `markup`, `settings` FROM ".$wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS." WHERE `handle` = %s", $handle), ARRAY_A);
		if(!empty($nav)){
			if($type !== false && $this->get_val($nav, 'type') !== $type) return false;

			$nav['settings'] = RevSliderFunctions::stripslashes_deep(json_decode($nav['settings'], true));
			if(!is_array($nav['settings'])) $nav['settings'] = json_decode($nav['settings'], true);
			$nav['factory'] = false;

			return $nav;
		}

		$def	= self::get_default_navigations();
		foreach($def ?? [] as $nav){
			if($nav['handle'] !== $handle) continue;

			if($type !== false && $this->get_val($nav, 'type') !== $type) return false;

			$nav['settings'] = RevSliderFunctions::stripslashes_deep(json_decode($nav['settings'], true));
			if(!is_array($nav['settings'])) $nav['settings'] = json_decode($nav['settings'], true);
			$nav['factory'] = true;

			return $this->add_default_presets($nav);
		}

		return false;
	}

	
	/**
	 * Get all Navigations Short
	 * id/handle/name/type only - for pickers that do not need css and markup
	 * @since: 5.0
	 * @return array
	 **/
	public function get_all_navigations_short($type = false){
		global $wpdb;
		$navs	= $wpdb->get_results("SELECT `id`, `handle`, `name`, `type` FROM ".$wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS, ARRAY_A);
		$def	= self::get_default_navigations();
		
		foreach($def ?? [] as $nav){
			$navs[] = [
				'id'		=> $nav['id'],
				'handle'	=> $nav['handle'],
				'name'		=> $nav['name'],
				'type'		=> $nav['type'],
				'factory'	=> true,
			];
		}

		$_navs = [];
		foreach($navs ?? [] as $nav){
			if($type !== false){
				if($this->get_val($nav, 'type') !== $type) continue;
			}

			$_navs[$this->get_val($nav, 'id')] = $nav;
			if(!isset($_navs[$this->get_val($nav, 'id')]['factory'])) $_navs[$this->get_val($nav, 'id')]['factory'] = false;
		}

		return $_navs;
	}


	/**
	 * all skins regrouped for the editor: type => [id => skin]
	 * @return array
	 */
	public function get_all_navigations_builder($defaults = true, $raw = false){
		$navs = $this->get_all_navigations($defaults, $raw);

		$real_navs = [
			'arrows'	=> [],
			'thumbs'	=> [],
			'bullets'	=> [],
			'tabs'		=> [],
			'scrubber'	=> []
		];

		foreach($navs ?? [] as $nav){
			$real_navs[$this->get_val($nav, 'type')][$this->get_val($nav, 'id')] = $nav;
		}

		return $real_navs;
	}

	/**
	 * get cache attempt of _get_all_navigations
	 * @return mixed
	 */
	public function get_all_navigations($defaults = true, $raw = false, $presets = true){
		return $this->get_wp_cache('_get_all_navigations', [$defaults, $raw, $presets]);
	}

	/**
	 * Get all Navigations
	 * @since: 5.0
	 * @param bool $defaults include the shipped factory skins
	 * @param bool $raw      true = return the database rows untouched (settings still JSON)
	 * @param bool $presets  merge the user's saved presets into the factory skins
	 * @return array
	 **/
	protected function _get_all_navigations($defaults = true, $raw = false, $presets = true){
		global $wpdb;

		$navigations = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS, ARRAY_A);

		if($raw === false){
			foreach($navigations ?? [] as $key => $nav){
				$navigations[$key]['factory']	= false;
				$navigations[$key]['css']		= stripslashes($navigations[$key]['css']);
				$navigations[$key]['markup']	= stripslashes($navigations[$key]['markup']);

				if(isset($navigations[$key]['settings']) && !empty($navigations[$key]['settings'])){
					$navigations[$key]['settings'] = RevSliderFunctions::stripslashes_deep(json_decode($navigations[$key]['settings'], true));
					if(!is_array($navigations[$key]['settings'])){
						$navigations[$key]['settings'] = json_decode($navigations[$key]['settings'], true);
					}
				}
			}
		}

		if($defaults === true){
			$def = self::get_default_navigations();

			$default_presets = $this->get_options(['presets', 'navigation'], []);

			if(!empty($def)){
				if($raw === false){
					foreach($def ?? [] as $key => $nav){
						$def[$key]['factory'] = true;

						if(isset($def[$key]['settings'])) $def[$key]['settings'] = json_decode($def[$key]['settings'], true);

						//add custom settings (placeholders) to the default navigation
						if(!empty($default_presets) && $presets === true){
							if(!isset($def[$key]['settings'])) $def[$key]['settings'] = [];
							if(!isset($def[$key]['settings']['presets'])) $def[$key]['settings']['presets'] = [];
							foreach($default_presets ?? [] as $id => $v){
								if($id !== $def[$key]['id']) continue;

								foreach($v ?? [] as $pr_k => $pr_v){
									if($this->get_val($pr_v, 'type') !== $def[$key]['type']) continue;

									$def[$key]['settings']['presets'][$pr_k] = [
										'name' => $this->get_val($pr_v, 'name'),
										'values' => $this->get_val($pr_v, 'values')
									];
								}
							}
						}
					}
				}
				$navigations = array_merge($navigations, $def);
			}
		}

		foreach($navigations ?? [] as $key => $nav){
			//check if this is the v6 version
			if(version_compare($this->get_val($navigations[$key], ['settings', 'version'], false), $this->version, '>=')){
				//we are v6, push settings to root
				$navigations[$key]['dim']			= $this->get_val($navigations[$key], ['settings', 'dim'], false);
				$navigations[$key]['placeholders']	= $this->get_val($navigations[$key], ['settings', 'placeholders'], false);
				$navigations[$key]['presets']		= $this->get_val($navigations[$key], ['settings', 'presets'], false);
				$navigations[$key]['version']		= $this->get_val($navigations[$key], ['settings', 'version'], false);
				unset($navigations[$key]['settings']);
			}
		}

		return $navigations;
	}


	/**
	 * Creates / Updates Navigation skins
	 * @since: 5.0
	 * decides between insert and update by looking for an existing id; factory skins are rejected
	 * @return true|string true on success, an error message otherwise
	 **/
	public function create_update_full_navigation($data){
		if(empty($data) || !is_array($data)) return true;

		global $wpdb;

		$navigations = $this->get_all_navigations(false);

		if($this->_truefalse($this->get_val($data, 'factory', false)) === true) return __('Default navigations can not be overwritten', 'revslider'); //defaults can't be deleted

		if(isset($data['id'])){ //new will be added temporary to navs to tell here that they are new
			foreach($navigations ?? [] as $nav){
				if($data['id'] != $nav['id']) continue;
			
				return $this->create_update_navigation($data, $data['id']); //update
			}
		}

		return $this->create_update_navigation($data);
	}

	/**
	 * Creates / Updates Navigation skins
	 * @since: 5.0
	 * writes one skin to the navigations table
	 * @param int $nav_id 0 = insert, otherwise update that row
	 * @return int|false the skin id, false on failure or for factory skins
	 **/
	public function create_update_navigation($data, $nav_id = 0){
		if($this->_truefalse($this->get_val($data, 'factory', false)) === true) return false;
		
		global $wpdb;

		$nav_id = intval($nav_id);

		if($nav_id > 0){
			$update = [];
			if(isset($data['name']))	 $update['name']	= $this->get_val($data, 'name');
			if(isset($data['handle']))	 $update['handle']	= $this->get_val($data, 'handle');
			if(isset($data['markup']))	 $update['markup']	= stripslashes($this->get_val($data, 'markup'));
			if(isset($data['css']))		 $update['css']		= stripslashes($this->get_val($data, 'css'));
			if(isset($data['settings'])){
				//get navigation settings and merge settings
				$n = $this->init_by_id($nav_id);
				$update['settings'] = $this->get_val($n, 'settings', []);
				foreach($data['settings'] ?? [] as $k => $s){
					$update['settings'][$k] = $s;
				}
				$update['settings'] = json_encode($update['settings']);
			}

			if(empty($update)) return false;

			$response = $wpdb->update(
				$wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS,
				$update,
				['id' => $nav_id]
			);
		}else{
			$response = $wpdb->insert(
				$wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS,
				[
					'name'		=> $this->get_val($data, 'name'),
					'handle'	=> $this->get_val($data, 'handle'),
					'type'		=> $this->get_val($data, 'type'),
					'css'		=> stripslashes($this->get_val($data, 'css')),
					'markup'	=> stripslashes($this->get_val($data, 'markup')),
					'settings'	=> json_encode($this->get_val($data, 'settings'))
				]
			);
			if($response !== false) return $wpdb->insert_id;
		}

		return ($response !== false) ? true : false;
	}

	/**
	 * Delete Navigation
	 * @since: 5.0
	 * @return true|string true on success, an error message otherwise
	 **/
	public function delete_navigation($nav_id = 0){
		if(!isset($nav_id) || intval($nav_id) == 0) return __('Invalid ID', 'revslider');
		
		global $wpdb;
		
		return ($wpdb->delete($wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS, ['id' => $nav_id]) === false) ? __('Navigation could not be deleted', 'revslider') : true;
	}


	/**
	 * Get Default Navigation
	 * @since: 5.0
	 * the skins shipped with the plugin, loaded from includes/navigations.php
	 * @return array
	 **/
	public static function get_default_navigations(){
		$navigations = [];

		include(RS_PLUGIN_PATH.'includes/navigations.php');

		return apply_filters('revslider_mod_default_navigations', $navigations);
	}

	/**
	 * Check if given Navigation is custom, if yes, export it
	 * @since: 5.1.1
	 * @param array|string $nav_handle one skin id, or a map of id => true
	 * @return array|false the matching skin rows, false when none are custom
	 **/
	public function export_navigation($nav_handle){
		$navs = self::get_all_navigations(false, true);

		if(!is_array($nav_handle)) $nav_handle = [$nav_handle => true];

		$entries = [];
		if(!empty($nav_handle) && !empty($navs)){
			foreach($nav_handle ?? [] as $nav_id => $u){
				foreach($navs ?? [] as $n => $v){
					//if($v['handle'] == $nav_id){
					if($v['id'] == $nav_id){
						$entries[$nav_id] = $navs[$n];
						break;
					}
				}
			}
			if(!empty($entries)) return $entries;
		}

		return false;
	}


	/**
	 * Check the CSS for placeholders, replace them with correspinding values
	 * @since: 5.2.0
	 * turns the skin's ##placeholder## markers into the values the slider actually configured
	 * @return string the final CSS for this skin
	 **/
	public function add_placeholder_modifications($def_navi, $slider, $output){
		if(!is_array($def_navi)) $def_navi = json_decode($def_navi, true);

		$css	= $this->get_val($def_navi, 'css');
		$type	= $this->get_val($def_navi, 'type');
		$handle	= $this->get_val($def_navi, 'handle');

		if(!in_array($type, ['arrows', 'bullets', 'thumbs', 'tabs', 'scrubber'])) return $css;

		$placeholders = $this->get_val($def_navi, 'placeholders', []);

		if(!is_array($placeholders) || empty($placeholders)) return $css;
	
		foreach($placeholders ?? [] as $phandle => $ph){
			$def	 = $slider->get_param(['nav', $type, 'presets', $phandle.'-def'], false);
			$replace = ($def === true) ? $slider->get_param(['nav', $type, 'presets', $phandle], $ph['data']) : $ph['data'];
			$css	 = str_replace('##'.$phandle.'##', $replace, $css);
		}
		$css = str_replace('.'.$handle, '#'.$output->get_html_id().'_wrapper .'.$handle, $css);

		return $css;
	}


	/**
	 * change rgb, rgba and hex to rgba like 120,130,50,0.5 (no () and rgb/rgba)
	 * parses a CSS string into selector => [property => value]; comments are stripped first
	 * @since: 3.0.0
	 * @return array|false false when a comment is left unclosed
	 **/
	public static function parse_css_to_array($css){

		while(strpos($css, '/*') !== false){
			if(strpos($css, '*/') === false) return false;
			$start = strpos($css, '/*');
			$end = strpos($css, '*/') + 2;
			$css = str_replace(substr($css, $start, $end - $start), '', $css);
		}

		//preg_match_all('/(?ims)([a-z0-9\s\.\:#_\-@]+)\{([^\}]*)\}/', $css, $arr);
		preg_match_all('/(?ims)([a-z0-9\,\s\.\:#_\-@]+)\{([^\}]*)\}/', $css, $arr);

		$result = [];
		foreach($arr[0] ?? [] as $i => $x){
			$selector = trim($arr[1][$i]);
			if(strpos($selector, '{') !== false || strpos($selector, '}') !== false) return false;
			$rules = explode(';', trim($arr[2][$i]));
			$result[$selector] = [];
			foreach ($rules ?? [] as $strRule){
				if(empty($strRule)) continue;
					
				$rule = explode(':', $strRule);
				if(strpos($rule[0], '{') !== false || strpos($rule[0], '}') !== false || strpos($rule[1], '{') !== false || strpos($rule[1], '}') !== false) return false;

				//put back everything but not $rule[0];
				$key = trim($rule[0]);
				unset($rule[0]);
				$values = implode(':', $rule);

				$result[$selector][trim($key)] = trim(str_replace("'", '"', $values));
			}
		}

		return $result;
	}

	/**
	 * Returns Array CSS modifications
	 * @since: 5.2.0
	 * per-slide preset overrides, emitted as extra CSS rules
	 * @return string
	 **/
	public function preset_return_array_css($c, $placeholders, $slide, $handle, $type, $output){
		if(empty($c)) return '';

		$c_css = '';
		$array_css = [];
	
		foreach($placeholders ?? [] as $k => $d){
			if($slide->get_param(['nav', $type, 'presets', $k.'-def'], false) !== true) continue; //get from Slide
			foreach($c ?? [] as $class => $styles){
				foreach($styles ?? [] as $name => $val){
					if(strpos($val, '##'.$k.'##') === false) continue;

					$e = $slide->get_param(['nav', $type, 'presets', $k]);
					$array_css[$class][$name] = str_replace('##'.$k.'##', $e, $val);
				}
			}
		}

		foreach($array_css ?? [] as $class => $styles){
			if(empty($styles)) continue;
			//class needs to get current slider and slide id
			$slide_id = $slide->get_id();
			$class = str_replace('.'.$handle, '#'.$output->get_html_id().'[data-slideactive="rs-'.$slide_id.'"] .'.$handle, $class);

			$c_css .= $class.'{'."\n";
			foreach($styles ?? [] as $style => $value){
				//check if there are still defaults that needs to be replaced
				if(strpos($value, '##') === false) continue;
			
				foreach($placeholders as $k => $d){
					if(strpos($value, '##'.$k.'##') === false) continue;
					$value = str_replace('##'.$k.'##', $d['data'], $value);
				}
				
				$c_css .= $style.': '.$value.' !important;'."\n";
			}
			$c_css .= '}'."\n";
		}

		return $c_css;
	}

	/**
	 * merge the user's saved presets into a factory skin - those cannot store presets in the table
	 * @return array the skin, with settings['presets'] filled in
	 */
	/**
	 * merge the user's saved presets into a factory skin - those cannot store presets in the table
	 * @return array the skin, with settings['presets'] filled in
	 */
	public function add_default_presets($nav){
		$default_presets = $this->get_options(['presets', 'navigation'], []);

		if(empty($nav)) return $nav;
		if(empty($default_presets)) return $nav;

		if(isset($nav['settings']) && !is_array($nav['settings'])) $nav['settings'] = json_decode($nav['settings'], true);

		//add custom settings (placeholders) to the default navigation
	
		if(!isset($nav['settings'])) $nav['settings'] = [];
		if(!isset($nav['settings']['presets'])) $nav['settings']['presets'] = [];
		
		foreach($default_presets ?? [] as $id => $v){
			if($id !== $nav['id']) continue;

			foreach($v ?? [] as $pr_k => $pr_v){
				if($this->get_val($pr_v, 'type') !== $nav['type']) continue;

				$nav['settings']['presets'][$pr_k] = [
					'name' => $this->get_val($pr_v, 'name'),
					'values' => $this->get_val($pr_v, 'values')
				];
			}
		}

		return $nav;
	}

	/**
	 * Add Navigation Preset to existing navigation
	 * @since: 5.2.0
	 * factory skins keep their presets in the options, custom ones in the table row
	 * @return bool
	 **/
	public function add_preset($data){
		if(!isset($data['id'])) return false;

		$navs = $this->get_all_navigations(true, false, false);

		foreach($navs ?? [] as $nav){
			if($nav['id'] != $data['id']) continue; //found the navigation, get ID and update settings

			//check if default, they cant have presets in the table
			if(isset($nav['factory']) && $nav['factory'] == true){
				//check if we are a default preset, if yes return error
				if(isset($nav['presets'])){
					foreach($nav['presets'] ?? [] as $prkey => $preset){
						if($prkey == $data['handle'] && !isset($preset['editable'])) return __("Can't modify a default preset of default navigations", 'revslider');
					}
				}

				//we want to add the preset somewhere
				$overwrite = false;
				$default_presets = $this->get_options(['presets', 'navigation'], []);

				if(!empty($default_presets) && isset($default_presets[$nav['id']])){
					if(isset($default_presets[$nav['id']][$data['handle']])){
						if($this->_truefalse($data['do_overwrite']) === false) return __('Preset handle already exists, please choose a different name', 'revslider');
						$handle = (isset($data['new_handle'])) ? $data['new_handle'] : $data['handle'];

						$default_presets[$nav['id']][$handle] = [
							'name'		=> esc_attr($data['name']),
							'type'		=> esc_attr($data['type']),
							'values'	=> $data['values'],
							'editable'	=> true
						];
						if(isset($data['new_handle'])) unset($default_presets[$nav['id']][$data['handle']]);

						$overwrite = true;
					}
				}
				
				if($overwrite === false){
					$default_presets[$nav['id']][$data['handle']] = [
						'name'		=> esc_attr($data['name']),
						'type'		=> esc_attr($data['type']),
						'values'	=> $data['values'],
						'editable'	=> true
					];
				}

				$this->update_option(['presets', 'navigation'], $default_presets);

				//return __('Can\'t add a preset to default navigations', 'revslider');
			}else{
				$overwrite = false;

				if(isset($nav['presets']) && is_array($nav['presets']) && !empty($nav['presets'])){
					if(isset($nav['presets'][$data['handle']])){
						if($this->_truefalse($data['do_overwrite']) === false) return __('Preset handle already exists, please choose a different name', 'revslider');
						$handle = (isset($data['new_handle'])) ? $data['new_handle'] : $data['handle'];

						$nav['presets'][$handle] = [
							'name'		=> esc_attr($data['name']),
							'type'		=> esc_attr($data['type']),
							'values'	=> $data['values']
						];
						if(isset($data['new_handle'])) unset($nav['presets'][$data['handle']]);

						$overwrite = true;
					}
				}else{
					$nav['presets'] = [];
				}

				if($overwrite === false){
					$nav['presets'][$data['handle']] = [
						'name'		=> esc_attr($data['name']),
						'type'		=> esc_attr($data['type']),
						'values'	=> $data['values']
					];
				}

				$placeholders = $this->get_val($nav, 'placeholders');
				foreach($placeholders ?? [] as $k => $pl){
					if(isset($pl['data'])) $placeholders[$k]['data'] = (!empty($pl['data'])) ? addslashes($pl['data']) : $pl['data'];
				}

				global $wpdb;

				//save this navigation
				$response = $wpdb->update(
					$wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS,
					[
						'settings' => json_encode(
							[
								'dim'		=> $this->get_val($nav, 'dim'),
								'placeholders' => $placeholders,
								'presets'	=> $this->get_val($nav, 'presets'),
								'version'	=> $this->version
							]
						)
					],
					['id' => $nav['id']]
				);
			}

			return true;
		}

		return __('Navigation not found, could not add preset', 'revslider');
	}


	/**
	 * remove a preset from a navigation skin
	 * @since: 5.2.0
	 * @return bool
	 **/
	public function delete_preset($data){
		if(!isset($data['id']) || !isset($data['handle']) || !isset($data['type'])) return false;

		$navs = $this->get_all_navigations();

		foreach($navs ?? [] as $nav){
			if($nav['id'] != $data['id']) continue;
			if($nav['type'] != $data['type']) continue;

			//found the navigation, get ID and update settings
			//check if default, they cant have presets
			if(isset($nav['factory']) && $nav['factory'] == true){
				$default_presets = $this->get_options(['presets', 'navigation'], []);

				if(!empty($default_presets) && isset($default_presets[$nav['id']])){
					foreach($default_presets[$nav['id']] as $prkey => $preset){
						if($prkey != $data['handle']) continue;

						unset($default_presets[$nav['id']][$prkey]);
						$this->update_option(['presets', 'navigation'], $default_presets);

						return true;
					}
					return __('Can\'t delete default preset of default navigations', 'revslider');
				}
				return __('Preset not found in default navigations', 'revslider');
			}else{
				if(!isset($nav['presets'])) return __('Preset not found', 'revslider');
				$found = false;
				foreach($nav['presets'] ?? [] as $pkey => $preset){
					if($pkey != $data['handle']) continue;
					
					unset($nav['presets'][$pkey]); //delete
					$found = true;
					break;
				}
				
				if(!$found) return __('Navigation not found, could not delete preset', 'revslider');
				 
				global $wpdb;

				//save this navigation
				$response = $wpdb->update(
					$wpdb->prefix.RevSliderFront::TABLE_NAVIGATIONS,
					[
						'settings' => json_encode(
							[
								'dim'			=> $this->get_val($nav, 'dim'),
								'placeholders'	=> $this->get_val($nav, 'placeholders'),
								'presets'		=> $this->get_val($nav, 'presets'),
								'version'		=> $this->version
							]
						)
					],
					['id' => $nav['id']]
				);
				
				return true;
			}
		}

		return __('Navigation not found, could not delete preset', 'revslider');
	}

}
