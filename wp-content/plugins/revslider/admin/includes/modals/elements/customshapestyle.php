<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/*
	The colour half of the Custom Shape, split off the way svgstyle.php is split off svg.php: what the layer
	IS lives in the Layer tab, how it looks lives here. For a custom shape the geometry is the content — there
	is no source to pick — so the builder sits above Position & Size with the other content panels, and only
	fill, stroke and stroke width stay behind.

	These three are also exactly what a preset does NOT write (see shapeGen.lookKeys): picking another shape
	must not repaint it. That the split lands on the same line twice is not a coincidence — it is the same
	distinction, once in the data model and once in the panel.
*/
?>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Shape Style','revslider'); ?></sr-separator-title>
	</sr-separator-head>
	<sr-separator-body>

		<!-- Fill (solid OR gradient via the SR7 background picker) & stroke colour -->
		<sr-wrap half class="sr--form--grp sr--mr--10">
			<sr-color-mini data-v="transparent" r="shape.fill" data-type="background" class="sr--mr--10" viewchild="layer_style" ignoreredraw
						   data-onchange="editor.elements.shape.update" data-onclose="editor.elements.shape.update" data-undoredo="editor.elements.shape.update"></sr-color-mini>
			<span><?php _e('Fill','revslider'); ?></span>
		</sr-wrap><!--
		--><sr-wrap half class="sr--form--grp">
			<sr-color-mini r="shape.stroke" data-type="text" class="sr--mr--10" viewchild="layer_style" ignoreredraw
						   data-onchange="editor.elements.shape.update" data-onclose="editor.elements.shape.update" data-undoredo="editor.elements.shape.update"></sr-color-mini>
			<span><?php _e('Stroke','revslider'); ?></span>
		</sr-wrap>

		<!-- Stroke size (all kinds). It is the only thing that makes a Line visible at all, which is why the
		     Line preset is the one preset allowed to write it. -->
		<sr-input wide class="sr--mb--0 sr--mt--15">
			<input name="Stroke Size" viewchild="layer_style" r="shape.strokeWidth" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="20" suffix="px" lastsuffix="px" validate="true" type="text">
			<span noicon class="sr--form--otitle"><?php _e('Stroke Size','revslider'); ?></span>
		</sr-input>

		<sr-sp h="20"></sr-sp>
	</sr-separator-body>
</sr-separator>
