<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 * @since	  6.2.0
 */

if(!defined('ABSPATH')) exit();

/**
 * License activation against the ThemePunch servers. The result is stored in the options as
 * system.valid ('true'/'false') and system.license (the key); everything else in the plugin reads the
 * premium state from there.
 */
class RevSliderLicense extends RevSliderFunctions {
	/**
	 * Activate the Plugin through the ThemePunch Servers
	 * @param string $code the license key
	 * @return true|string|false true when activated, 'exist' when the key is already in use elsewhere,
	 *                           'banned' when it was revoked, false on any error
	 **/
	public function activate_plugin($code){
		//only build the tracker when tracking is actually switched on - its constructor loads the global
		//settings and registers filters, all of which _run() would immediately discard again
		$this->run_tracking(true);

		$rslb = RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		$data = [
			'code'		=> urlencode($code),
			'version'	=> urlencode(RS_REVISION),
			'product'	=> urlencode(RS_PLUGIN_SLUG),
			'addition'	=> apply_filters('revslider_activate_plugin_info_addition', [])
		];
		
		$response = $rslb->call_url('activate.php', $data, 'updates');

		//check the response, not its body: wp_remote_retrieve_body() always hands back a string, so the
		//is_wp_error() that used to sit on $version_info was dead code
		if(is_wp_error($response)) return false;

		$version_info = wp_remote_retrieve_body($response);

		if($version_info == 'valid'){
			$this->update_option(['system', 'valid'], 'true');
			$this->update_option(['system', 'license'], $code);
			$this->update_option(['system', 'deregister'], 'false');

			return true;
		}
		if($version_info == 'exist') return 'exist';
		if($version_info == 'banned') return 'banned';
		
		return false;
	}
	
	
	/**
	 * Deactivate the Plugin through the ThemePunch Servers
	 * clears the stored key on success, so the site drops back to the free feature set
	 * @return bool false when the server refuses or cannot be reached
	 **/
	public function deactivate_plugin(){
		$this->run_tracking(false);

		$rslb = RevSliderGlobals::instance()->get('RevSliderLoadBalancer');
		$code = $this->get_options(['system', 'license'], '');
		$data = [
			'code'		=> urlencode($code),
			'product'	=> urlencode(RS_PLUGIN_SLUG),
			'addition'	=> apply_filters('revslider_deactivate_plugin_info_addition', [])
		];

		$res = $rslb->call_url('deactivate.php', $data, 'updates');

		//the WP_Error lives on the *response*, not on its body - wp_remote_retrieve_body() always returns a
		//string, so the is_wp_error() check that used to sit on $vi could never be true. Returning false for
		//both cases is intentional: the caller (RevSliderApi::deactivate_plugin) asks the load balancer for
		//the last request and reports the connection error from there, so the distinction is not lost.
		if(is_wp_error($res)) return false;

		$vi = wp_remote_retrieve_body($res);
		if($vi != 'valid') return false;

		$this->update_option(['system', 'valid'], 'false');
		$this->update_option(['system', 'license'], '');
		//$this->update_option(['system', 'deregister'], 'true');

		return true;
	}


	/**
	 * run the tracking payload, but only build the tracker when tracking is enabled at all.
	 *
	 * @param bool $licensed passed through to RevSliderTracking::_run()
	 * @return void
	 */
	private function run_tracking($licensed){
		$gs = $this->get_global_settings();
		if($this->get_val($gs, 'tracking', '') !== 'enabled') return;

		$rstrack = new RevSliderTracking();
		$rstrack->_run($licensed);
	}
}