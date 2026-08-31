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
		<sr-separator-title><?php _e('Audio Content','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>
		<sr-input style="width:230px;margin-right:2px;"><input name="Source" style="padding-right:50px" r="content.src" data-wave="sr_audio_wave" replace shortfilename data-onchange="editor.elements.audio.updateSrc" data-undoredo="editor.elements.audio.updateSrc" viewchild="layer_basics" type="text" validate validatecall="isValidHTML5Path"><span noicon="" class="sr--form--otitle"><?php _e('MPEG','revslider'); ?></span></sr-input><!--
		--><sr-button data-action="B.vidPick.wpaudio" data-aparams="editor.elements.audio.updateSrc" data-wave="sr_audio_wave" class="sr--sh--icon sr--mr--1 sr--video--picker"><svg  width="16" height="16" transform="translate(0, 4)"><use xlink:href="#WPIcon"></use></svg></sr-button><!--
		--><sr-button data-action="B.vidPick.sr"  data-aparams="editor.elements.audio.updateSrc" data-wave="sr_audio_wave" class="sr--sh--icon sr--mr--1 sr--video--picker"><svg  width="16" height="16" transform="translate(0, 4)"><use xlink:href="#SRIcon"></use></svg></sr-button>
		<sr-drop r="content.preload" viewchild="layer_basics" half dropsw="200" class="sr--mr--10">
			<sr-drop-view>
				<span class="sr--drop--value"></span>
				<span class="sr--form--otitle"><?php _e('Preload','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>			
			<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
			<sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops>			
			<sr-drops data-v="metadata"><?php _e('Meta Data','revslider'); ?></sr-drops>				
		</sr-drop><!--
	--><sr-drop r="content.controls" data-onchange="editor.elements.audio.updateControls" data-undoredo="editor.elements.audio.updateControls" viewchild="layer_basics" half dropsw="200" class="sr--mr--0">
		<sr-drop-view>
				<span class="sr--drop--value"></span>
				<span class="sr--form--otitle"><?php _e('Controls','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>			
			<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
			<sr-drops data-v="s"><?php _e('HTML5','revslider'); ?></sr-drops>			
		</sr-drop>
		<sr-drop r="content.autoPlay" viewchild="layer_basics" data-onchange="editor.elements.audio.update" data-undoredo="editor.elements.audio.update" wide dropsw="200" class="sr--mr--0">
			<sr-drop-view>
				<span class="sr--drop--value"></span>
				<span class="sr--form--otitle"><?php _e('Autoplay','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>			
			<sr-drops data-v="false"><?php _e('On Interaction','revslider'); ?></sr-drops>
			<sr-drops data-v="true"><?php _e('Every time','revslider'); ?></sr-drops>
			<sr-drops data-v="1sttime"><?php _e('1st Time ','revslider'); ?></sr-drops>			
			<sr-drops data-v="no1sttime"><?php _e('Skip 1st Time','revslider'); ?></sr-drops>				
		</sr-drop>	
					
		<sr-input half class="sr--mr--10">
			<input id="sr_audio_wave_start" r="content.start" replace viewchild="layer_basics" type="text" data-onchange="editor.elements.audio.update" data-undoredo="editor.elements.audio.update" validate videotimer updateformat="isValidVideoTime"><span noicon="" class="sr--form--otitle"><?php _e('From','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
				<sr-drops data-v="00:00">Start</sr-drops>
				<sr-drops data-v="00:30">30 Sec</sr-drops>
				<sr-drops data-v="02:30">01:30</sr-drops>
				<sr-drops data-v="01:30:30">01:30:30</sr-drops>
			</sr-drop> 
		</sr-input><!--
	--><sr-input half class="sr--mr--0">
			<input id="sr_audio_wave_end" r="content.end" replace viewchild="layer_basics" type="text" data-onchange="editor.elements.audio.update" data-undoredo="editor.elements.audio.update" validate videotimer updateformat="isValidVideoTime"><span noicon="" class="sr--form--otitle"><?php _e('Till','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
				<sr-drops data-v="00:00">End</sr-drops>
				<sr-drops data-v="00:45">45 Sec</sr-drops>
				<sr-drops data-v="03:15">03:15</sr-drops>
				<sr-drops data-v="01:45:30">01:45:30</sr-drops>
			</sr-drop> 
		</sr-input>		
		<sr-wave id="sr_audio_wave" wide data-target="#sr_audio_wave_canvas" viewchild="layer_basics" class="sr--mb--10" data-starttarget="#sr_audio_wave_end" data-endtarget="#sr_audio_wave_end" ><sr-wavesurfer id="sr_audio_wave_canvas"></sr-wavesurfer></sr-wave>		
		<sr-input half class="sr--mr--10">
			<input name="Volume" r="content.volume" replace viewchild="layer_basics" livevisup autocomplete="off" data-onchange="editor.elements.audio.volume" data-undoredo="editor.elements.audio.volume" type="text" validate number="true" min="0" max="100" step="1"><span noicon="" class="sr--form--otitle"><?php _e('Default Volume','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
				<sr-drops data-v="0">0</sr-drops>
				<sr-drops data-v="50">50</sr-drops>
				<sr-drops data-v="100">100</sr-drops>
			</sr-drop> 
		</sr-input><!--
		--><sr-input half class="sr--mr--0">
			<input name="Preload" r="content.preloadWait" replace viewchild="layer_basics" livevisup autocomplete="off" type="text" suffix="sec" lastsuffix="sec" validate number="true" min="0" max="100" step="1"><span noicon="" class="sr--form--otitle"><?php _e('Skip Preload','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
				<sr-drops data-v="0 sec">0 sec</sr-drops>
				<sr-drops data-v="1 sec">1 sec</sr-drops>
				<sr-drops data-v="5 sec">5 sec</sr-drops>
			</sr-drop> 
		</sr-input>		
						
		<sr-separator mini="" class="collapsed">
			<sr-separator-head>
				<sr-separator-title><?php _e('PlayBack Settings','revslider'); ?></sr-separator-title>
				<sr-separator-toggle><svg class="sr--icon" width="15" height="9"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
			</sr-separator-head>
			<sr-separator-body>									
			<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="content.stopAllMedia" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Stop Other Media','revslider'); ?></span></sr-wrap>
			<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="content.pauseTimer" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Pause Module Timer','revslider'); ?></span></sr-wrap>
			<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="content.rewind" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Rewind on Slide Start','revslider'); ?></span></sr-wrap>
			<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="content.loop" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Restart on End (Loop)','revslider'); ?></span></sr-wrap>
			<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="content.nextSlide" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Next Slide When Ends','revslider'); ?></span></sr-wrap>
			<sr-sh r="#MODULE#.type" data-shdep="carousel" viewchild="layer_basics"> 
				<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="content.cOC" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Keep when Slide Unfocused','revslider'); ?></span></sr-wrap>
			<!--<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="content.pauseOnSwap" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span>Pause when Swap</span></sr-wrap>-->
			</sr-sh>
			<sr-sp h="20"></sr-sp>
			</sr-separator-body>
		</sr-separator>
		
				
		<sr-sp h="15"></sr-sp>		
	</sr-separator-body>
</sr-separator>
