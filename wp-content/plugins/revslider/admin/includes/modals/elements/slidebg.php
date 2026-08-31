<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-wrap-dep dep="is[slidebg]">
	<sr-separator keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Slide Background','revslider'); ?></sr-separator-title>			
		</sr-separator-head>
		<sr-separator-body>  													
			<sr-drop r="bg.type" viewchild="layer_basics" wide data-sh=".sr_layer_bgtypes" data-onchange="editor.elements.bg.slide" data-undoredo="editor.elements.bg.slide" data-shdep="#eqvalue" data-v="image">
				<sr-drop-view>				
					<span class="sr--drop--value">Image</span>
					<span class="sr--form--otitle"><?php _e('Content Type','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="color"><?php _e('Color','revslider'); ?></sr-drops>
				<sr-drops data-v="image"><?php _e('Image','revslider'); ?></sr-drops>
				<sr-drops data-v="video"><?php _e('Video','revslider'); ?></sr-drops>			
			</sr-drop>
			<sr-wrap wide basic class="sr_layer_bgtypes sr--form--grp sr--mb--15" value="color"><sr-color-mini data-v="transparent" id="sr--slidebg--colorpicker" r="bg.color" data-type="background" class="sr--mr--10" data-onchange="editor.elements.bg.color" data-undoredo="editor.elements.bg.color" viewchild="layer_basics"></sr-color-mini><span><?php _e('Slide Background Color','revaslider');?></span></sr-wrap>
		</sr-separator-body>
	</sr-separator>	
</sr-wrap-dep>