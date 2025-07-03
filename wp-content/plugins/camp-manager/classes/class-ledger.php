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
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        global $wpdb;

        $ledger_id = isset($_POST['ledger_id']) ? intval($_POST['ledger_id']) : 0;
        $amount = isset($_POST['ledger_amount']) ? floatval($_POST['ledger_amount']) : 0;
        $date = isset($_POST['ledger_date']) ? sanitize_text_field($_POST['ledger_date']) : null;
        $type = isset($_POST['ledger_type']) ? sanitize_text_field($_POST['ledger_type']) : '';
        $note = isset($_POST['ledger_note']) ? sanitize_text_field($_POST['ledger_note']) : '';
        $cmid = isset($_POST['cmid']) ? intval($_POST['cmid']) : 0;

        // Handle line items
        $line_items = [];
        $descriptions = $_POST['ledger_line_item_note'] ?? [];
        $amounts = $_POST['ledger_line_item_amount'] ?? [];
        $receipt_ids = $_POST['ledger_line_item_receipt_id'] ?? [];
        $types = $_POST['ledger_type'] ?? [];

        $count = max(count($descriptions), count($amounts), count($receipt_ids), count($types));
        for ($i = 0; $i < $count; $i++) {
            $line_item = [
                'description' => sanitize_text_field($descriptions[$i] ?? ''),
                'amount' => floatval($amounts[$i] ?? 0),
                'receipt_id' => intval($receipt_ids[$i] ?? 0) ?: null,
                'type' => sanitize_text_field($types[$i] ?? ''),
            ];
            $line_items[] = (object) $line_item;
        }

        if ($ledger_id > 0) {
            $this->updateLedger($ledger_id, [
                'amount' => $amount,
                'type' => $type,
                'note' => $note,
                'date' => $date,
                'cmid' => $cmid,
                'line_items' => $line_items,
            ]);
        } else {
            $ledger_id = $this->insertLedger([
                'amount' => $amount,
                'type' => $type,
                'note' => $note,
                'date' => $date,
                'cmid' => $cmid,
                'line_items' => $line_items,
            ]);
        }

        wp_redirect(admin_url('admin.php?page=camp-manager-ledger&entry_saved=1'));
        exit;
    }

    public function insertLedger($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mf_ledger';
        $result = $wpdb->insert($table_name, [
            'amount' => $data['amount'],
            'type' => $data['type'],
            'note' => $data['note'],
            'date' => $data['date'],
            'cmid' => $data['cmid'],
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
            'type' => $data['type'],
            'note' => $data['note'],
            'date' => $data['date'],
            'cmid' => $data['cmid'],
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
            'ledger_id' => $ledger_id,
            'description' => $data->description,
            'amount' => $data->amount,
            'receipt_id' => $data->receipt_id,
            'type' => $data->type,
            'date' => current_time('mysql'),
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
        return $wpdb->get_var("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger WHERE type = 'Camp Dues' OR type = 'Partial Camp Dues'") ?: 0;
    }

    public function sumUserCampDues($cmid)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger WHERE cmid = %d AND (type = 'Camp Dues' OR type = 'Partial Camp Dues')", $cmid)) ?: 0;
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
