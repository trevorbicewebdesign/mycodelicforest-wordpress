<?php
/**
 * Page builder integrations — shared registry.
 *
 * A builder integration is the code that lets a page builder place a Slider Revolution module: pick one,
 * configure it, preview it. Seven of them ship with the plugin and each used to arrange its own assets,
 * repeat the same localization and re-declare the same options. This class is the half they share; what
 * stays with each integration is only what is genuinely its own (see SR7.Builders in
 * admin/assets/js/tools/shortcode.js for the JavaScript half).
 *
 * Contract (see docs/builder-harmonization/CONTRACT.md, B5):
 *   PHP : RevSliderBuilders::register($slug, $args)
 *   JS  : SR7.Builders.register(slug, adapter)
 *
 * Modelled on RevSliderPageEffects::register_type(): declarative config, hooks booted once on the first
 * registration, nothing loaded for a builder that is not present.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderBuilders {

	/**
	 * The version of this API. What is documented on RevSliderBuilders and SR7.Block is stable within a
	 * major version; what is not documented is not part of it, whatever its visibility.
	 */
	const API_VERSION = 1;

	/** @var array<string,array> registered builders */
	private static $builders = [];
	private static $booted = false;

	/**
	 * Register a builder integration. Called on plugins_loaded, before 'init'.
	 *
	 * @param string $slug  e.g. 'elementor'
	 * @param array  $args  title, detect, screens, editor, style, caps, boot, surfaces
	 * @return bool
	 */
	public static function register($slug, $args = []){
		$slug = sanitize_key($slug);
		if($slug === '') return false;

		self::$builders[$slug] = array_merge([
			'slug'		=> $slug,
			'title'		=> ucfirst($slug),
			// callable():bool - is this builder present at all. Without one the builder never loads.
			'detect'	=> null,
			// callable():bool - is the current screen one this builder edits on. Defaults to any admin screen
			// the shortcode wizard already answers for.
			'screens'	=> null,
			'editor'	=> '',		// plugin relative path to the integration's editor script
			'style'		=> '',		// plugin relative path to its editor stylesheet
			// what the builder can do natively, so core does not offer what it cannot honour. 'live-preview'
			// is the only one so far: it means the builder renders the page as it will look (CONTRACT B7).
			'caps'		=> [],
			// surfaces of the same builder we do NOT support - see the surface rule in CONTRACT D4. The JS
			// half decides what to draw; this is here so the decision is declared in one place.
			'unsupported'	=> [],
			'boot'		=> null		// callable() run once when the builder is present, for its own hooks
		], $args);

		self::boot();

		/**
		 * A builder integration has registered.
		 *
		 * The moment to hang anything of your own off a builder you do not own - its assets, its screens,
		 * an extra capability. Fires on plugins_loaded, before 'init', whether or not the builder turns out
		 * to be present on this screen.
		 *
		 * @param string $slug    e.g. 'elementor'
		 * @param array  $builder the registration, with the defaults filled in
		 */
		do_action('revslider_builder_registered', $slug, self::$builders[$slug]);

		return true;
	}

	/** @return array<string,array> every registered builder */
	public static function get_builders(){
		return self::$builders;
	}

	/**
	 * The builders actually present on this site.
	 * @return array<string,array>
	 */
	public static function active(){
		$active = [];
		foreach(self::$builders as $slug => $builder){
			if(self::is_present($builder)) $active[$slug] = $builder;
		}

		return $active;
	}

	/**
	 * Does a builder declare a capability.
	 * @return bool
	 */
	public static function supports($slug, $cap){
		$slug = sanitize_key($slug);

		return isset(self::$builders[$slug]) && in_array($cap, (array)self::$builders[$slug]['caps'], true);
	}

	/**
	 * register the shared hooks once, on the first register() call
	 * @return void
	 */
	private static function boot(){
		if(self::$booted) return;
		self::$booted = true;

		//An integration that registers on plugins_loaded at 20 or later - the priority the documentation's
		//own example uses - would otherwise never be booted: adding a callback to the priority currently
		//running is not something to rely on. 'init' at 0 still comes before everything a builder needs to
		//be registered for, so nothing is lost by falling back to it.
		if(did_action('plugins_loaded')){
			add_action('init', [self::class, 'boot_builders'], 0);
		}else{
			add_action('plugins_loaded', [self::class, 'boot_builders'], 20);
		}

		add_action('admin_enqueue_scripts', [self::class, 'enqueue'], 20);
	}

	/**
	 * Let each present builder wire up whatever is genuinely its own.
	 *
	 * Each builder is booted once, however often this runs - a builder registered after the first pass
	 * still gets its turn, and one that has already had it does not get a second.
	 *
	 * @return void
	 */
	public static function boot_builders(){
		static $booted = [];

		foreach(self::active() as $slug => $builder){
			if(isset($booted[$slug])) continue;
			$booted[$slug] = true;

			if(is_callable($builder['boot'])) call_user_func($builder['boot'], $builder);
		}
	}

	/** @return bool */
	private static function is_present($builder){
		return is_callable($builder['detect']) ? (bool)call_user_func($builder['detect']) : false;
	}

	/** @return bool */
	private static function is_on_screen($builder){
		if(is_callable($builder['screens'])) return (bool)call_user_func($builder['screens']);

		// the screens the shortcode wizard already loads on: editing a post, or a screen that embeds one
		global $pagenow, $typenow;
		if(in_array($pagenow, ['post-new.php', 'site-editor.php', 'widgets.php'], true)) return true;
		if($pagenow === 'post.php') return true;

		unset($typenow);

		return false;
	}

	/**
	 * The assets every integration needs before its own script can do anything: the SR7 front runtime, the
	 * tools bundle, the block model, and the handful of values the JavaScript reads out of SR7.E.
	 *
	 * Idempotent - WordPress dedupes by handle, so a builder may call this itself if it loads on a screen
	 * the registry does not know about.
	 *
	 * @return void
	 */
	public static function enqueue_shared(){
		static $done = false;
		if($done) return;
		$done = true;

		if(!self::user_may_edit()) return;

		wp_enqueue_script('tpgsap', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tpgsap.js', [], RevSliderFunctions::asset_time('public/js/libs/tpgsap.js'), ['strategy' => 'async']);
		wp_enqueue_script('_tpt', RS_PLUGIN_URL_CLEAN . 'public/js/libs/tptools.js', [], RevSliderFunctions::asset_time('public/js/libs/tptools.js'), ['strategy' => 'async']);
		wp_enqueue_script('revbuilder-backend', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/tools.js', [], RevSliderFunctions::asset_time('admin/assets/js/tools/tools.js'), false);
		wp_enqueue_script('sr7-tools-shortcode', RS_PLUGIN_URL_CLEAN . 'admin/assets/js/tools/shortcode.js', ['revbuilder-backend'], RevSliderFunctions::asset_time('admin/assets/js/tools/shortcode.js'), true);

		// what the JavaScript reads: where to call, where the plugin lives, what it may do. Every integration
		// localized some subset of this by hand, and one of them under a different handle each time.
		wp_localize_script('sr7-tools-shortcode', 'SR7ShortcodeData', self::client_config());
	}

	/**
	 * The values the JavaScript half needs. Kept in one place so an integration cannot ship a subset of them
	 * and then wonder why the picker cannot reach the server.
	 *
	 * @return array
	 */
	public static function client_config(){
		$uploads = wp_upload_dir();

		return [
			'ajaxurl'		=> admin_url('admin-ajax.php'),
			'plugin_url'	=> RS_PLUGIN_URL_CLEAN,
			'block_nonce'	=> wp_create_nonce('revslider_actions'),
			'wp_upload_url'	=> rtrim($uploads['baseurl'] ?? '', '/') . '/',
			'builders'		=> array_map(static function($builder){
				return [
					'slug'			=> $builder['slug'],
					'title'			=> $builder['title'],
					'caps'			=> array_values((array)$builder['caps']),
					'unsupported'	=> array_values((array)$builder['unsupported'])
				];
			}, self::active())
		];
	}

	/**
	 * Load the shared assets plus each present builder's own, on the screens it edits on.
	 * @return void
	 */
	public static function enqueue(){
		if(!self::user_may_edit()) return;

		foreach(self::active() as $slug => $builder){
			if(!self::is_on_screen($builder)) continue;

			self::enqueue_shared();

			if($builder['style'] !== ''){
				wp_enqueue_style('sr7-builder-' . $slug, RS_PLUGIN_URL_CLEAN . $builder['style'], [], RevSliderFunctions::asset_time($builder['style']));
			}
			if($builder['editor'] !== ''){
				wp_enqueue_script('sr7-builder-' . $slug, RS_PLUGIN_URL_CLEAN . $builder['editor'], ['sr7-tools-shortcode'], RevSliderFunctions::asset_time($builder['editor']), true);
			}
		}
	}

	/**
	 * "Slider with 5 Slides" - composed once, from parts translated once.
	 *
	 * The card in every builder writes the same sentence in JavaScript (SR7.Block.typeLabel). Both have to
	 * read the same, or a dropdown and the card it fills disagree about the module they describe.
	 *
	 * @param string $type   module type as stored, e.g. 'carousel'
	 * @param int    $slides number of slides
	 *
	 * @return string
	 */
	public static function type_label($type, $slides){
		$kind	= ($type === '' || $type === null) ? __('Slider', 'revslider') : ucfirst((string)$type);
		$slides	= (int)$slides;
		$noun	= ($slides === 1) ? __('Slide', 'revslider') : __('Slides', 'revslider');

		return $kind . ' ' . __('with', 'revslider') . ' ' . $slides . ' ' . $noun;
	}

	/**
	 * Every module on the site, as plain arrays.
	 *
	 * This is the call themes and builders actually want, and the one they have been reaching into
	 * RevSliderSlider for. Nothing but arrays crosses this boundary, which is what lets the classes behind
	 * it keep changing (CONTRACT B8.1).
	 *
	 *   RevSliderBuilders::modules(['include' => ['slides'], 'filter' => 'carousel']);
	 *   // [ ['id'=>12,'alias'=>'hero','title'=>'Hero','type'=>'carousel','premium'=>false,
	 *   //    'cover'=>['image'=>'...','color'=>'...'], 'slides'=>5, 'label'=>'Carousel with 5 Slides'], ... ]
	 *
	 * Cost: one query for the list. Asking for 'slides' adds one more for the whole list at once - never one
	 * per module, which is the trap the older listing helpers fall into.
	 *
	 * @param array $args include (slides), filter (all or a module type), search, orderby (title|id)
	 *
	 * @return array the modules, or nothing at all for a user who may not edit
	 */
	public static function modules($args = []){
		if(!self::user_may_edit()) return [];

		$args = array_merge([
			'include'	=> [],
			'filter'	=> 'all',
			'search'	=> '',
			'orderby'	=> 'title'
		], is_array($args) ? $args : []);

		global $wpdb;

		//One read, and no slider object built out of any of it. Everything the default shape needs - the
		//type, whether it is premium, its cover - is in this row's params. (Not in `settings`, which holds
		//the version and little else; that is worth knowing before trying to make this cheaper.)
		$rows = $wpdb->get_results(
			'SELECT id, title, alias, params FROM ' . $wpdb->prefix . RevSliderFront::TABLE_SLIDER . " WHERE `type` != 'folder'",
			ARRAY_A
		);

		$include		= (array)$args['include'];
		$want_slides	= in_array('slides', $include, true);
		$counts			= $want_slides ? self::slide_counts() : [];
		$search			= trim((string)$args['search']);
		$modules		= [];

		foreach((array)$rows as $row){
			$params	= json_decode((string)($row['params'] ?? ''), true);
			$params	= is_array($params) ? $params : [];
			$type	= isset($params['type']) ? (string)$params['type'] : '';

			if($args['filter'] !== 'all' && $type !== $args['filter']) continue;

			$title	= (string)($row['title'] ?? '');
			$alias	= (string)($row['alias'] ?? '');

			if($search !== '' && stripos($title . ' ' . $alias, $search) === false) continue;

			$id		= (int)($row['id'] ?? 0);
			$module	= [
				'id'		=> $id,
				'alias'		=> $alias,
				'title'		=> $title,
				'type'		=> $type,
				'premium'	=> !empty($params['prem']),
				'cover'		=> [
					'image'	=> isset($params['thumb']) ? (string)$params['thumb'] : '',
					'color'	=> isset($params['bg']['color']['string']) ? (string)$params['bg']['color']['string'] : ''
				]
			];

			if($want_slides){
				$module['slides']	= isset($counts[$id]) ? (int)$counts[$id] : 0;
				$module['label']	= self::type_label($type, $module['slides']);
			}

			$modules[] = $module;
		}

		$by_id = ($args['orderby'] === 'id');
		usort($modules, static function($a, $b) use ($by_id){
			return $by_id ? ($a['id'] <=> $b['id']) : strcasecmp($a['title'], $b['title']);
		});

		/**
		 * The module list handed to a builder or theme integration.
		 *
		 * @param array $modules plain arrays, never slider objects
		 * @param array $args    what the caller asked for
		 */
		return apply_filters('revslider_builder_modules', $modules, $args);
	}

	/**
	 * One module by alias or id, in the shape modules() returns.
	 *
	 * @param string|int $which alias or id
	 *
	 * @return array|false
	 */
	public static function module($which){
		foreach(self::modules(['include' => ['slides']]) as $module){
			if((string)$module['alias'] === (string)$which) return $module;
			if((string)$module['id'] === (string)$which) return $module;
		}

		return false;
	}

	/**
	 * How many slides each module has, for the whole list in one query.
	 *
	 * @return array id => count
	 */
	private static function slide_counts(){
		global $wpdb;

		$rows = $wpdb->get_results(
			'SELECT slider_id, COUNT(*) AS slides FROM ' . $wpdb->prefix . RevSliderFront::TABLE_SLIDES . ' WHERE static != 1 GROUP BY slider_id',
			ARRAY_A
		);

		$counts = [];
		foreach((array)$rows as $row){
			$counts[(int)$row['slider_id']] = (int)$row['slides'];
		}

		return $counts;
	}

	/**
	 * A ready-made module picker, for an integration that only wants a select box.
	 *
	 * A thin wrapper over modules() - one data path - and the escaping happens here so an integrator cannot
	 * get it wrong. Anyone wanting their own markup calls modules() instead.
	 *
	 *   echo RevSliderBuilders::dropdown(['name' => 'my_slider', 'selected' => $current, 'show' => ['type']]);
	 *
	 * @param array $args name, value (alias|id), selected, placeholder, show (type), attrs, echo, and
	 *                    anything modules() accepts
	 *
	 * @return string
	 */
	public static function dropdown($args = []){
		$args = array_merge([
			'name'			=> 'revslider_module',
			'value'			=> 'alias',
			'selected'		=> '',
			'placeholder'	=> __('Select a Module...', 'revslider'),
			'show'			=> [],
			'attrs'			=> [],
			'echo'			=> false
		], is_array($args) ? $args : []);

		$show		= (array)$args['show'];
		$with_type	= in_array('type', $show, true);
		$listing	= self::modules(array_merge($args, ['include' => $with_type ? ['slides'] : []]));

		$attributes = ' name="' . esc_attr($args['name']) . '"';
		foreach((array)$args['attrs'] as $attribute => $value){
			$attributes .= ' ' . esc_attr($attribute) . '="' . esc_attr($value) . '"';
		}

		$html  = '<select' . $attributes . '>';
		$html .= '<option value="">' . esc_html($args['placeholder']) . '</option>';

		foreach($listing as $module){
			$value	= ($args['value'] === 'id') ? (string)$module['id'] : $module['alias'];
			$label	= $module['title'];

			if($with_type && isset($module['label'])) $label .= ' - ' . $module['label'];

			$html .= '<option value="' . esc_attr($value) . '"' . selected($args['selected'], $value, false) . '>'
				. esc_html($label) . '</option>';
		}

		$html .= '</select>';

		if(!empty($args['echo'])){
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- every part escaped above
			return '';
		}

		return $html;
	}

	/**
	 * An embedded module, as its shortcode.
	 *
	 * The PHP twin of SR7.Block.toShortcode, and the reason it exists: an integration whose host gives it
	 * no place to run our JavaScript - Cornerstone's inspector is one - can still only reach the grammar by
	 * composing the string by hand. Five places in this plugin were already doing exactly that.
	 *
	 *   RevSliderBuilders::shortcode(['alias' => 'hero', 'usage' => 'modal']);
	 *   // [sr7 alias="hero" usage="modal"][/sr7]
	 *
	 * Takes the published grammar - the twelve attributes, flat - not the JavaScript model's nesting. Empty
	 * values are dropped rather than written out empty, and anything not in the grammar is ignored.
	 *
	 * @param array $atts alias, class, fullheight, fullwidth, modal, offset, order, settings, skin, usage,
	 *                    wrapperid, zindex
	 *
	 * @return string '' without an alias, since a shortcode without one renders nothing
	 */
	public static function shortcode($atts = []){
		$atts	= is_array($atts) ? $atts : [];
		$alias	= isset($atts['alias']) ? trim((string)$atts['alias']) : '';

		if($alias === '') return '';

		//The JavaScript twin drops the premium attributes on an unregistered site rather than writing them
		//out to be ignored later. Same here, or the same module reads differently depending on which half of
		//the API wrote it.
		$premium	= self::is_registered();
		$modal		= isset($atts['usage']) && $atts['usage'] === 'modal';
		$order		= $modal
			? ['alias', 'usage', 'modal', 'order', 'settings', 'skin']
			: ['alias', 'zindex', 'wrapperid', 'class', 'fullwidth', 'fullheight', 'offset', 'order', 'settings', 'skin'];
		$gated		= ['zindex', 'wrapperid', 'class', 'fullwidth', 'fullheight', 'offset'];
		$parts		= [];

		foreach($order as $key){
			if(!isset($atts[$key])) continue;
			if(!$premium && in_array($key, $gated, true)) continue;

			$value = is_bool($atts[$key]) ? ($atts[$key] ? 'true' : '') : trim((string)$atts[$key]);
			if($value === '') continue;

			$parts[] = $key . '="' . esc_attr($value) . '"';
		}

		return '[sr7 ' . implode(' ', $parts) . '][/sr7]';
	}

	/**
	 * A shortcode back to its attributes - the twin of SR7.Block.parseShortcode.
	 *
	 * Reads [sr7] and [rev_slider] alike, and the encodings a builder may have stored it under: Divi keeps
	 * the brackets as entities in its builder data and as percent escapes in saved content, so a module
	 * placed there is unreadable to a plain parse.
	 *
	 * @param string $shortcode
	 *
	 * @return array the attributes found, [] if there is no module shortcode in the string
	 */
	public static function parse_shortcode($shortcode){
		$shortcode = (string)$shortcode;

		$shortcode = str_replace(['&#91;', '&#093;', '&#93;', '&quot;', '&#39;', '&apos;', '&amp;'], ['[', ']', ']', '"', "'", "'", '&'], $shortcode);
		if(strpos($shortcode, '%91') !== false || strpos($shortcode, '%93') !== false){
			$shortcode = str_replace(['%91', '%93', '%22'], ['[', ']', '"'], $shortcode);
		}

		//The name must end where the attributes begin, or [sr7_something_else] is read as one of ours.
		if(!preg_match('/\[(?:sr7|rev_slider)(\s[^\]]*)?\]/i', $shortcode, $found)) return [];

		$atts = shortcode_parse_atts(isset($found[1]) ? $found[1] : '');

		return is_array($atts) ? $atts : [];
	}

	/**
	 * Is this install registered. Premium attributes are not written out without it.
	 * @return bool
	 */
	public static function is_registered(){
		return class_exists('RevSliderShortcodeWizard') ? (RevSliderShortcodeWizard::is_registered() === true) : false;
	}
	/**
	 * Every integration checked this before touching anything, in the same words.
	 * @return bool
	 */
	public static function user_may_edit(){
		return current_user_can('edit_posts') || current_user_can('edit_pages');
	}
}
