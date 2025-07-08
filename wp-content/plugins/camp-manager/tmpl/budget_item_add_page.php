<?php

$budget_item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$budget_item = $budget_item_id ? $this->budgets->getBudgetItem($budget_item_id) : null;
$is_edit = $budget_item !== null;
$budget_item_id = $is_edit ? intval($budget_item->id) : 0;
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Budget Item' : 'Add New Budget Item'; ?></h1>
    <hr/>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="camp_manager_save_budget_item">
        <?php if ($is_edit): ?>
            <input type="hidden" name="budget_item_id" value="<?php echo esc_attr($budget_item_id); ?>">
        <?php endif; ?>
        <table class="form-table">
            <tr>
                <th><label for="budget_item_name">Name</label></th>
                <td>
                    <input type="text" name="budget_item_name" id="budget_item_name" class="regular-text" value="<?php echo esc_attr($budget_item->name ?? ''); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="budget_item_description">Description</label></th>
                <td>
                    <textarea name="budget_item_description" id="budget_item_description" rows="4" class="large-text"><?php echo esc_textarea($budget_item->description ?? ''); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label for="budget_item_category">Category</label></th>
                <td>
                    <?php $categories = $this->core->getItemCategories(); ?>
                    <select name="budget_item_category" id="budget_item_category" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $cat_id => $cat): ?>
                            <option value="<?php echo esc_attr($cat['id']); ?>" <?php selected(($budget_item->category_id ?? '') == $cat_id); ?>>
                                <?php echo esc_html($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="budget_item_price">Price</label></th>
                <td>
                    <input type="number" step="0.01" name="budget_item_price" id="budget_item_price" value="<?php echo esc_attr($budget_item->price ?? ''); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="budget_item_quantity">Quantity</label></th>
                <td>
                    <input type="number" step="1" name="budget_item_quantity" id="budget_item_quantity" value="<?php echo esc_attr($budget_item->quantity ?? ''); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="budget_item_subtotal">Subtotal</label></th>
                <td>
                    <input type="number" step="0.01" name="budget_item_subtotal" id="budget_item_subtotal" value="<?php echo esc_attr($budget_item->subtotal ?? ''); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="budget_item_tax">Tax</label></th>
                <td>
                    <input type="number" step="0.01" name="budget_item_tax" id="budget_item_tax" value="<?php echo esc_attr($budget_item->tax ?? ''); ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="budget_item_total">Total</label></th>
                <td>
                    <input type="number" step="0.01" name="budget_item_total" id="budget_item_total" value="<?php echo esc_attr($budget_item->total ?? ''); ?>" required>
                </td>
            </tr>

            <tr>
                <th><label for="budget_item_priority">Priority</label></th>
                <td>
                    <input type="number" step="1" name="budget_item_priority" id="budget_item_priority" value="<?php echo esc_attr($budget_item->priority ?? ''); ?>" required>
                </td>
            </tr>
            
        </table>

        <?php submit_button($is_edit ? 'Edit Budget Item' : 'Add Budget Item'); ?>
    </form>
</div>
<?php