<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
<sr-tl-ruler-container>
    <sr-tl-left style="overflow:visible; z-index:2005">
        <sr-wrap wide style="min-height:25px">
            <sr-tl-header id="sr-tl-mainheader">                            
                <sr-wrap basic wide style="padding-top:10px">
                    <sr-icon-wrap class="sr--tl--scene--icon"><svg class="sr--icon" width="18" height="14.727" transform="translate(0,-1)"><use xlink:href="#Timeline_Scenes"></use></svg></sr-icon-wrap><!--
                    --><sr-drop id="sr_scene_selector"  r="#FULL#.editing.scene" data-onpresetextend="editor.scene.extendOption" data-dontupdate="true" animation data-typeicon="addMini" data-typelbl="<?php _e('Add a new scene','revslider'); ?>"  data-undoredo="editor.scene.redraw" data-type="preset" data-subtype="callaction" data-typeaction="editor.scene.newScene" class="sr--tl--title" dropsw="290" dropsh="340"  data-sctop="40" data-beforechange="editor.scene.select" data-source="scenes">
                        <!--data-onpreset="editor.scene.addNew" data-onpresetextend="editor.scene.extendOption"-->
                        <sr-drop-view>
                            <span class="sr--drop--value" style="white-space:nowrap;margin-right:15px;"><?php _e('In Animation','revslider'); ?></span>						
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>					
                    </sr-drop><!--
                    --><sr-tl-layerfilter id="sr_tl_filter" data-action="editor.timeline.filterList"><sr--tl--tooltip class="sr--tl--filteredall"><?php _e('Showing: All Layers','revslider'); ?></sr--tl--tooltip><sr--tl--tooltip class="sr--tl--filtered"><?php _e('Showing: Layers with Keyframes','revslider'); ?></sr--tl--tooltip><svg class="sr--icon" width="16" height="12" transform="translate(1, -1)"><use xlink:href="#AllLayers"></use></svg></sr-tl-layerfilter><!--
                    --><sr-tl-mediahandler id="sr_tl_handle"><svg class="sr--icon" width="8" height="12" transform="translate(1, -1)"><use xlink:href="#Options_Play"></use></svg></sr-tl-mediahandler>
                </sr-wrap>
            </sr-tl-header>
        </sr-wrap>
    </sr-tl-left><!--
--><sr-tl-resizer></sr-tl-resizer><!--
--><sr-tl-storyhandler data-action="editor.timeline.storyView.toggle" title="<?php _e('Read this slide as scroll distance','revslider'); ?>"><sr-wrap class="sr--emarkerinner"><svg class="sr--icon" width="16" height="12.36"><use xlink:href="#Module_Story"></use></svg></sr-wrap></sr-tl-storyhandler><!--
--><sr-tl-emarkerhandler data-action="editor.timeline.state.add"><sr-wrap class="sr--emarkerinner"><svg style="display:block; position:absolute;top:8px;left:12px" class="sr--icon" width="8" height="8"><use xlink:href="#Dashboard_Add_Mini"></use></svg><svg style="display:block; position:absolute;top:20px;left:9px" class="sr--icon sr--icon--second" width="12" height="7" transform="translate(1, -1)"><use xlink:href="#Emarker"></use></svg></sr-wrap></sr-tl-emarkerhandler><!--
--><sr-tl-right id="sr-tl-ruler"><sr-intromarker><span><b></b></span></sr-intromarker><sr-marker><span>IDLE</span></sr-marker><sr-quickmarker><span>IDLE</span></sr-quickmarker><sr-endmarker><span><b></b><i data-action="editor.scene.select" data-aparams="out"><svg class="sr--icon" width="10" height="7.778"><use xlink:href="#TLBack"></use></svg><sr--tl--tooltip><?php _e('Edit Out Animation','revslider'); ?></sr--tl--tooltip></i></span></sr-endmarker></sr-tl-right>
    <sr-back-to-idle data-action="editor.scene.select" data-aparams="in"><svg class="sr--icon" width="10" height="7.778"><use xlink:href="#TLBack"></use></svg><sr--tl--tooltip><?php _e('Edit In Animation','revslider'); ?></sr--tl--tooltip></sr-back-to-idle>
</sr-tl-ruler-container>
<sr-tl-story-band></sr-tl-story-band>
<sr-tl-content>    
    <sr-tl-left id="sr-tl-content-left"></sr-tl-left><!--
--><sr-tl-right id="sr-tl-content-right"></sr-tl-right>
</sr-tl-content>