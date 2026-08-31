
<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-popup id="sr_scene_transfer" style="width:420px">
	<sr-popup-header>
            <h2 class="sr--popup--title" id=""><?php echo __('Frames Transfer','revslider');?></h2> 
			<!--<span class="sr--text">Choose how to initialize the selected layers in this scene</span>-->
        <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
    </sr-popup-header>  
    <sr-popup-content>
		<sr-sp h="20"></sr-sp>		
		<sr-drop id="sr_scene_transfer_scene" class="sr--mb--15" data-novalue='<?php echo __('No Scene Selected','revslider');?>' wide="" data-v="" keepotitle=""  data-source="layerscenes" dropsw="350" dropsh="340">
			<sr-drop-view>
				<span class="sr--drop--value" style="padding-right:100px"><?php echo __('No Scene Selected','revslider');?></span>
				<span class="sr--form--otitle"><?php echo __('From Scene','revslider');?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
		</sr-drop>				
		<sr-tabs-wrap id="sr_scene_transfer_mode">
			<sr-tab half="" onethird="" data-v="move"><?php echo __('Move Here','revslider');?></sr-tab>			
			<sr-tab half="" onethird="" data-v="copy" class="sr--active--tab"><?php echo __('Copy Here','revslider');?></sr-tab>
		</sr-tabs-wrap>		
		<sr-sp h="15"></sr-sp>
		<sr-wrap class="sr--form--grp sr--mb--15"><sr-onoff id="sr_scene_transfer_children" class="sr--mr--10"></sr-onoff><span><?php echo __('Transfer Children','revslider');?></span></sr-wrap>
		<sr-sp h="25"></sr-sp>		
		<sr-button class="sr--cta sr--float--right sr--mb--0"  primary="" data-action="editor.elements.frames.transferScene"><svg class="sr--icon" width="13" height="13" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Addons"></use></svg><?php echo __('Transfer Scene','revslider');?></sr-button>
		<sr-sp h="0" class="sr--clear--both"></sr-sp>
	</sr-popup-content>
</sr-popup>