/**
 * @preserve
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */
/*
╔══════════════════════════════════════════════════════════════════════════════╗
║  PLUGIN:   SLIDER REVOLUTION 7                                                ║
║  MODULE:   PAGE EFFECTS  —  front runtime bootstrap + registry (core)         ║
╚══════════════════════════════════════════════════════════════════════════════╝

  RevSliderPageEffects::front_enqueue() emits, per page, SR7.E.pageEffects[effectId] = { type, data }
  and enqueues this file + each used type's runtime. Each addon registers its drawer via
  SR7.PE.registerRuntime(type, { mount, unmount }); this bootstrap routes every effect to its type.
  Also hosts the tiny shared responsive engine (devices / levelForWidth / lev #a-inherit).
*/
(function () {
	"use strict";

	window.SR7 ??= {};
	SR7.PE ??= {};
	SR7.PE.runtimes ??= {};
	SR7.PE._mounted ??= {};

	// =====================================================================================
	//  Responsive engine (shared by every effect; mirrors SR7's device model + gVal #a-inherit).
	//  Reuses SR7.D.devices when the engine is present; falls back on lite pages (PE-D9: a page with
	//  only a Page Effect deliberately does NOT load defaults.js / the SR7 module engine).
	// =====================================================================================
	SR7.PE.devices ??= (SR7.D && Array.isArray(SR7.D.devices)) ? SR7.D.devices : ["wdesktop", "desktop", "notebook", "tablet", "mobile"];
	SR7.PE.deviceWidths ??= [1600, 1240, 1024, 778, 480];   // design widths — mirror core layer-size defaults (defaults.js)

	// Active level for a viewport width: snuggest level whose design width still fits. Standalone (not
	// _tpt.getResponsiveLevel) on purpose — that needs SR7.G.breakPoints + the engine, absent on lite pages.
	SR7.PE.levelForWidth = (w) => {
		const d = SR7.PE.deviceWidths;
		for (let i = d.length - 1; i >= 0; i--) if (w <= d[i]) return i;
		return 0; // wider than the widest design → wide desktop
	};

	// Resolve a per-level field (scalar = same on all levels; array of 5 with "#a" = inherit). "#a"/empty
	// inherits from the nearest concrete level above (toward wdesktop), then below — same idea as SR7.gVal.
	SR7.PE.lev = (field, level) => {
		if (!Array.isArray(field)) return field;            // legacy scalar → identical on every level
		let v = field[level];
		if (v != null && v !== "#a" && v !== "") return v;
		for (let i = level - 1; i >= 0; i--) { v = field[i]; if (v != null && v !== "#a" && v !== "") return v; }
		for (let i = level + 1; i < 5; i++) { v = field[i]; if (v != null && v !== "#a" && v !== "") return v; }
		return 0;
	};

	SR7.PE.registerRuntime = (type, def) => {
		SR7.PE.runtimes[type] = def;
		SR7.PE.boot();                      // a runtime may register after the data is already present
	};

	SR7.PE.boot = () => {
		if (SR7.E && SR7.E.backend) return; // editor preview is handled by the recorder, not the front overlay
		const pe = SR7.E && SR7.E.pageEffects;
		if (!pe) return;
		for (const id in pe) {
			if (!Object.prototype.hasOwnProperty.call(pe, id)) continue;
			const entry = pe[id], rt = entry && SR7.PE.runtimes[entry.type];
			if (!entry || !rt || SR7.PE._mounted[id]) continue;
			try { SR7.PE._mounted[id] = rt.mount(entry.data, id) || true; } // per-effect isolation
			catch (e) { /* one bad effect must not break the others */ }
		}
	};

	if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", SR7.PE.boot);
	else SR7.PE.boot();
})();
