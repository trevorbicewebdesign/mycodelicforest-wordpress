<?php

$ledger = isset($this) && isset($this->ledger) ? $this->ledger : CampManagerLedgerTable::defaultLedger();
$table = new CampManagerLedgerTable($ledger);
$table->process_bulk_action();
$table->prepare_items();

$screen_id = 'camp-manager-ledger';
$filters = $table->filters();
$overview = $ledger->overview();
$money = [CampManagerDashboard::class, 'money'];
$viewing_all = CampManagerSeason::viewingAll();
$stats = [
    ['Starting balance', $money($overview['starting_balance']), 'starting'],
    ['Money in', $money($overview['money_in']), 'in'],
    ['Money out', $money($overview['money_out']), 'out'],
    ['Net', $money($overview['net']), 'net'],
    [$viewing_all ? 'Balance now' : 'Ending balance', $money($overview['ending_balance']), 'ending'],
];
CampManagerListTable::styles();
?>
<style>
    .ledger-table .column-date { width: 120px; }
    .ledger-table .column-note { width: 34%; }
    .ledger-table .column-amount { width: 120px; text-align: right; }
    .ledger-table td.column-amount { padding-right: 24px; }
    .ledger-table .column-receipts { width: 14%; }
    .ledger-table .column-link { width: 90px; }
    .ledger-table .column-season { width: 80px; }
    .ledger-table .cm-amount--out { color: #d63638; }
    .ledger-table .cm-amount--zero { color: #787c82; }
    .ledger-table .cm-flag { color: #996800; }
    .ledger-table .cm-flag .dashicons { font-size: 16px; width: 16px; height: 16px; vertical-align: text-bottom; }
    .ledger-table tr.is-attention td.column-date,
    .ledger-table tr.is-attention th.column-note { box-shadow: inset 4px 0 0 #dba617; }
    .cm-stat--negative .cm-stat__value { color: #d63638; }
</style>
<div class="wrap cm-list-page ledger-page">
    <h1 class="wp-heading-inline">Ledger</h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=camp-manager-add-ledger')); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <?php CampManagerSeason::renderSwitcher(true); ?>

    <?php CampManagerPostbox::boot($screen_id); ?>
    <?php CampManagerPostbox::open('ledger-overview', $viewing_all ? 'All seasons' : 'Season overview', $screen_id, ['class' => 'ledger-overview']); ?>
        <div class="cm-stats">
            <?php foreach ($stats as [$label, $value, $key]): ?>
                <div class="cm-stat<?php echo $key === 'net' && $overview['net'] < 0 ? ' cm-stat--negative' : ''; ?>" data-stat="<?php echo esc_attr($key); ?>">
                    <span class="cm-stat__label"><?php echo esc_html($label); ?></span>
                    <span class="cm-stat__value"><?php echo esc_html($value); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="cm-stat-footer">
            Camp dues <?php echo esc_html($money($overview['camp_dues'])); ?>
            &middot; Donations <?php echo esc_html($money($overview['donations'])); ?>
            &middot; Assets sold <?php echo esc_html($money($overview['assets_sold'])); ?>
            &middot; Expenses <?php echo esc_html($money($overview['expenses'])); ?>
            &middot; <?php echo (int) $overview['entries']; ?> <?php echo $overview['entries'] === 1 ? 'entry' : 'entries'; ?>
        </p>
    <?php CampManagerPostbox::close(); ?>

    <?php $table->views(); ?>

    <form method="get" id="<?php echo esc_attr(CampManagerLedgerTable::FILTER_FORM); ?>">
        <?php $table->filterFormFields([CampManagerSeason::VIEW_ALL_PARAM, 'flow']); ?>
        <?php $table->search_box('Search Ledger', 'ledger-search'); ?>
    </form>

    <form method="post">
        <?php $table->display(); ?>
    </form>
</div>
