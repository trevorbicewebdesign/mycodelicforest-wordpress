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
                    if (vc.add_element_block_view.$el.is(':visible')) {
                        SR7.B.wpBakeryShortcode.selectModule(null, this);
                    }
                    window.VcSliderRevolution7.__super__.render.call(this);

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
                }
            });

            if(typeof(window.InlineShortcodeView) !== 'undefined') {			
                var rs_show_frontend_overlay = false;
                jQuery(window).on('vc_build', function() {				
                    vc.add_element_block_view.$el.find('[data-element="sr7"]').on('click',function() {
                        rs_show_frontend_overlay = true;
                    });				
                });
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
                        const wpbs = SR7.B.wpBakeryShortcode;

                        if (rs_show_frontend_overlay) {
                            wpbs.selectModule(null, this);
                        }
                        window.VcSliderRevolution7.__super__.render.call(this);

                        var mv = this.$controls.find('.vc_element-move');
                        if (!mv) return;

                        mv.after(wpbs.addItem("Module Settings", settingsIcon, "sr7--wpbakery--settings", e => wpbs.moduleSettings(e, this)));
                        mv.after(wpbs.addItem("Edit Module", editIcon, "sr7--wpbakery--edit", e => wpbs.editModule(e, this)));
                        mv.after(wpbs.addItem("Select Module", selectIcon, "sr7--wpbakery--select", e => wpbs.selectModule(e, this)));

                        this.$controls.find('.vc_control-btn-clone').hide();

                        return this;				
                    },
                    update: function(model) {	rs_show_frontend_overlay = false;window.InlineShortcodeView_sr7.__super__.update.call(this, model);return this;},
                });		
            };
                
            jQuery(document).on('mouseenter','.wpb_sr7.wpb_content_element.wpb_sortable,.vc_element-container.ui-sortable', (e) => {
                let controls = e.currentTarget.querySelector(".vc_controls-cc");
                if (!controls || controls.dataset.revsliderControls) return;

                controls.dataset.revsliderControls = true;
                let mv = controls.querySelector('.vc_element-move');
                if (!mv) return;

                mv.after(this.addItem("Module Settings", settingsIcon, "sr7--wpbakery--settings"));
                mv.after(this.addItem("Edit Module", editIcon, "sr7--wpbakery--edit"));
                mv.after(this.addItem("Select Module", selectIcon, "sr7--wpbakery--select"));

                let clone = controls.querySelector('.vc_control-btn-clone');
                if (clone) clone.style.display = "none";
            });	
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
        selectModule : function(e, view) {
            e?.preventDefault();
            if (!view?.model) return;
            _tpt.regResource({id: "tools_shortcode", url: SR7.E.plugin_url + "admin/assets/js/tools/shortcode.js"});
            _tpt.checkResources(['tools_shortcode']).then(() => {
                SR7.B.shortcode.openSelectModule(data => {
                    const params = {...view.model.get('params')};
                    params.alias = data.alias;
                    params.slidertitle = data.title;
                    params.moduleid = data.id;
                    view.model.set('params', params);
                    view.model.save({params});
                });
            });
        },
        editModule : function(e, view) {
            e?.preventDefault();
            const params = view?.model?.get('params');
            if (!view?.model) return;
            _tpt.regResource({id: "tools_shortcode", url: SR7.E.plugin_url + "admin/assets/js/tools/shortcode.js"});
            _tpt.checkResources(['tools_shortcode']).then(() => {
				SR7.B.shortcode.editModule(params?.moduleid);
            });
        },
        moduleSettings : function(e, view) {
            e?.preventDefault();
            if (!view?.model) return;
            _tpt.regResource({id: "tools_shortcode", url: SR7.E.plugin_url + "admin/assets/js/tools/shortcode.js"});
            _tpt.checkResources(['tools_shortcode']).then(() => {
                document.querySelector(".sr--block--editor--popup--wrap").style.display = "block";
                const params = view.model.get('params');
                const data = SR7.B.shortcode.parseParams(params);
                data.layoutOverride = data.fullwidth || data.fullheight;
                if (data.offset) {
					for (let device in data.offset) {
						let use = data.offset[device].use;
                        let o = {};
                        for (let side in data.offset[device]) if (side !== "use") {
                            o[side[0]] = data.offset[device][side];
                        }
						data.offset[device] = {o, use}
					}
				}
				SR7.B.shortcode.settings.open(data, data => {
                    const params = SR7.B.shortcode.generateParams(data);
                    view.model.set('params', params);
                    view.model.save({params});
                });
            });
        },
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
