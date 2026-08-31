<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>

<sr-wrap><!--
--><sr-options-menu id="sr_element_main_menu" threeperrow class="sr--big--tabs">
        <sr-nav-btn data-sr-tabc="sr_element_defset_menu" data-modi="edit" class="sr--tab--call selected" data-tab-target-group="1" id="sr_element_editing_selector"><sr-icon-wrap><svg class="sr--icon" width="20" height="20"><use xlink:href="#EditMode"></use></svg></sr-icon-wrap></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_element_animation" data-modi="animation" class="sr--tab--call" id="sr_element_animation_selector"  data-action="editor.elements.panel.rebuild" animation data-tab-target-group="1"><sr-icon-wrap><svg class="sr--icon" width="10px" height="14.43px" transform="translate(0,-1)"><use xlink:href="#Play"></use></svg></sr-icon-wrap></sr-nav-btn>
        <sr-nav-btn data-sr-tabc="sr_element_actions" data-modi="action" class="sr--tab--call" id="sr_element_action_selector" data-action="editor.elements.panel.rebuild" data-tab-target-group="1" action ><sr-icon-wrap><svg class="sr--icon" width="20px" height="20px" transform="translate(0,-1)"><use xlink:href="#Main_Menu_Actions"></use></svg></sr-icon-wrap></sr-nav-btn>
    </sr-options-menu>
    <sr-wrap class="sr--tab--content sr--open" data-tab-target-group="1" id="sr_element_defset_menu">
        <sr-options-menu fourperrow="true" higher="true" class="sr--left--organised" id="sr_element_defset_menu_options_menu">        
            <sr-nav-btn id="sr_layer_basics_picker" data-sr-tabc="sr_layer_basics" data-modi="edit" data-action="editor.elements.panel.rebuild" class="sr--tab--call selected" data-tab-target-group="2"><sr-icon-wrap><svg class="sr--icon" width="28.97" height="26.95" transform="translate(0,-3)"><use xlink:href="#Submenu_Element"></use></svg></sr-icon-wrap><span><?php _e('Layer','revslider'); ?></span></sr-nav-btn>
            <sr-nav-btn data-sr-tabc="sr_layer_style" data-modi="edit" data-action="editor.elements.panel.rebuild" class="sr--tab--call sr--disable--on--slidebg" data-tab-target-group="2"><sr-icon-wrap><svg class="sr--icon" width="19.96" height="19.99" transform="translate(0,-1)"><use xlink:href="#Submenu_Style"></use></svg></sr-icon-wrap><span><?php _e('Style','revslider'); ?></span></sr-nav-btn>
            <sr-nav-btn data-sr-tabc="sr_layer_extra" data-modi="edit" data-action="editor.elements.panel.rebuild" class="sr--tab--call" data-tab-target-group="2"><sr-icon-wrap><svg class="sr--icon" width="20" height="20" transform="translate(0,-1)"><use xlink:href="#Submenu_Extra_Style"></use></svg></sr-icon-wrap><span><?php _e('Extra Style','revslider'); ?></span></sr-nav-btn>
            <sr-nav-btn data-sr-tabc="sr_layer_hover" data-modi="edit" data-action="editor.elements.panel.rebuild" class="sr--tab--call sr--disable--on--slidebg" data-tab-target-group="2"><sr-icon-wrap><svg class="sr--icon" width="14" height="21" transform="translate(0,-1)"><use xlink:href="#Submenu_Hover"></use></svg></sr-icon-wrap><span><?php _e('Interaction','revslider'); ?></span></sr-nav-btn>
            <sr-nav-btn data-sr-tabc="sr_layer_parallax" data-modi="edit" data-action="editor.elements.panel.rebuild" class="sr--tab--call" data-tab-target-group="2"><sr-icon-wrap><svg class="sr--icon" width="8.49" height="20.95" transform="translate(0,-1)"><use xlink:href="#Submenu_Parallax"></use></svg></sr-icon-wrap><span><?php _e('Parallax','revslider'); ?></span></sr-nav-btn>
            <sr-nav-btn data-sr-tabc="sr_layer_visibility" data-modi="edit" data-action="editor.elements.panel.rebuild" class="sr--tab--call sr--disable--on--slidebg" data-tab-target-group="2"><sr-icon-wrap><svg class="sr--icon" width="23.24" height="18" transform="translate(0,-1)"><use xlink:href="#Submenu_Visibility"></use></svg></sr-icon-wrap><span><?php _e('Visibility','revslider'); ?></span></sr-nav-btn>
            <sr-nav-btn data-sr-tabc="sr_layer_attr" data-modi="edit" data-action="editor.elements.panel.rebuild" class="sr--tab--call" data-tab-target-group="2"><sr-icon-wrap><svg class="sr--icon" width="21.9" height="17.9" transform="translate(0,-1)"><use xlink:href="#Submenu_Attributes"></use></svg></sr-icon-wrap><span><?php _e('Attibutes','revslider'); ?></span></sr-nav-btn>            
            <sr-nav-btn data-sr-tabc="sr_layer_acc" data-modi="edit" data-action="editor.elements.panel.rebuild" class="sr--tab--call sr--disable--on--slidebg" data-tab-target-group="2"><sr-icon-wrap><svg class="sr--icon" width="18" height="18" transform="translate(0,-1)"><use xlink:href="#Accessibility"></use></svg></sr-icon-wrap><span><?php _e('Accessibility','revslider'); ?></span></sr-nav-btn>            
        </sr-options-menu>     
        <sr-sidebar-content view="layer_basics" class="sr--tab--content sr--open" data-tab-target-group="2" id="sr_layer_basics"></sr-sidebar-content>
        <sr-sidebar-content view="layer_style" class="sr--tab--content" data-tab-target-group="2" id="sr_layer_style"></sr-sidebar-content> 
        <sr-sidebar-content view="layer_extra" class="sr--tab--content" data-tab-target-group="2" id="sr_layer_extra"></sr-sidebar-content> 
        <sr-sidebar-content view="layer_parallax" class="sr--tab--content" data-tab-target-group="2" id="sr_layer_parallax"></sr-sidebar-content> 
        <sr-sidebar-content view="layer_hover" class="sr--tab--content" data-tab-target-group="2" id="sr_layer_hover"></sr-sidebar-content> 
        <sr-sidebar-content view="layer_visibility" class="sr--tab--content" data-tab-target-group="2" id="sr_layer_visibility"></sr-sidebar-content> 
        <sr-sidebar-content view="layer_attr" class="sr--tab--content" data-tab-target-group="2" id="sr_layer_attr"></sr-sidebar-content> 
        <sr-sidebar-content view="layer_acc" class="sr--tab--content" data-tab-target-group="2" id="sr_layer_acc"></sr-sidebar-content> 
    </sr-wrap>
    <sr-sidebar-content class="sr--tab--content" view="layer_animations" data-tab-target-group="1" id="sr_element_animation"></sr-sidebar-content>
    <sr-sidebar-content class="sr--tab--content" view="slide_actions" data-tab-target-group="1" id="sr_element_actions">
        <sr-wrap class="sr--gray--bg sr--border--bottom">            
            <sr-drop wide viewchild="slide_actions" r="#FULL#.editing.slide" class="sr--mb--5" data-source="actionslides" data-onchange="B.popUp.hide,editor.slides.select"  dropsw="320" dropsh="200">
                <sr-drop-view>
                    <span class="sr--drop--value"></span>
                    <span class="sr--form--otitle"></span>                
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>
            </sr-drop>            
        </sr-wrap>        
        <sr-fieldset viewchild="slide_actions" id="slideactions"  data-source="editor.actions.sidepanel" class="sr--mb--0"></sr-fieldset>
    </sr-sidebar-content>    
</sr-wrap>