/**
 * Elementor Editor Controller
 */
(function() {
	"use strict";

	window.SR7 ??= {};
	window.SR7.E ??= {};
	window.SR7.B ??= {};
	window._tpt ??=  {};

    const devices = ["w","d","n","t","m"];
    const sides = ['top', 'right', 'bottom', 'left'];

    /**
     * Embed Modules to Content
     */
    SR7.B.elementorShortcode = {
        inited : false,
        init : function() {
            if (this.inited) return;
            this.inited = true;

            _tpt.regResource({id: "tools_shortcode", url: SR7.E.plugin_url + "admin/assets/js/tools/shortcode.js"});

            if (typeof elementor === "undefined" || !elementor?.hooks) return;

            SR7.E.block_editor = true;

			// Widget Selection Hook
			elementor.hooks.addAction('panel/open_editor/widget/slider_revolution', (panel, model, view) => {

				model.setSetting("registered", SR7.E.registered ? 'yes' : 'no');
				const settings = model.get('settings');

                // Add event listeners
				let selectButton = panel.el.querySelector('button[data-event="sr7.selectModule"]');
				if (!selectButton.dataset.sr7actionadded) {
					selectButton.dataset.sr7actionadded = true;
					selectButton.addEventListener('click', () => SR7.B.elementorShortcode.selectModule(model, view));
				}

				let editButton = panel.el.querySelector('button[data-event="sr7.editModule"]');
				if (!editButton.dataset.sr7actionadded) {
					editButton.dataset.sr7actionadded = true;
					editButton.addEventListener('click', () => SR7.B.elementorShortcode.editModule(model));
				}

				const modelChanged = () => {
					// Handle dependencies
					this.updatePopupDependencies(panel, model);

					// Render module info
					this.renderModuleInfo(panel, model);

					// Update shortcode
					this.updateShortcode(model);
				};
				settings.on('change', changeModel => {
					modelChanged();
				});
				modelChanged();

				// Observe Panel State Changes
				const observer = new MutationObserver((mutations, observer) => {
					observer.disconnect();
					for (let mutation of mutations) {
						for (let added of mutation.addedNodes) {
							if (added.classList?.contains('e-open')) {
								if (added.classList.contains('elementor-control-sr_module')) {
									this.renderModuleInfo(panel, model, view);
								}
								else if (added.classList.contains('elementor-control-sr_popup')) {
									this.updatePopupDependencies(panel, model);
								}
								else if (added.classList.contains('elementor-control-sr_offsets')) {
									this.buildOffsetMatrix(panel, model);
								}
							}
						}
					}
					observer.observe(panel.el, {attributes: false, childList: true, subtree: true});
				});
				observer.observe(panel.el, {attributes: false, childList: true, subtree: true});

				// Read Shortcode Data
				this.processShortcode(model, view, view.container.settings.attributes.shortcode);

				// Build Offset Matrix
				this.buildOffsetMatrix(panel, model);

				// Show module selection if none selected
				if (!model.getSetting("shortcode")) {
					SR7.B.elementorShortcode.selectModule(model, view);
				}

				// Mute advanced tab
				document.querySelector(".elementor-component-tab.elementor-panel-navigation-tab.elementor-tab-control-advanced").style.display = "none";
			});
        },
        updatePopupDependencies : (panel, model) => {
            const s = model.get('settings').toJSON();
			const modal = s.modal;

			const toggleControl = (panel, controlName, show) => {
				const control = panel.$el.find(`.elementor-control [data-setting="${controlName}"]`).closest('.elementor-control');
				if (!control.length) return;
				control[show ? "show" : "hide"]();
			}

			toggleControl(panel, 'popup_cookie_use', modal);
			toggleControl(panel, 'popup_time_use', modal);
			toggleControl(panel, 'popup_scroll_use', modal);
			toggleControl(panel, 'popup_event_use', modal);
			toggleControl(panel, 'popup_hash_use', modal);
			toggleControl(panel, 'popup_cookie_value', modal && s.popup_cookie_use === 'yes');
			toggleControl(panel, 'popup_time_value', modal && s.popup_time_use === 'yes');
			toggleControl(panel, 'popup_scroll_type', s.popup_scroll_use === 'yes');
			toggleControl(panel, 'popup_scroll_offset', s.popup_scroll_use === 'yes' && s.popup_scroll_type === 'offset');
			toggleControl(panel, 'popup_scroll_container', s.popup_scroll_use === 'yes' && s.popup_scroll_type === 'container');
			toggleControl(panel, 'popup_event_name', modal && s.popup_event_use === 'yes');
			toggleControl(panel, 'popup_hash_info', modal && s.popup_hash_use === 'yes');

			const eventCode = panel.$el.find('.elementor-control [data-setting="popup_event_name"]').closest('.elementor-control').find('code');
			if (eventCode.length) eventCode[0].textContent = 'document.dispatchEvent(new Event("' + s.popup_event_name + '"))';

			const hashInfo = panel.$el.find('.sr--popup--hash--preview');
			if (hashInfo.length && s.popup_hash_use === 'yes') hashInfo[0].textContent = 'https://yourwebsite.com/yourpage/#' + s.alias;
        },
        renderModuleInfo : (panel, model, view) => {
            const settings = model.get('settings').toJSON();
            const container = panel.$el.find('.sr--elementor--module--info');
            if (!container.length || !settings.alias) return;

			const escapeHtml = (str) => {
				if (typeof str !== 'string') return str;
				return str
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;')
					.replace(/'/g, '&#039;');
			}

            const title  = escapeHtml(settings.title || settings.alias);
            const alias  = escapeHtml(settings.alias);
            const type = escapeHtml((settings.type ? settings.type.replace(/^./, char => char.toUpperCase()) : 'Slider') + ' with ' + settings.slides + ' ' + (settings.slides == 1 ? 'Slide' : 'Slides'));

            container.html(`
                <div class="sr--module--info--title">
                    <strong>${title}</strong>
                </div>
                <div class="sr--module--info--row">
                    <strong>Alias:</strong>
                    <span>${alias}</span>
                </div>
                <div class="sr--module--info--row">
                    <strong>Type:</strong>
                    <span>${type}</span>
                </div>
            `);

			let selectButton = panel.el.querySelector('button[data-event="sr7.selectModule"]');
			if (!selectButton.dataset.sr7actionadded) {
				selectButton.dataset.sr7actionadded = true;
				selectButton.addEventListener('click', () => SR7.B.elementorShortcode.selectModule(model, view));
			}

			let editButton = panel.el.querySelector('button[data-event="sr7.editModule"]');
			if (!editButton.dataset.sr7actionadded) {
				editButton.dataset.sr7actionadded = true;
				editButton.addEventListener('click', () => SR7.B.elementorShortcode.editModule(model));
			}			
        },
		buildOffsetMatrix : (panel, model) => {
            const settings = model.get('settings').toJSON();
            const container = panel.$el.find('.sr--elementor--module--offset');
            if (!container.length) return;

			const updateOffsetValues = e => {
				const offset = {};
				container[0].querySelectorAll('.sr--offset--toggle').forEach(checkbox => {
					if (checkbox.checked) {
						const device = checkbox.getAttribute('data-device');
						offset[device] = {use: true};
						container[0].querySelectorAll('.sr--offset--input[data-device="' + device + '"]').forEach(input => {
							const side = input.getAttribute('data-side');
							let val = parseInt(input.value) || 0;
							offset[device][side] =  (val < -500 ? -500 : val > 2000 ? 2000 : val) + "px";
						});
					}
				});
				model.setSetting('offset', JSON.stringify(offset));
				SR7.B.elementorShortcode.updateShortcode(model);
			}

			container[0].querySelectorAll('.sr--offset--toggle').forEach(checkbox => {
				const device = checkbox.getAttribute('data-device');
				checkbox.addEventListener('change', e => {
					container[0].querySelectorAll('.sr--offset--input[data-device="' + device + '"]').forEach(input => {
						input.disabled = !checkbox.checked;
						updateOffsetValues();
					});
				});
			});

			const offset = settings.offset ? JSON.parse(settings.offset) : null;
			if (offset) {
				for (let device in offset) {
					if (offset[device].use) {
						container[0].querySelector('.sr--offset--toggle[data-device="' + device + '"]').checked = true;
						container[0].querySelectorAll('.sr--offset--input[data-device="' + device + '"]').forEach(input => {
							input.disabled = false;
							input.value = parseInt(offset[device][input.getAttribute('data-side')] || "0");
							input.addEventListener('change', e => {
								updateOffsetValues();
							});
						});
					}
				}
			}
        },
		processShortcode : (model, view, shortcodeString) => {
            _tpt.checkResources(['tools_shortcode']).then(() => {
				const shortcode = SR7.B.shortcode.parse(shortcodeString);
				let alias, settingsChanged = false;
				if (shortcode?.attributes) {

					const settingsCache = JSON.stringify(model.get('settings').toJSON());

					// Migrate layout override
					if (shortcode.attributes.layout) {
						model.setSetting('layout_override', 'yes');
						model.setSetting('fullwidth', ["fullwidth", "fullscreen"].includes(shortcode.attributes.layout) ? 'yes' : 'no');
						model.setSetting('fullheight', shortcode.attributes.layout == "fullscreen" ? 'yes' : 'no');
					}

					if (!alias && shortcode.attributes.alias) {
						alias = shortcode.attributes.alias;
						model.setSetting('alias', alias);
					}

					if (shortcode.attributes.fullwidth) model.setSetting('fullwidth', shortcode.attributes.fullwidth === "true" ? 'yes' : 'no');
					if (shortcode.attributes.fullheight) model.setSetting('fullheight', shortcode.attributes.fullheight === "true" ? 'yes' : 'no');
					if (model.getSetting('fullheight')=='yes' || model.getSetting('fullwidth')=='yes') model.setSetting('layout_override', 'yes');

					if (shortcode.attributes.modal) {
						model.setSetting('modal', 'yes');
						model.setSetting({
							popup_cookie_use: shortcode.attributes.popup?.cookie?.use ? 'yes' : '',
							popup_cookie_value: shortcode.attributes.popup?.cookie?.value,
							popup_time_use: shortcode.attributes.popup?.time?.use ? 'yes' : '',
							popup_time_value: shortcode.attributes.popup?.time?.value,
							popup_scroll_use: shortcode.attributes.popup?.scroll?.use ? 'yes' : '',
							popup_scroll_type: shortcode.attributes.popup?.scroll?.type,
							popup_scroll_offset: shortcode.attributes.popup?.scroll?.offset,
							popup_scroll_container: shortcode.attributes.popup?.scroll?.container,
							popup_event_use: shortcode.attributes.popup?.event?.use ? 'yes' : '',
							popup_event_name: 'popup_' + shortcode.attributes.alias,
							popup_hash_use: shortcode.attributes.popup?.hash?.use ? 'yes' : ''
						});
					}
					if (shortcode.attributes.offset) {
						model.setSetting('offset', JSON.stringify(shortcode.attributes.offset));
					}

					settingsChanged = JSON.stringify(model.get('settings').toJSON()) !== settingsCache;
				}
				if (alias && (settingsChanged || !model.getSetting('moduleId'))) {
					SR7.B.elementorShortcode.loadModule(model, view, alias);
				}
			});
        },
		updateShortcode : model => {
            _tpt.checkResources(['tools_shortcode']).then(() => {
				const s = model.get('settings').toJSON();
				let data = {
					alias: s.alias,
					modal: s.modal,
					zindex: s.zindex,
					fullwidth: s.layout_override == "yes" ? s.fullwidth == "yes" : null,
					fullheight: s.layout_override == "yes" ? s.fullheight == "yes" : null,
					popup: {
						cookie: {use: s.popup_cookie_use === 'yes', v: s.popup_cookie_value},
						time: {use: s.popup_time_use === 'yes', v: s.popup_time_value},
						scroll: {use: s.popup_scroll_use === 'yes', type: s.popup_scroll_type, v: s.popup_scroll_type == "offset" ? s.popup_scroll_offset : s.popup_scroll_container},
						event: {use: s.popup_event_use === 'yes', v: s.popup_event_name},
						hash: {use: s.popup_hash_use === 'yes'}
					},
					offset: s.offset ? JSON.parse(s.offset) : null
				};
				let content = SR7.B.shortcode.generate(data);
				model.setSetting('shortcode', content);
            });
		},
        selectModule : (model, view) => {
            _tpt.checkResources(['tools_shortcode']).then(() => {
                SR7.B.shortcode.openSelectModule(data => {
                    SR7.B.elementorShortcode.loadModule(model, view, data.alias);
                });
            });
        },
        editModule : model => {
			window.open(SR7.E.ajaxurl.replace("/admin-ajax.php", "/admin.php") + '?page=revslider&view=editor&module=' + model.getSetting("moduleId"), "_blank");
        },
		loadModule: (model, view, alias) => {
			_tpt.checkResources(['tools_shortcode']).then(() => {
                SR7.B.shortcode.loadModule(alias).then(data => {
					const nextSettings = {
						alias: data.alias,
						title: data.title,
						slides: data.slides,
						type: data.type,
						moduleId: data.id,
						slideId: data.slideId,
						image: data.cover.image,
						color: data.cover.color,
						premium: data.premium ? 'yes' : 'no',
						registered: SR7.E.registered ? 'yes' : 'no',
						notFound: 'no',
					};
					if (!model.getSetting('popup_event_name')) {
						nextSettings.popup_event_name = 'popup_' + data.alias;
					}
					SR7.B.elementorShortcode.updateShortcode(model);

					const hasRealChange = Object.entries(nextSettings).some(
						([key, value]) => model.getSetting(key) !== value
					);					

					const container =
						view?.getContainer?.() ||
						view?.container ||
						elementor.getPreviewView()?.children?.findByModelCid(model.cid)?.getContainer?.();

					if (container) {
						container.settings.setExternalChange(nextSettings);
						container.render();
					} else {
						Object.entries(nextSettings).forEach(([key, value]) => model.setSetting(key, value));
					}
					
					if (hasRealChange) {
						if (window.elementorV2?.editorDocuments?.setDocumentModifiedStatus) {
							window.elementorV2.editorDocuments.setDocumentModifiedStatus(true);
						} else if (window.$e?.internal) {
							$e.internal('document/save/set-is-modified', { status: true });
						} else if (elementor?.saver?.setFlagEditorChange) {
							elementor.saver.setFlagEditorChange(true);
						}
					}
                }).catch(error => {
					console.error("Failed to load module:", error);
					model.setSetting("notFound", "yes");
				});
            });
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