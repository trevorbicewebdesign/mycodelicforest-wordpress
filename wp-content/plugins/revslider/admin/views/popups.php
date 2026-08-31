<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();

?>
<!-------------------------------------
    PLEASE WAIT GENERAL POPUP
--------------------------------------->
<sr-pleasewait>            
    <sr-wrap>
        <span id="sr_pleasewait_title" class="sr--text"><?php echo __('Please Wait...','revslider');?></span>        
        <sr-sp h="0" style="display: block; height: 0px;"></sr-sp>
        <span id="sr_pleasewait_content" class="sr--text sr--mt--10"><?php echo __('Preparing Environment','revslider');?></span>
    </sr-wrap>                    
</sr-pleasewait>

<sr-popups id="sr-popups">
    <sr-popups-bg></sr-popups-bg>
           
    <!-------------------------------------
        SYSTEM CHECK POPUP
    --------------------------------------->
    <sr-popup style="max-width:320px" id="system_check_list">
        <sr-popup-header class="sr--text--center">
            <h2 class="sr--popup--title" id=""><span class="system_requirements_counter">0/0</span><br><?php echo __('System Requirements','revslider');?></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="15"></sr-sp>
        <sr-popup-content class="sr--text--center">
            <sr-wrap><span class="sr--text"><?php echo __('To ensure maximum compatibility, it\'s important to check the following points:','revslider');?></span></sr-wrap>
            <sr-sp h="20"></sr-sp>
            <sr-wrap id="system_check_list_dynamic">

            </sr-wrap>
            <sr-sp h="30"></sr-sp>
            <sr-wrap class="sr--text--center">
                <sr-button clean class="check_for_tpserver sr--cta sr--mr--10 sr--mb--0"><svg class="sr--icon" width="13.93" height="14"><use xlink:href="#General_Refresh"></use></svg><?php echo __('Refresh','revslider');?></sr-button><!--
                --><a href="https://www.sliderrevolution.com/documentation/system-requirements/" target="_blank" rel="noopener" clean class="sr--cta sr--mb--0"><?php echo __('Requirements FAQ','revslider');?></a>
            </sr-wrap>
        </sr-popup-content>
    </sr-popup>


    <!-------------------------------------
        SAVE BEFORE EXIT
    --------------------------------------->
    <sr-popup style="width:360px" id="save_before_exit">
        <sr-popup-header class="sr--text--center">        
            <sr-wrap class="sr--text--center"><svg class="sr--icon sr--bad" width="20" height="20" transform="translate(0, 0)"><use xlink:href="#Dashboard_Info_Border"></use></svg></sr-wrap>
            <sr-sp h="10"></sr-sp>
            <h2 class="sr--popup--title"><?php echo __('Save Changes Before Exit?','revslider');?></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="10"></sr-sp>
        <sr-popup-content class="sr--text--center">
            <sr-wrap><span class="sr--text"><?php echo __('Exiting the Global Settings Modal without saving<br>will result in loss of any unsaved changes.','revslider');?></span></sr-wrap>
            <sr-sp h="25"></sr-sp>
            <sr-wrap class="sr--text--center">
                <sr-button unsaved data-action="B.popUp.saveChanges" data-aparams="true" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Save & Exit','revslider');?></sr-button><!--
                --><sr-button data-action="B.popUp.saveChanges" data-aparams="false" clean class="sr--cta sr--mb--0"><?php echo __('Reset & Exit','revslider');?></sr-button>
            </sr-wrap>
        </sr-popup-content>
    </sr-popup>

    <!-------------------------------------
        Google Font Precaching
    --------------------------------------->
    <sr-popup style="width:380px" id="fonts_precaching">
        <sr-popup-header class="sr--text--center">        
            <sr-wrap class="sr--text--center"><svg class="sr--icon sr--def" width="16" height="12.31" transform="translate(0, 0)"><use xlink:href="#Dashboard_Rename"></use></svg></sr-wrap>
            <sr-sp h="10"></sr-sp>
            <h2 class="sr--popup--title"><?php echo __('Google Fonts need to be precached before they become available in the frontend.','revslider');?></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="10"></sr-sp>
        <sr-popup-content class="sr--text--center">
            <sr-wrap><span class="sr--text"><?php echo __('Do you want to start the precaching process now?','revslider');?></span></sr-wrap>
            <sr-sp h="25"></sr-sp>
            <sr-wrap class="sr--text--center">
                <sr-button unsaved data-action="B.globals.fontCache" data-aparams="full" class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Start Precaching Now','revslider');?></sr-button><!--
                --><sr-button data-action="B.popUp.hide" clean class="sr--cta sr--mb--0"><?php echo __('Precache Manually Later','revslider');?></sr-button>
            </sr-wrap>
        </sr-popup-content>
    </sr-popup>


    <!-------------------------------------
        ADDON WARNINGS POPUP
    --------------------------------------->
    <sr-popup id="sr_addon_warnings">        
        <sr-popup-header class="sr--text--center">
            <sr-sp h="18"></sr-sp>
            <sr-wrap class="sr--text--center"><span class="sr--icon-filled sr--bad" style="width:38px; height:40px"><svg class="sr--icon" width="10" height="20" transform="translate(0, 10)"><use xlink:href="#Dashboard_Info"></use></svg></span></sr-wrap>
            <sr-sp h="32"></sr-sp>
            <h2 class="sr--off--migration--issues sr--popup--big--title"><?php echo __('There is a problem with some of your<br>Slider Revolution Addons','revslider');?></h2>
            <h2 class="sr--on--migration--issues sr--popup--big--title"><?php echo __('Migration failed for some modules<br>Not initialized Addons','revslider');?></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="15"></sr-sp>
        <sr-popup-content class="sr-text-center">
            <sr-wrap class="sr--off--migration--issues sr--text--center"><span class="sr--text"><?php echo __('The following addons are deactivated, not installed, not up to date, or not compatible anymore:','revslider');?></span></sr-wrap>
            <sr-wrap class="sr--on--migration--issues sr--text--center"><span class="sr--text"><?php echo __('The add-ons listed below cannot be initialized without an active Slider Revolution 7 registration:','revslider');?></span></sr-wrap>
            <sr-sp h="50"></sr-sp>            
            <sr-wrap id="sr_addon_warnings_dynamic"></sr-wrap>            
            <sr-sp h="30"></sr-sp>
            <sr-wrap class="sr--text--center">
                <span class="sr--off--migration--issues sr--text"><?php echo __('Press the button below to install & activate or update all addons required by your modules.','revslider');?></span>
                <span class="sr--on--migration--issues sr--text"><?php echo __('Register Slider Revolution. It will allow to initialise all addons required by your pending migration','revslider');?></span>
                <sr-sp h="30"></sr-sp>
                <sr-button data-action="B.addons.fixAll" unsaved class="sr--off--migration--issues sr--cta sr--cta--big"><?php echo __('Fix All Addons','revslider');?></sr-button>
                <sr-button data-action="B.addons.registerFirst" unsaved class="sr--on--migration--issues sr--cta sr--cta--big"><?php echo __('Regsiter License','revslider');?></sr-button>
            </sr-wrap>
        </sr-popup-content>
    </sr-popup>

     <!-------------------------------------
        ADDON WARNINGS POPUP
    --------------------------------------->
    <sr-popup id="sr_migration_warnings">        
        <sr-popup-header class="sr--text--center">
            <sr-sp h="18"></sr-sp>
            <sr-wrap class="sr--text--center"><span class="sr--icon-filled sr--bad" style="width:38px; height:40px"><svg class="sr--icon" width="10" height="20" transform="translate(0, 10)"><use xlink:href="#Dashboard_Info"></use></svg></span></sr-wrap>
            <sr-sp h="32"></sr-sp>            
            <h2 class="sr--popup--big--title"><?php echo __('Migration failed for some modules','revslider');?></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="15"></sr-sp>
        <sr-popup-content class="sr-text-center">            
            <sr-wrap class="sr--text--center"><span class="sr--text"><?php echo __('The following Modules could not be migrated:','revslider');?></span></sr-wrap>
            <sr-sp h="50"></sr-sp>            
            <sr-wrap id="sr_migration_warnings_dynamic"></sr-wrap>            
            <sr-sp h="30"></sr-sp>
            <sr-wrap class="sr--text--center">                
                <span class="sr--text"><?php echo __('Silent Migration could not be finished. Please Contact Support','revslider');?></span>
                <sr-sp h="30"></sr-sp>                
                <sr-button unsaved class="sr--cta sr--cta--big"><?php echo __('Ignore Warnings Now','revslider');?></sr-button>
            </sr-wrap>
        </sr-popup-content>
    </sr-popup>

    <!-------------------------------------
        UPLOAD DARG AND DROP ZONE AND LIST
    --------------------------------------->
    <sr-popup id="sr_upload_popup">
        <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-popup-close>    
        <sr-popup-header id="sr_upload_popup_zone" class="sr--text--center">                        
            <sr-wrap class="sr--text--center"><svg class="sr--icon" width="18" height="22" transform="translate(0, 0)"><use xlink:href="#Dashboard_Export"></use></svg></sr-wrap>            
            <sr-sp h="18"></sr-sp>
            <h2 class="sr--popup--title"><?php echo __('Drag & Drop Import File','revslider');?></h2>            
            <sr-wrap class="sr--text--center sr--hideon--upload">
                <sr-sp h="5"></sr-sp>
                <span class="sr--text"><?php echo __('or','revslider');?></span>
                <sr-sp h="10"></sr-sp>
                <sr-button clean data-action="B.upload.browse" class="sr--cta sr--cta--big"><?php echo __('Click to Choose','revslider');?></sr-button>
            </sr-wrap>
        </sr-popup-header>        
        <sr-popup-content><sr-wrap id="sr_upload_file_list"></sr-wrap></sr-popup-content>
        <sr-wrap id="sr_upload_btnswarp" class="sr--text--center">
            <sr-sp h="40"></sr-sp>
            <sr-button data-action="B.upload.hide" primary class="sr--cta sr--cta--big"><?php echo __('Close Upload','revslider');?></sr-button>
            <sr-button id="sr_update_crossmodule" style="display:none; position:absolute; right:0px; bottom:5px;" data-action="B.upload.fixIds" success class="sr--cta sr--cta"><?php echo __('Update Cross-Module Actions','revslider');?></sr-button>
        </sr-wrap>
    </sr-popup>

    <!-------------------------------------
        UPLOAD CUSTOM FONT
    --------------------------------------->
    <style>
        #sr_custom_font_upload .sr--cf--intro{ display:block; line-height:1.5; font-size:11px; }
        #sr_custom_font_upload .sr--cf--addrow{ display:flex; gap:10px; align-items:stretch; }
        #sr_custom_font_upload .sr--cf--addrow sr-input{ flex:1; }
        #sr_custom_font_files{ display:block; }
        .sr--cf--file{ display:flex; flex-wrap:nowrap; align-items:flex-start; gap:10px; padding:8px 12px; margin-bottom:6px; border:1px solid var(--sr-col-w6-d0); border-radius:var(--sr-border-radius-s); background:var(--sr-col-w12-d4); }
        .sr--cf--file > *{ vertical-align:top; }
        .sr--cf--file .sr--cf--name{ flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; line-height:30px; }
        .sr--cf--file sr-input{ width:90px; margin:0; }
        .sr--cf--file sr-drop{ width:120px; margin:0; }
        .sr--cf--file .sr--cf--del{ margin:0; }
        #sr_custom_font_upload .sr--cf--actions{ text-align:right; }
    </style>
    <sr-popup id="sr_custom_font_upload" style="width:720px">
        <sr-popup-header class="sr--text--center">
            <sr-wrap class="sr--text--center"><svg class="sr--icon sr--def" width="20" height="20"><use xlink:href="#General_Upload"></use></svg></sr-wrap>
            <sr-sp h="10"></sr-sp>
            <h2 class="sr--popup--title"><?php echo __('Upload Custom Font','revslider');?></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="15"></sr-sp>
        <sr-popup-content>
            <sr-wrap class="sr--ph--20">
                <sr-wrap class="sr--cf--addrow">
                    <sr-input class="sr--mb--0"><input id="sr_custom_font_family" type="text" placeholder="<?php echo esc_attr__('Custom Font Name','revslider');?>"><span noicon="" class="sr--form--otitle"><?php echo __('Font Family Name','revslider');?></span></sr-input>
                    <sr-button primary data-action="B.globals.customFont.browse" class="sr--cta sr--mb--0"><?php echo __('Upload Files','revslider');?></sr-button>
                </sr-wrap>
                <sr-sp h="15"></sr-sp>
                <span class="sr--text sr--cf--intro"><?php echo __('Upload one file per weight or style. Allowed types are WOFF2, WOFF, TTF and OTF. Give your font a family name, then add the files — the weight and style are detected automatically and can be adjusted below.','revslider');?></span>
                <sr-sp h="15"></sr-sp>
                <sr-wrap id="sr_custom_font_empty"><span class="sr--text sr--note"><?php echo __('No font files added yet.','revslider');?></span></sr-wrap>
                <sr-wrap id="sr_custom_font_files"></sr-wrap>
                <sr-sp h="15"></sr-sp>
                <sr-wrap class="sr--cf--actions">
                    <sr-button data-action="B.globals.customFont.add" primary class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Add Font','revslider');?></sr-button>
                    <sr-button data-action="B.popUp.hide" clean class="sr--cta sr--mb--0"><?php echo __('Cancel','revslider');?></sr-button>
                </sr-wrap>
            </sr-wrap>
            <sr-sp h="10"></sr-sp>
        </sr-popup-content>
    </sr-popup>

     <!-------------------------------------
        PREMIUM FEATURES POPUP
    --------------------------------------->
    <sr-popup id="sr_popup_migration_issues" style="max-width:300px">
        <sr-popup-header class="sr--text--center">
            <h2 class="sr--popup--title" id=""><svg class="sr--icon premium_status_icon" width="20" height="20"></svg><br><?php echo __('Missing Addons & ','revslider');?> <span class="sr--red sr-f-bold"><?php echo __('Errors','revslider'); ?></span></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="15"></sr-sp>
        <sr-popup-content class="sr--text--center">
            <sr-wrap><span class="sr--text"><?php echo __('Some modules couldn’t be migrated. Please make sure the required Addons are installed or updated, then retry migration one by one — or simply reload the page for automatic migration.','revslider');?></span></sr-wrap>            
            <sr-sp h="20"></sr-sp>
            <sr-wrap  id="migration_errors" class="sr--text sr--text--left sr--ph--30">
                <span class="sr--text--list"><span class="sr--icon-filled sr--red sr--mr--10"><svg style="fill:#fff" class="sr--icon" width="7" height="7" transform="translate(0, 0)"><use xlink:href="#General_Close"></use></svg></span><span class="sr--text"><?php echo __('MouseTrap Addon Missing','revslider');?></span></span><br>
                <span class="sr--text--list"><span class="sr--icon-filled sr--red sr--mr--10"><svg style="fill:#fff" class="sr--icon" width="7" height="7" transform="translate(0, 0)"><use xlink:href="#General_Close"></use></svg></span><span class="sr--text"><?php echo __('Liquied Fluid Addon Missing','revslider');?></span></span><br>
                <span class="sr--text--list"><span class="sr--icon-filled sr--red sr--mr--10"><svg style="fill:#fff" class="sr--icon" width="7" height="7" transform="translate(0, 0)"><use xlink:href="#General_Close"></use></svg></span><span class="sr--text"><?php echo __('Before After Addon Missing','revslider');?></span></span><br>                
            </sr-wrap>            
            <sr-sp h="85"></sr-sp>
        </sr-popup-content>
    </sr-popup>   

    <!-------------------------------------
        PREMIUM FEATURES POPUP
    --------------------------------------->
    <sr-popup id="premium_features">
        <sr-popup-header class="sr--text--center">
            <h2 class="sr--popup--title" id=""><svg class="sr--icon premium_status_icon" width="20" height="20"></svg><br><?php echo __('Premium Features are','revslider');?> <span class="premium_status sr--red sr-f-bold"><?php echo __('Disabled','revslider'); ?></span></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="15"></sr-sp>
        <sr-popup-content class="sr--text--center">
            <sr-wrap><span class="sr--text" id="premium_features_intro"></span></sr-wrap>            
            <sr-sp h="20"></sr-sp>
            <sr-wrap  class="sr--text sr--text--left sr--ph--30">
                <span class="sr--text--list"><span class="sr--icon-filled premium_status_list sr--mr--10"><svg class="sr--icon"></svg></span><span class="sr--text"><?php echo __('200+ Ready-To-Go Templates','revslider');?></span></span><br>
                <span class="sr--text--list"><span class="sr--icon-filled premium_status_list sr--mr--10"><svg class="sr--icon"></svg></span><span class="sr--text"><?php echo __('Quick Start Module Generator','revslider');?></span></span><br>
                <span class="sr--text--list"><span class="sr--icon-filled premium_status_list sr--mr--10"><svg class="sr--icon"></svg></span><span class="sr--text"><?php echo __('Access to generative AI features','revslider');?></span></span><br>
                <span class="sr--text--list"><span class="sr--icon-filled premium_status_list sr--mr--10"><svg class="sr--icon"></svg></span><span class="sr--text"><?php echo __('30+ Addons','revslider');?></span></span><br>
                <span class="sr--text--list"><span class="sr--icon-filled premium_status_list sr--mr--10"><svg class="sr--icon"></svg></span><span class="sr--text"><?php echo __('Elements Asset Library','revslider');?></span></span><br>
                <span class="sr--text--list"><span class="sr--icon-filled premium_status_list sr--mr--10"><svg class="sr--icon"></svg></span><span class="sr--text"><?php echo __('Personalized 1on1 Support','revslider');?></span></span><br>
                <span class="sr--text--list"><span class="sr--icon-filled premium_status_list sr--mr--10"><svg class="sr--icon"></svg></span><span class="sr--text"><?php echo __('Instant Updates','revslider');?></span></span><br>
                
            </sr-wrap>
            <sr-sp h="30"></sr-sp>
            <sr-wrap class="sr--text--center sr--show--onnotreg">
                <a href="https://www.sliderrevolution.com/documentation/system-requirements/" target="_blank" rel="noopener" style="line-height:28px; padding:0px 12px;margin-right:5px;" primary class="sr--cta"><?php echo __('Buy a License','revslider');?></a><!--
                --><a href="https://www.sliderrevolution.com/help/sr7-video-tour-premium-features/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=premiumtour" target="_blank" rel="noopener" clean class="sr--cta"><svg class="sr--icon" style="fill:#000" width="10" height="10" transform="translate(0, -1)"><use xlink:href="#Play"></use></svg><?php echo __('Premium Features Tour','revslider'); ?></a>                
            </sr-wrap>
            <sr-sp h="85"></sr-sp>
        </sr-popup-content>
    </sr-popup>   
    
    <!-------------------------------------
        Welcome Register Popup
    --------------------------------------->
    <sr-popup id="sr7_welcome_login">
        <sr-popup-bg><sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close></sr-popup-bg>
        <sr-popup-header class="sr--text--center">
            <sr-wrap style="height:36px" class="sr--mb--25"><svg class="sr--icon" width="36" height="36"><use xlink:href="#EditorLogo"></use></svg></sr-wrap>
            <h2 class="sr--popup--title"><?php echo __('Welcome to Slider Revolution 7!','revslider');?><br><?php echo __('Register your License now to','revslider');?><br>🔓<?php echo __('Unlock Premium Features','revslider');?></h2>            
        </sr-popup-header>
        <sr-sp h="15"></sr-sp>
        <sr-popup-content class="sr--text--center">    
            <sr-sp h="20"></sr-sp>                    
            <sr-wrap style="width:480px; margin:0 auto">
                <sr-wrap  half inline class="sr--text sr--text--left" style="line-height:20px;white-space:normal">
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon" width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('200+ Ready-To-Go Templates','revslider');?></span></span>
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('30+ Addons','revslider');?></span></span>
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('Quick Start Module Generator','revslider');?></span></span>
                </sr-wrap><!--
                --><sr-wrap  half inline class="sr--text sr--text--left" style="line-height:20px;white-space:normal">
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('AI Credits for Image Generation','revslider');?></span></span><br>
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('Personalized 1on1 Support','revslider');?></span></span><br>
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('Instant Updates','revslider');?></span></span><br>
                </sr-wrap>
            </sr-wrap>
            <sr-sp h="40"></sr-sp>
            <sr-wrap class="sr_welcome_pwcontainer" class="sr--text--center">
                <sr-wrap wide class="sr--mb--10"><input id="system_license_code_welcome" keydown="licenseCheck+25" autocomplete="off" class="sr--input--text"  type="text" data-onchange="licenseCheck" data-action="licenseCheck" placeholder="<?php echo __('Enter your License Key','revslider'); ?>" value=""></sr-wrap>
                <sr-wrap wide><sr-button id="button_license_welcome" data-action="license.set,B.popUp.hideAll" primary=""  class="sr--cta sr--cta--big disabled"><?php echo __('No Key Entered','revslider'); ?></sr-button></sr-wrap>
            </sr-wrap>     
            <sr-wrap style="width:480px; margin:0 auto; text-align:center"> 
                <sr-sp h="20"></sr-sp>
                <a href="https://www.sliderrevolution.com/help/where-to-find-purchase-code/?utm_source=admin&utm_medium=welcomemodal&utm_campaign=srusers&utm_content=findkey" target="_blank" rel="noopener" class="sr--link--text sr--mr--20 sr--link--before"><?php echo __('How to find my Key?','revslider'); ?></a><!--
                --><a href="https://www.sliderrevolution.com/faqs-and-tutorials/?utm_source=admin&utm_medium=welcomemodal&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener" class="sr--link--text sr--mr--20 sr--link--before"><?php echo __('License FAQ','revslider'); ?></a><!--
                --><a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=welcomemodal&utm_campaign=srusers&utm_content=buykey" target="_blank" rel="noopener" class="sr--link--text sr--link--before"><?php echo __('Buy a License','revslider'); ?></a>
            </sr-wrap>
        </sr-popup-content>
    </sr-popup>  
    
    <!-------------------------------------
        Premium Register Popup
    --------------------------------------->
    <sr-popup id="sr7_premium_login">
        <sr-popup-bg></sr-popup-bg>
        <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        <sr-popup-header class="sr--text--center">
            <sr-wrap style="height:36px" class="sr--mb--25"><svg class="sr--icon" width="36" height="36"><use xlink:href="#EditorLogo"></use></svg></sr-wrap>
            <h2 class="sr--popup--title"><?php echo __('Get a Slider Revolution Subscription to','revslider');?><br>🔓<?php echo __('Unlock All Premium Features','revslider');?></h2>            
        </sr-popup-header>        
        <sr-popup-content class="sr--text--center">    
            <sr-sp h="20"></sr-sp>                    
            <sr-wrap style="width:480px; margin:0 auto">
                <sr-wrap  half inline class="sr--text sr--text--left" style="line-height:20px;white-space:normal">
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon" width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('200+ Ready-To-Go Templates','revslider');?></span></span>
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('30+ Addons','revslider');?></span></span>
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('Quick Start Module Generator','revslider');?></span></span>
                </sr-wrap><!--
                --><sr-wrap  half inline class="sr--text sr--text--left" style="line-height:20px;white-space:normal">
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('AI Credits for Image Generation','revslider');?></span></span><br>
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('Personalized 1on1 Support','revslider');?></span></span><br>
                    <span class="sr--text--list"><span class="sr--icon-filled sr--good sr--mr--10 sr--mt--2"><svg class="sr--icon"  width="8" height="8"><use xlink:href="#General_Check_Small"></use></svg></span><span class="sr--text"><?php echo __('Instant Updates','revslider');?></span></span><br>
                </sr-wrap>
            </sr-wrap>
            <sr-sp h="20"></sr-sp>
            <sr-wrap calss="sr--text--center">
                <a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=activationmodal&utm_campaign=srusers&utm_content=buykey" target="_blank" rel="noopener" primary="" class="sr--cta sr--cta--standard sr--mr--10"><?php echo __('Buy a License','revslider'); ?></a>
                <a href="https://www.sliderrevolution.com/help/sr7-video-tour-premium-features/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=premiumtour" target="_blank" rel="noopener" clean="" class="sr--cta sr--cta--standard sr--mr--10 sr--cta--white"><svg class="sr--icon" style="fill:#fff" width="10" height="10" transform="translate(0, -1)"><use xlink:href="#Play"></use></svg><?php echo __('Premium Features Tour','revslider'); ?></a>
            </sr-wrap>
            <sr-sp h="40"></sr-sp>
            <sr-wrap class="sr_premium_pwcontainer" class="sr--text--center">
                <sr-wrap wide class="sr--mb--10"><input id="system_license_code_premium" keydown="licenseCheck+25" autocomplete="off" class="sr--input--text"  type="text" data-onchange="licenseCheck" data-action="licenseCheck" placeholder="<?php echo __('Enter your License Key','revslider'); ?>" value=""></sr-wrap>
                <sr-wrap wide><sr-button id="button_license_premium" data-action="license.set,B.popUp.hideAll" primary=""  class="sr--cta sr--cta--big disabled"><?php echo __('No Key Entered','revslider'); ?></sr-button></sr-wrap>
            </sr-wrap>     
            <sr-wrap style="width:480px; margin:0 auto; text-align:center"> 
                <sr-sp h="20"></sr-sp>
                <a href="https://www.sliderrevolution.com/help/where-to-find-purchase-code/?utm_source=admin&utm_medium=activationmodal&utm_campaign=srusers&utm_content=findkey" target="_blank" rel="noopener" class="sr--link--text sr--mr--20 sr--link--before"><?php echo __('How to find my Key?','revslider'); ?></a><!--
                --><a href="https://www.sliderrevolution.com/faqs-and-tutorials/?utm_source=admin&utm_medium=activationmodal&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener"  class="sr--link--text sr--mr--20 sr--link--before"><?php echo __('License FAQ','revslider'); ?></a>
                
            </sr-wrap>
        </sr-popup-content>
    </sr-popup>   
    
    
    <!-------------------------------------
        AI Purchase Popup
    --------------------------------------->
    <sr-popup id="sr7_getai_credits">
        <sr-popup-bg></sr-popup-bg>
        <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        <sr-popup-header class="sr--text--center">            
            <h2 class="sr--popup--title"><?php echo __('How to get more AI Credits?','revslider');?></h2>            
        </sr-popup-header>        
        <sr-sp h="30"></sr-sp>
        <sr-popup-content class="sr--text--center">                
            <sr-wrap half inline class="sr-ai-credit-steps">
                <h3><?php echo __('Step 1:','revslider');?><br>
                <?php echo __('Open your Member Dashboard','revslider');?></h3>
                <sr-wrap><p class="sr--text"><?php echo __('AI Credits are assigned to individual license keys.','revslider');?><br><br>
                <a href="https://account.sliderrevolution.com/portal/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=sr7aidashboard" rel="noopener" target="_blank"><?php echo __('1. Log in to your account..','revslider');?></a><br>
                <?php echo __('2. This will take you to your Member Dashboard.','revslider');?><br>
                <?php echo __('3. Select the license key you want to add credits to.','revslider');?><br>
                <?php echo __('4. Click the AI Credits icon next to that license.','revslider');?><br><br>
                <b><?php echo __('Make sure you choose the correct license — credits are applied only to the selected key.','revslider');?></b></p></sr-wrap>
                <sr-sp h="30"></sr-sp>
                <sr-getai_step1_img></sr-getai_step1_img>
            </sr-wrap><!--
            --><sr-wrap half inline class="sr-ai-credit-steps">
                <h3><?php echo __('Step 2:','revslider');?><br>
                <?php echo __('Choose and Purchase an AI Credit Pack','revslider');?></h3>
                <sr-wrap><p class="sr--text"><?php echo __('1. Select the AI Credit pack that fits your needs.','revslider');?><br>
                <?php echo __('2. Complete the one-time purchase (no subscription).','revslider');?><br><br>          
                <b><?php echo __('Your credits will be added to the selected license immediately after payment.','revslider');?></b></p></sr-wrap>
                <sr-sp h="20"></sr-sp>
                <sr-getai_step2_img></sr-getai_step2_img>
            </sr-wrap>                   
            <sr-sp h="20"></sr-sp>               
            <sr-wrap calss="sr--text--center">
                <a href="https://www.sliderrevolution.com/help/how-to-use-slider-revolution-7-ai-features/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=sr7aifaq" target="_blank" rel="noopener" primary="" class="sr--cta sr--cta--big sr--mr--10"><?php echo __('Learn More About AI Features','revslider'); ?></a>
                <a href="https://account.sliderrevolution.com/portal/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=sr7aidashboard" target="_blank" rel="noopener" primary="" class="sr--cta sr--cta--big sr--mr--10 sr--cta--white"><?php echo __('Purchase AI Credits','revslider'); ?></a>
            </sr-wrap>            
        </sr-popup-content>
    </sr-popup>  

     <!-------------------------------------
        Premium Register Popup
    --------------------------------------->
    <sr-popup id="sr7_remote_deactivated">
        <sr-popup-bg></sr-popup-bg>
        <sr-popup-close><svg class="sr--icon" error width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        <sr-popup-header class="sr--text--center">
            <sr-wrap style="height:36px" class="sr--mb--25"><span class="sr--icon-filled sr--red sr--warning--label">!</span></sr-wrap>
            <sr-wrap style="margin:auto; max-width:500px">
                <h2 class="sr--popup--title sr--red"><?php echo __('Urgent','revslider');?></h2>
                <h2 class="sr--popup--title"><?php echo __('Your Slider Revolution License has been Deactivated!','revslider');?></h2>            
            </sr-wrap>
        </sr-popup-header>        
        <sr-popup-content class="sr--text--center">                        
            <sr-sp h="40"></sr-sp>
            <sr-wrap class="sr_premium_pwcontainer" class="sr--text--center">
                <p class="sr--text"><b><?php echo __('All of the premium features including templates,<br>add-ons and premium 1-on-1 support,<br>have been removed from your website','revslider');?></b></p>
                <sr-sp h="20"></sr-sp>
                <sr-wrap id="sr_deact_reasoning"></sr-wrap>
                <p class="sr--text"><?php echo __('We can help you restore everything now, all you <br>have to do is choose one of the options below:','revslider');?></p>
                            
                <sr-sp h="30"></sr-sp>
                <sr-wrap calss="sr--text--center">
                    <sr-button data-action="B.popUp.hide, license.showPremiumCheck+50" primary=""  class="sr--cta sr--cta--big sr--mr--20"><?php echo __('Register License Key','revslider'); ?></sr-button><!--
                    --><a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=deactivatedmodal&utm_campaign=srusers&utm_content=buykey" target="_blank" rel="noopener" primary="" class="sr--cta sr--cta--big"><?php echo __('Buy a License','revslider'); ?></a>
                </sr-wrap>
            </sr-wrap>     
            <sr-wrap style="width:480px; margin:0 auto; text-align:center"> 
                <sr-sp h="20"></sr-sp>
                <a href="https://www.sliderrevolution.com/help/what-happens-to-my-slider-revolution-content-when-my-license-expires/?utm_source=admin&utm_medium=deactivatedmodal&utm_campaign=srusers&utm_content=deactivatedfaq" target="_blank" rel="noopener" class="sr--link--text sr--mr--20 sr--link--before"><?php echo __('Wondering why this happened? Click here!','revslider'); ?></a>
            </sr-wrap>
        </sr-popup-content>
    </sr-popup>  

    <!-------------------------------------
        
    --------------------------------------->
    <sr-popup id="sr7_rchd_tl">
        <sr-popup-bg></sr-popup-bg>
        <sr-popup-close><svg class="sr--icon" error width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        <sr-popup-header class="sr--text--center">
            <sr-wrap style="height:36px" class="sr--mb--25"><span class="sr--icon-filled sr--red sr--warning--label">!</span></sr-wrap>
            <sr-wrap style="margin:auto; max-width:500px">
                <h2 class="sr--popup--title sr--red"><?php echo __('Urgent','revslider');?></h2>
                <h2 class="sr--popup--title"><?php echo __('You\'ve Hit the Free Module Limit','revslider');?></h2>            
            </sr-wrap>
        </sr-popup-header>        
        <sr-popup-content class="sr--text--center">                        
            <sr-sp h="40"></sr-sp>
            <sr-wrap class="sr_premium_pwcontainer" class="sr--text--center">
                <p class="sr--text"><b><?php echo __('You\'re using','revslider')?><span class="sr--text sr--red" style="font-weight:700;margin:0px 5px 0px"><?php echo __('3','revslider');?></span><?php echo __('of the 3 modules included in the free version. To create more, upgrade to Premium.','revslider');?></b></p>
                <sr-sp h="20"></sr-sp>
                <sr-wrap id="sr_deact_reasoning"></sr-wrap>
                <p class="sr--text"><?php echo __('Premium also unlocks 200+ ready-to-go<br>templates, 30+ add-ons, AI-powered image<br>generation, and 1-on-1 support.','revslider');?></p>
                            
                <sr-sp h="30"></sr-sp>
                <sr-wrap calss="sr--text--center">
                    <sr-button data-action="B.popUp.hide, license.showPremiumCheck+50" primary=""  class="sr--cta sr--cta--big sr--mr--20"><?php echo __('Register License Key','revslider'); ?></sr-button><!--
                    --><a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=deactivatedmodal&utm_campaign=srusers&utm_content=buykey" target="_blank" rel="noopener" primary="" class="sr--cta sr--cta--big"><?php echo __('Buy a License','revslider'); ?></a>
                </sr-wrap>
            </sr-wrap>                 
        </sr-popup-content>
    </sr-popup>  

    <sr-popup id="sr7_rchd_tl_mr">
        <sr-popup-bg></sr-popup-bg>
        <sr-popup-close><svg class="sr--icon" error width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        <sr-popup-header class="sr--text--center">
            <sr-wrap style="height:36px" class="sr--mb--25"><span class="sr--icon-filled sr--red sr--warning--label">!</span></sr-wrap>
            <sr-wrap style="margin:auto; max-width:500px">
                <h2 class="sr--popup--title sr--red"><?php echo __('Urgent','revslider');?></h2>
                <h2 class="sr--popup--title"><?php echo __('Your Modules Are Safe — but You\'ve<br>Reached the Free Limit','revslider');?></h2>            
            </sr-wrap>
        </sr-popup-header>        
        <sr-popup-content class="sr--text--center">                        
            <sr-sp h="40"></sr-sp>
            <sr-wrap class="sr_premium_pwcontainer" class="sr--text--center">
                <p class="sr--text"><b><?php echo __('We\'ve introduced a 3-module limit for free accounts.<br>Since you already have','revslider')?><span id="sr7_rchd_tl_mr_mnt" class="sr--text sr--red" style="font-weight:700;margin:0px 5px 0px"><?php echo __('x','revslider');?></span><?php echo __('modules, all of them will<br>continue to work normally.','revslider');?></b></p>
                <sr-sp h="20"></sr-sp>
                <sr-wrap id="sr_deact_reasoning"></sr-wrap>
                <p class="sr--text"><?php echo __('To add new modules going forward, upgrade to<br>Premium — which also unlocks 200+ templates,<br>30+ add-ons, and AI image generation.','revslider');?></p>
                            
                <sr-sp h="30"></sr-sp>
                <sr-wrap calss="sr--text--center">
                    <sr-button data-action="B.popUp.hide, license.showPremiumCheck+50" primary=""  class="sr--cta sr--cta--big sr--mr--20"><?php echo __('Register License Key','revslider'); ?></sr-button><!--
                    --><a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=deactivatedmodal&utm_campaign=srusers&utm_content=buykey" target="_blank" rel="noopener" primary="" class="sr--cta sr--cta--big"><?php echo __('Buy a License','revslider'); ?></a>
                </sr-wrap>
            </sr-wrap>                 
        </sr-popup-content>
    </sr-popup>  

    <!-------------------------------------
        DUPLICATE SLIDE DOUBLE CHECK POPUP
    --------------------------------------->
   <sr-popup id="sr-duplicate-slide-check">
        <sr-popup-header class="sr--text--center">
            <h2 class="sr--popup--title" id=""><svg class="sr--icon" width="20" height="22" transform="translate(0, 0)" style="fill:var(--sr-col-b0);"><use xlink:href="#Dashboard_Duplicate"></use></svg><sr-sp h="10"></sr-sp><?php echo __('Duplicate Slide','revslider');?></h2>
            <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
        </sr-popup-header>
        <sr-sp h="10"></sr-sp>
        <sr-popup-content class="sr--text--center">
            <sr-wrap><span class="sr--text"><?php echo __('Include scenes / actions?','revslider');?></span></sr-wrap>
            <sr-sp h="20"></sr-sp>
            <sr-wrap wide basic style="max-width:200px;margin:auto;">
                <sr-wrap id="sr_slide_duplicate_scenes_wrap" half basic="" class="sr--form--grp sr--mr--10"><sr-onoff id="sr_slide_duplicate_scenes" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Scenes','revslider'); ?></span></sr-wrap><!--
                --><sr-wrap half basic="" id="sr_slide_duplicate_actions_wrap" class="sr--form--grp"><sr-onoff id="sr_slide_duplicate_actions" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Actions','revslider'); ?></span></sr-wrap>
            </sr-wrap>
            <sr-sp h="30"></sr-sp>
            <sr-wrap class="sr--text--center">
                <sr-button clean class="sr--cta sr--mr--10 sr--mb--0"><?php echo __('Cancel','revslider');?></sr-button><!--
                --><sr-button primary data-action="editor.slides.duplicateRun" data-aparams="check" class="sr--cta sr--mr--0 sr--mb--0"><?php echo __('Duplicate Slide','revslider');?></sr-button>
            </sr-wrap>            
        </sr-popup-content>
    </sr-popup>  

