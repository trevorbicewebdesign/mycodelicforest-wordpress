<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>

<sr-wrap id="sr_editor_icon" data-action="editor.back"><svg class="sr--icon" width="36" height="36" transform="translate(0,6)"><use xlink:href="#EditorLogo"></use></svg></sr-wrap><!--
--><sr-sp w="15"></sr-sp><!--
--><sr-nav-wrap id="sr_main_toolbar" class="sr_main_navgroup">    
    <sr-nav-btn id="sr_est_settings" data-action="editor.sideBar.toggle" data-aparams="module" data-modi="editalways" toggleable togglegroup=".sr_main_navgroup" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="18" height="17"><use xlink:href="#Dashboard_Global"></use></svg></sr-icon-wrap><?php echo __('Settings','revslider');?></sr-nav-btn>    
    <sr-nav-btn id="sr_est_slides" data-action="editor.sideBar.toggle" data-aparams="slides" data-modi="editalways" toggleable togglegroup=".sr_main_navgroup" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="25" height="15.91"><use xlink:href="#Dashboard_Slides"></use></svg></sr-icon-wrap><?php echo __('Slides','revslider'); ?></sr-nav-btn>
    <sr-nav-btn id="editor_timeline_tab" data-action="editor.sideBar.toggle" data-aparams="timeline" animation toggleable togglegroup=".sr_main_navgroup" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20px" height="20px" transform="translate(0,-1)"><use xlink:href="#Top_Bar_Timeline"></use></svg></sr-icon-wrap><?php echo __('Timeline','revslider'); ?></sr-nav-btn>        
    <sr-nav-btn id="sr_est_elements" data-action="editor.sideBar.toggle" data-aparams="elements" data-modi="editifanim" toggleable togglegroup=".sr_main_navgroup" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20px" height="20px" transform="translate(0,-1)"><use xlink:href="#Top_Bar_Elements"></use></svg></sr-icon-wrap><?php echo __('Layers','revslider'); ?></sr-nav-btn>
    <sr-nav-btn id="sr_est_add" data-action="editor.sideBar.toggle" data-aparams="add" data-modi="editalways" toggleable togglegroup=".sr_main_navgroup" class="sr--tab--call"><sr-icon-wrap><svg class="sr--icon" width="20px" height="20px" transform="translate(0,-1)"><use xlink:href="#Top_Bar_Add"></use></svg></sr-icon-wrap><?php echo __('Add','revslider'); ?></sr-nav-btn>
</sr-nav-wrap><!--
--><sr-wrap id="sr_device_selector_topbar" class="sr--center sr--center--group">
        <sr-wrap id="sr_est_devices" class="sr--grouptoone sr--mt--5">
            <sr-grouptoone-result id="sr_device_selector_mini"><svg class="sr--icon" width="24" height="14" transform="translate(0, -2)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></sr-grouptoone-result>
            <sr-grouptoone-drop><svg class="sr--icon" width="10" height="6"><use xlink:href="#Drop_More"></use></svg></sr-grouptoone-drop>
            <sr-radio mirror="sr_device_selector_mini" id="sr_device_selector" data-action="stage.dims.update" data-aparams="deviceset">
                    <sr-radio-item value="0" class="checked" icon><svg class="sr--icon" width="24" height="14" transform="translate(0, -2)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></sr-radio-item><!--
                --><sr-radio-item value="1" icon ><svg class="sr--icon" width="22" height="18" transform="translate(0, -2)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></sr-radio-item><!--
                --><sr-radio-item value="2" icon ><svg class="sr--icon" width="22" height="16" transform="translate(0, -2)"><use xlink:href="#Top_Bar_Laptop"></use></svg></sr-radio-item><!--
                --><sr-radio-item value="3" icon ><svg class="sr--icon" width="20" height="24" transform="translate(0, -2)"><use xlink:href="#Top_Bar_Tablet"></use></svg></sr-radio-item><!--
                --><sr-radio-item value="4" icon ><svg class="sr--icon" width="14" height="20" transform="translate(0, -2)"><use xlink:href="#Top_Bar_Phone"></use></svg></sr-radio-item>                
            </sr-radio>
        </sr-wrap><!--
        --><sr-drop id="sr_used_devices" class="sr--drop--only--icon" data-sh="#sr_device_selector sr-radio-item" data-shdep="#eqvalue" multiselect="truefalse" multilen="5" r="settings.uSize" usecheck data-v="0#;#1#;#2#;##3#;#4" data-onchange="stage.dims.getLevel,stage.dims.redrawFull+20" dropsw="190" dropsh="200">            
        <svg style="display:inline-block" class="sr--icon" width="4px" height="16px" transform="translate(0, -2)"><use xlink:href="#Top_Bar_More"></use></svg>            
            <sr-drops data-v="0"><sr-wrap dropicon><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></sr-wrap><?php echo __('Wide Screen','revslider'); ?></sr-drops>
            <sr-drops data-ignoreclick="true" data-v="1"><sr-wrap dropicon><svg class="sr--icon" width="22" height="18" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></sr-wrap><?php echo __('Desktop','revslider'); ?></sr-drops>
            <sr-drops data-v="2"><sr-wrap dropicon><svg class="sr--icon" width="22" height="16" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Laptop"></use></svg></sr-wrap><?php echo __('Notebook','revslider'); ?></sr-drops>
            <sr-drops data-v="3"><sr-wrap dropicon><svg class="sr--icon" width="20" height="24" transform="translate(0, 0)"><use xlink:href="#Top_Bar_Tablet"></use></svg></sr-wrap><?php echo __('Tablet','revslider'); ?></sr-drops>
            <sr-drops data-v="4"><sr-wrap dropicon><svg class="sr--icon" width="14" height="20" transform="translate(0, 0)"><use xlink:href="#Top_Bar_Phone"></use></svg></sr-wrap><?php echo __('Mobile','revslider'); ?></sr-drops>
        </sr-drop>        
        <sr-sp w="5"></sr-sp>
        <sr-wrap inline basic class="sr--mt--12"><sr-input  style="pointer-events:none" class="sr--mr--5"><input id="sr_stage_dim_w" data-onchange="stage.dims.update" validate style="width:70px"  type="text" number="true" suffix="px" placeholder="1920px"></sr-input><!--
            --><svg class="sr--icon sr--mr--5" neutral width="8" height="8"><use xlink:href="#General_Close"></use></svg><!--
            --><sr-input style="pointer-events:none"><input disabled data-onchange="stage.dims.update" id="sr_stage_dim_h" validate style="width:70px" type="text" number="true" suffix="px" placeholder="1080px"></sr-input>
        </sr-wrap><!--
    --><sr-sp w="15"></sr-sp><!--<sr-nav-wrap class="sr_main_navgroup"></sr-nav-wrap>-->
