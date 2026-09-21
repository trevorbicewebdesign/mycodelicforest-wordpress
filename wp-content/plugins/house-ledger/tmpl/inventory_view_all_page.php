<?php

$HouseLedgerCore = new HouseLedgerCore();
$HouseLedgerInventory = new HouseLedgerInventory($HouseLedgerCore);
$table = new HouseLedgerInventoryTable($HouseLedgerInventory);
$table->process_bulk_action();
$table->prepare_items();
?>
<style>
    .wp-list-table .column-id          { width: 5%; }
    .wp-list-table .column-display_name    { width: 25% !important; }
    .wp-list-table .column-display_name a { white-space: nowrap; }
    .wp-list-table .column-units        { width: 5%; }
    .wp-list-table .column-location_name { width: 5%; }
    .wp-list-table .column-expiration    { width: 7%; }
    .wp-list-table .column-opened        { width: 5%; }
    .wp-list-table .column-created       { width: 5%; }

    .wp-list-table .column-percent_remaining { width: 5%; }

     .reimbursed-row {
        background-color: #e6ffea !important;
    }
</style>
<div class="wrap">
    <h1 class="wp-heading-inline">Inventory Items</h1>
    <a href="<?php echo admin_url('admin.php?page=house-ledger-add-inventory'); ?>" class="page-title-action">Add New</a>
    <?php echo $table->display(); ?>
</div>
