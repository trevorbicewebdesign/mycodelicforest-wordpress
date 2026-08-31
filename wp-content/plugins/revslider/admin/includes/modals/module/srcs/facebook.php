<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
<sr-wrap view="module_source_fb" viewchild="module_source"  id="sr_mosrc_fb" data-type="facebook" class="sr_module_srcs sr--force--hide">                            
    <sr-separator>    
        <sr-separator-body>        
            <sr-input wide textblock class="sr--mb--5"><textarea name="FaceBook Token" data-onchange="B.faceBook.reset" style="height:180px;margin-bottom:0px" r="source.facebook.appId" viewchild="module_source_fb"></textarea><span noicon="" style="bottom:10px" class="sr--form--otitle"><?php _e('Enter Token or','revslider'); ?> <a class="sr--link--inner-text" data-action="B.faceBook.getToken"><?php _e('Connect Here','revslider'); ?></a></span></sr-input>
            <sr-input wide class="sr--mb--15"><input name="Connected With" style="pointer-events:none" disabled r="source.facebook.connect_with" viewchild="module_source_fb" validate type="text"><span noicon="" class="sr--form--otitle"><?php _e('Connected With','revslider'); ?></span></sr-input>            
            <sr-wrap basic>
                    <sr-input half class="sr--mb--10 sr--mr--10"><input name="Amount of Slides" data-onchange="B.faceBook.reset" r="source.facebook.count" viewchild="module_source_fb" validate type="text" number="true" min="0" max="25" default="8"><span noicon="" class="sr--form--otitle"><?php _e('Amount of Slides','revslider'); ?></span></sr-input><!--
                --><sr-input half class="sr--mb--10"><input name="Cache (sec)" data-onchange="B.faceBook.reset" r="source.facebook.transient" viewchild="module_source_fb" validate type="text" number="true" min="0" max="50000" default="1200"><span noicon="" class="sr--form--otitle"><?php _e('Cache (sec).','revslider'); ?></span></sr-input>
            </sr-wrap>
            <sr-sp h="10"></sr-sp>
        </sr-separator-body>
    </sr-separator>
    <sr-separator noborder>
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('Facebook Source','revslider'); ?></sr-separator-title>                    
        </sr-separator-head>
        <sr-separator-body>
            <sr-input wide class="sr--mb--15"><input name="Page Id" data-onchange="B.faceBook.reset"  r="source.facebook.page_id" viewchild="module_source_fb" validate type="text"><span noicon="" class="sr--form--otitle"><?php _e('Page Id','revslider'); ?></span></sr-input>
            <sr-drop wide  data-v="" r="source.facebook.typeSource" viewchild="module_source_fb" dropsw="300" data-sh="#sr_module_src_facebooksrcs>sr-wrap" data-shdep="#eqvalue">
                <sr-drop-view>
                    <span class="sr--drop--value"></span>
                    <span class="sr--form--otitle"><?php _e('Stream Source','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>   
                <sr-drops data-v="album"><?php _e('Album','revslider'); ?></sr-drops>
                <sr-drops data-v="timeline"><?php _e('TimeLine','revslider'); ?></sr-drops>            
            </sr-drop>
            <sr-wrap id="sr_module_src_facebooksrcs">            
                <sr-wrap value="album">
                    <sr-drop wide multiselect usecheck keepotitle data-type="search" data-v="" r="source.facebook.album" data-source="facebookalbums" data-appid="source.facebook.appId" data-pageid="source.facebook.page_id" data-countsrc="source.facebook.count" viewchild="module_source_fb" dropsw="300" dropsh="300">
                        <sr-drop-view>
                            <span class="sr--drop--value" style="padding-right:50px"></span>
                            <span class="sr--form--otitle"><?php _e('Album','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                    </sr-drop>
                </sr-wrap>
            </sr-wrap>
            <sr-sp h="5"></sr-sp>
        </sr-separator-body>
    </sr-separator>
</sr-wrap>