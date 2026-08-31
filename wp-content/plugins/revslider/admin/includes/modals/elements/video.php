<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-separator keepborder class="sr_layer_bgtypes sr_updateForcehide" value="video">
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Video Content','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>			
		<sr-tabs-wrap r="bg.video.type" viewchild="layer_basics" class="sr--mb--20">
			<sr-tab left onethird data-v="html5" data-sh=".sr_media_settings_html5" data-hide=".sr_media_settings_youtube,.sr_media_settings_vimeo, .sr_media_settings_nothtml5"  onchange="editor.elements.video.resetsrc" data-undoredo="editor.elements.video.resetsrc" ><?php _e('HTML5','revslider'); ?></sr-tab>
			<sr-tab none onethird data-sh=".sr_media_settings_youtube, .sr_media_settings_nothtml5" data-hide=".sr_media_settings_html5,.sr_media_settings_vimeo" data-v="youtube" onchange="editor.elements.video.resetsrc" data-undoredo="editor.elements.video.resetsrc" ><?php _e('YouTube','revslider'); ?></sr-tab>
			<sr-tab right onethird data-v="vimeo" data-sh=".sr_media_settings_vimeo, .sr_media_settings_nothtml5" data-hide=".sr_media_settings_html5,.sr_media_settings_youtube" onchange="editor.elements.video.resetsrc" data-undoredo="editor.elements.video.resetsrc" ><?php _e('Vimeo','revslider'); ?></sr-tab>					
		</sr-tabs-wrap>						
		<sr-wrap inline basic class="sr_media_settings_html5">
			<sr-input style="width:230px;margin-right:2px;"><input name="Video Source" style="padding-right:50px" r="bg.video.src" replace shortfilename data-onupdate="editor.elements.video.updateSrc" data-undoredo="editor.elements.video.updateSrc" viewchild="layer_basics" type="text" validate validatecall="isValidHTML5Path"><span noicon="" class="sr--form--otitle"><?php _e('MPEG','revslider'); ?></span></sr-input><!--
			--><sr-button data-action="B.vidPick.wp"  data-aparams="editor.elements.video.updateSrc" class="sr--sh--icon sr--mr--1 sr--video--picker"><svg class="sr--icon" width="16" height="16" transform="translate(0, -1)"><use xlink:href="#WPIcon"></use></svg></sr-button><!--
			--><sr-button data-action="B.vidPick.sr"  data-aparams="editor.elements.video.updateSrc" class="sr--sh--icon sr--mr--1 sr--video--picker"><svg class="sr--icon" width="16" height="16" transform="translate(0, -1)"><use xlink:href="#SRIcon"></use></svg></sr-button>			
		</sr-wrap>
		<sr-wrap basic class="sr_media_settings_youtube">
			<sr-input wide><input name="YouTube Id" style="padding-right:80px" r="bg.video.src" replace data-onupdate="editor.elements.video.updateSrc" data-undoredo="editor.elements.video.updateSrc" viewchild="layer_basics" type="text" validate validatecall="isValidYouTubeId"><span noicon="" class="sr--form--otitle"><?php _e('YouTube ID','revslider'); ?></span></sr-input><!--
			--><sr-wrap-dep wide dep="fromstream" basic="" class="sr--form--grp sr--mt--0 sr--mb--15"><sr-onoff r="bg.video.fromStream" viewchild="layer_basics"  class="sr--mr--10"></sr-onoff><span><?php _e('Prefer Feed Video','revslider'); ?></span></sr-wrap-dep>
		</sr-wrap>
		<sr-wrap class="sr_media_settings_vimeo">
			<sr-input wide><input name="Vimeo Id" style="padding-right:80px"  r="bg.video.src" replace  data-onupdate="editor.elements.video.updateSrc" data-undoredo="editor.elements.video.updateSrc" viewchild="layer_basics" type="text" validate validatecall="isValidVimeoId"><span noicon="" class="sr--form--otitle"><?php _e('Vimeo ID','revslider'); ?></span></sr-input><!--
			--><sr-wrap-dep wide dep="fromstream" basic="" class="sr--form--grp sr--mt--5 sr--mb--20"><sr-onoff r="bg.video.fromStream" viewchild="layer_basics"  class="sr--mr--10"></sr-onoff><span><?php _e('Prefer Feed Video','revslider'); ?></span></sr-wrap-dep>	
		</sr-wrap>
		<sr-wrap-dep dep="not[slidebg]">
			<sr-wrap basic class="sr_media_settings_html5">
				<sr-drop r="bg.video.preload" viewchild="layer_basics" wide dropsw="200" class="sr--mr--0">
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Preload','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>			
					<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
					<sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops>			
					<sr-drops data-v="metadata"><?php _e('Meta Data','revslider'); ?></sr-drops>				
				</sr-drop>				
			</sr-wrap>
			<sr-drop r="bg.video.autoPlay" viewchild="layer_basics" data-onchange="editor.elements.video.update" data-undoredo="editor.elements.video.update" wide dropsw="200" class="sr--mr--0">
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
			<sr-drop r="bg.video.cover" class="sr--mr--10" viewchild="layer_basics" data-onchange="editor.elements.video.resetsrc"  data-undoredo="editor.elements.video.update"  half dropsw="200">
				<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Fit','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>			
					<sr-drops data-v="false"><?php _e('Contain','revslider'); ?></sr-drops>
					<sr-drops data-v="true"><?php _e('Cover','revslider'); ?></sr-drops>
			</sr-drop><!--
		--><sr-drop r="bg.video.ratio" viewchild="layer_basics" data-onchange="editor.elements.video.resetsrc" data-undoredo="editor.elements.video.update" half dropsw="150" class="sr--mr--0" list="16:9,4:3,1.85:1,2.39:1">
			<sr-drop-view>
				<span class="sr--drop--value"></span>				
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			</sr-drop>
		</sr-wrap-dep>				
		<sr-input half class="sr--mr--10">
			<input name="Video Start" r="bg.video.start" replace viewchild="layer_basics" type="text" data-onchange="editor.elements.video.update" data-undoredo="editor.elements.video.update" validate videotimer updateformat="isValidVideoTime"><span noicon="" class="sr--form--otitle"><?php _e('From','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
				<sr-drops data-v="00:00">Start</sr-drops>
				<sr-drops data-v="00:30">30 Sec</sr-drops>
				<sr-drops data-v="02:30">01:30</sr-drops>
				<sr-drops data-v="01:30:30">01:30:30</sr-drops>
			</sr-drop> 
		</sr-input><!--
	--><sr-input half class="sr--mr--0">
			<input name="Video End" r="bg.video.end" replace viewchild="layer_basics" type="text" data-onchange="editor.elements.video.update" data-undoredo="editor.elements.video.update" validate videotimer updateformat="isValidVideoTime"><span noicon="" class="sr--form--otitle"><?php _e('Till','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
				<sr-drops data-v="00:00">End</sr-drops>
				<sr-drops data-v="00:45">45 Sec</sr-drops>
				<sr-drops data-v="03:15">03:15</sr-drops>
				<sr-drops data-v="01:45:30">01:45:30</sr-drops>
			</sr-drop> 
		</sr-input>	
		<sr-wrap-dep dep="not[slidebg]">			
			<sr-input half class="sr--mr--10">
				<input name="Default Volume" r="bg.video.volume" replace viewchild="layer_basics" livevisup autocomplete="off" data-onchange="editor.elements.video.volume" data-undoredo="editor.elements.video.volume" type="text" validate number="true" min="0" max="100" step="1"><span noicon="" class="sr--form--otitle"><?php _e('Default Volume','revslider'); ?></span>
				<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
					<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
					<sr-drops data-v="0">0</sr-drops>
					<sr-drops data-v="50">50</sr-drops>
					<sr-drops data-v="100">100</sr-drops>
				</sr-drop> 
			</sr-input><!--
			--><sr-drop r="bg.video.speed" viewchild="layer_basics" half data-onchange="editor.elements.video.update" data-undoredo="editor.elements.video.update" dropsw="200" class="sr--mr--0 sr_media_settings_youtube">
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('Speed','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>			
				<sr-drops data-v="0.25"><?php _e('1/4','revslider'); ?></sr-drops>
				<sr-drops data-v="0.5"><?php _e('1/2','revslider'); ?></sr-drops>
				<sr-drops data-v="1"><?php _e('Normal','revslider'); ?></sr-drops>
				<sr-drops data-v="1.5"><?php _e('1.5x','revslider'); ?></sr-drops>
				<sr-drops data-v="2.0"><?php _e('2x','revslider'); ?></sr-drops>
			</sr-drop>
		</sr-wrap-dep>
		<sr-sp h="3"></sr-sp>
		<sr-separator mini="" class="collapsed">
			<sr-separator-head>
				<sr-separator-title><?php _e('Video Poster','revslider'); ?></sr-separator-title>
				<sr-separator-toggle><svg class="sr--icon" width="15" height="9"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>				
			</sr-separator-head>
			<sr-separator-body>
			<sr-wrap id="sr_layer_videoposter_image" class="sr_image_selector">				
				<sr-wrap>
					<sr-wrap inline class="sr--mr--10">
						<sr-img-src>
							<sr-bg-img r="bg.video.poster.src" style="background-size:cover" viewchild="layer_basics" data-onchange="editor.elements.video.poster.update">
								<svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
								<sr--bg--picker-wrap style="width:94px; height:22px">
									<svg data-action="B.imgPick.wp" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
									<svg data-action="B.imgPick.sr" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>
									<svg data-action="B.imgPick.th" data-aparams="layerbgvideo" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#Submenu_Thumbnail"></use></svg>
								</sr--bg--picker-wrap>
								<svg data-action="B.imgPick.clear" viewchild="layer_basics" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
							</sr-bg-img>						
						</sr-img-src>
					</sr-wrap><!--
				--><sr-wrap half style="margin-top:-8px">
						<sr-wrap basic="" class="sr--form--grp" style="white-space:nowrap"><sr-onoff r="bg.video.poster.showOnPause" data-onchange="editor.elements.video.poster.pause" data-undoredo="editor.elements.video.poster.pause" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('If Video Paused','revslider'); ?></span></sr-wrap>
						<sr-wrap basic="" class="sr--form--grp" style="white-space:nowrap"><sr-onoff r="bg.video.poster.noOnMobile" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Hide on Mobile','revslider'); ?></span></sr-wrap>
						<sr-wrap basic="" class="sr--form--grp" style="white-space:nowrap"><sr-onoff r="bg.video.poster.insteadVideo" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Keep on Mobile','revslider'); ?></span></sr-wrap>
					</sr-wrap>
				</sr-wrap>		
				<sr-wrap-dep wide dep="fromstream" basic="" class="sr--form--grp sr--mt--15"><sr-onoff r="bg.video.poster.fromStream" viewchild="layer_basics"  class="sr--mr--10"></sr-onoff><span><?php _e('Prefer Feed Cover','revslider'); ?></span></sr-wrap-dep>	
				<sr-wrap class="sr_media_settings_youtube">
					<sr-sp h="15"></sr-sp>
					<sr-input wide class="sr--mr--10">
						<input name="Show/Hide Duration" r="bg.video.animDur" replace viewchild="layer_basics" type="text" min="0" max="10000" suffix="ms" validate><span noicon="" class="sr--form--otitle"><?php _e('Show/Hide Duration','revslider'); ?></span>
						<sr-drop class="sr--drop--only--icon" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">            
							<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>            
							<sr-drops data-v="0">0</sr-drops>
							<sr-drops data-v="250">250</sr-drops>
							<sr-drops data-v="500">500</sr-drops>							
							<sr-drops data-v="1000">1000</sr-drops>							
						</sr-drop> 
					</sr-input>
				</sr-wrap>
				<sr-sp h="11"></sr-sp>
			</sr-wrap>				
			</sr-separator-body>
		</sr-separator>						
		<sr-separator mini="" class="collapsed">
			<sr-separator-head >
				<sr-separator-title><?php _e('PlayBack Settings','revslider'); ?></sr-separator-title>
				<sr-separator-toggle><svg class="sr--icon" width="15" height="9"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>				
			</sr-separator-head>
			<sr-separator-body style="margin-top:-3px">				
				<sr-wrap-dep dep="not[slidebg]" basic="" style="line-height:28px" class="sr--form--grp" style="white-space:nowrap"><sr-onoff r="bg.video.aFullScreen" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Allow Full Screen','revslider'); ?></span></sr-wrap-dep>
				<sr-wrap-dep dep="not[slidebg]" basic="" style="line-height:28px" basic="" class="sr--form--grp" style="white-space:nowrap"><sr-onoff r="bg.video.inline" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Use Inline Mode','revslider'); ?></span></sr-wrap-dep>				
				<sr-wrap-dep dep="not[slidebg]" basic="" style="line-height:28px" wide class="sr--form--grp sr--mr--10"><sr-onoff r="bg.video.mute" viewchild="layer_basics" data-onchange="editor.elements.video.mute" data-undoredo="editor.elements.video.mute" class="sr--mr--10"></sr-onoff><span><?php _e('Start Media Muted','revslider'); ?></span></sr-wrap-dep>
				<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="bg.video.stopAllMedia" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Stop Other Media','revslider'); ?></span></sr-wrap>
				<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="bg.video.pauseTimer" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Pause Module Timer','revslider'); ?></span></sr-wrap>		
				<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="bg.video.rewind" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Rewind on Slide Start','revslider'); ?></span></sr-wrap>		
				<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="bg.video.loop" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Restart on End (Loop)','revslider'); ?></span></sr-wrap>
				<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="bg.video.nextSlide" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Next Slide When Ends','revslider'); ?></span></sr-wrap>				
				<sr-sh r="#MODULE#.type" data-shdep="carousel" viewchild="layer_basics"> 
					<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="bg.video.cOC" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span><?php _e('Keep when Slide Unfocused','revslider'); ?></span></sr-wrap>
				<!--<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="bg.video.pauseOnSwap" viewchild="layer_basics" class="sr--mr--10"></sr-onoff><span>Pause when Swap</span></sr-wrap>-->
				</sr-sh>
				<sr-sp h="10"></sr-sp>
			</sr-separator-body>
		</sr-separator>
		<sr-wrap-dep dep="not[slidebg]">
			<sr-separator mini="" class="collapsed">
				<sr-separator-head >
					<sr-separator-title><?php _e('Interaction & Controls','revslider'); ?></sr-separator-title>
					<sr-separator-toggle><svg class="sr--icon" width="15" height="9"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>					
				</sr-separator-head>
				<sr-separator-body style="margin-top:-3px">
					<sr-wrap basic="" class="sr--form--grp"><sr-onoff r="bg.video.noInteract" viewchild="layer_basics" data-hide=".sr_elements_video_ctrls" class="sr--mr--10"></sr-onoff><span><?php _e('No Interaction (Frontend)','revslider'); ?></span></sr-wrap>
					<sr-wrap class="sr_elements_video_ctrls">
						<sr-sp h="10"></sr-sp>
						<sr-drop class="sr--mb--0" r="bg.video.controls" viewchild="layer_basics" wide dropsw="200">
							<sr-drop-view>
								<span class="sr--drop--value"></span>
								<span class="sr--form--otitle"><?php _e('Controls','revslider'); ?></span>
								<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
							</sr-drop-view>			
							<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
							<sr-drops data-v="s"><?php _e('Small','revslider'); ?></sr-drops>
							<sr-drops data-v="xl"><?php _e('Large','revslider'); ?></sr-drops>
							<sr-drops data-v="s+xl"><?php _e('Both','revslider'); ?></sr-drops>
						</sr-drop>
					</sr-wrap>		
					<sr-sp class="sr_media_settings_nothtml5" h="15"></sr-sp>		
				</sr-separator-body>
			</sr-separator>	
		</sr-wrap-dep>			
		<sr-separator mini="" class="collapsed sr_media_settings_nothtml5">
			<sr-separator-head >
				<sr-separator-title><?php _e('Video Arguments','revslider'); ?></sr-separator-title>
				<sr-separator-toggle><svg class="sr--icon" width="15" height="9"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>				
			</sr-separator-head>
			<sr-separator-body>
				<sr-input wide textblock class="sr--mb--0"><textarea id="sr_elements_video_args" r="bg.video.args" style="height:180px" data-onchange="editor.elements.video.args" data-undoredo="editor.elements.video.args" class="sr--mb--0" viewchild="layer_basics"></textarea></sr-input>					
				<sr-drop clean="" data-type="search"  data-onchange="editor.elements.args.select" data-target="sr_elements_video_args" data-source="args" data-source-type="yt" dropsw="500" dropsh="380" data-v="default" class="sr--cta sr--mr--0 sr--mb--0 sr_media_settings_youtube"><svg class="sr--icon" style="margin-right:4px !important" width="19.322" height="19.322" transform="translate(0, 0) rotate(-45)"><use xlink:href="#Options_Select_Meta"></use></svg>YouTube Args</sr-drop>
				<sr-drop clean="" data-type="search"  data-onchange="editor.elements.args.select" data-target="sr_elements_video_args" data-source="args" data-source-type="vimeo" dropsw="500" dropsh="380" data-v="default" class="sr--cta sr--mr--0 sr--mb--0 sr_media_settings_vimeo"><svg class="sr--icon" style="margin-right:4px !important" width="19.322" height="19.322" transform="translate(0, 0) rotate(-45)"><use xlink:href="#Options_Select_Meta"></use></svg>Vimeo Args</sr-drop>				
			</sr-separator-body>
		</sr-separator>
		<sr-sp h="20"></sr-sp>
	</sr-separator-body>
</sr-separator>
