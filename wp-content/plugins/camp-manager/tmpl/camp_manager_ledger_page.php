<?php

$table = new CampManagerLedgerTable();
$table->process_bulk_action();
$table->prepare_items();
?>
<style>
    .wp-list-table .column-id          { width: 5%; }
    .wp-list-table .column-amount      { width: 5%; }
    .wp-list-table .column-date        { width: 10%; }
    .wp-list-table .column-receipts   { width: 5%; }
    .wp-list-table .column-link       { width: 5%; }

</style>
<div class="wrap">
    <h1 class="wp-heading-inline">Ledger</h1>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-ledger'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <h3>Money In: <?php echo esc_html($table->get_total_money_in()); ?></h3>
    <h3>Money Out: <?php echo esc_html($table->get_total_money_out()); ?></h3>
    <form method="post">
        <?php
        $table->display();
        ?>
    </form>
</div>