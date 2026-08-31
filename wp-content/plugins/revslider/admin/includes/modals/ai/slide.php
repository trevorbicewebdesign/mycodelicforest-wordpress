<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-modal id="sr_module_aislide" class="sr--no--padding sr--panel--leftsidebar" view="aimodal" style="width:320px">
    <sr-ai-header>
		<sr-ai-header-title><?php _e('Adapt Slide Content','revslider'); ?></sr-ai-header-title>		
		<sr-modal-close data-clickable="true"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-modal-close>
	</sr-ai-header>		
	<sr-popup-content style="padding:15px; box-sizing:border-box;display:block;">				
		<sr-wrap>		
            <sr-input wide="" viewchild="aimodal"><input id="sr_ai_slide_industry" replace="" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Industry','revslider');?></span></sr-input>
            <sr-input wide="" viewchild="aimodal"><input id="sr_ai_slide_purpose" replace="" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Purpose','revslider');?></span></sr-input>
            <sr-input wide textblock class="sr--mb--15 sr--ai--txt--fields" value="advanced"><textarea id="sr_ai_slide_instructions" layercontent style="resize:none; height:100px"></textarea><span noicon="" style="bottom:0px" class="sr--form--otitle"><?php _e('Instructions','revslider'); ?></span></sr-input>																
            <sr-drop wide  id="sr_ai_slide_tone" class="sr--ai--txt--fields sr--mr--0" value="tone" viewchild="aimodal" data-v="insp" data-defval="insp" >
                <sr-drop-view>
                    <span class="sr--drop--value"><?php _e('Inspirational / Motivational','revslider'); ?></span>
                    <span class="sr--form--otitle"><?php _e('Tone','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>
                <sr-drops data-v="pro"><?php _e('Professional / Formal','revslider'); ?></sr-drops>
                <sr-drops data-v="casual"><?php _e('Casual / Conversational','revslider'); ?></sr-drops>
                <sr-drops data-v="friendly"><?php _e('Friendly / Warm','revslider'); ?></sr-drops>
                <sr-drops data-v="insp"><?php _e('Inspirational / Motivational','revslider'); ?></sr-drops>
                <sr-drops data-v="auth"><?php _e('Authoritative / Expert','revslider'); ?></sr-drops>
                <sr-drops data-v="min"><?php _e('Minimal / Straightforward','revslider'); ?></sr-drops>
                <sr-drops data-v="sale"><?php _e('Sales-Focused / Persuasive','revslider'); ?></sr-drops>
            </sr-drop>													
			<sr-drop id="sr_ai_slide_language" wide  data-v="auto" data-type="preset"  data-typelbl="<?php _e('Enter Language','revslider'); ?>" data-defval="auto" viewchild="aimodal" class="sr--mr--0" dropsw="350" dropsh="350">
                <sr-drop-view>
                    <span class="sr--drop--value"><?php _e('Auto','revslider'); ?></span>
                    <span class="sr--form--otitle"><?php _e('Translate','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>
                <sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops>
            </sr-drop>
	</sr-popup-content>
	<sr-ai-footer style="position:relative; width:100%; text-align:right">		
		<sr-button ai class="sr--cta sr--cta--big sr--mr--0 sr--mb--0" data-action="B.aiSlideGenerate"><?php _e('Generate','revslider'); ?></sr-button>
	</sr-ai-footer>
</sr-modal> 