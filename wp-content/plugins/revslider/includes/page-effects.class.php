<?php
/**
 * Page Effects framework — shared base for "animate existing page content" addons.
 *
 * A Page Effect is a Gutenberg block placed on a page that, on the front end, augments/animates
 * existing content (a scroll-drawn chalk line, a gallery turned into a filmstrip, …) WITHOUT a full
 * Slider Revolution module. This class is the GLOBAL half (the ADDON half lives in each effect addon):
 * it owns block registration, the per-page meta save/load, and front emission; addons register a
 * "type" that supplies the editor tool + the front runtime.
 *
 * Contract (see harmonization/PAGE-EFFECTS.md):
 *   PHP    : RevSliderPageEffects::register_type($type, $args)
 *   Editor : SR7.PE.registerType($type, {card, tool})        // admin/assets/js/page-effects.js
 *   Front  : SR7.PE.registerRuntime($type, {mount, unmount}) // public/js/page-effects.js
 *
 * Data: each effect instance is keyed by a per-block effectId and stored in the host POST's meta
 * (page-scoped, travels with the post) as { type, data }. Front emission scans the queried page's
 * blocks (works on ALL templates, incl. SR full-page templates that never render post_content).
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Framework for "Page Effects" - Gutenberg blocks whose visual effect is rendered by SR7 on the front end.
 *
 * Addons call register_type() to add an effect. Each instance keeps its data in the host post's meta
 * (META_PREFIX + effectId); the block itself renders nothing, front_enqueue() collects the effect ids from
 * the page's blocks and emits them as SR7.E.pageEffects for the runtime to pick up. Unsaved editor state
 * goes through preview_effect() into a short-lived transient instead.
 */
class RevSliderPageEffects {

	const META_PREFIX	= '_sr7_pe_';			// + effectId  → json { type, data }
	const PREVIEW_PREFIX	= 'sr7_pe_prev_';		// + userId_effectId → transient draft for Preview Live
	const SHELL_HANDLE	= 'sr7-page-effects';	// editor shell + registry + generic block edit
	const RT_HANDLE		= 'sr7-page-effects-rt';// front runtime bootstrap + registry

	/** @var array<string,array> registered effect types */
	private static $types = [];
	private static $booted = false;

	/**
	 * Register an effect type. Called by each addon (on plugins_loaded, before 'init').
	 *
	 * @param string $type   slug, e.g. 'chalkline'
	 * @param array  $args   block, title, icon, description, runtime (front JS url),
	 *                       editor (editor-tool JS url), sanitize (callable($data):array)
	 * @return bool
	 */
	public static function register_type($type, $args){
		$type = sanitize_key($type);
		if($type === '' || empty($args['block'])) return false;

		self::$types[$type] = array_merge([
			'type'			=> $type,
			'block'			=> '',
			'title'			=> ucfirst($type),
			'icon'			=> 'edit',
			'description'	=> '',
			'preview'		=> '',	// block-inserter hover-preview image url (falls back to the core SR7 placeholder)
			'runtime'		=> '',	// front runtime script url (registers SR7.PE.runtimes[type])
			'editor'		=> '',	// editor tool script url (registers SR7.PE.types[type])
			'version'		=> '',	// optional cache-bust ver for runtime/editor (falls back to RS_REVISION)
			'sanitize'		=> null,	// callable to whitelist this type's data on save
			'summary'		=> null,	// callable($data):string → the block card's short label (else generic)
			// Inspector-style effects (e.g. Filmstrip) configure in the block inspector instead of the
			// on-page Recorder, own their block edit() (custom attributes + InspectorControls) and emit
			// their own front markup. Recorder-style effects (e.g. ChalkLine) leave these at the defaults.
			'self_block'	=> false,	// true: the type's editor JS registers the block client-side (skip the generic card)
			'attributes'	=> [],		// extra block attributes (merged with effectId/summary) for the server registration
			'supports'		=> [],		// block supports (e.g. ['align' => ['wide','full']]) — registered server-side and
										// bootstrapped to the editor automatically; NEVER also pass these to a generic client
										// registerBlockType (double-declaring supports crashes block processing on WP 7.0)
			'render'		=> null		// front render_callback (defaults to empty: emission handled by front_enqueue)
		], $args);

		self::boot();
		return true;
	}

