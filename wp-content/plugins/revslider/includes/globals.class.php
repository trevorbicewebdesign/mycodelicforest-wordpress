<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Singleton service locator. RevSliderGlobals::instance()->get('RevSliderFunctions') returns a shared
 * instance of that class, creating it on first access - so the plugin's helper objects exist once per
 * request instead of being re-instantiated everywhere. add() can also park arbitrary values under a key.
 */
class RevSliderGlobals {
	const SLIDER_REVISION = RS_REVISION;

	/**
	 * Second set of names for table constants that RevSliderFront already defines. Nothing inside the plugin
	 * reads any of them - they are kept only because they are public and a theme or addon may still reference
	 * them. Use the RevSliderFront constants instead; a class constant cannot raise a deprecation notice at
	 * runtime, so this docblock is the only signal available.
	 *
	 * @deprecated 7.0.0 use RevSliderFront::TABLE_* instead
	 */
	const TABLE_SLIDERS_NAME = RevSliderFront::TABLE_SLIDER;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_SLIDES instead */
	const TABLE_SLIDES_NAME = RevSliderFront::TABLE_SLIDES;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_STATIC_SLIDES instead */
	const TABLE_STATIC_SLIDES_NAME = RevSliderFront::TABLE_STATIC_SLIDES;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_SETTINGS instead */
	const TABLE_SETTINGS_NAME = RevSliderFront::TABLE_SETTINGS;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_LAYER_ANIMATIONS instead */
	const TABLE_LAYER_ANIMS_NAME = RevSliderFront::TABLE_LAYER_ANIMATIONS;
	/** @deprecated 7.0.0 use RevSliderFront::TABLE_NAVIGATIONS instead */
	const TABLE_NAVIGATION_NAME = RevSliderFront::TABLE_NAVIGATIONS;
	/** @var string prefixed name of the legacy v6 slider table (set in the constructor) */
	public static $table_sliders;
	/** @var string prefixed name of the legacy v6 slides table */
	public static $table_slides;
	/** @var string prefixed name of the legacy v6 static slides table */
	public static $table_static_slides;

	/**
	 * Stores the singleton instance of the class
	 * @var RevSliderGlobals
	 */
	private static $instance;

	/**
	 * store global objects
	 * @var array
	 */
	private $storage = [];

	protected function __construct(){
		global $wpdb;

		self::$table_sliders = $wpdb->prefix.'revslider_sliders';
		self::$table_slides = $wpdb->prefix.'revslider_slides';
		self::$table_static_slides = $wpdb->prefix.'revslider_static_slides';
	}

	/**
	 * Instance accessor. If instance doesn't exist, we'll initialize the class.
	 *
	 * @return RevSliderGlobals
	 */
	public static function instance(){
		if(!isset(self::$instance))	self::$instance = new RevSliderGlobals();
		return self::$instance;
	}

	/**
	 * store $object under $key in $storage
	 * @param string $key
	 * @param mixed  $object
	 * @return void
	 */
	public function add($key, $object){
		$this->storage[$key] = $object;
	}

	/**
	 * get object from storage, creating it on first access
	 *
	 * @param string $key class name, or an arbitrary key that was put in with add()
	 * @return mixed|null
	 */
	public function get($key){
		if(!is_string($key) || $key === '') return NULL;
		if(array_key_exists($key, $this->storage)) return $this->storage[$key];

		if(!class_exists($key)) return NULL; //deliberately NOT cached - see below

		//only instantiate what can actually be built without arguments. "new $key" on a class with a required
		//constructor argument is a fatal ArgumentCountError, which would take the whole page down instead of
		//degrading to a null the callers already handle.
		try{
			$reflection = new ReflectionClass($key);
			if(!$reflection->isInstantiable()) return NULL;

			$constructor = $reflection->getConstructor();
			if($constructor !== null && $constructor->getNumberOfRequiredParameters() > 0) return NULL;
		}catch(ReflectionException $e){
			return NULL;
		}

		$this->add($key, new $key);

		return $this->storage[$key];
	}

	/**
	 * @return array  list of revslider DB tables
	 */
	public function get_rs_tables(){
		global $wpdb;

		//memoize per table-prefix (constant within a request; keyed so multisite switch_to_blog stays correct)
		static $table_cache = [];
		$prefix = $wpdb->prefix;
		if(!isset($table_cache[$prefix])){
			$table_cache[$prefix] = [
				$prefix . RevSliderFront::TABLE_SLIDER,
				$prefix . RevSliderFront::TABLE_SLIDES,
				$prefix . RevSliderFront::TABLE_STATIC_SLIDES,
				$prefix . RevSliderFront::TABLE_LAYER_ANIMATIONS,
				$prefix . RevSliderFront::TABLE_NAVIGATIONS,
				$prefix . RevSliderFront::TABLE_SETTINGS,
			];
		}

		return $table_cache[$prefix];
	}
	
}
