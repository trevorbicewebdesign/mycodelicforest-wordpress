<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();

$dep_translate = 'magnetic#;#attract#;#repel'; // axis only applies to the move modes
?>
<!-- Master "Interaction" toggle (pE / pointer-events). Off = the element ignores the cursor entirely.
     When on, three dropdowns sit directly under each other; each reveals its own settings below when set. -->
<sr-separator noborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Interaction','revslider'); ?></sr-separator-title>
		<sr-onoff data-on="auto" data-off="none" class="sr--mr--0" style="right:0px" data-sh=".sr_elements_pointere" r="pE" viewchild="layer_hover"></sr-onoff>
	</sr-separator-head>
	<sr-separator-body class="sr_elements_pointere">

		<!-- Cursor Icon -->
		<sr-drop wide r="cursor" viewchild="layer_hover" data-v="" class="sr--mb--10">
			<sr-drop-view>
				<span class="sr--drop--value">Default</span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('Cursor Icon','revslider'); ?></span>
			</sr-drop-view>
			<sr-drops data-v="auto"><?php _e('Auto','revslider'); ?></sr-drops><sr-drops data-v="default"><?php _e('Default','revslider'); ?></sr-drops><sr-drops data-v="cursor"><?php _e('Cursor','revslider'); ?></sr-drops><sr-drops data-v="crosshair"><?php _e('Crosshair','revslider'); ?></sr-drops><sr-drops data-v="pointer"><?php _e('Pointer','revslider'); ?></sr-drops><sr-drops data-v="move"><?php _e('Move','revslider'); ?></sr-drops><sr-drops data-v="text"><?php _e('Text','revslider'); ?></sr-drops><sr-drops data-v="wait"><?php _e('Wait','revslider'); ?></sr-drops><sr-drops data-v="help"><?php _e('Help','revslider'); ?></sr-drops><sr-drops data-v="zoom-in"><?php _e('Zoom-in','revslider'); ?></sr-drops><sr-drops data-v="zoom-out"><?php _e('Zoom-out','revslider'); ?></sr-drops><sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
		</sr-drop>

		<!-- Cursor Motion (reacts to the cursor; composes on top of all other animations) -->
		<sr-drop wide r="ix.type" viewchild="layer_hover" ignoreredraw data-v="none" data-defval="none" class="sr--mb--10"
				 data-sh=".sr--ix--dep" data-shdep="#eqvalue"
				 data-onchange="editor.elements.ix.setType" data-undoredo="editor.elements.ix.setType">
			<sr-drop-view>
				<span class="sr--drop--value">None</span>
				<span class="sr--form--otitle"><?php _e('Cursor Motion','revslider'); ?></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
			<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
			<sr-drops-title><?php _e('Move','revslider'); ?></sr-drops-title>
			<sr-drops data-v="magnetic"><?php _e('Magnetic (follow)','revslider'); ?></sr-drops>
			<sr-drops data-v="attract"><?php _e('Attract (lean)','revslider'); ?></sr-drops>
			<sr-drops data-v="repel"><?php _e('Repel / Push','revslider'); ?></sr-drops>
			<sr-drops-title><?php _e('Scale','revslider'); ?></sr-drops-title>
			<sr-drops data-v="pop"><?php _e('Pop (grow)','revslider'); ?></sr-drops>
			<sr-drops data-v="squeeze"><?php _e('Squeeze (shrink)','revslider'); ?></sr-drops>
			<sr-drops data-v="stretch"><?php _e('Stretch (toward cursor)','revslider'); ?></sr-drops>
			<sr-drops-title><?php _e('Rotate','revslider'); ?></sr-drops-title>
			<sr-drops data-v="tilt"><?php _e('Tilt 3D','revslider'); ?></sr-drops>
			<sr-drops data-v="rotate"><?php _e('Swing (2D)','revslider'); ?></sr-drops>
		</sr-drop>

		<!-- Style & Transform enable (Disabled / Enabled / Disabled on Mobile) -->
		<sr-drop wide r="hov.u" viewchild="layer_hover" data-v="" data-defval="false" class="sr--mb--0" data-sh=".sr_elements_hovanims" data-onchange="editor.elements.mouse.reset" data-onchangeparam="enableddisabled" data-shdep="#eqvalue">
			<sr-drop-view>
				<span class="sr--drop--value">Default</span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				<span class="sr--form--otitle"><?php _e('Style & Transform','revslider'); ?></span>
			</sr-drop-view>
			<sr-drops data-v="false"><?php _e('Disabled','revslider'); ?></sr-drops><sr-drops data-v="true"><?php _e('Enabled','revslider'); ?></sr-drops><sr-drops data-v="desktop"><?php _e('Disabled on Mobile','revslider'); ?></sr-drops>
		</sr-drop>
		<sr-sp h="20"></sr-sp>
	</sr-separator-body>
