<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Image optimizer.
 *
 * Generates resized WebP copies of the images a slider uses and stores them under
 * uploads/revslider/o/<slider id>/. Runs after a slider is saved or imported, and optionally on the fly
 * while rendering. Needs GD or Imagick with WebP support - verify_webp() gates everything.
 */
class RevSliderOptimizer extends RevSliderFunctions {

	/**
	 * @var bool Enable debug mode
	 */
	protected $debug = false;

	/**
	 * @var array Default settings
	 */
	protected $defaults = [
		'u'   => 'true',
		'f'   => 'webp',
		'mw'  => '2048',
		'mh'  => '2048',
		'msc' => '1.3',
		'q'   => 85,
		'otf' => 'false',
	];

	/**
	 * @var array Allowed image formats
	 */
	protected $allowed_formats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

	/**
	 * @var array Actual settings
	 */
	protected $settings = [];

	/**
	 * @var bool Is Image Optimizer enabled?
	 */
	protected $enabled = false;

	/**
	 * @var array Available php libraries to process webP convert
	 */
	protected $libs = [];

	/**
	 * @var string Path to the uploads folder
	 */
	protected $basedir;

	/**
	 * @var string Uploads folder URL
	 */
	protected $baseurl;

	/**
	 * @var string Uploads folder URL
	 */
	protected $basescheme;

	/**
	 * @var string Destination folder (relative to uploads)
	 */
	protected $dest_path = 'revslider/o/';

	/**
	 * @var bool Generate optimized images on load
	 */
	protected $on_the_fly = false;

	/**
	 * @var int Quality of the generated images
	 */
	protected $quality = 85;

	/**
	 * @var array Cache for image dimensions
	 */
	protected $imagesize_cache = [];

	/**
	 * Check if the Image Optimizer is enabled.
	 * Add hooks.
	 */
	public function __construct(){
		$globals = $this->get_global_settings();
		$this->settings = wp_parse_args($this->get_val($globals, ['opt', 'img']), $this->defaults);
		$this->enabled = $this->_truefalse($this->settings['u']);
		if (!$this->is_enabled() || !$this->verify_webp()) {
			$this->log('Optimizer is disabled or webP convert is not available');
			return;
		}
		
		foreach (['mw', 'mh'] as $key) {
			$this->settings[$key] = intval($this->settings[$key]);
			if ($this->settings[$key] < 1) $this->settings[$key] = intval($this->defaults[$key]);
		}

		// scale multiplier (msc): generate images a bit bigger than requested to avoid blur / look good on retina.
		$this->settings['msc'] = isset($this->settings['msc']) ? (float) $this->settings['msc'] : (float) $this->defaults['msc'];
		if ($this->settings['msc'] < 1.0) $this->settings['msc'] = 1.0;
		if ($this->settings['msc'] > 4.0) $this->settings['msc'] = 4.0;

		$this->set_quality($this->settings['q']);
		$this->set_on_the_fly($this->settings['otf']);

		$ud	 = wp_upload_dir();
		
		$this->baseurl = trailingslashit($ud['baseurl']);
		if (!filter_var($this->baseurl, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
			$this->log('Base URL validation error: ' . $this->baseurl);
			return;
		}
		
		$parsed = parse_url($this->baseurl);
		if (empty($parsed['scheme'])){
			$this->log('Base URL parse error: ' . $this->baseurl);
			return;
		}
		$this->basescheme = $parsed['scheme'];
		
		$this->basedir = trailingslashit($ud['basedir']);
		if (!wp_mkdir_p($this->basedir . $this->dest_path)) {
			$this->log('Could not create destination folder: ' . $this->basedir . $this->dest_path);
			return;
		}

		$this->add_hooks();
	}

	/**
	 * Add hooks.
	 * only reached when the optimizer is enabled and WebP is available
	 * @return void
	 */
	protected function add_hooks(){
		add_action('revslider_api_save_slider_after', [$this, 'optimize_images'], 10, 2);
		add_action('revslider_api_save_slider_advanced_after', [$this, 'optimize_images_advanced'], 10, 2);
		add_action('revslider_slider_imported', [$this, 'optimize_slider_imported']);
		add_action('revslider_slider_on_delete_slider', [$this, 'on_delete_slider'], 10, 1);
		add_filter('sr_get_image_lists', [$this, 'add_images'], 10, 2);
	}

	/**
	 * Is Generate optimized images on load enabled?
	 *
	 * @return bool
	 */
	public function is_on_the_fly(){
		return $this->on_the_fly;
	}

	/**
	 * Set Generate optimized images on load.
	 *
	 * @param bool|string $otf
	 */
	public function set_on_the_fly($otf){
		$this->on_the_fly = $this->_truefalse($otf);;
	}

	/**
	 * Get quality of the generated images.
	 */
	public function get_quality(){
		return $this->quality;
	}

	/**
	 * Set quality of the generated images.
	 *
	 * @param int $quality Quality of the generated images.
	 * @return void
	 */
	public function set_quality($quality){
		$this->quality = (int) $quality;
		if ( $this->quality < 0 || $this->quality > 100) {
			// Fallback to default value.
			$this->quality = 85;
		}

	}

	protected function log( $msg ){
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && $this->debug ) {
			error_log( 'RSOptimizer: ' . $msg );
		}
	}

