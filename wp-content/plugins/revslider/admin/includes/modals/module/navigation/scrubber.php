<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

$params = [
    'title' => __('Slide Title'),
    'description' => __('Slide Description')
];
for ($i = 0; $i <= 10; $i++) {
    $params['param' . $i] = __('Parameter') . ' ' . $i;
}

$markups = [
    'Sample Markup 1' => '<div class="tp-tab-content">&#013;    <span class="tp-tab-date">{{param1}}</span>&#013;    <span class="tp-tab-title">{{title}}</span>&#013;</div>&#013;<div class="tp-tab-image"></div>',
    'Sample Markup 2' => '<div class="tp-tab-content">&#013;    <span class="tp-tab-date">{{param1}}</span>&#013;    <span class="tp-tab-title">{{title}}</span>&#013;</div>&#013;<div class="tp-tab-image"></div>',
    'Sample Markup 3' => '<div class="tp-tab-content">&#013;    <span class="tp-tab-date">{{param1}}</span>&#013;    <span class="tp-tab-title">{{title}}</span>&#013;</div>&#013;<div class="tp-tab-image"></div>'
];

$elements = [
    'Tabs Container' => 'sr7-scrubber',
    'Tabs Mask' => 'sr7-tt-mask',
    'Tabs Wrapper' => 'sr7-scrubber-wrap',
    'Tab Element' => 'sr7-tab'
];

$classes = [
    'sr7-scrubber',
    'sr7-ndh',
    'sr7-nphl',
    'sr7-npvb',
    'sr7-scrubber-mask',
    'sr7-ntiw',
    'sr7-tab',
    'selected',
    'sr7-tab-content',
    'sr7-tab-date',
    'sr7-tab-title',
    'sr7-tab-img'
];

