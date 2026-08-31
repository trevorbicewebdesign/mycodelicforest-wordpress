<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Add-On management.
 *
 * Add-Ons are ordinary WordPress plugins named revslider-<name>-addon, installed straight from the
 * ThemePunch servers instead of wp.org: download_addon() fetches the zip and unpacks it into the plugins
 * folder, activate_addon() switches it on. The catalogue lives in the rs-addons option; $addon_version_required
 * declares the minimum add-on version the current core needs.
 */
class RevSliderAddons extends RevSliderFunctions {
	//private $addon_version_required = '2.0.0'; //this holds the globally needed addon version for the current RS version
	private $addons_path			= '/revslider/addons/';
	private $addons_basedir			= '';
	private $addons_baseurl			= '';
	private $addons_list			= [];
	private $addons_list_short		= [];

	//Minimum addon version required by the CURRENT core version (keys = official addon slugs from the rs-addons list). Baseline 7.0.0 = "any SR7 build is fine"; raise an entry only when a core change drops backward compatibility for that addon. Alphabetical, one line per official addon.
	//"effects pipeline" = the 7.1.5 group: core replaced the canvas BG engine (aCanvas) they sampled, so a pre-7.1.5 build of them draws blank.
	private $addon_version_required = [
		'revslider-404-addon'				=> '7.0.0',
		'revslider-aitranslate-addon'		=> '7.0.0',
		'revslider-backup-addon'			=> '7.0.0',
		'revslider-beforeafter-addon'		=> '7.1.6',
		'revslider-bubblemorph-addon'		=> '7.0.0',
		'revslider-carouselx-addon'			=> '7.1.0', //the harmonized core dropped carouselx's hardcoded 3dshowcase/fade support - it now lives in the addon's srCarExt hooks, so an older carouselx renders wrong (Track 2 R1f). Force the update.
		'revslider-chalkline-addon'			=> '7.1.5',
		'revslider-charts-addon'			=> '7.0.0',
		'revslider-commerce-addon'			=> '7.0.0',
		'revslider-depthforge-addon'		=> '7.1.5',
		'revslider-distortion-addon'		=> '7.1.5', //effects pipeline
		'revslider-domainswitch-addon'		=> '7.0.0',
		'revslider-duotonefilters-addon'	=> '7.0.0',
		'revslider-explodinglayers-addon'	=> '7.0.0',
		'revslider-featured-addon'			=> '7.1.2',
		'revslider-filmstrip-addon'			=> '7.1.6', //effects pipeline
		'revslider-fluiddynamics-addon'		=> '7.0.0',
		'revslider-gallery-addon'			=> '7.0.0',
		'revslider-hovermorph-addon'		=> '7.1.6',
		'revslider-login-addon'				=> '7.0.0',
		'revslider-lottie-addon'			=> '7.0.0',
		'revslider-maintenance-addon'		=> '7.0.0',
		'revslider-mousetrap-addon'			=> '7.0.0',
		'revslider-paintbrush-addon'		=> '7.1.5', //effects pipeline
		'revslider-panorama-addon'			=> '7.1.6', //interaction on/off
		'revslider-particles-addon'			=> '7.1.2',
		'revslider-particlewave-addon'		=> '7.0.0',
		'revslider-polyfold-addon'			=> '7.0.0',
		'revslider-prevnextposts-addon'		=> '7.0.0',
		'revslider-refresh-addon'			=> '7.0.0',
		'revslider-relposts-addon'			=> '7.0.0',
		'revslider-revealer-addon'			=> '7.0.0',
		'revslider-scrollvideo-addon'		=> '7.1.5', //effects pipeline
		'revslider-shapeburst-addon'		=> '7.1.6',
		'revslider-sharing-addon'			=> '7.0.0',
		'revslider-slicey-addon'			=> '7.0.0',
		'revslider-snow-addon'				=> '7.0.0',
		'revslider-spectrumlines-addon'		=> '7.1.5',
		'revslider-sunbeam-addon'			=> '7.1.5', //effects pipeline
		'revslider-thecluster-addon'		=> '7.1.5',
		'revslider-transitionpack-addon'	=> '7.1.6', //effects pipeline
		'revslider-typewriter-addon'		=> '7.0.0',
		'revslider-weather-addon'			=> '7.0.0',
		'revslider-whiteboard-addon'		=> '7.0.0',
	];

	/**
	 * @var RevSliderLoadBalancer
	 */
	private $rslb;
	
