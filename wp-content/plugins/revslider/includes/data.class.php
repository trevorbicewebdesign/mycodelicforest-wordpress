<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

define('RS_T', '	');
define('RS_T2', '		');
define('RS_T3', '			');
define('RS_T4', '				');
define('RS_T5', '					');
define('RS_T6', '						');
define('RS_T7', '							');
define('RS_T8', '								');
define('RS_T9', '									');
define('RS_T10', '										');
define('RS_T11', '											');

/**
 * Static catalogues + the object cache layer, at the very bottom of the class hierarchy
 * (RevSliderFunctions extends this, and almost everything extends that).
 *
 * Two jobs. First, it ships the large built-in datasets the editor needs - layer animations, slide
 * transitions, font families, image sizes, icon sets - mostly as JSON literals decoded on demand. Second,
 * it wraps wp_cache_* with a namespace key: since WP has no "delete a whole cache group", bumping
 * $_cache_ns_key invalidates every key at once, which happens automatically whenever a write to one of the
 * plugin's own tables is detected.
 */
class RevSliderData {

	const CACHE_GROUP = 'revslider';
	const CACHE_NS_KEY = 'revslider_namespace_key';

	public $css;
	public $directories = [
		'plugin' => [RS_PLUGIN_URL => '__REVSLIDER__']
	];
	public $image_path = ['slider' => [], 'slides' => [], 'layers' => []];
	public $image_path_v6 = ['slider' => [], 'slides' => [], 'layers' => []];

	/**
	 * wp cache does not support group delete
	 * this var hold the num to generate unique keys
	 * when data changed - key increased and invalidate old data
	 * @var int
	 */
	protected $_cache_ns_key;
	/**
	 * @var array hold revslider tables names
	 */
	protected $_rs_tables;
	/**
	 * @var string
	 */
	protected $_rs_tables_pattern;
	
	public function __construct(){
		//build the table list + write-detection regex once per request (constant, but this base constructor
		//runs for every Slide/Slider/Output object, so memoize keyed by table prefix)
		global $wpdb;
		static $rs_tables = [], $rs_tables_pattern = [];
		$prefix = $wpdb->prefix;
		if(!isset($rs_tables[$prefix])){
			$rs_tables[$prefix] = RevSliderGlobals::instance()->get_rs_tables();
			$rs_tables_pattern[$prefix] = "/^\s*(insert|update|replace|delete).+(".implode('|', $rs_tables[$prefix]).")/i";
		}
		$this->_rs_tables = $rs_tables[$prefix];
		$this->_rs_tables_pattern = $rs_tables_pattern[$prefix];
		
		$this->_cache_ns_key = wp_cache_get(self::CACHE_NS_KEY, self::CACHE_GROUP);
		if(false === $this->_cache_ns_key){
			$this->_cache_ns_key = 1;
			wp_cache_set(self::CACHE_NS_KEY, $this->_cache_ns_key, self::CACHE_GROUP);
		}

		$query_filter = RevSliderGlobals::instance()->get('rs_data_query_fiter');
		if(!$query_filter){
			add_filter('query', [$this, 'add_query_fiter'], 10, 1);
			RevSliderGlobals::instance()->add('rs_data_query_fiter', true);
		}

		
		$this->image_path['slider'][] = ['thumb'];
		$this->image_path['slider'][] = ['thumb', 'src'];
		$this->image_path['slider'][] = ['thumb', 'default', 'image', 'src'];
		$this->image_path['slider'][] = ['bg', 'image', 'src'];
		$this->image_path['slider'][] = ['general', 'fallbackURL'];
		$this->image_path['slider'][] = ['imgs', '__ARRAY__', 'src'];
		$this->image_path['slides'][] = ['bg', 'image'];
		$this->image_path['slides'][] = ['imgs', '__ARRAY__', 'src'];
		$this->image_path['slides'][] = ['bg', 'externalSrc'];
		$this->image_path['slides'][] = ['thumb', 'src'];
		$this->image_path['slides'][] = ['thumb', 'admin'];
		$this->image_path['slides'][] = ['thumb', 'default', 'image', 'src'];
		$this->image_path['slides'][] = ['video', 'poster', 'src'];
		$this->image_path['slides'][] = ['image', 'src'];
		$this->image_path['slides'][] = ['video', 'src'];
		$this->image_path['layers'][] = ['content', 'src'];
		$this->image_path['layers'][] = ['content', 'poster', 'src'];
		$this->image_path['layers'][] = ['bg', 'image', 'src'];
		$this->image_path['layers'][] = ['bg', 'video', 'src'];
		$this->image_path['layers'][] = ['bg', 'video', 'poster', 'src'];
		$this->image_path = apply_filters('revslider_import_image_path', $this->image_path);

		$this->image_path_v6['slider'][] = ['layout', 'bg', 'useImage'];
		$this->image_path_v6['slider'][] = ['layout', 'bg', 'useImage'];
		$this->image_path_v6['slider'][] = ['layout', 'bg', 'image'];
		$this->image_path_v6['slider'][] = ['troubleshooting', 'alternateURL'];
		$this->image_path_v6['slides'][] = ['bg', 'image'];
		$this->image_path_v6['slides'][] = ['layout', 'bg', 'image'];
		$this->image_path_v6['slides'][] = ['thumb', 'customThumbSrc'];
		$this->image_path_v6['slides'][] = ['thumb', 'customAdminThumbSrc'];
		$this->image_path_v6['slides'][] = ['troubleshooting', 'alternateURL'];
		$this->image_path_v6['slides'][] = ['bg', 'mpeg'];
		$this->image_path_v6['slides'][] = ['bg', 'webm'];
		$this->image_path_v6['slides'][] = ['bg', 'ogv'];
		$this->image_path_v6['layers'][] = ['media', 'imageUrl'];
		$this->image_path_v6['layers'][] = ['idle', 'backgroundImage'];
		$this->image_path_v6['layers'][] = ['media', 'thumbs', 'veryBig'];
		$this->image_path_v6['layers'][] = ['media', 'thumbs', 'big'];
		$this->image_path_v6['layers'][] = ['media', 'thumbs', 'large'];
		$this->image_path_v6['layers'][] = ['media', 'thumbs', 'medium'];
		$this->image_path_v6['layers'][] = ['media', 'thumbs', 'small'];
		$this->image_path_v6['layers'][] = ['media', 'mp4Url'];
		$this->image_path_v6['layers'][] = ['media', 'webmUrl'];
		$this->image_path_v6['layers'][] = ['media', 'ogvUrl'];
		$this->image_path_v6['layers'][] = ['media', 'audioUrl'];
		$this->image_path_v6['layers'][] = ['media', 'posterUrl'];
		$this->image_path_v6['layers'][] = ['svg', 'source'];
		$this->image_path_v6 = apply_filters('revslider_import_image_path_v6', $this->image_path_v6);

		//$very_big	= (is_array($very_big) && isset($very_big['url'])) ? $very_big['url'] : $very_big;
		//$big		= (is_array($big) && isset($big['url'])) ? $big['url'] : $big;
		//$large		= (is_array($large) && isset($large['url'])) ? $large['url'] : $large;
		//$medium		= (is_array($medium) && isset($medium['url'])) ? $medium['url'] : $medium;
		//$small		= (is_array($small) && isset($small['url'])) ? $small['url'] : $small;

		$this->directories['plugin'] = apply_filters('revslider_directory_path', $this->directories['plugin']);
	}

	/**
	 * invalidate group cache if we modify rs data
	 * @param string $sql
	 * @return string
	 */
	public function add_query_fiter($sql){
		if(preg_match($this->_rs_tables_pattern, $sql)) $this->invalidate_group_cache();

		return $sql;
	}

	/**
	 * invalidate group keys by increase namespace key
	 */
	public function invalidate_group_cache(){
		$this->_cache_ns_key += 1;
		wp_cache_set(self::CACHE_NS_KEY, $this->_cache_ns_key, self::CACHE_GROUP);
	}

	/**
	 * @param string $fname  cache key name ( usually function name )
	 * @param mixed $data  additional cache key data ( usually functions parameters )
	 * @return string
	 */
	public function get_wp_cache_key($fname, $data){
		return sprintf('%s_%s_%s_%s', get_class($this), $fname, $this->_cache_ns_key, md5(serialize($data)));
	}

	/**
	 * try to load cached result
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function get_wp_cache($method, $args = []){
		if(!is_array($args)) $args = [$args];
		//disable cache for admin
		if(is_admin()) return call_user_func_array([$this, $method], $args);
		
		$cache_key = $this->get_wp_cache_key($method, $args);
		$data = wp_cache_get($cache_key, self::CACHE_GROUP);
		if(false === $data){
			$data = call_user_func_array([$this, $method], $args);
			wp_cache_set($cache_key, $data, self::CACHE_GROUP);
		}

		return $data;
	}

	/**
	 * clear cached value
	 *
	 * @param string $method
	 * @param array $args
	 * @return void
	 */
	public function delete_wp_cache($method, $args = []){
		if (!is_array($args)) $args = [$args];

		$cache_key = $this->get_wp_cache_key($method, $args);
		wp_cache_delete($cache_key, self::CACHE_GROUP);
	}

	/**
	 * flush all cache
	 * @return void
	 */
	public function flush_wp_cache(){
		wp_cache_flush();
	}

	/**
	 * get cache attempt of _get_font_familys
	 * @return mixed
	 */
	public function get_font_familys(){
		//cache across requests, including the editor (where get_wp_cache is is_admin-gated and never caches).
		//The list only changes when the custom font list or the plugin version changes - key on both so it
		//self-busts. Building it includes googlefonts.php (8766 lines) + a large array build.
		$cfl	= $this->get_val($this->get_global_settings(), 'customFontList', []);
		//include active plugins in the key so the (addon-filterable) list refreshes when a font-providing
		//addon is activated/deactivated/updated; also busts on plugin version + custom-font changes
		$key	= 'rs_fontfamilys_' . RS_REVISION . '_' . md5(json_encode($cfl) . json_encode(get_option('active_plugins', [])));
		$cached	= get_transient($key);
		if($cached !== false) return $cached;

		$fonts = $this->_get_font_familys();
		set_transient($key, $fonts, DAY_IN_SECONDS);

		return $fonts;
	}

	/**
	 * the bundled Google Font catalogue: family => ['variants' => [], 'subsets' => [], 'category' => '']
	 *
	 * googlefonts.php is an 8766 line array literal of ~250 KB. it used to be pulled in with include() from
	 * three separate places - and include(), unlike include_once(), re-executes the file and rebuilds the whole
	 * array every single time. this loads it once per request into a static; the data itself is unchanged.
	 *
	 * @return array
	 */
	public static function get_google_fonts(){
		static $gf_cache = null;
		if($gf_cache !== null) return $gf_cache;

		$googlefonts = [];
		include(RS_PLUGIN_PATH . 'includes/googlefonts.php'); //defines $googlefonts
		$gf_cache = (is_array($googlefonts)) ? $googlefonts : [];

		return $gf_cache;
	}

