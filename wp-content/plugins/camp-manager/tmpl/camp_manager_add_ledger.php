<?php

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
        <input type="hidden" name="ledger_id" value="<?php echo esc_attr($ledger->id ?? ''); ?>">

        <table class="form-table">
            <tr>
                <th><label for="ledger_note">Note</label></th>
                <td><input type="text" name="ledger_note" class="regular-text" value="<?php echo esc_attr($ledger->note ?? ''); ?>" required></td>
            </tr>
            <tr>
                <th><label for="ledger_date">Date</label></th>
                <td><input type="date" name="ledger_date" class="regular-text" value="<?php echo esc_attr($ledger ? date('Y-m-d', strtotime($ledger->date)) : ''); ?>" required></td>
            </tr>
            <tr>
                <th><label for="ledger_amount">Total Amount</label></th>
                <td><input type="number" step="0.01" name="ledger_amount" class="regular-text" value="<?php echo esc_attr($ledger->amount ?? ''); ?>" required></td>
            </tr>
        </table>

        <h2>Line Items</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Receipt</th>
                    <th>Note</th>
                    <th>Amount</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="ledger-line-items">
                <?php
                $line_items = $ledger->line_items ?? [
                    (object)['note' => '', 'amount' => '', 'receipt_id' => '', 'type' => '']
                ];
                foreach ($line_items as $item): ?>
                    <tr class="ledger-line-row">
                        <td>
                            <?php echo esc_html($item->id ?? ''); ?>
                            <input type="hidden" name="ledger_line_item_id[]" value="<?php echo esc_attr($item->id ?? ''); ?>">
                        </td>
                        <td>
                            <select name="ledger_line_item_receipt_id[]" class="receipt-select">
                                <option value="">-- Select Receipt (optional) --</option>
                                <?php foreach ($CampManagerReceipts->getUnreimbursedReceipts() as $receipt): ?>
                                    <option value="<?php echo esc_attr($receipt->id); ?>" <?php selected($item->receipt_id ?? '', $receipt->id); ?>>
                                        <?php echo esc_html("{$receipt->id} - {$receipt->store} (" . date('Y-m-d', strtotime($receipt->date)) . ") - \${$receipt->total}"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($item->receipt_id)): ?>
                                <br><a href="admin.php?page=camp-manager-edit-receipt&id=<?php echo intval($item->receipt_id); ?>" target="_blank">View Receipt</a>
                            <?php endif; ?>
                        </td>
                        <td><input type="text" name="ledger_line_item_note[]" class="line-note regular-text" placeholder="Note" value="<?php echo esc_attr($item->description ?? $item->note ?? ''); ?>"></td>
                        <td><input type="number" step="0.01" name="ledger_line_item_amount[]" class="line-amount regular-text" placeholder="Amount" value="<?php echo esc_attr($item->amount ?? ''); ?>"></td>
                        <td>
                            <select name="ledger_type[]" class="line-type">
                                <option value="">-- Type --</option>
                                <option value="Camp Dues" <?php selected($item->type ?? '', 'Camp Dues'); ?>>Camp Dues</option>
                                <option value="Partial Camp Dues" <?php selected($item->type ?? '', 'Partial Camp Dues'); ?>>Partial Camp Dues</option>
                                <option value="Expense" <?php selected($item->type ?? '', 'Expense'); ?>>Expense</option>
                                <option value="Donation" <?php selected($item->type ?? '', 'Donation'); ?>>Donation</option>
                                <option value="Sold Asset" <?php selected($item->type ?? '', 'Sold Asset'); ?>>Sold Asset</option>
                            </select>
                        </td>
                        <td><button type="button" class="button remove-line-item">Remove</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><button type="button" class="button button-secondary" id="add-line-item">Add Line Item</button></p>

        <?php submit_button($ledger ? 'Update Ledger Entry' : 'Add Ledger Entry'); ?>
    </form>
    <table style="display: none;">
        <tbody>
            <tr id="ledger-line-template" class="ledger-line-row">
                <td>
                    <?php echo esc_html($item->id ?? ''); ?>
                    <input type="hidden" name="ledger_line_item_id[]" value="">
                </td>
                <td>
                    <select name="ledger_line_item_receipt_id[]" class="receipt-select">
                        <option value="">-- Select Receipt (optional) --</option>
                        <?php foreach ($CampManagerReceipts->getUnreimbursedReceipts() as $receipt): ?>
                            <option value="<?php echo esc_attr($receipt->id); ?>">
                                <?php echo esc_html("{$receipt->id} - {$receipt->store} (" . date('Y-m-d', strtotime($receipt->date)) . ") - \${$receipt->total}"); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="ledger_line_item_note[]" class="line-note regular-text" placeholder="Note"></td>
                <td><input type="number" step="0.01" name="ledger_line_item_amount[]" class="line-amount regular-text" placeholder="Amount"></td>
                <td>
                    <select name="ledger_type[]" class="line-type">
                        <option value="">-- Type --</option>
                        <option value="Camp Dues">Camp Dues</option>
                        <option value="Partial Camp Dues">Partial Camp Dues</option>
                        <option value="Expense">Expense</option>
                        <option value="Donation">Donation</option>
                        <option value="Sold Asset">Sold Asset</option>
                    </select>
                </td>
                <td><button type="button" class="button remove-line-item">Remove</button></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
jQuery(function($) {
    function bindReceiptChange($row) {
        const $select = $row.find('.receipt-select');
        const $note = $row.find('.line-note');
        const $amount = $row.find('.line-amount');
        const $type = $row.find('.line-type');

        $select.on('change', function () {
            const receiptId = $(this).val();
            if (receiptId) {
                $note.prop('readonly', true).val('');
                $amount.prop('readonly', true);
                $type.val('Expense').prop('disabled', true);
                $amount.val('...');

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
                        } else {
                            $amount.val('');
                        }
                    },
                    error: function() {
                        console.error('Failed to fetch receipt total');
                        $amount.val('');
                    }
                });
            } else {
                $note.prop('readonly', false).val('');
                $amount.prop('readonly', false).val('');
                $type.prop('disabled', false).val('');
            }
        });
    }

    $('.ledger-line-row').each(function () {
        bindReceiptChange($(this));
    });

    $('#add-line-item').on('click', function () {
        const $template = $('#ledger-line-template').clone().removeAttr('id').show();
        $('#ledger-line-items').append($template);
        bindReceiptChange($template);
    });

    $(document).on('click', '.remove-line-item', function () {
        $(this).closest('tr').remove();
    });
});
</script>