</sr-separator>

<!-- Conditional settings — only rendered once a Cursor Motion type / Style & Transform mode is chosen -->
<sr-wrap class="sr_elements_pointere">

	<!-- Cursor Motion settings -->
	<sr-wrap wide class="sr--ix--dep" value="magnetic#;#attract#;#repel#;#squeeze#;#pop#;#stretch#;#tilt#;#rotate">
		<sr-separator topborder keepborder>
			<sr-separator-head notoggle>
				<sr-separator-title><?php _e('Cursor Motion','revslider'); ?></sr-separator-title>
			</sr-separator-head>
			<sr-separator-body>
				<sr-input wide class="sr--mb--5">
					<input name="Strength" viewchild="layer_hover" r="ix.strength" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="150" validate="true" type="text">
					<span noicon class="sr--form--otitle"><?php _e('Strength','revslider'); ?></span>
				</sr-input>

				<sr-input wide class="sr--mb--5">
					<input name="Radius" viewchild="layer_hover" r="ix.radius" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="20" max="800" suffix="px" lastsuffix="px" validate="true" type="text">
					<span noicon class="sr--form--otitle"><?php _e('Proximity Radius','revslider'); ?></span>
				</sr-input>

				<sr-input wide class="sr--mb--10">
					<input name="Speed" viewchild="layer_hover" r="ix.speed" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="2" max="40" validate="true" type="text">
					<span noicon class="sr--form--otitle"><?php _e('Follow Speed','revslider'); ?></span>
				</sr-input>

				<!-- Return ease: how it springs back when the cursor leaves the field -->
				<sr-drop wide r="ix.returnEase" viewchild="layer_hover" ignoreredraw data-v="smooth" data-defval="smooth" class="sr--mb--10"
						 data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update">
					<sr-drop-view>
						<span class="sr--drop--value">Smooth</span>
						<span class="sr--form--otitle"><?php _e('Return','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="smooth"><?php _e('Smooth','revslider'); ?></sr-drops>
					<sr-drops data-v="back"><?php _e('Back (overshoot)','revslider'); ?></sr-drops>
					<sr-drops data-v="elastic"><?php _e('Elastic','revslider'); ?></sr-drops>
					<sr-drops data-v="bounce"><?php _e('Bounce','revslider'); ?></sr-drops>
				</sr-drop>

				<!-- Axis lock: only meaningful for the move modes -->
				<sr-wrap wide class="sr--ix--dep" value="<?php echo $dep_translate; ?>">
					<sr-drop wide r="ix.axis" viewchild="layer_hover" ignoreredraw data-v="both" data-defval="both" class="sr--mb--0"
							 data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update">
						<sr-drop-view>
							<span class="sr--drop--value">Both</span>
							<span class="sr--form--otitle"><?php _e('Axis','revslider'); ?></span>
							<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
						</sr-drop-view>
						<sr-drops data-v="both"><?php _e('Both','revslider'); ?></sr-drops>
						<sr-drops data-v="x"><?php _e('Horizontal','revslider'); ?></sr-drops>
						<sr-drops data-v="y"><?php _e('Vertical','revslider'); ?></sr-drops>
					</sr-drop>
				</sr-wrap>

				<!-- Perspective: only for Tilt 3D -->
				<sr-wrap wide class="sr--ix--dep" value="tilt">
					<sr-sp h="10"></sr-sp>
					<sr-input wide class="sr--mb--0">
						<input name="Perspective" viewchild="layer_hover" r="ix.perspective" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="200" max="3000" suffix="px" lastsuffix="px" validate="true" type="text">
						<span noicon class="sr--form--otitle"><?php _e('Perspective','revslider'); ?></span>
					</sr-input>
				</sr-wrap>
				<sr-sp h="10"></sr-sp>
			</sr-separator-body>
		</sr-separator>
	</sr-wrap>

	<!-- Style & Transform settings -->
	<sr-separator value="true#;#desktop" class="sr_elements_hovanims" data-menter="editor.elements.mouse.hover" data-mleave="editor.elements.mouse.idle" keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Style','revslider'); ?></sr-separator-title>
			<sr-wrap wide class="sr--on--par--hover sr--mini--title sr--mb--0" style="float:right" clean=""><sr-button viewchild="layer_hover"  data-action="editor.elements.mouse.reset"><?php _e('Reset Style','revslider'); ?></sr-button></sr-wrap>
		</sr-separator-head>
		<sr-separator-body>
			<sr-wrap-dep dep="is[text,button]">
				<sr-wrap onethird class="sr--form--grp"><sr-color-mini data-v="transparent" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" data-onchangeparams="direct" ignoreredraw r="hov.color.c" data-title="<?php _e('Text Hover Color','revslider'); ?>" data-type="text" class="sr--mr--10"></sr-color-mini><span><?php _e('Text','revslider'); ?></span></sr-wrap><!--
				--><sr-drop twothird data-v="" r="hov.deco" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" ignoreredraw>
					<sr-drop-view>
						<span class="sr--drop--value"></span>
						<span class="sr--form--otitle"><?php _e('Text Deco.','revslider'); ?></span>
						<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
					</sr-drop-view>
					<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
					<sr-drops data-v="overline"><?php _e('Overline','revslider'); ?></sr-drops>
					<sr-drops data-v="underline"><?php _e('Underline','revslider'); ?></sr-drops>
					<sr-drops data-v="line-through"><?php _e('Line Through','revslider'); ?></sr-drops>
				</sr-drop>
			</sr-wrap-dep>
			<sr-wrap-dep dep="is[svg]">
				<sr-wrap class="sr--form--grp"><sr-onoff class="sr--mr--10" data-hide=".sr_elements_showsvg" r="content.oC" viewchild="layer_hover" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover"></sr-onoff><span><?php _e('Keep Original Colors','revslider'); ?></span></sr-wrap>
				<sr-wrap class="sr_elements_showsvg">
					<sr-sp h="15"></sr-sp>
					<sr-wrap half class="sr--form--grp" half><sr-color-mini data-v="transparent" r="hov.svg.c" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover" responsive data-type="text" class="sr--mr--10" viewchild="layer_hover"></sr-color-mini><span class="sr--mr--30"><?php _e('SVG Color','revaslider');?></span></sr-wrap><!--
					--><sr-wrap half class="sr--form--grp" half><sr-color-mini data-v="transparent" r="hov.svg.stroke.c" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover"responsive data-type="text" class="sr--mr--10" viewchild="layer_hover"></sr-color-mini><span><?php _e('Stroke Color','revaslider');?></span></sr-wrap>
					<sr-sp h="15"></sr-sp>
					<sr-input onethird class="sr--mr--6 sr--mb--0"><!--Stroke Width-->
						<input name="Stroke Width" viewchild="layer_hover" r="hov.svg.stroke.w" replace livevisup autocomplete="off" number="true" min="0" max="500" suffix="" lastsuffix="" step="0.1" validate="true" type="text" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover">
						<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Width"></use></svg></span>
					</sr-input><!--
					--><sr-input onethird class="sr--mr--6 sr--mb--0"><!--Dash Array-->
						<input name="Dash Array" viewchild="layer_hover" r="hov.svg.stroke.d"  replace validate allowedchars="1234567890," livevisup autocomplete="off" type="text" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover">
						<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Dash"></use></svg></span>
					</sr-input><!--
					--><sr-input onethird class="sr--mb--0"><!--Dash Offset-->
						<input name="Dash Offset" viewchild="layer_hover" r="hov.svg.stroke.o" replace livevisup autocomplete="off" type="text" number="true" min="-100" max="100"  step="0.5" validate="true" type="text" livevisup autocomplete="off" data-onchange="editor.elements.svg.updateHover" data-undoredo="editor.elements.svg.updateHover">
						<span class="sr--input--icon"><svg width="14" height="14" transform="translate(0, 2)"><use xlink:href="#SVG_Offset"></use></svg></span>
					</sr-input>
				</sr-wrap>
				<sr-sp h="18"></sr-sp>
			</sr-wrap-dep>
			<sr-wrap onethird class="sr--form--grp sr--mr--10"><sr-color-mini data-v="transparent" r="hov.color.bg" data-onchange="editor.elements.mouse.rehover" data-onchangeparams="direct" ignoreredraw data-type="background" viewchild="layer_hover" class="sr--mr--10"></sr-color-mini><span><?php _e('BG','revaslider');?></span></sr-wrap><!--
			--><sr-drop twothird data-v="" r="hov.color.gAnim" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" ignoreredraw>
				<sr-drop-view>
					<span class="sr--drop--value"></span>
					<span class="sr--form--otitle"><?php _e('Gradient Anim.','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="sliding"><?php _e('Slide','revslider'); ?></sr-drops>
				<sr-drops data-v="fading"><?php _e('Fade','revslider'); ?></sr-drops>
			</sr-drop>
			<sr-wrap onethird class="sr--form--grp sr--mr--10"><sr-color-mini data-v="transparent" r="hov.border.c" data-onchange="editor.elements.mouse.rehover" data-onchangeparams="direct" ignoreredraw data-type="text" class="sr--mr--10"  viewchild="layer_hover"></sr-color-mini><span><?php _e('Border','revaslider');?></span></sr-wrap><!--
		--><sr-drop data-onchange="editor.elements.mouse.rehover" ignoreredraw r="hov.border.s" data-sh=".sr_elements_hov_border_style" data-shdep="#eqvalue" viewchild="layer_hover" twothird data-v="cover" dropsw="200">
				<sr-drop-view>
					<span class="sr--drop--value">None</span>
					<span class="sr--form--otitle"><?php _e('Border Style','revslider'); ?></span>
					<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
				</sr-drop-view>
				<sr-drops data-v="none"><?php _e('None','revslider'); ?></sr-drops>
				<sr-drops data-v="solid"><?php _e('Solid','revslider'); ?></sr-drops>
				<sr-drops data-v="dotted"><?php _e('Dotted','revslider'); ?></sr-drops>
				<sr-drops data-v="dashed"><?php _e('Dashed','revslider'); ?></sr-drops>
				<sr-drops data-v="double"><?php _e('Double','revslider'); ?></sr-drops>
				<sr-drops data-v="groove"><?php _e('Groove','revslider'); ?></sr-drops>
				<sr-drops data-v="ridge"><?php _e('Ridge','revslider'); ?></sr-drops>
				<sr-drops data-v="inset"><?php _e('Inset','revslider'); ?></sr-drops>
				<sr-drops data-v="outset"><?php _e('Outset','revslider'); ?></sr-drops>
			</sr-drop>
			<sr-wrap value="dotted#;#dashed#;#solid#;#double#;#groove#;#ridge#;#inset#;#outset" class="sr_elements_hov_border_style">
				<sr-bmp type="border" idpref="sr_layer_border_hov_full_" r="hov.border.w" data-onchange="editor.elements.mouse.rehover" ignoreredraw respshow="f-320middle" viewchild="layer_hover"></sr-bmp>
			</sr-wrap>
			<sr-bmp type="radius" idpref="sr_layer_radius_hov_full_" r="hov.radius" data-onchange="editor.elements.mouse.rehover" ignoreredraw viewchild="layer_hover"></sr-bmp>
			<sr-sp h="5"></sr-sp>
		</sr-separator-body>
	</sr-separator>

	<sr-separator value="true#;#desktop" class="sr_elements_hovanims" data-menter="editor.elements.mouse.hover" data-mleave="editor.elements.mouse.idle" keepborder>
		<sr-separator-head>
			<sr-separator-title><?php _e('Filters','revslider'); ?></sr-separator-title>
			<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
		</sr-separator-head>
		<sr-separator-body>
			<sr-input onethird class="sr--mr--6">
				<input name="Blur" viewchild="layer_hover"  replace livevisup autocomplete="off" type="text" r="hov.filter.b" data-onchange="editor.elements.mouse.rehover" ignoreredraw number="true" min="0" max="500" suffix="|inherit" lastsuffix="" validate="true" type="text" livevisup>
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Blur"></use></svg></span>
				<sr-drop class="sr--drop--only--icon" list="inherit,0,2,5,10,20,50" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
					<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
				</sr-drop>
			</sr-input><!--
			--><sr-input onethird class="sr--mr--6">
				<input name="Grayscale" viewchild="layer_hover" r="hov.filter.g" replace livevisup autocomplete="off" data-onchange="editor.elements.mouse.rehover" ignoreredraw number="true" min="0" max="100" suffix="iherit|" lastsuffix=""  validate="true" type="text">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Grayscale"></use></svg></span>
				<sr-drop class="sr--drop--only--icon" list="inherit,0,50,100" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
					<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
				</sr-drop>
			</sr-input><!--
			--><sr-input onethird>
				<input name="Brightness" viewchild="layer_hover" r="hov.filter.r"  replace livevisup autocomplete="off" data-onchange="editor.elements.mouse.rehover" ignoreredraw number="true" min="0" max="500" suffix="%|inherit" lastsuffix="%"  validate="true" type="text">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(2, 3)"><use xlink:href="#Brightness"></use></svg></span>
				<sr-drop class="sr--drop--only--icon" list="inherit,0,50%,100%,120%,150%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
					<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
				</sr-drop>
			</sr-input>

			<sr-sp h="5"></sr-sp>
		</sr-separator-body>
	</sr-separator>
	<sr-separator value="true#;#desktop" class="sr_elements_hovanims" data-menter="editor.elements.mouse.hover" data-mleave="editor.elements.mouse.idle" keepborder>
	<sr-separator-head>
			<sr-separator-title><?php _e('Animation','revslider'); ?></sr-separator-title>
			<sr-separator-toggle><svg class="sr--icon" width="20" height="12"><use xlink:href="#General_Expand_Large"></use></svg></sr-separator-toggle>
		</sr-separator-head>
		<sr-separator-body>
		<sr-input onethird class="sr--mr--10">
				<input name="Animation Duration" replace r="hov.frame.d" viewchild="layer_hover" type="text" number="true" suffix="ms" min="0" max="100000" fallback="0" data-onchange="editor.elements.mouse.rehover" ignoreredraw validate="true">
				<span class="sr--input--icon"><svg width="14" height="14" transform="translate(5, 3)"><use xlink:href="#Options_Timing"></use></svg></span>
		</sr-input><!--
		--><sr-drop twothird data-v="" r="hov.frame.e" data-source="ease" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" ignoreredraw>
			<sr-drop-view>
				<span class="sr--drop--value"><?php _e('None','revslider'); ?></span>
				<span class="sr--form--otitle"></span>
				<span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
			</sr-drop-view>
		</sr-drop>
		<sr-input half class="sr--mr--10">
			<input name="zIndex" replace r="hov.zIndex" viewchild="layer_hover" type="text"  number="true" data-onchange="editor.elements.mouse.rehover" ignoreredraw validate="true" min="0" max="5000" step="10" suffix="|auto"><span noicon="" class="sr--form--otitle"><?php _e('zIndex','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="auto,1,100,500,1000" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
	--><sr-input half>
			<input name="Opacity" replace r="hov.frame.o" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" ignoreredraw type="text" number="true" min="0" max="1" step="0.1" validate="true">
			<span class="sr--input--icon"><svg width="11.1" height="14" transform="translate(3, 3)"><use xlink:href="#Timeline_Opacity"></use></svg></span>
		</sr-input>

		<sr-input half class="sr--mr--10">
			<input name="Scale X" replace r="hov.frame.sX" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" ignoreredraw type="text"  number="true" validate="true" min="0" max="10" step="0.2" suffix="|inherit"><span noicon="" class="sr--form--otitle"><?php _e('scaleX','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0.5,1,2" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
	--><sr-input half class="sr--mr--0">
			<input name="Scale Y" replace r="hov.frame.sY" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" ignoreredraw type="text"  number="true" validate="true" min="0" max="10" step="0.2" suffix="|inherit"><span noicon="" class="sr--form--otitle"><?php _e('scaleY','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0.5,1,2" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input>

		<sr-input half class="sr--mr--10">
			<input name="Skew X" replace r="hov.frame.skX" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" ignoreredraw type="text"  number="true" validate="true" min="-100" max="100" step="1" suffix="|inherit"><span noicon="" class="sr--form--otitle"><?php _e('skewX','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,10,50,-10,-50" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
	--><sr-input half class="sr--mr--0">
			<input name="Skew Y" replace r="hov.frame.skY" viewchild="layer_hover" data-onchange="editor.elements.mouse.rehover" ignoreredraw type="text"  number="true" validate="true" min="-100" max="100" step="1" suffix="|inherit"><span noicon="" class="sr--form--otitle"><?php _e('skewY','revslider'); ?></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,10,50,-10,-50" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input>

		<sr-input onethird class="sr--mr--6">
			<input name="Rotation X" viewchild="layer_hover" r="hov.frame.rX" replace data-onchange="editor.elements.mouse.reWrap" ignoreredraw livevisup autocomplete="off" number="true" min="-5000" max="500" suffix="deg|inherit" lastsuffix="deg"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(4, 4) rotate(90)"><use xlink:href="#Options_Rotate_X"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
		--><sr-input onethird class="sr--mr--6">
			<input name="Rotation Y" viewchild="layer_hover" r="hov.frame.rY"  replace data-onchange="editor.elements.mouse.reWrap" ignoreredraw livevisup autocomplete="off" number="true" min="-5000" max="5000" suffix="deg|inherit" lastsuffix="deg"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Y"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
		--><sr-input onethird>
			<input name="Rotation Z" viewchild="layer_hover"  replace livevisup autocomplete="off" type="text" data-onchange="editor.elements.mouse.reWrap" ignoreredraw r="hov.frame.rZ" number="true" min="-5000" max="5000" lastsuffix="deg" suffix="deg|inherit" validate="true" type="text" livevisup>
			<span class="sr--input--icon"><svg width="20" height="20" transform="translate(6, 4)"><use xlink:href="#Options_Rotate_Z"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,45,90,180,270,360" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input>

		<sr-input onethird class="sr--mr--6">
			<input name="Origin X" viewchild="layer_hover" r="hov.frame.oX" replace data-onchange="editor.elements.mouse.reWrap" ignoreredraw livevisup autocomplete="off" number="true" min="-2500" max="2500" suffix="px|%|inherit" lastsuffix="%"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="12.37" height="14" transform="translate(2, 4)"><use xlink:href="#Origin_X"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,50%,100%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
		--><sr-input onethird class="sr--mr--6">
			<input name="Origin Y" viewchild="layer_hover" r="hov.frame.oY"  replace data-onchange="editor.elements.mouse.reWrap" ignoreredraw livevisup autocomplete="off" number="true" min="-2500" max="2500" suffix="px|%|inherit" lastsuffix="%"  validate="true" type="text">
			<span class="sr--input--icon"><svg width="12.628" height="14" transform="translate(2, 4)"><use xlink:href="#Origin_Y"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,50%,100%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input><!--
		--><sr-input onethird>
			<input name="Origin Z" viewchild="layer_hover"  replace livevisup autocomplete="off" data-onchange="editor.elements.mouse.reWrap" ignoreredraw type="text" r="hov.frame.oZ" number="true" min="-2500" max="2500" suffix="px|%|inherit"lastsuffix="%" validate="true" type="text" livevisup>
			<span class="sr--input--icon"><svg width="12.348" height="14" transform="translate(2, 4)"><use xlink:href="#Origin_Z"></use></svg></span>
			<sr-drop class="sr--drop--only--icon" list="inherit,0,50%,100%" tr="sibling" dropsw="92" dropsh="200" data-pver="bottom" data-phor="rightmatch">
				<svg style="display:inline-block" class="sr--icon" width="3px" height="13px" transform="translate(0, 0)"><use xlink:href="#Top_Bar_More"></use></svg>
			</sr-drop>
		</sr-input>
		<sr-wrap wide basic="" class="sr--form--grp"><sr-onoff r="hov.m" viewchild="layer_hover" data-onchange="editor.elements.mouse.reWrap" ignoreredraw class="sr--mr--10"></sr-onoff><span><?php _e('Animation Under Mask','revslider'); ?></span></sr-wrap>
		<sr-sp h="15"></sr-sp>
		</sr-separator-body>
	</sr-separator>
	<!-- Hover Effect (addon: transitionpack) injects its panel here — a transition preset that plays on hover. Image/video layers only. -->
	<sr-extendhere id="revealhover"></sr-extendhere>
</sr-wrap>