	/**
	 * Is the optimizer enabled?
	 *
	 * @return bool
	 */
	public function is_enabled(){
		return $this->enabled;
	}

	/**
	 * Verify if the webP convert is available.
	 *
	 * @return bool
	 */
	public function verify_webp(){
		if (!extension_loaded('gd') && !extension_loaded('imagick') ){
			$this->log('verify_webp failed: GD or Imagick extension is not loaded');
			return false;
		}

		$gd_functions = [
			'imagecreatefromjpeg',
			'imagecreatefrompng',
			'imagecreatefromgif',
			'imageistruecolor',
			'imagepalettetotruecolor',
			'imagewebp',
		];
		if (!in_array(false, array_map('function_exists', $gd_functions), true)) {
			$this->libs[] = 'gd';
		}

		if(extension_loaded('imagick') && class_exists('Imagick')){
			$image = new Imagick();
			if( in_array( 'WEBP', $image->queryFormats() ) ){
				$this->libs[] = 'imagick';
			}
		}

		if (empty($this->libs)){
			$this->log('verify_webp failed: Available libraries does not support webP');
		}

		return !empty($this->libs);
	}

	/**
	 * Get image dimensions (width / height).
	 *
	 * @param string $path Path to the image.
	 * @return array|null Array with width and height or null if the image could not be read.
	 */
	public function getimagesize($path){
		if (isset($this->imagesize_cache[$path])) {
			return $this->imagesize_cache[$path];
		}

		if (!is_file($path)) {
			$this->log('Could not read image dimensions: ' . $path);
			$this->imagesize_cache[$path] = null;
			return null;
		}

		$info = @getimagesize($path);
		if ($info === false) {
			$this->log('Could not read image dimensions: ' . $path);
			$this->imagesize_cache[$path] = null;
			return null;
		}

		$this->imagesize_cache[$path] = [
			'w' => (int) $info[0],
			'h' => (int) $info[1],
		];

		return $this->imagesize_cache[$path];
	}