	public function __construct(){
		$upload_dir = wp_upload_dir();
		$this->addons_basedir = $upload_dir['basedir'] . $this->addons_path;
		$this->addons_baseurl = $upload_dir['baseurl'] . $this->addons_path;

		$this->rslb = RevSliderGlobals::instance()->get('RevSliderLoadBalancer');

		add_action('revslider_update_all_options', array($this, 'on_update_all_options'), 10, 2);
	}

	/**
	 * clear the addon list
	 * drops the per-request memo so the next read reflects a fresh install/activation
	 * @return void
	 */
	public function clear_addon_list(){
		$this->addons_list = [];
		$this->addons_list_short = [];
	}

	/**
	 * clear the addon list on rs-addons option change
	 *
	 * @param array $options
	 * @param string $field
	 * @return void
	 */
	public function on_update_all_options($options, $field){
		if('rs-addons' === $field){
			$this->clear_addon_list();
		}
	}
	
	/**
	 * get all the addons with information
	 *
	 * @param bool $short return only the short list
	 * @return array
	 **/
	public function get_addon_list($short = false){
		if(!function_exists('get_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
		
		if(!$short && !empty($this->addons_list)){
			return $this->addons_list;
		}
		if($short && !empty($this->addons_list_short)){
			return $this->addons_list_short;
		}

		$addons	= $this->get_options(['addons'], [], false, 'rs-addons');
		$addons	= (array)$addons;
		$addons = array_reverse($addons, true);
		if(empty($addons)) return $addons;

		$plugins = get_plugins();

		foreach($addons as $k => $addon){
			if(!is_object($addon)) continue;

			if(array_key_exists($addon->slug.'/'.$addon->slug.'.php', $plugins)){
				$addons[$k]->full_title	= $plugins[$addon->slug.'/'.$addon->slug.'.php']['Name'];
				$addons[$k]->active		= is_plugin_active($addon->slug.'/'.$addon->slug.'.php');
				$addons[$k]->installed	= $plugins[$addon->slug.'/'.$addon->slug.'.php']['Version'];
			}else{
				$addons[$k]->active	 = false;
				$addons[$k]->installed = false;
			}
		}

		if(!$short) {
			$this->addons_list = $addons;
			return $addons;
		}

		$_addons = [];
		foreach($addons as $k => $addon){
			if(!is_object($addon)) continue;

			$k = str_replace(['revslider-', '-addon'], '', $k);
			$addon		 = apply_filters('sr_get_addon_data', $addon, $addon->slug);
			$addon->slug = str_replace(['revslider-', '-addon'], '', $k);
			$_addons[$k] = $addon;
		}

		$this->addons_list_short = $_addons;

		return $_addons;
	}

	/**
	 * one catalogue entry; the handle may be given with or without the revslider-/-addon wrapper
	 * @return object|false
	 */
	public function get_addon_data($handle, $short = false){
		$list		= $this->get_addon_list($short);
		$_handle	= str_replace(['revslider-', '-addon'], '', $handle);
		$addon		= $this->get_val($list, $_handle, false);

		return $addon;
	}
	
	/**
	 * get a specific addon version
	 * @return string|false the installed version, false when the add-on is not installed
	 **/
	public function get_addon_version($handle){
		$list = $this->get_addon_list();
		return $this->get_val($list, [$handle, 'installed'], false);
	}

	/**
	 * check if any addon is below version x (for RS6.0 this is version 2.0)
	 * if yes give a message that tells to update
	 * compares every active add-on against $addon_version_required
	 * @return array handle => version the user should update to (empty when everything is current)
	 **/
	public function check_addon_version(){
		$rs_addons	= $this->get_addon_list();
		$update		= [];
		
		if(empty($rs_addons)) return $update;
	
		foreach($rs_addons ?? [] as $handle => $addon){
			$installed = $this->get_val($addon, 'installed');
			if(trim($installed) === '') continue;
			if($this->get_val($addon, 'active', false) === false) continue;
			
			$version = $this->get_val($this->addon_version_required, $handle, false);
			if($version !== false && version_compare($installed, $version, '<')){
				$handle = str_replace(['revslider-', '-addon'], '', $handle);
				$update[$handle] = (version_compare($version, $this->get_val($addon, 'available'), '>')) ? $version : $this->get_val($addon, 'available');
			}
		}
		
		return $update;
	}
	
	/**
	 * Install Add-On/Plugin
	 * downloads the zip when needed and activates the add-on afterwards
	 *
	 * @since 6.0
	 * @param bool $force re-download even when the plugin folder already exists
	 * @return true|string true on success, an error message otherwise
	 */

	public function install_addon($addon, $force = false){
		if(empty($addon) || 0 !== strpos($addon, 'revslider-')) return false;
		
		if(!function_exists('get_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';
		
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) !== true) return __('Please activate Slider Revolution', 'revslider');
		
		//check if downloaded already
		$plugins	= get_plugins();
		$addon_path = $addon.'/'.$addon.'.php';

		if(!array_key_exists($addon_path, $plugins) || $this->_truefalse($force) === true || !file_exists(WP_PLUGIN_DIR.'/'.$addon_path)){
			//download if nessecary
			$downloaded = $this->download_addon($addon);
			if($downloaded !== true) return $downloaded;
		}

		//activate
		return $this->activate_addon($addon_path);
	}
	
	
	/**
	 * Download Add-On/Plugin
	 *
	 * @since    1.0.0
	 */
	/**
	 * can the given file be opened as a ZIP archive and does it contain anything?
	 *
	 * Used as a gate before the installed add-on folder is deleted, so a truncated download or an error page
	 * that arrived with a 200 cannot destroy a working installation.
	 *
	 * @param string $file absolute path
	 * @return true|string true when usable, otherwise a human readable reason
	 */
	private function is_readable_zip($file){
		$size = (is_file($file)) ? filesize($file) : 0;
		if($size < 22) return __('the downloaded file is empty or incomplete', 'revslider'); //22 = smallest possible zip

		if(class_exists('ZipArchive')){
			$zip	= new ZipArchive();
			$opened	= $zip->open($file, ZipArchive::CHECKCONS);
			//CHECKCONS is stricter than the extractor behind this gate: WP's unzip_file() falls back to
			//PclZip on archives that fail the consistency check but still extract. Retry with a plain open
			//so the gate never rejects something unzip_file() would have installed - a truncated file fails
			//the plain open too (the central directory is gone either way).
			if($opened !== true) $opened = $zip->open($file);
			if($opened !== true) return __('the downloaded file is not a valid ZIP archive', 'revslider');

			$entries = $zip->numFiles;
			$zip->close();

			return ($entries > 0) ? true : __('the downloaded archive is empty', 'revslider');
		}

		//no ZipArchive: WP's unzip_file() will use PclZip, and PclZip locates the End of Central Directory
		//record at the file's tail - it never gates on the leading local header. So check for exactly that:
		//a download truncated mid-body keeps its leading "PK\x03\x04" but loses the EOCD, which is the case
		//this gate exists to catch. The EOCD ("PK\x05\x06") sits in the last 22 bytes plus at most 64KB of
		//archive comment.
		$handle = @fopen($file, 'rb');
		if($handle === false) return __('the downloaded file could not be read', 'revslider');

		$signature = fread($handle, 4);

		$tail_len = (int)min($size, 65557); //22 byte EOCD + 65535 byte max comment
		fseek($handle, -$tail_len, SEEK_END);
		$tail = fread($handle, $tail_len);
		fclose($handle);

		if($signature !== "PK\x03\x04") return __('the downloaded file is not a valid ZIP archive', 'revslider');
		if(!is_string($tail) || strpos($tail, "PK\x05\x06") === false) return __('the downloaded file is incomplete (no end of archive record)', 'revslider');

		return true;
	}

	/**
	 * fetch an add-on zip from the ThemePunch servers and unpack it into the plugins folder
	 * @return true|string true on success, an error message otherwise
	 */
	public function download_addon($addon){
		if($this->_truefalse($this->get_options(['system', 'valid'], 'false')) !== true) return __('Please activate Slider Revolution', 'revslider');
		
		$plugin_slug	= basename($addon);
		if(0 !== strpos($plugin_slug, 'revslider-')) die( '-1' );

		$code	= $this->get_options(['system', 'license'], '');
		$rattr	= [
			'code'		=> urlencode($code),
			'version'	=> urlencode(RS_REVISION),
			'product'	=> urlencode(RS_PLUGIN_SLUG),
			'type'		=> urlencode($plugin_slug)
		];
		$get = $this->rslb->call_url('addons/'.$plugin_slug.'/download.php', $rattr);
		if(is_wp_error($get)) return sprintf(__('Add-On could not be downloaded: %s', 'revslider'), $get->get_error_message());

		if($get && $get['body'] != 'invalid' && wp_remote_retrieve_response_code($get) == 200){
			$upload_dir	= wp_upload_dir();
			$file		= $upload_dir['basedir']. '/revslider/templates/' . $plugin_slug . '.zip';
			@mkdir(dirname($file), 0777, true);
			$ret		= @file_put_contents($file, $get['body']);
			if($ret === false) return sprintf(__('Add-On package could not be written to %s. Please check the folder permissions.', 'revslider'), dirname($file));

			require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
			require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
			if(!function_exists('WP_Filesystem')) require_once ABSPATH . 'wp-admin/includes/file.php';
			
			$fsd = new WP_Filesystem_Direct(false);
			WP_Filesystem();
			
			global $wp_filesystem;

			$upload_dir	= wp_upload_dir();
			$d_path		= WP_PLUGIN_DIR;

			//verify the download is a usable archive BEFORE the installed add-on is removed. A truncated or
			//rejected download used to wipe a working add-on and leave nothing in its place.
			$readable = $this->is_readable_zip($file);
			if($readable !== true){
				wp_delete_file($file);
				return sprintf(__('Add-On package could not be extracted: %s', 'revslider'), $readable);
			}

			$fsd->rmdir($d_path . '/' . $plugin_slug, true); //remove the addon folder if exists

			$unzipfile	= unzip_file($file, $d_path);

			if(is_wp_error($unzipfile)){
				//guard the define: without it a second add-on download in the same request triggers a
				//"constant already defined" warning in the middle of the JSON response
				if(!defined('FS_METHOD')) define('FS_METHOD', 'direct'); //lets try direct.

				WP_Filesystem();  //WP_Filesystem() needs to be called again since now we use direct !

				//the previous third and fourth attempt are gone: the third repeated the second verbatim, and
				//the fourth extracted into uploads/revslider/templates/ and still reported success - an add-on
				//in a folder WordPress never loads plugins from.
				$unzipfile = unzip_file($file, $d_path);
			}

			wp_delete_file($file);

			$this->flush_wp_cache();
			$this->clear_addon_list();

			if(is_wp_error($unzipfile)) return sprintf(__('Add-On package could not be extracted: %s', 'revslider'), $unzipfile->get_error_message());

			return true;
		}

		if(!$get) return __('No response from the Slider Revolution server while downloading the Add-On.', 'revslider');
		if($get['body'] == 'invalid') return __('The Slider Revolution server rejected the Add-On download. Please check your license.', 'revslider');

		return sprintf(__('Add-On could not be downloaded, the server responded with status %s.', 'revslider'), wp_remote_retrieve_response_code($get));
	}
	
	/**
	 * Activates Installed Add-On/Plugin
	 * @param string    $candidate    plugin path, e.g. revslider-lottie-addon/revslider-lottie-addon.php
	 * @param bool|null $network_wide null decides from the current context
	 * @return bool true when the add-on is active afterwards
	 */
	public function activate_addon($candidate, $network_wide = null){
		if(empty($candidate) || 0 !== strpos($candidate, 'revslider-')) return false;
		
		// Determine network_wide default
		if($network_wide === null) $network_wide = is_multisite() && is_network_admin();
		
		// If already inactive, report cleanly
		$isActive = true;
		if(!is_plugin_active($candidate) || (is_multisite() && !is_plugin_active_for_network($candidate))){
			// Activate
			$result = activate_plugins($candidate, false, (bool) $network_wide);
			if (is_wp_error($result)) return false;
			
			// Check result
			$isActive = is_plugin_active($candidate) || (is_multisite() && is_plugin_active_for_network($candidate));
		}
		
		$this->clear_addon_list();

		return $isActive;
	}

	/**
	 * Deactivate an addon by handle or plugin path.
	 *
	 * @param string      $addon         Handle like 'revslider-404-addon' OR full plugin path 'revslider-404-addon/index.php'
	 * @param null|bool   $network_wide  Pass true/false to force network-wide deactivation on multisite. NULL = auto.
	 * @return bool
	 */
	public function deactivate_addon($addon, $network_wide = null){
		if(empty($addon) || 0 !== strpos($addon, 'revslider-')) return false;
		
		$candidate = $this->find_addon_path($addon);
		if($candidate === null) return false;

		// Determine network_wide default
		if($network_wide === null) $network_wide = is_multisite() && is_network_admin();

		// If already inactive, report cleanly
		if(!is_plugin_active($candidate) && !(is_multisite() && is_plugin_active_for_network($candidate))) return true;

		// Deactivate
		deactivate_plugins($candidate, false, (bool) $network_wide);

		// Check result
		$stillActive = is_plugin_active($candidate) || (is_multisite() && is_plugin_active_for_network($candidate));

		$this->clear_addon_list();

		return ! $stillActive;
	}


	/**
	 * resolve an add-on slug to its actual plugin file, since the folder name is not always the slug
	 * @param string $status only used for the error message
	 * @return string|null plugin path relative to the plugins folder
	 */
	public function find_addon_path($addon, $status = 'activated'){
		if(!function_exists('get_plugins')) require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// If it already looks like a plugin path and the file exists, use it directly
		$candidate = null;
		if(strpos($addon, '/') !== false && str_ends_with(strtolower($addon), '.php')){
			$full = trailingslashit(WP_PLUGIN_DIR) . $addon;
			if(file_exists($full)) $candidate = $addon; // already a valid plugin basename
		}

		// Otherwise, treat input as "handle" (e.g., 'revslider-404-addon') and try to resolve
		if(!$candidate){
			$handle = strtolower(trim($addon));

			// Helper to slugify strings similar to WP-style slugs
			$slugify = function ($s) {
				$s = strtolower($s);
				$s = preg_replace('~[^\pL\d]+~u', '-', $s);
				$s = trim($s, '-');
				$s = preg_replace('~[^-\w]+~', '', $s);
				return $s;
			};

			$plugins   = get_plugins();
			$found = 0;

			foreach($plugins ?? [] as $base => $data){
				// $base e.g. "revslider-404-addon/index.php"
				$dir  = strtolower(dirname($base));                 // "revslider-xxx-addon"
				$file = strtolower(basename($base, '.php'));        // "index" (or "revslider-xxx-addon")

				// Scoring heuristics (prefer exact dir match, then file, then TextDomain, then Name slug, then "contains")
				if($dir === $handle)                      $found = max($found, 100);
				if($file === $handle)                     $found = max($found, 90);

				if($found > 0 && is_plugin_active($base)){
					$plugin_status = is_plugin_active($base);

					if($plugin_status !== true && $status === 'activated') continue;
					if($plugin_status === true && $status === 'deactivated') continue;
					
					$candidate = $base;
					break;
				}
			}
		}

		return $candidate;

	}

	/**
	 * public URL of an add-on's logo/banner image, downloading it on first access
	 * @return string
	 */
	public function _get_media_url($handle, $download = true){
		return $this->_check_file_path($handle, true, $download);
	}

	/**
	 * check if image was uploaded, if yes, return path or url
	 * re-downloads the file when its checksum no longer matches the catalogue
	 * @param bool $url      return a public URL instead of a filesystem path
	 * @param bool $download allow fetching the image when it is missing
	 * @return string the resolved path/url, or $image unchanged when it could not be fetched
	 */
	public function _check_file_path($image, $url = false, $download = true){
		if(!wp_mkdir_p($this->addons_basedir)) return $image;
		
		$base_url = ($url) ? $this->addons_baseurl : $this->addons_basedir;
		$file     = $this->addons_basedir . $image;
		if(file_exists($file)){
			if(!$this->check_checksum($image, $file)) {
				$this->rslb->download_url( $image, $file, 'updates', false, $this->addons_basedir);
			}
			return $base_url . $image;
		}
		
		if($download !== true) return $image;
		
		$this->rslb->download_url($image, $file, 'updates', false, $this->addons_basedir);

		return (file_exists($file)) ? $base_url . $image : $image;
	}

	/**
	 * does the local file still match the md5 the catalogue lists for it?
	 * @return bool false also when the image is not in the catalogue at all
	 */
	public function check_checksum($image, $file){
		$addons_list = $this->get_addon_list(true);
		foreach($addons_list ?? [] as $key => $addon){
			//if($this->get_val($addon, 'background') === $image) return md5_file($file) !== $this->get_val($addon, 'background_md5');
			if($this->get_val($addon, ['logo', 'img_file']) === $image) return md5_file($file) === $this->get_val($addon, ['logo', 'img_md5']);
			if($this->get_val($addon, 'banner_file') === $image) return md5_file($file) === $this->get_val($addon, 'banner_md5');
		}

		return false; //image not found
	}

	/**
	 * get the addons that need to be migrated from old to new addon slugs during upgrade
	 * 
	 * @return array
	 */
	public function get_addons_to_migrate(){
		return [
			'revslider-domain-switch-addon' => 'revslider-domainswitch-addon',
			'revslider-prevnext-posts-addon' => 'revslider-prevnextposts-addon',
			'revslider-rel-posts-addon' => 'revslider-relposts-addon',
			'revslider-liquideffect-addon' => 'revslider-distortion-addon'
		];
	}
	
	/**
	 * get the addons that need to be removed during upgrade
	 * 
	 * @return array
	 */
	public function get_addons_to_remove(){
		return [
			'revslider-backup-addon',
		];
	}

	/**
	 * flush all cache
	 */
	public function flush_wp_cache(){
		wp_clean_plugins_cache(true);
		parent::flush_wp_cache();
	}
}
