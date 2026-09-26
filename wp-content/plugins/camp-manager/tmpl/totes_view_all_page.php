<?php

$inventory = isset($this) && isset($this->inventory) ? $this->inventory : new CampManagerInventory();
$table = new CampManagerTotesTable($inventory);
$table->process_bulk_action();
$table->prepare_items();

$screen_id = 'camp-manager-totes';
$overview = $inventory->totesOverview();
$lbs = static function (float $weight): string {
    return number_format($weight, 1) . ' lbs';
};
$stats = [
    ['Totes', number_format($overview['total']), 'total'],
    ['Packed', number_format($overview['packed']), 'packed'],
    ['Ready', number_format($overview['ready']), 'ready'],
    ['Packed weight', $lbs($overview['packed_weight']), 'packed_weight'],
    ['On Sojourner', $lbs($overview['sojourner_weight']), 'sojourner_weight'],
];
?>
<style>
    .cm-totes-page .column-name { width: 30%; }
    .cm-totes-page .column-size { width: 90px; }
    .cm-totes-page .column-status { width: 110px; }
    .cm-totes-page .column-items { width: 120px; }
</style>
<div class="wrap cm-list-page cm-totes-page">
    <?php CampManagerInventory::renderPageHeader('totes', admin_url('admin.php?page=camp-manager-add-tote')); ?>

    <?php CampManagerPostbox::boot($screen_id); ?>
    <?php CampManagerPostbox::open('totes-overview', 'Totes overview', $screen_id, ['class' => 'totes-overview']); ?>
        <div class="cm-stats">
            <?php foreach ($stats as [$label, $value, $key]): ?>
                <div class="cm-stat" data-stat="<?php echo esc_attr($key); ?>">
                    <span class="cm-stat__label"><?php echo esc_html($label); ?></span>
                    <span class="cm-stat__value"><?php echo esc_html($value); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="cm-stat-footer">
            All totes together weigh <?php echo esc_html($lbs($overview['total_weight'])); ?>.
        </p>
    <?php CampManagerPostbox::close(); ?>

    <form method="get" id="<?php echo esc_attr(CampManagerTotesTable::FILTER_FORM); ?>">
        <?php $table->filterFormFields(); ?>
        <?php $table->search_box('Search Totes', 'totes-search'); ?>
    </form>

    <form method="post">
        <?php $table->display(); ?>
    </form>
</div>
