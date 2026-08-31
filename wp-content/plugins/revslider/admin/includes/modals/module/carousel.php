<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_carousel" class="sr--no--padding sr--panel--leftsidebar" view="modulecarousel" style="width:320px">    
    <sr-options-menu fourperrow>
        <sr-nav-btn data-sr-tabc="sr_mose_carlay" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="16" height="12.31"><use xlink:href="#Dashboard_Rename"></use></svg></sr-icon-wrap><span><?php echo __('Layout','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mose_caeff" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="18.34" height="10.18" transform="translate(0,3)"><use xlink:href="#Options_SR"></use></svg></sr-icon-wrap><span><?php echo __('Effects','revslider');?></span></sr-nav-btn>  
    </sr-options-menu>
    <sr-modal-content>
        <!-- 
            SLIDE THUMBNAIL SETTINGS 
        -->
        <sr-wrap view="module_calay" viewchild="modulecarousel" class="sr--tab--content sr--open" id="sr_mose_carlay">
            <sr-separator>                
                <sr-separator-body>
                    <sr-sp h="20"></sr-sp>
                    <sr-wrap id="sr_carousel_prewrap"></sr-wrap>
                    <sr-tabs-wrap value="legacy#;#3dshowcase" class="sr--mb--15 sr--carousel--type--fields" viewchild="module_calay" r="carousel.type">
                        <sr-tab left half data-sh=".sr-carset-hor" data-hide=".sr-carset-ver" onchange="stage.dims.redrawFull+100" data-shaction="editor.module.carouselVisAmount"  data-v="h"><?php _e('Horizontal','revslider'); ?></sr-tab>
                        <sr-tab right half data-sh=".sr-carset-ver" data-hide=".sr-carset-hor" onchange="stage.dims.redrawFull+100" data-shaction="editor.module.carouselVisAmount" data-v="v"><?php _e('Vertical','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>  
                    <sr-wrap class="sr--carousel--type--fields" value="legacy">
                        <sr-wrap basic class="sr--form--grp"><sr-onoff r="carousel.infinity" viewchild="module_calay" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Infinity Scroll','revslider'); ?></span><sr-tooltip key="infinitycarousel"></sr-tooltip></sr-wrap>                    
                        <sr-wrap class="sr-carset-hor">
                            <sr-wrap basic class="sr--form--grp"><sr-onoff r="carousel.justify" data-sh=".sr-carset-jmwidth" data-hide=".sr-carset-nojm" data-onchange="stage.dims.redrawFull+100"  viewchild="module_calay" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Justify','revslider'); ?></span><sr-tooltip key="keepcarouselratio"></sr-tooltip></sr-wrap>
                            <sr-wrap basic class="sr-carset-jmwidth sr--form--grp"><sr-onoff r="carousel.jMWidth" viewchild="module_calay" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Respect Module Width','revslider'); ?></span></sr-wrap>
                            <sr-wrap basic class="sr-carset-nojm sr--form--grp"><sr-onoff data-shaction="editor.module.carouselVisAmount" r="carousel.stretch" data-onchange="stage.dims.redrawFull+100" viewchild="module_calay" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Stretch Slides','revslider'); ?></span><sr-tooltip key="stretchcarousel"></sr-tooltip></sr-wrap>
                        </sr-wrap>                    
                        <sr-wrap class="sr-carset-ver">
                            <sr-wrap basic class="sr--form--grp"><sr-onoff data-shaction="editor.module.carouselVisAmount" r="carousel.stretchV" data-onchange="stage.dims.redrawFull+100" viewchild="module_calay" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Stretch Slides','revslider'); ?></span><sr-tooltip key="stretchcarousel"></sr-tooltip></sr-wrap>
                        </sr-wrap>
                        <sr-wrap basic wide class="sr--form--grp"><sr-onoff r="carousel.snap" viewchild="module_calay" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Snap to Selected Alignment','revslider'); ?></span></sr-wrap>
                        <sr-sp h="15"></sr-sp>
                    </sr-wrap>
                    <sr-tabs-wrap viewchild="module_calay" r="carousel.aDir" class="sr--mb--15">
                        <sr-tab left half class="sr--active--tab" data-v="default"><?php _e('Right to Left','revslider'); ?></sr-tab>
                        <sr-tab right half data-v="reverse"><?php _e('Left to Right','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-wrap class="sr--carousel--type--fields" value="legacy#;#focusaccordion">
                        <sr-drop class="sr-carset-hor" wide data-v="" r="carousel.align" viewchild="module_calay">
                            <sr-drop-view>
                                <span class="sr--drop--value">3</span>
                                <span class="sr--form--otitle"><?php _e('Align','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>  
                            <sr-drops data-v="left"><?php _e('Left','revslider'); ?></sr-drops>
                            <sr-drops data-v="center"><?php _e('Center','revslider'); ?></sr-drops>
                            <sr-drops data-v="right"><?php _e('Right','revslider'); ?></sr-drops>
                        </sr-drop><!--   
                        --><sr-drop class="sr-carset-ver" wide data-v="" r="carousel.align" viewchild="module_calay">
                            <sr-drop-view>
                                <span class="sr--drop--value">3</span>
                                <span class="sr--form--otitle"><?php _e('Align','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>  
                            <sr-drops data-v="top"><?php _e('Top','revslider'); ?></sr-drops>
                            <sr-drops data-v="center"><?php _e('Center','revslider'); ?></sr-drops>
                            <sr-drops data-v="bottom"><?php _e('Bottom','revslider'); ?></sr-drops>
                        </sr-drop>                        
                    </sr-wrap>                
                    <sr-drop wide data-v="" r="carousel.showAllLayers" viewchild="module_calay">
                        <sr-drop-view>
                            <span class="sr--drop--value">3</span>
                            <span class="sr--form--otitle"><?php _e('Layer Visibility','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>  
                        <sr-drops data-v="false"><?php _e('Only if Slide in Focus','revslider'); ?></sr-drops>
                        <sr-drops data-v="all"><?php _e('Always on all Slide','revslider'); ?></sr-drops>
                        <sr-drops data-v="individual"><?php _e('Set by Layer Visibility','revslider'); ?></sr-drops>
                    </sr-drop>                
                    <sr-wrap class="sr--carousel--type--fields" value="legacy#;#focusaccordion#;#zoomslide">
                        <sr-drop wide id="sr_carousel_visamnt" data-onchange="stage.dims.redrawFull+100" data-v="forceTrue" class="sr--mr--10" r="carousel.maxV" viewchild="module_calay">
                                <sr-drop-view>
                                    <span class="sr--drop--value">3</span>
                                    <span class="sr--form--otitle"><?php _e('Max Visible Items','revslider'); ?></span>
                                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                                </sr-drop-view>  
                                <sr-drops data-v="1">1</sr-drops>
                                <sr-drops data-v="3">3</sr-drops>
                                <sr-drops data-v="5">5</sr-drops>
                                <sr-drops data-v="7">7</sr-drops>
                                <sr-drops data-v="9">9</sr-drops>
                                <sr-drops data-v="11">11</sr-drops>
                                <sr-drops data-v="13">13</sr-drops>
                                <sr-drops data-v="15">15</sr-drops>
                                <sr-drops data-v="17">17</sr-drops>
                            </sr-drop>
                        <sr-sp h="5"></sr-sp>
                    </sr-wrap>
                </sr-separator>
                <sr-wrap id="sr_carousel_prestyle_wrap"></sr-wrap>
                <sr-separator>       
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Style','revslider'); ?></sr-separator-title>
                    </sr-separator-head>         
                    <sr-separator-body>
                    <sr-input wide class="sr-carset-ver"><input name="Siblings Visibility" replace r="carousel.pNV" data-onupdate="stage.dims.redrawFull+100" viewchild="module_calay" type="text" number="true" min="0" max="400" suffix="px" fallback="none" zerotonone validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Siblings Visibility','revslider'); ?></span></sr-input>                    
                    <sr-wrap wide>
                        <sr-input half class="sr--mr--10"><input name="Border Radius" replace data-onupdate="stage.dims.redrawFull+100" livevisup autocomplete="off" r="carousel.bR" viewchild="module_calay" type="text" number="true" suffix="px|%" min="0" max="100" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Border Radius','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Slide Gaps" replace r="carousel.space" livevisup dragnumber data-onupdate="stage.dims.redrawFull+100" data-onchange="stage.dims.redrawFull+100"  autocomplete="off" viewchild="module_calay" type="text" number="true" suffix="px" min="-300" max="300" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Slide Gaps','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-wrap class="sr-carset-hor" wide>
                        <sr-input half class="sr--mr--10"><input name="Space Above" replace livevisup dragnumber data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" autocomplete="off" r="carousel.pT" viewchild="module_calay" type="text" number="true" suffix="px" min="0" max="400" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Space Above','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Space Below" replace livevisup dragnumber data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" autocomplete="off" r="carousel.pB" viewchild="module_calay" type="text" number="true" suffix="px" min="0" max="400" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Space Below','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-wrap class="sr--carousel--type--fields sr--force--hide" value="3dshowcase" wide>
                        <sr-wrap class="sr-carset-ver">
                            <sr-input half class="sr--mr--10"><input name="Space Above" replace livevisup dragnumber data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" autocomplete="off" r="carousel.pT" viewchild="module_calay" type="text" number="true" suffix="px" min="0" max="400" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Space Above','revslider'); ?></span></sr-input><!--
                            --><sr-input half><input name="Space Below" replace livevisup dragnumber data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" autocomplete="off" r="carousel.pB" viewchild="module_calay" type="text" number="true" suffix="px" min="0" max="400" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Space Below','revslider'); ?></span></sr-input>
                        </sr-wrap>
                    </sr-wrap>
                   <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator keepborder class="sr--carousel--type--fields" value="legacy#;#stack#;#zoomslide#;#3dshowcase">
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Slides Shadow','revslider'); ?></sr-separator-title>
                    <sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr--carslide--shadow" r="carousel.bShdw.use" data-onchange="stage.dims.redrawFull+100" viewchild="module_calay"></sr-onoff>		
                </sr-separator-head>
                <sr-separator-body class="sr--carslide--shadow">                                    
                    <sr-input class="sr--mr--10" half=""><input class="sr--mb--0 sr--mr--5" viewchild="module_calay" r="carousel.bShdw.h" data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" responsive="scale" respshow="f-320middle" replace="true" livevisup="true" dragnumber="true" number="true" min="-50" max="50" suffix="px" validate="true" type="text" id="sr_eqwuwrl_bShdw.h" lastsuffix="px"><span class="sr--form--otitle" noicon="">X</span></sr-input><!--
                    --><sr-input half=""><input class="sr--mb--0 sr--mr--5" viewchild="module_calay" r="carousel.bShdw.v" responsive="scale" data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" respshow="f-320middle" replace="true" livevisup="true" dragnumber="true" number="true" min="-50" max="50" suffix="px" validate="true" type="text" id="sr_zypa2hc_bShdw.v"><span class="sr--form--otitle" noicon="">Y</span></sr-input>
                    <sr-input class="sr--mr--10" half=""><input class="sr--mb--0 sr--mr--5" viewchild="module_calay" r="carousel.bShdw.blur" data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" responsive="scale" respshow="f-320middle" replace="true" livevisup="true" dragnumber="true" number="true" min="0" max="100" suffix="px" validate="true" type="text" id="sr_wt0v9rn_bShdw.blur"><span class="sr--form--otitle" noicon=""><?php _e('Blur','revslider'); ?></span></sr-input><!--
                    --><sr-input half=""><input class="sr--mb--0 sr--mr--5" viewchild="module_calay" r="carousel.bShdw.spread" responsive="scale" data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" respshow="f-320middle" replace="true" livevisup="true" dragnumber="true" number="true" min="-20" max="40" suffix="px" validate="true" type="text" id="sr_7c0cv07_bShdw.spread"><span class="sr--form--otitle" noicon=""><?php _e('Spread','revslider'); ?></span></sr-input>
                    <sr-wrap class="sr--form--grp sr--mr--25" inline="" basic=""><sr-color-mini class="sr--mr--10" viewchild="module_calay" data-onupdate="stage.dims.redrawFull+100"  data-onchange="stage.dims.redrawFull+100" v="transparent" r="carousel.bShdw.color" title="Shadow Color" type="text" data-onchange="stage.dims.redrawFull+100" data-undoredo="stage.dims.redrawFull+100"><sr-color-mini-preview></sr-color-mini-preview></sr-color-mini><span><?php _e('Shadow Color','revslider'); ?></span></sr-wrap>                    
                    <sr-sp h="5"></sr-sp>			
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="module_caeff" viewchild="modulecarousel" class="sr--tab--content" id="sr_mose_caeff">
            <sr-separator>                                
                <sr-separator-body>
                    <sr-sp h="20"></sr-sp>
                    <sr-input wide class="sr--mr--10"><input name="Change Duration" replace r="carousel.dur" viewchild="module_caeff" type="text" number="true" suffix="ms" min="0" max="100000" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Change Duration','revslider'); ?></span></sr-input>
                    <sr-drop wide data-v="" r="carousel.ease" data-source="ease" viewchild="module_caeff">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Easing','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                    </sr-drop>                    
                    <sr-wrap basic wide class="sr--form--grp"><sr-onoff r="carousel.overshoot" viewchild="module_caeff" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Overshoot Effect','revslider'); ?></span></sr-wrap>
                    <sr-wrap-dep dep="multianimengine" class="">
                        <sr-wrap basic wide class="sr--form--grp"><sr-onoff r="carousel.advtrans" viewchild="module_caeff" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Use Advanced Transitions','revslider'); ?></span></sr-wrap>
                    </sr-wrap-dep>
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>   
            <sr-wrap class="sr--carousel--type--fields" value="legacy">
                <sr-separator>
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Sibling Effects','revslider'); ?></sr-separator-title>
                        <sr-tooltip key="carouselsiblings"></sr-tooltip>
                    </sr-separator-head>
                    <sr-separator-body> 
                        <sr-drop wide data-v="" r="carousel.spin"  data-sh=".sr_car_effects" data-shdep="#eqvalue" data-onchange="stage.dims.redrawFull+100" viewchild="module_caeff">
                            <sr-drop-view>
                                <span class="sr--drop--value"><?php _e('Off','revslider'); ?></span>
                                <span class="sr--form--otitle"><?php _e('Spin','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>
                            <sr-drops data-v="off"><?php _e('Off','revslider'); ?></sr-drops>
                            <sr-drops data-v="2d"><?php _e('2D','revslider'); ?></sr-drops>
                            <sr-drops data-v="3d"><?php _e('3D','revslider'); ?></sr-drops>
                        </sr-drop><!--
                        --><sr-input wide value="2d#;#3d" class="sr_car_effects"><input name="Angle" replace r="carousel.spinA" data-onupdate="stage.dims.redrawFull+100"  viewchild="module_caeff" type="text" number="true" suffix="deg" min="0" max="360" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Angle','revslider'); ?></span></sr-input>
                        <sr-input half class="sr--mr--10"><input name="Skew X" replace r="carousel.skewX" viewchild="module_caeff" type="text" number="true" suffix="deg" min="-1000" max="1000" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Skew X','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Skew Y" replace r="carousel.skewY" viewchild="module_caeff" type="text" number="true" suffix="deg" min="-1000" max="1000" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Skew Y','revslider'); ?></span></sr-input>
                        <sr-sp h="5"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator>
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Fade Effect','revslider'); ?></sr-separator-title>
                        <sr-onoff style="right:0px"  r="carousel.opacity" data-onchange="stage.dims.redrawFull+100" data-sh=".sr_car_fadeeff" viewchild="module_caeff"></sr-onoff>
                    </sr-separator-head>
                    <sr-separator-body class="sr_car_fadeeff">   
                        <sr-sp h="5"></sr-sp>                  
                        <sr-input half class="sr_car_fadeeff sr--mb--10 sr--mr--10"><input name="Lowest Fade" replace r="carousel.maxO" data-onupdate="stage.dims.redrawFull+100" viewchild="module_caeff" type="text" number="true" min="0" max="300" fallback="0"  validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Lowest','revslider'); ?></span></sr-input><!--
                        --><sr-wrap half basic class="sr_car_fadeeff sr--mb--10 sr--form--grp"><sr-onoff r="carousel.varO" viewchild="module_caeff" data-onchange="stage.dims.redrawFull+100" class="sr--mr--10"></sr-onoff><span><?php _e('Distance Based','revslider'); ?></span></sr-wrap>                    
                        <sr-sp h="10"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator value="off" class="sr_car_effects">
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Rotation Effects','revslider'); ?></sr-separator-title>
                        <sr-onoff style="right:0px" r="carousel.rotation" data-onchange="stage.dims.redrawFull+100" data-sh=".sr_car_roteff" viewchild="module_caeff"></sr-onoff>
                    </sr-separator-head>  
                    <sr-separator-body class="sr_car_roteff">
                        <sr-sp h="5"></sr-sp>                                      
                        <sr-input half class=" sr--mb--10 sr--mr--10"><input name="Highest Rotation" replace r="carousel.maxR" data-onupdate="stage.dims.redrawFull+100" viewchild="module_caeff" type="text" number="true" min="-300" max="300" fallback="0" suffix="deg" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Highest','revslider'); ?></span></sr-input><!--
                        --><sr-wrap half basic class="sr--mb--10 sr--form--grp"><sr-onoff r="carousel.varR" data-onchange="stage.dims.redrawFull+100" viewchild="module_caeff" class="sr--mr--10"></sr-onoff><span><?php _e('Distance Based','revslider'); ?></span></sr-wrap>                                            
                        <sr-sp h="10"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator value="off" class="sr_car_effects">
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Scale Effects','revslider'); ?></sr-separator-title>
                        <sr-onoff style="right:0px"  r="carousel.scale" data-onchange="stage.dims.redrawFull+100" data-sh=".sr_car_scaeff" viewchild="module_caeff"></sr-onoff>
                    </sr-separator-head>  
                    <sr-separator-body class="sr_car_scaeff">   
                            <sr-sp h="5"></sr-sp>                                                                                                       
                            <sr-input half class="sr--mb--10 sr--mr--10"><input name="Lowest Scale" replace r="carousel.minS" data-onupdate="stage.dims.redrawFull+100" viewchild="module_caeff" type="text" number="true" min="0" max="300" fallback="0" suffix="" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Lowest','revslider'); ?></span></sr-input><!--
                        --><sr-wrap half basic class="sr--mb--10 sr--form--grp"><sr-onoff half r="carousel.vScale" data-onchange="stage.dims.redrawFull+100" class="sr--mr--10" viewchild="module_caeff"></sr-onoff><span><?php _e('Distance Based','revslider'); ?></span></sr-wrap>
                            <sr-sp h="0"></sr-sp> 
                            <sr-wrap wide basic class="sr--mb--10 sr--form--grp"><sr-onoff r="carousel.oScale" data-onchange="stage.dims.redrawFull+100" class="sr--mr--10" viewchild="module_caeff"></sr-onoff><span><?php _e('Use Scale Offset','revslider'); ?></span></sr-wrap>
                    </sr-separator-body>
                </sr-separator>  
            </sr-wrap>
        </sr-wrap>  
    </sr-modal-content>
</sr-modal> 