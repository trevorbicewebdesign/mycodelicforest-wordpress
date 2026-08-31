<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

const SVG = [
	'EditIcon' => '<svg width="24" height="16.076" viewBox="0 0 24 16.076"><path d="M70.12-722.121l9.609-9.609a.257.257,0,0,0,.078-.189.257.257,0,0,0-.078-.189L78.6-733.234a.257.257,0,0,0-.189-.078.257.257,0,0,0-.189.078l-9.609,9.609Zm-7.258,2.093a6.921,6.921,0,0,1-3.875-1.148,3.381,3.381,0,0,1-1.295-2.838,3.39,3.39,0,0,1,1.475-2.85,7.982,7.982,0,0,1,4.1-1.325,4.944,4.944,0,0,0,1.892-.456,1.063,1.063,0,0,0,.631-.961,1.408,1.408,0,0,0-.853-1.3,8.2,8.2,0,0,0-2.8-.644l.158-1.724a8.373,8.373,0,0,1,3.947,1.15,2.9,2.9,0,0,1,1.28,2.518,2.6,2.6,0,0,1-1.058,2.183,5.794,5.794,0,0,1-3.071.969,6.949,6.949,0,0,0-2.976.774,1.869,1.869,0,0,0-.992,1.666,1.771,1.771,0,0,0,.826,1.6,5.9,5.9,0,0,0,2.679.646Zm7.529.091L66.432-723.9l10.8-10.79a1.618,1.618,0,0,1,1.2-.506,1.676,1.676,0,0,1,1.2.506l1.557,1.557a1.644,1.644,0,0,1,.512,1.2,1.644,1.644,0,0,1-.512,1.2Zm-3.864.8a.694.694,0,0,1-.69-.2.694.694,0,0,1-.2-.689l.8-3.864,3.959,3.959Z" transform="translate(-57.693 735.192)"></path></svg>',
	'SelectIcon' => '<svg width="20" height="14.884" viewBox="0 0 20 14.884"><path d="M81.86-785.116a1.791,1.791,0,0,1-1.314-.547A1.791,1.791,0,0,1,80-786.977V-798.14a1.792,1.792,0,0,1,.547-1.314A1.792,1.792,0,0,1,81.86-800h5.581l1.86,1.86h7.442a1.792,1.792,0,0,1,1.314.547,1.792,1.792,0,0,1,.547,1.314H88.535l-1.86-1.86H81.86v11.163l2.233-7.442H100l-2.4,7.977a1.814,1.814,0,0,1-.686.965,1.846,1.846,0,0,1-1.1.36Zm1.953-1.86h12l1.674-5.581h-12Zm0,0,1.674-5.581Zm-1.953-9.3v0Z" transform="translate(-80 800)"></path></svg>',
];

