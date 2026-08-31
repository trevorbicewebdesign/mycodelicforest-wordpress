<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-popup id="sr_new_scene" style="width:420px">
	<sr-popup-header>
            <h2 class="sr--popup--title" id=""><?php echo __('Quick Scene Setup','revslider');?></h2> 
			<!--<span class="sr--text">Choose how to initialize the selected layers in this scene</span>-->
        <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
    </sr-popup-header>  
    <sr-popup-content>
		<sr-sp h="20"></sr-sp>
		<sr-input wide="true"><input id="sr_new_scene_name" type="text" replace="true" validate="true" value="New Scene"><span class="sr--form--otitle" noicon=""><?php echo __('Scene Name','revslider');?></span></sr-input>				
		<sr-drop id="sr_new_scene_layers" class="sr--mb--15" data-novalue='<sr-lbl valuelisting="" dropinfo="" medium="" class="sr--mr--5"><?php echo __('No Layers Selected','revslider');?></sr-lbl>' wide="" multiselect="" data-v="" usecheck="" keepotitle=""  data-type="search" data-fromslide="sceneFromSlide" data-source="layers" dropsw="350" dropsh="340">
			<sr-drop-view>
				<span class="sr--drop--value" style="padding-right:100px"><sr-lbl valuelisting="" dropinfo="" medium="" class="sr--mr--5"><?php echo __('No Layers Selected','revslider');?></sr-lbl></span>
				<span class="sr--form--otitle"><?php echo __('Affected Layers','revslider');?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
		</sr-drop>				
		<sr-tabs-wrap id="sr_new_scene_type">
			<sr-tab data-sh=".sr_new_scene_type" data-shdep="#eqvalue" left="" onethird="" data-v="in"><?php echo __('Play IN','revslider');?></sr-tab>
			<sr-tab data-sh=".sr_new_scene_type" data-shdep="#eqvalue" onethird="" data-v="out"><?php echo __('Play OUT','revslider');?></sr-tab>
			<sr-tab data-sh=".sr_new_scene_type" data-shdep="#eqvalue" right="" onethird="" data-v="custom" class="sr--active--tab"><?php echo __('Custom Scene','revslider');?></sr-tab>
		</sr-tabs-wrap>
		<!--<sr-wrap class="sr_new_scene_type sr--force--hide" value="in">
			<sr-sp h="25"></sr-sp>
			<sr-wrap basic wide class="sr--mb--0"><span class="sr--form--grp"><sr-onoff class="sr--mr--10 checked" id="sr_new_scene_disablein"></sr-onoff><span>Original Timeline Waiting</span></span><sr-tooltip key="newsceneshow"></sr-tooltip></sr-wrap>                    
		</sr-wrap>		-->
		<sr-sp h="25"></sr-sp>		
		<sr-button class="sr--cta sr--float--right sr--mb--0"  primary="" data-action="editor.scene.addNew"><svg class="sr--icon" width="8" height="8"><use xlink:href="#Dashboard_Add_Mini"></use></svg><?php echo __('Add Scene','revslider');?></sr-button>
		<sr-sp h="0" class="sr--clear--both"></sr-sp>
	</sr-popup-content>
</sr-popup>