<?php
/**
 * External Sources Flickr Class
 * @since: 5.0
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

if (!defined('ABSPATH')) exit();

/**
 * Flickr
 *
 * with help of the API this class delivers all kind of Images from flickr
 *
 * @package    socialstreams
 * @subpackage socialstreams/flickr
 * @author     ThemePunch <info@themepunch.com>
 */

/**
 * Flickr stream source. Unlike Facebook/Instagram this talks to the Flickr API directly with the API key
 * the user entered, so there is no OAuth flow - just cached REST calls.
 */
class RevSliderFlickr extends RevSliderFunctions {

	/**
	 * API key
	 * @var      string    $api_key    flickr API key
	 */
	private $api_key;

	/**
	 * API params
	 * @var      array    $api_param_defaults    Basic params to call with API
	 */
	private $api_param_defaults;

	/**
	 * Stream Array
	 * @var      array    $stream    Stream Data Array
	 */
	private $stream;

	/**
	 * Basic URL
	 * @var      string    $url    Url to fetch user from
	 */
	private $flickr_url;

	/**
	 * Transient seconds
	 * @var      number    $transient Transient time in seconds
	 */
	private $transient_sec;

	/**
	 * Initialize the class and set its properties.
	 * @param      string    $api_key	flickr API key.
	 */
	public function __construct($api_key, $transient_sec = 1200){
		$this->api_key = $api_key;
		$this->api_param_defaults = [
			'api_key' => $this->api_key,
			'format' => 'json',
			'nojsoncallback' => 1,
		];

		$this->transient_sec = $transient_sec;
	}

	/**
	 * Calls Flicker API with set of params, returns json
	 * @param    array    $params 	Parameter build for API request
	 * @return array|false the decoded API response, cached in a transient
	 */
	private function call_flickr_api($params, $ttl = null){
		//build url
		$encoded_params = [];
		foreach($params ?? [] as $k => $v){
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}

		//call the API and decode the response
		$url = 'https://api.flickr.com/services/rest/?'.implode('&', $encoded_params);
		$transient_name = 'revslider_' . md5($url);
		$ttl = ($ttl === null) ? $this->transient_sec : $ttl;

		if($ttl > 0 && false !== ($data = get_transient($transient_name))) return ($data);

		//wp_remote_get with a capped (filterable) timeout instead of file_get_contents, which has no
		//timeout (PHP default ~60s), depends on allow_url_fopen, and returns no error object
		$timeout  = (int)apply_filters('revslider_stream_http_timeout', 8);
		$response = wp_remote_get($url, ['timeout' => $timeout]);
		$body     = (is_wp_error($response)) ? '' : wp_remote_retrieve_body($response);
		$rsp      = ($body !== '') ? json_decode($body) : null;

		//stale-on-error: serve the last good copy instead of nothing when the API is slow/down
		if(!isset($rsp)){
			$backup = get_transient($transient_name.'_bk');
			return ($backup !== false) ? $backup : '';
		}

		if($ttl > 0) set_transient($transient_name, $rsp, $ttl);
		set_transient($transient_name.'_bk', $rsp, WEEK_IN_SECONDS); //long-lived backup for error fallback
		return $rsp;
	}

	/**
	 * Get User ID from its URL
	 * @param    string    $user_url URL of the Gallery
	 * @return string the numeric Flickr user id behind a profile URL
	 */
	public function get_user_from_url($user_url){
		//gallery params
		$user_params = $this->api_param_defaults + [
			'method'  => 'flickr.urls.lookupUser',
			'url' => $user_url,
		];

		//set User Url
		$this->flickr_url = $user_url;

		//get gallery info
		$user_info = $this->call_flickr_api($user_params, WEEK_IN_SECONDS); //URL->ID rarely changes - cache it long

		return $this->get_val($user_info, ['user', 'id'], '');
	}

	/**
	 * Get Group ID from its URL
	 * @param    string    $group_url URL of the Gallery
	 * @return string
	 */
	public function get_group_from_url($group_url){
		//gallery params
		$group_params = $this->api_param_defaults + [
			'method'  => 'flickr.urls.lookupGroup',
			'url' => $group_url,
		];

		//set User Url
		$this->flickr_url = $group_url;

		//get gallery info
		$group_info = $this->call_flickr_api($group_params, WEEK_IN_SECONDS); //URL->ID rarely changes - cache it long

		return $this->get_val($group_info, ['group', 'id'], '');
	}

