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
    'Sample Markup 1' => '<div class="tp-title-wrap">&#013;    <div class="tp-arr-imgholder"></div></div>',
    'Sample Markup 2' => '<div class="tp-title-wrap">&#013;    <div class="tp-arr-imgholder"></div></div>',
    'Sample Markup 3' => '<div class="tp-title-wrap">&#013;    <div class="tp-arr-imgholder"></div></div>'
];

$elements = [
    'Arrow Element' => 'sr7-arrow',
    'Temporary Image' => 'sr7-nav-img-tmp',
    'Live Image' => 'sr7-nav-img-live',
    'Description Container' => 'sr7-navdc'
];

$classes = [
    'sr7-arrows',
    'sr7-leftarrow',
    'sr7-rightarrow',
    'tp-title-wrap',
    'sr7-nav-img',
    'tp-arr-img-over',
    'tp-arr-titleholder'
];

?>
<sr-modal id="sr_module_arrows" class="sr--no--padding sr--panel--leftsidebar" view="navigationarrows" style="min-width:320px; width:auto" hasslideout="sr7-module-slideout-wrap">
    <sr-options-menu fourperrow>
        <sr-options-menu-innerwrap style="max-width:320px">  
            <sr-nav-btn data-sr-tabc="sr_nav_a_style" data-tab-target-group="1" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="16" height="19.2" transform="translate(0,-1)"><use xlink:href="#Addon_Paintbrush"></use></svg></sr-icon-wrap><span><?php echo __('Skin & Style','revslider');?></span></sr-nav-btn>  
            <sr-nav-btn data-sr-tabc="sr_nav_a_layout" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Preset_Popup"></use></svg></sr-icon-wrap><span><?php echo __('Layout','revslider');?></span></sr-nav-btn>  
            <sr-nav-btn data-sr-tabc="sr_nav_a_behavior" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Addon_Reveal"></use></svg></sr-icon-wrap><span><?php echo __('View','revslider');?></span></sr-nav-btn>    
            <sr-nav-btn data-sr-tabc="sr_nav_a_editor" data-tab-target-group="1" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="19.2" height="19.2" transform="translate(0,-1)"><use xlink:href="#Toolbar_Edit"></use></svg></sr-icon-wrap><span><?php echo __('Skin Editor','revslider');?></span></sr-nav-btn>
        </sr-options-menu-innerwrap>  
    </sr-options-menu>
    <sr-modal-content> 
        <sr-wrap view="sr_nav_a_style" viewchild="navigationarrows" class="sr--tab--content sr--open" data-tab-target-group="1" id="sr_nav_a_style" style="max-width:330px; width:auto">
            <sr-separator>
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Choose Skin','revslider'); ?></sr-separator-title>  
                </sr-separator-head>
                <sr-separator-body class="sr_nav_a_layoutuse"> 
                    <sr-drop wide data-v="" data-novalue="<?php _e('Pick a Skin','revslider'); ?>" r="nav.arrows.t" viewchild="sr_nav_a_style" data-source="navigation" data-source-type="arrows" data-onset="editor.nav.skin.get" data-onsetparams="arrows" data-onchange="editor.nav.skin.update" data-onchangeparams="arrows">
                        <sr-drop-view>
                            <span class="sr--drop--value"></span>
                            <span class="sr--form--otitle"><?php _e('Arrow Skin Type','revslider'); ?></span>
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
                        r="nav.arrows.ps"  viewchild="sr_nav_a_style" 
                            data-onchange="editor.nav.presets.reload" data-onchangeparams="arrows"                            
                            data-source="presets" data-source-type="navigation.arrows" 
                            dropsw="300" dropsh="380"><sr-icon-wrap style="width:25px"><svg class="sr--icon" width="16" height="16" transform="translate(0,-1)"><use xlink:href="#General_Download"></use></svg></sr-icon-wrap></sr-drop>
                        <sr-drop id="sr_nav_arrows_preset_drop"  class="sr--oicon" clean
                            r="nav.arrows.ps" viewchild="sr_nav_a_style"
                            data-onlyexport="true"                              
                            data-onchange="editor.nav.presets.save"
                            data-onpreset="editor.nav.presets.add" data-onpresetextend="editor.nav.presets.extendOption" data-onpresetparams="arrows" 
                            data-type="preset" data-typelbl="<?php _e('New Skin Preset','revslider'); ?>" 
                            data-source="presets" data-source-type="navigation.arrows" 
                            dropsw="300" dropsh="380"><sr-icon-wrap style="width:25px"><svg class="sr--icon" width="16" height="16" transform="translate(0,-1)"><use xlink:href="#General_Upload"></use></svg></sr-icon-wrap></sr-drop>
                    </sr-wrap>  
                </sr-separator-head> 
                <sr-separator-body>
                    <sr-sp h="5"></sr-sp>  
                    <sr-fieldset viewchild="sr_nav_a_style" id="fset_preset_arrows" data-type="single" data-source="editor.nav.presets.fieldset" data-sourceparams="arrows" r="nav.arrows.cst" class="sr--mb--0"></sr-fieldset>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="sr_nav_a_layout" viewchild="navigationarrows" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_a_layout" style="max-width:330px; width:auto">
            <sr-separator>    
                <sr-separator-body>  
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Left Arrow','revslider'); ?></sr-separator-title>  
                    </sr-separator-head>
                    <sr-tabs-wrap viewchild="sr_nav_a_layout" r="nav.arrows.l.a">
                        <sr-tab left half class="sr--active--tab" data-v="slide"><?php _e('Content Flow','revslider'); ?></sr-tab>
                        <sr-tab right half data-v="slider"><?php _e('Full Stage','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-sp h="15"></sr-sp>  
                    <sr-aligner mini class="sr--mr--10" responsive respshow="below" r="nav.arrows.l.v.#LEV#,nav.arrows.l.h.#LEV#" viewchild="sr_nav_a_layout">
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
                        --><sr-input half class="sr--mr--10"><input name="Position X" replace responsive="inherit" respfix="round" respshow="below"  r="nav.arrows.l.x.#LEV#" viewchild="sr_nav_a_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('X','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Position Y" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.arrows.l.y.#LEV#" viewchild="sr_nav_a_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Y','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>
                <sr-separator-body>  
                    <sr-separator-head notoggle>
                        <sr-separator-title><?php _e('Right Arrow','revslider'); ?></sr-separator-title>  
                    </sr-separator-head> 
                    <sr-tabs-wrap viewchild="sr_nav_a_layout" r="nav.arrows.r.a">
                        <sr-tab left half class="sr--active--tab" data-v="slide"><?php _e('Content Flow','revslider'); ?></sr-tab>
                        <sr-tab right half data-v="slider"><?php _e('Full Stage','revslider'); ?></sr-tab>
                    </sr-tabs-wrap>
                    <sr-sp h="15"></sr-sp>  
                    <sr-aligner mini class="sr--mr--10" responsive respshow="below" r="nav.arrows.r.v.#LEV#,nav.arrows.r.h.#LEV#" viewchild="sr_nav_a_layout">
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
                        --><sr-input half class="sr--mr--10"><input name="Position X" replace responsive="inherit" respfix="round" respshow="below"  r="nav.arrows.r.x.#LEV#" viewchild="sr_nav_a_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('X','revslider'); ?></span></sr-input><!--
                        --><sr-input half><input name="Position Y" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.arrows.r.y.#LEV#" viewchild="sr_nav_a_layout" type="text" number="true" min="-1000" max="10000" suffix="px" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Y','revslider'); ?></span></sr-input>
                    </sr-wrap>
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body> 
            </sr-separator>
        </sr-wrap>
        <sr-wrap view="sr_nav_a_behavior" viewchild="navigationarrows" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_a_behavior" style="max-width:330px; width:auto">
            <sr-separator>                  
                <sr-separator-body>
                    <sr-sp h="20"></sr-sp>
                    <sr-drop half data-v="" r="nav.arrows.l.anim" viewchild="sr_nav_a_behavior" class="sr--mr--10">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Pick an Animation','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Left','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>
                        <sr-drops data-v="fade"><?php _e('Fade','revslider'); ?></sr-drops>
						<sr-drops data-v="left"><?php _e('From Left','revslider'); ?></sr-drops>
						<sr-drops data-v="right"><?php _e('From Right','revslider'); ?></sr-drops>
						<sr-drops data-v="top"><?php _e('From Up','revslider'); ?></sr-drops>
						<sr-drops data-v="bottom"><?php _e('From Bottom','revslider'); ?></sr-drops>
						<sr-drops data-v="zoomin"><?php _e('Zoom In','revslider'); ?></sr-drops>
						<sr-drops data-v="zoomout"><?php _e('Zoom Out','revslider'); ?></sr-drops>
                    </sr-drop><!--
                    --><sr-drop half data-v="" r="nav.arrows.r.anim" viewchild="sr_nav_a_behavior">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Pick an Animation','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Right','revslider'); ?></span>
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
                    <sr-input wide class="sr--mr--10"><input name="Animation Speed" replace r="nav.arrows.s" viewchild="sr_nav_a_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Animation Speed','revslider'); ?></span></sr-input>
                    <sr-input wide class="sr--mr--10"><input name="Delay to Show" replace responsive="inherit" respfix="round" respshow="below"  r="nav.arrows.dIn.#LEV#" viewchild="sr_nav_a_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Delay to Show','revslider'); ?></span></sr-input>

                    <sr-drop  wide multiselect="truefalse" multilen="5" usecheck="" r="nav.arrows.show" viewchild="sr_nav_a_behavior" data-v="" dropsw="190" dropsh="200"> 
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
                    
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
            <sr-separator>  
                <sr-separator-head notoggle>
                    <sr-separator-title><?php _e('Keep Visible','revslider'); ?></sr-separator-title>
                    <sr-onoff r="nav.arrows.on" viewchild="sr_nav_a_behavior" data-sh=".sr_nav_arr_hide" data-shdep="!checked" style="right:0px"></sr-onoff>  
                </sr-separator-head>
                <sr-separator-body class="sr_nav_arr_hide sr--mb--0">                                    
                    <sr-input wide><input name="Delay to Hide" replace responsive="inherit" respfix="round"  respshow="below"  r="nav.arrows.dOut.#LEV#" viewchild="sr_nav_a_behavior" type="text" number="true" min="0" max="10000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Delay to Hide','revslider'); ?></span></sr-input>  
                    <sr-sp h="5"></sr-sp>
                </sr-separator-body>
            </sr-separator>
        </sr-wrap>   
               
        <sr-wrap view="sr_nav_a_editor" viewchild="navigationarrows" class="sr--tab--content" data-tab-target-group="1" id="sr_nav_a_editor" style="width:auto">
            <sr-panel-invers topborder dark>
                <sr-sp h="5"></sr-sp>
                <sr-tabs-wrap wrap="">
                    <sr-tab data-sr-tabc="sr_nav_a_css" data-tab-target-group="2" class="sr--tab--call" ltop="" onethird="">CSS</sr-tab>
                    <sr-tab data-sr-tabc="sr_nav_a_markup" data-tab-target-group="2" class="sr--tab--call" none="" onethird="">HTML Markup</sr-tab>  
                    <sr-tab data-sr-tabc="sr_nav_arr_metas" data-tab-target-group="2" class="sr--tab--call selected sr--active--tab" none="" onethird="">Meta List</sr-tab>  
                </sr-tabs-wrap>    
                <sr-sp h="5"></sr-sp>
                <sr-wrap style="position:absolute; right:0px; top:-53px; width:200px;">  
                    <sr-drop id="sr_nav_arrows_skin_drop" wide                    
                                r="nav.arrows.t" viewchild="sr_nav_a_editor" 
                                data-pver="bottom"                                
                                data-onlyexport="true"
                                data-source="navigation" data-source-type="arrows"
                                data-type="preset" data-typelbl="<?php _e('New Skin','revslider'); ?>"                                 
                                data-onchange="editor.nav.skin.change" 
                                data-onpreset="editor.nav.skin.add" data-onpresetextend="editor.nav.skin.extendOption" data-onpresetparams="arrows" 
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
            <sr7-module-slideout-wrap view="nav_arrows_css_codesnippets" viewchild="sr_nav_a_editor" class="sr--tab--content" data-tab-target-group="2" data-rel-id="sr_nav_a_css" id="nav_arrows_css_codesnippets">
                <sr-options-menu fourperrow>
                    <sr-nav-btn data-sr-tabc="sr_snippets_arr_css_meta" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Meta','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_arr_css_elements" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Selectors','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_arr_css_classes" data-tab-target-group="arrcss" class="sr--tab--call"><span><?php echo __('Classes','revslider');?></span></sr-nav-btn>
                </sr-options-menu>
                <sr7-module-slideout-content>
                    <sr-wrap view="codesnippets_meta" viewchild="nav_arrows_css_codesnippets" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_arr_css_meta">
                        <sr-fieldset viewchild="codesnippets_meta" class="sr--tab--content--wrap" id="fs_metas_arrows_snippets" data-type="single" data-source="editor.nav.metas.snippets" data-sourceparams="arrows" data-r="nav.arrows.css" ></sr-fieldset>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_elements" viewchild="sr_nav_arr_metas" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_arr_css_elements">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($elements as $label => $element) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams="<?php echo $element; ?> {&#013;}&#013;" data-t="nav.arrows.css" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_classes" viewchild="nav_arrows_css_codesnippets" data-tab-target-group="arrcss" class="sr--tab--content" id="sr_snippets_arr_css_classes">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($classes as $class) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo $class;?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams=".<?php echo $class; ?> {&#013;}&#013;" data-t="nav.arrows.css" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                </sr7-module-slideout-content>
            </sr7-module-slideout-wrap>
            <sr7-module-slideout-wrap view="nav_arrows_markup_codesnippets" class="sr--tab--content" data-tab-target-group="2" data-rel-id="sr_nav_a_markup" id="nav_arrows_markup_codesnippets">
                <sr-options-menu fourperrow>
                    <sr-nav-btn data-sr-tabc="sr_snippets_arr_html_meta" data-tab-target-group="arrmarkup" class="sr--tab--call"><span><?php echo __('Meta','revslider');?></span></sr-nav-btn>
                    <sr-nav-btn data-sr-tabc="sr_snippets_arr_html_markups" data-tab-target-group="arrmarkup" class="sr--tab--call"><span><?php echo __('Markup','revslider');?></span></sr-nav-btn>
                </sr-options-menu>
                <sr7-module-slideout-content>
                    <sr-wrap view="codesnippets_content" viewchild="nav_arrows_markup_codesnippets" data-tab-target-group="arrmarkup" class="sr--tab--content" id="sr_snippets_arr_html_meta">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($params as $param => $label) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams="{{<?php echo $param; ?>}}" data-t="nav.arrows.html" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                    <sr-wrap view="codesnippets_markups" viewchild="nav_arrows_markup_codesnippets" data-tab-target-group="arrmarkup" class="sr--tab--content" id="sr_snippets_arr_html_markups">
                        <sr-wrap class="sr--tab--content--wrap">
                            <?php foreach ($markups as $label => $markup) : ?>
                                <sr-wrap class="sr--with--button">
                                    <sr-wrap><p><?php echo __($label,'revslider');?></p></sr-wrap>
                                    <sr-button data-action="B.insertSnippet" clean data-aparams='<?php echo $markup; ?>&#013;' data-t="nav.arrows.html" class="sr--abs--top--right sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
                                </sr-wrap>
                            <?php endforeach; ?>
                        </sr-wrap>
                    </sr-wrap>
                </sr7-module-slideout-content>
            </sr7-module-slideout-wrap>            
            <sr-wrap view="sr_nav_a_css" viewchild="sr_nav_a_editor" class="sr--tab--content" data-tab-target-group="2" id="sr_nav_a_css">
                <sr-wrap style="margin:0px -15px">
                    <textarea name="Arrow CSS Code" codemirror="true" data-w="570" data-mode="css" data-onchange="editor.nav.metas.changed" data-onchangeparams="arrows" style="width:570px; height:500px;" r="nav.arrows.css" viewchild="sr_nav_a_css"></textarea>
                </sr-wrap>   
            </sr-wrap>
            <sr-wrap view="sr_nav_a_markup" viewchild="sr_nav_a_editor" class="sr--tab--content" data-tab-target-group="2" id="sr_nav_a_markup">
                <sr-wrap style="margin:0px -15px">
                    <textarea name="Arrow HTML Code" codemirror="true" data-w="570" data-mode="htmlembedded" data-onchange="editor.nav.metas.changed" data-onchangeparams="arrows"  style="width:570px;height:500px;" r="nav.arrows.html" viewchild="sr_nav_a_markup"></textarea>
                </sr-wrap>
            </sr-wrap>
            <sr-wrap view="sr_nav_arr_metas" viewchild="sr_nav_a_editor" class="sr--tab--content sr--open" data-tab-target-group="2" id="sr_nav_arr_metas" style="width:540px">
                <sr-separator>                    
                    <sr-separator-body>
                        <sr-sp h="20"></sr-sp>
                        <sr-fieldset viewchild="sr_nav_arr_metas" id="fset_metas_arrows" data-type="single" data-source="editor.nav.metas.fieldset" data-sourceparams="arrows" r="nav.arrows.def" class="sr--mb--0"></sr-fieldset> 
                        <sr-separator-line class="sr--mb--15"></sr-separator-line>
                        <!--<sr-panel-invers>    -->
                            <sr-wrap basic class="sr--mb--15">
                                <sr-input style="width:calc(100% * 7/15 + 5px);" class="sr--mr--13 sr--mb--0"><input id="sr_nav_add_meta_arrows_handle" replace type="text" class="sr--mb--0"><span noicon="" class="sr--form--otitle"><?php _e('Meta Handle','revslider'); ?></span></sr-input><!--
                                --><sr-drop style="width:calc(100% * 4/18);" class="sr--mr--10 sr--mb--0" id="sr_nav_add_meta_arrows_type">
                                    <sr-drop-view>
                                        <span class="sr--drop--value"><?php _e('Meta','revslider'); ?></span>
                                        <span class="sr--form--otitle"><?php _e('Type','revslider'); ?></span>
                                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                                    </sr-drop-view>  
                                    <sr-drops data-v="custom"><?php _e('Custom','revslider'); ?></sr-drops>
                                    <sr-drops data-v="color"><?php _e('Color','revslider'); ?></sr-drops>
                                    <sr-drops data-v="icon"><?php _e('Icon','revslider'); ?></sr-drops>
                                    <sr-drops data-v="font"><?php _e('Font','revslider'); ?></sr-drops>    
                                </sr-drop> 
                                <sr-button data-action="editor.nav.metas.add" data-aparams="arrows" primary="" class="sr--cta sr--mb--0">Add Meta</sr-button>
                            </sr-wrap>

                        <!--</sr-panel-invers>-->
                    </sr-separator-body>
                </sr-separator>
            </sr-wrap>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>