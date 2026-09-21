<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

?>
<script type="text/template" id="fusion-builder-block-module-sr7-avada-preview-template">
	<div class="sr--avada--module--preview--card">
		<h4 class="fusion_module_title">
			<span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>
			<span>{{ fusionAllElements[element_type].name }}</span>
		</h4>
		<div class="sr--module--info--details">
			<div class="sr--module--info--thumb" style="background-image: url({{ params && params.image ? params.image : '<?php echo RS_PLUGIN_URL_CLEAN; ?>admin/assets/images/sr7placeholder.webp' }});"></div>
			<# if ( params && params.alias ) { #>
				<# if ( params.title ) { #>
				<div class="sr--module--info--row"><?php echo esc_js( __('Title', 'revslider') ); ?>: {{ params.title }}</div>
				<# } #>
				<div class="sr--module--info--row"><?php echo esc_js( __('Alias', 'revslider') ); ?>: {{ params.alias }}</div>
				<# if ( params.type ) { #>
				<div class="sr--module--info--row"><?php echo esc_js( __('Type', 'revslider') ); ?>: {{ params.type }}</div>
				<# } #>
			<# } else { #>
				<div class="sr--module--info--row sr--module--info--empty"><?php echo esc_js( __('No Module Selected', 'revslider') ); ?></div>
			<# } #>
		</div>
	</div>
</script>
