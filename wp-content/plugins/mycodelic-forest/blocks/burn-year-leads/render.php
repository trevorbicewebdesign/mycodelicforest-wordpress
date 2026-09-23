<?php
/**
 * Burn Year Camp Leads (ACF block). Variables from ACF: $block, $is_preview, $post_id, $context.
 */
$history_post_id = !empty($context['postId']) ? (int) $context['postId'] : (int) $post_id;
$html = get_post_type($history_post_id) !== 'camp_year' ? '' : MycodelicForestHistory::$instance->renderLeads($history_post_id);

if ($html === '' && $is_preview) {
    $html = '<p class="mf-burn-placeholder">Burn Year Camp Leads: shows the current Burn Year&rsquo;s fields.</p>';
}

echo '<div ' . get_block_wrapper_attributes() . '>' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in MycodelicForestHistory.
