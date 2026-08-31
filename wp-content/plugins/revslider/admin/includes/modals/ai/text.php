<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>	
<sr-popup id="sr_ai_txt" view="layer_txt_ai">
	<sr-ai-header>
		<sr-ai-header-title><?php _e('AI Text Generation','revslider'); ?></sr-ai-header-title>		
		<sr-modal-close data-clickable="true"><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></sr-modal-close>
	</sr-ai-header>		
	<sr-popup-content>		
		<sr-sp h="20"></sr-sp>
		<sr-wrap id="sr_ai_txt_build">			
			<sr-wrap id="sr_ai_txt_maininput" wide basic class="sr--mb--10">
				<sr-input wide textblock class="sr--mb--0">
					<textarea id="sr_element_textcontent_ai" layercontent keyup="AI.text.update" viewchild="layer_basics_ai" style="resize:none; height:130px"></textarea>
					<sr-wrap class="sr--ai--txt--resbtns">						
						<sr-button data-action="AI.text.use"clean class="sr--cta sr--cta--big sr--oicon"><svg class="sr--icon" width="16" height="16" transform="translate(0, 0)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
					</sr-wrap>
				</sr-input>
			</sr-wrap>    
			<sr-wrap id="sr_ai_txt_settings">
				<sr-wrap inline basic style="width:328px;vertical-align:top"><sr-info><?php _e('Avoid entering personal data (names, email addresses, etc.) when using the text editing feature.','revslider');?></sr-info></sr-wrap>
				<sr-wrap inline basic class="sr--ml--15" id="sr_ai_txt_words">15/100</sr-wrap>
				<sr-sp h="20"></sr-sp>
				<sr-tabs-wrap id="sr_ai_txt_type" wrap="" viewchild="layer_txt_ai">
					<sr-tab ltop="" onefourth="" class="sr--active--tab" data-v="fix" data-shdep="#eqvalue" data-sh=".sr--ai--txt--fields" onchange="AI.text.setType"><?php _e('Fix Mistakes','revslider');?></sr-tab>
					<sr-tab none="" onefourth="" data-v="translate" data-shdep="#eqvalue" data-sh=".sr--ai--txt--fields" onchange="AI.text.setType"><?php _e('Translate','revslider');?></sr-tab>
					<sr-tab rtop="" onefourth="" data-v="expand" data-shdep="#eqvalue" data-sh=".sr--ai--txt--fields" onchange="AI.text.setType"><?php _e('Expand','revslider');?></sr-tab>
					<sr-tab lbottom="" onefourth="" data-v="shorten" data-shdep="#eqvalue" data-sh=".sr--ai--txt--fields" onchange="AI.text.setType"><?php _e('Shorten','revslider');?></sr-tab>
					<sr-tab none="" onethird=""data-v="tone" data-shdep="#eqvalue" data-sh=".sr--ai--txt--fields" onchange="AI.text.setType"><?php _e('Change Tone','revslider');?></sr-tab>
					<sr-tab none="" onethird=""data-v="clarity" data-shdep="#eqvalue" data-sh=".sr--ai--txt--fields" onchange="AI.text.setType"><?php _e('Improve Clarity','revslider');?></sr-tab>
					<sr-tab rbottom="" onethird="" data-v="advanced" data-shdep="#eqvalue" data-sh=".sr--ai--txt--fields" onchange="AI.text.setType"><?php _e('Advanced','revslider');?></sr-tab>
				</sr-tabs-wrap>			
				<sr-sp h="15"></sr-sp>			
				<sr-wrap basic class="sr--ai--txt--fields" value="expand#;#shorten" ><sr-input wide="" viewchild="layer_txt_ai"><input id="sr_ai_txt_minmax"  replace="" type="text"><span noicon="" class="sr--form--otitle"><?php _e('Word Count Target','revslider');?></span></sr-input></sr-wrap>
				<sr-drop wide  id="sr_ai_txt_tone" class="sr--ai--txt--fields sr--mr--0" value="tone" viewchild="layer_txt_ai" data-v="insp" data-defval="insp" >
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
				<sr-input wide textblock class="sr--mb--15 sr--ai--txt--fields" value="advanced"><textarea id="sr_ai_text_instructions" layercontent style="resize:none; height:100px"></textarea><span noicon="" style="bottom:0px" class="sr--form--otitle"><?php _e('Advanced Instructions','revslider'); ?></span></sr-input>
				<sr-wrap basic wide>
					<sr-wrap inline half><sr-wrap class="sr--ai--txt--fields sr--form--grp sr--mb--15" value="clarity#;#tone#;#expand#;#shorten#;#advanced"><sr-onoff id="sr_ai_txt_multi" class="sr--mr--10 checked" viewchild="layer_txt_ai"></sr-onoff><span><?php _e('Generate 3 Options','revslider'); ?></span></sr-wrap></sr-wrap><!--
				--><sr-drop id="sr_ai_txt_language" half  data-v="auto" data-type="preset"  data-typelbl="<?php _e('Enter Language','revslider'); ?>" data-defval="auto" viewchild="layer_txt_ai" class="sr--mr--0" dropsw="350" dropsh="350">
						<sr-drop-view>
							<span class="sr--drop--value"><?php _e('Auto','revslider'); ?></span>
							<span class="sr--form--otitle"><?php _e('Translate','revslider'); ?></span>
							<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
						</sr-drop-view>
						<sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops>
					</sr-drop>
				</sr-wrap>
			</sr-wrap>
			<sr-sp h="5"></sr-sp>
		</sr-wrap>
		<sr-wrap id="sr_ai_txt_results" class="sr-force-hidden">			
				<sr-input wide textblock class="sr--mb--0"><textarea  name="AI Suggestion 1" viewchild="layer_basics_ai"></textarea>
					<sr-wrap class="sr--ai--txt--resbtns">
						<sr-button data-action="AI.text.cont" clean class="sr--cta sr--cta--big sr--oicon  sr--mr--5"><svg width="13.145" height="11.865" class="sr--icon" transform="translate(0,0)"><use xlink:href="#AIStar"></use></svg></sr-button><!--
						--><sr-button data-action="AI.text.use" clean class="sr--cta sr--cta--big sr--oicon"><svg class="sr--icon" width="16" height="16" transform="translate(0, 0)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
					</sr-wrap>
				</sr-input>
				<sr-input wide textblock class="sr--mb--0"><textarea  name="AI Suggestion 2" viewchild="layer_basics_ai"></textarea>
					<sr-wrap class="sr--ai--txt--resbtns">
						<sr-button data-action="AI.text.cont"clean class="sr--cta sr--cta--big sr--oicon sr--mr--5"><svg width="13.145" height="11.865" class="sr--icon" transform="translate(0,0)"><use xlink:href="#AIStar"></use></svg></sr-button><!--
						--><sr-button data-action="AI.text.use"clean class="sr--cta sr--cta--big sr--oicon"><svg class="sr--icon" width="16" height="16" transform="translate(0, 0)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
					</sr-wrap>
				</sr-input>
				<sr-input wide textblock class="sr--mb--0"><textarea  name="AI Suggestion 3" viewchild="layer_basics_ai"></textarea>
					<sr-wrap class="sr--ai--txt--resbtns">
						<sr-button data-action="AI.text.cont" clean class="sr--cta sr--cta--big sr--oicon  sr--mr--5"><svg width="13.145" height="11.865" class="sr--icon" transform="translate(0,0)"><use xlink:href="#AIStar"></use></svg></sr-button><!--
						--><sr-button data-action="AI.text.use"clean class="sr--cta sr--cta--big sr--oicon"><svg class="sr--icon" width="16" height="16" transform="translate(0, 0)"><use xlink:href="#Dashboard_Add"></use></svg></sr-button>
					</sr-wrap>
				</sr-input>
		</sr-wrap>
		<sr-sp h="20"></sr-sp>
	</sr-popup-content>
	<sr-ai-footer>
		<p class="sr--txt" id="sr_ai_txt_todo"><?php _e('Click "Generate" for Suggestions','revslider'); ?></p>
		<sr-button ai id="sr_ai_txt_genbutton" class="sr--cta sr--cta--big sr--mr--0 sr--mb--0" data-action="AI.text.generate"><?php _e('Generate','revslider'); ?></sr-button>
	</sr-ai-footer>
</sr-popup>