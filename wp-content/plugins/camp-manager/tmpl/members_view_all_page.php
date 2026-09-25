<?php

$table = new CampManagerRosterTable($this->ledger);
$table->process_bulk_action();
$table->prepare_items();

$overview = $this->roster->seasonOverview($this->ledger);
$money = static function (float $amount): string {
    return '$' . number_format($amount, 2);
};

$screen_id = 'camp-manager-roster';

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
    .roster-table .column-cb { width: 2.2em; }
    .roster-table .column-camp_dues { width: 110px; text-align: right; }
    .roster-table td.column-camp_dues { padding-right: 24px; }
    .roster-table .column-dues_category { width: 130px; }
    .roster-table .column-payment { width: 110px; }
    .roster-table .column-status { width: 110px; }
    .roster-table .roster-row td,
    .roster-table .roster-row th.check-column { vertical-align: middle; }
    /* Row actions (Edit / View) keep their line whether shown or not, so hovering never changes a row's height. */
    .roster-table .row-actions { position: relative; }
    .roster-table tr:not(:hover):not(:focus-within) .row-actions { left: -9999em; }
    .roster-table tr:hover .row-actions,
    .roster-table tr:focus-within .row-actions { left: 0; }
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

    <?php CampManagerPostbox::boot($screen_id); ?>
    <?php CampManagerPostbox::open('roster-overview', 'Season overview', $screen_id, ['class' => 'roster-overview']); ?>
        <div class="cm-stats">
            <?php foreach ($stats as [$label, $value, $key]): ?>
                <div class="cm-stat" data-stat="<?php echo esc_attr($key); ?>">
                    <span class="cm-stat__label"><?php echo esc_html($label); ?></span>
                    <span class="cm-stat__value"><?php echo esc_html($value); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="cm-stat-footer">
            Low-income members: <?php echo (int) $overview['low_income']; ?>
            &middot;
            Low-income dues paid: <?php echo (int) $overview['low_income_dues_paid']; ?>
        </p>
    <?php CampManagerPostbox::close(); ?>

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
