<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>

<sr-separator>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Resize Between Devices','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>				
		<sr-wrap half basic="" class="sr--form--grp sr--mr--10"><sr-onoff r="fluid.po" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Position','revslider'); ?></span></sr-wrap><!--
		--><sr-wrap half basic="" class="sr--form--grp"><sr-onoff r="fluid.tr" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Size & Motion','revslider'); ?></span></sr-wrap>
		<sr-wrap half basic="" class="sr--form--grp sr--mr--10"><sr-onoff r="fluid.tx" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Text','revslider'); ?></span></sr-wrap><!--
		--><sr-wrap-dep half dep="is[text]"><sr-wrap basic="" class="sr--form--grp"><sr-onoff r="fluid.sp" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Padding','revslider'); ?></span></sr-wrap></sr-wrap-dep>				
		<sr-sp h="15"></sr-sp>
	</sr-separator-body>				
</sr-separator>
<sr-wrap-dep dep="notdesktop">
	<sr-separator>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Set Layer Sizes','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>	
		<sr-drop id="sr_inherit_sizes" wide data-source="fromdevices" data-v="1" class="sr--mr--10" ignoreredraw dropsw="290" dropsh="340">
			<sr-drop-view>
				<span class="sr--drop--value"><?php _e('From Desktop','revslider'); ?></span>
				<span class="sr--form--otitle"><svg class="sr--icon" width="20" height="16.36" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
		</sr-drop>
		<sr-wrap>
			<sr-wrap basic="" half inline class="sr--form--grp"><sr-wrap-dep dep="is[container]"><sr-onoff id="sr_inherit_children" class="sr--mr--10"></sr-onoff><span><?php _e('Incl. Children','revslider'); ?></span></sr-wrap-dep></sr-wrap><!--
			--><sr-wrap basic="" inline half class=""><sr-button primary="" data-action="editor.elements.inheritSizes" data-aparams="inherit" onefourth class="sr--cta sr--mr--5"><?php _e('Apply','revslider'); ?></sr-button><!--
			--><sr-button clean="" data-action="editor.elements.inheritSizes" data-aparams="reset" onefourth class="sr--cta sr--mr--0"><svg class="sr--icon" width="13.58" height="16" transform="translate(0, -2)"><use xlink:href="#Preset_Rotation"></use></svg><?php _e('Reset','revslider'); ?></sr-button>
			</sr-wrap>
		</sr-wrap>
		<sr-sp h="15"></sr-sp>
	</sr-separator-body>		
</sr-separator>
</sr-wrap-dep>
