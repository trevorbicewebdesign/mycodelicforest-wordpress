<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_scroll" class="sr--no--padding sr--panel--leftsidebar" view="modulescroll" style="width:360px">    
    <sr-options-menu fourperrow>        
        <sr-nav-btn data-sr-tabc="sr_moscr_mod" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="21.9" height="16.9" transform="translate(0,-1)"><use xlink:href="#Dashboard_HTML"></use></svg></sr-icon-wrap><span><?php echo __('Parallax Effect','revslider');?></span></sr-nav-btn>        
        <sr-nav-btn data-sr-tabc="sr_moscr_sbt" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="16" height="12.31"><use xlink:href="#Dashboard_Rename"></use></svg></sr-icon-wrap><span><?php echo __('Scroll Based Timeline','revslider');?></span></sr-nav-btn>
    </sr-options-menu>
    <sr-modal-content>
    <sr-wrap view="module_mods" viewchild="modulescroll" class="sr--tab--content sr--open" id="sr_moscr_mod">
            <sr-separator >
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('INTERACTIVITY','revslider'); ?></sr-separator-title>                    
                </sr-separator-head>                           
                <sr-separator-body id="sr_mod_settings"> 
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="mod.scroll" viewchild="module_mods" class="sr--mr--10" data-onchange="forms.populate"></sr-onoff><span><?php _e('On Scroll','revslider'); ?></span></sr-wrap>                
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="mod.mouse" viewchild="module_mods" data-sh=".sr_mod_par_mouseset" class="sr--mr--10" data-onchange="forms.populate"></sr-onoff><span><?php _e('On Mouse','revslider'); ?></span></sr-wrap>
                    <sr-wrap class="sr_mod_par_mouseset">    
                        <sr-sh r="type" data-shdep="carousel" viewchild="module_mods">
                            <sr-wrap basic class="sr--form--grp"><sr-onoff data-sh="#sr_mod_par_carset" r="mod.drag" viewchild="module_mods" class="sr--mr--10"></sr-onoff><span><?php _e('On Carousel Drag','revslider'); ?></span></sr-wrap>                        
                            <sr-wrap id="sr_mod_par_carset" wide basic>
                                <sr-sp h="10"></sr-sp>
                                <sr-drop wide data-v="same" r="mod.dir" viewchild="module_mods">
                                    <sr-drop-view>
                                        <span class="sr--drop--value"><?php _e('Same as Carousel','revslider'); ?></span>
                                        <span class="sr--form--otitle"><?php _e('Effect on Axis','revslider'); ?></span>
                                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                                    </sr-drop-view>
                                    <sr-drops data-v="same"><?php _e('Same as Carousel','revslider'); ?></sr-drops>
                                    <sr-drops data-v="opposite"><?php _e('Oposite of Carousel','revslider'); ?></sr-drops>
                                    <sr-drops data-v="both"><?php _e('Both Axis','revslider'); ?></sr-drops>
                                </sr-drop>                            
                                <sr-input wide><input name="Carousel Axis" replace r="mod.sm" viewchild="module_mods" type="text" number="true" min="0" max="999999" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Carousel Axis Multiplication','revslider'); ?></span></sr-input>
                                <sr-input wide><input name="Opposite Axis" replace r="mod.om" viewchild="module_mods" type="text" number="true" min="0" max="9999999" validate="true" ><span noicon="" class="sr--form--otitle"><?php _e('Opposite Axis Multiplication','revslider'); ?></span></sr-input>    
                            </sr-wrap>                                               
                        </sr-sh>   
                    </sr-wrap>
                    <sr-wrap class="sr_mod_par_mouseset">
                        <sr-sh r="type" data-shdep="standard#;#hero" viewchild="module_mods">
                            <sr-wrap basic class="sr--form--grp"><sr-onoff data-sh="#module_mods_3d" r="mod.d3" viewchild="module_mods" class="sr--mr--10" data-onchange="forms.populate"></sr-onoff><span><?php _e('3D Effect','revslider'); ?></span></sr-wrap>
                            <sr-wrap id="module_mods_3d" wide basic>                            
                                <sr-sp h="10"></sr-sp>
                                <sr-input wide class="sr--mr--10"><input name="Tween Speed" replace r="mod.d3s" viewchild="module_mods" type="text" number="true" def="3" min="0.1" max="20" suffix="s" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Tween Speed','revslider'); ?></span></sr-input>
                                <sr-input half class="sr--mr--10"><input name="Crop Fix (z)" replace r="mod.d3z" viewchild="module_mods" type="text" number="true" min="0" max="999999" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Crop Fix (z)','revslider'); ?></span></sr-input><!--
                            --><sr-input half><input name="3D Effect Depth" replace r="mod.d3d" viewchild="module_mods" type="text" number="true" min="0" max="9999999" validate="true" ><span noicon="" class="sr--form--otitle"><?php _e('3D Effect Depth','revslider'); ?></span></sr-input>    
                            </sr-wrap>
                        </sr-sh>
                    </sr-wrap>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="mod.dOM" viewchild="module_mods" class="sr--mr--10"></sr-onoff><span><?php _e('Disable on Mobile Devices','revslider'); ?></span></sr-wrap>
                    <sr-sp h="15"></sr-sp>
                </sr-separator-body>
            </sr-separator>            
        </sr-wrap>  
        <sr-wrap view="module_sbt" viewchild="modulescroll" class="sr--tab--content" id="sr_moscr_sbt">
            <sr-separator topborder dark>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Active','revslider'); ?></sr-separator-title>
                    <sr-onoff class="sr--mr--10" data-sh="#sr_sbt_settings" r="sbt.use" data-onchange="forms.populate,editor.module.submenu" data-onundoredo="editor.module.submenu" viewchild="module_sbt"></sr-onoff>
                </sr-separator-head>
            </sr-separator>
            <sr-separator id="sr_sbt_settings">   
                <sr-separator-body >
                    <sr-sp h="20"></sr-sp>
                    <sr-input wide class="sr--mr--10"><input name="Timeline Animation Speed" replace r="sbt.s" viewchild="module_sbt" type="text" number="true" min="0" max="999999" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Timeline Animation Speed','revslider'); ?></span></sr-input>
                    <sr-wrap basic class="sr--form--grp sr--mb--10"><sr-onoff data-sh="#module_sbt_smoothset" r="sbt.smooth" viewchild="module_sbt" class="sr--mr--10"></sr-onoff><span><?php _e('Smooth Scroll','revslider'); ?></span></sr-wrap>
                    <sr-wrap class="sr--mb--10" id="module_sbt_smoothset" wide basic>
                        <sr-input wide><input name="Smoothing Amount" replace r="sbt.smoothAmt" viewchild="module_sbt" type="text" number="true" def="0.12" min="0.02" max="1" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Smoothing (0.02 sluggish – 1 instant)','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-wrap basic class="sr--form--grp sr--mb--10"><sr-onoff data-sh="#module_sbt_fixscroll" r="sbt.f" viewchild="module_sbt" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Hold Module in View','revslider'); ?></span><sr-tooltip key="preventpagescroll"></sr-tooltip></sr-wrap>
                    <sr-wrap class="sr--mb--10" id="module_sbt_fixscroll" wide basic>                        
                        <sr-input half class="sr--mr--10"><input name="Hold From" replace r="sbt.fStart" viewchild="module_sbt" type="text" number="true" min="0" max="999999" suffix="ms" validate="true" extvalidate="editor.module.sbtCheck"><span noicon="" class="sr--form--otitle"><?php _e('Hold From','revslider'); ?></span></sr-input><!--
                     --><sr-input half><input name="Hold Until" replace r="sbt.fEnd" viewchild="module_sbt" type="text" number="true" min="0" max="9999999" validate="true" suffix="ms" extvalidate="editor.module.sbtCheck"><span noicon="" class="sr--form--otitle"><?php _e('Hold Until','revslider'); ?></span></sr-input>
                        <sr-drop wide r="sbt.a" viewchild="module_sbt">
                            <sr-drop-view>
                                <span class="sr--drop--value"></span>
                                <span class="sr--form--otitle"><?php _e('Vertical Align','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>
                            <sr-drops data-v="top"><?php _e('Top','revslider'); ?></sr-drops>
                            <sr-drops data-v="bottom"><?php _e('Bottom','revslider'); ?></sr-drops>
                            <sr-drops data-v="travel"><?php _e('Travel','revslider'); ?></sr-drops>                            
                        </sr-drop>
                        <sr-tabs-wrap viewchild="module_sbt" r="sbt.nL">
                            <sr-tab left half class="sr--active--tab" data-v="true"><?php _e('Traditional','revslider'); ?></sr-tab>
                            <sr-tab right half data-v="false"><?php _e('Advanced ','revslider'); ?></sr-tab>
                        </sr-tabs-wrap>                                                                 
                    </sr-wrap>
                    <!--<sr-wrap basic class="sr--form--grp sr--mb--10"><sr-onoff r="sbt.layers" viewchild="module_sbt" class="sr--mr--10 checked"></sr-onoff><span>Default Enabled on all Layers</span></sr-wrap>-->
                    <sr-sp h="10"></sr-sp>
                </sr-separator-body>
            </sr-separator>            
        </sr-wrap>

    </sr-modal-content>
</sr-modal>