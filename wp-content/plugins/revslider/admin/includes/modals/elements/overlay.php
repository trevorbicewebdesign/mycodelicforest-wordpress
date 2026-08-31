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
		<sr-separator-title><?php _e('Content Overlay','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>				
		<sr-drop r="bg.overlay.type" viewchild="layer_extra" data-shdep="#eqvalue" data-sh=".sr--overlay--settings" data-onchange="editor.elements.overlay" data-undoredo="editor.elements.overlay" class="sr_image_repeat sr--mr--10" wide data-v="no-repeat">
			<sr-drop-view>
				<span class="sr--drop--value">None</span>
				<span class="sr--form--otitle"><?php _e('Pattern','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="none"><?php _e('No Overlay','revslider'); ?></sr-drops>
			<sr-drops data-v="17"><?php _e('Full Filled','revslider'); ?></sr-drops>
			<sr-drops data-v="1"><?php _e('Dotted Small','revslider'); ?></sr-drops>
			<sr-drops data-v="2"><?php _e('Dotted Medium','revslider'); ?></sr-drops>
			<sr-drops data-v="3"><?php _e('Dotted Large','revslider'); ?></sr-drops>
			<sr-drops data-v="4"><?php _e('Horizontal Small','revslider'); ?></sr-drops>
			<sr-drops data-v="5"><?php _e('Horizontal Medium','revslider'); ?></sr-drops>
			<sr-drops data-v="6"><?php _e('Horizontal Large','revslider'); ?></sr-drops>
			<sr-drops data-v="7"><?php _e('Vertical Small','revslider'); ?></sr-drops>
			<sr-drops data-v="8"><?php _e('Vertical Medium','revslider'); ?></sr-drops>
			<sr-drops data-v="9"><?php _e('Vertical Large','revslider'); ?></sr-drops>
			<sr-drops data-v="10"><?php _e('Circles Small','revslider'); ?></sr-drops>
			<sr-drops data-v="11"><?php _e('Circles Medium','revslider'); ?></sr-drops>
			<sr-drops data-v="12"><?php _e('Diagonal 1','revslider'); ?></sr-drops>
			<sr-drops data-v="13"><?php _e('Diagonal 2','revslider'); ?></sr-drops>
			<sr-drops data-v="14"><?php _e('Diagonal 3','revslider'); ?></sr-drops>
			<sr-drops data-v="15"><?php _e('Diagonal 4','revslider'); ?></sr-drops>
			<sr-drops data-v="16"><?php _e('Cross','revslider'); ?></sr-drops>
		</sr-drop>
		<sr-wrap basic wide class="sr--overlay--settings" value="1#;#2#;#3#;#4#;#5#;#6#;#7#;#8#;#9#;#10#;#11#;#12#;#13#;#14#;#15#;#16" >
			<sr-input wide><input name="Overlay Size" replace r="bg.overlay.size" data-onchange="editor.elements.overlay" data-undoredo="editor.elements.overlay" viewchild="layer_extra" type="text" number="true" min="0" max="999999" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Pattern Size','revslider'); ?></span></sr-input><!--		
		--><sr-wrap class="sr--form--grp sr--mr--10" half><sr-color-mini data-v="transparent" data-type="text" r="bg.overlay.cA" data-type="background" data-onchange="editor.elements.overlay" data-undoredo="editor.elements.overlay" class="sr--mr--10" viewchild="layer_extra"></sr-color-mini><span><?php _e('Dot Color','revaslider');?></span></sr-wrap><!--
		--><sr-wrap class="sr--form--grp" half><sr-color-mini data-v="transparent" data-type="text"  r="bg.overlay.cB" data-type="background" data-onchange="editor.elements.overlay" data-undoredo="editor.elements.overlay" class="sr--mr--10" viewchild="layer_extra"></sr-color-mini><span><?php _e('Gap Color','revaslider');?></span></sr-wrap>
			<sr-sp h="15"></sr-sp>
		</sr-wrap>
		<sr-wrap class="sr--overlay--settings" value="17" basic wide>   
			<sr-wrap class="sr--form--grp sr--mr--10" half><sr-color-mini data-v="transparent" data-type="text" r="bg.overlay.cA" data-type="background" data-onchange="editor.elements.overlay" data-undoredo="editor.elements.overlay" class="sr--mr--10" viewchild="layer_extra"></sr-color-mini><span class="sr--mr--30"><?php _e('Overlay','revaslider');?></span></sr-wrap><!--
			--><sr-input  half><input name="Blur" replace r="bg.overlay.bf" data-onchange="editor.elements.overlay" data-undoredo="editor.elements.overlay" viewchild="layer_extra" type="text" number="true" min="0" max="100" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Blur','revslider'); ?></span></sr-input>
			
		</sr-wrap>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>