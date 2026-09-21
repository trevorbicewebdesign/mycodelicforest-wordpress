/**
 * Elementor editor controller.
 *
 * Elementor keeps a widget's state in its own model, and the shortcode setting is the record of how the
 * module is embedded. Reading that into a block model and writing one back is all this integration says
 * for itself; the controls, the pickers, quick edit and the settings modal are the shared ones
 * (CONTRACT B6).
 */
(function() {
	"use strict";

	window.SR7 ??= {};
	window.SR7.E ??= {};
	window.SR7.B ??= {};
	window._tpt ??=  {};

	SR7.B.elementorShortcode = {
		inited     : false,
		resources  : null,
		checks     : {},
		colorScheme: null,
		ink        : {
			border:         "var(--sr-col-w6-d0)",
			disabledBorder: "var(--sr-col-w8-d0)",
			text:           "var(--sr-col-w0-d0)",
			surface:        "var(--sr-col-w12-d0)",
			hover:          "var(--sr-col-w9-d2)",
			off:            "var(--sr-col-w4-d0)"
		},

		init : function() {
			if (this.inited) return;
			this.inited = true;

			if (typeof elementor === "undefined" || !elementor?.hooks) return;

			this.colorScheme = window.matchMedia('(prefers-color-scheme: dark)');
			if (this.colorScheme.addEventListener) this.colorScheme.addEventListener('change', () => this.syncTheme());
			else this.colorScheme.addListener(() => this.syncTheme());
			elementor.settings?.editorPreferences?.model?.on?.('change:ui_theme', () => this.syncTheme());

			SR7.E.block_editor = true;

			elementor.hooks.addAction('panel/open_editor/widget/slider_revolution', (panel, model, view) => {
				const owner = model.cid || model.get?.('id');
				panel.el.sr7ElementorOwner = owner;
				panel.el.sr7ElementorObserver?.disconnect();
				panel.el.sr7ElementorObserver = null;

				this.ready().then(ready => {
					if (!ready) return;
					//The settings panel is reused. Do not let an older, slower open redraw it for another widget.
					if (panel.el.sr7ElementorOwner !== owner) return;

					const ctx = {panel, model, view};
					model.setSetting("registered", SR7.E.registered ? 'yes' : 'no');

					this.draw(ctx);

					//Elementor rebuilds the panel's sections as they are opened, so the card has to be put back
					panel.el.sr7ElementorObserver = new MutationObserver(() => {
						if (panel.el.sr7ElementorOwner === owner) this.draw(ctx);
					});
					panel.el.sr7ElementorObserver.observe(panel.el, {childList: true, subtree: true});

					//Also check here as a fallback for widgets rendered before the preview controller was ready.
					this.check(ctx);

					//nothing of ours lives in the Advanced tab any more
					const advanced = document.querySelector(".elementor-component-tab.elementor-panel-navigation-tab.elementor-tab-control-advanced");
					if (advanced) advanced.style.display = "none";
				});
			});

			const pending = SR7.E.elementorChecks || [];
			SR7.E.elementorChecks = [];
			pending.forEach(id => this.checkModule(id));
		},

		//tptools is async, so both the panel and preview wait for the shared block API before using it
		ready : function() {
			if (this.resources) return this.resources;
			this.resources = new Promise(resolve => {
				let waited = 0;
				const load = () => {
					if (typeof _tpt.regResource === "function" && typeof _tpt.checkResources === "function") {
						_tpt.regResource({id: "tools_shortcode", url: SR7.E.plugin_url + "admin/assets/js/tools/shortcode.js"});
						_tpt.checkResources(['tools_shortcode']).then(() => {
							SR7.Builders.register("elementor", this.adapter);
							resolve(true);
						});
						return;
					}
					if ((waited += 50) <= 15000) return setTimeout(load, 50);
					this.resources = null;
					resolve(false);
				};
				load();
			});
			return this.resources;
		},

		//The preview iframe reports every rendered widget, allowing deleted modules to be found without a click
		checkModule : function(id) {
			return this.ready().then(ready => {
				if (!ready) return null;
				const container = elementor.getContainer?.(id);
				if (!container?.model) return null;
				return this.check({model: container.model, view: container.view, container}, id);
			});
		},

		check : function(ctx, id) {
			const current = SR7.Builders.read("elementor", ctx);
			if (!current?.alias) return Promise.resolve(null);

			const alias = current.alias;
			const key = id || ctx.model.get?.('id') || ctx.model.cid || alias;
			if (this.checks[key]?.alias === alias) return this.checks[key].promise;

			const write = (data, notFound) => {
				const latest = SR7.Builders.read("elementor", ctx);
				if (latest?.alias !== alias) return null;
				const model = Object.assign(latest, data ? {
					alias: data.alias,
					moduleId: data.id,
					title: data.title,
					type: data.type,
					slides: data.slides,
					cover: data.cover,
					premium: data.premium
				} : {}, {notFound});
				return SR7.Builders.write("elementor", ctx, model);
			};

			const promise = SR7.B.shortcode.loadModule(alias).then(
				data => write(data, false),
				() => write(null, true)
			);
			this.checks[key] = {alias, promise};
			promise.finally(() => {
				if (this.checks[key]?.promise === promise) delete this.checks[key];
			});
			return promise;
		},

		//Elementor's model in, block model out - and back again
		adapter : {
			read : ctx => {
				const s = ctx.model.get('settings').toJSON();
				const m = SR7.Block.normalize(SR7.Block.fromShortcode(s.shortcode || ""));

				//what the shortcode cannot say: which module this is, and what it looks like
				if (s.alias) m.alias = s.alias;
				m.moduleId = s.moduleId;
				m.title    = s.title;
				m.type     = s.type;
				m.slides   = s.slides;
				m.cover    = {image: s.image, color: s.color};
				//Elementor stores these as its own yes/no strings. Read as booleans, or write() sees a truthy
				//"no" and turns every one of them into a yes.
				m.premium  = s.premium === 'yes' || s.premium === true;
				m.notFound = s.notFound === 'yes' || s.notFound === true;
				return m;
			},

			write : (ctx, model) => {
				const next = {
					shortcode: SR7.Block.toShortcode(model),
					alias:     model.alias || "",
					title:     model.title || "",
					moduleId:  model.moduleId || "",
					slides:    model.slides || "",
					type:      model.type || "",
					image:     model.cover?.image || "",
					color:     model.cover?.color || "",
					premium:   model.premium ? 'yes' : 'no',
					notFound:  model.notFound ? 'yes' : 'no',
					registered: SR7.E.registered ? 'yes' : 'no'
				};

				const changed = Object.entries(next).some(([key, value]) => ctx.model.getSetting(key) !== value);
				if (!changed) {
					SR7.B.elementorShortcode.draw(ctx);
					return;
				}

				//Through the container when there is one: that is what puts the change on the undo stack and
				//redraws the widget in the preview. Setting on the model alone does neither.
				const container =
					ctx.container ||
					ctx.view?.getContainer?.() ||
					ctx.view?.container ||
					elementor.getPreviewView?.()?.children?.findByModelCid?.(ctx.model.cid)?.getContainer?.();

				if (container) {
					container.settings.setExternalChange(next);
					container.render();
				} else {
					Object.entries(next).forEach(([key, value]) => ctx.model.setSetting(key, value));
				}

				SR7.B.elementorShortcode.markDirty();
				SR7.B.elementorShortcode.draw(ctx);
			},

			postId : () => elementor?.config?.document?.id || SR7.E.post_id || ""
		},

		//The panel card, put back whenever Elementor rebuilds the section it lives in
		draw : function(ctx) {
			if (!ctx.panel?.el) return;
			const host = ctx.panel.el.querySelector(".sr--elementor--card");
			if (!host) return;
			this.syncTheme(host);
			if (host.dataset.sr7drawn === this.stamp(ctx)) return;
			host.dataset.sr7drawn = this.stamp(ctx);
			host.innerHTML = "";
			host.append(SR7.Builders.card("elementor", ctx, {variant: "panel", ink: this.ink}));
		},

		syncTheme : host => {
			const controller = SR7.B.elementorShortcode;
			const preference = elementor.getPreferences?.('ui_theme') ?? elementor.settings?.editorPreferences?.model?.get?.('ui_theme') ?? 'auto';
			const dark = preference === 'dark' || (preference === 'auto' && controller.colorScheme?.matches);
			const hosts = host ? [host] : document.querySelectorAll(".sr--elementor--card");
			hosts.forEach(el => el.classList.toggle("sr-dark-theme", dark));
		},

		//What the card is currently showing, so it is only redrawn when that changes
		stamp : function(ctx) {
			const s = ctx.model.get('settings').toJSON();
			return [s.alias, s.title, s.slides, s.type, s.image, s.notFound, s.shortcode].join("|");
		},

		//Elementor decides for itself when a document is modified; a change made from outside its own
		//controls has to say so, or the Update button stays asleep.
		markDirty : function() {
			if (window.elementorV2?.editorDocuments?.setDocumentModifiedStatus) {
				window.elementorV2.editorDocuments.setDocumentModifiedStatus(true);
			} else if (window.$e?.internal) {
				$e.internal('document/save/set-is-modified', {status: true});
			} else if (elementor?.saver?.setFlagEditorChange) {
				elementor.saver.setFlagEditorChange(true);
			}
		}
	};

	// Init on load
	if (document.readyState === "loading")
		document.addEventListener('readystatechange',function(){
			if (document.readyState === "interactive" || document.readyState === "complete") {
				SR7.B.elementorShortcode.init();
			}
		});
	else {
		SR7.B.elementorShortcode.init();
	}


	// Update Required Setings
	_tpt.R ??= {};
	_tpt.R.elementorShortcode =  _tpt.extend ?  _tpt.extend(_tpt.R.elementorShortcode, { status : 2, version : '1.0'}) : {status:2,version:'1.0'};

})();
