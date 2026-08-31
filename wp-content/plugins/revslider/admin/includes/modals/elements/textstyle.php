<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>

<sr-wrap-dep dep="is[text,button]">
	<sr-separator keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Text','revslider'); ?></sr-separator-title>
		</sr-separator-head>
		<sr-separator-body class="font_type_settings">		
			<sr-drop wide r="font.family" data-type="search" keepotitle="" data-lvu="reduced" data-source="fonttypes" data-v="" dropsh="280" dropsw="350" viewchild="layer_style" data-onset="B.fontTypes.updateSettings" data-onsetparams="fonttypeset" data-onchange="B.fontTypes.updateSettings" data-onchangeparams="fonttypechanged">
				<sr-drop-view style="overflow:hidden;">
					<span data-len="30" class="sr--drop--value sr--ohw--200"></span>
					<span class="sr--form--otitle--keep"><?php _e('Font','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-otitle="" data-v="" data-t=""></sr-drops>
			</sr-drop>
			<sr-input onethird class="sr--mr--6">
				<input name="Font Size" viewchild="layer_style" r="font.size.#LEV#" respmath="floor" livevisup autocomplete="off" responsive="scale" respshow="f-320middle" number="true" min="4" max="5000" suffix="px" lastsuffix="px"  validate="true" type="text">
				<span class="sr--input--icon"><svg width="13" height="10.4" transform="translate(0, 1)"><use xlink:href="#Options_Font_Size"></use></svg></span>				
			</sr-input><!--
			--><sr-input onethird class="sr--mr--6">
			<input name="Line Height" viewchild="layer_style" r="lh.#LEV#"  livevisup autocomplete="off" respmath="floor" responsive="scale" respshow="f-320middle" number="true" min="4" max="5000" suffix="px" lastsuffix="px"  validate="true" type="text">
				<span class="sr--input--icon"><svg width="16" height="12.8" transform="translate(0, 2)"><use xlink:href="#Options_Line_Height"></use></svg></span>
			</sr-input><!--
			--><sr-input onethird>
				<input name="Letter Spacing" viewchild="layer_style" type="text" placeholder="0px" r="font.ls.#LEV#" respmath="floor"  responsive="scale" respshow="f-320middle" number="true" min="-20" max="100" lastsuffix="px" validate="true" type="text" livevisup>
				<span class="sr--input--icon"><svg width="16" height="17.36" transform="translate(0, 5)"><use xlink:href="#Options_Letter_Spacing"></use></svg></span>
			</sr-input>
			<sr-drop viewchild="layer_style" r="font.weight"  half class="font_type_weight sr--mr--6" data-v="value" data-onchange="B.fontTypes.updateUsedFontsAndRedraw">
				<sr-drop-view>
					<span class="sr--drop--value"><?php _e('Font Weight','revslider');?></span>                            
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					<span class="sr--form--otitle"><svg class="sr--icon" width="8.114" height="10.182" transform="translate(1, -1)"><use xlink:href="#Bold"></use></svg></span>
				</sr-drop-view>
			</sr-drop><!--
			--><sr-drop half r="tag" viewchild="layer_style" data-v="">
				<sr-drop-view>
					<span class="sr--drop--value">None</span>
					<span class="sr--form--otitle"><svg class="sr--icon"  width="19.322" height="19.322" transform="translate(6, 0) rotate(-45)"><use xlink:href="#Options_Select_Meta"></use></svg></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="sr7-layer"><?php _e('&lt;sr7-layer&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="div"><?php _e('&lt;div&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="p"><?php _e('&lt;p&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h1"><?php _e('&lt;h1&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h2"><?php _e('&lt;h2&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h3"><?php _e('&lt;h3&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h4"><?php _e('&lt;h4&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h5"><?php _e('&lt;h5&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h6"><?php _e('&lt;h6&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="span"><?php _e('&lt;span&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="label"><?php _e('&lt;label&gt;','revslider'); ?></sr-drops>
			</sr-drop>			
			<sr-wrap inline basic class="sr--form--grp sr--mr--25"><sr-color-mini data-v="transparent" viewchild="layer_style" r="color.#LEV#" data-onchange="editor.elements.text.color" data-onclose="editor.elements.text.color" data-oncloseparams="final" data-undoredo="editor.elements.text.color" responsive data-title="<?php _e('Text Color','revslider'); ?>" data-type="text" class="sr--mr--10"></sr-color-mini><span><?php _e('Text Color','revslider'); ?></span></sr-wrap><!--
		--><sr-wrap inline basic class="sr--mr--0"><!--
			--><sr-radio r="content.trans" class="sr--mr--0" data-def="none" viewchild="layer_style" allow-empty="none"><!--
				--><sr-radio-item class="sr--mr--0"  style="width:27px" icon="" value="uppercase"><svg class="sr--icon" width="19.347" height="10.909" transform="translate(0, -2)"><use xlink:href="#Options_Uppercase"></use></svg></sr-radio-item><!--
						--><sr-radio-item class="sr--mr--0"  style="width:27px" icon="" value="capitalize"><svg class="sr--icon" width="15.273" height="11.022" transform="translate(0, -2)"><use xlink:href="#Options_Capitalize"></use></svg></sr-radio-item><!--
						--><sr-radio-item class="sr--mr--0"  style="width:27px" icon="" value="lowercase"><svg class="sr--icon" width="11.301" height="10.255" transform="translate(0, -2)"><use xlink:href="#Options_Lowercase"></use></svg></sr-radio-item>
				</sr-radio>
			</sr-wrap><!--
		--><sr-wrap inline basi class="sr--mr--0"><!--
			--><sr-radio class="sr--mr--0 font_type_italic" r="font.style" data-def="false" viewchild="layer_style" allow-empty="none">
					<sr-radio-item style="width:27px" icon="" value="true"><svg class="sr--icon" width="9.2" height="10.5" transform="translate(0, -2)"><use xlink:href="#Options_Italic"></use></svg></sr-radio-item>
				</sr-radio><!--
				--><sr-radio class="sr--mr--0" r="content.deco" data-def="none" viewchild="layer_style" allow-empty="none"><!--
						--><sr-radio-item style="width:27px" icon="" value="underline"><svg class="sr--icon" width="9.03" height="12.5" transform="translate(0, -1)"><use xlink:href="#Options_Underline"></use></svg></sr-radio-item><!--
						--><sr-radio-item style="width:27px" icon="" value="line-through"><svg class="sr--icon" width="9.03" height="10.5" transform="translate(0, -2)"><use xlink:href="#Options_Line_Through"></use></svg></sr-radio-item>
					</sr-radio>
			</sr-wrap>
			<sr-fieldset viewchild="layer_style" id="layer_frame_color"  data-source="editor.elements.text.frameColor" class="sr--mb--0"></sr-fieldset>				
			<sr-sp h="20"></sr-sp>				
		</sr-separator-body>
	</sr-separator>
</sr-wrap-dep>
