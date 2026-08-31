<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Background','revslider'); ?></sr-separator-title>
		<sr-button class="sr--on--par--hover sr--mini--title sr--mb--0" style="pointer-events:all;float:right" clean="" data-action="B.imgPick.edit"><svg class="sr--icon" width="13" height="13"><use xlink:href="#Toolbar_Edit"></use></svg></sr-button>
	</sr-separator-head>
	<sr-separator-body>  
		<sr-wrap half class="sr--form--grp sr--mr--10" half><sr-color-mini data-v="transparent" r="bg.color" data-type="background" class="sr--mr--10" data-onchange="editor.elements.bg.color" data-onclose="editor.elements.bg.color" data-oncloseparams="final" data-undoredo="editor.elements.bg.color" viewchild="layer_style"></sr-color-mini><span class="sr--mr--30"><?php _e('BG Color','revaslider');?></span></sr-wrap><!--
		--><sr-wrap-dep half dep="not[video]" basic="" class="sr--form--grp"><sr-onoff r="bg.image.u" viewchild="layer_style" data-sh="#sr_layer_image_bg" data-prepareon="editor.elements.bg.image.enable" data-onchange="editor.elements.bg.image.enableDisable" class="sr--mr--10"></sr-onoff><span><?php _e('BG Image','revslider'); ?></span></sr-wrap-dep><!--
		--><sr-wrap-dep dep="not[video]">
			<sr-wrap id="sr_layer_image_bg" class="sr_image_selector">
				<sr-sp h="15"></sr-sp>
				<sr-wrap>
					<sr-wrap inline class="sr--mr--12">
						<sr-bg-src>
							<sr-bg-img r="bg.image.src" viewchild="layer_style" data-onchange="editor.elements.bg.image.update">
								<svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
								<sr--bg--picker-wrap class="sr--with--aipicker">
									<svg data-action="B.imgPick.wp" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
									<svg data-action="B.imgPick.sr" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>
									<sr-button data-action="B.imgPick.ai" class="sr--bg--picker--ai"><?php _e('AI','revslider'); ?><svg width="15.145" height="13.865" class="sr--icon" transform="translate(0,6)"><use xlink:href="#AIStar"></use></svg></sr-button>
								</sr--bg--picker-wrap>
								<svg data-action="B.imgPick.clear" viewchild="layer_style" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
							</sr-bg-img>
							<sr-bg-pos-wrap r="bg.image.pos.x,bg.image.pos.y" default="50%" viewchild="layer_style" data-onchange="editor.elements.bg.image.update">
								<sr-bg-pos data-v="0% 0%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="50% 0%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="100% 0%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="0% 50%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="50% 50%" data-action="B.aligner.update" class="checked"></sr-bg-pos>
								<sr-bg-pos data-v="100% 50%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="0% 100%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="50% 100%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="100% 100%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-wrap basic class="sr_image_custompos"><span class="sr--form--otitle">%</span><sr-bg-pos class="sr--custom--aligner" data-v="custom" data-action="B.aligner.update"  data-sh="#sr_custom_bgimgpos_sh_a"></sr-bg-pos></sr-wrap>    
							</sr-bg-pos-wrap>
						</sr-bg-src>
					</sr-wrap><!--
					--><sr-wrap half style="overflow:visible">
						<sr-drop data-onchange="B.imgPick.draw,editor.elements.bg.image.update" r="bg.image.size" viewchild="layer_style" class="sr_image_bgsize" wide data-v="cover" dropsw="200" dropsh="300">
							<sr-drop-view>
								<span class="sr--drop--value">Cover</span>
								<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
							</sr-drop-view>
							<sr-drops data-v="cover"><?php _e('Cover','revslider'); ?></sr-drops>
							<sr-drops data-v="contain"><?php _e('Contain','revslider'); ?></sr-drops>
							<sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops>							
							<sr-drops data-v="" data-splitvalue=" " data-vpattern="##inp1##%">%
								<sr-wrap inline right>
									<sr-input mini class="sr--basic" style="width:70px"><input name="%" style="text-align:right" data-onchange="B.drop.combine" data-vref="inp1"  data-onchange="editor.elements.bg.image.update" class="sr--inp--pattern" data-type="text"  placeholder="100%" livevisup autocomplete="off" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input>									
								</sr-wrap>
							</sr-drops>
							<sr-drops data-v="" data-splitvalue=" " data-vpattern="##inp2##px">px
								<sr-wrap inline right>
									<sr-input mini class="sr--basic" style="width:70px"><input name="%" style="text-align:right" data-onchange="B.drop.combine" data-vref="inp2"  data-onchange="editor.elements.bg.image.update" class="sr--inp--pattern" data-type="text"  placeholder="500" livevisup autocomplete="off" validate="true" number="true" suffix="px" lastSuffix="px"></sr-input>									
								</sr-wrap>
							</sr-drops>
							<sr-drops data-ignoreclick="true" class="sr--nodrpsel" data-onopen="populate"><?php _e('Repeat X');?><sr-wrap inline="" right=""><sr-onoff r="bg.image.rx" viewchild="layer_style" livevisup autocomplete="off" data-onchange="editor.elements.bg.image.update"></sr-onoff></sr-wrap></sr-drops><!--
							--><sr-drops data-ignoreclick="true" class="sr--nodrpsel" data-onopen="populate"><?php _e('Repeat Y');?><sr-wrap inline="" right=""><sr-onoff r="bg.image.ry" viewchild="layer_style" livevisup autocomplete="off" data-onchange="editor.elements.bg.image.update"></sr-onoff></sr-wrap></sr-drops>
						</sr-drop>
						<sr-wrap id="sr_custom_bgimgpos_sh_a" basic wide>
							<sr-input half class="sr--basic sr--mr--8"><input class="sr--bg--custpos" name="Pos X" r="bg.image.pos.x" livevisup autocomplete="off" data-onchange="B.imgPick.draw,editor.elements.bg.image.update" livevisup autocomplete="off" repopulate viewchild="layer_style" data-type="text" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input><!--
							--><sr-input half class="sr--basic"><input class="sr--bg--custpos" name="Pos Y" r="bg.image.pos.y" livevisup autocomplete="off" data-onchange="B.imgPick.draw,editor.elements.bg.image.update" livevisup autocomplete="off" repopulate data-type="text"  viewchild="layer_style" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input>
						</sr-wrap>
					</sr-wrap>
				</sr-wrap>
				<sr-sp h="10"></sr-sp>
				<sr-input wide="" class="sr--basic sr--mr--0"><input style="padding-right:80px"  class="sr_image_external sr--basic" data-onchange="B.imgPick.ext" data-type="text" r="bg.image.src" viewchild="layer_style" autocomplete="off"><span noicon="" class="sr--form--otitle"><?php _e('Image URL','revslider'); ?></span></sr-input>
				<sr-sp h="10"></sr-sp>
				<sr-drop wide class="sr_image_variants sr--mb--0" data-onchange="B.imgPick.draw" data-v="none">
					<sr-drop-view>
						<span class="sr--drop--value"><?php _e('Select Background Image','revslider'); ?></span>
						<span class="sr--form--otitle">(Dimension)</span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
				</sr-drop>
				<sr-wrap-dep wide dep="is[bubblemorph]"><sr-sp h="10"></sr-sp></sr-wrap-dep>
					
			</sr-wrap>
			
			<sr-wrap-dep wide dep="is[bubblemorph]">				
				<sr-wrap basic half class="sr--mr--10"></sr-wrap><sr-wrap half basic class="sr--form--grp"><sr-onoff r="bg.fc" viewchild="layer_style" data-onchange="editor.elements.bg.forceCanvas" data-undoredo="editor.elements.bg.forceCanvas" class="sr--mr--10"></sr-onoff><span><?php _e('Force Canvas','revslider'); ?></span></sr-wrap>
			</sr-wrap-dep>
			<sr-wrap-dep dep="not[image,video,shape,slidebg,container,audio,svg]">			
				<sr-sp h="10"></sr-sp>			
				<sr-drop  r="bg.bClip" responsivedata-onchange="editor.elements.text.color" data-undoredo="editor.elements.text.color" viewchild="layer_style" half data-v="" dropsw="200" class="sr--mb--0 sr--mr--10">
					<sr-drop-view>
						<span class="sr--drop--value">None</span>
						<span class="sr--form--otitle"><?php _e('Clip','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>					
					</sr-drop-view>			
					<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
					<sr-drops data-v="text"><?php _e('Text','revslider'); ?></sr-drops>
					<!--<sr-drops data-v="border-box"><?php _e('Border','revslider'); ?></sr-drops>			
					<sr-drops data-v="padding-box"><?php _e('Padding','revslider'); ?></sr-drops>
					<sr-drops data-v="content-box"><?php _e('Content','revslider'); ?></sr-drops>-->
				</sr-drop><!--
				--><sr-drop  r="bg.tFCol" responsive viewchild="layer_style" data-onchange="editor.elements.text.color" data-undoredo="editor.elements.text.color"  half data-v="" dropsw="200" class="sr--mb--0">
					<sr-drop-view>
						<span class="sr--drop--value" style="width: 51%;text-overflow: ellipsis;overflow: hidden;vertical-align: top;">None</span>
						<span class="sr--form--otitle"><?php _e('Text Fill','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>					
					</sr-drop-view>			
					<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
					<sr-drops data-v="transparent"><?php _e('Transparent','revslider'); ?></sr-drops>				
				</sr-drop>
			</sr-wrap-dep>
		</sr-wrap-dep>
		<sr-sp h="20"></sr-sp>  
	</sr-separator-body>
</sr-separator>

