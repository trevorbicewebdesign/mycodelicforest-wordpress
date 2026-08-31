<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-wrap-dep dep="is[group]">	
			<sr-separator keepborder>
				<sr-separator-head notoggle>
					<sr-separator-title><?php _e('Content Behavior','revslider'); ?></sr-separator-title>					
				</sr-separator-head>
				<sr-separator-body>			
					<sr-wrap half basic inline>
						<sr-radio viewchild="layer_basics" responsive r="tA.#LEV#">
								<sr-radio-item value="left" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Left"></use></svg></sr-radio-item><!--
							--><sr-radio-item value="center" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Center"></use></svg></sr-radio-item><!--
							--><sr-radio-item value="right" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Right"></use></svg></sr-radio-item><!--
							--><sr-radio-item value="justify" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Justify"></use></svg></sr-radio-item>
						</sr-radio>
					</sr-wrap><!--
					--><sr-wrap basic="" class="sr--form--grp" half><sr-onoff r="fHOF" responsive viewchild="layer_basics" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Force Mask','revslider'); ?></span></sr-wrap>
					<sr-sp h="12"></sr-sp>
				</sr-separator-body>
			</sr-separator>				
		</sr-wrap-dep>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Layout Behavior','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>		
		<sr-wrap-dep dep="notin[root]">
			<sr-tabs-wrap viewchild="layer_basics"  r="pos.pos">
				<sr-tab left half  data-sh=".sr_layer_rel_settings" data-shdep="#eqvalue" onchange="editor.elements.resetPos,editor.elements.panel.dependencyForce+50" onchangeparams="both" data-v="relative"><?php _e('Relative','revslider'); ?></sr-tab>
				<sr-tab right half data-sh=".sr_layer_rel_settings" data-shdep="#eqvalue" onchange="editor.elements.resetPos,editor.elements.panel.dependencyForce+50" onchangeparams="both" data-v="absolute"><?php _e('Absolute','revslider'); ?></sr-tab>
			</sr-tabs-wrap>
			<sr-sp h="15"></sr-sp>
			<sr-wrap basic class="sr_layer_rel_settings" value="relative">
				<sr-tabs-wrap viewchild="layer_basics"  r="display.#LEV#">
					<sr-tab left half  data-sh=".sr_layer_block_settings" data-shdep="#eqvalue" data-v="block"><?php _e('Block','revslider'); ?></sr-tab>
					<sr-tab right half data-sh=".sr_layer_block_settings" data-shdep="#eqvalue" data-v="inline-block"><?php _e('Inline Block','revslider'); ?></sr-tab>
				</sr-tabs-wrap>
				<sr-wrap class="sr_layer_block_settings" value="inline-block">
					<sr-sp h="15"></sr-sp>
					<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="lbreak.#LEV#" responsive viewchild="layer_basics" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Line Break After','revslider'); ?></span></sr-wrap>
				</sr-wrap>
				<sr-sp h="30"></sr-sp>								
				<sr-wrap basic>
					<sr-drop half r="pos.float.#LEV#" responsive class="sr--mr--10" viewchild="layer_basics" data-v="">
						<sr-drop-view>
							<span class="sr--drop--value">None</span>
							<span class="sr--form--otitle"><?php _e('Float','revslider'); ?></span>
							<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
						</sr-drop-view>
						<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
						<sr-drops data-v="left"><?php _e('Left','revslider'); ?></sr-drops>                        
						<sr-drops data-v="right"><?php _e('Right','revslider'); ?></sr-drops>                        
					</sr-drop><!--
					--><sr-drop half r="pos.clear.#LEV#" responsive viewchild="layer_basics" data-v="" data-onchange="editor.elements.redraw">
						<sr-drop-view>
							<span class="sr--drop--value">None</span>
							<span class="sr--form--otitle"><?php _e('Clear','revslider'); ?></span>
							<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
						</sr-drop-view>
						<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
						<sr-drops data-v="left"><?php _e('Left','revslider'); ?></sr-drops>                        
						<sr-drops data-v="right"><?php _e('Right','revslider'); ?></sr-drops>                        
						<sr-drops data-v="both"><?php _e('Both','revslider'); ?></sr-drops>                        
					</sr-drop>
				</sr-wrap> 
			</sr-wrap>			
		</sr-wrap-dep><!--		
	--><sr-wrap-dep dep="not[row,column]" class="sr--mb--5">
			<sr-drop wide r="tag" viewchild="layer_basics" data-v="">
				<sr-drop-view>
					<span class="sr--drop--value">None</span>
					<span class="sr--form--otitle"><?php _e('HTML Tag','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="sr7-layer"><?php _e('&lt;sr7-layer&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="div"><?php _e('&lt;div&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="p"><?php _e('&lt;p&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h1"><?php _e('&lt;h1&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h2"><?php _e('&lt;h2&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h3"><?php _e('&lt;h3&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h4"><?php _e('&lt;h4&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h5"><?php _e('&lt;h5&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="h6"><?php _e('&lt;h6&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="span"><?php _e('&lt;span&gt;','revslider'); ?></sr-drops>
				<sr-drops data-v="label"><?php _e('&lt;label&gt;','revslider'); ?></sr-drops>
			</sr-drop>
		</sr-wrap-dep>		
	</sr-separator-body>		
</sr-separator>
