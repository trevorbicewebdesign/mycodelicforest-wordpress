<?php

$table = new CampManagerBudgetCategoriesTable();
$table->process_bulk_action();
$table->prepare_items();
?>
<style>
    
</style>
<div class="wrap">
    <h1 class="wp-heading-inline">Budget Categories</h1>
    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-budget-category'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <h3>Must Have: <?php echo $table->get_must_have_total(); ?></h3>
    <h3>Should Have: <?php echo $table->get_should_have_total(); ?></h3>
    <h3>Could Have: <?php echo $table->get_could_have_total(); ?></h3>
    <h3>Nice to Have: <?php echo $table->get_nice_to_have_total(); ?></h3>
    <form method="post">
        <?php
        $table->display();
        ?>
    </form>
</div>
<?php
