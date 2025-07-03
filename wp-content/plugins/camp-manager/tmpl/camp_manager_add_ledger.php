<?php

// Check if the user has permission to manage options
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}
$CampManagerCore = new CampManagerCore();
$CampManagerChatGPT = new CampManagerChatGPT($CampManagerCore);
$CampManagerReceipts = new CampManagerReceipts($CampManagerCore, $CampManagerChatGPT);
$CampManagerLedger = new CampManagerLedger($CampManagerReceipts);
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
                <th><label for="ledger_amount">Amount</label></th>
                <td>
                    <input type="number" name="ledger_amount" id="ledger_amount" class="regular-text" step="0.01" value="<?php echo $ledger ? esc_attr($ledger->amount) : ''; ?>" required>
                </td>
            </tr>
            
                <td colspan="2">
                    <!-- Ledger Line Items (for entries not linked to a receipt) -->
                    <table>
                        <?php
                        $line_items = [];
                        if ($ledger && isset($ledger->line_items) && is_array($ledger->line_items) && count($ledger->line_items) > 0) {
                            $line_items = $ledger->line_items;
                        } else {
                            // Always show at least one empty row
                            $line_items[] = ['note' => '', 'type' => '', 'amount' => '', 'receipt_id' => ''];
                        }
                        foreach ($line_items as $idx => $item): ?>
                           <tr class="ledger-line-row">
                                <td>
                                    <select name="ledger_line_item_receipt_id[]" class="receipt-select">
                                        <option value="">-- Select Receipt (optional) --</option>
                                        <?php foreach ($CampManagerReceipts->getUnreimbursedReceipts() as $receipt): ?>
                                            <option value="<?php echo esc_attr($receipt->id); ?>" <?php selected($item->receipt_id ?? '', $receipt->id); ?>>
                                                <?php echo esc_html("{$receipt->id} - {$receipt->store} (" . date('Y-m-d', strtotime($receipt->date)) . ") - {$receipt->total}"); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="ledger_line_item_note[]" class="line-note regular-text" placeholder="Enter note" value="<?php echo esc_attr($item->note ?? ''); ?>">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="ledger_line_item_amount[]" class="line-amount regular-text" placeholder="Amount" value="<?php echo esc_attr($item->amount ?? ''); ?>">
                                </td>
                                <td>
                                    <select name="ledger_type[]" class="line-type">
                                        <option value="camp_dues" <?php selected($item->type ?? '', 'camp_dues'); ?>>Camp Dues</option>
                                        <option value="partial_camp_dues" <?php selected($item->type ?? '', 'partial_camp_dues'); ?>>Partial Camp Dues</option>
                                        <option value="expense" <?php selected($item->type ?? '', 'expense'); ?>>Expense</option>
                                        <option value="donation" <?php selected($item->type ?? '', 'donation'); ?>>Donation</option>
                                        <option value="sold_asset" <?php selected($item->type ?? '', 'sold_asset'); ?>>Sold Asset</option>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
        </table>
    <?php submit_button($ledger ? 'Update Ledger Entry' : 'Add Ledger Entry'); ?>
    </form>
</div>
<script>
jQuery(function($) {
    $('.receipt-select').on('change', function() {
        const $row = $(this).closest('.ledger-line-row');
        const receiptId = $(this).val();
        const $itemSelect = $row.find('.receipt-item-select');
        const $note = $row.find('.line-note');
        const $amount = $row.find('.line-amount');
        const $type = $row.find('.line-type');

        if (receiptId) {
            // Set type to 'expense' and disable inputs
            $note.prop('readonly', true).val('');
            $amount.prop('readonly', true);
            $type.val('expense').prop('disabled', true);

            // Get receipt total
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'camp_manager_get_receipt_total',
                    receipt_id: receiptId
                },
                success: function(res) {
                    if (res.success && res.data.total !== undefined) {
                        $amount.val(res.data.total.toFixed(2));
                    }
                }
            });

            // Optional: Load items if you still use a receipt-item-select
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'camp_manager_get_receipt_items',
                    receipt_id: receiptId
                },
                success: function(res) {
                    if (res.success && Array.isArray(res.data)) {
                        $itemSelect.empty().append('<option value="">-- Select Receipt Item --</option>');
                        res.data.forEach(item => {
                            const label = `${item.name} - $${parseFloat(item.subtotal).toFixed(2)}`;
                            $itemSelect.append(`<option value="${item.id}" data-note="${item.name}" data-amount="${item.subtotal}">${label}</option>`);
                        });
                        $itemSelect.prop('disabled', false);
                    }
                }
            });

        } else {
            // Reset when no receipt selected
            $itemSelect.empty().append('<option value="">-- Select Receipt Item (optional) --</option>').prop('disabled', true);
            $note.prop('readonly', false).val('');
            $amount.prop('readonly', false).val('');
            $type.prop('disabled', false).val('');
        }
    });

});
</script>