</sr-wrap>
<sr-wrap id="sr_save_preview_toolbar" class="sr--right sr--right--group">
    <sr-nav-wrap class="sr--grouptoone" id="sr_publish_group">    
        <sr-lbl medium="" class="sr--bad sr--bold sr--mr--10">!</sr-lbl>        
        <sr-nav-btn data-action="editor.module.embed"><sr-icon-wrap><svg class="sr--icon" width="16" height="22" transform="translate(0,-2)"><use xlink:href="#Dashboard_Export"></use></svg></sr-icon-wrap><?php echo __('Publish','revslider');?></sr-nav-btn>        
        <sr-wrap class="sr--grouptoone-inner" inline>
            <sr-nav-btn data-action="editor.preview"><sr-icon-wrap><svg class="sr--icon" width="16px" height="16px"><use xlink:href="#Search"></use></svg></sr-icon-wrap><?php echo __('Preview','revslider');?></sr-nav-btn>
            <sr-nav-btn data-action="editor.save"><sr-icon-wrap><svg class="sr--icon" width="20" height="20"><use xlink:href="#Top_Bar_Save"></use></svg></sr-icon-wrap><?php echo __('Saved','revslider');?></sr-nav-btn>                    
        </sr-wrap>
    </sr-nav-wrap><!--
    --><sr-sp w="10"></sr-sp><!--
    --><!--<sr-nav-btn id="sr-dark-light-switch"><sr-icon-wrap><svg class="sr--icon" width="18" height="18"><use xlink:href="#Dashboard_Dark_Mode"></use></svg></sr-icon-wrap><span class="sr--nav--text">Go Dark</span></sr-nav-btn>--><!--
    --><sr-btn id="sr_est_undo" disabled data-action="editor.undo"><svg class="sr--icon" width="15" height="16.93" transform="translate(0, -2)"><use xlink:href="#Top_Bar_Undo"></use></svg></sr-btn><!--
    --><sr-btn disabled data-action="editor.redo"><svg class="sr--icon" width="15" height="15.689" transform="translate(0, -2)"><use xlink:href="#Top_Bar_Redo"></use></svg></sr-btn><!--
    --><sr-sp w="10"></sr-sp><!--
    --><!--<sr-drop style="width:75px" data-v="100" wide>
        <sr-drop-view>
                <span class="sr--drop--value">100%</span>
                <span class="sr--drop--icon"><svg width="10" height="6"><use xlink:href="#Drop_Down"></use></svg></span>
        </sr-drop-view>
        <sr-drops data-v="50">50%</sr-drops>
        <sr-drops data-v="75">75%</sr-drops>
        <sr-drops data-v="100">100%</sr-drops>
        <sr-drops data-v="125">125%</sr-drops>
        <sr-drops data-v="150">150%</sr-drops>
        <sr-drops data-v="200">200%</sr-drops>
    </sr-drop>-->
</sr-wrap>
<sr-wrap class="sr--topbar--infos">
    <sr-breadcrumb little id="sr-editing-path"><?php echo __('Layer Path','revslider');?><sr-ignore>›</sr-ignore></sr-breadcrumb>
    <sr-breadcrumb main id="sr-editing-element"><?php echo __('No Layer Selected','revslider');?></sr-breadcrumb>
    <sr-toggle-sidebar style="display:none" data-action="editor.sideBar.toggle" data-aparams="right"><svg class="sr--icon" width="29" height="16" transform="translate(0, -2)"><use xlink:href="#SideBarOpen"></use></svg></sr-toggle-sidebar>    
</sr-wrap>
