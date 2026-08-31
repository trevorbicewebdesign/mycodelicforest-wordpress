<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_new_guide">
    <sr-guide-step id="sr_guide_start">
        <sr-modal-header>
            <h2 class="sr--popup--big--title"><?php echo __('How would you like to start your module','revslider');?></h2>
            <sr-modal-close data-action="B.popUp.hideAll"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-modal-close>
        </sr-modal-header>            
        <sr-modal-content>
            <sr-sp h="30"></sr-sp>
            <sr-guide-list>
                <sr-guide-new data-action="B.popUp.hideAll,B.olibItem.blank+200" data-aparams="sr_overview">
                    <sr-guide-media>             
                        <sr-guide-box>?</sr-guide-box>
                    </sr-guide-media>
                    <sr-guide-content>
                        <h3 class="sr--popup--medium--title"><?php echo __('Start from Scratch','revslider');?></h3>
                        <sr-sp h="15"></sr-sp>
                        <p class="sr--text"><?php echo __('Build your Slider Revolution module entirely your way — begin with a blank canvas and full creative control.','revslider');?></p>
                    </sr-guide-content>
                </sr-guide-new>
                <sr-guide-new data-action="guide.quickstart">
                    <sr-lbl animation="" id="sr_rest_free_guide" style="z-index:20; position:absolute;top:10px; left:10px;" class="sr--show--onnotreg "><?php echo __('Premium','revslider');?></sr-lbl>
                    <sr-guide-media>  
                        <sr-img style="background-size:contain; background-image:url(<?php echo RS_PLUGIN_URL;?>/admin/assets/images/guide/qsg.webp)"></sr-img>                      
                    </sr-guide-media>
                    <sr-guide-content>
                        <h3 class="sr--popup--medium--title"><?php echo __('Quick Start Generator','revslider');?></h3>
                        <sr-sp h="15"></sr-sp>
                        <p class="sr--text"><?php echo __('Select your module type, size, and layout to instantly generate a ready-to-edit foundation for your design.','revslider');?></p>
                    </sr-guide-content>
                </sr-guide-new>
                <sr-guide-new data-action="B.popUp.hideAll,B.library.open+200" data-aparams="template_library,sr_tlib">
                    <sr-lbl animation="" style="z-index:20; position:absolute;top:10px; left:10px;" class="sr--show--onnotreg "><?php echo __('Premium','revslider');?></sr-lbl>
                    <sr-guide-media>  
                        <sr-img style="background-image:url(<?php echo RS_PLUGIN_URL;?>/admin/assets/images/guide/template_library.png)"></sr-img>                        
                    </sr-guide-media>
                    <sr-guide-content>
                        <h3 class="sr--popup--medium--title"><?php echo __('Use a Pre-built Template ','revslider');?></h3>
                        <sr-sp h="15"></sr-sp>
                        <p class="sr--text"><?php echo __('Choose from a curated library of professionally designed templates — each with polished layouts and sample content you can customize in minutes.','revslider');?></p>
                    </sr-guide-content>
                </sr-guide-new>                
            </sr-guide-list>            
        </sr-modal-content>
        <sr-sp h="50"></sr-sp>
    </sr-guide-step>
    <sr-guide-step id="sr_guide_quickstart" style="display:none;">
        <sr-modal-header>
            <h2 class="sr--popup--big--title"><?php echo __('Generate your Module','revslider');?></h2>
            <sr-modal-close data-action="B.popUp.hideAll"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-modal-close>
        </sr-modal-header>            
        <sr-modal-content>
            <sr-guide-types>
                <sr-guide-type data-action="guide.typechange" data-aparams="hero" class="sr--mr--5"><span class="sr--icon--wrap sr--mr--10"><svg class="sr--icon" width="22" height="17.01" transform="translate(0, -1)"><use xlink:href="#Addon_Panorama"></use></svg></span><?php echo __('Hero','revslider');?></sr-guide-type><!--
                --><sr-guide-type data-action="guide.typechange" data-aparams="slider" class="selected sr--mr--5"><span class="sr--icon--wrap sr--mr--10"><svg class="sr--icon" width="25" height="15.975" transform="translate(0, -1)"><use xlink:href="#Dashboard_Slides"></use></svg></span><?php echo __('Slider','revslider');?></sr-guide-type><!--
                --><sr-guide-type data-action="guide.typechange" data-aparams="carousel" class="sr--mr--5"><span class="sr--icon--wrap sr--mr--10"><svg class="sr--icon" width="25" height="17.105" transform="translate(0, -1)"><use xlink:href="#Carousel"></use></svg></span><?php echo __('Carousel','revslider');?></sr-guide-type>
            </sr-guide-types>
            <sr-sp h="30"></sr-sp>
            <sr-guide-qcontent>
                <sr-qguide-left class="sr_qguide_nav"><svg class="sr--icon"null width="8.485" height="14.142" transform="translate(2, 0) rotate(-180)"><use xlink:href="#General_Expand_Large_Right"></use></svg></sr-qguide-left>
                <sr-qguide-right class="sr_qguide_nav"><svg class="sr--icon" width="8.485" height="14.142"><use xlink:href="#General_Expand_Large_Right"></use></svg></sr-qguide-right>
                <sr-wrap class="sr--mb--0">
                    <h3 id="sr-qguide-title" class="sr--popup--medium--title sr--float--left"><?php echo __('Select one or more Slides','revslider');?></h3><sr-guide-template-order id="sr_qguide_clearorder" style="display:none"><svg class="sr--icon" width="10" height="10" transform="translate(0, -1)"><use xlink:href="#General_Close"></use></svg></sr-guide-template-order>
                    <sr-wrap class="sr--float--right">
                        <sr-drop id="sr_guide_colorfilter" style="width:160px" data-v="both" class="sr--mr--10 sr--mb--0" data-onchange="guide.filter" dropsw="200" dropsh="340">
                            <sr-drop-view><span class="sr--drop--value"><?php _e('Dark & Light','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                            <sr-drops data-v="dark"><?php _e('Dark','revslider');?></sr-drops>
                            <sr-drops data-v="light"><?php _e('Light','revslider');?></sr-drops>
                            <sr-drops data-v="both"><?php _e('Dark & Light','revslider');?></sr-drops>
                        </sr-drop>
                        <sr-drop id="sr_guide_designfilter" style="width:160px"  data-v="both" class="sr--mb--0" data-onchange="guide.filter" dropsw="200" dropsh="340">
                            <sr-drop-view><span class="sr--drop--value"><?php _e('Bold & Fine Design','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                            <sr-drops data-v="bold"><?php _e('Bold Design','revslider');?></sr-drops>
                            <sr-drops data-v="fine"><?php _e('Fine Design','revslider');?></sr-drops>
                            <sr-drops data-v="both"><?php _e('Bold & Fine Design','revslider');?></sr-drops>
                        </sr-drop>
                    </sr-wrap>
                    <sr-sp h="0" class="sr--clear--both"></sr-sp>
                </sr-wrap>
                <sr-guide-viewport>
                    <sr-guide-templates style="margin-left:-20px">
                        <sr-guide-template data-name="center" data-color="dark" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="center" data-color="dark" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="center" data-color="light" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="center" data-color="light" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="layout-one" data-color="dark" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="layout-one" data-color="dark" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="layout-one" data-color="light" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="layout-one" data-color="light" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="layout-two" data-color="dark" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="layout-two" data-color="dark" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="layout-two" data-color="light" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="layout-two" data-color="light" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="layout-three" data-color="dark" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="layout-three" data-color="dark" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="layout-three" data-color="light" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="layout-three" data-color="light" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="left" data-color="dark" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="left" data-color="dark" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="left" data-color="light" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="left" data-color="light" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="right" data-color="dark" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="right" data-color="dark" data-design="fine"></sr-guide-template>
                        <sr-guide-template data-name="right" data-color="light" data-design="bold"></sr-guide-template>
                        <sr-guide-template data-name="right" data-color="light" data-design="fine"></sr-guide-template>
                    </sr-guide-templates>
                </sr-guide-viewport>
                <sr-sp h="15"></sr-sp>
                <sr-qguide-footer>
                    <sr-wrap class="sr--float--left">
                        <sr-drop id="sr_guide_size" style="width:160px" data-v="fullwidth" class="sr--mb--0 sr--mr--10 sr--mb--0"  dropsw="200" dropsh="340">
                            <sr-drop-view><span class="sr--drop--value"><?php _e('Full Width','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                            <sr-drops data-v="auto"><?php _e('Auto','revslider');?></sr-drops>
                            <sr-drops data-v="fullwidth"><?php _e('Full Width','revslider');?></sr-drops>
                            <sr-drops data-v="fullscreen"><?php _e('Full Screen','revslider');?></sr-drops>
                        </sr-drop>
                        <sr-drop id="sr_guide_animation" style="width:160px"  data-v="slide" class="sr--mb--0 sr--mr--10"  dropsw="200" dropsh="340">
                            <sr-drop-view><span class="sr--drop--value"><?php _e('Slide Animation','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                            <sr-drops data-v="slide"><?php _e('Slide Animation','revslider');?></sr-drops>
                            <sr-drops data-v="fade"><?php _e('Fade Animation','revslider');?></sr-drops>
                            <sr-drops data-v="zoom"><?php _e('Zoom Animation','revslider');?></sr-drops>
                        </sr-drop>
                        <sr-drop id="sr_guide_nav" style="width:160px"  data-v="both" class="sr--mb--0"  dropsw="200" dropsh="340">
                            <sr-drop-view><span class="sr--drop--value"><?php _e('Bullets & Arrows','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                            <sr-drops data-v="both"><?php _e('Bullets & Arrows','revslider');?></sr-drops>
                            <sr-drops data-v="bullets"><?php _e('Bullets','revslider');?></sr-drops>
                            <sr-drops data-v="arrows"><?php _e('Arrows','revslider');?></sr-drops>
                            <sr-drops data-v="none"><?php _e('None','revslider');?></sr-drops>
                        </sr-drop>
                        <sr-wrap inline class="sr--form--grp sr--ml--20"><sr-onoff class="sr--mr--10 checked" id="sr_guide_autoplay" ></sr-onoff><span><?php echo __('Auto Play','revslider');?></span></sr-wrap>
                    </sr-wrap>
                    <sr-wrap class="sr--float--right">
                        <sr-button id="sr_guide_create" primary="" data-action="guide.create.start" class="notactive sr--cta sr--cta--big sr--mb--0  sr--mr--10"><?php _e('Select Slide(s) To Continue','revslider');?></sr-button>
                    </sr-wrap>
                    <sr-sp h="0" class="sr--clear--both"></sr-sp>
                </sr-qguide-footer>
            </sr-guide-qcontent>            
        </sr-modal-content>
    </sr-guide-step >

</sr-modal>