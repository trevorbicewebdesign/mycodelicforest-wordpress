<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * AI features (text, translation, slide and image generation).
 *
 * All requests are proxied by the ThemePunch servers, which hold the provider credentials and bill the
 * user's credits. Image generation is asynchronous: the server returns an event id, the pending ids are
 * kept in the options, and check_open_event_ids() polls them and imports finished images into the media
 * library.
 */
class RevSliderAI extends RevSliderFunctions {
	public $api_url		= 'https://ai.sliderrevolution.org/api/v1/';
	public $error_codes = [];

	/**
	 * @var RevSliderLoadBalancer
	 */
	private $rslb;
		
	public function __construct(){
		add_action('init', array($this, 'on_init'));
		$this->rslb = RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
	}
	
	/** @return void */
	public function on_init(){
		$this->error_codes = $this->get_error_codes();
	}

	/**
	 * @param string $url
	 * @param array  $data
	 * @return array|WP_Error
	 */
	protected function call_api($url, $data){
		if (strpos($url, 'image/') !== false) {
			$data['provider'] = 'fal';
		}
		$data['code'] = $this->get_options(['system', 'license'], '');

		return $this->rslb->call_url($url, $data);
	}

	/**
	 * @param array $data
	 * @param string $url
	 * @return array
	 */
	protected function get_ai_data($data, $url){
		if (!is_array($data)) {
			return [
				'success' => false,
				'message' => __('Wrong data format.', 'revslider')
			];
		}

		$response = $this->call_api($url, $data);
		if(is_wp_error($response)){
			return [
				'success' => false,
				'message' => $response->get_error_message()
			];
		}
		
		$result = json_decode(wp_remote_retrieve_body($response), true);
		if (json_last_error() !== JSON_ERROR_NONE || !isset($result['success'])){
			return [
				'success' => false,
				'message' => __('ThemePunch server error. Please contact our support for details.', 'revslider')
			];
		}

		if ($result['success']) {
			if (isset($result['message'])) {
				// message contain json_encoded response from model
				$result['data'] = json_decode( $result['message'], true );
				if ( json_last_error() !== JSON_ERROR_NONE ) {
					return [
						'success' => false,
						'message' => __( 'ThemePunch server response could not be decoded!', 'revslider' )
					];
				}
				unset( $result['message'] );
			}
		} else {
			// the message contains error code
			// translate error code to a meaningful message
			$result['message'] = wp_remote_retrieve_response_code($response) . ': ' . ($this->error_codes[$result['message']] ?? $result['message']);
		}

		return $result;
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function translate($data){
		return $this->get_ai_data($data, $this->api_url . 'translate');
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function get_slide($data){
		return $this->get_ai_data($data, $this->api_url . 'slide');
	}
	
	/**
	 * @param array $data
	 * @return array
	 */
	public function get_text($data){
		return $this->get_ai_data($data, $this->api_url . 'text');
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function get_credits($data){
		return $this->get_ai_data($data, $this->api_url . 'member/credits');
	}

	/**
	 * @param string $event_id Request ID from the image model
	 * @param string $source   who requested the status ( api or bg_jobs)
	 * @return array
	 */
	public function get_image_status($event_id, $source){
		if(empty($event_id)){
			return [
				'success' => false,
				'message' => __('Event ID is required for status check.', 'revslider')
			];
		}

		$event = $this->get_open_event_by_event_id($event_id);
		if(empty($event) && 'api' === $source){
			//check if the event already completed via bg_job
			$urls = [];
			$jobs = $this->get_finished_background_jobs();
			foreach($jobs as $url){
				if (strpos($url, $event_id) !== false) {
					$urls[] = ['url' => $url];
					$this->remove_finished_background_job($url);
				}
			}

			if(!empty($urls)){
				// this result returned for api requests only
				// RevSliderApi->get_ai_element_status()
				return [
					'success' => true,
					'status' => 'done_bg_job',
					'result' => $urls
				];
			}
		}
		if(empty($event)){
			return [
				'success' => false,
				'message' => __('Event not found.', 'revslider')
			];
		}

		$data = [
			'event_id' => $event_id,
			'engine'   => $event['engine'],
		];
		return $this->get_ai_data($data, $this->api_url . 'image/status');
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function generate_image($data){
		$url    = $this->api_url . 'image/start';
		$method = $this->get_val($data, 'method');
		if ($method === 'bgremove') {
			$url = $this->api_url . 'image/bgremove';
		}

		return $this->get_ai_data($data, $url);
	}

	/** @return array image jobs still waiting for a result, by event id */
	public function get_open_events(){
		return $this->get_options(['ai', 'event_ids'], []);
	}

	/** @return array */
	public function get_open_event_by_event_id($event_id){
		$event_ids = $this->get_options(['ai', 'event_ids'], []);
		return (isset($event_ids[$event_id])) ? $event_ids[$event_id] : [];
	}

	/** @return array|true polls the pending image jobs and imports the finished ones */
	public function check_open_event_ids($force = false, $add_to_bg_job = true){
		$do = 0;
		$max_calls = 3;
		$max_checks = 2;
		$event_ids = $this->get_options(['ai', 'event_ids'], []);
		if(empty($event_ids) || !is_array($event_ids)) return true;

		$_urls = [];
		foreach($event_ids as $event_id => $data){
			$calls			= $this->get_val($data, 'calls');
			$last_call		= $this->get_val($data, 'last_call');
			$prompt			= $this->get_val($data, 'prompt');
			if(!empty($prompt)) $prompt = 'Created through Slider Revolution with prompt: '.esc_html($prompt);

			if(empty($force)){
				if($last_call !== false && time() - $last_call < 60 * 2) continue; //only check every two minutes
			}

			if($do >= $max_checks) break;
			$calls++;
			$do++;
			
			$result = $this->get_image_status($event_id, 'bg_jobs');
			$this->trigger_event_id_call($event_id, $calls);
			if (!$result['success']) {
				if($calls >= $max_calls) $this->remove_event_id($event_id);
				continue;
			}
			
			if($result['status'] === 'done'){
				$fetch_mode = $this->get_val($result, 'fetch_mode', 'url');
				$img_stream = $this->get_val($result, 'result');
				if(empty($img_stream)){
					$this->remove_event_id($event_id);
					continue;
				}

				$_urls = $this->fetch_generated_images($img_stream, $event_id, $prompt, $add_to_bg_job, $fetch_mode);

				$this->remove_event_id($event_id);
			}

			if($calls >= $max_calls) $this->remove_event_id($event_id);
		}

		return $_urls;
	}

	/**
	 * @param array  $images
	 * @param string $event_id
	 * @param string $prompt
	 * @param bool   $add_to_bg_job
	 * @param string $mode fetch mode: url or data
	 * @return array
	 */
	public function fetch_generated_images($images, $event_id, $prompt, $add_to_bg_job, $mode){
		if (empty($images) || !is_array($images)) return [];
		if (empty($mode) || !in_array($mode, ['url', 'data'])) return [];

		$return = [];
		foreach($images as $k => $stream){
			if ('url' === $mode){
				$url = $this->fetch_from_url($stream, $event_id.'_'.$k, $prompt);
			} else {
				$url = $this->fetch_from_data($stream, $event_id.'_'.$k, $prompt);
			}
			if(!empty($url)){
				if($add_to_bg_job){
					$this->add_finished_background_job($url);
				}
				$return[] = ['url' => $url];
			}
		}

		return $return;
	}

	/**
	 * @param array $stream
	 * @param string $filename
	 * @param string $prompt
	 * @return string
	 */
	public function fetch_from_url($stream, $filename, $prompt){
		$url = $this->get_val($stream, 'url');
		if(empty($url)) return '';

		$ext = $this->validate_extension($url);
		if(empty($ext)) return '';

		$result = $this->import_media($url, 'ai/', $filename.'.'.$ext, $prompt);
		if (!$result['success']) return '';

		return $result['url'];
	}

	/**
	 * @param array $stream
	 * @param string $filename
	 * @param string $prompt
	 * @return string
	 */
	public function fetch_from_data($stream, $filename, $prompt){
		$orig_name = $this->get_val($stream, 'filename');
		$raw_data	= $this->get_val($stream, 'data');
		$img_stream = base64_decode($raw_data);
		if(empty($img_stream)) return '';

		$ext = $this->validate_extension($orig_name);
		if(empty($ext)) return '';

		// ---- Write to /tmp with a unique filename
		$tmp_file = tempnam($this->get_temp_path('rstemp'), 'img_');
		$final_path = $tmp_file . '.' . $ext;
		// rename a temp file to include extension
		rename($tmp_file, $final_path);
		// write the binary data
		file_put_contents($final_path, $img_stream);

		//add to WP library
		$result = $this->import_media($final_path, 'ai/', $filename.'.'.$ext, $prompt);
		if (!$result['success']) return '';

		return $result['url'];
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public function validate_mime_type($type){
		global $SR_GLOBALS;

		$ext = array_search($type, $SR_GLOBALS['mime_types']['image']);
		if (false === $ext) return '';

		// JPEG key contain multiple extensions
		// 'jpg|jpeg|jpe' => 'image/jpeg'
		if (strpos($ext, '|')){
			$ext = explode('|', $ext)[0];
		}

		return $ext;
	}

	/**
	 * @param string $url
	 * @return string
	 */
	public function validate_extension($url){
		$ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
		if (in_array($ext, ['jpg', 'jpeg', 'webp', 'png'])) return $ext;

		// flux2 started to return .bin extension for webp images
		if ('bin' === $ext) return 'webp';

		return '';
	}

	/** @return array */
	public function get_finished_background_jobs(){
		return $this->get_options(['ai', 'finished'], []);
	}

	/** @return bool */
	public function add_finished_background_job($url){
		$finished = $this->get_finished_background_jobs();
		$finished[] = $url;
		$this->update_option(['ai', 'finished'], $finished);

		return true;
	}

	/** @return bool */
	public function remove_finished_background_job($url){
		$finished = $this->get_finished_background_jobs();
		if(empty($finished) || !is_array($finished)) return true;

		foreach($finished as $k => $_url){
			if($_url !== $url) continue;
			unset($finished[$k]);
			break;
		}

		$this->update_option(['ai', 'finished'], $finished);
		
		return true;
	}

	/** @return void */
	public function clear_background_jobs($urls = []){
		if(empty($urls) || !is_array($urls)){
			$this->update_option(['ai', 'finished'], []);
			return;
		}

		foreach($urls as $url){
			$this->remove_finished_background_job($url);
		}
	}

	/** @return void */
	public function clear_event_ids(){
		$this->update_option(['ai', 'event_ids'], []);
	}

	/** @return bool */
	public function remove_event_id($id){
		$event_ids = $this->get_options(['ai', 'event_ids'], []);
		if(empty($event_ids) || !is_array($event_ids)) return true;
		if(isset($event_ids[$id])){
			unset($event_ids[$id]);
			$this->update_option(['ai', 'event_ids'], $event_ids);
		}

		return true;
	}

	/** @return bool */
	public function add_event_id($id, $data){
		$event_ids = $this->get_options(['ai', 'event_ids'], []);
		if(empty($event_ids)) $event_ids = [];

		$prompt = $this->get_val($data, 'prompt');
		$engine = $this->get_val($data, 'engine');

		$event_ids[$id] = ['prompt' => $prompt, 'engine' => $engine, 'calls' => 0, 'last_call' => time() - 90]; //wait for 30 seconds before it will be firstly
		$this->update_option(['ai', 'event_ids'], $event_ids);
		
		return true;
	}
	
	/** @return bool */
	public function trigger_event_id_call($id, $calls){
		$event_ids = $this->get_options(['ai', 'event_ids'], []);
		if(empty($event_ids)) $event_ids = [];
		if(!isset($event_ids[$id])) return false;
		$event_ids[$id]['calls'] = $calls;
		$event_ids[$id]['last_call'] = time();

		$this->update_option(['ai', 'event_ids'], $event_ids);

		return true;
	}

	/** @return bool */
	public function exist_event_id($id){
		$event_ids = $this->get_options(['ai', 'event_ids'], []);
		return !empty($event_ids) && isset($event_ids[$id]);
	}

	/** @return array|string */
	public function find_attachments_by_event_id($id){
		global $wpdb;

		$like = '%' . $wpdb->esc_like('/' . $id . '.') . '%';

		// Prefer: image attachments, exclude resized variants like -150x150.jpg
		$aid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT p.ID
				FROM {$wpdb->postmeta} pm
				JOIN {$wpdb->posts} p ON p.ID = pm.post_id
				WHERE pm.meta_key = '_wp_attached_file'
				AND pm.meta_value LIKE %s
				AND p.post_type = 'attachment'
				AND p.post_mime_type LIKE 'image/%%'
				AND pm.meta_value NOT REGEXP '-[0-9]+x[0-9]+\\.[A-Za-z0-9]+$'
				ORDER BY p.post_date_gmt DESC
				LIMIT 1",
				$like
			)
		);

		if(!$aid){
			// Fallback: take the most recent image attachment if all matches are resized
			$aid = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT p.ID
					FROM {$wpdb->postmeta} pm
					JOIN {$wpdb->posts} p ON p.ID = pm.post_id
					WHERE pm.meta_key = '_wp_attached_file'
					AND pm.meta_value LIKE %s
					AND p.post_type = 'attachment'
					AND p.post_mime_type LIKE 'image/%%'
					ORDER BY p.post_date_gmt DESC
					LIMIT 1",
					$like
				)
			);
		}

		if(!$aid) return [];

		$src = wp_get_attachment_image_src($aid, 'full');

		return is_array($src) ? $src[0] : '';
	}

	/**
	 * @return array
	 */
	public function get_error_codes(){
		return [
			'image_provider_required' => __('Missing Image Provider.', 'revslider'),
			'image_provider_not_supported' => __('Invalid Image Provider.', 'revslider'),
			'api_rate_limit_exceed' => __('API Rate Limit Exceeded.', 'revslider'),
			'api_route_not_found' => __('API Route Not Found.', 'revslider'),
			'code_invalid' => __('Code not valid.', 'revslider'),
			'credits_not_enough' => __('Not enough credits.', 'revslider'),
			'db_connect_error' => __('Database connection error.', 'revslider'),
			'db_query_error' => __('Database query error.', 'revslider'),
			'fal_bad_status' => __('Wrong status in creation process.', 'revslider'),
			'fal_image_empty_prompt' => __('Image prompt is empty.', 'revslider'),
			'fal_invalid_event_id' => __('Invalid event ID.', 'revslider'),
			'fal_invalid_index' => __('Invalid Image index.', 'revslider'),
			'fal_missing_token' => __('Missing API token.', 'revslider'),
			'fal_missing_url' => __('Missing API URL.', 'revslider'),
			'fal_remove_bg_failed' => __('Background removal failed.', 'revslider'),
			'fal_remove_bg_index_out_of_range' => __('Background index out of range.', 'revslider'),
			'fal_remove_bg_missing_images' => __('Background images missing.', 'revslider'),
			'fal_remove_bg_url_missing' => __('Background URL missing.', 'revslider'),
			'fal_sdk_error' => __('API SDK error.', 'revslider'),
			'fal_unknown_engine' => __('Unknown Image Generation Engine.', 'revslider'),
			'hf_bad_status' => __('Wrong status in creation process.', 'revslider'),
			'hf_curl_error_poll' => __('Lost connection while checking job status.', 'revslider'),
			'hf_curl_error_start' => __('Could not start the image job due to a network error.', 'revslider'),
			'hf_http_error_poll' => __('Polling failed. The job may have expired or the server is busy.', 'revslider'),
			'hf_http_error_start' => __('Image job request failed.', 'revslider'),
			'hf_image_empty_prompt' => __('Image prompt is empty.', 'revslider'),
			'hf_invalid_complete_data' => __('The server did not return proper image data.', 'revslider'),
			'hf_invalid_complete_json' => __('The job finished but returned invalid data.', 'revslider'),
			'hf_invalid_event_id' => __('Invalid event ID.', 'revslider'),
			'hf_method_not_implemented' => __('Method not implemented.', 'revslider'),
			'hf_missing_token' => __('Missing API token.', 'revslider'),
			'hf_missing_url' => __('Missing API URL.', 'revslider'),
			'image_action_not_found' => __('Image action not found.', 'revslider'),
			'invalid_credits_history_data' => __('Invalid credits history data.', 'revslider'),
			'invalid_credits_update_data' => __('Invalid credits update data.', 'revslider'),
			'invalid_request_id' => __('Invalid request ID.', 'revslider'),
			'member_action_not_found' => __('Member action not found.', 'revslider'),
			'request_method_not_allowed' => __('Request method not allowed.', 'revslider'),
			'slide_empty' => __('Slide is empty.', 'revslider'),
			'text_empty' => __('Text is empty.', 'revslider'),
			'text_invalid_provider_response' => __('Invalid text provider response.', 'revslider'),
			'text_missing_api_key' => __('Missing API key.', 'revslider'),
			'unsupported_api_version' => __('Unsupported API version.', 'revslider'),
			'translate_invalid_input' => __('Invalid Translation data.', 'revslider'),
			'translate_invalid_provider_response' => __('Invalid translate provider response.', 'revslider'),
			'translate_missing_translations' => __('Missing translations in provider response.', 'revslider'),
		];
	}

}
