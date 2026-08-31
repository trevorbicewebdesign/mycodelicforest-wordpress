<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-wrap class="sr_preset_advanced_modes" value="presets">
    <sr-wrap-dep dep="layerinscene">    
        <sr-separator id="sr_animation_presets" data-body="sr_animation_presets_body" noborder>        
            <sr-separator-body>
                <sr-fieldset viewchild="layer_animations" id="layeranimpresets"  data-source="editor.elements.animpresets.fieldset" class="sr--mb--0"></sr-fieldset>
                <sr-wrap id="sr_animation_presets_body"></sr-wrap>
            </sr-separator-body>
        </sr-separator>
    </sr-wrap-dep>
</sr-wrap>