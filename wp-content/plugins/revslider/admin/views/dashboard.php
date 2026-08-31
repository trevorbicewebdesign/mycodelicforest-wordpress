<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

/**
 * @var RevSliderFunctionsAdmin $sr_af
 * @var RevSliderAddons         $sr_addon
 * @var bool                    $sr_valid
 */

 if(!defined('ABSPATH')) exit();
 
//ToDO - Load things differently
require_once(RS_PLUGIN_PATH . 'admin/views/popups.php');    
echo RevSliderFunctions::get_sprite_svg();


if(!defined('ABSPATH')) exit();
global $SR_GLOBALS;

$system_config	= $sr_af->get_system_requirements();
$current_user	= wp_get_current_user();
$sr_code		= $sr_af->get_options(['system', 'license']);
$time			= current_time('H'); //site timezone, not the server's - the greeting below depends on it
$timezone		= wp_timezone_string();
$hi				= __('Good Evening ', 'revslider');
$slogan         = __('Boundless Creativity Awaits', 'revslider');
$sr_new_addon_counter	 = $sr_af->get_options(['counter'], false, false, 'rs-addons');
$sr_new_addon_counter	 = ($sr_new_addon_counter === false) ? count($sr_addon->get_addon_list(true)) : $sr_new_addon_counter;
$sr_new_temp_counter	 = $sr_af->get_options(['counter'], false, false, 'rs-templates');
if($sr_new_temp_counter === false){
	$_sr_tmplts			 = $sr_af->get_options(['templates'], false, false, 'rs-templates');
	$_sr_tmplts			 = $sr_af->do_uncompress($_sr_tmplts);
	$sr_new_temp_counter = (isset($_rs_tmplts['slider'])) ? count($_rs_tmplts['slider']) : $sr_new_temp_counter;
}
$sr_notices = $sr_af->get_notices();

if($time < '12'){
	$hi = __('Good Morning ', 'revslider');
}elseif($time >= '12' && $time < '17'){
	$hi = __('Good Afternoon ', 'revslider');
}
?>

<sr-view id="sr-dashboard" class="sr--page--padding">
    
    <!-- MENU -->
    <sr-section id="sr-dashboard-menu" class="sr--navigation sr--fixed sr--full" style="min-width:980px">
        <sr-wrap class="sr--maxw--1280">
            <sr-wrap class="sr--left sr--pt--10"><sr-logo class="sr--mr--10"></sr-logo></sr-wrap>
            <sr-nav-wrap style="transform:none; margin-left:-133px" class="sr--center">            
                <sr-nav-btn data-action="B.globals.open" data-aparams="true,below:center" id="sr_global_options_caller"><sr-icon-wrap><svg class="sr--icon" width="18" height="17"><use xlink:href="#Dashboard_Global"></use></svg></sr-icon-wrap><?php echo __('Settings','revslider');?></sr-nav-btn>
                <sr-nav-btn data-action="B.scrollOnAction" data-aparams="sr_overview_menu,-150" ><sr-icon-wrap><svg class="sr--icon" width="16" height="16"><use xlink:href="#Dashboard_Modules"></use></svg></sr-icon-wrap><?php echo __('Modules','revslider');?></sr-nav-btn>
                <sr-nav-btn data-action="B.library.open" data-aparams="template_library,sr_tlib"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Dashboard_Templates"></use></svg></sr-icon-wrap><?php echo __('Templates','revslider');?></sr-nav-btn>
                <sr-nav-btn data-action="B.library.open" data-aparams="addon_library,sr_alib"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Dashboard_Addons"></use></svg></sr-icon-wrap><?php echo __('Addons','revslider');?></sr-nav-btn>
                <sr-nav-btn id="sr-dark-light-switch"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Dashboard_Dark_Mode"></use></svg></sr-icon-wrap><span class="sr--nav--text"><?php echo __('Go Dark','revslider');?></span></sr-nav-btn>
            </sr-nav-wrap>
            <sr-wrap class="sr--right sr--pt--8">
                <sr-button id="sr_videoguide_call" data-action="B.videoGuide" primary class="sr--cta sr--cta--big sr--cta--video sr--mr--8 sr--oicon"><svg class="sr--icon" width="10" height="14.434" transform="translate(2, 0)"><use xlink:href="#Play"></use></svg></sr-button><!--
                --><a href="https://www.sliderrevolution.com/help-center/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=helpcenter" target="_blank" rel="noopener" class="sr--awrap sr--mr--8" style="display:inline-block">
                    <sr-button primary class="sr--cta sr--cta--big"><?php echo __('Help','revslider');?></sr-button>
                </a><!--
                --><a href="https://account.sliderrevolution.com/portal/?utm_source=admin&amp;utm_medium=button&amp;utm_campaign=srusers&amp;utm_content=members" target="_blank" rel="noopener" class="sr--awrap" style="display:inline-block">
                    <sr-button clean class="sr--cta sr--cta--big"><svg class="sr--icon" width="12" height="18" transform="translate(0, -2)"><use xlink:href="#Dashboard_User"></use></svg><?php echo __('Account','revslider');?></sr-button>
                </a><!--
                ---><sr-lbl-w data-action="system.fixIssues" class="sr-xxl-iconw sr--icon-filled system_all_summary_icon sr--ml--8"><svg class="sr--icon" width="14" height="19.25"><use xlink:href="#General_Check_Small"></use></svg><sr-lbl class="sr--lbl--abs"></sr-lbl></sr-lbl-w>
            </sr-wrap>
        </sr-wrap>
    </sr-section><!--    
