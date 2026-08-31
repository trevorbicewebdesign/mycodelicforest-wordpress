<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>	
	<!-- AI Star Sprite  -->
	<svg style="position:absolute;width:0;height:0;overflow:hidden" aria-hidden="true">
	<defs>
		<linearGradient id="ai-linear-gradient" x1="0.352" y1="0.147" x2="0.695" y2="0.724" gradientUnits="objectBoundingBox">
		<stop offset="0" stop-color="#5c24ff"/>
		<stop offset="1" stop-color="#ff2399"/>
		</linearGradient>
	</defs>

	<symbol id="AI_Star" viewBox="0 0 21.001 19.067">
		<g transform="translate(-1678.5 -179)">
		<path d="M-2471.5-1826.394l-4.326,1.461.079-4.489-2.753-3.587,4.375-1.313,2.625-3.677,2.625,3.677,4.375,1.313-2.753,3.587.079,4.489Zm9.5-10.145-2.782.94.051-2.886-1.769-2.306,2.813-.844L-2462-1844l1.688,2.364,2.813.844-1.77,2.306.051,2.886Z"
				transform="translate(4157 2023)"/>
		</g>
	</symbol>
	</svg>

	<sr-ai-header id="sr_ai_header">
		<sr-ai-header-title><?php _e('AI Image Generation','revslider'); ?></sr-ai-header-title>		
		<sr-ai-close data-action="editor.ai.close"><svg class="sr--icon" width="10" height="10" style="touch-action: none;"><use xlink:href="#General_Close" style="touch-action: none;"></use></svg></sr-ai-close>
	</sr-ai-header>
	<sr-wrap id="sr_ai_fields" style="z-index:1;position:relative;">
		<sr-options-menu style="display:none" id="sr_ai_menu" fourperrow="" nopopulate="true">        
			<sr-nav-btn data-sr-tabc="sr_ai_image" data-mode="image" data-action="editor.ai.switchMode" class="sr--ai--modeselector sr--tab--call selected"><sr-icon-wrap><svg class="sr--icon sr--ai--bg" width="21" height="19" transform="translate(0,-1)"><use xlink:href="#AI_Star"></use></svg></sr-icon-wrap><span><?php _e('New Image','revslider'); ?></span></sr-nav-btn>        
			<sr-nav-btn data-sr-tabc="sr_ai_upscale" data-mode="upscale" data-action="editor.ai.switchMode" class="sr--ai--modeselector  sr--tab--call"><sr-icon-wrap><svg class="sr--icon sr--ai--bg" width="18" height="18"><use xlink:href="#Upscale"></use></svg></sr-icon-wrap><span><?php _e('Upscale Image','revslider'); ?></span></sr-nav-btn>
		</sr-options-menu>		
		<sr-wrap style="padding:15px 15px 0px">
			<sr-wrap id="sr_ai_generated"></sr-wrap>
			<sr-wrap id="sr_ai_upscaled"></sr-wrap>
		</sr-wrap>
		<sr-modal-content id="sr_ai_fields_inner">
			<sr-wrap class="sr--tab--content sr--open" id="sr_ai_image">
				<sr-separator>            
					<sr-separator-body> 												
						<sr-input wide textblock class="sr--mb--15"><textarea r="#FULL#.M.ai.prompt" viewchild="layers.ai" id="sr_ai_prompt" class="sr--mb--0" placeholder="<?php _e('Describe the image you want to generate. Please do not include personal data, sensitive information, or identifiable individuals in prompts.','revslider'); ?>" style="vertical-align:top; height:100px"></textarea></sr-input>
						<sr-tabs-wrap r="#FULL#.M.ai.amnt" viewchild="ai">
							<sr-tab left="" half="" class="" data-v="1" onchange="editor.ai.updateCosts+200"><?php _e('Single Image','revslider'); ?></sr-tab>
							<sr-tab right="" half="" data-v="4" class="sr--active--tab" onchange="editor.ai.updateCosts+200"><?php _e('4 Images','revslider'); ?></sr-tab>
						</sr-tabs-wrap>
						<sr-sp h="15"></sr-sp>
						<sr-drop r="#FULL#.M.ai.engine" data-sh=".sr_ai_img_engine" data-shdep="#eqvalue" viewchild="ai" wide class="sr--mb--15" default="flux1" data-v="flux1" dropsh="320" data-onchange="editor.ai.fluxUpdate">
							<sr-drop-view>
								<span class="sr--drop--value"><?php _e('Engine','revslider'); ?></span>                            
								<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
								<span class="sr--form--otitle"><?php _e('AI Engine','revslider');?></span>
							</sr-drop-view>						
							<sr-drops data-v="flux1"><?php _e('FLUX 1 [schnell]','revslider'); ?></sr-drops>
							<sr-drops data-v="flux2"><?php _e('FLUX 2 [flash]','revslider'); ?></sr-drops>							
							<!-- Grok Was here -->
						</sr-drop>
						<sr-drop r="#FULL#.M.ai.style" viewchild="ai" wide class="sr--mb--15" default="none" data-v="none" dropsh="320" data-onchange="">
							<sr-drop-view>
								<span class="sr--drop--value"><?php _e('No Style (Raw Prompt)','revslider'); ?></span>                            
								<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
								<span class="sr--form--otitle"><?php _e('Style Preset','revslider');?></span>
							</sr-drop-view>						
							<sr-drops data-v="none"><?php _e('No Style (Raw Prompt)','revslider'); ?></sr-drops>
							<sr-drops data-v="photo"><?php _e('Photorealistic','revslider'); ?></sr-drops>
							<sr-drops data-v="movie"><?php _e('Cinematic','revslider'); ?></sr-drops>
							<sr-drops data-v="3d"><?php _e('3D Render','revslider'); ?></sr-drops>
							<sr-drops data-v="painting"><?php _e('Illustration (2D Art)','revslider'); ?></sr-drops>
							<sr-drops data-v="flat"><?php _e('Minimalist Flat','revslider'); ?></sr-drops>
							<sr-drops data-v="abstract"><?php _e('Vintage / Film Grain','revslider'); ?></sr-drops>
						</sr-drop>
						<sr-drop r="#FULL#.M.ai.ratio" viewchild="ai" wide class="sr--mb--15" default="none" data-v="none" dropsh="320" data-onchange="editor.ai.sizeUpdate" data-onchangeparams="ratio">
							<sr-drop-view>
								<span class="sr--drop--value"><?php _e('No Style','revslider'); ?></span>                            
								<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
								<span class="sr--form--otitle"><?php _e('Aspect Ratio','revslider');?></span>
							</sr-drop-view>						
							<sr-drops data-v="free"><?php _e('Free','revslider'); ?></sr-drops>
							<sr-drops data-v="1:1"><?php _e('1:1 (Square)','revslider'); ?></sr-drops>
							<sr-drops data-v="3:2"><?php _e('3:2 (Landscape)','revslider'); ?></sr-drops>
							<sr-drops data-v="4:3"><?php _e('4:3 (Landscape)','revslider'); ?></sr-drops>
							<sr-drops data-v="16:9"><?php _e('16:9 (Landscape)','revslider'); ?></sr-drops>
							<sr-drops data-v="3:4"><?php _e('3:4 (Portrait)','revslider'); ?></sr-drops>
							<sr-drops data-v="2:3"><?php _e('2:3 (Portrait)','revslider'); ?></sr-drops>
							<sr-drops data-v="9:6"><?php _e('9:6 (Landscape)','revslider'); ?></sr-drops>						
						</sr-drop>
						<sr-wrap view="layer" id="sr_ai_generate_imagedims" class="sr_ai_img_engine" value="flux1#;#flux2">
							<sr-wrap id="sr_ai_imagedims">
								<sr-input id="sr_ai_width_wrap" half class="sr--mr--10 sr--mb--0">
									<input name="Image Width" class="sr--mb--5" r="#FULL#.M.ai.width" viewchild="ai" replace data-onchange="editor.ai.sizeUpdate" data-onchangeparams="width" data-onset="editor.ai.sizeUpdate" data-onsetparams="resetdrag" livevisup autocomplete="off"  number="true" min="64" max="1920" suffix="px" lastsuffix="px"  validate="true" type="text">
									<span noicon="" class="sr--form--otitle">W</span>
									<sr-slider>
										<sr-track><sr-fill></sr-fill><sr-handle></sr-handle></sr-track>
										<sr-slider-label class="sr--left"></sr-slider-label>
										<sr-slider-label class="sr--right"></sr-slider-label>
									</sr-slider>															
								</sr-input><!--
								--><sr-input id="sr_ai_height_wrap" half class="sr--mr--0 sr--mb--0">
									<input name="Image Height" class="sr--mb--5" r="#FULL#.M.ai.height" viewchild="ai"  replace data-onchange="editor.ai.sizeUpdate" data-onchangeparams="height" data-onset="editor.ai.sizeUpdate" data-onsetparams="resetdrag" livevisup autocomplete="off"  number="true" min="64" max="1728" suffix="px" lastsuffix="px"  validate="true" type="text">
									<span noicon="" class="sr--form--otitle">H</span>
									<sr-slider>
										<sr-track><sr-fill></sr-fill><sr-handle></sr-handle></sr-track>
										<sr-slider-label class="sr--left"></sr-slider-label>
										<sr-slider-label class="sr--right"></sr-slider-label>
									</sr-slider>									
								</sr-input>
								<sr-sp style="pointer-event:none" h="20"></sr-sp>
								<sr-wrap basic id="sr_ai_match_selection_wrap" style="display:none">
									<sr-button clean class="sr--cta sr--mr--10 sr--mb--0 sr--mr--0 sr--center" data-action="editor.ai.matchselection"><svg class="sr--icon" width="9.8" height="10" transform="translate(0, 0) rotate(90)"><use xlink:href="#Options_Scale_Layer"></use></svg><?php _e('Match Selection Size','revslider'); ?></sr-button>
									<sr-sp style="pointer-event:none" h="20"></sr-sp>
								</sr-wrap>
								
							</sr-wrap>
						</sr-wrap>					
						<sr-separator topborder class="collapsed">
							<sr-separator-head>
								<sr-separator-title><?php _e('Seed','revslider'); ?></sr-separator-title>
								<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
							</sr-separator-head>
							<sr-separator-body>								
								<sr-input wide class="sr--mr--0 sr--mb--0">
									<input name="Seed" id="sr_ai_seed_input" class="sr--mb--5" r="#FULL#.M.ai.seed" viewchild="ai"  replace data-onchange="editor.ai.seedUpdate" data-onset="editor.ai.seedUpdate" data-onsetparams="resetdrag" livevisup autocomplete="off"  number="true" min="0" max="2147483647"  validate="true" type="text">
									<span noicon="" class="sr--form--otitle"><?php _e('Seed','revslider'); ?></span>
									<sr-slider>
										<sr-track><sr-fill></sr-fill><sr-handle></sr-handle></sr-track>
										<sr-slider-label class="sr--left">0</sr-slider-label>
										<sr-slider-label class="sr--right">2147483647</sr-slider-label>
									</sr-slider>									
								</sr-input>
								<sr-sp h="15"></sr-sp>
								<sr-wrap basic="" class="sr--form--grp"><sr-onoff data-onchange="editor.ai.randomize" r="#FULL#.M.ai.seedrnd" viewchild="ai"  class="sr--mr--10"></sr-onoff><span><?php _e('Randomize Seed','revslider'); ?></span></sr-wrap>
								<sr-sp h="20"></sr-sp>
							</sr-separator-body>
						</sr-separator>
						<sr-separator topborder class="collapsed">
							<sr-separator-head>
								<sr-separator-title><?php _e('Quality','revslider'); ?></sr-separator-title>
								<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
							</sr-separator-head>
							<sr-separator-body><!--
								--><sr-wrap basic half inline class="sr--mr--10 sr_ai_img_engine" value="flux1#;#flux2" style="overflow:visible">
									<sr-input wide class="sr--mr--0 sr--mb--0">
										<input name="Guidance" class="sr--mb--5" r="#FULL#.M.ai.guide" viewchild="ai"  replace data-onchange="editor.ai.guideUpdate" data-onset="editor.ai.guideUpdate" data-onsetparams="resetdrag" livevisup autocomplete="off"  number="true" min="0" max="10" step="0.1" validate="true" type="text">
										<span noicon="" class="sr--form--otitle"><?php _e('Guidance','revslider'); ?></span>
										<sr-slider>
											<sr-track><sr-fill></sr-fill><sr-handle></sr-handle></sr-track>
											<sr-slider-label class="sr--left">0</sr-slider-label>
											<sr-slider-label class="sr--right">10</sr-slider-label>
										</sr-slider>										
									</sr-input>									
								</sr-wrap><!--								
								--><sr-wrap basic half inline  class="sr_ai_img_engine" value="flux1#;#flux2" style="overflow:visible">	
									<sr-input  wide class="sr--mr--0 sr--mb--0 sr_ai_img_engine" value="flux1" >
										<input name="Inference Steps" class="sr--mb--5" r="#FULL#.M.ai.isteps" viewchild="ai"  replace data-onchange="editor.ai.iStepsUpdate" data-onset="editor.ai.iStepsUpdate" data-onsetparams="resetdrag" livevisup autocomplete="off"  number="true" min="0" max="10"  validate="true" type="text">
										<span noicon="" class="sr--form--otitle"><?php _e('Inference Steps','revslider'); ?></span>
										<sr-slider>
											<sr-track><sr-fill></sr-fill><sr-handle></sr-handle></sr-track>
											<sr-slider-label class="sr--left">0</sr-slider-label>
											<sr-slider-label class="sr--right">10</sr-slider-label>
										</sr-slider>										
									</sr-input><!--
									--><sr-wrap wide basic="" class="sr--form--grp sr_ai_img_engine sr--mr--0" value="flux2"><sr-onoff r="#FULL#.M.ai.exp" viewchild="ai"  class="sr--mr--10"></sr-onoff><span><?php _e('Expansion','revslider'); ?></span></sr-wrap>
								</sr-wrap>

								<!--<sr-drop r="#FULL#.M.ai.gq" viewchild="ai" wide class="sr--mt--15 sr--mb--15 sr_ai_img_engine" value="grok" default="2k" data-v="2k" dropsh="320" onchange="editor.ai.updateCosts+50">
									<sr-drop-view>
										<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>                            
										<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
										<span class="sr--form--otitle"><?php _e('Resolution','revslider');?></span>
									</sr-drop-view>						
									<sr-drops data-v="1k"><?php _e('1K','revslider'); ?></sr-drops>
									<sr-drops data-v="2k"><?php _e('2K','revslider'); ?></sr-drops>									
								</sr-drop>-->

								<sr-drop r="#FULL#.M.ai.acc" viewchild="ai" wide class="sr--mt--15 sr--mb--0 sr_ai_img_engine" value="flux1" default="none" data-v="none" dropsh="320">
									<sr-drop-view>
										<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>                            
										<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
										<span class="sr--form--otitle"><?php _e('Acceleration','revslider');?></span>
									</sr-drop-view>						
									<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
									<sr-drops data-v="regular"><?php _e('Regular','revslider'); ?></sr-drops>
									<sr-drops data-v="high"><?php _e('High','revslider'); ?></sr-drops>										
								</sr-drop>
																
								<sr-sp class="sr_ai_img_engine" value="flux1#;#flux2" h="15"></sr-sp>	
								<sr-drop r="#FULL#.M.ai.format" viewchild="ai" wide class="sr--mb--15" default="none" data-v="none" dropsh="320">
									<sr-drop-view>
										<span class="sr--drop--value"><?php _e('WebP','revslider'); ?></span>                            
										<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
										<span class="sr--form--otitle"><?php _e('Image Format','revslider');?></span>
									</sr-drop-view>						
									<sr-drops data-v="webp"><?php _e('WebP','revslider'); ?></sr-drops>
									<sr-drops data-v="jpeg"><?php _e('JPEG','revslider'); ?></sr-drops>
									<sr-drops data-v="png"><?php _e('PNG','revslider'); ?></sr-drops>								
								</sr-drop>					
							</sr-separator-body>
						</sr-separator>
					</sr-separator-body>				
				</sr-separator>    
				<sr-separator noborder>
					<sr-separator-head>
						<sr-separator-title><?php _e('Background Ideas','revslider'); ?></sr-separator-title>
						<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
					</sr-separator-head>
					<sr-separator-body>   
						<sr-lbl data-action="editor.ai.preset" data-aparams="1" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux1#;#grok"><?php _e('Apline Lake','revslider'); ?></sr-lbl><!--
						--><sr-lbl data-action="editor.ai.preset" data-aparams="2" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux1#;#grok"><?php _e('Polar Landscape','revslider'); ?></sr-lbl><!--
						--><sr-lbl data-action="editor.ai.preset" data-aparams="3" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux2#;#grok"><?php _e('Female Model','revslider'); ?></sr-lbl><!--
						--><sr-lbl data-action="editor.ai.preset" data-aparams="4" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux1#;#flux2#;#grok"><?php _e('Fashion Silouhette','revslider'); ?></sr-lbl><!--
						--><sr-lbl data-action="editor.ai.preset" data-aparams="5" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux2#;#grok"><?php _e('Mountain Climber','revslider'); ?></sr-lbl><!--
						--><sr-lbl data-action="editor.ai.preset" data-aparams="6" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux2#;#grok"><?php _e('3D Cartoon Monster','revslider'); ?></sr-lbl><!--
						--><sr-lbl data-action="editor.ai.preset" data-aparams="7" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux2#;#grok"><?php _e('Stylized 3D Characters','revslider'); ?></sr-lbl><!--
						--><sr-lbl data-action="editor.ai.preset" data-aparams="8" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux1#;#grok"><?php _e('Dreamcore Nature','revslider'); ?></sr-lbl><!--
						--><sr-lbl data-action="editor.ai.preset" data-aparams="9" medium="" class="sr--mr--5 sr--mb--5 sr_ai_img_engine" value="flux1#;#grok"><?php _e('Aerial Beach Photo','revslider'); ?></sr-lbl>
					</sr-separator-body>     
				</sr-separator>
			</sr-wrap>
			<sr-wrap class="sr--tab--content" id="sr_ai_upscale">
				<sr-separator>            
					<sr-separator-body>
						<sr-wrap id="sr_ai_upscale_imagedims"></sr-wrap>
						<sr-wrap basic id="sr_ai_upscale_info"><span class="sr--ai--info"><?php _e('Select an image for upscaling','revslider'); ?></span><sr-tooltip key="aiupscaleimgsrc"></sr-tooltip></sr-wrap>
					</sr-separator-body>
				</sr-separator>            
			</sr-wrap>			
		</sr-modal-content>
		<sr-sp h="115"></sr-sp>
	</sr-wrap>
	<sr-ai-footer id="sr_ai_footer">
		<sr-tip id="sr-ai-more-credits" class="sr--ai--tip sr--more--credits"><?php _e('Your AI Credit balance.Add AI Credits anytime through your member dashboard.','revslider');?></sr-tip>
		<sr-tip id="sr-ai-no-credits" class="sr--ai--tip"><?php _e('Click to add AI Credits in your member dashboard to continue using generative AI features.','revslider');?></sr-tip>
		<sr-tip id="sr-ai-notregistered" class="sr--ai--tip"><?php _e('Click to register a SR7 license and start using generative AI features right away.','revslider');?></sr-tip>
		<sr-wrap basic half inline>
			<sr-wrap class="sr--ai--cost"><b><?php _e('Cost','revslider');?>:</b> <span id="sr_ai_cost">0</span></sr-wrap>
			<sr-button id="sr_ai_credits_btn" data-action="editor.ai.generate.buy" class="sr--ai--credits sr--shd--4 sr--mr--0 sr--mb--0"><span id="sr_ai_credits" class="sr--mr--10">Fetching...</span><svg class="sr--icon" width="14" height="12.66" transform="translate(0,-1)"><use xlink:href="#AI_Star"></use></svg><sr--ai--plus><svg class="sr--icon" width="8" height="8"><use xlink:href="#Dashboard_Add_Mini"></use></svg></sr--ai--plus></sr-button>
		</sr-wrap><!--
			--><sr-wrap basic half inline id="sr_ai_generate_btn" style="text-align:right;padding:6px 0px 0px; overflow:visible"><sr-button ai  class="sr--cta sr--cta--big sr--mr--0 sr--mb--0" data-action="editor.ai.generate.run"><?php _e('Generate','revslider'); ?></sr-button></sr-wrap><!--
			--><sr-wrap basic half inline id="sr_ai_upscale_btn"  style="text-align:right;padding:6px 0px 0px;display:none"><sr-button ai  class="sr--cta sr--cta--big sr--mr--0 sr--mb--0" data-action="editor.ai.generate.run"><?php _e('Upscale','revslider'); ?></sr-button></sr-wrap>
	</sr-ai-footer>