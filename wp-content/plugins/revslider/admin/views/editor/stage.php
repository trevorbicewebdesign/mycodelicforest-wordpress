<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();

?>
<sr-stage-placeholder></sr-stage-placeholder>
<sr-stage>
    <sr-stage-sizer id="sr_stage_drg_left"></sr-stage-sizer>
    <sr-stage-sizer id="sr_stage_drg_right"></sr-stage-sizer>
    <sr-stage-toplblbar>
        <sr-lbl id="sr_editor_mode" design="" class="sr--mr--5"><?php _e('Design Mode','revslider'); ?></sr-lbl><!--
        --><sr-drop id="sr_editor_scenestate"  class="sr--force--hidden" r="#FULL#.editing.scenestate" data-source="scenestates" data-onchange="editor.timeline.state.goto, editor.elements.updateList+t50" dropsw="240" dropsh="200">
            <sr-drop-view >
                <span class="sr--drop--value"><?php _e('Idle','revslider'); ?></span>
                <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
            </sr-drop-view>
        </sr-drop>
    </sr-stage-toplblbar>
    <sr-lbl id="sr_stage_dim_lbl_w">1500px</sr-lbl>
    <sr-lbl id="sr_stage_dim_lbl_h">1500px</sr-lbl>
    <sr-lbl id="sr-cfc-marker_lbl_h" info>1240px</sr-lbl>
    <sr-lbl id="sr-cfc-marker_lbl_w" info>1240px</sr-lbl>
    <sr-cfc-marker></sr-cfc-marker>
    <sr-stage-drag></sr-stage-drag>
    <sr-stage-edit><sr-multibox></sr-multibox></sr-stage-edit>
    <sr-stage-content>
        <!--<sr-stage-co top></sr-stage-co>
        <sr-stage-co bottom></sr-stage-co>
        <sr-stage-co left></sr-stage-co>
        <sr-stage-co right></sr-stage-co>-->
    </sr-stage-content>    
</sr-stage>

<script>
    window.SR7 ??={};
    SR7.stage ??={};
    SR7.stage.state = "ready";
</script>