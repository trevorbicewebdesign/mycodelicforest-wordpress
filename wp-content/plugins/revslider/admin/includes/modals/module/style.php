<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_style" class="sr--no--padding sr--panel--leftsidebar" view="modulestyles" style="width:320px; border-radius:0px;" haspreview="sr7-module-previewstylesdemo-wrap">    
    <!--<sr-options-menu fourperrow>
        <sr-nav-btn data-sr-tabc="sr_mostyle_style" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="16" height="12.31"><use xlink:href="#Dashboard_Rename"></use></svg></sr-icon-wrap><span><?php echo __('Module Style','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mostyle_skin" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="21.9" height="16.9" transform="translate(0,-1)"><use xlink:href="#Dashboard_HTML"></use></svg></sr-icon-wrap><span><?php echo __('Loading Spinner','revslider');?></span></sr-nav-btn>
    </sr-options-menu>-->
    <sr7-module-previewstylesdemo-wrap id="sr_module_bg_preview">
        <sr7-module-shadow-demo id="sr7-module-shadow-demo"></sr7-module-shadow-demo>    
        <sr7-module-demo-wrap id="sr7-module-demo-wrap">
            <sr7-preloader-demo id="sr7-module-preloader-demo"></sr7-preloader-demo>
            <sr7-module-overlay-demo id="sr7-module-overlay-demo"></sr7-module-overlay-demo>
        </sr7-module-demo-wrap>  
    </sr7-module-previewstylesdemo-wrap>

    <sr-modal-content>
        <!-- 
            SLIDE THUMBNAIL SETTINGS 
        -->
        <sr-wrap view="module_style" viewchild="modulestyles" class="sr--tab--content sr--open" id="sr_mostyle_style">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Module Background','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body>  
                    <sr-wrap class="sr--form--grp sr--mr--10" half><sr-color-mini data-v="transparent" r="bg.color" data-type="background" class="sr--mr--10" data-onchange="editor.module.bgupdate" viewchild="module_style"></sr-color-mini><span class="sr--mr--30"><?php _e('Color','revaslider');?></span></sr-wrap><!--
                    --><sr-wrap basic half class="sr--form--grp"><sr-onoff r="bg.image.u" viewchild="module_style" data-sh="#sr_module_image_selector" data-onchange="editor.module.bgupdate" class="checked sr--mr--10"></sr-onoff><span class="sr--mr--15"><?php _e('Image','revslider'); ?></span></sr-wrap>                    
                    <sr-sp h="15"></sr-sp>
                    <sr-wrap id="sr_module_image_selector" class="sr_image_selector">                        
                        <sr-wrap>
                            <sr-wrap inline class="sr--mr--10">
                                <sr-bg-src>
                                    <sr-bg-img r="bg.image.src" viewchild="module_style" data-onchange="editor.module.bgupdate">
                                        <svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
                                        <sr--bg--picker-wrap>
                                            <svg data-action="B.imgPick.wp" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
                                            <svg data-action="B.imgPick.sr" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>
                                        </sr--bg--picker-wrap>
                                        <svg data-action="B.imgPick.clear,editor.module.bgupdate" viewchild="module_style" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
                                    </sr-bg-img>
                                    <sr-bg-pos-wrap r="bg.image.pos.x,bg.image.pos.y" viewchild="module_style" data-onchange="editor.module.bgupdate">
                                        <sr-bg-pos data-v="0% 0%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="50% 0%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="100% 0%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="0% 50%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="50% 50%" data-action="B.aligner.update" class="checked"></sr-bg-pos>
                                        <sr-bg-pos data-v="100% 50%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="0% 100%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="50% 100%" data-action="B.aligner.update"></sr-bg-pos>
                                        <sr-bg-pos data-v="100% 100%" data-action="B.aligner.update"></sr-bg-pos>  
                                        <sr-wrap basic class="sr_image_custompos"><span class="sr--form--otitle">%</span><sr-bg-pos class="sr--custom--aligner" data-v="custom" data-action="B.aligner.update" data-sh="#sr_custom_bgimgpos_slider"></sr-bg-pos></sr-wrap>      
                                    </sr-bg-pos-wrap>
                                </sr-bg-src>
                            </sr-wrap><!--
                            --><sr-wrap half>                                
                                <sr-drop data-onchange="B.imgPick.draw,editor.module.bgupdate" r="bg.image.size" viewchild="module_style" class="sr_image_bgsize" wide data-v="cover" dropsw="200" dropsh="250">
                                    <sr-drop-view>
                                        <span class="sr--drop--value">Cover</span>
                                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                                    </sr-drop-view>
                                    <sr-drops data-v="cover"><?php _e('Cover','revslider'); ?></sr-drops>
                                    <sr-drops data-v="contain"><?php _e('Contain','revslider'); ?></sr-drops>
                                    <sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops>
                                    <sr-drops data-v="" data-splitvalue=" " data-vpattern="##inp1##%">%
                                        <sr-wrap inline right>
                                            <sr-input mini class="sr--basic" style="width:70px"><input name="%" style="text-align:right" data-onchange="B.drop.combine" data-vref="inp1"  data-onchange="editor.elements.bg.image.update" class="sr--inp--pattern" data-type="text"  placeholder="100%" livevisup autocomplete="off" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input>									
                                        </sr-wrap>
                                    </sr-drops>
                                    <sr-drops data-v="" data-splitvalue=" " data-vpattern="##inp2##px">px
                                        <sr-wrap inline right>
                                            <sr-input mini class="sr--basic" style="width:70px"><input name="px" style="text-align:right" data-onchange="B.drop.combine" data-vref="inp2"  data-onchange="editor.elements.bg.image.update" class="sr--inp--pattern" data-type="text"  placeholder="500" livevisup autocomplete="off" validate="true" number="true" suffix="px" lastSuffix="px"></sr-input>									
                                        </sr-wrap>
                                    </sr-drops>
                                    <sr-drops data-ignoreclick="true" class="sr--nodrpsel" data-onopen="populate"><?php _e('Repeat X');?><sr-wrap inline="" right=""><sr-onoff r="bg.image.rx" viewchild="module_style" livevisup autocomplete="off" data-onchange="editor.module.bgupdate"></sr-onoff></sr-wrap></sr-drops>
                                    <sr-drops data-ignoreclick="true" class="sr--nodrpsel" data-onopen="populate"><?php _e('Repeat Y');?><sr-wrap inline="" right=""><sr-onoff r="bg.image.ry" viewchild="module_style" livevisup autocomplete="off" data-onchange="editor.module.bgupdate"></sr-onoff></sr-wrap></sr-drops>
                                </sr-drop>
                                <sr-wrap id="sr_custom_bgimgpos_slider" basic wide>
                                    <sr-input half class="sr--basic sr--mr--10"><input class="sr--bg--custpos" name="Image Position X" r="bg.image.pos.x" livevisup autocomplete="off" data-onchange="B.imgPick.draw,editor.module.bgupdate" viewchild="module_style" data-type="text" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input><!--
                                    --><sr-input half class="sr--basic"><input class="sr--bg--custpos"name="Image Position Y" r="bg.image.pos.y" livevisup autocomplete="off" data-onchange="B.imgPick.draw,editor.module.bgupdate" data-type="text"  viewchild="module_style" validate="true" number="true" suffix="%" lastSuffix="%"></sr-input>
                                </sr-wrap>
                            </sr-wrap>
                        </sr-wrap>
                        <sr-sp h="10"></sr-sp>
                        <sr-drop wide class="sr_image_variants" data-onchange="B.imgPick.draw" data-v="none">
                            <sr-drop-view>
                                <span class="sr--drop--value"><?php _e('Select Background Image','revslider'); ?></span>
                                <span class="sr--form--otitle">(Dimension)</span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>
                        </sr-drop>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>  
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Overlay','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body> 
                    <sr-drop data-onchange="editor.module.overlay" data-onset="editor.module.overlay" r="bg.overlay.type" viewchild="module_style" class="sr_image_repeat sr--mr--10" twothird data-v="no-repeat">
                        <sr-drop-view>
                            <span class="sr--drop--value">None</span>
                            <span class="sr--form--otitle"><?php _e('Style','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="none"><?php _e('No Overlay','revslider'); ?></sr-drops>
                        <sr-drops data-v="1"><?php _e('Dotted Small','revslider'); ?></sr-drops>
                        <sr-drops data-v="2"><?php _e('Dotted Medium','revslider'); ?></sr-drops>
                        <sr-drops data-v="3"><?php _e('Dotted Large','revslider'); ?></sr-drops>
                        <sr-drops data-v="4"><?php _e('Horizontal Small','revslider'); ?></sr-drops>
                        <sr-drops data-v="5"><?php _e('Horizontal Medium','revslider'); ?></sr-drops>
                        <sr-drops data-v="6"><?php _e('Horizontal Large','revslider'); ?></sr-drops>
                        <sr-drops data-v="7"><?php _e('Vertical Small','revslider'); ?></sr-drops>
                        <sr-drops data-v="8"><?php _e('Vertical Medium','revslider'); ?></sr-drops>
                        <sr-drops data-v="9"><?php _e('Vertical Large','revslider'); ?></sr-drops>
                        <sr-drops data-v="10"><?php _e('Circles Small','revslider'); ?></sr-drops>
                        <sr-drops data-v="11"><?php _e('Circles Medium','revslider'); ?></sr-drops>
                        <sr-drops data-v="12"><?php _e('Diagonal 1','revslider'); ?></sr-drops>
                        <sr-drops data-v="13"><?php _e('Diagonal 2','revslider'); ?></sr-drops>
                        <sr-drops data-v="14"><?php _e('Diagonal 3','revslider'); ?></sr-drops>
                        <sr-drops data-v="15"><?php _e('Diagonal 4','revslider'); ?></sr-drops>
                        <sr-drops data-v="16"><?php _e('Cross','revslider'); ?></sr-drops>
                    </sr-drop><!--
                    --><sr-input onethird><input name="Overlay Size" replace r="bg.overlay.size" data-onchange="editor.module.overlay" viewchild="module_style" type="text" number="true" min="0" max="999999" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Size','revslider'); ?></span></sr-input>
                    <sr-wrap basic wide>   
                        <sr-wrap class="sr--form--grp" half><sr-color-mini data-v="transparent" data-type="text" r="bg.overlay.cA" data-onchange="editor.module.overlay" data-type="background" class="sr--mr--10" viewchild="module_style"></sr-color-mini><span class="sr--mr--30"><?php _e('Dot Color','revaslider');?></span></sr-wrap><!--
                        --><sr-wrap class="sr--form--grp" half><sr-color-mini data-v="transparent" data-type="text"  r="bg.overlay.cB" data-onchange="editor.module.overlay" data-type="background" class="sr--mr--10" viewchild="module_style"></sr-color-mini><span><?php _e('Gap Color','revaslider');?></span></sr-wrap>
                    </sr-wrap>
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Module Shadow Effect','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body> 
                    <sr-drop r="shdw" viewchild="module_style" data-onchange="editor.module.shadow" data-onset="editor.module.shadow" class="sr_image_repeat sr--mr--10" wide data-v="0">
                        <sr-drop-view>
                            <span class="sr--drop--value">None</span>
                            <span class="sr--form--otitle"><?php _e('Shadow','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="0"><?php _e('No Shadow','revslider'); ?></sr-drops>
                        <sr-drops data-v="1"><?php _e('Type 1','revslider'); ?></sr-drops>
                        <sr-drops data-v="2"><?php _e('Type 2','revslider'); ?></sr-drops>
                        <sr-drops data-v="3"><?php _e('Type 3','revslider'); ?></sr-drops>
                        <sr-drops data-v="4"><?php _e('Type 4','revslider'); ?></sr-drops>
                        <sr-drops data-v="5"><?php _e('Type 5','revslider'); ?></sr-drops>
                    </sr-drop>  
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Preloader Animation','revslider'); ?></sr-separator-title>
                </sr-separator-head>   
                <sr-separator-body> 
                    <sr-drop r="pLoader.type" viewchild="module_style" data-sh="#sr_spinner_color" data-shdep="#eqvalue" data-onchange="editor.module.ploader" data-onset="editor.module.ploader" class="sr_image_repeat sr--mr--10" wide data-v="0">
                        <sr-drop-view>
                            <span class="sr--drop--value">None</span>
                            <span class="sr--form--otitle"><?php _e('Preloader Type','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="off"><?php _e('No Preloader','revslider'); ?></sr-drops>
                        <sr-drops data-v="0"><?php _e('Type 1','revslider'); ?></sr-drops>
                        <sr-drops data-v="1"><?php _e('Type 2','revslider'); ?></sr-drops>
                        <sr-drops data-v="2"><?php _e('Type 3','revslider'); ?></sr-drops>
                        <sr-drops data-v="3"><?php _e('Type 4','revslider'); ?></sr-drops>
                        <sr-drops data-v="4"><?php _e('Type 5','revslider'); ?></sr-drops>
                        <sr-drops data-v="5"><?php _e('Type 6','revslider'); ?></sr-drops>
                        <sr-drops data-v="6"><?php _e('Type 7','revslider'); ?></sr-drops>
                        <sr-drops data-v="7"><?php _e('Type 8','revslider'); ?></sr-drops>
                        <sr-drops data-v="8"><?php _e('Type 9','revslider'); ?></sr-drops>
                        <sr-drops data-v="9"><?php _e('Type 10','revslider'); ?></sr-drops>
                        <sr-drops data-v="10"><?php _e('Type 11','revslider'); ?></sr-drops>
                        <sr-drops data-v="11"><?php _e('Type 12','revslider'); ?></sr-drops>
                        <sr-drops data-v="12"><?php _e('Type 13','revslider'); ?></sr-drops>
                        <sr-drops data-v="13"><?php _e('Type 14','revslider'); ?></sr-drops>
                        <sr-drops data-v="14"><?php _e('Type 15','revslider'); ?></sr-drops>
                        <sr-drops data-v="15"><?php _e('Type 16','revslider'); ?></sr-drops>
                    </sr-drop><!--
                   --><sr-wrap id="sr_spinner_color" basic  value="1#;#2#;#3#;#4#;#6#;#7#;#8#;#9#;#10#;#11#;#12#;#13#;#14#;#15" class="sr--form--grp sr--mb--15" wide><sr-color-mini data-v="transparent" data-onchange="editor.module.ploader" r="pLoader.color" data-type="background" class="sr--mr--10" viewchild="module_style"></sr-color-mini><span class="sr--mr--30"><?php _e('Color','revaslider');?></span></sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="module_spinner" viewchild="modulestyles" class="sr--tab--content" id="sr_mostyle_skin">
        
            
        </sr-wrap>   
    </sr-modal-content>
</sr-modal> 