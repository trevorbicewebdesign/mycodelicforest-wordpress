/**
 * Avada / Fusion Builder controller.
 *
 * Fusion keeps an element's state in its model's params, and for this element those params *are* the
 * shortcode attributes - it is written to the page as [rev_slider ...] and read back by the same
 * shortcode core renders. So reading them into a block model and writing one back is all this integration
 * has to say for itself; the card, the pickers, quick edit and the settings modal are the shared ones
 * (CONTRACT B6).
 *
 * One file for both of Avada's builders. They share a settings view name, and Fusion falls back to this
 * script when an element declares no separate front end one - so the panel is written once. The live
 * builder needs one thing more: a card on the canvas wherever the module itself must not be rendered.
 */
( function( $ ) {
	"use strict";

	window.SR7 ??= {};
	window.SR7.E ??= {};
	window.SR7.B ??= {};
	window._tpt ??= {};

	//Everything a Fusion element can honour: its params hold the whole grammar, so nothing is withheld
	const ACTIONS = ["select", "template", "editor", "quick", "settings"];

	//"true"/"yes" is on and "false" is off. Anything else - "no", missing - was never set: Avada's own
	//element wrote "no" for both "off" and "leave it to the module", and only ever meant the second.
	const tri = v => (v === "true" || v === "yes" || v === true) ? true
		: ((v === "false" || v === false) ? false : undefined);

	SR7.B.avadaShortcode = {
		inited : false,

		init : function() {
			if (this.inited) return;
			this.inited = true;

			SR7.E.block_editor = true;
			if (window.SR7AvadaLiveData) SR7.E.registered = SR7AvadaLiveData.registered ?? SR7.E.registered;
			//the live builder is a front end page, where nothing else has told us where the plugin lives
			if (window.SR7ShortcodeData) {
				SR7.E.ajaxurl ??= SR7ShortcodeData.ajaxurl;
				SR7.E.plugin_url ??= SR7ShortcodeData.plugin_url;
			}

			this.extendSettingsView();
			this.extendCanvasView();
		},

		//Fusion's params in, block model out - and back again
		adapter : {
			read : view => {
				const p = Object.assign({}, view?.model?.get("params") || {});

				const m = SR7.Block.normalize(SR7.B.shortcode.parseParams(p));

				m.layout.fullwidth	= tri(p.fullwidth);
				m.layout.fullheight	= tri(p.fullheight);
				//The shared rule reads an override off the attributes being there at all, which Avada cannot use:
				//it wrote "no" for unset, so the same rule has to run after tri() has said what is really set.
				m.layout.override	= m.layout.fullwidth !== undefined || m.layout.fullheight !== undefined;
				m.wrapperid			= p.wrapperid || p.id || "";	//the element called it id before B3 named it
				m.cssclass			= p.class || "";

				//what the shortcode cannot say: which module this is, and what it looks like
				m.title		= p.title;
				m.moduleId	= p.m_id;
				m.type		= p.type;
				m.slides	= p.slides;
				m.cover		= {image: p.image, color: p.color};
				m.premium	= p.premium === "yes";
				m.notFound	= p.not_found === "yes";
				return m;
			},

			write : (view, model) => {
				if (!view?.model) return;
				const self = SR7.B.avadaShortcode;

				const attrs = SR7.Block.toParams(model);
				for (let key in attrs) if (typeof attrs[key] === "boolean") attrs[key] = attrs[key] ? "true" : "false";

				//Every attribute the grammar can carry is named here, so one the model no longer sets is
				//cleared rather than left behind from the last time it did. id goes with them: what it held
				//is written as wrapperid now.
				const next = Object.assign({
					alias: "", usage: "", modal: "", zindex: "", fullwidth: "", fullheight: "",
					offset: "", wrapperid: "", class: "", id: ""
				}, attrs, {
					title:		model.title || "",
					m_id:		model.moduleId || "",
					type:		model.type || "",
					slides:		model.slides || "",
					image:		model.cover?.image || "",
					color:		model.cover?.color || "",
					premium:	model.premium ? "yes" : "no",
					not_found:	model.notFound ? "yes" : "no"
				});

				const params = Object.assign({}, view.model.get("params") || {}, next);
				view.model.set("params", params);
				self.syncFields(view, params);
				self.drawPanelCard(view);
			},

			//Quick Edit is scoped to the post being edited, which each builder keeps somewhere else
			postId : () => window.FusionApp?.postID || $("#post_ID").val() || SR7.E.post_id || ""
		},

		/**
		 * Put the params where Fusion looks for them.
		 *
		 * The backend dialog reads its own fields when it saves, so the model alone is not enough. The live
		 * builder works the other way round - it is the change event that makes it re-render - so one is
		 * fired at the end rather than one per field, which would re-render the module once per attribute.
		 */
		syncFields : function(view, params) {
			for (let name in params) {
				const field = view.$el.find('[name="' + name + '"], #' + name);
				if (field.length) field.val(params[name] ?? "");
			}
			//Fusion drops a change whose value it has already recorded, and the model above holds them all
			//by now. This one is marked as always worth acting on, so the element still redraws.
			view.$el.find('[name="alias"]').addClass("fusion-always-update").trigger("change", [{userClicked: true}]);
		},

		//--- the builder's own panel ---------------------------------------------------------------------
		extendSettingsView : function() {
			if (typeof FusionPageBuilder === "undefined" || !FusionPageBuilder.ElementSettingsView) return;

			const Base = FusionPageBuilder.ElementSettingsView;
			const self = this;

			//No template of its own: Fusion draws the options, and the card is one of them (an info param).
			//What is left to do is fill that card in.
			FusionPageBuilder.ModuleSettingsSR7View = Base.extend({
				onRender : function() {
					if (typeof Base.prototype.onRender === "function") Base.prototype.onRender.apply(this, arguments);
					self.drawPanelCard(this);
				}
			});
		},

		drawPanelCard : function(view) {
			const host = view?.el?.querySelector(".sr--avada--card");
			//tptools is fetched asynchronously on the screens the backend builder runs on, and nothing below
			//can run before it lands. This is called from Fusion's own render(), which a throw would take with it.
			if (!host || typeof _tpt.checkResources !== "function") return;

			this.withShared().then(() => {
				host.innerHTML = "";
				host.append(SR7.Builders.card("avada", view, {variant: "panel", actions: ACTIONS}));
			});
		},

		//The shared module, fetched the first time something needs it
		withShared : function() {
			_tpt.regResource({id: "tools_shortcode", url: SR7.E.plugin_url + "admin/assets/js/tools/shortcode.js"});
			return _tpt.checkResources(["tools_shortcode"]).then(() => {
				SR7.B.shortcode.fixAjaxUrl();
				SR7.Builders.register("avada", this.adapter);
			});
		},

		//--- the live builder's canvas -------------------------------------------------------------------
		extendCanvasView : function() {
			if (typeof FusionPageBuilder === "undefined" || !FusionPageBuilder.rev_slider) return;

			const Base = FusionPageBuilder.rev_slider;
			const self = this;

			FusionPageBuilder.rev_slider = Base.extend({
				filterRenderContent : function(output) {
					const card = self.canvasCard(this);
					return card !== null ? card : Base.prototype.filterRenderContent.call(this, output);
				},
				filterOutput : function(output) {
					const card = self.canvasCard(this);
					return card !== null ? card : Base.prototype.filterOutput.call(this, output);
				}
			});
		},

		/**
		 * The card standing in for the module, wherever the page must not show the module itself.
		 *
		 * Markup rather than an element, because Fusion inserts the string it is handed - so the card is
		 * drawn without its actions. Those belong in the settings panel anyway, where there is room to name
		 * them, and the canvas keeps the builder's own controls uncontested (as in BeBuilder).
		 *
		 * @return {String|null} null when the module itself should be rendered
		 */
		canvasCard : function(view) {
			if (!window.SR7?.Block) return null;

			const p = view.model.get("params") || {};
			const locked = p.premium === "yes" && !SR7.E.registered;
			//A module used as a modal has nothing to show in place: it renders hidden and waits for its
			//trigger, and a timed one left running here opens over the canvas with nothing to dismiss it
			//(CONTRACT B7).
			if (p.live_preview === "yes" && p.usage !== "modal" && !locked) return null;

			const card = SR7.Block.card(this.adapter.read(view), {actions: []});
			card.classList.add("sr--avada--module--preview--widget");
			return card.outerHTML;
		}
	};

	$(document).ready(function() {
		SR7.B.avadaShortcode.init();
	});

	// Update Required Setings
	_tpt.R ??= {};
	_tpt.R.avadaShortcode = _tpt.extend ? _tpt.extend(_tpt.R.avadaShortcode, {status: 2, version: '1.0'}) : {status: 2, version: '1.0'};

}( jQuery ) );
