<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

/*
	The Custom Shape builder, in the LAYER tab above Position & Size — because for this layer type the
	geometry IS the content. Every other type puts its content there (text.php, image.php, svg.php's
	content.src) and only its colours in Style, and a custom shape has no source to pick, so the shape itself
	takes that slot. Fill and stroke live in customshapestyle.php, mirroring svg.php / svgstyle.php.
*/

//Which shape kinds each control applies to (sr-sh show/hide groups, bound to shape.kind). tools/shape-tiles
///tilecheck.js compares these against shapeGen's own metadata — a kind added on one side and not the other
//is a field that silently never appears, which is exactly how it would be missed.
//The generic names mean something different per kind, which is why the lists are not interchangeable:
//complexity is corner radius on rect, sides on polygon, points on star, shaft thickness on arrow;
//variation is spike depth on star, steepness on slant, band thickness on ring, head size on arrow.
$dep_complexity = 'rect#;#polygon#;#star#;#blob#;#arrow#;#bubble#;#wave#;#peaks#;#zigzag#;#scallop#;#blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
$dep_variation  = 'star#;#blob#;#ring#;#arrow#;#slant#;#wave#;#peaks#;#blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
$dep_profile    = 'slant#;#wave#;#peaks#;#zigzag#;#scallop#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks'; //balance / position / invert
$dep_layers     = 'blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
// The multiplier. A separate field from Layers on purpose (see nest() in elements/shape.js) and shown for a
// disjoint set of kinds, so the two numbers are never on screen together.
$dep_repeat     = 'line#;#rect#;#oval#;#polygon#;#star#;#blob#;#ring#;#arrow#;#bubble#;#heart#;#slant';
$dep_edge       = 'polygon#;#star#;#wave#;#peaks#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
// Randomize only changes shape.seed — hide it for kinds that ignore seed
$dep_randomize  = 'blob#;#wave#;#peaks#;#blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
// Morph is the one ANIMATION with a geometry dependency this strict, so its list lives up here with the
// field ones. It lerps between pre-baked keyframes, so a kind can only join it by having something to vary
// that leaves the path commands alone: a seed to re-roll (isSeeded) or one proportion to breathe
// (MORPH_PARAM). Everything else selected Morph and then sat perfectly still — an Oval and a Heart have no
// such knob at all, and polygon's Complexity IS its side count, which changes the number of points.
// = shapeGen.canMorph(), and tilecheck.js compares the two exactly, the way it does $dep_repeat.
$dep_morph      = 'blob#;#wave#;#peaks#;#blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks#;#star#;#ring#;#arrow#;#slant#;#rect#;#bubble';

