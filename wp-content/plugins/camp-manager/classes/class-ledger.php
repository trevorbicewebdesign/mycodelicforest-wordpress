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
        add_action('admin_post_camp_manager_save_ledger_entry', [$this, 'handle_ledger_entry_save']);
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

       

        if ($ledger_id) {

            $this->updateLedger($ledger_id, [
                'amount' => $amount,
                'note' => $note,
                'date' => $date,
            ]);
        } else {
            // Insert new ledger
            $ledger_id = $this->insertLedger([
                'note' => $note,
                'date' => $date,
                'amount' => $amount,
                'type' => '', // Default type, can be updated later
                'line_items' => [] // Will be filled below
            ]);
        }

        // Process line items
        $line_items = $this->normalizeLedgerLineItems(
            $_POST['ledger_line_item_id'],
            $_POST['ledger_line_item_note'],
            $_POST['ledger_line_item_amount'],
            $_POST['ledger_line_item_receipt_id'],
            $_POST['ledger_type']
        );

        // Delete removed line items
        if ($ledger_id) {
            $existing_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT id FROM $table_lines WHERE ledger_id = %d", $ledger_id
            ));

            $to_delete = array_diff($existing_ids, $seen_ids);

            foreach ($to_delete as $id) {
                $wpdb->delete($table_lines, ['id' => $id]);
            }
        }

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


    public function insertLedger($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mf_ledger';
        $result = $wpdb->insert($table_name, [
            'amount' => $data['amount'],
            'note' => $data['note'],
            'date' => $data['date'],
        ]);

        if (!$result) {
            return false;
        }

        $ledger_id = $wpdb->insert_id;

        foreach ($data['line_items'] as $item) {
            $this->insertLedgerLineItem($ledger_id, $item);
        }

        return $ledger_id;
    }

    public function updateLedger($ledger_id, $data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mf_ledger';
        $wpdb->update($table_name, [
            'amount' => $data['amount'],
            'note' => $data['note'],
            'date' => $data['date'],
        ], [ 'id' => $ledger_id ]);

        $wpdb->delete($wpdb->prefix . 'mf_ledger_line_items', ['ledger_id' => $ledger_id]);

        foreach ($data['line_items'] as $item) {
            $this->insertLedgerLineItem($ledger_id, $item);
        }
    }

    public function insertLedgerLineItem($ledger_id, $data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mf_ledger_line_items';
        $wpdb->insert($table_name, [
            'ledger_id'   => $ledger_id,
            // 'description' => $data['description'],
            'amount'      => $data['amount'],
            'receipt_id'  => isset($data['receipt_id']) ? $data['receipt_id'] : null,
            'type'        => isset($data['type']) ? $data['type'] : '',
            'date'        => current_time('mysql'),
        ]);
    }

    public function getLedger($ledger_id)
    {
        global $wpdb;

        $ledger = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}mf_ledger WHERE id = %d", $ledger_id));
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

    public function record_money_in($amount, $description = '', $date = null)
    {
        return [
            'type' => 'money_in',
            'amount' => $amount,
            'description' => $description,
            'date' => $date ?: date('Y-m-d H:i:s'),
        ];
    }

    public function record_money_out($amount, $description = '', $date = null)
    {
        return [
            'type' => 'money_out',
            'amount' => $amount,
            'description' => $description,
            'date' => $date ?: date('Y-m-d H:i:s'),
        ];
    }
}