	/** @return array<string,array> every registered effect type */
	public static function get_types(){ return self::$types; }

	/**
	 * register the framework's hooks once, on the first register_type() call
	 * @return void
	 */
	private static function boot(){
		if(self::$booted) return;
		self::$booted = true;

		add_action('init', [self::class, 'register_blocks']);
		add_filter('block_categories_all', [self::class, 'category']);
		add_action('wp_enqueue_scripts', [self::class, 'front_enqueue']);
		add_action('enqueue_block_editor_assets', [self::class, 'editor_assets']);
	}

	/**
	 * Register each type's block server-side (dynamic, render '': the front output is emitted by
	 * front_enqueue()). Attributes parsed here so front_enqueue can read effectId from the markup.
	 * @return void
	 */
	public static function register_blocks(){
		if(!function_exists('register_block_type')) return;

		foreach(self::$types as $t){
			if(empty($t['block']) || \WP_Block_Type_Registry::get_instance()->is_registered($t['block'])) continue;
			$attrs = array_merge([
				'effectId'	=> ['type' => 'string', 'default' => ''],
				'summary'	=> ['type' => 'string', 'default' => ''],
				'savedAt'	=> ['type' => 'number', 'default' => 0]
			], (isset($t['attributes']) && is_array($t['attributes'])) ? $t['attributes'] : []);
			$args = [
				'api_version'		=> 3,
				'title'				=> $t['title'],
				'category'			=> 'themepunch',
				'icon'				=> $t['icon'],
				'description'		=> $t['description'],
				'attributes'		=> $attrs,
				// block supports (e.g. align) — must be registered server-side too so the align attribute exists
				// and the front render can honour it; kept in lock-step with the client registerBlockType.
				'supports'			=> (isset($t['supports']) && is_array($t['supports'])) ? $t['supports'] : [],
				// Recorder-style effects emit on the front via front_enqueue() (render stays empty); an
				// inspector-style type supplies its own render_callback (markup baked from its attributes).
				'render_callback'	=> is_callable($t['render'] ?? null) ? $t['render'] : '__return_empty_string'
			];
			register_block_type($t['block'], $args);
		}
	}

	/**
	 * Editor: load the shared shell + each type's editor tool, and hand the shell its config
	 * (the registered types so it can register the blocks client-side).
	 *
	 * Save/get run through the shared SR7 _tpt.ajax helper (the same path every other api.class.php
	 * call uses), so tptools + the SR7.E config it reads in the block editor (ajaxurl + block nonce)
	 * are seeded here as well.
	 */
	/**
	 * Cache-bust value for a core PE-framework asset = its file mtime, so edits to the shell / colorpicker are
	 * picked up without an RS_REVISION bump (which stays static across iterations). Falls back to RS_REVISION.
	 * @return int|string
	 */
	private static function asset_ver($rel){
		$t = @filemtime(RS_PLUGIN_PATH . $rel);
		return $t ? $t : RS_REVISION;
	}

