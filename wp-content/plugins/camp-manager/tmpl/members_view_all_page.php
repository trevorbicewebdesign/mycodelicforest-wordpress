<?php

$table = new CampManagerRosterTable($this->ledger);
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

    .paid-row {
        background-color: #e6ffea !important;
    }
    .unpaid-row {
        background-color: #ffe6e6 !important;
    }
</style>
<div class="wrap">
    <h1 class="wp-heading-inline">Roster</h1>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-member'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <h3>Total Camp Members: <?php echo $this->roster->countRosterMembers(); ?></h3>
    <h4>Low Income Camp Members: <?php echo $this->roster->countLowIncomeMembers(); ?></h4>
    <h3>Total Camp Dues: <?php echo $this->ledger->totalCampDues(); ?></h3>
    <h3>Low Income Camp Dues Paid: <?php echo $this->roster->countPaidLowIncomeCampDues(); ?></h3>

    <form method="post">
        <?php
        $table->display();
        ?>
    </form>
</div>