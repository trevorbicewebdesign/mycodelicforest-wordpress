<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Internal slider output cache.
 *
 * Rendered slider HTML is stored in transients (revslider_slider_<id>_...). Everything that cannot live
 * inside that cached HTML - hook callbacks, footer scripts, global CSS/font collections - is recorded as an
 * "addition" while rendering and stored alongside it; on a cache hit do_additions() replays those, so the
 * page behaves as if the slider had been rendered live.
 */
class RevSliderCache extends RevSliderFunctions {

	/** @var bool the "internal cache" switch from the global settings */
	public $cache_enabled = false;

	/**
	 * additions recorded during rendering, keyed by kind:
	 * 'action'/'filter' => [hook name => html chunks], 'html' => plain chunks, 'special' => global state
	 **/
	private $cache_additions = ['action' => [], 'filter' => [], 'html' => [], 'special' => []];

	/** @var array html chunks queued per hook name, printed by print_addition() */
	private $output_html = [];

	public function __construct(){
		$globals = $this->get_global_settings();
		$this->cache_enabled = ($this->_truefalse($this->get_val($globals, ['opt', 'intcache'])) === true) ? true : false;
	}


	/**
	 * is the internal slider cache switched on?
	 * @return bool
	 **/
	public function is_enabled(){
		return $this->cache_enabled;
	}


	/**
	 * can sliders of this source type be cached? social streams cannot
	 * @return bool
	 **/
	public function is_supported_type($type){
		return (in_array($type, ['post', 'posts', 'specific_posts', 'specific_post', 'current_post', 'woocommerce', 'woo', 'gallery'], true)) ? true : false;
	}

	/**
	 * delete the cached output of every slider (value and timeout rows)
	 * @return int|false deleted value rows, false on a database error
	 **/
	public function clear_all_transients(){
		global $wpdb;

		$return = $wpdb->query("DELETE FROM ". $wpdb->prefix . "options WHERE `option_name` LIKE '\\_transient\\_revslider\\_slider\\_%'");
		$wpdb->query("DELETE FROM ". $wpdb->prefix . "options WHERE `option_name` LIKE '\\_transient\\_timeout\\_revslider\\_slider\\_%'");
		return $return;
	}


	/**
	 * delete the cached output of one slider
	 * @since: 6.4.7
	 * @return int|false deleted value rows, false when $sid is not a positive id
	 **/
	public function clear_transients_by_slider($sid){
		global $wpdb;
		
		$return = false;
		
		$sid = intval($sid);
		if($sid > 0){
			$return = $wpdb->query($wpdb->prepare("DELETE FROM ". $wpdb->prefix . "options WHERE `option_name` LIKE '\\_transient\\_revslider\\_slider\\_%d%%'", $sid));
			$wpdb->query($wpdb->prepare("DELETE FROM ". $wpdb->prefix . "options WHERE `option_name` LIKE '\\_transient\\_timeout\\_revslider\\_slider\\_%d%%'", $sid));
		}
		
		return $return;
	}
	
	
	/**
	 * delete the cached output of multiple sliders in two queries instead of 2×N
	 * @since: 6.4.8
	 * @param int[] $ids slider ids; non-numeric entries are dropped
	 * @return int|false deleted value rows, false when no valid id remains
	 **/
	public function clear_transients_by_sliders($ids = []){
		global $wpdb;

		$ids = array_unique(array_filter(array_map('intval', $ids), function($id){ return $id > 0; }));
		if(empty($ids)) return false;

		//the ids are already intval'd, but the query is still built through prepare(): that way the safety of
		//this statement does not depend on a filter step further up that a later change could weaken.
		//esc_like() escapes the underscores in the transient prefix so they are literals and not LIKE wildcards.
		$conditions	= [];
		$args_main	= [];
		$args_time	= [];

		foreach($ids as $id){
			$conditions[]	= '`option_name` LIKE %s';
			$args_main[]	= $wpdb->esc_like('_transient_revslider_slider_' . $id) . '%';
			$args_time[]	= $wpdb->esc_like('_transient_timeout_revslider_slider_' . $id) . '%';
		}

		$where	= implode(' OR ', $conditions);
		$return	= $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE {$where}", $args_main));
		$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->options} WHERE {$where}", $args_time));

