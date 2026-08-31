<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
<sr-wrap view="module_source_flickr" viewchild="module_source"  id="sr_mosrc_flickr" data-type="flickr" class="sr_module_srcs sr--force--hide">    
    <sr-separator>
        <sr-separator-body>
            <sr-input wide textblock class="sr--mb--5"><textarea name="Flickr API Key" style="height:53px;margin-bottom:0px" r="source.flickr.apiKey" viewchild="module_source_flickr"></textarea><span noicon="" style="bottom:10px" class="sr--form--otitle"><?php _e('Flickr Api Key <a noopener class="sr--link--inner-text"  href="https://weblizar.com/get-flickr-api-key/" target="_blank">(Where to get)</a>','revslider'); ?></span></sr-input>        
                <sr-wrap basic>
                    <sr-input half class="sr--mb--10 sr--mr--10"><input name="Max Slides" r="source.flickr.count" viewchild="module_source_flickr" validate type="text" number="true" min="0" max="150" default="8"><span noicon="" class="sr--form--otitle"><?php _e('Max Slides','revslider'); ?></span></sr-input><!--
                --><sr-input half class="sr--mb--10"><input name="Cache (sec)" data-onchange="B.flickr.reset" r="source.flickr.transient" viewchild="module_source_flickr" validate type="text" number="true" min="0" max="50000" default="1200"><span noicon="" class="sr--form--otitle"><?php _e('Cache (sec).','revslider'); ?></span></sr-input>
            </sr-wrap>
            <sr-sp h="10"></sr-sp> 
        </sr-separator-body>        
    </sr-separator>
    <sr-separator noborder>
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('Flickr Source','revslider'); ?></sr-separator-title>                    
        </sr-separator-head>
        <sr-separator-body>
            <sr-drop wide  data-v="" r="source.flickr.type" data-onchange="B.flickr.reset" viewchild="module_source_flickr" dropsw="300" data-sh="#sr_module_src_flickrsrcs>sr-wrap" data-shdep="#eqvalue">
                <sr-drop-view>
                    <span class="sr--drop--value"></span>
                    <span class="sr--form--otitle"><?php _e('Stream Source','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>   
                <sr-drops data-v="publicphotos"><?php _e('User Public Photos','revslider'); ?></sr-drops>
                <sr-drops data-v="photosets"><?php _e('Certain Album from User','revslider'); ?></sr-drops>
                <sr-drops data-v="gallery"><?php _e('From Gallery','revslider'); ?></sr-drops>
                <sr-drops data-v="group"><?php _e('From Groups','revslider'); ?></sr-drops>
            </sr-drop>
            <sr-wrap id="sr_module_src_flickrsrcs">
                <sr-wrap value="publicphotos;photosets"><sr-input data-onchange="B.flickr.reset" wide class="sr--mb--15"><input name="User URL" r="source.flickr.userURL" viewchild="module_source_flickr" type="text"><span noicon="" class="sr--form--otitle"><?php _e('User URL','revslider'); ?></span></sr-input></sr-wrap>
                <sr-wrap value="group"><sr-input wide class="sr--mb--15"><input name="Group URL" r="source.flickr.groupURL" viewchild="module_source_flickr" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Group URL','revslider'); ?></span></sr-input></sr-wrap>
                <sr-wrap value="gallery"><sr-input  wide class="sr--mb--15"><input name="Gallery URL" r="source.flickr.galleryURL" viewchild="module_source_flickr" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Gallery URL','revslider'); ?></span></sr-input></sr-wrap>
                <sr-wrap value="photosets">
                    <sr-drop wide multiselect usecheck keepotitle data-type="search" class="sr--mb--15" data-v="" r="source.flickr.photoSet" data-source="flickrsets" data-apisrc="source.flickr.apiKey" data-urlsrc="source.flickr.userURL" data-countsrc="source.flickr.count" viewchild="module_source_flickr" dropsw="300" dropsh="300">
                        <sr-drop-view>
                            <span class="sr--drop--value" style="padding-right:50px"></span>
                            <span class="sr--form--otitle"><?php _e('Album','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                    </sr-drop>
                    <sr-sp h="0"></sr-sp> 
                </sr-wrap>
            </sr-wrap>
            <sr-sp h="5"></sr-sp> 
        </sr-separator-body>
    </sr-separator>
</sr-wrap>