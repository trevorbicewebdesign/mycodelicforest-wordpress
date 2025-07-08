<?php

$budget_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$budget = $budget_id ? $this->budgets->getBudgetCategory($budget_id) : null;

$is_edit = $budget !== null;
$budget_id = $is_edit ? intval($budget->id) : 0;
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Budget Category' : 'Add New Budget Category'; ?></h1>

    <hr/>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="camp_manager_save_budget_category">
        <?php if ($is_edit): ?>
            <input type="hidden" name="budget_id" value="<?php echo esc_attr($budget_id); ?>">
        <?php endif; ?>
        <table class="form-table">
            <tr>
                <th><label for="budget_category_name">Name</label></th>
                <td>
                    <input type="text" name="budget_category_name" id="budget_category_name" class="regular-text" value="<?php echo esc_attr($budget->name ?? ''); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="budget_category_description">Description</label></th>
                <td>
                    <textarea name="budget_category_description" id="budget_category_description" rows="4" class="large-text"><?php echo esc_textarea($budget->description ?? ''); ?></textarea>
                </td>
            </tr>
        </table>
        <?php submit_button($is_edit ? 'Update Budget Category' : 'Add Budget Category'); ?>
    </form>

    <hr>

    <h2>Budget Items in this Category</h2>
    <?php
    $table = new CampManagerBudgetItemsTable($budget_id);
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

    <a href="<?php echo admin_url('admin.php?page=camp-manager-add-budget-item'); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">
    <form method="post">
        <?php
        $table->display();
        ?>

</div>
<?php