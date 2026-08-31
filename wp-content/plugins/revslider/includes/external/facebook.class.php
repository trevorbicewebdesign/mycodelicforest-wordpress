<?php
/**
 * External Sources Facebook Class
 * @since: 5.0
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Facebook
 *
 * with help of the API this class delivers album images from Facebook
 *
 * @package    socialstreams
 * @subpackage socialstreams/facebook
 * @author     ThemePunch <info@themepunch.com>
 */

/**
 * Facebook stream source.
 *
 * The site never talks to Facebook directly: the OAuth dance and the Graph calls run through the
 * ThemePunch servers (see RevSliderLoadBalancer), which hold the app credentials. do_init() catches the
 * redirect coming back from that flow and stores the returned token on the slider.
 *
 * Every fetch is cached twice - a short lived transient for normal operation, plus a long lived "_bk"
 * copy that is served when the API errors out, so a slider keeps rendering during an outage.
 */
class RevSliderFacebook extends RevSliderFunctions {

	const TRANSIENT_PREFIX	= 'revslider_fb_';
	
	const URL_FB_AUTH		= 'fb/login.php';
	const URL_FB_API		= 'fb/api.php';

	const QUERY_SHOW		= 'fb_show';
	const QUERY_TOKEN		= 'fb_token';
	const QUERY_PAGE_ID		= 'fb_page_id';
	const QUERY_CONNECTWITH	= 'fb_page_name';
	const QUERY_ERROR		= 'fb_error_message';

	/**
	 * @var int  Transient time in seconds
	 */
	private $transient_sec;

	public function __construct($transient_sec = 1200){
		$this->transient_sec = 	$transient_sec;
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

		//fb returned error
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
		$page_id	 = isset($_GET[self::QUERY_PAGE_ID]) ? sanitize_text_field($_GET[self::QUERY_PAGE_ID]) : '';
		$id			 = $this->get_val($_GET, 'slide');
		$slider_id	 = $this->get_val($_GET, 'module');
		$nonce		 = $this->get_val($_GET, 'rs_fb_nonce');
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

		$slider->set_param(['source', 'facebook', 'token_source'], 'account');
		$slider->set_param(['source', 'facebook', 'appId'], $token);
		$slider->set_param(['source', 'facebook', 'page_id'], $page_id);
		$slider->set_param(['source', 'facebook', 'connect_with'], $connectwith);
		$slider->update_params([]);

		//redirect - use the site's own host (from home_url), not the client-supplied Host header, to avoid host-header injection into the redirect target
		$_site = wp_parse_url(home_url());
		$_host = $this->get_val($_site, 'host', '') . (empty($_site['port']) ? '' : ':' . $_site['port']);
		$url = set_url_scheme('http://' . $_host . $_SERVER['REQUEST_URI']);
		$url = add_query_arg([self::QUERY_TOKEN => false, self::QUERY_PAGE_ID => false, self::QUERY_CONNECTWITH => false, 'rs_fb_nonce' => false, self::QUERY_SHOW => 1], $url);
		wp_redirect($url);
		exit();
	}

	/** @return void echoes a script that reopens the source panel after the OAuth redirect */
	public function footer_js(){
		// are we on revslider page?
		if($this->get_val($_GET, 'page') != 'revslider') return;

		if(isset($_GET[self::QUERY_SHOW]) || isset($_GET[self::QUERY_ERROR])) echo '<script>SR7.openEditorView = "module.source";</script>'."\n";
		if(isset($_GET[self::QUERY_ERROR]))	echo '<script>SR7.postOpenMessage = "'. __('Facebook API error: ', 'revslider') . esc_html($_GET[self::QUERY_ERROR]) .'";</script>'."\n";
	}

	/** @return string|false the OAuth URL the editor sends the user to */
	public static function get_login_url($id, $slide_id){
		$rslb	= RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		
		return (!empty($id)) ? $rslb->get_url('updates') . '/' . self::URL_FB_AUTH . '?state=' . base64_encode( admin_url('admin.php?page=revslider&view=editor&module='.$id.'&slide='.$slide_id.'&rs_fb_nonce='.wp_create_nonce(self::get_nonce_name($id)))) : false;
	}

