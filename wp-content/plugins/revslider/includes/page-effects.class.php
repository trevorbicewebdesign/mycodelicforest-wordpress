<?php
/**
 * Page Effects framework — shared base for "animate existing page content" addons.
 *
 * A Page Effect is a Gutenberg block that augments existing content on the front end (a scroll-drawn chalk
 * line, a gallery turned into a filmstrip) WITHOUT a full Slider Revolution module. This class is the GLOBAL
 * half: block registration, the per-page meta save/load, and front emission. An addon registers a "type"
 * supplying the editor tool and the front runtime.
 *
 * Contract (see harmonization/PAGE-EFFECTS.md):
 *   PHP    : RevSliderPageEffects::register_type($type, $args)
 *   Editor : SR7.PE.registerType($type, {card, tool})        // admin/assets/js/page-effects.js
 *   Front  : SR7.PE.registerRuntime($type, {mount, unmount}) // public/js/page-effects.js
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/**
 * Each instance keeps its data in the host post's meta (META_PREFIX + effectId), keyed by a per-block
 * effectId. The block renders nothing: front_enqueue() collects the effect ids from the queried page's
 * blocks and emits them as SR7.E.pageEffects, which also works on templates that never render post_content.
 * Unsaved editor state goes through preview_effect() into a short-lived transient instead.
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
			// Template Packages (docs/template-packages-v2.md): rewrite this type's own data against the installer's
			// resolution map. Only the TYPE knows which of its fields are references - a ChalkLine anchor's `bind` is
			// a CSS selector that may name an id generated from a module, and that module gets a NEW id on the
			// customer's site. Without this the effect installs cleanly and binds to nothing. callable($data,$map):array
			'remap'			=> null,
			// Where an instance's config actually lives, which is what decides how a package has to carry it:
			//   'meta'  → post meta _sr7_pe_<effectId>; the framework's front_enqueue() emits it (every addon type)
			//   'attrs' → the block's own attributes, and the type runs its own front_enqueue() (core's
			//             ScrollAnim / PanZoom / PointerIX, which also ride on arbitrary HOST blocks)
			//   'both'  → some of each
			// Default 'meta' so every type registered before packages existed keeps behaving exactly as it did.
			'storage'		=> 'meta',
			// Inspector-style effects (e.g. Filmstrip) configure in the block inspector instead of the
			// on-page Recorder, own their block edit() (custom attributes + InspectorControls) and emit
			// their own front markup. Recorder-style effects (e.g. ChalkLine) leave these at the defaults.
			'self_block'	=> false,	// true: the type's editor JS registers the block client-side (skip the generic card)
			// Which block attribute this effect writes when it takes over a host block, so the OTHERS can see
			// it: ['attr' => 'sr7PanZoom', 'scope' => 'self'|'descendants']. 'descendants' = the effect swallows
			// every image below it (a Filmstrip), so it blocks from an ancestor too. Read by SR7.PE.claimedBy().
			'claim'			=> null,
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
		add_action('send_headers', [self::class, 'workbench_isolation']);
		add_action('wp_enqueue_scripts', [self::class, 'front_enqueue']);
		add_action('enqueue_block_editor_assets', [self::class, 'editor_assets']);
		add_filter('block_editor_settings_all', [self::class, 'badge_canvas_styles']);
	}

	/**
	 * The badge on a host block, inside the editor iframe. ONE pill for the block, drawn by the shell: every
	 * effect used to bring its own copy of exactly this CSS, all of them pinned to the same corner, so two
	 * effects on one block put two pills on top of each other.
	 * @return array
	 */
	public static function badge_canvas_styles($settings){
		// The mark on its own, no purple pill and no caption: it signs itself "Page FX". An <img>, because this is
		// portalled INTO the canvas iframe where a sprite reference would resolve against the outer document.
		// 🔴 The mark is flat ink and lands on the author's own picture, which may be black. The white halo does
		// what the die-cut edge did and the soft dark shadow what the pill did. Doubled: one drop-shadow is too thin.
		$css = '.has-sr7-pe{position:relative}'
			// 🔴 A SHARE of the block's HEIGHT, never 40 flat pixels: a row of thumbnails is 120x42, and a 40px sticker
			// pinned 8px in covered the picture. Height drives it and `aspect-ratio` gives back the width, so the mark
			// stays square at every size - a `max-height` on top of a set width does NOT pull the width in, it
			// flattens the mark. Percentages are safe against an auto-height parent.
			. '.sr7pe-badge{position:absolute;top:6px;right:6px;z-index:21;display:block;height:clamp(14px,30%,40px);width:auto;aspect-ratio:1;padding:0;border:0;background:none;line-height:0;cursor:pointer;filter:drop-shadow(0 0 2px #fff) drop-shadow(0 0 2px #fff) drop-shadow(0 2px 6px rgba(0,0,0,.3))}'
			. '.sr7pe-badge:hover{filter:drop-shadow(0 0 2px #fff) drop-shadow(0 0 3px #5C24FF) drop-shadow(0 2px 8px rgba(0,0,0,.35))}'
			. '.sr7pe-badge:focus{outline:2px solid #5C24FF;outline-offset:3px;border-radius:6px}'
			. '.sr7pe-badge svg,.sr7pe-badge img{display:block;height:100%;width:auto}'
			// 🔴 A CONTAINER and the block inside it share that corner: a gallery's top right IS the top right of its
			// last image, so the gallery's own mark landed exactly under the one that image already wore. Only the
			// block that holds another badged block steps to the left.
			. '.has-sr7-pe:has(.has-sr7-pe)>.sr7pe-badge{right:auto;left:6px}'
			// The buttons ON a block's card, in ONE language - the same two shapes the panels use, spelled again because
			// a card lives in the canvas iframe and the panel stylesheet never reaches it. 28px, the height every SR7
			// control stands at. 🔴 SR7 purple, not the shell accent, which is the SR7 EDITOR's blue and reads as
			// WordPress's own on a page. And a plain <button>, never WP's <Button>: `is-primary` brings its own fill.
			. '.sr7pe-cta{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:28px;padding:0 12px;box-sizing:border-box;border:1px solid #dcdcde;border-radius:4px;background:#fff;color:#1e1e1e;font:600 13px/1 Inter,-apple-system,BlinkMacSystemFont,system-ui,sans-serif;cursor:pointer;box-shadow:none;text-decoration:none}'
			. '.sr7pe-cta:hover{border-color:#5C24FF;color:#5C24FF}'
			// 🔴 The focus mark is INSIDE the border, never a second frame around it.
			. '.sr7pe-cta:focus{outline:none;box-shadow:none}'
			. '.sr7pe-cta:focus-visible{border-color:#5C24FF;box-shadow:inset 0 0 0 1px #5C24FF}'
			. '.sr7pe-cta svg{display:block}'
			. '.sr7pe-cta-go{background:#5C24FF;border-color:#5C24FF;color:#fff}'
			. '.sr7pe-cta-go:hover{background:#4A17E0;border-color:#4A17E0;color:#fff}'
			. '.sr7pe-cta-go:focus-visible{box-shadow:inset 0 0 0 1px #fff}';
		if(!isset($settings['styles']) || !is_array($settings['styles'])) $settings['styles'] = [];
		$settings['styles'][] = ['css' => $css];
		return $settings;
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
				// Same family name the editor puts in front of the block title (SR7.PE.fxTitle) — this one shows
				// wherever WP reads the block from the server instead: the site editor's block list, patterns.
				'title'				=> __('Page FX', 'revslider') . ' · ' . $t['title'],
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
	 * Editor: load the shared shell + each type's editor tool, and hand the shell its config. Save/get run
	 * through the shared SR7 _tpt.ajax helper, so tptools and the SR7.E config it reads in the block editor
	 * (ajaxurl + block nonce) are seeded here as well.
	 */
	/**
	 * Cache-bust value for a core PE-framework asset = its file mtime, so edits to the shell are picked up
	 * without an RS_REVISION bump. Falls back to RS_REVISION.
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

		// _tpt.ajax reads the endpoint + block nonce from SR7.E in the block editor; seed them (idempotent with the
		// shortcode wizard). plugin_url rides along because an effect that wants to show itself in the editor has
		// to reach the front runtime, and SR7's own resource loader builds every module URL from this one value.
		wp_add_inline_script(self::SHELL_HANDLE, 'window.SR7=window.SR7||{};SR7.E=SR7.E||{};SR7.E.ajaxurl=SR7.E.ajaxurl||' . wp_json_encode(admin_url('admin-ajax.php')) . ';SR7.E.block_nonce=SR7.E.block_nonce||' . wp_json_encode(wp_create_nonce('revslider_actions')) . ';SR7.E.plugin_url=SR7.E.plugin_url||' . wp_json_encode(RS_PLUGIN_URL) . ';SR7.E.block_editor=true;', 'before');

		$types = [];
		foreach(self::$types as $type => $t){
			$types[$type] = ['type' => $type, 'block' => $t['block'], 'title' => $t['title'], 'icon' => $t['icon'], 'spriteIcon' => isset($t['spriteIcon']) ? $t['spriteIcon'] : '', 'description' => $t['description'], 'preview' => isset($t['preview']) ? $t['preview'] : '', 'self_block' => !empty($t['self_block']), 'claim' => (isset($t['claim']) && is_array($t['claim'])) ? $t['claim'] : null];
			if(!empty($t['editor'])){
				$ver = !empty($t['version']) ? $t['version'] : RS_REVISION;
				wp_enqueue_script(self::SHELL_HANDLE . '-' . $type, $t['editor'], [self::SHELL_HANDLE], $ver, true);
			}
		}
		wp_localize_script(self::SHELL_HANDLE, 'SR7PECfg', [
			'types'		=> $types,
			'fontsUrl'	=> RS_PLUGIN_URL . 'public/css/fonts/'	// bundled icon fonts (Font Awesome, …) for the icon picker / stamps
		]);
		wp_localize_script(self::SHELL_HANDLE, 'SR7PELang', self::lang());
		// Inject the SR7 icon sprite into the block editor (outer document) so block-inserter icons can <use> the
		// addon symbols (e.g. #Addon_Filmstrip). The sprite is normally only printed on SR7's own editor pages.
		add_action('admin_footer', [self::class, 'print_sprite']);
	}

	/**
	 * The words this screen actually shows. SR7.t reads SR7.LANG, and on the block-editor screen neither the
	 * admin bundle that defines SR7.t nor the table it reads is enqueued.
	 *
	 * ⚠ The SUBSET, not the editor's table: the full one is 1477 strings / 85 KB. i18n-strings-pe.php is
	 * harvested from the Page-Effect editor files alone (tools/i18n/extract-lang.js), and each addon hands over
	 * its own through the filter it already answers for the SR7 editor.
	 * @return array
	 */
	public static function lang(){
		$file = RS_PLUGIN_PATH . 'admin/includes/i18n-strings-pe.php';
		$lang = is_file($file) ? (array) include $file : [];
		foreach((array) apply_filters('revslider_api_get_addon_lang', []) as $addon){
			if(is_array($addon)) $lang = array_merge($lang, $addon);
		}
		return $lang;
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
	 * The post as the visitor is actually being shown it — the ONE way an effect may read post_content.
	 *
	 * 🔴 `get_post(get_queried_object_id())` is wrong on a preview and silently so. WP_Query swaps the autosave's
	 * content in by mutating ITS OWN post object (the_preview → _set_preview) and that never reaches the object
	 * cache, while WP_Post::get_instance() ends on `return new WP_Post($_post)`, a fresh object from the cache.
	 * So re-fetching by id mid-preview hands back the SAVED blocks. The queried object is what WP substituted into.
	 *
	 * @return \WP_Post|null
	 * @since 7.1.7
	 */
	public static function queried_post(){
		$post = get_queried_object();
		if(!($post instanceof \WP_Post)){
			$id		= get_queried_object_id();
			$post	= $id ? get_post($id) : null;
		}
		if(!$post) return $post;

		// 🔴 And WP's own substitution cannot be relied on either: `_set_preview()` mutates the post object, but the
		// very next call is `get_post()` on it, and WP_Post::filter() hands back a FRESH instance from the cache
		// whenever the filter flag is not 'raw', dropping the mutation. Measured: the editor's autosave held
		// pan="breathe" while the preview served pan="zoompan". So the autosave is read here directly.
		if(is_preview() && current_user_can('edit_post', $post->ID)){
			$auto = wp_get_post_autosave($post->ID);
			// A draft is overwritten by its own autosave (wp_autosave), so an older revision may still be
			// lying about — take it only when it is genuinely newer than what is saved.
			if($auto && strtotime($auto->post_modified_gmt) >= strtotime($post->post_modified_gmt)){
				$post = clone $post;					// never hand a mutated object back into the cache
				$post->post_content = $auto->post_content;
			}
		}
		return $post;
	}

	/**
	 * The page the workbench frames, saying the same sentence about isolation the editor screen says.
	 *
	 * WP 7.1 sends `Document-Isolation-Policy: isolate-and-credentialless` on the block-editor screen, and an
	 * isolated document refuses to embed a frame without the same policy: the frame loaded, every read of its
	 * document threw "cross-origin", and the recorder had nothing to draw on. Repeating the header puts the page
	 * back in the editor's isolated context WITH its cookies.
	 *
	 * ⚠ Only on the workbench request. On the live page it would make cross-origin embeds (a YouTube layer, a
	 * font host) go credentialless for every visitor.
	 *
	 * @since 7.1.8
	 * @return void
	 */
	public static function workbench_isolation(){
		if(is_admin() || !isset($_GET['sr7pe_edit']) || !current_user_can('edit_posts')) return;
		header('Document-Isolation-Policy: isolate-and-credentialless');
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

		$post = self::queried_post();
		if(!$post) return;
		$post_id = $post->ID;

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
	 * Tag a host block's OWN root element so the front runtime finds it by [data-sr7pe]. Tagging beats wrapping:
	 * a wrapper box resets the block's width, alignment and offset.
	 *
	 * SEVERAL effects may share a block, so the id is APPENDED to whatever is there: data-sr7pe="id1 id2".
	 * 🔴 A second attribute would not work: HTML keeps only the FIRST duplicate, and the loser's querySelector
	 * then finds nothing - it never mounts, silently. Hence the runtimes match with [data-sr7pe~="id"].
	 *
	 * @param string $html       the block's rendered markup
	 * @param string $effect_id  the effect instance id
	 * @param string $classes    space separated classes to add to the root element
	 * @return string
	 */
	public static function tag_host($html, $effect_id, $classes = ''){
		$eid = sanitize_key($effect_id);
		if($eid === '' || !preg_match('/^(\s*)<([a-zA-Z0-9]+)\b([^>]*)>/', $html, $m)) return $html;
		$attrs = $m[3];
		// already tagged by another effect → add this id to the list instead of turning it away
		if(preg_match('/\sdata-sr7pe\s*=\s*(["\'])(.*?)\1/', $attrs, $d)){
			$have = preg_split('/\s+/', trim($d[2]), -1, PREG_SPLIT_NO_EMPTY);
			if(in_array($eid, $have, true)) return $html;
			$have[] = $eid;
			$attrs = str_replace($d[0], ' data-sr7pe="' . esc_attr(implode(' ', $have)) . '"', $attrs);
			if($classes !== ''){
				if(preg_match('/\sclass\s*=\s*["\']/', $attrs)) $attrs = preg_replace('/(\sclass\s*=\s*["\'])/', '$1' . $classes . ' ', $attrs, 1);
				else $attrs = ' class="' . esc_attr($classes) . '"' . $attrs;
			}
			return substr_replace($html, '<' . $m[2] . $attrs . '>', strlen($m[1]), strlen($m[0]) - strlen($m[1]));
		}
		if($classes !== ''){
			if(preg_match('/\sclass\s*=\s*["\']/', $attrs)) $attrs = preg_replace('/(\sclass\s*=\s*["\'])/', '$1' . $classes . ' ', $attrs, 1);
			else $attrs = ' class="' . esc_attr($classes) . '"' . $attrs;
		}
		$open = '<' . $m[2] . $attrs . ' data-sr7pe="' . esc_attr($eid) . '">';
		return substr_replace($html, $open, strlen($m[1]), strlen($m[0]) - strlen($m[1]));
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
	 * Mint an effectId for a brand-new instance, matching what the editor produces (mintEffectId() in
	 * page-effects.js: "pe" + 12 chars). Collision-checked against the post it is going onto — two effects
	 * sharing an id would read each other's meta.
	 *
	 * @return string
	 */
	public static function mint_effect_id($post_id = 0){
		$post_id = intval($post_id);

		for($i = 0; $i < 8; $i++){
			$eid = 'pe' . substr(str_replace('-', '', wp_generate_uuid4()), 0, 12);
			if($post_id <= 0 || get_post_meta($post_id, self::META_PREFIX . $eid, true) === '') return $eid;
		}

		//Eight uuid4 draws all colliding does not happen; falling through to a longer id beats returning ''.
		return 'pe' . substr(str_replace('-', '', wp_generate_uuid4()), 0, 24);
	}

	/**
	 * Install one effect instance onto a freshly created post, for the Template Package installer.
	 *
	 * Differs from save_effect() because this post was made a moment ago by an installer, not edited by a person:
	 * the effectId is minted here rather than supplied by an existing block, the type's remap() runs first so the
	 * data's references point at what this install created, and no cache bust happens (the installer flushes once
	 * when it is done). Attribute-backed types ('storage' => 'attrs') still come through here for the minted id
	 * and the remap.
	 *
	 * @param int    $post_id host post
	 * @param string $type    a registered effect type
	 * @param array  $data    the instance's config, straight from the manifest
	 * @param array  $map     the installer's resolution map, handed to the type's remap()
	 * @return array|WP_Error ['effect_id' => string, 'data' => array] on success
	 */
	public static function install_effect($post_id, $type, $data, $map = []){
		$post_id = intval($post_id);
		$type    = sanitize_key($type);

		if(!$post_id || !isset(self::$types[$type])) return new WP_Error('bad_request', __('Bad Request', 'revslider'));
		if(!current_user_can('edit_post', $post_id)) return new WP_Error('forbidden', __('Function only available for administrators', 'revslider'));
		if(!is_array($data)) $data = [];

		$data = self::remap_data($type, $data, $map);

		$sanitize = self::$types[$type]['sanitize'];
		$clean    = is_callable($sanitize) ? call_user_func($sanitize, $data) : [];
		if(!is_array($clean)) $clean = [];

		$eid = self::mint_effect_id($post_id);

		//'attrs' types carry everything in the block markup; writing an empty meta row for them would leave
		//front_enqueue() emitting a payload the type never reads.
		if(self::$types[$type]['storage'] !== 'attrs'){
			update_post_meta($post_id, self::META_PREFIX . $eid, wp_slash(wp_json_encode(['type' => $type, 'data' => $clean])));
		}

		return ['effect_id' => $eid, 'data' => $clean];
	}

	/**
	 * Resolve the media references in one of a type's own fields.
	 *
	 * The exporter turns every uploads URL into a %%SR7_MEDIA:…%% reference and banks the file with the package;
	 * the installer resolves those again in MARKUP. A type's own config never passes through markup, and only the
	 * type knows which of its fields hold a picture, which is why it calls this rather than the other way round.
	 * The id is rewritten along with the url: it names a different picture on the site the package lands on.
	 *
	 * @param mixed $value a url, or a picked image ['id' => int, 'url' => string, …]
	 * @param array $map   the installer's resolution map
	 * @return mixed the same shape, pointing at this site's copy
	 */
	public static function remap_media($value, $map){
		if(is_array($value)){
			if(!isset($value['url'])) return $value;

			$id = self::media_id($value['url'], $map);
			if($id <= 0) return $value;

			$value['url'] = wp_get_attachment_url($id);
			$value['id']  = $id;

			return $value;
		}

		if(!is_string($value) || strpos($value, '%%SR7_MEDIA:') === false) return $value;
		if(!class_exists('RevSliderPackageInstaller')) return $value;

		return preg_replace_callback(RevSliderPackageInstaller::TOKEN_MEDIA, function($m) use ($map){
			$id = self::mapped_media($m[1], $map);

			return $id ? wp_get_attachment_url($id) : '';
		}, $value);
	}

	/** @return int the attachment this url resolves to, or 0 if it is not a package reference */
	private static function media_id($url, $map){
		if(!is_string($url) || !class_exists('RevSliderPackageInstaller')) return 0;
		if(!preg_match(RevSliderPackageInstaller::TOKEN_MEDIA, $url, $m)) return 0;

		return self::mapped_media($m[1], $map);
	}

	/** @return int */
	private static function mapped_media($ref, $map){
		$media = (isset($map['media']) && is_array($map['media'])) ? $map['media'] : [];

		return isset($media[$ref]) ? intval($media[$ref]) : 0;
	}

	/**
	 * Run a type's remap() over an instance's data. Kept separate from install_effect() because attribute-backed
	 * effects need the same rewrite applied to their BLOCK ATTRIBUTES, which never pass through post meta.
	 * A type without a remap() gets its data back untouched.
	 *
	 * @return array
	 */
	public static function remap_data($type, $data, $map){
		$type = sanitize_key($type);
		if(!isset(self::$types[$type]) || !is_array($data)) return is_array($data) ? $data : [];

		$remap = self::$types[$type]['remap'];
		if(!is_callable($remap)) return $data;

		$out = call_user_func($remap, $data, is_array($map) ? $map : []);

		return is_array($out) ? $out : $data;
	}

	/**
	 * Which block attribute a type claims on a host block ('sr7Anim', 'sr7PanZoom', …), or '' when the type
	 * is only ever its own block. The package installer writes the minted effectId into this attribute
	 * rather than trusting a manifest to name it.
	 *
	 * @return string
	 */
	public static function claimed_attr($type){
		$type = sanitize_key($type);
		if(!isset(self::$types[$type])) return '';

		$claim = self::$types[$type]['claim'];

		return (is_array($claim) && !empty($claim['attr'])) ? (string)$claim['attr'] : '';
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
