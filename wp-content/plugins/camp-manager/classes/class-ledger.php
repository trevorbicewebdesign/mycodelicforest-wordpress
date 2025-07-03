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
        // camp_manager_save_ledger_entry
        add_action('admin_post_camp_manager_save_ledger_entry', [$this, 'handle_ledger_entry_save']);
    }

    public function handle_ledger_entry_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

       
        // Validate and sanitize input
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : null;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';
        $note = isset($_POST['note']) ? sanitize_text_field($_POST['note']) : '';
        $cmid = isset($_POST['cmid']) ? intval($_POST['cmid']) : 0;

        // Handle line items from separate arrays
        $line_items = [];
        $descriptions = isset($_POST['ledger_line_item_description']) ? $_POST['ledger_line_item_description'] : [];
        $amounts = isset($_POST['ledger_line_item_amount']) ? $_POST['ledger_line_item_amount'] : [];
        $dates = isset($_POST['ledger_line_item_date']) ? $_POST['ledger_line_item_date'] : [];
        $receipt_ids = isset($_POST['ledger_line_item_receipt_id']) ? $_POST['ledger_line_item_receipt_id'] : [];

        $count = max(count($descriptions), count($amounts), count($dates), count($receipt_ids));
        for ($i = 0; $i < $count; $i++) {
            $desc = isset($descriptions[$i]) ? sanitize_text_field($descriptions[$i]) : '';
            $amt = isset($amounts[$i]) ? floatval($amounts[$i]) : 0;
            $item_date = isset($dates[$i]) ? sanitize_text_field($dates[$i]) : null;
            $receipt_id = isset($receipt_ids[$i]) ? intval($receipt_ids[$i]) : null;
            $line_item = [
                'description' => $desc,
                'amount' => $amt,
                'date' => $item_date,
            ];
            if ($receipt_id) {
                $line_item['receipt_id'] = $receipt_id;
            }
            $line_items[] = (object) $line_item;
        }

        // Insert the ledger entry and get the entry ID
        $entry_id = $this->insertLedger([
            'amount' => $amount,
            'type' => $type,
            'note' => $note,
            'date' => $date,
            'cmid' => $cmid,
            'line_items' => $line_items,
        ]);
        
        // Redirect or send a response
        if ($entry_id) {
            wp_redirect(admin_url('admin.php?page=camp-manager-ledger&entry_saved=1'));
        } else {
            wp_redirect(admin_url('admin.php?page=camp-manager-ledger&error=1'));
        }
        exit;
    }

    public function insertLedger($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mf_ledger';
        $result = $wpdb->insert($table_name, [
            'amount' => $data['amount'],
            'type' => isset($data['type']) ? $data['type'] : '',
            'note' => isset($data['note']) ? $data['note'] : '',
            'date' => isset($data['date']) ? $data['date'] : current_time('mysql'),
            'cmid' => isset($data['cmid']) ? (int)$data['cmid'] : 0,
        ]);

        if (!$result) {
            return false;
        }

        if($data['receipt_id'] ?? false) {
            $receipt = $this->receipts->get_receipt($data['receipt_id']);
            print_r($receipt);
        }

        $ledger_id = $wpdb->insert_id;

        // Insert line items if provided
        if (!empty($data['line_items']) && is_array($data['line_items'])) {
            foreach ($data['line_items'] as $item) {
                $this->insertLedgerLineItem($ledger_id, $item);
            }
        }

        return $ledger_id;
    }

    public function insertLedgerLineItem($ledger_id, $data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'mf_ledger_line_items';
        $result = $wpdb->insert($table_name, [
            'ledger_id' => $ledger_id,
            'description' => isset($data['description']) ? $data['description'] : '',
            'amount' => isset($data['amount']) ? floatval($data['amount']) : 0,
            'date' => isset($data['date']) ? $data['date'] : current_time('mysql'),
        ]);

        return $result ? $wpdb->insert_id : false;
    }

    public function getLedger($ledger_id)
    {
        global $wpdb;
      
        $query = $wpdb->prepare("
            SELECT * 
            FROM {$wpdb->prefix}mf_ledger 
            WHERE id = %d
            ", $ledger_id);
        $result = $wpdb->get_row($query);

        $items = $this->getLedgerLineItems($ledger_id);
        if ($result) {
            $result->line_items = $items;
        }   

        return $result ? $result : [];
    }

    public function getLedgerLineItems($ledger_id)
    {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT * 
            FROM {$wpdb->prefix}mf_ledger_line_items
            WHERE ledger_id = %d
            ", $ledger_id);
        $result = $wpdb->get_results($query);
        return $result ? $result : [];
    }

    public function startingBalance()
    {
        return 2037.80; 
    }

    public function totalMoneyIn()
    {
        global $wpdb;

        $query = "
            SELECT SUM(amount) 
            FROM {$wpdb->prefix}mf_ledger 
            WHERE amount > 0
            "; 
        $total = $wpdb->get_var($query);

        return $total ? $total : 0;
    }

    public function totalMoneyOut()
    {
        global $wpdb;

        $query = "
            SELECT SUM(amount) 
            FROM {$wpdb->prefix}mf_ledger 
            WHERE amount < 0
            "; 
        $total = $wpdb->get_var($query);

        return $total ? abs($total) : 0;
    }

    public function totalDonations()
    {
        global $wpdb;

        $query = "
            SELECT SUM(amount) 
            FROM {$wpdb->prefix}mf_ledger 
            WHERE type = 'Donation'
            "; 
        $total = $wpdb->get_var($query);

        return $total ? $total : 0;
    }

    public function totalCampDues()
    {
        global $wpdb;

        $query = "
            SELECT SUM(amount) 
            FROM {$wpdb->prefix}mf_ledger 
            WHERE type = 'Camp Dues' OR type = 'Partial Camp Dues'
            "; 
        $total = $wpdb->get_var($query);

        return $total ? $total : 0;
    }

    public function sumUserCampDues($cmid)
    {
        global $wpdb;

        $query = "
            SELECT SUM(amount) 
            FROM {$wpdb->prefix}mf_ledger 
            WHERE cmid = %d AND (type = 'Camp Dues' OR type = 'Partial Camp Dues')
            "; 
        $query = $wpdb->prepare($query, $cmid);
        $total = $wpdb->get_var($query);

        return $total ? $total : 0;
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