	/**
	 * Calculates and returns the dimensions of an image.
	 *
	 * @param string $path      The file path to the image.
	 * @param array  $requested Requested dimensions.
	 * @param string $src_ext   Source image extension.
	 * @return array|null The dimensions of the image as an array, or null if unable to determine dimensions.
	 */
	protected function calculate_dimensions($path, $requested, $src_ext){
		// Read original dimensions
		$orig = $this->getimagesize($path);
		if (empty($orig) || $orig['w'] <= 0 || $orig['h'] <= 0) {
			$this->log('Could not read image dimensions: ' . $path);
			return null;
		}
		$origW = $orig['w'];
		$origH = $orig['h'];

		// Max bounds
		$maxW = $this->settings['mw'];
		$maxH = $this->settings['mh'];

		// requested size from slider data
		$requested = $this->normalize_requested_dim($requested);
		$reqW = $requested['w'];
		$reqH = $requested['h'];

		// Apply scale multiplier (msc) to generate a bigger image than requested (helps retina / avoids blur)
		$msc = isset($this->settings['msc']) ? (float) $this->settings['msc'] : 1.0;
		if ($msc > 1.0) {
			$reqW = (int) ceil($reqW * $msc);
			$reqH = (int) ceil($reqH * $msc);
		}


		$this->log('calculate_dimensions ' . str_replace($this->basedir, '', $path));
		$this->log(json_encode(['orig' => $orig, 'requested' => $requested, 'max' => ['w' => $maxW, 'h' => $maxH]]));

		// Absolute cap from settings
		$capW = $maxW / $origW;
		$capH = $maxH / $origH;
		$cap  = min($capW, $capH, 1.0);

		// Choose the larger possible result while preserving ratio (no crop) within $cap.
		$candidates = [];
		$candidates[] = $reqW / $origW;
		$candidates[] = $reqH / $origH;

		$scale = min(max($candidates), $cap);

		// We do not shrink
		if ($scale >= 1.0) {
			// check if we still need to convert an image to the different format
			if ($src_ext == $this->get_dest_ext($src_ext)) return null;
			return [
				'w' => $origW,
				'h' => $origH,
			];
		}

		$newW = max(1, (int) floor($origW * $scale));
		$newH = max(1, (int) floor($origH * $scale));

		return [
			'w' => $newW,
			'h' => $newH,
		];
	}

	/**
	 * Converts and saves an image from one format to another using GD library.
	 *
	 * @param string $src_file The path to the source image file.
	 * @param string $dst_file The path to the destination image file.
	 * @param array $dim Dimensions. ['w'=>int,'h'=>int]
	 *
	 * @return array|bool An array with conversion details or false on failure.
	 */
	public function save_gd($src_file, $dst_file, $dim){
		$loaders = [
			'jpg'  => 'imagecreatefromjpeg',
			'jpeg' => 'imagecreatefromjpeg',
			'png'  => 'imagecreatefrompng',
			'gif'  => 'imagecreatefromgif',
			'webp' => 'imagecreatefromwebp',
		];

		$savers = [
			'jpg'  => 'imagejpeg',
			'jpeg' => 'imagejpeg',
			'png'  => 'imagepng',
			'gif'  => 'imagegif',
			'webp' => 'imagewebp',
		];

		$src_ext = pathinfo($src_file, PATHINFO_EXTENSION);
		$dst_ext = pathinfo($dst_file, PATHINFO_EXTENSION);
		if (!isset($loaders[$src_ext], $savers[$dst_ext])) {
			return false;
		}
		if (!function_exists($loaders[$src_ext]) || !function_exists($savers[$dst_ext])) {
			return false;
		}

		try {
			$create = $loaders[$src_ext];
			$save   = $savers[$dst_ext];

			$image = @$create($src_file);
			if (!$image) {
				return false;
			}

			// Convert palette images when targeting formats that benefit from truecolor
			if (in_array($dst_ext, ['webp', 'jpg', 'jpeg'], true) && !imageistruecolor($image)) {
				imagepalettetotruecolor($image);
			}

			// Preserve alpha where applicable (PNG/WebP). Harmless for others.
			imagealphablending($image, true);
			imagesavealpha($image, true);

			$src_w = imagesx($image);
			$src_h = imagesy($image);
			if ($src_w !== $dim['w'] || $src_h !== $dim['h']) {
				$resized = imagecreatetruecolor($dim['w'], $dim['h']);
				// preserve alpha for formats that support it
				imagealphablending($resized, false);
				imagesavealpha($resized, true);
				$transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
				imagefilledrectangle($resized, 0, 0, $dim['w'], $dim['h'], $transparent);

				imagecopyresampled($resized, $image, 0, 0, 0, 0, $dim['w'], $dim['h'], $src_w, $src_h);
				$image = $resized;
			}

			$ok = false;
			switch ($dst_ext) {
				case 'jpg':
				case 'jpeg':
				case 'webp':
					$ok = $save($image, $dst_file, $this->get_quality());
					break;

				case 'png':
					// imagepng compression: 0..9 (0 = no compression, 9 = max compression)
					$compression = (int) round(9 - ($this->get_quality() / 100) * 9);
					$ok = $save($image, $dst_file, $compression);
					break;

				default:
					// gif (no quality param)
					$ok = $save($image, $dst_file);
			}

			if (!$ok || !is_file($dst_file)) {
				return false;
			}

			return [
				'path' => $dst_file,
				'size' => [
					'before' => filesize($src_file),
					'after'  => filesize($dst_file),
				],
			];
		} catch (\Throwable $e) {
			$this->log($e->getMessage()); //not print_r($e): the argument is built even when log() is disabled, and the dump carries the full stack trace incl. argument values
			return false;
		}
	}

