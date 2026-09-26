<?php

$inventory = isset($this) && isset($this->inventory) ? $this->inventory : new CampManagerInventory();
$table = new CampManagerToteInventoryTable(null, $inventory);
$table->process_bulk_action();
$table->prepare_items();

$screen_id = 'camp-manager-tote-inventory';
$filters = $table->filters();
$overview = $inventory->toteInventoryOverview($filters['tote'] !== '' ? (int) $filters['tote'] : null);
$stats = [
    ['Items packed', number_format($overview['items']), 'items'],
    ['Pieces', number_format($overview['quantity']), 'quantity'],
    ['Item weight', number_format($overview['weight'], 1) . ' lbs', 'weight'],
    ['Totes in use', number_format($overview['totes']), 'totes'],
];
?>
<style>
    .cm-tote-inventory-page .column-inventory_name { width: 34%; }
    .cm-tote-inventory-page .column-tote_name { width: 24%; }
</style>
<div class="wrap cm-list-page cm-tote-inventory-page">
    <?php CampManagerInventory::renderPageHeader('tote_inventory', admin_url('admin.php?page=camp-manager-add-tote-inventory')); ?>

    <?php CampManagerPostbox::boot($screen_id); ?>
    <?php CampManagerPostbox::open('tote-inventory-overview', $filters['tote'] !== '' ? 'This tote' : 'Packed overview', $screen_id, ['class' => 'tote-inventory-overview']); ?>
        <div class="cm-stats">
            <?php foreach ($stats as [$label, $value, $key]): ?>
                <div class="cm-stat" data-stat="<?php echo esc_attr($key); ?>">
                    <span class="cm-stat__label"><?php echo esc_html($label); ?></span>
                    <span class="cm-stat__value"><?php echo esc_html($value); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="cm-stat-footer">
            Item weight is each item's weight times the pieces packed; a tote's own weighed weight is on the Totes tab.
        </p>
    <?php CampManagerPostbox::close(); ?>

    <form method="get" id="<?php echo esc_attr(CampManagerToteInventoryTable::FILTER_FORM); ?>">
        <?php $table->filterFormFields(); ?>
        <?php $table->search_box('Search Tote Inventory', 'tote-inventory-search'); ?>
    </form>

    <form method="post">
        <?php $table->display(); ?>
    </form>
</div>
