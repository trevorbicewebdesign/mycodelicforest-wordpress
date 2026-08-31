<?php
/**
 * External Sources Instagram Class
 * @since: 5.0
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Instagram
 *
 * with help of the API this class delivers all kind of Images from instagram
 *
 * @package    socialstreams
 * @subpackage socialstreams/instagram
 * @author     ThemePunch <info@themepunch.com>
 */

/**
 * Instagram stream source.
 *
 * Same shape as the Facebook integration: OAuth and API calls are proxied by the ThemePunch servers, the
 * resulting token lives on the slider, and every fetch keeps a short lived cache plus a long lived "_bk"
 * fallback used when the API errors out. Instagram tokens expire, so refresh_token() renews them.
 */
class RevSliderInstagram extends RevSliderFunctions {

	const TRANSIENT_PREFIX = 'revslider_ig_';

	const URL_IG_AUTH = 'https://updates.themepunch.tools/ig/login.php';
	const URL_IG_API = 'https://updates.themepunch.tools/ig/api.php';

	const QUERY_SHOW = 'ig_show';
	const QUERY_TOKEN = 'ig_token';
	const QUERY_CONNECTWITH = 'ig_user';
	const QUERY_ERROR = 'ig_error_message';

	/**
	 * Stream Array
	 * @access   private
	 * @var      array    $stream    Stream Data Array
	 */
	private $stream;

	/**
	 * instagram data transient expiration time in seconds
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var int  $transient_sec
	 */
	private $transient_sec;
	
	/**
	 * instagram token transient expiration time
	 * trigger token_refresh on expire
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var int  $transient_token_sec
	 */
	private $transient_token_sec;

	/**
	 * Initialize the class and set its properties.
	 * @param int $transient_sec  Transient time in seconds
	 */
	public function __construct($transient_sec = DAY_IN_SECONDS){
		$this->transient_sec = $transient_sec;
		$this->transient_token_sec = MONTH_IN_SECONDS;
	}

	/**
	 * @return int
	 */
	public function getTransientSec(){
		return $this->transient_sec;
	}

	/**
	 * @param int $transient_sec
	 * @return void
	 */
	public function setTransientSec($transient_sec){
		$this->transient_sec = $transient_sec;
	}

	/**
	 * @param int $transient_token_sec
	 * @return void
	 */
	public function setTransientTokenSec($transient_token_sec)
	{
		$this->transient_token_sec = $transient_token_sec;
	}

	/** @return void */
	public function add_actions(){
		add_action('init', [&$this, 'do_init'], 5);
		add_action('admin_footer', [&$this, 'footer_js']);
		add_action('revslider_slider_on_delete_slider', [&$this, 'on_delete_slider'], 10, 1);
	}

	/**
	 * check if we have QUERY_ARG set
	 * try to login the user
	 * @return void handles the OAuth callback and stores the received token on the slider
	 */
	public function do_init(){
		// are we on revslider page?
		if($this->get_val($_GET, 'page') != 'revslider') return;

		//instagram returned error
		if(isset($_GET[self::QUERY_ERROR])) return;

		//we need token and slide ID / slider alias to proceed with saving token
		if(!isset($_GET[self::QUERY_TOKEN]) || !isset($_GET['module'])) return;

		$sr_admin = RevSliderGlobals::instance()->get('RevSliderAdmin');
		if(!current_user_can($sr_admin->get_user_role())){
			$_GET[self::QUERY_ERROR] = __('Bad Request', 'revslider');
			return;
		}

		$token		 = sanitize_text_field($_GET[self::QUERY_TOKEN]);
		$connectwith = isset($_GET[self::QUERY_CONNECTWITH]) ? sanitize_text_field($_GET[self::QUERY_CONNECTWITH]) : '';
		$id			 = $this->get_val($_GET, 'slide');
		$slider_id	 = $this->get_val($_GET, 'module');
		$nonce		 = $this->get_val($_GET, 'rs_ig_nonce');
		$slider		 = new RevSliderSlider();
		
		if(empty($slider_id)){
			$slide = new RevSliderSlide();
			$slide->init_by_id($id);

			$slider_id = $slide->get_slider_id();
			if(intval($slider_id) == 0){
				$_GET[self::QUERY_ERROR] = __('Slider could not be loaded', 'revslider');
				return;
			}
		}

		$slider->init_by_id($slider_id);
		if($slider->inited === false){
			$_GET[self::QUERY_ERROR] = __('Slider could not be loaded', 'revslider');
			return;
		}

		if(wp_verify_nonce($nonce, self::get_nonce_name($slider_id)) == false){
			$_GET[self::QUERY_ERROR] = __('Bad Request', 'revslider');
			return;
		}

		// save token transient to trigger refresh on expire
		set_transient( $this->get_transient_name('token', $token), $token, $this->transient_token_sec);

		$slider->set_param(['source', 'instagram', 'token_source'], 'account');
		$slider->set_param(['source', 'instagram', 'token'], $token);
		$slider->set_param(['source', 'instagram', 'connect_with'], $connectwith);
		$slider->update_params([]);

		//redirect - use the site's own host (from home_url), not the client-supplied Host header, to avoid host-header injection into the redirect target
		$_site = wp_parse_url(home_url());
		$_host = $this->get_val($_site, 'host', '') . (empty($_site['port']) ? '' : ':' . $_site['port']);
		$url = sanitize_url( set_url_scheme( 'http://' . $_host . $_SERVER['REQUEST_URI'] ) );
		$url = add_query_arg([self::QUERY_TOKEN => false, 'rs_ig_nonce' => false, self::QUERY_SHOW => 1], $url);
		wp_redirect($url);
		exit();
	}

