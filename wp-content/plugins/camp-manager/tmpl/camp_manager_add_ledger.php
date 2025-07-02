<?php

// Check if the user has permission to manage options
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Add Ledger Entry</h1>
    <hr/>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="camp_manager_save_ledger_entry">
        <table class="form-table">
            <tr>
                <th><label for="ledger_date">Date</label></th>
                <td>
                    <input type="date" name="ledger_date" id="ledger_date" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th><label for="ledger_description">Description</label></th>
                <td>
                    <input type="text" name="ledger_description" id="ledger_description" class="regular-text" required>
                </td>
            </tr>
            <tr>
                <th><label for="ledger_amount">Amount</label></th>
                <td>
                    <input type="number" name="ledger_amount" id="ledger_amount" class="regular-text" step="0.01" required>
                </td>
            </tr>
        </table>
    <?php submit_button('Add Ledger Entry'); ?>
    </form>
</div>
