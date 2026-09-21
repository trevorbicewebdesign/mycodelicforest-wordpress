<?php
/**
 * The five device rows of a navigation element's Visibility drop. Sits inside the caller's own <sr-drop>,
 * whose r= and sizes differ per element.
 *
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */

 if(!defined('ABSPATH')) exit();
?>
                        <sr-drop-view>
                            <span class="sr--drop--value"></span>    
                            <span class="sr--form--otitle"><?php _e('Visibility','revslider'); ?></span>
                            <span class="sr--drop--icon"><svg width="10" height="6" transform="translate(0, -1)"><use xlink:href="#Drop_Down"></use></svg></span>
                        </sr-drop-view>     
                        <sr-drops valuelisting data-v="0"><sr-wrap dropicon=""><svg class="sr--icon" width="24" height="14" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Wide_Desktop"></use></svg></sr-wrap><?php _e('Wide Screen','revslider'); ?></sr-drops>
                        <sr-drops valuelisting data-v="1"><sr-wrap dropicon=""><svg class="sr--icon" width="22" height="18" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Regular_Desktop"></use></svg></sr-wrap><?php _e('Desktop','revslider'); ?></sr-drops>
                        <sr-drops valuelisting data-v="2"><sr-wrap dropicon=""><svg class="sr--icon" width="22" height="16" transform="translate(0, -1)"><use xlink:href="#Top_Bar_Laptop"></use></svg></sr-wrap><?php _e('Notebook','revslider'); ?></sr-drops>
                        <sr-drops valuelisting data-v="3"><sr-wrap dropicon=""><svg class="sr--icon" width="20" height="24" transform="translate(0, 0)"><use xlink:href="#Top_Bar_Tablet"></use></svg></sr-wrap><?php _e('Tablet','revslider'); ?></sr-drops>
                        <sr-drops valuelisting data-v="4"><sr-wrap dropicon=""><svg class="sr--icon" width="14" height="20" transform="translate(0, 0)"><use xlink:href="#Top_Bar_Phone"></use></svg></sr-wrap><?php _e('Mobile','revslider'); ?></sr-drops>
