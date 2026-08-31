<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-wrap r="#MODULE#.mod.scroll" data-shdep="true" data-sh="#layer_parallax_scroll_settings" viewchild="layer_parallax">
	<sr-separator id="layer_parallax_scroll_settings" keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('On Scroll','revslider'); ?></sr-separator-title>			
			<sr-onoff style="right:0px" data-sh=".sr_elements_parallaxssettings" r="mod.s.u" viewchild="layer_parallax" data-onset="toolTip.prepare" data-onchange="editor.elements.parallax" data-onchangeparams="scroll"></sr-onoff>
			<sr-sh r="#MODULE#.mod.scroll" data-shdep="false" data-sh="#layer_parallax_scroll_info" viewchild="layer_parallax"><sr-tooltip id="layer_parallax_scroll_info" class="sr_elements_parallaxssettings sr--warning" data-cs="sr--tip--left" style="pointer-events:all !important; right:45px" key="warning_global_px_scroll"></sr-tooltip></sr-sh>
		</sr-separator-head>
		<sr-separator-body class="sr_elements_parallaxssettings">
			<sr-sp h="5"></sr-sp>	
			<sr-input wide class="sr--mr--10">
					<input name="Delay" replace r="mod.s.s" viewchild="layer_parallax" type="text" number="true" suffix="ms" min="0" max="100000" fallback="0" ignoreredraw validate="true">
					<span noicon="" class="sr--form--otitle"><?php _e('Delay','revslider'); ?></span>
			</sr-input>
			<sr-drop wide data-v="" r="mod.s.e" data-source="ease" viewchild="layer_parallax" ignoreredraw>
				<sr-drop-view>
					<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
					<span class="sr--form--otitle"><?php _e('Easing','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					
				</sr-drop-view>
			</sr-drop> 
			<sr-input half class="sr--mr--10">
					<input name="X Offset" replace r="mod.s.x" viewchild="layer_parallax" ignoreredraw type="text"  number="true" validate="true" min="-500" max="500" suffix="px"><span noicon="" class="sr--form--otitle"><?php _e('X','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" list="0,10,-10,50,-50,100,-100" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop> 		
				</sr-input><!--
			--><sr-input half class="sr--mr--0">
					<input name="Y Offset" replace r="mod.s.y" viewchild="layer_parallax" ignoreredraw type="text"  number="true" validate="true" min="-500" max="500" suffix="px"><span noicon="" class="sr--form--otitle"><?php _e('Y','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" list="0,10,-10,50,-50,100,-100" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop> 		
				</sr-input>	
			
			<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="mod.mask" viewchild="layer_parallax" data-onchange="layer.reWrap" class="sr--mr--10"></sr-onoff><span><?php _e('Under Mask Animation','revslider'); ?></span></sr-wrap>
			<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="mod.s.utr" viewchild="layer_parallax" data-onchange="layer.reWrap" data-sh="#sr_layer_parallax_trans" class="sr--mr--10"></sr-onoff><span><?php _e('Advanced Transforms','revslider'); ?></span></sr-wrap>
			<sr-wrap id="sr_layer_parallax_trans">
				<sr-sp h="15"></sr-sp>
				<sr-input wide>
					<input name="Scale" viewchild="layer_parallax" r="mod.s.sc" replace ignoreredraw livevisup autocomplete="off" number="true" min="0" max="10" suffix="" step="0.2" lastsuffix=""  validate="true" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Scale','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" list="0.1,0.5,1,2,5,10" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>			
					</sr-drop>
				</sr-input><!--
				--><sr-input onethird class="sr--mr--7">
					<input name="Rotate X" viewchild="layer_parallax" r="mod.s.rX" replace ignoreredraw livevisup autocomplete="off" number="true" min="-5000" max="500" suffix="deg|inherit" lastsuffix="deg"  validate="true" type="text">
					<span class="sr--input--icon"><svg width="20" height="20" transform="translate(4, 4) rotate(90)"><use xlink:href="#Options_Rotate_X"></use></svg></span>
					<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>			
					</sr-drop>
				</sr-input><!--
				--><sr-input onethird class="sr--mr--7">
					<input name="Rotate Y" viewchild="layer_parallax" r="mod.s.rY"  replace ignoreredraw livevisup autocomplete="off" number="true" min="-5000" max="5000" suffix="deg|inherit" lastsuffix="deg"  validate="true" type="text">
					<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Y"></use></svg></span>
					<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop>
				</sr-input><!--
				--><sr-input onethird>
					<input name="Rotate Z" viewchild="layer_parallax"  replace livevisup autocomplete="off" type="text" ignoreredraw r="mod.s.rZ" number="true" min="-5000" max="5000" lastsuffix="deg" suffix="deg|inherit" validate="true" type="text" livevisup>
					<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Z"></use></svg></span>
					<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop>
				</sr-input>
			</sr-wrap>
			<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="mod.s.uf" viewchild="layer_parallax" data-onchange="layer.reWrap" data-sh="#sr_layer_parallax_filter" class="sr--mr--10"></sr-onoff><span><?php _e('Filter Effects','revslider'); ?></span></sr-wrap>
			<sr-wrap id="sr_layer_parallax_filter">
				<sr-sp h="15"></sr-sp>
				<sr-input half class="sr--mr--10">
					<input name="Fade" viewchild="layer_parallax" r="mod.s.f" replace ignoreredraw livevisup autocomplete="off" number="true" min="0" max="10" suffix="|inherit" lastsuffix=""  validate="true" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Fade','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" list="inherit,0,10,25,50,75,80,100" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>			
					</sr-drop>
				</sr-input><!--
				--><sr-input half>
					<input name="Offsets" viewchild="layer_parallax" r="mod.s.t" replace ignoreredraw livevisup autocomplete="off" number="true" min="0" max="1" suffix="" lastsuffix=""  validate="true" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Offsets','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" list="0,0.1,0.25,0.5,0.75,1" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>			
					</sr-drop>
				</sr-input>
				<sr-input onethird class="sr--mr--7 sr--mb--0">
					<input name="Blur" viewchild="layer_parallax"  replace livevisup autocomplete="off" type="text" r="mod.s.b" ignoreredraw number="true" min="0" max="500" suffix="|inherit" lastsuffix="" validate="true" type="text" livevisup>
					<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Blur"></use></svg></span>
					<sr-drop class="sr--drop--only--icon" list="inherit,0,2,5,10,20,50" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop>
				</sr-input><!--
				--><sr-input onethird class="sr--mr--7 sr--mb--0">
					<input name="Grayscale" viewchild="layer_parallax" r="mod.s.g" replace livevisup autocomplete="off" ignoreredraw number="true" min="0" max="100" suffix="|inherit" lastsuffix=""  validate="true" type="text">
					<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Grayscale"></use></svg></span>
					<sr-drop class="sr--drop--only--icon" list="inherit,0,50,100" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop>
				</sr-input><!--
				--><sr-input onethird class="sr--mb--0">
					<input name="Brightness" viewchild="layer_parallax" r="mod.s.r"  replace livevisup autocomplete="off" ignoreredraw number="true" min="0" max="500" suffix="%" lastsuffix="%"  validate="true" type="text">
					<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Brightness"></use></svg></span>
					<sr-drop class="sr--drop--only--icon" list="0,50%,100%,120%,150%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop>
				</sr-input>
				
			</sr-wrap>
			<sr-sp h="20"></sr-sp>			
		</sr-separator-body>
	</sr-separator>
</sr-wrap>
<sr-wrap r="#MODULE#.mod.mouse" data-shdep="true" data-sh="#layer_parallax_mouse_settings" viewchild="layer_parallax">
	<sr-separator id="layer_parallax_mouse_settings" keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('On Mouse Move','revslider'); ?></sr-separator-title>
			<sr-onoff style="right:0px" data-sh=".sr_elements_parallaxmsettings" r="mod.m.u" data-onchange="layer.reWrap,editor.elements.parallax" data-onchangeparams="mouse" viewchild="layer_parallax"></sr-onoff>
			<sr-sh r="#MODULE#.mod.mouse" data-shdep="false" data-sh="#layer_parallax_mouse_info" viewchild="layer_parallax"><sr-tooltip id="layer_parallax_mouse_info" class="sr_elements_parallaxmsettings sr--warning" data-cs="sr--tip--left" style="pointer-events:all !important; right:45px" key="warning_global_px_mouse"></sr-tooltip>		
		</sr-separator-head>
		<sr-separator-body class="sr_elements_parallaxmsettings">
			<sr-sp h="5"></sr-sp>
			<sr-input wide class="sr--mr--10">
					<input name="Delay" replace r="mod.m.s" viewchild="layer_parallax" type="text" number="true" suffix="ms" min="0" max="100000" fallback="0" ignoreredraw validate="true">
					<span noicon="" class="sr--form--otitle"><?php _e('Delay','revslider'); ?></span>
			</sr-input>
			<sr-drop wide data-v="" r="mod.m.e" data-source="ease" viewchild="layer_parallax" ignoreredraw>
				<sr-drop-view>
					<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
					<span class="sr--form--otitle"><?php _e('Easing','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
			</sr-drop>
			<sr-input half class="sr--mr--10">
					<input name="X Offset" replace r="mod.m.x" viewchild="layer_parallax" ignoreredraw type="text"  number="true" validate="true" min="-500" max="500" suffix="px"><span noicon="" class="sr--form--otitle"><?php _e('X','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" list="0,10,-10,50,-50,100,-100" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop> 		
			</sr-input><!--
			--><sr-input half class="sr--mr--0">
					<input name="Y Offset" replace r="mod.m.y" viewchild="layer_parallax" ignoreredraw type="text"  number="true" validate="true" min="-500" max="500" suffix="px"><span noicon="" class="sr--form--otitle"><?php _e('Y','revslider'); ?></span>
					<sr-drop class="sr--drop--only--icon" list="0,10,-10,50,-50,100,-100" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
						<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
					</sr-drop> 		
			</sr-input>	
			<sr-drop wide data-v="" r="mod.m.o" viewchild="layer_parallax" ignoreredraw>
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Mouse Origin','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="c"><?php _e('Module Center','revslider'); ?></sr-drops>
					<sr-drops data-v="e"><?php _e('Entered Point','revslider'); ?></sr-drops>
				</sr-drop> 
				<sr-sp h="5"></sr-sp>								
		</sr-separator-body>
	</sr-separator>
</sr-wrap>

<sr-sh r="#MODULE#.mod.d3" data-shdep="true" data-sh="#layer_3dparallax_mouse_settings" viewchild="layer_parallax">
	<sr-separator id="layer_parallax_mouse_settings" keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Bind to Slide BG 3D','revslider'); ?></sr-separator-title>
			<sr-onoff style="right:0px"  r="mod.m.aBG" data-onchange="layer.reWrap,editor.elements.parallax" data-onchangeparams="d3" viewchild="layer_parallax"></sr-onoff>		
		</sr-separator-head>		
	</sr-separator>
</sr-sh>

<sr-sh r="#MODULE#.sbt.use" data-shdep="true" data-sh="#layer_sbt_t_settings" viewchild="layer_parallax">
	<sr-separator keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Scroll Based Timeline','revslider'); ?></sr-separator-title>
			<sr-onoff style="right:0px" data-sh=".sr_elements_sbtsettings" r="sbt.u" viewchild="layer_parallax"></sr-onoff>		
		</sr-separator-head>
		<sr-separator-body class="sr_elements_sbtsettings">
			<sr-sp h="5"></sr-sp>	
			<sr-input wide class="sr--mr--0">
				<input name="Time Offset" replace r="sbt.so" viewchild="layer_parallax" type="text" number="true" suffix="ms" min="0" max="100000" fallback="0" ignoreredraw validate="true"><!--
				--><span noicon="" class="sr--form--otitle"><?php _e('Time Offset','revslider'); ?></span>
			</sr-input>
			<sr-sp h="5"></sr-sp>
	</sr-separator>
</sr-sh>
<sr-sp h="15"></sr-sp>
<sr-wrap style="text-align:center" basic wide><sr-button clean="" class="sr--cta" data-action="editor.module.openParallax"><svg class="sr--icon" width="12" height="11"><use xlink:href="#Dashboard_Global"></use></svg><?php _e('Module Parallax Settings','revslider'); ?></sr-button></sr-wrap>
		
