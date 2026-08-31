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
    'Sample Markup 1' => '<span class="tp-thumb-img-wrap">&#013;    <span class="tp-thumb-image"></span>&#013;</span>',
    'Sample Markup 2' => '<span class="tp-thumb-img-wrap">&#013;    <span class="tp-thumb-image"></span>&#013;</span>',
    'Sample Markup 3' => '<span class="tp-thumb-img-wrap">&#013;    <span class="tp-thumb-image"></span>&#013;</span>'
];

$elements = [
    'Thumbs Container' => 'sr7-thumbs',
    'Thumbs Mask' => 'sr7-tt-mask',
    'Thumbs Wrapper' => 'sr7-thumbs-wrap',
    'Thumb Element' => 'sr7-thumb'
];

$classes = [
    'sr7-thumbs',
    'sr7-ndh',
    'sr7-nphc',
    'sr7-npvb',
    'sr7-thumbs-mask',
    'sr7-ntiw',
    'sr7-thumb',
    'sr7-thumb-img-wrap'
];

?>
<sr-modal id="sr_module_thumbs" class="sr--no--padding sr--panel--leftsidebar" view="navigationthumbs" style="min-width:360px; width:auto" hasslideout="sr7-module-slideout-wrap">
    <sr-options-menu fiveperrow>
        <sr-options-menu-innerwrap style="max-width:330px">  
            <sr-nav-btn data-sr-tabc="sr_nav_thumbs_style" data-tab-target-group="1" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="16" height="19.2" transform="translate(0,-1)"><use xlink:href="#Addon_Paintbrush"></use></svg></sr-icon-wrap><span><?php echo __('Skin & Style','revslider');?></span></sr-nav-btn>  
            <sr-nav-btn data-sr-tabc="sr_nav_thumbs_layout" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Preset_Popup"></use></svg></sr-icon-wrap><span><?php echo __('Layout','revslider');?></span></sr-nav-btn>  
            <sr-nav-btn data-sr-tabc="sr_nav_thumbs_behavior" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Addon_Reveal"></use></svg></sr-icon-wrap><span><?php echo __('View','revslider');?></span></sr-nav-btn>    
            <sr-nav-btn data-sr-tabc="sr_nav_thumbs_editor" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Toolbar_Edit"></use></svg></sr-icon-wrap><span><?php echo __('Skin Editor','revslider');?></span></sr-nav-btn>
        </sr-options-menu-innerwrap>
    </sr-options-menu>
    <sr-modal-content> 
        <sr-wrap view="sr_nav_thumbs_style" viewchild="navigationthumbs" class="sr--tab--content sr--open" data-tab-target-group="1" id="sr_nav_thumbs_style" style="max-width:330px; width:auto">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Based On','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body class="sr_nav_thumbs_layoutuse"> 
                    <sr-drop wide data-v="" r="nav.thumbs.t" viewchild="sr_nav_thumbs_style" data-source="navigation" data-source-type="thumbs" data-onset="editor.nav.skin.get" data-onsetparams="thumbs" data-onchange="editor.nav.skin.update" data-onchangeparams="thumbs">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Pick a Skin','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('thumbs Skin Type','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>  
                    </sr-drop>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle nohover>
                    <sr-separator-title><?php _e('Settings','revslider'); ?></sr-separator-title>
                    <sr-wrap inline class="sr--abs--top--right sr--allowpointer sr--mt--10">
                        <sr-drop  class="sr--oicon" clean
                        r="nav.thumbs.ps"  viewchild="sr_nav_thumbs_style" 
                            data-onchange="editor.nav.presets.reload" data-onchangeparams="thumbs"                            
                            data-source="presets" data-source-type="navigation.thumbs" 
                            dropsw="300" dropsh="380"><sr-icon-wrap style="width:25px"><svg class="sr--icon" width="16" height="16" transform="translate(0,-1)"><use xlink:href="#General_Download"></use></svg></sr-icon-wrap></sr-drop>
                        <sr-drop id="sr_nav_thumbs_preset_drop"  class="sr--oicon" clean
                            r="nav.thumbs.ps" viewchild="sr_nav_thumbs_style"
                            data-onlyexport="true"                              
                            data-onchange="editor.nav.presets.save"
                            data-onpreset="editor.nav.presets.add" data-onpresetextend="editor.nav.presets.extendOption" data-onpresetparams="thumbs" 
                            data-type="preset" data-typelbl="<?php _e('New Skin Preset','revslider'); ?>" 
                            data-source="presets" data-source-type="navigation.thumbs" 
                            dropsw="300" dropsh="380"><sr-icon-wrap style="width:25px"><svg class="sr--icon" width="16" height="16" transform="translate(0,-1)"><use xlink:href="#General_Upload"></use></svg></sr-icon-wrap></sr-drop>
                    </sr-wrap>  
                </sr-separator-head> 
                <sr-separator-body>
                    <sr-sp h="5"></sr-sp>  
                    <sr-fieldset viewchild="sr_nav_thumbs_style" id="fset_preset_thumbs" data-type="single" data-source="editor.nav.presets.fieldset" data-sourceparams="thumbs" r="nav.thumbs.cst" class="sr--mb--0"></sr-fieldset>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="sr_nav_thumbs_layout" viewchild="navigationthumbs" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_thumbs_layout" style="max-width:330px; width:auto">
            <sr-separator>
                <sr-separator-body>  
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Thumbs','revslider'); ?></sr-separator-title>  
                    </sr-separator-head>
                    <sr-input half class="sr--mr--10"><input name="Visible Amount" replace responsive="inherit" respfix="round" respshow="below"  r="nav.thumbs.m.#LEV#" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="1" max="100" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Visible Amount','revslider'); ?></span></sr-input><!--
                    --><sr-input half><input name="Gaps" replace r="nav.thumbs.g" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Gaps','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body> 
            </sr-separator>
            <sr-separator>    
                <sr-separator-body>  
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Position','revslider'); ?></sr-separator-title>  
                    </sr-separator-head>
                    <sr-tabs-wrap viewchild="sr_nav_thumbs_layout" r="nav.thumbs.io"  class="sr--mb--15">
                        <sr-tab left half class="sr--active--tab" onchange="editor.nav.ioChanged" onchangeparams="thumbs" data-v="i"><?php _e('Inner','revslider'); ?></sr-tab>
                        <sr-tab right half onchange="editor.nav.ioChanged" onchangeparams="thumbs" data-v="o"><?php _e('Outer','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-tabs-wrap viewchild="sr_nav_thumbs_layout" r="nav.thumbs.d" data-type="thumbs" class="sr--mb--15">
                        <sr-tab left half class="sr--active--tab" onchange="editor.nav.ioChanged" onchangeparams="thumbs" data-v="horizontal"><?php _e('Horizontal','revslider'); ?></sr-tab>
                        <sr-tab right half onchange="editor.nav.ioChanged" onchangeparams="thumbs" data-v="vertical"><?php _e('Vertical','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-tabs-wrap viewchild="sr_nav_thumbs_layout" r="nav.thumbs.a" class="sr--mb--15">
                        <sr-tab left half class="sr--active--tab" data-v="slide"><?php _e('Content Flow','revslider'); ?></sr-tab>
                        <sr-tab right half data-v="slider"><?php _e('Full Stage','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-aligner mini class="sr--mr--10" id="sr_nav_thumbs_align" responsive respshow="below" r="nav.thumbs.v.#LEV#,nav.thumbs.h.#LEV#" viewchild="sr_nav_thumbs_layout">
                        <sr-aligner-wrap>    
                            <sr-aligner-pos data-v="top left" data-action="B.aligner.update,editor.nav.preview.update"></sr-aligner-pos>
                            <sr-aligner-pos data-v="top center" data-action="B.aligner.update,editor.nav.preview.update"></sr-aligner-pos>
                            <sr-aligner-pos data-v="top right" data-action="B.aligner.update,editor.nav.preview.update"></sr-aligner-pos>
                            <sr-aligner-pos data-v="center left" data-action="B.aligner.update,editor.nav.preview.update"></sr-aligner-pos>
                            <sr-aligner-pos data-v="center center" data-action="B.aligner.update,editor.nav.preview.update" class="checked"></sr-aligner-pos>
                            <sr-aligner-pos data-v="center right" data-action="B.aligner.update,editor.nav.preview.update"></sr-aligner-pos>
                            <sr-aligner-pos data-v="bottom left" data-action="B.aligner.update,editor.nav.preview.update"></sr-aligner-pos>
                            <sr-aligner-pos data-v="bottom center" data-action="B.aligner.update,editor.nav.preview.update"></sr-aligner-pos>
                            <sr-aligner-pos data-v="bottom right" data-action="B.aligner.update,editor.nav.preview.update"></sr-aligner-pos>    
                        </sr-aligner-wrap>
                    </sr-aligner><!--                    
                    --><sr-wrap basic alignpickerxy class="sr--mb--15"><!--
                        --><sr-input half class="sr--mr--10"><input name="X" replace responsive="inherit" respfix="round" respshow="below"  r="nav.thumbs.x.#LEV#" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('X','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Y" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.thumbs.y.#LEV#" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Y','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body> 
            </sr-separator>
            
            <sr-separator>
                <sr-separator-body>  
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Size','revslider'); ?></sr-separator-title>  
                    </sr-separator-head>
                    <sr-drop wide data-v="0" dropsw="200" r="nav.thumbs.size.t" data-sh=".sr_nav_thumbs_respsizes" data-shdep="#eqvalue" viewchild="sr_nav_thumbs_layout">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Legacy','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Responsivness','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="0"><?php _e('Legacy','revslider'); ?></sr-drops>    
                        <sr-drops data-v="1"><?php _e('Custom Multilevel','revslider'); ?></sr-drops>
                    </sr-drop>    
                    <sr-wrap class="sr_nav_thumbs_respsizes" value="0">
                        <sr-input half class="sr--mr--10"><input name="Min Width" replace r="nav.thumbs.size.mw" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Min Width','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Max Width" replace r="nav.thumbs.size.nw" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Max Width','revslider'); ?></span></sr-input>
                        <sr-sp h="0"></sr-sp>
                        <sr-input half><input name="Max Height" replace r="nav.thumbs.size.nh" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Max Height','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-wrap class="sr_nav_thumbs_respsizes" value="1">
                        <sr-input half class="sr--mr--10"><input name="Width" replace responsive="inherit" respfix="round" respshow="below"  r="nav.thumbs.size.w.#LEV#" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Width','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Height" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.thumbs.size.h.#LEV#" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Height','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>  
            </sr-separator>
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Wrapper','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body>
                    <sr-wrap class="sr--form--grp sr--mr--10" half><sr-color-mini data-v="transparent" r="nav.thumbs.wr.c" data-type="background" class="sr--mr--10" viewchild="sr_nav_thumbs_layout"></sr-color-mini><span class="sr--mr--30"><?php _e('Background','revslider'); ?></span></sr-wrap><!--    
                    --><sr-wrap basic half class="sr--form--grp sr--mb--15"><sr-onoff viewchild="sr_nav_thumbs_layout" r="nav.thumbs.wr.s" class="sr--mr--10"></sr-onoff><span><?php _e('Wide Wrapper','revslider'); ?></span></sr-wrap>
                    
                    <sr-input onethird class="sr--mr--7"><input name="Padding" replace responsive="inherit" respfix="round" respshow="below"  r="nav.thumbs.wr.p.#LEV#" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="0" max="500" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Padding','revslider'); ?></span></sr-input><!--
                    --><sr-input onethird class="sr--mr--7"><input name="Offset X" replace responsive="inherit" respfix="round" respshow="below"  r="nav.thumbs.wr.mx.#LEV#" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Offset X','revslider'); ?></span></sr-input><!--
                    --><sr-input onethird><input name="Offset Y" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.thumbs.wr.my.#LEV#" viewchild="sr_nav_thumbs_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Offset Y','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>  
        <sr-wrap view="sr_nav_thumbs_behavior" viewchild="navigationthumbs" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_thumbs_behavior" style="max-width:330px; width:auto">
            <sr-separator>                  
                <sr-separator-body>
                    <sr-sp h="20"></sr-sp>
                    <sr-drop wide data-v="" r="nav.thumbs.anim" viewchild="sr_nav_thumbs_behavior" class="sr--mr--10">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('In / Out Animation','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="fade"><?php _e('Fade','revslider'); ?></sr-drops>
						<sr-drops data-v="left"><?php _e('From Left','revslider'); ?></sr-drops>
						<sr-drops data-v="right"><?php _e('From Right','revslider'); ?></sr-drops>
						<sr-drops data-v="top"><?php _e('From Up','revslider'); ?></sr-drops>
						<sr-drops data-v="bottom"><?php _e('From Bottom','revslider'); ?></sr-drops>
						<sr-drops data-v="zoomin"><?php _e('Zoom In','revslider'); ?></sr-drops>
						<sr-drops data-v="zoomout"><?php _e('Zoom Out','revslider'); ?></sr-drops>
                    </sr-drop>
                    <sr-input wide class="sr--mr--10"><input name="Animation Speed" replace r="nav.thumbs.s" viewchild="sr_nav_thumbs_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Animation Speed','revslider'); ?></span></sr-input>
                    <sr-input wide class="sr--mr--10"><input name="Delay to Show" replace responsive="inherit" respfix="round" respshow="below"  r="nav.thumbs.dIn.#LEV#" viewchild="sr_nav_thumbs_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Delay to Show','revslider'); ?></span></sr-input>                    
                    <sr-drop  wide multiselect="truefalse" multilen="5" usecheck="" r="nav.thumbs.show" viewchild="sr_nav_thumbs_behavior" data-v="" dropsw="190" dropsh="200"> 
                        <sr-drop-view>
                            <span class="sr--drop--value"></span>    
                            <span class="sr--form--otitle"><?php _e('Visibility','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>     
                        <sr-drops valuelisting data-v="0"><sr-wrap dropicon=""><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></sr-wrap><?php _e('Wide Screen','revslider'); ?></sr-drops>
                        <sr-drops valuelisting data-v="1"><sr-wrap dropicon=""><svg class="sr--icon" width="22" height="18" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></sr-wrap><?php _e('Desktop','revslider'); ?></sr-drops>
                        <sr-drops valuelisting data-v="2"><sr-wrap dropicon=""><svg class="sr--icon" width="22" height="16" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Laptop"></use></svg></sr-wrap><?php _e('Notebook','revslider'); ?></sr-drops>
                        <sr-drops valuelisting data-v="3"><sr-wrap dropicon=""><svg class="sr--icon" width="20" height="24" transform="translate(0, 0)"><use xlink:href="#Top_Bar_Tablet"></use></svg></sr-wrap><?php _e('Tablet','revslider'); ?></sr-drops>
                        <sr-drops valuelisting data-v="4"><sr-wrap dropicon=""><svg class="sr--icon" width="14" height="20" transform="translate(0, 0)"><use xlink:href="#Top_Bar_Phone"></use></svg></sr-wrap><?php _e('Mobile','revslider'); ?></sr-drops>
                    </sr-drop> 
                    
                </sr-separator-body>
            </sr-separator>            
            <sr-separator>  
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Keep Visible','revslider'); ?></sr-separator-title>
                    <sr-onoff r="nav.thumbs.on" viewchild="sr_nav_thumbs_behavior" data-sh=".sr_nav_thumbs_hide" data-shdep="!checked" class="sr--mr--0" style="right:0px"></sr-onoff>
                </sr-separator-head>
                <sr-separator-body class="sr_nav_thumbs_hide sr--mb--0">                                    
                    <sr-input wide class="sr--mb--0"><input name="Delay to Hide" replace class="sr--mb--0" responsive="inherit" respfix="round"  respshow="below"  r="nav.thumbs.dOut.#LEV#" viewchild="sr_nav_thumbs_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Delay to Hide','revslider'); ?></span></sr-input> 
                    <sr-sp h="20"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>     
               
        <sr-wrap view="sr_nav_thumbs_editor" viewchild="navigationthumbs" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_thumbs_editor" style="width:auto">
            <sr-panel-invers topborder dark>
                <sr-sp h="5"></sr-sp>
                <sr-tabs-wrap wrap="">
                    <sr-tab data-sr-tabc="sr_nav_thumbs_css" data-tab-target-group="2" class="sr--tab--call" ltop="" onethird="">CSS</sr-tab>
                    <sr-tab data-sr-tabc="sr_nav_thumbs_markup" data-tab-target-group="2" class="sr--tab--call" none="" onethird="">HTML Markup</sr-tab>  
                    <sr-tab data-sr-tabc="sr_nav_thumbs_metas" data-tab-target-group="2" class="sr--tab--call selected sr--active--tab" none="" onethird="">Meta List</sr-tab>  
                </sr-tabs-wrap>
                <sr-sp h="5"></sr-sp>
                <sr-wrap style="position:absolute; right:0px; top:-53px; width:200px;">  
                    <sr-drop id="sr_nav_thumbs_skin_drop" wide                    
                                r="nav.thumbs.t" viewchild="sr_nav_thumbs_editor" 
                                data-pver="bottom"                                
                                data-onlyexport="true"
                                data-source="navigation" data-source-type="thumbs"
                                data-type="preset" data-typelbl="<?php _e('New Skin','revslider'); ?>"                                 
                                data-onchange="editor.nav.skin.change" 
                                data-onpreset="editor.nav.skin.add" data-onpresetextend="editor.nav.skin.extendOption" data-onpresetparams="thumbs" 
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
            <sr7-module-slideout-wrap view="nav_thumbs_css_codesnippets" viewchild="sr_nav_thumbs_editor" class="sr--tab--content" data-tab-target-group="2" data-rel-id="sr_nav_thumbs_css" id="nav_thumbs_css_codesnippets">
                <sr-options-menu fourperrow>
                    <sr-nav-btn data-sr-tabc="sr_snippets_thumbs_css_meta" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Meta','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_thumbs_css_elements" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Selectors','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_thumbs_css_classes" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Classes','revslider');?></span></sr-nav-btn>
                </sr-options-menu>
                <sr7-module-slideout-content>
                    <sr-wrap view="codesnippets_meta" viewchild="nav_thumbs_css_codesnippets" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_thumbs_css_meta">
                        <sr-fieldset viewchild="codesnippets_meta" class="sr--tab--content--wrap" id="fs_metas_thumbs_snippets" data-type="single" data-source="editor.nav.metas.snippets" data-sourceparams="thumbs" data-r="nav.thumbs.css" ></sr-fieldset>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_elements" viewchild="sr_nav_thumbs_metas" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_thumbs_css_elements">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($elements as $label => $element) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams="<?php echo $element; ?> {&#013;}&#013;" data-t="nav.thumbs.css" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_classes" viewchild="nav_thumbs_css_codesnippets" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_thumbs_css_classes">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($classes as $class) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo $class;?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams=".<?php echo $class; ?> {&#013;}&#013;" data-t="nav.thumbs.css" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                </sr7-module-slideout-content>
            </sr7-module-slideout-wrap>
            <sr7-module-slideout-wrap view="nav_thumbs_markup_codesnippets" class="sr--tab--content" data-tab-target-group="2" data-rel-id="sr_nav_thumbs_markup" id="nav_thumbs_markup_codesnippets">
                <sr-options-menu fourperrow>
                    <sr-nav-btn data-sr-tabc="sr_snippets_thumbs_html_meta" data-tab-target-group="arrmarkup" class="sr--tab--call"><span><?php echo __('Meta','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_thumbs_html_markups" data-tab-target-group="arrmarkup" class="sr--tab--call"><span><?php echo __('Markup','revslider');?></span></sr-nav-btn>
                </sr-options-menu>
                <sr7-module-slideout-content>
                    <sr-wrap view="codesnippets_content" viewchild="nav_thumbs_markup_codesnippets" data-tab-target-group="arrmarkup" class="sr--tab--content" id="sr_snippets_thumbs_html_meta">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($params as $param => $label) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams="{{<?php echo $param; ?>}}" data-t="nav.thumbs.html" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_markups" viewchild="nav_thumbs_markup_codesnippets" data-tab-target-group="arrmarkup" class="sr--tab--content" id="sr_snippets_thumbs_html_markups">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($markups as $label => $markup) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams='<?php echo $markup; ?>&#013;' data-t="nav.thumbs.html" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                </sr7-module-slideout-content>
            </sr7-module-slideout-wrap>
            <sr-wrap view="sr_nav_thumbs_css" viewchild="sr_nav_thumbs_editor" class="sr--tab--content" data-tab-target-group="2" id="sr_nav_thumbs_css">
                <sr-wrap style="margin:0px -15px">
                    <textarea name="Thumbs CSS Code" codemirror="true" data-w="570" data-mode="css"  data-onchange="editor.nav.metas.changed" data-onchangeparams="thumbs" style="width:570px; height:500px;" r="nav.thumbs.css" viewchild="sr_nav_thumbs_css"></textarea>
                </sr-wrap>   
            </sr-wrap>
            <sr-wrap view="sr_nav_thumbs_markup" viewchild="sr_nav_thumbs_editor" class="sr--tab--content" data-tab-target-group="2" id="sr_nav_thumbs_markup">
                <sr-wrap style="margin:0px -15px">
                    <textarea name="Thumbs HTML Code" codemirror="true" data-w="570" data-mode="htmlembedded"  data-onchange="editor.nav.metas.changed" data-onchangeparams="thumbs" style="width:570px;height:500px;" r="nav.thumbs.html" viewchild="sr_nav_thumbs_markup"></textarea>
                </sr-wrap>
            </sr-wrap>
            <sr-wrap view="sr_nav_thumbs_metas" viewchild="sr_nav_thumbs_editor" class="sr--tab--content sr--open" data-tab-target-group="2" id="sr_nav_thumbs_metas" style="width:540px">
                <sr-separator>                    
                    <sr-separator-body>
                    <sr-sp h="20"></sr-sp>
                    <sr-input half class="sr--mr--10"><input name="Default Width" replace r="nav.thumbs.ddim.w" viewchild="sr_nav_thumbs_metas" type="text" data-onchange="editor.nav.metas.changed" data-onchangeparams="thumbs" number="true" min="0" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Default Width','revslider'); ?></span></sr-input><!--
                    --><sr-input half><input name="Default Height" replace r="nav.thumbs.ddim.h" viewchild="sr_nav_thumbs_metas" type="text" data-onchange="editor.nav.metas.changed" data-onchangeparams="thumbs" number="true" min="0" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Default Height','revslider'); ?></span></sr-input>
                    <sr-sp h="5"></sr-sp>
                    </sr-separator-body>
                </sr-separator>
                <sr-separator>                    
                    <sr-separator-body>   
                        <sr-sp h="20"></sr-sp>                     
                        <sr-fieldset viewchild="sr_nav_thumbs_metas" id="fset_metas_thumbs" data-type="single" data-source="editor.nav.metas.fieldset" data-sourceparams="thumbs" r="nav.thumbs.def" class="sr--mb--0"></sr-fieldset> 
                        <sr-separator-line class="sr--mb--15"></sr-separator-line>
                        <!--<sr-panel-invers>    -->
                            <sr-wrap basic class="sr--mb--15">
                                <sr-input style="width:calc(100% * 7/15 + 5px);" class="sr--mr--13 sr--mb--0"><input id="sr_nav_add_meta_thumbs_handle" replace type="text" class="sr--mb--0"><span noicon="" class="sr--form--otitle"><?php _e('Meta Handle','revslider'); ?></span></sr-input><!--
                                --><sr-drop style="width:calc(100% * 4/18);" class="sr--mr--10 sr--mb--0" id="sr_nav_add_meta_thumbs_type">
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
                                <sr-button data-action="editor.nav.metas.add" data-aparams="thumbs" primary="" class="sr--cta sr--mb--0">Add Meta</sr-button>
                            </sr-wrap>

                        <!--</sr-panel-invers>-->
                    </sr-separator-body>
                </sr-separator>
            </sr-wrap>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>