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
		<sr-separator-title><?php _e('Base Transform & Display','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>					
		<sr-input onethird class="sr--mr--6">
			<input name="Rotation X" viewchild="layer_extra" r="tr.rX" replace data-onchange="layer.reWrap" data-onchangeparams="mask,tl" livevisup autocomplete="off" dragnumber number="true" min="-360" max="360" suffix="deg" lastsuffix="deg"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(4, 4) rotate(90)"><use xlink:href="#Options_Rotate_X"></use></svg></span>
		</sr-input><!--
		--><sr-input onethird class="sr--mr--6">
			<input name="Rotation Y" viewchild="layer_extra" r="tr.rY"  replace data-onchange="layer.reWrap" data-onchangeparams="mask,tl" livevisup autocomplete="off" dragnumber number="true" min="-360" max="360" suffix="deg" lastsuffix="deg"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Y"></use></svg></span>
		</sr-input><!--
		--><sr-input onethird>
			<input name="Rotation Z" viewchild="layer_extra"  replace data-onchange="layer.reWrap" data-onchangeparams="mask,tl" livevisup autocomplete="off" type="text" dragnumber r="tr.rZ" number="true" min="-360" max="360" suffix="deg"  lastsuffix="deg" validate="true" type="text" livevisup>
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Z"></use></svg></span>
		</sr-input><!--
		--><sr-input wide>
			<input name="Opacity" viewchild="layer_extra" type="text" r="tr.o"  data-onchange="layer.reWrap,editor.elements.updateEditables+50" data-onchangeparams="mask,tl"  livevisup dragnumber autocomplete="off" number="true" min="0" max="1" step="0.05" validate="true" type="text" livevisup>
			<span noicon="" class="sr--form--otitle"><?php _e('Opacity','revslider'); ?></span>
		</sr-input>
		<sr-input wide>
			<input name="Backdrop Blur" replace r="bF" viewchild="layer_extra" type="text" number="true" min="0" max="100" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Backdrop Blur','revslider'); ?></span>
		</sr-input>
		<sr-drop viewchild="layer_extra" r="tr.fix" wide class="font_type_weight sr--mr--6" data-v="value" data-onchange="B.fontTypes.updateUsedFonts">
			<sr-drop-view>
				<span class="sr--drop--value"></span>                            
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('iOS Sort Mode','revslider');?></span>
			</sr-drop-view>
			<sr-drops data-v="d"><?php _e('Default','revslider'); ?></sr-drops>
			<sr-drops data-v="z"><?php _e('By Transform Z','revslider'); ?></sr-drops>
			<sr-drops data-v="x"><?php _e('By Transform X','revslider'); ?></sr-drops>
			<sr-drops data-v="r"><?php _e('By Rotation','revslider'); ?></sr-drops>
			<sr-drops data-v="p"><?php _e('By Perspective','revslider'); ?></sr-drops>
		</sr-drop>
			<sr-sp h="5"></sr-sp>			
	</sr-separator-body>
</sr-separator>