	/** @return array the decoded response, or ['error' => true, 'message' => ...] */
	protected function _make_api_call($args = []){
		global $wp_version;

		$rslb = RevSliderGlobals::instance()->get('RevSliderLoadBalancer');

		$response = wp_safe_remote_post($rslb->get_url('updates') . '/' . self::URL_FB_API, [
			'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url'),
			'body'		 => $args,
			'timeout'	 => 45
		]);

		if(is_wp_error($response)) return ['error' => true, 'message' => 'Facebook API error: ' . $response->get_error_message()];

		$responseData = json_decode($response['body'], true);
		return (empty($responseData)) ? ['error' => true, 'message' => 'Facebook API error: Empty response body or wrong data format'] : $responseData;
	}

	/** @return array the cached stream, falling back to the long lived backup copy on an API error */
	protected function _get_transient_fb_data($requestData){
		$transient_name = self::TRANSIENT_PREFIX . $requestData['slider_id'] . '_' . md5(json_encode($requestData));
		if($this->transient_sec > 0 && false !== ($data = get_transient($transient_name))) return $data;

		$responseData = $this->_make_api_call($requestData);
		//stale-on-error: serve the last good copy instead of nothing when the API call fails
		if(!empty($responseData['error']) || !isset($responseData['data'])){
			$backup = get_transient($transient_name.'_bk');
			return ($backup !== false) ? $backup : [];
		}

		set_transient($transient_name, $responseData['data'], $this->transient_sec);
		set_transient($transient_name.'_bk', $responseData['data'], WEEK_IN_SECONDS);
		return $responseData['data'];
	}

	/**
	 * Get Photosets List from User
	 *
	 * @param	string	$access_token 	page access token
	 * @param	string	$page_id 	page id
	 * @return	mixed
	 */
	public function get_photo_sets($access_token, $page_id){
		return $this->_make_api_call([
			'token'		=> $access_token,
			'page_id'	=> $page_id,
			'action'	=> 'albums',
		]);
	}

	/**
	 * Get Photosets List from User as Options for Selectbox
	 *
	 * @param	string	$access_token 	page access token
	 * @param	string	$page_id 	page id
	 * @return	mixed	options html string | array('error' => true, 'message' => '...');
	 */
	public function get_photo_set_photos_options($access_token, $page_id){
		$photo_sets = $this->get_photo_sets($access_token, $page_id);

		if($photo_sets['error']) return $photo_sets;

		$return = [];
		if(!is_array($photo_sets['data'])) return $return;
	
		foreach($photo_sets['data'] ?? [] as $photo_set){
			$return[] = [
				'value'	=> $this->get_val($photo_set, ['id']),
				'text'	=> $this->get_val($photo_set, ['name']),
			];
		}

		return $return;
	}

	/**
	 * Get Photoset Photos
	 *
	 * @param	mixed	$slider_id 	slider id
	 * @param	string	$access_token 	page access token
	 * @param	string	$album_id 	Album ID
	 * @param	int 	$item_count 	items count
	 * @return	array
	 */
	public function get_photo_set_photos($slider_id, $access_token, $album_id, $item_count = 8){
		$requestData = [
			'slider_id'	=> $slider_id,
			'token'		=> $access_token,
			'action'	=> 'photos',
			'album_id'	=> $album_id,
			'limit'		=> $item_count,
		];
		return $this->_get_transient_fb_data($requestData);
	}

	/**
	 * Get Feed
	 *
	 * @param	mixed	$slider_id 	slider id
	 * @param	string	$access_token 	page access token
	 * @param	string	$page_id 	page id
	 * @param	int 	$item_count 	items count
	 * @return	array
	 */
	public function get_photo_feed($slider_id, $access_token, $page_id, $item_count = 8){
		$requestData = [
			'slider_id'	=> $slider_id,
			'token'		=> $access_token,
			'page_id'	=> $page_id,
			'action'	=> 'feed',
			'limit'		=> $item_count,
		];
		return $this->_get_transient_fb_data($requestData);
	}

	/**
	 * delete slider fb transients upon deletion
	 * 
	 * @param	$id		slider id
	 * @return	void
	 */
	public function on_delete_slider($id){
		global $wpdb;

		if(empty($id)) return;

		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE `option_name` LIKE '%s'", '%'.self::TRANSIENT_PREFIX . $id.'%'));
	}

	/**
	 * @param int|string $p
	 * @return string
	 */
	public static function get_nonce_name($p){
		return self::TRANSIENT_PREFIX . 'nonce_' . $p;
	}

}
