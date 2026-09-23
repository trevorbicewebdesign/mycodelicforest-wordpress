<?php
/**
 * Title: Burn Years archive
 * Slug: mycodelic-forest-child/archive-camp-year
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"className":"swt-block-page-banner-group","style":{"spacing":{"margin":{"top":"0","bottom":"0"}},"background":{"backgroundImage":{"url":"<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/assets/images/screen-shot-2013-08-26-at-1-06-50-am_1.png","id":216,"source":"file","title":"screen-shot-2013-08-26-at-1-06-50-am_1"},"backgroundSize":"cover","backgroundPosition":"50% 27%"},"border":{"top":{"width":"0px","style":"none"},"right":{"width":"0px","style":"none"},"bottom":{"color":"var:preset|color|heading","width":"1px"},"left":{"width":"0px","style":"none"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group swt-block-page-banner-group" style="border-top-style:none;border-top-width:0px;border-right-style:none;border-right-width:0px;border-bottom-color:var(--wp--preset--color--heading);border-bottom-width:1px;border-left-style:none;border-left-width:0px;margin-top:0;margin-bottom:0"><!-- wp:heading {"textAlign":"center","level":1,"className":"swt-block-page-title","style":{"spacing":{"margin":{"top":"var:preset|spacing|x-large","bottom":"var:preset|spacing|large"}},"typography":{"textTransform":"uppercase"}},"textColor":"white"} -->
<h1 class="wp-block-heading has-text-align-center swt-block-page-title has-white-color has-text-color" style="margin-top:var(--wp--preset--spacing--x-large);margin-bottom:var(--wp--preset--spacing--large);text-transform:uppercase">Camp History</h1>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"main","backgroundColor":"background","style":{"spacing":{"padding":{"top":"var:preset|spacing|x-large","bottom":"var:preset|spacing|x-large"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--x-large);padding-bottom:var(--wp--preset--spacing--x-large)"><!-- wp:query {"queryId":21,"query":{"perPage":100,"pages":0,"offset":0,"postType":"camp_year","order":"desc","orderBy":"date","inherit":true},"align":"wide"} -->
<div class="wp-block-query alignwide"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|large"}},"layout":{"type":"grid","columnCount":3,"minimumColumnWidth":"16rem"}} -->
<!-- wp:group {"className":"mf-burn-year-card","style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"stretch"}} -->
<div class="wp-block-group mf-burn-year-card"><!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9"} /-->

<!-- wp:post-title {"level":3,"isLink":true,"style":{"spacing":{"margin":{"top":"0","bottom":"0"}}},"fontSize":"large"} /-->

<!-- wp:mycodelic/burn-year-summary {"name":"mycodelic/burn-year-summary","mode":"preview"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">No years yet.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
