<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-popup id="sr_copyright" class="sr--popup--tabs">
    <sr-popup-header class=""> 
        <span class="sr-text"><?php echo __('Copyright & Licensing - Slider Revolution Library','revslider');?></span>   
        <sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
    </sr-popup-header>  
    <sr-popup-content>
        <sr-wrap class="sr--popup--sidebar sr--no--border">
            <sr-sp h="40"></sr-sp>
            <sr-wrap>
                <sr-lib-tag data-sr-tabc="sr_cr_templates" class="sr--tab--call selected"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Dashboard_Modules"></use></svg><span><?php echo __('Templates','revslider');?></span></sr-lib-tag>
                <sr-lib-tag data-sr-tabc="sr_cr_images" class="sr--tab--call"><svg class="sr--icon" width="20" height="15"><use xlink:href="#Dashboard_Thumbs"></use></svg><span><?php echo __('Images','revslider');?></span></sr-lib-tag>
                <sr-lib-tag data-sr-tabc="sr_cr_objects" class="sr--tab--call"><svg class="sr--icon" width="18" height="13.114"><use xlink:href="#Elements_Icon"></use></svg><span><?php echo __('Objects','revslider');?></span></sr-lib-tag>
                <sr-lib-tag data-sr-tabc="sr_cr_videos" class="sr--tab--call"><svg class="sr--icon" width="20" height="16.36"><use xlink:href="#Elements_Video"></use></svg><span><?php echo __('Videos','revslider');?></span></sr-lib-tag>
                <sr-lib-tag data-sr-tabc="sr_cr_svg" class="sr--tab--call"><svg class="sr--icon" width="20" height="21.11"><use xlink:href="#Elements_Bubble"></use></svg><span><?php echo __('SVG Icons','revslider');?></span></sr-lib-tag>
                <sr-lib-tag data-sr-tabc="sr_cr_icon" class="sr--tab--call"><svg class="sr--icon" width="16" height="13" transform="translate(0, 0)"><use xlink:href="#Options_Font_Size"></use></svg><span><?php echo __('Font Icons','revslider');?></span></sr-lib-tag>
                <!--<sr-lib-tag data-sr-tabc="sr_cr_layers" class="sr--tab--call"><svg class="sr--icon" width="14" height="14"><use xlink:href="#Top_Bar_Elements"></use></svg><span>Layers</span></sr-lib-tag>-->
            </sr-wrap>
        </sr-wrap>
        <sr-wrap class="sr--popup--tab--content"> 
            <sr-wrap class="sr--popup--tab--content--inner">
                <sr-wrap class="sr--tab--content sr--open" id="sr_cr_templates">
                    <h2 class="sr--popup--medium--title"><?php echo __('Terms of using Layer Group Objects from the Library','revslider');?></h2>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Templates from the Slider Revolution Library must only be used with a','revslider');?> <a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=buykey" rel="noopener" target="_blank" class="sr--a--link"><?php echo __('registered license key','revslider');?></a> <?php echo __('on that particular website.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Media assets used in the respective templates, are licensed according to the here mentioned license terms (see list on the left).','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Slider Revolution Add-Ons must only be used with a','revslider');?> <a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=buykey" rel="noopener" target="_blank" class="sr--a--link"><?php echo __('registered license key','revslider');?></a> <?php echo __('on that particular website.','revslider');?></span>
                    <sr-sp h="45"></sr-sp>
                    <a primary="" href="https://getsliderrevolution.com" rel="noopener" target="_blank" class="sr--cta sr--cta--big"><span class="sr--lib--recache"><?php echo __('Buy another License*','revslider') ?></span></a>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--text sr--rel"><?php echo __('*One License Key / Purchase Code is required for each Website','revslider');?></span>
                </sr-wrap>
                <sr-wrap class="sr--tab--content" id="sr_cr_images">
                    <h2 class="sr--popup--medium--title"><?php echo __('Terms of using JPG Images from the Library','revslider');?></h2>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('The pictures are free for personal and even for commercial use.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('You can modify, copy and distribute the photos. All without asking for permission or setting a link to the source. So, attribution is not required.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('The only restriction is that identifiable people may not appear in a bad light or in a way that they may find offensive, unless they give their consent.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('The CC0 license was released by the non-profit organization Creative Commons (CC). Get more information about Creative Commons images and the license on the official license page.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Images from','revslider');?> <a href="https://www.pexels.com/" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('Pexels','revslider');?></a> <?php echo __('under the license','revslider');?> <a href="https://creativecommons.org/share-your-work/public-domain/cc0/" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('CC0 Creative Commons','revslider');?></a></span>
                        <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Some images provided by Unsplash, licensed under the ','revslider');?> <a href="https://unsplash.com/de/lizenz" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('Unsplash License','revslider');?></a></span>
                </sr-wrap>
                <sr-wrap class="sr--tab--content" id="sr_cr_objects">
                    <h2 class="sr--popup--medium--title"><?php echo __('Terms of using PNG Objects from the Library','revslider');?></h2>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('PNG Objects from the Slider Revolution Library must only be used with a','revslider');?> <a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=buykey" rel="noopener" target="_blank" class="sr--a--link"><?php echo __('registered license key','revslider');?></a> <?php echo __('on that particular website.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Licenses via extended license and cooperation with author','revslider');?> <a href="https://creativemarket.com/ceacle" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('Ceacle','revslider');?></a></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('If you need .psd files for objects, you can purchase it from the original author','revslider');?> <a href="https://creativemarket.com/ceacle" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('here','revslider');?></a></span>
                    <sr-sp h="45"></sr-sp>
                    <a primary="" href="https://getsliderrevolution.com" rel="noopener" target="_blank" class="sr--cta sr--cta--big"><span class="sr--lib--recache"><?php echo __('Buy another License*','revslider') ?></span></a>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--text sr--rel"><?php echo __('*One License Key / Purchase Code is required for each Website','revslider');?></span>
                </sr-wrap>
                <sr-wrap class="sr--tab--content" id="sr_cr_videos">
                    <h2 class="sr--popup--medium--title"><?php echo __('Terms of using HTML5 Videos from the Library','revslider');?></h2>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('The videos are free for personal and even for commercial use.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('You can modify, copy and distribute the videos. All without asking for permission or setting a link to the source. So, attribution is not required.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('The only restriction is that identifiable people may not appear in a bad light or in a way that they may find offensive, unless they give their consent.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('The CC0 license was released by the non-profit organization Creative Commons (CC). Get more information about Creative Commons images and the license on the official license page.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Videos from','revslider');?> <a href="https://www.pexels.com/" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('Pexels','revslider');?></a> <?php echo __('under the license','revslider');?> <a href="https://creativecommons.org/share-your-work/public-domain/cc0/" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('CC0 Creative Commons','revslider');?></a></span>
                </sr-wrap>
                <sr-wrap class="sr--tab--content" id="sr_cr_svg">
                    <h2 class="sr--popup--medium--title"><?php echo __('Terms of using SVG Objects from the Library','revslider');?></h2>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Usage only allowed within Slider Revolution Plugin.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('A variety of sizes and densities can be also downloaded from the','revslider');?> <a href="https://github.com/google/material-design-icons" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('git repository','revslider');?></a> <?php echo __(', making it even easier for developers to customize, share, and re-use outside of Slider Revolution.','revolution');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Licenses via Apache License. Read More at ');?> <a href="https://github.com/google/material-design-icons/blob/master/LICENSE" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('Google Material Design Icons','revslider');?></a></span>
                </sr-wrap>
                <sr-wrap class="sr--tab--content" id="sr_cr_icon">
                    <h2 class="sr--popup--medium--title"><?php echo __('Terms of using ICON Objects from the Library','revslider');?></h2>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Please check the listed license files for details about how you can use the "FontAwesome" and "Stroke 7 Icon" font sets for commercial projects, open source projects, or really just about whatever you want.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Please respect all other icon fonts licenses for fonts not included directly into Slider Revolution','revslider');?></span>
                    <sr-sp h="45"></sr-sp>
                    <h2 class="sr--popup--medium--title"><?php echo __('Further License Information','revslider');?></h2>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Font Awesome by @davegandy','revslider');?> <a href="http://fontawesome.io/license" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('FontAwesome IO','revslider');?></a></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Stroke 7 Icon Font Set');?> <a href="http://www.pixeden.com/icon-fonts/stroke-7-icon-font-set" target="_blank" rel="noopener" class="sr--a--link"><?php echo __('Pixeden Com','revslider');?></a></span>
                </sr-wrap>
                <sr-wrap class="sr--tab--content" id="sr_cr_layers">
                    <h2 class="sr--popup--medium--title"><?php echo __('Terms of using Layer Group Objects from the Library','revslider');?></h2>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Layer Group Objects from the Slider Revolution Library must only be used with a','revslider');?> <a href="https://account.sliderrevolution.com/portal/pricing/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=buykey" rel="noopener" target="_blank" class="sr--a--link"><?php echo __('registered license key','revslider');?></a> <?php echo __('on that particular website.','revslider');?></span>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--pl--25 sr--text sr--rel"><svg class="sr--icon sr--abs--left" width="12" height="12" transform="translate(0, 4)"><use xlink:href="#Dashboard_Arrow"></use></svg><?php echo __('Media assets used in the respective Layer Group Objects, are licensed according to the here mentioned license terms (see list on the left).','revslider');?></span>
                    <sr-sp h="45"></sr-sp>
                    <a primary="" href="https://getsliderrevolution.com" rel="noopener" target="_blank" class="sr--cta sr--cta--big"><span class="sr--lib--recache"><?php echo __('Buy another License*','revslider') ?></span></a>
                    <sr-sp h="25"></sr-sp>
                    <span class="sr--text sr--rel"><?php echo __('*One License Key / Purchase Code is required for each Website','revslider');?></span>
                </sr-wrap>
            </sr-wrap>
        </sr-wrap>
    </sr-popup-content>
</sr-popup>