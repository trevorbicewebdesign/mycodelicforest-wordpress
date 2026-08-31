/* global FusionPageBuilderApp, fusionAllElements, fusionBuilderText */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

    const SR7_ICON = '<svg width="36" height="36" viewBox="0 0 36 36"><g id="NEW_SR_LOGO" data-name="NEW SR LOGO" transform="translate(8 7.493)"><rect id="Rectangle_16" data-name="Rectangle 16" width="36" height="36" rx="5" transform="translate(-8 -7.493)" fill="#5c24ff"/><path id="Path_1" data-name="Path 1" d="M46.311,18.755l2.807,2.81a.2.2,0,0,1-.14.337H40.089a.121.121,0,0,1-.137-.137l-.006-8.912a.188.188,0,0,1,.322-.134l2.938,2.933a.109.109,0,0,0,.185-.006A6.5,6.5,0,0,0,43.9,7.7a6.271,6.271,0,0,0-5.255-2.944q-.123.009-.123-.117l0-3.991a.121.121,0,0,1,.148-.137c5.283.394,9.36,3.746,10.293,8.929a10.885,10.885,0,0,1-2.231,8.781.516.516,0,0,1-.168.145l-.211.114Q46.146,18.589,46.311,18.755Z" transform="translate(-28.331 -0.8)" fill="#fff"/><path id="Path_2" data-name="Path 2" d="M0,1.745V1.46l8.975.014a.124.124,0,0,1,.14.14l0,8.627a.251.251,0,0,1-.431.177L5.974,7.688a.133.133,0,0,0-.225.009C2.037,12.144,5,18.126,10.521,18.714a.168.168,0,0,1,.148.168v3.766a.123.123,0,0,1-.145.14A10.328,10.328,0,0,1,3.94,20.36Q-1.717,15.456.79,7.893A9.566,9.566,0,0,1,2.844,4.8a.151.151,0,0,0-.006-.234Z" transform="translate(-0.8 -1.481)" fill="#fff"/></g></svg>';
    const EYE_ICON = '<svg width="16" height="14.564" viewBox="0 0 16 14.564"><path d="M12.709,12.859l-.8-.8a1.8,1.8,0,0,0-.491-2.145,1.942,1.942,0,0,0-2.091-.436l-.8-.8a2.2,2.2,0,0,1,.691-.291A3.364,3.364,0,0,1,10,8.3a3.073,3.073,0,0,1,3.091,3.091,3.179,3.179,0,0,1-.1.791,2.4,2.4,0,0,1-.282.682ZM15.055,15.2l-.727-.727a8.527,8.527,0,0,0,1.555-1.464,5.843,5.843,0,0,0,.973-1.627A7.257,7.257,0,0,0,14.127,8.2a7.14,7.14,0,0,0-3.945-1.173,8.732,8.732,0,0,0-1.564.145,5.8,5.8,0,0,0-1.255.345l-.836-.855A8.244,8.244,0,0,1,8.155,6.15a8.991,8.991,0,0,1,1.936-.218,8.208,8.208,0,0,1,4.755,1.482A8.592,8.592,0,0,1,18,11.386a8.982,8.982,0,0,1-1.218,2.127A8.782,8.782,0,0,1,15.055,15.2Zm1.055,4.109-3.055-3a7.064,7.064,0,0,1-1.436.391A9.613,9.613,0,0,1,10,16.841a8.336,8.336,0,0,1-4.818-1.482A8.685,8.685,0,0,1,2,11.386,8.25,8.25,0,0,1,3.009,9.541,10.335,10.335,0,0,1,4.582,7.823L2.291,5.532l.764-.782L16.818,18.514ZM5.327,8.586a6.691,6.691,0,0,0-1.3,1.291,5.985,5.985,0,0,0-.9,1.509,7.281,7.281,0,0,0,2.791,3.191,7.77,7.77,0,0,0,4.227,1.173,9.519,9.519,0,0,0,1.182-.073,2.824,2.824,0,0,0,.873-.218L11.036,14.3a1.9,1.9,0,0,1-.491.136,3.544,3.544,0,0,1-.545.045,3.006,3.006,0,0,1-2.182-.891,2.958,2.958,0,0,1-.909-2.2,3.313,3.313,0,0,1,.045-.545,2.226,2.226,0,0,1,.136-.491ZM10.873,11.168ZM8.764,12.223Z" transform="translate(-2 -4.75)"></path></svg>';

	function collectOptions( params, keys ) {
		var options = [];

		keys.forEach( function( key ) {
			if ( 'object' === typeof params[ key ] && null !== params[ key ] ) {
				options.push( params[ key ] );
			}
		} );

		return options;
	}	

	$( document ).ready( function() {

		SR7 ??= {};
		SR7.E ??= {};
		SR7.E.registered = SR7AvadaLiveData.registered ?? false;

		FusionPageBuilder.ModuleSettingsSR7View = FusionPageBuilder.ElementSettingsView.extend( {

			events: function() {
				return _.extend( {}, FusionPageBuilder.ElementSettingsView.prototype.events, {
					'click .sr--module--info--button--select': 'selectModule',
					'click .sr--module--info--button--edit': 'editModule',
					'change input': 'updateParams'
				} );
			},

			template: FusionPageBuilder.template( $( '#fusion-builder-block-module-settings-sr7-template' ).html() ),

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
						SR7.B.shortcode.checkDepsLoaded().then(() => {
							const args = SR7.B.shortcode.generateParams(data);
							const modal = this.el.querySelector("input[name='modal']");
							modal.value = args.modal ?? '';
							this.el.querySelector('[data-option-id="popup_event_name"] .description code').textContent = "document.dispatchEvent(new Event(\"" + data.popup.event.v + "\"));";
						});
					}, 300);
				}
			},

			filterAttributes: function( attributes ) {
				var element = fusionAllElements[ attributes.element_type ] || {},
					params = element.params || {};

				attributes.moduleOptions = collectOptions( params, [
					'alias',
					'live_preview',
					'title',
					'm_id',
					'type',
					'image',
					'not_found',
					'premium'
				] );
				attributes.layoutOptions = collectOptions( params, [
					'layout_override',
					'fullwidth',
					'fullheight',
					'zindex',
					'class',
					'id'
				] );
				attributes.modalOptions = collectOptions( params, [
					'modal',
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
				] );

				return attributes;
			},

			selectModule: function() {
				SR7.B.shortcode.checkDepsLoaded().then(() => {
					SR7.B.shortcode.selectModule(data => this.loadModule(data.alias));
				});
			},

			loadModule(alias) {
				SR7.B.shortcode.loadModule(alias).then(data => {
					const params = $.extend( true, {}, this.model.get( 'params' ) || {} );
					if (params.popup_event_name == '') params.popup_event_name = data.alias;
					this.el.querySelector('[data-option-id="popup_hash_use"] .description code').textContent = "https://yourwebsite.com/yourpage/#" + data.alias;
					params.title = data.title;
					params.type = data.moduleType;
					params.m_id = data.id;
					params.image = data.cover.image;
					params.premium = data.premium ? 'yes' : 'no';
					params.not_found = "no";
					this.model.set('params', params);
					this.applyParamsToFields(params);
					this.updateModuleInfo(params);
					this.el.querySelectorAll('.sr--module--info--hidden').forEach(el => el.classList.remove('sr--module--info--hidden'));
					this.$el.find('[name="alias"]').val(data.alias).trigger("change", [{userClicked: true}]);
				}).catch(error => {
					console.error("Failed to load module:", error);
					const params = $.extend( true, {}, this.model.get( 'params' ) || {} );
					params.not_found = "no";
					this.model.set('params', params);
					this.render();
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

			fixAjaxUrl: function() {
				if (window.SR7ShortcodeData) {
					window.SR7 ??= {};
					window.SR7.E ??= {};
					window.SR7.E.ajaxurl = window.SR7ShortcodeData.ajaxurl;
					window.SR7.E.plugin_url = window.SR7ShortcodeData.plugin_url;
				}
			},

			editModule: function() {
				const id = this.model.get('params').m_id;
				if (!id) return;
				SR7.B.shortcode.checkDepsLoaded().then(() => SR7.B.shortcode.editModule(id));
			}

		} );

		var BaseRevSliderView = FusionPageBuilder.rev_slider;

		FusionPageBuilder.rev_slider = BaseRevSliderView.extend( {
			filterRenderContent: function( output ) {
				if ( this.shouldUsePreviewCard() ) {
					return this.renderPreviewCard();
				}
				return BaseRevSliderView.prototype.filterRenderContent.call( this, output );
			},

			filterOutput: function( output ) {
				if ( this.shouldUsePreviewCard() ) {
					return this.renderPreviewCard();
				}
				return BaseRevSliderView.prototype.filterOutput.call( this, output );
			},


			shouldUsePreviewCard: function() {
				return this.model.get('params').live_preview !== "yes" || (this.model.get('params').premium === "yes" && !SR7.E.registered);
			},

			renderPreviewCard: function() {
				const params = this.model.get('params');

				const escapeHtml = value => String(value ?? '')
					.replace(/&/g, '&amp;')
					.replace(/</g, '&lt;')
					.replace(/>/g, '&gt;')
					.replace(/"/g, '&quot;')
					.replace(/'/g, '&#039;');

				const escapeAttr = value => escapeHtml(value).replace(/`/g, '&#96;');			

				let coverStyle = 'background-image: url(' + (params.image ? escapeAttr(params.image) : SR7.E.plugin_url + "admin/assets/images/sr7placeholder.webp") + ');';

				let content = '';
				content += '<div class="sr--avada--module--preview--widget sr--block--wrap">';
				content += '	<div class="sr--block--head">';
				content += '		<div class="sr--block--logo">' + SR7_ICON + '</div>';
				content += '		<div class="sr--block--title">' + escapeHtml(params.title) + '</div>';
				content += '	</div>';
				content += '	<div class="revslider sr--block--preview" style="' + coverStyle + '">';
				if (params.premium === "yes" && SR7.E.registered) {
					content += '		<div class="sr--block--label sr--block--premium sr--show--onreg">' + escapeHtml(SR7FusionBuilderText.sr7_premium_template) + '</div>';
				}
				if (params.premium === "yes" && !SR7.E.registered) {
					content += '		<div class="sr--block--label sr--block--error sr--show--onnotreg">' + EYE_ICON + ' ' + escapeHtml(SR7FusionBuilderText.sr7_premium_template) + '</div>';
					content += '		<div class="sr--block--label sr--block--error sr--show--onnotreg sr--show--on--parent--hover">' + EYE_ICON + ' ' + escapeHtml(SR7FusionBuilderText.sr7_register_license_to_unlock) + '</div>';
				}
				content += '		<div class="sr--block--thumb" style="' + coverStyle + '"></div>';
				content += '	</div>';
				content += '</div>';
				return content;
			}
		} );


	} );

}( jQuery ) );
