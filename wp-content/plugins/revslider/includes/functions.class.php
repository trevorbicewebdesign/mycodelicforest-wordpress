<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Shared helper base class - almost every class in the plugin extends it.
 *
 * Provides the plugin's option access (get_options/update_option, with the fonts branch split into its own
 * non-autoloaded option), the get_val()/set_val() array accessors used instead of isset() chains
 * throughout the codebase, request variable readers, and a grab bag of image, post type and string
 * utilities.
 */
class RevSliderFunctions extends RevSliderData {

	/**
	 * separate options field for the fonts branch. it holds the downloaded google font css + the collected font
	 * list (~650KB) and is only needed while rendering or managing fonts - keeping it inside the autoloaded
	 * 'sr-options' meant WordPress loaded and unserialized all of it on *every* request.
	 */
	const OPTIONS_FONTS = 'sr-options-fonts';

	public function __construct(){
		parent::__construct();
	}

	/**
	 * is the given file extension on the import blocklist ($SR_GLOBALS['bad_extensions'])?
	 *
	 * this is called once per file inside an imported zip, where in_array() over the ~70 entry list is a linear
	 * scan every time. the flipped map is built once and only rebuilt when the list itself changed, so an addon
	 * that adds its own extensions at runtime still takes effect.
	 *
	 * @param string $extension without the dot, case insensitive
	 * @return bool
	 */
	public function is_bad_extension($extension){
		global $SR_GLOBALS;

		static $map	  = null;
		static $count = -1;

		$list = (isset($SR_GLOBALS['bad_extensions']) && is_array($SR_GLOBALS['bad_extensions'])) ? $SR_GLOBALS['bad_extensions'] : [];
		if($map === null || $count !== count($list)){
			$map   = array_flip(array_map('strtolower', $list));
			$count = count($list);
		}

		return isset($map[strtolower((string)$extension)]);
	}

	/**
	 * raise the PHP runtime limits for a long running job (import, version upgrade, image processing).
	 *
	 * plain ini_set()/set_time_limit() calls are a problem on hardened/managed hosting: both are regularly
	 * listed in disable_functions, where the bare call emits a PHP warning that lands in the middle of an
	 * AJAX/JSON response body and breaks the client side JSON.parse(). this wrapper
	 *  - checks that the function is actually callable before using it,
	 *  - never lowers a limit the host set higher,
	 *  - leaves an unlimited limit (0 for max_execution_time, -1 for memory_limit) alone.
	 *
	 * @param int    $seconds seconds of runtime that should be available from now on, 0 to skip
	 * @param string $memory  memory_limit to request, e.g. '1G', '' to skip
	 * @return bool
	 */
	public function raise_runtime_limits($seconds = 0, $memory = ''){
		if(!function_exists('ini_get')) return false;

		if($seconds > 0){
			$cur = (int)ini_get('max_execution_time');
			if($cur > 0){ //0 means "no limit" (usually CLI) - there is nothing to raise
				//max() so the timer is reset without ever shortening a longer host limit
				$want = max((int)$seconds, $cur);
				if(function_exists('set_time_limit')){
					@set_time_limit($want);
				}elseif(function_exists('ini_set')){
					@ini_set('max_execution_time', (string)$want);
				}
			}
		}

		if($memory !== '' && function_exists('ini_set')){
			$cur  = wp_convert_hr_to_bytes((string)ini_get('memory_limit'));
			$want = wp_convert_hr_to_bytes($memory);
			if($cur > 0 && $want > $cur) @ini_set('memory_limit', $memory); //<= 0 means unlimited
		}

		return true;
	}

	/**
	 * is current user can NOT perform action
	 *
	 * @param string $role
	 * @return bool
	 */
	public function current_user_can_not($role = ''){
		/* @var $sr_admin RevSliderAdmin */
		$sr_admin = RevSliderGlobals::instance()->get('RevSliderAdmin');
		if(is_null($sr_admin)) return true;
		
		$role = $role ?: $sr_admin->get_user_role();
		return !current_user_can($role) && apply_filters('revslider_restrict_role', true);
	}

	/**
	 * attempt to load cache for _get_global_settings
	 * @return mixed
	 */
	public function get_global_settings(){
		return $this->get_wp_cache('_get_global_settings');
	}

	/**
	 * Get Global Settings
	 **/
	protected function _get_global_settings(){
		$gs = $this->get_options(['system', 'settings'], '');

		if(!is_array($gs)){
			//settings are stored as a JSON string and get_global_settings() is called ~20+ times per editor
			//request, where get_wp_cache is is_admin-gated and would re-decode the full blob every call.
			//Memoize the decode against the last raw value so it auto-busts when settings are saved.
			static $last_raw = null, $last_decoded = null;
			if($gs !== $last_raw){
				$last_raw	  = $gs;
				$last_decoded = json_decode($gs, true);
			}
			$gs = $last_decoded;
		}

		return apply_filters('rs_get_global_settings', $gs);
	}

	/**
	 * update general settings
	 * stored JSON encoded in the system.settings option
	 * @param bool $merge true merges recursively into the existing settings instead of replacing them
	 * @return bool
	 */
	public function set_global_settings($global, $merge = false){
		$this->delete_wp_cache('_get_global_settings');
		if($this->_truefalse($merge) === true){
			$_global = $this->get_global_settings();
			if(!is_array($_global)) $_global = [];
			if(!is_array($global)) $global = [];
			$global = array_replace_recursive($_global, $global);
		}
		
		$global = json_encode($global);
		
		$this->update_option(['system', 'settings'], $global);
		
		return true;
	}


	/**
	 * get all additions from the update checks
	 * extra payload the update server sends along, e.g. notices and feature flags
	 * @since: 6.2.0
	 * @param string $key optional single key instead of the whole array
	 * @return mixed
	 **/
	public function get_addition($key = ''){
		$additions = (array)$this->get_options(['system', 'additions'], []);
		$additions = (!is_array($additions)) ? json_decode($additions, true) : $additions;
		
		return (empty($key)) ? $additions : $this->get_val($additions, $key);
	}


	/**
	 * throw an error
	 * @return void
	 * @throws Exception always
	 **/
	public function throw_error($message, $code = null){
		if(!empty($code)) throw new Exception($message, $code);

		throw new Exception($message);
	}


	/**
	 * get value from array. if not - return alternative
	 * 
	 * @param mixed $arr  could be array | object | scalar 
	 * @param mixed $key  could be array | string
	 * @param mixed $default  value to return if key not found
	 * @return mixed  
	 */
	public function get_val($arr, $key, $default = ''){
		//scalar =  int, float, string и bool
		if(is_scalar($arr)) return $default;
		//convert obj to array 
		if(is_object($arr)) $arr = (array)$arr;
		//if key is string, check immediately 
		if(!is_array($key)) return (isset($arr[$key])) ? $arr[$key] : $default;
		//loop thru keys
		foreach($key as $v){
			if(is_object($arr)) $arr = (array)$arr;
			if(isset($arr[$v])) {
				$arr = $arr[$v];
			} else {
				return $default;
			}
		}
		return $arr;
	}

	
	/**
	 * set parameter
	 * counterpart to get_val(): writes into a nested array/object, creating missing levels on the way
	 * @param mixed        $base  modified by reference
	 * @param array|string $name  a single key, or a path of keys
	 * @since: 6.0
	 * @return void
	 */
	public function set_val(&$base, $name, $value){
		if(is_array($name)){
			foreach($name as $key){
				if(is_array($base)){
					if(!isset($base[$key])) $base[$key] = [];
					$base = &$base[$key];
				}elseif(is_object($base)){
					if(!isset($base->$key)) $base->$key = new stdClass();
					$base = &$base->$key;
				}
			}
			$base = $value;
		}else{
			$base[$name] = $value;
		}
	}
	
	/**
	 * get POST variable
	 * @param bool $esc run the value through esc_html()
	 * @return mixed
	 */
	public function get_post_var($key, $default = '', $esc = true){
		$val = $this->get_var($_POST, $key, $default);
		return ($esc) ? esc_html($val) : $val;
	}
	
	/**
	 * get GET variable
	 * @param bool $esc run the value through esc_html()
	 * @return mixed
	 */
	public function get_get_var($key, $default = '', $esc = true){
		$val = $this->get_var($_GET, $key, $default);
		return ($esc) ? esc_html($val) : $val;
	}
	
	/**
	 * get POST or GET variable in this order
	 * @param bool $esc run the value through esc_html()
	 * @return mixed
	 */
	public function get_request_var($key, $default = '', $esc = true){
		$val = (array_key_exists($key, $_POST)) ? $this->get_var($_POST, $key, $default) : $this->get_var($_GET, $key, $default);
		return ($esc) ? esc_html($val) : $val;
	}
	
	/**
	 * get a variable from an array,
	 * @return mixed
	 */
	public function get_var($arr, $key, $default = ''){
		return (isset($arr[$key])) ? $arr[$key] : $default;
	}
	
	/**
	 * check for true and false in all possible ways
	 * settings arrive as 'true'/'on'/1/'1' from very different places, so comparisons go through here
	 * @since: 6.0
	 * @return mixed bool when recognised, otherwise the input unchanged
	 **/
	public function _truefalse($v){
		//fast path: already a bool (the most common case) - true => true, false => false
		if(is_bool($v)) return $v;
		//static so the lookup arrays are built once, not on every call
		static $false_vals = ['false', 'off', NULL, 0, -1, "0"];
		static $true_vals  = ['true', 'on', 1, "1"];
		if(in_array($v, $false_vals, true)) return false;
		if(in_array($v, $true_vals, true)) return true;

		return $v;
	}
	
	/**
	 * validate that some value is numeric
	 * @param string $fn field name for the error message
	 * @return void
	 * @throws Exception when the value is empty or not numeric
	 */
	public function validate_numeric($val, $fn = 'Field'){
		$this->validate_not_empty($val, $fn);
		
		if(!is_numeric($val)) $this->throw_error($fn.__(' needs to be numeric', 'revslider'));
	}
	
	/**
	 * validate that some variable not empty
	 * @param string $fn field name for the error message
	 * @return void
	 * @throws Exception when the value is empty (0 counts as filled)
	 */
	public function validate_not_empty($val, $fn = 'Field'){
		if(empty($val) && is_numeric($val) == false) $this->throw_error($fn.__(' needs to not be empty', 'revslider'));
	}
	
