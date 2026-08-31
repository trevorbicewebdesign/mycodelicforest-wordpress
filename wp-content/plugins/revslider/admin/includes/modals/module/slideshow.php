<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_slideshow" class="sr--no--padding sr--panel--leftsidebar" view="moduleslideshow" style="width:320px">    
    <sr-options-menu fourperrow>
        <sr-nav-btn data-sr-tabc="sr_moshow_sshow" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="10" height="14.434"><use xlink:href="#Play"></use></svg></sr-icon-wrap><span><?php echo __('SlideShow','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_moshow_pbar" class="sr--tab--call sr_moshow_autorotators"><sr-icon-wrap><svg class="sr--icon" width="30" height="10" transform="translate(0,5)"><use xlink:href="#ProgressBar"></use></svg></sr-icon-wrap><span><?php echo __('Progress Bar','revslider');?></span></sr-nav-btn>  
        <sr-nav-btn data-sr-tabc="sr_moshow_timings" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="24" height="24" transform="translate(0,-2)"><use xlink:href="#Time_Progress"></use></svg></sr-icon-wrap><span><?php echo __('Duration & Init','revslider');?></span></sr-nav-btn>  
    </sr-options-menu>
    <sr-modal-content>
        <!-- 
            SLIDE THUMBNAIL SETTINGS 
        -->
        <sr-wrap view="module_slideshow" viewchild="moduleslideshow" class="sr--tab--content sr--open" id="sr_moshow_sshow">
            <sr-separator topborder dark>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Auto Progress','revslider'); ?></sr-separator-title>
                    <sr-onoff style="right:0px" data-sh=".sr_moshow_autorotators" r="slideshow.auto" viewchild="module_slideshow"></sr-onoff>
                </sr-separator-head>
            </sr-separator>
            <sr-separator class="sr_moshow_autorotators">
                <sr-separator-body>   
                    <sr-sp h="20"></sr-sp> 
                    <sr-drop wide data-v="-1" dropsw="200" r="slideshow.loop" data-hide="#sr_moshow_stopatslide" data-shdep="#eqvalue" viewchild="module_slideshow">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Infinity','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Loop(s)','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="-1"><?php _e('Infinity','revslider'); ?></sr-drops>  
                        <sr-drops data-v="0" data-vpattern="##inp1##" data-rule="nosuffix"><?php _e('Loop Amount','revslider'); ?>
                            <sr-wrap inline="" right="">
                                <sr-input mini="" class="sr--basic"><input name="Loops" value="0" data-onchange="B.drop.combine" data-vref="inp1" class="sr--inp--pattern" data-type="text" validate="true" number="true" min="0" max="1000"></sr-input>
                            </sr-wrap>
                        </sr-drops>
                    </sr-drop>
                    <sr-drop id="sr_moshow_stopatslide" value="-1" wide data-v="last" dropsw="200" data-source="slidelength" data-sourceext="lastslide" r="slideshow.stopAt" viewchild="module_slideshow">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Last Slide','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Stop at Slide (after Loops)','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>  
                    </sr-drop>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="slideshow.sOH" viewchild="module_slideshow" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Pause on Hover','revslider'); ?></span></sr-wrap>
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="module_pbar" viewchild="moduleslideshow" class="sr--tab--content" id="sr_moshow_pbar">
            <sr-separator topborder dark>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Active','revslider'); ?></sr-separator-title>
                    <sr-onoff style="right:0px" data-sh="#sr_moshow_prbar" r="pbar.set" data-onchange="editor.elements.updatePbar" viewchild="module_pbar"></sr-onoff>
                </sr-separator-head>
            </sr-separator>
            <sr-wrap id="sr_moshow_prbar">
                <sr-separator> 
                    <sr-separator-body>
                        <sr-sp h="20"></sr-sp>                    
                        <sr-drop wide data-v="slide" r="pbar.base" data-onchange="editor.elements.updatePbar" viewchild="module_pbar" data-sh="#sr_moshow_pbar_gaps" data-shdep="#eqvalue">
                            <sr-drop-view>
                                <span class="sr--drop--value"></span>
                                <span class="sr--form--otitle"><?php _e('Show Progress of','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>    
                            <sr-drops data-v="slide"><?php _e('Focused Slide','revslider'); ?></sr-drops>
                            <sr-drops data-v="module"><?php _e('Full Module','revslider'); ?></sr-drops>
                        </sr-drop>
                        <sr-drop wide data-v="horizontal" r="pbar.t" data-onchange="editor.elements.updatePbar" viewchild="module_pbar">
                            <sr-drop-view>
                                <span class="sr--drop--value"></span>
                                <span class="sr--form--otitle"><?php _e('Progress "Bar" Type','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>    
                            <sr-drops data-v="horizontal"><?php _e('Horizontal Bar','revslider'); ?></sr-drops>
                            <sr-drops data-v="vertical"><?php _e('Vertical Bar','revslider'); ?></sr-drops>
                            <sr-drops data-v="cw"><?php _e('Clock Wise Circle','revslider'); ?></sr-drops>
                            <sr-drops data-v="ccw"><?php _e('Counter Clock Wise Circle','revslider'); ?></sr-drops>
                        </sr-drop>
                        <sr-sp h="5"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator id="sr_moshow_pbar_gaps" value="module">
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Gaps','revslider'); ?></sr-separator-title>    
                        <sr-onoff style="right:0px" data-sh="#sr_moshow_pbar_gaps_settings" data-onchange="editor.elements.updatePbar" r="pbar.set" viewchild="module_pbar"></sr-onoff>
                    </sr-separator-head>
                    <sr-separator-body id="sr_moshow_pbar_gaps_settings">                        
                        <sr-input half class="sr--mr--10"><input name="Gaps Size" replace r="pbar.gs" viewchild="module_pbar" data-onchange="editor.elements.updatePbar" type="text" number="true" min="0" max="100" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Gap Size','revslider'); ?></span></sr-input><!--    
                        --><sr-wrap class="sr--form--grp" half><sr-color-mini data-v="transparent" viewchild="module_pbar" data-onchange="editor.elements.updatePbar" r="pbar.gc" data-title="<?php _e('Progress Bar Gaps','revslider'); ?>" data-type="background" class="sr--mr--10"></sr-color-mini><span><?php _e('Gap Color','revslider'); ?></span></sr-wrap>                        
                        <sr-sp h="5"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator>         
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Style','revslider'); ?></sr-separator-title>
                    </sr-separator-head>
                    <sr-separator-body>
                        <sr-input half class="sr--mr--10"><input name="Strength" replace r="pbar.s" data-onchange="editor.elements.updatePbar" viewchild="module_pbar" type="text" number="true" min="0" max="1000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Strength','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Radius" replace r="pbar.r" viewchild="module_pbar" data-onchange="editor.elements.updatePbar" type="text" number="true" min="0" max="1000" suffix="" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Radius','revslider'); ?></span></sr-input>                
                        <sr-wrap class="sr--form--grp sr--mr--10" half><sr-color-mini data-v="transparent" data-onchange="editor.elements.updatePbar" viewchild="module_pbar" r="pbar.c" data-title="<?php _e('Progress Bar ’Time Done’ Color','revslider'); ?>" data-type="background" class="sr--mr--10"></sr-color-mini><span><?php _e('Progress','revslider'); ?></span></sr-wrap><!--
                        --><sr-wrap class="sr--form--grp" half><sr-color-mini data-v="transparent" data-onchange="editor.elements.updatePbar" viewchild="module_pbar" r="pbar.bg" data-title="<?php _e('Progress Bar ’Time Left Color','revslider'); ?>" data-type="background" class="sr--mr--10"></sr-color-mini><span><?php _e('Background','revslider'); ?></span></sr-wrap>  
                        <sr-sp h="20"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator>
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Position','revslider'); ?></sr-separator-title>
                    </sr-separator-head>                               
                    <sr-separator-body>                                       
                        <sr-tabs-wrap viewchild="module_pbar"  r="pbar.a">
                            <sr-tab left half class="sr--active--tab" onchange="editor.elements.updatePbar" data-v="slide"><?php _e('Content Flow','revslider'); ?></sr-tab>
                            <sr-tab right half onchange="editor.elements.updatePbar" data-v="slider"><?php _e('Full Stage','revslider'); ?></sr-tab>
                        </sr-tabs-wrap>
                        <sr-sp h="15"></sr-sp>    
                        <sr-aligner mini class="sr--mr--10 sr-onchangeupdate" r="pbar.v,pbar.h" data-onchange="editor.elements.updatePbar" viewchild="module_pbar">  
                            <sr-aligner-wrap>
                                <sr-aligner-pos data-v="top left" data-action="B.aligner.update"></sr-aligner-pos>
                                <sr-aligner-pos data-v="top center" data-action="B.aligner.update"></sr-aligner-pos>
                                <sr-aligner-pos data-v="top right" data-action="B.aligner.update"></sr-aligner-pos>
                                <sr-aligner-pos data-v="center left" data-action="B.aligner.update"></sr-aligner-pos>
                                <sr-aligner-pos data-v="center center" data-action="B.aligner.update" class="checked"></sr-aligner-pos>
                                <sr-aligner-pos data-v="center right" data-action="B.aligner.update"></sr-aligner-pos>
                                <sr-aligner-pos data-v="bottom left" data-action="B.aligner.update"></sr-aligner-pos>
                                <sr-aligner-pos data-v="bottom center" data-action="B.aligner.update"></sr-aligner-pos>
                                <sr-aligner-pos data-v="bottom right" data-action="B.aligner.update"></sr-aligner-pos>  
                            </sr-aligner-wrap>
                        </sr-aligner><!--                    
                        --><sr-wrap basic alignpickerxy><!--
                            --><sr-input half class="sr--mr--10"><input name="Position X" replace r="pbar.x" data-onchange="editor.elements.updatePbar" viewchild="module_pbar" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('X','revslider'); ?></span></sr-input><!--
                            --><sr-input half><input name="Position Y" replace r="pbar.y" viewchild="module_pbar" data-onchange="editor.elements.updatePbar" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Y','revslider'); ?></span></sr-input>
                        </sr-wrap>
                        <sr-sp h="15"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator>
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Visibility','revslider'); ?></sr-separator-title>
                    </sr-separator-head>                               
                    <sr-separator-body>                                                           
                        <sr-drop  wide multiselect="truefalse" multilen="5" usecheck="" data-onchange="editor.elements.updatePbar" r="pbar.vis" viewchild="module_pbar" data-v="" dropsw="190" dropsh="200"> 
                            <sr-drop-view>
                                <span class="sr--drop--value"></span>
                                <span class="sr--form--otitle"><?php _e('Visibility','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>   
                            <sr-drops data-v="0"><sr-wrap dropicon=""><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></sr-wrap>Wide Screen</sr-drops>
                            <sr-drops data-v="1"><sr-wrap dropicon=""><svg class="sr--icon" width="22" height="18" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></sr-wrap>Desktop</sr-drops>
                            <sr-drops data-v="2"><sr-wrap dropicon=""><svg class="sr--icon" width="22" height="16" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Laptop"></use></svg></sr-wrap>Notebook</sr-drops>
                            <sr-drops data-v="3"><sr-wrap dropicon=""><svg class="sr--icon" width="20" height="24" transform="translate(0, 0)"><use xlink:href="#Top_Bar_Tablet"></use></svg></sr-wrap>Tablet</sr-drops>
                            <sr-drops data-v="4"><sr-wrap dropicon=""><svg class="sr--icon" width="14" height="20" transform="translate(0, 0)"><use xlink:href="#Top_Bar_Phone"></use></svg></sr-wrap>Mobile</sr-drops>
                        </sr-drop>  
                        <sr-wrap basic class="sr--form--grp sr--mb--15"><sr-onoff r="pbar.elive" viewchild="module_timing" data-onchange="editor.elements.updatePbar" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Preview in Editor','revslider'); ?></span></sr-wrap>
                        <sr-sp h="5"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
            </sr-wrap>
        </sr-wrap>

        <sr-wrap view="module_timing" viewchild="moduleslideshow" class="sr--tab--content" id="sr_moshow_timings">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Duration','revslider'); ?></sr-separator-title>    
                </sr-separator-head>
                <sr-separator-body>
                    <sr-input wide><input name="Default Slide Duration" replace r="default.len" viewchild="module_timing" type="text" number="true" min="0" max="999999" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Default Slide Duration','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Order','revslider'); ?></sr-separator-title>    
                </sr-separator-head>
                <sr-separator-body>
                    <sr-drop wide data-v="auto" dropsw="200" data-source="slidelength" data-sourceext="auto,lastslide" r="slideshow.firstSlide" viewchild="module_timing">
                        <sr-drop-view>
                            <span class="sr--drop--value"></span>
                            <span class="sr--form--otitle"><?php _e('Alternative 1st Slide','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>  
                    </sr-drop>
                    <sr-wrap basic class="sr--form--grp sr--mb--15"><sr-onoff r="slideshow.shuffle" viewchild="module_timing" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Shuffle Order on Load','revslider'); ?></span></sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator noborder>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Initialisation & Delay','revslider'); ?></sr-separator-title>    
                </sr-separator-head>
                <sr-separator-body>     
                    <sr-input wide><input name="Start After DOM Loaded" replace r="slideshow.initDelay" viewchild="module_timing" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('After DOM Loaded','revslider'); ?></span></sr-input>
                    <sr-input wide><input name="ViewPort Threshold"  replace r="vPort" viewchild="module_timing" type="text" number="true" min="-1000" max="1000" suffix="px" lastsuffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Viewport Threshold (+/-)','revslider'); ?></span></sr-input>   
                    <sr-wrap basic class="sr--form--grp sr--mb--15"><sr-onoff r="slideshow.waitApi" viewchild="module_timing" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Wait for API Call','revslider'); ?></span></sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
    </sr-modal-content>
</sr-modal> 