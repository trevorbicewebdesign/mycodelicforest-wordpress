<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * AddOn admin notices, hoisted out of every AddOn (each shipped an identical includes/notices.class.php
 * + dismiss-admin-notice.js). One core implementation + one core dismiss script for the whole fleet.
 * Shown when an AddOn can not run: missing/old core or missing Purchase Code registration.
 *
 * Usage from an AddOn fallback path: RevSliderAddonNotices::show($title, $type[, $min_version]).
 * Types: 'add_notice_activation' (no registration), 'add_notice_plugin' (core missing), 'add_notice_version' (core too old).
 **/
/**
 * Admin notice an AddOn shows when it needs a newer core version than the one installed.
 */
class RevSliderAddonNotices extends RevSliderFunctions {

	private $title;
	private $notice;
	private $min_version;

	/** @return RevSliderAddonNotices */
	public static function show($title, $notice, $min_version = ''){
		return new self($title, $notice, $min_version);
	}

	public function __construct($title, $notice, $min_version = ''){
		$this->title		= $title;
		$this->notice		= $notice;
		$this->min_version	= $min_version;

		add_action('admin_enqueue_scripts', [$this, 'enqueue_notice_script']);
		add_action('admin_notices', [$this, 'add_notice']);
	}

	/** @return void */
	public function enqueue_notice_script(){
		wp_enqueue_script('rs_'.$this->title.'-notice', RS_PLUGIN_URL.'admin/assets/js/dismiss-admin-notice.js', ['jquery'], RS_REVISION, true);
	}

	/** @return void echoes the notice markup */
	public function add_notice(){
		switch($this->notice){

			case 'add_notice_activation':
				$id = md5('revslider_'.$this->title.'_addon'.'_add_notice_activation');
				$this->notice = 'The <a href="?page=revslider">'.ucfirst($this->title).' Add-On</a> requires an active '.
						'<a href="//www.sliderrevolution.com/help/setup-guide-install-unlock-update/#h-unlock-slider-revolution" target="_blank">Purchase Code Registration</a>';
			break;

			case 'add_notice_plugin':
				$id = md5('revslider_'.$this->title.'_addon'.'_add_notice_activation');
				$this->notice = '<a href="//revolution.themepunch.com/" target="_blank">Slider Revolution</a> required to use the '.ucfirst($this->title).' Add-On';
			break;

			case 'add_notice_version':
				$id = md5('revslider_'.$this->title.'_addon'.'_add_notice_activation');
				$this->notice = 'The '.ucfirst($this->title).' Add-On requires Slider Revolution '.$this->min_version.
						'  <a href="//www.themepunch.com/slider-revolution/install-activate-and-update/#plugin-updates" target="_blank">Update Slider Revolution</a>';
			break;
			default:
				$id = '';
				$this->notice = '';
			// end default
		}

		if($this->notice === '') return;

		?>
		<div class="error below-h2 soc-notice-wrap revaddon-notice" style="display: none">
			<p><?php _e($this->notice, 'rs_'.$this->title); ?><span data-addon="<?php echo 'rs_'.$this->title; ?>-notice" data-noticeid="<?php echo $id; ?>" style="float: right; cursor: pointer" class="revaddon-dismiss-notice dashicons dashicons-dismiss"></span></p>
		</div>
		<?php
	}
}
