<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */
 
if(!defined('ABSPATH')) exit();

/**
 * Plugin updates from the ThemePunch servers instead of wp.org.
 *
 * Hooks into WordPress' own update machinery: set_update_transient() adds this plugin to the
 * update_plugins transient, set_updates_api_results() supplies the "View details" payload. Without a valid
 * licence only the last free/stable version is offered.
 */
class RevSliderUpdate extends RevSliderFunctions {

	private $plugin_url		 = 'https://www.sliderrevolution.com/';
	private $remote_url		 = 'check_for_updates.php';
	private $remote_url_info = 'revslider/revslider.php';
	private $plugin_slug	 = 'revslider';
	public $force			 = false;
	private $data;
	private $version;
	private $plugins;
	
	public function __construct($version){
		$this->data = new stdClass;
		$this->version = (empty($version)) ? RS_REVISION : $version;
	}
	
	
	/** @return void */
	public function add_update_checks(){
		if($this->force === true){
			$this->raise_runtime_limits(300); //an update can follow, so set the execution time high for the runtime
			$transient = get_site_transient('update_plugins');
			$rs_t = $this->set_update_transient($transient);
			if(!empty($rs_t)) set_site_transient('update_plugins', $rs_t);
		}
		
		add_filter('pre_set_site_transient_update_plugins', [&$this, 'set_update_transient']);
		add_filter('plugins_api', [&$this, 'set_updates_api_results'], 10, 3);
	}
	
	
	/** @return object the update_plugins transient with this plugin's update added */
	public function set_update_transient($transient){
		$this->_check_updates();

		if(!is_object($transient))			$transient = new stdClass();
		if(!isset($transient->response))	$transient->response = [];
		if(!isset($transient->no_update))	$transient->no_update = [];
		if(!isset($transient->checked))		$transient->checked = [];
		$transient->checked[RS_PLUGIN_SLUG_PATH] = $this->version;

		if(!isset($this->data))			return $transient;
		if(!isset($this->data->basic))	return $transient;

		if(!empty($this->data->basic) && is_object($this->data->basic)){
			$version = (isset($this->data->basic->version)) ? $this->data->basic->version : $this->data->basic->new_version;
			$basic	 = $this->_normalize_update_object($this->data->basic);

			if(version_compare($this->version, $version, '<')){
				$basic->new_version = $version;
				if(isset($basic->version)) unset($basic->version);

				$transient->response[RS_PLUGIN_SLUG_PATH] = $basic;
				if(isset($transient->no_update[RS_PLUGIN_SLUG_PATH])) unset($transient->no_update[RS_PLUGIN_SLUG_PATH]);
			}else{
				//required so the "enable auto-updates" toggle shows up on the plugins screen and WP's background updater considers the plugin
				$basic->new_version = $this->version;
				$transient->no_update[RS_PLUGIN_SLUG_PATH] = $basic;
			}
		}

		return $transient;
	}


	/**
	 * make sure the update object that gets pushed into the update_plugins transient
	 * carries every field WordPress core expects, so the auto-update flow works the
	 * same way it does for plugins hosted on wp.org
	 * @return object fills in every field WP core expects on an update object
	 **/
	private function _normalize_update_object($obj){
		$obj = (is_object($obj)) ? clone $obj : new stdClass;
		if(empty($obj->slug))			$obj->slug			= $this->plugin_slug;
		if(empty($obj->plugin))			$obj->plugin		= RS_PLUGIN_SLUG_PATH;
		if(empty($obj->id))				$obj->id			= $this->plugin_slug.'/'.$this->plugin_slug;
		if(empty($obj->url))			$obj->url			= $this->plugin_url;
		if(!isset($obj->icons))			$obj->icons			= [];
		if(!isset($obj->banners))		$obj->banners		= [];
		if(!isset($obj->banners_rtl))	$obj->banners_rtl	= [];
		if(!isset($obj->tested))		$obj->tested		= '';
		if(!isset($obj->requires))		$obj->requires		= '';
		if(!isset($obj->requires_php))	$obj->requires_php	= '';
		if(!isset($obj->compatibility))	$obj->compatibility	= new stdClass;
		return $obj;
	}


	/**
	 * register the update transient filters in non-admin contexts (most importantly
	 * during wp-cron when WP's automatic updater runs). add_update_checks() already
	 * handles the admin side via the RevSliderAdmin bootstrap.
	 * @return void registers the transient filters outside the admin, so wp-cron auto updates work
	 **/
	public static function init_auto_updates(){
		if(is_admin()) return; //admin path is handled by RevSliderAdmin::do_update_checks()
		if(!defined('RS_REVISION')) return;

		add_filter('pre_set_site_transient_update_plugins', ['RevSliderUpdate', '_filter_update_transient']);
		add_filter('plugins_api', ['RevSliderUpdate', '_filter_plugins_api'], 10, 3);
	}


	/** @return object */
	public static function _filter_update_transient($transient){
		$upgrade = new RevSliderUpdate(RS_REVISION);
		return $upgrade->set_update_transient($transient);
	}


	/** @return mixed */
	public static function _filter_plugins_api($result, $action, $args){
		$upgrade = new RevSliderUpdate(RS_REVISION);
		return $upgrade->set_updates_api_results($result, $action, $args);
	}


	/** @return mixed the plugin information WP shows in the update details dialog */
	public function set_updates_api_results($result, $action, $args){
		$this->_check_updates();

		return (
			$action != 'plugin_information' ||
			!isset($args->slug) ||
			$args->slug != $this->plugin_slug ||
			!isset($this->data) ||
			!isset($this->data->full) ||
			!is_object($this->data->full) ||
			empty($this->data->full)
		) ? $result : $this->data->full;
	}


