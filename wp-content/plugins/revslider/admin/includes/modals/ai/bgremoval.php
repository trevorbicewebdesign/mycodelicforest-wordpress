<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>	
<sr-popup id="sr_ai_bgremoval" view="layer_bgremoval">
	<sr-ai-header id="sr_ai_bgremoval_header">
		<sr-ai-header-title><?php _e('AI Image Background Removal','revslider'); ?></sr-ai-header-title>		
		<sr-ai-close data-action="editor.ai.bgremove.close"><svg class="sr--icon" width="10" height="10" style="touch-action: none;"><use xlink:href="#General_Close" style="touch-action: none;"></use></svg></sr-ai-close>
	</sr-ai-header>
	<sr-wrap style="z-index:1;position:relative;padding:15px; box-sizing:border-box;">		
		<sr-ai-preview style="width:380px; height:380px" class="sr--ai--prev--single">
			<sr-ai-preview-blur id="sr_ai_bgremove_blur"></sr-ai-preview-blur>
			<sr-ai-preview-img id="sr_ai_bgremove_img"></sr-ai-preview-img>									
			<sr-wrap basic="" inline="" style="position: absolute; bottom: 10px; right: 0px; z-index: 10;">					
				<sr-button id="sr_ai_bgremove_preview" class="sr--ai--preview--gen sr--shd--3 sr--cta sr--oicon sr--cta--big sr--mb--0 sr--mr--10" white="true" data-action="editor.ai.generate.preview.showimage" data-aparams=""><svg class="sr--icon" width="15" height="15" transform="translate(0, -1)"><use xlink:href="#Search"></use></svg></sr-button>
				<sr-button id="sr_ai_bgremove_add" class="sr--ai--use--gen sr--shd--3 sr--cta sr--oicon sr--cta--big sr--mb--0 sr--mr--10" white="true" data-action="editor.ai.generate.use" data-aparams=""><svg class="sr--icon" width="13" height="13"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
			</sr-wrap>
			<sr-ai-preview-load id="sr_ai_bgremove_timer"></sr-ai-preview-load>
		</sr-ai-preview>		
	</sr-wrap>
	<sr-ai-footer id="sr_ai_footer_bgremoval">
		<sr-wrap basic half inline>
			<sr-wrap class="sr--ai--cost"><b><?php _e('Cost','revslider');?>:</b> <span id="sr_ai_bgremove_cost">2</span></sr-wrap>
			<sr-button class="sr--ai--credits sr--shd--4 sr--mr--0 sr--mb--0"><span id="sr_ai_credits_bg" class="sr--mr--10">Fetching...</span><svg class="sr--icon" width="14" height="12.66" transform="translate(0,-1)"><use xlink:href="#AI_Star"></use></svg><sr--ai--plus><svg class="sr--icon" width="8" height="8"><use xlink:href="#Dashboard_Add_Mini"></use></svg></sr--ai--plus></sr-button>
		</sr-wrap><!--
		--><sr-wrap basic half inline id="sr_ai_bgremove_btn" style="text-align:right;padding:6px 10px 0px;">
			<sr-button ai  class="sr--cta sr--cta--big sr--mr--0 sr--mb--0" data-action="editor.ai.bgremove.run"><?php _e('Remove Background','revslider'); ?></sr-button>
		</sr-wrap>			
	</sr-ai-footer>
</sr-popup>