	/**
	 * encode array into json for client side
	 * returns the JSON already quoted and slash escaped, ready to be printed into a JS literal
	 * @return string the encoded value, '{}' when there is nothing to encode
	 */
	public function json_encode_client_side($arr, $options = 0){
		if(empty($arr)) return '{}';

		if(defined('JSON_INVALID_UTF8_IGNORE')) $options |= JSON_INVALID_UTF8_IGNORE;
		$json = json_encode($arr, $options);
		$json = (!empty($json)) ? addslashes($json) : $json;
		
		return (empty($json)) ? '{}' : "'".$json."'";
	}
	
	
	/**
	 * turn a string into an array, check also for slashes!
	 * tries the unslashed string first, since values reach us slashed from different layers
	 * @since: 6.0
	 * @return mixed the decoded value, or the input unchanged when it is not a string
	 */
	public function json_decode_slashes($data){
		if(gettype($data) !== 'string') return $data;

		$data_decoded = json_decode(stripslashes($data), true);
		return (empty($data_decoded)) ? json_decode($data, true) : $data_decoded;
	}

	/**
	 * maybe_unserialize() for *untrusted* payloads such as remote HTTP response bodies.
	 * keeps WordPress' pass-through behaviour for plain (non serialized) data, but never lets the payload
	 * instantiate arbitrary classes: an unrestricted unserialize() on a remote body is a PHP object
	 * injection surface (gadget chains via __wakeup()/__destruct()).
	 *
	 * @param mixed       $data     raw body
	 * @param array|false $allowed  classes the payload may instantiate, false = none at all
	 * @return mixed
	 */
	public function maybe_unserialize_safe($data, $allowed = false){
		if(!is_string($data) || !is_serialized($data)) return $data;

		return @unserialize(trim($data), ['allowed_classes' => $allowed]);
	}

	
	/**
	 * Convert std class to array, with all sons
	 * @return array|null
	 */
	public function class_to_array($arr){
		return json_decode(json_encode($arr), true);
	}
	
	/**
	 * Convert std class to array, single
	 * @return array
	 */
	public function class_to_array_single($arr){
		return (array)$arr;
	}
	
	/**
	 * Check Array for Value Recursive
	 */
	public function in_array_r($needle, $haystack, $strict = false){
		if(!is_array($haystack) || empty($haystack)) return false;

		foreach($haystack ?? [] as $item){
			if(($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict)))	return true;
		}
	
