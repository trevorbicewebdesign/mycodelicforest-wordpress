<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_modal" class="sr--no--padding sr--panel--leftsidebar" view="modulemodal" style="width:320px">
    <sr-modal-content>                    
        <sr-separator>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Modal Settings','revslider'); ?></sr-separator-title>
            </sr-separator-head>
            <sr-separator-body>                
                <sr-wrap basic class="sr--form--grp sr--mb--10"><sr-onoff r="modal.pS" viewchild="modulemodal" class="sr--mr--10 checked"></sr-onoff><span><?php _e('Page Scroll Allowed','revslider'); ?></span></sr-wrap>
                <sr-input wide><input name="Toggled Body Class" replace r="modal.bC" viewchild="modulemodal" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Toggled Body Class','revslider'); ?></span></sr-input>
                <sr-sp h="5"></sr-sp>
            </sr-separator-body>
        </sr-separator>
        <sr-separator>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Style and Position','revslider'); ?></sr-separator-title>
            </sr-separator-head>
            <sr-separator-body>                
                <sr-aligner mini class="sr--mr--10" r="modal.v,modal.v" viewchild="modulemodal">
                    <sr-aligner-wrap>                            
                        <sr-aligner-pos data-v="top left" data-action="B.aligner.update"></sr-aligner-pos>
                        <sr-aligner-pos data-v="top center" data-action="B.aligner.update"></sr-aligner-pos>
                        <sr-aligner-pos data-v="top right" data-action="B.aligner.update"></sr-aligner-pos>
                        <sr-aligner-pos data-v="center left" data-action="B.aligner.update"></sr-aligner-pos>
                        <sr-aligner-pos data-v="center center" data-action="B.aligner.update" class="checked"></sr-aligner-pos>
                        <sr-aligner-pos data-v="center right" data-action="B.aligner.update"></sr-aligner-pos>
                        <sr-aligner-pos data-v="bottom left" data-action="B.aligner.update"></sr-aligner-pos>
                        <sr-aligner-pos data-v="bottom center" data-action="B.aligner.update"></sr-aligner-pos>
                        <sr-aligner-pos data-v="bottom right" data-action="B.aligner.update"></sr-aligner-pos>
                    </sr-aligner-wrap>
                </sr-aligner><!--
                --><sr-input twothird withaligner><input name="Pop Up Speed" replace r="modal.sp" viewchild="modulemodal" type="text" number="true" min="0" max="5000" suffix="ms" validate="true"><span noicon="" class="sr--form--otitle"><?php _e('Pop Up Speed','revslider'); ?></span></sr-input>
                <sr-sp h="0"></sr-sp>
                <sr-wrap basic half class="sr--form--grp sr--mr--10"><sr-onoff r="modal.cover" viewchild="modulemodal" data-sh="#sr_modal_ucol" class="checked sr--mr--10"></sr-onoff><span class="sr--mr--15"><?php _e('Underlay','revslider'); ?></span></sr-wrap><!--
                --><sr-wrap id="sr_modal_ucol" basic half inline class="sr--form--grp" basic><sr-color-mini data-v="transparent" viewchild="modulemodal" r="modal.bg" data-title="<?php _e('Modal Underlay Color','revslider'); ?>" data-type="background" class="sr--mr--10"></sr-color-mini><span><?php _e('Color','revslider'); ?></span></sr-wrap>                    
                <sr-sp h="20"></sr-sp>
            </sr-separator-body>
        </sr-separator>                                
        <sr-separator>
            <sr-separator-head notoggle>
                <sr-separator-title><?php _e('Embedding','revslider'); ?></sr-separator-title>
            </sr-separator-head>
            <sr-separator-body>                            
                <sr-button data-action="editor.module.embed" primary="" class="sr--cta sr--mr--10"><svg class="sr--icon" width="18" height="15" transform="translate(0, -2)"><use xlink:href="#Dashboard_Embed"></use></svg>How to Embed</sr-button>
                <sr-sp h="5"></sr-sp>
            </sr-separator-body>
        </sr-separator>                                
    </sr-modal-content>
</sr-modal> 