<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 *
 * Where a held module sits while the scroll drives it. Shared by Scroll Based Timeline and Story Telling: a story
 * shows no SBT tab at all, so without its own copy of this row a story that is not full height had no way to say
 * whether it sticks to the top, rests on the bottom, or travels with the page.
 */

if(!defined('ABSPATH')) exit();
?>
<sr-drop wide r="sbt.a" viewchild="module_sbt">
    <sr-drop-view>
        <span class="sr--drop--value"></span>
        <span class="sr--form--otitle"><?php _e('Vertical Align','revslider'); ?></span>
        <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
    </sr-drop-view>
    <sr-drops data-v="top"><?php _e('Top','revslider'); ?></sr-drops>
    <sr-drops data-v="bottom"><?php _e('Bottom','revslider'); ?></sr-drops>
    <sr-drops data-v="travel"><?php _e('Travel','revslider'); ?></sr-drops>
</sr-drop>
