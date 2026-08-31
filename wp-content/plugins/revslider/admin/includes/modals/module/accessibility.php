<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_acc" class="sr--no--padding sr--panel--leftsidebar" view="moduleaccessibility" style="width:320px; border-radius:0px;" haspreview="sr7-module-previewstylesdemo-wrap">    
    <sr-modal-content>
        <!-- 
            SLIDE THUMBNAIL SETTINGS 
        -->
        <sr-wrap view="module_acc" viewchild="moduleaccessibility" class="sr--tab--content sr--open" id="sr_mostyle_style">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Screen Reader Support','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="acc.use" data-sh=".accuse" viewchild="module_acc" class="sr--mr--10" data-onchange="forms.populate"></sr-onoff><span><?php _e('Enable ARIA Attributes','revslider'); ?></span><sr-tooltip key="acc_gen_aria"></sr-tooltip></sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="acc.live" viewchild="module_acc" class="sr--mr--10"></sr-onoff><span><?php _e('Announce Slide Changes (ARIA-Live)','revslider'); ?></span><sr-tooltip key="acc_gen_arialive"></sr-tooltip></sr-wrap>
                    <sr-sp h="15"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator class="accuse">
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Navigation Elements','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body> 
                    <sr-drop wide  class="sr--mb--15" r="nav.acc.hidden" viewchild="module_acc">
                        <sr-drop-view>
                            <span class="sr--drop--value"></span>
                            <span class="sr--form--otitle"><?php _e('Hide from Screen Readers','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>                    
                        <sr-drops data-v="true"><?php _e('True','revslider'); ?></sr-drops>
                        <sr-drops data-v="false"><?php _e('False','revslider'); ?></sr-drops>
                    </sr-drop> 
                    <sr-drop wide class="sr-mb--15" r="nav.acc.pressed" viewchild="module_acc">
                        <sr-drop-view>
                            <span class="sr--drop--value"></span>
                            <span class="sr--form--otitle"><?php _e('Pressed State','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>                    
                        <sr-drops data-v="set"><?php _e('Track','revslider'); ?></sr-drops>
                        <sr-drops data-v="unset"><?php _e('Untrack','revslider'); ?></sr-drops>
                    </sr-drop>
                    <sr-wrap basic wide>
                        <sr-input half class="sr--mr--10"><input name="Next" replace r="nav.acc.next" viewchild="module_acc" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Next','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Previous" replace r="nav.acc.prev" viewchild="module_acc" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Prev.','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-input wide><input name="Bullet" replace r="nav.acc.bullet" viewchild="module_acc" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Bullet','revslider'); ?></span></sr-input>
                    <sr-input wide><input name="Thumb" replace r="nav.acc.thumb" viewchild="module_acc" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Thumb','revslider'); ?></span></sr-input>
                    <sr-input wide><input name="Tab" replace r="nav.acc.tab" viewchild="module_acc" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Tab','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>            
        </sr-wrap>   
    </sr-modal-content>
</sr-modal> 