/*
╔══════════════════════════════════════════════════════════════════════════════╗
║  PLUGIN:   SLIDER REVOLUTION 7                                                ║
║  MODULE:   PAGE EFFECTS  —  editor shell + registry (core)                    ║
║  AUTHOR:   ThemePunch                                                         ║
╚══════════════════════════════════════════════════════════════════════════════╝

  Provides: the generic "page effect" Gutenberg block (delegates its card to the effect type),
  and the on-page Recorder shell (full-screen overlay, live-page iframe stage, top bar with
  Save/Close + device tabs, a left panel for the effect tool, and an element-bind picker).
  Each addon registers SR7.PE.registerType(type, { card?, tool }); the tool owns its own panel UI,
  its iframe overlay, and load()/collect(). Save/load go through the framework AJAX (sr7_pe_*).
  Also hosts the shared responsive engine, the skin (--pe-* tokens), and the device switcher.

  @copyright 2026 ThemePunch
*/
(() => {
	"use strict";

	window.SR7 ??= {};
	SR7.PE ??= {};
	SR7.PE.types ??= {};

	const CFG = window.SR7PECfg || { types: {} };

	SR7.PE.registerType = (type, def) => { SR7.PE.types[type] = def || {}; };

	// =====================================================================================
	//  Responsive engine (mirror of public/js/page-effects.js — the editor page does not load
	//  the front bootstrap, so the same tiny device model + gVal-style #a resolver lives here too).
	//  Reuses SR7.D.devices when present (the SR7 engine isn't loaded in the block editor).
	// =====================================================================================
	SR7.PE.devices ??= (window.SR7 && SR7.D && Array.isArray(SR7.D.devices)) ? SR7.D.devices : ["wdesktop", "desktop", "notebook", "tablet", "mobile"];
	SR7.PE.deviceWidths ??= [1600, 1240, 1024, 778, 480];   // design widths — mirror core layer-size defaults (defaults.js)
	SR7.PE.deviceLabels ??= ["Wide", "Desktop", "Notebook", "Tablet", "Mobile"];
	SR7.PE.levelForWidth ??= (w) => { const d = SR7.PE.deviceWidths; for (let i = d.length - 1; i >= 0; i--) if (w <= d[i]) return i; return 0; };
	SR7.PE.lev ??= (field, level) => {
		if (!Array.isArray(field)) return field;
		let v = field[level];
		if (v != null && v !== "#a" && v !== "") return v;
		for (let i = level - 1; i >= 0; i--) { v = field[i]; if (v != null && v !== "#a" && v !== "") return v; }
		for (let i = level + 1; i < 5; i++) { v = field[i]; if (v != null && v !== "#a" && v !== "") return v; }
		return 0;
	};
	// write a per-level value, lazily promoting a legacy scalar to a 5-level array (scalar → base on
	// wide desktop, "#a" elsewhere) so editing one device cascades down to narrower ones, like SR7.
	SR7.PE.setLev = (obj, key, level, val) => {
		let f = obj[key];
		if (!Array.isArray(f)) { f = [f == null ? "#a" : f, "#a", "#a", "#a", "#a"]; obj[key] = f; }
		f[level] = val;
		return f;
	};

	// =====================================================================================
	//  Page Effects skin — ONE design language for every effect's authoring UI. Derived from the
	//  SR7 admin DARK theme tokens (base.css .sr-dark-theme + forms.css sr-drop-view / sr-slider):
	//  Inter + BLUE accent (--sr-col-b0 #309BFF) + SR7 dark surfaces + 10/4px radii, so the recorder
	//  reads as part of the SR7 family while Page Effects keeps its own structure. Exposed both as
	//  CSS custom props (--pe-*, on .sr7pe-overlay) and as a JS object (for SVG/canvas drawing colors).
	// =====================================================================================
	// Two palettes taken straight from SR7's own themes so the recorder shares the SAME color families as the
	// SR7 editor: DARK = base.css .sr-dark-theme tokens (verbatim), LIGHT = the :root (light) counterparts of
	// the same tokens. Shared bits (Inter, brand blue, radii) live in skinBase. The active palette is picked by
	// the SR7 dark/light cookie (sr7_colortheme via SR7.B.darkLightSwitch), DEFAULT LIGHT.
	SR7.PE.skinBase ??= {
		font: "'Inter', -apple-system, BlinkMacSystemFont, system-ui, sans-serif",
		accent: "#309BFF", accentStrong: "#5597FC", accentText: "#FFFFFF", good: "#008A6B", danger: "#FF6B6B",
		radius: "10px", radiusS: "4px"
	};
	SR7.PE.skins ??= {
		dark: {
			// verbatim SR7 dark tokens: text = --sr-col-w0-d0, control surface = --sr-col-tr-d0, border = --sr-col-w6-d0
			bg: "#1C1E20", surface: "#2A2C2F", surface2: "#323438", surface3: "#383B41",
			border: "#3F444A", border2: "#4A4E51", text: "#B7BBC0", text2: "#AAAEB3", textDim: "#777C80",
			accentBg: "rgba(48,155,255,.16)", shadow: "0 10px 30px rgba(0,0,0,.35)", stage: "#0b0d10", dropSel: "#20374D"
		},
		light: {
			bg: "#f5f5f5", surface: "#ffffff", surface2: "#F9F9F9", surface3: "#f0f0f1",
			border: "#dddddd", border2: "#cccccc", text: "#1a1a1a", text2: "#555555", textDim: "#777777",
			accentBg: "rgba(48,155,255,.14)", shadow: "0 8px 28px rgba(0,0,0,.14)", stage: "#eeeeee", dropSel: "#e6f1fc"
		}
	};
	// active theme from the SR7 cookie (default light); resolveSkin exposes the flat SR7.PE.skin used everywhere
	// (CSS var build + SVG/canvas drawing colors) so existing readers keep working.
	SR7.PE.theme ??= () => {
		const c = (window._tpt && _tpt.cookie && _tpt.cookie.get) ? (_tpt.cookie.get("sr7_colortheme") || "") : "";
		return String(c).indexOf("dark") !== -1 ? "dark" : "light";
	};
	SR7.PE.resolveSkin ??= () => (SR7.PE.skin = Object.assign({}, SR7.PE.skinBase, SR7.PE.skins[SR7.PE.theme()]));
	SR7.PE.resolveSkin();

	SR7.PE.injectSkin = () => {
		SR7.PE.resolveSkin();                 // pick dark/light from the SR7 cookie (may have changed since last call)
		const k = SR7.PE.skin;
		const vars = "--pe-font:" + k.font + ";--pe-bg:" + k.bg + ";--pe-surface:" + k.surface + ";--pe-surface-2:" + k.surface2 +
			";--pe-surface-3:" + k.surface3 + ";--pe-border:" + k.border + ";--pe-border-2:" + k.border2 +
			";--pe-text:" + k.text + ";--pe-text-2:" + k.text2 + ";--pe-text-dim:" + k.textDim +
			";--pe-accent:" + k.accent + ";--pe-accent-strong:" + k.accentStrong + ";--pe-accent-bg:" + k.accentBg +
			";--pe-accent-text:" + k.accentText + ";--pe-good:" + k.good + ";--pe-danger:" + k.danger +
			";--pe-radius:" + k.radius + ";--pe-radius-s:" + k.radiusS + ";--pe-shadow:" + k.shadow +
			";--pe-stage:" + k.stage + ";--pe-drop-sel:" + k.dropSel + ";";
		// theme vars go on :root and are RE-APPLIED every call, so a dark/light switch takes effect and the
		// drop list (appended to <body>, outside .sr7pe-overlay) inherits the active theme rather than a literal.
		let v = document.getElementById("sr7pe-skin-vars");
		if (!v) { v = document.createElement("style"); v.id = "sr7pe-skin-vars"; document.head.appendChild(v); }
		v.textContent = ":root{" + vars + "}";
		if (document.getElementById("sr7pe-skin")) return;   // structural component CSS injected once (uses the vars)
		const s = document.createElement("style"); s.id = "sr7pe-skin";
		s.textContent =
			".sr7pe-overlay{position:fixed;inset:0;z-index:100000;display:flex;flex-direction:column;background:var(--pe-bg);font-family:var(--pe-font);-webkit-font-smoothing:antialiased}" +
			".sr7pe-bar{flex:0 0 52px;display:flex;align-items:center;gap:12px;padding:0 16px;background:var(--pe-surface);border-bottom:1px solid var(--pe-border);color:var(--pe-text);position:relative}" +
			".sr7pe-brand{display:flex;align-items:center;gap:9px;font-size:14px;font-weight:600;color:var(--pe-text)}" +
			".sr7pe-logo{display:flex;flex:0 0 auto}.sr7pe-logo svg{display:block;width:24px;height:24px}" +
			".sr7pe-brand em{font-style:normal;color:var(--pe-text-dim);font-weight:400}" +
			// device switcher — centred in the top bar (same icon row as Quick Edit)
			".sr7pe-devices{position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);display:flex;align-items:center;gap:4px;background:none;border:0;border-radius:0;padding:0;box-shadow:none}" +
			".sr7pe-device{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border:0;background:transparent;border-radius:4px;box-shadow:none;color:var(--pe-text-2);cursor:pointer;padding:0;transition:color .12s,background .12s}" +
			".sr7pe-device:hover{background:var(--pe-surface-3);color:var(--pe-text)}" +
			".sr7pe-device.sr7pe-device--active{background:transparent;color:var(--pe-accent)}" +
			".sr7pe-device svg{display:block;pointer-events:none}" +
			".sr7pe-spacer{flex:1}" +
			".sr7pe-btn{background:var(--pe-surface-2);color:var(--pe-text);border:1px solid var(--pe-border-2);border-radius:var(--pe-radius-s);padding:7px 15px;font:500 13px var(--pe-font);cursor:pointer;transition:.13s}" +
			".sr7pe-btn:hover{border-color:var(--pe-text-dim);color:var(--pe-text)}" +
			".sr7pe-btn--primary{background:var(--pe-accent);border-color:var(--pe-accent);color:var(--pe-accent-text)}" +
			".sr7pe-btn--primary:hover{background:var(--pe-accent-strong);border-color:var(--pe-accent-strong);color:#fff}" +
			".sr7pe-btn--saved{background:var(--pe-good);border-color:var(--pe-good);color:#fff}" +
			".sr7pe-file{position:absolute;width:1px;height:1px;opacity:0;overflow:hidden;clip:rect(0,0,0,0)}" +
			".sr7pe-body{flex:1;display:flex;min-height:0}" +
			".sr7pe-panel{width:308px;flex:0 0 308px;background:var(--pe-surface-2);border-right:1px solid var(--pe-border);overflow:auto;color:var(--pe-text);font-size:13px}" +
			".sr7pe-stage{flex:1;position:relative;background:var(--pe-stage) repeating-linear-gradient(45deg,transparent,transparent 10px,rgba(128,128,128,.06) 10px,rgba(128,128,128,.06) 20px);display:flex;justify-content:center;align-items:stretch;overflow:auto}" +
			".sr7pe-iframe{height:100%;border:0;display:block;background:#fff;box-shadow:var(--pe-shadow);transition:width .18s ease}" +

			// ---- SR7 dark-editor form components, EXTRACTED from sr-drop-view / sr-slider / sr-onoff and
			//      SCOPED to .sr7pe-panel so they reproduce the editor look WITHOUT the editor's component
			//      JS and WITHOUT leaking into Gutenberg's own controls. (User: "extract and guard".)
			".sr7pe-panel,.sr7pe-panel *{box-sizing:border-box}" +
			// dropdown — like sr-drop-view (#383B41 / 1px #4A4E51 / r4 / 13px-600), native arrow replaced
			".sr7pe-panel select{-webkit-appearance:none;-moz-appearance:none;appearance:none;font:600 13px/26px var(--pe-font);height:28px;color:var(--pe-text);background-color:var(--pe-surface-3);border:1px solid var(--pe-border-2);border-radius:var(--pe-radius-s);padding:0 26px 0 9px;cursor:pointer;background-image:url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'><path d='M1 1l4 4 4-4' fill='none' stroke='%23AAAEB3' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/></svg>\");background-repeat:no-repeat;background-position:right 9px center}" +
			".sr7pe-panel select:hover{border-color:var(--pe-accent)}" +
			".sr7pe-panel select:focus{outline:none;border-color:var(--pe-accent);box-shadow:0 0 0 2px var(--pe-accent-bg)}" +
			".sr7pe-panel option{background:var(--pe-surface-2);color:var(--pe-text)}" +
			// custom dropdown (SR7.PE.dropField) — matches the editor's sr-drop EXACTLY (closed + open),
			// which a native <select> can't. Closed: bold VALUE left (the input), muted label right,
			// 10x6 chevron far right, 28px/lh26. Open list: dark panel, blue selected/hover on #20374D.
			".sr7pe-drop{position:relative;display:block;height:28px;margin:0 16px 8px;border:1px solid var(--pe-border-2);border-radius:var(--pe-radius-s);background:var(--pe-surface-3);padding:0 8px;cursor:pointer;font:600 13px/26px var(--pe-font);color:var(--pe-text);box-sizing:border-box}" +
			".sr7pe-drop:hover,.sr7pe-drop.open{border-color:var(--pe-accent)}" +
			".sr7pe-drop.disabled{opacity:.5;cursor:default;color:var(--pe-text-dim);border-color:var(--pe-border)}" +
			".sr7pe-drop.off{display:none}" +
			".sr7pe-drop-value{display:block;margin-right:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;line-height:26px}" +
			".sr7pe-drop-title{position:absolute;right:24px;top:0;line-height:28px;font-size:11px;font-weight:400;color:var(--pe-text-dim);pointer-events:none;max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}" +
			".sr7pe-drop-arrow{position:absolute;right:8px;top:0;height:28px;display:flex;align-items:center;color:var(--pe-text-dim);pointer-events:none}" +
			// NOTE: the open list is appended to <body> (outside .sr7pe-overlay) so it must NOT rely on the
			// --pe-* vars cascading — every value carries a literal fallback (like the colorpicker popover).
			".sr7pe-drop-list{position:fixed;z-index:100030;background:var(--pe-surface-2,#323438);border:1px solid var(--pe-border,#3F444A);border-radius:var(--pe-radius-s,4px);box-shadow:var(--pe-shadow,0 10px 30px rgba(0,0,0,.45));padding:5px;max-height:280px;overflow:auto;box-sizing:border-box;font-family:var(--pe-font,'Inter',system-ui,sans-serif)}" +
			".sr7pe-drop-opt{padding:8px 12px;border-radius:4px;font-size:13px;font-weight:500;color:var(--pe-text,#B7BBC0);cursor:pointer;white-space:nowrap}" +
			".sr7pe-drop-opt:hover,.sr7pe-drop-opt.selected{color:var(--pe-accent,#309BFF);background:var(--pe-drop-sel,#20374D)}" +
			// text / number input — same surface as dropdown
			".sr7pe-panel input[type=text],.sr7pe-panel input[type=number]{font:600 13px/24px var(--pe-font);color:var(--pe-text);background:var(--pe-surface-3);border:1px solid var(--pe-border-2);border-radius:var(--pe-radius-s);padding:2px 9px;height:28px}" +
			".sr7pe-panel input[type=text]:focus,.sr7pe-panel input[type=number]:focus{outline:none;border-color:var(--pe-accent)}" +
			// color swatch
			".sr7pe-panel input[type=color]{-webkit-appearance:none;-moz-appearance:none;appearance:none;width:40px;height:26px;border:1px solid var(--pe-border-2);border-radius:var(--pe-radius-s);background:none;cursor:pointer;padding:2px}" +
			".sr7pe-panel input[type=color]::-webkit-color-swatch-wrapper{padding:0}.sr7pe-panel input[type=color]::-webkit-color-swatch{border:none;border-radius:2px}" +
			// range — thin track + blue fill (sr-slider used --sr-col-b0); accent-color gives the fill natively
			".sr7pe-panel input[type=range]{-webkit-appearance:none;appearance:none;width:130px;height:14px;background:transparent;accent-color:var(--pe-accent);cursor:pointer}" +
			".sr7pe-panel input[type=range]::-webkit-slider-runnable-track{height:4px;border-radius:9999px;background:var(--pe-surface-3)}" +
			".sr7pe-panel input[type=range]::-moz-range-track{height:4px;border-radius:9999px;background:var(--pe-surface-3)}" +
			".sr7pe-panel input[type=range]::-webkit-slider-thumb{-webkit-appearance:none;width:13px;height:13px;margin-top:-5px;border-radius:50%;background:#fff;border:2px solid var(--pe-text-dim);box-shadow:0 1px 2px rgba(0,0,0,.4)}" +
			".sr7pe-panel input[type=range]::-moz-range-thumb{width:13px;height:13px;border-radius:50%;background:#fff;border:2px solid var(--pe-text-dim)}" +
			// checkbox → SR7 pill toggle (sr-onoff look): grey off, blue on, white knob
			".sr7pe-panel input[type=checkbox]{-webkit-appearance:none;-moz-appearance:none;appearance:none;box-sizing:border-box;vertical-align:middle;width:34px;height:18px;border-radius:9999px;background:var(--pe-border-2);position:relative;cursor:pointer;transition:.15s;flex:0 0 auto;margin:0}" +
			".sr7pe-panel input[type=checkbox]:checked{background:var(--pe-accent)}" +
			// kill WordPress's native checkmark (wp-core-ui injects a dashicon tick via ::before in the block editor)
			".sr7pe-panel input[type=checkbox]:before,.sr7pe-panel input[type=checkbox]:checked:before{content:none!important;display:none!important}" +
			// SR7 sr-onoff look: white sliding knob, grey off / blue on
			".sr7pe-panel input[type=checkbox]:after{content:'';position:absolute;width:14px;height:14px;border-radius:50%;background:#fff;top:50%;margin-top:-7px;left:2px;transition:left .15s;box-shadow:0 1px 2px rgba(0,0,0,.35)}" +
			".sr7pe-panel input[type=checkbox]:checked:after{left:18px}";
		document.head.appendChild(s);
	};

	// SR7 brand mark (the same SR mark the revslider Gutenberg block uses) — reused for the Page-Effect
	// inspector header so every effect's settings panel reads like the SR7 module block (one house).
	SR7.PE.logoSVG ??= "<svg viewBox='0 0 36 36' xmlns='http://www.w3.org/2000/svg' width='26' height='26'><g transform='translate(8 7.493)'><rect width='36' height='36' rx='5' transform='translate(-8 -7.493)' fill='#5c24ff'/><path d='M46.311,18.755l2.807,2.81a.2.2,0,0,1-.14.337H40.089a.121.121,0,0,1-.137-.137l-.006-8.912a.188.188,0,0,1,.322-.134l2.938,2.933a.109.109,0,0,0,.185-.006A6.5,6.5,0,0,0,43.9,7.7a6.271,6.271,0,0,0-5.255-2.944q-.123.009-.123-.117l0-3.991a.121.121,0,0,1,.148-.137c5.283.394,9.36,3.746,10.293,8.929a10.885,10.885,0,0,1-2.231,8.781.516.516,0,0,1-.168.145l-.211.114Q46.146,18.589,46.311,18.755Z' transform='translate(-28.331 -0.8)' fill='#fff'/><path d='M0,1.745V1.46l8.975.014a.124.124,0,0,1,.14.14l0,8.627a.251.251,0,0,1-.431.177L5.974,7.688a.133.133,0,0,0-.225.009C2.037,12.144,5,18.126,10.521,18.714a.168.168,0,0,1,.148.168v3.766a.123.123,0,0,1-.145.14A10.328,10.328,0,0,1,3.94,20.36Q-1.717,15.456.79,7.893A9.566,9.566,0,0,1,2.844,4.8a.151.151,0,0,0-.006-.234Z' transform='translate(-0.8 -1.481)' fill='#fff'/></g></svg>";

	// Inspector chrome for a Page-Effect block's settings panel. LIGHT-ONLY: the SR7 logo is placed onto the
	// panel's OWN title bar (so it stays one collapsible row with Gutenberg's native chevron), Inter on the
	// title, and the SR7 blue is fed into Gutenberg's controls via the SANCTIONED --wp-admin-theme-color var.
	// We ride Gutenberg's own controls (nothing of wp-components' internals restyled) so WP updates can't break
	// it — the logo is an additive ::before that just degrades to "no logo" if the panel markup ever changes.
	// (Dark was dropped: dark controls in a light editor read wrong and would mean chasing wp-components.)
	SR7.PE.injectInspectorChrome ??= () => {
		if (document.getElementById("sr7pe-inspector-css")) return;
		const k = SR7.PE.skin;
		const s = document.createElement("style"); s.id = "sr7pe-inspector-css";
		s.textContent =
			// The panel's brand icon is now provided per-effect via PanelBody's own `icon` prop (the SAME sprite
			// icon as the block), so it always matches the block — no generic SR-mark ::before here.
			".sr7pe-inspector .components-panel__body-title button{font-family:'Inter',-apple-system,BlinkMacSystemFont,system-ui,sans-serif;font-weight:600}" +
			".sr7pe-inspector .components-panel__body.is-opened h2{margin-bottom:8px}" +
			// Shared Page-Effect control polish (all PEs ride .sr7pe-inspector). Scoped so WP core UI and other
			// blocks are untouched; only STABLE semantic component classes (never the hashed emotion classes), no
			// !important → a future WP restructure just no-ops these back to defaults rather than breaking anything.
			// (1) No blue focus box on our controls (the PE accent is blue and reads wrong on the SR7 panels).
			// Buttons keep the keyboard ring (:focus-visible) for a11y; selects/inputs drop it unconditionally
			// because browsers keep them :focus-visible after a mouse pick, so the blue would otherwise persist.
			".sr7pe-inspector .components-button:focus:not(:focus-visible){box-shadow:none;outline:none}" +
			".sr7pe-inspector input:focus,.sr7pe-inspector select:focus,.sr7pe-inspector textarea:focus{box-shadow:none;outline:none}" +
			".sr7pe-inspector .components-input-base:focus-within .components-input-control__backdrop{box-shadow:none}" +
			// (2) Compact RangeControl: vertically centre the slider with its number field + shrink the 40px number
			// input. The height lives on the input's flex CONTAINER (setting it on the input alone is stretched back
			// by the flex), so shrink the container too — scoped to the RangeControl, so the slider is untouched.
			".sr7pe-inspector .components-range-control__root{align-items:center}" +
			".sr7pe-inspector .components-range-control .components-input-control,.sr7pe-inspector .components-range-control .components-input-base,.sr7pe-inspector .components-range-control .components-input-control__container{min-height:32px;height:32px}" +
			".sr7pe-inspector .components-range-control .components-input-control__input{min-height:32px;height:32px}" +
			".sr7pe-inspector{--wp-admin-theme-color:" + k.accent + ";--wp-admin-theme-color-darker-10:" + k.accentStrong + ";--wp-admin-theme-color-darker-20:" + k.accentStrong + "}";
		document.head.appendChild(s);
	};

	// =====================================================================================
	//  Dropdown — reusable custom control matching the SR7 editor's sr-drop (closed + open list).
	//  Built (not a native <select>) so the value-left/label-right layout and the styled option list
	//  match exactly. value object stored externally; opts = [[value,label],…].
	// =====================================================================================
	let OPEN_DROP = null;
	const closeDrop = () => {
		if (!OPEN_DROP) return;
		const o = OPEN_DROP; OPEN_DROP = null;
		document.removeEventListener("pointerdown", o.outside, true);
		document.removeEventListener("keydown", o.onKey, true);
		if (o.list.parentNode) o.list.remove();
		o.el.classList.remove("open");
	};
	SR7.PE.closeDropdown = closeDrop;

	SR7.PE.dropField = (cfg) => {
		cfg = cfg || {};
		let opts = cfg.options || [], val = cfg.value;
		const el = document.createElement("div"); el.className = "sr7pe-drop" + (cfg.disabled ? " disabled" : ""); el.tabIndex = 0;
		const valEl = document.createElement("span"); valEl.className = "sr7pe-drop-value";
		const titleEl = document.createElement("span"); titleEl.className = "sr7pe-drop-title"; titleEl.textContent = cfg.title || "";
		const arrow = document.createElement("span"); arrow.className = "sr7pe-drop-arrow";
		arrow.innerHTML = "<svg width='10' height='6' viewBox='0 0 10 6'><path d='M1 1l4 4 4-4' fill='none' stroke='currentColor' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/></svg>";
		el.appendChild(valEl); el.appendChild(titleEl); el.appendChild(arrow);

		const labelFor = (v) => { const o = opts.find(o => o[0] === v); return o ? o[1] : (v == null ? "" : v); };
		const paint = () => { valEl.textContent = labelFor(val); };
		const open = () => {
			if (el.classList.contains("disabled")) return;
			closeDrop();
			const list = document.createElement("div"); list.className = "sr7pe-drop-list";
			opts.forEach(o => {
				const it = document.createElement("div"); it.className = "sr7pe-drop-opt" + (o[0] === val ? " selected" : ""); it.textContent = o[1];
				it.addEventListener("pointerdown", (e) => { e.preventDefault(); val = o[0]; paint(); closeDrop(); if (cfg.onChange) cfg.onChange(val); });
				list.appendChild(it);
			});
			document.body.appendChild(list);
			const r = el.getBoundingClientRect(); list.style.left = r.left + "px"; list.style.width = r.width + "px";
			const lh = list.offsetHeight; let top = r.bottom + 4; if (top + lh > window.innerHeight - 8) top = Math.max(8, r.top - lh - 4);
			list.style.top = top + "px"; el.classList.add("open");
			const outside = (e) => { if (!list.contains(e.target) && !el.contains(e.target)) closeDrop(); };
			const onKey = (e) => { if (e.key === "Escape") closeDrop(); };
			document.addEventListener("pointerdown", outside, true); document.addEventListener("keydown", onKey, true);
			OPEN_DROP = { el, list, outside, onKey };
		};
		el.addEventListener("click", open);
		el.setValue = (v) => { val = v; paint(); };
		el.getValue = () => val;
		el.setDisabled = (d) => { el.classList.toggle("disabled", !!d); };
		el.setOptions = (o) => { opts = o || []; paint(); };
		paint();
		return el;
	};

	// =====================================================================================
	//  Icon picker — a searchable grid of icon-FONT glyphs from SR7's bundled fonts (Font Awesome,
	//  Pe-icon-7-stroke). We parse each font's CSS for class→codepoint ourselves (standalone: the SR7
	//  editor's SR7.B.iconUtils isn't guaranteed in this context). value = {family, code, cls, name};
	//  consumers render it as an SVG <text> glyph so it scales/rotates/colours like any stamp.
	// =====================================================================================
	SR7.PE.iconFonts = {
		FontAwesome:   { family: "FontAwesome", base: "fa", css: "font-awesome/css/font-awesome.css" },
		PeIcon7Stroke: { family: "Pe-icon-7-stroke", base: "", css: "pe-icon-7-stroke/css/pe-icon-7-stroke.css" }
	};
	const peFontsUrl = () => (window.SR7PECfg && SR7PECfg.fontsUrl) || (window.SR7 && SR7.E && SR7.E.peFontsUrl) || "";
	// inject the font-face CSS so the picker can DRAW the glyphs (and so SVG <text> can use the family)
	SR7.PE.ensureIconFonts = (doc) => {
		doc = doc || document; const base = peFontsUrl(); if (!base) return;
		for (const k in SR7.PE.iconFonts) {
			const id = "sr7pe-iconfont-" + k;
			if (!doc.getElementById(id)) { const l = doc.createElement("link"); l.id = id; l.rel = "stylesheet"; l.href = base + SR7.PE.iconFonts[k].css; doc.head.appendChild(l); }
		}
	};
	SR7.PE.loadIcons = async () => {
		if (SR7.PE._iconList) return SR7.PE._iconList;
		const base = peFontsUrl(), out = [];
		for (const k in SR7.PE.iconFonts) {
			const f = SR7.PE.iconFonts[k];
			try {
				const css = await fetch(base + f.css).then(r => r.text());
				const re = /\.([a-zA-Z0-9_-]+):before\s*\{\s*content:\s*["']\\([a-fA-F0-9]+)["']/g; let m;
				while ((m = re.exec(css))) out.push({ family: f.family, code: m[2], cls: (f.base ? f.base + " " : "") + m[1], name: m[1].replace(/^fa-|^pe-7s-/, "").replace(/-/g, " ") });
			} catch (e) {}
		}
		SR7.PE._iconList = out; return out;
	};
	let OPEN_ICONS = null;
	const closeIcons = () => { if (!OPEN_ICONS) return; const o = OPEN_ICONS; OPEN_ICONS = null; document.removeEventListener("pointerdown", o.outside, true); document.removeEventListener("keydown", o.onKey, true); if (o.pop.parentNode) o.pop.remove(); o.el.classList.remove("open"); };
	const injectIconCSS = () => {
		if (document.getElementById("sr7pe-icon-css")) return;
		const s = document.createElement("style"); s.id = "sr7pe-icon-css";
		s.textContent =
			".sr7pe-iconpop{position:fixed;z-index:100040;width:300px;background:#323438;border:1px solid #3F444A;border-radius:6px;box-shadow:0 10px 30px rgba(0,0,0,.45);padding:8px;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif}" +
			".sr7pe-iconpop input{width:100%;box-sizing:border-box;height:30px;background:#383B41;border:1px solid #4A4E51;border-radius:4px;color:#C7CCD2;padding:0 9px;font-size:13px;margin-bottom:8px;outline:none}" +
			".sr7pe-icongrid{display:grid;grid-template-columns:repeat(6,1fr);gap:2px;max-height:300px;overflow:auto}" +
			".sr7pe-iconbtn{display:flex;align-items:center;justify-content:center;height:38px;border-radius:4px;color:#C7CCD2;cursor:pointer;font-size:18px}" +
			".sr7pe-iconbtn:hover{background:#20374D;color:#309BFF}.sr7pe-iconbtn.sel{background:#309BFF;color:#fff}" +
			".sr7pe-iconempty{padding:14px;color:#777C80;font-size:12px;text-align:center}";
		document.head.appendChild(s);
	};
	SR7.PE.iconField = (cfg) => {
		cfg = cfg || {};
		let val = cfg.value || null;
		const el = document.createElement("div"); el.className = "sr7pe-drop"; el.tabIndex = 0;
		const valEl = document.createElement("span"); valEl.className = "sr7pe-drop-value";
		const titleEl = document.createElement("span"); titleEl.className = "sr7pe-drop-title"; titleEl.textContent = cfg.title || "Icon";
		const arrow = document.createElement("span"); arrow.className = "sr7pe-drop-arrow";
		arrow.innerHTML = "<svg width='10' height='6' viewBox='0 0 10 6'><path d='M1 1l4 4 4-4' fill='none' stroke='currentColor' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/></svg>";
		el.appendChild(valEl); el.appendChild(titleEl); el.appendChild(arrow);
		// render a glyph by font-family + codepoint (NOT the .fa class — SR7's font-awesome.css scopes the
		// .fa font-family to its own containers, which our popover isn't inside; the @font-face still works).
		const glyphHTML = (o) => '<span style="font-family:\'' + o.family + '\';font-style:normal;font-weight:normal;line-height:1">' + String.fromCodePoint(parseInt(o.code, 16) || 0) + '</span>';
		const paint = () => { valEl.innerHTML = (val && val.code) ? (glyphHTML(val) + '<span style="margin-left:7px">' + (val.name || "icon") + '</span>') : "Pick an icon…"; };
		const open = async () => {
			if (el.classList.contains("disabled")) return;
			injectIconCSS(); SR7.PE.ensureIconFonts(); closeIcons();
			const pop = document.createElement("div"); pop.className = "sr7pe-iconpop";
			const search = document.createElement("input"); search.type = "text"; search.placeholder = "Search icons…";
			const grid = document.createElement("div"); grid.className = "sr7pe-icongrid";
			pop.appendChild(search); pop.appendChild(grid); document.body.appendChild(pop);
			const r = el.getBoundingClientRect();
			pop.style.left = Math.max(8, Math.min(r.left, window.innerWidth - 308)) + "px";
			let top = r.bottom + 4; if (top + 360 > window.innerHeight - 8) top = Math.max(8, r.top - 364); pop.style.top = top + "px";
			el.classList.add("open");
			const all = await SR7.PE.loadIcons();
			const renderGrid = (q) => {
				grid.innerHTML = ""; const ql = (q || "").toLowerCase().trim();
				const list = ql ? all.filter(ic => ic.name.indexOf(ql) > -1 || ic.cls.indexOf(ql) > -1) : all;
				if (!list.length) { const e = document.createElement("div"); e.className = "sr7pe-iconempty"; e.textContent = all.length ? "No matches" : "Icons couldn’t load"; grid.appendChild(e); return; }
				list.slice(0, 600).forEach(ic => {
					const b = document.createElement("div"); b.className = "sr7pe-iconbtn" + (val && val.code === ic.code && val.family === ic.family ? " sel" : ""); b.title = ic.name; b.innerHTML = glyphHTML(ic);
					b.addEventListener("pointerdown", (ev) => { ev.preventDefault(); val = { family: ic.family, code: ic.code, cls: ic.cls, name: ic.name }; paint(); closeIcons(); if (cfg.onChange) cfg.onChange(val); });
					grid.appendChild(b);
				});
			};
			renderGrid("");
			search.addEventListener("input", () => renderGrid(search.value));
			setTimeout(() => search.focus(), 30);
			const outside = (e) => { if (!pop.contains(e.target) && !el.contains(e.target)) closeIcons(); };
			const onKey = (e) => { if (e.key === "Escape") closeIcons(); };
			document.addEventListener("pointerdown", outside, true); document.addEventListener("keydown", onKey, true);
			OPEN_ICONS = { el, pop, outside, onKey };
		};
		el.addEventListener("click", open);
		el.setValue = (v) => { val = v; paint(); };
		el.getValue = () => val;
		el.setDisabled = (d) => el.classList.toggle("disabled", !!d);
		paint(); return el;
	};

	// ---- AJAX (routes through the shared SR7 _tpt.ajax helper — the same path every other api.class.php call uses) ----
	const ajax = (clientAction, data) => new Promise((resolve) => {
		_tpt.ajax({ action: clientAction, data, callBack: (raw) => resolve(_tpt.fixResponse(raw)) });
	});
	SR7.PE.ajax = ajax;

	// =====================================================================================
	//  Block registration (generic — one per registered type, card delegated to the type)
	// =====================================================================================
	const cardStyle = { display: "flex", alignItems: "center", gap: "16px", width: "100%", boxSizing: "border-box", border: "1px solid #e0d8ff", borderRadius: "0", padding: "16px 20px", background: "linear-gradient(180deg,#faf8ff,#fff)", fontFamily: "'Inter',system-ui,sans-serif" };
	const badgeStyle = { display: "inline-flex", alignItems: "center", gap: "6px", flex: "0 0 auto", padding: "6px 12px", borderRadius: "999px", background: "linear-gradient(135deg,#9570FF,#5C24FF)", color: "#fff", fontSize: "12px", fontWeight: 700, letterSpacing: ".01em", whiteSpace: "nowrap", boxShadow: "0 4px 12px rgba(92,36,255,.30)" };
	SR7.PE.badgeStyle = badgeStyle;   // shared so self_block effects (Filmstrip) reuse the same "✦ SR7 …" pill

	// SR7 brand mark as a block-inserter icon (purple tile + SR logo). Every Page-Effect block wears it so they
	// read as one SR7 family (Slider Revolution, Filmstrip, Chalk Line …), distinguished by their title.
	SR7.PE.logoIcon = () => { const c = wp.element.createElement;
		return c("svg", { width: 24, height: 24, viewBox: "0 0 36 36", xmlns: "http://www.w3.org/2000/svg" },
			c("g", { transform: "translate(8 7.493)" },
				c("rect", { width: 36, height: 36, rx: 5, transform: "translate(-8 -7.493)", fill: "#5c24ff" }),
				c("path", { fill: "#fff", transform: "translate(-28.331 -0.8)", d: "M46.311,18.755l2.807,2.81a.2.2,0,0,1-.14.337H40.089a.121.121,0,0,1-.137-.137l-.006-8.912a.188.188,0,0,1,.322-.134l2.938,2.933a.109.109,0,0,0,.185-.006A6.5,6.5,0,0,0,43.9,7.7a6.271,6.271,0,0,0-5.255-2.944q-.123.009-.123-.117l0-3.991a.121.121,0,0,1,.148-.137c5.283.394,9.36,3.746,10.293,8.929a10.885,10.885,0,0,1-2.231,8.781.516.516,0,0,1-.168.145l-.211.114Q46.146,18.589,46.311,18.755Z" }),
				c("path", { fill: "#fff", transform: "translate(-0.8 -1.481)", d: "M0,1.745V1.46l8.975.014a.124.124,0,0,1,.14.14l0,8.627a.251.251,0,0,1-.431.177L5.974,7.688a.133.133,0,0,0-.225.009C2.037,12.144,5,18.126,10.521,18.714a.168.168,0,0,1,.148.168v3.766a.123.123,0,0,1-.145.14A10.328,10.328,0,0,1,3.94,20.36Q-1.717,15.456.79,7.893A9.566,9.566,0,0,1,2.844,4.8a.151.151,0,0,0-.006-.234Z" })));
	};

	// A block-inserter icon that <use>s a symbol from the SR7 sprite (the PHP side injects the sprite into the
	// block editor) on the SR7 purple tile — so each effect shows its OWN addon icon (Filmstrip = Addon_Filmstrip,
	// Chalk Line = a pencil, …), same purple family as the Slider Revolution block.
	// A COMPONENT (like the revslider block icon) so Gutenberg passes width/height per context — inserter /
	// toolbar / list view — and it scales (a fixed size looked huge in the toolbar). The purple ROUNDED tile
	// (rx) + a roomy white symbol are baked into the svg so it reads like the SR module mark everywhere.
	// A self-sufficient purple tile + white addon glyph, mirroring the main block's SR7Icon (36-unit box,
	// rx 5, baked purple rect). The baked rect is what shows in contexts that DON'T apply the icon object's
	// `background` — the block toolbar switcher, list view, document outline. The `background` (see
	// registerBlocks) additionally fills the whole inserter cell, so the big lila bg matches the main block.
	// Glyph inset: 7/22 (was 9/18) — still padded off the rounded corners, but reads clearly at inserter size.
	SR7.PE.spriteIcon = (id) => (props) => {
		const c = wp.element.createElement;
		// Default width/height so the svg has an intrinsic size — some hosts (PanelBody's <Icon>) pass only a
		// `size` prop the svg can't read, and without width/height the icon expands to fill its container.
		const { size, ...p } = props || {};   // <Icon> passes a non-DOM `size` prop; consume it, don't spread it
		const dim = size || 24;
		return c("svg", Object.assign({ width: dim, height: dim, viewBox: "0 0 36 36", role: "img", "aria-hidden": true, xmlns: "http://www.w3.org/2000/svg" }, p),
			c("rect", { width: 36, height: 36, rx: 5, fill: "#5c24ff" }),
			c("use", { href: "#" + id, xlinkHref: "#" + id, x: 7, y: 7, width: 22, height: 22, fill: "#fff" }));
	};

	// The block-inserter hover preview. Each effect can supply its own image (meta.preview); otherwise it
	// falls back to the same placeholder image the main Slider Revolution block uses, so an effect with no
	// custom art still previews as one SR7 family. Served from core (SR7.E.plugin_url).
	const previewImage = (url) => {
		const el = wp.element.createElement;
		const src = url || (((window.SR7 && SR7.E && SR7.E.plugin_url) || "") + "admin/assets/images/sr7placeholder.webp");
		return el("img", { src: src, style: { width: "100%", display: "block" }, alt: "" });
	};
	SR7.PE.previewImage = previewImage;   // shared so self_block effects (Filmstrip) preview identically

	// SR7 block-button look — mirrors the module block's `.sr--block--button` (neutral bordered secondary,
	// Inter, weight 600). Shared on SR7.PE so self_block effects (Filmstrip) style their action buttons the
	// same, so every Page-Effect block's buttons read like the Slider Revolution module block's.
	SR7.PE.btnStyle = { fontFamily: "'Inter',system-ui,sans-serif", fontWeight: 600, boxShadow: "inset 0 0 0 1px #ccc", color: "#1e1e1e" };

	const defaultCard = (props, meta, onEdit) => {
		const el = wp.element.createElement, useBlockProps = wp.blockEditor.useBlockProps, Button = wp.components.Button, __ = wp.i18n.__;
		// a pencil glyph so the action reads like SR7's "Edit Module" button (icon + label, secondary style)
		const editIcon = el("svg", { width: 18, height: 18, viewBox: "0 0 24 24", "aria-hidden": true, focusable: false },
			el("path", { fill: "currentColor", d: "M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04a.996.996 0 000-1.41l-2.34-2.34a.996.996 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" }));
		return el("div", useBlockProps({ className: "sr7-pe-block" }),
			el("div", { style: cardStyle },
				el("span", { style: badgeStyle }, el("span", { style: { fontSize: "13px", lineHeight: 1 } }, "✦"), "SR7 " + meta.title),
				el("div", { style: { flex: "1", minWidth: 0 } },
					el("div", { style: { color: "#646970", fontSize: "13px" } }, props.attributes.summary || __("Not set up yet — click Live Edit to draw your line.", "revslider"))
				),
				el(Button, { variant: "secondary", onClick: onEdit, style: SR7.PE.btnStyle },
					editIcon,
					el("span", { style: { marginLeft: "6px" } }, __("Live Edit", "revslider")))
			)
		);
	};

	// Unique effect id per block instance. Gutenberg "Duplicate" copies a block's attributes (incl.
	// effectId) but assigns a NEW clientId — so a duplicate would otherwise share the original's
	// effectId, hence its post-meta (editing one would edit both). We track which clientId claimed each
	// id this editor session and re-mint on collision; pre-existing duplicates self-heal on next open.
	const claimedIds = {};   // effectId -> clientId
	const mintEffectId = (clientId) => "pe" + String(clientId || "").replace(/-/g, "").slice(0, 12);
	const ensureEffectId = (props) => {
		const cid = props.clientId || "";
		let eid = props.attributes.effectId;
		if (!eid) {
			eid = mintEffectId(cid);
			props.setAttributes({ effectId: eid });
		} else if (claimedIds[eid] && claimedIds[eid] !== cid) {
			// effectId already owned by another instance → this block is a duplicate: take a fresh id and
			// drop the copied summary (its own meta is empty until configured).
			eid = mintEffectId(cid);
			props.setAttributes({ effectId: eid, summary: "" });
		}
		claimedIds[eid] = cid;
	};
	SR7.PE.ensureEffectId = ensureEffectId;

	let blocksRegistered = false;
	const registerBlocks = () => {
		if (blocksRegistered || !window.wp || !wp.blocks) return;
		blocksRegistered = true;

		Object.keys(CFG.types).forEach(type => {
			const meta = CFG.types[type];
			// Inspector-style effects register their own block (custom attributes + InspectorControls edit)
			// from their editor script — skip the generic recorder card here so we don't double-register.
			if (meta.self_block) return;
			wp.blocks.registerBlockType(meta.block, {
				apiVersion: 3,
				title: "Slider Revolution " + meta.title,
				keywords: ["Slider Revolution", "SR7", "themepunch", meta.title],
				category: "themepunch",
				icon: (meta.spriteIcon
					? { src: SR7.PE.spriteIcon(meta.spriteIcon), background: "#5c24ff", foreground: "#fff" }
					: SR7.PE.logoIcon()),
				description: meta.description,
				attributes: { effectId: { type: "string", default: "" }, summary: { type: "string", default: "" }, previewMode: { type: "boolean", default: false }, savedAt: { type: "number", default: 0 } },
				// NOTE: block `supports` are declared SERVER-side (register_type → register_blocks) and bootstrapped
				// to the editor automatically — do NOT also pass them here. Double-specifying supports (client +
				// server) crashes the block-supports processing on WP 7.0 ("'color' in undefined").
				// inserter hover-preview: reuse the same placeholder image the main Slider Revolution block shows.
				example: { attributes: { previewMode: true } },
				edit: (props) => {
					if (props.attributes.previewMode) return previewImage(meta.preview);
					ensureEffectId(props);   // mint / de-duplicate this block's effectId
					const def = SR7.PE.types[type];
					const card = (def && def.card) ? def.card : defaultCard;
					return card(props, meta, () => openFromBlock(props, meta, type));
				},
				save: () => null
			});
		});
	};

	const openFromBlock = (props, meta, type) => {
		const post = wp.data.select("core/editor").getCurrentPost();
		const postId = wp.data.select("core/editor").getCurrentPostId();
		SR7.PE.openRecorder({
			type,
			postId,
			effectId: props.attributes.effectId,
			url: (post && post.link) ? post.link : ("/?page_id=" + postId),
			onSaved: (summary) => { props.setAttributes({ summary: String(summary || ""), savedAt: Date.now() }); }
		});
	};

	// =====================================================================================
	//  Recorder shell
	// =====================================================================================
	let R = null; // active recorder session

	const btn = (label, cls) => { const b = document.createElement("button"); b.textContent = label; b.className = "sr7pe-btn" + (cls ? " " + cls : ""); return b; };

	// ---- device switcher (shell-owned; icon row matching Quick Edit — admin/assets/js/tools/quickedit.js) ----
	// Inlined SR7 top-bar device glyphs so they render in Gutenberg where the editor sprite isn't loaded.
	const PE_DEVICE_ICONS = {
		wdesktop: '<svg viewBox="0 0 24 14" width="24" height="14" fill="currentColor"><path d="M11,16H3a3,3,0,0,1-3-3V8A3,3,0,0,1,3,5H21a3,3,0,0,1,3,3v5a3,3,0,0,1-3,3H13v1h2a1,1,0,0,1,0,2H9a1,1,0,0,1,0-2h2ZM3,7H21a1,1,0,0,1,1,1v5a1,1,0,0,1-1,1H3a1,1,0,0,1-1-1V8A1,1,0,0,1,3,7Z" transform="translate(0 -5)" fill-rule="evenodd"/></svg>',
		desktop: '<svg viewBox="0 0 22 18" width="22" height="18" fill="currentColor"><path d="M11,17H4a3,3,0,0,1-3-3V6A3,3,0,0,1,4,3H20a3,3,0,0,1,3,3v8a3,3,0,0,1-3,3H13v2h3a1,1,0,0,1,0,2H8a1,1,0,0,1,0-2h3ZM4,5H20a1,1,0,0,1,1,1v8a1,1,0,0,1-1,1H4a1,1,0,0,1-1-1V6A1,1,0,0,1,4,5Z" transform="translate(-1 -3)" fill-rule="evenodd"/></svg>',
		notebook: '<svg viewBox="0 0 22 16" width="22" height="16" fill="currentColor"><path d="M3,6A2,2,0,0,1,5,4H19a2,2,0,0,1,2,2v8a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2ZM5,6H19v8H5Z" transform="translate(-1 -4)" fill-rule="evenodd"/><path d="M2,18a1,1,0,0,0,0,2H22a1,1,0,0,0,0-2Z" transform="translate(-1 -4)"/></svg>',
		tablet: '<svg viewBox="0 0 20 24" width="20" height="24" fill="currentColor"><path d="M16,23.955a.817.817,0,0,0,.6-.232.846.846,0,0,0,0-1.173.885.885,0,0,0-1.194,0,.846.846,0,0,0,0,1.173A.817.817,0,0,0,16,23.955ZM7.667,26a1.622,1.622,0,0,1-1.181-.477A1.564,1.564,0,0,1,6,24.364V3.636a1.564,1.564,0,0,1,.486-1.159A1.622,1.622,0,0,1,7.667,2H24.333a1.622,1.622,0,0,1,1.181.477A1.564,1.564,0,0,1,26,3.636V24.364a1.564,1.564,0,0,1-.486,1.159A1.622,1.622,0,0,1,24.333,26Zm0-4.091v2.455H24.333V21.909Zm0-1.636H24.333V6.091H7.667Zm0-15.818H24.333V3.636H7.667Z" transform="translate(-6 -2)"/></svg>',
		mobile: '<svg viewBox="0 0 14 20" width="14" height="20" fill="currentColor"><path d="M13,16H11v2h2Z" transform="translate(-5 -2)"/><path d="M5,4A2,2,0,0,1,7,2H17a2,2,0,0,1,2,2V20a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2ZM7,4H17V20H7Z" transform="translate(-5 -2)" fill-rule="evenodd"/></svg>'
	};
	const PE_DEVICE_KEYS = ["wdesktop", "desktop", "notebook", "tablet", "mobile"];
	const paintDeviceTabs = () => {
		if (!R?.devices) return;
		R.devices.querySelectorAll(".sr7pe-device").forEach(b => {
			b.classList.toggle("sr7pe-device--active", +b.dataset.lev === R.level);
		});
	};
	const buildDeviceTabs = () => {
		if (!R?.devices) return;
		R.devices.innerHTML = "";
		const labels = SR7.PE.deviceLabels || SR7.PE.devices;
		SR7.PE.devices.forEach((dev, idx) => {
			const key = PE_DEVICE_KEYS[idx] || "desktop";
			const b = document.createElement("button");
			b.type = "button";
			b.className = "sr7pe-device";
			b.dataset.lev = String(idx);
			b.title = (labels[idx] || dev) + " (" + SR7.PE.deviceWidths[idx] + "px)";
			b.innerHTML = PE_DEVICE_ICONS[key] || PE_DEVICE_ICONS.desktop;
			b.onclick = () => setLevel(idx);
			R.devices.appendChild(b);
		});
		paintDeviceTabs();
	};
	// width of the iframe for the active device: the design width, but never wider than the stage
	const applyDeviceWidth = () => {
		if (!R) return;
		const w = SR7.PE.deviceWidths[R.level], avail = R.stage.clientWidth || 9999;
		R.iframe.style.width = (R.level === 0 || w >= avail) ? "100%" : (w + "px");
	};
	const setLevel = (idx) => {
		if (!R || idx === R.level) return;
		R.level = idx; paintDeviceTabs(); applyDeviceWidth();
		// let the iframe reflow at the new width before the tool re-reads element positions
		const raf = (R.win && R.win.requestAnimationFrame) || window.requestAnimationFrame;
		raf(() => raf(() => { if (R && R.levelCb) R.levelCb(R.level); }));
	};

	// =====================================================================================
	//  Element-bind picker (framework service, shared by all effects). Snaps a viewport point to the
	//  most MEANINGFUL box under it and returns a STABLE css selector that matches the SAME element on
	//  the front end (the iframe loads the real permalink, so editor-generated selectors are front-valid).
	//  Priority while climbing from the clicked node → first of: sr7-module[id] · element with id ·
	//  Gutenberg wp-block-* wrapper · a meaningful block-level tag · else the clicked element itself.
	// =====================================================================================
	// block-level tags worth binding to (so clicking text inside a <p> snaps to the <p>, an image to <img>)
	const PE_MEANINGFUL = { IMG: 1, FIGURE: 1, PICTURE: 1, SVG: 1, VIDEO: 1, CANVAS: 1, BUTTON: 1, A: 1, H1: 1, H2: 1, H3: 1, H4: 1, H5: 1, H6: 1, P: 1, BLOCKQUOTE: 1, UL: 1, OL: 1, LI: 1, TABLE: 1, SECTION: 1, HEADER: 1, FOOTER: 1, ARTICLE: 1, ASIDE: 1, NAV: 1 };
	const peEsc = (s) => (window.CSS && CSS.escape) ? CSS.escape(s) : String(s).replace(/[^a-zA-Z0-9_-]/g, "\\$&");
	const peIsWpBlock = (el) => { const c = el.getAttribute && el.getAttribute("class"); return !!(c && /\bwp-block-\S/.test(c)); };
	SR7.PE.isWpBlock = peIsWpBlock;

	// Build a robust selector for el: an id when present, else a `>`-joined tag:nth-of-type path rooted at
	// the nearest ancestor that has an id (or <body>). Child combinator + nth-of-type is stable for the
	// statically-rendered front markup (which has no Gutenberg clientIds).
	SR7.PE.selectorFor = (el) => {
		if (!el || el.nodeType !== 1) return "";
		if (el.id) return "#" + peEsc(el.id);
		const parts = [];
		let node = el;
		while (node && node.nodeType === 1 && node.tagName !== "BODY" && node.tagName !== "HTML") {
			if (node.id) { parts.unshift("#" + peEsc(node.id)); break; }
			const tag = node.tagName.toLowerCase();
			let idx = 1, sib = node, many = false;
			while ((sib = sib.previousElementSibling)) if (sib.tagName === node.tagName) idx++;
			sib = node;
			while ((sib = sib.nextElementSibling)) if (sib.tagName === node.tagName) { many = true; break; }
			parts.unshift((idx > 1 || many) ? tag + ":nth-of-type(" + idx + ")" : tag);
			node = node.parentElement;
		}
		return parts.join(" > ");
	};

	const bindPicker = (clientX, clientY) => {
		if (!R || !R.doc) return null;
		const doc = R.doc;
		const under = doc.elementFromPoint(clientX, clientY);
		if (!under || under === doc.body || under === doc.documentElement) return null;
		// closest identifiable / meaningful box around the click
		let target = null, node = under;
		while (node && node !== doc.body) {
			if ((node.tagName === "SR7-MODULE" && node.id) || node.id || peIsWpBlock(node) || PE_MEANINGFUL[node.tagName]) { target = node; break; }
			node = node.parentElement;
		}
		if (!target) target = under;
		const selector = SR7.PE.selectorFor(target);
		let unique = false;
		try { const f = doc.querySelectorAll(selector); unique = (f.length === 1 && f[0] === target); } catch (e) {}
		return { selector, el: target, unique };
	};

	SR7.PE.openRecorder = (opts) => {
		if (R) closeRecorder();
		const def = SR7.PE.types[opts.type];
		if (!def || !def.tool) { window.alert("Effect tool not loaded: " + opts.type); return; }

		SR7.PE.injectSkin();

		const overlay = document.createElement("div"); overlay.className = "sr7pe-overlay";

		// top bar (shell-owned): brand left, device icons centred, actions right
		const bar = document.createElement("div"); bar.className = "sr7pe-bar";
		const brand = document.createElement("div"); brand.className = "sr7pe-brand";
		const logo = document.createElement("span"); logo.className = "sr7pe-logo"; logo.innerHTML = SR7.PE.logoSVG || "";
		const brandTxt = document.createElement("span");
		brandTxt.appendChild(document.createTextNode("Slider Revolution"));
		const em = document.createElement("em"); em.textContent = " · " + (def.label || def.title || "Page Effect"); brandTxt.appendChild(em);
		brand.appendChild(logo); brand.appendChild(brandTxt);
		const devices = document.createElement("div"); devices.id = "sr7pe-devices"; devices.className = "sr7pe-devices";
		const spacer = document.createElement("span"); spacer.className = "sr7pe-spacer";
		const expBtn = btn(wp.i18n ? wp.i18n.__("Export", "revslider") : "Export");
		const impBtn = btn(wp.i18n ? wp.i18n.__("Import", "revslider") : "Import");
		const impFile = document.createElement("input");
		impFile.type = "file"; impFile.accept = "application/json,.json"; impFile.className = "sr7pe-file";
		const preview = btn(wp.i18n ? wp.i18n.__("Preview Live", "revslider") : "Preview Live");
		const save = btn(wp.i18n ? wp.i18n.__("Save", "revslider") : "Save", "sr7pe-btn--primary");
		const close = btn(wp.i18n ? wp.i18n.__("Close", "revslider") : "Close");
		[brand, spacer, expBtn, impBtn, preview, save, close, impFile].forEach(n => bar.appendChild(n));
		bar.appendChild(devices);   // absolute-centred — appended last so it layers above the bar flex row

		// body: left panel (tool UI) + iframe stage
		const body = document.createElement("div"); body.className = "sr7pe-body";
		const panel = document.createElement("div"); panel.className = "sr7pe-panel";
		// stage centres the iframe so narrower devices show as a framed viewport on the dark backdrop
		const stage = document.createElement("div"); stage.className = "sr7pe-stage";
		const iframe = document.createElement("iframe"); iframe.className = "sr7pe-iframe"; iframe.style.width = "100%";
		stage.appendChild(iframe); body.appendChild(panel); body.appendChild(stage);

		overlay.appendChild(bar); overlay.appendChild(body);
		document.body.appendChild(overlay);

		R = { opts, overlay, iframe, stage, panel, devices, preview, save, expBtn, impBtn, impFile, dirty: false, doc: null, win: null, tool: def.tool, level: 1, levelCb: null };
		buildDeviceTabs();

		expBtn.onclick = doExport;
		impBtn.onclick = () => { if (R && R.impFile) R.impFile.click(); };
		impFile.onchange = () => {
			const f = impFile.files && impFile.files[0];
			impFile.value = "";
			if (f) doImportFile(f);
		};
		preview.onclick = doPreviewLive;
		save.onclick = doSave;
		close.onclick = () => { if (!R.dirty || window.confirm(wp.i18n ? wp.i18n.__("Close without saving?", "revslider") : "Close without saving?")) closeRecorder(); };

		iframe.addEventListener("load", () => {
			try { R.doc = iframe.contentDocument; R.win = iframe.contentWindow; void R.doc.body; }
			catch (err) { window.alert("Cannot access the page (cross-origin). It must be on this site."); return; }

			const ctx = {
				doc: R.doc, win: R.win, panel, devices,
				bindPicker,
				getLevel: () => R.level,                       // active editing device level (0..4)
				onLevelChange: (cb) => { R.levelCb = cb; },     // tool re-bakes/renders at the new level
				markDirty: () => { R.dirty = true; save.textContent = (wp.i18n ? wp.i18n.__("Save", "revslider") : "Save") + " *"; }
			};
			R.handle = R.tool.mount(ctx) || {};
			applyDeviceWidth();   // size the freshly-loaded iframe to the current device
			ajax("page_effects.get", { post_id: opts.postId, effect_id: opts.effectId }).then(r => {
				const stored = (r && r.success) ? r.stored : null; // api merges the response payload at the top level
				if (stored && stored.data && R.tool.load) R.tool.load(stored.data);
			});
		});
		// load the page with a flag so the live effect is suppressed in the iframe — only the editor draws
		iframe.src = opts.url + (opts.url.indexOf("?") > -1 ? "&" : "?") + "sr7pe_edit=1";
	};

	// ---- Export / Import (page-effect JSON; remaps SR7 binds by alias + layer key) ----
	// Bind shape: #SR7_{sliderId}_{serial}-{slideId}-{layerKey}
	// Module import on another site changes sliderId, serial AND slideId — only alias + layerKey stay stable.
	const __pe = (s, d) => (wp.i18n && wp.i18n.__) ? wp.i18n.__(s, "revslider") : (d || s);

	const listPageModules = (doc, win) => {
		const out = [];
		if (!doc) return out;
		doc.querySelectorAll("sr7-module[id]").forEach(el => {
			const htmlId = el.id;
			const M = win && win.SR7 && win.SR7.M && win.SR7.M[htmlId];
			const alias = (M && (M.alias || (M.settings && M.settings.alias))) || el.getAttribute("data-alias") || (el.dataset && el.dataset.alias) || "";
			const m = String(htmlId).match(/^SR7_(\d+)_(\d+)$/);
			out.push({ htmlId, alias: String(alias || ""), sliderId: m ? m[1] : "", instance: m ? m[2] : "", el });
		});
		return out;
	};

	// #SR7_1340_2-9028-125 → { htmlId, slideId, layerKey }
	const parseSrBind = (bind) => {
		const m = String(bind || "").match(/^#(SR7_\d+_\d+)(?:-(.+))?$/);
		if (!m) return null;
		const htmlId = m[1], rest = m[2] || "";
		if (!rest) return { htmlId, slideId: "", layerKey: "" };
		const i = rest.lastIndexOf("-");
		if (i < 0) return { htmlId, slideId: rest, layerKey: "" };
		return { htmlId, slideId: rest.slice(0, i), layerKey: rest.slice(i + 1) };
	};

	const slideKeysFor = (win, htmlId) => {
		const M = win && win.SR7 && win.SR7.M && win.SR7.M[htmlId];
		if (!M || !M.slides) return [];
		const staticSet = {};
		(M.staticSlides || []).forEach(s => { if (s) staticSet[String(s)] = true; });
		const usable = (k) => {
			if (!k || k === "error" || !M.slides[k]) return false;
			if (staticSet[String(k)]) return false;
			if (String(k).endsWith("_2")) return false; // static duplicate
			if (M.slides[k].slide && M.slides[k].slide.global) return false;
			return true;
		};
		// Prefer nav / logical order (content slides only); never index into static slides.
		let keys = [];
		if (Array.isArray(M.navSlideOrder) && M.navSlideOrder.length) keys = M.navSlideOrder.map(String);
		else if (Array.isArray(M.slideOrder) && M.slideOrder.length) keys = M.slideOrder.map(String);
		else keys = Object.keys(M.slides);
		return keys.filter(usable);
	};

	const layerExists = (win, htmlId, slideId, layerKey) => {
		const M = win && win.SR7 && win.SR7.M && win.SR7.M[htmlId];
		const layers = M && M.slides && M.slides[slideId] && M.slides[slideId].layers;
		if (!layers || layerKey == null || layerKey === "") return false;
		return layers[layerKey] != null || layers[String(layerKey)] != null;
	};

	// Pick the local slide id: slideIndex first (stable across module re-import), then exact old id if still present.
	const resolveSlideId = (win, htmlId, preferSlideIndex, preferSlideId) => {
		const keys = slideKeysFor(win, htmlId);
		if (!keys.length) return "";
		if (preferSlideIndex != null && preferSlideIndex >= 0 && keys[preferSlideIndex]) return String(keys[preferSlideIndex]);
		if (preferSlideId != null && preferSlideId !== "" && keys.indexOf(String(preferSlideId)) >= 0) return String(preferSlideId);
		return String(keys[0]);
	};

	// Find #htmlId-*-layerKey on the page (slide id may have changed after module import).
	// Never invent a bind without verifying the layer exists — that was remapping to wrong/static slides.
	const findBindByLayerKey = (doc, win, htmlId, layerKey, preferSlideIndex, preferSlideId) => {
		if (!htmlId || layerKey == null || layerKey === "") return "";
		const keys = slideKeysFor(win, htmlId);
		const trySid = (sid) => (sid && layerExists(win, htmlId, sid, layerKey)) ? ("#" + htmlId + "-" + sid + "-" + layerKey) : "";
		let hit = trySid(resolveSlideId(win, htmlId, preferSlideIndex, preferSlideId));
		if (hit) return hit;
		for (let i = 0; i < keys.length; i++) {
			hit = trySid(keys[i]);
			if (hit) return hit;
		}
		// Also scan static / any remaining slides in M.slides (layer may live only there)
		const M = win && win.SR7 && win.SR7.M && win.SR7.M[htmlId];
		if (M && M.slides) {
			for (const sid of Object.keys(M.slides)) {
				hit = trySid(sid);
				if (hit) return hit;
			}
		}
		// DOM fallback (layers already mounted) — ignore stale slide ids
		if (doc) {
			const nodes = doc.querySelectorAll('[id^="' + htmlId.replace(/"/g, "") + '-"]');
			for (let i = 0; i < nodes.length; i++) {
				const id = nodes[i].id;
				if (id.length <= htmlId.length + 1) continue;
				const rest = id.slice(htmlId.length + 1);
				const li = rest.lastIndexOf("-");
				if (li >= 0 && rest.slice(li + 1) === String(layerKey)) return "#" + id;
			}
		}
		return "";
	};

	const findBindSlideOnly = (win, htmlId, preferSlideIndex, preferSlideId) => {
		if (!htmlId) return "";
		const sid = resolveSlideId(win, htmlId, preferSlideIndex, preferSlideId);
		return sid ? ("#" + htmlId + "-" + sid) : ("#" + htmlId);
	};

	const eachAnchor = (data, fn) => {
		(data && data.segments || []).forEach(s => (s.anchors || []).forEach(fn));
		(data && data.anchors || []).forEach(fn);
	};

	const moduleIdsFromData = (data) => {
		const ids = [];
		eachAnchor(data, a => {
			const p = parseSrBind(a && a.bind);
			if (p && ids.indexOf(p.htmlId) < 0) ids.push(p.htmlId);
		});
		return ids;
	};

	const modulesForExport = (data, doc, win) => {
		const all = listPageModules(doc, win);
		const byId = {};
		all.forEach(m => { byId[m.htmlId] = m; });
		return moduleIdsFromData(data).map(htmlId => byId[htmlId] || {
			htmlId,
			alias: "",
			sliderId: (String(htmlId).match(/^SR7_(\d+)/) || [])[1] || "",
			instance: (String(htmlId).match(/^SR7_\d+_(\d+)/) || [])[1] || ""
		});
	};

	// Attach stable _export meta on each bound anchor (alias + layerKey + slideIndex).
	const enrichAnchorsForExport = (data, doc, win) => {
		const clone = JSON.parse(JSON.stringify(data || {}));
		const mods = listPageModules(doc, win);
		const byId = {};
		mods.forEach(m => { byId[m.htmlId] = m; });
		eachAnchor(clone, a => {
			if (!a || !a.bind) return;
			const p = parseSrBind(a.bind);
			if (!p) return;
			const mod = byId[p.htmlId];
			const keys = slideKeysFor(win, p.htmlId);
			const slideIndex = p.slideId ? keys.indexOf(String(p.slideId)) : -1;
			a._export = {
				alias: (mod && mod.alias) || "",
				sliderId: (mod && mod.sliderId) || ((p.htmlId.match(/^SR7_(\d+)/) || [])[1] || ""),
				htmlId: p.htmlId,
				slideId: p.slideId || "",
				layerKey: p.layerKey || "",
				slideIndex: slideIndex >= 0 ? slideIndex : null
			};
		});
		return clone;
	};

	const stripExportMeta = (data) => {
		const clone = JSON.parse(JSON.stringify(data || {}));
		eachAnchor(clone, a => { if (a) delete a._export; });
		return clone;
	};

	const pickTargetModule = (meta, parsed, targetMods) => {
		if (meta && meta.alias) {
			const al = String(meta.alias);
			let t = targetMods.find(x => x.alias && x.alias === al);
			if (t) return t;
			const soft = al.toLowerCase().trim();
			t = targetMods.find(x => x.alias && x.alias.toLowerCase().trim() === soft);
			if (t) return t;
		}
		if (meta && meta.sliderId) {
			const t = targetMods.find(x => x.sliderId === String(meta.sliderId));
			if (t) return t;
		}
		if (parsed) {
			const exact = targetMods.find(x => x.htmlId === parsed.htmlId);
			if (exact) return exact;
			const sid = (parsed.htmlId.match(/^SR7_(\d+)/) || [])[1];
			if (sid) {
				const t = targetMods.find(x => x.sliderId === sid);
				if (t) return t;
			}
		}
		return null;
	};

	// Fill missing _export from top-level modules[] (v1 exports) + parse bind.
	const hydrateExportMeta = (data, exportedMods) => {
		const byHtml = {};
		(exportedMods || []).forEach(m => { if (m && m.htmlId) byHtml[m.htmlId] = m; });
		eachAnchor(data, a => {
			if (!a || !a.bind) return;
			const p = parseSrBind(a.bind);
			if (!p) return;
			const mod = byHtml[p.htmlId] || {};
			const cur = a._export || {};
			a._export = {
				alias: cur.alias || mod.alias || "",
				sliderId: cur.sliderId || mod.sliderId || ((p.htmlId.match(/^SR7_(\d+)/) || [])[1] || ""),
				htmlId: cur.htmlId || p.htmlId,
				slideId: cur.slideId != null && cur.slideId !== "" ? cur.slideId : (p.slideId || ""),
				layerKey: cur.layerKey != null ? cur.layerKey : (p.layerKey || ""),
				slideIndex: cur.slideIndex != null ? cur.slideIndex : null
			};
		});
		return data;
	};

	const remapEffectDataV2 = (data, targetMods, doc, win) => {
		const clone = JSON.parse(JSON.stringify(data || {}));
		const report = { ok: 0, missing: [], remapped: 0, aliasesNeeded: [], aliasesFound: targetMods.map(m => m.alias || m.htmlId) };
		eachAnchor(clone, a => {
			if (!a || !a.bind) return;
			const meta = a._export || null;
			if (meta && meta.alias && report.aliasesNeeded.indexOf(meta.alias) < 0) report.aliasesNeeded.push(meta.alias);
			const parsed = parseSrBind(a.bind);
			const mod = pickTargetModule(meta, parsed, targetMods);
			if (!mod) {
				report.missing.push((meta && meta.alias ? meta.alias + " / " : "") + a.bind);
				return;
			}
			const layerKey = (meta && meta.layerKey) || (parsed && parsed.layerKey) || "";
			const slideId = (meta && meta.slideId) || (parsed && parsed.slideId) || "";
			const slideIndex = meta && meta.slideIndex != null ? meta.slideIndex : null;
			let next = "";
			if (layerKey) {
				next = findBindByLayerKey(doc, win, mod.htmlId, layerKey, slideIndex, slideId);
			} else if (slideId || slideIndex != null) {
				next = findBindSlideOnly(win, mod.htmlId, slideIndex, slideId);
			} else {
				next = "#" + mod.htmlId;
			}
			if (!next) {
				report.missing.push(a.bind + " → " + (mod.alias || mod.htmlId) + (layerKey ? (" / layer " + layerKey) : ""));
				return;
			}
			if (next !== a.bind) report.remapped++;
			a.bind = next;
			report.ok++;
			delete a._export;
		});
		return { data: clone, report };
	};

	// Needed layer keys per alias (from hydrated _export) so we wait until layers are actually in M.slides.
	const neededLayersByAlias = (data) => {
		const map = {};
		eachAnchor(data, a => {
			const meta = a && a._export;
			if (!meta || !meta.alias) return;
			const lk = meta.layerKey != null ? String(meta.layerKey) : "";
			if (!lk) return;
			if (!map[meta.alias]) map[meta.alias] = [];
			if (map[meta.alias].indexOf(lk) < 0) map[meta.alias].push(lk);
		});
		return map;
	};

	const moduleHasLayers = (win, htmlId, layerKeys) => {
		const M = win && win.SR7 && win.SR7.M && win.SR7.M[htmlId];
		if (!M || !M.slides) return false;
		const keys = Object.keys(M.slides).filter(k => k !== "error" && M.slides[k]);
		if (!keys.length) return false;
		if (!(layerKeys || []).length) return true;
		return layerKeys.every(lk => keys.some(sid => layerExists(win, htmlId, sid, lk)));
	};

	// Kick viewport-lazy modules so SR7.M[id].slides (+ needed layers) exist for remapping.
	const ensureModulesReady = (doc, win, mods, layerNeed) => new Promise(resolve => {
		if (!doc || !win || !mods.length) { resolve(); return; }
		layerNeed = layerNeed || {};
		const kick = () => {
			mods.forEach(m => {
				const el = doc.getElementById(m.htmlId);
				if (el && win.SR7 && win.SR7.F && win.SR7.F.module && win.SR7.F.module.init) {
					try { win.SR7.F.module.init(el); } catch (e) {}
				}
			});
		};
		kick();
		let n = 0;
		const t = setInterval(() => {
			n++;
			if (n === 5 || n === 20 || n === 40) kick();
			const pending = mods.filter(m => {
				const need = (m.alias && layerNeed[m.alias]) || [];
				return !moduleHasLayers(win, m.htmlId, need);
			});
			if (!pending.length || n > 160) { clearInterval(t); resolve(); }  // ~8s
		}, 50);
	});

	const downloadJson = (obj, filename) => {
		const blob = new Blob([JSON.stringify(obj, null, 2)], { type: "application/json" });
		const a = document.createElement("a");
		a.href = URL.createObjectURL(blob);
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		setTimeout(() => { try { URL.revokeObjectURL(a.href); } catch (e) {} a.remove(); }, 500);
	};

	const doExport = () => {
		if (!R || !R.tool || !R.tool.collect) return;
		const data = enrichAnchorsForExport(R.tool.collect(), R.doc, R.win);
		const payload = {
			format: "sr7-page-effect",
			version: 2,
			type: R.opts.type,
			exportedAt: new Date().toISOString(),
			source: {
				postId: R.opts.postId,
				effectId: R.opts.effectId,
				url: R.opts.url || ""
			},
			modules: modulesForExport(data, R.doc, R.win).map(m => ({
				htmlId: m.htmlId, alias: m.alias, sliderId: m.sliderId, instance: m.instance
			})),
			data
		};
		const safeType = String(R.opts.type || "effect").replace(/[^a-z0-9_-]/gi, "");
		const safeId = String(R.opts.effectId || "pe").replace(/[^a-z0-9_-]/gi, "");
		downloadJson(payload, "sr7-" + safeType + "-" + safeId + ".json");
	};
	SR7.PE.exportEffect = doExport;

	const doImportFile = (file) => {
		if (!R || !R.tool || !R.tool.load) return;
		const reader = new FileReader();
		reader.onerror = () => window.alert(__pe("Could not read the file.", "Could not read the file."));
		reader.onload = () => {
			let raw;
			try { raw = JSON.parse(String(reader.result || "")); }
			catch (e) { window.alert(__pe("Invalid JSON file.", "Invalid JSON file.")); return; }

			let type = raw.type || R.opts.type;
			let data = raw.data;
			let exportedMods = raw.modules || [];
			if (raw.format === "sr7-page-effect") {
				type = raw.type;
				data = raw.data;
				exportedMods = raw.modules || [];
			} else if (!data && (raw.segments || raw.anchors)) {
				data = raw;
				type = R.opts.type;
			}
			if (!data || typeof data !== "object") {
				window.alert(__pe("This file has no effect data.", "This file has no effect data."));
				return;
			}
			if (type && type !== R.opts.type) {
				window.alert(__pe("This export is for a different effect type.", "This export is for a different effect type.") + " (" + type + ")");
				return;
			}

			data = hydrateExportMeta(JSON.parse(JSON.stringify(data)), exportedMods);

			const targetMods = listPageModules(R.doc, R.win);
			if (!targetMods.length) {
				window.alert(__pe("No Slider Revolution modules found on this page. Place the same modules first, then import.", "No Slider Revolution modules found on this page. Place the same modules first, then import."));
				return;
			}

			const neededAliases = [];
			eachAnchor(data, a => {
				const al = a && a._export && a._export.alias;
				if (al && neededAliases.indexOf(al) < 0) neededAliases.push(al);
			});
			(exportedMods || []).forEach(m => {
				if (m && m.alias && neededAliases.indexOf(m.alias) < 0) neededAliases.push(m.alias);
			});
			const pageAliases = targetMods.map(m => m.alias).filter(Boolean);
			const missingAliases = neededAliases.filter(a => !pageAliases.some(p => p === a || p.toLowerCase() === a.toLowerCase()));
			if (missingAliases.length) {
				window.alert(
					__pe("This page is missing the modules this chalk line was bound to.", "This page is missing the modules this chalk line was bound to.") +
					"\n\n" + __pe("Needed aliases:", "Needed aliases:") + "\n• " + missingAliases.join("\n• ") +
					"\n\n" + __pe("Modules on this page:", "Modules on this page:") + "\n• " + (pageAliases.length ? pageAliases.join("\n• ") : "(none with alias)") +
					"\n\n" + __pe("Import the same SR modules (same aliases) onto this page, then try Import again.", "Import the same SR modules (same aliases) onto this page, then try Import again.")
				);
				return;
			}

			R.impBtn && (R.impBtn.textContent = __pe("Importing…", "Importing…"));
			const layerNeed = neededLayersByAlias(data);
			ensureModulesReady(R.doc, R.win, targetMods, layerNeed).then(() => {
				const freshMods = listPageModules(R.doc, R.win);
				const { data: remapped, report } = remapEffectDataV2(data, freshMods, R.doc, R.win);
				R.tool.load(stripExportMeta(remapped));
				R.dirty = true;
				if (R.save) R.save.textContent = __pe("Save", "Save") + " *";
				if (R.impBtn) R.impBtn.textContent = __pe("Import", "Import");

				let msg = __pe("Imported. Click Save to store on this page.", "Imported. Click Save to store on this page.");
				msg += "\n" + __pe("Anchors linked:", "Anchors linked:") + " " + report.ok;
				if (report.remapped) msg += "\n" + __pe("Binds remapped to local module/slide ids:", "Binds remapped to local module/slide ids:") + " " + report.remapped;
				if (report.missing.length) {
					msg += "\n\n" + __pe("Could not match these anchors:", "Could not match these anchors:") + "\n" + report.missing.map(x => "• " + x).join("\n");
					msg += "\n\n" + __pe("Page modules:", "Page modules:") + " " + (report.aliasesFound.join(", ") || "(none)");
					msg += "\n\n" + __pe("Wait for modules to finish loading in Live Edit, then Import again.", "Wait for modules to finish loading in Live Edit, then Import again.");
				}
				window.alert(msg);
			});
		};
		reader.readAsText(file);
	};
	SR7.PE.importEffectFile = doImportFile;

	// Preview Live — like SR7.B.preview: stage the current (unsaved) tool state in a transient, then open /
	// refresh a named tab on the real permalink so the front runtime draws it (not the editor overlay).
	const doPreviewLive = () => {
		if (!R || !R.tool || !R.tool.collect) return;
		const __ = (wp.i18n && wp.i18n.__) ? wp.i18n.__ : (s) => s;
		const tabName = "SliderRevolutionPageEffectPreview";
		let w = window.open("", tabName);
		try {
			if (w && w.document) {
				w.document.title = "Slider Revolution Preview";
				if (!w.document.body) w.document.write("<!doctype html><title>Slider Revolution Preview</title><body></body>");
				if (w.document.body) w.document.body.innerHTML = '<div style="font-family:system-ui,sans-serif;padding:16px;">' + __("Loading preview…", "revslider") + "</div>";
			}
		} catch (e) {}
		R.preview.textContent = __("Previewing…", "revslider");
		ajax("page_effects.preview", { post_id: R.opts.postId, effect_id: R.opts.effectId, type: R.opts.type, effect: JSON.stringify(R.tool.collect()) })
			.then(r => {
				R.preview.textContent = __("Preview Live", "revslider");
				if (!r || !r.success || !r.effect_id || !r.token) {
					window.alert(__("Preview failed.", "revslider"));
					try { if (w) w.close(); } catch (e) {}
					return;
				}
				let url = R.opts.url;
				const sep = url.indexOf("?") > -1 ? "&" : "?";
				url += sep + "sr7pe_preview=1&pe_id=" + encodeURIComponent(r.effect_id) + "&pe_token=" + encodeURIComponent(r.token);
				if (w) {
					try { w.location.replace(url); } catch (e) { w.location.href = url; }
					try { w.focus(); } catch (e) {}
				} else window.open(url, tabName);
			}).catch(() => {
				R.preview.textContent = __("Preview Live", "revslider");
				window.alert(__("Preview failed.", "revslider"));
				try { if (w) w.close(); } catch (e) {}
			});
	};
	SR7.PE.previewLive = doPreviewLive;

	const doSave = () => {
		if (!R || !R.tool.collect) return;
		R.save.textContent = wp.i18n ? wp.i18n.__("Saving…", "revslider") : "Saving…";
		ajax("page_effects.save", { post_id: R.opts.postId, effect_id: R.opts.effectId, type: R.opts.type, effect: JSON.stringify(R.tool.collect()) })
			.then(r => {
				if (r && r.success) {
					R.dirty = false;
					R.save.textContent = wp.i18n ? wp.i18n.__("Saved ✓", "revslider") : "Saved ✓";
					R.save.classList.add("sr7pe-btn--saved");
					if (R.opts.onSaved) R.opts.onSaved(r.summary || "");
					setTimeout(() => { if (R) { R.save.textContent = wp.i18n ? wp.i18n.__("Save", "revslider") : "Save"; R.save.classList.remove("sr7pe-btn--saved"); } }, 1500);
				} else { R.save.textContent = "Save (error)"; }
			}).catch(() => { if (R) R.save.textContent = "Save (error)"; });
	};

	const closeRecorder = () => {
		if (!R) return;
		try { if (R.tool && R.tool.destroy) R.tool.destroy(); } catch (e) {}
		if (R.overlay) R.overlay.remove();
		R = null;
	};
	SR7.PE.closeRecorder = closeRecorder;

	// register blocks once the editor is ready
	if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", registerBlocks);
	else registerBlocks();
})();