	/** @return void */
	public function _check_updates(){
		// Get data
		if(empty($this->data)){
			$data = $this->get_options(['update', 'information'], false);
			$data = $data ? $data : new stdClass;
			
			$this->data = is_object($data) ? $data : maybe_unserialize($data);
		}
		
		$last_check = $this->get_options(['timestamps', 'update'], false);
		if( ! $last_check ){ //first time called
			$last_check = time() - 172802;
			$this->update_option(['timestamps', 'update'], $last_check);
		}
		
		// Check for updates
		if(time() - $last_check < 172800 && ! $this->force ) return;
	
		$data = $this->_retrieve_update_info();
		$this->update_option(['timestamps', 'update'], time());
		if(isset($data->basic)){
			$this->data->checked = time();
			$this->data->basic	 = $data->basic;
			$this->data->full	 = $data->full;
			
			$this->update_option(['system', 'stable'], $data->full->stable);
			$this->update_option(['system', 'version'], $data->full->version);
		}
		
		$this->update_option(['update', 'information'], $this->data); // Save results
	}


	/** @return object */
	public function _retrieve_update_info(){
		$rslb = RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		$data = new stdClass;
		$rattr = [
			'code'		=> urlencode($this->get_options(['system', 'license'], '')),
			'version'	=> urlencode(RS_REVISION),
			'last_launch' => urlencode(get_option('sr_last_launch')),
		];
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) !== true && version_compare(RS_REVISION, $this->get_options(['system', 'stable'], '4.2'), '<')){ //We'll get the last stable only now!
			$rattr['get_stable'] = 'true';
		}
		
		$request = $rslb->call_url($this->remote_url_info, $rattr, 'updates');

		if(is_wp_error($request)) return $data;

		$body = wp_remote_retrieve_body($request);
		$response = (is_string($body) && $body !== '') ? @unserialize($body, ['allowed_classes' => ['stdClass']]) : false;
		if($response !== false && is_object($response) && ($response instanceof stdClass)){
			$data = $response;
			if(!isset($data->basic) || !($data->basic instanceof stdClass)) $data->basic = new stdClass;
			if(!isset($data->full)  || !($data->full  instanceof stdClass)) $data->full  = new stdClass;
			$data->basic->url	= $this->plugin_url;
			$data->full->url	= $this->plugin_url;
			$data->full->external = 1;
		}

		return $data;
	}
	
	
	/** @return string|false the latest version reported by the server */
	public function _retrieve_version_info(){
		$rslb		= RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		$last_check	= $this->get_options(['timestamps', 'update-short'], false);

		// Check for updates
		if( ! $last_check || time() - $last_check > 172800 || $this->force ){
			do_action('revslider-retrieve_version_info', $this);
			$this->update_option(['timestamps', 'update-short'], time());
			
			$hash		= ($this->force === true) ? '' : $this->get_options(['hashes', 'update'], '');
			$purchase	= ($this->_truefalse($this->get_options(['system', 'valid'], 'false')) === true) ? $this->get_options(['system', 'license'], '') : '';
			$data		= [
				'version' => urlencode(RS_REVISION),
				'item' => urlencode(RS_PLUGIN_SLUG),
				'hash' => urlencode($hash),
				'code' => urlencode($purchase),
				'addition' => apply_filters('revslider_retrieve_version_info_addition', []),
				'last_launch' => urlencode(get_option('sr_last_launch')),
			];

			$request = $rslb->call_url($this->remote_url, $data, 'updates');
			$info	 = wp_remote_retrieve_body($request);
			
			if(wp_remote_retrieve_response_code($request) != 200 || is_wp_error($info)){
				$this->update_option(['system', 'connect'], false);
				return false;
			}
			
			$this->update_option(['system', 'connect'], true);
			
			if('actual' != $info){
				$info = json_decode($info);
				
				if(isset($info->hash))		$this->update_option(['hashes', 'update'], $info->hash);
				if(isset($info->version))	$this->update_option(['system', 'version'], $info->version);
				if(isset($info->stable))	$this->update_option(['system', 'stable'], $info->stable);
				if(isset($info->notices))	$this->update_option(['overview', 'notices'], $info->notices);
				if(isset($info->additions))	$this->update_option(['system', 'additions'], $info->additions);
				if(isset($info->addons)){
					$addons = $this->get_options(['addons'], [], false, 'rs-addons');
					$addons = (is_object($addons)) ? (array)$addons : $addons;
					$addons = (!is_array($addons)) ? json_decode($addons, true) : $addons;
					if(!is_array($addons) || empty($addons)) $addons = [];
					
					$cur_addons_count = count($addons);
					$new_addons_count = count((array)$info->addons);
					if($cur_addons_count < $new_addons_count){
						$counter = $new_addons_count - $cur_addons_count;
						$this->update_option(['counter'], $counter, 'rs-addons');
					}
					
					$this->update_option(['addons'], $info->addons, 'rs-addons');
				}
				
				if(isset($info->deactivated) && $info->deactivated === true && $this->_truefalse($this->get_options(['system', 'valid'], 'false')) === true){
					//remove validation, add notice
					$this->update_option(['system', 'valid'], 'false');
					$this->update_option(['system', 'deregister'], true);
					if( !empty($info->deactivated_msg) ) $this->update_option(['system', 'deregister-msg'], $info->deactivated_msg);
				}
			}
		}
		
		//force that the update will be directly searched
		if( $this->force ) $this->update_option(['timestamps', 'update'], '');
		
		return $this->get_options(['system', 'version'], RS_REVISION);
	}
}
