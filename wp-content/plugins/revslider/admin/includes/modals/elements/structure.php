<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>

<sr-wrap-dep dep="is[group,column]">
	<sr-separator keepborder>
		<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Content Behavior','revslider'); ?></sr-separator-title>			
		</sr-separator-head>
		<sr-separator-body>		
				<sr-wrap half basic inline>
					<sr-radio viewchild="layer_basics" responsive r="tA.#LEV#">
							<sr-radio-item value="left" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Left"></use></svg></sr-radio-item><!--
						--><sr-radio-item value="center" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Center"></use></svg></sr-radio-item><!--
						--><sr-radio-item value="right" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Align_Right"></use></svg></sr-radio-item><!--
						--><sr-radio-item value="justify" icon=""><svg class="sr--icon" width="18" height="14" transform="translate(0, -2)"><use xlink:href="#Toolbar_Text_Justify"></use></svg></sr-radio-item>
					</sr-radio>
				</sr-wrap><!--
				--><sr-wrap half basic inline>
					<sr-radio viewchild="layer_basics" r="vA">
							<sr-radio-item value="top" icon=""><svg class="sr--icon" width="12" height="15.98" transform="translate(0, -2)"><use xlink:href="#Toolbar_Align_Top"></use></svg></sr-radio-item><!--
						--><sr-radio-item value="middle" icon=""><svg class="sr--icon" width="10" height="12" transform="translate(0, -2)"><use xlink:href="#Toolbar_Align_Middle"></use></svg></sr-radio-item><!--
						--><sr-radio-item value="bottom" icon=""><svg class="sr--icon" width="12" height="16" transform="translate(0, -2)"><use xlink:href="#Toolbar_Align_Bottom"></use></svg></sr-radio-item>
					</sr-radio>
				</sr-wrap>
				<sr-sp h="12"></sr-sp>		
		</sr-separator-body>
	</sr-separator>
</sr-wrap-dep>
<sr-separator keepborder>
	<sr-separator-head notoggle>
		<sr-separator-title><?php _e('Container Structure','revslider'); ?></sr-separator-title>		
	</sr-separator-head>
	<sr-separator-body>

		<sr-fieldset viewchild="layer_basics" id="fset_columns"  data-source="editor.elements.columns.fieldset" class="sr--mb--0"></sr-fieldset>		
		<sr-wrap class="sr--mt--20">
			<sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/1" wide></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/2" half></sr-wrap><sr-wrap size="1/2" half></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/3" onethird></sr-wrap><sr-wrap size="1/3" onethird></sr-wrap><sr-wrap size="1/3" onethird></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure"><sr-wrap size="1/4" fourth></sr-wrap><sr-wrap size="1/4" fourth></sr-wrap><sr-wrap size="1/4" fourth></sr-wrap><sr-wrap size="1/4" fourth></sr-wrap></sr-wrap>
		</sr-wrap>
		<sr-wrap class="sr--mt--10">
			<sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/3" onethird></sr-wrap><sr-wrap size="2/3" twothird></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/4" fourth></sr-wrap><sr-wrap size="3/4" threefourth></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="3/4" threefourth></sr-wrap><sr-wrap size="1/4" fourth></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap twothird></sr-wrap><sr-wrap onethird></sr-wrap></sr-wrap>
		</sr-wrap>
		<sr-wrap class="sr--mt--10">
			<sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/4" fourth></sr-wrap><sr-wrap size="1/2" half></sr-wrap><sr-wrap size="1/4" fourth></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/4" fourth></sr-wrap><sr-wrap size="1/4" fourth></sr-wrap><sr-wrap size="1/2" half></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/2" half></sr-wrap><sr-wrap size="1/4" fourth></sr-wrap><sr-wrap size="1/4" fourth></sr-wrap></sr-wrap><!--
			--><sr-wrap data-action="editor.elements.columns.structure" onefourth class="sr--col--structure sr--mr--6"><sr-wrap size="1/5" fifth></sr-wrap><sr-wrap size="1/5" fifth></sr-wrap><sr-wrap size="1/5" fifth></sr-wrap><sr-wrap size="1/5" fifth></sr-wrap><sr-wrap size="1/5" fifth></sr-wrap></sr-wrap>
		</sr-wrap>
		<sr-sp h="15"></sr-sp>
		<sr-fieldset viewchild="layer_basics" id="fset_rowgaps"  data-source="editor.elements.rows.useGaps" class="sr--mb--0"></sr-fieldset>
		<sr-wrap half>
			<sr-wrap wide class="sr--mini--title"><?php _e('Break Down At','revslider'); ?></sr-wrap>
			<sr-fieldset viewchild="layer_basics" id="fset_rowbreak"  data-source="editor.elements.rows.fieldset" data-sourceparams="break"></sr-fieldset>
		</sr-wrap>
		<sr-wrap half>
			<sr-wrap wide class="sr--mini--title"><?php _e('Row Position','revslider'); ?></sr-wrap>
			<sr-fieldset viewchild="layer_basics" id="fset_rowbreak"  data-source="editor.elements.rows.fieldset" data-sourceparams="pid"></sr-fieldset>
		</sr-wrap>
		<sr-sp h="20"></sr-sp>
	</sr-separator-body>		
</sr-separator>