	/** @return void */
	public static function editor_assets(){
		if(empty(self::$types)) return;

		// shared SR7 ajax helper (_tpt.ajax) — the shell routes its save/get through the central api.class.php dispatcher
		wp_enqueue_script('_tpt', RS_PLUGIN_URL . 'public/js/libs/tptools.js', [], RS_REVISION, true);
		// shared reusable color picker (solid/alpha/gradient) — SHELL depends on it so SR7.PE.colorField exists
		wp_enqueue_script(self::SHELL_HANDLE . '-cp', RS_PLUGIN_URL . 'admin/assets/js/page-effects-colorpicker.js', [], self::asset_ver('admin/assets/js/page-effects-colorpicker.js'), true);
		wp_enqueue_script(self::SHELL_HANDLE, RS_PLUGIN_URL . 'admin/assets/js/page-effects.js', ['wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-i18n', '_tpt', self::SHELL_HANDLE . '-cp'], self::asset_ver('admin/assets/js/page-effects.js'), true);

		// _tpt.ajax reads the endpoint + block nonce from SR7.E in the block editor; seed them (idempotent with the shortcode wizard)
		wp_add_inline_script(self::SHELL_HANDLE, 'window.SR7=window.SR7||{};SR7.E=SR7.E||{};SR7.E.ajaxurl=SR7.E.ajaxurl||' . wp_json_encode(admin_url('admin-ajax.php')) . ';SR7.E.block_nonce=SR7.E.block_nonce||' . wp_json_encode(wp_create_nonce('revslider_actions')) . ';SR7.E.block_editor=true;', 'before');

		$types = [];
		foreach(self::$types as $type => $t){
			$types[$type] = ['type' => $type, 'block' => $t['block'], 'title' => $t['title'], 'icon' => $t['icon'], 'spriteIcon' => isset($t['spriteIcon']) ? $t['spriteIcon'] : '', 'description' => $t['description'], 'preview' => isset($t['preview']) ? $t['preview'] : '', 'self_block' => !empty($t['self_block'])];
			if(!empty($t['editor'])){
				$ver = !empty($t['version']) ? $t['version'] : RS_REVISION;
				wp_enqueue_script(self::SHELL_HANDLE . '-' . $type, $t['editor'], [self::SHELL_HANDLE], $ver, true);
			}
		}
		wp_localize_script(self::SHELL_HANDLE, 'SR7PECfg', [
			'types'		=> $types,
			'fontsUrl'	=> RS_PLUGIN_URL . 'public/css/fonts/'	// bundled icon fonts (Font Awesome, …) for the icon picker / stamps
		]);
		// Inject the SR7 icon sprite into the block editor (outer document) so block-inserter icons can <use> the
		// addon symbols (e.g. #Addon_Filmstrip). The sprite is normally only printed on SR7's own editor pages.
		add_action('admin_footer', [self::class, 'print_sprite']);
	}

	/**
	 * Print the SR7 icon sprite (hidden) so Page-Effect block icons can reference its symbols by id.
	 * @return void
	 */
	public static function print_sprite(){
		static $done = false;
		if($done || !class_exists('\RevSliderFunctions')) return;
		$done = true;
		echo '<div style="display:none" aria-hidden="true">' . \RevSliderFunctions::get_sprite_svg() . '</div>';
	}

