<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();

//ToDO - Load things differently
require_once(RS_PLUGIN_PATH . 'admin/views/popups.php');
echo RevSliderFunctions::get_sprite_svg();

?>

<sr-view id="sr-editor">
    <sr-topbar><?php require_once(RS_PLUGIN_PATH . 'admin/views/editor/topbar.php'); ?></sr-topbar>    
    <sr-sidebar id="elements_settings_sidebar" class="disabled"><?php require_once(RS_PLUGIN_PATH . 'admin/views/editor/sidebar.php'); ?></sr-sidebar>
    <sr-premium-warning id="sr_sidebar_premium_warning" style="display:none;">
            <sr-lbl data-action="license.showPremiumCheck" error="" class="sr--mr--10"><svg class="sr--icon sr--mr--5" width="16" height="14.56"><use xlink:href="#Options_Visibility"></use></svg><?php _e('Register License to unlock','revslider'); ?></sr-lbl>
            <sr-sp h="10"></sr-sp>
            <p class="sr--text"><?php _e('This is a <b>Premium template</b> from the Slider Revolution','revslider'); ?>
            <a class="sr--text--link" href="https://www.sliderrevolution.com/wordpress-templates/" target="_blank" rel="noopener"><?php _e('template library','revslider'); ?></a><br>
            <?php _e('It can only be used on this website with a','revslider'); ?><a class="sr--text--link" href="https://account.sliderrevolution.com/portal/premium-slider-revolution/" target="_blank" rel="noopener"> <?php _e('registered license key','revslider'); ?></a>.
            </p>                    
    </sr-premium-warning>
    <sr-tabs-wrap id="sr_preset_advanced_tabs" data-pointerleave="B.pointer.removeInnerTip" animation="true" wrap="true" r="tledit" viewchild="layer_animations">
        <sr-tip id="sr-tip-presetmode" class="sr--tip--presetmode"  data-pos="top"></sr-tip>
        <sr-tab half="true" left="true" data-action="editor.elements.setToEssentials" data-v="presets" class="sr--active--tab" data-sh=".sr_preset_advanced_modes" data-shdep="#eqvalue"><?php _e('Essentials','revslider'); ?></sr-tab>
        <sr-tab half="true" right="true" data-action="editor.elements.setToAdvanced" data-v="advanced" data-sh=".sr_preset_advanced_modes" data-shdep="#eqvalue"><?php _e('Advanced','revslider'); ?></sr-tab>
    </sr-tabs-wrap>
    <sr-stage-wrap><?php require_once(RS_PLUGIN_PATH . 'admin/views/editor/stage.php'); ?></sr-stage-wrap>
    <sr-timeline><?php require_once(RS_PLUGIN_PATH . 'admin/views/editor/timeline.php'); ?></sr-timeline>
</sr-view>
    

<script>
    if (window.SR7?.B?.initForms) SR7.B.initForms();
    document.body.classList.add('sr-editor','hide--wp--elements');
    window.SR7 ??={};
    SR7.editor ??={};
    SR7.editor.c = document.getElementById('sr-editor');
    SR7.editor.state = "ready";    
</script>