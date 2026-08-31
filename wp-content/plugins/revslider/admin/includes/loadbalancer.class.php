<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Picks which ThemePunch server to talk to and performs the request.
 *
 * A list of interchangeable hosts is kept in the options and refreshed monthly; on a failure the list is
 * rotated so the next attempt starts at another host. Also handles the Cloudflare 429 case by parking a
 * retry-after lock in a transient, so a rate limited site stops hammering the server.
 */
class RevSliderLoadBalancer extends RevSliderFunctions {

	public $last_request = null;
	public $servers = [];
	public $defaults = ['themepunch.tools', 'themepunch-ext-a.tools', 'themepunch-ext-b.tools', 'themepunch-ext-c.tools'];
	 

	/**
	 * set the server list on construct
	 **/
	public function __construct(){
		$this->servers = $this->get_options(['system', 'servers'], []);
		if(empty($this->servers)){
			shuffle($this->defaults);
			$this->update_option(['system', 'servers'], $this->defaults);
		}
		
		$this->servers = (empty($this->servers)) ? $this->defaults : $this->servers;
		
		
	}
	
	/** @return array|WP_Error the raw result of the most recent call, so callers can report the real error */
	public function get_last_request(){
		return $this->last_request;
	}

	/**
	 * get the url depending on the purpose.
	 * you can switch to a different server with the key
	 * @return string|false the base URL of the current server for this purpose
	 **/
	public function get_url($purpose, $key = 0, $force_http = false){
		$url	 = ($force_http ) ? 'http://' : 'https://';
		$use_url = (!isset($this->servers[$key])) ? reset($this->servers) : $this->servers[$key];
		
		switch($purpose){
			case 'updates':
				$url .= 'updates.';
				break;
			case 'templates':
				$url .= 'templates.';
				break;
			case 'library':
				$url .= 'library.';
				break;
			default:
				return false;
		}
		
		$url .= $use_url;
		
		return $url;
	}
	
	/**
	 * refresh the server list to be used, will be done once in a month
	 * @return void fetches the current server list from the fixed bootstrap URL
	 **/
	public function refresh_server_list($force = false){
		global $wp_version;
		
		//only privileged users may force a refresh via the URL param, so a visitor cannot trigger the blocking remote call
		$rs_rsl		= (isset($_GET['rs_refresh_server']) && function_exists('current_user_can') && current_user_can('manage_options')) ? true : false;
		$last_check	= $this->get_options(['timestamps', 'servers'], false);
		if($last_check === false || empty($last_check)) $this->update_option(['timestamps', 'servers'], time());

		if($force === true || $rs_rsl === true || ($last_check !== false && time() - $last_check > MONTH_IN_SECONDS)){
			$data = [
				'item'    => urlencode( RS_PLUGIN_SLUG ),
				'version' => urlencode( RS_REVISION )
			];
			$request = $this->call_url('https://updates.themepunch.tools/get_server_list.php', $data);
			if(!is_wp_error($request)){
				if($response = $this->maybe_unserialize_safe($request['body'])){ //remote body: never instantiate classes
					$list = json_decode($response, true);
					if(json_last_error() === JSON_ERROR_NONE && is_array($list)) {
						$this->update_option( [ 'system', 'servers' ], $list );
						$this->servers = $list;
					}
				}
			}
			
			$this->update_option(['timestamps', 'servers'], time());
		}
	}
	
	/**
	 * move the server list, to take the next server as the one currently seems unavailable
	 * @return void rotates the list so the next request starts at the following server
	 **/
	public function move_server_list(){
		$servers	= $this->servers;
		$a			= array_shift($servers);
		$servers[]	= $a;
		
		$this->servers = $servers;
		$this->update_option(['system', 'servers'], $servers);
	}

	/** @return string */
	protected function validate_url($url, $subdomain, $force_http){
		if(!preg_match("/^https?:\/\//i", $url)){
			//just a filename passed, lets build an url
			$server	 = $this->get_url($subdomain, 0, $force_http);
			$url = $server . '/' . ltrim($url, '/');
		}else{
			//full URL passed, lets check if we need to force http
			if($force_http) $url = preg_replace("/^https:\/\//i", "http://", $url);
		}

		return $url;
	}
	
	/**
	 * call an themepunch URL and retrieve data
	 * @return array|WP_Error the WP HTTP response
	 **/
	public function call_url($url, $data, $subdomain = 'updates', $force_http = false){
		global $wp_version;

		$is_full_url = preg_match("/^https?:\/\//i", $url);
		
		//add version if not passed
		$data['version'] = (!isset($data['version'])) ? urlencode(RS_REVISION) : $data['version'];
		$count	= 0;
		
		do{
			$full_url = $this->validate_url($url, $subdomain, $force_http);
			$cf_lock = $this->get_cf_rate_limit_lock($full_url);
			if ( ! $cf_lock ) {
				$request = wp_safe_remote_post($full_url, [
					'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url'),
					'body'		 => $data,
					'timeout'	 => 45
				]);
				$this->last_request = $request;

				$response_code = wp_remote_retrieve_response_code($request);
				if($response_code == 200) return $request;

				if($response_code == 429) {
					$cf_lock = $this->set_cf_rate_limit_lock($full_url, $request);
					if ( $cf_lock ) {
						$request            = $this->get_cf_wp_error( $cf_lock );
						$this->last_request = $request;
					}
				}
			} else {
				$request = $this->get_cf_wp_error($cf_lock);
				$this->last_request = $request;
			}
			
			if ($is_full_url) {
				// full URL passed, no need to try other servers
				break;
			}
			
			$this->move_server_list();
			$count++;
		}while($count < 3);
		
