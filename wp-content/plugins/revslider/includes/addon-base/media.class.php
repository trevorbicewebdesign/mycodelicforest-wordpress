<?php
/**
 * @author	ThemePunch <info@themepunch.com>
 * @link	  https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * AddOn media export/import. Only wired for AddOns that declare a 'media' config.
 * Collects the configured media paths below addOns.<title> into the slider export list and
 * registers the same paths for import, plus raw v6 legacy entries.
 **/
/**
 * Media handling for one AddOn: contributes the image/video files the AddOn references to a slider export,
 * and maps their paths back when such a package is imported again.
 */
class RevSliderAddonMedia extends RevSliderFunctions {

	private $title;
	private $media	= false;	//['layers' => [path arrays below addOns.<title>], 'slides' => [...], 'v6' => raw legacy import entries]

	public function __construct($base, $args){
		$this->title	= $base->title;
		$this->media	= $this->get_val($args, 'media', false);

		if(is_admin() && $this->media !== false){
			add_filter('sr_exportSlider_usedMedia', [$this, 'export_addon_media'], 10, 3);
			add_filter('revslider_import_image_path', [$this, 'add_import_paths']);
			if(!empty($this->media['v6'])) add_filter('revslider_import_image_path_v6', [$this, 'add_import_paths_v6']);
		}
	}

	/**
	 * Collect the configured Media Paths into the Export List. The old copied export_adddon_images never added the src urls ($url/$src Copy Paste), this walk does
	 * @return array the media files this addon references, added to the export package
	 **/
	public function export_addon_media($used_media, $slides, $sliderParams){
		foreach($this->get_val($this->media, 'layers', []) as $path){
			foreach($slides ?? [] as $slide){
				foreach($this->get_val($slide, 'layers', []) as $layer){
					$this->collect_media($this->get_val($layer, ['addOns', $this->title], []), $path, $used_media);
				}
			}
		}
		foreach($this->get_val($this->media, 'slides', []) as $path){
			foreach($slides ?? [] as $slide){
				$node = $this->get_val($slide, ['params', 'addOns', $this->title], $this->get_val($slide, ['addOns', $this->title], []));
				$this->collect_media($node, $path, $used_media);
			}
		}
		return $used_media;
	}

	/** @return void */
	private function collect_media($node, $path, &$used_media){
		if(empty($node)) return;
		$key = array_shift($path);
		if($key === '__ARRAY__'){
			if(!is_array($node)) return;
			foreach($node as $sub) $this->collect_media($sub, $path, $used_media);
			return;
		}
		$next = $this->get_val($node, $key, false);
		if(empty($path)){
			if(is_string($next) && $next !== '') $used_media[$next] = true;
			return;
		}
		$this->collect_media($next, $path, $used_media);
	}

	/** @return array */
	public function add_import_paths($image_path){
		foreach($this->get_val($this->media, 'layers', []) as $path) $image_path['layers'][] = array_merge(['addOns', $this->title], $path);
		foreach($this->get_val($this->media, 'slides', []) as $path) $image_path['slides'][] = array_merge(['addOns', $this->title], $path);
		return $image_path;
	}

	/**
	 * v6 Entries are raw full Paths per Environment, the legacy Schemas differ too much per AddOn for a
	 * Prefix Convention
	 * @return array
	 */
	public function add_import_paths_v6($image_path){
		foreach($this->get_val($this->media, 'v6', []) as $env => $paths){
			foreach($paths ?? [] as $path) $image_path[$env][] = $path;
		}
		return $image_path;
	}
}
