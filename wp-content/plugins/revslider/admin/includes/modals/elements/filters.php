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
		<sr-separator-title><?php _e('Color Effect Modes','revslider'); ?></sr-separator-title>
	</sr-separator-head>
	<sr-separator-body>
		<sr-drop half r="blend" data-type="search" data-source="blends" keepotitle="" data-v="" dropsh="280" dropsw="350" viewchild="layer_extra" class="sr--mr--10">
			<sr-drop-view style="overflow:hidden;">
				<span class="sr--drop--value" style="text-overflow: ellipsis;max-width: 75px;white-space: nowrap;overflow: hidden;vertical-align: top;"></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('Blend','revslider');?></span>
			</sr-drop-view>
		</sr-drop><!--
		--><sr-drop half r="mF" data-type="search" data-source="filters"  keepotitle="" data-onchange="editor.elements.filter.update" data-undoredo="editor.elements.filter.update" data-undoredoparams="undoredo" data-v="" dropsh="280" dropsw="350" viewchild="layer_extra">
			<sr-drop-view style="overflow:hidden;">
				<span class="sr--drop--value" style="text-overflow: ellipsis;max-width: 75px;white-space: nowrap;overflow: hidden;vertical-align: top;"></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('Filter','revslider');?></span>
			</sr-drop-view>
		</sr-drop>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Liquid Glass Effect','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_glass" r="glass.u" data-onchange="editor.elements.glass.update" viewchild="layer_extra"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_glass">
		<sr-input half class="sr--mr--10">
			<input name="Blur" replace r="glass.blur" viewchild="layer_extra" type="text" number="true" min="0" max="40" suffix="px" lastsuffix="px" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Blur','revslider'); ?></span>
		</sr-input><!--
		--><sr-input half>
			<input name="Intensity" replace r="glass.intensity" viewchild="layer_extra" type="text" number="true" min="0" max="100" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Intensity','revslider'); ?></span>
		</sr-input>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Progressive Blur','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_pblur" r="pblur.u" data-onchange="editor.elements.pblur.update" viewchild="layer_extra"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_pblur">
		<sr-drop wide r="pblur.dir" data-type="search" data-source="pblurdirs" keepotitle="" data-onchange="editor.elements.pblur.update" data-v="" dropsh="280" dropsw="350" viewchild="layer_extra">
			<sr-drop-view style="overflow:hidden;">
				<span class="sr--drop--value" style="text-overflow: ellipsis;max-width: 160px;white-space: nowrap;overflow: hidden;vertical-align: top;"></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('Direction','revslider');?></span>
			</sr-drop-view>
		</sr-drop>
		<sr-sp h="8"></sr-sp>
		<sr-input onethird class="sr--mr--7">
			<input name="Max Blur" replace r="pblur.max" viewchild="layer_extra" type="text" number="true" min="0" max="60" suffix="px" lastsuffix="px" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Blur','revslider'); ?></span>
		</sr-input><!--
		--><sr-input onethird class="sr--mr--7">
			<input name="Fade" replace r="pblur.fade" viewchild="layer_extra" type="text" number="true" min="1" max="100" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Fade','revslider'); ?></span>
		</sr-input><!--
		--><sr-input onethird>
			<input name="Tint" replace r="pblur.tint" viewchild="layer_extra" type="text" number="true" min="0" max="80" suffix="%" lastsuffix="%" livevisup dragnumber autocomplete="off" validate="true">
			<span noicon="" class="sr--form--otitle"><?php _e('Tint','revslider'); ?></span>
		</sr-input>
		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>
