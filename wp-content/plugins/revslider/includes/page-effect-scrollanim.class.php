<?php
/**
 * SCROLL ANIMATION PAGE EFFECT — the SR7 layer-animation catalogue, on ordinary page content.
 *
 * INSPECTOR-style, self_block page effect built on the Pan & Zoom page effect's shape and, like it, CORE's
 * own. Two ways in: a dedicated block themepunch/sr7-scrollanim, or a block EXTENSION on the common content
 * blocks, where the front animates the markup the block already rendered. Like Pan & Zoom it does NOT pull
 * the SR7 module engine: public/js/animpreset.js plays a preset body on a plain element with only GSAP.
 *
 * ⭐ THE PRESET IS A REFERENCE, NOT A COPY. The block stores the preset KEY ("cinematic.reveal" + a variant)
 * and this class resolves the body out of the catalogue (RevSliderData::get_layer_animations) at RENDER time,
 * so a retune reaches every page that uses the preset and tools/anim-player/fidelity.mjs stays meaningful.
 * Baking could not keep its promise anyway: the ~10 rules in animpreset.js interpret the data, so a change
 * there moves a frozen body just as much as a live one. The price is that a renamed preset stops resolving,
 * hence the rule below: the start state is set by JS ALONE, never by PHP or inline CSS.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

class RevSliderPageEffectScrollAnim {

	const TYPE	= 'scrollanim';
	const BLOCK	= 'themepunch/sr7-scrollanim';
	// Blocks the effect can be switched on for (block extension — scrollanim.editor.js).
	const HOSTS	= ['core/paragraph', 'core/heading', 'core/list', 'core/quote', 'core/image', 'core/group',
					'core/cover', 'core/columns', 'core/buttons', 'core/media-text', 'core/gallery', 'core/video'];
	// Of those, the ones that carry text: only there is a split preset (lines / words / characters) offered,
	// because SplitText needs something to cut. Our own block counts as one — the author puts text inside it.
	const TEXT_HOSTS = ['core/paragraph', 'core/heading', 'core/list', 'core/quote'];

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
		// The player + GSAP + the curated presets for the block editor: the panel's tiles play the real
		// animation (priority 20 — the framework enqueues its shell, and tptools, at the default 10).
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

	/** Register Scroll Animation as a Page Effect TYPE with the framework. @return void */
	public static function register(){
		if(!class_exists('RevSliderPageEffects')) return;
		RevSliderPageEffects::register_type(self::TYPE, [
			'block'			=> self::BLOCK,
			'title'			=> __('Scroll Animation', 'revslider'),
			'icon'			=> 'controls-play',
			'spriteIcon'	=> 'MoveIn',
			'description'	=> __('Give any block a Slider Revolution layer animation — it plays when the block scrolls into view.', 'revslider'),
			'self_block'	=> true,			// the editor JS registers the block (inspector edit), not the generic card
			'claim'			=> ['attr' => 'sr7Anim', 'scope' => 'self', 'takes' => 'box'],	// the block's own box, not the picture in it — so an image effect may run alongside	// we animate the block itself — see SR7.PE.claimedBy
			'attributes'	=> self::attributes(),
			'supports'		=> ['align' => ['wide', 'full']],
			'storage'		=> 'attrs',			// config lives in the block attributes, not in post meta — see front_enqueue()
			'render'		=> [self::class, 'render'],
			'editor'		=> RS_PLUGIN_URL . 'admin/assets/js/scrollanim.editor.js?mt=' . self::mt('admin/assets/js/scrollanim.editor.js'),
			'runtime'		=> RS_PLUGIN_URL . 'public/js/scrollanim.pe.js?mt=' . self::mt('public/js/scrollanim.pe.js'),
			'sanitize'		=> [self::class, 'sanitize']
		]);
	}

	/** Block attribute schema (mirrored in scrollanim.editor.js so the dynamic block round-trips). @return array */
	public static function attributes(){
		return [
			'preset'	=> ['type' => 'string',	'default' => ''],		// "cat.preset" — a leaf of the "in" catalogue
			'variant'	=> ['type' => 'string',	'default' => ''],		// the preset's own axis value ("up", "strong", …)
			'speed'		=> ['type' => 'number',	'default' => 1],		// timeScale — the body carries the real durations
			'delay'		=> ['type' => 'number',	'default' => 0],		// ms after it comes into view
			'mode'		=> ['type' => 'string',	'default' => 'once'],	// once | down | up | both — see sanitize()
			'offset'	=> ['type' => 'number',	'default' => 15],		// % of the viewport it has to come in by
			// The entrance's CLOCK. 'trigger' plays it at its own speed once the block is far enough in;
			// 'scroll' hands the clock to the scroll position — the reveal runs under the reader's finger and
			// winds back when they go up. With that, speed / delay / mode have nothing left to say.
			'inMode'	=> ['type' => 'string',	'default' => 'trigger'],	// trigger | scroll
			'inSpan'	=> ['type' => 'number',	'default' => 50],		// % of the screen the scrubbed entrance runs over
			// Leaving again. Only ever emitted when 'mode' is not 'once' — a block that plays a single time
			// has no second act. See the out scene's own rules in animpreset.js (build → opt.scene).
			'outPreset'	=> ['type' => 'string',	'default' => ''],		// "cat.preset" — a leaf of the "out" catalogue
			'outVariant'=> ['type' => 'string',	'default' => ''],
			'outMode'	=> ['type' => 'string',	'default' => 'scroll'],	// scroll = along the leaving stretch | trigger = fire and forget
			'outAt'		=> ['type' => 'number',	'default' => 20],		// % of the viewport where the leaving stretch starts
			'devices'	=> ['type' => 'string',	'default' => 'all'],	// all | nomobile | desktop
			'stagger'	=> ['type' => 'number',	'default' => 0]			// ms between the block's own children, 0 = the block moves as one
		];
	}

	// Blocks that hold children worth staggering. Anywhere else the setting is not offered, because a
	// paragraph's "children" are its words — which is what the Text presets are for.
	const STAGGER_HOSTS = ['core/group', 'core/columns', 'core/buttons', 'core/gallery', 'core/list'];

	// =====================================================================================
	//  The catalogue side: the curated tiles, and the leaf a block's key points at.
	// =====================================================================================

	/**
	 * The presets the picker offers. NOT a copy of any preset data - a key, a name and the still the editor's own
	 * animation browser uses for that tile (presets.js _IMGLAYER). The variants are read from the catalogue at
	 * runtime (tile_list), so an axis added there shows up here without an edit.
	 *
	 * Curated rather than complete: the catalogue's 124 presets include families that need a module stage, a mask
	 * wrapper or an SVG path, and a tile that quietly does nothing is worse than no tile. Every entry below is
	 * measured by tools/anim-player/fidelity.mjs. 'x' marks a text preset, offered only where there is text.
	 * @return array
	 */
	public static function tiles($scene = 'in'){
		if($scene === 'out') return [
			// Leaving again. Fewer, on purpose: an out preset is over in a moment and nobody browses them the
			// way they browse an entrance. Mask families are left out for the same reason as above — no wrapper.
			['k' => 'basic.fade',			't' => __('Fade Out', 'revslider'),		'a' => 'layer-presets/basic/fade.jpg'],
			['k' => 'slide.shortdistance',	't' => __('Slide Away', 'revslider'),	'a' => 'layer-presets/movement/slde.jpg'],
			['k' => 'zoom.zoomout',			't' => __('Zoom Out', 'revslider'),		'a' => 'layer-presets/movement/zoom.jpg'],
			['k' => 'zoom.zoomin',			't' => __('Zoom In', 'revslider'),		'a' => 'layer-presets/movement/zoom.jpg'],
			['k' => 'rotate.turn',			't' => __('Turn', 'revslider'),			'a' => 'layer-presets/rotate/turn.jpg'],
			['k' => 'rotate.flip',			't' => __('Flip', 'revslider'),			'a' => 'layer-presets/rotate/flip.jpg'],
			['k' => 'lines.fade',			't' => __('Lines Fade', 'revslider'),	'x' => 'lines'],
			['k' => 'words.fade',			't' => __('Words Fade', 'revslider'),	'x' => 'words'],
			['k' => 'letter.fade',			't' => __('Letters Fade', 'revslider'),	'x' => 'chars']
		];
		return [
			// Motion — plays on the block as a whole, so it fits any of them.
			['k' => 'basic.fade',			't' => __('Fade', 'revslider'),			'a' => 'layer-presets/basic/fade.jpg'],
			['k' => 'cinematic.reveal',		't' => __('Reveal', 'revslider'),		'a' => 'layer-presets/cinematic/reveal.jpg'],
			['k' => 'cinematic.rise',		't' => __('Rise', 'revslider'),			'a' => 'layer-presets/cinematic/rise.jpg'],
			['k' => 'cinematic.glide',		't' => __('Glide', 'revslider'),		'a' => 'layer-presets/cinematic/glide.jpg'],
			['k' => 'cinematic.bounce',		't' => __('Bounce', 'revslider'),		'a' => 'layer-presets/cinematic/bounce.jpg'],
			['k' => 'cinematic.punch',		't' => __('Punch', 'revslider'),		'a' => 'layer-presets/cinematic/punch.jpg'],
			['k' => 'cinematic.fold',		't' => __('Fold', 'revslider'),			'a' => 'layer-presets/cinematic/fold.jpg'],
			['k' => 'cinematic.tilt',		't' => __('Tilt', 'revslider'),			'a' => 'layer-presets/cinematic/tilt.jpg'],
			['k' => 'cinematic.swing',		't' => __('Swing', 'revslider'),		'a' => 'layer-presets/cinematic/swing.jpg'],
			['k' => 'cinematic.overshoot',	't' => __('Overshoot', 'revslider'),	'a' => 'layer-presets/cinematic/overshoot.jpg'],
			['k' => 'cinematic.skewslide',	't' => __('Skew Slide', 'revslider'),	'a' => 'layer-presets/cinematic/skewslide.jpg'],
			['k' => 'cinematic.zoomblast',	't' => __('Zoom Blast', 'revslider'),	'a' => 'layer-presets/cinematic/zoomblast.jpg'],
			['k' => 'cinematic.vortex',		't' => __('Vortex', 'revslider'),		'a' => 'layer-presets/cinematic/vortex.jpg'],
			['k' => 'curve.arcin',			't' => __('Arc', 'revslider'),			'a' => 'layer-presets/curved-motion/arcin.jpg'],
			['k' => 'curve.swoop',			't' => __('Swoop', 'revslider'),		'a' => 'layer-presets/curved-motion/swoop.jpg'],
			['k' => 'curve.whip',			't' => __('Whip', 'revslider'),			'a' => 'layer-presets/curved-motion/whip.jpg'],
			['k' => 'curve.hook',			't' => __('Hook', 'revslider'),			'a' => 'layer-presets/curved-motion/hook.jpg'],
			['k' => 'curve.drift',			't' => __('Drift', 'revslider'),		'a' => 'layer-presets/curved-motion/drift.jpg'],
			['k' => 'rotate.flip',			't' => __('Flip', 'revslider'),			'a' => 'layer-presets/rotate/flip.jpg'],
			['k' => 'rotate.turn',			't' => __('Turn', 'revslider'),			'a' => 'layer-presets/rotate/turn.jpg'],
			['k' => 'rotate.smash',			't' => __('Smash', 'revslider'),		'a' => 'layer-presets/rotate/smash.jpg'],
			['k' => 'zoom.zoomin',			't' => __('Zoom In', 'revslider'),		'a' => 'layer-presets/movement/zoom.jpg'],
			['k' => 'slide.shortdistance',	't' => __('Slide', 'revslider'),		'a' => 'layer-presets/movement/slde.jpg'],
			// Text — the block's own words, lines or characters carry the animation. No artwork for these in
			// the editor either: its browser puts SAMPLE TEXT on the tile (presets.js _SAMPLE), and so do we.
			['k' => 'lines.fade',			't' => __('Lines Fade', 'revslider'),	'x' => 'lines'],
			['k' => 'words.fade',			't' => __('Words Fade', 'revslider'),	'x' => 'words'],
			['k' => 'words.popin',			't' => __('Words Pop In', 'revslider'),	'x' => 'words'],
			['k' => 'letter.fade',			't' => __('Letters Fade', 'revslider'),	'x' => 'chars'],
			['k' => 'letter.zoomchars',		't' => __('Letters Zoom', 'revslider'),	'x' => 'chars'],
			['k' => 'kinetic.headline',		't' => __('Kinetic Headline', 'revslider'), 'x' => 'words']
		];
	}

	/**
	 * The whole "in" catalogue, decoded once per request. Only ever loaded on a page that actually holds the
	 * effect (front_enqueue bails before this) or in the block editor.
	 * @return array
	 */
	private static function catalog($scene = 'in'){
		static $all = null;
		if($all === null){
			$all = [];
			//RevSliderFunctions, not its RevSliderData parent: the catalogue getters call get_val(), which the child defines
			if(class_exists('RevSliderFunctions')){
				$data = new RevSliderFunctions();
				$all = $data->get_layer_animations();
			}
		}
		$scene = ($scene === 'out') ? 'out' : 'in';
		return (isset($all[$scene]) && is_array($all[$scene])) ? $all[$scene] : [];
	}

	// Keys of a multi preset that name something other than a variant.
	private static function meta_keys(){
		return ['multi', 'demo', 'icon', 'name', 'group', 'extend', 'roles'];
	}

	/**
	 * The catalogue leaf a stored key points at. A preset whose variant is gone falls back to the preset's
	 * first one — a renamed axis should still animate — but a preset that is gone resolves to null, and
	 * nothing is emitted at all (the block then renders untouched, which is the point).
	 * @return array|null
	 */
	public static function resolve($preset, $variant, $scene = 'in'){
		$parts = explode('.', (string)$preset);
		if(count($parts) !== 2) return null;

		$cat = self::catalog($scene);
		$node = isset($cat[$parts[0]][$parts[1]]) ? $cat[$parts[0]][$parts[1]] : null;
		if(!is_array($node)) return null;
		if(empty($node['multi'])) return $node;   // single-variant preset — the leaf IS the body

		if(isset($node[$variant]) && is_array($node[$variant])) return $node[$variant];
		foreach($node as $k => $v){
			if(!in_array($k, self::meta_keys(), true) && is_array($v)) return $v;
		}
		return null;
	}

	/**
	 * The curated tiles with the variants each one actually has, read out of the catalogue. Dropping the
	 * do-nothing variants ("noanim") keeps a tile from offering "…and then it doesn't animate".
	 * @return array
	 */
	public static function tile_list($scene = 'in'){
		$cat = self::catalog($scene);
		$out = [];
		foreach(self::tiles($scene) as $t){
			$parts = explode('.', $t['k']);
			$node = isset($cat[$parts[0]][$parts[1]]) ? $cat[$parts[0]][$parts[1]] : null;
			if(!is_array($node)) continue;   // the catalogue moved on and this tile no longer exists

			$vars = [];
			if(!empty($node['multi'])){
				foreach($node as $k => $v){
					if(in_array($k, self::meta_keys(), true) || !is_array($v)) continue;
					if($k === 'noanim' || $k === 'none') continue;
					$vars[] = $k;
				}
				if(empty($vars)) continue;
			}
			$t['v'] = $vars;   // empty = the preset has no axis at all
			$out[] = $t;
		}
		return $out;
	}

	/**
	 * Whitelist the block attributes into the payload the runtime plays, and RESOLVE the preset key into the
	 * catalogue body while we are at it — that resolution is the whole reference model, and it belongs here
	 * (one place, on both the front and the editor preview path).
	 * @return array
	 */
	public static function sanitize($a){
		if(!is_array($a)) $a = [];

		// "cat.preset" — the catalogue's own key shape; sanitize_key would eat the dot
		$preset = preg_replace('/[^a-z0-9.]/', '', strtolower((string)($a['preset'] ?? '')));
		$variant = sanitize_key((string)($a['variant'] ?? ''));
		if($preset === '' || strlen($preset) > 48) return [];

		$body = self::resolve($preset, $variant);
		if(empty($body)) return [];   // renamed or removed → emit nothing, and the block stays as it is

		// once = the first time and never again · down/up = every time it is entered FROM that direction
		// (entered from the other one it is simply there, at rest) · both = every time.
		$mode = sanitize_key((string)($a['mode'] ?? ''));
		if(!in_array($mode, ['once', 'down', 'up', 'both'], true)){
			// blocks saved before the modes existed carried a plain "play once" switch
			$mode = (isset($a['once']) && empty($a['once'])) ? 'both' : 'once';
		}

		$inMode = (sanitize_key((string)($a['inMode'] ?? '')) === 'scroll') ? 'scroll' : 'trigger';

		$devices = sanitize_key((string)($a['devices'] ?? ''));
		if(!in_array($devices, ['all', 'nomobile', 'desktop'], true)) $devices = 'all';

		$data = [
			'body'		=> $body,
			'speed'		=> max(0.25, min(4, round(floatval($a['speed'] ?? 1), 2))),
			'delay'		=> max(0, min(5000, intval($a['delay'] ?? 0))),
			'mode'		=> $mode,
			'offset'	=> max(0, min(50, intval($a['offset'] ?? 15))),
			'inMode'	=> $inMode,
			// Under 10% the whole entrance would be over in a tenth of a screen, which reads as a jump rather
			// than a movement; over 90% it can outrun the page on the last block.
			'inSpan'	=> max(10, min(90, intval($a['inSpan'] ?? 50))),
			'devices'	=> $devices,
			'stagger'	=> max(0, min(600, intval($a['stagger'] ?? 0)))
		];

		// The exit. Only for a block that is allowed to play more than once — an out on a "once" block would
		// take the content away and never bring it back.
		$outPreset = preg_replace('/[^a-z0-9.]/', '', strtolower((string)($a['outPreset'] ?? '')));
		// A scrubbed entrance is a function of position, not an event — "plays once" cannot apply to it, so the
		// exit is on offer there whatever `mode` says.
		if(($mode !== 'once' || $inMode === 'scroll') && $outPreset !== '' && strlen($outPreset) <= 48){
			$outBody = self::resolve($outPreset, sanitize_key((string)($a['outVariant'] ?? '')), 'out');
			if(!empty($outBody)){
				$outMode = sanitize_key((string)($a['outMode'] ?? ''));
				$data['out'] = [
					'body'	=> $outBody,
					// scroll = the timeline is driven by how far the block has travelled out, so it can never be
					// finished while the block is still on screen. trigger = it plays at its own speed once the
					// mark is passed, which is snappier and can outrun a slow reader. Krisztian wanted both.
					'mode'	=> ($outMode === 'trigger') ? 'trigger' : 'scroll',
					'at'	=> max(0, min(90, intval($a['outAt'] ?? 20)))
				];
			}
		}

		return $data;
	}

	/**
	 * Block editor: GSAP + the player, so a tile plays the very animation the front will run, plus the curated
	 * list and the BODIES behind it — the panel resolves nothing itself, it is handed what PHP resolved.
	 * @return void
	 */
	public static function editor_assets(){
		if(!wp_script_is(RevSliderPageEffects::SHELL_HANDLE, 'enqueued')) return;   // no page effects on this screen
		// '_tpt' is the handle the framework enqueues tptools under — tpgsap writes into that namespace.
		wp_enqueue_script('sr7-tpgsap', RS_PLUGIN_URL . 'public/js/libs/tpgsap.js', ['_tpt'], RS_REVISION, true);
		wp_enqueue_script('sr7-animpreset', RS_PLUGIN_URL . 'public/js/animpreset.js', ['_tpt'], self::mt('public/js/animpreset.js'), true);

		$tiles = $bodies = [];
		foreach(['in', 'out'] as $scene){
			$tiles[$scene] = self::tile_list($scene);
			$cat = self::catalog($scene);
			foreach($tiles[$scene] as $t){
				$parts = explode('.', $t['k']);
				$node = $cat[$parts[0]][$parts[1]] ?? null;
				if(!is_array($node)) continue;
				if(empty($t['v'])){ $bodies[$scene][$t['k']] = ['' => $node]; continue; }
				foreach($t['v'] as $v) if(isset($node[$v])) $bodies[$scene][$t['k']][$v] = $node[$v];
			}
		}

		wp_localize_script(RevSliderPageEffects::SHELL_HANDLE . '-' . self::TYPE, 'SR7ScrollAnimEd', [
			'url'			=> RS_PLUGIN_URL . 'admin/assets/images/presets/',
			'tiles'			=> $tiles,
			'bodies'		=> $bodies,
			'textHosts'		=> self::TEXT_HOSTS,
			'staggerHosts'	=> self::STAGGER_HOSTS,
			'block'			=> self::BLOCK
		]);
	}

	/** The block card inside the editor iframe (the SR7 Page Effect family look). The badge on a host block is
	 *  the shell's — see RevSliderPageEffects::badge_canvas_styles(). @return array */
	public static function editor_canvas_styles($settings){
		$css = '.sr7-scrollanim-block{position:relative}'
			. '.sr7-sa-edit{border:1px dashed #d9ccff;border-radius:0;padding:6px}'
			. '.sr7-sa-edit-head{display:flex;align-items:center;gap:10px;margin-bottom:8px;font-size:13px}'
			. '.sr7-sa-edit-meta{color:#646970;flex:1;font-size:13px}';
		if(!isset($settings['styles']) || !is_array($settings['styles'])) $settings['styles'] = [];
		$settings['styles'][] = ['css' => $css];
		return $settings;
	}

	/**
	 * Front: enqueue the runtime bootstrap + the player + the adapter, and emit each block's RESOLVED config as
	 * x.
	 * @return void
	 */
	public static function front_enqueue(){
		if(is_admin() || !is_singular()) return;
		$post = RevSliderPageEffects::queried_post();   // NOT get_post($id) — that misses a preview, see there
		if(!$post) return;

		$blocks = self::collect_effects(parse_blocks($post->post_content));
		if(empty($blocks)) return;

		$payloads = [];
		$split = false;
		foreach($blocks as $eid => $a){
			$data = self::sanitize($a);
			if(empty($data)) continue;
			$payloads[$eid] = $data;
			foreach(['lines', 'words', 'chars'] as $s){
				if(isset($data['body'][$s]) || isset($data['out']['body'][$s])) $split = true;
			}
		}
		if(empty($payloads)) return;

		$ver		= RS_REVISION;
		$rtHandle	= RevSliderPageEffects::RT_HANDLE;

		// tptools carries the resource loader the adapter drives to pull GSAP; the same handle core's own
		// front uses, so a page that ALSO holds a module does not load it twice.
		wp_enqueue_script('tp-tools', RS_PLUGIN_URL . 'public/js/libs/tptools.js', [], $ver, true);
		wp_enqueue_script($rtHandle, RS_PLUGIN_URL . 'public/js/page-effects.js', [], $ver, true);
		wp_enqueue_script('sr7-scrollanim-pe', RS_PLUGIN_URL . 'public/js/scrollanim.pe.js', [$rtHandle, 'tp-tools'], self::mt('public/js/scrollanim.pe.js'), true);
		// The two files the adapter loads through SR7's own loader once it is running.
		wp_localize_script('sr7-scrollanim-pe', 'SR7ScrollAnimPE', [
			'player'	=> RS_PLUGIN_URL . 'public/js/animpreset.js?ver=' . self::mt('public/js/animpreset.js'),
			'gsap'		=> RS_PLUGIN_URL . 'public/js/libs/tpgsap.js?ver=' . $ver
		]);

		// A split unit has to be a block-level box or a transform does nothing to it. SplitText writes the
		// display inline itself, but the engine's own pages carry these four rules (public/css/sr7.lp.css) and
		// the classes it hands out are the same — so a page effect brings them along rather than relying on
		// whichever the split library happens to set. Only emitted when a split preset is actually on the page.
		if($split){
			wp_register_style('sr7-scrollanim', false);
			wp_enqueue_style('sr7-scrollanim');
			wp_add_inline_style('sr7-scrollanim',
				'.sr7_splitted_lines{display:block;color:inherit;font-size:inherit;font-weight:inherit;line-height:inherit}'
				. '.sr7_splitted_words,.sr7_splitted_words_noanim,.sr7_splitted_chars{display:inline-block;color:inherit;font-size:inherit;font-weight:inherit;line-height:inherit}');
		}

		$js = 'window.SR7=window.SR7||{};SR7.E=SR7.E||{};SR7.E.pageEffects=SR7.E.pageEffects||{};';
		foreach($payloads as $eid => $data){
			$js .= 'SR7.E.pageEffects[' . wp_json_encode($eid) . ']=' . wp_json_encode(['type' => self::TYPE, 'data' => $data]) . ';';
		}
		wp_add_inline_script('sr7-scrollanim-pe', $js, 'before');
	}

	/**
	 * Recursively collect [effectId => attrs] for every Scroll Animation on the page:
	 *  - the dedicated themepunch/sr7-scrollanim block → its own attributes
	 *  - a host block with the effect switched on → its sr7Anim attributes
	 * @return array
	 */
	private static function collect_effects($blocks){
		$out = [];
		foreach((array)$blocks as $b){
			$name = isset($b['blockName']) ? $b['blockName'] : '';
			if($name === self::BLOCK && !empty($b['attrs']['effectId'])){
				$eid = sanitize_key($b['attrs']['effectId']);
				if($eid !== '') $out[$eid] = $b['attrs'];
			}else if(in_array($name, self::HOSTS, true) && !empty($b['attrs']['sr7Anim'])){
				$sa = $b['attrs']['sr7Anim'];
				if(is_array($sa) && !empty($sa['enabled']) && !empty($sa['effectId'])){
					$eid = sanitize_key($sa['effectId']);
					if($eid !== '') $out[$eid] = $sa;
				}
			}
			if(!empty($b['innerBlocks'])) $out += self::collect_effects($b['innerBlocks']);
		}
		return $out;
	}

	/**
	 * Mark a host block that has the effect switched on so the runtime can find it by [data-sr7pe] and animate
	 * the markup it already rendered. Pre-JS the block renders normally — and that is also what a visitor sees
	 * if the scripts never arrive, because nothing here hides anything.
	 * @return string
	 */
	public static function wrap_host($html, $block){
		if(empty($block['blockName']) || !in_array($block['blockName'], self::HOSTS, true)) return $html;

		// A rich-text block IS its own editable element, so an editor badge portalled into it became part of the TEXT
		// and was saved with the post. The editor no longer does that; this takes the leftovers back out of content
		// saved before the fix. Both names: this effect's own badge, and the shell's that replaced it.
		if(strpos($html, 'sr7-sa-hostbadge') !== false || strpos($html, 'sr7pe-badge') !== false){
			$html = preg_replace('#<button[^>]*(?:sr7-sa-hostbadge|sr7pe-badge)[^>]*>.*?</button>#is', '', $html);
		}

		$sa = isset($block['attrs']['sr7Anim']) ? $block['attrs']['sr7Anim'] : null;
		if(!is_array($sa) || empty($sa['enabled']) || empty($sa['effectId'])) return $html;
		return RevSliderPageEffects::tag_host($html, $sa['effectId'], 'sr7-sa sr7-scrollanim-host');
	}

	/**
	 * Front markup for the dedicated block: its inner blocks in a box the effect owns. Pre-JS (and with JS
	 * off) that is the content, plain and visible.
	 * @return string
	 */
	public static function render($attributes, $content = '', $block = null){
		$a	 = is_array($attributes) ? $attributes : [];
		$eid = isset($a['effectId']) ? sanitize_key($a['effectId']) : '';
		if($eid === '') return $content;

		$align = (isset($a['align']) && in_array($a['align'], ['wide', 'full'], true)) ? ' align' . $a['align'] : '';

		return '<div class="sr7-sa sr7-scrollanim' . $align . '" id="sr7sa-' . esc_attr($eid) . '" data-sr7pe="' . esc_attr($eid) . '">'
			. $content
			. '</div>';
	}
}