	/**
	 * get all font family types
	 */
	protected function _get_font_familys(){
		$fonts = [];

		//add custom added fonts
		$gs = $this->get_global_settings();
		$cfl = $this->get_val($gs, 'customFontList', []);

		if(!empty($cfl) && is_array($cfl)){
			foreach($cfl as $_cfl){
				$fonts[] = [
					'type'		=> 'custom',
					'version'	=> __('Custom Fonts', 'revslider'),
					'url'		=> $this->get_val($_cfl, 'url'),
					'frontend'	=> $this->_truefalse($this->get_val($_cfl, 'frontend', false)),
					'backend'	=> $this->_truefalse($this->get_val($_cfl, 'backend', true)),
					'label'		=> $this->get_val($_cfl, 'family'),
					'variants'	=> explode(',', $this->get_val($_cfl, 'weights')),
				];
			}
		}

		//Web Safe Fonts
		// GOOGLE Loaded Fonts
		$fonts[] = ['type' => 'websafe', 'version' => __('Loaded Google Fonts', 'revslider'), 'label' => 'Dont Show Me'];

		//Serif Fonts
		$fonts[] = ['type' => 'websafe', 'version' => __('Serif Fonts', 'revslider'), 'label' => 'Georgia, serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Serif Fonts', 'revslider'), 'label' => '\'Palatino Linotype\', \'Book Antiqua\', Palatino, serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Serif Fonts', 'revslider'), 'label' => '\'Times New Roman\', Times, serif'];

		//Sans-Serif Fonts
		$fonts[] = ['type' => 'websafe', 'version' => __('Sans-Serif Fonts', 'revslider'), 'label' => 'Arial, Helvetica, sans-serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Sans-Serif Fonts', 'revslider'), 'label' => '\'Arial Black\', Gadget, sans-serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Sans-Serif Fonts', 'revslider'), 'label' => '\'Comic Sans MS\', cursive, sans-serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Sans-Serif Fonts', 'revslider'), 'label' => 'Impact, Charcoal, sans-serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Sans-Serif Fonts', 'revslider'), 'label' => '\'Lucida Sans Unicode\', \'Lucida Grande\', sans-serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Sans-Serif Fonts', 'revslider'), 'label' => 'Tahoma, Geneva, sans-serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Sans-Serif Fonts', 'revslider'), 'label' => '\'Trebuchet MS\', Helvetica, sans-serif'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Sans-Serif Fonts', 'revslider'), 'label' => 'Verdana, Geneva, sans-serif'];

		//Monospace Fonts
		$fonts[] = ['type' => 'websafe', 'version' => __('Monospace Fonts', 'revslider'), 'label' => '\'Courier New\', Courier, monospace'];
		$fonts[] = ['type' => 'websafe', 'version' => __('Monospace Fonts', 'revslider'), 'label' => '\'Lucida Console\', Monaco, monospace'];


		//push all variants to the websafe fonts
		foreach($fonts as $f => $font){
			if(!empty($cfl) && is_array($cfl) && $font['type'] === 'custom') continue; //already manually added before on these

			$font[$f]['variants'] = ['100', '100italic', '200', '200italic', '300', '300italic', '400', '400italic', '500', '500italic', '600', '600italic', '700', '700italic', '800', '800italic', '900', '900italic'];
		}

		foreach(self::get_google_fonts() as $f => $val){
			$fonts[] = ['type' => 'googlefont', 'version' => __('Google Fonts', 'revslider'), 'label' => $f, 'variants' => $val['variants'], 'subsets' => $val['subsets'], 'category' => $val['category']];
		}

		return apply_filters('revslider_data_get_font_familys', apply_filters('revslider_operations_getArrFontFamilys', $fonts));
	}

	/**
	 * get animations array
	 * the user's own "in" layer animations
	 * @return array
	 */
	public function get_animations(){
		return $this->get_custom_animations_full_pre('in');
	}

	/**
	 * get "end" animations array
	 * the user's own "out" layer animations
	 * @return array
	 */
	public function get_end_animations(){
		return $this->get_custom_animations_full_pre('out');
	}

	/**
	 * the user's own looping layer animations
	 * @return array
	 */
	public function get_loop_animations(){
		return $this->get_custom_animations_full_pre('loop');
	}

	/**
	 * get the version 5 animations only, if available
	 * v5 stored custom animations with the types customin/customout; kept so old sliders still animate
	 * @return array
	 **/
	public function get_animations_v5(){
		global $SR_GLOBALS;
		$custom = [];
		$temp	= [];
		$sort	= [];

		$this->fill_animations();

		foreach($SR_GLOBALS['animations'] ?? [] as $value){
			$type = $this->get_val($value, ['params', 'type'], '');
			if(!in_array($type, ['customout', 'customin'])) continue;

			$settings = $this->get_val($value, 'settings', '');
			$type = $this->get_val($value, 'type', '');
			if($type == '' && $settings == ''){
				$temp[$value['id']] = $value;
				$temp[$value['id']]['id'] = $value['id'];
				$sort[$value['id']] = $value['handle'];
			}
		}
		if(!empty($sort)){
			asort($sort);
			foreach($sort ?? [] as $k => $v){
				$custom[$k] = $temp[$k];
			}
		}

		return $custom;
	}

	/**
	 * get custom animations
	 * @param string $pre which set to return: 'in', 'out' or 'loop'
	 * @return array
	 */
	public function get_custom_animations_full_pre($pre = 'in'){
		global $SR_GLOBALS;
		$custom = [];
		$temp	= [];
		$sort	= [];

		$this->fill_animations();

		foreach($SR_GLOBALS['animations'] ?? [] as $value){
			$settings = $this->get_val($value, 'settings', '');
			$type = $this->get_val($value, 'type', '');
			if($type == '' && $settings == '' || $type == $pre){
				$temp[$value['id']] = $value;
				$temp[$value['id']]['id'] = $value['id'];
				$sort[$value['id']] = $value['handle'];
			}

			if($settings == 'in' && $pre == 'in' || $settings == 'out' && $pre == 'out' || $settings == 'loop' && $pre == 'loop'){
				$temp[$value['id']] = $value['params'];
				$temp[$value['id']]['settings'] = $settings;
				$temp[$value['id']]['id'] = $value['id'];
				$sort[$value['id']] = $value['handle'];
			}
		}
		if(!empty($sort)){
			asort($sort);
			foreach($sort ?? [] as $k => $v){
				$custom[$k] = $temp[$k];
			}
		}

		return $custom;
	}

	/**
	 * Fetch all Custom Animations only one time
	 * loads the layer animations table into $SR_GLOBALS on first use - the getters above all read from there
	 * @since: 5.2.4
	 * @return void
	 **/
	public function fill_animations(){
		global $SR_GLOBALS;
		if(empty($SR_GLOBALS['animations'])){
			global $wpdb;

			$result = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS, ARRAY_A);
			$SR_GLOBALS['animations'] = (!empty($result)) ? $result : [];

			if(!empty($SR_GLOBALS['animations'])){
				foreach($SR_GLOBALS['animations'] as $ak => $av){
					$SR_GLOBALS['animations'][$ak]['params'] = json_decode(str_replace("'", '"', $av['params']), true);
				}
			}

			if(!empty($SR_GLOBALS['animations'])){
				array_walk_recursive($SR_GLOBALS['animations'], ['RevSliderData', 'force_to_boolean']);
			}
		}
	}

	/**
	 * make sure that all false and true are really boolean
	 * both arguments are modified by reference; meant for usort/comparison callbacks
	 * @return void
	 **/
	public static function force_to_boolean(&$a, $b){
		$a = ($a === 'false') ? false : $a;
		$a = ($a === 'true') ? true : $a;
		$b = ($b === 'false') ? false : $b;
		$b = ($b === 'true') ? true : $b;
	}

	/**
	 * Get all images sizes + custom added sizes
	 * @param string $type source type the sizes are for, e.g. 'gallery' or 'flickr'
	 * @return array size handle => label
	 */
	public function get_all_image_sizes($type = 'gallery'){
		$custom_sizes = [];

		switch($type){
			case 'flickr':
				$custom_sizes = [
					'original' => __('Original', 'revslider'),
					'large' => __('Large', 'revslider'),
					'large-square' => __('Large Square', 'revslider'),
					'medium' => __('Medium', 'revslider'),
					'medium-800' => __('Medium 800', 'revslider'),
					'medium-640' => __('Medium 640', 'revslider'),
					'small' => __('Small', 'revslider'),
					'small-320' => __('Small 320', 'revslider'),
					'thumbnail' => __('Thumbnail', 'revslider'),
					'square' => __('Square', 'revslider'),
				];
			break;
			case 'instagram':
				$custom_sizes = [
					'standard_resolution' => __('Standard Resolution', 'revslider'),
					'thumbnail' => __('Thumbnail', 'revslider'),
					'low_resolution' => __('Low Resolution', 'revslider'),
					'original_size' => __('Original Size', 'revslider'),
					'large' => __('Large Size', 'revslider'),
				];
			break;
			case 'facebook':
				$custom_sizes = [
					'full' => __('Original Size', 'revslider'),
					'thumbnail' => __('Thumbnail', 'revslider'),
				];
			break;
			case 'youtube':
				$custom_sizes = [
					'high' => __('High', 'revslider'),
					'medium' => __('Medium', 'revslider'),
					'default' => __('Default', 'revslider'),
					'standard' => __('Standard', 'revslider'),
					'maxres' => __('Max. Res.', 'revslider'),
				];
			break;
			case 'vimeo':
				$custom_sizes = [
					'thumbnail_large' => __('Large', 'revslider'),
					'thumbnail_medium' => __('Medium', 'revslider'),
					'thumbnail_small' => __('Small', 'revslider'),
				];
			break;
			case 'gallery':
			default:
				$added_image_sizes = get_intermediate_image_sizes();
				if(!empty($added_image_sizes) && is_array($added_image_sizes)){
					foreach($added_image_sizes as $key => $img_size_handle){
						$custom_sizes[$img_size_handle] = ucwords(str_replace('_', ' ', $img_size_handle));
					}
				}
				$img_orig_sources = [
					'full' => __('Original Size', 'revslider'),
					'thumbnail' => __('Thumbnail', 'revslider'),
					'medium' => __('Medium', 'revslider'),
					'large' => __('Large', 'revslider'),
				];
				$custom_sizes = array_merge($img_orig_sources, $custom_sizes);
			break;
		}

		return $custom_sizes;
	}

	/**
	 * get the default layer animations
	 * the built-in animation catalogue (JSON literals below) merged with the user's custom ones
	 * @param bool $raw true returns the JSON string instead of the decoded array
	 * @return array|string
	 **/
	public function get_layer_animations($raw = false){
		$custom_in = $this->get_animations();
		$custom_out = $this->get_end_animations();
		$custom_loop = $this->get_loop_animations();

		$in = '{
			"basic":{
				"none":{"content":{"all":[{"o":1},{"o":1,"d":300}]}},
				"fade": {
					"multi":true,
					"noanim":{"content":{"all":[{"o":1},{"o":1,"d":300}]}},
					"easy":{"content":{"all":[{"o":0},{"d":1500,"e":"power4.inOut","o":1}]}},					
					"middle":{"content":{"all":[{"o":0},{"d":1000,"e":"power4.inOut","o":1}]}},					
					"strong":{"content":{"all":[{"o":0},{"d":500,"e":"power4.inOut","o":1}]}}
				}
			},
			"cinematic":{"reveal":{"multi":true,"up":{"content":{"all":[{"y":"90"},{"d":520,"e":"power4.out","y":0}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"sX":[{"sX":"0.8"},{"d":1150,"e":"back.out","sX":1}],"sY":[{"sY":"0.8"},{"d":1150,"e":"back.out","sY":1}],"rZ":[{"rZ":"-6deg"},{"d":820,"e":"power2.out","rZ":0}]}},"down":{"content":{"all":[{"y":"-90"},{"d":520,"e":"power4.out","y":0}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"sX":[{"sX":"0.8"},{"d":1150,"e":"back.out","sX":1}],"sY":[{"sY":"0.8"},{"d":1150,"e":"back.out","sY":1}],"rZ":[{"rZ":"6deg"},{"d":820,"e":"power2.out","rZ":0}]}},"left":{"content":{"all":[{"x":"90"},{"d":520,"e":"power4.out","x":0}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"sX":[{"sX":"0.8"},{"d":1150,"e":"back.out","sX":1}],"sY":[{"sY":"0.8"},{"d":1150,"e":"back.out","sY":1}],"rZ":[{"rZ":"-6deg"},{"d":820,"e":"power2.out","rZ":0}]}},"right":{"content":{"all":[{"x":"-90"},{"d":520,"e":"power4.out","x":0}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"sX":[{"sX":"0.8"},{"d":1150,"e":"back.out","sX":1}],"sY":[{"sY":"0.8"},{"d":1150,"e":"back.out","sY":1}],"rZ":[{"rZ":"6deg"},{"d":820,"e":"power2.out","rZ":0}]}}},"rise":{"multi":true,"up":{"content":{"all":[{"y":"54"},{"d":700,"e":"power3.out","y":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sY":[{"sY":"0.9"},{"d":1000,"e":"back.out","sY":1}],"sX":[{"sX":"0.96"},{"d":1250,"e":"power2.out","sX":1}]}},"down":{"content":{"all":[{"y":"-54"},{"d":700,"e":"power3.out","y":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sY":[{"sY":"0.9"},{"d":1000,"e":"back.out","sY":1}],"sX":[{"sX":"0.96"},{"d":1250,"e":"power2.out","sX":1}]}},"left":{"content":{"all":[{"x":"54"},{"d":700,"e":"power3.out","x":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sY":[{"sY":"0.9"},{"d":1000,"e":"back.out","sY":1}],"sX":[{"sX":"0.96"},{"d":1250,"e":"power2.out","sX":1}]}},"right":{"content":{"all":[{"x":"-54"},{"d":700,"e":"power3.out","x":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sY":[{"sY":"0.9"},{"d":1000,"e":"back.out","sY":1}],"sX":[{"sX":"0.96"},{"d":1250,"e":"power2.out","sX":1}]}}},"glide":{"multi":true,"up":{"content":{"all":[{"y":"240"},{"d":950,"e":"power4.out","y":0}],"o":[{"o":0},{"d":400,"e":"power1.out","o":1}],"sY":[{"sY":"1.14"},{"d":1250,"e":"power2.out","sY":1}],"sX":[{"sX":"0.94"},{"d":1250,"e":"power2.out","sX":1}],"skY":[{"skY":"-5deg"},{"d":420,"e":"power3.out","skY":0}]}},"down":{"content":{"all":[{"y":"-240"},{"d":950,"e":"power4.out","y":0}],"o":[{"o":0},{"d":400,"e":"power1.out","o":1}],"sY":[{"sY":"1.14"},{"d":1250,"e":"power2.out","sY":1}],"sX":[{"sX":"0.94"},{"d":1250,"e":"power2.out","sX":1}],"skY":[{"skY":"5deg"},{"d":420,"e":"power3.out","skY":0}]}},"left":{"content":{"all":[{"x":"240"},{"d":950,"e":"power4.out","x":0}],"o":[{"o":0},{"d":400,"e":"power1.out","o":1}],"sX":[{"sX":"1.14"},{"d":1250,"e":"power2.out","sX":1}],"sY":[{"sY":"0.94"},{"d":1250,"e":"power2.out","sY":1}],"skX":[{"skX":"-5deg"},{"d":420,"e":"power3.out","skX":0}]}},"right":{"content":{"all":[{"x":"-240"},{"d":950,"e":"power4.out","x":0}],"o":[{"o":0},{"d":400,"e":"power1.out","o":1}],"sX":[{"sX":"1.14"},{"d":1250,"e":"power2.out","sX":1}],"sY":[{"sY":"0.94"},{"d":1250,"e":"power2.out","sY":1}],"skX":[{"skX":"5deg"},{"d":420,"e":"power3.out","skX":0}]}}},"tilt":{"multi":true,"up":{"content":{"all":[{"y":"34"},{"d":620,"e":"power3.out","y":0}],"o":[{"o":0},{"d":320,"e":"power2.out","o":1}],"rX":[{"rX":"26deg"},{"d":1150,"e":"power3.out","rX":0}],"rY":[{"rY":"-15deg"},{"d":700,"e":"power2.out","rY":0}],"sX":[{"sX":"0.92"},{"d":980,"e":"power2.out","sX":1}],"sY":[{"sY":"0.92"},{"d":980,"e":"power2.out","sY":1}]}},"down":{"content":{"all":[{"y":"-34"},{"d":620,"e":"power3.out","y":0}],"o":[{"o":0},{"d":320,"e":"power2.out","o":1}],"rX":[{"rX":"-26deg"},{"d":1150,"e":"power3.out","rX":0}],"rY":[{"rY":"15deg"},{"d":700,"e":"power2.out","rY":0}],"sX":[{"sX":"0.92"},{"d":980,"e":"power2.out","sX":1}],"sY":[{"sY":"0.92"},{"d":980,"e":"power2.out","sY":1}]}},"left":{"content":{"all":[{"x":"34"},{"d":620,"e":"power3.out","x":0}],"o":[{"o":0},{"d":320,"e":"power2.out","o":1}],"rY":[{"rY":"26deg"},{"d":1150,"e":"power3.out","rY":0}],"rX":[{"rX":"-15deg"},{"d":700,"e":"power2.out","rX":0}],"sX":[{"sX":"0.92"},{"d":980,"e":"power2.out","sX":1}],"sY":[{"sY":"0.92"},{"d":980,"e":"power2.out","sY":1}]}},"right":{"content":{"all":[{"x":"-34"},{"d":620,"e":"power3.out","x":0}],"o":[{"o":0},{"d":320,"e":"power2.out","o":1}],"rY":[{"rY":"-26deg"},{"d":1150,"e":"power3.out","rY":0}],"rX":[{"rX":"15deg"},{"d":700,"e":"power2.out","rX":0}],"sX":[{"sX":"0.92"},{"d":980,"e":"power2.out","sX":1}],"sY":[{"sY":"0.92"},{"d":980,"e":"power2.out","sY":1}]}}},"swing":{"multi":true,"up":{"content":{"orig":{"x":"50%","y":"0%","z":0},"all":[{"y":"60"},{"d":560,"e":"power3.out","y":0}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"rZ":[{"rZ":"-16deg"},{"d":1200,"e":"elastic.out","rZ":0}],"sY":[{"sY":"0.94"},{"d":800,"e":"back.out","sY":1}]}},"down":{"content":{"orig":{"x":"50%","y":"0%","z":0},"all":[{"y":"-60"},{"d":560,"e":"power3.out","y":0}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"rZ":[{"rZ":"16deg"},{"d":1200,"e":"elastic.out","rZ":0}],"sY":[{"sY":"0.94"},{"d":800,"e":"back.out","sY":1}]}},"left":{"content":{"orig":{"x":"50%","y":"0%","z":0},"all":[{"x":"60"},{"d":560,"e":"power3.out","x":0}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"rZ":[{"rZ":"-16deg"},{"d":1200,"e":"elastic.out","rZ":0}],"sY":[{"sY":"0.94"},{"d":800,"e":"back.out","sY":1}]}},"right":{"content":{"orig":{"x":"50%","y":"0%","z":0},"all":[{"x":"-60"},{"d":560,"e":"power3.out","x":0}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"rZ":[{"rZ":"16deg"},{"d":1200,"e":"elastic.out","rZ":0}],"sY":[{"sY":"0.94"},{"d":800,"e":"back.out","sY":1}]}}},"bounce":{"multi":true,"up":{"content":{"all":[{"y":"80"},{"d":400,"e":"power3.out","y":0}],"o":[{"o":0},{"d":200,"e":"power1.out","o":1}],"sY":[{"sY":"1.3"},{"d":1250,"e":"elastic.out","sY":1}],"sX":[{"sX":"0.78"},{"d":1250,"e":"back.out","sX":1}]}},"down":{"content":{"all":[{"y":"-80"},{"d":400,"e":"power3.out","y":0}],"o":[{"o":0},{"d":200,"e":"power1.out","o":1}],"sY":[{"sY":"1.3"},{"d":1250,"e":"elastic.out","sY":1}],"sX":[{"sX":"0.78"},{"d":1250,"e":"back.out","sX":1}]}},"left":{"content":{"all":[{"x":"80"},{"d":400,"e":"power3.out","x":0}],"o":[{"o":0},{"d":200,"e":"power1.out","o":1}],"sX":[{"sX":"1.3"},{"d":1250,"e":"elastic.out","sX":1}],"sY":[{"sY":"0.78"},{"d":1250,"e":"back.out","sY":1}]}},"right":{"content":{"all":[{"x":"-80"},{"d":400,"e":"power3.out","x":0}],"o":[{"o":0},{"d":200,"e":"power1.out","o":1}],"sX":[{"sX":"1.3"},{"d":1250,"e":"elastic.out","sX":1}],"sY":[{"sY":"0.78"},{"d":1250,"e":"back.out","sY":1}]}}},"skewslide":{"multi":true,"up":{"content":{"all":[{"y":"170"},{"d":580,"e":"power4.out","y":0}],"o":[{"o":0},{"d":230,"e":"power1.out","o":1}],"skY":[{"skY":"-16deg"},{"d":330,"e":"power4.out","skY":0}],"sY":[{"sY":"1.16"},{"d":900,"e":"power2.out","sY":1}],"sX":[{"sX":"0.95"},{"d":900,"e":"power2.out","sX":1}]}},"down":{"content":{"all":[{"y":"-170"},{"d":580,"e":"power4.out","y":0}],"o":[{"o":0},{"d":230,"e":"power1.out","o":1}],"skY":[{"skY":"16deg"},{"d":330,"e":"power4.out","skY":0}],"sY":[{"sY":"1.16"},{"d":900,"e":"power2.out","sY":1}],"sX":[{"sX":"0.95"},{"d":900,"e":"power2.out","sX":1}]}},"left":{"content":{"all":[{"x":"170"},{"d":580,"e":"power4.out","x":0}],"o":[{"o":0},{"d":230,"e":"power1.out","o":1}],"skX":[{"skX":"-16deg"},{"d":330,"e":"power4.out","skX":0}],"sX":[{"sX":"1.16"},{"d":900,"e":"power2.out","sX":1}],"sY":[{"sY":"0.95"},{"d":900,"e":"power2.out","sY":1}]}},"right":{"content":{"all":[{"x":"-170"},{"d":580,"e":"power4.out","x":0}],"o":[{"o":0},{"d":230,"e":"power1.out","o":1}],"skX":[{"skX":"16deg"},{"d":330,"e":"power4.out","skX":0}],"sX":[{"sX":"1.16"},{"d":900,"e":"power2.out","sX":1}],"sY":[{"sY":"0.95"},{"d":900,"e":"power2.out","sY":1}]}}},"overshoot":{"multi":true,"up":{"content":{"all":[{"y":"120"},{"d":950,"e":"back.out","y":0}],"o":[{"o":0},{"d":220,"e":"power2.out","o":1}],"sX":[{"sX":"0.7"},{"d":600,"e":"power3.out","sX":1}],"sY":[{"sY":"0.7"},{"d":600,"e":"power3.out","sY":1}],"rZ":[{"rZ":"-8deg"},{"d":750,"e":"back.out","rZ":0}]}},"down":{"content":{"all":[{"y":"-120"},{"d":950,"e":"back.out","y":0}],"o":[{"o":0},{"d":220,"e":"power2.out","o":1}],"sX":[{"sX":"0.7"},{"d":600,"e":"power3.out","sX":1}],"sY":[{"sY":"0.7"},{"d":600,"e":"power3.out","sY":1}],"rZ":[{"rZ":"8deg"},{"d":750,"e":"back.out","rZ":0}]}},"left":{"content":{"all":[{"x":"120"},{"d":950,"e":"back.out","x":0}],"o":[{"o":0},{"d":220,"e":"power2.out","o":1}],"sX":[{"sX":"0.7"},{"d":600,"e":"power3.out","sX":1}],"sY":[{"sY":"0.7"},{"d":600,"e":"power3.out","sY":1}],"rZ":[{"rZ":"-8deg"},{"d":750,"e":"back.out","rZ":0}]}},"right":{"content":{"all":[{"x":"-120"},{"d":950,"e":"back.out","x":0}],"o":[{"o":0},{"d":220,"e":"power2.out","o":1}],"sX":[{"sX":"0.7"},{"d":600,"e":"power3.out","sX":1}],"sY":[{"sY":"0.7"},{"d":600,"e":"power3.out","sY":1}],"rZ":[{"rZ":"8deg"},{"d":750,"e":"back.out","rZ":0}]}}},"punch":{"multi":true,"up":{"content":{"all":[{"y":"22"},{"d":450,"e":"power4.out","y":0}],"o":[{"o":0},{"d":140,"e":"power1.out","o":1}],"sX":[{"sX":"0.42"},{"d":700,"e":"back.out","sX":1}],"sY":[{"sY":"0.6"},{"d":700,"e":"back.out","sY":1}],"rZ":[{"rZ":"-10deg"},{"d":400,"e":"power4.out","rZ":0}]}},"down":{"content":{"all":[{"y":"-22"},{"d":450,"e":"power4.out","y":0}],"o":[{"o":0},{"d":140,"e":"power1.out","o":1}],"sX":[{"sX":"0.42"},{"d":700,"e":"back.out","sX":1}],"sY":[{"sY":"0.6"},{"d":700,"e":"back.out","sY":1}],"rZ":[{"rZ":"10deg"},{"d":400,"e":"power4.out","rZ":0}]}},"left":{"content":{"all":[{"x":"22"},{"d":450,"e":"power4.out","x":0}],"o":[{"o":0},{"d":140,"e":"power1.out","o":1}],"sX":[{"sX":"0.42"},{"d":700,"e":"back.out","sX":1}],"sY":[{"sY":"0.6"},{"d":700,"e":"back.out","sY":1}],"rZ":[{"rZ":"-10deg"},{"d":400,"e":"power4.out","rZ":0}]}},"right":{"content":{"all":[{"x":"-22"},{"d":450,"e":"power4.out","x":0}],"o":[{"o":0},{"d":140,"e":"power1.out","o":1}],"sX":[{"sX":"0.42"},{"d":700,"e":"back.out","sX":1}],"sY":[{"sY":"0.6"},{"d":700,"e":"back.out","sY":1}],"rZ":[{"rZ":"10deg"},{"d":400,"e":"power4.out","rZ":0}]}}},"zoomblast":{"multi":true,"up":{"content":{"all":[{"y":"40"},{"d":700,"e":"power3.out","y":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sX":[{"sX":"2.4"},{"d":1150,"e":"power4.out","sX":1}],"sY":[{"sY":"2.4"},{"d":1150,"e":"power4.out","sY":1}],"rZ":[{"rZ":"-5deg"},{"d":900,"e":"power2.out","rZ":0}]}},"down":{"content":{"all":[{"y":"-40"},{"d":700,"e":"power3.out","y":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sX":[{"sX":"2.4"},{"d":1150,"e":"power4.out","sX":1}],"sY":[{"sY":"2.4"},{"d":1150,"e":"power4.out","sY":1}],"rZ":[{"rZ":"5deg"},{"d":900,"e":"power2.out","rZ":0}]}},"left":{"content":{"all":[{"x":"40"},{"d":700,"e":"power3.out","x":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sX":[{"sX":"2.4"},{"d":1150,"e":"power4.out","sX":1}],"sY":[{"sY":"2.4"},{"d":1150,"e":"power4.out","sY":1}],"rZ":[{"rZ":"-5deg"},{"d":900,"e":"power2.out","rZ":0}]}},"right":{"content":{"all":[{"x":"-40"},{"d":700,"e":"power3.out","x":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sX":[{"sX":"2.4"},{"d":1150,"e":"power4.out","sX":1}],"sY":[{"sY":"2.4"},{"d":1150,"e":"power4.out","sY":1}],"rZ":[{"rZ":"5deg"},{"d":900,"e":"power2.out","rZ":0}]}}},"vortex":{"multi":true,"up":{"content":{"all":[{"y":"70"},{"d":640,"e":"power3.out","y":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"rZ":[{"rZ":"-95deg"},{"d":1250,"e":"power2.out","rZ":0}],"sX":[{"sX":"0.32"},{"d":950,"e":"back.out","sX":1}],"sY":[{"sY":"0.32"},{"d":950,"e":"back.out","sY":1}]}},"down":{"content":{"all":[{"y":"-70"},{"d":640,"e":"power3.out","y":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"rZ":[{"rZ":"95deg"},{"d":1250,"e":"power2.out","rZ":0}],"sX":[{"sX":"0.32"},{"d":950,"e":"back.out","sX":1}],"sY":[{"sY":"0.32"},{"d":950,"e":"back.out","sY":1}]}},"left":{"content":{"all":[{"x":"70"},{"d":640,"e":"power3.out","x":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"rZ":[{"rZ":"-95deg"},{"d":1250,"e":"power2.out","rZ":0}],"sX":[{"sX":"0.32"},{"d":950,"e":"back.out","sX":1}],"sY":[{"sY":"0.32"},{"d":950,"e":"back.out","sY":1}]}},"right":{"content":{"all":[{"x":"-70"},{"d":640,"e":"power3.out","x":0}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"rZ":[{"rZ":"95deg"},{"d":1250,"e":"power2.out","rZ":0}],"sX":[{"sX":"0.32"},{"d":950,"e":"back.out","sX":1}],"sY":[{"sY":"0.32"},{"d":950,"e":"back.out","sY":1}]}}},"fold":{"multi":true,"up":{"content":{"orig":{"x":"50%","y":"100%","z":0},"all":[{"y":"30"},{"d":560,"e":"power3.out","y":0}],"o":[{"o":0},{"d":260,"e":"power2.out","o":1}],"rX":[{"rX":"-82deg"},{"d":1050,"e":"back.out","rX":0}],"sY":[{"sY":"0.92"},{"d":880,"e":"power2.out","sY":1}]}},"down":{"content":{"orig":{"x":"50%","y":"0%","z":0},"all":[{"y":"-30"},{"d":560,"e":"power3.out","y":0}],"o":[{"o":0},{"d":260,"e":"power2.out","o":1}],"rX":[{"rX":"82deg"},{"d":1050,"e":"back.out","rX":0}],"sY":[{"sY":"0.92"},{"d":880,"e":"power2.out","sY":1}]}},"left":{"content":{"orig":{"x":"100%","y":"50%","z":0},"all":[{"x":"30"},{"d":560,"e":"power3.out","x":0}],"o":[{"o":0},{"d":260,"e":"power2.out","o":1}],"rY":[{"rY":"82deg"},{"d":1050,"e":"back.out","rY":0}],"sY":[{"sY":"0.92"},{"d":880,"e":"power2.out","sY":1}]}},"right":{"content":{"orig":{"x":"0%","y":"50%","z":0},"all":[{"x":"-30"},{"d":560,"e":"power3.out","x":0}],"o":[{"o":0},{"d":260,"e":"power2.out","o":1}],"rY":[{"rY":"-82deg"},{"d":1050,"e":"back.out","rY":0}],"sY":[{"sY":"0.92"},{"d":880,"e":"power2.out","sY":1}]}}}},
			"curve":{"arcin":{"multi":true,"up":{"content":{"all":[{"x":"0","y":"150"},{"d":650,"e":"power2.out","x":0,"y":0,"arc":1.2}],"o":[{"o":0},{"d":260,"e":"power2.out","o":1}]}},"down":{"content":{"all":[{"x":"0","y":"-150"},{"d":650,"e":"power2.out","x":0,"y":0,"arc":-1.2}],"o":[{"o":0},{"d":260,"e":"power2.out","o":1}]}},"left":{"content":{"all":[{"x":"190","y":"0"},{"d":650,"e":"power2.out","x":0,"y":0,"arc":1.2}],"o":[{"o":0},{"d":260,"e":"power2.out","o":1}]}},"right":{"content":{"all":[{"x":"-190","y":"0"},{"d":650,"e":"power2.out","x":0,"y":0,"arc":-1.2}],"o":[{"o":0},{"d":260,"e":"power2.out","o":1}]}}},"swoop":{"multi":true,"up":{"content":{"all":[{"x":"0","y":"340"},{"d":1150,"e":"power1.out","x":0,"y":0,"arc":2.4}],"o":[{"o":0},{"d":280,"e":"power2.out","o":1}],"sX":[{"sX":"0.9"},{"d":1250,"e":"power2.out","sX":1}],"sY":[{"sY":"0.9"},{"d":1250,"e":"power2.out","sY":1}],"rZ":[{"rZ":"-20deg"},{"d":1250,"e":"power2.out","rZ":0}]}},"down":{"content":{"all":[{"x":"0","y":"-340"},{"d":1150,"e":"power1.out","x":0,"y":0,"arc":-2.4}],"o":[{"o":0},{"d":280,"e":"power2.out","o":1}],"sX":[{"sX":"0.9"},{"d":1250,"e":"power2.out","sX":1}],"sY":[{"sY":"0.9"},{"d":1250,"e":"power2.out","sY":1}],"rZ":[{"rZ":"20deg"},{"d":1250,"e":"power2.out","rZ":0}]}},"left":{"content":{"all":[{"x":"420","y":"0"},{"d":1150,"e":"power1.out","x":0,"y":0,"arc":2.4}],"o":[{"o":0},{"d":280,"e":"power2.out","o":1}],"sX":[{"sX":"0.9"},{"d":1250,"e":"power2.out","sX":1}],"sY":[{"sY":"0.9"},{"d":1250,"e":"power2.out","sY":1}],"rZ":[{"rZ":"-20deg"},{"d":1250,"e":"power2.out","rZ":0}]}},"right":{"content":{"all":[{"x":"-420","y":"0"},{"d":1150,"e":"power1.out","x":0,"y":0,"arc":-2.4}],"o":[{"o":0},{"d":280,"e":"power2.out","o":1}],"sX":[{"sX":"0.9"},{"d":1250,"e":"power2.out","sX":1}],"sY":[{"sY":"0.9"},{"d":1250,"e":"power2.out","sY":1}],"rZ":[{"rZ":"20deg"},{"d":1250,"e":"power2.out","rZ":0}]}}},"whip":{"multi":true,"up":{"content":{"all":[{"x":"0","y":"420"},{"d":520,"e":"power2.out","x":0,"y":0,"arc":-1.2}],"o":[{"o":0},{"d":160,"e":"power1.out","o":1}],"skY":[{"skY":"-22deg"},{"d":340,"e":"power4.out","skY":0}],"sY":[{"sY":"1.2"},{"d":800,"e":"power2.out","sY":1}],"sX":[{"sX":"0.9"},{"d":800,"e":"power2.out","sX":1}]}},"down":{"content":{"all":[{"x":"0","y":"-420"},{"d":520,"e":"power2.out","x":0,"y":0,"arc":1.2}],"o":[{"o":0},{"d":160,"e":"power1.out","o":1}],"skY":[{"skY":"22deg"},{"d":340,"e":"power4.out","skY":0}],"sY":[{"sY":"1.2"},{"d":800,"e":"power2.out","sY":1}],"sX":[{"sX":"0.9"},{"d":800,"e":"power2.out","sX":1}]}},"left":{"content":{"all":[{"x":"520","y":"0"},{"d":520,"e":"power2.out","x":0,"y":0,"arc":-1.2}],"o":[{"o":0},{"d":160,"e":"power1.out","o":1}],"skX":[{"skX":"-22deg"},{"d":340,"e":"power4.out","skX":0}],"sX":[{"sX":"1.2"},{"d":800,"e":"power2.out","sX":1}],"sY":[{"sY":"0.9"},{"d":800,"e":"power2.out","sY":1}]}},"right":{"content":{"all":[{"x":"-520","y":"0"},{"d":520,"e":"power2.out","x":0,"y":0,"arc":1.2}],"o":[{"o":0},{"d":160,"e":"power1.out","o":1}],"skX":[{"skX":"22deg"},{"d":340,"e":"power4.out","skX":0}],"sX":[{"sX":"1.2"},{"d":800,"e":"power2.out","sX":1}],"sY":[{"sY":"0.9"},{"d":800,"e":"power2.out","sY":1}]}}},"drift":{"multi":true,"up":{"content":{"all":[{"x":"0","y":"220"},{"d":1900,"e":"sine.out","x":0,"y":0,"arc":2.8}],"o":[{"o":0},{"d":900,"e":"sine.out","o":1}],"sX":[{"sX":"1.08"},{"d":2000,"e":"sine.out","sX":1}],"sY":[{"sY":"1.08"},{"d":2000,"e":"sine.out","sY":1}]}},"down":{"content":{"all":[{"x":"0","y":"-220"},{"d":1900,"e":"sine.out","x":0,"y":0,"arc":-2.8}],"o":[{"o":0},{"d":900,"e":"sine.out","o":1}],"sX":[{"sX":"1.08"},{"d":2000,"e":"sine.out","sX":1}],"sY":[{"sY":"1.08"},{"d":2000,"e":"sine.out","sY":1}]}},"left":{"content":{"all":[{"x":"260","y":"0"},{"d":1900,"e":"sine.out","x":0,"y":0,"arc":2.8}],"o":[{"o":0},{"d":900,"e":"sine.out","o":1}],"sX":[{"sX":"1.08"},{"d":2000,"e":"sine.out","sX":1}],"sY":[{"sY":"1.08"},{"d":2000,"e":"sine.out","sY":1}]}},"right":{"content":{"all":[{"x":"-260","y":"0"},{"d":1900,"e":"sine.out","x":0,"y":0,"arc":-2.8}],"o":[{"o":0},{"d":900,"e":"sine.out","o":1}],"sX":[{"sX":"1.08"},{"d":2000,"e":"sine.out","sX":1}],"sY":[{"sY":"1.08"},{"d":2000,"e":"sine.out","sY":1}]}}},"hook":{"multi":true,"up":{"content":{"all":[{"x":"0","y":"280"},{"d":1000,"e":"power2.inOut","x":0,"y":0,"arc":-2.4}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sX":[{"sX":"0.94"},{"d":1150,"e":"power2.out","sX":1}],"sY":[{"sY":"0.94"},{"d":1150,"e":"power2.out","sY":1}],"rZ":[{"rZ":"14deg"},{"d":900,"e":"power3.out","rZ":0}]}},"down":{"content":{"all":[{"x":"0","y":"-280"},{"d":1000,"e":"power2.inOut","x":0,"y":0,"arc":2.4}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sX":[{"sX":"0.94"},{"d":1150,"e":"power2.out","sX":1}],"sY":[{"sY":"0.94"},{"d":1150,"e":"power2.out","sY":1}],"rZ":[{"rZ":"-14deg"},{"d":900,"e":"power3.out","rZ":0}]}},"left":{"content":{"all":[{"x":"340","y":"0"},{"d":1000,"e":"power2.inOut","x":0,"y":0,"arc":-2.4}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sX":[{"sX":"0.94"},{"d":1150,"e":"power2.out","sX":1}],"sY":[{"sY":"0.94"},{"d":1150,"e":"power2.out","sY":1}],"rZ":[{"rZ":"14deg"},{"d":900,"e":"power3.out","rZ":0}]}},"right":{"content":{"all":[{"x":"-340","y":"0"},{"d":1000,"e":"power2.inOut","x":0,"y":0,"arc":2.4}],"o":[{"o":0},{"d":300,"e":"power2.out","o":1}],"sX":[{"sX":"0.94"},{"d":1150,"e":"power2.out","sX":1}],"sY":[{"sY":"0.94"},{"d":1150,"e":"power2.out","sY":1}],"rZ":[{"rZ":"-14deg"},{"d":900,"e":"power3.out","rZ":0}]}}},"lob":{"multi":true,"left":{"content":{"all":[{"x":"300","y":"180"},{"d":1000,"e":"power2.out","x":0,"y":0,"arc":2.2}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"sY":[{"sY":"1.1"},{"d":900,"e":"back.out","sY":1}],"sX":[{"sX":"0.92"},{"d":900,"e":"back.out","sX":1}],"rZ":[{"rZ":"10deg"},{"d":1100,"e":"power2.out","rZ":0}]}},"right":{"content":{"all":[{"x":"-300","y":"180"},{"d":1000,"e":"power2.out","x":0,"y":0,"arc":-2.2}],"o":[{"o":0},{"d":240,"e":"power2.out","o":1}],"sY":[{"sY":"1.1"},{"d":900,"e":"back.out","sY":1}],"sX":[{"sX":"0.92"},{"d":900,"e":"back.out","sX":1}],"rZ":[{"rZ":"-10deg"},{"d":1100,"e":"power2.out","rZ":0}]}}}},
			"lines":{
				"fade":{
					"multi":true,"demo":"lines",
					"easy":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"lines":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":40,"o":1}]}},
					"middle":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"lines":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":100,"o":1}]}},
					"strong":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"lines":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":150,"o":1}]}}					
				},
				"flyinrandom":{
					"multi":true,
					"demo":"lines",
					"up":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"lines":{"ex":true,"all":[{"y":["#a","{10,200}","#a","#a","#a"],"xRe":1,"o":"0","rZ":"{0,50}"},{"d":700, "dir":"start", "e":"power4.inOut","sd":105,"y":0,"o":1,"rZ":"0deg"}]}},
					"down":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"lines":{"ex":true,"all":[{"y":["#a","{-200,-10}","#a","#a","#a"],"xRe":1,"o":"0","rZ":"{-50,0}"},{"d":700, "dir":"start", "e":"power4.inOut","sd":105,"y":0,"o":1,"rZ":"0deg"}]}},
					"left":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"lines":{"ex":true,"all":[{"x":["#a","{50,200}","#a","#a","#a"],"xRe":1,"o":"0","rZ":"{0,90}"},{"d":1000, "dir":"start", "e":"power4.inOut","sd":75,"x":0,"o":1,"rZ":"0deg"}]}},
					"right":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"lines":{"ex":true,"all":[{"x":["#a","{-200,50}","#a","#a","#a"],"xRe":1,"o":"0","rZ":"{-90,0}"},{"d":1000, "dir":"end", "e":"power4.inOut","sd":75,"x":0,"o":1,"rZ":"0deg"}]}},					
					"cross":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"lines":{"ex":true,"all":[{"x":["#a","{-200,200}","#a","#a","#a"],"y":["#a","{-200,200}","#a","#a","#a"],"xRe":1,"yRe":1,"o":"0","rZ":"{-9,90}"},{"d":1000, "dir":"start", "e":"power4.inOut","sd":75,"x":0,"y":0,"o":1,"rZ":"0deg"}]}}
				},
				"flip":{
					"multi":true,
					"demo":"lines",					
					"up":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"lines":{"ex":true,"orig":{"z":"-50%","y":"0%"},"all":[{"y":"-100%","yRe":0,"o":"0","rX":"-90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"y":0,"o":1,"rX":0}]}},
					"down":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"lines":{"ex":true,"orig":{"z":"-50%","y":"100%"},"all":[{"y":"100%","yRe":0,"o":"0","rX":"90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"y":0,"o":1,"rX":0}]}}				
				},
				"slide":{
					"multi":true,
					"demo":"lines",										
					"left":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"lines":{"ex":true,"orig":{"z":"-30%","x":"100%"},"all":[{"x":"105%","xRe":1,"o":"0","rY":"-90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"x":0,"o":1,"rY":0}]}},
					"right":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"lines":{"ex":true,"orig":{"z":"-30%","x":"0%"},"all":[{"x":"-105%","xRe":1,"o":"0","rY":"90deg"},{"d":800, "dir":"end", "e":"power4.inOut","sd":75,"x":0,"o":1,"rY":0}]}},
					"pressleft":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"lines":{"ex":true,"orig":{"z":"-30%","x":"100%"},"all":[{"x":"105%","xRe":1,"o":"0","rY":"-90deg"},{"d":800, "dir":"start", "e":"back.out","sd":75,"x":0,"o":1,"rY":0}]}},
					"pressright":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"lines":{"ex":true,"orig":{"z":"-30%","x":"0%"},"all":[{"x":"-105%","xRe":1,"o":"0","rY":"90deg"},{"d":800, "dir":"end", "e":"back.out","sd":75,"x":0,"o":1,"rY":0}]}}
				},
				"cycle":{
					"multi":true,
					"demo":"lines",
					"vertical":{"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":"1","rX":"70deg","rZ":"20deg","rY":"20deg"},{"t":20,"d":1250,"f":300,"e":"power2.out","a":"Anim To","pE":"d","sX":1,"sY":1,"o":1,"off":0,"dC":1700,"rX":"0deg","rZ":"0deg","rY":"0deg"}]},"lines":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"y":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rX":"[-70,70]","dir":"start"},{"t":20,"d":970,"e":"power4.inOut","x":["#a",0,"#a","#a","#a"],"o":1,"y":0, "rX":0,"dir":"center","sd":31}]}},
					"horizontal":{"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":"1","rX":"20deg","rZ":"20deg","rY":"70deg"},{"t":20,"d":1250,"f":300,"e":"power2.out","a":"Anim To","pE":"d","sX":1,"sY":1,"o":1,"off":0,"dC":1700,"rX":"0deg","rZ":"0deg","rY":"0deg"}]},"lines":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"x":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rY":"[-70,70]","dir":"start"},{"t":20,"d":970,"e":"power4.inOut","y":["#a",0,"#a","#a","#a"],"o":1,"x":0, "rY":0,"dir":"center","sd":31}]}}					
				},
				"popin": {
					"multi": true,
					"demo": "lines",
					"easy": {
						"content": { "all": [ { "o": 0, "sX": 0.7, "sY": 0.7 }, { "d": 850, "e": "elastic.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"lines": { "ex": true, "all": [ { "o": 0, "sX": 0.7, "sY": 0.7 }, { "d": 1000, "e": "elastic.out", "o": 1, "sX": 1, "sY": 1, "sd": 65 } ] }
					},
					"medium": {
						"content": { "all": [ { "o": 0, "sX": 0.3, "sY": 0.3 }, { "d": 480, "e": "back.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"lines": { "ex": true, "all": [ { "o": 0, "sX": 0.3, "sY": 0.3 }, { "d": 600, "e": "back.out", "o": 1, "sX": 1, "sY": 1, "sd": 40 } ] }
					},
					"strong": {
						"content": { "all": [ { "o": 0, "sX": 0.1, "sY": 0.1 }, { "d": 400, "e": "back.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"lines": { "ex": true, "all": [ { "o": 0, "sX": 0.1, "sY": 0.1 }, { "d": 400, "e": "back.out", "o": 1, "sX": 1, "sY": 1, "sd": 20 } ] }
					}
				}	
			},
			"words":{
				"fade":{
					"multi":true,
					"demo":"words",
					"easy":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":40,"o":1}]}},
					"middle":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":100,"o":1}]}},
					"strong":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":150,"o":1}]}},
					"perspective": {"content": { "pers": 900, "all": [ { "o": 0, "rX": "-80deg", "z": -120 }, { "d": 850, "e": "power4.out", "o": 1, "rX": 0, "z": 0 } ] },"words": { "ex": true, "all": [ { "rY": "60deg", "o": 0 }, { "d": 690, "e": "power2.inOut", "rY": 0, "o": 1, "sd": 110 } ] }}
				},
				"flyinrandom":{
					"multi":true,
					"demo":"words",
					"up":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"all":[{"y":["#a","{10,200}","#a","#a","#a"],"xRe":1,"o":"0","rZ":"{0,50}"},{"d":700, "dir":"start", "e":"power4.inOut","sd":105,"y":0,"o":1,"rZ":"0deg"}]}},
					"down":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"all":[{"y":["#a","{-200,-10}","#a","#a","#a"],"xRe":1,"o":"0","rZ":"{-50,0}"},{"d":700, "dir":"start", "e":"power4.inOut","sd":105,"y":0,"o":1,"rZ":"0deg"}]}},
					"left":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"all":[{"x":["#a","{50,200}","#a","#a","#a"],"xRe":1,"o":"0","rZ":"{0,90}"},{"d":1000, "dir":"start", "e":"power4.inOut","sd":75,"x":0,"o":1,"rZ":"0deg"}]}},
					"right":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"all":[{"x":["#a","{-200,50}","#a","#a","#a"],"xRe":1,"o":"0","rZ":"{-90,0}"},{"d":1000, "dir":"end", "e":"power4.inOut","sd":75,"x":0,"o":1,"rZ":"0deg"}]}},					
					"cross":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"all":[{"x":["#a","{-200,200}","#a","#a","#a"],"y":["#a","{-200,200}","#a","#a","#a"],"xRe":1,"yRe":1,"o":"0","rZ":"{-9,90}"},{"d":1000, "dir":"start", "e":"power4.inOut","sd":75,"x":0,"y":0,"o":1,"rZ":"0deg"}]}						
					}
				},
				"flip":{
					"multi":true,
					"demo":"words",					
					"up":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"orig":{"z":"-50%","y":"0%"},"all":[{"y":"-100%","yRe":0,"o":"0","rX":"-90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"y":0,"o":1,"rX":0}]}						},
					"down":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"orig":{"z":"-50%","y":"100%"},"all":[{"y":"100%","yRe":0,"o":"0","rX":"90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"y":0,"o":1,"rX":0}]}						}				
				},
				"slide":{
					"multi":true,
					"demo":"words",										
					"left":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"orig":{"z":"-30%","x":"100%"},"all":[{"x":"105%","xRe":1,"o":"0","rY":"-90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"x":0,"o":1,"rY":0}]}						},
					"right":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"orig":{"z":"-30%","x":"0%"},"all":[{"x":"-105%","xRe":1,"o":"0","rY":"90deg"},{"d":800, "dir":"end", "e":"power4.inOut","sd":75,"x":0,"o":1,"rY":0}]}						},
					"pressleft":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"orig":{"z":"-30%","x":"100%"},"all":[{"x":"105%","xRe":1,"o":"0","rY":"-90deg"},{"d":800, "dir":"start", "e":"back.out","sd":75,"x":0,"o":1,"rY":0}]}						},
					"pressright":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"words":{"ex":true,"orig":{"z":"-30%","x":"0%"},"all":[{"x":"-105%","xRe":1,"o":"0","rY":"90deg"},{"d":800, "dir":"end", "e":"back.out","sd":75,"x":0,"o":1,"rY":0}]}						}
				},
				"cycle":{
					"multi":true,
					"demo":"words",
					"vertical":{"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":"1","rX":"70deg","rZ":"20deg","rY":"20deg"},{"t":20,"d":1250,"f":300,"e":"power2.out","a":"Anim To","pE":"d","sX":1,"sY":1,"o":1,"off":0,"dC":1700,"rX":"0deg","rZ":"0deg","rY":"0deg"}]},"words":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"y":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rX":"[-70,70]","dir":"start"},{"t":20,"d":970,"e":"power4.inOut","x":["#a",0,"#a","#a","#a"],"o":1,"y":0, "rX":0,"dir":"center","sd":31}]}},
					"horizontal":{"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":"1","rX":"20deg","rZ":"20deg","rY":"70deg"},{"t":20,"d":1250,"f":300,"e":"power2.out","a":"Anim To","pE":"d","sX":1,"sY":1,"o":1,"off":0,"dC":1700,"rX":"0deg","rZ":"0deg","rY":"0deg"}]},"words":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"x":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rY":"[-70,70]","dir":"start"},{"t":20,"d":970,"e":"power4.inOut","y":["#a",0,"#a","#a","#a"],"o":1,"x":0, "rY":0,"dir":"center","sd":31}]}}					
				},
				"popin": {
					"multi": true,
					"demo": "words",
					"easy": {
						"content": { "all": [ { "o": 0, "sX": 0.7, "sY": 0.7 }, { "d": 850, "e": "elastic.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"words": { "ex": true, "all": [ { "o": 0, "sX": 0.7, "sY": 0.7 }, { "d": 1000, "e": "elastic.out", "o": 1, "sX": 1, "sY": 1, "sd": 65 } ] }
					},
					"medium": {
						"content": { "all": [ { "o": 0, "sX": 0.3, "sY": 0.3 }, { "d": 480, "e": "back.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"words": { "ex": true, "all": [ { "o": 0, "sX": 0.3, "sY": 0.3 }, { "d": 600, "e": "back.out", "o": 1, "sX": 1, "sY": 1, "sd": 40 } ] }
					},
					"strong": {
						"content": { "all": [ { "o": 0, "sX": 0.1, "sY": 0.1 }, { "d": 400, "e": "back.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"words": { "ex": true, "all": [ { "o": 0, "sX": 0.1, "sY": 0.1 }, { "d": 400, "e": "back.out", "o": 1, "sX": 1, "sY": 1, "sd": 20 } ] }
					}
				},
				"splitreveal": {
					"multi": true,
					"demo": "words",
					"up": {
						"content": { "all": [ { "o": 0 }, { "d": 700, "e": "power2.out", "o": 1 } ] },
						"mask": { "all": [ { "oflow": "hidden" , "y": "100%", "d": 0 }, { "oflow":"hidden", "y": 0, "d": 650, "e": "power2.out" } ] },
						"words": {"ex": true,"all": [ { "y": "70%", "o": 0 , "rY":"220deg"}, { "d": 1200, "e": "back.out", "y": 0, "o": 1, "sd": 80,"rY":0 } ]},
						"chars": {"ex": true,"all": [ { "y": "120%", "o": 0 }, { "d": 520, "e": "power4.out", "y": 0, "o": 1, "sd": 25 } ]}
					},
					"down": {
						"content": { "all": [ { "o": 0 }, { "d": 700, "e": "power2.out", "o": 1 } ] },
						"mask": { "all": [ { "oflow": "hidden" , "y": "-100%", "d": 0 }, { "oflow":"hidden", "y": 0, "d": 650, "e": "power2.out" } ] },
						"words": {"ex": true,"all": [ { "y": "-70%", "o": 0 , "rY":"220deg"}, { "d": 1200, "e": "back.out", "y": 0, "o": 1, "sd": 80,"rY":0 } ]},
						"chars": {"ex": true,"all": [ { "y": "-120%", "o": 0 }, { "d": 520, "e": "power4.out", "y": 0, "o": 1, "sd": 25 } ]}
					}
				}				
			},
			"letter":{
				"fade":{
					"multi":true,
					"demo":"chars",
					"easy":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":20,"o":1}]}},
					"middle":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":70,"o":1}]}},
					"strong":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"all":[{"o":"0"},{"d":700, "dir":"start", "e":"power4.inOut","sd":120,"o":1}]}}					
				},
				"wave":{"multi":true,"demo":"chars","up":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"all":[{"y":["#a","[-30%,30%]","#a","#a","#a"],"o":"0"},{"d":850,"dir":"start","e":"power3.out","sd":38,"y":0,"o":1}]}},"down":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"all":[{"y":["#a","[-30%,30%]","#a","#a","#a"],"o":"0"},{"d":850,"dir":"end","e":"power3.out","sd":38,"y":0,"o":1}]}},"left":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"all":[{"x":["#a","[-30%,30%]","#a","#a","#a"],"o":"0"},{"d":850,"dir":"start","e":"power3.out","sd":38,"x":0,"o":1}]}},"right":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"all":[{"x":["#a","[-30%,30%]","#a","#a","#a"],"o":"0"},{"d":850,"dir":"end","e":"power3.out","sd":38,"x":0,"o":1}]}}},
				"unfold":{"multi":true,"demo":"chars","up":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"orig":{"y":"100%","z":"-50%"},"all":[{"o":"0","rX":"-90deg"},{"d":750,"dir":"start","e":"power3.out","sd":45,"o":1,"rX":0}]}},"down":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"orig":{"y":"0%","z":"-50%"},"all":[{"o":"0","rX":"90deg"},{"d":750,"dir":"start","e":"power3.out","sd":45,"o":1,"rX":0}]}},"left":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"orig":{"x":"100%","z":"-50%"},"all":[{"o":"0","rY":"90deg"},{"d":750,"dir":"start","e":"power3.out","sd":45,"o":1,"rY":0}]}},"right":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"orig":{"x":"0%","z":"-50%"},"all":[{"o":"0","rY":"-90deg"},{"d":750,"dir":"start","e":"power3.out","sd":45,"o":1,"rY":0}]}}},
				"zoomchars":{"multi":true,"demo":"chars","up":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"all":[{"o":"0","sX":"0","sY":"0","y":"40"},{"d":700,"dir":"start","e":"back.out","sd":35,"o":1,"sX":1,"sY":1,"y":0}]}},"down":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"all":[{"o":"0","sX":"0","sY":"0","y":"-40"},{"d":700,"dir":"start","e":"back.out","sd":35,"o":1,"sX":1,"sY":1,"y":0}]}},"left":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"all":[{"o":"0","sX":"0","sY":"0","x":"40"},{"d":700,"dir":"start","e":"back.out","sd":35,"o":1,"sX":1,"sY":1,"x":0}]}},"right":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},"chars":{"ex":true,"all":[{"o":"0","sX":"0","sY":"0","x":"-40"},{"d":700,"dir":"start","e":"back.out","sd":35,"o":1,"sX":1,"sY":1,"x":0}]}}},
				"flyin":{
					"multi":true,
					"demo":"chars",
					"up":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"all":[{"y":"-100%","xRe":1,"o":"0","rZ":"35deg"},{"d":700, "dir":"start", "e":"power4.inOut","sd":105,"y":0,"o":1,"rZ":"0deg"}]},"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}},
					"down":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"all":[{"y":"100%","xRe":1,"o":"0","rZ":"-35deg"},{"d":700, "dir":"start", "e":"power4.inOut","sd":105,"y":0,"o":1,"rZ":"0deg"}]},"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}},
					"left":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"all":[{"x":"-105%","xRe":1,"o":"0","rZ":"-90deg"},{"d":1000, "dir":"end", "e":"power4.inOut","sd":75,"x":0,"o":1,"rZ":"0deg"}]},"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}},
					"right":{"content":{"all":[{"o":1},{"d":500,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"all":[{"x":"105%","xRe":1,"o":"0","rZ":"90deg"},{"d":1000, "dir":"start", "e":"power4.inOut","sd":75,"x":0,"o":1,"rZ":"0deg"}]},"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}}
				},
				"flip":{
					"multi":true,
					"demo":"chars",					
					"up":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"orig":{"z":"-50%","y":"0%"},"all":[{"y":"-100%","yRe":0,"o":"0","rX":"-90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"y":0,"o":1,"rX":0}]}},
					"down":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"orig":{"z":"-50%","y":"100%"},"all":[{"y":"100%","yRe":0,"o":"0","rX":"90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"y":0,"o":1,"rX":0}]}},					
					"left":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"orig":{"z":"-30%","x":"100%"},"all":[{"x":"105%","xRe":1,"o":"0","rY":"-90deg"},{"d":800, "dir":"start", "e":"power4.inOut","sd":75,"x":0,"o":1,"rY":0}]}},
					"right":{"content":{"all":[{"o":1},{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]},	"chars":{"ex":true,"orig":{"z":"-30%","x":"0%"},"all":[{"x":"-105%","xRe":1,"o":"0","rY":"90deg"},{"d":800, "dir":"end", "e":"power4.inOut","sd":75,"x":0,"o":1,"rY":0}]}}
				},
				"cycle":{
					"multi":true,
					"demo":"chars",
					"vertical":  {"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":"1","rX":"70deg","rZ":"20deg","rY":"20deg"},{"t":20,"d":1250,"f":300,"e":"power2.out","a":"Anim To","pE":"d","sX":1,"sY":1,"o":1,"off":0,"dC":1700,"rX":"0deg","rZ":"0deg","rY":"0deg"}]},"chars":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"y":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rX":"[-70,70]","dir":"start"},{"t":20,"d":970,"e":"power4.inOut","x":["#a",0,"#a","#a","#a"],"o":1,"y":0, "rX":0,"dir":"center","sd":31}]}},
					"horizontal":{"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":"1","rX":"20deg","rZ":"20deg","rY":"70deg"},{"t":20,"d":1250,"f":300,"e":"power2.out","a":"Anim To","pE":"d","sX":1,"sY":1,"o":1,"off":0,"dC":1700,"rX":"0deg","rZ":"0deg","rY":"0deg"}]},"chars":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"x":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rY":"[-70,70]","dir":"start"},{"t":20,"d":970,"e":"power4.inOut","y":["#a",0,"#a","#a","#a"],"o":1,"x":0, "rY":0,"dir":"center","sd":31}]}}						
				},
				"popin": {
					"multi": true,
					"demo": "chars",
					"easy": {
						"content": { "all": [ { "o": 0, "sX": 0.7, "sY": 0.7 }, { "d": 850, "e": "elastic.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"chars": { "ex": true, "all": [ { "o": 0, "sX": 0.7, "sY": 0.7 }, { "d": 1000, "e": "elastic.out", "o": 1, "sX": 1, "sY": 1, "sd": 65 } ] }
					},
					"medium": {
						"content": { "all": [ { "o": 0, "sX": 0.3, "sY": 0.3 }, { "d": 480, "e": "back.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"chars": { "ex": true, "all": [ { "o": 0, "sX": 0.3, "sY": 0.3 }, { "d": 600, "e": "back.out", "o": 1, "sX": 1, "sY": 1, "sd": 40 } ] }
					},
					"strong": {
						"content": { "all": [ { "o": 0, "sX": 0.1, "sY": 0.1 }, { "d": 400, "e": "back.out", "o": 1, "sX": 1, "sY": 1 } ] },
						"chars": { "ex": true, "all": [ { "o": 0, "sX": 0.1, "sY": 0.1 }, { "d": 400, "e": "back.out", "o": 1, "sX": 1, "sY": 1, "sd": 20 } ] }
					}
				}										
			},"kinetic":{"headline":{"multi":true,"demo":"words","up":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1,"sX":"0.96","sY":"0.96"},{"d":1400,"e":"power2.out","o":1,"sX":1,"sY":1}]},"words":{"ex":true,"all":[{"o":1,"y":"120%"},{"d":900,"e":"power4.out","sd":120,"dir":"start","o":1,"y":0}]},"chars":{"ex":true,"all":[{"o":0,"gap":16},{"d":700,"e":"power2.out","sd":18,"dir":"center","o":1,"gap":0}]}},"down":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1,"sX":"0.96","sY":"0.96"},{"d":1400,"e":"power2.out","o":1,"sX":1,"sY":1}]},"words":{"ex":true,"all":[{"o":1,"y":"-120%"},{"d":900,"e":"power4.out","sd":120,"dir":"start","o":1,"y":0}]},"chars":{"ex":true,"all":[{"o":0,"gap":16},{"d":700,"e":"power2.out","sd":18,"dir":"center","o":1,"gap":0}]}},"left":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1,"sX":"0.96","sY":"0.96"},{"d":1400,"e":"power2.out","o":1,"sX":1,"sY":1}]},"words":{"ex":true,"all":[{"o":1,"x":"120%"},{"d":900,"e":"power4.out","sd":120,"dir":"start","o":1,"x":0}]},"chars":{"ex":true,"all":[{"o":0,"gap":16},{"d":700,"e":"power2.out","sd":18,"dir":"center","o":1,"gap":0}]}},"right":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1,"sX":"0.96","sY":"0.96"},{"d":1400,"e":"power2.out","o":1,"sX":1,"sY":1}]},"words":{"ex":true,"all":[{"o":1,"x":"-120%"},{"d":900,"e":"power4.out","sd":120,"dir":"start","o":1,"x":0}]},"chars":{"ex":true,"all":[{"o":0,"gap":16},{"d":700,"e":"power2.out","sd":18,"dir":"center","o":1,"gap":0}]}}},"cascade":{"multi":true,"demo":"words","up":{"content":{"all":[{"o":1},{"d":1300,"e":"power2.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"rX":"-88deg","y":"35%"},{"d":1000,"e":"power3.out","sd":150,"dir":"start","o":1,"rX":0,"y":0}]},"chars":{"ex":true,"all":[{"o":0,"y":"-50%"},{"d":520,"e":"power2.out","sd":26,"dir":"end","o":1,"y":0}]}},"down":{"content":{"all":[{"o":1},{"d":1300,"e":"power2.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"rX":"88deg","y":"-35%"},{"d":1000,"e":"power3.out","sd":150,"dir":"start","o":1,"rX":0,"y":0}]},"chars":{"ex":true,"all":[{"o":0,"y":"50%"},{"d":520,"e":"power2.out","sd":26,"dir":"end","o":1,"y":0}]}},"left":{"content":{"all":[{"o":1},{"d":1300,"e":"power2.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"rY":"88deg","x":"35%"},{"d":1000,"e":"power3.out","sd":150,"dir":"start","o":1,"rY":0,"x":0}]},"chars":{"ex":true,"all":[{"o":0,"x":"-50%"},{"d":520,"e":"power2.out","sd":26,"dir":"end","o":1,"x":0}]}},"right":{"content":{"all":[{"o":1},{"d":1300,"e":"power2.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"rY":"-88deg","x":"-35%"},{"d":1000,"e":"power3.out","sd":150,"dir":"start","o":1,"rY":0,"x":0}]},"chars":{"ex":true,"all":[{"o":0,"x":"50%"},{"d":520,"e":"power2.out","sd":26,"dir":"end","o":1,"x":0}]}}},"swipe":{"multi":true,"demo":"words","up":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1},{"d":900,"e":"power3.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"y":"180"},{"d":700,"e":"power4.out","sd":75,"dir":"start","o":1,"y":0}]},"chars":{"ex":true,"all":[{"o":0,"skX":"-22deg","gap":-9},{"d":380,"e":"power4.out","sd":16,"dir":"start","o":1,"skX":0,"gap":0}]}},"down":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1},{"d":900,"e":"power3.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"y":"-180"},{"d":700,"e":"power4.out","sd":75,"dir":"start","o":1,"y":0}]},"chars":{"ex":true,"all":[{"o":0,"skX":"22deg","gap":-9},{"d":380,"e":"power4.out","sd":16,"dir":"start","o":1,"skX":0,"gap":0}]}},"left":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1},{"d":900,"e":"power3.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"x":"180"},{"d":700,"e":"power4.out","sd":75,"dir":"start","o":1,"x":0}]},"chars":{"ex":true,"all":[{"o":0,"skX":"-22deg","gap":-9},{"d":380,"e":"power4.out","sd":16,"dir":"start","o":1,"skX":0,"gap":0}]}},"right":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1},{"d":900,"e":"power3.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"x":"-180"},{"d":700,"e":"power4.out","sd":75,"dir":"start","o":1,"x":0}]},"chars":{"ex":true,"all":[{"o":0,"skX":"22deg","gap":-9},{"d":380,"e":"power4.out","sd":16,"dir":"start","o":1,"skX":0,"gap":0}]}}},"lift":{"multi":true,"demo":"chars","up":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1,"y":"30"},{"d":1300,"e":"power2.out","o":1,"y":0}]},"words":{"ex":true,"all":[{"o":1,"y":"20%"},{"d":1100,"e":"power3.out","sd":100,"dir":"start","o":1,"y":0}]},"chars":{"ex":true,"all":[{"o":1,"y":"110%"},{"d":800,"e":"power4.out","sd":34,"dir":"start","o":1,"y":0}]}},"down":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1,"y":"-30"},{"d":1300,"e":"power2.out","o":1,"y":0}]},"words":{"ex":true,"all":[{"o":1,"y":"-20%"},{"d":1100,"e":"power3.out","sd":100,"dir":"start","o":1,"y":0}]},"chars":{"ex":true,"all":[{"o":1,"y":"-110%"},{"d":800,"e":"power4.out","sd":34,"dir":"start","o":1,"y":0}]}},"left":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1,"x":"30"},{"d":1300,"e":"power2.out","o":1,"x":0}]},"words":{"ex":true,"all":[{"o":1,"x":"20%"},{"d":1100,"e":"power3.out","sd":100,"dir":"start","o":1,"x":0}]},"chars":{"ex":true,"all":[{"o":1,"x":"110%"},{"d":800,"e":"power4.out","sd":34,"dir":"start","o":1,"x":0}]}},"right":{"mask":{"all":[{"oflow":"hidden"},{"oflow":"hidden"}]},"content":{"all":[{"o":1,"x":"-30"},{"d":1300,"e":"power2.out","o":1,"x":0}]},"words":{"ex":true,"all":[{"o":1,"x":"-20%"},{"d":1100,"e":"power3.out","sd":100,"dir":"start","o":1,"x":0}]},"chars":{"ex":true,"all":[{"o":1,"x":"-110%"},{"d":800,"e":"power4.out","sd":34,"dir":"start","o":1,"x":0}]}}},"spotlight":{"demo":"chars","content":{"all":[{"o":1,"sX":"1.04","sY":"1.04"},{"d":1600,"e":"power2.out","o":1,"sX":1,"sY":1}]},"chars":{"ex":true,"all":[{"o":0,"gap":34},{"d":1100,"e":"power3.out","sd":14,"dir":"center","o":1,"gap":0}]}},"assemble":{"demo":"chars","content":{"all":[{"o":1},{"d":1200,"e":"power2.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"rZ":"[-10,10]","sX":"0.88","sY":"0.88"},{"d":1100,"e":"back.out","sd":70,"dir":"center","o":1,"rZ":0,"sX":1,"sY":1}]},"chars":{"ex":true,"all":[{"o":0,"x":"[-90,90]","y":"[-70,70]","rZ":"[-40,40]"},{"d":700,"e":"power3.out","sd":20,"dir":"random","o":1,"x":0,"y":0,"rZ":0}]}},"bloom":{"demo":"chars","content":{"all":[{"o":1},{"d":1400,"e":"power2.out","o":1}]},"words":{"ex":true,"all":[{"o":1,"sX":"0.9","sY":"1.22"},{"d":1300,"e":"elastic.out","sd":90,"dir":"center","o":1,"sX":1,"sY":1}]},"chars":{"ex":true,"all":[{"o":0,"sX":"0.3","sY":"0.3","gap":22},{"d":900,"e":"back.out","sd":24,"dir":"edges","o":1,"sX":1,"sY":1,"gap":0}]}}},								
			"slide":{
				"shortdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"y":"50"},{"d":1000,"e":"power3.inOut","o":1,"y":0}]}},
					"down":{"content":{"all":[{"o":0,"y":"-50"},{"d":1000,"e":"power3.inOut","o":1,"y":0}]}},
					"left":{"content":{"all":[{"o":0,"x":"50"},{"d":1000,"e":"power3.inOut","o":1,"x":0}]}},
					"right":{"content":{"all":[{"o":0,"x":"-50"},{"d":1000,"e":"power3.inOut","o":1,"x":0}]}}
				},
				"mediumdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"yRe":2,"y":"25%"},{"d":1000,"e":"power3.inOut","o":1,"y":0}]}},
					"down":{"content":{"all":[{"o":0,"yRe":2,"y":"-25%"},{"d":1000,"e":"power3.inOut","o":1,"y":0}]}},
					"left":{"content":{"all":[{"o":0,"xRe":2,"x":"25%"},{"d":1000,"e":"power3.inOut","o":1,"x":0}]}},
					"right":{"content":{"all":[{"o":0,"xRe":2,"x":"-25%"},{"d":1000,"e":"power3.inOut","o":1,"x":0}]}}
				},
				"longdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"yRe":1,"y":"bottom"},{"d":1000,"e":"power3.inOut","o":1,"y":0}]}},
					"down":{"content":{"all":[{"o":0,"yRe":1,"y":"top"},{"d":1000,"e":"power3.inOut","o":1,"y":0}]}},
					"left":{"content":{"all":[{"o":0,"xRe":1,"x":"right"},{"d":1000,"e":"power3.inOut","o":1,"x":0}]}},
					"right":{"content":{"all":[{"o":0,"xRe":1,"x":"left"},{"d":1000,"e":"power3.inOut","o":1,"x":0}]}}
				},
				"diagonal":{
					"multi":true,
					"rightup":{"content":{"all":[{"o":0,"y":"100%", "x":"-100%"},{"d":1000,"e":"power3.inOut","o":1,"y":0, "x":0}]}},
					"rightdown":{"content":{"all":[{"o":0,"y":"-100%", "x":"-100%"},{"d":1000,"e":"power3.inOut","o":1,"y":0, "x":0}]}},
					"leftup":{"content":{"all":[{"o":0,"y":"100%", "x":"100%"},{"d":1000,"e":"power3.inOut","o":1,"y":0, "x":0}]}},
					"leftdown":{"content":{"all":[{"o":0,"y":"-100%", "x":"100%"},{"d":1000,"e":"power3.inOut","o":1,"y":0, "x":0}]}}
				}					
			},	
			"skew":{
				"shortdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"y":"85","skY":-10},{"d":1000,"e":"power3.inOut","skY":0,"o":1,"y":0}]}},
					"down":{"content":{"all":[{"o":0,"y":"-85","skY":10},{"d":1000,"e":"power3.inOut","skY":0,"o":1,"y":0}]}},
					"left":{"content":{"all":[{"o":0,"x":"75","skX":-20},{"d":1000,"e":"power3.inOut","skX":0,"o":1,"x":0}]}},
					"right":{"content":{"all":[{"o":0,"x":"-75","skX":20},{"d":1000,"e":"power3.inOut","skX":0,"o":1,"x":0}]}}
				},
				"mediumdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"yRe":2,"y":"25%","skY":-20},{"d":1000,"e":"power3.inOut","skY":0,"o":1,"y":0}]}},
					"down":{"content":{"all":[{"o":0,"yRe":2,"y":"-25%","skY":20},{"d":1000,"e":"power3.inOut","skY":0,"o":1,"y":0}]}},
					"left":{"content":{"all":[{"o":0,"xRe":2,"x":"25%","skX":-25},{"d":1000,"e":"power3.inOut","skX":0,"o":1,"x":0}]}},
					"right":{"content":{"all":[{"o":0,"xRe":2,"x":"-25%","skX":25},{"d":1000,"e":"power3.inOut","skX":0,"o":1,"x":0}]}}
				},
				"longdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"yRe":1,"y":"bottom","skY":-30},{"d":1000,"e":"power3.inOut","skY":0,"o":1,"y":0}]}},
					"down":{"content":{"all":[{"o":0,"yRe":1,"y":"top","skY":30},{"d":1000,"e":"power3.inOut","skY":0,"o":1,"y":0}]}},
					"left":{"content":{"all":[{"o":0,"xRe":1,"x":"right","skX":30},{"d":1000,"e":"power3.inOut","skX":0,"o":1,"x":0}]}},
					"right":{"content":{"all":[{"o":0,"xRe":1,"x":"left","skX":-30},{"d":1000,"e":"power3.inOut","skX":0,"o":1,"x":0}]}}
				}									
			},			
			"rotate":{				
				"smash":{
					"multi":true,
					"up":{"content":{"all":[{"rZ":"20deg", "o":0, "y":"200%", "sY":2, "sX":2},{"d":1000,"e":"power3.inOut","o":1,"y":0,"rZ":0,"sX":1,"sY":1}]}},					
					"down":{"content":{"all":[{"rZ":"-20deg",  "o":0,  "y":"-200%", "sY":2, "sX":2},{"d":1000,"e":"power3.inOut","o":1,"y":0,"rZ":0,"rY":0,"sX":1,"sY":1}]}},					
					"left":{"content":{"all":[{"rZ":"20deg", "o":0, "x":"200%", "sY":2, "sX":2},{"d":1000,"e":"power3.inOut","o":1,"x":0,"rZ":0,"rY":0,"sX":1,"sY":1}]}},
					"right":{"content":{"all":[{"rZ":"-20deg", "o":0, "x":"-200%", "sY":2, "sX":2},{"d":1000,"e":"power3.inOut","o":1,"x":0,"rZ":0,"rY":0,"sX":1,"sY":1}]}},
					"center":{"content":{"all":[{"o":1,"rY":"-20deg", "rX":"-20deg", "y":"200%", "sY":2, "sX":2},{"d":1000,"e":"power3.out","o":1,"y":0,"rZ":0,"rY":0,"rX:":"0", "sX":1,"sY":1}]}}
				},
				"flip":{
					"multi":true,
					"up":{"content":{"all":[{"rX":"-360deg", "o":0},{"d":500, "e":"back.out", "rX":0, "o":1}]}},
					"down":{"content":{"all":[{"rX":"360deg", "o":0},{"d":500, "e":"back.out", "rX":0, "o":1}]}},
					"left":{"content":{"all":[{"rY":"360deg", "o":0},{"d":500, "e":"back.out", "rY":0, "o":1}]}},
					"right":{"content":{"all":[{"rY":"-360deg", "o":0},{"d":500, "e":"back.out", "rY":0, "o":1}]}}					
				},
				"turn":{
					"multi":true,
					"up":{"content":{"orig":{"z":"-50%"}, "all":[{"rX":"70deg", "o":0},{"d":1250,"e":"power2.inOut","o":1,"rX":0}]}},
					"down":{"content":{"orig":{"z":"-50%"}, "all":[{"rX":"-70deg", "o":0},{"d":1250,"e":"power2.inOut","o":1,"rX":0}]}},
					"left":{"content":{"orig":{"z":"-50%"}, "all":[{"rY":"70deg", "o":0},{"d":1250,"e":"power2.inOut","o":1,"rY":0}]}},
					"right":{"content":{"orig":{"z":"-50%"}, "all":[{"rY":"-70deg", "o":0},{"d":1250,"e":"power2.inOut","o":1,"rY":0}]}}
				}
			},	
			"zoom":{
				"zoomin":{
					"multi":true,
					"normal":{"content":{"all":[{"o":0,"sX":"0.8","sY":"0.8"},{"d":1000,"e":"power3.inOut","o":1,"sX":1,"sY":1}]}},
					"squashh":{"content":{"all":[{"o":0,"sX":"0.3","sY":"0.95"},{"d":1000,"e":"power3.inOut","o":1,"sX":1,"sY":1}]}},
					"squashv":{"content":{"all":[{"o":0,"sX":"0.95","sY":"0.3"},{"d":1000,"e":"power3.inOut","o":1,"sX":1,"sY":1}]}}
				},
				"zoomout":{
					"multi":true,
					"normal":{"content":{"all":[{"o":0,"sX":"1.25","sY":"1.25"},{"d":1000,"e":"power3.inOut","o":1,"sX":1,"sY":1}]}},
					"squashh":{"content":{"all":[{"o":0,"sX":"1.8","sY":"1.05"},{"d":1000,"e":"power3.inOut","o":1,"sX":1,"sY":1}]}},
					"squashv":{"content":{"all":[{"o":0,"sX":"1.05","sY":"1.8"},{"d":1000,"e":"power3.inOut","o":1,"sX":1,"sY":1}]}}
				}
			},
			"masktrans":{				
				"zoom":{
					"multi":true,
					"crossout":{						
						"content": {"all":[{"o":0,"sX":2,"sY":2},{"d":1000,"e":"power2.out","o":1,"sX":1,"sY":1}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}						
					},
					"crossin":{						
						"content": {"all":[{"o":0,"sX":0.2,"sY":0.2},{"d":1000,"e":"back.out","o":1,"sX":1,"sY":1}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}						
					}					
				},
				"reveal":{
					"multi":true,
					"up":{						
						"content": {"all":[{"o":0,"y":"100%"},{"d":1200,"e":"power3.inOut","o":1,"y":0}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},
					"down":{						
						"content": {"all":[{"o":0,"y":"-100%"},{"d":1200,"e":"power3.inOut","o":1,"y":0}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},
					"left":{						
						"content": {"all":[{"o":0,"x":"100%"},{"d":1200,"e":"power3.inOut","o":1,"x":0}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},
					"right":{						
						"content": {"all":[{"o":0,"x":"-100%"},{"d":1200,"e":"power3.inOut","o":1,"x":0}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					}
						
				},
				"glide":{
					"multi":true,
					"up":{
						"content": {"all":[{"o":1,"y":"175%"},{"d":1200,"e":"power3.out","o":1,"y":0}]},
						"mask": {"all":[{"oflow":"hidden", "y":"-100%"},{"d":1200,"e":"power3.out","oflow":"hidden", "y":0}]}
					},
					"down":{
						"content": {"all":[{"o":1,"y":"-175%"},{"d":1200,"e":"power3.out","o":1,"y":0}]},
						"mask": {"all":[{"oflow":"hidden", "y":"100%"},{"d":1200,"e":"power3.out","oflow":"hidden", "y":0}]}
					},								
					"left":{
						"content": {"all":[{"o":1,"x":"175%"},{"d":1200,"e":"power3.out","o":1,"x":0}]},
						"mask": {"all":[{"oflow":"hidden", "x":"-100%"},{"d":1200,"e":"power3.out","oflow":"hidden", "x":0}]}
					},
					"right":{
						"content": {"all":[{"o":1,"x":"-175%"},{"d":1200,"e":"power3.out","o":1,"x":0}]},
						"mask": {"all":[{"oflow":"hidden", "x":"100%"},{"d":1200,"e":"power3.out","oflow":"hidden", "x":0}]}
					}
				},
				"uncover":{
					"multi":true,
					"up":{
						"content": {"all":[{"o":1,"y":"100%"},{"d":1200,"e":"power3.out","o":1,"y":0}]},
						"mask": {"all":[{"oflow":"hidden", "y":"-100%"},{"d":1200,"e":"power3.out","oflow":"hidden", "y":0}]}
					},
					"down":{
						"content": {"all":[{"o":1,"y":"-100%"},{"d":1200,"e":"power3.out","o":1,"y":0}]},
						"mask": {"all":[{"oflow":"hidden", "y":"100%"},{"d":1200,"e":"power3.out","oflow":"hidden", "y":0}]}
					},								
					"left":{
						"content": {"all":[{"o":1,"x":"100%"},{"d":1200,"e":"power3.out","o":1,"x":0}]},
						"mask": {"all":[{"oflow":"hidden", "x":"-100%"},{"d":1200,"e":"power3.out","oflow":"hidden", "x":0}]}
					},
					"right":{
						"content": {"all":[{"o":1,"x":"-100%"},{"d":1200,"e":"power3.out","o":1,"x":0}]},
						"mask": {"all":[{"oflow":"hidden", "x":"100%"},{"d":1200,"e":"power3.out","oflow":"hidden", "x":0}]}
					}
				},
				"rotate":{
					"multi":true,
					"rightdown":{
						"content":{"orig":{"x":"0%", "y":"0%"},"all":[{"rZ":"-70deg", "x":"-50%", "o":0},{"d":1000,"e":"power3.out","x":"0%","o":1,"rZ":0}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},					
					"rightup":{
						"content":{"orig":{"x":"0%", "y":"100%"},"all":[{"rZ":"70deg", "x":"-50%", "o":0},{"d":1000,"e":"power3.out","x":"0%","o":1,"rZ":0}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},					
					"leftup":{
						"content":{"orig":{"x":"100%", "y":"0%"},"all":[{"rZ":"-70deg", "x":"50%","o":0},{"d":1000,"e":"power3.out","x":"0%","o":1,"rZ":0}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},
					"leftdown":{
						"content":{"orig":{"x":"100%", "y":"100%"},"all":[{"rZ":"70deg", "x":"50%","o":0},{"d":1000,"e":"power3.out","x":"0%","o":1,"rZ":0}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					}				},
				"blck" : {
					"multi":true,
					"left" : {
						"content": {"all":[{"o":0},{"d":1200,"e":"power4.out","o":1,"fx":"cleft","fxc":"#ffffff","fxe":"power4.inOut","fxs":"1200"}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},
					"right" : {
						"content": {"all":[{"o":0},{"d":1200,"e":"power4.out","o":1,"fx":"cright","fxc":"#ffffff","fxe":"power4.inOut","fxs":"1200"}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},
					"up" : {
						"content": {"all":[{"o":0},{"d":1200,"e":"power4.out","o":1,"fx":"ctop","fxc":"#ffffff","fxe":"power4.inOut","fxs":"1200"}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					},
					"down" : {
						"content": {"all":[{"o":0},{"d":1200,"e":"power4.out","o":1,"fx":"cbottom","fxc":"#ffffff","fxe":"power4.inOut","fxs":"1200"}]},
						"mask": {"all":[{"oflow":"hidden"},{"oflow":"hidden"}]}
					}
				}				
			},
			"oneshot":{"hop":{"multi":true,"easy":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.91,"sX":1.07},{"d":300,"e":"power2.out","y":-60,"sY":1.1,"sX":0.94},{"d":255,"e":"power2.in"},{"d":100,"e":"power2.out","y":6,"sY":0.86,"sX":1.12},{"d":260,"e":"elastic.out"}]},"middle":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.91,"sX":1.07},{"d":380,"e":"power2.out","y":-110,"sY":1.1,"sX":0.94},{"d":323,"e":"power2.in"},{"d":100,"e":"power2.out","y":6,"sY":0.86,"sX":1.12},{"d":260,"e":"elastic.out"}]},"strong":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.91,"sX":1.07},{"d":460,"e":"power2.out","y":-180,"sY":1.1,"sX":0.94},{"d":391,"e":"power2.in"},{"d":100,"e":"power2.out","y":6,"sY":0.86,"sX":1.12},{"d":260,"e":"elastic.out"}]}},"flip":{"multi":true,"forward":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.91,"sX":1.07},{"d":420,"e":"power2.out","y":-145,"rX":180},{"d":380,"e":"power2.in","rX":360},{"d":100,"e":"power2.out","y":6,"sY":0.86,"sX":1.12,"rX":360},{"d":280,"e":"elastic.out","rX":360}]},"backward":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.91,"sX":1.07},{"d":420,"e":"power2.out","y":-145,"rX":-180},{"d":380,"e":"power2.in","rX":-360},{"d":100,"e":"power2.out","y":6,"sY":0.86,"sX":1.12,"rX":-360},{"d":280,"e":"elastic.out","rX":-360}]},"barrel":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.91,"sX":1.07},{"d":420,"e":"power2.out","y":-145,"rY":180},{"d":380,"e":"power2.in","rY":360},{"d":100,"e":"power2.out","y":6,"sY":0.86,"sX":1.12,"rY":360},{"d":280,"e":"elastic.out","rY":360}]},"spin":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.91,"sX":1.07},{"d":420,"e":"power2.out","y":-145,"rZ":180},{"d":380,"e":"power2.in","rZ":360},{"d":100,"e":"power2.out","y":6,"sY":0.86,"sX":1.12,"rZ":360},{"d":280,"e":"elastic.out","rZ":360}]}},"pop":{"multi":true,"easy":{"additive":true,"steps":[{"d":160,"e":"power2.out","sX":1.07,"sY":1.07,"rZ":1.5},{"d":420,"e":"elastic.out"}]},"middle":{"additive":true,"steps":[{"d":160,"e":"power2.out","sX":1.16,"sY":1.16,"rZ":2.5},{"d":420,"e":"elastic.out"}]},"strong":{"additive":true,"steps":[{"d":160,"e":"power2.out","sX":1.3,"sY":1.3,"rZ":4},{"d":420,"e":"elastic.out"}]}},"shake":{"multi":true,"horizontal":{"additive":true,"steps":[{"d":80,"e":"sine.out","x":20},{"d":95,"e":"sine.inOut","x":-16},{"d":95,"e":"sine.inOut","x":12},{"d":95,"e":"sine.inOut","x":-7},{"d":95,"e":"sine.inOut","x":3},{"d":95,"e":"sine.out"}]},"vertical":{"additive":true,"steps":[{"d":80,"e":"sine.out","y":17},{"d":95,"e":"sine.inOut","y":-14},{"d":95,"e":"sine.inOut","y":10},{"d":95,"e":"sine.inOut","y":-6},{"d":95,"e":"sine.inOut","y":3},{"d":95,"e":"sine.out"}]},"rotate":{"additive":true,"steps":[{"d":80,"e":"sine.out","rZ":7},{"d":95,"e":"sine.inOut","rZ":-6},{"d":95,"e":"sine.inOut","rZ":4},{"d":95,"e":"sine.inOut","rZ":-2.5},{"d":95,"e":"sine.inOut","rZ":1},{"d":95,"e":"sine.out"}]}},"dash":{"multi":true,"left":{"additive":true,"steps":[{"d":110,"e":"power2.out","x":20,"y":0},{"d":230,"e":"power3.out","x":-150,"y":0,"skX":12},{"d":300,"e":"power2.inOut"}]},"right":{"additive":true,"steps":[{"d":110,"e":"power2.out","x":-20,"y":0},{"d":230,"e":"power3.out","x":150,"y":0,"skX":-12},{"d":300,"e":"power2.inOut"}]},"up":{"additive":true,"steps":[{"d":110,"e":"power2.out","x":0,"y":20},{"d":230,"e":"power3.out","x":0,"y":-150,"sY":1.12,"sX":0.93},{"d":300,"e":"power2.inOut"}]},"down":{"additive":true,"steps":[{"d":110,"e":"power2.out","x":0,"y":-20},{"d":230,"e":"power3.out","x":0,"y":150,"sY":1.12,"sX":0.93},{"d":300,"e":"power2.inOut"}]}},"nod":{"additive":true,"steps":[{"d":90,"e":"power2.out","rX":-7},{"d":230,"e":"power2.inOut","rX":26,"y":6},{"d":200,"e":"power2.inOut","rX":-5},{"d":170,"e":"power2.inOut","rX":14,"y":3},{"d":300,"e":"power2.out"}]},"tada":{"additive":true,"steps":[{"d":130,"e":"power2.out","sX":0.93,"sY":0.93,"rZ":-4},{"d":110,"e":"power2.out","sX":1.12,"sY":1.12,"rZ":5},{"d":110,"e":"sine.inOut","sX":1.12,"sY":1.12,"rZ":-5},{"d":110,"e":"sine.inOut","sX":1.12,"sY":1.12,"rZ":4},{"d":110,"e":"sine.inOut","sX":1.12,"sY":1.12,"rZ":-3},{"d":280,"e":"power2.out"}]},"jello":{"additive":true,"steps":[{"d":120,"e":"power2.out","skX":15,"skY":-6},{"d":130,"e":"sine.inOut","skX":-11,"skY":5},{"d":130,"e":"sine.inOut","skX":7,"skY":-3},{"d":130,"e":"sine.inOut","skX":-4,"skY":2},{"d":240,"e":"power2.out"}]},"heartbeat":{"additive":true,"steps":[{"d":120,"e":"power2.out","sX":1.14,"sY":1.18},{"d":150,"e":"power2.in"},{"d":100,"e":"power2.out","sX":1.22,"sY":1.27},{"d":320,"e":"power2.in"}]},"rubber":{"additive":true,"steps":[{"d":180,"e":"power2.out","sX":1.28,"sY":0.76},{"d":160,"e":"power2.inOut","sX":0.82,"sY":1.22},{"d":150,"e":"power2.inOut","sX":1.13,"sY":0.9},{"d":300,"e":"elastic.out"}]},"levitate":{"additive":true,"steps":[{"d":560,"e":"power2.inOut","y":-30,"sX":1.04,"sY":1.04},{"d":340,"e":"sine.inOut","y":-38,"sX":1.04,"sY":1.04,"rZ":1.6},{"d":620,"e":"power2.inOut"}]},"stomp":{"additive":true,"steps":[{"d":170,"e":"power2.out","y":-46,"sY":1.08,"sX":0.96},{"d":120,"e":"power3.in","y":15,"sY":0.8,"sX":1.16},{"d":360,"e":"elastic.out"}]},"swoosh":{"multi":true,"left":{"additive":true,"steps":[{"d":110,"e":"power2.out","x":24,"y":0},{"d":400,"e":"power2.out","x":-200,"y":-70,"arc":0.85},{"d":460,"e":"power2.inOut","arc":0.85}]},"right":{"additive":true,"steps":[{"d":110,"e":"power2.out","x":-24,"y":0},{"d":400,"e":"power2.out","x":200,"y":-70,"arc":-0.85},{"d":460,"e":"power2.inOut","arc":-0.85}]},"up":{"additive":true,"steps":[{"d":110,"e":"power2.out","x":0,"y":24},{"d":400,"e":"power2.out","x":70,"y":-200,"arc":0.85},{"d":460,"e":"power2.inOut","arc":0.85}]},"down":{"additive":true,"steps":[{"d":110,"e":"power2.out","x":0,"y":-24},{"d":400,"e":"power2.out","x":70,"y":200,"arc":-0.85},{"d":460,"e":"power2.inOut","arc":-0.85}]}},"orbit":{"multi":true,"left":{"additive":true,"steps":[{"d":260,"e":"power1.in","x":-75,"y":-75,"arc":0.92},{"d":260,"e":"none","x":-150,"y":0,"arc":0.92},{"d":260,"e":"none","x":-75,"y":75,"arc":0.92},{"d":260,"e":"power1.out","x":0,"y":0,"arc":0.92}]},"right":{"additive":true,"steps":[{"d":260,"e":"power1.in","x":75,"y":-75,"arc":-0.92},{"d":260,"e":"none","x":150,"y":0,"arc":-0.92},{"d":260,"e":"none","x":75,"y":75,"arc":-0.92},{"d":260,"e":"power1.out","x":0,"y":0,"arc":-0.92}]},"up":{"additive":true,"steps":[{"d":260,"e":"power1.in","x":75,"y":-75,"arc":0.92},{"d":260,"e":"none","x":0,"y":-150,"arc":0.92},{"d":260,"e":"none","x":-75,"y":-75,"arc":0.92},{"d":260,"e":"power1.out","x":0,"y":0,"arc":0.92}]},"down":{"additive":true,"steps":[{"d":260,"e":"power1.in","x":75,"y":75,"arc":-0.92},{"d":260,"e":"none","x":0,"y":150,"arc":-0.92},{"d":260,"e":"none","x":-75,"y":75,"arc":-0.92},{"d":260,"e":"power1.out","x":0,"y":0,"arc":-0.92}]}},"figure8":{"additive":true,"steps":[{"d":220,"e":"power1.in","x":60,"y":-60,"arc":-0.92},{"d":220,"e":"none","x":120,"y":0,"arc":-0.92},{"d":220,"e":"none","x":60,"y":60,"arc":-0.92},{"d":220,"e":"none","x":0,"y":0,"arc":-0.92},{"d":220,"e":"none","x":-60,"y":-60,"arc":0.92},{"d":220,"e":"none","x":-120,"y":0,"arc":0.92},{"d":220,"e":"none","x":-60,"y":60,"arc":0.92},{"d":220,"e":"power1.out","x":0,"y":0,"arc":0.92}]},"boomerang":{"multi":true,"left":{"additive":true,"steps":[{"d":430,"e":"power2.out","x":-280,"y":-70,"rZ":180,"arc":0.8},{"d":540,"e":"power2.inOut","rZ":360,"arc":-0.8}]},"right":{"additive":true,"steps":[{"d":430,"e":"power2.out","x":280,"y":-70,"rZ":-180,"arc":-0.8},{"d":540,"e":"power2.inOut","rZ":-360,"arc":0.8}]}},"lasso":{"multi":true,"left":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.9,"sX":1.08},{"d":230,"e":"power2.in","x":-100,"y":-100,"arc":0.92},{"d":230,"e":"none","x":-200,"y":0,"arc":0.92},{"d":230,"e":"none","x":-100,"y":100,"arc":0.92},{"d":230,"e":"power2.out","x":0,"y":0,"arc":0.92},{"d":110,"e":"power2.out","y":7,"sY":0.87,"sX":1.11},{"d":280,"e":"elastic.out"}]},"right":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.9,"sX":1.08},{"d":230,"e":"power2.in","x":100,"y":-100,"arc":-0.92},{"d":230,"e":"none","x":200,"y":0,"arc":-0.92},{"d":230,"e":"none","x":100,"y":100,"arc":-0.92},{"d":230,"e":"power2.out","x":0,"y":0,"arc":-0.92},{"d":110,"e":"power2.out","y":7,"sY":0.87,"sX":1.11},{"d":280,"e":"elastic.out"}]},"up":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.9,"sX":1.08},{"d":230,"e":"power2.in","x":100,"y":-100,"arc":0.92},{"d":230,"e":"none","x":0,"y":-200,"arc":0.92},{"d":230,"e":"none","x":-100,"y":-100,"arc":0.92},{"d":230,"e":"power2.out","x":0,"y":0,"arc":0.92},{"d":110,"e":"power2.out","y":7,"sY":0.87,"sX":1.11},{"d":280,"e":"elastic.out"}]},"down":{"additive":true,"steps":[{"d":120,"e":"power2.out","y":9,"sY":0.9,"sX":1.08},{"d":230,"e":"power2.in","x":100,"y":100,"arc":-0.92},{"d":230,"e":"none","x":0,"y":200,"arc":-0.92},{"d":230,"e":"none","x":-100,"y":100,"arc":-0.92},{"d":230,"e":"power2.out","x":0,"y":0,"arc":-0.92},{"d":110,"e":"power2.out","y":7,"sY":0.87,"sX":1.11},{"d":280,"e":"elastic.out"}]}}},
			"loops":{
				"noloop":{					
					"extend":"loop",
					"loop":{
						"ex":true,
						"orig":{"x":"50%","y":"50%","z":0},
						"all":[{"t":300,"d":0,"e":"power1.in","a":"","o":1,"aR":false,"mpt":"d"},{"t":300,"d":750,"e":"power1.in","a":"","x":0,"y":0,"o":1,"aR":false,"mpt":"d","dC":750}]
					}
				},
				"pendulum":{
					"multi":true,				
					"up":{
						"extend":"loop",
						"loop":{								
							"ex":true,
							"orig":{"x":"50%","y":"200%","z":0},
							"all":[{"t":300,"d":0,"e":"power1.in","a":"","o":1,"aR":false,"mpt":"d"},{"t":300,"d":750,"e":"power1.in","a":"","x":0,"y":0,"o":1,"aR":false,"mpt":"d","dC":750}],
							"rZ":[{"t":300,"d":0,"e":"power1.in","a":""},{"t":300,"d":750,"e":"power1.in","a":"","rZ":-40,"dC":750},{"t":1050,"d":1500,"e":"sine.inOut","a":"","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rZ":40,"dC":1500}]
						}
					},	
					"down":{
						"extend":"loop",	
						"loop":{							
							"ex":true,
							"orig":{"x":"50%","y":"-200%","z":0},
							"all":[{"t":300,"d":0,"e":"power1.in","a":"","o":1,"aR":false,"mpt":"d"},{"t":300,"d":750,"e":"power1.in","a":"","x":0,"y":0,"o":1,"aR":false,"mpt":"d","dC":750}],
							"rZ":[{"t":300,"d":0,"e":"power1.in","a":""},{"t":300,"d":750,"e":"power1.in","a":"","rZ":-40,"dC":750},{"t":1050,"d":1500,"e":"sine.inOut","a":"","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rZ":40,"dC":1500}]
						}
					},		
					"left":{
						"extend":"loop",
						"loop":{								
							"ex":true,
							"orig":{"x":"150%","y":"50%","z":0},
							"all":[{"t":300,"d":0,"e":"power2.in","a":"","o":1,"aR":false,"mpt":"d"},{"t":300,"d":750,"e":"power2.in","a":"","x":0,"y":0,"o":1,"aR":false,"mpt":"d","dC":750}],
							"rZ":[{"t":300,"d":0,"e":"power1.in","a":""},{"t":300,"d":750,"e":"power1.in","a":"","rZ":-20,"dC":750},{"t":1050,"d":1500,"e":"sine.inOut","a":"","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rZ":20,"dC":1500}]
						}
					},
					"right":{	
						"extend":"loop",																
						"loop":{								
							"ex":true,"orig":{"x":"-50%","y":"50%","z":0},
							"all":[{"t":300,"d":0,"e":"power1.in","a":"","o":1,"aR":false,"mpt":"d"},{"t":300,"d":750,"e":"power1.in","a":"","x":0,"y":0,"o":1,"aR":false,"mpt":"d","dC":750}],		
							"rZ":[{"t":300,"d":0,"e":"power1.in","a":""},{"t":300,"d":750,"e":"power1.in","a":"","rZ":-20,"dC":750},{"t":1050,"d":1500,"e":"sine.inOut","a":"","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rZ":20,"dC":1500}]
						}					
					},
					"inplace":{
						"extend":"loop",
						"loop":{								
							"ex":true,
							"orig":{"x":"50%","y":"50%","z":0},
							"all":[{"t":300,"d":0,"e":"power1.in","o":1,"aR":false,"mpt":"d"},{"t":300,"d":750,"e":"power1.in","x":0,"y":0,"o":1,"aR":false,"mpt":"d","dC":750}],
							"rZ":[{"t":300,"d":0,"e":"power1.in","a":""},{"t":300,"d":750,"e":"power1.in","a":"","rZ":-40,"dC":750},{"t":1050,"d":1500,"e":"power1.inOut","a":"","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rZ":40,"dC":1500}]
						}
					}		
				},
				
				"orbit":{
					"multi":true,
					"right": {
						"extend": "loop",
						"loop": {"ex": true,"all": [{ "t": 0, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{"t": 10, "d": 3000, "e": "none", "o": 1, "aR": false, "mpt": "p","path":"M30.03488,35.61537 C15.03487,50.61537 -8.75192,56.82856 -29.96512,35.61537 -51.17832,14.40217 -51.17832,-3.17143 -29.96512,-24.38463 -8.75192,-45.59783 8.82168,-45.59783 30.03488,-24.38463 51.24808,-3.17143 51.24808,14.40217 30.03488,35.61537","rep": { "s": "se", "r": -1, "sh": false, "y": false, "c": false }}]}
					},
					"left": {
						"extend": "loop",
						"loop": {"ex": true,"all": [{ "t": 0, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{"t": 10, "d": 3000, "e": "none", "o": 1, "aR": false, "mpt": "p","path": "M-30.03488,35.61537 C-15.03487,50.61537 8.75192,56.82856 29.96512,35.61537 51.17832,14.40217 51.17832,-3.17143 29.96512,-24.38463 8.75192,-45.59783 -8.82168,-45.59783 -30.03488,-24.38463 -51.24808,-3.17143 -51.24808,14.40217 -30.03488,35.61537","rep": { "s": "se", "r": -1, "sh": false, "y": false, "c": false }}]}
					},
					"rightbig": {
						"extend": "loop",
						"loop": {"ex": true,"all": [{ "t": 0, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{"t": 10, "d": 3000, "e": "none", "o": 1, "aR": false, "mpt": "p","path":"M90.10464,106.84611 C45.10461,151.84611 -26.25576,170.48568 -89.89536,106.84611 -153.53496,43.20651 -153.53496,-9.51429 -89.89536,-73.15389 -26.25576,-136.79349 26.46504,-136.79349 90.10464,-73.15389 153.74424,-9.51429 153.74424,43.20651 90.10464,106.84611","rep": { "s": "se", "r": -1, "sh": false, "y": false, "c": false }}]}
					},
					"leftbig": {
						"extend": "loop",
						"loop": {"ex": true,"all": [{ "t": 0, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{"t": 10, "d": 3000, "e": "none", "o": 1, "aR": false, "mpt": "p","path":"M-90.10464,106.84611 C-45.10461,151.84611 26.25576,170.48568 89.89536,106.84611 153.53496,43.20651 153.53496,-9.51429 89.89536,-73.15389 26.25576,-136.79349 -26.46504,-136.79349 -90.10464,-73.15389 -153.74424,-9.51429 -153.74424,43.20651 -90.10464,106.84611","rep": { "s": "se", "r": -1, "sh": false, "y": false, "c": false }}]}
					}					
				},
				"wave":{
					"multi":true,					
					"left": {
						"extend": "loop",
						"loop": {"ex": true,"all": [{ "t": 0, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{"t": 10, "d": 3000, "e": "none", "o": 1, "aR": false, "mpt": "p","path":"M164.51514,2.45565 C136.6845,24.55645 109.6724,24.55645 82.66031,0 54.82966,-18.00806 27.81756,-18.00806 0.80547,0 -27.02518,24.55645 -54.03728,24.55645 -81.04937,2.45565 -108.88001,-18.00806 -135.89211,-18.00806 -163.72276,0","rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false }}]}
					},
					"right": {
						"extend": "loop",
						"loop": {"ex": true,"all": [{ "t": 0, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{"t": 10, "d": 3000, "e": "none", "o": 1, "aR": false, "mpt": "p","path":"M-164.51514,2.45565 C-136.6845,24.55645 -109.6724,24.55645 -82.66031,0 -54.82966,-18.00806 -27.81756,-18.00806 -0.80547,0 27.02518,24.55645 54.03728,24.55645 81.04937,2.45565 108.88001,-18.00806 135.89211,-18.00806 163.72276,0","rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false }}]}
					},					
					"leftup": {
						"extend": "loop",
						"loop": {"ex": true,"all": [{ "t": 0, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{"t": 10, "d": 3000, "e": "none", "o": 1, "aR": true, "mpt": "p","path":"M164.51514,2.45565 C136.6845,24.55645 109.6724,24.55645 82.66031,0 54.82966,-18.00806 27.81756,-18.00806 0.80547,0 -27.02518,24.55645 -54.03728,24.55645 -81.04937,2.45565 -108.88001,-18.00806 -135.89211,-18.00806 -163.72276,0","rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false }}]}
					},
					"rightup": {
						"extend": "loop",
						"loop": {"ex": true,"all": [{ "t": 0, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{"t": 10, "d": 3000, "e": "none", "o": 1, "aR": true, "mpt": "p","path":"M-164.51514,2.45565 C-136.6845,24.55645 -109.6724,24.55645 -82.66031,0 -54.82966,-18.00806 -27.81756,-18.00806 -0.80547,0 27.02518,24.55645 54.03728,24.55645 81.04937,2.45565 108.88001,-18.00806 135.89211,-18.00806 163.72276,0","rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false }}]}
					}
				},
				"wiggleh":{
					"multi":true,
					"easy": {
						"extend": "loop",
						"loop": {
							"ex": true,
							"all": [{ "t": 300, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{ "t": 300, "d": 200, "e": "none", "x": 0, "y": 0, "o": 1, "aR": false, "mpt": "d" }],							
							"rY": [{ "t": 300, "d": 0, "e": "none" },{ "t": 300, "d": 500, "e": "none", "rY": -40 },{ "t": 1000, "d": 1500, "e": "sine.inOut", "rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false }, "rY": 40 }]
						}
					},
					"medium":{"extend":"loop",
						"loop":{
							"ex":true,
							"orig": {"x": "50%", "y": "50%", "z": "60"},
							"all":[{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},{"t":300,"d":500,"e":"none","x":0,"y":0,"o":1,"aR":false,"mpt":"d"}],
							"rY":[{"t":300,"d":0,"e":"none"},{"t":300,"d":500,"e":"sine.in","rY":-40},{"t":800,"d":1500,"e":"sine.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rY":40}]
						}
					},
					"strong":{"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":"-160"},
							"all":[{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},{"t":300,"d":200,"e":"none","x":0,"y":0,"o":1,"aR":false,"mpt":"d"}],
							"rY":[{"t":300,"d":0,"e":"none"},{"t":300,"d":500,"e":"sine.in","rY":-40},{"t":800,"d":1500,"e":"sine.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rY":40}]
						}
					}
				},
				"wigglev":{
					"multi":true,
					"easy": {
						"extend": "loop",
						"loop": {
							"ex": true,
							"all": [{ "t": 300, "d": 0, "e": "none", "o": 1, "aR": false, "mpt": "d" },{ "t": 300, "d": 200, "e": "none", "x": 0, "y": 0, "o": 1, "aR": false, "mpt": "d" }],							
							"rX": [{ "t": 300, "d": 0, "e": "none" },{ "t": 300, "d": 500, "e": "none", "rX": -40 },{ "t": 1000, "d": 1500, "e": "sine.inOut", "rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false }, "rX": 40 }]
						}
					},
					"medium":{"extend":"loop",
						"loop":{
							"ex":true,
							"orig": {"x": "50%", "y": "50%", "z": "60"},
							"all":[{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},{"t":300,"d":500,"e":"none","x":0,"y":0,"o":1,"aR":false,"mpt":"d"}],
							"rX":[{"t":300,"d":0,"e":"none"},{"t":300,"d":500,"e":"sine.in","rX":-40},{"t":800,"d":1500,"e":"sine.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rX":40}]
						}
					},
					"strong":{"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":"-160"},
							"all":[{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},{"t":300,"d":200,"e":"none","x":0,"y":0,"o":1,"aR":false,"mpt":"d"}],
							"rX":[{"t":300,"d":0,"e":"none"},{"t":300,"d":500,"e":"sine.in","rX":-40},{"t":800,"d":1500,"e":"sine.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rX":40}]
						}
					},
					"diagonal":{"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"-50%","z":"-160"},
							"all":[
								{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},
								{"t":300,"d":200,"e":"none","x":100,"y":-70,"sX":1,"sY":1,"o":1,"rX":-20,"rY":-20,"rZ":10,"aR":false,"mpt":"d"},
								{"t":500,"d":1500,"e":"circ.inOut","x":0,"y":70,"rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sX":1.4,"sY":1.4,"o":1,"rX":30,"rY":10,"rZ":-5,"aR":false,"mpt":"d"}
							]
						}
					},
					"diagonalmirror":{"extend":"loop",
							"loop":{
								"ex":true,"orig":{"x":"50%","y":"-50%","z":"-160"},
								"all":[
									{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},
									{"t":300,"d":200,"e":"none","x":-100,"y":-70,"sX":1,"sY":1,"o":1,"rX":-20,"rY":20,"rZ":-10,"aR":false,"mpt":"d"},
									{"t":500,"d":1500,"e":"circ.inOut","x":0,"y":70,"rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sX":1.4,"sY":1.4,"o":1,"rX":30,"rY":10,"rZ":-5,"aR":false,"mpt":"d"}]}}
				},
				"spin":{
					"multi":true,
					"normal":{
						"extend":"loop",
						"loop":{
							"ex":true,
							"orig":{"x":"50%","y":"50%","z":0},
							"all":[
							{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},
							{"t":300,"d":500,"e":"none","x":0,"y":0,"o":1,"rZ":0,"aR":false,"mpt":"d"},
							{"t":800,"d":1500,"e":"none","x":0,"y":0,"rep":{"s":"se","r":-1,"sh":false,"y":false,"c":false},"o":1,"rZ":360,"aR":false,"mpt":"d"}
							]
						}
					},
					"yoyo":{
						"extend":"loop",
						"loop":{
							"ex":true,
							"orig":{"x":"50%","y":"50%","z":0},
							"all":[
								{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},
								{"t":300,"d":500,"e":"none","x":0,"y":0,"o":1,"rZ":0,"aR":false,"mpt":"d"},
								{"t":800,"d":1500,"e":"none","x":0,"y":0,"rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"o":1,"rZ":180,"aR":false,"mpt":"d"}
							]
						}
					},
					"floaty":{"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":0},
							"all":[
								{"t":300,"d":0,"e":"none","aR":false,"mpt":"d"},
								{"t":300,"d":500,"e":"none","aR":false,"mpt":"d"},
								{"t":800,"d":6000,"e":"none","rep":{"s":"se","r":-1,"sh":false,"y":false,"c":false},"aR":false,"mpt":"p","path":"M0,-12 C0,-12 98.1631,-140.1631 -30,-12 -131.6466,89.6466 -137.17087,-97.17087 -30,10 136.83301,176.83301 -151.30873,171.30873 10,10 78.50097,-58.50097 10,-2 10,-2"}
							],							
							"filter":[{"t":300,"d":0,"o":1},{"t":300,"d":500,"fu":true,"r":100},{"t":800,"d":1500,"rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"fu":true,"b":5,"r":100}],"rZ":[{"t":300,"d":0},{"t":300,"d":200,"rZ":0},{"t":500,"d":3000,"rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"rZ":720}]
						}
					}					
				},	
				"hoover":{
					"multi":true,
					"vertical":{
						"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":0},
							"all":[
								{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},
								{"t":300,"d":1500,"e":"none","y":10,"o":1,"rZ":0,"aR":false,"mpt":"d"},
								{"t":1800,"d":3000,"e":"sine.inOut","y":-10,"rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"o":1,"aR":false,"mpt":"d"}
							]							
						}
					},
					"horizontal":{
						"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":0},
							"all":[
								{"t":300,"d":0,"e":"none","o":1,"aR":false,"mpt":"d"},
								{"t":300,"d":750,"e":"none","x":100,"aR":false,"mpt":"d"},
								{"t":1050,"d":1500,"e":"sine.inOut","x":-100,"rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"aR":false,"mpt":"d"}
							]							
						}
					}
				},			
				"pulse":{
					"multi":true,
					"fast":{
						"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":0},
							"all":[{"t":300,"d":0,"e":"none","aR":false,"mpt":"d"},{"t":500,"d":1000,"e":"power2.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"aR":false,"mpt":"d"}],
							"sX":[{"t":300,"d":0,"e":"none"},{"t":500,"d":1000,"e":"power2.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sX":1.1}],
							"sY":[{"t":300,"d":0,"e":"none"},{"t":500,"d":1000,"e":"power2.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sY":1.1}]
						}
					},
					"heavy":{
						"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":0},
							"all":[{"t":300,"d":0,"e":"none","aR":false,"mpt":"d"},{"t":500,"d":1500,"e":"power4.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"aR":false,"mpt":"d"}],
							"sX":[{"t":300,"d":0,"e":"none"},{"t":500,"d":1500,"e":"power4.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sX":1.35}],
							"sY":[{"t":300,"d":0,"e":"none"},{"t":500,"d":1500,"e":"power4.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sY":1.35}]
						}
					},
					"soft":{
						"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":0},
							"all":[{"t":300,"d":0,"e":"none","aR":false,"mpt":"d"},{"t":500,"d":2000,"e":"sine.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"aR":false,"mpt":"d"}],
							"sX":[{"t":300,"d":0,"e":"none"},{"t":500,"d":2000,"e":"sine.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sX":1.1}],
							"sY":[{"t":300,"d":0,"e":"none"},{"t":500,"d":2000,"e":"sine.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sY":1.1}]
						}
					},
					"sharp":{
						"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":0},
							"all":[{"t":300,"d":0,"e":"none","aR":false,"mpt":"d"},{"t":500,"d":500,"e":"expo.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"aR":false,"mpt":"d"}],
							"sX":[{"t":300,"d":0,"e":"none"},{"t":500,"d":500,"e":"expo.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sX":1.25}],
							"sY":[{"t":300,"d":0,"e":"none"},{"t":500,"d":500,"e":"expo.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sY":1.25}]
						}
					},
					"pop":{
						"extend":"loop",
						"loop":{
							"ex":true,"orig":{"x":"50%","y":"50%","z":0},
							"all":[{"t":300,"d":0,"e":"none","aR":false,"mpt":"d"},{"t":500,"d":800,"e":"back.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"aR":false,"mpt":"d"}],
							"sX":[{"t":300,"d":0,"e":"none"},{"t":500,"d":800,"e":"back.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sX":1.3}],
							"sY":[{"t":300,"d":0,"e":"none"},{"t":500,"d":800,"e":"back.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sY":1.3}]
						}
					},
					"shrink":{
						"extend":"loop",
						"loop":{
							"ex":true,
							"orig":{"x":"50%","y":"50%","z":0},
							"all":[
								{"t":300,"d":0,"e":"none","a":"","aR":false,"mpt":"d"},
								{"t":300,"d":500,"aR":false,"mpt":"d","dC":500,"sX":1,"sY":1},
								{"t":800,"d":1000,"e":"power0.inOut","rep":{"s":"se","r":-1,"sh":false,"y":true,"c":false},"sX":0.8,"sY":0.8,"dC":1000}
							]							
						}
					}	
				},
				"filter":{
					"multi":true,
					"mono": {
						"extend":"loop",
						"loop": {					
							"ex": true,
							"all": [
								{"t": 300, "d": 0,  "e": "none", "a": "", "aR": false, "mpt": "d"},
								{"t": 300, "d": 500, "e": "none", "aR": false, "mpt": "d"}								
							],							
							"filter": [
								{"t": 300, "d": 0,  "e": "none", "a": "", "o": 1},
								{"t": 300, "d": 500, "e": "none", "a": "", "fu": true, "b": 0, "g": 0, "r": 100, "bu": false, "bb": 0, "bg": 0, "br": 100, "bs": 0, "bi": 0},
								{"t": 800, "d": 500,  "e": "sine.inOut", "a": "", "rep": {"s": "se", "r": -1, "sh": false, "y": true, "c": false}, "fu": true, "b": 0, "g": 100, "r": 100, "bu": false, "bb": 0, "bg": 0, "br": 100, "bs": 0, "bi": 0}
							]
						}
					},
					"fade": {
						"extend":"loop",
						"loop": {					
							"ex": true,
							"all": [
								{"t": 300, "d": 0,  "e": "none", "a": "", "aR": false, "mpt": "d"},
								{"t": 300, "d": 750, "e": "none", "aR": false, "mpt": "d"}								
							],							
							"opacity": [
								{"t": 300, "d": 0,  "e": "none", "a": "", "o": 0},
								{"t": 300, "d": 750, "e": "none", "a": "", "o":1},
								{"t": 1050, "d": 750,  "e": "sine.inOut", "a": "", "rep": {"s": "se", "r": -1, "sh": false, "y": true, "c": false}, "o":0}
							]
						}
					},
					"flatter": {
						"extend": "loop",
						"loop": {
							"ex": true,
							"all": [
								{ "t": 300, "d": 0, "e": "none",  "aR": false, "mpt": "d" },
								{ "t": 300, "d": 200, "e": "none", "aR": false, "mpt": "d" }
							],
							"opacity": [
								{ "t": 300, "d": 0, "e": "none",  "o": 1 },
								{ "t": 300, "d": 200, "e": "none",  "o": 1 },
								{ "t": 700, "d": 100, "e": "sine.inOut", "rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false }, "o": 0.2 }
							],
							"filter": [
								{"t": 300, "d": 0, "e": "none", "o": 1},
								{"t": 300, "d": 200, "e": "none", "fu": true, "b": 0, "g": 0, "r": 100,"bu": false, "bb": 0, "bg": 0, "br": 100, "bs": 0, "bi": 0},
								{"t": 700, "d": 100, "e": "sine.inOut", "rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false },"fu": true, "b": 4, "g": 0, "r": 100,"bu": false, "bb": 0, "bg": 0, "br": 100, "bs": 0, "bi": 0}
							]
						}
					},
					"lightning": {
						"extend": "loop",
						"loop": {
							"ex": true,
							"all": [
								{ "t": 300, "d": 0, "e": "none", "aR": false, "mpt": "d" },
								{ "t": 300, "d": 200, "e": "none", "aR": false, "mpt": "d" }
							],							
							"filter": [
								{"t": 300, "d": 0, "e": "none", "o": 1 },
								{"t": 300, "d": 500, "e": "none","fu": true, "b": 0, "g": 0, "r": 100,"bu": false, "bb": 0, "bg": 0, "br": 100, "bs": 0, "bi": 0},
								{"t": 800, "d": 500, "e": "sine.inOut","rep": { "s": "se", "r": -1, "sh": false, "y": true, "c": false },"fu": true, "b": 0, "g": 0, "r": 1000,"bu": false, "bb": 0, "bg": 0, "br": 100, "bs": 0, "bi": 0}
							]
						}
					}
				}
			}
		}';


		$out = '{
			"basic":{
				"fade": {
					"multi":true,					
					"easy":{"content":{"all":[{"d":1500,"e":"power4.inOut","o":0}]}},					
					"middle":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":0}]}},					
					"strong":{"content":{"all":[{"d":500,"e":"power4.inOut","o":0}]}}
				}
			},
			
			"lines":{
				"fade":{
					"multi":true,"demo":"lines",
					"easy":{"content":{"all":[{"d":500,"e":"power4.inOut","o":0}]}},
					"middle":{"content":{"all":[{"d":700,"e":"power4.inOut","o":0}]}},
					"strong":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":0}]}}
				},
				"flyin":{
					"multi":true,"demo":"lines",
					"up":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "lines":{"ex":true,"all":[{"y":"-100%","yRe":1,"o":"0","rZ":"45deg","d":800,"dir":"start","e":"power3.inOut","sd":90}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"down":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "lines":{"ex":true,"all":[{"y":"100%","yRe":1,"o":"0","rZ":"-45deg","d":800,"dir":"start","e":"power3.inOut","sd":90}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"left":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "lines":{"ex":true,"all":[{"x":"-105%","xRe":1,"o":"0","rZ":"-90deg","d":800,"dir":"start","e":"power3.inOut","sd":120}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"right":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "lines":{"ex":true,"all":[{"x":"105%","xRe":1,"o":"0","rZ":"90deg","d":800,"dir":"end","e":"power3.inOut","sd":120}]}, "mask":{"all":[{"oflow":"hidden"}]}}
				},
				"flip":{
					"multi":true,"demo":"lines",
					"up":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "lines":{"ex":true,"orig":{"z":"10%","y":"0%"},"all":[{"yRe":1,"o":"0","rY":"0deg","rX":"-90deg","d":800,"dir":"start","e":"power4.inOut","sd":120}]}},
					"down":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "lines":{"ex":true,"orig":{"z":"10%","y":"100%"},"all":[{"yRe":0,"o":"0","rY":"0deg","rX":"90deg","d":800,"dir":"start","e":"power4.inOut","sd":120}]}},
					"left":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "lines":{"ex":true,"orig":{"z":"10%","x":"100%"},"all":[{"xRe":1,"o":"0","rX":"0deg","rY":"-90deg","d":800,"dir":"end","e":"power4.inOut","sd":120}]}},
					"right":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "lines":{"ex":true,"orig":{"z":"10%","x":"0%"},"all":[{"xRe":1,"o":"0","rX":"0deg","rY":"90deg","d":800,"dir":"start","e":"power4.inOut","sd":120}]}}
				},
				"cycle":{
					"multi":true,"demo":"lines",
					"vertical":{
					"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":1,"rX":"70deg","rZ":"20deg","rY":"20deg","t":20,"d":1250,"f":300,"e":"power2.inOut","a":"Anim To","pE":"d","sX":1,"sY":1,"off":0,"dC":1700}]},
					"lines":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"y":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rX":"[-70,70]","dir":"start","t":20,"d":970,"e":"power4.inOut","dir":"center","sd":55}]}
					},
					"horizontal":{
					"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":1,"rX":"20deg","rZ":"20deg","rY":"70deg","t":20,"d":1250,"f":300,"e":"power2.inOut","a":"Anim To","pE":"d","sX":1,"sY":1,"off":0,"dC":1700}]},
					"lines":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"x":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rY":"[-70,70]","dir":"start","t":20,"d":970,"e":"power4.inOut","dir":"center","sd":55}]}
					}
				}
			},
			"words":{
				"fade":{
					"multi":true,"demo":"words",
					"easy":{"content":{"all":[{"d":500,"e":"power4.inOut","o":0}]}},
					"middle":{"content":{"all":[{"d":700,"e":"power4.inOut","o":0}]}},
					"strong":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":0}]}}
				},
				"flyin":{
					"multi":true,"demo":"words",
					"up":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "words":{"ex":true,"all":[{"y":"-100%","yRe":1,"o":"0","rZ":"45deg","d":800,"dir":"start","e":"power3.inOut","sd":90}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"down":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "words":{"ex":true,"all":[{"y":"100%","yRe":1,"o":"0","rZ":"-45deg","d":800,"dir":"start","e":"power3.inOut","sd":90}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"left":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "words":{"ex":true,"all":[{"x":"-105%","xRe":1,"o":"0","rZ":"-90deg","d":800,"dir":"start","e":"power3.inOut","sd":120}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"right":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "words":{"ex":true,"all":[{"x":"105%","xRe":1,"o":"0","rZ":"90deg","d":800,"dir":"end","e":"power3.inOut","sd":120}]}, "mask":{"all":[{"oflow":"hidden"}]}}
				},
				"flip":{
					"multi":true,"demo":"words",
					"up":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "words":{"ex":true,"orig":{"z":"10%","y":"0%"},"all":[{"yRe":1,"o":"0","rY":"0deg","rX":"-90deg","d":800,"dir":"start","e":"power4.inOut","sd":120}]}},
					"down":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "words":{"ex":true,"orig":{"z":"10%","y":"100%"},"all":[{"yRe":0,"o":"0","rY":"0deg","rX":"90deg","d":800,"dir":"start","e":"power4.inOut","sd":120}]}},
					"left":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "words":{"ex":true,"orig":{"z":"10%","x":"100%"},"all":[{"xRe":1,"o":"0","rX":"0deg","rY":"-90deg","d":800,"dir":"end","e":"power4.inOut","sd":120}]}},
					"right":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "words":{"ex":true,"orig":{"z":"10%","x":"0%"},"all":[{"xRe":1,"o":"0","rX":"0deg","rY":"90deg","d":800,"dir":"start","e":"power4.inOut","sd":120}]}}
				},
				"cycle":{
					"multi":true,"demo":"words",
					"vertical":{
					"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":1,"rX":"70deg","rZ":"20deg","rY":"20deg","t":20,"d":1250,"f":300,"e":"power2.inOut","a":"Anim To","pE":"d","sX":1,"sY":1,"off":0,"dC":1700}]},
					"words":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"y":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rX":"[-70,70]","dir":"start","t":20,"d":970,"e":"power4.inOut","dir":"center","sd":55}]}
					},
					"horizontal":{
					"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":1,"rX":"20deg","rZ":"20deg","rY":"70deg","t":20,"d":1250,"f":300,"e":"power2.inOut","a":"Anim To","pE":"d","sX":1,"sY":1,"off":0,"dC":1700}]},
					"words":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"x":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rY":"[-70,70]","dir":"start","t":20,"d":970,"e":"power4.inOut","dir":"center","sd":55}]}
					}
				}
			},
			"letter":{
				"fade":{
					"multi":true,
					"easy":{"content":{"all":[{"d":500,"e":"power4.inOut","o":0}]}},
					"middle":{"content":{"all":[{"d":700,"e":"power4.inOut","o":0}]}},
					"strong":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":0}]}}
				},
				"flyin":{
					"multi":true,"demo":"chars",
					"up":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "chars":{"ex":true,"all":[{"y":"-100%","yRe":1,"o":"0","rZ":"55deg","d":800,"dir":"start","e":"power3.inOut","sd":50}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"down":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "chars":{"ex":true,"all":[{"y":"100%","yRe":1,"o":"0","rZ":"-55deg","d":800,"dir":"start","e":"power3.inOut","sd":50}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"left":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "chars":{"ex":true,"all":[{"x":"-105%","xRe":1,"y":"0","o":"0","rZ":"-90deg","d":800,"dir":"start","e":"power3.inOut","sd":75}]}, "mask":{"all":[{"oflow":"hidden"}]}},
					"right":{"content":{"all":[{"d":500,"e":"power3.inOut","o":1}]}, "chars":{"ex":true,"all":[{"x":"105%","xRe":1,"y":"0","o":"0","rZ":"90deg","d":800,"dir":"end","e":"power3.inOut","sd":75}]}, "mask":{"all":[{"oflow":"hidden"}]}}
				},
				"flip":{
					"multi":true,"demo":"chars",
					"up":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "chars":{"ex":true,"orig":{"z":"10%","y":"0%"},"all":[{"yRe":1,"o":"0","rY":"0deg","rX":"-90deg","d":800,"dir":"start","e":"power4.inOut","sd":75}]}},
					"down":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "chars":{"ex":true,"orig":{"z":"10%","y":"100%"},"all":[{"yRe":0,"o":"0","rY":"0deg","rX":"90deg","d":800,"dir":"start","e":"power4.inOut","sd":75}]}},
					"left":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "chars":{"ex":true,"orig":{"z":"10%","x":"100%"},"all":[{"xRe":1,"o":"0","rX":"0deg","rY":"-90deg","d":800,"dir":"end","e":"power4.inOut","sd":75}]}},
					"right":{"content":{"all":[{"d":1000,"e":"power4.inOut","o":1,"x":0,"opacity":1,"rZ":"0deg"}]}, "chars":{"ex":true,"orig":{"z":"10%","x":"0%"},"all":[{"xRe":1,"o":"0","rX":"0deg","rY":"90deg","d":800,"dir":"start","e":"power4.inOut","sd":75}]}}
				},
				"cycle":{
					"multi":true,"demo":"chars",
					"vertical":{
					"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":1,"rX":"70deg","rZ":"20deg","rY":"20deg","t":20,"d":1250,"f":300,"e":"power2.inOut","a":"Anim To","pE":"d","sX":1,"sY":1,"off":0,"dC":1700}]},
					"chars":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"y":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rX":"[-70,70]","dir":"start","t":20,"d":970,"e":"power4.inOut","dir":"center","sd":31}]}
					},
					"horizontal":{
					"content":{"pers":600,"ex":false,"d":false,"orig":{"x":50,"y":"50%","z":-50},"all":[{"o":1,"rX":"20deg","rZ":"20deg","rY":"70deg","t":20,"d":1250,"f":300,"e":"power2.inOut","a":"Anim To","pE":"d","sX":1,"sY":1,"off":0,"dC":1700}]},
					"chars":{"pers":600,"ex":true,"d":false,"orig":{"x":"50%","y":"50%","z":-5},"all":[{"x":["#a","[-70%,70%]","#a","#a","#a"],"o":0,"rY":"[-70,70]","dir":"start","t":20,"d":970,"e":"power4.inOut","dir":"center","sd":31}]}
					}
				}
			},
			"slide":{
				"shortdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"y":"-50","d":1000,"e":"power3.inOut"}]}},
					"down":{"content":{"all":[{"o":0,"y":"50","d":1000,"e":"power3.inOut"}]}},
					"left":{"content":{"all":[{"o":0,"x":"-50","d":1000,"e":"power3.inOut"}]}},
					"right":{"content":{"all":[{"o":0,"x":"50","d":1000,"e":"power3.inOut"}]}}
				},
				"mediumdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"yRe":2,"y":"-25%","d":1000,"e":"power3.inOut"}]}},
					"down":{"content":{"all":[{"o":0,"yRe":2,"y":"25%","d":1000,"e":"power3.inOut"}]}},
					"left":{"content":{"all":[{"o":0,"xRe":2,"x":"-25%","d":1000,"e":"power3.inOut"}]}},
					"right":{"content":{"all":[{"o":0,"xRe":2,"x":"25%","d":1000,"e":"power3.inOut"}]}}
				},
				"longdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"yRe":1,"y":"top","d":1000,"e":"power3.inOut"}]}},
					"down":{"content":{"all":[{"o":0,"yRe":1,"y":"bottom","d":1000,"e":"power3.inOut"}]}},
					"left":{"content":{"all":[{"o":0,"xRe":1,"x":"left","d":1000,"e":"power3.inOut"}]}},
					"right":{"content":{"all":[{"o":0,"xRe":1,"x":"right","d":1000,"e":"power3.inOut"}]}}
				},
				"diagonal":{
					"multi":true,
					"rightup":{"content":{"all":[{"o":0,"y":"-100%", "x":"100%", "yRe":1, "xyRe":1,"d":1000,"e":"power3.inOut"}]}},
					"rightdown":{"content":{"all":[{"o":0,"y":"100%", "x":"100%", "yRe":1, "xyRe":1,"d":1000,"e":"power3.inOut"}]}},
					"leftup":{"content":{"all":[{"o":0,"y":"-100%", "x":"-100%", "yRe":1, "xyRe":1,"d":1000,"e":"power3.inOut"}]}},
					"leftdown":{"content":{"all":[{"o":0,"y":"100%", "x":"-100%", "yRe":1, "xyRe":1,"d":1000,"e":"power3.inOut"}]}}
				}					
			},
			"skew":{
				"shortdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"y":"-85","skY":10,"d":1000,"e":"power3.inOut"}]}},
					"down":{"content":{"all":[{"o":0,"y":"85","skY":-10,"d":1000,"e":"power3.inOut"}]}},
					"left":{"content":{"all":[{"o":0,"x":"-75","skX":20,"d":1000,"e":"power3.inOut"}]}},
					"right":{"content":{"all":[{"o":0,"x":"75","skX":-20,"d":1000,"e":"power3.inOut"}]}}
				},
				"mediumdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"yRe":2,"y":"-25%","skY":20,"d":1000,"e":"power3.inOut"}]}},
					"down":{"content":{"all":[{"o":0,"yRe":2,"y":"25%","skY":-20,"d":1000,"e":"power3.inOut"}]}},
					"left":{"content":{"all":[{"o":0,"xRe":2,"x":"-25%","skX":25,"d":1000,"e":"power3.inOut"}]}},
					"right":{"content":{"all":[{"o":0,"xRe":2,"x":"25%","skX":-25,"d":1000,"e":"power3.inOut"}]}}
				},
				"longdistance": {
					"multi":true,
					"up":{"content":{"all":[{"o":0,"yRe":1,"y":"bottom","skY":-30,"d":1000,"e":"power3.inOut"}]}},
					"down":{"content":{"all":[{"o":0,"yRe":1,"y":"top","skY":30,"d":1000,"e":"power3.inOut"}]}},
					"left":{"content":{"all":[{"o":0,"xRe":1,"x":"right","skX":30,"d":1000,"e":"power3.inOut"}]}},
					"right":{"content":{"all":[{"o":0,"xRe":1,"x":"left","skX":-30,"d":1000,"e":"power3.inOut"}]}}
				}									
			},			
			"rotate":{				
				"smash":{
					"multi":true,
					"up":{"content":{"all":[{"rZ":"20deg", "o":0, "y":"200%", "sY":2, "sX":2,"d":1000,"e":"power3.inOut"}]}},					
					"down":{"content":{"all":[{"rZ":"-20deg",  "o":0,  "y":"-200%", "sY":2, "sX":2,"d":1000,"e":"power3.inOut"}]}},					
					"left":{"content":{"all":[{"rZ":"20deg", "o":0, "x":"200%", "sY":2, "sX":2,"d":1000,"e":"power3.inOut"}]}},
					"right":{"content":{"all":[{"rZ":"-20deg", "o":0, "x":"-200%", "sY":2, "sX":2,"d":1000,"e":"power3.inOut"}]}},
					"center":{"content":{"all":[{"o":0,"rY":"-20deg", "x":"-10%", "rX":"-20deg", "y":"10%", "sY":2, "sX":2,"d":1000,"e":"power3.out"}]}}
				},
				"flip":{
					"multi":true,
					"up":{"content":{"all":[{"rX":"-360deg", "o":0,"d":1000, "e":"power2.in"}]}},
					"down":{"content":{"all":[{"rX":"360deg", "o":0,"d":1000, "e":"power2.in"}]}},
					"left":{"content":{"all":[{"rY":"360deg", "o":0,"d":1000, "e":"power2.in"}]}},
					"right":{"content":{"all":[{"rY":"-360deg", "o":0,"d":1000, "e":"power2.in"}]}}					
				},
				"turn":{
					"multi":true,
					"up":{"content":{"orig":{"z":"-50%"}, "all":[{"rX":"70deg", "o":0,"d":1250,"e":"power2.inOut"}]}},
					"down":{"content":{"orig":{"z":"-50%"}, "all":[{"rX":"-70deg", "o":0,"d":1250,"e":"power2.inOut"}]}},
					"left":{"content":{"orig":{"z":"-50%"}, "all":[{"rY":"70deg", "o":0,"d":1250,"e":"power2.inOut"}]}},
					"right":{"content":{"orig":{"z":"-50%"}, "all":[{"rY":"-70deg", "o":0,"d":1250,"e":"power2.inOut"}]}}
				}
			},	
			"zoom":{
				"zoomout":{
					"multi":true,
					"normal":{"content":{"all":[{"o":0,"sX":"0.8","sY":"0.8","d":1000,"e":"power3.in"}]}},
					"squashh":{"content":{"all":[{"o":0,"sX":"0.3","sY":"0.95","d":1000,"e":"power3.in"}]}},
					"squashv":{"content":{"all":[{"o":0,"sX":"0.95","sY":"0.3","d":1000,"e":"power3.in"}]}}
				},
				"zoomin":{
					"multi":true,
					"normal":{"content":{"all":[{"o":0,"sX":"1.25","sY":"1.25","d":1000,"e":"power3.in"}]}},
					"squashh":{"content":{"all":[{"o":0,"sX":"1.8","sY":"1.05","d":1000,"e":"power3.in"}]}},
					"squashv":{"content":{"all":[{"o":0,"sX":"1.05","sY":"1.8","d":1000,"e":"power3.in"}]}}
				}
			},
			"masktrans":{				
				"zoom":{
					"multi":true,
					"crossout":{						
						"content": {"all":[{"o":0,"sX":2,"sY":2,"d":1000,"e":"power2.in"}]},
						"mask": {"all":[{"oflow":"hidden"}]}						
					},
					"crossin":{						
						"content": {"all":[{"o":0,"sX":0.2,"sY":0.2,"d":1000,"e":"back.in"}]},
						"mask": {"all":[{"oflow":"hidden"}]}						
					}					
				},
				"conceal":{
					"multi":true,
					"up":{						
						"content": {"all":[{"o":0,"y":"-100%","d":1200,"e":"power3.inOut"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},
					"down":{						
						"content": {"all":[{"o":0,"y":"100%","d":1200,"e":"power3.inOut"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},
					"left":{						
						"content": {"all":[{"o":0,"x":"-100%","d":1200,"e":"power3.inOut"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},
					"right":{						
						"content": {"all":[{"o":0,"x":"100%","d":1200,"e":"power3.inOut"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					}
						
				},
				"glide":{
					"multi":true,
					"up":{
						"content": {"all":[{"o":1,"y":"-175%","d":1200,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden", "y":"100%","d":1200,"e":"power3.in"}]}
					},
					"down":{
						"content": {"all":[{"o":1,"y":"175%","d":1200,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden", "y":"-100%","d":1200,"e":"power3.in"}]}
					},								
					"left":{
						"content": {"all":[{"o":1,"x":"-175%","d":1200,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden", "x":"100%","d":1200,"e":"power3.out"}]}
					},
					"right":{
						"content": {"all":[{"o":1,"x":"175%","d":1200,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden", "x":"-100%","d":1200,"e":"power3.in"}]}
					}
				},
				"cover":{
					"multi":true,
					"up":{
						"content": {"all":[{"o":1,"y":"-100%","d":1200,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden", "y":"100%","d":1200,"e":"power3.in"}]}
					},
					"down":{
						"content": {"all":[{"o":1,"y":"100%","d":1200,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden", "y":"-100%","d":1200,"e":"power3.in"}]}
					},								
					"left":{
						"content": {"all":[{"o":1,"x":"-100%","d":1200,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden", "x":"100%","d":1200,"e":"power3.in"}]}
					},
					"right":{
						"content": {"all":[{"o":1,"x":"100%","d":1200,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden", "x":"-100%","d":1200,"e":"power3.in"}]}
					}
				},
				"rotate":{
					"multi":true,
					"rightdown":{
						"content":{"orig":{"x":"100%", "y":"100%"},"all":[{"rZ":"70deg",  "x":"50%", "o":0,"d":1000,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},					
					"rightup":{
						"content":{"orig":{"x":"100%", "y":"0%"},"all":[{"rZ":"-70deg", "x":"50%", "o":0,"d":1000,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},					
					"leftup":{
						"content":{"orig":{"x":"0%", "y":"100%"},"all":[{"rZ":"70deg", "x":"-50%","o":0,"d":1000,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},
					"leftdown":{
						"content":{"orig":{"x":"0%", "y":"0%"},"all":[{"rZ":"-70deg", "x":"-50%","o":0,"d":1000,"e":"power3.in"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					}				},
				"blck" : {
					"multi":true,
					"left" : {
						"content": {"all":[{"d":1200,"e":"power4.out","o":1,"fx":"cleft","fxc":"#ffffff","fxe":"power4.inOut","fxs":"1200"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},
					"right" : {
						"content": {"all":[{"d":1200,"e":"power4.out","o":1,"fx":"cright","fxc":"#ffffff","fxe":"power4.inOut","fxs":"1200"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},
					"up" : {
						"content": {"all":[{"d":1200,"e":"power4.out","o":1,"fx":"ctop","fxc":"#ffffff","fxe":"power4.inOut","fxs":"1200"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					},
					"down" : {
						"content": {"all":[{"d":1200,"e":"power4.out","o":1,"fx":"cbottom","fxc":"#ffffff","fxe":"power4.inOut","fxs":"1200"}]},
						"mask": {"all":[{"oflow":"hidden"}]}
					}
				}
		}
		}';

		

		$anim = [];
		$inDecoded = ($raw) ? $in : json_decode($in, true);
		$outDecoded = ($raw) ? $out : json_decode($out, true);

		$anim['in']   = $inDecoded;
		$anim['out']  = $outDecoded;
		//"Additional" = everything that ADDS to the layer instead of overwriting it (extend:"loop")
		$anim['loops']['oneshot'] = isset($inDecoded['oneshot']) ? $inDecoded['oneshot'] : null;
		$anim['loops']['loops'] = isset($inDecoded['loops']) ? $inDecoded['loops'] : null;
		

		return $anim;
	}

	/**
	 * add default icon sets of Slider Revolution
	 * filter callback: adds our icon fonts and their CSS class prefixes to the editor's list
	 * @since: 5.0
	 * @return array
	 **/
	public function set_icon_sets($icon_sets){

		$icon_sets['FontAwesomeIcon'] = 'fa-icon-';
		$icon_sets['FontAwesome'] = 'fa-';
		$icon_sets['PeIcon'] = 'pe-7s-';

		return $icon_sets;
	}

	/**
	 * attempt to load cache for _get_base_transitions
	 * @return mixed
	 */
	public function get_base_transitions($raw = false){
		return $this->get_wp_cache('_get_base_transitions', [$raw]);
	}

	/**
	 * get base transitions
	 **/
	protected function _get_base_transitions($raw = false){
		$transitions = '{
			"basic":{ 
				"icon":"scene",
				"name":"Base",
				"fade":{					
					"notransition":{"speed":"10","in":{"o":1},"out":{"a":false, "o":1}},
					"fade":{
						"multi":true,
						"simple":{"in":{"o":0},"out":{"a":false}},
						"cross":{"in":{"o":0}},
						"dark":{"in":{"o":0},"out":{"a":false,"o":0},"p":"dark"},
						"light":{"in":{"o":0},"out":{"a":false,"o":0},"p":"light"},
						"transparent":{"in":{"o":0},"out":{"a":false,"o":0},"p":"transparent"}
					},
					"gradient":{
						"multi":true,
						"horizontal":{"in":{"o":0,"col":30},"f":"slidebased","sd":75},
						"vertical":{"in":{"o":0,"row":30},"f":"slidebased","sd":75},
						"right":{"in":{"o":0,"col":30},"f":"start","sd":75},
						"left":{"in":{"o":0,"col":30},"f":"end","sd":75},
						"down":{"in":{"o":0,"row":30},"f":"start","sd":75},
						"up":{"in":{"o":0,"row":30},"f":"end","sd":75}
					},
					"radial":{
						"multi":true,
						"centerout":{"d":"15","in":{"o":0,"sx":"1.1","sy":"1.1","m":true,"row":30,"col":30},"out":{"a":false},"f":"center"},
						"edgesin":{"d":"15","in":{"o":0,"sx":"1.1","sy":"1.1","m":true,"row":30,"col":30},"out":{"a":false},"f":"edges"},
						"darkcenterout":{"d":"15","p":"dark","in":{"o":0,"sx":"1.1","sy":"1.1","m":true,"row":30,"col":30},"out":{"a":false},"f":"center"},
						"darkedgesin":{"d":"15","p":"dark","in":{"o":0,"sx":"1.1","sy":"1.1","m":true,"row":30,"col":30},"out":{"a":false},"f":"edges"}
					}
				},
				"slide":{
					"slideover":{
						"multi":true,
						"vertical":{"in":{"o":"1","y":"(100%)"},"out":{"o":"1", "a":false}},
						"horizontal":{"in":{"o":"1","x":"(100%)"},"out":{"o":"1", "a":false}},
						"up":{"in":{"o":"1","y":"100%"},"out":{"o":"1", "a":false}},
						"down":{"in":{"o":"1","y":"-100%"},"out":{"o":"1", "a":false}},
						"left":{"in":{"o":"1","x":"100%"},"out":{"o":"1", "a":false}},						
						"right":{"in":{"o":"1","x":"-100%"},"out":{"o":"1", "a":false}}						
					},
					"remove":{
						"multi":true,
						"vertical":{"out":{"a":false,"y":"(-100%)"}},
						"horizontal":{"out":{"a":false,"x":"(-100%)"}},						
						"up":{"out":{"a":false,"y":"-100%"}},
						"down":{"out":{"a":false,"y":"100%"}},
						"left":{"out":{"a":false,"x":"-100%"}},
						"right":{"out":{"a":false,"x":"100%"}}										
					},
					"slideinout":{
						"multi":true,
						"vertical":{ "in":{"o":"1","y":"(100%)"}, "out":{"y":"(-100%)"}},
						"horizontal":{"in":{"o":"1","x":"(100%)"}, "out":{"x":"(-100%)"}},					
						"up":{ "in":{"o":"1","y":"100%"}, "out":{"y":"-100%"}},
						"down":{ "in":{"o":"1","y":"-100%"}, "out":{"y":"100%"}},
						"left":{"in":{"o":"1","x":"100%"}, "out":{"x":"-100%"}},
						"right":{"in":{"o":"1","x":"-100%"}, "out":{"x":"100%"}}
					},
					"slideinoutfadein":{
						"multi":true,
						"vertical":{"in":{"o":0,"y":"(100%)"},"out":{"y":"(-100%)","a":false}},
						"horizontal":{"in":{"o":0,"x":"(100%)"},"out":{"x":"(-100%)","a":false}},
						"up":{"in":{"o":0,"y":"100%"},"out":{"y":"-100%","a":false}},
						"down":{"in":{"o":0,"y":"-100%"},"out":{"y":"100%","a":false}},
						"left":{"in":{"o":0,"x":"100%"},"out":{"x":"-100%","a":false}},
						"right":{"in":{"o":0,"x":"-100%"},"out":{"x":"100%","a":false}}
					},
					"slideinoutfadeinout":{
						"multi":true,
						"vertical":{"in":{"o":0,"y":"(100%)"},"out":{"y":"(-100%)","o":0,"a":false}},
						"horizontal":{"in":{"o":0,"x":"(100%)"},"out":{"x":"(-100%)","o":0,"a":false}},
						"up":{"in":{"o":0,"y":"100%"},"out":{"y":"-100%","o":0,"a":false}},
						"down":{"in":{"o":0,"y":"-100%"},"out":{"y":"100%","o":0,"a":false}},
						"left":{"in":{"o":0,"x":"100%"},"out":{"x":"-100%","o":0,"a":false}},
						"right":{"in":{"o":0,"x":"-100%"},"out":{"x":"100%","o":0,"a":false}}
					}
				},
				"parallax":{
					"singleparallax":{
						"multi":true,
						"vertical":{ "in":{"o":1,"y":"(100%)"},"out":{"a":false,"y":"(-60%)"}},
						"horizontal":{ "in":{"o":1,"x":"(100%)"},"out":{"a":false,"x":"(-60%)"}},
						"up":{ "in":{"o":1,"y":"100%"},"out":{"a":false,"y":"-60%"}},
						"down":{ "in":{"o":1,"y":"-100%"},"out":{"a":false,"y":"60%"}},
						"left":{ "in":{"o":1,"x":"100%"},"out":{"a":false,"x":"-60%"}},
						"right":{ "in":{"o":1,"x":"-100%"},"out":{"a":false,"x":"60%"}}
					},
					"double":{
						"multi":true,
						"vertical":{"speed":"2000", "in":{"y":"(100%)"},"e":"slidingoverlay"},
						"horizontal":{"speed":"2000","in":{"x":"(100%)"},"e":"slidingoverlay"},
						"up":{"speed":"2000", "in":{"y":"100%"},"e":"slidingoverlay"},
						"down":{"speed":"2000", "in":{"y":"-100%"},"e":"slidingoverlay"},
						"left":{"speed":"2000", "in":{"x":"100%"},"e":"slidingoverlay"},
						"right":{"speed":"2000", "in":{"x":"-100%"},"e":"slidingoverlay"}
					}
				},
				"zoom":{
					"simplezoomin":{
						"multi":true,
						"in":{"in":{"sx":"0.6","sy":"0.6","o":0},"out":{"a":false,"sx":"1.6","sy":"1.6","o":0}},						
						"indark":{"p":"dark", "in":{"sx":"0.6","sy":"0.6","o":0},"out":{"a":false,"sx":"1.6","sy":"1.6","o":0}},						
						"inlight":{ "p":"light", "in":{"sx":"0.6","sy":"0.6","o":0},"out":{"a":false,"sx":"1.6","sy":"1.6","o":0}}						
					},
					"simplezoomout":{
						"multi":true,						
						"out":{"in":{"sx":"1.6","sy":"1.6","o":-0.5,"e":"power0.inOut"},"out":{"a":false,"sx":"0.6","sy":"0.6","o":0}},						
						"outdark":{"p":"dark", "in":{"sx":"1.6","sy":"1.6","o":-0.5,"e":"power0.inOut"},"out":{"a":false,"sx":"0.6","sy":"0.6","o":0}},						
						"outlight":{"p":"light", "in":{"sx":"1.6","sy":"1.6","o":-0.5,"e":"power0.inOut"},"out":{"a":false,"sx":"0.6","sy":"0.6","o":0}}
					},
					"zoomslidein":{
						"multi":true,
						"vertical":{ "in":{"o":"1","y":"(100%)"}, "out":{"a":false, "sx":"0.85", "sy":"0.85", "o":"1"}},
						"horizontal":{ "in":{"o":"1","x":"(100%)"}, "out":{"a":false, "sx":"0.85", "sy":"0.85", "o":"1"}},
						"up":{ "in":{"o":"1","y":"100%"}, "out":{"a":false, "sx":"0.85", "sy":"0.85", "o":"1"}},
						"down":{ "in":{"o":"1","y":"-100%"}, "out":{"a":false, "sx":"0.85", "sy":"0.85", "o":"1"}},
						"left":{ "in":{"o":"1","x":"100%"}, "out":{"a":false, "sx":"0.85", "sy":"0.85", "o":"1"}},
						"right":{ "in":{"o":"1","x":"-100%"}, "out":{"a":false, "sx":"0.85", "sy":"0.85", "o":"1"}}
					},
					"zoomslideout":{
						"multi":true,
						"vertical":{"o":"outin", "in":{"sx":"0.85", "sy":"0.85","o":"0"}, "out":{"a":false, "y":"(100%)", "o":"1"}},
						"horizontal":{"o":"outin", "in":{"sx":"0.85", "sy":"0.85","o":"0"}, "out":{"a":false,  "x":"(100%)", "o":"1"}},
						"up":{"o":"outin", "in":{"sx":"0.85", "sy":"0.85","o":"0"}, "out":{"a":false, "y":"-100%", "o":"1"}},
						"down":{"o":"outin", "in":{"sx":"0.85", "sy":"0.85","o":"0"}, "out":{"a":false, "y":"100%", "o":"1"}},
						"left":{"o":"outin", "in":{"sx":"0.85", "sy":"0.85","o":"0"}, "out":{"a":false,  "x":"100%", "o":"1"}},
						"right":{"o":"outin", "in":{"sx":"0.85", "sy":"0.85","o":"0"}, "out":{"a":false,  "x":"-100%", "o":"1"}}
					}
				},
				"filter":{
					"blurzoom": {
						"multi":true,
						"tiny":{"filter":{"u":true, "b":"2", "e":"power2.in"},"in":{"o":"0","e":"power1.in", "sx":"1.01","sy":"1.01"}},
						"easy":{"filter":{"u":true, "b":"4", "e":"power2.in"},"in":{"o":"0","e":"power1.in", "sx":"1.02","sy":"1.02"}},
						"medium":{"filter":{"u":true, "b":"6", "e":"power2.in"},"in":{"o":"0","e":"power1.in", "sx":"1.05","sy":"1.05"}},
						"strong":{"filter":{"u":true, "b":"10", "e":"power2.in"},"in":{"o":"0","e":"power1.in", "sx":"1.1","sy":"1.1"}}
					},
					"brightness":{
						"multi":true,
						"easy":{"filter":{"u":true, "h":"200", "e":"power1.inOut"},"in":{"o":"0","e":"power1.in"}},
						"medium":{"filter":{"u":true, "h":"400", "e":"power1.inOut"},"in":{"o":"0","e":"power1.in"}}
					},
					"grayscale":{
						"multi":true,
						"easy":{"filter":{"u":true, "g":"50", "e":"power2.in"},"in":{"o":"0","e":"power1.in"}},
						"medium":{"filter":{"u":true, "g":"80", "e":"power2.in"},"in":{"o":"0","e":"power1.in"}},
						"strong":{"filter":{"u":true, "g":"100", "e":"power2.in"},"in":{"o":"0","e":"power1.in"}}
					},
					"sephia":{
						"multi":true,
						"easy":{"filter":{"u":true, "s":"20", "e":"power2.in"},"in":{"o":"0","e":"power1.in"}},	
						"medium":{"filter":{"u":true, "s":"50", "e":"power2.in"},"in":{"o":"0","e":"power1.in"}},
						"strong":{"filter":{"u":true, "s":"100", "e":"power2.in"},"in":{"o":"0","e":"power1.in"}}
					}
				},
				"effects":{
					"cube":{
						"multi":true,
						"vertical":{"speed":"2000", "in":{"o":0},"out":{"a":false},"d3":{"f":"cube", "d":"vertical", "z":"400", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"1"}},
						"verticalzoom":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"cube", "d":"vertical", "z":"600", "t":"40", "c":"#ccc", "e":"power2.inOut","su":"true"}},
						"horizontal":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"cube", "d":"horizontal", "z":"400", "c":"#ccc", "e":"power2.inOut","su":"true"}},
						"horizontalzoom":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"cube", "d":"horizontal", "t":"-45", "z":"450", "c":"#ccc", "e":"power2.inOut","su":"true"}}
					},
					"stageturn":{
						"multi":true,
						"vertical":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"incube", "d":"vertical", "z":"400", "c":"#ccc", "e":"power2.inOut"}},
						"horizontal":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"incube", "d":"horizontal", "z":"400", "c":"#ccc", "e":"power2.inOut"}}
					},
					"dynamictoss":{	
						"multi":true,					
						"vertical":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"fly", "d":"vertical", "z":"400", "c":"#ccc", "e":"power2.out", "fdi":"1.5","fdo":"1.5", "fz":"10","su":"true"}},
						"verticalzoom":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"fly", "d":"vertical", "z":"700", "c":"#ccc", "e":"power2.out","t":"-40", "fdi":"1.5","fdo":"1.5", "fz":"10","su":"true"}},
						"horizontal":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"fly", "d":"horizontal", "z":"400", "c":"#ccc", "e":"power2.out", "fdi":"1.5", "fdo":"1.5", "fz":"10","su":"true"}},
						"horizontalzoom":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"fly", "d":"horizontal", "z":"650", "c":"#ccc", "e":"power2.out", "t":"20", "fdi":"1.5", "fdo":"1.5", "fz":"10","su":"true"}}
					},
					"slideflip":{
						"multi":true,
						"horizontalsimple":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"turn", "d":"horizontal","su":"true"}},
						"easeback":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"turn", "d":"horizontal", "e":"back.out","su":"true"}},
						"verticalsimple":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"turn", "d":"vertical","su":"true"}},
						"bounce":{"speed":"2000","in":{"o":0},"out":{"a":false},"d3":{"f":"turn", "d":"vertical", "e":"BounceStrong","su":"true"}}
					}
				}
			},
			"splits":{ 
				"icon":"grid",
				"name":"Splits",
				"fade":{
					"simplefade":{
						"multi":true,
						"simple":{ "d":"10","in":{"o":0,"sx":1.1,"sy":1.1,"m":true,"row":6,"col":6},"out":{"a":false},"f":"oppslidebased"},
						"horizontal":{ "in":{"o":0,"sx":1.1,"sy":1.1,"m":true,"row":5,"col":5},"out":{"a":false},"f":"random"},
						"dark":{ "p":"dark", "d":"20","in":{"o":0,"sx":1.1,"sy":1.1,"m":true,"row":6,"col":6},"out":{"a":false},"f":"oppslidebased"}
					},
					"zoomandfade":{
						"multi":true,
						"mhorizontal":{"f":"edges","d":"15","speed":"1000","in":{"o":0,"r":"[-10,10]","sx":"0.1","sy":"0.1","row":8,"col":8,"x":"[-10,10]","y":"[-10,10]"}},
						"horizontal":{"f":"center","d":"15","speed":"1000","in":{"o":0,"r":"[-10,10]","sx":"0.1","sy":"0.1","row":8,"col":8,"x":"[-10,10]","y":"[-10,10]"}},
						"vertical":{"f":"edges","d":"15","speed":"1000","in":{"o":0,"r":"[-10,10]","sx":"0.1","sy":"0.1","row":20,"col":20,"x":"[-10,10]","y":"[-10,10]"}},
						"varyvertical":{"f":"center","d":"15","speed":"1000","in":{"o":0,"r":"[-10,10]","sx":"0.1","sy":"0.1","row":20,"col":20,"x":"[-10,10]","y":"[-10,10]"}}
					},
					"crossfade":{
						"multi":true,
						"up":{ "in":{"o":0,"sx":0,"sy":0,"row":5,"col":5},"out":{"a":false},"f":"nodelay"},						
						"down":{"d":"30", "f":"center", "in":{"o":0,"sx":1.2,"sy":1.2,"row":5,"col":5},"out":{"a":false,"o":0,"sx":0.5,"sy":0.5,"m":true,"row":5,"col":5}},
						"left":{"d":"30", "f":"center", "in":{"o":"-0.3","sx":0.5,"sy":0.5,"row":5,"col":5},"out":{"a":false,"o":0,"sx":1.3,"sy":1.3,"m":true,"row":5,"col":5}},
						"right":{"f":"edges","d":"10","speed":"910","in":{"o":0,"sx":"4","sy":"4","row":20,"col":20,"x":"[-10,10]","y":"[-10,10]"}},
						"vertical":{"f":"center","d":"10","speed":"910","in":{"o":0,"sx":"4","sy":"4","row":20,"col":20,"x":"[-10,10]","y":"[-10,10]"}},						
						"horizontal": {"speed":"1500", "f":"edges", "d":"20", "in":{"col":"17","row":"17", "e":"power2.inOut", "r":"[20,10,8,5,2,1,2,-1,-2,-5,-8,-10,-20]", "x":"[20,10,8,5,2,1,2,-1,-2,-5,-8,-10,-20]", "y":"[20,10,8,5,2,1,2,-1,-2,-5,-8,-10,-20]", "m":"true", "sx":"[8,7,6,4,3,2,1.3,2,3,4,6,7,8]", "sy":"[8,7,6,4,3,2,1.3,2,3,4,6,7,8]", "o":"0"},"out":{"a":false,"o":"0"}}
					}	
				},
				"slide":{
					"horizontalcols":{
						"multi":true,
						"horizontal":{ "in":{"x":"(100%)","m":true,"col":5},"f":"nodelay"},
						"lefttime":{"in":{"x":"100%","m":true,"col":5},"f":"slidebased"},
						"right":{"in":{"x":"-100%","m":true,"col":5},"f":"nodelay"},
						"darktime":{"p":"dark","in":{"x":"(100%)","m":true,"col":5},"f":"slidebased"},
						"lighttime":{"p":"light","in":{"x":"(100%)","m":true,"col":5},"f":"nodelay"}						
					},
					"verticalcols":{
						"multi":true,
						"vertical":{"p":"transparent","in":{"y":"(100%)","m":true,"col":5},"f":"slidebased"},
						"verticaldark":{"p":"dark","in":{"y":"(100%)","m":true,"col":5},"f":"slidebased"},
						"varyvertical":{"in":{"y":"[100%,-100%]","m":true,"col":8},"f":"slidebased"},
						"varyvdark":{"p":"dark", "in":{"y":"[100%,-100%]","m":true,"col":8},"f":"slidebased"}						
					},
					"curtain":{
						"multi":true,
						"right":{"in":{"y":"(-100%)","col":5}},
						"left":{"in":{"y":"(-100%)","col":5},"f":"end"},
						"horizontal":{"in":{"y":"(-100%)","col":5},"f":"center"},
						"mhorizontal":{"in":{"y":"(-100%)","col":5},"f":"edges"},
						"cross":{"in":{"y":"(-100%)","col":5},"f":"random"}						
					},
					"horizontalrows":{
						"multi":true,
						"horizontal":{"in":{"x":"(100%)","m":true,"row":5},"f":"slidebased"},
						"varyhorizontal":{"in":{"x":"[100%,-100%]","m":true,"row":8},"f":"slidebased"}
					},
					
					"verticalrows":{
						"multi":true,
						"vertical":{"in":{"y":"(100%)","m":true,"row":5},"f":"nodelay"},
						"verticaltime":{"in":{"y":"(100%)","m":true,"row":5},"f":"slidebased"}						
					},
					"slidingboxes":{
						"multi":true,
						"left":{"d":"20","f":"slidebased", "in":{"o":"-0.5", "x":"(15%)","sy":"0.8","sx":"0.8", "row":5,"col":5},"out":{"o":"0.5",  "x":"(-15%)","sy":"0.8","sx":"0.8", "row":5,"col":5}},
						"up":{"d":"20","f":"slidebased", "in":{"o":"-0.5", "y":"(15%)", "sy":"0.8","sx":"0.8", "row":5,"col":5},"out":{"o":"0.5", "y":"(-15%)", "sy":"0.8","sx":"0.8", "row":5,"col":5}},
						"leftdark":{"d":"20","p":"dark", "f":"slidebased", "in":{"o":"0", "x":"(15%)","sy":"0.8","sx":"0.8", "row":5,"col":5, "e":"power2.out"},"out":{"a":false,"o":"0",  "x":"(-15%)","sy":"0.8","sx":"0.8", "row":5,"col":5,"e":"power2.in"}},
						"uplight":{"d":"20","p":"light","f":"slidebased", "in":{"o":"0", "y":"(15%)", "sy":"0.8","sx":"0.8", "row":5,"col":5,"e":"power2.out"},"out":{"a":false,"o":"0", "y":"(-15%)", "sy":"0.8","sx":"0.8", "row":5,"col":5,"e":"power2.in"}},
						"cross":{"d":"20","f":"slidebased", "in":{"o":"-0.5", "y":"(15%)", "x":"(15%)","sy":"0.8","sx":"0.8", "row":5,"col":5},"out":{"a":false,"o":"0.5", "y":"(-15%)", "x":"(-15%)","sy":"0.8","sx":"0.8", "row":5,"col":5}}						
					},
					"slidingandzoomingboxes":{
						"multi":true,
						"vertical":{"d":"20", "in":{"o":0,"m":"true", "y":"(100%)","sy":"2","sx":"2", "row":5,"col":5},"f":"center"},
						"horizontal":{"d":"20", "in":{"o":0,"m":"true", "x":"(100%)","sy":"2","sx":"2", "row":5,"col":5},"f":"center"},
						"masked":{"d":"20", "in":{"o":0,"m":"true", "y":"(50%)", "x":"(50%)","sy":"2","sx":"2", "row":5,"col":5},"f":"center"}
					},
					"flyingboxes":{
						"multi":true,
						"horizontal":{"speed":"1000","in":{"o":"0","mou":true,"mo":"45","r":"{-100,100}","x":"(100%)","y":"{-100,100}","sx":"{0,2}","sy":"{0,2}","row":7,"col":7,"e":"power3.out"},"out":{"a":false},"f":"slidebased","d":"10"},
						"vertical":{"speed":"1000","in":{"o":"0","mou":true,"mo":"45","r":"{-100,100}","y":"(100%)","x":"{-100,100}","sx":"{0,2}","sy":"{0,2}","row":7,"col":7,"e":"power3.out"},"out":{"a":false},"f":"slidebased","d":"10"}
					}
					
				},
				"zoom":{
					"columns":{									
						"multi":true,
						"zvertical":{"f":"nodelay", "filter":{"u":true, "b":"2", "e":"default"}, "in":{"col":"6", "e":"power2.inOut", "m":"true", "sx":"1.5", "sy":"1.2", "o":"0"}},
						"zdown":{"f":"start", "d":"50", "filter":{"u":true, "b":"2", "e":"default"}, "in":{"col":"6", "e":"power2.inOut", "m":"true", "x":"(-20%)", "y":"(-20%)","sx":"1.5", "sy":"1.5", "o":"0"}},
						"right":{"speed":"500", "in":{"x":"(-50%)", "sx":"0.7","sy":"0.7","o":"0","m":true,"col":6,"e":"power4.inOut"},"out":{"a":false},"f":"slidebased","d":"10"},
						"cross":{ "speed":"800", "f":"random", "d":"10", "in":{"col":"7", "e":"power2.inOut", "r":"[-5,-3,-10,-5,-2,0,3,10,8,5]", "m":"true", "sx":"2", "sy":"2", "o":"0"}}
						
						
					},
					"rows":{
						"multi":true,
						"zhorizontal":{ "f":"nodelay", "filter":{"u":true, "b":"2", "e":"default"}, "in":{"row":"6", "e":"power2.inOut", "m":"true", "sx":"1.2", "sy":"1.5", "o":"0"}},
						"zright":{ "f":"start", "d":"50", "filter":{"u":true, "b":"2", "e":"default"}, "in":{"row":"6", "e":"power2.inOut", "m":"true", "x":"(-20%)", "y":"(-20%)","sx":"1.5", "sy":"1.5", "o":"0"}},
						"down":{ "speed":"500", "in":{"y":"(-50%)", "sx":"0.7","sy":"0.7","o":"0","m":true,"row":5,"e":"power4.inOut"},"out":{"a":false},"f":"slidebased","d":"10"},
						"cross":{ "speed":"800", "f":"random", "d":"10", "in":{"row":"7", "e":"power2.inOut", "r":"[-5,-3,-10,-5,-2,0,3,10,8,5]", "m":"true", "sx":"2", "sy":"2", "o":"0"}}
					}					
				},
				"rotation":{
					"columns":{
						"multi":true,
						"left":{"speed":"1500", "f":"center", "d":"100", "in":{"col":"7", "e":"power2.inOut", "r":"[10,6,3,0,-3,-6,-10]", "m":"true", "sx":"1.5", "sy":"1.2", "o":"0"}},
						"right":{ "speed":"600", "f":"center", "d":"10", "p":"light", "in":{"col":"50", "e":"power2.inOut", "r":"10", "sx":"1.5", "sy":"1.5", "o":"0"}},
						"vertical":{ "speed":"600", "f":"random", "d":"10", "in":{"mou":true, "mo":"45", "col":"20", "e":"power2.inOut", "r":"{-45,45}", "sx":"0.8", "sy":"0.8", "o":"0", "y":"(100%)"}},
						"horizontal":{"speed":"600", "f":"slidebased", "d":"10", "in":{"mou":true, "mo":"45", "col":"20", "e":"power2.inOut", "r":"{-45,45}", "sx":"0.8", "sy":"0.8", "o":"0", "x":"(100%)"}},
						"cross":{ "speed":"1300", "f":"edges", "d":"15", "in":{"mou":true, "mo":"35", "col":"100", "e":"sine.inOut", "r":"180", "o":"0", "x":"{-20,20}","y":"{-20,20}"},"out":{"a":false}}						
					},
					"rows":{
						"multi":true,
						"cross":{ "speed":"1500", "f":"center", "d":"100", "in":{"row":"7", "e":"power2.inOut", "r":"[10,6,3,0,-3,-6,-10]", "m":"true", "sx":"1.2", "sy":"1.5", "o":"0"}},
						"up":	{"speed":"800", "f":"random", "d":"10", "in":{"row":"7", "e":"power2.inOut", "r":"[-5,-3,-10,-5,-2,0,3,10,8,5]", "m":"true", "sx":"2", "sy":"2", "o":"0"}},
						"down":{ "speed":"600", "f":"center", "d":"10", "p":"dark", "in":{"row":"50", "e":"power2.inOut", "r":"10", "sx":"1.5", "sy":"1.5", "o":"0"}},
						"left":{ "speed":"600", "f":"random", "d":"10", "in":{"mou":true, "mo":"45", "row":"20", "e":"power2.inOut", "r":"{-45,45}", "sx":"0.8", "sy":"0.8", "o":"0", "x":"(100%)"}},
						"right":{ "speed":"600", "f":"slidebased", "d":"10", "in":{"mou":true, "mo":"45", "row":"20", "e":"power2.inOut", "r":"{-45,45}", "sx":"0.8", "sy":"0.8", "o":"0", "y":"(100%)"}},
						"vary":{ "speed":"1000", "f":"edges", "d":"10", "in":{"mou":true, "mo":"35", "row":"25", "e":"sine.in", "r":"{-40,40}", "sx":"2", "sy":"2", "o":"0", "y":"{-20,20}","x":"{-20,20}"},"out":{"a":false}}						
					},
					"boxes":{
						"multi":true,
						"left":{"in":{"o":0,"r":"{-45,45}","sx":0,"sy":0,"row":5,"col":5},"out":{"a":false},"f":"random"},
						"cross":{"speed":"1300", "in":{"o":0,"r":"120","x":"{-20,20}", "y":"{-20,20}","sx":10,"sy":10,"row":5,"col":5,"e":"expo.inOut"},"out":{"a":false},"f":"slidebased","d":"20"}
					}					
				},							
				"3d":{
					"columns":{
						"multi":true,
						"horizontal":{"speed":"2000", "in":{"col":"8","o":"0","e":"power2.inOut"},"f":"slidebased","d":"35", "d3":{"f":"cube", "d":"horizontal", "rm":"board", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"left":{"speed":"2000", "in":{"col":"8","o":"0","e":"power2.inOut"},"f":"end","d":"35", "d3":{"f":"cube", "d":"horizontal", "rm":"board", "rd":"1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"right":{"speed":"2000", "in":{"col":"8","o":"0","e":"power2.inOut"},"f":"start","d":"35", "d3":{"f":"cube", "d":"horizontal", "rm":"board", "rd":"-1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"vertical":{"speed":"2000", "in":{"col":"8","o":"0","e":"power2.inOut"},"f":"slidebased","d":"35", "d3":{"f":"cube", "d":"vertical", "rm":"rubik", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"up":{"speed":"2000", "in":{"col":"8","o":"0","e":"power2.inOut"},"f":"start","d":"35", "d3":{"f":"cube", "d":"vertical", "rm":"rubik", "rd":"1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"down":{"speed":"2000", "in":{"col":"8","o":"0","e":"power2.inOut"},"f":"end","d":"35", "d3":{"f":"cube", "d":"vertical", "rm":"rubik", "rd":"-1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}}
					},
					"rows":{
						"multi":true,
						"vertical":{"speed":"2000", "in":{"row":"8","o":"0","e":"power2.inOut"},"f":"slidebased","d":"35", "d3":{"f":"cube", "d":"vertical", "rm":"board", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"up":{"speed":"2000", "in":{"row":"8","o":"0","e":"power2.inOut"},"f":"start","d":"35", "d3":{"f":"cube", "d":"vertical", "rm":"board", "rd":"1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"down":{"speed":"2000", "in":{"row":"8","o":"0","e":"power2.inOut"},"f":"end","d":"35", "d3":{"f":"cube", "d":"vertical", "rm":"board", "rd":"-1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"horizontal":{"speed":"2000", "in":{"row":"8","o":"0","e":"power2.inOut"},"f":"slidebased","d":"35", "d3":{"f":"cube", "d":"horizontal", "rm":"rubik", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"left":{"speed":"2000", "in":{"row":"8","o":"0","e":"power2.inOut"},"f":"start","d":"35", "d3":{"f":"cube", "d":"horizontal", "rm":"rubik", "rd":"1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"right":{"speed":"2000", "in":{"row":"8","o":"0","e":"power2.inOut"},"f":"end","d":"35", "d3":{"f":"cube", "d":"horizontal", "rm":"rubik", "rd":"-1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}}
					},
					"boxes":{
						"multi":true,
						"vertical":{"speed":"1800", "in":{"col":"6","row":"6","o":"0","e":"power2.inOut"},"f":"slidebased","d":"50", "d3":{"f":"cube", "d":"vertical", "rm":"board", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"up":{"speed":"1800", "in":{"col":"6","row":"6","o":"0","e":"power2.inOut"},"f":"start","d":"50", "d3":{"f":"cube", "d":"vertical", "rm":"board", "rd":"1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"down":{"speed":"1800", "in":{"col":"6","row":"6","o":"0","e":"power2.inOut"},"f":"end","d":"50", "d3":{"f":"cube", "d":"vertical", "rm":"board", "rd":"-1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"horizontal":{"speed":"1800", "in":{"col":"6","row":"6","o":"0","e":"power2.inOut"},"f":"slidebased","d":"50", "d3":{"f":"cube", "d":"horizontal", "rm":"board", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"left":{"speed":"1800", "in":{"col":"6","row":"6","o":"0","e":"power2.inOut"},"f":"end","d":"50", "d3":{"f":"cube", "d":"horizontal", "rm":"board", "rd":"1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"right":{"speed":"1800", "in":{"col":"6","row":"6","o":"0","e":"power2.inOut"},"f":"start","d":"50", "d3":{"f":"cube", "d":"horizontal", "rm":"board", "rd":"-1", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"crossin":{"speed":"1800", "in":{"col":"6","row":"6","o":"0","e":"power2.inOut"},"f":"center","d":"50", "d3":{"f":"cube", "d":"vertical", "rm":"rubik", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"crossout":{"speed":"1800", "in":{"col":"6","row":"6","o":"0","e":"power2.inOut"},"f":"edges","d":"50", "d3":{"f":"cube", "d":"horizontal", "rm":"rubik", "z":"450", "t":"20", "c":"#ccc", "e":"power2.inOut","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}}
					},
					"rain":{
						"multi":true,
						"vertical":{"speed":"1500", "f":"random", "d":"60",  "in":{"col":"12", "row":"7", "o":"0", "e":"power2.out"},"d3":{"f":"cube", "d":"vertical", "rm":"drop", "z":"450", "t":"12", "c":"#ccc", "e":"power3.out","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}},
						"horizontal":{"speed":"1500", "f":"random", "d":"60",   "in":{"col":"12", "row":"7", "o":"0", "e":"power2.out"},"d3":{"f":"cube", "d":"horizontal", "rm":"drop", "z":"450", "t":"12", "c":"#ccc", "e":"power3.out","su":"true", "smi":"0", "sma":"0.5","sc":"#9e9e9e"}}
					}
				},
				"effects":{ 	
					"cuteffects":{
						"multi":true,
						"horizontal":{"o":"outin","speed":"1500","f":"nodelay","in":{"e":"power2.out","y":"(15%)", "x":"(-10%)","r":"20","sx":"0.7","sy":"0.7"},"out":{"a":false,"col":"2","e":"power2.inOut","x":"[-90%,170%]","y":"[(60%),(130%)]","r":"[(-30),(60)]","sx":"1.2","sy":"1.3"}},
						"vertical":{"o":"outin","speed":"1500","f":"nodelay","in":{"e":"power2.out","x":"(15%)", "y":"(-10%)","r":"20","sx":"0.7","sy":"0.7"},"out":{"a":false,"row":"2","e":"power2.inOut","x":"[(60%),(130%)]","y":"[-90%,170%]","r":"[(-30),(60)]","sx":"1.3","sy":"1.2"}},
						"varyhorizontal": {"speed":"1000", "f":"center", "d":"80","filter":{"u":true, "b":"3", "e":"late2"}, "in":{"col":"3","e":"power2.inOut",  "x":"[-100%,0,100%]",  "sx":"[1,0,1]", "sy":"[1,0,1]", "o":"0"}},						
						"varyvertical": { "speed":"1000", "f":"center", "d":"80","filter":{"u":true, "b":"3", "e":"late2"}, "in":{"row":"3","e":"power2.inOut",  "y":"[-100%,0,100%]",  "sx":"[1,0,1]", "sy":"[1,0,1]", "o":"0"}},
						"crossin":{"f":"start", "d":"40", "filter":{"u":true, "b":"3", "e":"default"}, "in":{"o":0,"e":"power2.inOut", "x":"[-100%,-100%,100%,100%]","y":"[-20%,20%,-20%,20%]", "r":"[-20,20,-20,20]","sx":0.5,"sy":0.5,"row":2,"col":2},"out":{"a":false,"e":"power2.inOut",  "o":0,"x":"[5%,5%,-5%,-5%]","y":"[4%,-4%,4%,-4%]","sx":0.8,"sy":0.8,"row":2,"col":2}},
						"left": {"title":"*repeat* Switch", "speed":"1000", "f":"center", "d":"80","filter":{"u":true, "b":"3", "e":"late2"}, "in":{"col":"3","row":"3", "e":"power2.inOut",  "x":"[-100%,0,100%]",  "sx":"[1,0,1]", "sy":"[1,0,1]", "o":"0"}},
						"right": {"title":"*repeat* Switch & Rotate", "speed":"800", "f":"start", "d":"40", "in":{"col":"3","row":"3", "r":"[(-180),0,(180)]","e":"back.out",  "x":"[-100%,0,100%]",  "sx":"[1,0,1]", "sy":"[1,0,1]", "o":"0"},"out":{"a":false, "col":"3","row":"3", "r":"[(-180),0,(180)]","e":"power3.inOut",  "x":"[100%,0,-100%]",  "sx":"[1,0.5,1]", "sy":"[1,0.5,1]", "o":"1"}}
					},
					"pulleffects":{																									
						"multi":true,
						"crossin":{"speed":"900", "f":"center", "d":"20",  "in":{"col":"400", "e":"power2.inOut", "sx":"4", "sy":"3", "o":"0","y":"(100%)","m":"true"}, "out":{"a":false, "col":"400","m":"true","y":"(-150%)","sx":"3", "sy":"3","e":"power2.inOut"}},					
						"crossout":{ "speed":"900", "f":"center", "d":"20",  "in":{"row":"400", "e":"power2.inOut", "sx":"3", "sy":"4", "o":"0","x":"(100%)","m":"true"}, "out":{"a":false, "row":"400","m":"true","x":"(-150%)","sx":"3", "sy":"3","e":"power2.inOut"}}						
					},																								
					"noiseeffects":{
						"multi":true,
						"vertical":{ "speed":"910", "f":"start", "d":"20",  "in":{"col":"100", "row":"10", "e":"power3.Out", "sx":"2", "sy":"2", "o":"0", "y":"{-200,200}"}},
						"horizontal":{ "speed":"910", "f":"start", "d":"20",   "in":{"col":"10", "row":"100", "e":"power3.Out", "sx":"2", "sy":"2", "o":"0", "x":"{-200,200}"}},
						"zhorizontal":{"f":"random","d":"40","p":"light", "speed":"1000","in":{"o":0,"sx":"5","r":"[(180),(-180),(90),(-90),(270),(-270)]","sy":"5","row":30,"col":30,"x":"{-10,100}","y":"{-50,50}","e":"power2.out"}, "out":{"a":false, "e":"power2.in", "o":0,"sx":"6","r":"[(-180),(180),(-90),(90),(-270),(270)]","sy":"6","row":30,"col":30,"x":"{-50,50}","y":"{-50,50}"}}					
					},										
					"dreameffects":{
						"multi":true,						
						"tiny":{"title":"*center_focus_strong* Get Focus","speed":"1000","in":{"o":"0","mou":true,"mo":"70","r":"{-40,40}","sx":"2","sy":"2","x":"{-20,20}","y":"{-20,20}","row":10,"col":10,"e":"circ.in"},"out":{"a":false},"f":"edges","d":"15"},
						"easy":{"title":"*waves* Ripples","speed":"1000","in":{"o":"0","mou":true,"mo":"70","r":"{-40,40}","sx":"2","sy":"2","x":"{-20,20}","y":"{-20,20}","row":20,"col":20,"e":"elastic.out"},"out":{"a":false},"f":"center","d":"15"},
						"medium":{"title":"*waves* Double Ripples","speed":"1000","in":{"o":"0","r":"{-40,40}","sx":"2","sy":"2","x":"{-20,20}","y":"{-20,20}","row":20,"col":20,"e":"bounce.in"},"out":{"a":false},"f":"center","d":"15"},
						"strong":{"title":"*waves* Bounced Ripples","speed":"1000","mou":true,"mo":"40","in":{"o":"0","r":"[-10,10,-20,20,-30,30]","sx":"[2,4]","sy":"[2,4]","x":"[-10,10,-20,20,-30,30]","y":"[-10,10,-20,20,-30,30]","row":20,"col":20,"e":"BounceExtrem"},"out":{"a":false},"f":"center","d":"15"}
					}
					
					
				}			
				
			},
			"random":{ 
				"icon":"random",
				"name":"Random",
				"noSubLevel":"true",
				"rndany":	{"title":"*done_all* Random All","random":"true","rndmain":"all"},
				"rndbasic":	{"title":"*aspect_ratio* Random Base","random":"true","rndmain":"basic"},
				"rndrow":	{"title":"*line_weight* Random Row","random":"true","rndmain":"rows"},
				"rndcolumns":	{"title":"*view_week* Random Column","random":"true","rndmain":"columns"},
				"rndboxes":	{"title":"*apps* Random Box","random":"true","rndmain":"boxes"},
				"rndfade":	{"title":"*opacity* Random Fade","random":"true","rndmain":"all","rndgrp":"fade"},
				"rndslide":	{"title":"*open_with* Random Slide","random":"true","rndmain":"all","rndgrp":"slide,curtain,slideover,remove,slideinout,slideinoutfadein,slideinoutfadeinout,parallax,double"},
				"rndzoom":	{"title":"*add* Random Zoom","random":"true","rndmain":"all","rndgrp":"zoom,zoomslidein,zoomslideout"},
				"rndrotation":	{"title":"*rotate_left* Random Rotation","random":"true","rndmain":"all","rndgrp":"rotation"},
				"rndeffects":	{"title":"*3d_rotation* Random Effects","random":"true","rndmain":"all","rndgrp":"effects,circle,filter"}
			}

		}';

		$transitions = apply_filters('revslider_data_get_base_transitions', $transitions);

		return ($raw) ? $transitions : json_decode($transitions, true);
	}
	
	/**
	 * HTML tags a layer may be rendered as - anything else is replaced by the layer's default
	 * @return array
	 */
	public function get_allowed_layer_tags(){
		return ['sr7-layer', 'rs-layer', 'a', 'div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'label'];
	}

	/**
	 * @param array $layer
	 * @param string $tag_key
	 * @param string $default
	 * @return array
	 */
	public function filter_single_layer_tags($layer, $tag_key, $default){
		$allowed_tags = $this->get_allowed_layer_tags();
		if(!empty($layer[$tag_key]) && !in_array($layer[$tag_key], $allowed_tags)){
			$layer[$tag_key] = $default;
		}
		return $layer;
	}
}
