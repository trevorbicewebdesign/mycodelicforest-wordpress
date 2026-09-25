<?php

$table = new CampManagerRosterTable($this->ledger);
$table->process_bulk_action();
$table->prepare_items();

$overview = $this->roster->seasonOverview($this->ledger);
$money = static function (float $amount): string {
    return '$' . number_format($amount, 2);
};

// The overview box is a regular WordPress postbox, so core's postbox script handles the
// collapse toggle and remembers (per user) whether it was left closed.
$screen_id = 'camp-manager-roster';
$overview_id = 'roster-overview';
$closed_boxes = get_user_option('closedpostboxes_' . $screen_id);
$overview_closed = is_array($closed_boxes) && in_array($overview_id, $closed_boxes, true);
wp_enqueue_script('postbox');
wp_add_inline_script('postbox', 'jQuery(function () { postboxes.add_postbox_toggles(' . wp_json_encode($screen_id) . '); });');

$filters = $table->filters();
$stats = [
    ['Total members', number_format($overview['total']), 'total'],
    ['Confirmed', number_format($overview['confirmed']), 'confirmed'],
    ['Unpaid members', number_format($overview['unpaid']), 'unpaid'],
    ['Dues collected', $money($overview['dues_collected']), 'collected'],
    ['Expected dues', $money($overview['dues_expected']), 'expected'],
];
?>
<style>
    .roster-overview { margin-top: 0; }
    .roster-overview .postbox-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #c3c4c7; }
    .roster-overview .hndle { flex: 1; margin: 0; padding: 12px 16px; font-size: 15px; line-height: 1.4; }
    .roster-overview.closed .postbox-header { border-bottom: 0; }
    /* Core draws the collapse arrow only inside .meta-box-sortables, which would also make the box draggable. */
    .roster-overview .toggle-indicator::before { content: "\f142"; display: inline-block; font: normal 20px/1 dashicons; -webkit-font-smoothing: antialiased; }
    .roster-overview.closed .toggle-indicator::before { content: "\f140"; }
    .roster-overview .handlediv { width: 36px; height: 36px; }
    .roster-overview .inside { margin: 0; padding: 0; }
    .roster-overview__stats { display: flex; flex-wrap: wrap; }
    .roster-overview__stat { flex: 1 1 160px; padding: 14px 16px 12px; border-left: 1px solid #dcdcde; }
    .roster-overview__stat:first-child { border-left: 0; }
    .roster-overview__label { display: block; color: #50575e; margin-bottom: 4px; }
    .roster-overview__value { display: block; font-size: 24px; line-height: 1.2; font-weight: 600; color: #1d2327; }
    .roster-overview__footer { margin: 0; padding: 10px 16px 14px; color: #50575e; }

    .roster-table .column-cb { width: 2.2em; }
    .roster-table .column-camp_dues { width: 110px; text-align: right; }
    .roster-table td.column-camp_dues { padding-right: 24px; }
    .roster-table .column-dues_category { width: 130px; }
    .roster-table .column-payment { width: 110px; }
    .roster-table .column-status { width: 110px; }
    .roster-table .roster-row td,
    .roster-table .roster-row th.check-column { vertical-align: middle; }
    /* Row actions only take up room while the row is hovered or focused. */
    .roster-table tr:not(:hover):not(:focus-within) .row-actions { position: absolute; }
    .roster-table .is-dropped td { color: #787c82; }
    .roster-table .is-dropped .row-title { color: #787c82; }
    .roster-table .roster-empty { color: #787c82; }

    .roster-payment { display: inline-flex; align-items: center; gap: 4px; min-height: 20px; }
    .roster-payment--paid .dashicons { color: #00a32a; width: 20px; height: 20px; font-size: 20px; }
    .roster-payment__ring { box-sizing: border-box; width: 16px; height: 16px; margin: 0 2px; border: 2px solid #d63638; border-radius: 50%; }

    .roster-page .subsubsub { margin-bottom: 0; }
</style>
<div class="wrap roster-page">
    <h1 class="wp-heading-inline">Roster</h1>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-member'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <?php CampManagerSeason::renderSwitcher(); ?>

    <?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false); ?>
    <div id="<?php echo esc_attr($overview_id); ?>" class="postbox roster-overview<?php echo $overview_closed ? ' closed' : ''; ?>">
        <div class="postbox-header">
            <h2 class="hndle is-non-sortable">Season overview</h2>
            <div class="handle-actions hide-if-no-js">
                <button type="button" class="handlediv" aria-expanded="<?php echo $overview_closed ? 'false' : 'true'; ?>">
                    <span class="screen-reader-text">Toggle panel: Season overview</span>
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
        </div>
        <div class="inside">
            <div class="roster-overview__stats">
                <?php foreach ($stats as [$label, $value, $key]): ?>
                    <div class="roster-overview__stat" data-stat="<?php echo esc_attr($key); ?>">
                        <span class="roster-overview__label"><?php echo esc_html($label); ?></span>
                        <span class="roster-overview__value"><?php echo esc_html($value); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="roster-overview__footer">
                Low-income members: <?php echo (int) $overview['low_income']; ?>
                &middot;
                Low-income dues paid: <?php echo (int) $overview['low_income_dues_paid']; ?>
            </p>
        </div>
    </div>

    <?php $table->views(); ?>

    <form method="get" id="<?php echo esc_attr(CampManagerRosterTable::FILTER_FORM); ?>">
        <input type="hidden" name="page" value="<?php echo esc_attr(sanitize_key($_GET['page'] ?? 'camp-manager-members')); ?>">
        <?php if ($filters['member_status']): ?>
            <input type="hidden" name="member_status" value="<?php echo esc_attr($filters['member_status']); ?>">
        <?php endif; ?>
        <?php $table->search_box('Search Members', 'roster-search'); ?>
    </form>

    <form method="post">
        <?php
        $table->display();
        ?>
    </form>
</div>