		return false;
	}
	
	/**
	 * compress an array/object/string to a string
	 * used for the big cached catalogues (templates, object library) before they go into an option
	 * @since 6.6.0
	 * @return string base64 encoded gzip, or the plain JSON when zlib is unavailable
	 **/
	public function do_compress($data, $level = 9){
		if(is_array($data) || is_object($data)) $data = json_encode($data);
		
		if(!function_exists('gzcompress') || !function_exists('gzuncompress')) return $data; //gzencode / gzdecode

		return base64_encode(gzcompress($data, $level));
	}

	/**
	 * decompress an string to an array/object/string
	 * also accepts uncompressed JSON, so old option values keep working
	 * @since 6.6.0
	 * @return mixed
	 **/
	public function do_uncompress($data){
		if($data === false || empty($data) || is_array($data) || is_object($data)) return $data;

		//memoize per request: the same large compressed blob (e.g. the rs-templates catalog) is uncompressed
		//many times per request; gzuncompress + json_decode costs far more than hashing the input key
		static $cache = [];
		$ckey = md5($data);
		if(isset($cache[$ckey])) return $cache[$ckey];

		$_data = json_decode($data, true);
		if(is_array($_data) || is_object($_data)){ $cache[$ckey] = $_data; return $_data; }
		if(!function_exists('gzcompress') || !function_exists('gzuncompress')) return $data; //gzencode / gzdecode

		$raw	= gzuncompress(base64_decode($data));
		$_data	= json_decode($raw, true);
		$result	= (!empty($_data)) ? $_data : $raw;

		$cache[$ckey] = $result;
		return $result;
	}

	/**
	 * read the (large, static) admin icon sprite once per request - it is file_get_contents()'d from
	 * several places (editor/dashboard/markups views + the modal AJAX). NOTE: this only de-duplicates the
	 * disk read; the ~264KB are still inlined into the page. Cutting that page weight would need serving the
	 * sprite as a browser-cacheable external resource (an editor-JS change to the <use href> references).
	 * @return string
	 */
	public static function get_sprite_svg(){
		static $sprite_svg = null;
		if($sprite_svg === null) $sprite_svg = (string)file_get_contents(RS_PLUGIN_PATH . 'admin/assets/images/sprite.svg');
		return $sprite_svg;
	}

	/**
	 * get attachment image url
	 * @param string $size registered image size, 'full' for the original
	 * @return string|false
	 */
	public function get_url_attachment_image($id, $size = 'full'){
		//safety net for edge cases where WP core media functions are not loaded yet;
		//the function_exists guard avoids the redundant require_once stat on every call
		if(!function_exists('wp_get_attachment_image_src')){
			require_once(ABSPATH . 'wp-load.php');
			require_once(ABSPATH . 'wp-includes/pluggable.php');
		}
		$image	= wp_get_attachment_image_src($id, $size);
		$url	= (empty($image)) ? false : $this->get_val($image, 0);
		if($url === false) $url = wp_get_attachment_url($id);
		
		return $url;
	}


	/**
	 * gets a temporary path where files can be stored
	 *
	 * wp_mkdir_p() instead of mkdir($dir, 0777, true): 0777 makes the directory world writable, and imported
	 * archives are unpacked into it - on shared hosting every other account on the box could then write there.
	 * wp_mkdir_p() is recursive as well and applies FS_CHMOD_DIR (or the parent's permissions), which is what
	 * the rest of WordPress uses.
	 * @return string absolute path with a trailing slash; the first writable candidate wins
	 **/
	public function get_temp_path($path = 'rstemp'){
		if(function_exists('sys_get_temp_dir')){
			$temp = sys_get_temp_dir();
			if(@is_dir($temp) && wp_is_writable($temp)){
				$dir = trailingslashit($temp).$path.'/';
				if(!is_dir($dir)) wp_mkdir_p($dir);
				if(is_dir($dir) && wp_is_writable($dir)) return $dir;
			}
		}

		$temp = ini_get('upload_tmp_dir');
		if(@is_dir($temp) && wp_is_writable($temp)){
			$dir = trailingslashit($temp).$path.'/';
			if(!is_dir($dir)) wp_mkdir_p($dir);
			if(is_dir($dir) && wp_is_writable($dir)) return $dir;
		}

		$temp_dir = get_temp_dir();
		if(wp_is_writable($temp_dir)){
			$dir = trailingslashit($temp_dir).$path.'/';
			if(!is_dir($dir)) wp_mkdir_p($dir);
			if(is_dir($dir) && wp_is_writable($dir)) return $dir;
		}

		$upload_dir = wp_upload_dir();
		$dir		= $upload_dir['basedir'].'/'.$path.'/';
		if(!is_dir($dir)) wp_mkdir_p($dir);

		return $dir;
	}
	
	
	/**
	 * retrieve the image id from the given image url
	 * a "-800x600" size suffix is stripped first, so a thumbnail URL resolves to its original attachment
	 * @return int|false
	 */
	public function get_image_id_by_url($url){
		if($url === '') return false;

		//memoize per request - attachment_url_to_postid() runs an uncached postmeta query each call
		static $img_cache = [];
		if(isset($img_cache[$url])) return $img_cache[$url];

		$clean = preg_replace('/-\d+x\d+(?=\.\w{3,4}$)/', '', $url);
		$id	   = attachment_url_to_postid($clean);
		$id	   = (is_null($id) || $id === 0) ? false : $id; //fix for B-5855627275

		//only memoize positive hits - a "not found" must stay live so an attachment created later in the
		//same request (e.g. during import) is detected on the next lookup instead of returning a stale false
		if($id !== false) $img_cache[$url] = $id;
		return $id;
	}
	
	/**
	 * retrieve the image id from the given image filename/basename
	 * fallback for when the URL no longer matches, e.g. after a domain change
	 * @since: 6.1.5
	 * @return int|false
	 */
	public function get_image_id_by_basename($basename){
		global $wpdb;
		
		$var = $wpdb->get_var($wpdb->prepare("SELECT `post_id` FROM `".$wpdb->postmeta."` WHERE `meta_value` LIKE %s LIMIT 0,1", '%/'.$basename));
		
		return intval($var) ?: false;
	}
	
	/**
	 * get image url from image path.
	 * relative paths are prefixed with the upload base url, absolute ones are left alone
	 * @return string empty when the path is not a usable image path
	 */
	public function get_image_url_from_path($path){
		if(empty($path) || substr($path, -1) === '/' || substr($path, -1) === '\\') return ''; //check if the path ends with /, if yes its not a correct image path
		
		//check if it has an extension, if not leave it as it is
		if(empty(strtolower(pathinfo($path, PATHINFO_EXTENSION)))) return $path;
		
		//protect from absolute url
		$lower		= strtolower($path);
		$base_url	= $this->get_base_url();
		$return		= (strpos($lower, 'http://') !== false || strpos($lower, 'https://') !== false || strpos($lower, 'www.') === 0) ? $path : $base_url.$path;
		
		return ($return !== $base_url) ? preg_replace('~(?<!:)//+~', '/', $return) : '';
	}
	
	/**
	 * Check if Path is a Valid Image File
	 * extension check only, the file is not opened
	 * @return string|false the url when the extension is an image one, false otherwise
	 **/
	public function check_valid_image($url){
		if(empty($url)) return false;

		$ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
		
		return (in_array($ext, ['gif', 'jpg', 'jpeg', 'png', 'webp'])) ? $url : false;
	}
	
	/**
	 * get the upload URL of images
	 * @return string wp-content on single sites, the uploads folder on multisite
	 */
	public static function get_base_url(){
		return (is_multisite() == false) ? content_url().'/' : wp_upload_dir()['baseurl'].'/';
	}
	
	/**
	 * strip slashes recursive
	 * @since: 5.0
	 * @return mixed
	 */
	public static function stripslashes_deep($value){
		if(empty($value)) return $value;

		$value = is_array($value) ? array_map(['RevSliderFunctions', 'stripslashes_deep'], $value) : stripslashes($value);
		
		return $value;
	}
	
	/**
	 * esc attr recursive
	 * @since: 6.0
	 * @return mixed
	 */
	public static function esc_attr_deep($value){
		$value = is_array($value) ? array_map(['RevSliderFunctions', 'esc_attr_deep'], $value) : esc_attr($value);
		
		return $value;
	}
	
	
	/**
	 * get post types with categories for client side.
	 * flat list for the editor's source picker: one entry per post type + taxonomy combination
	 * @return array
	 */
	public function get_post_types_with_categories_for_client(){
		$c = 0;
		$ret = [];
		$post_types = $this->get_post_types_with_taxonomies();
		foreach($post_types as $name => $tax){
			$cat = [];
			if(empty($tax)){
				$ret[$name] = $cat;
				continue;
			}
			
			foreach($tax as $tax_name => $tax_title){
				$cats = $this->get_categories_assoc($tax_name);
				if(empty($cats)) continue;

				$c++;
				$cat['option_disabled_'.$c] = '---- '. $tax_title .' ----';
				foreach($cats as $catID => $catTitle){
					$cat[$tax_name.'_'.$catID] = $catTitle;
				}
			}
			
			$ret[$name] = $cat;
		}

		return $ret;
	}
	
	
	/**
	 * get post types array with taxomonies
	 * @return array post type name => its taxonomies
	 */
	public function get_post_types_with_taxonomies(){
		$post_types = $this->get_post_type_assoc();
		
		foreach($post_types ?? [] as $post_type => $title){
			$post_types[$post_type]	= $this->get_post_type_taxonomies($post_type);
		}
		
		return $post_types;
	}
	
	
	/**
	 * 
	 * get array of post types with categories (the taxonomies is between).
	 * get only those taxomonies that have some categories in it.
	 * @return array post type => taxonomy => terms
	 */
	public function get_post_types_with_categories(){
		$post_types_categories	= [];
		$post_types				= $this->get_post_types_with_taxonomies();
		
		foreach($post_types as $name => $tax){
			$ptwc = [];
			foreach($tax ?? [] as $tax_name => $tax_title){
				$cats = $this->get_categories_assoc($tax_name);
				if(!empty($cats)){
					$ptwc[$tax_name] = [
						'title'	=> $tax_title,
						'cats'	=> $cats
					];
				}
			}
			$post_types_categories[$name] = $ptwc;
		}
		
		return $post_types_categories;
	}
	
	
	/**
	 * get all the post types including custom ones
	 * the put to top items will be always in top (they must be in the list)
	 * @param array $put_to_top post type names to sort to the front
	 * @return array post type name => label
	 */
	public function get_post_type_assoc($put_to_top = []){
		$build_in		= ['post' => 'post', 'page'=>'page'];
		$custom_types	= get_post_types(['_builtin' => false]);
		
		//top items validation - add only items that in the customtypes list
		$top_updated	= [];
		foreach($put_to_top ?? [] as $top){
			if(in_array($top, $custom_types) == true){
				$top_updated[$top] = $top;
				unset($custom_types[$top]);
			}
		}
		
		$post_types = array_merge($top_updated, $build_in, $custom_types);
		
		//update label
		foreach($post_types ?? [] as $key => $type){
			$post_types[$key] = $this->get_post_type_title($type);
		}
		
		return $post_types;
	}
	
	
	/**
	 * return post type title from the post type
	 * @return string the singular label, or the raw name for unregistered types
	 */
	public static function get_post_type_title($post_type){
		$obj_type = get_post_type_object($post_type);
		
		return (empty($obj_type)) ? ($post_type) : $obj_type->labels->singular_name;
	}
	
	
	/**
	 * get post type taxomonies
	 * @return array taxonomy name => label
	 */
	public function get_post_type_taxonomies($post_type){
		$tax = get_object_taxonomies(['post_type' => $post_type], 'objects');
		
		if(empty($tax)) return [];

		$names	= [];
		foreach($tax ?? [] as $obj_tax){
			if($post_type === 'product' && !in_array($obj_tax->name, ['product_cat', 'product_tag'])) continue;
			$names[$obj_tax->name] = $obj_tax->labels->name;
		}
		
		return $names;
	}
	
	
	/**
	 * get post categories list assoc - id / title
	 * child terms are indented with dashes so the flat select still shows the hierarchy
	 * @return array term id => title
	 */
	public function get_categories_assoc($taxonomy = 'category'){
		$categories	= [];
		if(strpos($taxonomy, ',') !== false){
			$taxes = explode(',', $taxonomy);
			foreach($taxes ?? [] as $tax){
				$cats		= $this->get_categories_assoc($tax);
				$categories	= array_merge($categories, $cats);
			}
		}else{
			$args = ['taxonomy' => $taxonomy, 'number' => 10000, 'hide_empty' => false];
			$cats = get_categories($args);
			foreach($cats ?? [] as $cat){
				$num				= $cat->count;
				$id					= $cat->cat_ID;
				$name				= ($num == 1) ? 'item' : 'items';
				$title				= $cat->name . ' ('.$num.' '.$name.')';
				$categories[$id]	= $title;
			}
		}
		
		return $categories;
	}
	
	
	/**
	 * check if css string is rgb
	 * @return bool
	 **/
	public function is_rgb($rgba){
		return (strpos($rgba, 'rgb') !== false) ? true : false;
	}
	
	
	/**
	 * check if file is in zip
	 * during an import: look for the file inside the extracted package and, if found, move it into the
	 * media library - otherwise keep the original reference
	 * @since: 5.0
	 * $folder in v6 was images
	 * @param array $imported modified by reference: already imported files, keyed by source path
	 * @return string|false the new URL, false when the file is not part of the package
	 */
	public function check_file_in_zip($path, $file, $alias, &$imported, $add_path = false, $folder = 'media'){ 
		global $wp_filesystem;
		
		$file = (is_array($file)) ? $this->get_val($file, 'url') : $file;
		if(trim($file) === '' || strpos($file, 'http') !== false) return $file; //http -> external image, do not change

		$strip	= false;
		$zimage	= $wp_filesystem->exists($path.$folder.'/'.$file);
		if(!$zimage){
			$zimage	= $wp_filesystem->exists(str_replace('//', '/', $path.$folder.'/'.$file));
			$strip	= true;
		}

		if($zimage !== false){
			if(!isset($imported[$folder.'/'.$file])){
				//check if we are object folder, if yes, do not import into media library but add it to the object folder
				$uimg = ($strip == true) ? str_replace('//', '/', $folder.'/'.$file) : $file; //pclzip
				
				if(strpos($uimg, 'revslider/objects/') === 0){ 
					// we are from the object library, copy the image to the objects folder if false
					/* @var RevSliderObjectLibrary $obj */
					$obj = RevSliderGlobals::instance()->get('RevSliderObjectLibrary');
					$importImage = $obj->_import_object($path.$folder.'/'.$uimg);
				}else{
					$importImage = $this->import_media($path.$folder.'/'.$uimg, $alias.'/');
					if (!$importImage['success']) $importImage = false;
				}
				
				if($importImage !== false){
					$imported[$folder.'/'.$file] = $importImage['path'];
					
					$file = $importImage['path'];
				}
			}else{
				$file = $imported[$folder.'/'.$file];
			}
		}

		if($add_path){
			$updir = wp_upload_dir()['baseurl'];
			if(strpos($file, $updir) === false) $file = str_replace('uploads/uploads/', 'uploads/', $updir . '/' . $file);
		}
		
		return $file;
	}
	
	/**
	 * Import the Media as it is
	 *
	 * @param string $name
	 * @param string $bitmap
	 * @return array
	 **/
	public function import_media_raw($name, $bitmap){
		$data = substr($bitmap, strpos($bitmap, ',') + 1);
		$data = base64_decode(str_replace(' ', '+', $data));
		if($data === false) {
			return [
				'success' => false,
				'message' => __('Image has an invalid type', 'revslider'),
			];
		}

		if(!preg_match('/^data:image\/(\w+);base64,/', $bitmap, $type)) {
			return [
				'success' => false,
				'message' => __('Image has an invalid data', 'revslider'),
			];
		}

		$type = strtolower($type[1]); // jpg, png, gif
		if(!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
			return [
				'success' => false,
				'message' => __('Image has an invalid type', 'revslider'),
			];
		}

		if(strpos($name, '.') !== false) $name = explode('.', $name)[0];

		$path = $this->get_temp_path('rstemp');
		$name = preg_replace("/[^a-zA-Z0-9\-\.\_]/", '', $name.'.'.$type);
		
		if(file_put_contents($path.$name, $data) === false) {
			return [
				'success' => false,
				'message' => __('Image could not be saved', 'revslider'),
			];
		}

		return $this->import_media($path.$name , 'video-media/');
	}

	/**
	 * normalize slashes in file path
	 * do not use with urls
	 *
	 * @param string $path
	 * @return string
	 */
	public function normalize_slashes($path){
		return preg_replace('#/+#', '/', $path);
	}

	/**
	 * @param string $value
	 * @return boolean
	 */
	function path_or_url_exists($value){
		// Remote URL
		if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$response = wp_remote_head( $value );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			return wp_remote_retrieve_response_code( $response ) === 200;
		}

		// Local filesystem path
		return file_exists( $value ) && is_readable( $value );
	}
	
	/**
	 * copy a source file to its destination - the source may be a local path or a remote URL.
	 *
	 * a plain copy() on an http(s) URL goes through the PHP stream wrapper: it needs allow_url_fopen, has no
	 * timeout, does not verify the TLS certificate, follows redirects without a limit and reports nothing but
	 * false. download_url() routes it through the WP HTTP API instead, which handles all of that and can be
	 * filtered by the site (proxies, blocked hosts). Local paths keep using copy().
	 *
	 * @param string $source
	 * @param string $destination absolute path
	 * @return bool|WP_Error true/false from copy(), WP_Error when the download failed
	 */
	public function copy_source_file($source, $destination){
		if(!preg_match('#^https?://#i', (string)$source)) return @copy($source, $destination);

		require_once(ABSPATH . 'wp-admin/includes/file.php');

		$tmp = download_url($source);
		if(is_wp_error($tmp)) return $tmp;

		$copied = @copy($tmp, $destination);
		wp_delete_file($tmp);

		return $copied;
	}

	/**
	 * Import media from url
	 *
	 * @param string $file_url    URL of the existing file from the original site
	 * @param int    $folder_name The slidername will be used as a folder name in import
	 * @return array
	 */
	public function import_media($file_url, $folder_name, $filename = '', $post_content = '', $post_excerpt = ''){
		global $SR_GLOBALS;
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		WP_Filesystem();
		/* @var WP_Filesystem_Base $wp_filesystem */
		global $wp_filesystem;

		if (!$this->path_or_url_exists($file_url)){
			return [
				'success' => false,
				'message' => sprintf( __( 'Source file not exists: %s', 'revslider' ), $file_url),
			];
		}

		$path_info = pathinfo($file_url);
		if(empty($path_info['extension']) || $this->is_bad_extension($path_info['extension'])){
			return [
				'success' => false,
				'message' => sprintf( __( 'Extension not allowed: %s', 'revslider' ), $path_info['extension']),
			];
		}

		// folder inside uploads/revslider/
		$folder_name = trailingslashit( ltrim( $folder_name, '/' ) );

		$ul_dir = wp_upload_dir();
		$paths = [
			'sr' => 'revslider/',
			'basedir' => trailingslashit($ul_dir['basedir']),
			'baseurl' => trailingslashit($ul_dir['baseurl']),
			'filename' => (empty($filename)) ? basename($file_url) : $filename,
		];
		// relative to the upload dir
		$paths['relative'] = $this->normalize_slashes($paths['sr'] . $folder_name . $paths['filename']);
		// absolute path
		$paths['absolute'] = $paths['basedir'] . $paths['relative'];

		if (!wp_mkdir_p(dirname($paths['absolute']))) {
			return [
				'success' => false,
				'message' => sprintf( __( 'Could not create destination directory: %s', 'revslider' ), $paths['absolute']),
			];
		}

		$atc_id	= $this->get_image_id_by_url($paths['relative']);
		if (empty($atc_id)) {
			$atc_id = $this->get_image_id_by_basename($paths['filename']);
			if (!empty($atc_id)) {
				// the image was found through get_image_id_by_basename()
				// new save = found file location, if the files are the same
				$attached_file_path = get_attached_file($atc_id);
				if(!empty($attached_file_path) && @md5_file($attached_file_path) === @md5_file($file_url)){
					$paths['absolute'] = $attached_file_path;
					$paths['relative'] = str_replace($paths['basedir'], '', $paths['absolute']);
				}
			}
		}
		
		/**
		 * check if the files have matching md5, if not change the filename
		 * change save_dir so that the file is not
		 **/
		if (!empty($atc_id)) {
			if(!$wp_filesystem->exists($paths['absolute']) || @md5_file($file_url) !== @md5_file($paths['absolute'])){
				$fn_info = pathinfo($paths['filename']);
				$nr = 0;
				while(true){
					$nr++;
					$paths['filename'] = $fn_info['filename'] . $nr . '.' . $fn_info['extension'];
					$paths['relative'] = dirname($paths['relative']) . '/' . $paths['filename'];
					$paths['absolute'] = $paths['basedir'] . $paths['relative'];
					if(!$wp_filesystem->exists($paths['absolute'])){
						break;
					}
				}
				$atc_id = $this->get_image_id_by_url($paths['relative']);
			}
			//check if the file really exists in the filesystem, if not reset and redownload
			if(!$wp_filesystem->exists($paths['absolute'])) $atc_id = false;
		}

		if (empty($atc_id)) {
			$copied = $this->copy_source_file($file_url, $paths['absolute']);
			if(is_wp_error($copied)){
				return [
					'success' => false,
					'message' => sprintf( __( 'Could not download source file: %s', 'revslider' ), $copied->get_error_message()),
				];
			}
			if(!$wp_filesystem->exists($paths['absolute'])){
				return [
					'success' => false,
					'message' => sprintf( __( 'Could not create destination file: %s', 'revslider' ), $paths['absolute']),
				];
			}

			$paths = apply_filters('revslider_import_media_insert_attachment_before', $paths);

			$file_info = wp_getimagesize($paths['absolute']);
			$mime_type = $this->get_val($file_info, 'mime');
			$allowed_mimes = array_merge($this->get_val($SR_GLOBALS, ['mime_types', 'image'], []), $this->get_val($SR_GLOBALS, ['mime_types', 'video'], []));
			//AddOns may import non image/video media that already passed the bad_extensions gate (e.g. lottie .json objects) - let them whitelist their own types
			$allowed_mimes = apply_filters('revslider_import_media_allowed_mimes', $allowed_mimes, $paths['filename']);
			if(!$file_info){
				$type_check = wp_check_filetype($paths['absolute'], $allowed_mimes);
				if(empty($type_check['type'])){
					$wp_filesystem->delete($paths['absolute']);
					return [
						'success' => false,
						'message' => sprintf( __( 'Could not validate destination file: %s', 'revslider' ), $paths['absolute']),
					];
				}
				$mime_type = $type_check['type'];
			}
			if(empty($mime_type) || !in_array($mime_type, $allowed_mimes, true)){
				$wp_filesystem->delete($paths['absolute']);
				return [
					'success' => false,
					'message' => sprintf( __( 'Mime type not allowed: %s', 'revslider' ), $mime_type),
				];
			}

			//SVGs imported via zip/object-library reach this point without passing through the upload
			//sanitizer in functions-admin.class.php - strip script/onload/foreignObject/XXE here too, so
			//every code path that writes an SVG into the media library is sanitized.
			if($mime_type === 'image/svg+xml' || strtolower((string)$this->get_val($path_info, 'extension')) === 'svg'){
				if(!class_exists('RevSliderSvgSanitizer')){
					require_once(RS_PLUGIN_PATH . 'admin/includes/svg_sanitizer/subject.class.php');
					require_once(RS_PLUGIN_PATH . 'admin/includes/svg-sanitizer.class.php');
				}
				$sanitizer	= new RevSliderSvgSanitizer();
				$svg_clean	= $sanitizer->sanitize($wp_filesystem->get_contents($paths['absolute']));
				if($svg_clean === false || $svg_clean === '' || !$wp_filesystem->put_contents($paths['absolute'], $svg_clean, FS_CHMOD_FILE)){
					$wp_filesystem->delete($paths['absolute']);
					return [
						'success' => false,
						'message' => sprintf( __( 'SVG could not be sanitized: %s', 'revslider' ), $paths['filename']),
					];
				}
			}

			// Create an array of attachment data to insert into wp_posts table
			$artdata = [
				'post_author'	 => 1, 
				'post_date'		 => current_time('mysql'),
				'post_date_gmt'	 => current_time('mysql'),
				'post_title'	 => $paths['filename'],
				'post_status'	 => 'inherit',
				'comment_status' => 'closed',
				'ping_status'	 => 'closed',
				'post_name'		 => sanitize_title_with_dashes(str_replace('_', '-', $paths['filename'])),
				'post_modified'	 => current_time('mysql'),
				'post_modified_gmt' => current_time('mysql'),
				'post_parent'	 => '',
				'post_type'		 => 'attachment',
				'guid'			 => $paths['baseurl'] . $paths['relative'],
				'post_mime_type' => $mime_type,
				'post_excerpt'	 => $post_excerpt,
				'post_content'	 => $post_content
			];
			//insert the database record
			$attach_id = wp_insert_attachment($artdata, $paths['relative']);
			//generate metadata and thumbnails
			add_filter('intermediate_image_sizes_advanced', ['RevSliderFunctions', 'temporary_remove_sizes'], 10, 2);
			
			$rs_meta_create = get_option('rs_image_meta_todo', []);
			if(!isset($rs_meta_create[$attach_id])){
				$rs_meta_create[$attach_id] = $paths['absolute'];
				update_option('rs_image_meta_todo', $rs_meta_create);
			}
			if($attach_data = @wp_generate_attachment_metadata($attach_id, $paths['absolute'])){
				@wp_update_attachment_metadata($attach_id, $attach_data);
			}
		}else{
			$attach_id = $atc_id;
		}
		
		$paths['upload_relative'] = !is_multisite() ? 'uploads/' . $paths['relative'] : $paths['relative'];
		
		return [
			'success' => true,
			'id'      => $attach_id,
			'path'    => $paths['upload_relative'],
			'url'     => $paths['baseurl'] . $paths['relative'],
		];
	}
	
	
	/**
	 * temporary remove image sizes so that only the needed thumb will be created
	 * intermediate_image_sizes_advanced filter, hooked only around an import so WordPress does not generate
	 * the theme's whole size set for every imported image
	 * @since: 6.0
	 * @return array
	 **/
	public static function temporary_remove_sizes($sizes, $meta = false){
		return (!empty($sizes) && isset($sizes['thumbnail'])) ? ['thumbnail' => $sizes['thumbnail']] : $sizes;
	}
	
	
	/**
	 * get wp-content path
	 * @return string absolute path, multisite aware
	 */
	public function get_upload_path(){
		if(is_multisite()){
			global $wpdb;
			return (!defined('BLOGUPLOADDIR')) ? ABSPATH . 'wp-content/uploads/sites/' . $wpdb->blogid : BLOGUPLOADDIR;
		}
		
		return (!empty(WP_CONTENT_DIR)) ? WP_CONTENT_DIR . '/' : ABSPATH . 'wp-content/uploads/';
	}
	
	/**
	 * get contents of the static css file
	 * the user's own CSS from the editor's global settings
	 * @return string
	 */
	public function get_static_css(){
		return $this->get_options(['other', 'static-css'], '');
	}
	
	/**
	 * store the user's own CSS, unescaping the slashes it arrives with
	 * @return string the stored CSS
	 */
	public function update_static_css($css){
		$css = str_replace(["\'", '\"', '\\\\'], ["'", '"', '\\'], trim($css));
		$this->update_option(['other', 'static-css'], $css); //write where get_static_css() reads it; the old standalone 'revslider-static-css' option was never read back (and autoloaded)
		return $css;
	}

	/**
	 * get the client browser with version
	 * parsed from the user agent; used for browser specific font format decisions
	 * @return array ['userAgent', 'name', 'version', 'platform', 'pattern']
	 **/
	public function get_browser(){
		$u_agent	= $this->get_val($_SERVER, 'HTTP_USER_AGENT');
		$bname		= 'Unknown';
		$platform	= 'Unknown';
		$version	= '';
		$ub			= '';

		// get platform
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		} elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}

		// get name of useragent
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
			$bname = 'Internet Explorer';
			$ub = 'MSIE';
		} elseif(preg_match('/Firefox/i',$u_agent)) {
			$bname = 'Mozilla Firefox';
			$ub = 'Firefox';
		} elseif(preg_match('/OPR/i',$u_agent))	{
			$bname = 'Opera';
			$ub = 'Opera';
		} elseif(preg_match('/Chrome/i',$u_agent) && !preg_match('/Edg/i',$u_agent)) {
			$bname = 'Google Chrome';
			$ub = 'Chrome';
		} elseif(preg_match('/Safari/i',$u_agent) && !preg_match('/Edg/i',$u_agent)) {
			$bname = 'Apple Safari';
			$ub = 'Safari';
		} elseif(preg_match('/Netscape/i',$u_agent)) {
			$bname = 'Netscape';
			$ub = 'Netscape';
		} elseif(preg_match('/Edg/i',$u_agent)) {
			$bname = 'Edge';
			$ub = 'Edg';
		} elseif(preg_match('/Trident/i',$u_agent)) {
			$bname = 'Internet Explorer';
			$ub = 'MSIE';
		}

		// get version
		$known		= ['Version', $ub, 'other'];
		$pattern	= '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)){ /* */ }
		// see how many we have
		$i			= count($this->get_val($matches, 'browser', [])); //[] default: get_val returns '' otherwise, and count('') is a TypeError in PHP 8
		$version	= $this->get_val($matches, ['version', 0]);
		if ($i != 1) {
			//we will have two since we are not using the 'other' argument yet
			//see if the version is before or after the name
			$version = (strripos($u_agent, 'Version') < strripos($u_agent,$ub)) ? $version : $this->get_val($matches, ['version', 1]);
		}

		// check if we have a number
		if ($version == null || $version == '') $version = '0';

		return [
			'name'		=> $bname,
			'version'	=> $version,
			'platform'	=> $platform
		];
    }

	/**
	 * Change FontURL to new URL (added for chinese support since google is blocked there)
	 * replaces the Google Fonts host with the mirror configured in the global settings
	 * @since: 5.0
	 * @return string
	 */
	public function modify_fonts_url($url, $remove = true){
		$gs = $this->get_global_settings();
		$df = $this->get_val($gs, ['fonts', 'url'], '');
		$df = (!in_array($df, ['', 'off'])) ? $df : $url;

		//force absolute https for the font CDN: a protocol-relative //host URL gets collapsed to a page-relative ?family= by some optimizers/CDNs, which then loads the (text/html) page as a stylesheet
		return ($remove) ? $this->remove_http($df, 'https') : $df;
	}
	
	/**
	 * convert date to the date format that the user chose.
	 * @param bool $with_time append the site's time format
	 * @return string
	 */
	public function convert_post_date($date, $with_time = false){
		if(empty($date)) return $date;

		return ($with_time) ? date_i18n(get_option('date_format').' '.get_option('time_format'), strtotime($date)) : date_i18n(get_option('date_format'), strtotime($date));
	}
	
	
	/**
	 * return biggest value of object depending on which devices are enabled
	 * v6 device values: an object keyed d/n/t/m (desktop, notebook, tablet, mobile). Returns the value of
	 * the largest enabled device, so a setting only filled for mobile still has something to inherit from.
	 * @since: 5.0
	 * @param string $default sentinel '########' means "no default given"
	 * @return mixed
	 **/
	public function get_biggest_device_setting($obj, $enabled_devices, $default = '########'){
		$dv = $this->get_val($obj, ['d', 'v']);
		if($this->get_val($enabled_devices, 'd') === true && $dv != '') return $dv;
		if($default !== '########') return $default;
		$nv = $this->get_val($obj, ['n', 'v']);
		if($this->get_val($enabled_devices, 'n') === true && $nv != '') return $nv;
		$tv = $this->get_val($obj, ['t', 'v']);
		if($this->get_val($enabled_devices, 't') === true && $tv != '') return $tv;
		$mv = $this->get_val($obj, ['m', 'v']);
		if($this->get_val($enabled_devices, 'm') === true && $mv != '') return $mv;
		
		return '';
	}

	/**
	 * same for v7, where the value is a flat array ordered [ld, d, n, t, m] and '#a' marks "inherit"
	 * @since: 5.0
	 * @return mixed
	 **/
	public function get_biggest_device_setting_v7($obj, $enabled_devices, $default = '########'){
		if(empty($obj)) return ($default !== '########') ? $default : '';
		if(!is_array($obj)) return $obj;
		
		$devices = ['ld', 'd', 'n', 't', 'm'];
		foreach($devices as $key => $device){
			if($this->get_val($enabled_devices, $device) === true && !in_array($obj[$key], ['', '#a'])) return $obj[$key];
		}

		return ($default !== '########') ? $default : $obj[1];
	}

	/**
	 * like get_biggest_device_setting_v7(), but compares numerically and returns the largest value found
	 * instead of the first enabled one
	 * @since: 5.0
	 * @return int
	 **/
	public function get_biggest_size_setting_v7($obj, $enabled_devices, $default = '########'){
		$devices = ['ld', 'd', 'n', 't', 'm'];
		$biggest = false;

		foreach($devices ?? [] as $key => $device){
			if($this->get_val($enabled_devices, $device) === true && !in_array($obj[$key], ['', '#a'])) if($biggest === false || intval($obj[$key]) > $biggest) $biggest = $obj[$key];
		}

		return ($biggest === false && $default !== '########') ? $default : intval($biggest);
	}
	
	
	/**
	 * normalize object with device informations depending on what is enabled for the Slider
	 * fills the gaps in a device object: every disabled or empty device inherits from the next larger one,
	 * so the front end always gets a complete set
	 * @since: 5.0
	 * @param string $return 'obj' returns the array, anything else joins the values with $use
	 * @param array  $set_to_if value replacements applied before normalizing, from => to
	 * @return mixed
	 **/
	public function normalize_device_settings($obj, $enabled_devices, $return = 'obj', $default = [], $set_to_if = [], $use = ','){ //array -> from -> to
		/*d n t m*/
		$obj			= $this->fill_device_settings($obj);
		$_def			= (!empty($default)) ? reset($default) : '########';
		$inherit_size	= $this->get_biggest_device_setting($obj, $enabled_devices, $_def);
		
		if(!empty($set_to_if)){
			foreach($obj as $device => $key){
				foreach($set_to_if as $from => $to){
					if(trim($this->get_val($obj, [$device, 'v'])) == $from) $obj[$device]['v'] = $to;
				}
			}
		}

		$device_types = ['d', 'n', 't', 'm'];
		foreach($device_types as $device){
			if($enabled_devices[$device] === true){
				$value = $this->get_val($obj, [$device, 'v'], '');
				if($value === ''){
					$obj[$device]['v'] = ($_def !== '########') ? $_def : $inherit_size;
				}else{
					$inherit_size = $value;
				}
			}else{
				$obj[$device]['v'] = $inherit_size;
			}
		}
	
		switch ($return) {
			case 'obj':
				return [
					'd' => $obj['d']['v'],
					'n' => $obj['n']['v'],
					't' => $obj['t']['v'],
					'm' => $obj['m']['v']
				];
			case 'html-array':
				$html_array = ($obj['d']['v'] === $obj['n']['v'] && $obj['d']['v'] === $obj['m']['v'] && $obj['d']['v'] === $obj['t']['v']) ? $obj['d']['v'] : implode($use, array_column($obj, 'v'));

				return (!empty($default) && in_array($html_array, $default)) ? '' : $html_array;
			case 'array':
				$array = [];
				if($obj['d']['v'] === $obj['n']['v'] && $obj['d']['v'] === $obj['m']['v'] && $obj['d']['v'] === $obj['t']['v']){
					$array[$obj['d']['v']] = $obj['d']['v'];
				}else{
					$array[$obj['d']['v']] = $this->get_val($obj, ['d', 'v']);
					$array[$obj['n']['v']] = $this->get_val($obj, ['n', 'v']);
					$array[$obj['t']['v']] = $this->get_val($obj, ['t', 'v']);
					$array[$obj['m']['v']] = $this->get_val($obj, ['m', 'v']);
					if(!empty($array)){
						foreach($array as $k => $v){
							if(trim($v) === ''){
								unset($array[$k]);
							}
						}
					}
				}
				
				return $array;
		}
	
		return $obj;
	}
	
	/**
	 * fill object with default values
	 * make sure a device object has an entry for every device, so callers can index it without checks
	 * @since: 6.0
	 * @return array
	 **/
	public function fill_device_settings($obj){
		$push = ['d', 'n', 't', 'm'];
		
		if(is_string($obj)){
			$t = $obj;
			$obj = [];
			foreach($push as $p){
				$obj[$p] = ['v' => $t];
			}
			return $obj;
		}
		
		$_obj = [];
		foreach($push as $p){
			$_obj[$p] = (!isset($obj[$p])) ? [] : $obj[$p];
			if(!isset($_obj[$p]['v'])){
				$_obj[$p]['v'] = '';
				$_obj[$p]['u'] = '';
			}
		}
		
		return $_obj;
	}

	/**
	 * get the values for the given transition
	 * looks a slide transition up by name in the nested base transition catalogue
	 * @return array empty when the name is unknown
	 **/
	public function get_slide_transition_values($transition, $base_transitions = []){
		if(empty($base_transitions)) $base_transitions = $this->get_base_transitions();
		foreach($base_transitions as $t){
			if(!is_array($t)) continue;
			foreach($t as $_t){
				if(!is_array($_t)) continue;
				foreach($_t as $name => $values){
					if($name !== $transition) continue;
					
					return $values;
				}
			}
		}
		return [];
	}
	
	
	/**
	 * get a random slide transition for the given main and grp
	 * @param string       $main transition family, 'all' for any
	 * @param array|string $grp  group filter, comma separated string allowed
	 * @return string transition name, '' when nothing matches
	 **/
	public function get_random_slide_transition($main, $grp, $base_transitions = []){
		if(empty($base_transitions)) $base_transitions = $this->get_base_transitions();
		
		if(!is_array($grp) && !empty($grp)) $grp = explode(',', $grp);
		if($grp === '') $grp = [];
		
		$items = [];
		foreach($base_transitions as $m => $bt){
			if(!is_string($m) || $m === 'random' || $m === 'custom' || ($main !== 'all' && $main !== $m)) continue;
			foreach($bt as $g => $_bt){
				if(is_array($_bt) && $g !== 'icon' && (empty($grp) || in_array($g, $grp))){
					foreach($_bt as $e => $__bt){
						$items[] = $e;
					}
				}
			}
		}
		
		$num = (!empty($items)) ? array_rand($items, 1) : false;
		return ($num !== false) ? $items[$num] : '';
	}
	
	
	/**
	 * Remove http:// and https://
	 * @since: 6.0.0
	 * @param string $special 'auto' makes the URL protocol relative, 'http'/'https' force a scheme,
	 *                        'keep' leaves it untouched
	 * @return string
	 **/
	public function remove_http($url, $special = 'auto'){
		switch($special){
			case 'http':
				$url = str_replace('https://', 'http://', $url);
				if(strpos($url, 'http://') === false) $url = 'http://'.$url;
			break;
			case 'https':
				if(strpos($url, '//') === 0) $url = 'https:'.$url; //protocol-relative -> https
				$url = str_replace('http://', 'https://', $url);
				if(strpos($url, 'https://') === false) $url = 'https://'.$url;
			break;
			case 'keep': //do nothing
			break;
			case 'auto':
			default:
				$url = str_replace(['http://', 'https://'], '//' , $url);
			break;
		}
		return $url;
	}

	/**
	 * set the memory limit to at least 256MB if possible
	 * @since: 6.1.6
	 * @return void
	 **/
	public static function set_memory_limit(){
		wp_raise_memory_limit('revslider');
	}
	
	
	/**
	 * Check if page is edited in Gutenberg
	 */
	public function _is_gutenberg_page(){
		if(isset($_GET['action']) && $_GET['action'] == 'elementor') return false; // Elementor Page Edit
		if(isset($_GET['vc_action']) && $_GET['vc_action'] == 'vc_inline') return false; // WP Bakery Front Edit
		if(function_exists('is_gutenberg_page') && is_gutenberg_page()) return true; // Gutenberg Edit with WP < 5
		if(function_exists('get_current_screen')){
			$current_screen = get_current_screen();
			if(!empty($current_screen) && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) return true; //Gutenberg Edit with WP >= 5
		}
		return false;
	}
	
	
	
	/**
	 * get custom transitions
	 * slide transitions the user built in the editor, stored in the options
	 * @return array
	 **/
	public function get_custom_slidetransitions(){
		$custom = $this->get_options(['other', 'slide-transitions'], []);
		
		return apply_filters('rs_get_custom_slidetransitions', $custom);
	}
	
	
	/**
	 * store a custom slide transition, assigning a new id when the template has none
	 * @return int the id it was stored under
	 **/
	public function save_custom_slidetransitions($template){
		$custom = $this->get_custom_slidetransitions();
		
		//empty custom templates?
		if(empty($custom)){
			$custom = [];
			$new_id = 1;
		}else{
			$id = $this->get_val($template, 'id', 0);
			//custom templates exist
			$new_id = ($id > 0) ? $id : max(array_keys($custom)) + 1;
		}
		
		//update or insert template
		$custom[$new_id]['title']	= $template['obj']['title'];
		$custom[$new_id]['preset']	= $template['obj']['preset'];
		//return the ID the template was saved with
		$this->update_option(['other', 'slide-transitions'], $custom);

		return $new_id;
	}
	
	
	/**
	 * remove a custom slide transition (identified by its id inside $template)
	 * @return bool false when no transition with that id exists
	 **/
	public function delete_custom_slidetransitions($template){
		//load templates array
		$custom = $this->get_custom_slidetransitions();
		
		$id = intval($this->get_val($template, 'id', 0));
		//custom template exist
		if($id > 0 && isset($custom[$id])){
			//delete given ID
			unset($custom[$id]);
			//save the resulting templates array again
			$this->update_option(['other', 'slide-transitions'], $custom);
			return true;	
		}
		
		return false;
	}
	/**
	 * push the matieral icons css into the global variable
	 * queues the Material Icons font once per request, either from the CDN or the bundled copy
	 * @return string the markup to print, '' when it is already queued
	 **/
	public function add_material_icons(){
		global $SR_GLOBALS;
		if($this->get_val($SR_GLOBALS, ['icon_sets', 'Materialicons', 'css'], false) !== false) return '';

		$gs = $this->get_global_settings();

		if($this->get_val($gs, ['fonts', 'download'], 'off') === 'off'){
			$font_face = "@font-face {
  font-family: 'Material Icons';
  font-style: normal;
  font-weight: 400;  
  src: url(//fonts.gstatic.com/s/materialicons/v41/flUhRq6tzZclQEJ-Vdg-IuiaDsNcIhQ8tQ.woff2) format('woff2');
}";
		}else{
			$font_face = "@font-face {
font-family: 'Material Icons';
font-style: normal;
font-weight: 400;  

src: local('Material Icons'),
local('MaterialIcons-Regular'),
  url(".RS_PLUGIN_URL_CLEAN."public/css/fonts/material/MaterialIcons-Regular.woff2) format('woff2'),
  url(".RS_PLUGIN_URL_CLEAN."public/css/fonts/material/MaterialIcons-Regular.woff) format('woff'),  
  url(".RS_PLUGIN_URL_CLEAN."public/css/fonts/material/MaterialIcons-Regular.ttf) format('truetype');
}";
		}

		$this->set_val($SR_GLOBALS, ['icon_sets', 'Materialicons', 'css'], "/* 
ICON SET 
*/
".$font_face."

rs-module .material-icons {
  font-family: 'Material Icons';
  font-weight: normal;
  font-style: normal;
	font-size: inherit;
  display: inline-block;  
  text-transform: none;
  letter-spacing: normal;
  word-wrap: normal;
  white-space: nowrap;
  direction: ltr;
  vertical-align: top;
  line-height: inherit;
  /* Support for IE. */
  font-feature-settings: 'liga';

  -webkit-font-smoothing: antialiased;
  text-rendering: optimizeLegibility;
  -moz-osx-font-smoothing: grayscale;
}");
	}
	
	/**
	 * get the current page id
	 * @since: 6.0
	 * @return int|string the post id, or 'homepage' on the front page
	 **/
	public function get_current_page_id(){
		$id = '';
		
		if(is_front_page() == true || is_home() == true){
			$id = 'homepage';
		}else{
			global $post;
			$id = (isset($post->ID)) ? $post->ID : $id;
		}
		
		return $id;
	}

	/**
	 * this will return the exact alias of the rev_slider modules on given posts/pages
	 * parses the [rev_slider] / [sr7] shortcodes out of the post content
	 * @param array|int $ids post ids
	 * @return array the slider aliases used on those posts, without duplicates
	 **/
	public function get_shortcode_from_page($ids){
		$_shortcodes = [];
		$ids		 = (!is_array($ids)) ? (array)$ids : $ids;

		foreach($ids as $id){
			$post = get_post($id);
			$sc = [];
			if(is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'rev_slider') || has_shortcode($post->post_content, 'sr7'))){
				preg_match_all('/\[sr7.*alias=.(.*)"\]/', $post->post_content, $shortcodes);
				preg_match_all('/\[rev_slider.*alias=.(.*)"\]/', $post->post_content, $shortcodesold);
				if(isset($shortcodes[1]) && $shortcodes[1] !== '') $sc = $shortcodes[1];
				if(isset($shortcodesold[1]) && $shortcodesold[1] !== '') $sc = array_merge($sc, $shortcodesold[1]);
				
				if(!empty($sc)){
					foreach($sc as $k => $s){
						if(strpos($s, '"') !== false) $s = $this->get_val(explode('"', $s), 0);
						if(!in_array($s, $_shortcodes)) $_shortcodes[] = $s;
					}
				}
			}
		}
		
		return $_shortcodes;
	}

	/**
	 * checks if any shortcode format is present in given string
	 * @return bool
	 */
	public function has_any_shortcode($text){
		//cheap pre-check: skip the regex when there is no '[' at all (the common case)
		if(!is_string($text) || strpos($text, '[') === false) return false;
		return (preg_match('/\[.*?\]/', $text)) ? true : false;
	}

	/**
	 * the site wide basic metas, resolved lazily as they are page context, not post loop data
	 * single source of truth for PHP, mirrored in JS by SR7.F.getSiteMeta()
	 * @since 7.1.4
	 * @return array
	 */
	public function get_basic_metas(){
		$permalink = get_permalink(); //no caching, this follows the loop and can change per call

		return [
			'home_url'			=> esc_url(home_url('/')),
			'current_page_link'	=> esc_url($permalink ? $permalink : home_url('/'))
		];
	}

	/**
	 * Resolve site-wide basic meta placeholders (text, href, action URLs).
	 * @since 7.1.4
	 * @return string
	 */
	public function resolve_basic_metas($text){
		if(!is_string($text) || strpos($text, '{{') === false) return $text;
		foreach($this->get_basic_metas() as $meta => $value){
			if(strpos($text, '{{'.$meta.'}}') === false) continue;
			$text = str_replace('{{'.$meta.'}}', $value, $text);
		}
		return $text;
	}

	/**
	 * open and checks a zip file for filetypes
	 * rejects the whole import when any entry has a blocked extension
	 * @param array|false $extensions_allowed whitelist; false uses the global blocklist instead
	 * @return void
	 * @throws Exception when the zip cannot be opened or contains an illegal file
	 **/
	public function check_bad_files($zip_file, $extensions_allowed = false){
		global $SR_GLOBALS;
		if(class_exists('ZipArchive')){
			$zip = new ZipArchive;
			$success = $zip->open($zip_file);
			
			if($success !== true) $this->throw_error(__("Can not open zip file", 'revslider'));

			for($i = 0; $i < $zip->numFiles; $i++){
				$path_info = pathinfo($zip->getNameIndex($i));
				if(!isset($path_info['extension'])) continue;
			
				$pi = strtolower($path_info['extension']);
				if($extensions_allowed !== false){
					if(!in_array($pi, $extensions_allowed)) $this->throw_error(__("zip file contains illegal files", 'revslider'));
				}else{
					if($this->is_bad_extension($pi)) $this->throw_error(__("zip file contains illegal files", 'revslider'));
				}
			}
		}else{ //fallback to pclzip
			require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
			
			$pclzip = new PclZip($zip_file);
			
			$content = $pclzip->listContent();
			if(is_array($content) && !empty($content)){
				foreach($content as $file){
					if(!isset($file['filename'])) continue;

					$path_info = pathinfo($file['filename']);
					if(!isset($path_info['extension'])) continue;

					$pi = strtolower($path_info['extension']);
					if($extensions_allowed !== false){
						if(!in_array($pi, $extensions_allowed)) $this->throw_error(__("zip file contains illegal files", 'revslider'));
					}else{
						if($this->is_bad_extension($pi)) $this->throw_error(__("zip file contains illegal files", 'revslider'));
					}
				}
			}
		}
	}
	
	/**
	 * generate missing attachement metadata for images
	 * works through the queue an import left behind, so thumbnail generation does not block the import
	 * @since: 6.0
	 * @return void
	 **/
	public function generate_attachment_metadata(){
		$rs_meta_create = $this->get_options(['other', 'image-meta'], []);
		
		if(!empty($rs_meta_create)){
			foreach($rs_meta_create as $attach_id => $save_dir){
				unset($rs_meta_create[$attach_id]);
				$this->update_option(['other', 'image-meta'], $rs_meta_create);

				if($attach_data = @wp_generate_attachment_metadata($attach_id, $save_dir)){
					@wp_update_attachment_metadata($attach_id, $attach_data);
				}
			}
		}
	}
	
	/**
	 * set the font clean for import
	 * queues one font in $SR_GLOBALS['fonts'] so RevSliderFonts can emit it later
	 * @return void
	 */
	public function set_clean_font_import($font, $class = '', $url = '', $variants = [], $subsets = []){
		global $SR_GLOBALS;
		
		if(!isset($SR_GLOBALS['fonts'])) $SR_GLOBALS['fonts'] = ['queue' => [], 'loaded' => []]; //if this is called without revslider.php beeing loaded
		
		if(!empty($variants) || !empty($subsets)){
			if(!isset($SR_GLOBALS['fonts']['queue'][$font])) $SR_GLOBALS['fonts']['queue'][$font] = [];
			if(!isset($SR_GLOBALS['fonts']['queue'][$font]['variants'])) $SR_GLOBALS['fonts']['queue'][$font]['variants'] = [];
			if(!isset($SR_GLOBALS['fonts']['queue'][$font]['subsets'])) $SR_GLOBALS['fonts']['queue'][$font]['subsets'] = [];
			
			if(!empty($variants)){
				foreach($variants as $k => $v){
					//check if the variant is already in loaded
					if(!in_array($v, $SR_GLOBALS['fonts']['queue'][$font]['variants'], true)){
						$SR_GLOBALS['fonts']['queue'][$font]['variants'][] = $v;
					}else{ //already included somewhere, so do not call it anymore
						unset($variants[$k]);
					}
				}
			}
			if(!empty($subsets)){
				foreach($subsets as $k => $v){
					if(!in_array($v, $SR_GLOBALS['fonts']['queue'][$font]['subsets'], true)){
						$SR_GLOBALS['fonts']['queue'][$font]['subsets'][] = $v;
					}else{ //already included somewhere, so do not call it anymore
						unset($subsets[$k]);
					}
				}
			}
			if($url !== ''){
				$SR_GLOBALS['fonts']['queue'][$font]['url'] = $url;
			}
		}
	}

	
	/**
	 * get categories list, copy the code from default wp functions
	 * @param bool $full $cat_ids already holds term objects instead of ids
	 * @return string linked category list
	 */
	public function get_categories_html($cat_ids, $tax = null, $post_id = '', $full = false){
		global $wp_rewrite;

		if(!empty($post_id) && $full === false) return get_the_category_list(', ', null, $post_id);
		
		$categories	= ($full === true && !empty($cat_ids)) ? $cat_ids :  $this->get_categories_by_id($cat_ids, $tax);
		$errors		= $this->get_val($categories, 'errors');
		$list		= [];
		$err		= '';
		$rel 		= (is_object($wp_rewrite) && $wp_rewrite->using_permalinks()) ? 'rel="category tag"' : 'rel="category"';
		
		if(!empty($errors)){
			foreach($errors as $error){
				$err .= implode(',', $error);
			}
			$this->throw_error(__('retrieving categories error: '.esc_html($err)));
		}
		
		foreach($categories as $category){
			$link = get_category_link($this->get_val($category, 'term_id'));
			$name = $this->get_val($category, 'name');

			$list[] = (!empty($link)) ? '<a href="' . esc_url($link) . '" title="' . esc_attr(sprintf(__('View all posts in %s', 'revslider'), $name)) .'" '. $rel .'>'. $name .'</a>' : $name;
		}

		return (!empty($list)) ? implode(', ', $list) : '';
	}

	/**
	 * get text intro, limit by number of words
	 * appends an ellipsis when it had to cut, and strips leftover shortcodes
	 * @return string
	 */
	public function get_text_intro($text, $limit){
		$limit++;
		$array = explode(' ', $text, $limit);
		
		if(count($array) >= $limit){
			array_pop($array);
			$intro = implode(' ', $array);
			$intro = trim($intro);
			$intro .= (!empty($intro)) ? '...' : '';
		}else{
			$intro = $text;
		}
		
		return preg_replace('`\[[^\]]*\]`', '', $intro);
	}
	
	/**
	 * same limited by characters instead of words
	 * @return string
	 */
	public function get_text_intro_chars($text, $limit){
		$intro = substr($text, 0, $limit);
		return preg_replace('`\[[^\]]*\]`', '', $intro);
	}

	/**
	 * convert assoc array to array
	 * @return array
	 */
	public static function assoc_to_array($assoc){
		return array_values($assoc ?? []);
	}

	/**
	 * filter non-allowed chars for html classes / IDs
	 * 
	 * @param array|string $classes
	 * @return array|string
	 */
	public function filter_class_name($classes){
		$single = false;
		if(!is_array($classes)){
			$classes = [$classes];
			$single = true;
		}

		$classes = array_map(function($className) {
			return preg_replace('/[^a-zA-Z \d_-]/', '', $className);
		}, $classes);

		return $single ? $classes[0] : $classes;
	}

	public function add_deprecation_message($old_func, $new_func){
		global $SR_GLOBALS;

		if(isset($SR_GLOBALS['deprecated'][$old_func])) return;
		//_deprecated_function($old_func, '7.0', $new_func);
		$SR_GLOBALS['deprecated'][$old_func] = $new_func;
	}
	
	/**
	 * Add Meta Generator Tag in FrontEnd
	 * @since: 5.4.3
		//NOT COMPRESSED VERSION
		function setREVStartSize(e){	
			//window.requestAnimationFrame(function() {	
				window.RSIW = window.RSIW===undefined ? window.innerWidth : window.RSIW;	
				window.RSIH = window.RSIH===undefined ? window.innerHeight : window.RSIH;	
				try {								
					var pw = document.getElementById(e.c).parentNode.offsetWidth,
						newh;
					pw = pw===0 || isNaN(pw) || (e.l=="fullwidth" || e.layout=="fullwidth") ? window.RSIW : pw;
					e.tabw = e.tabw===undefined ? 0 : parseInt(e.tabw);
					e.thumbw = e.thumbw===undefined ? 0 : parseInt(e.thumbw);
					e.tabh = e.tabh===undefined ? 0 : parseInt(e.tabh);
					e.thumbh = e.thumbh===undefined ? 0 : parseInt(e.thumbh);
					e.tabhide = e.tabhide===undefined ? 0 : parseInt(e.tabhide);
					e.thumbhide = e.thumbhide===undefined ? 0 : parseInt(e.thumbhide);
					e.mh = e.mh===undefined || e.mh=="" || e.mh==="auto" ? 0 : parseInt(e.mh,0);
					if(e.layout==="fullscreen" || e.l==="fullscreen")
						newh = Math.max(e.mh,window.RSIH);
					else{					
						e.gw = Array.isArray(e.gw) ? e.gw : [e.gw];
						for (var i in e.rl) if (e.gw[i]===undefined || e.gw[i]===0) e.gw[i] = e.gw[i-1];
						e.gh = e.el===undefined || e.el==="" || (Array.isArray(e.el) && e.el.length==0)? e.gh : e.el;
						e.gh = Array.isArray(e.gh) ? e.gh : [e.gh];
						for (var i in e.rl) if (e.gh[i]===undefined || e.gh[i]===0) e.gh[i] = e.gh[i-1];
											
						var nl = new Array(e.rl.length),
							ix = 0,
							sl;
						e.tabw = e.tabhide>=pw ? 0 : e.tabw;
						e.thumbw = e.thumbhide>=pw ? 0 : e.thumbw;
						e.tabh = e.tabhide>=pw ? 0 : e.tabh;
						e.thumbh = e.thumbhide>=pw ? 0 : e.thumbh;
						for (var i in e.rl) nl[i] = e.rl[i]<window.RSIW ? 0 : e.rl[i];
						sl = nl[0];									
						for (var i in nl) if (sl>nl[i] && nl[i]>0) { sl = nl[i]; ix=i;}
						var m = pw>(e.gw[ix]+e.tabw+e.thumbw) ? 1 : (pw-(e.tabw+e.thumbw)) / (e.gw[ix]);
						newh =  (e.gh[ix] * m) + (e.tabh + e.thumbh);
					}				
					var el = document.getElementById(e.c);
					if (el!==null && el) el.style.height = newh+"px";
					el = document.getElementById(e.c+"_wrapper");
					if (el!==null && el) el.style.height = newh+"px";
				} catch(e){
					console.log("Failure at Presize of Slider:" + e)
				}
			//}
		  };
	 */
	/**
	 * print the inline setREVStartSize() helper once per page.
	 * It reserves the slider's height before the JS engine loads, so the page does not jump - which is why
	 * it has to be inline and cannot wait for sr7.js.
	 * @return false|void echoes the script; false when it was already printed this request
	 **/
	public static function js_set_start_size(){
		global $SR_GLOBALS;

		if(isset($SR_GLOBALS['js_startsize_init']) && $SR_GLOBALS['js_startsize_init'] === true) return false;
		
		$script = '<script>';
		$script .= 'function setREVStartSize(e){
			//window.requestAnimationFrame(function() {
				window.RSIW = window.RSIW===undefined ? window.innerWidth : window.RSIW;
				window.RSIH = window.RSIH===undefined ? window.innerHeight : window.RSIH;
				try {
					var pw = document.getElementById(e.c).parentNode.offsetWidth,
						newh;
					pw = pw===0 || isNaN(pw) || (e.l=="fullwidth" || e.layout=="fullwidth") ? window.RSIW : pw;
					e.tabw = e.tabw===undefined ? 0 : parseInt(e.tabw);
					e.thumbw = e.thumbw===undefined ? 0 : parseInt(e.thumbw);
					e.tabh = e.tabh===undefined ? 0 : parseInt(e.tabh);
					e.thumbh = e.thumbh===undefined ? 0 : parseInt(e.thumbh);
					e.tabhide = e.tabhide===undefined ? 0 : parseInt(e.tabhide);
					e.thumbhide = e.thumbhide===undefined ? 0 : parseInt(e.thumbhide);
					e.mh = e.mh===undefined || e.mh=="" || e.mh==="auto" ? 0 : parseInt(e.mh,0);
					if(e.layout==="fullscreen" || e.l==="fullscreen")
						newh = Math.max(e.mh,window.RSIH);
					else{
						e.gw = Array.isArray(e.gw) ? e.gw : [e.gw];
						for (var i in e.rl) if (e.gw[i]===undefined || e.gw[i]===0) e.gw[i] = e.gw[i-1];
						e.gh = e.el===undefined || e.el==="" || (Array.isArray(e.el) && e.el.length==0)? e.gh : e.el;
						e.gh = Array.isArray(e.gh) ? e.gh : [e.gh];
						for (var i in e.rl) if (e.gh[i]===undefined || e.gh[i]===0) e.gh[i] = e.gh[i-1];
											
						var nl = new Array(e.rl.length),
							ix = 0,
							sl;
						e.tabw = e.tabhide>=pw ? 0 : e.tabw;
						e.thumbw = e.thumbhide>=pw ? 0 : e.thumbw;
						e.tabh = e.tabhide>=pw ? 0 : e.tabh;
						e.thumbh = e.thumbhide>=pw ? 0 : e.thumbh;
						for (var i in e.rl) nl[i] = e.rl[i]<window.RSIW ? 0 : e.rl[i];
						sl = nl[0];
						for (var i in nl) if (sl>nl[i] && nl[i]>0) { sl = nl[i]; ix=i;}
						var m = pw>(e.gw[ix]+e.tabw+e.thumbw) ? 1 : (pw-(e.tabw+e.thumbw)) / (e.gw[ix]);
						newh =  (e.gh[ix] * m) + (e.tabh + e.thumbh);
					}
					var el = document.getElementById(e.c);
					if (el!==null && el) el.style.height = newh+"px";
					el = document.getElementById(e.c+"_wrapper");
					if (el!==null && el) {
						el.style.height = newh+"px";
						el.style.display = "block";
					}
				} catch(e){
					console.log("Failure at Presize of Slider:" + e)
				}
			//});
		  };';
		$script .= '</script>' . "\n";
		echo apply_filters('revslider_add_setREVStartSize', $script);
		
		$SR_GLOBALS['js_startsize_init'] = true;
	}

	/**
	 * make sure every rendered slider gets a unique DOM id, appending _1, _2 … on collisions
	 * @return string the id to use
	 **/
	public function set_html_id_v7($html_id, $check_for_duplication){
		global $SR_GLOBALS;

		if($check_for_duplication){ //check if it already exists, if yes change it and add attribute for console output
			$ids = $this->get_val($SR_GLOBALS, ['collections', 'ids']);
			if(in_array($html_id, $ids, true)){
				$i = 0;
				do{$i++; }while(in_array($html_id.'_'.$i, $ids, true));
				$html_id .= '_'.$i;
			}
		}
		if(!in_array($html_id, $SR_GLOBALS['collections']['ids'])) $SR_GLOBALS['collections']['ids'][] = $html_id;

		return $html_id;
	}

	/**
	 * compress the css
	 * strips comments and superfluous whitespace from generated stylesheets
	 * @return string
	 **/
	public function compress_css($buffer){
		/* remove comments - ?? keeps the uncompressed css if the (nested-quantifier) pattern hits the
		   backtrack limit on large input; preg_replace returns null there and would wipe the css entirely */
		$buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", '', $buffer) ?? $buffer;
		/* remove tabs, spaces, newlines, etc. */
		$arr = ["\r\n", "\r", "\n", "\t", '  ', '    ', '    '];
		$rep = ['', '', '', '', ' ', ' ', ' '];
		$buffer = str_replace($arr, $rep, $buffer);
		/* remove whitespaces around {}:, */
		$buffer = preg_replace("/\s*([\{\}:,])\s*/", "$1", $buffer) ?? $buffer;
		/* remove last ; */
		$buffer = str_replace(';}', '}', $buffer);
		
		return $buffer;
	}

	/**
	 * parse css stylesheet to an array
	 * @return array selector => [property => value]
	 **/
	public function css_to_array($css){
		
		while(strpos($css, '/*') !== false){
			if(strpos($css, '*/') === false) return false;
			$start	= strpos($css, '/*');
			$end	= strpos($css, '*/') + 2;
			$css	= str_replace(substr($css, $start, $end - $start), '', $css);
		}
		
		preg_match_all('/(?ims)([a-z0-9\,\s\.\:#_\-@]+)\{([^\}]*)\}/', $css, $arr);

		$result = [];
		foreach($arr[0] as $i => $x){
			$selector = trim($arr[1][$i]);
			if(strpos($selector, '{') !== false || strpos($selector, '}') !== false) return false;
			$rules = explode(';', trim($arr[2][$i]));
			$result[$selector] = [];
			foreach($rules as $strRule){
				if(!empty($strRule)){
					$rule = explode(':', $strRule);
					
					//put back everything but not $rule[0];
					$key = trim($rule[0]);
					unset($rule[0]);
					$values = implode(':', $rule);
					
					$result[$selector][trim($key)] = trim(str_replace("'", '"', $values));
				}
			}
		}
		
		return $result;
	}

	
	/**
	 * Get nested array value by path
	 */
	/*public function array_get_path(&$array, $path, $default = false) {
		$ref = &$array;
		foreach ($path as $key) {
			if (!is_array($ref) || !array_key_exists($key, $ref)) {
				return $default;
			}
			$ref = &$ref[$key];
		}
		return $ref;
	}

	/**
	 * Set nested array value by path
	 */
	/*public function array_set_path(&$array, $path, $value) {
		$ref = &$array;
		foreach ($path as $key) {
			if (!isset($ref[$key]) || !is_array($ref[$key])) {
				$ref[$key] = [];
			}
			$ref = &$ref[$key];
		}
		$ref = $value;
	}*/


	/**
	 * read a nested array value by a path of keys
	 * @return mixed $default when any level of the path is missing
	 */
	public function array_get_path($root, array $path, $default = false) {
		if(empty($path)) return $default;

		$key = array_shift($path);

		if($key === '__ARRAY__'){
			if(!is_array($root)) return $default;
			$out = [];
			foreach ($root as $item) {
				$out[] = empty($path) ? $item : $this->array_get_path($item, $path, $default);
			}
			return $out;
		}

		if(!is_array($root) || !array_key_exists($key, $root)) return $default;
		if(empty($path)) return $root[$key];

		return $this->array_get_path($root[$key], $path, $default);
	}

	/**
	 * write a nested array value by a path of keys, creating the levels on the way
	 * @param array $root modified by reference
	 * @return void
	 */
	public function array_set_path(&$root, array $path, $value) {
		if(empty($path)) return;

		$key = array_shift($path);

		if($key === '__ARRAY__'){
			if(!is_array($root)) return;

			$isListValue = is_array($value);
			$i = 0;
			foreach ($root as $idx => &$item) {
				$valForThis = $isListValue && array_key_exists($i, $value) ? $value[$i] : $value;

				if (!empty($path)) {
					$this->array_set_path($item, $path, $valForThis);
				} else {
					// At ['...','__ARRAY__'] the element itself is the leaf; set it directly.
					$item = $valForThis;
				}
				$i++;
			}
			unset($item);
			return;
		}

		// Build shells as needed
		if(!isset($root[$key]) || !is_array($root[$key])){
			if (!isset($root[$key])) $root[$key] = [];
			elseif (!is_array($root[$key])) $root[$key] = [];
		}

		if(empty($path)){
			$root[$key] = $value;
			return;
		}

		$this->array_set_path($root[$key], $path, $value);
	}
	
	/**
	 * get options depending on given keys as array
	 * $db -> get directly from database, every time its true
	 * @param array|string $keys  path inside the option, [] for everything
	 * @param string       $field which option to read; the fonts branch lives in its own (see OPTIONS_FONTS)
	 * @return mixed
	 */
	public function get_options($keys = [], $default = '', $db = false, $field = 'sr-options'){
		global $SR_GLOBALS;
		$options_field = ($field === 'sr-options') ? 'options' : $field;

		$options = ($db === false && !empty($SR_GLOBALS[$options_field])) ? $SR_GLOBALS[$options_field] : get_option($field, '');
		
		if(!is_array($options)) $options = json_decode($options, true);

		if($db === false && empty($SR_GLOBALS[$options_field])) $SR_GLOBALS[$options_field] = $options;

		return (empty($keys)) ? $options : $this->get_val($options, $keys, $default);
	}

	/**
	 * save all options into json format
	 * replaces the whole option; use update_option() to change a single path
	 * @return void
	 */
	public function update_all_options($options, $field = 'sr-options'){
		global $SR_GLOBALS;

		//never autoload the fonts blob - it is large and only read while rendering/managing fonts.
		//null = leave WordPress' default/existing autoload flag untouched for every other field.
		update_option($field, $options, ($field === self::OPTIONS_FONTS) ? false : null);

		$options_field = ($field === 'sr-options') ? 'options' : $field;

		$SR_GLOBALS[$options_field] = $options;

		do_action('revslider_update_all_options', $options, $field);
	}

	/**
	 * update internal options by given keys of array
	 * revslider-valid and revslider-code will be double saved for the usage in older addons
	 * so that they can still be updated if they are on an older version pre v7
	 * @return void
	 */
	public function update_option($keys, $value, $field = 'sr-options'){
		$extra_save = [
			'revslider-valid' => ['system', 'valid'],
			'revslider-code' => ['system', 'license'],
		];
		$options = $this->get_options([], '', true, $field);
		if(empty($options)) $options = [];

		$this->set_val($options, $keys, $value);
		$this->update_all_options($options, $field);

		foreach($extra_save as $handle => $v){
			if(array_diff($v, $keys)) continue;
			update_option($handle, $value);
			break;
		}
	}

	/**
	 * deletes the option in our internal options by given keys of array
	 * @return bool false when the path does not exist
	 */
	public function delete_option($keys, $field = 'sr-options'){
		$options	= $this->get_options([], '', true, $field);
		$_options	= &$options;
		$keys		= (array)$keys;
		$last_key	= array_pop($keys);

		foreach($keys ?? [] as $key){
			if(!isset($_options[$key])) return false;
			$_options = &$_options[$key];
		}

		if(!isset($_options[$last_key])) return false;
		
		unset($_options[$last_key]);
		
		$this->update_all_options($options, $field);

		return true;
	}

	
	/**
	 * Create a temporary fake page/post
	 * a WP_Post that exists only in memory (id 999999999), so the_content filters can be run against
	 * arbitrary content - used by the preview and the HTML export
	 * @since: 6.0
	 * @return WP_Post
	 **/
	public function create_fake_post($content, $title = 'Slider Revolution'){
		$fake_id				= 999999999;
		$post					= new stdClass();
		$post->ID				= $fake_id;
		$post->post_author		= get_current_user_id();
		$post->post_date		= current_time('mysql');
		$post->post_date_gmt	= current_time('mysql', 1);
		$post->post_title		= $title;
		$post->post_content		= $content;
		$post->post_status		= 'publish';
		$post->comment_status	= 'closed';
		$post->ping_status		= 'closed';
		$post->post_name		= 'rs-fake-page-' . rand(1, 99999);
		$post->post_type		= 'page';
		$post->filter			= 'raw';

		$wp_post = new WP_Post($post);

		wp_cache_add($wp_post->ID, $wp_post, 'posts');

		return $wp_post;
	}


	/**
	 * global function to add revslider textdomain
	 * @return string
	 **/
	public function _t($text) {
		return __($text, 'revslider');
	}

	/**
	 * return filetime or SR version for cache busting
	 * @param string $path relative to the plugin directory
	 * @return int|string the file mtime, RS_REVISION when the file is missing
	 */
	public static function asset_time($path){
		$fullPath = RS_PLUGIN_PATH . $path;
		return file_exists($fullPath) ? filemtime($fullPath) : RS_REVISION;
	}

}
