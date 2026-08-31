<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
<sr-wrap view="module_source_instagram" viewchild="module_source"  id="sr_mosrc_instagram" data-type="instagram" class="sr_module_srcs sr--force--hide">                                
    <sr-separator-body>        
        <sr-input wide textblock class="sr--mb--5"><textarea name="Instagram Token"  style="height:180px;margin-bottom:0px" r="source.instagram.token" viewchild="module_source_instagram"></textarea><span noicon="" style="bottom:10px" class="sr--form--otitle"><?php _e('Enter Token or','revslider'); ?> <a class="sr--link--inner-text"  data-action="B.instagram.getToken"><?php _e('Connect Here','revslider'); ?></a></span></sr-input>
        <sr-input wide class="sr--mb--15"><input name="Connected With" style="pointer-events:none" disabled r="source.instagram.connect_with" viewchild="module_source_instagram" validate type="text"><span noicon="" class="sr--form--otitle"><?php _e('Connected With','revslider'); ?></span></sr-input>            
        <sr-wrap basic>
                <sr-input half class="sr--mb--5 sr--mr--10"><input name="Amount of Slides" r="source.instagram.count" viewchild="module_source_instagram" validate type="text" number="true" min="0" max="25" default="8"><span noicon="" class="sr--form--otitle"><?php _e('Amount of Slides','revslider'); ?></span></sr-input><!--
            --><sr-input half class="sr--mb--10"><input name="Cache (sec)" r="source.instagram.transient" viewchild="module_source_instagram" validate type="text" number="true" min="0" max="50000" default="1200"><span noicon="" class="sr--form--otitle"><?php _e('Cache (sec).','revslider'); ?></span></sr-input>
        </sr-wrap>
        <sr-sp h="10"></sr-sp>
    </sr-separator-body>   
</sr-wrap>