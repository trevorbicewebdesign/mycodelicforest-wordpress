<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_source" class="sr--no--padding sr--panel--leftsidebar " view="modulesources" style="width:320px">  
    <sr-modal-content>
    <sr-wrap view="module_source" viewchild="modulesources" class="sr--tab--content sr--open" id="sr_mosrc_type">
        <sr-separator noborder>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Content Source','revslider'); ?></sr-separator-title>  
            </sr-separator-head>
            <sr-separator-body>
                <sr-drop wide data-v="gallery" r="source.type" data-shaction="editor.module.showHideSource" viewchild="module_source" dropsw="300" dropsh="460">
                    <sr-drop-view>
                        <span class="sr--drop--value"><?php _e('Custom Gallery','revslider'); ?></span>
                        <span class="sr--form--otitle"></span>
                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                    </sr-drop-view> 
                    <sr-drops-title><?php _e('Website Content Sources','revslider'); ?></sr-drops-title>
                    <sr-drops data-v="gallery"><?php _e('Custom Gallery','revslider'); ?></sr-drops>                    
                    <sr-drops data-v="post"><?php _e('WordPress Posts','revslider'); ?></sr-drops>
                    <sr-drops data-v="woo"><?php _e('WooCommerce','revslider'); ?></sr-drops>
                    <sr-drops-title><?php _e('Dynamic Social Content','revslider'); ?></sr-drops-title>
                    <sr-drops data-v="flickr"><?php _e('Flickr','revslider'); ?></sr-drops>
                    <sr-drops data-v="instagram"><?php _e('Instagram','revslider'); ?></sr-drops>
                    <sr-drops data-v="facebook"><?php _e('FaceBook','revslider'); ?></sr-drops>
                    <sr-drops data-v="youtube"><?php _e('YouTube','revslider'); ?></sr-drops>
                    <sr-drops data-v="vimeo"><?php _e('Vimeo','revslider'); ?></sr-drops>
                </sr-drop>
            </sr-separator-body>    
        </sr-separator>
        
        <sr-separator noborder data-type="gallery" class="sr_module_srcs sr--force--hide"><sr-sp h="5"></sr-sp></sr-separator>
        
        <?php include_once( RS_PLUGIN_PATH . 'admin/includes/modals/module/srcs/post.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'admin/includes/modals/module/srcs/woo.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'admin/includes/modals/module/srcs/flickr.php' ); ?>  
        <?php include_once( RS_PLUGIN_PATH . 'admin/includes/modals/module/srcs/facebook.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'admin/includes/modals/module/srcs/instagram.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'admin/includes/modals/module/srcs/youtube.php' ); ?>
        <?php include_once( RS_PLUGIN_PATH . 'admin/includes/modals/module/srcs/vimeo.php' ); ?>
    </sr-wrap>
    </sr-modal-content>
</sr-modal>