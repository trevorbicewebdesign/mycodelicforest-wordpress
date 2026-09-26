<?php

$inventory = isset($this) && isset($this->inventory) ? $this->inventory : new CampManagerInventory();
$table = new CampManagerInventoryTable($inventory);
$table->process_bulk_action();
$table->prepare_items();
?>
<style>
    .cm-items-page .column-name { width: 32%; }
    .cm-items-page .column-category { width: 14%; }
    .cm-items-page .column-location { width: 14%; }
    .cm-items-page .column-links { width: 110px; }
</style>
<div class="wrap cm-list-page cm-items-page">
    <?php CampManagerInventory::renderPageHeader('items', admin_url('admin.php?page=camp-manager-add-inventory')); ?>

    <form method="get" id="<?php echo esc_attr(CampManagerInventoryTable::FILTER_FORM); ?>">
        <?php $table->filterFormFields(); ?>
        <?php $table->search_box('Search Inventory', 'inventory-search'); ?>
    </form>

    <form method="post">
        <?php $table->display(); ?>
    </form>
</div>