	/** @return void echoes a script that reopens the source panel after the OAuth redirect */
	public function footer_js(){
		// are we on revslider page?
		if($this->get_val($_GET, 'page') != 'revslider') return;

		if(isset($_GET[self::QUERY_SHOW]) || isset($_GET[self::QUERY_ERROR])) echo '<script>SR7.openEditorView = "module.source";</script>'."\n";
		if(isset($_GET[self::QUERY_ERROR])) echo '<script>SR7.postOpenMessage = "'. __('Instagram Reports: ', 'revslider') . esc_html($_GET[self::QUERY_ERROR]) .'";</script>'."\n";
	}

	/** @return string|false the OAuth URL the editor sends the user to */
	public static function get_login_url($id, $slide_id){
		return (!empty($id)) ? self::URL_IG_AUTH . '?state=' . base64_encode( admin_url('admin.php?page=revslider&view=editor&module='.$id.'&slide='.$slide_id.'&rs_ig_nonce='.wp_create_nonce(self::get_nonce_name($id)))) : false;
	}
	
	/**
	 * get grid transient name
	 *
	 * @param mixed  $id  transient id (grid id or string)
	 * @param string $token
	 * @param int    $count
	 * @return string
	 */
	public function get_transient_name($id, $token, $count = 0){
		return self::TRANSIENT_PREFIX . $id . '_' . md5(json_encode($token . '_' . $count));
	}

	/**
	 * refresh Instagram token if needed
	 *
	 * @param  string $token
	 * @return string
	 */
	public function refresh_token($token){
		if(empty($token)){
			return $token;
		}
		
		$transient_token = $this->get_transient_name('token', $token);
		if($this->transient_token_sec > 0 && false !== ($data = get_transient($transient_token))){
			return $token;
		}

		//$request contain new token, however old token expiry date also updated, so we could still use it
		$request = $this->_callAPI([
			'action' => 'refresh_token',
			'token' => $token,
		]);
		if(isset($request['data']['access_token'])){
			set_transient($transient_token, $token, $this->transient_token_sec);
			return $token;
		}
		
		return '';
	}

	/**
	 * Get Instagram User Profile
	 *
	 * @param string $token Instagram Access Token
	 * @return mixed
	 */
	public function get_user_profile($token){
		$this->refresh_token($token);
		
		$transient_name = 'revslider_'. md5('instagram-profile-' . $token);
		//the profile changes rarely - cache it for at least a day regardless of the (short) stream TTL
		$profile_ttl = max((int)$this->transient_sec, DAY_IN_SECONDS);
		if(false !== ($data = get_transient($transient_name))){
			return $data;
		}

		$profile = $this->_callAPI([
			'action' => 'profile',
			'token' => $token,
		]);
		if(isset($profile['data'])){
			$profile = $profile['data'];
			set_transient($transient_name, $profile, $profile_ttl);
			set_transient($transient_name.'_bk', $profile, WEEK_IN_SECONDS);
			return $profile;
		}
		//stale-on-error: serve the last good profile instead of null when the API call fails
		$backup = get_transient($transient_name.'_bk');
		return ($backup !== false) ? $backup : null;
	}


