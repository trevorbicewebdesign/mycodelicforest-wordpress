<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

//Which shape kinds each control applies to (#eqvalue show/hide groups)
$dep_complexity = 'rect#;#polygon#;#star#;#blob#;#wave#;#peaks#;#zigzag#;#scallop#;#blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
$dep_variation  = 'star#;#blob#;#wave#;#peaks#;#blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
$dep_profile    = 'wave#;#peaks#;#zigzag#;#scallop#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks'; //balance / position / invert
$dep_layers     = 'blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
$dep_edge       = 'polygon#;#star#;#wave#;#peaks#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
// Randomize only changes shape.seed — hide it for kinds that ignore seed (line/rect/oval/polygon/star/zigzag/scallop)
$dep_randomize  = 'blob#;#wave#;#peaks#;#blobScene#;#layeredWaves#;#stackedWaves#;#stackedPeaks#;#layeredPeaks';
?>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Custom Shape','revslider'); ?></sr-separator-title>
	</sr-separator-head>
	<sr-separator-body>

		<sr-drop wide keepvalue class="sr--mb--10" dropsw="400" dropsh="250" data-defval="Preset" data-v="Preset" data-name="preset" data-onchange="editor.elements.shape.preset">
                <sr-drop-view>
                    <span class="sr--drop--value"><?php _e('Pick a Preset','revslider'); ?></span>
					<span class="sr--form--otitle"><?php _e('Preset','revslider'); ?></span>
                    <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                </sr-drop-view>
			<sr-drops-title><?php _e('Waves','revslider'); ?></sr-drops-title>
			<sr-drops data-v="calmWave"><?php _e('Calm Wave','revslider'); ?></sr-drops>
			<sr-drops data-v="boldWave"><?php _e('Bold Wave','revslider'); ?></sr-drops>
			<sr-drops data-v="spikyPeaks"><?php _e('Spiky Peaks','revslider'); ?></sr-drops>
			<sr-drops data-v="gentleHills"><?php _e('Gentle Hills','revslider'); ?></sr-drops>
			<sr-drops data-v="oceanLayers"><?php _e('Ocean Layers','revslider'); ?></sr-drops>
			<sr-drops-title><?php _e('Blobs','revslider'); ?></sr-drops-title>
			<sr-drops data-v="bubblyBlobs"><?php _e('Bubbly Blobs','revslider'); ?></sr-drops>
			<sr-drops data-v="softBlob"><?php _e('Soft Blob','revslider'); ?></sr-drops>
			<sr-drops-title><?php _e('Edges','revslider'); ?></sr-drops-title>
			<sr-drops data-v="zigzagBand"><?php _e('Zigzag Band','revslider'); ?></sr-drops>
			<sr-drops data-v="scallopEdge"><?php _e('Scallop Edge','revslider'); ?></sr-drops>
		</sr-drop>

		<!-- Shape kind: drives the #eqvalue show/hide of all dependent fields below -->
		<sr-drop wide r="shape.kind" viewchild="layer_style" ignoreredraw data-v="wave" class="sr--mb--10"
				 data-sh=".sr--cshape--dep" data-shdep="#eqvalue"
				 data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update">
			<sr-drop-view>
				<span class="sr--drop--value">Wave</span>
				<span class="sr--form--otitle"><?php _e('Type','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="line"><?php _e('Line','revslider'); ?></sr-drops>
			<sr-drops data-v="rect"><?php _e('Rectangle','revslider'); ?></sr-drops>
			<sr-drops data-v="oval"><?php _e('Oval','revslider'); ?></sr-drops>
			<sr-drops data-v="polygon"><?php _e('Polygon','revslider'); ?></sr-drops>
			<sr-drops data-v="star"><?php _e('Star','revslider'); ?></sr-drops>
			<sr-drops data-v="blob"><?php _e('Blob','revslider'); ?></sr-drops>
			<sr-drops data-v="wave"><?php _e('Wave','revslider'); ?></sr-drops>
			<sr-drops data-v="peaks"><?php _e('Peaks','revslider'); ?></sr-drops>
			<sr-drops data-v="zigzag"><?php _e('Zigzag','revslider'); ?></sr-drops>
			<sr-drops data-v="scallop"><?php _e('Scallop','revslider'); ?></sr-drops>
			<sr-drops data-v="blobScene"><?php _e('Blob Scene','revslider'); ?></sr-drops>
			<sr-drops data-v="layeredWaves"><?php _e('Layered Waves','revslider'); ?></sr-drops>
			<sr-drops data-v="stackedWaves"><?php _e('Stacked Waves','revslider'); ?></sr-drops>
			<sr-drops data-v="stackedPeaks"><?php _e('Stacked Peaks','revslider'); ?></sr-drops>
			<sr-drops data-v="layeredPeaks"><?php _e('Layered Peaks','revslider'); ?></sr-drops>
		</sr-drop>

		<!-- Complexity -->
		<sr-wrap wide class="sr--cshape--dep" value="<?php echo $dep_complexity; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Complexity" viewchild="layer_style" r="shape.complexity" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="2" max="20" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Complexity','revslider'); ?></span>
			</sr-input>
		</sr-wrap>

		<!-- Variation -->
		<sr-wrap wide class="sr--cshape--dep" value="<?php echo $dep_variation; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Variation" viewchild="layer_style" r="shape.variation" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="100" suffix="%" lastsuffix="%" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Variation','revslider'); ?></span>
			</sr-input>
		</sr-wrap>

		<!-- Balance -->
		<sr-wrap wide class="sr--cshape--dep" value="<?php echo $dep_profile; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Balance" viewchild="layer_style" r="shape.balance" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="100" suffix="%" lastsuffix="%" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Balance','revslider'); ?></span>
			</sr-input>
		</sr-wrap>

		<!-- Layers (scene kinds only) -->
		<sr-wrap wide class="sr--cshape--dep" value="<?php echo $dep_layers; ?>">
			<sr-input wide class="sr--mb--5">
				<input name="Layers" viewchild="layer_style" r="shape.layers" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="1" max="8" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Layers','revslider'); ?></span>
			</sr-input>
		</sr-wrap>

		<!-- Stroke size (all kinds) -->
		<sr-input wide class="sr--mb--10">
			<input name="Stroke Size" viewchild="layer_style" r="shape.strokeWidth" ignoreredraw replace data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="20" suffix="px" lastsuffix="px" validate="true" type="text">
			<span noicon class="sr--form--otitle"><?php _e('Stroke Size','revslider'); ?></span>
		</sr-input>

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

		<sr-sp h="10"></sr-sp>

		<!-- Edge type + Position -->
		<sr-wrap wide basic class="sr--cshape--dep" value="<?php echo $dep_edge; ?>">
			<sr-drop half r="shape.edge" viewchild="layer_style" ignoreredraw data-v="smooth" class="sr--mr--10 sr--mb--0"
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
			--><sr-drop half r="shape.position" viewchild="layer_style" ignoreredraw data-v="bottom" class="sr--mb--0"
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
		</sr-wrap>

		<sr-sp h="15"></sr-sp>

		<!-- Tools: Invert (profile kinds) + Randomize (seeded kinds only) -->
		<sr-wrap half basic class="sr--form--grp sr--mr--10 sr--cshape--dep" value="<?php echo $dep_profile; ?>">
			<sr-onoff r="shape.invert" viewchild="layer_style" ignoreredraw class="sr--mr--10"
					  data-onchange="editor.elements.shape.update" data-undoredo="editor.elements.shape.update"></sr-onoff>
			<span><?php _e('Invert','revslider'); ?></span>
		</sr-wrap><!--
		--><sr-wrap half basic class="sr--cshape--dep" value="<?php echo $dep_randomize; ?>">
			<sr-button clean full class="sr--cta sr--mb--0 sr--center" data-action="editor.elements.shape.randomize">
				<svg class="sr--icon" width="12" height="12" transform="translate(0, 0)"><use xlink:href="#Preset_Random"></use></svg><?php _e('Randomize','revslider'); ?>
			</sr-button>
		</sr-wrap>

		<sr-sp h="20"></sr-sp>
	</sr-separator-body>
</sr-separator>

<sr-separator keepborder class="collapsed">
	<sr-separator-head>
		<sr-separator-title><?php _e('Shape Animation','revslider'); ?></sr-separator-title>
		<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
	</sr-separator-head>
	<sr-separator-body>

		<!-- Animation type -->
		<sr-drop wide r="shape.anim.type" viewchild="layer_style" ignoreredraw data-v="none" class="sr--mb--10"
				 data-sh=".sr--canim--dir" data-shdep="#eqvalue"
				 data-onchange="editor.elements.shape.anim" data-undoredo="editor.elements.shape.anim">
			<sr-drop-view>
				<span class="sr--drop--value">None</span>
				<span class="sr--form--otitle"><?php _e('Animation','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
			<sr-drops data-v="drift"><?php _e('Drift (waves slide)','revslider'); ?></sr-drops>
			<sr-drops data-v="float"><?php _e('Float (gentle bob)','revslider'); ?></sr-drops>
			<sr-drops data-v="morph"><?php _e('Morph (reshape)','revslider'); ?></sr-drops>
		</sr-drop>

		<!-- Speed -->
		<sr-input wide class="sr--mb--5">
			<input name="Speed" viewchild="layer_style" r="shape.anim.speed" ignoreredraw replace data-onchange="editor.elements.shape.anim" data-undoredo="editor.elements.shape.anim" livevisup autocomplete="off" dragnumber number="true" min="0.1" max="5" step="0.1" validate="true" type="text">
			<span noicon class="sr--form--otitle"><?php _e('Speed','revslider'); ?></span>
		</sr-input>

		<!-- Amount (float/morph intensity) -->
		<sr-input wide class="sr--mb--10">
			<input name="Amount" viewchild="layer_style" r="shape.anim.amount" ignoreredraw replace data-onchange="editor.elements.shape.anim" data-undoredo="editor.elements.shape.anim" livevisup autocomplete="off" dragnumber number="true" min="0" max="100" suffix="%" lastsuffix="%" validate="true" type="text">
			<span noicon class="sr--form--otitle"><?php _e('Amount','revslider'); ?></span>
		</sr-input>

		<!-- Drift direction (only for drift) -->
		<sr-wrap wide basic class="sr--canim--dir" value="drift">
			<sr-drop wide r="shape.anim.dir" viewchild="layer_style" ignoreredraw data-v="left" class="sr--mb--0"
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
