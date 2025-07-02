<?php

$table = new CampManagerReceiptsTable();
$table->process_bulk_action();
$table->prepare_items();
?>
<style>
    .wp-list-table .column-store       { width: 40%; }
    .wp-list-table .column-date        { width: 15%; }
    .wp-list-table .column-total       { width: 10%; text-align: right; }
    .wp-list-table .column-subtotal    { width: 10%; text-align: right; }
    .wp-list-table .column-tax         { width: 10%; text-align: right; }
    .wp-list-table .column-shipping    { width: 15%; text-align: right; }
</style>
<div class="wrap">
    <h1 class="wp-heading-inline">Receipts</h1>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-receipt'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <form method="post">
        <?php
        $table->display();
        ?>
    </form>
</div>
