<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-------------------------------------
	TEMPLATE LIBRARY MODAL
--------------------------------------->
<sr-modal id="template_library" class="sr--basic--library">
	<sr-modal-header>		
		<sr-wrap class="sr--library--left">
			<h2 class="sr--modal--title"><svg class="sr--icon sr--mr--10" width="36px" height="36px"><use xlink:href="#Logo"></use></svg><?php echo __('TEMPLATES','revslider');?></h2>
		</sr-wrap><!--
		--><sr-wrap class="sr--library--right"><!--
		--><sr-breadcrumbs id="sr_tlib_breadcrumb"></sr-breadcrumbs><!--
		--><sr-input class="sr--mb--10">
			<span class="sr--input--icon sr--input--icon--left"><svg width="12" height="12" transform="translate(4, 1)"><use xlink:href="#Search"></use></svg></span>    
			<input id="sr_tlib_search" class="sr--input--withicon--left" type="text" placeholder="Search">
		</sr-input><!-- 
		--><sr-sp w="20"></sr-sp><!--         
		--><sr-drop class="sr--mb--10 sr--mr--10" data-v="alias" data-onchange="B.library.updateSort" data-onchangeparams="sr_tlib" data-onopen="B.library.getSort" data-mode="sort" dropsw="250" dropsh="200">
			<svg style="display:inline-block" class="sr--icon" width="18px" height="12px"><use xlink:href="#Sort"></use></svg>
			<sr-drop-view style="display:inline-block;background:transparent" ><span class="sr--drop--value"><?php _e('Sort By Date (Newest First)','revslider');?></span></sr-drop-view>
			<sr-drops data-v="alias"><?php _e('Sort By Title (A to Z)','revslider');?></sr-drops>
			<sr-drops data-v="-alias"><?php _e('Sort By Title (Z to A)','revslider');?></sr-drops>
			<sr-drops data-v="-id"><?php _e('Sort By Date (Newest First)','revslider');?></sr-drops>
			<sr-drops data-v="id"><?php _e('Sort By Date (Oldest First)','revslider');?></sr-drops>
		</sr-drop><!--            
		--><sr-radio class="sr--mr--5 sr--mb--10" allow-empty multi>
					<sr-radio-item data-action="B.tlibItems.topdownloaded" data-aparams="sr_tlib"><svg class="sr--icon" width="16" height="18.779" transform="translate(0, -2)"><use xlink:href="#TopDownload"></use></svg><span class="sr--ml--0">Top Downloaded</span></sr-radio-item><!--
				--><sr-radio-item data-action="B.tlibItems.ffavorite" data-aparams="sr_tlib"><svg class="sr--icon" width="18.95" height="18" transform="translate(0, -2)"><use xlink:href="#Dashboard_Star"></use></svg><span class="sr--ml--0">Favorites</span></sr-radio-item>    
			</sr-radio>
			<sr-modal-close style="z-index:1000"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-modal-close>
			<sr-button data-action="B.library.reCache" data-aparams="sr_tlib" clean="" class="sr--cta sr--library--update"><svg class="sr--icon" width="13.93" height="14" transform="translate(0, 0)"><use xlink:href="#General_Refresh"></use></svg><span class="sr--lib--recache"><?php echo __('Check For New Templates','revslider') ?></span></sr-button>
		</sr-wrap>
	</sr-modal-header><!--
	--><sr-wrap class="sr--popup--sidebar">
		<sr-wrap id="sr_tlib_tags"></sr-wrap>
		<sr-wrap class="sr--sidebar--adverts">
			<!--<sr-panel class="sr--filled sr--advert--bg--01">
				<sr-panel-content>
					<h3 class="sr--text--title--medium">Template Quick Start</h3>
					<sr-sp h="3"></sr-sp>
					<p class="sr--text">Learn the basics of customizing your favorite template.</p>
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
		<sr-wrap class="sr--text--center sr--abs--bottom--left sr--mb--20" style="width:100%"><span data-action="B.popUp.show" data-position="center"  data-aparams="sr_copyright" class="sr--text sr--link--text"><?php echo __('© Copyright & License Info','revslider');?></span></sr-wrap>
	</sr-wrap><!--
	--><sr-library-wrap> 
		<sr-library-wrap-inner>
			<sr-library id="sr_tlib" data-src="templates" data-breadcrumb="sr_tlib_breadcrumb" data-tags="sr_tlib_tags" data-pages="sr_tlib_pages" data-page-selector="sr_tlib_pageselector" class="sr--overview" data-min-width="267"></sr-library>
		</sr-library-wrap-inner>
		<sr-sp h="10"></sr-sp> 
		<lib-pagination class="sr--text--right">
			<sr-pageselector-wrap>
				<span class="sr_pageselector_icon"><svg class="sr--icon sr--bicol" width="10" height="10" style="margin-top:-2px"><use xlink:href="#Dashboard_Pages"></use></svg></span>
				<sr-drop clean id="sr_tlib_pageselector" data-onchange="B.library.updatePages" data-isfixed="true" data-onchangeparams="sr_tlib" class="sr_pageselector" dropsw="200" dropsh="200"></sr-drop>
			</sr-pageselector-wrap>			
			<lib-pages id="sr_tlib_pages"></lib-pages>
		</lib-pagination>
		<sr-sp h="5"></sr-sp> 
	</sr-library-wrap>  
</sr-modal>