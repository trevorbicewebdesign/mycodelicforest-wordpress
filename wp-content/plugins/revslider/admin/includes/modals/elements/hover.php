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

		<!-- Cursor Icon — the twelve values as a grid instead of a list of their names.
		     🔴 A CSS cursor cannot be read back out: the browser hands `cursor:` to the OS and gets no picture
		     in return, and the drawing differs per OS and theme. So the glyph is DRAWN (sprite.svg, Cursor_*),
		     and each cell also carries its own `cursor:` value — hovering a cell shows the real system cursor,
		     which is the one preview no artwork can give.
		     sr-radio is the form system's own picker, so binding, populate and undo come with it; `cursor` is
		     SR7's own default and resolves to no CSS at all, hence "Inherit" next to the real `default` arrow. -->
		<sr-wrap class="sr--cursorgrid--wrap">
			<sr-radio r="cursor" viewchild="layer_hover" class="sr--cursorgrid" data-onset="editor.elements.ix.cursorSync">
				<sr-radio-item icon value="cursor"    title="<?php _e('Inherit','revslider'); ?>"   style="cursor:auto"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Inherit"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="auto"      title="<?php _e('Auto','revslider'); ?>"      style="cursor:auto"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Auto"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="default"   title="<?php _e('Arrow','revslider'); ?>"     style="cursor:default"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Default"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="pointer"   title="<?php _e('Pointer','revslider'); ?>"   style="cursor:pointer"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Pointer"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="text"      title="<?php _e('Text','revslider'); ?>"      style="cursor:text"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Text"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="move"      title="<?php _e('Move','revslider'); ?>"      style="cursor:move"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Move"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="crosshair" title="<?php _e('Crosshair','revslider'); ?>" style="cursor:crosshair"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Crosshair"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="help"      title="<?php _e('Help','revslider'); ?>"      style="cursor:help"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Help"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="wait"      title="<?php _e('Wait','revslider'); ?>"      style="cursor:wait"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_Wait"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="zoom-in"   title="<?php _e('Zoom In','revslider'); ?>"   style="cursor:zoom-in"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_ZoomIn"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="zoom-out"  title="<?php _e('Zoom Out','revslider'); ?>"  style="cursor:zoom-out"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_ZoomOut"></use></svg></sr-radio-item><!--
			--><sr-radio-item icon value="none"      title="<?php _e('Hidden','revslider'); ?>"    style="cursor:none"><svg class="sr--icon" width="16" height="16"><use xlink:href="#Cursor_None"></use></svg></sr-radio-item>
			</sr-radio>
		</sr-wrap>

		<!-- Cursor Motion — the catalogue as tiles instead of the dropdown that stood here. Eight modes whose
		     difference a word cannot carry (a magnet and a lean travel the same direction and feel nothing
		     alike), and this is the one effect whose tile needs no simulated hand: a tile is only ever looked
		     at while the pointer is inside it, and that pointer IS the input. Catalogue + tile recipe live in
		     admin/assets/js/pointer.presets.js, so the Gutenberg Page Effect can read the same list.
		     Paged behind a dot bar (owner opt-in, see essentials.js), so it stands permanently open at a fixed
		     two rows — and it needs no heading of its own to say what a grid of cursors is about. -->
		<sr-wrap id="sr_ix_essentials"></sr-wrap>

		<!-- …and its numbers directly underneath, inside the same block rather than in a section of their own
		     further down the panel. They are what the tile just wrote; a heading and a screen of tiles between
		     the two made them read as an unrelated Advanced area. -->
		<sr-sh r="ix.type" viewchild="layer_hover" data-shdep="magnetic#;#attract#;#repel#;#squeeze#;#pop#;#stretch#;#tilt#;#rotate">
			<sr-input half class="sr--mr--10">
				<input name="Strength" viewchild="layer_hover" r="ix.strength" ignoreredraw replace data-onset="editor.elements.ix.ensureBrowser" data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="0" max="150" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Strength','revslider'); ?></span>
			</sr-input><!--
		--><sr-input half>
				<input name="Radius" viewchild="layer_hover" r="ix.radius" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="20" max="800" suffix="px" lastsuffix="px" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Radius','revslider'); ?></span>
			</sr-input>

			<!-- …and next to Speed the Return ease: how it springs back when the cursor leaves the field -->
			<sr-input half class="sr--mr--10">
				<input name="Speed" viewchild="layer_hover" r="ix.speed" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="2" max="40" validate="true" type="text">
				<span noicon class="sr--form--otitle"><?php _e('Speed','revslider'); ?></span>
			</sr-input><!--
		--><sr-drop half r="ix.returnEase" viewchild="layer_hover" ignoreredraw data-v="smooth" data-defval="smooth"
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

			<!-- Axis lock and Perspective are mutually exclusive (move modes vs tilt), so at most one of the two
			     ever occupies this row -->
			<sr-sh r="ix.type" viewchild="layer_hover" data-shdep="<?php echo $dep_translate; ?>">
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
			</sr-sh>

			<sr-sh r="ix.type" viewchild="layer_hover" data-shdep="tilt">
				<sr-input wide class="sr--mb--0">
					<input name="Perspective" viewchild="layer_hover" r="ix.perspective" ignoreredraw replace data-onchange="editor.elements.ix.update" data-undoredo="editor.elements.ix.update" livevisup autocomplete="off" dragnumber number="true" min="200" max="3000" suffix="px" lastsuffix="px" validate="true" type="text">
					<span noicon class="sr--form--otitle"><?php _e('Perspective','revslider'); ?></span>
				</sr-input>
			</sr-sh>
			<sr-sp h="15"></sr-sp>
		</sr-sh>

		<sr-sp h="5"></sr-sp>
	</sr-separator-body>