// Which animation types each knob actually reaches. A slider shown where it does nothing is half of what made
// this block feel empty: drift never read Amount, and a full turn has no arc for Amount to scale.
// tilecheck.js compares $anim_all against the types SR7.F.shapeAnim really handles, so a type offered here and
// not implemented there (or the reverse) cannot go unnoticed — that was the other half.
$anim_all = 'drift#;#float#;#orbit#;#spin#;#sway#;#pulse#;#cascade#;#morph';
$anim_amt = 'float#;#orbit#;#sway#;#pulse#;#cascade#;#morph';
$anim_dir = 'drift#;#orbit#;#spin';
?>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Custom Shape','revslider'); ?></sr-separator-title>
	</sr-separator-head>
	<sr-separator-body>

		<!-- The catalogue as tiles, and the tile IS the shape: the generator that builds the layer draws the
		     picture too (shapeGen.tile), so a preset cannot look like one thing and apply as another. It
		     replaces BOTH drops that stood here — nine bundles behind "Pick a Preset" and fifteen Kinds behind
		     "Type" as bare words, two pickers for one decision. Paged behind a dot bar (owner.pages), so it
		     stands permanently open at a fixed two rows, and one page is one family.
		     Built from panel.update() in editor/elements/basics.js, next to the Cursor Motion preview hook —
		     there is no field in this block that every kind shows, so it cannot hang off a data-onset. -->
		<sr-wrap id="sr_shape_essentials"></sr-wrap>

		<!-- shape.kind is no longer a field of its own, so the dependent rows read it themselves. Same sr-sh
		     the Interaction panel uses for ix.type: value-bound, no driving control needed. -->

		<!-- Complexity -->
		<sr-sh r="shape.kind" viewchild="layer_basics" data-shdep="<?php echo $dep_complexity; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Complexity" viewchild="layer_basics" r="shape.complexity" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="2" max="20" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Complexity','revslider'); ?></span>
			</sr-input>
		</sr-sh>

		<!-- Variation -->
		<sr-sh r="shape.kind" viewchild="layer_basics" data-shdep="<?php echo $dep_variation; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Variation" viewchild="layer_basics" r="shape.variation" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="100" suffix="%" lastsuffix="%" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Variation','revslider'); ?></span>
			</sr-input>
		</sr-sh>

		<!-- Balance -->
		<sr-sh r="shape.kind" viewchild="layer_basics" data-shdep="<?php echo $dep_profile; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Balance" viewchild="layer_basics" r="shape.balance" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="100" suffix="%" lastsuffix="%" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Balance','revslider'); ?></span>
			</sr-input>
		</sr-sh>

		<!-- Layers (scene kinds only) -->
		<sr-sh r="shape.kind" viewchild="layer_basics" data-shdep="<?php echo $dep_layers; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Layers" viewchild="layer_basics" r="shape.layers" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="1" max="8" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Layers','revslider'); ?></span>
			</sr-input>
		</sr-sh>

		<!-- Repeat: the same shape again, nested inward — or spread across, for the ones with no inside
		     (Line becomes parallel rules, Slant becomes stacked bands).
		     🔴 def="1", not notset="1". shape.repeat is absent until something writes it, and notset only
		     paints a placeholder AFTER _tpt.validate has already run and flagged the field: validate reads
		     el.value, which an absent value sets to the STRING "undefined", fails parseFloat and lands on
		     `isbad = !suffixPattern.includes(v)` — with no suffix that is always true, so the field sat
		     permanently red. def/fallback is the hook validate itself offers for exactly this (tools.js:352). -->
		<sr-sh r="shape.kind" viewchild="layer_basics" data-shdep="<?php echo $dep_repeat; ?>">
			<sr-input wide class="sr--mb--0">
				<input name="Repeat" viewchild="layer_basics" r="shape.repeat" ignoreredraw replace def="1" data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="1" max="10" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Repeat','revslider'); ?></span>
			</sr-input>
		</sr-sh>

		<!-- Edge type + Position. The gap above sits on the drops, not in an sr-sp between the blocks: a spacer
		     out here survives the row being hidden, and with three conditional rows below it that added up to
		     dead panel on any kind that uses none of them (an Oval, say). -->
		<sr-sh r="shape.kind" viewchild="layer_basics" data-shdep="<?php echo $dep_edge; ?>">
			<sr-drop half r="shape.edge" viewchild="layer_basics" ignoreredraw data-v="smooth" class="sr--mr--10 sr--mb--0 sr--mt--10"
					 data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update">
				<sr-drop-view>
					<span class="sr--drop--value">Smooth</span>
					<span class="sr--form--otitle"><?php _e('Edge','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="smooth"><?php _e('Smooth','revslider'); ?></sr-drops>
				<sr-drops data-v="square"><?php _e('Square','revslider'); ?></sr-drops>
				<sr-drops data-v="pointy"><?php _e('Pointy','revslider'); ?></sr-drops>
			</sr-drop><!--
			--><sr-drop half r="shape.position" viewchild="layer_basics" ignoreredraw data-v="bottom" class="sr--mb--0 sr--mt--10"
					 data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update">
				<sr-drop-view>
					<span class="sr--drop--value">Bottom</span>
					<span class="sr--form--otitle"><?php _e('Position','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="bottom"><?php _e('Bottom','revslider'); ?></sr-drops>
				<sr-drops data-v="top"><?php _e('Top','revslider'); ?></sr-drops>
				<sr-drops data-v="left"><?php _e('Left','revslider'); ?></sr-drops>
				<sr-drops data-v="right"><?php _e('Right','revslider'); ?></sr-drops>
			</sr-drop>
		</sr-sh>

		<!-- Tools: Invert (profile kinds) + Randomize (seeded kinds only). Two different dependencies, so the
		     sr-sh IS the cell. Wrapping an sr-wrap[half] inside one made that wrap the :first-child of its own
		     wrapper, and sr-wrap[half]:first-child hands out a 10px gutter — at (0,2,1) it outranks
		     .sr--mr--0 (0,2,0), so both halves carried it, the row overflowed and broke onto two lines.
		     The 15px above rides on the cells for the same reason the row above carries its own 10px. -->
		<sr-sh half r="shape.kind" viewchild="layer_basics" data-shdep="<?php echo $dep_profile; ?>" class="sr--form--grp sr--mr--10 sr--mt--15">
			<sr-onoff r="shape.invert" viewchild="layer_basics" ignoreredraw class="sr--mr--10"
					  data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update"></sr-onoff>
			<span><?php _e('Invert','revslider'); ?></span>
		</sr-sh><!--
		--><sr-sh half r="shape.kind" viewchild="layer_basics" data-shdep="<?php echo $dep_randomize; ?>" class="sr--mt--15">
			<sr-button clean full class="sr--cta sr--mb--0 sr--center" data-action="editor.elements.shape.randomize">
				<svg class="sr--icon" width="12" height="12" transform="translate(0, 0)"><use xlink:href="#Preset_Random"></use></svg><?php _e('Randomize','revslider'); ?>
			</sr-button>
		</sr-sh>

		<sr-sp h="20"></sr-sp>
	</sr-separator-body>
</sr-separator>

<!-- Shape Animation stays with the geometry rather than moving to the Animation tab: that tab is the keyframe
     timeline, and this is an ambient loop baked into the shape itself. It is also coupled to the geometry —
     drift only tiles on profile kinds, morph only reshapes the seeded ones — so separating the two would mean
     reading one to understand the other. -->
<sr-separator keepborder class="collapsed">
	<sr-separator-head>
		<sr-separator-title><?php _e('Shape Animation','revslider'); ?></sr-separator-title>
		<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
	</sr-separator-head>
	<sr-separator-body>

		<!-- Animation type. Drift and Morph are the two that need the right GEOMETRY — drift only tiles on a
		     profile kind, morph only reshapes a seeded one — so on a Star, a Ring or an Arrow the original
		     four left nothing but Float. The middle group is transform-only and runs on anything.
		     Morph is the only one that cannot fall back: drift on a non-tileable kind and cascade on a
		     single-path one both land on a gentle float in SR7.F.shapeAnim, but morph with no keyframes
		     returns and leaves the shape standing. So it is the one that has to be taken OFF the list where
		     it does not apply (dep/depv, per-Option) rather than left to be picked and do nothing — the group
		     title said "Needs the right Shape" and there was no way to find out which. -->
		<!-- 🔴 Kind-dependent OPTIONS, not a kind-dependent drop: sr-sh hides a whole control, and every other
		     type here still applies to every shape. dep/depv filters the single Option as the list is built
		     (drop.js createOptionElement), so the label of an ALREADY stored morph is still resolved — a shape
		     saved before this, or moved onto a kind that cannot morph, keeps showing its own value instead of
		     falling back to the first Option. setPreset carries that case over to Float. -->

		<sr-drop wide r="shape.anim.type" viewchild="layer_basics" ignoreredraw data-v="none" class="sr--mb--10" dropsh="290"
				 data-sh=".sr--canim--spd,.sr--canim--amt,.sr--canim--dir" data-shdep="#eqvalue"
				 data-onchange="editor.elements.shape.anim" data-undoredo="editor.elements.shape.anim">
			<sr-drop-view>
				<span class="sr--drop--value">None</span>
				<span class="sr--form--otitle"><?php _e('Animation','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
			<sr-drops-title><?php _e('Any Shape','revslider'); ?></sr-drops-title>
			<sr-drops data-v="float"><?php _e('Float (gentle bob)','revslider'); ?></sr-drops>
			<sr-drops data-v="orbit"><?php _e('Orbit (small circle)','revslider'); ?></sr-drops>
			<sr-drops data-v="spin"><?php _e('Spin (full turn)','revslider'); ?></sr-drops>
			<sr-drops data-v="sway"><?php _e('Sway (pendulum)','revslider'); ?></sr-drops>
			<sr-drops data-v="pulse"><?php _e('Pulse (breathe)','revslider'); ?></sr-drops>
			<sr-drops-title><?php _e('Needs Layers or Repeat','revslider'); ?></sr-drops-title>
			<sr-drops data-v="cascade"><?php _e('Cascade (one by one)','revslider'); ?></sr-drops>
			<sr-drops-title><?php _e('Needs the right Shape','revslider'); ?></sr-drops-title>
			<sr-drops data-v="drift"><?php _e('Drift (waves slide)','revslider'); ?></sr-drops>
			<sr-drops data-v="morph" dep="shape.kind" depv="<?php echo $dep_morph; ?>"><?php _e('Morph (reshape)','revslider'); ?></sr-drops>
		</sr-drop>

		<!-- Speed — every type but None -->
		<sr-wrap wide class="sr--canim--spd" value="<?php echo $anim_all; ?>">
			<sr-input wide class="sr--mb--5">
				<!-- def mirrors what SR7.F.shapeAnim falls back to (layer.js: speed ?? 1, amount ?? 50) —
				     defaults() never writes a shape.anim block, so without it these two sat red on every shape
				     that has no animation yet, for the same reason Repeat did. Pre-existing; it never showed. -->
				<input name="Speed" viewchild="layer_basics" r="shape.anim.speed" ignoreredraw replace def="1" data-onchange="editor.elements.shape.anim" data-undoredo="editor.elements.shape.anim" livevisup autocomplete="off" dragnumber number="true" min="0.1" max="5" step="0.1" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Speed','revslider'); ?></span>
			</sr-input>
		</sr-wrap>

		<!-- Amount — hidden for Drift, which never read it, and for Spin, whose turn is a full circle -->
		<sr-wrap wide class="sr--canim--amt" value="<?php echo $anim_amt; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Amount" viewchild="layer_basics" r="shape.anim.amount" ignoreredraw replace def="50" data-onchange="editor.elements.shape.anim" data-undoredo="editor.elements.shape.anim" livevisup autocomplete="off" dragnumber number="true" min="0" max="100" suffix="%" lastsuffix="%" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Amount','revslider'); ?></span>
			</sr-input>
		</sr-wrap>

		<!-- Direction — the same field for all three that have one: which way the waves slide, which way it
		     turns, which way it goes round. Right reads as clockwise for the two that rotate. -->
		<sr-wrap wide basic class="sr--canim--dir" value="<?php echo $anim_dir; ?>">
			<sr-drop wide r="shape.anim.dir" viewchild="layer_basics" ignoreredraw data-v="left" class="sr--mb--0 sr--mt--10"
					 data-onchange="editor.elements.shape.anim" data-undoredo="editor.elements.shape.anim">
				<sr-drop-view>
					<span class="sr--drop--value">Left</span>
					<span class="sr--form--otitle"><?php _e('Direction','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="left"><?php _e('Left','revslider'); ?></sr-drops>
				<sr-drops data-v="right"><?php _e('Right','revslider'); ?></sr-drops>
			</sr-drop>
		</sr-wrap>

		<sr-sp h="20"></sr-sp>
	</sr-separator-body>
</sr-separator>
