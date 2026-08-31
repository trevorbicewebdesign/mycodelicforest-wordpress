<?php
/**
* @author    ThemePunch <info@themepunch.com>
* @link      https://www.themepunch.com/
* @copyright 2024 ThemePunch
*/

if(!defined('ABSPATH')) exit();

/**
 * Static catalogue of the editor's help tooltips, grouped by editor section. getTooltips() is filterable so
 * addons can contribute their own entries.
 */
class RevSliderTooltips {

	/**
	 * one tooltip by its handle, searched across all sections
	 * @return array|false [handle => tooltip], false when unknown
	 */
	public function get_tooltip_by_handle($handle){
		$tt = self::getTooltips();
		foreach($tt as $_tt){
			if(isset($_tt[$handle])) return [$handle => $_tt[$handle]];
		}

		return false;
	}

	/**
	 * full text search over all tooltips (title, description, links) - used by the editor's help search
	 * @return array handle => tooltip
	 */
	public function get_tooltips_by_string($search){
		$tt		= self::getTooltips();
		$found	= [];
		$search = strtolower($search);
		foreach($tt as $_tt){
			foreach($_tt as $handle => $v){
				$_v = strtolower(json_encode($v));
				if(strpos($_v, $search) === false) continue;
				$found[$handle] = $v;
			}
		}

		return $found;
	}

	/**
	 * @return array section => [handle => tooltip]
	 */
	public static function getTooltips() {
		return apply_filters('revslider_getTooltips', [
			'module' => [				
				'diffbulkvalues' => [					
					'desc' => 'Bulk edit target attributes differ.<br>Using values from the first layer.',					
				],
				'fullwidth' => [					
					'desc' => 'The module will always match the width of the browser window.
					',					
				],
				'fullheight' => [					
					'desc' => 'The module height will match the browser window (reduced by custom limitations) or expand beyond it if the content requires.
					',					
				],
				'decreasemoduleheight' => [					
					'desc' => 'The module height will be reduced by the height of one or more containers, defined using HTML selectors or pixel values. Separate multiple selectors with commas.
					',
				],
				'minmaxmodulesizes' => [					
					'desc' => 'Set minimum and maximum height for the module. Additionally, if the module is not using full width mode, set maximum width.
					',
				],
				'keepbreakpoint' => [					
					'desc' => 'When enabled, the module’s height remains fixed between breakpoints. At the next breakpoint, the height updates to match the content flow container height defined for that device size.
					',
				],
				'preventpagescroll' => [					
					'desc' => 'Locks the module in place on screen while the scroll-driven timeline plays within the range specified below.<br><br>
					The module remains fixed until the timeline moves past the "Hold From" and "Hold To" positions.<br><br>
					If advanced mode is active, the module scrolls gently between the top and bottom of the page for subtle dynamic movement.
					',
				],
				'keepratio' => [					
					'desc' => 'Between breakpoints, the module maintains its aspect ratio by scaling height proportionately with width.<br><br>
					At the next breakpoint, it adopts the aspect ratio defined in the content flow container for that device size.
					',					
				],
				'ignoremobileheight' => [					
					'desc' => 'Ignore height changes caused by the visibility of the mobile browser’s address bar.',					
				],
				'acc_gen_aria' => [
					'desc' => 'Adds custom and auto ARIA roles and labels to improve screen reader compatibility. Update values per layer and slide as required.
					',					
				],
				'acc_gen_arialive' => [
					'desc' => 'Announces active slide changes to screen readers via aria-live. Use this only if slide content needs dynamic announcement, and set the announcement text per slide as required.
					',					
				],
				'youtubetip' => [					
					'desc' => 'The “YouTube Stream” content source is used to display a full stream of videos from a channel/playlist. If you want to display a single youtube video, please select the content source “Default Slider” and add a video layer in the slide editor.',					
				],
				'vimeotip' => [					
					'desc' => 'The “Vimeo Stream” content source is used to display a full stream of videos from a user/album/group/channel. If you want to display a single vimeo video, please select the content source “Default Slider” and add a video layer in the slide editor.',					
				],
				'layerupscaling' => [					
					'desc' => 'Layer size will automatically increase to exceed the predefined dimensions on screens larger than your maximum custom-defined device size.',					
				],
				'keepflow' => [					
					'desc' => 'Sets the container width to auto and uses the Content Flow width (per device) as the limit.',
				],
				'infinitycarousel' => [					
					'desc' => 'When enabled, the carousel automatically adjusts the combined width or height of slides to exceed the visible area, allowing for seamless infinite scrolling.
					',					
				],
				'keepcarouselratio' => [					
					'desc' => 'Adjusts the width of individual slides based on the module’s height and aspect ratio to create a justified layout.
					',					
				],

				'stretchcarousel' => [					
					'desc' => 'Expands slides to fill the full width of the module, showing only one slide at a time in the visible area.
					',					
				],
				'globallayersvisibility' => [
					'desc' => 'Toggles the visibility of static slide layers when a non-static slide is selected in the editor.',
				],
				'carouselsiblings' => [					
					'desc' => 'Adjusts the distance, animation, and styling of non-focused carousel slides.<br><br>
					Note: Some effects (e.g., 3D Spin) only appear during transitions in preview or on the live site. The editor may show slight differences in spacing and sibling count.',					
				],
				'warning_global_px_scroll' => [					
					'desc' => 'On Scroll is DEACTIVATED under Settings > Scroll & Parallax.',					
				],
				'warning_global_px_mouse' => [					
					'desc' => 'On Mouse is DEACTIVATED  under Settings > Scroll & Parallax.',					
				],
				'aiupscaleimgsrc' => [					
					'desc' => 'Select an image from the WordPress library, Slider Revolution Elements library, from a layer, or generate with AI.',
				],
			],
			'slide' => [	
							
			],
			'global' => [	
				'gdprsettings' => [
					'desc' => 'Controls how YouTube and Vimeo videos are loaded depending on the active consent management plugin.<br><br>If "none", videos always allowed to play.<br><br>If active, consent is checked in the browser before loading the video.<br><br>While the visitor is deciding, loading waits up to 15 seconds. If no consent is given, the video stays blocked until the next page load.',
				],			
			],
			'layer' => [	
				'frames_custom_off' => [
					'desc' => 'Split attribute target<br>onto its own track'
				],
				'frames_custom_on' => [
					'desc' => 'Merge attribute back<br>into the parent track'
				],
				'loop_between_frames' => [
					'desc' => 'Loop animation in<br>between keyframes.'
				]				
			],
			'navigation' => [
                    'navthumbnail' => [
						'desc' => 'Optimization will be used only when new Image Picked. It is stored in the selected Format and will be used as Navigation Thumbnail.',
					]					
			],
			'blocksettingswrapperid' => [
				'desc' => 'Enter a word or two — without spaces or special characters — to make a unique web address just for this module.'
			]
		]);
	}	
}