?>
<sr-modal id="sr_module_scrubber" class="sr--no--padding sr--panel--leftsidebar" view="navigationscrubber" style="min-width:360px; width:auto" hasslideout="sr7-module-slideout-wrap">
    <sr-options-menu fiveperrow>
        <sr-options-menu-innerwrap style="max-width:330px">  
            <sr-nav-btn data-sr-tabc="sr_nav_scrubber_style" data-tab-target-group="1" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="16" height="19.2" transform="translate(0,-1)"><use xlink:href="#Addon_Paintbrush"></use></svg></sr-icon-wrap><span><?php echo __('Style Skin','revslider');?></span></sr-nav-btn>  
            <sr-nav-btn data-sr-tabc="sr_nav_scrubber_layout" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Preset_Popup"></use></svg></sr-icon-wrap><span><?php echo __('Layout','revslider');?></span></sr-nav-btn>  
            <sr-nav-btn data-sr-tabc="sr_nav_scrubber_elements" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Addon_Reveal"></use></svg></sr-icon-wrap><span><?php echo __('Controls','revslider');?></span></sr-nav-btn>    
            <sr-nav-btn data-sr-tabc="sr_nav_scrubber_behavior" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Addon_Reveal"></use></svg></sr-icon-wrap><span><?php echo __('View','revslider');?></span></sr-nav-btn>    
            <sr-nav-btn data-sr-tabc="sr_nav_scrubber_editor" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Toolbar_Edit"></use></svg></sr-icon-wrap><span><?php echo __('Skin Editor','revslider');?></span></sr-nav-btn>
        </sr-options-menu-innerwrap>
    </sr-options-menu>
    <sr-modal-content> 
        <sr-wrap view="sr_nav_scrubber_style" viewchild="navigationscrubber" class="sr--tab--content sr--open" data-tab-target-group="1" id="sr_nav_scrubber_style" style="max-width:330px; width:auto">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Skins & Presets','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body class="sr_nav_scrubber_layoutuse"> 
                    <sr-wrap id="sr_nav_scrubber_essentials" class="sr--mb--10"></sr-wrap>
                    <sr-sp h="5"></sr-sp><!--
                    --><sr-wrap wide basic class="sr--form--grp sr--mb--15"><sr-onoff viewchild="sr_nav_scrubber_behavior" r="nav.scrubber.changeOnRelease" class="sr--mr--10"></sr-onoff><span><?php _e('Slide Change on Release','revslider'); ?></span></sr-wrap>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle nohover>
                    <sr-separator-title><?php _e('Settings','revslider'); ?></sr-separator-title>
                </sr-separator-head> 
                <sr-separator-body>
                    <sr-sp h="5"></sr-sp>
                    <sr-fieldset viewchild="sr_nav_scrubber_style" id="fset_preset_scrubber" data-type="single" data-source="editor.nav.presets.fieldset" data-sourceparams="scrubber" r="nav.scrubber.cst" class="sr--mb--0"></sr-fieldset>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="sr_nav_scrubber_layout" viewchild="navigationscrubber" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_scrubber_layout" style="max-width:330px; width:auto">
            <sr-separator>
                <sr-separator-body>  
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Items','revslider'); ?></sr-separator-title>  
                    </sr-separator-head>
                    <sr-input half class="sr--mr--10"><input name="Visible Amount" replace responsive="inherit" respfix="round" respshow="below"  r="nav.scrubber.m.#LEV#" viewchild="sr_nav_scrubber_layout" type="text" number="true" min="1" max="100" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Visible Amount','revslider'); ?></span></sr-input><!--
                    --><sr-input half><input name="Gaps" replace r="nav.scrubber.g" viewchild="sr_nav_scrubber_layout" type="text" number="true" min="-500" max="500" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Gaps','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body> 
            </sr-separator>
            <sr-separator>
                <sr-separator-body>
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Position','revslider'); ?></sr-separator-title>
                    </sr-separator-head>
                    <sr-tabs-wrap viewchild="sr_nav_scrubber_layout" r="nav.scrubber.io" class="sr--mb--15">
                        <sr-tab left half class="sr--active--tab" data-v="i"><?php _e('Inner','revslider'); ?></sr-tab>
                        <sr-tab right half data-v="o"><?php _e('Outer','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-tabs-wrap viewchild="sr_nav_scrubber_layout" r="nav.scrubber.d" class="sr--mb--15">
                        <sr-tab left half class="sr--active--tab" data-v="horizontal"><?php _e('Horizontal','revslider'); ?></sr-tab>
                        <sr-tab right half data-v="vertical"><?php _e('Vertical','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-tabs-wrap viewchild="sr_nav_scrubber_layout" r="nav.scrubber.a" class="sr--mb--15">
                        <sr-tab left half class="sr--active--tab" data-v="slide"><?php _e('Content Flow','revslider'); ?></sr-tab>
                        <sr-tab right half data-v="slider"><?php _e('Full Stage','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-aligner mini class="sr--mr--10" responsive respshow="below" r="nav.scrubber.v.#LEV#,nav.scrubber.h.#LEV#" viewchild="sr_nav_scrubber_layout">
<?php include(RS_PLUGIN_PATH . 'admin/includes/modals/module/navigation/parts/aligner.php'); ?>
                    </sr-aligner><!--                    
                    --><sr-wrap basic alignpickerxy class="sr--mb--15"><!--
                        --><sr-input half class="sr--mr--10"><input name="X" replace responsive="inherit" respfix="round" respshow="below"  r="nav.scrubber.x.#LEV#" viewchild="sr_nav_scrubber_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('X','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Y" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.scrubber.y.#LEV#" viewchild="sr_nav_scrubber_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Y','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body> 
            </sr-separator>
            <sr-separator>
                <sr-separator-body>  
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Size','revslider'); ?></sr-separator-title>  
                    </sr-separator-head>
                    <sr-wrap>
                        <sr-input half class="sr--mr--10"><input name="Width" replace responsive="inherit" respfix="round" respshow="below"  r="nav.scrubber.size.w.#LEV#" viewchild="sr_nav_scrubber_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Width','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Height" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.scrubber.size.h.#LEV#" viewchild="sr_nav_scrubber_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Height','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>  
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="sr_nav_scrubber_elements" viewchild="navigationscrubber" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_scrubber_elements" style="max-width:330px; width:auto">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Handle Arrows','revslider'); ?></sr-separator-title>
                    <sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_scrubber_arrow" r="nav.scrubber.ha" viewchild="sr_nav_scrubber_elements"></sr-onoff>
                </sr-separator-head>
                <sr-separator-body class="sr_elements_scrubber_arrow">
                    <sr-wrap basic half class="sr--form--grp sr--mb--15"><sr-onoff viewchild="sr_nav_scrubber_elements" r="nav.scrubber.f" class="sr--mr--10"></sr-onoff><span><?php _e('Fade','revslider'); ?></span></sr-wrap><!--
                    --><sr-wrap basic half class="sr--form--grp sr--mb--15"><sr-onoff viewchild="sr_nav_scrubber_elements" r="nav.scrubber.asc" class="sr--mr--10"></sr-onoff><span><?php _e('Scale','revslider'); ?></span></sr-wrap><!--
                    --><sr-wrap basic half class="sr--form--grp sr--mb--15 sr--mr--10"><sr-onoff viewchild="sr_nav_scrubber_elements" r="nav.scrubber.sp" class="sr--mr--10"></sr-onoff><span><?php _e('Spin','revslider'); ?></span></sr-wrap><!--
                    --><sr-input half><input name="Translate" replace r="nav.scrubber.tr" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="-1000" max="1000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Translate','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Handle Settings','revslider'); ?></sr-separator-title>
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap basic half class="sr--form--grp sr--mb--15"><sr-onoff viewchild="sr_nav_scrubber_elements" r="nav.scrubber.tn" class="sr--mr--10"></sr-onoff><span><?php _e('Thumbnails','revslider'); ?></span></sr-wrap><!--
                    --><sr-wrap basic half class="sr--form--grp sr--mb--15"><sr-onoff viewchild="sr_nav_scrubber_elements" r="nav.scrubber.ho" class="sr--mr--10"></sr-onoff><span><?php _e('Hover Scale','revslider'); ?></span></sr-wrap><!--
                    --><sr-input half class="sr--mr--10"><input name="Skew" replace r="nav.scrubber.sk" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="-100" max="100" suffix="deg" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Skew','revslider'); ?></span></sr-input><!--
                    --><sr-input half><input name="Thumb Offset" replace r="nav.scrubber.to" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="0" max="500" suffix="%" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Thumb Offset','revslider'); ?></span></sr-input><!--
                    --><sr-input half class="sr--mr--10"><input name="Thumb Scale" replace r="nav.scrubber.ts" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="100" max="1000" suffix="%" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Thumb Scale','revslider'); ?></span></sr-input><!--
                    --><sr-input half><input name="Handle Scale" replace r="nav.scrubber.hs" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="100" max="1000" suffix="%" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Handle Scale','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Show Progress','revslider'); ?></sr-separator-title>
                    <sr-onoff class="sr--mr--0" style="right:0px" data-sh=".sr_elements_scrubber_progress" r="nav.scrubber.hp" viewchild="sr_nav_scrubber_elements"></sr-onoff>
                </sr-separator-head>
                <sr-separator-body class="sr_elements_scrubber_progress">
                    <sr-tabs-wrap viewchild="sr_nav_scrubber_elements" r="nav.scrubber.pa" class="sr--mb--15">
                        <sr-tab left onethird class="sr--active--tab" data-v="inner"><?php _e('Left','revslider'); ?></sr-tab>
                        <sr-tab left onethird data-v="inner"><?php _e('Center','revslider'); ?></sr-tab>
                        <sr-tab right onethird data-v="outer"><?php _e('Right','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>            
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Wrapper','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body>
                    <sr-drop wide data-v="dots" r="nav.scrubber.tp" viewchild="sr_nav_scrubber_elements" data-onchange="editor.nav.preview.recreate" data-onchangeparams="scrubber">
                        <sr-drop-view>
                            <span class="sr--drop--value"></span>
                            <span class="sr--form--otitle"><?php _e('Track Pattern','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
                        <sr-drops data-v="dots"><?php _e('Dots','revslider'); ?></sr-drops>
                        <sr-drops data-v="vlines"><?php _e('Vertical Lines','revslider'); ?></sr-drops>
                        <sr-drops data-v="hlines"><?php _e('Horizontal Lines','revslider'); ?></sr-drops>
                    </sr-drop>
                    <sr-input wide><input name="Idle Track Size" replace r="nav.scrubber.sc" viewchild="sr_nav_scrubber_elements" data-onchange="editor.nav.preview.recreate" data-onchangeparams="scrubber" type="text" number="true" min="10" max="100" suffix="%" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Idle Track Size','revslider'); ?></span></sr-input>
                    <sr-wrap class="sr--form--grp" half><sr-color-mini data-v="transparent" r="nav.scrubber.wr.c" data-type="background" class="sr--mr--10" viewchild="sr_nav_scrubber_elements"></sr-color-mini><span class="sr--mr--30"><?php _e('Background','revslider'); ?></span></sr-wrap><!--
                    --><sr-wrap basic half class="sr--form--grp sr--mb--15"><sr-onoff viewchild="sr_nav_scrubber_elements" r="nav.scrubber.wr.s" class="sr--mr--10"></sr-onoff><span><?php _e('Span','revslider'); ?></span></sr-wrap><!--
                    --><sr-input half class="sr--mr--7"><input name="Padding" replace responsive="inherit" respfix="round" respshow="below" r="nav.scrubber.wr.p.#LEV#" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="0" max="500" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Padding','revslider'); ?></span></sr-input><!--
                    --><sr-input half class="sr--mr--0"><input name="Min. Size" replace r="nav.scrubber.ms" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="0" max="100" suffix="%" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Min. Size','revslider'); ?></span></sr-input><!--
                    --><sr-input half class="sr--mr--7"><input name="Offset X" replace responsive="inherit" respfix="round" respshow="below"  r="nav.scrubber.wr.mx.#LEV#" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Offset X','revslider'); ?></span></sr-input><!--
                    --><sr-input half><input name="Offset Y" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.scrubber.wr.my.#LEV#" viewchild="sr_nav_scrubber_elements" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Offset Y','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>        
        <sr-wrap view="sr_nav_scrubber_behavior" viewchild="navigationscrubber" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_scrubber_behavior" style="max-width:330px; width:auto">
            <sr-separator>                  
                <sr-separator-body>
                    <sr-sp h="20"></sr-sp>
                    <sr-drop wide data-v="" r="nav.scrubber.anim" viewchild="sr_nav_scrubber_behavior" class="sr--mr--10">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('In / Out Animation','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
<?php include(RS_PLUGIN_PATH . 'admin/includes/modals/module/navigation/parts/anim-options.php'); ?>
                    </sr-drop>
                    <sr-input wide class="sr--mr--10"><input name="Animation Speed" replace r="nav.scrubber.s" viewchild="sr_nav_scrubber_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Animation Speed','revslider'); ?></span></sr-input>
                    <sr-input wide class="sr--mr--10"><input name="Delay to Show" replace responsive="inherit" respfix="round" respshow="below"  r="nav.scrubber.dIn.#LEV#" viewchild="sr_nav_scrubber_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Delay to Show','revslider'); ?></span></sr-input>                    
                    <sr-drop  wide multiselect="truefalse" multilen="5" usecheck="" r="nav.scrubber.show" viewchild="sr_nav_scrubber_behavior" data-v="" dropsw="190" dropsh="200"> 
<?php include(RS_PLUGIN_PATH . 'admin/includes/modals/module/navigation/parts/visibility.php'); ?>
                    </sr-drop> 
                    
                </sr-separator-body>
            </sr-separator>            
            <sr-separator>  
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Keep Visible','revslider'); ?></sr-separator-title>
                    <sr-onoff r="nav.scrubber.on" viewchild="sr_nav_scrubber_behavior" data-sh=".sr_nav_tab_hide" data-shdep="!checked" class="sr--mr--0" style="right:0px"></sr-onoff>
                </sr-separator-head>
                <sr-separator-body class="sr_nav_tab_hide sr--mb--0">                                    
                    <sr-input wide class="sr--mb--0"><input name="Delay to Hide" replace class="sr--mb--0" responsive="inherit" respfix="round"  respshow="below"  r="nav.scrubber.dOut.#LEV#" viewchild="sr_nav_scrubber_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Delay to Hide','revslider'); ?></span></sr-input> 
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>  

        <sr-wrap view="sr_nav_scrubber_editor" viewchild="navigationscrubber" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_scrubber_editor" style="width:auto">
            <sr-panel-invers topborder dark>
                <sr-sp h="5"></sr-sp>
                <sr-tabs-wrap wrap="">
                    <sr-tab data-sr-tabc="sr_nav_scrubber_css" data-tab-target-group="2" class="sr--tab--call" ltop="" onethird="">CSS</sr-tab>
                    <sr-tab data-sr-tabc="sr_nav_scrubber_markup" data-tab-target-group="2" class="sr--tab--call" none="" onethird="">HTML Markup</sr-tab>  
                    <sr-tab data-sr-tabc="sr_nav_scrubber_metas" data-tab-target-group="2" class="sr--tab--call selected sr--active--tab" none="" onethird="">Meta List</sr-tab>  
                </sr-tabs-wrap>
                <sr-sp h="5"></sr-sp>
                <sr-wrap style="position:absolute; right:0px; top:-53px; width:200px;">  
                    <sr-drop id="sr_nav_scrubber_skin_drop" wide                    
                                r="nav.scrubber.t" viewchild="sr_nav_scrubber_editor" 
                                data-pver="bottom"                                
                                data-onlyexport="true"
                                data-source="navigation" data-source-type="scrubber"
                                data-type="preset" data-typelbl="<?php _e('New Skin','revslider'); ?>"                                 
                                data-onchange="editor.nav.skin.change" 
                                data-onpreset="editor.nav.skin.add" data-onpresetextend="editor.nav.skin.extendOption" data-onpresetparams="scrubber" 
                                dropsw="400" dropsh="250">
                        <sr-drop-view>
                            <sr-lbl medium="" class="sr--bad sr--bold sr--mr--10">!</sr-lbl>
                            <span class="sr--drop--value"></span>
                            <span class="sr--form--otitle"></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>  
                    </sr-drop>   
                </sr-wrap>
            </sr-panel-invers>
            <sr7-module-slideout-wrap view="nav_scrubber_css_codesnippets" viewchild="sr_nav_scrubber_editor" class="sr--tab--content" data-tab-target-group="2" data-rel-id="sr_nav_scrubber_css" id="nav_scrubber_css_codesnippets">
                <sr-options-menu fourperrow>
                    <sr-nav-btn data-sr-tabc="sr_snippets_scrubber_css_meta" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Meta','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_scrubber_css_elements" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Selectors','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_scrubber_css_classes" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Classes','revslider');?></span></sr-nav-btn>
                </sr-options-menu>
                <sr7-module-slideout-content>
                    <sr-wrap view="codesnippets_meta" viewchild="nav_scrubber_css_codesnippets" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_scrubber_css_meta">
                        <sr-fieldset viewchild="codesnippets_meta" class="sr--tab--content--wrap" id="fs_metas_scrubber_snippets" data-type="single" data-source="editor.nav.metas.snippets" data-sourceparams="scrubber" data-r="nav.scrubber.css" ></sr-fieldset>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_elements" viewchild="sr_nav_scrubber_metas" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_scrubber_css_elements">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($elements as $label => $element) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams="<?php echo $element; ?> {&#013;}&#013;" data-t="nav.scrubber.css" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_classes" viewchild="nav_scrubber_css_codesnippets" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_scrubber_css_classes">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($classes as $class) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo $class;?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams=".<?php echo $class; ?> {&#013;}&#013;" data-t="nav.scrubber.css" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                </sr7-module-slideout-content>
            </sr7-module-slideout-wrap>
            <sr7-module-slideout-wrap view="nav_scrubber_markup_codesnippets" class="sr--tab--content" data-tab-target-group="2" data-rel-id="sr_nav_scrubber_markup" id="nav_scrubber_markup_codesnippets">
                <sr-options-menu fourperrow>
                    <sr-nav-btn data-sr-tabc="sr_snippets_scrubber_html_meta" data-tab-target-group="arrmarkup" class="sr--tab--call"><span><?php echo __('Meta','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_scrubber_html_markups" data-tab-target-group="arrmarkup" class="sr--tab--call"><span><?php echo __('Markup','revslider');?></span></sr-nav-btn>
                </sr-options-menu>
                <sr7-module-slideout-content>
                    <sr-wrap view="codesnippets_content" viewchild="nav_scrubber_markup_codesnippets" data-tab-target-group="arrmarkup" class="sr--tab--content" id="sr_snippets_scrubber_html_meta">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($params as $param => $label) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams="{{<?php echo $param; ?>}}" data-t="nav.scrubber.html" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_markups" viewchild="nav_scrubber_markup_codesnippets" data-tab-target-group="arrmarkup" class="sr--tab--content" id="sr_snippets_scrubber_html_markups">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($markups as $label => $markup) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams='<?php echo $markup; ?>&#013;' data-t="nav.scrubber.html" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                </sr7-module-slideout-content>
            </sr7-module-slideout-wrap>
            <sr-wrap view="sr_nav_scrubber_css" viewchild="sr_nav_scrubber_editor" class="sr--tab--content" data-tab-target-group="2" id="sr_nav_scrubber_css">
                <sr-wrap style="margin:0px -15px">
                    <textarea name="Scrubber CSS Code" codemirror="true" data-w="570" data-mode="css"  data-onchange="editor.nav.metas.changed" data-onchangeparams="scrubber" style="width:570px; height:500px;" r="nav.scrubber.css" viewchild="sr_nav_scrubber_css"></textarea>
                </sr-wrap>   
            </sr-wrap>
            <sr-wrap view="sr_nav_scrubber_markup" viewchild="sr_nav_scrubber_editor" class="sr--tab--content" data-tab-target-group="2" id="sr_nav_scrubber_markup">
                <sr-wrap style="margin:0px -15px">
                    <textarea name="Scrubber HTML Code" codemirror="true" data-w="570" data-mode="htmlembedded"  data-onchange="editor.nav.metas.changed" data-onchangeparams="scrubber" style="width:570px;height:500px;" r="nav.scrubber.html" viewchild="sr_nav_scrubber_markup"></textarea>
                </sr-wrap>
            </sr-wrap>
            <sr-wrap view="sr_nav_scrubber_metas" viewchild="sr_nav_scrubber_editor" class="sr--tab--content sr--open" data-tab-target-group="2" id="sr_nav_scrubber_metas" style="width:540px">
                <sr-separator>                    
                    <sr-separator-body>
                    <sr-sp h="20"></sr-sp>
                    <sr-input half class="sr--mr--10"><input name="Default Width" replace r="nav.scrubber.ddim.w" viewchild="sr_nav_scrubber_metas" type="text" data-onchange="editor.nav.metas.changed" data-onchangeparams="scrubber" number="true" min="0" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Default Width','revslider'); ?></span></sr-input><!--
                    --><sr-input half><input name="Default Height" replace r="nav.scrubber.ddim.h" viewchild="sr_nav_scrubber_metas" type="text" data-onchange="editor.nav.metas.changed" data-onchangeparams="scrubber" number="true" min="0" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Default Height','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator>                    
                    <sr-separator-body>   
                        <sr-sp h="20"></sr-sp> 
                        <sr-fieldset viewchild="sr_nav_scrubber_metas" id="fset_metas_scrubber" data-type="single" data-source="editor.nav.metas.fieldset" data-sourceparams="scrubber" r="nav.scrubber.def" class="sr--mb--0"></sr-fieldset> 
                        <sr-separator-line class="sr--mb--15"></sr-separator-line>
                        <!--<sr-panel-invers>    -->
                            <sr-wrap basic class="sr--mb--15">
                                <sr-input style="width:calc(100% * 7/15 + 5px);" class="sr--mr--13 sr--mb--0"><input id="sr_nav_add_meta_scrubber_handle" replace type="text" class="sr--mb--0"><span noicon="" class="sr--form--otitle"><?php _e('Meta Handle','revslider'); ?></span></sr-input><!--
                                --><sr-drop style="width:calc(100% * 4/18);" class="sr--mr--10 sr--mb--0" id="sr_nav_add_meta_scrubber_type">
                                    <sr-drop-view>
                                        <span class="sr--drop--value"><?php _e('Meta','revslider'); ?></span>
                                        <span class="sr--form--otitle"><?php _e('Type','revslider'); ?></span>
                                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                                    </sr-drop-view>  
                                    <sr-drops data-v="custom"><?php _e('Custom','revslider'); ?></sr-drops>
                                    <sr-drops data-v="color"><?php _e('Color','revslider'); ?></sr-drops>
                                    <sr-drops data-v="icon"><?php _e('Icon','revslider'); ?></sr-drops>
                                    <sr-drops data-v="font-family"><?php _e('Font','revslider'); ?></sr-drops>    
                                </sr-drop> 
                                <sr-button data-action="editor.nav.metas.add" data-aparams="scrubber" primary="" class="sr--cta sr--mb--0">Add Meta</sr-button>
                            </sr-wrap>

                        <!--</sr-panel-invers>-->
                    </sr-separator-body>
                </sr-separator>
            </sr-wrap>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>