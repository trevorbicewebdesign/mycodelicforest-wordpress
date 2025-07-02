<?php

// Check if the user has permission to manage options
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$CampManagerLedger = new CampManagerLedger;
$ledger_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ledger = $ledger_id ? $CampManagerLedger->getLedger($ledger_id) : null;

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo $ledger ? 'Edit Ledger Entry' : 'Add Ledger Entry'; ?></h1>
    <hr/>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="camp_manager_save_ledger_entry">
        <table class="form-table">
            <tr>
                <th><label for="ledger_id">ID</label></th>
                <td>
                    <input type="number" name="ledger_id" id="ledger_id" class="regular-text" value="<?php echo $ledger ? esc_attr($ledger->id) : ''; ?>" readonly>
                </td>
            </tr>
            <tr>
                <th><label for="ledger_note">Note</label></th>
                <td>
                    <input type="text" name="ledger_note" id="ledger_note" class="regular-text" value="<?php echo $ledger ? esc_attr($ledger->note) : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="ledger_date">Date</label></th>
                <td>
                    <input type="date" name="ledger_date" id="ledger_date" class="regular-text" value="<?php echo $ledger ? esc_attr(date('Y-m-d', strtotime($ledger->date))) : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="ledger_description">Description</label></th>
                <td>
                    <input type="text" name="ledger_description" id="ledger_description" class="regular-text" value="<?php echo $ledger ? esc_attr($ledger->description) : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="ledger_amount">Amount</label></th>
                <td>
                    <input type="number" name="ledger_amount" id="ledger_amount" class="regular-text" step="0.01" value="<?php echo $ledger ? esc_attr($ledger->amount) : ''; ?>" required>
                </td>
            </tr>
        </table>
    <?php submit_button($ledger ? 'Update Ledger Entry' : 'Add Ledger Entry'); ?>
    </form>
</div>
