<?php
/**
 * @author    ThemePunch <info@themepunch.com>
 * @link      https://www.themepunch.com/
 * @copyright 2024 ThemePunch
 */

if(!defined('ABSPATH')) exit();
?>
<!-------------------------------------
	EMBED MODULE SHORTCODES POPUP
--------------------------------------->
<sr-popup id="sr_embed_code">
	<sr-popup-header class="sr--text--center">    
		<sr-popup-close><svg class="sr--icon" width="10" height="10"><use xlink:href="#General_Close"></use></svg></svg></sr-popup-close>
	</sr-popup-header>
	<sr-sp h="15"></sr-sp>
	<sr-popup-content class="sr--text--left">
		<sr-wrap>
			<sr-sp h="20"></sr-sp>
			<h2 class="sr--popup--big--title"><?php echo __('Standard Module Embedding','revslider');?></h2>
			<sr-sp h="15"></sr-sp>
			<span class="sr--text"><?php echo __('To embed in the <b>pages and posts</b> editor, insert the shortcode:','revslider');?></span>    
			<sr-sp h="10"></sr-sp>
			<sr-clipboard class="sr--text--code"><span>[sr7 alias="###"][/sr7]</span><svg data-action="B.clipBoard.copy" data-aparams="[sr7 alias=&quot;###&quot;][/sr7]" class="sr--icon" width="12" height="15" transform="translate(0, 0)"><use xlink:href="#Dashboard_Duplicate"></use></svg></sr-clipboard>
			<sr-sp h="15"></sr-sp>
			<span class="sr--text"><?php echo __('To add as a <b>modal</b> in the <b>pages and posts</b> editor insert the shortcode:','revslider');?></span>    
			<sr-sp h="10"></sr-sp>
			<sr-clipboard class="sr--text--code"><span>[sr7 usage=&quot;modal&quot; alias=&quot;###&quot;][/sr7]</span><svg data-action="B.clipBoard.copy" data-aparams="[sr7 usage=&quot;modal&quot; alias=&quot;###&quot;][/sr7]" class="sr--icon" width="12" height="15" transform="translate(0, 0)"><use xlink:href="#Dashboard_Duplicate"></use></svg></sr-clipboard>
			<sr-sp h="15"></sr-sp>
			<span class="sr--text"><?php echo __('To add via widgets, drag the "Revolution Module" widget from the <b>widgets panel</b> to the desired sidebar.','revslider');?></span>    
			<sr-sp h="80"></sr-sp>    
			<h2 class="sr--popup--big--title"><?php echo __('Advanced Module Embedding','revslider');?></h2>
			<sr-sp h="15"></sr-sp>
			<span class="sr--text"><?php echo __('To add to theme HTML, use:','revslider');?></span>    
			<sr-sp h="10"></sr-sp>
			<sr-clipboard class="sr--text--code"><span>&lt;?php add _revslider (&quot;###&quot;); ?&gt;</span><svg data-action="B.clipBoard.copy" data-aparams="&lt;?php add_revslider(&quot;###&quot;);?&gt;" class="sr--icon" width="12" height="15" transform="translate(0, 0)"><use xlink:href="#Dashboard_Duplicate"></use></svg></sr-clipboard>
			<sr-sp h="15"></sr-sp>
			<span class="sr--text"><?php echo __('To add only to the homepage, use:','revslider');?></span>    
			<sr-sp h="10"></sr-sp>
			<sr-clipboard class="sr--text--code"><span>&lt;?php add _revslider (&quot;###&quot;, &quot;homepage&quot;); ?&gt;</span><svg data-action="B.clipBoard.copy" data-aparams="&lt;?php add_revslider(&quot;###&quot;,&quot;homepage&quot;);?&gt;" class="sr--icon" width="12" height="15" transform="translate(0, 0)"><use xlink:href="#Dashboard_Duplicate"></use></svg></sr-clipboard>
			<sr-sp h="15"></sr-sp>
			<span class="sr--text"><?php echo __('To add only to single pages, use:','revslider');?></span>
			<sr-sp h="10"></sr-sp>
			<sr-clipboard class="sr--text--code"><span>&lt;?php add _revslider (&quot;###&quot;, &quot;2,10&quot;); ?&gt;</span><svg data-action="B.clipBoard.copy" data-aparams="&lt;?php add_revslider(&quot;###&quot;,&quot;2,10&quot;);?&gt;" class="sr--icon" width="12" height="15" transform="translate(0, 0)"><use xlink:href="#Dashboard_Duplicate"></use></svg></sr-clipboard>
			<sr-sp h="25"></sr-sp>
		</sr-wrap>
		<sr-sp h="20"></sr-sp>
	</sr-popup-content>
</sr-popup>