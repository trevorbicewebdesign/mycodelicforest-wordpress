<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-sh r="#MODULE#.acc.use" data-shdep="true" data-sh="#sr_layer_accessibility_use" viewchild="layer_acc">
	<sr-separator id="sr_layer_accessibility_use" keepborder>
		<sr-separator-head>
			<sr-separator-title><?php _e('Layer Accessibility Settings','revslider'); ?></sr-separator-title>
			<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
		</sr-separator-head>
		<sr-separator-body>
			<sr-drop wide  class="sr--mb--15" r="acc.hidden" viewchild="layer_acc">
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('Hide from Screen Readers','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>                    
				<sr-drops data-v="true"><?php _e('True','revslider'); ?></sr-drops>
				<sr-drops data-v="false"><?php _e('False','revslider'); ?></sr-drops>
			</sr-drop> 
			<sr-drop wide  class="sr--mb--15" r="acc.role" viewchild="layer_acc">
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('Semantic Role','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>                    
				<sr-drops data-v="unset"><?php _e('Unset','revslider'); ?></sr-drops>
				<sr-drops data-v="button"><?php _e('Button','revslider'); ?></sr-drops>
				<sr-drops data-v="link"><?php _e('Link','revslider'); ?></sr-drops>
				<sr-drops data-v="image"><?php _e('Image','revslider'); ?></sr-drops>
				<sr-drops data-v="heading"><?php _e('Heading','revslider'); ?></sr-drops>
				<sr-drops data-v="paragraph"><?php _e('Paragraph','revslider'); ?></sr-drops>
				<sr-drops data-v="group"><?php _e('Group','revslider'); ?></sr-drops>
				<sr-drops data-v="presentation"><?php _e('Presentation','revslider'); ?></sr-drops>				
				<sr-drops data-v="article"><?php _e('Article','revslider'); ?></sr-drops>
			</sr-drop>
			<sr-input wide><input name="ARIA Label" replace r="acc.label" viewchild="layer_acc" type="text"><span noicon="" class="sr--form--otitle"><?php _e('ARIA Label','revslider'); ?></span></sr-input>	
			<sr-input wide><input name="ARIA Labeled by" replace r="acc.labeledby" viewchild="layer_acc" type="text"><span noicon="" class="sr--form--otitle"><?php _e('ARIA Labeled by','revslider'); ?></span></sr-input>	
			<sr-drop wide  class="sr--mb--15" r="acc.haspopup" viewchild="layer_acc">
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('Triggers any PopUp','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>                    
				<sr-drops data-v="true"><?php _e('True','revslider'); ?></sr-drops>
				<sr-drops data-v="false"><?php _e('False','revslider'); ?></sr-drops>
			</sr-drop> 
			
			<sr-sp h="5"></sr-sp>			
		</sr-separator-body>		
	</sr-separator>
</sr-sh>
<sr-sp h="15"></sr-sp>
<sr-wrap style="text-align:center" basic="" wide=""><sr-button clean="" class="sr--cta" data-action="editor.module.openGlobalAccess"><svg class="sr--icon" width="12" height="11"><use xlink:href="#Dashboard_Global"></use></svg><?php _e('Module Accessibility Settings','revslider'); ?></sr-button></sr-wrap>
