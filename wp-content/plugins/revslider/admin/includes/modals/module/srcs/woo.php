<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
<sr-wrap view="module_source_woo" viewchild="module_source"  id="sr_mosrc_woo" data-type="woo" class="sr_module_srcs sr--force--hide">                
    <sr-separator>
        <sr-separator-body>
            <sr-drop wide multiselect usecheck keepotitle data-v="" r="source.woo.types" viewchild="module_source_woo" dropsw="300">
                <sr-drop-view>
                    <span class="sr--drop--value" style="padding-right:100px"></span>
                    <span class="sr--form--otitle"><?php _e('Woo Types','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>   
                <sr-drops valuelisting data-v="product"><?php _e('Products','revslider'); ?></sr-drops>                        
                <sr-drops valuelisting data-v="product_variation"><?php _e('Product Variations','revslider'); ?></sr-drops>                                                  
            </sr-drop>
                    
            <sr-drop id="sr_mosr_post_category" wide multiselect usecheck keepotitle data-type="search" data-v="cat_tag" r="source.woo.category" data-source="taxonomies" data-taxonomiesof="source.woo.types" viewchild="module_source_woo" dropsw="300" dropsh="300">
                <sr-drop-view>
                    <span class="sr--drop--value" style="padding-right:50px"></span>
                    <span class="sr--form--otitle"><?php _e('Woo Products','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>
            </sr-drop>
            <sr-sp h="5"></sr-sp>
        </sr-separator-body>
    </sr-separator>
    <sr-separator>
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('Limits','revslider'); ?></sr-separator-title>                    
        </sr-separator-head>
        <sr-separator-body>
            <sr-wrap basic>
                <sr-input half class="sr--mb--10 sr--mr--10"><input name="Max Products" r="source.woo.maxProducts" viewchild="module_source_woo" validate type="text" number="true" min="0" max="1500" default="30"><span noicon="" class="sr--form--otitle"><?php _e('Max. Products','revslider'); ?></span></sr-input><!--
                --><sr-input half class="sr--mb--10"><input name="Excerpt Limit" r="source.woo.excerptLimit" viewchild="module_source_woo" validate type="text" number="true" min="0" max="500" lastsuffix="chars" suffix="words|chars" default="55"><span noicon="" class="sr--form--otitle"><?php _e('Excerpt Len.','revslider'); ?></span></sr-input>
            </sr-wrap>
            <sr-wrap basic>
                <sr-input half class="sr--mb--10 sr--mr--10"><input name="Price From" r="source.woo.regPriceFrom" viewchild="module_source_woo" validate type="text" number="true" min="0" placeholder="0"><span noicon="" class="sr--form--otitle"><?php _e('Price From','revslider'); ?></span></sr-input><!--
                --><sr-input half class="sr--mb--10"><input name="Price To" r="source.woo.regPriceTo" viewchild="module_source_woo" validate type="text" number="true" min="0"  placeholder="9999"><span noicon="" class="sr--form--otitle"><?php _e('Price To','revslider'); ?></span></sr-input>
            </sr-wrap>    
            <sr-wrap basic>
                <sr-input half class="sr--mb--10 sr--mr--10"><input name="Sales Price From" r="source.woo.salePriceFrom" viewchild="module_source_woo" validate type="text" number="true" min="0" placeholder="0"><span noicon="" class="sr--form--otitle"><?php _e('Sales Price From','revslider'); ?></span></sr-input><!--
                --><sr-input half class="sr--mb--10"><input name="Sales Price To" r="source.woo.salePriceTo" viewchild="module_source_woo" validate type="text" number="true" min="0" placeholder="9999"><span noicon="" class="sr--form--otitle"><?php _e('Sales Price To','revslider'); ?></span></sr-input>
            </sr-wrap>
            <sr-sp h="10"></sr-sp>
        </sr-separator-body>  
    </sr-separator>
    <sr-separator>
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('Filters','revslider'); ?></sr-separator-title>                    
        </sr-separator-head>
        <sr-separator-body>                
            <sr-wrap basic=""> 
                <span half class="sr--form--grp sr--mr--10"><sr-onoff r="source.woo.inStockOnly" viewchild="module_source_woo" class="sr--mr--10"></sr-onoff><span><?php _e('In Stock Only','revslider'); ?></span></span><!--   
                --><span half class="sr--form--grp"><sr-onoff r="source.woo.featuredOnly" viewchild="module_source_woo" class="sr--mr--10"></sr-onoff><span><?php _e('Featured Only','revslider'); ?></span></span>
            </sr-wrap> 
            <sr-sp h="20"></sr-sp>  
        </sr-separator-body>
    </sr-separator>
    <sr-separator noborder>
        <sr-separator-head notoggle>
            <sr-separator-title><?php _e('Sort By','revslider'); ?></sr-separator-title>                    
        </sr-separator-head>
        <sr-separator-body>
            <sw-wrap basic>
                <sr-drop half class="sr--mr--10" data-v="ID" r="source.woo.sortBy" viewchild="module_source_woo">
                    <sr-drop-view>
                        <span class="sr--drop--value"><?php _e('Woo Post ID','revslider'); ?></span>                        
                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                    </sr-drop-view>
                    <sr-drops data-v="meta_num__regular_price"><?php _e('Regular Price','revslider'); ?></sr-drops>                        
                    <sr-drops data-v="meta_num__sale_price"><?php _e('Sale Price','revslider'); ?></sr-drops>                        
                    <sr-drops data-v="meta_num_total_sales"><?php _e('Number Of Sales','revslider'); ?></sr-drops>                        
                    <sr-drops data-v="meta__sku"><?php _e('SKU','revslider'); ?></sr-drops>                        
                    <sr-drops data-v="meta_num_stock"><?php _e('Stock Quantity','revslider'); ?></sr-drops>                        
                    <sr-drops data-v="ID"><?php _e('Woo Post ID','revslider'); ?></sr-drops>                        
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
                --><sr-drop half data-v="DESC" r="source.woo.sortDirection" viewchild="module_source_woo">
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
</sr-wrap>