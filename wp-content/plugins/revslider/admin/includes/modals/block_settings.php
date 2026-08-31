<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

?>
<sr-modal id="block_settings" class="sr--no--padding" data-beforeclose="B.shortcode.settings.check" data-reset="B.shortcode.settings.reset" data-save="B.shortcode.settings.save" view="bs">
    <sr-modal-header>
        <h2 class="sr--modal--title sr--modal--title--simple"><?php echo __('Module Settings','revslider');?></h2>
        <sr-modal-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-modal-close>
    </sr-modal-header>    
    <sr-options-menu fiveperrow class="sr--left--organised" style="gap:10px 10px">
        <sr-nav-btn data-sr-tabc="sr_bs_layout" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="18" height="17.91"><use xlink:href="#Preset_Popup"></use></svg></sr-icon-wrap><span><?php echo __('Module Layout','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_bs_modal" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="39" height="20"><use xlink:href="#Modal"></use></svg></sr-icon-wrap><span><?php echo __('Use as Modal','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_bs_offset" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Toolbar_Content_Flow"></use></svg></sr-icon-wrap><span><?php echo __('Block Offsets','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_bs_depth" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20" height="20"><use xlink:href="#Top_Bar_Elements"></use></svg></sr-icon-wrap><span><?php echo __('Block Depth','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_bs_advanced" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20" height="20"><use xlink:href="#Submenu_Extra_Style"></use></svg></sr-icon-wrap><span><?php echo __('Advanced','revslider');?></span></sr-nav-btn>
    </sr-options-menu>
    <sr-modal-content>

        <sr-wrap view="bs_layout" viewchild="bs" open class="sr--tab--content sr--open" id="sr_bs_layout">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Module Layout','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff viewchild="bs_layout" r="layoutOverride" class="sr--mr--10" data-sh="#sr_bs_overridelayout" data-shdep="checked"></sr-onoff><span><?php echo __('Override Module Layout','revslider');?></span></sr-wrap>
                <sr-sp h="10"></sr-sp>
                <sr-wrap id="sr_bs_overridelayout">
                    <sr-wrap class="sr--form--grp"><sr-onoff viewchild="bs_layout" r="fullwidth" class="sr--mr--10"></sr-onoff><span><?php echo __('Full Width','revslider');?></span></sr-wrap>
                    <sr-sp h="10"></sr-sp>
                    <sr-wrap class="sr--form--grp"><sr-onoff viewchild="bs_layout" r="fullheight" class="sr--mr--10"></sr-onoff><span><?php echo __('Full Height','revslider');?></span></sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-wrap>
            </sr-wrap>
        </sr-wrap>

        <sr-wrap view="bs_modal" viewchild="bs" open class="sr--tab--content" id="sr_bs_modal">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Use as Modal','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp">
                    <sr-onoff viewchild="bs_modal" r="modal" class="sr--mr--10" data-sh="#sr_bs_modal_settings" data-shdep="checked"></sr-onoff>
                    <span><?php echo __('Insert Module as Modal (Popup)','revslider');?></span>
                </sr-wrap>
                <sr-sp h="15"></sr-sp>
                <sr-wrap id="sr_bs_modal_settings">
                    <sr-wrap class="sr--form--grp">
                        <sr-onoff viewchild="bs_modal" r="popup.cookie.use" class="sr--mr--10" data-sh="#sr_bs_modal_cookie_value" data-shdep="checked"></sr-onoff>
                        <span><?php echo __('1 Time Per Session','revslider');?></span>
                    </sr-wrap>
                    <sr-sp h="10"></sr-sp>
                    <sr-wrap id="sr_bs_modal_cookie_value">
                        <sr-input twothird class="sr--mr--0  sr--mb--0">
                            <input viewchild="bs_modal" r="popup.cookie.v" type="text" min="0" max="1000" />
                            <span noicon class="sr--form--otitle"><?php echo __('Session (hours)','revslider');?></span>
                        </sr-input>
                        <sr-sp h="10"></sr-sp>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                    <sr-wrap class="sr--form--grp">
                        <sr-onoff viewchild="bs_modal" r="popup.time.use" class="sr--mr--10" data-sh="#sr_bs_modal_time_value" data-shdep="checked"></sr-onoff>
                        <span><?php echo __('Pop Up after Time','revslider');?></span>
                    </sr-wrap>
                    <sr-sp h="10"></sr-sp>
                    <sr-wrap id="sr_bs_modal_time_value">
                        <sr-input twothird class="sr--mr--0 sr--mb--0">
                            <input viewchild="bs_modal" r="popup.time.v" type="text"/>
                            <span noicon class="sr--form--otitle"><?php echo __('After (ms)','revslider');?></span>
                        </sr-input>
                        <sr-sp h="10"></sr-sp>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                    <sr-wrap class="sr--form--grp">
                        <sr-onoff viewchild="bs_modal" r="popup.scroll.use" class="sr--mr--10" data-sh="#sr_bs_modal_scroll_settings" data-shdep="checked"></sr-onoff>
                        <span><?php echo __('Pop Up at Scroll Position','revslider');?></span>
                    </sr-wrap>
                    <sr-sp h="10"></sr-sp>
                    <sr-wrap id="sr_bs_modal_scroll_settings">
                        <sr-wrap twothird>
                            <sr-tabs-wrap viewchild="bs_modal" r="popup.scroll.type" class="sr--mb--10">
                                <sr-tab left half data-v="offset" data-sh="#sr_bs_modal_scroll_offset" data-hide="#sr_bs_modal_scroll_container"><?php _e('Offset Based','revslider'); ?></sr-tab>
                                <sr-tab right half data-v="container" data-sh="#sr_bs_modal_scroll_container" data-hide="#sr_bs_modal_scroll_offset"><?php _e('Container Based','revslider'); ?></sr-tab>
                            </sr-tabs-wrap>
                        </sr-wrap>
                        <sr-wrap class="sr--form--grp" id="sr_bs_modal_scroll_offset">
                            <sr-input twothird class="sr--mr--0 sr--mb--0">
                                <input viewchild="bs_modal" r="popup.scroll.v" type="text" />
                                <span noicon class="sr--form--otitle"><?php echo __('Offset','revslider');?></span>
                            </sr-input>
                        </sr-wrap>
                        <sr-wrap class="sr--form--grp" id="sr_bs_modal_scroll_container">
                            <sr-input twothird class="sr--mr--0 sr--mb--0">
                                <input name="popup.scroll.container" viewchild="bs_modal" r="popup.scroll.container" type="text" />
                                <span noicon class="sr--form--otitle"><?php echo __('Container','revslider');?></span>
                            </sr-input>
                        </sr-wrap>
                        <sr-sp h="10"></sr-sp>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                    <sr-wrap class="sr--form--grp">
                        <sr-onoff viewchild="bs_modal" r="popup.event.use" class="sr--mr--10" data-sh="#sr_bs_modal_event" data-shdep="checked"></sr-onoff>
                        <span><?php echo __('Pop Up by Events','revslider');?></span>
                    </sr-wrap>
                    <sr-sp h="10"></sr-sp>
                    <sr-wrap id="sr_bs_modal_event">
                        <sr-input twothird class="sr--mr--0 sr--mb--0">
                            <input name="popup.event.name" viewchild="bs_modal" r="popup.event.name" type="text" readonly="readonly" />
                            <span noicon class="sr--form--otitle"><?php echo __('Listen to','revslider');?></span>
                        </sr-input>
                        <sr-sp h="10"></sr-sp>
                        <sr-input twothird class="sr--mr--0 sr--mb--0">
                            <input name="popup.event.sample" viewchild="bs_modal" r="popup.event.sample" type="text" readonly="readonly"/>
                            <span noicon class="sr--form--otitle"><?php echo __('Sample','revslider');?></span>
                        </sr-input>
                        <sr-sp h="10"></sr-sp>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                    <sr-wrap class="sr--form--grp">
                        <sr-onoff viewchild="bs_modal" r="popup.hash.use" class="sr--mr--10" data-sh="#sr_bs_modal_hash_info" data-shdep="checked"></sr-onoff>
                        <span><?php echo __('Pop Up on URL Hash','revslider');?></span>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                    <sr-wrap id="sr_bs_modal_hash_info">
                        <div><?php echo 'https://yourwebsite.com/yourpage/#'; ?><span class="sr--popup--hash--preview"></span></div>
                    </sr-wrap>
                    <sr-separator></sr-separator>
                    <sr-notice status="info" isdismissible="false">
                        <?php echo __("Modals can also be triggered by Layer Actions. See more details in ", 'revslider'); ?>
                        <a href="https://www.themepunch.com/slider-revolution/lightbox-modal/" target="_blank"><?php echo __("Modal Documentation", 'revslider'); ?></a>
                    </sr-notice>
                </sr-wrap>
            </sr-wrap>
        </sr-wrap>

        <sr-wrap view="bs_offset" viewchild="bs" open class="sr--tab--content" id="sr_bs_offset">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Block Offsets','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap>
                    <sr-wrap class="bs_offset_w" dropicon><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></sr-wrap><!--
                    --><sr-wrap inline class="sr--ml--10 sr--mr--10"><sr-bmp type="margin" class="bs_offset_w" idpref="" r="offset.w.o" min="-500" max="2000" suffix="px" viewchild="bs_offset"></sr-bmp></sr-wrap><!--
                    --><sr-wrap inline class="sr--form--grp" style="transform:translateY(3px)"><sr-onoff data-ed=".bs_offset_w" data-eddep="checked" r="offset.w.use" viewchild="bs_offset" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                </sr-wrap>
                <sr-wrap>
                    <sr-wrap class="bs_offset_d" dropicon><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></sr-wrap><!--
                    --><sr-wrap inline class="sr--ml--10 sr--mr--10"><sr-bmp type="margin" class="bs_offset_d" idpref="" r="offset.d.o" min="-500" max="2000" suffix="px" viewchild="bs_offset"></sr-bmp></sr-wrap><!--
                    --><sr-wrap inline class="sr--form--grp" style="transform:translateY(3px)"><sr-onoff data-ed=".bs_offset_d" data-eddep="checked" r="offset.d.use" viewchild="bs_offset" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                </sr-wrap>
                <sr-wrap>
                    <sr-wrap class="bs_offset_n" dropicon><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Laptop"></use></svg></sr-wrap><!--
                    --><sr-wrap inline class="sr--ml--10 sr--mr--10"><sr-bmp type="margin" class="bs_offset_n" idpref="" r="offset.n.o" min="-500" max="2000" suffix="px" viewchild="bs_offset"></sr-bmp></sr-wrap><!--
                    --><sr-wrap inline class="sr--form--grp" style="transform:translateY(3px)"><sr-onoff data-ed=".bs_offset_n" data-eddep="checked" r="offset.n.use" viewchild="bs_offset" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                </sr-wrap>
                <sr-wrap>
                    <sr-wrap class="bs_offset_t" dropicon><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Tablet"></use></svg></sr-wrap><!--
                    --><sr-wrap inline class="sr--ml--10 sr--mr--10"><sr-bmp type="margin" class="bs_offset_t" idpref="" r="offset.t.o" min="-500" max="2000" suffix="px" viewchild="bs_offset"></sr-bmp></sr-wrap><!--
                    --><sr-wrap inline class="sr--form--grp" style="transform:translateY(3px)"><sr-onoff data-ed=".bs_offset_t" data-eddep="checked" r="offset.t.use" viewchild="bs_offset" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                </sr-wrap>
                <sr-wrap>
                    <sr-wrap class="bs_offset_m" dropicon><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Phone"></use></svg></sr-wrap><!--
                    --><sr-wrap inline class="sr--ml--10 sr--mr--10"><sr-bmp type="margin" class="bs_offset_m" idpref="" r="offset.m.o" min="-500" max="2000" suffix="px" viewchild="bs_offset"></sr-bmp></sr-wrap><!--
                    --><sr-wrap inline class="sr--form--grp" style="transform:translateY(3px)"><sr-onoff data-ed=".bs_offset_m" data-eddep="checked" r="offset.m.use" viewchild="bs_offset" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                </sr-wrap>
                <sr-sp h="5"></sr-sp>
            </sr-wrap>
        </sr-wrap>

        <sr-wrap view="bs_depth" viewchild="bs" open class="sr--tab--content" id="sr_bs_depth">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Block Depth','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-input twothird class="sr--mr--10"><input replace r="zindex" viewchild="bs_depth" type="text" suffix=""><span noicon class="sr--form--otitle"><?php _e('Z-Index', 'revslider'); ?></span></sr-input>
                <sr-sp h="5"></sr-sp>
            </sr-wrap>
        </sr-wrap>

        <sr-wrap view="bs_advanced" viewchild="bs" open class="sr--tab--content" id="sr_bs_advanced">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Advanced','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-input twothird class="sr--mr--10"><input replace r="wrapperid" viewchild="bs_depth" type="text" suffix=""><span noicon class="sr--form--otitle"><?php _e('Module Wrapper IDs', 'revslider'); ?></span></sr-input>
                <sr-tooltip key="blocksettingswrapperid"></sr-tooltip>
                <sr-sp h="5"></sr-sp>
            </sr-wrap>
        </sr-wrap>        

        <sr-wrap right class="sr--tab--call">
            <sr-button primary="" data-action="B.shortcode.settings.save" class="sr--cta sr--cta--big sr--mr--10"><?php echo __('Save Module Settings','revslider');?></sr-button>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>