	/**
	 * Converts and saves an image from one format to another using IMagick library.
	 *
	 * @param string $src_file The path to the source image file.
	 * @param string $dst_file The path to the destination image file.
	 * @param array $dim Dimensions. ['w'=>int,'h'=>int]
	 *
	 * @return array|bool An array with conversion details or false on failure.
	 */
	public function save_imagick($src_file, $dst_file, $dim){
		try {
			$image = new Imagick($src_file);
			$image->setImageColorspace(Imagick::COLORSPACE_RGB);
			$image->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);

			$src_w = $image->getImageWidth();
			$src_h = $image->getImageHeight();

			if ($src_w !== $dim['w'] || $src_h !== $dim['h']) {
				$image->resizeImage($dim['w'], $dim['h'], Imagick::FILTER_LANCZOS, 1, false);
			}

			$dst_ext = pathinfo($dst_file, PATHINFO_EXTENSION);
			switch ($dst_ext) {
				case 'jpg':
				case 'jpeg':
					$image->setImageFormat('jpeg');
					$image->setImageCompression(Imagick::COMPRESSION_JPEG);
					$image->setImageCompressionQuality($this->get_quality());
					break;

				case 'png':
					$image->setImageFormat('png');
					$image->setImageCompressionQuality($this->get_quality());
					break;

				case 'webp':
					$image->setImageFormat('webp');
					$image->setImageCompressionQuality($this->get_quality());
					break;

				default:
					$image->setImageFormat($dst_ext);
			}

			$image->writeImage($dst_file);
			$image->clear();

			if (!is_file($dst_file)) {
				return false;
			}

			return [
				'path' => $dst_file,
				'size' => [
					'before' => filesize($src_file),
					'after'  => filesize($dst_file),
				],
			];
		} catch (\Throwable $e) {
			$this->log($e->getMessage()); //not print_r($e): the argument is built even when log() is disabled, and the dump carries the full stack trace incl. argument values
			return false;
		}
	}

	/**
	 * Save an optimized image using the first available library.
	 *
	 * @param string $src_file The path to the source image file.
	 * @param string $dst_file The path to the destination image file.
	 * @param array $dim       Dimensions. ['w'=>int,'h'=>int]
	 * @return array|bool An array with conversion details or false on failure.
	 */
	public function save($src_file, $dst_file, $dim){
		if (empty($this->libs)) return false;
		$fn = 'save_' . $this->libs[0];
		if (!method_exists($this, $fn)) return false;
		if (!is_file($src_file)) return false;

		//only raise to 1G / 120s; never lower a higher host limit, leave unlimited alone, and stay silent when
		//ini_set()/set_time_limit() are blocked (a warning here would corrupt the JSON response)
		$this->raise_runtime_limits(120, '1G');

		$dimW = (int) ($dim['w'] ?? 0);
		$dimH = (int) ($dim['h'] ?? 0);
		if ($dimW <= 0 || $dimH <= 0) {
			$this->log('Requested dimensions are invalid: ' . $dimW . 'x' . $dimH);
			return false;
		}

		if (!wp_mkdir_p(dirname($dst_file))) {
			$this->log('Could not create destination directory: ' . dirname($dst_file));
			return false;
		}

		// Skip if dimensions already match the desired ones
		$existing = $this->getimagesize($dst_file);
		if (!empty($existing) && $existing['w'] === $dimW && $existing['h'] === $dimH) {
			return [
				'skipped' => true,
				'path'    => $dst_file,
				'size'    => [
					'before' => filesize($src_file),
					'after' => filesize($dst_file)
				],
			];
		}

		return $this->$fn($src_file, $dst_file, $dim);
	}

	/**
	 * Fill required dimensions with default max values if not provided.
	 *
	 * @param array  $dim  Dimensions. ['w'=>int,'h'=>int]
	 * @return array
	 */
	protected function normalize_requested_dim($dim){
		$result = $dim ?? [];
		$result['w'] = isset($result['w']) ? (int) $result['w'] : (int) $this->settings['mw'];
		$result['h'] = isset($result['h']) ? (int) $result['h'] : (int) $this->settings['mh'];
		return $result;
	}

	/**
	 * @param string $filename Absolute path|url to image file
	 * @return string
	 */
	protected function get_filename_hash($filename){
		return substr( hash( 'sha256', str_replace([$this->basedir, $this->baseurl], '', $filename) ),0,32 );
	}

	/**
	 * @param string $src_ext  Source image extension
	 * @return string
	 */
	protected function get_dest_ext($src_ext){
		if ('webp' === $this->settings['f']) return 'webp';
		return strtolower($src_ext);
	}

	/**
	 * @param string $src_url      Source image URL
	 * @param int    $slider_alias Slider Alias
	 * @param string $suffix
	 * @return string Absolute path to destination image in module folder
	 */
	public function get_dest_thumb_file($src_url, $slider_alias, $suffix = ''){
		//take the path component first: basename() on "…/bild.jpg?v=2" returns "bild.jpg?v=2", after which
		//pathinfo() reports the extension as "jpg?v=2" and the generated file name carries the query string
		$url_path = wp_parse_url($src_url, PHP_URL_PATH);
		$src_file = basename(($url_path !== false && $url_path !== null) ? $url_path : $src_url);
		$path_parts = pathinfo($src_file);

		$path = $this->basedir . 'revslider/' . $slider_alias . '/' . $path_parts['filename'] . $suffix . '.' . $this->get_dest_ext($this->get_val($path_parts, 'extension', ''));
		$path     = wp_normalize_path($path);
		$base_dir = wp_normalize_path($this->basedir . 'revslider/');

		if (validate_file($path) !== 0) {
			return '';
		}

		if (strpos($path, $base_dir) !== 0) {
			return '';
		}

		return $path;
	}
	
	/**
	 * @param string $src_file  Absolute path to source image
	 * @param int    $slider_id Slider ID
	 * @return string Absolute path to destination image
	 */
	protected function get_dest_file($src_file, $slider_id){
		$path_parts = pathinfo($src_file);
		$path = $this->basedir . $this->dest_path . $slider_id . '/';
		return $path . $this->get_filename_hash($src_file) . '.' . $this->get_dest_ext($path_parts['extension']);
	}

	/**
	 * @param mixed  $src_url Absolute url to thumb image | array with image data
	 * @param int    $slider_id
	 * @param array  $dim       Dimensions. ['w'=>int,'h'=>int]
	 * @param string $dest_file Absolute path to destination image
	 * @return array
	 */
	public function optimize_webp($src_url, $slider_id, $dim, $dest_file){
		if (!$this->is_enabled() || !$this->verify_webp()) {
			return [];
		}
		if ( empty( $src_url ) ) {
			$this->log('optimize_webp: empty src url');
			return [];
		}
		
		// check if the image is located in the plugin folder
		$plugin_file = false;
		//strpos() !== false: a hit at offset 0 returns 0, which is falsy - the rest of the codebase spells
		//this out too
		if (strpos($src_url, '/wp-content/plugins/revslider/') !== false){
			$parts = explode('/wp-content/plugins/revslider/', $src_url);
			//the *destination* was already contained by basename(), but the source was taken from the URL as
			//is - a crafted layer image URL containing "../" could copy any readable file on the server into
			//the public uploads folder. validate_file() rejects traversal, and the resolved path has to stay
			//inside the plugin directory.
			//rawurldecode() first: "%2e%2e%2f" cannot resolve on a filesystem, but decoding before the check
			//means the guard holds no matter whether the URL reached us encoded or already decoded
			$rel_file = (!empty($parts[1])) ? wp_normalize_path(rawurldecode($parts[1])) : '';
			if ($rel_file !== '' && validate_file($rel_file) === 0 && strpos($rel_file, '../') === false) {
				$src_path = wp_normalize_path(RS_PLUGIN_PATH . $rel_file);
				$base_dir = wp_normalize_path(RS_PLUGIN_PATH);

				if (strpos($src_path, $base_dir) === 0 && is_file($src_path)) {
					$plugin_file = $this->basedir . $this->dest_path . basename($rel_file);
					if (wp_mkdir_p(dirname($plugin_file)) && @copy($src_path, $plugin_file)) {
						$src_url = $this->baseurl . str_replace($this->basedir, '', $plugin_file);
					}else{
						//nothing was copied - do not point $src_url at a file that is not there
						$this->log('optimize_webp: could not copy plugin file ' . $src_path);
						$plugin_file = false;
					}
				}
			}
		}

		// ensure that the image will be converted to webp format. try/finally so an exception inside
		// optimize_single_image() cannot leave the optimizer stuck on 'webp' for the rest of the request.
		$original_ext = $this->settings['f'];
		$this->settings['f'] = 'webp';

		try{
			$result = $this->optimize_single_image(
				[
					'src'    => $src_url,
					'r'      => $dim,
					'dest_file' => $dest_file,
				],
				$slider_id
			);
		}finally{
			// restore original format
			$this->settings['f'] = $original_ext;

			// remove the plugin file if it was created
			if ( $plugin_file ) {
				wp_delete_file($plugin_file);
			}
		}

		if (empty($result['path'])) return [];

		return [
			'path' => $result['path'],
			'url'  => $this->baseurl . str_replace($this->basedir, '', $result['path'])
		];
	}
	
	/**
	 * @param mixed  $thumb_src Absolute url to thumb image | array with image data
	 * @param int    $slider_id
	 * @return mixed
	 */
	public function optimize_thumb($thumb_src, $slider_id){
		if (!$this->is_enabled() || !$this->verify_webp()) {
			return $thumb_src;
		}

		$slider = new RevSliderSlider();
		$slider->init_by_id( $slider_id );
		if ( $slider->inited === false ) {
			$this->log( 'optimize_thumb: Module could not be loaded');
			return $thumb_src;
		}
		
		$thumb_url = '';
		if ( is_string( $thumb_src ) ) {
			$thumb_url = $thumb_src;
		} elseif ( is_array( $thumb_src ) && isset( $thumb_src['type'] ) && 'image' == $thumb_src['type'] && !empty( $thumb_src['image']['src'] ) ) {
			$thumb_url = $thumb_src['image']['src'];
		}

		if ( empty( $thumb_url ) ) {
			$this->log('optimize_thumb: empty thumb url');
			return $thumb_src;
		}

		$o_result = $this->optimize_webp(
			$thumb_url, 
			$slider_id, 
			[
				'w' => 298,
				'h' => 150,
			],
			$this->get_dest_thumb_file($thumb_url, $slider->alias, '_thumb')
		);
		if ( empty( $o_result['url'] ) ) {
			$this->log('optimize_thumb: optimize_webp return empty url');
			return $thumb_src;
		}
		
		$data = [ 'params' => [ 'thumb' => $o_result['url'] ] ];
		$slider->save_slider_advanced($slider_id, $data);
		
		return $o_result['url'];
	}

	/**
	 * @param array $i
	 * @param int   $slider_id
	 * @return bool|array
	 */
	public function optimize_single_image($i, $slider_id){
		if (empty($i['src'])) return false;
		
		if (!filter_var($i['src'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
			// url might be missing scheme
			if (!filter_var($this->basescheme . ':' . $i['src'], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)){
				$this->log('optimize_single_image: non-valid URL: ' . $i['src']);
				return false;
			}
			$i['src'] = $this->basescheme . ':' . $i['src'];
		}

		$src_file = $this->basedir . str_replace($this->baseurl, '', $i['src']);
		$src_ext  = strtolower(pathinfo($src_file, PATHINFO_EXTENSION));
		if (!in_array($src_ext, $this->allowed_formats)) {
			$this->log('optimize_single_image: skip unsupported format: ' . $src_file);
			return false;
		}

		// calc dimensions and generate image
		$i['r'] = empty($i['r']) || !is_array($i['r']) ? [] : $i['r'];
		$dim = $this->calculate_dimensions($src_file, $i['r'], $src_ext);
		if (empty($dim)) {
			$this->log('optimize_single_image: skip ' . str_replace($this->basedir, '', $src_file));
			return false;
		}

		return $this->save(
			$src_file,
			!empty($i['dest_file']) ? $i['dest_file'] : $this->get_dest_file($src_file, $slider_id), 
			$dim
		);
	}

	/**
	 * @param array $data
	 * @param int   $slider_id
	 * @return void
	 */
	protected function do_optimize_images($images, $slider_id){
		if (empty($images)) {
			$this->log('do_optimize_images: empty images');
			return;
		}

		$result = [];
		$keep = [];

		foreach ($images as $i){
			$single = $this->optimize_single_image($i, $slider_id);
			if (empty($single)) continue;
			
			$result[ $i['src'] ] = $single;
			
			// add to keep list for cleanup routine
			if (!empty($single['path'])) {
				$keep[ basename($single['path']) ] = true;
			}
		}

		// Clean up old images.
		// Keep files with _<suffix>
		$dest_dir = trailingslashit( $this->basedir . $this->dest_path . $slider_id );
		if ( is_dir( $dest_dir ) ) {
			$files = array_diff( scandir( $dest_dir ), [ '..', '.', 'index.php' ] );
			foreach ( $files as $f ) {
				if ( isset( $keep[ $f ] ) || strpos( $f, '_' ) ) {
					continue;
				}
				@unlink( $dest_dir . $f );
			}
		}

		// for debug, remove later
		//$this->log('do_optimize_images');
		//$this->log( print_r( $result, 1 ) );
	}

	/**
	 * revslider_slider_imported hook: optimize every image of a freshly imported slider
	 * @param int $slider_id
	 * @return void
	 */
	public function optimize_slider_imported($slider_id) {
		try {
			$new_slider = new RevSliderSlider();
			$new_slider->init_by_id($slider_id);
		}catch(Exception $e){
			$this->log('optimize_slider_imported: ' . $e->getMessage());
			return;
		}

		if (!empty($new_slider->params['imgs'])) {
			$this->do_optimize_images($new_slider->params['imgs'], $slider_id);
		}
		if (!empty($settings['thumb'])) {
			$this->optimize_thumb($settings['thumb'], $slider_id);
		}
	}

	/**
	 * @param array $data
	 * @param int   $slider_id
	 * @return void
	 */
	public function optimize_images($data, $slider_id){
		$slider_id = (int)$slider_id;
		if ($slider_id < 1) return;

		$settings = $this->json_decode_slashes($this->get_val($data, 'settings'));
		if (!empty($settings['imgs'])) {
			$this->do_optimize_images($settings['imgs'], $slider_id);
		}
		if (!empty($settings['thumb'])) {
			$this->optimize_thumb($settings['thumb'], $slider_id);
		}
	}
	
	/**
	 * @param array $data
	 * @param int   $slider_id
	 * @return void
	 */
	public function optimize_images_advanced($data, $slider_id){
		$slider_id = (int)$slider_id;
		if ($slider_id < 1) return;

		if (!empty($data['params']['thumb'])) {
			$this->optimize_thumb($data['params']['thumb'], $slider_id);
		}
	}

	/**
	 * @param array $images
	 * @param RevSlider7Output $output
	 * @return array
	 */
	public function add_images($images, $output){
		if (empty($images)) {
			$this->log('add_images: empty images');
			return $images;
		}

		$slider_id = (int)$output->slider_id;
		if ($slider_id < 1) return $images;

		$dest_dir = trailingslashit($this->basedir . $this->dest_path . $slider_id);
		if ( $this->is_on_the_fly() && !is_dir($dest_dir)) {
			$this->do_optimize_images($images, $slider_id);
		}
		if (!is_dir($dest_dir) ) {
			return $images;
		}

		// List of existing files in the optimized folder
		// [hand] => hand.webp
		$optimized_files = [];
		$files = array_diff(scandir($dest_dir), ['..', '.', 'index.php']);
		if (empty($files) ) return $images;
		foreach ($files as $f) {
			$optimized_files[ pathinfo($f, PATHINFO_FILENAME) ] = $f;
		}

		$ourl = $this->remove_http(trailingslashit($this->baseurl . $this->dest_path . $slider_id));
		foreach ($images as $k => $i){
			$stripped = str_replace($this->baseurl, '', $i['src']);
			$pi = pathinfo($stripped);
			$hash = $this->get_filename_hash($stripped);
			if (
				!empty($pi['extension'])
				&& in_array(strtolower($pi['extension']), $this->allowed_formats)
				&& isset($optimized_files[$hash])
			) {
				$images[$k]['src'] = $ourl . $optimized_files[$hash];
			}
		}

		// for debug, remove later
		//$this->log('add_images');
		//$this->log( print_r( $images, 1 ) );

		return $images;
	}

	/**
	 * Convert imported media to webp format
	 * Use with 'revslider_import_media_insert_attachment_before' filter
	 * 
	 * @param array $paths
	 * @return array
	 */
	public function convert_import_media_webp($paths){
		// to be updated
		// "filename":"xxx.png",
		// "relative":"revslider/xxx/xxx.png",
		// "absolute":"/xxx/revslider/xxx/xxx.png"

		if (!$this->is_enabled() || !$this->verify_webp()) {
			return $paths;
		}
		if (empty($paths['filename']) || empty($paths['absolute'])) {
			return $paths;
		}

		$fn_info = pathinfo($paths['filename']);
		if ('webp' === $fn_info['extension']){
			return $paths;
		}

		$dim = $this->getimagesize($paths['absolute']);
		if (empty($dim) || $dim['w'] <= 0 || $dim['h'] <= 0) {
			return $paths;
		}

		$result = $this->save(
			$paths['absolute'],
			dirname($paths['absolute']) . '/' . $fn_info['filename'] . '.webp',
			$dim
		);
		if (!$result) {
			return $paths;
		}

		$paths['filename'] = $fn_info['filename'] . '.webp';
		$paths['relative'] = dirname($paths['relative']) . '/' . $paths['filename'];
		$paths['absolute'] = dirname($paths['absolute']) . '/' . $paths['filename'];

		return $paths;
	}

	/**
	 * triggered by delete slider hook
	 * clean up the slider optimized images
	 *
	 * @param  int $slider_id  Slider id
	 * @return void
	 */
	public function on_delete_slider($slider_id){
		$slider_id = (int) $slider_id;
		if ($slider_id < 1) return;
		
		$dest_dir = trailingslashit($this->basedir . $this->dest_path . $slider_id);
		if (is_dir($dest_dir)) {
			global $wp_filesystem;
			WP_Filesystem();
			$wp_filesystem->delete($dest_dir, true);
		}
	}

}
