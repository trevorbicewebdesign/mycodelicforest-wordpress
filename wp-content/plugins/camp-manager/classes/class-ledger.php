<?php

class CampManagerLedger
{
    private $receipts;

    public function __construct(CampManagerReceipts $receipts)
    {
        $this->receipts = $receipts;
    }

    public function init()
    {
        add_action('admin_post_camp_manager_save_ledger', [$this, 'handle_ledger_entry_save']);
        add_action('admin_post_camp_manager_save_and_close_ledger', [$this, 'handle_ledger_entry_save']);

    }

    public function handle_ledger_entry_save()
    {
        global $wpdb;

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission.'));
        }

        $ledger_id = intval($_POST['ledger_id'] ?? 0);
        $note = sanitize_text_field($_POST['ledger_note'] ?? '');
        $date = sanitize_text_field($_POST['ledger_date'] ?? '');
        $amount = floatval($_POST['ledger_amount'] ?? 0);

        $table_ledger = $wpdb->prefix . 'mf_ledger';
        $table_lines = $wpdb->prefix . 'mf_ledger_line_items';

        $data = [
            'ledger_id' => $ledger_id>0? $ledger_id : null,
            'note' => $note,
            'date' => $date,
            'amount' => $amount,
            'line_items' => $this->normalizeLedgerLineItems(
                $_POST['ledger_line_item_id'] ?? [],
                $_POST['ledger_line_item_note'] ?? [],
                $_POST['ledger_line_item_amount'] ?? [],
                $_POST['ledger_line_item_receipt_id'] ?? [],
                $_POST['ledger_line_item_type'] ?? []
            )
        ];
     
        // Save the ledger entry
        $ledger_id = $this->saveLedger($data);

        // Redirect
        wp_redirect(admin_url('admin.php?page=camp-manager-add-ledger&id=' . $ledger_id . '&success=1'));
        exit;
    }


    /**
     * Normalize posted line item arrays into structured objects
     */
    public function normalizeLedgerLineItems(array $ids, array $notes, array $amounts, array $receipt_ids, array $types): array
    {
        $items = [];

        $count = max(
            count($ids),
            count($notes),
            count($amounts),
            count($receipt_ids),
            count($types)
        );

        for ($i = 0; $i < $count; $i++) {
            $amount = isset($amounts[$i]) ? floatval($amounts[$i]) : 0;
            $type = isset($types[$i]) ? sanitize_text_field($types[$i]) : '';

            // Skip empty/irrelevant line items
            if ($amount === 0 && empty($type)) {
                continue;
            }

            $item = (object)[
                'id'         => isset($ids[$i]) ? intval($ids[$i]) : 0,
                'note'       => isset($notes[$i]) ? sanitize_text_field($notes[$i]) : '',
                'amount'     => $amount,
                'receipt_id' => isset($receipt_ids[$i]) ? intval($receipt_ids[$i]) : null,
                'type'       => $type,
            ];

            $items[] = $item;
        }

        return $items;
    }

    public function saveLedger($data)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_ledger';

        $is_new = empty($data['ledger_id']);

        if ($is_new) {
            $result = $wpdb->insert($table, [
                'amount' => $data['amount'],
                'note'   => $data['note'],
                'date'   => $data['date'],
            ]);

            if (!$result) {
                return false;
            }

            $ledger_id = $wpdb->insert_id;
        } else {
            $ledger_id = $data['ledger_id'];
            $wpdb->update($table, [
                'amount' => $data['amount'],
                'note'   => $data['note'],
                'date'   => $data['date'],
            ], ['id' => $ledger_id]);
        }

        // Save line items if present
        if (!empty($data['line_items']) && is_array($data['line_items'])) {
            $this->saveLedgerLineItems($ledger_id, $data['line_items']);
        }

        return $ledger_id;
    }

    public function saveLedgerLineItems($ledger_id, array $line_items)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_ledger_line_items';

        // Get existing line item IDs from DB for this ledger
        $existing_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT id FROM $table WHERE ledger_id = %d", $ledger_id
        ));

        $seen_ids = [];

        foreach ($line_items as $item) {
            $id = intval($item->id ?? 0);  // object-style since normalizeLedgerLineItems returns objects

            $data = [
            'ledger_id'  => $ledger_id,
            'amount'     => floatval($item->amount ?? 0),
            'receipt_id' => !empty($item->receipt_id) ? intval($item->receipt_id) : null,
            'note'       => sanitize_text_field($item->note ?? ''),
            'type'       => $item->type,
            ];

            if ($id > 0 && in_array($id, $existing_ids)) {
            // Existing item – update
            $wpdb->update($table, $data, ['id' => $id]);
            $seen_ids[] = $id;
            } else {
            // New item – insert
            $wpdb->insert($table, $data);
            $seen_ids[] = $wpdb->insert_id;
            }
        }

        // Delete line items that were not seen in the form (i.e., removed by user)
        $to_delete = array_diff($existing_ids, $seen_ids);
        foreach ($to_delete as $delete_id) {
            $wpdb->delete($table, ['id' => $delete_id]);
        }
    }


    public function getLedger(int $ledger_id)
    {
        global $wpdb;

        $query = "SELECT * FROM {$wpdb->prefix}mf_ledger WHERE id = %d";
        $query = $wpdb->prepare($query, $ledger_id);
        $ledger = $wpdb->get_row($query);
        if ($ledger) {
            $ledger->line_items = $this->getLedgerLineItems($ledger_id);
        }
        return $ledger;
    }

    public function getLedgerLineItems($ledger_id)
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mf_ledger_line_items WHERE ledger_id = %d", $ledger_id));
    }

    public function startingBalance() { return 2037.80; }

    public function totalMoneyIn()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger WHERE amount > 0") ?: 0;
    }

    public function totalMoneyOut()
    {
        global $wpdb;
        return abs($wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger WHERE amount < 0") ?: 0);
    }

    public function totalDonations()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger WHERE type = 'Donation'") ?: 0;
    }

    public function totalCampDues()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger_line_items WHERE type = 'Camp Dues' OR type = 'Partial Camp Dues'") ?: 0;
    }

    public function sumUserCampDues($cmid)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger_line_items WHERE cmid = %d AND (type = 'Camp Dues' OR type = 'Partial Camp Dues')", $cmid)) ?: 0;
    }
}