	/**
	 * Get Public Photos
	 * @param    string    $user_id 	flicker User id (not name)
	 * @param    int       $item_count 	number of photos to pull
	 * @return array
	 */
	public function get_public_photos($user_id, $item_count = 10){
		//public photos params
		$public_photo_params = $this->api_param_defaults + [
			'method'  => 'flickr.people.getPublicPhotos',
			'user_id' => $user_id,
			'extras'  => 'description, license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o',
			'per_page'=> $item_count,
			'page' => 1
		];

		//get photo list
		$public_photos_list = $this->call_flickr_api($public_photo_params);

		return $this->get_val($public_photos_list, ['photos', 'photo'], '');
	}

	/**
	 * Get Photosets List from User
	 * @param    string    $user_id 	flicker User id (not name)
	 * @param    int       $item_count 	number of photos to pull
	 * @return array the albums as value/text pairs for the editor's select
	 */
	public function get_photo_sets($user_id, $item_count){ //item count default is 10
		//photoset params
		$photo_set_params = $this->api_param_defaults + [
			'method'  => 'flickr.photosets.getList',
			'user_id' => $user_id,
			'per_page'=> $item_count,
			'page'    => 1
		];

		//get photoset list
		$photo_sets_list = $this->call_flickr_api($photo_set_params);
		$photo_sets		 = $this->get_val($photo_sets_list, ['photosets', 'photoset'], []);
		$return = [];
		foreach($photo_sets ?? [] as $photo_set){
			if(empty($photo_set->title->_content)) $photo_set->title->_content = "";
			if(empty($photo_set->photos)) $photo_set->photos = 0;

			$return[] = [
				'value'	 => $this->get_val($photo_set, ['id']),
				'text'	 => $this->get_val($photo_set, ['title', '_content']),
				'photos' => $this->get_val($photo_set, ['photos']),
			];
		}
		
		return $return;
	}

	/**
	 * Get Photoset Photos
	 * @param    string    $photo_set_id 	Photoset ID
	 * @param    int       $item_count 	number of photos to pull
	 * @return array
	 */
	public function get_photo_set_photos($photo_set_id,$item_count=10){
		//photoset photos params
		$this->stream = [];
		$photo_set_params = $this->api_param_defaults + [
			'method'  		=> 'flickr.photosets.getPhotos',
			'photoset_id' 	=> $photo_set_id,
			'per_page'		=> $item_count,
			'page'    		=> 1,
			'extras'		=> 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o'
		];

		//get photo list
		$photo_set_photos = $this->call_flickr_api($photo_set_params);

		return $this->get_val($photo_set_photos, ['photoset', 'photo'], '');
	}

	/**
	 * Get Groop Pool Photos
	 * @param    string    $group_id 	Photoset ID
	 * @param    int       $item_count 	number of photos to pull
	 * @return array
	 */
	public function get_group_photos($group_id,$item_count=10){
		//photoset photos params
		$group_pool_params = $this->api_param_defaults + [
			'method'  		=> 'flickr.groups.pools.getPhotos',
			'group_id' 	=> $group_id,
			'per_page'		=> $item_count,
			'page'    		=> 1,
			'extras'		=> 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o'
		];

		//get photo list
		$group_pool_photos = $this->call_flickr_api($group_pool_params);

		return $this->get_val($group_pool_photos, ['photos', 'photo'], '');
	}

	/**
	 * Get Gallery ID from its URL
	 * @param    string    $gallery_url URL of the Gallery
	 * @param    int       $item_count 	number of photos to pull
	 * @return string
	 */
	public function get_gallery_from_url($gallery_url){
		//gallery params
		$gallery_params = $this->api_param_defaults + [
			'method'  => 'flickr.urls.lookupGallery',
			'url' => $gallery_url,
		];

		//get gallery info
		$gallery_info = $this->call_flickr_api($gallery_params, WEEK_IN_SECONDS); //URL->ID rarely changes - cache it long

		return $this->get_val($gallery_info, ['gallery', 'id'], '');
	}

	/**
	 * Get Gallery Photos
	 * @param    string    $gallery_id 	flicker Gallery id (not name)
	 * @param    int       $item_count 	number of photos to pull
	 * @return array
	 */
	public function get_gallery_photos($gallery_id,$item_count=10){
		//gallery photos params
		$gallery_photo_params = $this->api_param_defaults + [
			'method'  => 'flickr.galleries.getPhotos',
			'gallery_id' => $gallery_id,
			'extras'  => 'description, license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, tags, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_q, url_m, url_n, url_z, url_c, url_l, url_o',
			'per_page'=> $item_count,
			'page' => 1
		];

		//get photo list
		$gallery_photos_list = $this->call_flickr_api($gallery_photo_params);

		return $this->get_val($gallery_photos_list, ['photos', 'photo'], '');
	}
}	// End Class