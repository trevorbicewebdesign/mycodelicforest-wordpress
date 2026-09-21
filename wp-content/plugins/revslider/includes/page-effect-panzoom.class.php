<?php
/**
 * Pan & Zoom PAGE EFFECT — the Ken Burns move from the SR7 editor, on a native WordPress image.
 *
 * INSPECTOR-style, self_block page effect (modelled on the Filmstrip / Hover Morph page effects) but CORE's
 * own. Two ways in: a dedicated block themepunch/sr7-panzoom, or a block EXTENSION on core/image + core/cover,
 * where the front runs on the <img> the block already rendered.
 *
 * Unlike its siblings it does NOT pull the SR7 module engine: a pan IS a transform on the image (see
 * public/js/panzoom.js), so the front loads only tptools + GSAP + the shared catalogue. That is why there is
 * no 'revslider_include_libraries' filter here.
 *
 * The core framework owns the type registry, the block category, the editor skin and the front bootstrap;
 * this class supplies the Pan & Zoom specifics.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderPageEffectPanZoom {

	const TYPE	= 'panzoom';
	const BLOCK	= 'themepunch/sr7-panzoom';
	// Existing image blocks the effect can be switched on for (block extension — panzoom.editor.js).
	// core/cover holds its image as a background element, which the runtime picks up as well.
	const HOSTS	= ['core/image', 'core/cover'];

	/** wire the hooks (called once on plugins_loaded) @return void */
	public static function init(){
		static $done = false;
		if($done) return;
		$done = true;

		// Register on 'init' priority 5, before the framework's register_blocks at init:10, so __() is safe.
		add_action('init', [self::class, 'register'], 5);
		// Mark a host block that has the effect switched on so the runtime finds it by [data-sr7pe].
		add_filter('render_block', [self::class, 'wrap_host'], 20, 2);
		// Editor CANVAS styles (the block list is iframed since WP 6.3 → they must go through editor settings).
		add_filter('block_editor_settings_all', [self::class, 'editor_canvas_styles']);
		// The catalogue + GSAP for the block editor: the panel's tiles play the real move, not a mock-up
		// (priority 20 — the framework enqueues its shell, and tptools, at the default 10).
		add_action('enqueue_block_editor_assets', [self::class, 'editor_assets'], 20);
		// Front assets + per-block config (priority 20: after core's own add_scripts at 10).
		add_action('wp_enqueue_scripts', [self::class, 'front_enqueue'], 20);
	}

	/**
	 * Cache-bust value for one of this effect's assets = its file mtime. The framework enqueues editor/runtime
	 * scripts under the static RS_REVISION, which does not move while we iterate.
	 * @return int|string
	 */
	private static function mt($rel){
		$t = @filemtime(RS_PLUGIN_PATH . $rel);
		return $t ? $t : RS_REVISION;
	}

	/** Register Pan & Zoom as a Page Effect TYPE with the framework. @return void */
	public static function register(){
		if(!class_exists('RevSliderPageEffects')) return;
		RevSliderPageEffects::register_type(self::TYPE, [
			'block'			=> self::BLOCK,
			'title'			=> __('Pan & Zoom', 'revslider'),
			'icon'			=> 'format-image',
			'description'	=> __('Give any image the Slider Revolution Ken Burns move — zoom, pan, drift — right in the page.', 'revslider'),
			'self_block'	=> true,			// the editor JS registers the block (inspector edit), not the generic card
			// A pan IS a transform: this effect never touches a pixel, it moves a box. So it takes 'box', which
			// clashes with nothing that PAINTS — it rides on top of a Filmstrip or a Hover Morph just as happily
			// as on a bare image. Only another box-mover on the same element would fight it (style.transform).
			'claim'			=> ['attr' => 'sr7PanZoom', 'scope' => 'self', 'takes' => 'box'],	// see SR7.PE.claimedBy
			'attributes'	=> self::attributes(),
			'supports'		=> ['align' => ['wide', 'full']],
			'render'		=> [self::class, 'render'],
			'editor'		=> RS_PLUGIN_URL . 'admin/assets/js/panzoom.editor.js?mt=' . self::mt('admin/assets/js/panzoom.editor.js'),
			'runtime'		=> RS_PLUGIN_URL . 'public/js/panzoom.pe.js?mt=' . self::mt('public/js/panzoom.pe.js'),
			'storage'		=> 'attrs',			// config lives in the block attributes, not in post meta — see front_enqueue()
			'sanitize'		=> [self::class, 'sanitize']
		]);
	}

	/** Block attribute schema (mirrored in panzoom.editor.js so the dynamic block round-trips). @return array */
	public static function attributes(){
		return [
			'image'		=> ['type' => 'object',	'default' => null],		// {id,url,w,h,alt}
			'height'	=> ['type' => 'number',	'default' => 480],
			'pan'		=> ['type' => 'string',	'default' => ''],		// preset key — SR7.PANZ.list
			'pdir'		=> ['type' => 'string',	'default' => 'right'],	// direction axis (presets that travel)
			'pcor'		=> ['type' => 'string',	'default' => 'tl'],		// corner axis (Corner Push)
			'pstr'		=> ['type' => 'string',	'default' => 'middle'],	// Subtle / Medium / Strong
			'ease'		=> ['type' => 'string',	'default' => ''],		// GSAP ease, empty = the preset's own
			'dur'		=> ['type' => 'number',	'default' => 9],		// seconds for one pass
			'loop'		=> ['type' => 'boolean','default' => true]		// repeat, or play once and hold
		];
	}

	/**
	 * Whitelist the block attributes into the payload the runtime plays. `pan` is passed through as a plain
	 * key instead of being checked against a list: the catalogue lives in JS (public/js/panzoom.js) and a
	 * second copy here would go stale the moment a preset is added — an unknown key simply resolves to no
	 * preset and the effect stays off.
	 * @return array
	 */
	public static function sanitize($a){
		if(!is_array($a)) $a = [];
		$key = function($v, $def){ $v = sanitize_key(is_string($v) ? $v : ''); return ($v === '' || strlen($v) > 24) ? $def : $v; };

		$pan = $key($a['pan'] ?? '', '');
		if($pan === '') return [];   // no preset picked → nothing to emit

		// a GSAP ease is dotted ("sine.inOut"), which sanitize_key would eat
		$ease = preg_replace('/[^a-zA-Z0-9.\-]/', '', (string)($a['ease'] ?? ''));

		$draft = [
			'pan'	=> $pan,
			'pdir'	=> $key($a['pdir'] ?? '', 'right'),
			'pcor'	=> $key($a['pcor'] ?? '', 'tl'),
			'pstr'	=> $key($a['pstr'] ?? '', 'middle')
		];
		// "no ease picked" must be ABSENT, not an empty string: the frame maths falls back to the preset's
		// own ease with ?? , and an empty string is not nullish — it would silently run every move linear.
		if($ease !== '') $draft['ease'] = $ease;

		return [
			'draft'	=> $draft,
			'dur'	=> max(1000, min(60000, intval(round(floatval($a['dur'] ?? 9) * 1000)))),
			'loop'	=> !isset($a['loop']) || !empty($a['loop'])
		];
	}

	/**
	 * Block editor: GSAP + the shared catalogue, so the panel's preset tiles animate with the very maths the
	 * front will run, and the artwork base url the tiles are drawn from.
	 * @return void
	 */
	public static function editor_assets(){
		if(!wp_script_is(RevSliderPageEffects::SHELL_HANDLE, 'enqueued')) return;   // no page effects on this screen
		// '_tpt' is the handle the framework enqueues tptools under — tpgsap writes into that namespace.
		wp_enqueue_script('sr7-tpgsap', RS_PLUGIN_URL . 'public/js/libs/tpgsap.js', ['_tpt'], RS_REVISION, true);
		wp_enqueue_script('sr7-panzoom-cat', RS_PLUGIN_URL . 'public/js/panzoom.js', ['_tpt'], self::mt('public/js/panzoom.js'), true);
		wp_localize_script(RevSliderPageEffects::SHELL_HANDLE . '-' . self::TYPE, 'SR7PanZoomEd', [
			'url' => RS_PLUGIN_URL . 'admin/assets/images/presets/'
		]);
	}

	/** The block card inside the editor iframe (the SR7 Page Effect family look). The badge on a host block is
	 *  the shell's — see RevSliderPageEffects::badge_canvas_styles(). @return array */
	public static function editor_canvas_styles($settings){
		$css = '.sr7-pz-edit{border:1px solid #e0d8ff;border-radius:0;padding:8px 20px 16px;background:linear-gradient(180deg,#faf8ff,#fff);font-family:\'Inter\',system-ui,sans-serif}'
			. '.sr7-pz-edit-head{display:flex;align-items:center;gap:10px;margin-bottom:12px;font-size:13px}'
			. '.sr7-pz-edit-meta{color:#646970;flex:1;font-size:13px}'
			. '.sr7-pz-edit-prev{position:relative;overflow:hidden;background:#f0f0f1;max-height:320px}'
			. '.sr7-pz-edit-prev img{display:block;width:100%;height:auto}'
			. '.sr7-panzoom-block .components-placeholder__fieldset .components-button{font-family:\'Inter\',system-ui,sans-serif!important;font-weight:600!important;color:#1e1e1e!important;background:#fff!important;box-shadow:inset 0 0 0 1px #ccc!important}'
			. '.sr7-panzoom-block .components-placeholder__fieldset .components-button:hover{color:#5c24ff!important;background:#fff!important;box-shadow:inset 0 0 0 1px #5c24ff!important}';
		if(!isset($settings['styles']) || !is_array($settings['styles'])) $settings['styles'] = [];
		$settings['styles'][] = ['css' => $css];
		return $settings;
	}

	/**
	 * Front: enqueue the runtime bootstrap + the catalogue + the adapter, and emit each block's config as
	 * SR7.E.pageEffects[effectId] = { type:'panzoom', data:{ draft, dur, loop } }.
	 * @return void
	 */
	public static function front_enqueue(){
		if(is_admin() || !is_singular()) return;
		$post = RevSliderPageEffects::queried_post();   // NOT get_post($id) — that misses a preview, see there
		if(!$post) return;

		$blocks = self::collect_effects(parse_blocks($post->post_content));
		if(empty($blocks)) return;

		$payloads = [];
		foreach($blocks as $eid => $a){
			$data = self::sanitize($a);
			if(!empty($data)) $payloads[$eid] = $data;
		}
		if(empty($payloads)) return;

		$ver		= RS_REVISION;
		$rtHandle	= RevSliderPageEffects::RT_HANDLE;

		// tptools carries the resource loader the adapter drives to pull GSAP; the same handle core's own
		// front uses, so a page that ALSO holds a module does not load it twice.
		wp_enqueue_script('tp-tools', RS_PLUGIN_URL . 'public/js/libs/tptools.js', [], $ver, true);
		wp_enqueue_script($rtHandle, RS_PLUGIN_URL . 'public/js/page-effects.js', [], $ver, true);
		wp_enqueue_script('sr7-panzoom-pe', RS_PLUGIN_URL . 'public/js/panzoom.pe.js', [$rtHandle, 'tp-tools'], self::mt('public/js/panzoom.pe.js'), true);
		// The two files the adapter loads through SR7's own loader once it is running.
		wp_localize_script('sr7-panzoom-pe', 'SR7PanZoomPE', [
			'catalog'	=> RS_PLUGIN_URL . 'public/js/panzoom.js?ver=' . self::mt('public/js/panzoom.js'),
			'gsap'		=> RS_PLUGIN_URL . 'public/js/libs/tpgsap.js?ver=' . $ver
		]);

		$js = 'window.SR7=window.SR7||{};SR7.E=SR7.E||{};SR7.E.pageEffects=SR7.E.pageEffects||{};';
		foreach($payloads as $eid => $data){
			$js .= 'SR7.E.pageEffects[' . wp_json_encode($eid) . ']=' . wp_json_encode(['type' => self::TYPE, 'data' => $data]) . ';';
		}
		wp_add_inline_script('sr7-panzoom-pe', $js, 'before');
	}

	/**
	 * Recursively collect [effectId => attrs] for every Pan & Zoom on the page:
	 *  - the dedicated themepunch/sr7-panzoom block → its own attributes
	 *  - a host block (core/image, core/cover) with the effect switched on → its sr7PanZoom attributes
	 * @return array
	 */
	private static function collect_effects($blocks){
		$out = [];
		foreach((array)$blocks as $b){
			$name = isset($b['blockName']) ? $b['blockName'] : '';
			if($name === self::BLOCK && !empty($b['attrs']['effectId'])){
				$eid = sanitize_key($b['attrs']['effectId']);
				if($eid !== '') $out[$eid] = $b['attrs'];
			}else if(in_array($name, self::HOSTS, true) && !empty($b['attrs']['sr7PanZoom'])){
				$pz = $b['attrs']['sr7PanZoom'];
				if(is_array($pz) && !empty($pz['enabled']) && !empty($pz['effectId'])){
					$eid = sanitize_key($pz['effectId']);
					if($eid !== '') $out[$eid] = $pz;   // no 'image' key → the adapter animates the host's own <img>
				}
			}
			if(!empty($b['innerBlocks'])) $out += self::collect_effects($b['innerBlocks']);
		}
		return $out;
	}

	/**
	 * Mark a host block that has the effect switched on so the runtime can find it by [data-sr7pe] and pan
	 * its already-rendered <img>. Pre-JS the image renders normally.
	 * @return string
	 */
	public static function wrap_host($html, $block){
		if(empty($block['blockName']) || !in_array($block['blockName'], self::HOSTS, true)) return $html;
		$pz = isset($block['attrs']['sr7PanZoom']) ? $block['attrs']['sr7PanZoom'] : null;
		if(!is_array($pz) || empty($pz['enabled']) || empty($pz['effectId'])) return $html;
		return RevSliderPageEffects::tag_host($html, $pz['effectId'], 'sr7-pz sr7-panzoom-host');
	}

	/**
	 * Front markup for the dedicated block: the picked image in a fixed-height box. Pre-JS that is a plain
	 * cover-fit image (graceful fallback + the source the runtime pans).
	 * @return string
	 */
	public static function render($attributes, $content = '', $block = null){
		$a	 = is_array($attributes) ? $attributes : [];
		$eid = isset($a['effectId']) ? sanitize_key($a['effectId']) : '';
		if($eid === '') return '';
		$img = (isset($a['image']) && is_array($a['image'])) ? $a['image'] : null;
		if(empty($img['url'])) return '';

		$h		= max(80, min(2000, isset($a['height']) ? intval($a['height']) : 480));
		$url	= esc_url($img['url']);
		$alt	= isset($img['alt']) ? esc_attr($img['alt']) : '';
		$align	= (isset($a['align']) && in_array($a['align'], ['wide', 'full'], true)) ? ' align' . $a['align'] : '';

		return '<div class="sr7-pz sr7-panzoom' . $align . '" id="sr7pz-' . esc_attr($eid) . '" data-sr7pe="' . esc_attr($eid) . '" style="height:' . $h . 'px;overflow:hidden">'
			. '<img class="sr7-panzoom-item" src="' . $url . '" alt="' . $alt . '" loading="lazy" decoding="async" style="width:100%;height:100%;object-fit:cover;display:block" />'
			. '</div>';
	}
}
