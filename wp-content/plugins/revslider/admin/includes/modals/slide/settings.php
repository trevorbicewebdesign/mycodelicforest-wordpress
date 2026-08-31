<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_staticslidesettings" class="sr--no--padding" view="staticslidesettings"> 
    <sr-modal-content>
        <sr-separator>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Static Slide Settings','revslider'); ?></sr-separator-title>
            </sr-separator-head>
            <sr-separator-body>
                <sr-wrap basic wide><span class="sr--form--grp"><sr-onoff r="oflow" viewchild="staticslidesettings" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Overflow Visible','revslider'); ?></span></span></sr-wrap>                    
                <sr-sp h="15"></sr-sp>
                <sr-drop wide r="pos" viewchild="staticslidesettings" data-v="front">
                    <sr-drop-view>
                        <span class="sr--drop--value"></span>
                        <span class="sr--form--otitle"><?php _e('Static Slide Position','revslider'); ?></span>
                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                    </sr-drop-view>                        
                    <sr-drops data-v="front"><?php _e('Above All Slides','revslider'); ?></sr-drops>
                    <sr-drops data-v="back"><?php _e('Behind All Slides','revslider'); ?></sr-drops>					
                </sr-drop>
                <sr-sp h="5"></sr-sp>
            </sr-separator-body>
        </sr-separator>
        <sr-separator>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Static Slide Behavior (Editor)','revslider'); ?></sr-separator-title>                    
            </sr-separator-head>
            <sr-separator-body>
                <sr-wrap basic wide><span class="sr--form--grp"><sr-onoff r="#MODULE#.eVis" viewchild="staticslidesettings" class="sr--mr--10"></sr-onoff><span><?php _e('Visibility in Editor','revslider'); ?></span><sr-tooltip key="globallayersvisibility"></sr-tooltip></span></sr-wrap>
                <sr-sp h="15"></sr-sp>
                <sr-wrap basic wide>
                    <sr-drop wide r="#MODULE#.eVisSlide" data-source="moduleslides" data-sourceparams="real" data-novalue='<?php _e('No Reference Slide','revslider'); ?>' data-onchange="editor.elements.drawReference" viewchild="staticslidesettings" ignoreredraw dropsw="340" dropsh="340">
                        <sr-drop-view>
                            <span class="sr--drop--value" style="display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; padding-right:70px; min-height:26px;"></span>
                            <span class="sr--form--otitle"><?php _e('Reference Slide Layers','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                    </sr-drop>
                </sr-wrap>
                <sr-sp h="15"></sr-sp>
            </sr-separator-body>
        </sr-separator>        
    </sr-modal-content>
</sr-modal>   

<sr-modal id="sr_slidesettings" class="sr--no--padding" view="slidesettings" style="width:320px">    
    <sr-options-menu threeperrow="true" class="sr--left--organised">
        <sr-nav-btn  data-sr-tabc="sr_slse_thumbnail" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="20" height="15"><use xlink:href="#Dashboard_Thumbs"></use></svg></sr-icon-wrap><span><?php echo __('Thumbnail','revslider');?></span></sr-nav-btn>
        <sr-nav-btn  data-sr-tabc="sr_slse_progress" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="22" height="22"><use xlink:href="#Submenu_Progress"></use></svg></sr-icon-wrap><span><?php echo __('Progress','revslider');?></span></sr-nav-btn>
        <sr-nav-btn  data-sr-tabc="sr_slse_schedule" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Submenu_Scheduling"></use></svg></sr-icon-wrap><span><?php echo __('Scheduling','revslider');?></span></sr-nav-btn>
        <sr-nav-btn  data-sr-tabc="sr_slse_params" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20" height="17.84"><use xlink:href="#Addon_Related_Posts"></use></svg></sr-icon-wrap><span><?php echo __('Parameters','revslider');?></span></sr-nav-btn>  
        <sr-nav-btn  data-sr-tabc="sr_slse_attrs" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="21" height="17.84"><use xlink:href="#Submenu_Attributes"></use></svg></sr-icon-wrap><span><?php echo __('Attributes','revslider');?></span></sr-nav-btn>  
        <sr-nav-btn  data-sr-tabc="sr_slse_acc" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Accessibility"></use></svg></sr-icon-wrap><span><?php echo __('Accessibility','revslider');?></span></sr-nav-btn>  
    </sr-options-menu>
    <sr-modal-content>
        <!-- 
            SLIDE THUMBNAIL SETTINGS 
        -->
        <sr-wrap view="slide_thumb" viewchild="slidesettings" class="sr--tab--content sr--open" id="sr_slse_thumbnail">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Admin Thumbnail','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body class="sr_image_selector">    
                    <sr-wrap wide>
                        <sr-img-src style="width:100%;height:164px">
                            <sr-bg-img r="thumb.admin" rdef="thumb.default" viewchild="slide_thumb" data-onchange="editor.slides.updateThumbs" data-undoredo="editor.slides.updateThumbs">
                                    <svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
                                    <sr--bg--picker-wrap>
                                        <svg data-action="B.imgPick.wp" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
                                        <svg data-action="B.imgPick.sr" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>
                                    </sr--bg--picker-wrap>
                                    <svg data-action="B.imgPick.clear" viewchild="slide_thumb" rdef="thumb.default" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
                            </sr-bg-img>  
                        </sr-img-src>
                    </sr-wrap>
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Navigation Thumbnail','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body class="sr_image_selector sr-img-showdim">    
                    <sr-wrap wide>
                        <sr-img-src style="width:100%;height:164px">
                            <sr-bg-img r="thumb.src" rdef="thumb.default" viewchild="slide_thumb" data-onchange="editor.slides.updateThumbs" data-undoredo="editor.slides.updateThumbs">
                                    <svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
                                    <sr--bg--picker-wrap>
                                        <svg data-action="B.imgPick.wp" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
                                        <svg data-action="B.imgPick.sr" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>
                                    </sr--bg--picker-wrap>
                                    <svg data-action="B.imgPick.clear" viewchild="slide_thumb" rdef="thumb.default" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
                                    <sr--bg--dim></sr--bg--dim>
                            </sr-bg-img>  
                        </sr-img-src>
                    </sr-wrap>
                    <sr-sp h="15"></sr-sp>
                    <sr-wrap>
                        <sr-drop wide data-v="value" r="thumb.dimension" viewchild="slide_thumb">
                            <sr-drop-view>
                                <span class="sr--drop--value"></span>
                                <span style="padding-right:5px" class="sr--form--otitle"><?php _e('Optimized Size','revslider'); ?></span>
                                
                            </sr-drop-view>
                            <sr-drops data-v="slider"><?php _e('Navigation Preview','revslider'); ?></sr-drops>
                            <sr-drops data-v="orig"><?php _e('Original Media Size','revslider'); ?></sr-drops>
                        </sr-drop>
                        <sr-tooltip style="top:19px; right:5px;" key="navthumbnail"></sr-tooltip>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator> 
        </sr-wrap>
        <!-- 
            SLIDE PROGRESSING SETTINGS 
        -->
        <sr-wrap view="slide_progress" viewchild="slidesettings" class="sr--tab--content" id="sr_slse_progress">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Slide Duration','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap basic>
                        <sr-input half><input name="Slide Duration" replace class="sr--capitalize" r="slideshow.len" rdef="module.settings.default.len" viewchild="slide_progress" type="text" validate="true" min="500" max="300000" fallback="default" number="true" suffix="ms"><span class="sr--input--icon"><svg width="18" height="18" transform="translate(0, 4)"><use xlink:href="#Options_Timing"></use></svg></span></sr-input><!--
                        --><sr-sp w="15"></sr-sp><!--
                        --><span class="sr--form--grp"><sr-onoff r="slideshow.stop" viewchild="slide_progress" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Pause Module','revslider'); ?></span></span>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Visibility','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="slideshow.hfn" viewchild="slide_progress" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Hide from Navigation','revslider'); ?></span></sr-wrap><!--
                    --><sr-wrap basic class="sr--form--grp sr--mb--10"><sr-onoff r="slideshow.hom" viewchild="slide_progress" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Hide on Mobile','revslider'); ?></span></sr-wrap><!--
                    --><sr-wrap basic>
                        <sr-input wide><input name="Hide After Loops" replace r="slideshow.hal" viewchild="slide_progress" type="text" validate="true" min="0" max="500" number="true"><span noicon="" class="sr--form--otitle"><?php _e('Hide After "n" Loops','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <!-- 
            SLIDE PUBLISHED / UNPUBLISHED  SETTINGS 
        -->
        <sr-wrap view="slide_schedule" viewchild="slidesettings" class="sr--tab--content" id="sr_slse_schedule">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Publish','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap basic>
                        <sr-drop data-v="" r="publish.state" viewchild="slide_schedule" data-onchange="editor.slides.showList"  wide>
                            <sr-drop-view>
                                    <span class="sr--drop--value"></span>
                                    <span class="sr--form--otitle"><?php _e('Status','revslider'); ?></span>
                                    <span class="sr--drop--icon"><svg width="10" height="6"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>
                            <sr-drops data-v="published"><?php _e('Published','revslider'); ?></sr-drops>
                            <sr-drops data-v="unpublished"><?php _e('Unpublished','revslider'); ?></sr-drops>
                        </sr-drop>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Schedule','revslider'); ?></sr-separator-title>  
                    <sr-onoff style="right:0px" r="publish.sch" data-sh="#slide_schedule_fromto" data-onchange="editor.slides.showList" data-shdep="checked" viewchild="slide_schedule" ></sr-onoff>
                </sr-separator-head>
                <sr-separator-body id="slide_schedule_fromto">
                    <sr-wrap basic>
                        <sr-input wide class="sr--clickable--icon">
                            <input name="Publish From" data-action="B.calendar.show" r="publish.from" viewchild="slide_schedule" type="text" placeholder="From">
                            <span  data-action="B.calendar.show" class="sr--input--icon" ><svg width="14" height="14" transform="translate(0, 3)"><use xlink:href="#Submenu_Scheduling"></use></svg></span>
                        </sr-input>
                    </sr-wrap>
                    <sr-wrap basic><!--                        
                        --><sr-input wide class="sr--clickable--icon">
                            <input name="Publish To"  data-action="B.calendar.show" r="publish.to" viewchild="slide_schedule" type="text" placeholder="To">
                            <span  data-action="B.calendar.show" class="sr--input--icon" ><svg width="14" height="14" transform="translate(0, 3)"><use xlink:href="#Submenu_Scheduling"></use></svg></span>
                        </sr-input>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>

        <sr-wrap view="slide_parameters" viewchild="slidesettings" class="sr--tab--content" id="sr_slse_attrs">
            <sr-separator keepborder>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Slide Attributes','revslider'); ?></sr-separator-title>			
                </sr-separator-head>
                <sr-separator-body>
                    <sr-input wide><input name="Slide ID" replace r="attr.id" viewchild="slidesettings" type="text" style="padding-right:50px"><span noicon="" class="sr--form--otitle"><?php _e('HTML ID','revslider'); ?></span></sr-input>	
                    <sr-input wide><input name="Slide Class" replace r="attr.class" viewchild="slidesettings" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('HTML Class','revslider'); ?></span></sr-input>	
                    <sr-input wide><input name="Slide Data" replace r="attr.data" viewchild="slidesettings" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('HTML Data','revslider'); ?></span></sr-input>	
                    <sr-input wide><input name="Slide Deep Link" replace r="attr.deepLink" viewchild="slidesettings" type="text" style="padding-right:90px"><span noicon="" class="sr--form--otitle"><?php _e('DeepLink ID','revslider'); ?></span></sr-input>			
                    <sr-input wide><input name="Slide Attribute" replace r="attr.attr" viewchild="slidesettings" type="text" style="padding-right:50px"><span noicon="" class="sr--form--otitle"><?php _e('Attribute','revslider'); ?></span></sr-input>	                    
                    <sr-sp h="5"></sr-sp>			
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>

        <sr-wrap view="slide_parameters" viewchild="slidesettings" class="sr--tab--content" id="sr_slse_params">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Description','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body>
                    <sr-input wide textblock><textarea name="Slide Description" style="height:89px;margin-bottom:5px;vertical-align:top" r="description" viewchild="slide_parameters"></textarea><span noicon="" class="sr--form--otitle"><?php _e('Slide Description','revslider'); ?></span></sr-input>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Parameters','revslider'); ?></sr-separator-title>    
                </sr-separator-head>
                <sr-separator-body>   
                    <sr-fieldset viewchild="slide_parameters" id="fset_slide_parameters" r="params" listtype="object" class="sr--mb--0"></sr-fieldset>
                    <sr-sp h="5"></sr-sp>
                    <sr-button data-action="B.fieldSet.add" data-aparams="slide,params,object" primary="" class="sr--cta sr--mr--10"><?php echo __('Add Parameters','revslider');?></sr-button>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="slide_accessibility" viewchild="slidesettings" class="sr--tab--content" id="sr_slse_acc">
            <sr-sh r="#MODULE#.acc.use" data-shdep="true" data-sh="#sr_slide_accessibility_use" viewchild="slide_accessibility">
                <sr-separator id="sr_slide_accessibility_use">
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('ARIA Attributes','revslider'); ?></sr-separator-title>  
                    </sr-separator-head>
                    <sr-separator-body>
                        <sr-drop wide  class="sr--mb--15" r="acc.hidden" viewchild="slide_accessibility">
                            <sr-drop-view>
                                <span class="sr--drop--value"></span>
                                <span class="sr--form--otitle"><?php _e('Hide from Screen Readers','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>                    
                            <sr-drops data-v="true"><?php _e('True','revslider'); ?></sr-drops>
                            <sr-drops data-v="false"><?php _e('False','revslider'); ?></sr-drops>
                            <sr-drops data-v="auto"><?php _e('Visible if Focused','revslider'); ?></sr-drops>
                        </sr-drop>                     
                        <sr-drop wide  class="sr--mb--15" r="acc.role" viewchild="slide_accessibility">
                            <sr-drop-view>
                                <span class="sr--drop--value"></span>
                                <span class="sr--form--otitle"><?php _e('Semantic Role','revslider'); ?></span>
                                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                            </sr-drop-view>                    
                            <sr-drops data-v="unset"><?php _e('Unset','revslider'); ?></sr-drops>
                            <sr-drops data-v="group"><?php _e('Group','revslider'); ?></sr-drops>
                            <sr-drops data-v="presentation"><?php _e('Presentation','revslider'); ?></sr-drops>
                            <sr-drops data-v="region"><?php _e('Region','revslider'); ?></sr-drops>
                            <sr-drops data-v="article"><?php _e('Article','revslider'); ?></sr-drops>
                        </sr-drop>
                        <sr-input wide><input name="Role Description" replace r="acc.roledep" viewchild="slide_accessibility" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Role Description','revslider'); ?></span></sr-input>
                        <sr-input wide><input name="Label" replace r="acc.label" viewchild="slide_accessibility" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Label','revslider'); ?></span></sr-input>
                        <sr-sp h="5"></sr-sp>
                    </sr-separator-body>
                </sr-separator>      
            </sr-sh>      
            <sr-sh r="#MODULE#.acc.live" data-shdep="true" data-sh="#sr_slide_accessibility_live" viewchild="slide_accessibility">
                <sr-separator topborder id="sr_slide_accessibility_live">
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Live Announcement Text','revslider'); ?></sr-separator-title>  
                    </sr-separator-head>
                    <sr-separator-body>
                        <sr-input wide><input name="Title" replace r="acc.live" viewchild="slide_accessibility" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Title','revslider'); ?></span></sr-input>
                        <sr-sp h="5"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
            </sr-sh>
            <sr-sh r="#MODULE#.acc.live" data-shdep="false" data-sh="#sr_slide_accessibility_live_false" viewchild="slide_accessibility">
                <sr-sp h="15"></sr-sp>
                <sr-wrap id="sr_slide_accessibility_live_false" style="text-align:center" basic="" wide=""><sr-button clean="" class="sr--cta" data-action="editor.module.openGlobalAccess"><svg class="sr--icon" width="12" height="11"><use xlink:href="#Dashboard_Global"></use></svg><?php _e('Module Accessibility Settings','revslider'); ?></sr-button></sr-wrap>
            </sr-sh>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>