<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

/** @var RevSliderFunctionsAdmin $sr_af */

if(!defined('ABSPATH')) exit(); 

update_option('sr_last_launch', current_time('mysql', 1));
?>
 <!-- Overview Menu -->
 <sr-section id="sr_overview_menu" class="sr--navigation sr--filled">        
    <sr-wrap class="sr--left sr--pt--5">
        <h2 id="sr_overview_title" class="sr--section--title" style="display:inline-block"></h2><sr-wrap id="sr_olbl_lima" basic inline style="line-height:40px; margin:0px 0px 0px 10px; vertical-align:top;"></sr-wrap>
    </sr-wrap>
    <sr-nav-wrap id="sr_overview_toolbar" style="transform:none; margin-left:-105px" class="sr--center">
        <sr-nav-btn class="sr--overview--toolbar--normal" data-action="B.openGuide"><sr-icon-wrap><svg class="sr--icon" width="18px" height="18px"><use xlink:href="#Dashboard_Blank"></use></svg></sr-icon-wrap><?php echo __('New','revslider'); ?></sr-nav-btn>        
        <sr-nav-btn class="sr--overview--toolbar--normal" data-action="B.upload.show"><sr-icon-wrap><svg class="sr--icon" width="18px" height="19.98px" transform="translate(0,-1)"><use xlink:href="#Dashboard_Import"></use></svg></sr-icon-wrap><?php echo __('Import','revslider'); ?></sr-nav-btn>
        <sr-nav-btn class="sr--overview--toolbar--normal" data-action="B.library.search" data-aparams="sr_overview"><sr-icon-wrap><svg class="sr--icon" width="16px" height="16px"><use xlink:href="#Search"></use></svg></sr-icon-wrap><?php echo __('Search','revslider'); ?></sr-nav-btn>
        <sr-nav-btn class="sr--overview--toolbar--multi" data-action="B.library.multiDelete"><sr-icon-wrap><svg class="sr--icon" width="14" height="18" transform="translate(0, 0)"><use xlink:href="#Delete"></use></svg></sr-icon-wrap><?php echo __('Delete','revslider'); ?></sr-nav-btn>        
        <sr-nav-btn class="sr--overview--toolbar--multi" data-action="B.library.multiExport"><sr-icon-wrap><svg class="sr--icon" width="16" height="22" transform="translate(0, -2)"><use xlink:href="#Dashboard_Export"></use></svg></sr-icon-wrap><?php echo __('Export','revslider'); ?></sr-nav-btn>
        <sr-nav-btn class="sr--overview--toolbar--multi" data-action="B.library.multiGroup" data-aparams="sr_overview"><sr-icon-wrap><svg class="sr--icon" width="20px" height="16px"><use xlink:href="#Dashboard_Add_Folder"></use></svg></sr-icon-wrap><?php echo __('Group','revslider'); ?></sr-nav-btn>
        <sr-nav-btn class="sr--overview--toolbar--multi" data-action="B.library.multiMove" data-aparams="sr_overview"><sr-icon-wrap><svg class="sr--icon" width="20" height="14.88"><use xlink:href="#Dashboard_Folder_Open"></use></svg></sr-icon-wrap><?php echo __('Move','revslider'); ?></sr-nav-btn>
    </sr-nav-wrap>
    <sr-wrap class="sr--right sr--pt--10">        
        <sr-nav-btn id="sr_overiew_list_toggle" data-action="B.library.listToggle" data-aparams="sr_overview" class="sr--j--icon sr--overview--toolbar--normal"><svg class="sr--icon" width="20px" height="14px"><use xlink:href="#Dashboard_List_View"></use></svg></sr-nav-btn>
        <sr-nav-btn id="sr_overview_filter" data-action="B.drop.open" data-onchange="B.library.updateFilter" data-onchangeparams="sr_overview" data-onopen="B.library.getFilter" data-mode="filter" dropsw="200" dropsh="200" class="sr--j--icon sr--overview--toolbar--normal"><!--
            --><svg class="sr--icon" width="12px" height="11.74px"><use xlink:href="#Filter"></use></svg>
            <sr-drops data-v="all"><?php _e('All','revslider');?></sr-drops>
            <sr-drops data-v="hero"><?php _e('Hero','revslider');?></sr-drops>
            <sr-drops data-v="carousel"><?php _e('Carousel','revslider');?></sr-drops>
            <sr-drops data-v="standard"><?php _e('Slider','revslider');?></sr-drops>
            <sr-drops data-v="favorites"><?php _e('Favorites','revslider');?></sr-drops>
        </sr-nav-btn>
        <sr-nav-btn id="sr_overview_sort" data-action="B.drop.open" data-onchange="B.library.updateSort" data-onchangeparams="sr_overview" data-onopen="B.library.getSort" data-mode="sort" dropsw="200" dropsh="200" class="sr--j--icon sr--overview--toolbar--normal"><!--
            --><svg class="sr--icon" width="18px" height="12px"><use xlink:href="#Sort"></use></svg>
            <sr-drops data-v="alias"><?php _e('Title Ascending (A to Z)','revslider');?></sr-drops>
            <sr-drops data-v="-alias"><?php _e('Title Descending (Z to A)','revslider');?></sr-drops>
            <sr-drops data-v="-id"><?php _e('Creation Date (Newest first)','revslider');?></sr-drops>
            <sr-drops data-v="id"><?php _e('Creation Date (Oldest first)','revslider');?></sr-drops>
        </sr-nav-btn>        
        <sr-nav-btn id="sr_overview_addfolder" data-action="B.library.folder.add" data-aparams="sr_overview" class="sr--j--icon sr--overview--toolbar--normal"><svg class="sr--icon" width="20px" height="16px"><use xlink:href="#Dashboard_Add_Folder"></use></svg></sr-nav-btn>        
        <sr-nav-btn id="sr_overview_multiselect" data-action="B.library.multiSelect" data-aparams="sr_overview" class="sr--j--icon"><svg class="sr--icon" width="20" height="17.869" transform="translate(0, -1)"><use xlink:href="#Dynamic_Content"></use></svg></sr-nav-btn>        
    </sr-wrap>        
