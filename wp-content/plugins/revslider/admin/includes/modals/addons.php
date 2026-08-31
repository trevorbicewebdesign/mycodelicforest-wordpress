<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-------------------------------------    
    ADDONS LIBRARY MODAL
--------------------------------------->
<sr-modal id="addon_library" class="sr--basic--library">
    <sr-modal-header>
        <sr-wrap class="sr--library--left">
            <h2 class="sr--modal--title"><svg class="sr--icon sr--mr--10" width="36px" height="36px"><use xlink:href="#Logo"></use></svg><?php echo __('ADDONS','revslider');?></h2>
        </sr-wrap><!--
        --><sr-wrap class="sr--library--right"><!--            
        --><sr-input class="sr--mb--10">
            <span class="sr--input--icon sr--input--icon--left"><svg width="12" height="12" transform="translate(4, 1)"><use xlink:href="#Search"></use></svg></span>    
            <input id="sr_alib_search" class="sr--input--withicon--left" type="text" placeholder="Search">
        </sr-input><!-- 
        --><sr-sp w="20"></sr-sp><!--         
        --><sr-drop class="sr--mb--10 sr--mr--10" data-v="-releaseid" data-onchange="B.library.updateSort" data-onchangeparams="sr_alib" data-onopen="B.library.getSort" data-mode="sort" dropsw="170" dropsh="200">
            <svg style="display:inline-block" class="sr--icon" width="18px" height="12px"><use xlink:href="#Sort"></use></svg>
            <sr-drop-view style="display:inline-block;background:transparent" ><span class="sr--drop--value"><?php _e('Newest first','revslider');?></span></sr-drop-view>
            <sr-drops data-v="alias"><?php _e('A to Z','revslider');?></sr-drops>
            <sr-drops data-v="-alias"><?php _e('Z to A','revslider');?></sr-drops>
            <sr-drops data-v="-releaseid"><?php _e('Newest first','revslider');?></sr-drops>
            <sr-drops data-v="releaseid"><?php _e('Oldest first','revslider');?></sr-drops>
        </sr-drop><!--  
        --><sr-sp w="10"></sr-sp><!--         
        --><sr-drop id="sr_aoverview_filter" data-v="all" data-onchange="B.alibItems.bfilter" data-onchangeparams="sr_alib" data-onopen="B.alibItems.getbFilter" data-mode="filter" dropsw="170" dropsh="240" class="sr--j--icon"><!--
        --><svg class="sr--icon" width="12px" height="11.74px"><use xlink:href="#Filter"></use></svg>
            <sr-drop-view style="display:inline-block;background:transparent" ><span class="sr--drop--value"><?php _e('All States','revslider');?></span></sr-drop-view>
            <sr-drops data-v="all"><?php _e('All States','revslider');?></sr-drops>
            <sr-drops data-v="module"><?php _e('Enabled in Module','revslider');?></sr-drops>
            <sr-drops data-v="global"><?php _e('Global Addons','revslider');?></sr-drops>
            <sr-drops data-v="attention"><?php _e('Need Attention','revslider');?></sr-drops>
        </sr-drop>
            <sr-modal-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-modal-close>
            <sr-wrap class="sr--text--right sr--library--addon--buttons">  
                <sr-button data-action="B.library.reCache" data-aparams="sr_alib" clean="" class="sr--cta"><svg class="sr--icon" width="13.93" height="14" transform="translate(0, 0)"><use xlink:href="#General_Refresh"></use></svg><span class="sr--lib--recache"><?php echo __('Check for Updates','revslider') ?></span></sr-button>
                <sr-button data-action="system.fixIssues" data-aparams="sr_alib" unsaved="" class="sr--cta sr--ml--5 sr--show--if--addon--update--available"><svg class="sr--icon" width="13.93" height="14" transform="translate(0, 0)"><use xlink:href="#General_Refresh"></use></svg><span class="sr--lib--recache"><?php echo __('Update All','revslider') ?></span></sr-button>
            </sr-wrap>
        </sr-wrap>
    </sr-modal-header><!--
    --><sr-wrap class="sr--popup--sidebar">
        <sr-wrap id="sr_alib_tags"></sr-wrap>
        <sr-wrap class="sr--sidebar--adverts">
            <!--<sr-panel class="sr--filled sr--advert--bg">
                <sr-panel-content>
                    <h3 class="sr--text--title--medium">Addon Quick Start></h3>
                    <sr-sp h="3"></sr-sp>
                    <p class="sr--text">Learn the basics of working with your favorite Addon.</p>
                    <a href="" target="_blank" primary class="sr--advert--link sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" ><use xlink:href="#Dashboard_Arrow"></use></svg></a>
                </sr-panel-content>
            </sr-panel>
            <sr-sp h="20"></sr-sp>-->
            <sr-panel class="sr--filled sr--advert--bg--01">
				<sr-panel-content>
					<h3 class="sr--text--title--medium"><?php echo __('Need Help?','revslider');?></h3>
					<sr-sp h="3"></sr-sp>
					<p class="sr--text"><?php echo __('Head over to the Slider Revolution Help Center for editor tours, videos, FAQs, and tutorials.','revslider');?></p>
					<a href="https://www.sliderrevolution.com/help-center/?utm_source=admin&utm_medium=button&utm_campaign=srusers&utm_content=helpcenter" rel="nofollow" target="_blank" primary class="sr--advert--link sr--cta sr--oicon"><svg class="sr--icon" width="12" height="12" ><use xlink:href="#Dashboard_Arrow"></use></svg></a>
				</sr-panel-content>
			</sr-panel>  
        </sr-wrap>    
        <sr-wrap class="sr--text--center sr--abs--bottom--left sr--mb--20" style="width:100%"><span data-action="B.popUp.show" data-position="center" data-aparams="sr_copyright" class="sr--text sr--link--text"><?php echo __('© Copyright & License Info','revslider');?></span></sr-wrap>
    </sr-wrap><!--
    --><sr-library-wrap> 
        <sr-library-wrap-inner>
            <sr-library id="sr_alib" data-src="addons" data-tags="sr_alib_tags" class="sr--overview" data-min-width="267"></sr-library>
        </sr-library-wrap-inner>
    </sr-library-wrap>  
</sr-modal>