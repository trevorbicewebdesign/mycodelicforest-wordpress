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
			<sr-separator-title><?php _e('Container Line Height','revslider'); ?></sr-separator-title>
		</sr-separator-head>
		<sr-separator-body>
			<sr-input wide>
			<input name="Container Line Height" viewchild="layer_style" r="lh.#LEV#"  livevisup autocomplete="off" responsive respshow="f-320middle" number="true" min="4" max="5000" suffix="px" lastsuffix="px"  validate="true" type="text">
				<span class="sr--input--icon"><svg width="16" height="12.8" transform="translate(0, 2)"><use xlink:href="#Options_Line_Height"></use></svg></span>
			</sr-input>	
			<sr-sp h="5"></sr-sp>			
		</sr-separator-body>
	</sr-separator>
</sr-wrap-dep>
