<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
<sr-wrap view="module_source_yt" viewchild="module_source"  id="sr_mosrc_yt" data-type="youtube" class="sr_module_srcs sr--force--hide">                            
    <sr-separator>    
        <sr-separator-body>        
            <sr-input wide textblock class="sr--mb--5"><textarea name="YouTube API Key" data-onchange="B.youtube.reset" style="height:56px;margin-bottom:0px; padding-right:2px;" r="source.youtube.api" viewchild="module_source_yt"></textarea><span noicon="" style="bottom:10px" class="sr--form--otitle"><?php _e('YouTube Api Key <a class="sr--link--inner-text"  href="https://developers.google.com/youtube/v3/getting-started#before-you-start" noopener target="_blank">(Where to get)</a>','revslider'); ?></span></sr-input>
            <sr-wrap basic>
                    <sr-input half class="sr--mb--10 sr--mr--10"><input name="Amount of Slides" data-onchange="B.youtube.reset" r="source.youtube.count" viewchild="module_source_yt" validate type="text" number="true" min="0" max="25" default="8"><span noicon="" class="sr--form--otitle"><?php _e('Amount of Slides','revslider'); ?></span></sr-input><!--
                --><sr-input half class="sr--mb--10"><input name="Cache (sec)" data-onchange="B.youtube.reset" r="source.youtube.transient" viewchild="module_source_yt" validate type="text" number="true" min="0" max="50000" default="1200"><span noicon="" class="sr--form--otitle"><?php _e('Cache (sec).','revslider'); ?></span></sr-input>
            </sr-wrap>
            <sr-sp h="10"></sr-sp>
        </sr-separator-body>
    </sr-separator>
    <sr-separator>
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('YouTube Source','revslider'); ?></sr-separator-title><sr-tooltip key="youtubetip"></sr-tooltip>                    
        </sr-separator-head>
        <sr-separator-body>
            <sr-input wide class="sr--mb--15"><input name="Channel Id" data-onchange="B.youtube.reset"  r="source.youtube.channelId" viewchild="module_source_yt" validate type="text"><span noicon="" class="sr--form--otitle"><?php _e('Channel Id','revslider'); ?></span></sr-input>
            <sr-drop wide  data-v="" r="source.youtube.typeSource" viewchild="module_source_yt" dropsw="300" data-sh="#sr_module_src_youtubesrcs>sr-wrap" data-shdep="#eqvalue">
                <sr-drop-view>
                    <span class="sr--drop--value"></span>
                    <span class="sr--form--otitle"><?php _e('Stream Source','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>   
                <sr-drops data-v="channel"><?php _e('Channel','revslider'); ?></sr-drops>
                <sr-drops data-v="playlist"><?php _e('PlayList','revslider'); ?></sr-drops>            
            </sr-drop>
            <sr-wrap id="sr_module_src_youtubesrcs">            
                <sr-wrap value="playlist">
                    <sr-drop wide keepotitle data-type="search" data-v="" r="source.youtube.playlist" data-source="youtubeplaylist" data-api="source.youtube.api" data-channelid="source.youtube.channelId" viewchild="module_source_yt" dropsw="300" dropsh="300">
                        <sr-drop-view>
                            <span class="sr--drop--value" style="padding-right:50px"></span>
                            <span class="sr--form--otitle"><?php _e('Playlist','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                    </sr-drop>
                </sr-wrap>
            </sr-wrap>                        
            <sr-sp h="5"></sr-sp>
        </sr-separator-body>
    </sr-separator>        
</sr-wrap>