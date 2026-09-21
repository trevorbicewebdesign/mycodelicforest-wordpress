/**
 * BeBuilder controller for Slider Revolution.
 *
 * BeTheme has its own "Slider Plugin" element and we hook it rather than adding one: the element keeps a
 * single field, a select holding our module's alias, and renders [rev_slider <alias>]. That is the whole
 * of what it can store - there is nowhere for the modal triggers, offsets or z-index to live - so this
 * builder offers everything except Module Settings (the surface rule, CONTRACT D4).
 *
 * Everything the controls do is shared (CONTRACT B6); what is here is how to read and write BeTheme's
 * field, and where to put the card.
 */
(function() {
	"use strict";

	window.SR7 ??= {};
	window.SR7.E ??= {};
	window.SR7.B ??= {};
	window._tpt ??= {};

	const MODULE_SLUG = "slider_plugin";

	const MODULE_TITLE = "Slider Revolution";

	//What this element can honour. No settings: BeTheme's element has one field and it holds the alias.
	const ACTIONS = ["select", "template", "editor", "quick"];

	SR7.B.beBuilderShortcode = {
		inited: false,
		cache: {},

		//The card writes its colours inline, which outranks any stylesheet, so the theme has to reach it
		//through these. They are BeTheme's own tokens, redefined under .mfn-ui-dark on the builder root the
		//card sits inside, so it follows whatever the builder is set to - Auto included - without asking.
		ink: {
			border:         "var(--mfn-ui-input-border)",
			disabledBorder: "var(--mfn-ui-input-disabled-bg)",
			text:           "var(--mfn-ui-btn-color)",
			surface:        "var(--mfn-ui-btn-bg)",
			hover:          "var(--mfn-ui-btn-bg-hover)",
			off:            "var(--mfn-ui-input-disabled-color)"
		},

		init() {
			if (this.inited || !document.body) return;
			this.inited = true;
			//Registered here when the shared module is already in, and again from render() when it is not.
			//shortcode.js is enqueued without being declared a dependency of this file, so on a cold load it
			//can still be arriving - and reaching for SR7.Builders then threw before the observer was set up,
			//which left the panel with no card at all rather than a late one.
			this.register();
			this.observeSliderSelector();
		},

		//idempotent: SR7.Builders.register overwrites its own entry
		register() {
			if (!window.SR7?.Builders?.register) return false;
			SR7.Builders.register("bebuilder", this.adapter);
			return true;
		},

		//BeTheme keeps the module in a select of its own, and everything we know about that module in a
		//cache beside it - the element itself has nowhere to put a title or a cover.
		adapter: {
			read(ctx) {
				const alias = (ctx.select.value && ctx.select.value !== "0") ? ctx.select.value : "";
				const known = SR7.B.beBuilderShortcode.cache[alias] || {};
				return SR7.Block.normalize({
					alias,
					moduleId: known.id,
					title: known.title,
					type: known.type,
					slides: known.slides,
					cover: known.cover,
					premium: known.premium,
					notFound: known.notFound
				});
			},
			write(ctx, model) {
				const self = SR7.B.beBuilderShortcode;
				const alias = model.alias || "";

				if (alias) {
					//BeTheme fills this list server side, when it builds the panel, so a module it has not seen -
					//a template imported a moment ago - has no option to select and the assignment below would be
					//a silent no-op.
					if (!Array.from(ctx.select.options).some(o => o.value === alias)) {
						ctx.select.add(new Option(model.title || alias, alias));
					}
					self.cache[alias] = {
						id: model.moduleId, title: model.title, type: model.type,
						slides: model.slides, cover: model.cover, premium: model.premium,
						notFound: model.notFound
					};
				}

				ctx.select.value = alias;
				ctx.select.dispatchEvent(new Event("change", {bubbles: true}));

				self.render(ctx);
				if (alias && !model.notFound) self.showInCanvas(ctx, model);
			},
			//Quick Edit is scoped to the post being edited, which BeBuilder keeps on the body
			postId() {
				return document.body?.dataset?.postId || SR7.E.post_id || "";
			}
		},

		observeSliderSelector() {
			//The settings panel is rebuilt every time an element is opened, so watch for it instead of polling for it
			const check = () => {
				const sliderSelector = document.querySelector(".mfn-form-row.mfn-field-select.slider_plugin.rev");
				if (sliderSelector && !sliderSelector.classList.contains("sr7--bebuilder--enhanced")) {
					this.enhanceSliderSelector(sliderSelector);
				}
			};
			new MutationObserver(check).observe(document.body, {childList: true, subtree: true});
			check();
		},

		enhanceSliderSelector(sliderSelector) {
			const select = sliderSelector.querySelector("select.mfn-field-value");
			if (!select) return; //Half built row - leave it unmarked so it can still be enhanced once BeBuilder has finished it

			sliderSelector.classList.add("sr7--bebuilder--enhanced");

			const host = document.createElement("div");
			host.className = "sr--bebuilder--module--info";
			sliderSelector.append(host);

			const ctx = {select, sliderSelector, host};
			this.render(ctx);

			//What the field already holds, filled in from the module itself
			if (select.value && select.value !== "0") this.loadDetails(ctx, select.value);

			//BeTheme's own select still works: follow it when it is used directly
			select.addEventListener("change", () => {
				const alias = select.value;
				if (alias && alias !== "0" && !this.cache[alias]) this.loadDetails(ctx, alias);
				else this.render(ctx);
			});
		},

		//The panel card, with the actions this element can honour
		render(ctx) {
			if (!this.register()) return;
			ctx.host.innerHTML = "";
			ctx.host.append(SR7.Builders.card("bebuilder", ctx, {variant: "panel", actions: ACTIONS, ink: this.ink}));
		},

		//Fill in everything about a module the field alone cannot say, then redraw
		loadDetails(ctx, alias) {
			const known = this.cache[alias];
			if (known) {
				this.render(ctx);
				this.showInCanvas(ctx, this.adapter.read(ctx));
				return;
			}
			SR7.B.shortcode.checkDepsLoaded(false)
				.then(() => SR7.B.shortcode.loadModule(alias))
				.then(data => {
					this.cache[alias] = {
						id: data.id, title: data.title, type: data.type, slides: data.slides,
						cover: data.cover, premium: data.premium, notFound: false
					};
					this.render(ctx);
					this.showInCanvas(ctx, this.adapter.read(ctx));
				})
				.catch(() => {
					this.cache[alias] = {notFound: true};
					this.render(ctx);
				});
		},

		//--- the builder's canvas, which is an iframe of its own ---------------------------------------
		showInCanvas(ctx, model) {
			const element = ctx.sliderSelector.closest(".mfn-element-fields-wrapper")?.dataset.element;
			if (element) this.observeWidget(element, model);
		},

		observeWidget(element, model) {
			const iframe = document.getElementById('mfn-vb-ifr');
			const iframeDocument = iframe?.contentDocument || iframe?.contentWindow?.document;
			if (!iframeDocument?.body) return;
			if (!iframeDocument.body.classList.contains("sr7--bebuilder--widget--enhanced")) {
				iframeDocument.body.classList.add("sr7--bebuilder--widget--enhanced");
				const previewStyle = document.getElementById('sr7-bebuilder-css-css');
				if (previewStyle) {
					iframeDocument.head.appendChild(previewStyle.cloneNode(true));
				}
			}
			//Wait for the widget to appear in the builder iframe, then stop watching. Only the newest selection is waited on
			this.widgetObserver?.disconnect();
			this.widgetObserver = null;
			const find = () => {
				const widget = iframeDocument.querySelector(`.${element}`);
				if (!widget) return false;
				this.widgetObserver?.disconnect();
				this.widgetObserver = null;
				this.enhanceWidget(widget, model, iframeDocument);
				return true;
			};
			if (find()) return;
			this.widgetObserver = new MutationObserver(find);
			this.widgetObserver.observe(iframeDocument.body, {childList: true, subtree: true});
		},

		enhanceWidget(widget, model, iframeDocument) {
			const widgetInner = widget.querySelector(".mcb-column-inner");
			if (!widgetInner) return;
			const widgetReplacedSlider = widgetInner.querySelector(".mfn-rev-slider");

			//The theme's wrapper is there as soon as the widget renders, but it only holds an sr7-module once
			//a module has actually been resolved - an unset or unknown alias leaves it empty
			if (widgetReplacedSlider?.querySelector('sr7-module')?.dataset.alias == model.alias) {
				if (widgetReplacedSlider.style.display == "none") widgetReplacedSlider.style.display = "block";
				widgetInner.querySelector(".sr--block--wrap")?.remove();
				return;
			}

			//the card standing in for a module the theme has not rendered, drawn in the canvas's own document
			widgetInner.querySelector(".sr--block--wrap")?.remove();
			widgetInner.append(SR7.Block.card(model, {actions: [], document: iframeDocument}));
			if (widgetReplacedSlider) widgetReplacedSlider.style.display = "none";
		},

		injectIntoItemsList(sourceItem) {
			const newItem = sourceItem.cloneNode(true);
			newItem.dataset.title = MODULE_TITLE;
			newItem.dataset.type = MODULE_SLUG;
			newItem.classList.add("mfn-item-slider_revolution");
			newItem.classList.remove("mfn-item-slider_plugin");
			newItem.querySelector("span.title").textContent = MODULE_TITLE;
			sourceItem.parentNode.insertBefore(newItem, sourceItem);
		}
	};

	if (document.readyState === "loading") {
		document.addEventListener("readystatechange", function() {
			if (document.readyState === "interactive" || document.readyState === "complete") {
				SR7.B.beBuilderShortcode.init();
			}
		});
	} else {
		SR7.B.beBuilderShortcode.init();
	}

	_tpt.R ??= {};
	_tpt.R.beBuilderShortcode = _tpt.extend ? _tpt.extend(_tpt.R.beBuilderShortcode, {status: 2, version: '1.0'}) : {status: 2, version: '1.0'};
})();
