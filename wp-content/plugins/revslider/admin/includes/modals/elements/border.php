<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Border','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>  
		<sr-wrap half class="sr--form--grp" half><sr-color-mini data-v="transparent" r="border.c" data-type="text" class="sr--mr--10" data-onchange="editor.elements.border.update" data-undoredo="editor.elements.border.update" viewchild="layer_style"></sr-color-mini><span class="sr--mr--30"><?php _e('Border','revaslider');?></span></sr-wrap><!--
	--><sr-drop data-onchange="editor.elements.border.update" r="border.s.#LEV#" data-sh=".sr_elements_borde_style" data-shdep="#eqvalue" responsive viewchild="layer_style" half data-v="cover" dropsw="200">
			<sr-drop-view>
				<span class="sr--drop--value">None</span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>			
			<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
			<sr-drops data-v="solid"><?php _e('Solid','revslider'); ?></sr-drops>			
			<sr-drops data-v="dotted"><?php _e('Dotted','revslider'); ?></sr-drops>
			<sr-drops data-v="dashed"><?php _e('Dashed','revslider'); ?></sr-drops>			
			<sr-drops data-v="double"><?php _e('Double','revslider'); ?></sr-drops>
			<sr-drops data-v="groove"><?php _e('Groove','revslider'); ?></sr-drops>
			<sr-drops data-v="ridge"><?php _e('Ridge','revslider'); ?></sr-drops>
			<sr-drops data-v="inset"><?php _e('Inset','revslider'); ?></sr-drops>
			<sr-drops data-v="outset"><?php _e('Outset','revslider'); ?></sr-drops>												
		</sr-drop>		
		<sr-wrap value="dotted#;#dashed#;#solid#;#double#;#groove#;#ridge#;#inset#;#outset" class="sr_elements_borde_style">
			<sr-bmp type="border" idpref="sr_layer_border_full_" responsive r="border.w" viewchild="layer_style"></sr-bmp>			
		</sr-wrap>
		<sr-bmp type="radius" idpref="sr_layer_radius_full_" r="radius" viewchild="layer_style"></sr-bmp>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>