</sr-section>

<sr-wrap>
    <sr-sp h="30"></sr-sp>
    <sr-breadcrumbs id="sr_overview_breadcrumb"></sr-breadcrumbs>
    <sr-library-wrap>        
        <sr-library id="sr_overview" data-src="modules" class="sr--overview" data-min-width="267" data-pages="sr_overview_pages" data-page-selector="sr_overview_pageselector" data-title="sr_overview_title" data-toolbar="sr_overview_toolbar" data-breadcrumb="sr_overview_breadcrumb">
            
            <lib-item data-action="B.openGuide" data-aparams="sr_overview" class="sr--overview--item sr--basic--creator sr--media--noblur">  
                <lib-i-top>
                    <lib-i-media id="new_first_module"></lib-i-media>
                </lib-i-top><!--
                --><lib-i-bottom><span class="lib--title"><?php echo __('Create Your First Module','revslider');?></span></lib-i-bottom>           
            </lib-item>
            <lib-item data-action="B.upload.show" class="sr--overview--item sr--basic--creator sr--media--noblur">  
                <lib-i-top>
                    <lib-i-media id="import_module"></lib-i-media>
                </lib-i-top><!--
                --><lib-i-bottom><span class="lib--title"><?php echo __('Import Module','revslider');?></span></lib-i-bottom>           
            </lib-item>
        </sr-library>
    </sr-library-wrap>
    <sr-sp h="30"></sr-sp> 
    <lib-pagination class="sr--text--right">
        <sr-pageselector-wrap>
            <span class="sr_pageselector_icon"><svg class="sr--icon sr--bicol" width="10" height="10" style="margin-top:-2px"><use xlink:href="#Dashboard_Pages"></use></svg></span>
            <sr-drop clean id="sr_overview_pageselector" data-onchange="B.library.updatePages" data-onchangeparams="sr_overview" class="sr_pageselector" dropsw="200" dropsh="200"></sr-drop>
        </sr-pageselector-wrap>        
        <lib-pages id="sr_overview_pages"></lib-pages>
    </lib-pagination>
    
</sr-wrap>

<script>
    window.SR7 ??= {};
    SR7.LIB ??= {};
    SR7.LIB.M = _tpt.fixResponse(<?php echo $sr_af->json_encode_client_side(['sliders' => $sr_af->get_slider_overview()]); ?>).sliders;
    function initOverview() {
        if (SR7?.B?.library?.init && _tpt?.draggable) SR7.B.library.dashboard();
        else requestAnimationFrame(initOverview);
    }
    initOverview();
</script>
