/* global FusionPageBuilder */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {
	'use strict';

	$( document ).ready( function() {
		FusionPageBuilder.ModuleSettingsSR7View = FusionPageBuilder.ElementSettingsView.extend( {

			template: FusionPageBuilder.template( $( '#fusion-builder-block-module-settings-sr7-template' ).html() ),

			events: function() {
				return _.extend( {}, FusionPageBuilder.ElementSettingsView.prototype.events, {
					'click .sr--module--info--button--select': 'selectModule',
					'click .sr--module--info--button--edit': 'editModule',
					'change input': 'updateParams'
				} );
			},

			updateParams: function(event) {
				const triggers = [
					'usage',
					'popup_cookie_use',
					'popup_cookie_value',
					'popup_time_use',
					'popup_time_value',
					'popup_scroll_use',
					'popup_scroll_type',
					'popup_scroll_offset',
					'popup_scroll_container',
					'popup_event_use',
					'popup_event_name',
					'popup_hash_use',
					'popup_note'
				];
				if (triggers.includes(event.target.id)) {
					setTimeout(() => {
						const getValue = name => this.el.querySelector("[name='" + name + "']").value;
						const data = {
							modal: getValue('usage') === "modal",
							popup: {
								cookie: {
									use:       getValue('popup_cookie_use') === "yes",
									v:     	   getValue('popup_cookie_value'),
								},
								event: {
									use:       getValue('popup_event_use') === "yes",
									v:     	   getValue('popup_event_name')
								},
								hash: {
									use:       getValue('popup_hash_use') === "yes",
								},
								scroll: {
									use:       getValue('popup_scroll_use') === "yes",
									v:    	   getValue('popup_scroll_offset'),
									type:      getValue('popup_scroll_type'),
									container: getValue('popup_scroll_container'),
								},
								time: {
									use:       getValue('popup_time_use') === "yes",
									v:         getValue('popup_time_value')
								},
							}
						}
						_tpt.checkResources(['tools_shortcode']).then(() => {
							SR7.B.shortcode.checkDepsLoaded().then(() => {
								const args = SR7.B.shortcode.generateParams(data);
								const modal = this.el.querySelector("input[name='modal']");
								const params = $.extend( true, {}, this.model.get( 'params' ) || {} );
								modal.value = args.modal ?? '';
								this.el.querySelector('[data-option-id="popup_event_name"] .description code').textContent = "document.dispatchEvent(new Event(\"" + data.popup.event.v + "\"));";
								this.el.querySelector('[data-option-id="popup_hash_use"] .description code').textContent = "https://yourwebsite.com/yourpage/#" + params.alias;
							});
						});
					}, 300);
				}
			},

			selectModule: function() {
				_tpt.checkResources(['tools_shortcode']).then(() => {
					SR7.B.shortcode.checkDepsLoaded().then(() => {
						setTimeout(() => document.querySelector(".sr--block--editor--popup--wrap").style.display = "block", 100);
						SR7.B.shortcode.selectModule(data => this.loadModule(data.alias));
					});
				});
			},

			loadModule(alias) {
				_tpt.checkResources(['tools_shortcode']).then(() => {
					SR7.B.shortcode.loadModule(alias).then(data => {
						const params = $.extend( true, {}, this.model.get( 'params' ) || {} );
						params.alias = data.alias;
						if (params.popup_event_name == '') params.popup_event_name = data.alias;
						this.el.querySelector('[data-option-id="popup_hash_use"] .description code').textContent = "https://yourwebsite.com/yourpage/#" + data.alias;
						params.title = data.title;
						params.type = data.moduleType;
						params.m_id = data.id;
						params.image = data.cover.image;
						params.premium = data.premium ? 'yes' : 'no';
						params.registered = SR7.E.registered ? 'yes' : 'no';
						params.not_found = "no";
						this.model.set('params', params);
						this.applyParamsToFields(params);
						this.updateModuleInfo(params);
						this.el.querySelectorAll('.sr--module--info--hidden').forEach(el => el.classList.remove('sr--module--info--hidden'));
					}).catch(error => {
						console.error("Failed to load module:", error);
						const params = $.extend( true, {}, this.model.get( 'params' ) || {} );
						params.not_found = "yes";
						this.model.set('params', params);
						this.applyParamsToFields(params);
					});
				});
			},

			applyParamsToFields: function(params) {
				_.each(params, (value, name) => {
					const field = this.$el.find('[name="' + name + '"], #' + name);
					if (!field.length) {
						return;
					}
					field.val(value ?? '');
					if (field.is('select')) {
						field.trigger('change');
					}
				});
			},

			updateModuleInfo: function(params) {
				const image = params.image && typeof params.image == 'string' ? params.image : SR7.E.plugin_url + 'admin/assets/images/sr7placeholder.webp';
				this.$el.find('.sr--module--info--thumb').css('background-image', 'url("' + String(image).replace(/"/g, '\\"') + '")');
				this.$el.find('.sr--module--info--title strong').text(params.title || '');
				this.$el.find('.sr--module--info--row').eq(0).text('Alias: ' + (params.alias || ''));
				this.$el.find('.sr--module--info--row').eq(1).text('Type: ' + (params.type || ''));
			},

			editModule: function() {
				const id = this.model.get('params').m_id;
				if (!id) return;
				_tpt.checkResources(['tools_shortcode']).then(() => {
					SR7.B.shortcode.checkDepsLoaded().then(() => SR7.B.shortcode.editModule(id));
				});
			}

		} );

		_tpt.regResource({id: "tools_shortcode", url: SR7.E.plugin_url + "admin/assets/js/tools/shortcode.js"});
	} );
}( jQuery ) );