	/**
	 * Get Instagram User Pictures
	 * 
	 * @param int $slider_id slider ID
	 * @param string $token Instagram Access Token
	 * @param string $count media count
	 * @param string $orig_image
	 * @return mixed
	 */
	public function get_public_photos($slider_id, $token, $count, $orig_image = ''){
		$this->refresh_token($token);
		
		$transient_name = $this->get_transient_name($slider_id, $token, $count);
		if($this->transient_sec > 0 && false !== ($data = get_transient($transient_name))){
			$this->stream = $data;
			return $this->stream;
		}else{
			delete_transient($transient_name);
		}

		//Getting instagram images
		$medias = $this->_callAPI([
			'action' => 'public_photos',
			'token' => $token,
			'count' => $count,
		]);
		if(isset($medias['data']['data'])){
			$this->instagram_output_array($medias['data']['data'], $count);
		}
		if(!empty($this->stream)){
			set_transient($transient_name, $this->stream, $this->transient_sec);
			set_transient($transient_name.'_bk', $this->stream, WEEK_IN_SECONDS);
			return $this->stream;
		}else{
			//stale-on-error: serve the last good media set instead of an error when the API call fails
			$backup = get_transient($transient_name.'_bk');
			if($backup !== false){
				$this->stream = $backup;
				return $this->stream;
			}

			$err = (isset($medias['error']) && $medias['error'] === true) ? $this->get_val($medias, 'message') : translate('Instagram reports: Please check the settings','revslider');
			$_err = $this->get_val($medias, ['data', 'error', 'message']);
			$err = (!empty($_err)) ? $_err : $err;

			echo $err;
			return false;
		}
	}

	/**
	 * @param array $args
	 * @return array
	 */
	protected function _callAPI($args = []){
		$rslb = RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		$request = $rslb->call_url(self::URL_IG_API, $args);
		if(is_wp_error($request)) return ['error' => true, 'message' => 'Instagram API error: ' . $request->get_error_message()];

		$responseData = json_decode(wp_remote_retrieve_body($request), true);
		return (empty($responseData)) ? ['error' => true, 'message' => 'Instagram API error: Empty response body or wrong data format'] : $responseData;
	}

	/** @return mixed a request variable with a default */
	function input($name, $default = null){
		return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
	}

	/**
	 * Prepare output array $stream
	 *
	 * @param    array $photos Instagram Output Data
	 * @param    int $count resulting number of items
	 * @return int
	 */
	private function instagram_output_array($photos, $count){
		$this->stream = [];

		foreach($photos ?? [] as $photo){
			if($count <= 0) break;

			$count--;
			$shortcode = '';

			preg_match('/.+\/p\/(.+)?\//m', $photo['permalink'], $matches);
			if(isset($matches[1])) $shortcode = $matches[1];
			
			$photo['display_url'] = isset($photo['media_url']) ? $photo['media_url'] : '';
			if($photo['media_type'] == 'VIDEO'){
				$photo['display_url'] = isset($photo['thumbnail_url']) ? $photo['thumbnail_url'] : '';
				$photo['thumbnail_src'] = $photo['display_url'];
				$photo['videos']['standard_resolution']['url'] = isset($photo['media_url']) ? $photo['media_url'] : '';
			}
			$photo['link'] = isset($photo['permalink']) ? $photo['permalink'] : '';
			$photo['shortcode'] = $shortcode;
			$photo['taken_at_timestamp'] = isset($photo['timestamp']) ? $photo['timestamp'] : '';
			$photo['edge_media_to_caption']['edges'][0]['node']['text'] = isset($photo['caption']) ? $photo['caption'] : '';
			$this->stream[] = $photo;
		}

		return $count;
	}

	/**
	 * delete slider ig transients upon deletion
	 *
	 * @param	$id		slider id
	 * @return	void
	 */
	public function on_delete_slider($id){
		global $wpdb;

		if(empty($id)) return;

		$prefix = self::TRANSIENT_PREFIX . $id;
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE `option_name` LIKE '%s'", '%'.$prefix.'%'));
	}

	/**
	 * @param int|string $p
	 * @return string
	 */
	public static function get_nonce_name($p){
		return self::TRANSIENT_PREFIX . 'nonce_' . $p;
	}

}
