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
		<sr-separator-title><?php _e('Text Shadow','revslider'); ?></sr-separator-title>
		<sr-onoff class="sr--mr--0" style="right:0px"  data-sh=".sr_elements_tShdw" r="tShdw.use" data-onchange="editor.elements.shadows.update" viewchild="layer_extra"></sr-onoff>		
	</sr-separator-head>
	<sr-separator-body class="sr_elements_tShdw">
		<sr-fieldset viewchild="layer_extra" id="fset_tshadows"  data-source="editor.elements.shadows.fieldset" data-sourceparams="tShdw" class="sr--mb--0"></sr-fieldset>
		<sr-sp h="5"></sr-sp>			
	</sr-separator-body>
</sr-separator>
