<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_browser" class="sr--no--padding sr--panel--leftsidebar" view="modulebrowser" style="width:320px; border-radius:0px;">
    <!--<sr-options-menu fourperrow>
        <sr-nav-btn data-sr-tabc="sr_mobrow_dep" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="20.775" height="14.55"><use xlink:href="#Browser"></use></svg></sr-icon-wrap><span><?php echo __('Browser Based','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_modev_dep" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="22.015" height="14.73"><use xlink:href="#Device"></use></svg></sr-icon-wrap><span><?php echo __('Device Based','revslider');?></span></sr-nav-btn>
    </sr-options-menu>-->
    <sr-modal-content>
        <!-- 
            SLIDE THUMBNAIL SETTINGS 
        -->
        <!--<sr-wrap view="modulebrowser" viewchild="modulebrowser" class="sr--tab--content sr--open" id="sr_mobrow_dep">-->
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Browser Behavior','revslider'); ?></sr-separator-title>
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="general.observeWrap" viewchild="modulebrowser" class="sr--mr--10"></sr-onoff><span><?php _e('Observe Wrapper Container','revslider'); ?></span></sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="browser.freezeOnBlur" viewchild="modulebrowser" class="sr--mr--10"></sr-onoff><span><?php _e('Freeze on Blur','revslider'); ?></span></sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="browser.useURLDeeplink" viewchild="modulebrowser" class="sr--mr--10"></sr-onoff><span><?php _e('Use Deeplink Hash in URL','revslider'); ?></span></sr-wrap>
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Runtime and Output','revslider'); ?></sr-separator-title>
                </sr-separator-head>
                <sr-separator-body>    
                    <sr-drop r="general.dpr" viewchild="modulebrowser" wide data-v="">
                        <sr-drop-view>
                            <span class="sr--drop--value">None</span>
                            <span class="sr--form--otitle"><?php _e('Max Image DPR','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="ax1"><?php _e('Auto but Max x1','revslider'); ?></sr-drops>
                        <sr-drops data-v="ax2"><?php _e('Auto but Max x2','revslider'); ?></sr-drops>
                        <sr-drops data-v="ax3"><?php _e('Auto but Max x3','revslider'); ?></sr-drops>
                        <sr-drops data-v="ax4"><?php _e('Auto but Max x4','revslider'); ?></sr-drops>
                        <sr-drops data-v="dpr">Auto</sr-drops>
                        <sr-drops data-v="x1">x1</sr-drops>
                        <sr-drops data-v="x2">x2</sr-drops>
                        <sr-drops data-v="x3">x3</sr-drops>
                        <sr-drops data-v="x4">x4</sr-drops>
                    </sr-drop> 
                    <sr-drop r="general.outputFilter" viewchild="modulebrowser"  wide data-v="">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Markup Output Filter','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
                        <sr-drops data-v="compress"><?php _e('By Compressing Output','revslider'); ?></sr-drops>
                        <sr-drops data-v="echo"><?php _e('By Echo Output','revslider'); ?></sr-drops>
                    </sr-drop> 
                    <sr-drop r="general.icache" viewchild="modulebrowser" wide data-v="">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Internal Cache Usage','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="default"><?php _e('Global Default','revslider'); ?></sr-drops>
                        <sr-drops data-v="on"><?php _e('On','revslider'); ?></sr-drops>
                        <sr-drops data-v="off"><?php _e('Off','revslider'); ?></sr-drops>
                    </sr-drop>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>   
       <!-- </sr-wrap>     
        <sr-wrap view="module_dev_deps" viewchild="modulebrowser" class="sr--tab--content" id="sr_modev_dep">-->
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Mobile Device Limitations','revslider'); ?></sr-separator-title>
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="general.disableOnMobile" viewchild="module_dev_deps" class="sr--mr--10"></sr-onoff><span><?php _e('Disable Module on Mobile','revslider'); ?></span></sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="general.disablePanZoomMobile" viewchild="module_dev_deps" class="sr--mr--10"></sr-onoff><span><?php _e('Disable Pan Zoom Effects on Mobile','revslider'); ?></span></sr-wrap>
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
    </sr-modal-content>
</sr-modal>