?>
<script type="text/template" id="fusion-builder-block-module-settings-sr7-template">
	<#
	var SR7Options = {
			module: {},
			layout: {},
			modal: {}
		},
		SR7OptionTabs = {
			alias: 'module',
			live_preview: 'module',
			title: 'module',
			m_id: 'module',
			type: 'module',
			image: 'module',
			not_found: 'module',
			premium: 'module',
			layout_override: 'layout',
			fullwidth: 'layout',
			fullheight: 'layout',
			zindex: 'layout',
			cssclasses: 'layout',
			id: 'layout',
			class: 'layout',
			modal: 'modal',
			usage: 'modal',
			popup_cookie_use: 'modal',
			popup_cookie_value: 'modal',
			popup_time_use: 'modal',
			popup_time_value: 'modal',
			popup_scroll_use: 'modal',
			popup_scroll_type: 'modal',
			popup_scroll_offset: 'modal',
			popup_scroll_container: 'modal',
			popup_event_use: 'modal',
			popup_event_name: 'modal',
			popup_hash_use: 'modal',
			popup_note: 'modal'
		};

	_.each( fusionAllElements[ atts.element_type ].params, function( param, index ) {
		var paramName,
			tabName;

		if ( 'object' !== typeof param || null === param ) {
			return;
		}

		paramName = 'undefined' !== typeof param.param_name ? param.param_name : index;
		tabName   = SR7OptionTabs[ paramName ];

		if ( 'undefined' !== typeof tabName ) {
			SR7Options[ tabName ][ paramName ] = param;
		}
	} );
	#>

	<div class="fusion-builder-modal-top-container">
		<# if ( 'undefined' !== typeof fusionAllElements[atts.element_type] ) { #>
			<h2>{{ fusionAllElements[ atts.element_type ].name }}</h2>
		<# } #>

		<div class="fusion-builder-modal-close fusiona-plus2"></div>
		<ul class="fusion-tabs-menu">
			<li><a href="#sr7-module"><?php esc_html_e( 'Module', 'revslider' ); ?></a></li>
			<li><a href="#sr7-layout"><?php esc_html_e( 'Layout', 'revslider' ); ?></a></li>
			<li><a href="#sr7-modal"><?php esc_html_e( 'Modal', 'revslider' ); ?></a></li>
		</ul>
	</div>

	<div class="fusion-builder-modal-bottom-container">
		<a href="#" class="fusion-builder-modal-save">
			<span>
				<# if ( true === FusionPageBuilderApp.shortcodeGenerator && true !== FusionPageBuilderApp.shortcodeGeneratorMultiElementChild ) { #>
					{{ fusionBuilderText.insert }}
				<# } else { #>
					{{ fusionBuilderText.save }}
				<# } #>
			</span>
		</a>

		<a href="#" class="fusion-builder-modal-close">
			<span>{{ fusionBuilderText.cancel }}</span>
		</a>
	</div>

	<div class="fusion-builder-main-settings fusion-builder-main-settings-full has-group-options">
		<div class="fusion-tabs">

			<div id="sr7-module" class="fusion-tab-content">

				<div class="sr--avada--module--info">
					<div class="sr--module--info--logo"></div>
					<div class="sr--module--info--details {{ atts.params.alias ? '' : 'sr--module--info--hidden' }}">
						<div class="sr--module--info--thumb" style="background-image: url({{ atts.params && atts.params.image && typeof atts.params.image == 'string' ? atts.params.image : '<?php echo RS_PLUGIN_URL_CLEAN; ?>admin/assets/images/sr7placeholder.webp' }});"></div>
						<div class="sr--module--info--title">
							<strong>{{ atts.params && atts.params.title ? atts.params.title : '<?php esc_html_e( 'Module Not Found', 'revslider' ); ?>' }}</strong>
						</div>
						<div class="sr--module--info--row">
							Alias: {{ atts.params && atts.params.alias ? atts.params.alias : '' }}
						</div>
						<div class="sr--module--info--row">
							Type: {{ atts.params && atts.params.type ? atts.params.type : '<?php esc_html_e( 'Slider', 'revslider' ); ?>' }}
						</div>
					</div>
					<div class="sr--avada--module--info--buttons">
						<button class="sr--module--info--button--select" type="button" data-sr7-avada-action="select"><?php echo SVG['SelectIcon']; ?> <?php esc_html_e( 'Select Module', 'revslider' ); ?></button>
						<button class="sr--module--info--button--edit {{ atts.params.alias ? '' : 'sr--module--info--hidden' }}" type="button" data-sr7-avada-action="edit"><?php echo SVG['EditIcon']; ?> <?php esc_html_e( 'Edit Module', 'revslider' ); ?></button>
					</div>
				</div>

				<?php fusion_element_options_loop( 'SR7Options.module' ); ?>
			</div>

			<div id="sr7-layout" class="fusion-tab-content">
				<?php fusion_element_options_loop( 'SR7Options.layout' ); ?>
			</div>

			<div id="sr7-modal" class="fusion-tab-content">
				<?php fusion_element_options_loop( 'SR7Options.modal' ); ?>
			</div>

		</div>
	</div>

</script>
