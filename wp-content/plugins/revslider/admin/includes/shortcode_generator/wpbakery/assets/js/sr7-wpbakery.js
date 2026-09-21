/**
 * Elementor Editor Controller
 */
(function() {
	"use strict";

	window.SR7 ??= {};
	window.SR7.E ??= {};
	window.SR7.B ??= {};
	window._tpt ??=  {};


    // ensure custom WPBakery param type doesn't wipe out our saved values
    if (typeof window.vc !== "undefined" && window.vc.atts) {
        // add parser for our custom rev_slider_shortcode parameter type
        vc.atts.rev_slider_shortcode = {
            parse: function(param) {
                // first try to read an actual input element (shouldn't exist normally)
                var $el = this.content().find('.wpb_vc_param_value[name="' + param.param_name + '"]');
                if ($el.length) {
                    return $el.val();
                }
                // fallback to model value so the alias/other settings aren't lost
                var params = this.model.get('params') || {};
                return typeof params[param.param_name] !== 'undefined' ? params[param.param_name] : '';
            },
            render: function(param, value) {
                // nothing special to render
                return value;
            }
        };
    }

    //These are WPBakery's own toolbar icons, drawn into its control bar as it is built - before any
    //action has had a reason to fetch the shared block module. They stay local for that reason.
    const selectIcon = '<svg width="20" height="14.884" viewBox="0 0 20 14.884"><path d="M81.86-785.116a1.791,1.791,0,0,1-1.314-.547A1.791,1.791,0,0,1,80-786.977V-798.14a1.792,1.792,0,0,1,.547-1.314A1.792,1.792,0,0,1,81.86-800h5.581l1.86,1.86h7.442a1.792,1.792,0,0,1,1.314.547,1.792,1.792,0,0,1,.547,1.314H88.535l-1.86-1.86H81.86v11.163l2.233-7.442H100l-2.4,7.977a1.814,1.814,0,0,1-.686.965,1.846,1.846,0,0,1-1.1.36Zm1.953-1.86h12l1.674-5.581h-12Zm0,0,1.674-5.581Zm-1.953-9.3v0Z" transform="translate(-80 800)"></path></svg>';
    const editIcon = '<svg width="24" height="16.076" viewBox="0 0 24 16.076"><path d="M70.12-722.121l9.609-9.609a.257.257,0,0,0,.078-.189.257.257,0,0,0-.078-.189L78.6-733.234a.257.257,0,0,0-.189-.078.257.257,0,0,0-.189.078l-9.609,9.609Zm-7.258,2.093a6.921,6.921,0,0,1-3.875-1.148,3.381,3.381,0,0,1-1.295-2.838,3.39,3.39,0,0,1,1.475-2.85,7.982,7.982,0,0,1,4.1-1.325,4.944,4.944,0,0,0,1.892-.456,1.063,1.063,0,0,0,.631-.961,1.408,1.408,0,0,0-.853-1.3,8.2,8.2,0,0,0-2.8-.644l.158-1.724a8.373,8.373,0,0,1,3.947,1.15,2.9,2.9,0,0,1,1.28,2.518,2.6,2.6,0,0,1-1.058,2.183,5.794,5.794,0,0,1-3.071.969,6.949,6.949,0,0,0-2.976.774,1.869,1.869,0,0,0-.992,1.666,1.771,1.771,0,0,0,.826,1.6,5.9,5.9,0,0,0,2.679.646Zm7.529.091L66.432-723.9l10.8-10.79a1.618,1.618,0,0,1,1.2-.506,1.676,1.676,0,0,1,1.2.506l1.557,1.557a1.644,1.644,0,0,1,.512,1.2,1.644,1.644,0,0,1-.512,1.2Zm-3.864.8a.694.694,0,0,1-.69-.2.694.694,0,0,1-.2-.689l.8-3.864,3.959,3.959Z" transform="translate(-57.693 735.192)"></path></svg>';
    const settingsIcon = '<svg width="18" height="17" viewBox="0 0 18 17"><path  d="M7,3a4,4,0,0,1,3.874,3H19V8H10.874A4,4,0,1,1,7,3ZM7,9A2,2,0,1,0,5,7,2,2,0,0,0,7,9Z" transform="translate(-3 -3)" fill-rule="evenodd"/><path  d="M17,20a4,4,0,0,1-3.874-3H5V15h8.126A4,4,0,1,1,17,20Zm0-2a2,2,0,1,0-2-2A2,2,0,0,0,17,18Z" transform="translate(-3 -3)" fill-rule="evenodd"/></svg>';


    /**
     * Embed Modules to Content
     */
    SR7.B.wpBakeryShortcode = {
        inited : false,
        init : function() {
            if (this.inited) return;
            this.inited = true;
            SR7.E.block_editor = true;
            this.addListeners();
        },
        addListeners : function() {
            if (typeof vc==="undefined" || vc==undefined) return;

            window.VcSliderRevolution7 = vc.shortcode_view.extend({
                events: {
                    'click > .vc_controls .sr7--wpbakery--settings': 'sr7_settings',
                    'click > .vc_controls .sr7--wpbakery--quickedit': 'sr7_quickedit',
                    'click > .vc_controls .sr7--wpbakery--template': 'sr7_template',
                    'click > .vc_controls .sr7--wpbakery--edit': 'sr7_edit',
                    'click > .vc_controls .sr7--wpbakery--select': 'sr7_select',
                    'click .column_delete,.vc_control-btn-delete': 'deleteShortcode',
                    'click .vc_control-btn-edit': 'sr7_edit',
                    'click .column_clone,.vc_control-btn-clone': 'clone',
                    mousemove: "checkControlsPosition"
                },
                initialize: function() {
                    return window.VcSliderRevolution7.__super__.initialize.call(this);
                },
                ready: function() {
                    return window.VcSliderRevolution7.__super__.ready.call(this);
                },
                render: function () {
                    //Adding the element used to open the module picker straight away. There are two ways to
                    //fill it now - an existing module or a template - so the choice is left to the user.
                    window.VcSliderRevolution7.__super__.render.call(this);
                    SR7.B.wpBakeryShortcode.drawEmptyNote(this);

                    return this;
                },
                sr7_settings : function() {
                    SR7.B.wpBakeryShortcode.moduleSettings(null, this);
                },
                sr7_edit : function() {
                    SR7.B.wpBakeryShortcode.editModule(null, this);
                },
                sr7_select : function() {
                    SR7.B.wpBakeryShortcode.selectModule(null, this);
                },
                sr7_template : function() {
                    SR7.B.wpBakeryShortcode.importTemplate(null, this);
                },
                sr7_quickedit : function() {
                    SR7.B.wpBakeryShortcode.quickEdit(null, this);
                }
            });

            if(typeof(window.InlineShortcodeView) !== 'undefined') {			
                window.InlineShortcodeView_sr7 = window.InlineShortcodeView.extend({	
                    events: {
                        'click > .vc_controls .vc_control_rev_optimizer': 'rs_optim',
                        'click > .vc_controls .vc_control_rev_selector': 'rs_select',
                        'click > .vc_controls .vc_control_rev_settings': 'rs_settings',
                        'click .column_delete,.vc_control-btn-delete': 'destroy',
                        'click .vc_control-btn-edit': 'edit',					
                        mousemove: "checkControlsPosition"
                    },					
                    render: function() {
                        window.VcSliderRevolution7.__super__.render.call(this);
                        SR7.B.wpBakeryShortcode.drawControls(this.$controls?.[0], this);

                        return this;
                    },
                    update: function(model) {	window.InlineShortcodeView_sr7.__super__.update.call(this, model);return this;},
                });		
            };
                
            jQuery(document).on('mouseenter','.wpb_sr7.wpb_content_element.wpb_sortable,.vc_element-container.ui-sortable', (e) => {
                const controls = e.currentTarget.querySelector(".vc_controls-cc");


                this.drawControls(controls, null, e.currentTarget);
            });	
        },
        /**
         * Draw our buttons into one element's control bar.
         *
         * One list, reached two ways: the inline view draws it as the element renders, and the hover
         * handler draws it for elements WPBakery had already put on the page. It used to be written out in
         * both places, which is two lists to keep in step.
         *
         * Ours are cleared and rebuilt rather than added to, because this runs again the moment a module is
         * chosen and the bar has three more buttons then than it had a second earlier.
         *
         * @param {Element} controls  the control bar
         * @param {object}  view      the element's view, when the caller has one
         * @param {Element} container the element, for looking its model up when the caller has no view
         */
        drawControls : function(controls, view, container) {
            if (!controls) return;

            const mv = controls.querySelector('.vc_element-move');
            if (!mv) return;

            const params = view?.model ? view.model.get("params") : this.paramsFor(container);
            const picked = this.hasModule(params);

            //Nothing to do if the bar already says what it should. Without this the hover handler would
            //rebuild it on every pass of the pointer.
            const state = picked ? "1" : "0";
            if (controls.dataset.revsliderControls === state) return;
            controls.dataset.revsliderControls = state;

            for (const icon of Array.from(controls.querySelectorAll('.sr7--wpbakery--icon'))) icon.remove();

            //A view gets its buttons wired directly; without one they are left to the view's own event map.
            const on = action => view ? (e => this[action](e, view)) : undefined;

            //each goes straight after the move control, so the bar reads in the reverse of this
            if (picked) mv.after(this.addItem("Module Settings", settingsIcon, "sr7--wpbakery--settings", on("moduleSettings")));
            if (picked) mv.after(this.addItem("Quick Edit", editIcon, "sr7--wpbakery--quickedit", on("quickEdit")));
            if (picked) mv.after(this.addItem("Edit Module", editIcon, "sr7--wpbakery--edit", on("editModule")));
            mv.after(this.addItem("Import Template", selectIcon, "sr7--wpbakery--template", on("importTemplate")));
            mv.after(this.addItem("Select Module", selectIcon, "sr7--wpbakery--select", on("selectModule")));

            const clone = controls.querySelector('.vc_control-btn-clone');
            if (clone) clone.style.display = "none";
        },

        /**
         * Say so when the element holds no module yet.
         *
         * WPBakery builds the element's body from its admin labels, and hides any whose value is empty -
         * they come out as spans carrying hidden-label and display:none. Before a module is picked both of
         * ours are empty, so the element showed its name and then a blank line under it.
         */
        drawEmptyNote : function(view) {
            const wrap = (view?.$el?.[0] || view?.el)?.querySelector('.wpb_element_wrapper');
            if (!wrap) return;

            let note = wrap.querySelector('.sr7--wpbakery--empty');
            if (!note) {
                note = document.createElement('span');
                note.className = 'vc_admin_label sr7--wpbakery--empty';
                note.textContent = (SR7.t ? SR7.t('No Module Selected') : 'No Module Selected');
                wrap.append(note);
            }

            //hasModule reads an unknown element as having one, so a note is never left on a working block
            note.style.display = this.hasModule(view?.model?.get('params')) ? 'none' : '';
        },

        /**
         * Draw the bar again for one element, after its module has changed.
         *
         * The bar is built before a module is chosen, so Edit Module, Quick Edit and Module Settings are
         * not on it. Choosing one has to build it again or they only appear after a save and a reload.
         */
        refreshControls : function(view) {
            const el = view?.$el?.[0] || view?.el;
            if (!el) return;
            this.drawControls(el.querySelector(".vc_controls-cc") || el.querySelector(".vc_controls"), view, el);
        },
        addItem : function(title, icon, className, action) {
            const buttonContentIcons = document.createElement("i");
            buttonContentIcons.classList.add("vc-composer-icon");
            buttonContentIcons.innerHTML = icon;

            const buttonContent = document.createElement("span");
            buttonContent.classList.add("vc_btn-content");
            buttonContent.append(buttonContentIcons);

            let selectButton = document.createElement("a");
            selectButton.classList.add("vc_control-btn");
            selectButton.classList.add("sr7--wpbakery--icon");
            selectButton.classList.add(className);
            selectButton.setAttribute("href", "#");
            selectButton.setAttribute("title", title);
            selectButton.append(buttonContent);
            if (typeof action === "function") {
                selectButton.addEventListener("click", action);
            }

            return selectButton;
        },
        //WPBakery keeps everything for one element in its model's params. Reading and writing that is all
        //this integration has to say for itself; the actions below are the shared ones (CONTRACT B6).
        adapter : {
            read : view => {
                const params = view?.model?.get("params") || {};
                const model = SR7.Block.normalize(SR7.B.shortcode.parseParams(params));
                //the element's own fields, which are not shortcode attributes
                model.title = params.slidertitle;
                model.moduleId = params.moduleid;
                return model;
            },
            write : (view, model) => {
                if (!view?.model) return;
                //Every attribute the grammar can carry is named, so one the model no longer sets is cleared
                //rather than left behind from the last save: an override switched off used to keep its
                //fullwidth in the saved page and come back on with it after a reload.
                const params = Object.assign({
                    alias: "", usage: "", modal: "", zindex: "", fullwidth: "", fullheight: "",
                    offset: "", wrapperid: "", class: ""
                }, SR7.Block.toParams(model));
                //VC turns a boolean false into an empty attribute, which is how it says "cleared", so a
                //switch that is off would take the override down with it. Written as text it survives.
                for (const key in params) if (typeof params[key] === "boolean") params[key] = params[key] ? "true" : "false";
                if (model.title) params.slidertitle = model.title;
                if (model.moduleId) params.moduleid = model.moduleId;
                view.model.set("params", params);
                view.model.save({params});
                //both were drawn when there was no module on this element; there is one now
                SR7.B.wpBakeryShortcode.refreshControls(view);
                SR7.B.wpBakeryShortcode.drawEmptyNote(view);
            }
        },

        //The element a control bar belongs to. VC keeps its models in vc.shortcodes, keyed by the id the
        //container carries. Returning null means we could not tell, and the caller then assumes a module
        //is set rather than hiding controls on a block that has one.
        paramsFor : function(container) {
            const id = container?.dataset?.modelId || (container?.getAttribute && container.getAttribute("data-model-id"));
            const model = (id && window.vc?.shortcodes?.get) ? vc.shortcodes.get(id) : null;
            return model ? (model.get("params") || {}) : null;
        },
        //null = unknown, and unknown is treated as "yes" so a working element never loses its controls
        hasModule : function(params) {
            return params === null || params === undefined ? true : !!params.alias;
        },

        //Every control does the same two things: make sure the shared module is here, then hand over to it
        run : function(e, view, action) {
            e?.preventDefault();
            if (!view?.model) return;
            //belt and braces: these three are not drawn without a module, but nothing should act on none
            if (["openEditor", "quickEdit", "settings"].indexOf(action) !== -1 && !this.hasModule(view.model.get("params"))) return;
            _tpt.regResource({id: "tools_shortcode", url: SR7.E.plugin_url + "admin/assets/js/tools/shortcode.js"});
            _tpt.checkResources(["tools_shortcode"]).then(() => {
                SR7.Builders.register("wpbakery", this.adapter);
                SR7.Builders[action]("wpbakery", view);
            });
        },
        selectModule   : function(e, view) { this.run(e, view, "pickModule"); },
        importTemplate : function(e, view) { this.run(e, view, "pickTemplate"); },
        editModule     : function(e, view) { this.run(e, view, "openEditor"); },
        quickEdit      : function(e, view) { this.run(e, view, "quickEdit"); },
        moduleSettings : function(e, view) { this.run(e, view, "settings"); },
    };


	// Init on load
	if (document.readyState === "loading") 
		document.addEventListener('readystatechange',function(){
			if (document.readyState === "interactive" || document.readyState === "complete") {
				SR7.B.wpBakeryShortcode.init();
			}
		});
	else {
		SR7.B.wpBakeryShortcode.init();
	}


	// Update Required Setings	
	_tpt.R ??= {};
	_tpt.R.wpBakeryShortcode =  _tpt.extend ?  _tpt.extend(_tpt.R.wpBakeryShortcode, { status : 2, version : '1.0'}) : {status:2,version:'1.0'};	

})();
