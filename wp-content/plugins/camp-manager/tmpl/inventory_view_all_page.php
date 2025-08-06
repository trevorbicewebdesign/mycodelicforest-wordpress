<?php

$table = new CampManagerInventoryTable();
$table->process_bulk_action();
$table->prepare_items();
?>
<style>
    .wp-list-table .column-name       { width: 30%; }
</style>
<div class="wrap">
    <h1 class="wp-heading-inline">Inventory</h1>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-inventory'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <form method="post">
        <?php
        $table->search_box('Search Inventory', 'tote-inventory-search');
        $table->display();
        ?>
    </form>
</div>
