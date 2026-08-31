<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_general" class="sr--no--padding sr--panel--leftsidebar" view="modulesettings" style="width:320px">
    <sr-options-menu fourperrow>
        <sr-nav-btn data-sr-tabc="sr_mose_naming" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="16" height="12.31"><use xlink:href="#Dashboard_Rename"></use></svg></sr-icon-wrap><span><?php echo __('Basic','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mose_contentflow" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20" height="18" transform="translate(0,-1)"><use xlink:href="#Toolbar_Content_Flow"></use></svg></sr-icon-wrap><span><?php echo __('Content Flow','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_mose_html" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="18" height="17" transform="translate(0,-1)"><use xlink:href="#Dashboard_Global"></use></svg></sr-icon-wrap><span><?php echo __('Advanced','revslider');?></span></sr-nav-btn>  
    </sr-options-menu>
    <sr-modal-content>
        <!-- 
            SLIDE THUMBNAIL SETTINGS 
        -->
        <sr-wrap view="module_naming" viewchild="modulesettings" class="sr--tab--content sr--open" id="sr_mose_naming">
            <sr-separator>                
                <sr-separator-body>
                    <sr-sp h="15"></sr-sp><!--
                    --><sr-wrap inline id="sr_module_titlealias_wrap" style="width:232px;margin-right:1px;vertical-align:top">
                        <sr-input id="sr_module_title_wrap" wide><input name="Module Title" replace r="title" viewchild="module_naming" type="text" style="padding-right:35px"><span noicon="" class="sr--form--otitle"><?php _e('Title','revslider'); ?></span></sr-input>                        
                        <sr-input id="sr_module_alias_wrap" wide><input name="Module Alias" data-onchange="editor.module.checkAlias" replace r="alias" style="padding-right:39px" viewchild="module_naming" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Alias','revslider'); ?></span></sr-input>
                    </sr-wrap><!--
                    --><sr-wrap inline style="width:57px;vertical-align:top">
                        <sr-button data-action="editor.module.aliastoggle" class="sr--sh--icon sr--mr--1"><svg class="sr--icon" width="14" height="12.808" transform="translate(0, -2)"><use xlink:href="#Hash"></use></svg></sr-button><!--
                        --><sr-button data-action="editor.module.embed" class="sr--sh--icon sr--mr--0 sr--ml--0"><svg class="sr--icon" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#Publish"></use></svg></sr-button>
                    </sr-wrap>
                    <sr-sp h="3"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Type','revslider'); ?></sr-separator-title>
                </sr-separator-head>
                <sr-separator-body>
                    <sr-radio r="type" viewchild="module_naming" data-onchange="editor.module.submenu,stage.dims.redrawFull+100,editor.module.carouselCheck" data-onundoredo="editor.module.submenu,stage.dims.redrawFull+100" class="sr--mb--10">
                            <sr-radio-item class="sr--mtype--sel" value="hero"><span class="sr--icon--wrap"><svg class="sr--icon" width="22" height="17.01" transform="translate(0, -2)"><use xlink:href="#Addon_Panorama"></use></svg></span><span><?php _e('Hero','revslider'); ?></span></sr-radio-item><!--
                            --><sr-radio-item class="sr--mtype--sel" value="standard"><span class="sr--icon--wrap"><svg class="sr--icon" width="25" height="15.975" transform="translate(0, -2)"><use xlink:href="#Dashboard_Slides"></use></svg></span><span><?php _e('Slider','revslider'); ?></span></sr-radio-item><!--
                            --><sr-radio-item class="sr--mtype--sel" value="carousel"><span class="sr--icon--wrap"><svg class="sr--icon" width="25" height="17.105" transform="translate(0, -2)"><use xlink:href="#Carousel"></use></svg></span><span><?php _e('Carousel','revslider'); ?></span></sr-radio-item>  
                    </sr-radio>
                    <sr-sp h="10"></sr-sp>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="size.fullWidth" viewchild="module_naming" class="sr--mr--10 checked" data-onchange="stage.dims.prepareLevels" data-onchangeparams="force" data-shaction="editor.module.minMaxDimCheck"></sr-onoff><span><?php _e('Full Width','revslider'); ?></span><sr-tooltip key="fullwidth"></sr-tooltip></sr-wrap>
                    <sr-wrap basic class="sr--form--grp  sr--mb--10 "><sr-onoff r="size.fullHeight" viewchild="module_naming" class="sr--mr--10 checked" data-onchange="stage.dims.prepareLevels" data-onchangeparams="force" data-shaction="editor.module.minMaxDimCheck"></sr-onoff><span><?php _e('Full Height','revslider'); ?></span><sr-tooltip key="fullheight"></sr-tooltip></sr-wrap>
                    <sr-sp h="10"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Responsive Behavior','revslider'); ?></sr-separator-title>                    
                </sr-separator-head>
                <sr-separator-body>
                <sr-wrap basic class="sr--form--grp"><sr-onoff r="size.keepBPHeight" data-onchange="stage.dims.prepareLevels" data-onchangeparams="force" viewchild="module_naming" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Fixed Breakpoint Heights','revslider'); ?></span><sr-tooltip key="keepbreakpoint"></sr-tooltip></sr-wrap> 
                <sr-wrap basic class="sr--form--grp"><sr-onoff r="size.respectRatio" data-onchange="stage.dims.prepareLevels" data-onchangeparams="force" viewchild="module_naming" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Keep Aspect Ratio','revslider'); ?></span><sr-tooltip key="keepratio"></sr-tooltip></sr-wrap> 
                <sr-wrap basic class="sr--form--grp" id="sr_module_upscaling"><sr-onoff r="size.upscaling" viewchild="module_naming" data-onchange="stage.dims.prepareLevels" data-onchangeparams="force" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Layer Upscaling','revslider'); ?></span><sr-tooltip key="layerupscaling"></sr-tooltip></sr-wrap> 
                <sr-wrap basic class="sr--form--grp" id="sr_module_keepflow"><sr-onoff r="size.keepFlow" viewchild="module_naming" data-onchange="stage.dims.prepareLevels" data-onchangeparams="force" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Keep Content Flow Width','revslider'); ?></span><sr-tooltip key="keepflow"></sr-tooltip></sr-wrap> 
                <sr-wrap basic class="sr--form--grp" id="sr_module_urljumpfix"><sr-onoff r="mobileURLJumpFix" viewchild="module_naming" data-onchange="stage.dims.prepareLevels" data-onchangeparams="force" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Ignore Mobile Height Changes','revslider'); ?></span><sr-tooltip key="ignoremobileheight"></sr-tooltip></sr-wrap>
                <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Size Limitations','revslider'); ?></sr-separator-title>                    
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap id="sr_module_fullheightdecr">
                        <sr-wrap basic class="sr--form--grp"><sr-onoff r="size.FHOU" data-sh="#sr_module_fullheightdecr_inp" viewchild="module_naming" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Decrease Module Height','revslider'); ?></span><sr-tooltip key="decreasemoduleheight"></sr-tooltip></sr-wrap> 
                        <sr-wrap id="sr_module_fullheightdecr_inp" class="sr--mt--10 sr--mb--0" basic><sr-input wide textblock class="sr--mb--0"><textarea name="Full Height Offset Containers" style="height:28px;line-height:28px;vertical-align:top" class="sr--mb--0" r="size.fullHeightOffset" viewchild="module_naming"></textarea><span noicon="" class="sr--form--otitle" style="bottom:0px"><?php _e('#topbar,.content,5px','revslider'); ?></span></sr-input><sr-sp h="12"></sr-sp></sr-wrap>
                    </sr-wrap>
                    <sr-wrap basic><!--
                        --><sr-wrap basic class="sr--form--grp sr--mb--0"><sr-onoff r="size.MMOU" data-sh="#sr_module_mmsizes_inp" viewchild="module_naming" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Min/Max Limitations','revslider'); ?></span><sr-tooltip key="minmaxmodulesizes"></sr-tooltip></sr-wrap>
                        <sr-sp h="5"></sr-sp><!--
                        --><sr-wrap id="sr_module_mmsizes_inp"><!--                            
                            --><sr-sp h="5"></sr-sp><!--
                            --><sr-input wide id="sr_module_maxwidth" class="sr--mb--10"><input name="Max Width" replace r="size.maxWidth" class="sr--mb--0" viewchild="module_naming" type="text" number="true" min="0" max="2400" suffix="px" fallback="none" zerotonone validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Max. Width','revslider'); ?></span></sr-input><!--                            
                            --><sr-input half id="sr_module_minheight" class="sr--mr--10 sr--mb--10"><input name="Min Height" class="sr--mb--0"  replace responsive r="size.minHeight.#LEV#" viewchild="module_naming" type="text" number="true" min="0" max="50000" suffix="px" fallback="none" zerotonone validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Min. Height','revslider'); ?></span></sr-input><!--
                            --><sr-input half id="sr_module_maxheight" class="sr--mb--10"><input name="Max Height" class="sr--mb--0"  replace responsive r="size.maxHeight.#LEV#" viewchild="module_naming" type="text" number="true" min="0" max="50000" suffix="px" fallback="none" zerotonone validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Max. Height','revslider'); ?></span></sr-input><!--
                            --><sr-sp h="0"></sr-sp>
                        </sr-wrap>
                    </sr-wrap>                                        
                    <sr-sp h="15"></sr-sp>
                </sr-separator-body>
            </sr-separator>             
        </sr-wrap>
        <sr-wrap view="module_cfc" viewchild="modulesettings" class="sr--tab--content" id="sr_mose_contentflow">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Content Flow Container','revslider'); ?></sr-separator-title>                    
                </sr-separator-head>
                <sr-separator-body class="sr--modulesizes">
                    <sr-wrap>                        
                        <sr-wrap class="sr_cfc_lev0" dropicon><svg class="sr--icon" width="24" height="14" transform="translate(0, -10)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></sr-wrap><!--
                        --><sr-input onethird class="sr--mr--6 sr_cfc_lev0"><input name="Wide Desktop Width" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.width.0" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="2400" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('W','revslider'); ?></span></sr-input><!--
                        --><sr-input onethird class="sr--mr--20 sr_cfc_lev0"><input name="Wide Desktop Height" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.height.0" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="50000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('H','revslider'); ?></span></sr-input><!--
                        --><sr-wrap inline basic class="sr--form--grp"><sr-onoff data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" data-ed=".sr_cfc_lev0" data-eddep="checked" r="uSize.0" viewchild="module_cfc" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                    </sr-wrap>
                    <sr-wrap>
                        <sr-wrap class="sr_cfc_lev1" dropicon><svg class="sr--icon" width="22" height="18" transform="translate(0, -10)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></sr-wrap><!--
                        --><sr-input onethird class="sr--mr--6 sr_cfc_lev1"><input name="Desktop Width" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.width.1" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="2400" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('W','revslider'); ?></span></sr-input><!--
                        --><sr-input onethird class="sr--mr--20 sr_cfc_lev1"><input name="Desktop Height" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.height.1" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="50000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('H','revslider'); ?></span></sr-input><!--                        
                        --><sr-wrap  inline basic class="sr--form--grp"><sr-onoff style="pointer-events:none" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                    </sr-wrap>
                    <sr-wrap>
                        <sr-wrap class="sr_cfc_lev2" dropicon><svg class="sr--icon" width="22" height="16" transform="translate(0, -10)"><use xlink:href="#Top_Bar_Laptop"></use></svg></sr-wrap><!--
                        --><sr-input onethird class="sr--mr--6 sr_cfc_lev2"><input name="Notebook Width" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.width.2" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="2400" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('W','revslider'); ?></span></sr-input><!--
                        --><sr-input onethird class="sr--mr--20 sr_cfc_lev2"><input name="Notebook Height" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.height.2"data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="50000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('H','revslider'); ?></span></sr-input><!--                        
                        --><sr-wrap inline basic class="sr--form--grp"><sr-onoff data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" data-ed=".sr_cfc_lev2" data-eddep="checked" r="uSize.2" viewchild="module_cfc" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                    </sr-wrap>
                    <sr-wrap>
                        <sr-wrap class="sr_cfc_lev3" dropicon><svg class="sr--icon" width="20" height="24" transform="translate(0, -10)"><use xlink:href="#Top_Bar_Tablet"></use></svg></sr-wrap><!--
                        --><sr-input onethird class="sr--mr--6 sr_cfc_lev3"><input name="Tablet Width" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.width.3" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="2400" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('W','revslider'); ?></span></sr-input><!--
                        --><sr-input onethird class="sr--mr--20 sr_cfc_lev3"><input name="Tablet Height" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.height.3" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="50000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('H','revslider'); ?></span></sr-input><!--                        
                        --><sr-wrap inline basic class="sr--form--grp"><sr-onoff data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" data-ed=".sr_cfc_lev3" data-eddep="checked" r="uSize.3" viewchild="module_cfc" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                    </sr-wrap>
                    <sr-wrap>
                        <sr-wrap class="sr_cfc_lev4" dropicon><svg class="sr--icon" width="14" height="20" transform="translate(0, -10)"><use xlink:href="#Top_Bar_Phone"></use></svg></sr-wrap><!--
                        --><sr-input onethird class="sr--mr--6 sr_cfc_lev4"><input name="Mobile Width" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.width.4" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="2400" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('W','revslider'); ?></span></sr-input><!--
                        --><sr-input onethird class="sr--mr--20 sr_cfc_lev4"><input name="Mobile Height" respcalc="stage.dims.calcAuto" populate livevisup autocomplete="off" replace r="size.height.4" data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" viewchild="module_cfc" type="text" number="true" min="0" max="50000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('H','revslider'); ?></span></sr-input><!--
                        --><sr-wrap inline basic class="sr--form--grp"><sr-onoff data-onchange="stage.dims.prepareLevels,stage.dims.redrawFull+100" data-onchangeparams="force" data-ed=".sr_cfc_lev4" data-eddep="checked" r="uSize.4" viewchild="module_cfc" class="sr--ml--10 checked"></sr-onoff></sr-wrap>
                    </sr-wrap>
                <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>                        
        </sr-wrap>        
        <sr-wrap view="module_html" viewchild="modulesettings" class="sr--tab--content" id="sr_mose_html">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('HTML Settings','revslider'); ?></sr-separator-title>                    
                </sr-separator-head>
                <sr-separator-body>
                    <sr-sp h="5"></sr-sp>
                    <sr-input wide><input name="HTML Id" replace r="id" viewchild="module_html" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Module ID','revslider'); ?></span></sr-input>
                    <sr-input wide><input name="HTML Class" replace r="class" viewchild="module_html" type="text" style="padding-right:70px"><span noicon="" class="sr--form--otitle"><?php _e('Module Class','revslider'); ?></span></sr-input>
                    <sr-input wide><input name="Wrapper Class" replace r="wClass" viewchild="module_html" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Wrapper Class','revslider'); ?></span></sr-input>
                    <sr-wrap basic class="sr--form--grp"><sr-onoff r="uS" viewchild="module_html" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Allow Layer Selection','revslider'); ?></span></sr-wrap>
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>                
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Module Spacing','revslider'); ?></sr-separator-title>
                    <sr-onoff style="right:0px" class="sr--mr--0" data-sh=".sr_modulespaces" r="size.MSOU" viewchild="module_html"></sr-onoff>
                </sr-separator-head>
                <sr-separator-body class="sr_modulespaces">
                    <sr-sp h="5"></sr-sp>
                    <sr-bmp id="sr_module_margin_row" type="margin" topbottom responsive respshow="below" r="size.m" viewchild="module_html"></sr-bmp>
                    <sr-bmp type="padding" responsive respshow="below" r="size.p" viewchild="module_html"></sr-bmp>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Module Position','revslider'); ?></sr-separator-title>
                    <sr-onoff style="right:0px" class="sr--mr--0" data-sh=".sr_moduleposition" r="size.MPOU" viewchild="module_html"></sr-onoff>
                </sr-separator-head>
                <sr-separator-body class="sr_moduleposition">
                    <sr-sp h="5"></sr-sp>
                    <sr-drop wide data-v="none" r="sticky" data-onchange="editor.module.syncSticky" viewchild="module_html">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Sticky','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
                        <sr-drops data-v="top"><?php _e('Top (i.e. Menu)','revslider'); ?></sr-drops>
                        <sr-drops data-v="bottom"><?php _e('Bottom (i.e. Info Bar)','revslider'); ?></sr-drops>
                    </sr-drop>
                    <sr-drop wide data-v="true" r="size.overflow" viewchild="module_html">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Visible','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Overflow','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>                    
                        <sr-drops data-v="true"><?php _e('Visible','revslider'); ?></sr-drops>                    
                        <sr-drops data-v="false"><?php _e('Hidden','revslider'); ?></sr-drops>
                    </sr-drop>
                    <sr-wrap basic class="sr--form--grp sr--mt--5">
                        <sr-onoff r="size.stickyReserve" viewchild="module_html" class="sr--mr--10"></sr-onoff>
                        <span><?php _e('Reserve Space','revslider'); ?></span>
                        <span class="sr--form--otitle"><?php _e('(for page content)','revslider'); ?></span>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('3D Perspective','revslider'); ?></sr-separator-title>
                    <sr-onoff style="right:0px" class="sr--mr--0" data-sh=".sr_module3d" r="general.D3OU" viewchild="module_html"></sr-onoff>
                </sr-separator-head>
                <sr-separator-body class="sr_module3d"> 
                    <sr-sp h="5"></sr-sp>                   
                    <sr-drop wide data-v="isometric" r="general.perspectiveType" data-sh="#modal_glbl_persp" data-shdep="global" viewchild="module_html">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Isometric (All Layers)','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Projection','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>                    
                        <sr-drops data-v="isometric"><?php _e('Isometric (All Layers)','revslider'); ?></sr-drops>
                        <sr-drops data-v="global"><?php _e('Perspective (All Layers)','revslider'); ?></sr-drops>
                        <sr-drops data-v="local"><?php _e('Perspective (Per Layer)','revslider'); ?></sr-drops>
                    </sr-drop>
                    <sr-input wide id="modal_glbl_persp"><input name="Module General Perspective" replace r="general.perspective" viewchild="module_html" type="text" number="true" min="0" max="2400" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('All Layers','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Layer Scaling Between Devices','revslider'); ?></sr-separator-title>  
                    <sr-onoff style="right:0px" class="sr--mr--0" data-sh=".sr_moduledeflayerbehave" r="default.LROU" viewchild="module_html"></sr-onoff>                  
                </sr-separator-head>
                <sr-separator-body class="sr_moduledeflayerbehave">				
                    <sr-wrap half basic="" class="sr--form--grp sr--mr--10"><sr-onoff r="default.fluid.po" viewchild="module_html" class="sr--mr--10"></sr-onoff><span><?php _e('Position','revslider'); ?></span></sr-wrap><!--
                    --><sr-wrap half basic="" class="sr--form--grp"><sr-onoff r="default.fluid.tr" viewchild="module_html" class="sr--mr--10"></sr-onoff><span><?php _e('Size & Motion','revslider'); ?></span></sr-wrap>
                    <sr-wrap half basic="" class="sr--form--grp sr--mr--10"><sr-onoff r="default.fluid.tx" viewchild="module_html" class="sr--mr--10"></sr-onoff><span><?php _e('Text','revslider'); ?></span></sr-wrap><!--
                    --><sr-wrap-dep half dep="is[text]"><sr-wrap basic="" class="sr--form--grp"><sr-onoff r="default.fluid.sp" viewchild="module_html" class="sr--mr--10"></sr-onoff><span><?php _e('Padding','revslider'); ?></span></sr-wrap></sr-wrap-dep>				
                    <sr-sp h="15"></sr-sp>
                </sr-separator-body>               
            </sr-separator>
        </sr-wrap>
    </sr-modal-content>
</sr-modal> 