--><sr-sp h="80"></sr-sp>
    <sr-view-content> 
        <!-- ADVERT AREA -->
		<?php 
		foreach($sr_notices ?? [] as $notice){
			$sr_note_disable = $sr_af->_truefalse($sr_af->get_val($notice, 'disable'));
			$sr_note_code	 = $sr_af->get_val($notice, 'code');
			$sr_note_theme	 = $sr_af->get_val($notice, 'theme', 'light');
			$sr_closer_class = ($sr_note_theme === 'light') ? 'sr--dark' : 'sr--light';
			echo '<div style="border-radius:10px;overflow:hidden;margin-bottom:80px; position:relative;"';
			if(!empty($sr_af->get_val($notice, 'id'))) echo ' id="'.$sr_af->get_val($notice, 'id').'"';
			echo ' data-code="'.$sr_note_code.'"';
			if(isset($notice->registered)){
				$sr_note_registered = ($this->_truefalse($sr_af->get_val($notice, 'registered')) === true) ? 'true' : 'false';
				echo ' data-registered="'.$sr_note_registered.'"';
			}
			echo '>'."\n";
			if($sr_note_disable === true){
				echo '<sr-advert-close data-action="advert.close" class="'.$sr_closer_class.'" data-code="'.$sr_note_code.'"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-advert-close>'."\n";
			}
			echo $sr_af->get_val($notice, 'text');
			echo '</div>'."\n";
		}
		?>

        <!--WELCOME -->
        <!-- SYSTEM -->
        <sr-sections-wrap class="sr--section--flex sr--section--noflexbreak" style="min-width:980px">
            <sr-section class="sr--grid--threefifth sr--filled" style="width:100px">
                <sr-section-header>
                    <h2 class="sr--page--title"><?php echo $hi; echo ucwords($current_user->display_name);?></h2>                    
                </sr-section-header>
                <sr-sp h="25"></sr-sp>
                <sr-section-content>
                        <sr-wrap class="sr--carousel--nav">    
                            <sr-carousel-nav data-dir="left" class="sr--carousel--arrow"><svg class="sr--icon sr--rot--90" width="14.14" height="8.49"><use xlink:href="#General_Expand_Large"></use></svg></sr-carousel-nav><!--
                            --><sr-carousel-nav data-dir="right" class="sr--carousel--arrow"><svg class="sr--icon sr--rot--d90" width="14.14" height="8.49"><use xlink:href="#General_Expand_Large"></use></svg></sr-carousel-nav>
                        </sr-wrap>                        
                        <sr-panels-wrap id="sr_latest_news" class="sr--flex--row sr--carousel" data-minw="235" data-maxamnt="4">
                            <sr-panel id="sr_migrate_panel" class="sr--filled sr--advert--panel sr--flex--element sr--carousel--element">                                
                                <sr-panel-content class="sr--video--panel--content">
                                    <video class="sr--image--corner--80" style="overflow: hidden;background: transparent;border-radius: 14px;"autoplay muted loop playsinline preload="auto" aria-hidden="true">
                                        <source src="<?php echo RS_PLUGIN_URL;?>/admin/assets/images/bg/migration-loop.mp4" type="video/mp4">
                                    </video>
                                    <h4 class="sr--text--title"><?php echo __('SR7 Data Migration','revslider');?></h4>
                                    <p class="sr--text"><?php echo __('Data Migration is running.','revslider');?> <b style="font-weight:500"><?php echo __('Please Wait!','revslider');?></b></p>  
                                    <sr-sp h="20"></sr-sp>  
                                    <wrap wide>
                                        <span half class="sr--text--list sr--mr--10"><?php echo __('Progress:','revslider'); ?></span><span id="sr_migration_togo" class="sr--text--value sr--good sr--mr--30"></span><!--
                                        --><span id="sr_migration_issues" half class="sr--text--list sr--mr--10"><?php echo __('Issues:','revslider'); ?></span><span id="sr_migration_issues_amnt" style="cursor:pointer" data-action="B.migrate.showErrors" class="sr--text--value sr--bad sr--link--after"></span>
                                    </wrap>
                                </sr-panel-content>                                
                            </sr-panel>
                            <?php if($sr_af->is_wpml_active()){ ?>
                            <sr-panel class="sr--filled sr--advert--panel sr--flex--element sr--carousel--element">
                                <a class="sr--panel--link sr--wpml--news sr--wpml--news--unreg" style="cursor:pointer" data-action="B.showRegisterSliderInfo">
                                    <sr-panel-content>
                                        <h4 class="sr--text--title"><?php echo __('Slider Revolution is WPML compatible','revslider');?></h4>
                                        <p class="sr--text"><?php echo __('Translate entire Slider Revolution modules or individual layers with AI in seconds. Works standalone or seamlessly with WPML for fully localized content.','revslider');?></p>
                                        <img class="sr--image--corner--80" src="<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/wpmlpost1.webp">
                                    </sr-panel-content>
                                </a><!--
                                --><a class="sr--panel--link sr--wpml--news sr--wpml--news--reg" style="cursor:pointer" data-action="B.library.open" data-aparams="addon_library,sr_alib">
                                    <sr-panel-content>
                                        <h4 class="sr--text--title"><?php echo __('Slider Revolution is WPML compatible','revslider');?></h4>
                                        <p class="sr--text"><?php echo __('Translate entire Slider Revolution modules or individual layers with AI in seconds. Works standalone or seamlessly with WPML for fully localized content.','revslider');?></p>
                                        <img class="sr--image--corner--80" src="<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/wpmlpost2.webp">
                                    </sr-panel-content>
                                </a>
                            </sr-panel>
                            <?php } ?>
                            <sr-panel class="sr--filled sr--advert--panel sr--flex--element sr--carousel--element">
                                <a class="sr--panel--link" target="_blank" rel="noopener" href="https://www.sliderrevolution.com/help/how-to-use-slider-revolution-7-ai-features/?utm_source=admin&utm_medium=newsrotator&utm_campaign=srusers&utm_content=aifeatures">
                                    <sr-panel-content>
                                        <h4 class="sr--text--title"><?php echo __('Create any image you can imagine','revslider');?></h4>
                                        <p class="sr--text"><?php echo __('SR7\'s built-in AI image generator lets you create custom visuals on the spot. Try it in the editor today!','revslider');?></p>
                                        <img class="sr--image--corner--80" src="<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/post7.webp">
                                    </sr-panel-content>
                                </a>
                            </sr-panel><sr-panel class="sr--filled sr--advert--panel sr--flex--element sr--carousel--element">
                                <a class="sr--panel--link" target="_blank" rel="noopener" href="https://www.sliderrevolution.com/help/whats-changed-from-slider-revolution-6-to-7/?utm_source=admin&utm_medium=newsrotator&utm_campaign=srusers&utm_content=whatchanged">
                                    <sr-panel-content>
                                        <h4 class="sr--text--title"><?php echo __('What\'s Changed from SR6 to SR7?','revslider');?></h4>
                                        <p class="sr--text"><?php echo __('New editor. Familiar workflow. Fully compatible. Learn more here!','revslider');?></p>
                                        <img class="sr--image--corner--80" src="<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/post1.webp">
                                    </sr-panel-content>
                                </a>
                            </sr-panel>
                            <sr-panel class="sr--filled sr--advert--panel sr--flex--element sr--carousel--element"> 
                                <a class="sr--panel--link" target="_blank" rel="noopener" href="https://www.sliderrevolution.com/editor-tour/?utm_source=admin&utm_medium=newsrotator&utm_campaign=srusers&utm_content=editortour">
                                    <sr-panel-content>
                                        <h4 class="sr--text--title"><?php echo __('Editor Tour','revslider');?></h4>
                                        <p class="sr--text"><?php echo __('Get up to speed fast with the Slider Revolution 7 Editor Tour','revslider');?></p>                                    
                                        <img class="sr--image--corner--80" src="<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/post2.webp">
                                    </sr-panel-content>
                                </a>
                            </sr-panel>
                            <sr-panel class="sr--filled sr--advert--panel sr--flex--element sr--carousel--element">                    
                                <a class="sr--panel--link" target="_blank" rel="noopener" href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=newsrotator&utm_campaign=srusers&utm_content=pricingpage">
                                    <sr-panel-content>
                                        <h4 class="sr--text--title"><?php echo __('Cut per-site cost up to 97%','revslider');?></h4>
                                        <p class="sr--text"><?php echo __('From $39 → $0.84 per website with the 250-site Agency Plan.','revslider');?></p>
                                        <img class="sr--image--corner--80" src="<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/post3.webp">
                                    </sr-panel-content>
                                </a>
                            </sr-panel>
                            <sr-panel class="sr--filled sr--advert--panel sr--flex--element sr--carousel--element"> 
                                <a class="sr--panel--link" target="_blank" rel="noopener" href="https://support.sliderrevolution.com/?utm_source=admin&utm_medium=newsrotator&utm_campaign=srusers&utm_content=supportticket">
                                    <sr-panel-content>
                                        <h4 class="sr--text--title"><?php echo __('Stuck somewhere and need Help?','revslider');?></h4>
                                        <p class="sr--text"><?php echo __('Submit a support ticket and our team will get back to you asap!','revslider');?></p>
                                        <img class="sr--image--corner--80" src="<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/post4.webp">
                                    </sr-panel-content>
                                </a>
                            </sr-panel>
                        </sr-panels-wrap>
                </sr-section-content>
            </sr-section>
            <sr-section class="sr--grid--twofifth sr--filled sr--with--tabs">
                <sr-section-tabs>
                    <sr-tab class="sr--tab--onehalf <?php if ($sr_valid) echo 'sr--active--tab'; ?>" data-sr-tabc="sr-updates"><span class="sr--section--title">Updates</span><span class="sr--icon-filled sr--ml--10 system_summary_icon"><svg class="sr--icon"><use xlink:href="#General_Check_Small"></use></svg></span></sr-tab><!--
                    --><sr-tab class="sr--tab--onehalf <?php if (!$sr_valid) echo 'sr--active--tab'; ?>" data-sr-tabc="sr-license"><span class="sr--section--title">License</span><span class="sr--icon-filled sr--ml--10 license_summary_icon"><svg class="sr--icon"><use xlink:href="#General_Check_Small"></use></svg></span></sr-tab>
                </sr-section-tabs>
                <sr-tab-content id="sr-updates" class="sr--good <?php if ($sr_valid) echo 'sr--open'; ?>">
                    <span class="sr--text--list"><?php echo __('Installed Version:','revslider'); ?></span><span id="system_revision_number" class="sr--text--value"></span><br>
                    <span class="sr--text--list"><?php echo __('Available Version:','revslider'); ?></span><a href="https://www.sliderrevolution.com/changelog/" target="_blank" id="system_latest_revision" class="sr--text--value"></a><a href="https://www.sliderrevolution.com/changelog/" target="_blank" class="sr--cta sr--link--after sr--keep--black"></a><br>
                    <span class="sr--text--list"><?php echo __('Addons:','revslider'); ?></span><span id="system_addons_counter" class="sr--text--value"></span><br>
                    <span class="sr--text--list"><?php echo __('System Requirements:','revslider'); ?></span><span data-action="system.details" class="system_requirements_counter sr--text--value sr--cta sr--link--after"></span>
                    <sr-sp h="20"></sr-sp>
                    <sr-button clean data-action="system.checkUpgrades" class="sr--cta sr--mr--10"><svg class="sr--icon" width="13.93" height="14"><use xlink:href="#General_Refresh"></use></svg><?php echo __('Refresh','revslider');?></sr-button>
                    <sr-sp h="50"></sr-sp>
                    <sr-button primary data-action="system.updateCore" id="button_system" style="display:none;position:absolute" class="sr--cta sr--cta--big sr--abs--bottom--left"></sr-button>
                </sr-tab-content>
                
                <sr-tab-content id="sr-license" class="sr--good <?php if (!$sr_valid) echo 'sr--open'; ?>">
                    <span class="sr--text--list"><?php echo __('Status:','revslider'); ?></span><span id="system_license_status" class="sr--text--value"></span><br>
                    <span class="sr--text--list"><?php echo __('Premium Features:','revslider'); ?></span><span id="system_premium_status" data-action="B.popUp.show" data-aparams="premium_features" data-position="center" class="sr--text--value sr--cta sr--link--after"></span><br>
                    <sr-sp h="10"></sr-sp>
                    <input class="sr--input--text sr--minw--200" type="text" id="system_license_code" keydown="licenseCheck+25" autocomplete="off" data-onchange="licenseCheck" data-action="licenseCheck" placeholder="<?php echo __('Enter your License Key','revslider'); ?>" value="<?php echo $sr_code; ?>"><br>
                    <sr-sp h="20"></sr-sp>
                    <a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&amp;utm_medium=button&amp;utm_campaign=srusers&amp;utm_content=buykey" target="_blank" rel="noopener" class="sr--link--text sr--mr--20 sr--show--onreg sr--link--before"><?php echo __('Need more Licenses?','revslider'); ?></a>
                    <a href="https://www.sliderrevolution.com/faq/where-to-find-purchase-code/?utm_source=admin&amp;utm_medium=button&amp;utm_campaign=srusers&amp;utm_content=findkey" target="_blank" rel="noopener" class="sr--link--text sr--mr--20 sr--show--onnotreg sr--link--before"><?php echo __('How to find my Key?','revslider'); ?></a>
                    <a href="https://www.sliderrevolution.com/faqs-and-tutorials/?utm_source=admin&utm_medium=activationmodal&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener" class="sr--link--text sr--link--before"><?php echo __('License FAQ','revslider'); ?></a>                
                    <sr-sp h="50"></sr-sp>
                    <sr-button id="button_license" data-action="license.set" primary style="position:absolute" class="sr--cta sr--cta--big sr--abs sr--abs--bottom--left"></sr-button>
                </sr-tab-content>  
                
            </sr-section>            
        </sr-sections-wrap><!--    
        --><sr-sp h="80"></sr-sp>
        
        <?php require_once(RS_PLUGIN_PATH . 'admin/views/overview.php');?>
        
        <sr-sp h="80"></sr-sp>

        <!-- FOOTER -->
        <sr-section class="sr--filled">
            <sr-section-header>
                <h2 class="sr--page--title"><?php echo __('Need Help?','revslider');?><br><?php echo __('We Got You Covered.','revslider');?></h2>
                <!--<sr-button primary class="sr--cta sr--cta--big sr--abs--top--right"><svg class="sr--icon sr--mr--10" width="18" height="14.32"><use xlink:href="#Dashboard_Newsletter"></use></svg><?php echo __('Join Our Newsletter','revslider');?></sr-button>-->
            </sr-section-header>
            <sr-sp h="45"></sr-sp>
            <sr-section-content>
                <sr-panels-wrap class="sr--grid">
                    <sr-panel class="sr--with--bg" style="height:300px">
                        <a href="https://www.sliderrevolution.com/help/how-to-use-slider-revolution-7-ai-features/?utm_source=admin&utm_medium=footerlink&utm_campaign=srusers&utm_content=editortour" target="_blank">
                            <sr-wrap class="sr--blurred--bg" style="background-image:url(<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/post6.webp)"></sr-wrap>                        
                            <sr-panel-content>
                                <h4 class="sr--section--title sr--white"><?php echo __('How to Use Slider Revolution 7 AI Features','revslider');?></h4>
                                <p class="sr--text sr--white"><?php echo __('Slider Revolution 7 comes with in-built AI tools for image and text, helping you create and edit faster than ever before.','revslider');?></p>
                                <sr-sp h="150"></sr-sp>                            
                            </sr-panel-content>
                            <sr-img class="sr--image--corner--big" style="border-radius:8px; overflow:hidden; background-image:url(<?php echo RS_PLUGIN_URL;?>/admin/assets/images/dashboard/post6.webp)"></sr-img>
                        </a>
                    </sr-panel>
                    <sr-panel class="sr--filled">                    
                        <sr-panel-content>
                            <h4 class="sr--section--title"><?php echo __('Coming from Version 6?','revslider');?></h4>
                            <p class="sr--text"><?php echo __('We are confident that you will feel right at home in Slider Revolution 7.  Here are useful resources in case you have any questions or run into issues:','revslider');?></p>
                            <sr-sp h="30"></sr-sp>
                            <a href="https://www.sliderrevolution.com/help/whats-changed-from-slider-revolution-6-to-7/?utm_source=admin&utm_medium=footerlink&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener" class="sr--link--text sr--cta sr--mr--20 sr--mb--15  sr--link--before"><?php echo __('What’s Changed from SR6 to SR7?','revslider'); ?></a><br>
                            <a href="https://www.sliderrevolution.com/help/setup-guide-install-unlock-update/?utm_source=admin&utm_medium=footerlink&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener" class="sr--link--text sr--cta sr--mr--20 sr--mb--15  sr--link--before"><?php echo __('Setup Guide (install, registration, update)','revslider'); ?></a><br>                            
                        </sr-panel-content>
                    </sr-panel>
                    <sr-panel class="sr--filled">                    
                        <sr-panel-content>
                            <h4 class="sr--section--title"><?php echo __('Resources','revslider');?></h4>
                            <p class="sr--text"><?php echo __('Resources that most of our users found helpful:','revslider');?></p>
                            <sr-sp h="30"></sr-sp>
                            <a href="https://www.sliderrevolution.com/help-center/?utm_source=admin&utm_medium=footerlink&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener" class="sr--link--text sr--cta sr--mr--20 sr--mb--15  sr--link--before"><?php echo __('Help Center','revslider'); ?></a><br>
                            <a href="https://www.sliderrevolution.com/faqs-and-tutorials/?utm_source=admin&utm_medium=footerlink&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener" class="sr--link--text sr--cta sr--mr--20 sr--mb--15  sr--link--before"><?php echo __('FAQs & Tutorials','revslider'); ?></a><br>
                            <a href="https://www.sliderrevolution.com/wordpress-templates/?utm_source=admin&utm_medium=footerlink&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener" class="sr--link--text sr--cta sr--mr--20 sr--mb--15  sr--link--before"><?php echo __('Templates','revslider'); ?></a><br>
                            <a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=footerlink&utm_campaign=srusers&utm_content=helpresource" target="_blank" rel="noopener" class="sr--link--text sr--cta sr--mr--20 sr--mb--15  sr--link--before"><?php echo __('Explore license options','revslider'); ?></a><br>
                        </sr-panel-content>
                    </sr-panel>               
                </sr-panels-wrap>            
            </sr-section-content>
            <sr-sp h="45"></sr-sp>
            <sr-section-footer>
                <sr-wrap class="sr--grid">
                    <sr-wrap class="sr--text--left">
                        <a href="https://x.com/revslider" target="_blank" rel="noopener" class="sr--mr--20 sr--hov--scale"><svg class="sr--icon" width="22.12" height="20"><use xlink:href="#Dashboard_X"></use></svg></a><!--
                        --><a href="https://www.facebook.com/official.sliderrevolution" target="_blank" rel="noopener" class="sr--mr--20 sr--hov--scale"><svg class="sr--icon" width="20.12" height="20"><use xlink:href="#Dashboard_Facebook"></use></svg></a><!--
                        --><a href="https://www.instagram.com/sliderrevolution" target="_blank" rel="noopener" class="sr--mr--20 sr--hov--scale"><svg class="sr--icon" width="20" height="20"><use xlink:href="#Dashboard_Insta"></use></svg></a><!--
                        --><a href="https://www.dribbble.com/sliderrevolutio" target="_blank" rel="noopener" class="sr--mr--20 sr--hov--scale"><svg class="sr--icon" width="20" height="20"><use xlink:href="#Dashboard_Dribbble"></use></svg></a><!--
                        --><a href="https://www.pinterest.com/sliderrevolution" target="_blank" rel="noopener" class="sr--mr--20 sr--hov--scale"><svg class="sr--icon" width="19.97" height="20"><use xlink:href="#Dashboard_Pinterest"></use></svg></a><!--                        
                        --><a href="https://www.youtube.com/user/ThemePunch" target="_blank" rel="noopener" class="sr--mr--20 sr--hov--scale"><svg class="sr--icon" width="28.39" height="20"><use xlink:href="#Dashboard_YouTube"></use></svg></a>                   
                    </sr-wrap>
                    
                    <sr-wrap class="sr--text--right"><?php echo __('Slider Revolution © 2026','revslider'); ?></sr-wrap>
                </sr-wrap>
            </sr-section-footer>
        </sr-section>
    </sr-view-content>
</sr-view>

<script>    
    window.SR7 ??={};
    SR7.VIEW = 'dashboard';
    SR7.E ??={};
    SR7.E.system = _tpt.fixResponse(<?php echo $sr_af->json_encode_client_side($system_config); ?>);
    SR7.E.new ??={};
	SR7.E.new.addons = '<?php echo $sr_new_addon_counter; ?>';
	SR7.E.new.templates = '<?php echo $sr_new_temp_counter; ?>';

    SR7.B.initDashboard();
</script>
