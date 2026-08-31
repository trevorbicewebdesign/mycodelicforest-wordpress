<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
<sr-wrap view="module_source_post" viewchild="module_source"  id="sr_mosrc_post" data-type="post" class="sr_module_srcs sr--force--hide">                            
    <sr-separator>    
        <sr-separator-body>        
            <sr-tabs-wrap class="sr--mb--20"  viewchild="module_source_post" r="source.post.subType">
                <sr-tab left="" onethird="" data-sh="#sr_module_post_selection,#sr_module_post_sorting" data-hide="#sr_module_post_specific" data-v="post"><?php _e('All','revslider'); ?></sr-tab>
                <sr-tab         onethird="" data-sh="#sr_module_post_specific,#sr_module_post_sorting" data-hide="#sr_module_post_selection" data-v="specific_post"><?php _e('Specific','revslider'); ?></sr-tab>
                <sr-tab right="" onethird="" data-hide="#sr_module_post_selection,#sr_module_post_sorting,#sr_module_post_specific" data-v="current_post"><?php _e('Current','revslider'); ?></sr-tab>
            </sr-tabs-wrap>        
        </sr-separator-body>
    </sr-separator>
    <sr-separator id="sr_module_post_specific">
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('Specific Posts','revslider'); ?></sr-separator-title>                    
        </sr-separator-head>
        <sr-separator-body>
            <sr-input wide="" textblock="" class="sr--mb--0"><textarea name="Post IDs" style="height:89px;margin-bottom:0px" r="source.post.list" viewchild="module_source_post"></textarea><span noicon="" style="bottom:10px" class="sr--form--otitle">Comma separated list of Ids</span></sr-input>
        </sr-separator-body>
        <sr-sp h="10"></sr-sp>
    </sr-separator>
    
    <sr-separator id="sr_module_post_selection">
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('Post Selection','revslider'); ?></sr-separator-title>                    
        </sr-separator-head>
        <sr-separator-body>
            <sr-drop wide multiselect usecheck keepotitle data-v="" data-source="posttypes" r="source.post.types" viewchild="module_source_post" dropsw="300">
                <sr-drop-view>
                    <span class="sr--drop--value" style="padding-right:100px"></span>
                    <span class="sr--form--otitle"><?php _e('Post Types','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>                                                     
            </sr-drop>

            <sr-drop wide data-v="cat_tag" r="source.post.fetchType" viewchild="module_source_post" data-sh="#sr_mosr_post_category" data-shdep="cat_tag;popular;recent" dropsw="300" dropsh="350">
                <sr-drop-view>
                    <span class="sr--drop--value"><?php _e('Categories & Taxonomies','revslider'); ?></span>
                    <span class="sr--form--otitle"><?php _e('Fetch By','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>                         
                <sr-drops data-v="any"><?php _e('All Posts','revslider'); ?></sr-drops>                        
                <sr-drops data-v="cat_tag"><?php _e('Categories & Taxonomies','revslider'); ?></sr-drops>                        
                <sr-drops data-v="related"><?php _e('Related','revslider'); ?></sr-drops>
                <sr-drops data-v="popular"><?php _e('Popular','revslider'); ?></sr-drops>                        
                <sr-drops data-v="recent"><?php _e('Recent','revslider'); ?></sr-drops>
                <sr-drops data-v="next_prev"><?php _e('Next / Previous','revslider'); ?></sr-drops>                        
            </sr-drop>
            
            <sr-drop id="sr_mosr_post_category" wide multiselect usecheck keepotitle data-type="search" data-v="cat_tag" r="source.post.category" data-source="taxonomies" data-taxonomiesof="source.post.types" viewchild="module_source_post" dropsw="300" dropsh="300">
                <sr-drop-view>
                    <span class="sr--drop--value" style="padding-right:50px"></span>
                    <span class="sr--form--otitle"><?php _e('Posts','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>
            </sr-drop>
            <sr-sp h="5"></sr-sp>
        </sr-separator-body>
    </sr-separator>
    <sr-wrap id="sr_module_post_sorting">
        <sr-separator>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Sort By','revslider'); ?></sr-separator-title>                    
            </sr-separator-head>
            <sr-separator-body>
                <sw-wrap basic>
                    <sr-drop half class="sr--mr--10" data-v="ID" r="source.post.sortBy" viewchild="module_source_post">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Post ID','revslider'); ?></span>
                            <span class="sr--form--otitle"><?php _e('Sort by','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>                         
                        <sr-drops data-v="ID"><?php _e('Post ID','revslider'); ?></sr-drops>                        
                        <sr-drops data-v="date"><?php _e('Date','revslider'); ?></sr-drops>
                        <sr-drops data-v="title"><?php _e('Title','revslider'); ?></sr-drops>                        
                        <sr-drops data-v="name"><?php _e('Name','revslider'); ?></sr-drops>
                        <sr-drops data-v="author"><?php _e('Author','revslider'); ?></sr-drops>
                        <sr-drops data-v="modified"><?php _e('Modified Date','revslider'); ?></sr-drops>
                        <sr-drops data-v="comment_count"><?php _e('Number of Comments','revslider'); ?></sr-drops>
                        <sr-drops data-v="rand"><?php _e('Random','revslider'); ?></sr-drops>
                        <sr-drops data-v="none"><?php _e('Unsorted','revslider'); ?></sr-drops>
                        <sr-drops data-v="menu_order"><?php _e('Custom Order','revslider'); ?></sr-drops>
                    </sr-drop><!--
                    --><sr-drop half data-v="DESC" r="source.post.sortDirection" viewchild="module_source_post">
                        <sr-drop-view>
                            <span class="sr--drop--value"><?php _e('Descending','revslider'); ?></span>
                            <span class="sr--form--otitle"><svg class="sr--icon" width="18" height="12" transform="translate(0, -1)"><use xlink:href="#Sort"></use></svg></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>                         
                        <sr-drops data-v="DESC"><?php _e('Descending','revslider'); ?><span class="sr--form--otitle"><svg class="sr--icon" width="15" height="10" transform="translate(0, -1)"><use xlink:href="#Sort"></use></svg></span></sr-drops>                        
                        <sr-drops data-v="ASC"><?php _e('Ascending','revslider'); ?><span class="sr--form--otitle"><svg class="sr--icon" width="15" height="10" transform="translate(0, -1) scale(1 -1)"><use xlink:href="#Sort"></use></svg></span></sr-drops>                            
                    </sr-drop>
                    <sr-sp h="5"></sr-sp>
            </sr-separator-body>
        </sr-separator>
        <sr-separator noborder>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Limits','revslider'); ?></sr-separator-title>                    
            </sr-separator-head>
            <sr-separator-body>
                <sr-wrap basic>
                    <sr-input half class="sr--mb--10 sr--mr--10"><input name="Max Posts" r="source.post.maxPosts" viewchild="module_source_post" validate type="text" number="true" min="0" max="500" default="30"><span noicon="" class="sr--form--otitle"><?php _e('Max. Posts','revslider'); ?></span></sr-input><!--
                    --><sr-input half class="sr--mb--10"><input name="Excerpt Length" r="source.post.excerptLimit" viewchild="module_source_post" validate type="text" number="true" min="0" max="500" lastsuffix="chars" suffix="words|chars" default="55"><span noicon="" class="sr--form--otitle"><?php _e('Excerpt Len.','revslider'); ?></span></sr-input>
                </sr-wrap>
                <sr-sp h="10"></sr-sp>
            </sr-separator-body> 
        </sr-separator>  
    </sr-wrap>         
</sr-wrap>