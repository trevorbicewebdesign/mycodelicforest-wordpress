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
		<sr-separator-title><?php _e('SVG Style','revslider'); ?></sr-separator-title>					
	</sr-separator-head>
	<sr-separator-body>			
		<sr-wrap twothird class="sr--form--grp sr--mr--10"><sr-onoff class="sr--mr--10" data-hide=".sr_elements_showsvg" r="content.oC" viewchild="layer_style" data-onchange="editor.elements.svg.update" data-undoredo="editor.elements.svg.update"></sr-onoff><span><?php _e('Keep Original Colors','revslider'); ?></span></sr-wrap><!--
		--><sr-wrap onethird class="sr--form--grp"><sr-onoff r="content.image.u" viewchild="layer_style" data-sh=".sr_svg_image_bg_pickers" data-hide=".sr_svg_color_fill" data-prepareon="editor.elements.svg.update" data-onchange="editor.elements.svg.update" class="sr--mr--10"></sr-onoff><span><?php _e('Image','revslider'); ?></span></sr-wrap>
		<sr-wrap class="sr_elements_showsvg">
			<sr-sp h="15"></sr-sp>				
			<sr-wrap wide id="sr_svg_image_bg" class="sr_image_selector">
				<sr-wrap>
					<sr-wrap half class="sr--form--grp sr_svg_color_fill sr--mr--0"><sr-color-mini data-v="transparent" r="content.color" data-onchange="editor.elements.svg.update+t100" data-undoredo="editor.elements.svg.update" data-type="text" class="sr--mr--10" viewchild="layer_style"></sr-color-mini><span class="sr--mr--30"><?php _e('SVG Color','revaslider');?></span></sr-wrap><!--
					--><sr-wrap inline class="sr--mr--10 sr_svg_image_bg_pickers">								
						<sr-bg-src>
							<sr-bg-img r="content.image.src" viewchild="layer_style" data-onchange="editor.elements.svg.update">
								<svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
								<sr--bg--picker-wrap class="sr--with--aipicker">
									<svg data-action="B.imgPick.wp" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
									<svg data-action="B.imgPick.sr" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>
									<sr-button data-action="B.imgPick.ai" class="sr--bg--picker--ai"><?php _e('AI','revslider'); ?><svg width="15.145" height="13.865" class="sr--icon" transform="translate(0,6)"><use xlink:href="#AIStar"></use></svg></sr-button>
								</sr--bg--picker-wrap>
								<svg data-action="B.imgPick.clear" viewchild="layer_style" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
							</sr-bg-img>
							<sr-bg-pos-wrap r="content.image.pos.x,content.image.pos.y" default="50%" viewchild="layer_style" data-onchange="editor.elements.svg.update">
								<sr-bg-pos data-v="0% 0%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="50% 0%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="100% 0%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="0% 50%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="50% 50%" data-action="B.aligner.update" class="checked"></sr-bg-pos>
								<sr-bg-pos data-v="100% 50%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="0% 100%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="50% 100%" data-action="B.aligner.update"></sr-bg-pos>
								<sr-bg-pos data-v="100% 100%" data-action="B.aligner.update"></sr-bg-pos>								
							</sr-bg-pos-wrap>							
						</sr-bg-src>
					</sr-wrap><!--
					--><sr-wrap half>
						<sr-wrap basic class="sr_svg_image_bg_pickers">
							<sr-drop wide data-onchange="B.imgPick.draw,editor.elements.svg.update" r="content.image.size" viewchild="layer_style" class="sr_image_bgsize" data-v="cover" dropsw="200" dropsh="300">
								<sr-drop-view>
									<span class="sr--drop--value">Cover</span>
									<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
								</sr-drop-view>
								<sr-drops data-v="cover"><?php _e('Cover','revslider'); ?></sr-drops>
								<sr-drops data-v="contain"><?php _e('Contain','revslider'); ?></sr-drops>
								<sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops>														
							</sr-drop>
						</sr-wrap>
						<sr-wrap basic wide><sr-color-mini data-v="transparent" r="content.stroke.c" data-onchange="editor.elements.svg.update" data-undoredo="editor.elements.svg.update"responsive data-type="text" class="sr--mr--10" viewchild="layer_style"></sr-color-mini><span><?php _e('Stroke Color','revaslider');?></span></sr-wrap>
					</sr-wrap>
				</sr-wrap>												
			</sr-wrap>															
			<sr-sp h="15"></sr-sp>
			<sr-input onethird class="sr--mr--6 sr--mb--0"><!--Stroke Width-->
				<input name="Stroke Width" viewchild="layer_style" r="content.stroke.w" replace livevisup autocomplete="off" number="true" min="0" max="500" suffix="" lastsuffix="" step="0.1" validate="true" type="text" data-onupdate="editor.elements.svg.update+t100" data-undoredo="editor.elements.svg.update">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Width"></use></svg></span>
			</sr-input><!--
			--><sr-input onethird class="sr--mr--6 sr--mb--0"><!--Dash Array-->
				<input name="Dash Array" viewchild="layer_style" r="content.stroke.d"  replace validate allowedchars="1234567890," livevisup autocomplete="off" type="text" data-onupdate="editor.elements.svg.update+t100" data-undoredo="editor.elements.svg.update">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Dash"></use></svg></span>
			</sr-input><!--
			--><sr-input onethird class="sr--mb--0"><!--Dash Offset-->
				<input name="Dash Offset" viewchild="layer_style" r="content.stroke.o" replace livevisup autocomplete="off" type="text" number="true" min="-100" max="100"  step="0.5" validate="true" type="text" livevisup autocomplete="off" data-onupdate="editor.elements.svg.update+t100" data-undoredo="editor.elements.svg.update">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Offset"></use></svg></span>
			</sr-input>
		</sr-wrap>	
		<sr-sp h="18"></sr-sp>
	</sr-separator-body>
</sr-separator>

