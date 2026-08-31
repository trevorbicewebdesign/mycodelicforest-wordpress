<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderGutenberg {
	
	private $prefix;
	
	public function __construct($pre){
		global $wp_version;
		$this->prefix = $pre;

		// Register Block Type
		add_action('init', function(){
			register_block_type( RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/gutenberg/build' );
		});
		
		// add ThemePunch block category
		if(version_compare($wp_version, '5.8', '>=')){
			add_filter('block_categories_all', [$this, 'create_block_category'], 10, 2);
		}else{ //block_categories is deprecated since 5.8
			add_filter('block_categories', [$this, 'create_block_category'], 10, 2);
		}
		
		// Hook: Editor assets — force-load block CSS into parent + iframe (belt and suspenders;
		// some Site Editor / WP versions skip block.json editorStyle registration).
		add_action('enqueue_block_editor_assets', function() {
			$css = RS_PLUGIN_PATH . 'admin/includes/shortcode_generator/gutenberg/build/index.css';
			if(!file_exists($css)) return;
			wp_enqueue_style(
				'revslider-gutenberg-block-editor',
				RS_PLUGIN_URL . 'admin/includes/shortcode_generator/gutenberg/build/index.css',
				[],
				filemtime($css)
			);
		});
	}
	
	/**
	 * Check Array for Value Recursive
	 * @return bool recursive in_array()
	 */
	private function in_array_r($needle, $haystack, $strict = false){
		if(is_array($haystack) && !empty($haystack)){
			foreach($haystack as $item){
				if(($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))){
					return true;
				}
			}
		}
	
		return false;
	}
	
	/**
	 * Add ThemePunch Gutenberg Block Category
	 * @return array makes sure the "ThemePunch" block category exists
	 */
	public function create_block_category($categories, $post){
		if($this->in_array_r('themepunch', $categories)){
			return $categories;
		}

		return array_merge($categories, [['slug' => 'themepunch', 'title' => __('ThemePunch', 'revslider')]]);
	}

}