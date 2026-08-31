<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_navigation" class="sr--no--padding sr--panel--leftsidebar" view="modulenavigation" style="width:360px">    
    <sr-options-menu id="sr_module_navigation_navgroup" fourperrow>        
        <sr-nav-btn data-sr-tabc="sr_mo_controls" data-tab-target-group="1" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="24" height="14" transform="translate(0,-1)"><use xlink:href="#Elements_Navigation"></use></svg></sr-icon-wrap><span><?php echo __('Controls','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mo_imouse" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="14" height="21" transform="translate(0,-1)"><use xlink:href="#Submenu_Hover"></use></svg></sr-icon-wrap><span><?php echo __('Mouse Wheel','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mo_ikeyboard" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="24" height="16.42" transform="translate(0,-1)"><use xlink:href="#Addon_Typewriter"></use></svg></sr-icon-wrap><span><?php echo __('Keyboard','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mo_itouch" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20" height="20" transform="translate(0,-1)"><use xlink:href="#Main_Menu_Actions"></use></svg></sr-icon-wrap><span><?php echo __('Touch','revslider');?></span></sr-nav-btn>
    </sr-options-menu>
    <sr-modal-content>   
        <sr-wrap view="navigation" viewchild="modulenavigation" class="sr--tab--content sr--open" data-tab-target-group="1" id="sr_mo_controls">
            <sr-separator>
                <sr-sp h="20"></sr-sp>
                <sr-separator-body>
                <sr-input wide class="sr--mr--10"><input name="Preview IMG Width" replace r="nav.p.w" viewchild="navigation" type="text" number="true" min="32" max="1280" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Preview Image Width','revslider'); ?></span></sr-input>
                <sr-input wide><input name="Preview IMG Height" replace r="nav.p.h" viewchild="navigation" type="text" number="true" min="18" max="720" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Preview Image Height','revslider'); ?></span></sr-input>
                <sr-drop wide data-v="auto" r="nav.rtl" viewchild="navigation">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('off','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('RTL Mode','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="on"><?php _e('On','revslider'); ?></sr-drops>
                        <sr-drops data-v="off"><?php _e('Off','revslider'); ?></sr-drops>
                        <sr-drops data-v="auto"><?php _e('Auto Detect','revslider'); ?></sr-drops>    
                    </sr-drop>  
                <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>    
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Available Controls','revslider'); ?></sr-separator-title>
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.arrows.set" viewchild="navigation" class="sr--mr--10 checked" data-action="editor.nav.checkDefaults,editor.nav.preview.toggle" data-aparams="arrows" data-onchange="editor.module.submenu" data-onundoredo="editor.module.submenu"></sr-onoff><span><?php _e('Arrows','revslider'); ?></span></sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.bullets.set" viewchild="navigation" class="sr--mr--10 checked" data-action="editor.nav.checkDefaults,editor.nav.preview.toggle" data-aparams="bullets" data-onchange="editor.module.submenu" data-onundoredo="editor.module.submenu"></sr-onoff><span><?php _e('Bullets','revslider'); ?></span></sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.tabs.set" viewchild="navigation" class="sr--mr--10 checked" data-action="editor.nav.checkDefaults,editor.nav.preview.toggle" data-aparams="tabs" data-onchange="editor.module.submenu" data-onundoredo="editor.module.submenu"></sr-onoff><span><?php _e('Tabs','revslider'); ?></span></sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.thumbs.set" viewchild="navigation" class="sr--mr--10 checked" data-action="editor.nav.checkDefaults,editor.nav.preview.toggle" data-aparams="thumbs"  data-onchange="editor.module.submenu" data-onundoredo="editor.module.submenu"></sr-onoff><span><?php _e('Thumbnails','revslider'); ?></span></sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.scrubber.set" viewchild="navigation" class="sr--mr--10 checked" data-action="editor.nav.checkDefaults,editor.nav.preview.toggle" data-aparams="scrubber" data-onchange="editor.module.submenu" data-onundoredo="editor.module.submenu"></sr-onoff><span><?php _e('Scrubber','revslider'); ?></span></sr-wrap>
                </sr-separator-body>
                <sr-sp h="20"></sr-sp>
            </sr-separator>
            
            <!--<sr-panel-invers>
                <sr-tabs-wrap wrap="">
                    <sr-tab data-sr-tabc="sr_nav_arrows" data-tab-target-group="2" class="sr--tab--call selected sr--active--tab" ltop="" onethird="">1</sr-tab>
                    <sr-tab data-sr-tabc="sr_nav_bullets" data-tab-target-group="2" class="sr--tab--call" none="" onethird="">2</sr-tab>
                    <sr-tab data-sr-tabc="sr_nav_tabs" data-tab-target-group="2" class="sr--tab--call" rtop="" onethird="" class="sr--active--tab">3</sr-tab>
                    <sr-tab data-sr-tabc="sr_nav_thumbs" data-tab-target-group="2" class="sr--tab--call" lbottom="" onethird="">4</sr-tab>
                    <sr-tab data-sr-tabc="sr_nav_scrubber" data-tab-target-group="2" class="sr--tab--call" none="" onethird="">5</sr-tab>
                </sr-tabs-wrap>
            </sr-panel-invers>-->            
        </sr-wrap>  
        <sr-wrap view="wheel" viewchild="modulenavigation" class="sr--tab--content" data-tab-target-group="1" id="sr_mo_imouse">
            <sr-separator>            
                <sr-separator-body>                
                    <sr-sp h="20"></sr-sp>
                    <sr-drop wide data-v="mouse" r="nav.m.use" viewchild="wheel" data-sh=".sr_nav_wheelsettings" data-shdep="#eqvalue">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('off','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Wheel Scroll','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="off"><?php _e('Ignore','revslider'); ?></sr-drops>
                        <sr-drops data-v="infinity"><?php _e('Infinite','revslider'); ?></sr-drops>
                        <sr-drops data-v="on"><?php _e('Sequential','revslider'); ?></sr-drops>    
                    </sr-drop>  
                    <sr-wrap value="infinity#;#on" class="sr_nav_wheelsettings">
                        <sr-tabs-wrap viewchild="wheel" r="nav.m.r" class="sr--mb--15">
                            <sr-tab left half class="sr--active--tab" data-v="default"><?php _e('Normal Direction','revslider'); ?></sr-tab>
                            <sr-tab right half data-v="reverse"><?php _e('Mirrored Direction','revslider'); ?></sr-tab>
                        </sr-tabs-wrap>
                        <sr-input wide class="sr--mr--10"><input name="Min. Visibility" replace r="nav.m.v" viewchild="wheel" type="text" number="true" min="0" max="100" suffix="%" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Required Min. Module Visibilty','revslider'); ?></span></sr-input>
                        <?php // Page-scroll overflow options below only apply to standard/hero modules.
                              // Carousels rotate smoothly on wheel and take infinite looping & snapping from the Carousel settings. ?>
                        <sr-sh r="type" data-shdep="standard#;#hero" viewchild="wheel">
                            <sr-drop wide data-v="mouse" r="nav.m.t" viewchild="wheel">
                                <sr-drop-view>
                                    <span class="sr--drop--value"><?php _e('html','revslider'); ?></span>
                                    <span class="sr--form--otitle"><?php _e('Scroll Overflow Target','revslider'); ?></span>
                                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                                </sr-drop-view>
                                <sr-drops data-v="window"><?php _e('Window','revslider'); ?></sr-drops>
                                <sr-drops data-v="html"><?php _e('HTML','revslider'); ?></sr-drops>
                                <sr-drops data-v="body"><?php _e('Body','revslider'); ?></sr-drops>
                            </sr-drop>
                            <sr-input half class="sr--mr--10"><input name="Call Delay" replace r="nav.m.cd" viewchild="wheel" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Call Delay','revslider'); ?></span></sr-input><!--
                            --><sr-input half><input name="Snap Threshold" replace r="nav.m.st" viewchild="wheel" type="text" number="true" min="0" max="1000" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Snap Threshold','revslider'); ?></span></sr-input>
                        </sr-sh>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>     
        </sr-wrap>     
        <sr-wrap view="keyboard" viewchild="modulenavigation" class="sr--tab--content" data-tab-target-group="1" id="sr_mo_ikeyboard">
            <sr-separator>                
                <sr-separator-body>   
                    <sr-sp h="20"></sr-sp>
                    <sr-drop wide data-v="off" r="nav.k.use" viewchild="keyboard">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('off','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Active Keys','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="horizontal"><?php _e('Left & Right Arrows','revslider'); ?></sr-drops>
                        <sr-drops data-v="vertical"><?php _e('Up & Down Arrows','revslider'); ?></sr-drops>
                        <sr-drops data-v="off"><?php _e('None','revslider'); ?></sr-drops>
                    </sr-drop>
                <sr-separator-body>
            </sr-separator>
        </sr-wrap> 
        <sr-wrap view="touch" viewchild="modulenavigation" class="sr--tab--content" data-tab-target-group="1" id="sr_mo_itouch">
            <sr-separator>                
                <sr-separator-body> 
                    <sr-sp h="20"></sr-sp>
                    <sr-sh r="type" data-shdep="standard#;#hero" viewchild="touch">   
                        <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.s.use" viewchild="touch" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Swipe to navigate on Mobile','revslider'); ?></span></sr-wrap>    
                        <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.s.desk" viewchild="touch" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Swipe to navigate on Desktop','revslider'); ?></span></sr-wrap>
                    </sr-sh>
                    <sr-sh r="type" data-shdep="carousel" viewchild="touch">   
                        <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.s.mobC" viewchild="touch" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Carousel Swipe on Mobile','revslider'); ?></span></sr-wrap>    
                        <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.s.deskC" viewchild="touch" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Carousel Swipe on Desktop','revslider'); ?></span></sr-wrap>
                    </sr-sh>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="nav.s.bV" viewchild="touch" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Block Vertical Drag from Scroll','revslider'); ?></span></sr-wrap>
                    <sr-wrap basic class="sr--form--grp sr--mb--10"><sr-onoff r="nav.s.forceSwipe" viewchild="touch" class="sr--mr--10"></sr-onoff><span><?php _e('Force Swipe over Effects','revslider'); ?></span></sr-wrap>
                    <sr-tabs-wrap viewchild="touch" r="nav.s.d" class="sr--mb--15">
                        <sr-tab left half class="sr--active--tab" data-v="horizontal"><?php _e('Horizontal','revslider'); ?></sr-tab>
                        <sr-tab right half data-v="vertical"><?php _e('Vertical','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-drop wide data-v="1" r="nav.s.t" viewchild="touch">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('1','revslider'); ?></span> 
                            <span class="sr--form--otitle"><?php _e('Touch Sensitivity (Fingers)','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="1"><?php _e('1','revslider'); ?></sr-drops>
                        <sr-drops data-v="2"><?php _e('2','revslider'); ?></sr-drops>
                        <sr-drops data-v="3"><?php _e('3','revslider'); ?></sr-drops>
                        <sr-drops data-v="4"><?php _e('4','revslider'); ?></sr-drops>
                        <sr-drops data-v="5"><?php _e('5','revslider'); ?></sr-drops>
                    </sr-drop> 
                    <sr-sp h="5"></sr-sp>
                <sr-separator-body>  
            </sr-separator>
        </sr-wrap> 
    </sr-modal-content>
</sr-modal>