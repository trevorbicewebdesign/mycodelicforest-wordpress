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
		<sr-separator-body>		
			<sr-wrap wide basic>
				<sr-radio class="sr--mr--5" viewchild="layer_basics" responsive r="tA.#LEV#">
						<sr-radio-item value="left" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Left"></use></svg></sr-radio-item><!--
					--><sr-radio-item value="center" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Center"></use></svg></sr-radio-item><!--
					--><sr-radio-item value="right" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Right"></use></svg></sr-radio-item><!--
					--><sr-radio-item value="justify" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Justify"></use></svg></sr-radio-item>
				</sr-radio><!--				
				--><sr-drop clean="" data-type="search" data-onchange="editor.elements.icons.select" data-target="sr_element_textcontent" data-source="icons" data-source-type="all" dropsw="300" dropsh="380" data-v="default" class="sr--cta sr--mr--5"><svg class="sr--icon" width="10" height="10" transform="translate(0, -1)"><use xlink:href="#Options_Select_Icon"></use></svg>Icon</sr-drop><!--
				--><sr-drop clean="" data-type="search"  data-onchange="editor.elements.metas.select" data-target="sr_element_textcontent" data-source="metas" data-source-type="all" dropsw="500" dropsh="380" data-v="default" class="sr--cta sr--mr--0"><svg class="sr--icon" style="margin-right:4px !important" width="19.322" height="19.322" transform="translate(0, 0) rotate(-45)"><use xlink:href="#Options_Select_Meta"></use></svg>Meta</sr-drop>
			</sr-wrap>
			<sr-input wide textblock><textarea id="sr_element_textcontent" layercontent r="content.text"  keydown="editor.elements.text.register" keyup="editor.elements.text.update" data-undoredo="editor.elements.text.redrawAll" data-onchange="editor.elements.text.redrawAll"  viewchild="layer_basics" style="resize:none; height:130px"></textarea><sr-button data-action="editor.elements.text.ai" class="sr--bg--picker--ai-txt">AI<svg width="13.145" height="11.865" class="sr--icon" transform="translate(0,3)"><use xlink:href="#AIStar"></use></svg></sr-button></sr-input>
			<sr-sp h="5"></sr-sp>
			<sr-fieldset viewchild="layer_basics" id="fset_textlayer_dynamic" data-type="single" data-source="editor.elements.text.dynamicTextFields" class="sr--mb--0"></sr-fieldset>
			<sr-wrap-dep dep="is[text]" class="sr--mb--5">
				<sr-drop wide r="ws.#LEV#" responsive viewchild="layer_basics" data-onchange="editor.elements.text.redrawAll" data-action="editor.elements.text.register" data-v="">
					<sr-drop-view>
						<span class="sr--drop--value">None</span>
						<span class="sr--form--otitle"><?php _e('Line Wrap','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="full"><?php _e('Auto','revslider'); ?></sr-drops>
					<sr-drops data-v="normal"><?php _e('Content Width','revslider'); ?></sr-drops>
					<sr-drops data-v="content"><?php _e('Any Content Breaks','revslider'); ?></sr-drops>
					<sr-drops data-v="nowrap"><?php _e('Only &lt;br&gt; tags','revslider'); ?></sr-drops>				
				</sr-drop>
			</sr-wrap-dep>
		</sr-separator-body>
	</sr-separator>
</sr-wrap-dep>