</sr-separator>

<!-- Hover Style & Transform — its own block with its own switch, the way Interaction has one.
     ⚠ `hov.u` used to be a THREE-value drop (false / true / desktop). "desktop" meant "on, but not on
     mobile", which is a second decision wearing the first one's clothes — and the engine never read it:
     _tpt.tf("desktop") hands back the string, which is truthy, so it was on everywhere. It is two flags now,
     and SR7.D.layerObject rewrites the old value on the way through (public/js/defaults.js). -->
<sr-separator topborder keepborder class="sr_elements_pointere">
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Hover Style & Transform','revslider'); ?></sr-separator-title>
		<sr-onoff r="hov.u" viewchild="layer_hover" default="false" class="sr--mr--0" style="right:0px" data-sh=".sr_elements_hovanims"
				  data-onchange="editor.elements.mouse.reset" data-onchangeparams="enableddisabled"></sr-onoff>
	</sr-separator-head>
</sr-separator>

<!-- Conditional settings — only rendered once a Style & Transform mode is chosen. The Cursor Motion numbers
     are NOT here any more: they belong to the tile that wrote them and sit directly under it, above. -->
<sr-wrap class="sr_elements_pointere">

	<!-- Style & Transform settings -->
	<sr-separator class="sr_elements_hovanims" data-menter="editor.elements.mouse.hover" data-mleave="editor.elements.mouse.idle" noborder>
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
		</sr-separator-body>
	</sr-separator>

	<sr-separator class="sr_elements_hovanims" data-menter="editor.elements.mouse.hover" data-mleave="editor.elements.mouse.idle" noborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Filters','revslider'); ?></sr-separator-title>
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

		</sr-separator-body>
	</sr-separator>
	<sr-separator class="sr_elements_hovanims" data-menter="editor.elements.mouse.hover" data-mleave="editor.elements.mouse.idle" noborder>
	<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Animation','revslider'); ?></sr-separator-title>
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
		<!-- Last, and deliberately so: this is the old "Disabled on Mobile" third value of hov.u, which the
		     engine never actually read (see the note at the top). It works now, but it is the least-reached-for
		     switch in the block, so it sits where the least-reached-for switch belongs. -->
		<sr-wrap wide basic="" class="sr--form--grp"><sr-onoff r="hov.dMo" viewchild="layer_hover" default="false" class="sr--mr--10"></sr-onoff><span><?php _e('Disable on Mobile','revslider'); ?></span></sr-wrap>
		<sr-sp h="15"></sr-sp>
		</sr-separator-body>
	</sr-separator>
	<!-- Hover Effect (addon: transitionpack) injects its panel here — a transition preset that plays on hover. Image/video layers only. -->
	<sr-extendhere id="revealhover"></sr-extendhere>
</sr-wrap>
