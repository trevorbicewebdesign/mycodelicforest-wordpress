<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-separator keepborder class="sr_layer_bgtypes sr_updateForcehide" value="image">
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Image Content','revslider'); ?></sr-separator-title>
		<sr-button class="sr--img--edit--pen sr--mini--title sr--mb--0" style="pointer-events:all;float:right" clean="" data-action="B.imgPick.edit"><svg class="sr--icon" width="13" height="13"><use xlink:href="#Toolbar_Edit"></use></svg></sr-button>
	</sr-separator-head>
	<sr-separator-body>  						
		<sr-wrap id="sr_layer_image_content" class="sr_image_selector">				
			<sr-wrap>
				<sr-wrap inline class="sr--mr--12">
					<sr-bg-src>
						<sr-bg-img r="bg.image.src" viewchild="layer_basics" data-onchange="editor.elements.bg.image.update">
							<svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
							<sr--bg--picker-wrap class="sr--with--aipicker">
								<svg data-action="B.imgPick.wp" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
								<svg data-action="B.imgPick.sr" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>
								<sr-button data-action="B.imgPick.ai" class="sr--bg--picker--ai"><?php _e('AI','revslider'); ?><svg width="15.145" height="13.865" class="sr--icon" transform="translate(0,6)"><use xlink:href="#AIStar"></use></svg></sr-button>
							</sr--bg--picker-wrap>
							<svg data-action="B.imgPick.clear" viewchild="layer_basics" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
						</sr-bg-img>
						<sr-bg-pos-wrap r="bg.image.pos.x,bg.image.pos.y" viewchild="layer_basics" data-onchange="editor.elements.bg.image.update">
							<sr-bg-pos data-v="0% 0%" data-action="B.aligner.update"></sr-bg-pos>
							<sr-bg-pos data-v="50% 0%" data-action="B.aligner.update"></sr-bg-pos>
							<sr-bg-pos data-v="100% 0%" data-action="B.aligner.update"></sr-bg-pos>
							<sr-bg-pos data-v="0% 50%" data-action="B.aligner.update"></sr-bg-pos>
							<sr-bg-pos data-v="50% 50%" data-action="B.aligner.update" class="checked"></sr-bg-pos>
							<sr-bg-pos data-v="100% 50%" data-action="B.aligner.update"></sr-bg-pos>
							<sr-bg-pos data-v="0% 100%" data-action="B.aligner.update"></sr-bg-pos>
							<sr-bg-pos data-v="50% 100%" data-action="B.aligner.update"></sr-bg-pos>
							<sr-bg-pos data-v="100% 100%" data-action="B.aligner.update"></sr-bg-pos>							
							<sr-wrap basic class="sr_image_custompos"><span class="sr--form--otitle">%</span><sr-bg-pos class="sr--custom--aligner" data-v="custom" data-action="B.aligner.update" data-sh="#sr_custom_bgimgpos"></sr-bg-pos></sr-wrap>
						</sr-bg-pos-wrap>
						
					</sr-bg-src>
				</sr-wrap><!--
				--><sr-wrap half style="overflow:visible">
				<sr-drop data-onchange="B.imgPick.draw,editor.elements.bg.image.update" r="bg.image.size" viewchild="layer_basics" class="sr_image_bgsize" wide data-v="cover" dropsw="200" dropsh="300">
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
							<sr-drops data-ignoreclick="true" class="sr--nodrpsel" data-onopen="populate"><?php _e('Repeat X');?><sr-wrap inline="" right=""><sr-onoff r="bg.image.rx" viewchild="layer_basics" livevisup autocomplete="off" data-onchange="editor.elements.bg.image.update"></sr-onoff></sr-wrap></sr-drops>
							<sr-drops data-ignoreclick="true" class="sr--nodrpsel" data-onopen="populate"><?php _e('Repeat Y');?><sr-wrap inline="" right=""><sr-onoff r="bg.image.ry" viewchild="layer_basics" livevisup autocomplete="off" data-onchange="editor.elements.bg.image.update"></sr-onoff></sr-wrap></sr-drops>
						</sr-drop>
					<sr-wrap id="sr_custom_bgimgpos" basic wide>
						<sr-input half class="sr--basic sr--mr--8"><input class="sr--bg--custpos" name="Position X" r="bg.image.pos.x" livevisup autocomplete="off" repopulate data-onchange="B.imgPick.draw,editor.elements.bg.image.update" livevisup autocomplete="off"  viewchild="layer_basics" data-type="text" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input><!--
						--><sr-input half class="sr--basic"><input class="sr--bg--custpos"name="Position Y" r="bg.image.pos.y" livevisup autocomplete="off" repopulate data-onchange="B.imgPick.draw,editor.elements.bg.image.update" livevisup autocomplete="off"  data-type="text"  viewchild="layer_basics" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input>
					</sr-wrap>
				</sr-wrap>
			</sr-wrap>
			<sr-sp h="10"></sr-sp>
			<sr-input wide="" class="sr--basic sr--mr--0"><input style="padding-right:80px" class="sr_image_external sr--basic" data-onchange="B.imgPick.ext" data-type="text" r="bg.image.src" viewchild="layer_basics" autocomplete="off"><span noicon="" class="sr--form--otitle"><?php _e('Image URL','revslider'); ?></span></sr-input>
			<sr-sp h="10"></sr-sp>
			<sr-drop wide class="sr_image_variants sr--mb--0" data-onchange="B.imgPick.draw" data-v="none">
				<sr-drop-view>
					<span class="sr--drop--value"><?php _e('Select Background Image','revslider'); ?></span>
					<span class="sr--form--otitle">(Dimension)</span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
			</sr-drop>
		</sr-wrap>
		<sr-wrap-dep wide dep="fromstream" basic="" class="sr--form--grp sr--mt--15"><sr-onoff r="bg.image.fromStream" viewchild="layer_basics"  class="sr--mr--10"></sr-onoff><span><?php _e('Prefer Feed Image','revslider'); ?></span></sr-wrap-dep>	
		<sr-sp h="20"></sr-sp>  
	</sr-separator-body>
</sr-separator>

