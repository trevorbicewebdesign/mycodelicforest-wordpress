<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-wrap-dep dep="not[column]">
	<sr-separator keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Position & Size','revslider'); ?></sr-separator-title>			
		</sr-separator-head>
		<sr-separator-body>	
			<sr-wrap-dep dep="in[root] && globalslide">
				<sr-drop wide r="sZ" viewchild="layer_basics" data-v="default">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Static Slide Placement','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="default"><?php _e('Default','revslider'); ?></sr-drops>
					<sr-drops data-v="front"><?php _e('Front','revslider'); ?></sr-drops>
					<sr-drops data-v="back"><?php _e('Back','revslider'); ?></sr-drops>					
				</sr-drop>	
			</sr-wrap-dep>		
			<sr-wrap-dep dep="(in[root] && absolute) || is[row]"><!--
			--><sr-tabs-wrap viewchild="layer_basics"  r="rTo">
					<sr-tab data-pointerenter="B.pointer.rTo.show" data-pointerleave="B.pointer.rTo.hide" data-pointerparams="cfc" left half  data-v="cfc"><?php _e('Content Flow','revslider'); ?></sr-tab>
					<sr-tab data-pointerenter="B.pointer.rTo.show" data-pointerleave="B.pointer.rTo.hide" data-pointerparams="slide" right half data-v="slide"><?php _e('Full Stage','revslider'); ?></sr-tab>
				</sr-tabs-wrap>
				<sr-sp h="15"></sr-sp>
			</sr-wrap-dep>
			
			<sr-wrap-dep dep="is[shape,group] && in[group]"><!--			
			--><sr-tabs-wrap viewchild="layer_basics" r="size.cMode">
					<sr-tab left half data-v="custom" onchange="editor.elements.panel.updateDependecy" data-onundoredo="editor.elements.panel.updateDependecy"><?php _e('Custom','revslider'); ?></sr-tab>
					<sr-tab right half data-v="fullinset" onchange="editor.elements.panel.updateDependecy" data-onundoredo="editor.elements.panel.updateDependecy"><?php _e('Inset','revslider'); ?></sr-tab>					
				</sr-tabs-wrap>
				<sr-sp h="15"></sr-sp>
				<sr-wrap-dep dep="inset">
					<sr-bmp type="margin" idpref="sr_layer_inset_" responsive respshow="f-320middle" r="m" viewchild="layer_basics"></sr-bmp>
				</sr-wrap-dep>	
			</sr-wrap-dep>			
			<sr-wrap-dep dep="notinset">
				<sr-wrap-dep style="position:relative" dep="absolute">								
					<sr-input onethird class="sr--mr--10"><input id="sr_layer_posx" replace responsive="scale" respshow="f-320middle" r="pos.x.#LEV#" livevisup autocomplete="off" viewchild="layer_basics" type="text" number="true" min="-5000" max="5000" suffix="px" fallback="0px" validate="true" ><span noicon="" class="sr--form--otitle"><?php _e('X','revslider'); ?></span></sr-input><!--
					--><sr-input onethird><input id="sr_layer_posy" replace responsive="scale" respshow="f-320middle" r="pos.y.#LEV#" livevisup autocomplete="off" viewchild="layer_basics" type="text" number="true" min="-5000" max="5000" suffix="px" fallback="0px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Y','revslider'); ?></span></sr-input>					
					<sr-aligner class="sr--pos--align" r="pos.h.#LEV#,pos.v.#LEV#" viewchild="layer_basics">
						<sr-aligner-wrap >
							
							<sr-aligner-pos data-v="left top" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both"></sr-aligner-pos>
							<sr-aligner-pos data-v="center top" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both"></sr-aligner-pos>
							<sr-aligner-pos data-v="right top" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both"></sr-aligner-pos>
							<sr-aligner-pos data-v="left center" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both"></sr-aligner-pos>
							<sr-aligner-pos data-v="center center" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both" class="checked"></sr-aligner-pos>
							<sr-aligner-pos data-v="right center" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both"></sr-aligner-pos>
							<sr-aligner-pos data-v="left bottom" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both"></sr-aligner-pos>
							<sr-aligner-pos data-v="center bottom" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both"></sr-aligner-pos>
							<sr-aligner-pos data-v="right bottom" data-action="B.aligner.update,editor.elements.resetPos" data-aparams= "both"></sr-aligner-pos>							
						</sr-aligner-wrap>
					</sr-aligner>	
				</sr-wrap-dep>
				<sr-wrap-dep dep="not[row,column]"><!--
					--><sr-input onethird class="sr--mr--10">
						<input id="sr_layer_sizew" replace data-onchange="editor.elements.layerSizeHandling" data-onchangeparams="size.h.#LEV#" responsive="scale" respshow="f-320middle" r="size.w.#LEV#" viewchild="layer_basics" livevisup autocomplete="off" type="text" number="true" min="0" max="5000" suffix="px|%|auto" calc="true" lastsuffix="px"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('W','revslider'); ?></span>
						<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
							<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
							<sr-drops data-v="100px">100px</sr-drops>
							<sr-drops data-v="100%">100%</sr-drops>
							<sr-drops data-v="#1/3#">#1/3#</sr-drops>
							<sr-drops data-v="#2/3#">#2/3#</sr-drops>
							<sr-drops data-v="auto">auto</sr-drops>
						</sr-drop> 
					</sr-input><!--
					--><sr-input onethird>
							<input id="sr_layer_sizeh" replace data-onchange="editor.elements.layerSizeHandling" data-onchangeparams="size.w.#LEV#" responsive="scale" respshow="f-320middle" r="size.h.#LEV#" viewchild="layer_basics" livevisup autocomplete="off" type="text" number="true" min="0" max="5000" suffix="px|%|auto" calc="true" lastsuffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('H','revslider'); ?></span>
							<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
								<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
								<sr-drops data-v="100px">100px</sr-drops>
								<sr-drops data-v="100%">100%</sr-drops>
								<sr-drops data-v="#1/3#">#1/3#</sr-drops>
								<sr-drops data-v="#2/3#">#2/3#</sr-drops>
								<sr-drops data-v="auto">auto</sr-drops>
							</sr-drop>
						</sr-input><!--
					--><sr-wrap onethird><span r="size.sProp" id="sr_size_lock_toggler" data-action="B.locker.toggle" viewchild="layer_basics" class="sr--lock sr--ml--5"><svg class="sr--icon" width="11" height="12.83" transform="translate(0, -1)"><use xlink:href="#Options_Lock"></use></svg></span></sr-wrap>
				</sr-wrap-dep>
				<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="size.mm" viewchild="layer_basics" data-sh="#sr_layer_mm_settings" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Min/Max Width & Height','revslider'); ?></span></sr-wrap>
				<sr-sp h="5"></sr-sp>
				<!-- MINIMUM MAXIMUM WIDTH HEIGHT -->
				<sr-wrap id="sr_layer_mm_settings">
					<sr-sp h="10"></sr-sp>
					<sr-wrap-dep dep="not[row]" basic>						
							<sr-input half class="sr--mr--10">
								<input id="sr_layer_sizeminw" replace responsive="scale" respshow="f-320middle" r="size.minW.#LEV#" viewchild="layer_basics" livevisup autocomplete="off" type="text" number="true" min="0" max="5000" suffix="px|none|auto" lastsuffix="px"  validate="true" extvalidate="editor.elements.sizeMinMax"><span noicon="" class="sr--form--otitle"><?php _e('Min W.','revslider'); ?></span>
								<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
									<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
									<sr-drops data-v="100px">100px</sr-drops>															
									<sr-drops data-v="none">none</sr-drops>
									<sr-drops data-v="auto">auto</sr-drops>
								</sr-drop> 
							</sr-input><!--
						--><sr-input half>
								<input id="sr_layer_sizemaxw" replace responsive="scale" respshow="f-320middle" r="size.maxW.#LEV#" viewchild="layer_basics" livevisup autocomplete="off" type="text" number="true" min="0" max="5000" suffix="px|none" lastsuffix="px" validate="true"  extvalidate="editor.elements.sizeMinMax"><span noicon="" class="sr--form--otitle"><?php _e('Max W.','revslider'); ?></span>
								<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
									<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
									<sr-drops data-v="100px">100px</sr-drops>														
									<sr-drops data-v="none">none</sr-drops>								
								</sr-drop>
							</sr-input>						
					</sr-wrap-dep>	
					<sr-wrap basic="" class="sr--mb--5">
						<sr-input half class="sr--mr--10 sr--mb--0">
							<input id="sr_layer_sizeminh" replace responsive="scale" respshow="f-320middle" r="size.minH.#LEV#" viewchild="layer_basics" livevisup autocomplete="off" type="text" number="true" min="0" max="5000" suffix="px|none|auto" lastsuffix="px"  validate="true"  extvalidate="editor.elements.sizeMinMax"><span noicon="" class="sr--form--otitle"><?php _e('Min H.','revslider'); ?></span>
							<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
								<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
								<sr-drops data-v="100px">100px</sr-drops>															
								<sr-drops data-v="none">none</sr-drops>
								<sr-drops data-v="auto">auto</sr-drops>
							</sr-drop> 
						</sr-input><!--
					--><sr-wrap-dep half dep="not[row]"><!--
						--><sr-input wide class="sr--mb--0">
								<input id="sr_layer_sizemaxh" class="sr--mb--0" replace responsive="scale" respshow="f-320middle" r="size.maxH.#LEV#" viewchild="layer_basics" livevisup autocomplete="off" type="text" number="true" min="0" max="5000" suffix="px|none" lastsuffix="px" validate="true"  extvalidate="editor.elements.sizeMinMax"><span noicon="" class="sr--form--otitle"><?php _e('Max H.','revslider'); ?></span>
								<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
									<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
									<sr-drops data-v="100px">100px</sr-drops>															
									<sr-drops data-v="none">none</sr-drops>
								</sr-drop>
							</sr-input>
						</sr-wrap-dep>
					</sr-wrap>					
				</sr-wrap>
				<sr-sp h="15"></sr-sp>

			</sr-wrap-dep>
		</sr-separator-body>
	</sr-separator>
	<!-- Slot for addon-registered position modes (see SR7.editor.registerPositionMode).
	     Placed OUTSIDE the Position separator so each mode body is its own top-level
	     section (with its own divider line). Shown only when layer.rTo === mode.value. -->
	<sr-rto-bodies></sr-rto-bodies>
</sr-wrap-dep>