<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-------------------------------------
    COLOR PICKER MODAL
--------------------------------------->
<sr-modal  id="sr_colorpicker_modal" style="width:410px" class="sr--color--picker--wrap" draggable>    
    <sr-modal-header>
        <h2 id="sr_colorpicker_title" class="sr--modal--title"><?php _e('Title','revslider'); ?>r</h2>
        <sr-modal-close data-clickable="true"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-modal-close>
    </sr-modal-header>
    <sr-sp h="20"></sr-sp>
    <sr-color-picker data-clickable="true" style="cursor:default; z-index:2; position:relative; display:block;">
        <sr-color-background data-clickable="true">
            <sr-color-panel left wide>
                <sr-drop
                    style="width:190px" class="sr--mr--10" data-name="bgt"
                    data-onchange="B.colorpicker.setBgType"
                    data-sh="SR-COLOR-BACKGROUND>*:not(sr-color-panel),SR-COLOR-BACKGROUND>sr-color-panel>*:not(SR-DROP[data-name='bgt'])"
                    data-shdep="linear;radial">
                    <sr-drop-view >
                        <span class="sr--drop--value"></span>
                        <span class="sr--drop--icon"><svg width="10" height="6"><use xlink:href="#Drop_Down"></use></svg></span>
                        <sr-drops data-v="solid"><?php _e('Solid','revslider'); ?></sr-drops>
                        <sr-drops data-v="linear"><?php _e('Linear Gradient','revslider'); ?></sr-drops>
                        <sr-drops data-v="radial"><?php _e('Radial Gradient','revslider'); ?></sr-drops>
                    </sr-drop-view>
                </sr-drop><!--
                --><sr-wrap inline basic><sr-button clean="" class="sr--cta" data-action="B.colorpicker.reverseGradient"><svg class="sr--icon" width="16.015" height="11.625" transform="translate(0, -2)"><use xlink:href="#Timeline_X"></use></svg><?php _e('Reverse','revslider'); ?></sr-button></sr-wrap>
                <sr-sp h="0"></sr-sp><!--
                --><sr-drop style="width:190px" class="sr--mr--10" data-source="ease" data-sourcetype="color" data-valuesplit=".ease" data-name="ge" data-v="none" dropsw="200" dropsh="200" data-onchange="B.colorpicker.setGEasing">
                    <sr-drop-view >
                        <span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
                        <span class="sr--form--otitle"><?php _e('Easing','revslider');?></span>
                        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                    </sr-drop-view>
                </sr-drop><!--
                --><sr-input mini>
                    <input type="text" name="angle" placeholder="" value="90" data-onchange="B.colorpicker.setGradAngle" data-mask-regexp="([\-1-3][0-9][0-9]?|[\-1-9][0-9]?|[\-0-9]?)" data-mask-template="$0" />
                </sr-input><sr-sp w="5"></sr-sp><!--
                --><sr-color-angle><sr-color-angle-handle /></sr-color-angle>
            </sr-color-panel>
            <sr-gradient-preview><sr-color-overlay /></sr-gradient-preview>
            <sr-separator></sr-separator>
            <sr-sp h="20"></sr-sp>
            <sr-color-select-slider data-type="gradient" data-dir="x">
                <sr-color-overlay  />
            </sr-color-select-slider>
            <sr-sp h="20"></sr-sp>
        </sr-color-background>
        <sr-color-panel left>
            <sr-color-select-palette data-dir="x,y" data-range="x,y">
                <sr-color-handle></sr-color-handle>
            </sr-color-select-palette><sr-sp w="15"></sr-sp><!--
            --><sr-color-select-slider data-type="hue" data-dir="y">
                <sr-color-overlay  />
                <sr-color-handle />
            </sr-color-select-slider><sr-sp w="15"></sr-sp><!--
            --><sr-color-select-slider data-type="alpha" data-dir="y">
                <sr-color-overlay  />
                <sr-color-handle />
            </sr-color-select-slider>
        </sr-color-panel><sr-color-panel right top>
            <sr-color-input data-format="hex">
                <sr-input wide>
                    <input type="text" name="hex" placeholder="" value="" spellcheck="false" data-onchange="B.colorpicker.setColor" data-mask="hex" />
                </sr-input>
            </sr-color-input>
            <sr-color-input data-format="rgb">
                <sr-input mini>
                    <input type="text" name="rgb.r" placeholder="" value="" spellcheck="false" data-onchange="B.colorpicker.setColor" data-mask-regexp="(2[0-5][0-9]?|1[0-9][0-9]?|[1-9][0-9]?|[0-9]?)" data-mask-template="$0" />
                </sr-input>
                <sr-input mini>
                    <input type="text" name="rgb.g" placeholder="" value="" spellcheck="false" data-onchange="B.colorpicker.setColor" data-mask-regexp="(2[0-5][0-9]?|1[0-9][0-9]?|[1-9][0-9]?|[0-9]?)" data-mask-template="$0" />
                </sr-input>
                <sr-input mini>
                    <input type="text" name="rgb.b" placeholder="" value="" spellcheck="false" data-onchange="B.colorpicker.setColor" data-mask-regexp="(2[0-5][0-9]?|1[0-9][0-9]?|[1-9][0-9]?|[0-9]?)" data-mask-template="$0" />
                </sr-input>
            </sr-color-input><!--
            --><sr-sp h="0"></sr-sp><!--
            --><sr-input wide>
                <input type="text" name="opacity" placeholder="" value="100" data-onchange="B.colorpicker.setOpacity" data-mask-regexp="(100|[1-9][0-9]?|0)" data-mask-template="$0" />
                <span noicon="" class="sr--form--otitle"><?php _e('Opacity','revslider'); ?></span>
            </sr-input><!--
            --><sr-sp h="0"></sr-sp><!--
            --><sr-drop mini
                data-name="format"
                data-onchange="B.colorpicker.setFormat"
                data-v="hex" 
                data-sh="SR-COLOR-INPUT[data-format='hex']"
                data-hide="SR-COLOR-INPUT[data-format='rgb']"
                data-shdep="hex"
            >
                <sr-drop-view>
                    <span class="sr--drop--value"><?php _e('Hex','revslider');?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>
                <sr-drops data-v="hex"><?php _e('Hex','revslider');?></sr-drops>
                <sr-drops data-v="rgb"><?php _e('RGB','revslider');?></sr-drops>
            </sr-drop><!--
            --><sr-sp w="10"></sr-sp><!--
            --><sr-color-preview><sr-color-canvas></sr-color-canvas><sr-color-overlay></sr-color-overlay></sr-color-preview><!--
            --><sr-sp w="10"></sr-sp><!--
            --><sr-color-pick-icon data-action="B.colorpicker.showPickFromImageSelector" class="sr--mr--10"><svg class="sr--icon" width="20" height="20" transform="translate(0, -1)"><use xlink:href="#Options_Color_Picker"></use></svg></sr-color-pick-icon><!--
            --><sr-color-clear-icon data-action="B.colorpicker.clearColor"><svg class="sr--icon" width="20" height="20" transform="translate(0, 0)"><use xlink:href="#RemoveColor"></use></svg></sr-color-clear-icon>
        </sr-color-panel>
        <sr-sp h="15"></sr-sp>
        <sr-separator></sr-separator>
        <sr-sp h="20"></sr-sp>
        <sr-color-panel left>
            <sr-color-presets-wrap><sr-color-presets><sr-color-preset class="icon--plus" data-action="B.colorpicker.addPresetItem,B.colorpicker.i.presetsScroll.updateScrollbar" data-aparams="true"></sr-color-preset></sr-color-presets></sr-color-presets-wrap>
        </sr-color-panel><sr-color-panel right top>
            <sr-drop
                wide
                class="sr--mb--10"
                dropsw="400"
                dropsh="250"
                data-type="preset"
                data-name="preset"
                data-source="colorpresets"
                data-onchange="B.colorpicker.selectPreset"
                data-onpreset="B.colorpicker.presetManager.add"
                data-onpresetextend="B.colorpicker.presetManager.extendOption" 
            >
                <sr-drop-view>
                    <span class="sr--drop--value"></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>
            </sr-drop>
        </sr-color-panel>
    </sr-color-picker>
</sr-modal>