		return $request;
	}

	/**
	 * Streams a downloaded file directly to the disk
	 * USE WITH DIRECT URLS, as downloads_url() has wp_safe_remote_get() inside
	 *
	 * @param string $media      media path on the server
	 * @param string $dst        destination path
	 * @param string $subdomain  subdomain to use
	 * @param bool   $force_http force http
	 * @param string $basepath   absolute path to the media parent folder. it should already exist in filesystem before calling this function
	 * @param array  $mime_types mime types to validate a downloaded file
	 *
	 * @return string|WP_Error   Destination path on success, WP_Error object on failure
	 */
	public function download_url($media, $dst, $subdomain = 'templates', $force_http = false, $basepath = '', $mime_types = []){
		if(!function_exists('download_url')) require_once ABSPATH . 'wp-admin/includes/file.php';
		if (empty($basepath)) {
			return new WP_Error('basepath_fail', 'Base path is required');
		}

		$base = realpath($basepath);
		if ($base === false) {
			return new WP_Error('basepath_fail', 'Invalid base path');
		}

		$base = wp_normalize_path($base);
		$dst_normalized = wp_normalize_path($dst);
		// ensure trailing slash
		$base = rtrim($base, '/') . '/';

		if (strpos($dst_normalized, $base) !== 0) {
			return new WP_Error('dest_path_fail', 'Invalid destination path');
		}

		$url = $this->validate_url($media, $subdomain, $force_http);
		$tmp  = download_url($url, 45);
		if (is_wp_error($tmp)) {
			return $tmp;
		}

		if (empty($mime_types)) {
			global $SR_GLOBALS;
			$mime_types = array_merge($this->get_val($SR_GLOBALS, ['mime_types', 'image']), $this->get_val($SR_GLOBALS, ['mime_types', 'video']));
		}
		$file_type = wp_check_filetype($dst_normalized, $mime_types);
		if(!$file_type['ext'] || !$file_type['type']) {
			wp_delete_file($tmp);
			return new WP_Error('download_fail', 'Failed to download file');
		}

		if (!wp_mkdir_p(dirname($dst_normalized))) {
			wp_delete_file($tmp);
			return new WP_Error('mkdir_fail', 'Uploads dir not writable');
		}
		if (!@rename($tmp, $dst_normalized)) {
			wp_delete_file($tmp);
			return new WP_Error('move_fail', 'Failed to move file');
		}

		return $dst_normalized;
	}

	/**
	 * Downloads a file directly to the disk using wp_remote_post()
	 *
	 * @param string $url media url
	 * @param string $dst destination path
	 * @param array  $data POST data
	 * @param string $subdomain subdomain to use
	 * @param bool $force_http force http
	 *
	 * @return string|WP_Error   Destination path on success, WP_Error object on failure
	 */
	public function download_url_post($url, $dst, $data, $subdomain = 'updates', $force_http = false) {
		global $wp_version;
		
		if(!function_exists('wp_tempnam')) require_once ABSPATH . 'wp-admin/includes/file.php';

		$tmpfname = wp_tempnam( $url );
		if ( ! $tmpfname ) {
			return new WP_Error( 'http_no_file', __( 'Could not create temporary file.' ) );
		}

		$url = $this->validate_url($url, $subdomain, $force_http);

		$response = wp_safe_remote_post($url, [
			'user-agent' => 'WordPress/'.$wp_version.'; '.get_bloginfo('url'),
			'body'		 => $data,
			'timeout'	 => 45,
			'stream'   => true,
			'filename' => $tmpfname,
		]);

		if ( is_wp_error( $response ) ) {
			@unlink( $tmpfname );
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$error_body = file_get_contents( $tmpfname );
			@unlink( $tmpfname );

			return new WP_Error(
				'http_error',
				'Remote server returned ' . $response_code,
				array( 'body' => $error_body )
			);
		}

		if (!wp_mkdir_p(dirname($dst))) {
			wp_delete_file($tmpfname);
			return new WP_Error('mkdir_fail', 'Uploads dir not writable');
		}
		if (!@rename($tmpfname, $dst)) {
			wp_delete_file($tmpfname);
			return new WP_Error('move_fail', 'Failed to move file');
		}

		return $dst;
	}

	/**
	 * @param string $url
	 *
	 * @return string
	 */
	public function get_cf_transient_key($url){
		$host = parse_url($url, PHP_URL_HOST);
		if (empty($host) || !strpos($host, '.tools')) return '';
		
		return 'rs-cf-rate-limit-tools-' . md5($host);
	}

	/**
	 * @param string         $url      Requested URL
	 * @param array|WP_Error $response The response or WP_Error on failure.
	 *
	 * @return false|int
	 */
	public function set_cf_rate_limit_lock($url, $response){
		$cf_transient_key = $this->get_cf_transient_key($url);
		if (empty($cf_transient_key)) return false;
		
		// CF send header "retry-after: 3586"
		// seconds before next attempt
		$retry_after = (int) wp_remote_retrieve_header($response, 'retry-after');
		if ($retry_after <= 0) {
			$retry_after = 3600;
		}

		$cf_lock = time() + $retry_after;
		set_transient($cf_transient_key, $cf_lock, $retry_after);
		
		return $cf_lock;
	}

	/**
	 * @param string $url Requested URL
	 *
	 * @return false|int
	 */
	public function get_cf_rate_limit_lock($url){
		$cf_transient_key = $this->get_cf_transient_key($url);
		if (empty($cf_transient_key)) return false;
		
		return get_transient($cf_transient_key);
	}

	/**
	 * @param int $cf_lock
	 *
	 * @return WP_Error
	 */
	public function get_cf_wp_error($cf_lock){
		return new WP_Error('rate_limit_error', 'Too many requests from your IP, please try again in ' . human_time_diff( time(), $cf_lock ) . '.');
	}
}
