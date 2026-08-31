<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

?>
<script type="text/template" id="fusion-builder-block-module-settings-sr7-template">
<#
	var sidebarEditing = 'dialog' !== FusionApp.preferencesData.editing_mode && 'generated_element' !== atts.type ? true : false;
#>
	<div class="fusion-builder-modal-top-container">
	<# if ( sidebarEditing ) { #>
		<div class="ui-dialog-titlebar">
			<h2>{{{ atts.title }}}</h2>

			<div class="fusion-utility-menu-wrap">
				<span class="fusion-utility-menu fusiona-ellipsis"></span>
			</div>
			<button id="fusion-close-element-settings" type="button" class="fusiona-close-fb" aria-label="Close" role="button" title="Close"></button>
		</div>
	<# } #>
		<ul class="fusion-tabs-menu">
			<li>
				<a href="#sr7-module" class="has-tooltip" aria-label="<?php esc_html_e( 'Module', 'revslider' ); ?>">
					<span class="fusiona-related-posts"></span>
					<span><?php esc_html_e( 'Module', 'revslider' ); ?></span>
				</a>
			</li>
			<li>
				<a href="#sr7-layout" class="has-tooltip" aria-label="<?php esc_html_e( 'Layout', 'revslider' ); ?>">
					<span class="fusiona-settings"></span>
					<span><?php esc_html_e( 'Layout', 'revslider' ); ?></span>
				</a>
			</li>
			<li>
				<a href="#sr7-modal" class="has-tooltip" aria-label="<?php esc_html_e( 'Modal', 'revslider' ); ?>">
					<span class="fusiona-navigator"></span>
					<span><?php esc_html_e( 'Modal', 'revslider' ); ?></span>
				</a>
			</li>			
		</ul>
	</div>

	<div class="fusion-builder-main-settings <# if ( sidebarEditing ) { #>fusion-builder-customizer-settings<# } #> fusion-builder-main-settings-full has-group-options">
		<div class="fusion-tabs">

			<div id="sr7-module" class="fusion-tab-content">
				<div class="sr--avada--module--info sr--live--module--info">
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
						<button class="sr--module--info--button--select" type="button" data-sr7-avada-action="select"><?php esc_html_e( 'Select Module', 'revslider' ); ?></button>
						<button class="sr--module--info--button--edit {{ atts.params.alias ? '' : 'sr--module--info--hidden' }}" type="button" data-sr7-avada-action="edit"><?php esc_html_e( 'Edit', 'revslider' ); ?></button>
					</div>
				</div>

				<?php fusion_element_options_loop( 'atts.moduleOptions' ); ?>
			</div>

			<div id="sr7-layout" class="fusion-tab-content">
				<?php fusion_element_options_loop( 'atts.layoutOptions' ); ?>
			</div>
			
			<div id="sr7-modal" class="fusion-tab-content">
				<?php fusion_element_options_loop( 'atts.modalOptions' ); ?>
			</div>

		</div>
	</div>
</script>
