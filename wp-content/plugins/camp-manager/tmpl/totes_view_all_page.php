<?php

$table = new CampManagerTotesTable();
$table->process_bulk_action();
$table->prepare_items();
?>
<style>
    .wp-list-table td.column-name { width: 30%; }
    .wp-list-table td.column-name {
        white-space: normal;
        word-break: break-word;
    }

    .wp-list-table td.column-name a {
        display: inline-block;
        max-width: 200px;
    }
    .wp-list-table td, .wp-list-table th {
            padding: 4px 6px;
            vertical-align: middle;
        }
</style>
<div class="wrap">
    <h1 class="wp-heading-inline">Totes</h1>
    <h4><?php echo "Packed Totes: " . number_format($this->inventory->sumPackedTotes(), 2); ?> lbs</h4>
    <h4><?php echo "Sojourner Totes: " . number_format($this->inventory->sumSojournerTotes(), 2); ?> lbs</h4>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-tote'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <form method="post">
        <?php
        $table->display();
        ?>
    </form>
</div>
