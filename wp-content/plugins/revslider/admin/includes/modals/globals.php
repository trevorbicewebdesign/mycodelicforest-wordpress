<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
$sr_v6_exists = RevSliderPluginUpdateV6::do_v6_tables_exist();
?>
<sr-modal id="sr_globals" class="sr--no--padding" data-beforeclose="B.globals.check" data-reset="B.globals.reset" data-save="B.globals.save" view="glbls">
    <sr-modal-header>
        <h2 class="sr--modal--title sr--modal--title--simple"><?php echo __('Global Settings','revslider');?></h2>
        <sr-modal-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-modal-close>
    </sr-modal-header>    
    <sr-options-menu fiveperrow class="sr--left--organised" style="gap:10px 10px">
        <sr-nav-btn data-sr-tabc="sr_gl_general" class="sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon" width="18" height="17.91"><use xlink:href="#Main_Menu_Design_Mode"></use></svg></sr-icon-wrap><span><?php echo __('General','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_gl_bpoints" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="39" height="20"><use xlink:href="#Breakpoints"></use></svg></sr-icon-wrap><span><?php echo __('Breakpoints','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_gl_fonts" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Elements_Text"></use></svg></sr-icon-wrap><span><?php echo __('Fonts','revslider');?></span></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_gl_optimiz" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20" height="20"><use xlink:href="#Optimization"></use></svg></sr-icon-wrap><span><?php echo __('Optimization','revslider');?></span></sr-nav-btn>        
        <sr-nav-btn data-sr-tabc="sr_gl_system" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Main_Menu_Design_Mode"></use></svg></sr-icon-wrap><span><?php echo __('System','revslider');?></span></sr-nav-btn>
        <!--<sr-nav-btn data-sr-tabc="sr_gl_ttips" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20" height="20"><use xlink:href="#Tooltips"></use></svg></sr-icon-wrap><span>Tooltips & Guides</span></sr-nav-btn>-->
    </sr-options-menu>
    <sr-modal-content>
        <sr-wrap view="glbls_1" viewchild="glbls" open class="sr--tab--content sr--open" id="sr_gl_general">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Permissions & Language','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-drop twothird="" data-v="admin" r="permission" viewchild="glbls_1">
                    <sr-drop-view><span class="sr--drop--value"><?php echo __('Admin','revslider');?></span><span class="sr--form--otitle"><?php echo __('Editing Permisson','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                    <sr-drops data-v="admin"><?php echo __('Admin','revslider');?></sr-drops>
                    <sr-drops data-v="editor"><?php echo __('Editor, Admin','revslider');?></sr-drops>
                    <sr-drops data-v="author"><?php echo __('Author, Editor, Admin','revslider');?></sr-drops>    
                </sr-drop>
                <sr-sp h="0"></sr-sp>
                <sr-drop twothird="" data-v="en_US" r="lang" viewchild="glbls_1">
                    <sr-drop-view><span class="sr--drop--value"><?php echo __('English','revslider');?></span><span class="sr--form--otitle"><?php echo __('Editing Language','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                    <sr-drops data-v="default"><?php echo __('Default','revslider');?></sr-drops>
                    <sr-drops data-v="de_DE"><?php echo __('German','revslider');?></sr-drops>
                    <sr-drops data-v="en_US"><?php echo __('English','revslider');?></sr-drops>
                    <sr-drops data-v="fr_FR"><?php echo __('French','revslider');?></sr-drops>
                    <sr-drops data-v="zh_CN"><?php echo __('Chinese','revslider');?></sr-drops>    
                </sr-drop>
                <sr-wrap class="sr--form--grp"><sr-onoff class="sr--mr--10" r="trackOnOff" viewchild="glbls_1"></sr-onoff><span><?php echo __('Share Slider Revolution Usage Analytics','revslider');?></span></sr-wrap>
                <sr-sp h="5"></sr-sp>
            </sr-wrap>
            <sr-separator></sr-separator>
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Slider Revolution JS Libraries','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff viewchild="glbls_1" r="inclAll" class="sr--mr--10" data-sh="#sr_global_onlypages" data-shdep="!checked"></sr-onoff><span><?php echo __('Load JS Libraries Globally','revslider');?></span></sr-wrap>                
                <sr-drop id="sr_global_onlypages" class="sr--mb--0" twothird="" multiselect usecheck data-v="all" data-type="preset" r="incl" viewchild="glbls_1">
                    <sr-sp h="15"></sr-sp>
                    <sr-drop-view>
                        <span class="sr--drop--value"><sr-lbl info medium class="sr--mr--5"><?php echo __('All Pages With SR','revslider');?></sr-lbl></span><span class="sr--form--otitle"><?php echo __('Pages to Load SR JS Libraries','revslider');?></span>
                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                    </sr-drop-view>  
                    <sr-drops data-v="all"><?php echo __('All Pages With SR','revslider');?></sr-drops>
                </sr-drop>
                <sr-sp h="5"></sr-sp>
                <!--<sr-drop twothird="" data-v="anonymous" r="xOrig" viewchild="glbls_1">
                    <sr-drop-view><span class="sr--drop--value">Anonymous</span><span class="sr--form--otitle">Cross-origin Images</span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                    <sr-drops data-v="unset">Unset</sr-drops>
                    <sr-drops data-v="anonymous">Anonymous</sr-drops>
                    <sr-drops data-v="use-credentials">Use Credentials</sr-drops>  
                </sr-drop>-->                
            </sr-wrap>
            <sr-separator></sr-separator>
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Editor Behavior','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap half inline class="sr--form--grp sr--mr--10"><sr-onoff viewchild="glbls_1" r="alignGuides" class="sr--mr--10" data-sh=".sr_align_snap"></sr-onoff><span><?php echo __('Enable Align Guide Lines','revslider');?></span></sr-wrap><!--
                --><sr-input half class="sr_align_snap sr--mr--0">
                        <input name="Align Threshold" viewchild="glbls_1" type="text" r="alignThreshold"  livevisup dragnumber autocomplete="off" number="true" min="5" max="50" suffix="" step="1" validate="true">
                        <span noicon="" class="sr--form--otitle"><?php _e('Align Threshold','revslider'); ?></span>
                    </sr-input> 
                <sr-wrap class="sr_align_snap">                    
                    <sr-wrap half inline class="sr--form--grp"><sr-onoff viewchild="glbls_1" r="alignSnap" class="sr--mr--10"></sr-onoff><span><?php echo __('Allow Align Position Snap','revslider');?></span></sr-wrap><!--
                    --><sr-input half class="sr--mr--0">
                        <input name="Snap Threshold" viewchild="glbls_1" type="text" r="alignSnapThreshold"  livevisup dragnumber autocomplete="off" number="true" min="5" max="50" suffix="" step="1" validate="true">
                        <span noicon="" class="sr--form--otitle"><?php _e('Snap Threshold','revslider'); ?></span>
                    </sr-input>
                </sr-wrap>
                <sr-wrap class="sr_align_snap">
                    <sr-wrap half inline class="sr--form--grp"><sr-onoff viewchild="glbls_1" r="alignSpacing" class="sr--mr--10"></sr-onoff><span><?php echo __('Show Equal Spacing Guides','revslider');?></span></sr-wrap>
                </sr-wrap>
                <?php if(is_rtl()){ ?>
                <sr-wrap>
                    <sr-sp h="10"></sr-sp>
                    <sr-wrap half inline class="sr--form--grp"><sr-onoff viewchild="glbls_1" r="editorForceLTR" class="sr--mr--10"></sr-onoff><span><?php echo __('Editor in LTR Mode (reload required)','revslider');?></span></sr-wrap>
                </sr-wrap>
                <?php } ?>
                <sr-sp h="5"></sr-sp>
            </sr-wrap>
        </sr-wrap>
        <sr-wrap view="glbls_2" viewchild="glbls" class="sr--tab--content" id="sr_gl_bpoints">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Default Responsive Breakpoints','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-input twothird class="sr--icon--left--c"><input name="Wide Desktop" viewchild="glbls_2"  r="breakPoints.0" class="sr--pl--50" type="text" placeholder="1920px" validate="true" min="1240" max="2800" number="true" suffix="px"><span noicon class="sr--form--otitle"><?php echo __('Wide Screen Breakpoint','revslider');?></span><span class="sr--input--icon"><svg width="24" height="14" transform="translate(0, 2)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></span></sr-input>
                <sr-input twothird class="sr--icon--left--c"><input name="Desktop" viewchild="glbls_2"  r="breakPoints.1" class="sr--pl--50" type="text" placeholder="1500px" validate="true" min="960" max="1640" number="true" suffix="px"><span noicon class="sr--form--otitle"><?php echo __('Desktop Breakpoint','revslider');?></span><span class="sr--input--icon"><svg width="22" height="18" transform="translate(0, 4)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></span></sr-input>
                <sr-input twothird class="sr--icon--left--c"><input name="Notbeook" viewchild="glbls_2"  r="breakPoints.2" class="sr--pl--50" type="text" placeholder="1240px" validate="true" min="778" max="1240" number="true" suffix="px"><span noicon class="sr--form--otitle"><?php echo __('Notebook Breakpoint','revslider');?></span><span class="sr--input--icon"><svg width="22" height="16" transform="translate(0, 4)"><use xlink:href="#Top_Bar_Laptop"></use></svg></span></sr-input>
                <sr-input twothird class="sr--icon--left--c"><input name="Tablet" viewchild="glbls_2"  r="breakPoints.3" class="sr--pl--50" type="text" placeholder="778px" validate="true" min="640" max="1240" number="true" suffix="px"><span noicon class="sr--form--otitle"><?php echo __('Tablet Breakpoint','revslider');?></span><span class="sr--input--icon"><svg width="20" height="24" transform="translate(0, 3)"><use xlink:href="#Top_Bar_Tablet"></use></svg></span></sr-input>
                <sr-input twothird class="sr--icon--left--c"><input name="Mobile" viewchild="glbls_2"  r="breakPoints.4" class="sr--pl--50" type="text" placeholder="480px" validate="true" min="0" max="640" number="true" suffix="px"><span noicon class="sr--form--otitle"><?php echo __('Mobile Breakpoint','revslider');?></span><span class="sr--input--icon"><svg width="14" height="20" transform="translate(0, 5)"><use xlink:href="#Top_Bar_Phone"></use></svg></span></sr-input>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff viewchild="glbls_2" r="fSUVW" class="sr--mr--10"></sr-onoff><span><?php echo __('Breakpoints Include Scrollbar Width','revslider');?></span></sr-wrap>
            </sr-wrap>
        </sr-wrap>
        <sr-wrap view="glbls_3" viewchild="glbls" class="sr--tab--content" id="sr_gl_optimiz">  
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Module Optimization & Loading','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>    
                <sr-drop viewchild="glbls_3" r="getTec.core" twothird="" data-v="MIX">
                    <sr-drop-view><span class="sr--drop--value"><?php echo __('Smart Loading','revslider');?></span><span class="sr--form--otitle"><?php echo __('SR7 Data Load Method','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                    <sr-drops data-v="MIX"><?php echo __('Smart Loading','revslider');?></sr-drops>
                    <sr-drops data-v="JSON"><?php echo __('Preloading','revslider');?></sr-drops>
                    <sr-drops data-v="REST"><?php echo __('On Demand Loading','revslider');?></sr-drops>  
                </sr-drop>
                <sr-sp h="0"></sr-sp>
                <sr-drop viewchild="glbls_3" r="getTec.feed" twothird="" data-v="JSON">
                    <sr-drop-view><span class="sr--drop--value"><?php echo __('Preloading','revslider');?></span><span class="sr--form--otitle"><?php echo __('SR7 Feeds Load Method','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>  
                    <sr-drops data-v="JSON"><?php echo __('Preloading','revslider');?></sr-drops>
                    <sr-drops data-v="REST"><?php echo __('On Demand Loading','revslider');?></sr-drops>  
                </sr-drop>    
                <sr-wrap class="sr--form--grp"><sr-onoff r="opt.dprmobile" viewchild="glbls_3" class="sr--mr--10"></sr-onoff><span><?php echo __('Force 1xDPR on Mobile','revslider');?></span></sr-wrap>    
                <sr-sp h="5"></sr-sp>
            </sr-wrap>
            <sr-separator></sr-separator>
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Image Optimization','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap onefourth class="sr--form--grp sr--mr--10"><sr-onoff r="opt.img.u" viewchild="glbls_3" class="sr--mr--10"></sr-onoff><span><?php echo __('On Save','revslider');?></span></sr-wrap><!--
                --><sr-wrap onefourth class="sr--form--grp sr--mr--5"><sr-onoff r="opt.img.otf" viewchild="glbls_3" class="sr--mr--10"></sr-onoff><span><?php echo __('On The Fly','revslider');?></span></sr-wrap><!--
                --><sr-input half class="sr--mr--0">
                    <input name="Max Image Scale Multiplier" viewchild="glbls_3" type="text" r="opt.img.msc"  livevisup dragnumber autocomplete="off" number="true" min="1" max="2" suffix="" step="0.05" validate="true">
                    <span noicon="" class="sr--form--otitle"><?php _e('Max Img. Scale Multiplier','revslider'); ?></span>
                </sr-input>                
                <sr-drop viewchild="glbls_3" r="opt.img.f" half="" data-v="" class="sr--mr--10">
                    <sr-drop-view><span class="sr--drop--value"></span><span class="sr--form--otitle"><?php echo __('Image Format','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                    <sr-drops data-v="webp"><?php echo __('WebP','revslider');?></sr-drops>
                    <sr-drops data-v="old"><?php echo __('Original','revslider');?></sr-drops>                    
                </sr-drop><!--
                --><sr-input half class="sr--mr--0">
                        <input name="Quality" viewchild="glbls_3" type="text" r="opt.img.q"  livevisup dragnumber autocomplete="off" number="true" min="50" max="100" suffix="" step="1" validate="true">
                        <span noicon="" class="sr--form--otitle"><?php _e('Quality','revslider'); ?></span>
                    </sr-input>            
               <!-- <sr-wrap wide>
                    <sr-input half class="sr--mr--10">
                        <input name="Retina max Width" viewchild="glbls_3" type="text" r="opt.img.rw"  livevisup dragnumber autocomplete="off" number="true" min="200" max="4096" suffix="px" step="1" validate="true">
                        <span noicon="" class="sr--form--otitle"><?php _e('Retina max Width','revslider'); ?></span>
                    </sr-input>
                    <sr-input half class="sr--mr--0">
                        <input name="Retina max Height" viewchild="glbls_3" type="text" r="opt.img.rh"  livevisup dragnumber autocomplete="off" number="true" min="200" max="4096" suffix="px" step="1" validate="true">
                        <span noicon="" class="sr--form--otitle"><?php _e('Retina max Height','revslider'); ?></span>
                    </sr-input>
                </sr-wrap>-->
                <sr-wrap wide>
                    <sr-input half class="sr--mr--10">
                        <input name="Max Width" viewchild="glbls_3" type="text" r="opt.img.mw"  livevisup dragnumber autocomplete="off" number="true" min="200" max="2048" suffix="px" step="1" validate="true">
                        <span noicon="" class="sr--form--otitle"><?php _e('Max Width','revslider'); ?></span>
                    </sr-input><!--
                    --><sr-input half class="sr--mr--0">
                        <input name="Max Height" viewchild="glbls_3" type="text" r="opt.img.mh"  livevisup dragnumber autocomplete="off" number="true" min="200" max="2048" suffix="px" step="1" validate="true">
                        <span noicon="" class="sr--form--otitle"><?php _e('Max Height','revslider'); ?></span>
                    </sr-input>
                </sr-wrap>
                <sr-sp h="5"></sr-sp>    
            </sr-wrap>
            <sr-separator></sr-separator>
        </sr-wrap>        
        <sr-wrap view="glbls_4" viewchild="glbls" class="sr--tab--content" id="sr_gl_fonts">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Font Loading and Caching','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-drop viewchild="glbls_4" r="fonts.download" data-onchange="B.globals.fontCachePopup" twothird="" data-v="off" data-sh="#sr_glshow_fontcache" data-shdep="#eqvalue">
                    <sr-drop-view><span class="sr--drop--value"><?php echo __('Load from Google','revslider');?></span><span class="sr--form--otitle"><?php echo __('Google Fonts Download','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                    <sr-drops data-v="off"><?php echo __('Load from Google','revslider');?></sr-drops>
                    <sr-drops data-v="preload"><?php echo __('Cache Fonts Locally','revslider');?></sr-drops>
                    <sr-drops data-v="disable"><?php echo __('Disable, Load on Your Own','revslider');?></sr-drops>    
                </sr-drop>
                <sr-wrap base wide id="sr_glshow_fontcache" value="preload">
                    <sr-sp h="0"></sr-sp>  
                    <sr-button primary="" data-action="B.globals.fontCache" data-aparams="full" class="sr--cta sr--mr--10"><?php echo __('Clear and Recache Fonts','revslider');?></sr-button>    
                    <sr-button primary="" data-action="B.globals.fontCache" data-aparams="extend" class="sr--cta sr--mr--10"><?php echo __('Update Font Cache','revslider');?></sr-button>
                    <sr-sp h="0"></sr-sp>    
                </sr-wrap>
                <sr-wrap class="sr--form--grp"><sr-onoff viewchild="glbls_4" r="fonts.awesome" class="sr--mr--10"></sr-onoff><span><?php echo __('Disable SR Font Awesome Library','revslider');?></span></sr-wrap> 
                <sr-sp h="5"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff viewchild="glbls_4" r="fonts.dpc" class="sr--mr--10"></sr-onoff><span><?php echo __('Disable Google Font Preconnects','revslider');?></span></sr-wrap> 
            </sr-wrap>
            <sr-separator></sr-separator>
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Optional Google Fonts loading URL','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-input class="sr--mb--0" viewchild="glbls_4" r="fonts.url" twothird><input name="Custom Font URL" type="text" placeholder="(ie. http://fonts.useso.com/css?family)"></sr-input>  
            </sr-wrap>
            <sr-separator></sr-separator> 
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Custom Fonts','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap id="wrap_custom_fonts"><sr-fieldset viewchild="glbls_4" id="fset_custom_fonts" r="fonts.list" class="sr--mb--0"></sr-fieldset></sr-wrap>
                <sr-sp h="5"></sr-sp>
                <sr-button data-action="B.fieldSet.add,B.globals.adjustContent" data-actarget="wrap_custom_fonts" data-acheight="129" data-aparams="global,fonts.list" primary="" class="sr--cta sr--mr--10"><?php echo __('Add Custom Font','revslider');?></sr-button>
                <sr-button data-action="B.globals.customFont.open" primary="" class="sr--cta sr--mr--10"><?php echo __('Upload Custom Font','revslider');?></sr-button>
            </sr-wrap>    
        </sr-wrap>
        <sr-wrap view="glbls_5" viewchild="glbls" class="sr--tab--content" id="sr_gl_system">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('WordPress Integration','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff r="groupAddons" viewchild="glbls_5" class="sr--mr--10"></sr-onoff><span><?php echo __('Group Add-Ons in the WP Plugins List','revslider');?></span></sr-wrap>
                <sr-sp h="5"></sr-sp>
            </sr-wrap>
            <sr-separator></sr-separator>
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Privacy & GDPR Settings','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff r="gdpr.ytnc" viewchild="glbls_5" class="sr--mr--10"></sr-onoff><span><?php echo __('YouTube No-Cookie Mode','revslider');?></span></sr-wrap>    
                <sr-sp h="15"></sr-sp>                                
                <sr-wrap wide basic><sr-drop viewchild="glbls_5" r="gdpr.filter" data-sh=".sr_gdpr_options" data-shdep="#eqvalue" twothird="" data-v="" class="sr--mb--0">
                    <sr-drop-view><span class="sr--drop--value"></span><span class="sr--form--otitle"><?php echo __('Consent Management Plugin','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                    <sr-drops data-v="none"><?php echo __('None','revslider');?></sr-drops>                    
                    <?php if(defined('TP_COOKIES_ACTIVE')){ ?>
                    <sr-drops data-v="themepunch" data-title="<?php echo __('ThemePunch Cookie','revslider');?>"><?php echo __('ThemePunch Cookie','revslider'); ?><sr-drop-sm-txt class="gdprinstalled">Installed</sr-drop-sm-txt></sr-drops>
                    <?php } ?>
                    <sr-drops data-v="borlabscookie" data-title="<?php echo __('Borlabs Cookie','revslider');?>"><?php echo __('Borlabs Cookie','revslider'); echo (defined('BORLABS_COOKIE_CACHE_PATH')) ? '<sr-drop-sm-txt class="gdprinstalled">Installed</sr-drop-sm-txt>' : ''; ?></sr-drops>
                    <sr-drops data-v="complianz" data-title="<?php echo __('Complianz','revslider');?>"><?php echo __('Complianz','revslider'); echo (defined('CMPLZ_VERSION')) ? '<sr-drop-sm-txt class="gdprinstalled">Installed</sr-drop-sm-txt>' : ''; ?></sr-drops>
                    <sr-drops data-v="cookiebotcmp" data-title="<?php echo __('Cookiebot CMP','revslider');?>"><?php echo __('Cookiebot CMP','revslider'); echo (defined('CYBOT_COOKIEBOT_PLUGIN_URL')) ? '<sr-drop-sm-txt class="gdprinstalled">Installed</sr-drop-sm-txt>' : ''; ?></sr-drops>
                    <sr-drops data-v="cookienotice" data-title="<?php echo __('Cookie Notice','revslider');?>"><?php echo __('Cookie Notice','revslider'); echo (defined('COOKIE_NOTICE_PATH')) ? '<sr-drop-sm-txt class="gdprinstalled">Installed</sr-drop-sm-txt>' : ''; ?></sr-drops>
                    <sr-drops data-v="cookieyes" data-title="<?php echo __('Cookie Yes','revslider');?>"><?php echo __('Cookie Yes','revslider'); echo (defined('CLI_VERSION')) ? '<sr-drop-sm-txt class="gdprinstalled">Installed</sr-drop-sm-txt>' : ''; ?></sr-drops>
                    <sr-drops data-v="gdprcc" data-title="<?php echo __('GDPR Cookie Comp.','revslider');?>"><?php echo __('GDPR Cookie Comp.','revslider'); echo (defined('MOOVE_GDPR_VERSION')) ? '<sr-drop-sm-txt class="gdprinstalled">Installed</sr-drop-sm-txt>' : ''; ?></sr-drops>
                    <sr-drops data-v="realcookiebanner" data-title="<?php echo __('Real Cookie Banner','revslider');?>"><?php echo __('Real Cookie Banner','revslider'); echo (defined('RCB_VERSION')) ? '<sr-drop-sm-txt class="gdprinstalled">Installed</sr-drop-sm-txt>' : ''; ?></sr-drops>
                </sr-drop><sr-tooltip key="gdprsettings"></sr-tooltip>
                </sr-wrap>                
                <sr-wrap class="sr_gdpr_options" value="complianz#;#cookienotice#;#cookieyes#;#cookiebotcmp">
                    <sr-sp h="15"></sr-sp>  
                    <sr-drop viewchild="glbls_5" r="gdpr.category" twothird="" data-v="" class="sr--mb--0">
                        <sr-drop-view><span class="sr--drop--value"></span><span class="sr--form--otitle"><?php echo __('Category','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>
                        <sr-drops data-v="functional"><?php echo __('Functional','revslider');?></sr-drops>
                        <sr-drops data-v="marketing"><?php echo __('Marketing','revslider');?></sr-drops>
                        <sr-drops data-v="preferences"><?php echo __('Preferences','revslider');?></sr-drops>
                        <sr-drops data-v="statistics"><?php echo __('Statistics','revslider');?></sr-drops>
                    </sr-drop>
                </sr-wrap>  
            </sr-wrap>            
            
            <sr-separator></sr-separator>
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Content Caching','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff r="opt.intcache" viewchild="glbls_5" class="sr--mr--10"></sr-onoff><span><?php echo __('Use Internal Caching','revslider');?></span></sr-wrap>
                <sr-sp h="15"></sr-sp>
                <sr-button clean="" data-action="B.globals.clearCache"  class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Clear Internal Cache','revslider');?></sr-button>                
            </sr-wrap>
            <sr-separator></sr-separator>
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Database Health & Repair','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <?php if($sr_v6_exists === true){ ?>
                <sr-button id="sr6_remove_db_button" clean="" data-action="B.globals.deleteSR6Confirm" class="sr--cta sr sr--mr--10"><?php echo __('Delete Unmigrated Modules','revslider');?></sr-button><?php } ?><sr-button clean="" data-action="B.globals.checkTables" class="sr--cta sr--mr--10"><?php echo __('Scan Database Health','revslider');?></sr-button><!--                
                --><sr-button clean="" data-action="B.globals.forceTablesCreate" class="sr--cta sr--mr--10"><?php echo __('Fix Missing SR7 Tables','revslider');?></sr-button>
            </sr-wrap>
        </sr-wrap>
        <sr-wrap view="glbls_6" viewchild="glbls" class="sr--tab--content" id="sr_gl_ttips">
            <sr-wrap class="sr--p--20--15">
                <sr-section-title><?php echo __('Editor Guides','revslider');?></sr-section-title>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff r="guide.template" viewchild="glbls_6" class="sr--mr--10"></sr-onoff><span><?php echo __('Template Editing Guide','revslider');?></span></sr-wrap>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--form--grp"><sr-onoff r="guite.module" viewchild="glbls_6" class="sr--mr--10"></sr-onoff><span><?php echo __('Module Creation Guide','revslider');?></span></sr-wrap>
            </sr-wrap>
        </sr-wrap>  
        <sr-wrap right class="sr--tab--call">
            <sr-button primary="" data-action="B.globals.save" class="sr--cta sr--cta--big sr--mr--10"><?php echo __('Save Global Settings','revslider');?></sr-button>
        </sr-wrap>
    </sr-modal-content>
</sr-modal>
