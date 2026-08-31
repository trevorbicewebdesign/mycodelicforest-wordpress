/*
╔══════════════════════════════════════════════════════════════════════════════╗
║  PLUGIN:   SLIDER REVOLUTION 7                                                ║
║  MODULE:   PAGE EFFECTS  —  shared color picker (SR7-styled, self-contained)  ║
║  AUTHOR:   ThemePunch                                                         ║
╚══════════════════════════════════════════════════════════════════════════════╝

  A compact colour/gradient picker for the Page-Effects authoring UI, modelled on the SR7 editor's
  picker (SR-COLOR look + behaviour) but WITHOUT its substrate (GSAP / _tpt.obj / SR7.B.popUp / the
  modal partial) — those are editor-only and can't run in Gutenberg. Dependency-free vanilla JS +
  pointer events, styled from the shared skin (var(--pe-*)), and reusable by every effect.

  Value object V = { type:'solid'|'linear'|'radial', color:'#rrggbb', opacity:0-100,
                     angle:deg, stops:[{pos:0-100, color:'#rrggbb', opacity:0-100}] }

  API:  SR7.PE.colorField({value, onInput, onChange}) -> swatch element (has setValue/getValue)
        SR7.PE.openColorPicker(anchorEl, value, {onInput, onChange})
        SR7.PE.colorToCss(V) -> css color / gradient string   SR7.PE.normColor(v) -> V

  @copyright 2026 ThemePunch
*/
(() => {
	"use strict";

	window.SR7 ??= {};
	SR7.PE ??= {};

	// ---------- colour math ----------
	const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
	const hex2rgb = (h) => { h = String(h || "").replace("#", ""); if (h.length === 3) h = h.split("").map(c => c + c).join(""); const n = parseInt(h.slice(0, 6) || "ffffff", 16); return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255 }; };
	const hx = (v) => ("0" + Math.round(clamp(v, 0, 255)).toString(16)).slice(-2);
	const rgb2hex = (r, g, b) => "#" + hx(r) + hx(g) + hx(b);
	const rgb2hsv = (r, g, b) => { r /= 255; g /= 255; b /= 255; const mx = Math.max(r, g, b), mn = Math.min(r, g, b), d = mx - mn; let h = 0; if (d) { if (mx === r) h = ((g - b) / d) % 6; else if (mx === g) h = (b - r) / d + 2; else h = (r - g) / d + 4; h *= 60; if (h < 0) h += 360; } return { h, s: mx ? d / mx : 0, v: mx }; };
	const hsv2rgb = (h, s, v) => { const c = v * s, x = c * (1 - Math.abs((h / 60) % 2 - 1)), m = v - c; let t; h = (h % 360 + 360) % 360; if (h < 60) t = [c, x, 0]; else if (h < 120) t = [x, c, 0]; else if (h < 180) t = [0, c, x]; else if (h < 240) t = [0, x, c]; else if (h < 300) t = [x, 0, c]; else t = [c, 0, x]; return { r: (t[0] + m) * 255, g: (t[1] + m) * 255, b: (t[2] + m) * 255 }; };
	const rgbaStr = (hex, op) => { const c = hex2rgb(hex); return `rgba(${c.r | 0},${c.g | 0},${c.b | 0},${op == null ? 1 : op / 100})`; };

	SR7.PE.normColor = (v) => {
		if (v && typeof v === "object") {
			const stops = (Array.isArray(v.stops) && v.stops.length >= 2)
				? v.stops.map(s => ({ pos: clamp(+s.pos || 0, 0, 100), color: (typeof s.color === "string" && s.color[0] === "#") ? s.color.slice(0, 7) : "#ffffff", opacity: s.opacity == null ? 100 : clamp(+s.opacity, 0, 100) }))
				: null;
			return {
				type: (v.type === "linear" || v.type === "radial") ? v.type : "solid",
				color: (typeof v.color === "string" && v.color[0] === "#") ? v.color.slice(0, 7) : "#ffffff",
				opacity: v.opacity == null ? 100 : clamp(+v.opacity, 0, 100),
				angle: v.angle == null ? 90 : clamp(+v.angle, 0, 360),
				stops: stops || [{ pos: 0, color: (v.color && v.color[0] === "#") ? v.color.slice(0, 7) : "#ffffff", opacity: 100 }, { pos: 100, color: "#000000", opacity: 100 }]
			};
		}
		const hex = (typeof v === "string" && v[0] === "#") ? v.slice(0, 7) : "#ffffff";
		return { type: "solid", color: hex, opacity: 100, angle: 90, stops: [{ pos: 0, color: hex, opacity: 100 }, { pos: 100, color: "#000000", opacity: 100 }] };
	};

	SR7.PE.colorToCss = (V) => {
		V = SR7.PE.normColor(V);
		if (V.type === "solid") return rgbaStr(V.color, V.opacity);
		const st = V.stops.slice().sort((a, b) => a.pos - b.pos).map(s => rgbaStr(s.color, s.opacity) + " " + s.pos + "%").join(",");
		return V.type === "radial" ? "radial-gradient(circle," + st + ")" : "linear-gradient(" + V.angle + "deg," + st + ")";
	};
	const stopsBarCss = (V) => "linear-gradient(to right," + V.stops.slice().sort((a, b) => a.pos - b.pos).map(s => rgbaStr(s.color, s.opacity) + " " + s.pos + "%").join(",") + ")";

	// ---------- one-time CSS (scoped to .sr7pe-cp / .sr7pe-cp-swatch) ----------
	const CHECKER = "linear-gradient(45deg,#888 25%,transparent 25%),linear-gradient(-45deg,#888 25%,transparent 25%),linear-gradient(45deg,transparent 75%,#888 75%),linear-gradient(-45deg,transparent 75%,#888 75%)";
	const injectCss = () => {
		if (document.getElementById("sr7pe-cp-css")) return;
		const s = document.createElement("style"); s.id = "sr7pe-cp-css";
		s.textContent =
			".sr7pe-cp-swatch{width:40px;height:26px;padding:0;border:1px solid var(--pe-border-2,#4A4E51);border-radius:var(--pe-radius-s,4px);cursor:pointer;background:#fff;background-image:" + CHECKER + ";background-size:10px 10px;background-position:0 0,0 5px,5px -5px,-5px 0;overflow:hidden}" +
			".sr7pe-cp-swatch-fill{display:block;width:100%;height:100%}" +
			".sr7pe-cp{position:fixed;z-index:100020;width:236px;background:var(--pe-surface,#2A2C2F);border:1px solid var(--pe-border,#3F444A);border-radius:var(--pe-radius,10px);box-shadow:var(--pe-shadow,0 10px 30px rgba(0,0,0,.45));padding:12px;font-family:var(--pe-font,'Inter',system-ui,sans-serif);color:var(--pe-text,#C7CCD2);user-select:none}" +
			".sr7pe-cp-types{display:flex;gap:2px;padding:3px;background:var(--pe-bg,#1C1E20);border:1px solid var(--pe-border,#3F444A);border-radius:var(--pe-radius-s,4px);margin-bottom:10px}" +
			".sr7pe-cp-types button{flex:1;background:transparent;color:var(--pe-text-dim,#777C80);border:0;border-radius:3px;padding:5px 0;font:500 11px var(--pe-font,sans-serif);cursor:pointer}" +
			".sr7pe-cp-types button.on{background:var(--pe-accent,#309BFF);color:var(--pe-accent-text,#fff)}" +
			".sr7pe-cp-sv{position:relative;width:100%;height:128px;border-radius:6px;cursor:crosshair;margin-bottom:10px;overflow:hidden}" +
			".sr7pe-cp-cur{position:absolute;width:12px;height:12px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.4);transform:translate(-50%,-50%);pointer-events:none}" +
			".sr7pe-cp-track{position:relative;height:12px;border-radius:9999px;margin:0 5px 12px;cursor:pointer}" +
			".sr7pe-cp-hue{background:linear-gradient(to right,#f00 0%,#ff0 17%,#0f0 33%,#0ff 50%,#00f 67%,#f0f 83%,#f00 100%)}" +
			".sr7pe-cp-alpha{background-image:" + CHECKER + ";background-size:10px 10px;background-position:0 0,0 5px,5px -5px,-5px 0}" +
			".sr7pe-cp-alpha-fill{position:absolute;inset:0;border-radius:inherit}" +
			".sr7pe-cp-knob{position:absolute;top:50%;width:14px;height:14px;border-radius:50%;background:#fff;border:1px solid rgba(0,0,0,.35);box-shadow:0 1px 3px rgba(0,0,0,.4);transform:translate(-50%,-50%);pointer-events:none}" +
			".sr7pe-cp-fields{display:flex;gap:8px;align-items:center}" +
			".sr7pe-cp-fields input{font:600 12px var(--pe-font,sans-serif);color:var(--pe-text,#C7CCD2);background:var(--pe-surface-3,#383B41);border:1px solid var(--pe-border-2,#4A4E51);border-radius:var(--pe-radius-s,4px);padding:5px 7px;width:100%;box-sizing:border-box}" +
			".sr7pe-cp-fields input:focus{outline:none;border-color:var(--pe-accent,#309BFF)}" +
			".sr7pe-cp-hex{flex:1}.sr7pe-cp-op{width:58px;flex:0 0 58px}" +
			".sr7pe-cp-grad{margin-top:12px;border-top:1px solid rgba(255,255,255,.07);padding-top:10px}" +
			".sr7pe-cp-bar{position:relative;height:18px;border-radius:4px;margin-bottom:14px;background-image:" + CHECKER + ";background-size:10px 10px;cursor:copy}" +
			".sr7pe-cp-bar-fill{position:absolute;inset:0;border-radius:4px}" +
			".sr7pe-cp-stop{position:absolute;top:-3px;width:14px;height:24px;border-radius:3px;background:#fff;border:1px solid rgba(0,0,0,.4);box-shadow:0 1px 3px rgba(0,0,0,.4);transform:translateX(-50%);cursor:grab}" +
			".sr7pe-cp-stop.on{border-color:var(--pe-accent,#309BFF);box-shadow:0 0 0 2px var(--pe-accent,#309BFF)}" +
			".sr7pe-cp-stop-c{position:absolute;inset:2px;border-radius:2px}" +
			".sr7pe-cp-gradrow{display:flex;align-items:center;justify-content:space-between;gap:8px;font-size:11px;color:var(--pe-text-2,#AAAEB3)}" +
			".sr7pe-cp-angle{width:62px;flex:0 0 62px}" +
			".sr7pe-cp-del{color:var(--pe-danger,#FF6B6B);cursor:pointer;font-size:11px}.sr7pe-cp-del.off{opacity:.35;pointer-events:none}";
		document.head.appendChild(s);
	};

	// pointer drag → normalized [0..1] x,y inside el
	const track = (el, onMove) => {
		const pos = (e) => { const r = el.getBoundingClientRect(); return { x: clamp((e.clientX - r.left) / r.width, 0, 1), y: clamp((e.clientY - r.top) / r.height, 0, 1) }; };
		el.addEventListener("pointerdown", (e) => {
			e.preventDefault(); el.setPointerCapture && el.setPointerCapture(e.pointerId); onMove(pos(e), true);
			const mv = (ev) => onMove(pos(ev), false);
			const up = () => { el.removeEventListener("pointermove", mv); el.removeEventListener("pointerup", up); el.removeEventListener("pointercancel", up); };
			el.addEventListener("pointermove", mv); el.addEventListener("pointerup", up); el.addEventListener("pointercancel", up);
		});
	};

	let OPEN = null;
	const closePicker = (commit) => {
		if (!OPEN) return;
		const o = OPEN; OPEN = null;
		document.removeEventListener("pointerdown", o.outside, true);
		document.removeEventListener("keydown", o.onKey, true);
		if (o.el.parentNode) o.el.parentNode.removeChild(o.el);
		if (commit && o.handlers.onChange) o.handlers.onChange(o.V);
	};
	SR7.PE.closeColorPicker = closePicker;

	SR7.PE.openColorPicker = (anchor, value, handlers) => {
		injectCss();
		closePicker(false);
		handlers = handlers || {};
		const V = SR7.PE.normColor(value);
		let sel = 0;                       // selected gradient stop index
		const hsv = rgb2hsv(hex2rgb(V.color).r, hex2rgb(V.color).g, hex2rgb(V.color).b);

		const root = document.createElement("div"); root.className = "sr7pe-cp";

		// type toggle
		const types = document.createElement("div"); types.className = "sr7pe-cp-types";
		const tBtns = {};
		[["solid", "Solid"], ["linear", "Linear"], ["radial", "Radial"]].forEach(t => {
			const b = document.createElement("button"); b.textContent = t[1]; b.onclick = () => setType(t[0]); types.appendChild(b); tBtns[t[0]] = b;
		});
		root.appendChild(types);

		// SV field
		const sv = document.createElement("div"); sv.className = "sr7pe-cp-sv";
		const cur = document.createElement("div"); cur.className = "sr7pe-cp-cur"; sv.appendChild(cur);
		root.appendChild(sv);

		// hue + alpha
		const hue = document.createElement("div"); hue.className = "sr7pe-cp-track sr7pe-cp-hue";
		const hueKnob = document.createElement("div"); hueKnob.className = "sr7pe-cp-knob"; hue.appendChild(hueKnob); root.appendChild(hue);
		const alpha = document.createElement("div"); alpha.className = "sr7pe-cp-track sr7pe-cp-alpha";
		const alphaFill = document.createElement("div"); alphaFill.className = "sr7pe-cp-alpha-fill"; alpha.appendChild(alphaFill);
		const alphaKnob = document.createElement("div"); alphaKnob.className = "sr7pe-cp-knob"; alpha.appendChild(alphaKnob); root.appendChild(alpha);

		// hex + opacity
		const fields = document.createElement("div"); fields.className = "sr7pe-cp-fields";
		const hexIn = document.createElement("input"); hexIn.className = "sr7pe-cp-hex"; hexIn.spellcheck = false;
		const opIn = document.createElement("input"); opIn.type = "number"; opIn.min = 0; opIn.max = 100; opIn.className = "sr7pe-cp-op";
		fields.appendChild(hexIn); fields.appendChild(opIn); root.appendChild(fields);

		// gradient editor
		const grad = document.createElement("div"); grad.className = "sr7pe-cp-grad";
		const bar = document.createElement("div"); bar.className = "sr7pe-cp-bar";
		const barFill = document.createElement("div"); barFill.className = "sr7pe-cp-bar-fill"; bar.appendChild(barFill);
		grad.appendChild(bar);
		const gradRow = document.createElement("div"); gradRow.className = "sr7pe-cp-gradrow";
		const del = document.createElement("span"); del.className = "sr7pe-cp-del"; del.textContent = "✕ remove stop";
		const angleWrap = document.createElement("span"); angleWrap.style.cssText = "display:flex;align-items:center;gap:6px";
		const angleLbl = document.createElement("span"); angleLbl.textContent = "Angle";
		const angleIn = document.createElement("input"); angleIn.type = "number"; angleIn.min = 0; angleIn.max = 360; angleIn.className = "sr7pe-cp-angle";
		angleWrap.appendChild(angleLbl); angleWrap.appendChild(angleIn);
		gradRow.appendChild(del); gradRow.appendChild(angleWrap);
		grad.appendChild(gradRow); root.appendChild(grad);

		// ---- value access (solid vs selected stop) ----
		const cc = () => V.type === "solid" ? V : V.stops[sel];
		const emit = () => { if (handlers.onInput) handlers.onInput(V); };
		const syncFromColor = () => { const c = hex2rgb(cc().color), h2 = rgb2hsv(c.r, c.g, c.b); hsv.s = h2.s; hsv.v = h2.v; if (h2.s > 0.001 && h2.v > 0.001) hsv.h = h2.h; };
		const applyHsv = () => { const c = hsv2rgb(hsv.h, hsv.s, hsv.v); cc().color = rgb2hex(c.r, c.g, c.b); };

		const renderStops = () => {
			[].slice.call(bar.querySelectorAll(".sr7pe-cp-stop")).forEach(n => n.remove());
			barFill.style.background = stopsBarCss(V);
			V.stops.forEach((s, i) => {
				const st = document.createElement("div"); st.className = "sr7pe-cp-stop" + (i === sel ? " on" : ""); st.style.left = s.pos + "%";
				const c = document.createElement("div"); c.className = "sr7pe-cp-stop-c"; c.style.background = rgbaStr(s.color, s.opacity); st.appendChild(c);
				st.addEventListener("pointerdown", (e) => { e.stopPropagation(); sel = i; startStopDrag(i); syncFromColor(); sync(); });
				bar.appendChild(st);
			});
			del.classList.toggle("off", V.stops.length <= 2);
		};
		const startStopDrag = (i) => {
			const r = bar.getBoundingClientRect();
			const mv = (ev) => { V.stops[i].pos = Math.round(clamp((ev.clientX - r.left) / r.width, 0, 1) * 100); renderStops(); emit(); };
			const up = () => { document.removeEventListener("pointermove", mv); document.removeEventListener("pointerup", up); };
			document.addEventListener("pointermove", mv); document.addEventListener("pointerup", up);
		};
		const sampleAt = (pos) => { // interpolate color at pos% across current stops
			const ss = V.stops.slice().sort((a, b) => a.pos - b.pos);
			let lo = ss[0], hi = ss[ss.length - 1];
			for (let i = 0; i < ss.length; i++) { if (ss[i].pos <= pos) lo = ss[i]; if (ss[i].pos >= pos) { hi = ss[i]; break; } }
			const span = (hi.pos - lo.pos) || 1, t = clamp((pos - lo.pos) / span, 0, 1);
			const a = hex2rgb(lo.color), b = hex2rgb(hi.color);
			return { color: rgb2hex(a.r + (b.r - a.r) * t, a.g + (b.g - a.g) * t, a.b + (b.b - a.b) * t), opacity: Math.round(lo.opacity + (hi.opacity - lo.opacity) * t) };
		};
		bar.addEventListener("pointerdown", (e) => {
			if (e.target.closest(".sr7pe-cp-stop")) return;
			const r = bar.getBoundingClientRect(), pos = Math.round(clamp((e.clientX - r.left) / r.width, 0, 1) * 100);
			const smp = sampleAt(pos); V.stops.push({ pos, color: smp.color, opacity: smp.opacity }); sel = V.stops.length - 1;
			syncFromColor(); renderStops(); sync(); emit();
		});
		del.onclick = () => { if (V.stops.length <= 2) return; V.stops.splice(sel, 1); sel = Math.max(0, sel - 1); syncFromColor(); renderStops(); sync(); emit(); };
		angleIn.oninput = () => { V.angle = clamp(parseInt(angleIn.value, 10) || 0, 0, 360); emit(); };

		const setType = (t) => {
			V.type = t;
			if (t !== "solid" && (!V.stops || V.stops.length < 2)) V.stops = [{ pos: 0, color: V.color, opacity: V.opacity }, { pos: 100, color: "#000000", opacity: 100 }];
			if (t !== "solid") sel = Math.min(sel, V.stops.length - 1);
			syncFromColor(); sync(); emit();
		};

		// ---- master sync: reflect V/hsv into every control ----
		const sync = () => {
			Object.keys(tBtns).forEach(k => tBtns[k].classList.toggle("on", V.type === k));
			const isGrad = V.type !== "solid";
			grad.style.display = isGrad ? "" : "none";
			angleWrap.style.display = V.type === "linear" ? "" : "none";
			const c = cc();
			sv.style.background = "linear-gradient(to top,#000,transparent),linear-gradient(to right,#fff,hsl(" + Math.round(hsv.h) + ",100%,50%))";
			cur.style.left = (hsv.s * 100) + "%"; cur.style.top = ((1 - hsv.v) * 100) + "%";
			cur.style.background = c.color;
			hueKnob.style.left = (hsv.h / 360 * 100) + "%";
			alphaFill.style.background = "linear-gradient(to right," + rgbaStr(c.color, 0) + "," + rgbaStr(c.color, 100) + ")";
			alphaKnob.style.left = c.opacity + "%";
			hexIn.value = c.color; opIn.value = c.opacity; angleIn.value = V.angle;
			if (isGrad) renderStops();
		};

		track(sv, (p) => { hsv.s = p.x; hsv.v = 1 - p.y; applyHsv(); sync(); emit(); });
		track(hue, (p) => { hsv.h = p.x * 360; applyHsv(); sync(); emit(); });
		track(alpha, (p) => { cc().opacity = Math.round(p.x * 100); sync(); emit(); });
		hexIn.onchange = () => { let v = hexIn.value.trim(); if (v[0] !== "#") v = "#" + v; if (/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v)) { const c = hex2rgb(v); cc().color = rgb2hex(c.r, c.g, c.b); syncFromColor(); sync(); emit(); } else sync(); };
		opIn.oninput = () => { cc().opacity = clamp(parseInt(opIn.value, 10) || 0, 0, 100); sync(); emit(); };

		// position near the anchor (prefer right of the swatch; flip/clamp to viewport)
		document.body.appendChild(root);
		const ar = anchor.getBoundingClientRect(), pw = root.offsetWidth || 236, ph = root.offsetHeight || 360;
		let left = ar.right + 8; if (left + pw > window.innerWidth - 8) left = ar.left - pw - 8; if (left < 8) left = 8;
		let top = ar.top; if (top + ph > window.innerHeight - 8) top = Math.max(8, window.innerHeight - ph - 8);
		root.style.left = left + "px"; root.style.top = top + "px";

		const outside = (e) => { if (!root.contains(e.target) && e.target !== anchor && !anchor.contains(e.target)) closePicker(true); };
		const onKey = (e) => { if (e.key === "Escape") closePicker(true); };
		document.addEventListener("pointerdown", outside, true);
		document.addEventListener("keydown", onKey, true);

		OPEN = { el: root, V, handlers, outside, onKey };
		sync();
		return root;
	};

	// swatch field that opens the picker
	SR7.PE.colorField = (opts) => {
		injectCss();
		opts = opts || {};
		let V = SR7.PE.normColor(opts.value);
		const sw = document.createElement("button"); sw.type = "button"; sw.className = "sr7pe-cp-swatch";
		const fill = document.createElement("span"); fill.className = "sr7pe-cp-swatch-fill"; sw.appendChild(fill);
		const paint = () => { fill.style.background = SR7.PE.colorToCss(V); };
		paint();
		sw.addEventListener("click", (e) => {
			e.preventDefault();
			SR7.PE.openColorPicker(sw, V, {
				onInput: (nv) => { V = nv; paint(); if (opts.onInput) opts.onInput(V); },
				onChange: (nv) => { V = nv; paint(); if (opts.onChange) opts.onChange(V); }
			});
		});
		sw.setValue = (v) => { V = SR7.PE.normColor(v); paint(); };
		sw.getValue = () => V;
		return sw;
	};
})();