		return $return;
	}


	/**
	 * the additions recorded so far (see the class docblock)
	 * @return array
	 **/
	public function get_additions(){
		return $this->cache_additions;
	}


	/**
	 * record something that must be replayed on a cache hit
	 *
	 * @param string       $type     'action' | 'filter' | 'html' | 'special'
	 * @param string|false $name     hook name (action/filter) or key (special); false appends to a flat list
	 * @param mixed        $output   html chunk, or the value to restore for 'special'
	 * @param int          $priority output order within the same hook
	 * @return void
	 **/
	public function add_addition($type, $name = false, $output = '', $priority = 10){
		if($output === '') return;
		
		if(!isset($this->cache_additions[$type])) $this->cache_additions[$type] = [];
		
		if($name === false){
			$this->cache_additions[$type][] = $output;
		}else{
			if(!isset($this->cache_additions[$type][$name])) $this->cache_additions[$type][$name] = [];
			
			if($type === 'special'){
				$this->cache_additions[$type][$name][] = $output;
			}else{
				$this->cache_additions[$type][$name][] = [
					'html' => $output,
					'priority' => $priority
				];
			}
		}
	}
	
	/**
	 * hook for replacing per-request placeholders in cached HTML before it is echoed - currently a plain
	 * pass-through, the one replacement it ever did is commented out below
	 * @return string
	 **/
	public function do_html_changes($html){
		//$html = str_replace('##NONCE##', wp_create_nonce('RevSlider_Front'), $html);

		return $html;
	}

	/**
	 * replay the recorded additions on a cache hit: re-register the action/filter output through
	 * print_addition() and restore the 'special' entries (css collection, icon font flags) into $SR_GLOBALS
	 * @since: 6.4.7
	 * @param array             $additions the 'addition' part of the stored transient
	 * @param RevSlider7Output  $output    passed through to the revslider_do_cache_additions hook only
	 * @return void
	 **/
	public function do_additions($additions, $output){
		$t_actions = $this->get_val($additions, 'action', []);
		if(!empty($t_actions)){
			foreach($t_actions as $_action => $t_a){
				if(!empty($t_a)){
					
					foreach($t_a as $t_sa){
						if(!isset($this->output_html[$_action])) $this->output_html[$_action] = [];
						$this->output_html[$_action][] = $t_sa;
						add_action($_action, [$this, 'print_addition']);
					}
				}
			}
		}
		
		$t_filters = $this->get_val($additions, 'filter', []);
		if(!empty($t_filters)){
			foreach($t_filters as $_filter => $t_a){
				if(!empty($t_a)){
					foreach($t_a as $t_sa){
						if(!isset($this->output_html[$_filter])) $this->output_html[$_filter] = [];
						$this->output_html[$_filter][] = $t_sa;
						add_filter($_filter, [$this, 'print_addition']);
					}
				}
			}
		}
		
		$t_special = $this->get_val($additions, 'special', []);
		if(!empty($t_special)){
			$_rs_css_collection = $this->get_val($t_special, 'rs_css_collection', []);
			if(!empty($_rs_css_collection)){
				global $SR_GLOBALS;
				$SR_GLOBALS['collections']['css'] = $_rs_css_collection;
			}
			$_font_var = $this->get_val($t_special, 'font_var', []);
			if(!empty($_font_var)){
				global $SR_GLOBALS;
				foreach($_font_var as $fw){
					if(!isset($SR_GLOBALS['icon_sets'][$fw])) $SR_GLOBALS['icon_sets'][$fw] = ['css' => false, 'parsed' => false];
					$SR_GLOBALS['icon_sets'][$fw]['css'] = true;
				}
			}
		}
		
		do_action('revslider_do_cache_additions', $additions, $output);
	}
	
	
	/**
	 * store rendered html plus the recorded additions as one transient (one week), then reset the buffer
	 * @param string $transient transient name
	 * @param int    $sid       unused; kept for callers outside the plugin
	 * @param string $content   rendered slider html
	 * @return void
	 **/
	public function set_full_transient($transient, $sid, $content){
		$add = [
			'html' => $content,
			'addition' => $this->get_additions()
		];
		
		$add = json_encode($add);
		set_transient($transient, $add, WEEK_IN_SECONDS);
		
		$this->cache_additions = [];
	}
	
	/**
	 * echo the html queued for the currently running hook, ordered by priority; the footer scripts hook
	 * gets a <script> wrapper
	 * @since: 6.4.7
	 * @return void
	 **/
	public function print_addition(){
		$html = $this->get_val($this->output_html, current_filter());
		if(is_array($html)){
			if(!empty($html)){
				usort($html, [$this, 'sort_by_priority']);
				echo (current_filter() === 'wp_print_footer_scripts') ? '<script>'."\n" : '';
				foreach($html as $echo){
					echo $this->get_val($echo, 'html');
				}
				echo (current_filter() === 'wp_print_footer_scripts') ? RS_T.'</script>'."\n" : '';
			}
		}else{
			echo $html;
		}
	}
	
	/**
	 * runs on save_post/publish: clears the cache of every slider with a post-based source, since the
	 * changed post may appear in their output. gallery sliders are untouched
	 * @return void
	 **/
	public function check_for_post_transient_deletion(){
		$post_types = ['post', 'posts', 'specific_posts', 'specific_post', 'current_post', 'woocommerce', 'woo'];
		foreach($post_types as $k => $pt){
			$post_types[$k] = '"sourcetype":"'.$pt.'"';
		}
		$_slider = RevSliderGlobals::instance()->get('RevSliderSlider');
		
		$slider = $_slider->get_slider_by_param_string($post_types, true);
		
		//clear cache for all of these sliders in a single bulk operation
		if(!empty($slider) && is_array($slider)){
			$_self = $this;
			$ids   = array_map(function($s) use($_self){ return $_self->get_val($s, 'id'); }, $slider);
			$this->clear_transients_by_sliders($ids);
		}
	}
	
	
	/**
	 * usort callback for print_addition()
	 * @return int
	 **/
	public function sort_by_priority($a, $b) {
		return $a['priority'] - $b['priority'];
	}
}