<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('SVG Content','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>  						
		<sr-wrap id="sr_layer_svg_content" class="sr_image_selector">				
			<sr-wrap>
				<sr-wrap inline class="sr--mr--10">
					<sr-bg-src style="width:84px">
						<sr-bg-img r="content.src" viewchild="layer_basics" data-onchange="editor.elements.svg.update" data-undoredo="editor.elements.svg.update">
							<svg class="sr--bg--mountain" width="30" height="16.364" transform="translate(0, -2)"><use xlink:href="#Mountain"></use></svg>
							<sr--bg--picker-wrap>								
								<svg data-action="B.imgPick.wpsvg" class="sr--bg--picker" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#WPIcon"></use></svg>
								<svg data-action="B.imgPick.sr" class="sr--bg--picker" data-aparams="filterType:icons||svgs#;#strictType:icons||svgs" width="18" height="18.001" transform="translate(0, -2)"><use xlink:href="#SRIcon"></use></svg>								
							</sr--bg--picker-wrap>
							<svg data-action="B.imgPick.clear" viewchild="layer_basics" class="sr--bg--clear" width="14" height="14" transform="translate(0, -2)"><use xlink:href="#General_Close"></use></svg>
						</sr-bg-img>						
					</sr-bg-src>
				</sr-wrap>				
			</sr-wrap>			
		</sr-wrap>		
		<sr-sp h="20"></sr-sp>  
	</sr-separator-body>
</sr-separator>

