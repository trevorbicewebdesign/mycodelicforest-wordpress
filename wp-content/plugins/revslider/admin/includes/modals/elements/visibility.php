<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
	<sr-separator noborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Visibility','revslider'); ?></sr-separator-title>			
		</sr-separator-head>
		<sr-separator-body>
			<sr-wrap inline basic>
				<sr-combo r="vis.0" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mr--5 sr--mb--5">
					<sr-combo-cnt class="sr--mr--9"><?php _e('Wide Screen','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo><!--
				--><sr-combo r="vis.1" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mr--5 sr--mb--5">
					<sr-combo-cnt class="sr--mr--9"><?php _e('Desktop','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo><!--
				--><sr-combo r="vis.2" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mr--5 sr--mb--5">
				<sr-combo-cnt class="sr--mr--9"><?php _e('Notebook','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo><!--
				--><sr-combo r="vis.3" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mr--5 sr--mb--5">
				<sr-combo-cnt class="sr--mr--9"><?php _e('Tablet','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo><!--
				--><sr-combo r="vis.4" data-onchange="editor.elements.updateEditables+50" viewchild="layer_visibility" class="sr--check--parent sr--mb--5">
				<sr-combo-cnt class="sr--mr--9"><?php _e('Mobile','revslider'); ?></sr-combo-cnt>					
					<sr-check parent="" class=""></sr-check>
				</sr-combo>
			</sr-wrap>
			<sr-sp h="10"></sr-sp>
			<sr-wrap basic class="sr--form--grp"><sr-onoff id="sr_radio_viSH" r="viSH" data-onchange="B.onoff.turnoff" data-onchangeparams="viOC" viewchild="layer_visibility" class="sr--mr--10"></sr-onoff><span><?php _e('Visible if Module Hovered','revslider'); ?></span></sr-wrap>
			<sr-wrap basic class="sr--form--grp"><sr-onoff id="sr_radio_viOC" r="viOC" data-onchange="B.onoff.turnoff" data-onchangeparams="viSH" viewchild="layer_visibility" class="sr--mr--10"></sr-onoff><span><?php _e('Always Visible on Carousel','revslider'); ?></span></sr-wrap>			
			<sr-sp h="15"></sr-sp>
		</sr-separator-body>		
	</sr-separator>
	<sr-wrap-dep dep="globalslide">
		<sr-separator topborder>
			<sr-separator-head notoggle>
				<sr-separator-title><?php _e('Show Global Layer','revslider'); ?></sr-separator-title>			
			</sr-separator-head>
			<sr-separator-body>
				<sr-drop wide   r="sStart" data-source="globalss" data-sourceparams="start" viewchild="layer_visibility" ignoreredraw dropsw="290" dropsh="340">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('From Slide','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
				</sr-drop>
				<sr-drop wide   r="sEnd" data-source="globalss" data-sourceparams="end" viewchild="layer_visibility" ignoreredraw dropsw="290" dropsh="340">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Till Slide','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
				</sr-drop>
			</sr-separator-body>
		</sr-separator>
	</sr-wrap-dep>