	/**
	 * Front: scan the queried page for any page-effect block, read each one's meta, emit
	 * SR7.E.pageEffects[effectId] = { type, data } and enqueue the bootstrap + each used runtime.
	 * @return void
	 */
	public static function front_enqueue(){
		if(is_admin() || !is_singular() || empty(self::$types)) return;
		// Recorder preview: when the editor loads the page in its iframe, suppress the live effect so only
		// the editing overlay draws (otherwise the saved line + the edit preview both show, looking "stuck").
		if(isset($_GET['sr7pe_edit']) && current_user_can('edit_posts')) return;

		$post_id = get_queried_object_id();
		if(!$post_id) return;
		$post = get_post($post_id);
		if(!$post) return;

		$names = [];
		foreach(self::$types as $t) if(!empty($t['block'])) $names[$t['block']] = true;

		// Preview Live: unsaved draft stored in a short-lived transient (see preview_effect()) and injected
		// when the page is opened with sr7pe_preview + a valid nonce — same front runtime as after Save.
		$preview_eid = '';
		$preview_entry = null;
		if(isset($_GET['sr7pe_preview']) && current_user_can('edit_posts')){
			// Keep preview responses out of full-page caches so the transient draft is actually rendered.
			if(!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
			if(!defined('DONOTCACHEDB')) define('DONOTCACHEDB', true);
			nocache_headers();
			$preview_eid = sanitize_key($_GET['pe_id'] ?? '');
			$token = sanitize_text_field(wp_unslash($_GET['pe_token'] ?? ''));
			if($preview_eid !== '' && wp_verify_nonce($token, 'sr7pe_preview_' . $preview_eid)){
				$raw = get_transient(self::PREVIEW_PREFIX . get_current_user_id() . '_' . $preview_eid);
				$decoded = $raw ? json_decode($raw, true) : null;
				if(is_array($decoded) && !empty($decoded['type']) && isset(self::$types[$decoded['type']]) && is_array($decoded['data'] ?? null)){
					$preview_entry = ['type' => $decoded['type'], 'data' => $decoded['data']];
				}
			}
		}

		$payloads = [];
		foreach(self::collect_effects(parse_blocks($post->post_content), $names) as $eid){
			$eid = sanitize_key($eid);
			if($eid === '' || isset($payloads[$eid])) continue;
			if($preview_eid !== '' && $eid === $preview_eid && $preview_entry){
				$payloads[$eid] = $preview_entry;
				continue;
			}
			$meta = get_post_meta($post_id, self::META_PREFIX . $eid, true);
			if(empty($meta)) continue;
			$stored = json_decode($meta, true);
			if(!is_array($stored) || empty($stored['type']) || !isset(self::$types[$stored['type']])) continue;
			if(!is_array($stored['data']) || empty($stored['data'])) continue;
			$payloads[$eid] = ['type' => $stored['type'], 'data' => $stored['data']];
		}
		if(empty($payloads)) return;

		wp_enqueue_script(self::RT_HANDLE, RS_PLUGIN_URL . 'public/js/page-effects.js', [], self::asset_ver('public/js/page-effects.js'), true);

		$used = [];
		foreach($payloads as $p) $used[$p['type']] = true;
		foreach(array_keys($used) as $type){
			if(empty(self::$types[$type]['runtime'])) continue;
			$ver = !empty(self::$types[$type]['version']) ? self::$types[$type]['version'] : RS_REVISION;
			wp_enqueue_script(self::RT_HANDLE . '-' . $type, self::$types[$type]['runtime'], [self::RT_HANDLE], $ver, true);
		}

		$js = 'window.SR7=window.SR7||{};SR7.E=SR7.E||{};SR7.E.pageEffects=SR7.E.pageEffects||{};';
		$js .= 'SR7.E.peFontsUrl=' . wp_json_encode(RS_PLUGIN_URL . 'public/css/fonts/') . ';';   // for icon-font stamps
		foreach($payloads as $eid => $p){
			$js .= 'SR7.E.pageEffects[' . wp_json_encode($eid) . ']=' . wp_json_encode($p) . ';';
		}
		wp_add_inline_script(self::RT_HANDLE, $js, 'before');
	}

	/**
	 * Save an effect instance to the host post's meta. The type's own sanitize callable whitelists data.
	 * The AJAX entrypoint lives centrally in RevSliderApi::save_page_effect(); this stays here because
	 * it owns the effect-type registry (sanitize + summary).
	 *
	 * @return array|WP_Error  ['summary' => string] on success
	 */
	public static function save_effect($post_id, $effect_id, $type, $data){
		$post_id	= intval($post_id);
		$effect_id	= sanitize_key($effect_id);
		$type		= sanitize_key($type);
		if(!$post_id || $effect_id === '' || !isset(self::$types[$type])) return new WP_Error('bad_request', __('Bad Request', 'revslider'));
		if(!current_user_can('edit_post', $post_id)) return new WP_Error('forbidden', __('Function only available for administrators', 'revslider'));
		if(!is_array($data)) return new WP_Error('invalid_data', __('Invalid Data', 'revslider'));

		$sanitize	= self::$types[$type]['sanitize'];
		$clean		= is_callable($sanitize) ? call_user_func($sanitize, $data) : [];
		if(!is_array($clean)) $clean = [];

		update_post_meta($post_id, self::META_PREFIX . $effect_id, wp_slash(wp_json_encode(['type' => $type, 'data' => $clean])));

		// Effect data lives in post meta and is inlined into the front HTML at render time. Full-page caches
		// (CDN / WP Rocket / LiteSpeed / …) typically only purge on save_post — meta-only writes leave the
		// old SR7.E.pageEffects payload cached until the WP page is saved. Bust caches now so Save + Preview
		// Live reflect the new line without requiring a separate Gutenberg "Update".
		self::bust_post_caches($post_id);

		return ['summary' => self::summarize($type, $clean)];
	}

	/**
	 * Invalidate WP + common full-page caches for a host post after a page-effect meta write.
	 * Touches post_modified so caches keyed on mtime also drop the stale render — without a full
	 * wp_update_post (no revision noise / no "post changed in another tab" fights in Gutenberg).
	 * @return void
	 */
	private static function bust_post_caches($post_id){
		$post_id = intval($post_id);
		if($post_id <= 0) return;

		global $wpdb;
		$wpdb->update($wpdb->posts, [
			'post_modified'		=> current_time('mysql'),
			'post_modified_gmt'	=> current_time('mysql', true)
		], ['ID' => $post_id]);
		clean_post_cache($post_id);

		// Common full-page / CDN purge hooks (no-ops when the plugin isn't present).
		if(function_exists('rocket_clean_post')) rocket_clean_post($post_id);
		if(function_exists('w3tc_flush_post')) w3tc_flush_post($post_id);
		if(function_exists('wp_cache_post_change')) wp_cache_post_change($post_id);
		if(has_action('litespeed_purge_post')) do_action('litespeed_purge_post', $post_id);
		if(has_action('ce_clear_cache')) do_action('ce_clear_cache');
		if(function_exists('sg_cachepress_purge_cache')) sg_cachepress_purge_cache();
		if(has_action('nitropack_integration_purge_single_post')) do_action('nitropack_integration_purge_single_post', $post_id);
		do_action('revslider_page_effect_saved', $post_id);
	}

	/**
	 * Load a stored effect instance from the host post's meta (RevSliderApi::get_page_effect()).
	 *
	 * @return array|WP_Error  ['stored' => array|null] on success
	 */
	public static function get_effect($post_id, $effect_id){
		$post_id	= intval($post_id);
		$effect_id	= sanitize_key($effect_id);
		if(!$post_id || $effect_id === '') return new WP_Error('bad_request', __('Bad Request', 'revslider'));
		if(!current_user_can('edit_post', $post_id)) return new WP_Error('forbidden', __('Function only available for administrators', 'revslider'));

		$meta	= get_post_meta($post_id, self::META_PREFIX . $effect_id, true);
		$stored	= $meta ? json_decode($meta, true) : null;
		return ['stored' => (is_array($stored) ? $stored : null)];
	}

	/**
	 * Stage unsaved effect data for Preview Live (transient, not post meta). Returns a nonce the preview
	 * tab must pass so only the authoring user can read their own draft on the front end.
	 *
	 * @return array|WP_Error  ['effect_id' => string, 'token' => string] on success
	 */
	public static function preview_effect($post_id, $effect_id, $type, $data){
		$post_id	= intval($post_id);
		$effect_id	= sanitize_key($effect_id);
		$type		= sanitize_key($type);
		if(!$post_id || $effect_id === '' || !isset(self::$types[$type])) return new WP_Error('bad_request', __('Bad Request', 'revslider'));
		if(!current_user_can('edit_post', $post_id)) return new WP_Error('forbidden', __('Function only available for administrators', 'revslider'));
		if(!is_array($data)) return new WP_Error('invalid_data', __('Invalid Data', 'revslider'));

		$sanitize	= self::$types[$type]['sanitize'];
		$clean		= is_callable($sanitize) ? call_user_func($sanitize, $data) : [];
		if(!is_array($clean)) $clean = [];

		$key = self::PREVIEW_PREFIX . get_current_user_id() . '_' . $effect_id;
		set_transient($key, wp_json_encode(['type' => $type, 'data' => $clean, 'post_id' => $post_id]), HOUR_IN_SECONDS);

		return [
			'effect_id'	=> $effect_id,
			'token'		=> wp_create_nonce('sr7pe_preview_' . $effect_id)
		];
	}

	/**
	 * A short human label for the block card. Each type supplies its own summary callable; generic fallback.
	 * @return string
	 */
	private static function summarize($type, $data){
		$summ = isset(self::$types[$type]['summary']) ? self::$types[$type]['summary'] : null;
		if(is_callable($summ)){ $s = call_user_func($summ, $data); if(is_string($s) && $s !== '') return $s; }
		return __('configured', 'revslider');
	}

	/**
	 * block_categories_all filter: make sure the "ThemePunch" block category exists
	 * @return array
	 */
	public static function category($categories){
		foreach($categories as $c) if(isset($c['slug']) && $c['slug'] === 'themepunch') return $categories;
		return array_merge($categories, [['slug' => 'themepunch', 'title' => __('ThemePunch', 'revslider')]]);
	}

	/**
	 * Recursively collect effectId attributes from any registered page-effect block.
	 * @return array effect ids found in the block tree
	 */
	private static function collect_effects($blocks, $names){
		$ids = [];
		foreach((array)$blocks as $b){
			if(isset($b['blockName'], $names[$b['blockName']]) && !empty($b['attrs']['effectId'])) $ids[] = $b['attrs']['effectId'];
			if(!empty($b['innerBlocks'])) $ids = array_merge($ids, self::collect_effects($b['innerBlocks'], $names));
		}
		return $ids;
	}
}