</sr-popups>



<script>
    window.SR7 ??={};    
    SR7.tmps = {
        fset_slide_parameters : '<sr-wrap basic>'+
										'<sr-input class="sr--mr--5" threefifth><input name="Parameter" replace r="##index##.v" viewchild="slide_parameters" placeholder="Parameter" type="text"><span noicon="" class="sr--form--otitle">(##key+1##)</span></sr-input>' +
										'<sr-input class="sr--mr--10"  style="width:85px"><input name="Cut at" replace r="##index##.l" viewchild="slide_parameters" type="text" validate="true" min="0" max="500" number="true"><span noicon="" class="sr--form--otitle"><?php _e('Cut at','revslider'); ?></span></sr-input>' +
										'<sr-button data-action="B.fieldSet.remove" viewchild="slide_parameters" keepPopUp="true" data-aparams="##index##" clean="" class="sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Delete"></use></svg></sr-button>'+
                                '</sr-wrap>',
		fset_custom_fonts : '<sr-input class="sr--mr--5" style="width:80px"><input name="Name" viewchild="glbls_4" r="##index##.family" type="text" placeholder="Name"></sr-input>' +
                            '<sr-input class="sr--mr--5" style="width:150px"><input name="CSS URL" viewchild="glbls_4" r="##index##.url" type="text" placeholder="CSS URL"></sr-input>'+               
                            '<sr-input class="sr--mr--5" style="width:100px"><input name="Weights"viewchild="glbls_4" r="##index##.weights" type="text" placeholder="Weights"></sr-input>'+
                            '<sr-drop viewchild="glbls_4" r="##index##.in" style="width:120px; vertical-align:top" data-v="both" class="sr--mr--10"><sr-drop-view><span class="sr--drop--value"><?php echo __('Editor & Live','revslider');?></span><span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span></sr-drop-view>'+
                                '<sr-drops data-v="both"><?php echo __('Editor & Live','revslider');?></sr-drops>'+
                                '<sr-drops data-v="live"><?php echo __('Live','revslider');?></sr-drops>'+
                                '<sr-drops data-v="editor"><?php echo __('Editor','revslider');?></sr-drops>'+
                            '</sr-drop>'+
                            '<sr-button data-action="B.globals.customFont.removeRow" keepPopUp="true" viewchild="glbls_4" data-aparams="##index##" unsaved="" class="sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" transform="translate(0, -1)"><use xlink:href="#Dashboard_Delete"></use></svg></sr-button>'
	}
</script>
