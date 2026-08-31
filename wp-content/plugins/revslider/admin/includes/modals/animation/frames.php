<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-wrap class="sr_preset_advanced_modes sr--force--hide" value="advanced">
	<sr-wrap-dep dep="layerinscene">
		<sr-separator  id="sr_anim_parts_wrap" keepborder class="sr--bg--level2">
			<sr-separator-head notoggle nominihover>
				<sr-separator-title class="sr--bold" style="color: var(--sr-col-w3);"><?php _e('Animation Targets','revslider'); ?></sr-separator-title>            
				<sr-wrap-dep wide="" dep="notpart[content]&notpart[bg]" class="sr--dark--par--hover sr--mini--title sr--mb--0" style="float:right" clean=""><sr-button viewchild="layer_animations" id="sr_parts_removepart" data-action="editor.elements.parts.remove"></sr-button></sr-wrap-dep>
			</sr-separator-head>	
			<sr-separator-body>
				<sr-options-menu id="sr_anim_parts" r="tl.#SCENE#" viewchild="layer_animations" animation style="padding-top:0px" class="sr--parts--list sr--left--organised" fourperrow="true" higher="true">
					<sr-nav-btn data-v="content" class="selected" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="20" height="20.953" transform="translate(0,0)"><use xlink:href="#CLayers"></use></svg></sr-icon-wrap><span><?php _e('Layer','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="bg" class="selected" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="20" height="20.953" transform="translate(0,0)"><use xlink:href="#Elements_Image"></use></svg></sr-icon-wrap><span><?php _e('Background','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="pan" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="16" height="16" transform="translate(0,2)"><use xlink:href="#PanZoom"></use></svg></sr-icon-wrap><span><?php _e('PanZoom','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="mask" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="22" height="22" transform="translate(0,1)"><use xlink:href="#Preset_Mask"></use></svg></sr-icon-wrap><span><?php _e('Mask','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="clip" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="20" height="20" transform="translate(0,1)"><use xlink:href="#Preset_Slide"></use></svg></sr-icon-wrap><span><?php _e('Clip','revslider'); ?></span></sr-nav-btn>            
					<sr-nav-btn data-v="filter" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="16" height="14.471" transform="translate(0,3)"><use xlink:href="#Timeline_Filter"></use></svg></sr-icon-wrap><span><?php _e('Filter','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="effects" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="16" height="16" transform="translate(0,2)"><use xlink:href="#Timeline_Filter"></use></svg></sr-icon-wrap><span><?php _e('Effects','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="loop" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="18.6" height="8.2" transform="translate(0,7) scale(1.5)"><use xlink:href="#Toolbar_Stage"></use></svg></sr-icon-wrap><span><?php _e('Wrapper','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="repeat" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="14" height="14" transform="translate(0,1)"><use xlink:href="#General_Refresh"></use></svg></sr-icon-wrap><span><?php _e('Repeats','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="chars" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="22" height="2" transform="translate(0,6)"><use xlink:href="#Timeline_Chars"></use></svg></sr-icon-wrap><span><?php _e('Chars.','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="words" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="21.9" height="17.9" transform="translate(0,2)"><use xlink:href="#Timeline_Words"></use></svg></sr-icon-wrap><span><?php _e('Words','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn data-v="lines" data-action="editor.elements.parts.select"><sr-icon-wrap><svg class="sr--icon" width="17" height="11.9" transform="translate(0,4)"><use xlink:href="#Timeline_Line"></use></svg></sr-icon-wrap><span><?php _e('Lines','revslider'); ?></span></sr-nav-btn>
					<sr-nav-btn id="sr_animtargets_add" data-v="modify" style="position:relative;height:55px">
						<sr-drop id="sr_used_parts" style="width:100%;height:100%;position:absolute;top:0px;left:0px;padding-top:2px" class="sr--drop--only--icon"  data-source="tlparts" data-v="" data-onchange="editor.elements.parts.change" dropsw="250" dropsh="350" data-phor="left" data-pver="bottom">                
							<sr-icon-wrap><svg class="sr--icon" width="16" height="16" transform="translate(0,-3)"><use xlink:href="#Dashboard_Add"></use></svg></sr-icon-wrap><span><?php _e('Add','revslider'); ?></span>                
						</sr-drop>
					</sr-nav-btn>        
				</sr-options-menu>
			</sr-separator-body>
		</sr-separator>
	</sr-wrap-dep>
	<sr-wrap-dep dep="notmotionpath&notpart[bg]">
		<sr-separator noborder class="sr--bg--level2">
			<sr-separator-head notoggle style="cursor:default">
				<sr-separator-title class="sr--bold" id="sr_framegroup_title"><?php _e('KeyFrames','revslider'); ?></sr-separator-title>
				<sr-drop id="sr_used_custom_fgs" data-source="tlfgs" data-v="" data-onchange="editor.elements.framegroups.change" data-pos="after" dropsw="250" dropsh="350" shiftx="-50px" shifty="0px">                
					<span class="sr--drop--icon"><svg width="10" height="4.975" transform="translate(0, 0)"><use xlink:href="#DropFull"></use></svg></span>
				</sr-drop>
				<sr-frames-mods>
				<sr-frames-custom id="sr_frames_custom" data-action="editor.elements.frames.extract" class="sr-has-tooltip" data-tooltip-key="frames_custom_on"><svg class="sr--icon" width="14" height="14" transform="translate(0, -1)"><use xlink:href="#Ccustom"></use></svg></sr-frames-custom><!--	
				--><sr-frames-repeats id="sr_frames_repeats" data-action="editor.elements.frames.repeats.open" class="sr-has-tooltip" data-tooltip-key="loop_between_frames"  ><svg class="sr--icon" width="14" height="15.646" transform="translate(0, -1)"><use xlink:href="#CLoop"></use></svg></sr-frames-repeats>
				</sr-frames-mods>
			</sr-separator-head>
			<sr-separator-body>
				<sr-fieldset viewchild="layer_animations" id="layerframes"  data-source="editor.elements.frames.fieldset" class="sr--mb--0"></sr-fieldset>		
				<sr-sp h="15"></sr-sp>
			</sr-separator-body>
		</sr-separator>
	</sr-wrap-dep>	
	<sr-wrap-dep dep="frameselected&&extractedpart&layerinscene&notfirstframe">
		<sr-separator class="sr-anim-separator-container" keepborder>
			<sr-separator-head notoggle>
				<sr-separator-title><?php _e('Timing and Flow','revslider'); ?></sr-separator-title>	
				<sr-wrap-dep wide dep="notfirstframes&&notmotionpath" class="sr--dark--par--hover sr--mini--title sr--mb--0" style="float:right" clean=""><sr-button viewchild="layer_animations"  data-action="editor.elements.frames.remove"><svg class="sr--mr--5 sr--icon" width="9" height="9.5" transform="translate(0, -1)"><use xlink:href="#Dashboard_Delete"></use></svg><?php _e('Remove Frame','revslider'); ?></sr-button></sr-wrap-dep>
			</sr-separator-head>	
			<sr-separator-body>
				<!--<sr-wrap-dep wide dep="notfirstframe">-->
					<sr-input wide class="sr--mr--10"><input id="sr_frame_duration" replace r="tl.#FRAME#.d" viewchild="layer_animations" type="text" validate extvalidate="editor.elements.frames.validatelength" data-onupdate="editor.elements.frames.render" data-onupdateparams="tlredraw"><span id="sr_frame_duration_title" noicon="" class="sr--form--otitle"><?php _e('Duration','revslider'); ?></span></sr-input><!--
					--><sr-drop wide animation data-v="" r="tl.#FRAME#.e" data-source="ease" viewchild="layer_animations" data-onchange="editor.elements.frames.render" ignoreredraw>
						<sr-drop-view>
							<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
							<span class="sr--form--otitle"><?php _e('Easing','revslider'); ?></span>
							<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
						</sr-drop-view>
					</sr-drop>							
					<sr-wrap-dep half dep="part[chars,words,lines]" class="sr--mr--10"><sr-input wide><input replace id="sr_frame_delay" r="tl.#FRAME#.sd" viewchild="layer_animations" type="text" validate number="true" min="0" max="1000"  data-onupdate="editor.elements.frames.render" data-onupdateparams="tlredraw"><span noicon="" class="sr--form--otitle"><?php _e('Split Delay','revslider'); ?></span></sr-input></sr-wrap-dep><!--
					--><sr-wrap-dep half dep="part[bg]&bgrowcol" class="sr--mr--10"><sr-input wide id="framespread" value="default#;#start#;#end#;#center#;#edges#;#random#;#slidebased#;#oppslidebased"><input replace id="sr_frame_spread" r="tl.#FRAME#.sd" viewchild="layer_animations" type="text" validate number="true" min="0" max="5000"  data-onupdate="editor.elements.frames.render" data-onupdateparams="tlredraw"><span noicon="" class="sr--form--otitle"><?php _e('Total Spread','revslider'); ?></span></sr-input></sr-wrap-dep><!--
					--><sr-wrap-dep half dep="part[chars,words,lines]||bgrowcol"><sr-drop wide animation data-v="" r="tl.#FRAME#.dir" data-source="splitdelays" dropsw="200" viewchild="layer_animations" data-onchange="editor.elements.frames.bgrender" ignoreredraw data-sh="#framespread" data-shdep="#eqvalue">
							<sr-drop-view>
								<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
								<span class="sr--form--otitle"><?php _e('Dir','revslider'); ?></span>
								<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>							
							</sr-drop-view>
						</sr-drop></sr-wrap-dep>						
				<!--</sr-wrap-dep>				-->				
				<sr-sp h="5"></sr-sp>
			</sr-separator-body>		
		</sr-separator>
	</sr-wrap-dep>
	<sr-wrap-dep dep="part[bg]">		
		<sr-wrap-dep dep="part[bg]&&multianimengine">
			<sr-sp h="15"></sr-sp>
			<sr-drop wide animation data-v="" class="sr--mb--0" r="tl.#FRAME#.eng" data-source="bganimengines" dropsw="200" viewchild="layer_animations" data-onchange="editor.elements.animpresets.updateEngineAndCanvas,editor.elements.frames.bgrender+100">
				<sr-drop-view>
					<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
					<span class="sr--form--otitle"><?php _e('Engine','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>							
				</sr-drop-view>
			</sr-drop>
			<sr-sp h="15"></sr-sp>
		</sr-wrap-dep>		
	</sr-wrap-dep>
	<sr-wrap-dep dep="frameselected&&layerinscene"> 	
		<sr-separator class="sr-anim-separator-container">
			<sr-wrap-dep dep="notpart[bg,effects]">
				<sr-separator-head notoggle>
					<sr-separator-title id="sr_fg_attr_title"><?php _e('Attributes','revslider'); ?></sr-separator-title>
					<sr-wrap-dep wide dep="notmotionpath" class="sr--dark--par--hover sr--mini--title sr--mb--0" style="float:right" clean=""><sr-button viewchild="layer_animations"  data-action="editor.elements.frames.resetCall"><svg class="sr--icon" width="9" height="8" style="transform:scaleX(-1) translate(0px,-1px)"><use xlink:href="#General_Refresh"></use></svg><?php _e('Reset Keyframe','revslider'); ?></sr-button></sr-wrap-dep>
				</sr-separator-head>				
			</sr-wrap-dep>
			<sr-wrap-dep dep="part[bg]&isanimateCore">			
				<sr-panel-invers class="sr--anim--panel--invers">
					<sr-tabs-wrap animation id="sr_frames_bg_settings" wrap="true">
						<sr-tab class="sr--tab--call sr--active--tab" onethird="true" ltop="true" data-tab-target-group="3" data-sr-tabc="sr_bg_anim_transition"><?php _e('Transition','revslider'); ?></sr-tab>
						<sr-tab class="sr--tab--call" onethird="true" none="true" data-tab-target-group="3" data-sr-tabc="sr_bg_anim_in"><?php _e('Incoming','revslider'); ?></sr-tab>
						<sr-tab class="sr--tab--call" onethird="true" rtop="true" data-tab-target-group="3" data-sr-tabc="sr_bg_anim_out"><?php _e('Leaving','revslider'); ?></sr-tab>
						<sr-tab class="sr--tab--call" half="true" lbottom="true" data-tab-target-group="3" data-sr-tabc="sr_bg_anim_filters"><?php _e('Filter','revslider'); ?></sr-tab>
						<sr-tab class="sr--tab--call" half="true" rbottom="true" data-tab-target-group="3" data-sr-tabc="sr_bg_anim_3d"><?php _e('3D','revslider'); ?></sr-tab>
					</sr-tabs-wrap>
				</sr-panel-invers>
			</sr-wrap-dep>			
			<sr-separator-body>
				<sr-fieldset viewchild="layer_animations" id="layerframeattributes"  data-source="editor.elements.attrs.fieldset" class="sr--mb--0"></sr-fieldset>
			</sr-separator-body>
		</sr-separator>
	</sr-wrap-dep>
</sr-wrap>