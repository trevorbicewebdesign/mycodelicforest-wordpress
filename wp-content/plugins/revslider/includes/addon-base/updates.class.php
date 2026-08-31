<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * AddOn updater. Registered exactly once per AddOn (the copied Update classes used to dupe this).
 * Checks the ThemePunch update endpoint at most every 30 days and feeds WordPress' plugin
 * update transient + plugin information modal for the AddOn.
 **/
/**
 * Update check for one AddOn against the ThemePunch servers, injected into WordPress' own update_plugins
 * transient so the AddOn appears on the plugins screen like any other update.
 */
class RevSliderAddonUpdates extends RevSliderFunctions {

	private $slug;
	private $title;
	private $version;
	private $update_data;
	private $update_option;
	private $premium_url = 'https://account.sliderrevolution.com/portal/premium-slider-revolution/?utm_source=admin&utm_medium=menu&utm_campaign=srusers&utm_content=gopremium';

	public function __construct($base){
		$this->slug			= $base->slug;
		$this->title		= $base->title;
		$this->version		= $base->version;
		$this->update_option= $this->slug.'_update_info';
		$this->update_data	= new stdClass;

		if(is_admin()){
			add_filter('pre_set_site_transient_update_plugins', [$this, 'set_update_transient']);
			add_filter('plugins_api', [$this, 'set_updates_api_results'], 10, 3);
		}
	}

	/** @return object the update_plugins transient, with this addon added when an update exists */
	public function set_update_transient($transient){
		$this->_check_updates();

		if(isset($transient) && !isset($transient->response)) $transient->response = [];

		if(!empty($this->update_data->basic) && is_object($this->update_data->basic)){
			$version = (isset($this->update_data->basic->version)) ? $this->update_data->basic->version : $this->update_data->basic->new_version;
			if(version_compare($this->version, $version, '<')){
				$this->update_data->basic->new_version = $version;
				if(isset($this->update_data->basic->version)) unset($this->update_data->basic->version);
				$transient->response[$this->slug.'/'.$this->slug.'.php'] = $this->update_data->basic;
			}
		}

		return $transient;
	}

	/** @return mixed */
	public function set_updates_api_results($result, $action, $args){
		$this->_check_updates();

		if(isset($args->slug) && $args->slug == $this->slug && $action == 'plugin_information'){
			if(!empty($this->update_data->full) && is_object($this->update_data->full)) $result = $this->update_data->full;
		}

		return $result;
	}

	/** @return void */
	protected function _check_updates($force_check = false){
		if((isset($_GET['checkforupdates']) && $_GET['checkforupdates'] == 'true') || isset($_GET['force-check'])) $force_check = true;

		if(empty($this->update_data)){
			$data = get_option($this->update_option, false);
			$data = $data ? $data : new stdClass;
			$this->update_data = is_object($data) ? $data : maybe_unserialize($data);
		}

		$last_check = get_option('revslider_'.$this->title.'_addon-update-check');
		if($last_check == false){
			$last_check = time();
			update_option('revslider_'.$this->title.'_addon-update-check', $last_check);
		}

		if(time() - $last_check > MONTH_IN_SECONDS || $force_check == true){
			$data = $this->_retrieve_update_info();

			if(isset($data->basic)){
				update_option('revslider_'.$this->title.'_addon-update-check', time());

				$this->update_data->checked = time();
				$this->update_data->basic = $data->basic;
				$this->update_data->full = $data->full;

				update_option('revslider_'.$this->title.'_addon-latest-version', $data->full->version);
			}
		}

		update_option($this->update_option, $this->update_data);
	}

	/** @return object */
	public function _retrieve_update_info(){
		$rslb = new RevSliderLoadBalancer();
		$data = new stdClass;

		$purchase	= ($rslb->_truefalse($rslb->get_options(['system', 'valid'], 'false')) === true) ? $rslb->get_options(['system', 'license'], '') : '';
		$rattr		= [
			'code'			=> urlencode($purchase),
			'version'		=> urlencode(RS_REVISION),
			'addon_version' => urlencode($this->version)
		];

		$request = $rslb->call_url('addons/'.$this->slug.'/'.$this->slug.'.php', $rattr, 'updates');

		if(!is_wp_error($request)){
			if($response = $this->maybe_unserialize_safe($request['body'], ['stdClass'])){ //remote body: only stdClass, the code below reads $response->basic/->full
				if(is_object($response)){
					$data = $response;
					$data->basic->url = $this->premium_url;
					$data->full->url = $this->premium_url;
					$data->full->external = 1;
				}
			}
		}

		return $data;
	}
}
