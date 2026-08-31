<?php
/**
 * External Sources YouTube Class
 * @since: 5.0
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.sliderrevolution.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Youtube
 *
 * with help of the API this class delivers all kind of Images/Videos from youtube
 *
 * @package    socialstreams
 * @subpackage socialstreams/youtube
 * @author     ThemePunch <info@themepunch.com>
 */

/**
 * YouTube stream source: fetches a playlist's or channel's videos with the user's API key, cached like the
 * other streams with a long lived "_bk" copy as fallback.
 */
class RevSliderYoutube extends RevSliderFunctions {

	/**
	 * API key
	 *
	 * @access   private
	 * @var      string    $api_key    Youtube API key
	 */
	private $api_key;

	/**
	 * Channel ID
	 *
	 * @access   private
	 * @var      string    $channel_id    Youtube Channel ID
	 */
	private $channel_id;

	/**
	 * Stream Array
	 *
	 * @access   private
	 * @var      array    $stream    Stream Data Array
	 */
	private $stream;

	/**
	 * Transient seconds
	 *
	 * @access   private
	 * @var      number    $transient Transient time in seconds
	 */
	private $transient_sec;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param      string    $api_key	Youtube API key.
	 */
	public function __construct($api_key, $channel_id, $transient_sec = 1200){
		$this->api_key = $api_key;
		$this->channel_id = $channel_id;
		$this->transient_sec = $transient_sec;
	}

	/**
	 * fetch + decode a remote JSON url with a capped (filterable) timeout so a slow API cannot stall render
	 * @return array|object|null the decoded response body
	 */
	private function remote_json($url, $assoc = false){
		$timeout  = (int)apply_filters('revslider_stream_http_timeout', 8);
		$response = wp_remote_get($url, ['timeout' => $timeout]);
		if(is_wp_error($response)) return ($assoc) ? [] : null;
		$body = wp_remote_retrieve_body($response);
		return ($body !== '') ? json_decode($body, $assoc) : (($assoc) ? [] : null);
	}


	/**
	 * Get Youtube Playlists
	 * @return array
	 */
	public function get_playlists(){
		//call the API and decode the response
		$url = "https://www.googleapis.com/youtube/v3/playlists?part=snippet&maxResults=50&channelId=".$this->channel_id."&key=".$this->api_key;

		return $this->remote_json($url, true);
	}

	/**
	 * Get Youtube Playlist Items
	 *
	 * @param    string    $playlist_id 	Youtube Playlist ID
	 * @param    integer    $count 	Max videos count
	 * @return array the cached videos, falling back to the long lived backup copy on an API error
	 */
	public function show_playlist_videos($playlist_id, $count = 50){
		//call the API and decode the response
		if(empty($count)) $count = 50;

		$url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=".$playlist_id."&maxResults=".$count."&fields=items%2Fsnippet&key=".$this->api_key;

		$transient_name = 'revslider_' . md5($url);

		if($this->transient_sec > 0 && false !== ($data = get_transient($transient_name))) return($data);

		$rsp = $this->remote_json($url);

		if(!isset($rsp->items)){
			$backup = get_transient($transient_name.'_bk'); //stale-on-error fallback
			return ($backup !== false) ? $backup : [];
		}

		set_transient($transient_name, $rsp->items, $this->transient_sec);
		set_transient($transient_name.'_bk', $rsp->items, WEEK_IN_SECONDS);

		return $rsp->items;
	}

	/**
	 * Get Youtube Channel Items
	 *
	 * @param    integer    $count 	Max videos count
	 * @return array the cached videos, falling back to the long lived backup copy on an API error
	 */
	public function show_channel_videos($count = 50){
		if(empty($count)) $count = 50;
		//call the API and decode the response
		$url = 'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId='.$this->channel_id.'&maxResults='.$count.'&key='.$this->api_key.'&order=date';

		$transient_name = 'revslider_' . md5($url);
		if($this->transient_sec > 0 && false !== ($data = get_transient($transient_name))) return ($data);

		$rsp = $this->remote_json($url);

		if(!isset($rsp->items)){
			$backup = get_transient($transient_name.'_bk'); //stale-on-error fallback
			return ($backup !== false) ? $backup : [];
		}

		set_transient($transient_name, $rsp->items, $this->transient_sec);
		set_transient($transient_name.'_bk', $rsp->items, WEEK_IN_SECONDS);

		return $rsp->items;
	}

	/**
	 * Get Playlists from Channel as Options for Selectbox
	 * @return array the playlists as value/text pairs for the editor's select
	 */
	public function get_playlist_options(){
		$return		= [];
		$playlists	= $this->get_playlists();
		$error		= $this->get_val($playlists, 'error', []);
		if(!empty($error)) return $playlists;
		$playlists = $this->get_val($playlists, 'items', []);

		foreach($playlists ?? [] as $playlist){
			$return[] = [
				'value'	 => $this->get_val($playlist, ['id']),
				'text'	 => $this->get_val($playlist, ['snippet', 'title']),
			];
		}

		return $return;
	}
}