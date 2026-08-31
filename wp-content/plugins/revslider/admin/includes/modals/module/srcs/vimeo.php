<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
<sr-wrap view="module_source_vimeo" viewchild="module_source"  id="sr_mosrc_vimeo" data-type="vimeo" class="sr_module_srcs sr--force--hide">                            
    <sr-separator>
        <sr-separator-body>                
            <sr-wrap basic>
                    <sr-input half class="sr--mb--10 sr--mr--10"><input name="Amount of Slides" r="source.vimeo.count" viewchild="module_source_vimeo" validate type="text" number="true" min="0" max="25" default="8"><span noicon="" class="sr--form--otitle"><?php _e('Amount of Slides','revslider'); ?></span></sr-input><!--
                --><sr-input half class="sr--mb--10"><input name="Cache (sec)" r="source.vimeo.transient" viewchild="module_source_vimeo" validate type="text" number="true" min="0" max="50000" default="1200"><span noicon="" class="sr--form--otitle"><?php _e('Cache (sec).','revslider'); ?></span></sr-input>
            </sr-wrap>
            <sr-sp h="10"></sr-sp>
        </sr-separator-body>        
    </sr-separator>
    <sr-separator noborder>
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('Vimeo Source','revslider'); ?></sr-separator-title><sr-tooltip key="vimeotip"></sr-tooltip>
        </sr-separator-head>
        <sr-separator-body>    
            <sr-drop wide  data-v="" r="source.vimeo.typeSource" viewchild="module_source_vimeo" dropsw="300" data-sh="#sr_module_src_vimeosrcs>sr-wrap" data-shdep="#eqvalue">
                <sr-drop-view>
                    <span class="sr--drop--value"></span>
                    <span class="sr--form--otitle"><?php _e('Stream Source','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>   
                <sr-drops data-v="user"><?php _e('User','revslider'); ?></sr-drops>
                <sr-drops data-v="album"><?php _e('Showcase','revslider'); ?></sr-drops>            
                <sr-drops data-v="group"><?php _e('Group','revslider'); ?></sr-drops>            
                <sr-drops data-v="channel"><?php _e('Channel','revslider'); ?></sr-drops>            
            </sr-drop>
            <sr-wrap id="sr_module_src_vimeosrcs">            
                <sr-wrap value="user"><sr-input wide class="sr--mb--10"><input name="User Name" r="source.vimeo.userName" viewchild="module_source_vimeo" validate type="text" number="true" min="0" max="25" default="8"><span noicon="" class="sr--form--otitle"><?php _e('User Name','revslider'); ?></span></sr-input></sr-wrap>
                <sr-wrap value="album"><sr-input wide class="sr--mb--10"><input name="ShowCase Id" r="source.vimeo.albumId" viewchild="module_source_vimeo" validate type="text" number="true" min="0" max="25" default="8"><span noicon="" class="sr--form--otitle"><?php _e('ShowCase Id','revslider'); ?></span></sr-input></sr-wrap>
                <sr-wrap value="group"><sr-input wide class="sr--mb--10"><input name="Group Name" r="source.vimeo.groupName" viewchild="module_source_vimeo" validate type="text" number="true" min="0" max="25" default="8"><span noicon="" class="sr--form--otitle"><?php _e('Group Name','revslider'); ?></span></sr-input></sr-wrap>
                <sr-wrap value="channel"><sr-input wide class="sr--mb--10"><input name="Channel Name" r="source.vimeo.channelName" viewchild="module_source_vimeo" validate type="text" number="true" min="0" max="25" default="8"><span noicon="" class="sr--form--otitle"><?php _e('Channel Name','revslider'); ?></span></sr-input></sr-wrap>
            </sr-wrap>            
            <sr-sp h="10"></sr-sp>
        </sr-separator-body>
    </sr-separator>
</sr-wrap>