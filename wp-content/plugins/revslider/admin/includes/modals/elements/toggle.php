<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>

<sr-wrap-dep dep="is[text,button]">
	<sr-separator keepborder>
	<sr-separator-head notoggle>
			<sr-separator-title><?php _e('Toggled Text','revslider'); ?></sr-separator-title>
			<sr-onoff class="sr--mr--0" style="right:0px"  data-sh=".sr_elements_txttoggle" r="tog.u" data-action="editor.elements.text.invert" viewchild="layer_hover"></sr-onoff>			
		</sr-separator-head>
		<sr-separator-body class="sr_elements_txttoggle">				
				<sr-wrap class="sr_layer_text_tog_settings">
					<sr-sp h="10"></sr-sp>
					<sr-input wide textblock><textarea name="Toggled Text" layercontent r="tog.t" keydown="editor.elements.text.register" keyup="editor.elements.text.update" data-undoredo="editor.elements.text.redrawAll" data-onchange="editor.elements.text.redrawAll" viewchild="layer_hover"></textarea></sr-input>
				</sr-wrap>
				<sr-sp h="5"></sr-sp>
		</sr-separator-body>
	</sr-separator>
</sr-wrap-dep>
