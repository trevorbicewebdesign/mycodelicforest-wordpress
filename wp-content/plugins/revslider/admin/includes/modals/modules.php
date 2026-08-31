<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit(); 

?>
<sr-modal id="modules" class="sr--basic--library toplevel_page_revslider">
	<sr-modal-header>
		<sr-wrap class="sr--library--left">
			<h2 class="sr--modal--title"><svg class="sr--icon sr--mr--10" width="36px" height="36px"><use xlink:href="#Logo"></use></svg><?php echo __('Select Module','revslider');?></h2>
		</sr-wrap><!--
		--><sr-wrap class="sr--library--right"><!--
		--><sr-breadcrumbs id="sr_mlib_breadcrumb"></sr-breadcrumbs><!--
		--><sr-input class="sr--mb--10">
			<span class="sr--input--icon sr--input--icon--left"><svg width="12" height="12" transform="translate(4, 1)"><use xlink:href="#Search"></use></svg></span>    
			<input id="sr_mlib_search" class="sr--input--withicon--left" type="text" placeholder="Search">
		</sr-input><!-- 
		--><sr-sp w="20"></sr-sp><!--
		--><sr-drop class="sr--mb--10 sr--mr--10" data-v="all" data-onchange="B.library.updateFilter" data-onchangeparams="sr_mlib" data-onopen="B.library.getFilter" data-mode="filter" dropsw="250" dropsh="200">
			<svg style="display:inline-block" class="sr--icon" width="12px" height="11.74px"><use xlink:href="#Filter"></use></svg>
			<sr-drop-view style="display:inline-block;background:transparent"><span class="sr--drop--value"><?php _e('All','revslider');?></span></sr-drop-view>
			<sr-drops data-v="all"><?php _e('All','revslider');?></sr-drops>
			<sr-drops data-v="hero"><?php _e('Hero','revslider');?></sr-drops>
			<sr-drops data-v="carousel"><?php _e('Carousel','revslider');?></sr-drops>
			<sr-drops data-v="standard"><?php _e('Slider','revslider');?></sr-drops>
			<sr-drops data-v="favorites"><?php _e('Favorites','revslider');?></sr-drops>
		</sr-drop><!--
		--><sr-drop class="sr--mb--10 sr--mr--10" data-v="alias" data-onchange="B.library.updateSort" data-onchangeparams="sr_mlib" data-onopen="B.library.getSort" data-mode="sort" dropsw="250" dropsh="200">
			<svg style="display:inline-block" class="sr--icon" width="18px" height="12px"><use xlink:href="#Sort"></use></svg>
			<sr-drop-view style="display:inline-block;background:transparent" ><span class="sr--drop--value"><?php _e('Sort By Date (Newest First)','revslider');?></span></sr-drop-view>
			<sr-drops data-v="alias"><?php _e('Sort By Title (A to Z)','revslider');?></sr-drops>
			<sr-drops data-v="-alias"><?php _e('Sort By Title (Z to A)','revslider');?></sr-drops>
			<sr-drops data-v="-id"><?php _e('Sort By Date (Newest First)','revslider');?></sr-drops>
			<sr-drops data-v="id"><?php _e('Sort By Date (Oldest First)','revslider');?></sr-drops>
		</sr-drop><!--
		--><sr-modal-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-modal-close>
		</sr-wrap>
	</sr-modal-header><!--
	--><sr-wrap class="sr--popup--sidebar">
		<sr-wrap id="sr_mlib_tags"></sr-wrap>
		<sr-wrap class="sr--text--center sr--abs--bottom--left sr--mb--20" style="width:100%"><span data-action="B.popUp.show" data-position="center"  data-aparams="sr_copyright" class="sr--text sr--link--text"><?php echo __('© Copyright & License Info','revslider');?></span></sr-wrap>
	</sr-wrap><!--
	--><sr-library-wrap> 
		<sr-library-wrap-inner>
			<sr-library id="sr_mlib" data-src="modules" data-breadcrumb="sr_mlib_breadcrumb" data-tags="sr_mlib_tags" data-pages="sr_mlib_pages" data-page-selector="sr_mlib_pageselector" class="sr--overview" data-min-width="267"></sr-library>
		</sr-library-wrap-inner>
		<sr-sp h="10"></sr-sp> 
		<lib-pagination class="sr--text--right">
			<sr-pageselector-wrap>
				<span class="sr_pageselector_icon"><svg class="sr--icon sr--bicol" width="10" height="10" style="margin-top:-2px"><use xlink:href="#Dashboard_Pages"></use></svg></span>
				<sr-drop clean id="sr_mlib_pageselector" data-onchange="B.library.updatePages" data-onchangeparams="sr_mlib" data-prevent="true" class="sr_pageselector" dropsw="200" dropsh="200"></sr-drop>
			</sr-pageselector-wrap>
			<sr-sp w="30"></sr-sp>
			<lib-pages id="sr_mlib_pages"></lib-pages>
		</lib-pagination>
		<sr-sp h="5"></sr-sp> 
	</sr-library-wrap> 
</sr-modal>