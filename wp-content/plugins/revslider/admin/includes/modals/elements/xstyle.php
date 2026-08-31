<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-wrap-dep dep="is[text,button]">
	<sr-separator keepborder notoggle>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Text Stroke','revslider'); ?></sr-separator-title>
			<sr-onoff class="sr--mr--0" style="right:0px"  data-sh=".sr_elements_txtstrokes" r="tStroke.use" data-onchange="editor.elements.tStroke.update" viewchild="layer_extra"></sr-onoff>			
		</sr-separator-head>
		<sr-separator-body class="sr_elements_txtstrokes">		
			<sr-wrap half inline basic class="sr--form--grp sr--mr--25"><sr-color-mini data-v="transparent" viewchild="layer_extra" r="tStroke.c" data-onchange="editor.elements.tStroke.update" data-undoredo="editor.elements.tStroke.update" responsive data-title="<?php _e('Stroke Color','revslider'); ?>" data-type="text" class="sr--mr--10"></sr-color-mini><span><?php _e('Stroke Color','revslider'); ?></span></sr-wrap><!--							
			--><sr-input half><input name="Text Stroke Width" viewchild="layer_extra" r="tStroke.w.#LEV#"  livevisup autocomplete="off" responsive respshow="f-320middle" number="true" min="0" max="5000" suffix="px" lastsuffix="px"  validate="true" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Stroke Width','revslider'); ?></span></sr-input>
		<sr-sp h="5"></sr-sp>
		</sr-separator-body>		
	</sr-separator>
</sr-wrap-dep>
<sr-separator noborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Spikes','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px"  data-sh=".sr_elements_spikes" r="spike.use" data-onchange="editor.elements.spike.update" viewchild="layer_extra"></sr-onoff>		
	</sr-separator-head>
	<sr-separator-body class="sr_elements_spikes">		
		<sr-drop twothird r="spike.l" viewchild="layer_extra" data-v="" class="sr--mr--10">
			<sr-drop-view>
				<span class="sr--drop--value">None</span>
				<span class="sr--form--otitle"><?php _e('Left','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>			
			<sr-drops data-v="none"><?php _e('No Spike','revslider'); ?></sr-drops>
			<sr-drops data-v="top"><?php _e('Top Spike','revslider'); ?></sr-drops>
			<sr-drops data-v="middle"><?php _e('Middle Spike','revslider'); ?></sr-drops>
			<sr-drops data-v="bottom"><?php _e('Bottom Spike','revslider'); ?></sr-drops>
			<sr-drops data-v="two"><?php _e('Two Spikes','revslider'); ?></sr-drops>
			<sr-drops data-v="three"><?php _e('Three Spikes','revslider'); ?></sr-drops>
			<sr-drops data-v="four"><?php _e('Four Spikes','revslider'); ?></sr-drops>
			<sr-drops data-v="five"><?php _e('Five Spikes','revslider'); ?></sr-drops>
		</sr-drop><!--
		--><sr-input onethird=""><input name="Left Spike Width" viewchild="layer_extra" r="spike.lw" replace="true" livevisup="true" number="true" min="0" max="100" suffix="%" validate="true" type="text" lastsuffix="%"><span class="sr--form--otitle" noicon=""><?php _e('Width','revslider'); ?></span></sr-input>
		<sr-drop twothird r="spike.r" viewchild="layer_extra" data-v="" class="sr--mr--10">
				<sr-drop-view>
					<span class="sr--drop--value">None</span>
					<span class="sr--form--otitle"><?php _e('Right','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>			
				<sr-drops data-v="none"><?php _e('No Spike','revslider'); ?></sr-drops>
			<sr-drops data-v="top"><?php _e('Top Spike','revslider'); ?></sr-drops>
			<sr-drops data-v="middle"><?php _e('Middle Spike','revslider'); ?></sr-drops>
			<sr-drops data-v="bottom"><?php _e('Bottom Spike','revslider'); ?></sr-drops>
			<sr-drops data-v="two"><?php _e('Two Spikes','revslider'); ?></sr-drops>
			<sr-drops data-v="three"><?php _e('Three Spikes','revslider'); ?></sr-drops>
			<sr-drops data-v="four"><?php _e('Four Spikes','revslider'); ?></sr-drops>
			<sr-drops data-v="five"><?php _e('Five Spikes','revslider'); ?></sr-drops>
		</sr-drop><!--
		 --><sr-input onethird=""><input name="Right Spike Width" viewchild="layer_extra" r="spike.rw" replace="true" livevisup="true" number="true" min="0" max="100" suffix="%" validate="true" type="text" lastsuffix="%"><span class="sr--form--otitle" noicon=""><?php _e('Width','revslider'); ?></span></sr-input>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>		
</sr-separator>