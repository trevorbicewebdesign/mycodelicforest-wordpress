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
    <?php CampManagerSeason::renderSwitcher(); ?>
    <?php if (!$table->items): ?>
        <?php $previous = $this->budgets->previousSeasonWithCategories(); ?>
        <?php if ($previous): ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin: 8px 0 16px;">
                <input type="hidden" name="action" value="camp_manager_copy_categories">
                <input type="hidden" name="from_season" value="<?php echo (int) $previous; ?>">
                <?php wp_nonce_field('camp_manager_copy_categories'); ?>
                <p>No categories for <?php echo (int) CampManagerSeason::selected(); ?> yet.
                    <button class="button">Copy the <?php echo (int) $previous; ?> categories</button>
                    <span class="description">Copies category names only, not budget items.</span></p>
            </form>
        <?php endif; ?>
    <?php endif; ?>
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
