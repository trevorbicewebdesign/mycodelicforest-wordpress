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
        add_action('admin_post_camp_manager_save_and_close_ledger', [$this, 'handle_ledger_entry_save_close']);

    }
    public function handle_ledger_entry_save()
    {
        global $wpdb;

        if (!current_user_can(CampManagerRoles::cap('finances'))) {
            wp_die(__('You do not have permission.'));
        }

        $ledger_id = intval($_POST['ledger_id'] ?? 0);
        $note = sanitize_text_field($_POST['ledger_note'] ?? '');
        $date = sanitize_text_field($_POST['ledger_date'] ?? '');
        $amount = floatval($_POST['ledger_amount'] ?? 0);
        $link = isset($_POST['ledger_link']) ? esc_url_raw($_POST['ledger_link']) : null;
        
        $table_ledger = $wpdb->prefix . 'mf_ledger';
        $table_lines = $wpdb->prefix . 'mf_ledger_line_items';

        $data = [
            'ledger_id' => $ledger_id>0? $ledger_id : null,
            'note' => $note,
            'date' => $date,
            'amount' => $amount,
            'link' => $link,
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

    public function handle_ledger_entry_save_close()
    {
        global $wpdb;

        if (!current_user_can(CampManagerRoles::cap('finances'))) {
            wp_die(__('You do not have permission.'));
        }

        $ledger_id = intval($_POST['ledger_id'] ?? 0);
        $note = sanitize_text_field($_POST['ledger_note'] ?? '');
        $date = sanitize_text_field($_POST['ledger_date'] ?? '');
        $amount = floatval($_POST['ledger_amount'] ?? 0);
        $link = isset($_POST['ledger_link']) ? esc_url_raw($_POST['ledger_link']) : null;
        
        $table_ledger = $wpdb->prefix . 'mf_ledger';
        $table_lines = $wpdb->prefix . 'mf_ledger_line_items';

        $data = [
            'ledger_id' => $ledger_id>0? $ledger_id : null,
            'note' => $note,
            'date' => $date,
            'amount' => $amount,
            'link' => $link,
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
        wp_redirect(admin_url('admin.php?page=camp-manager-ledger&success=1'));
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
            // floatval() returns a float, so compare numerically (=== 0 was never true).
            if (abs($amount) < 0.00001 && $type === '') {
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
                'link'   => $data['link'],
                'season' => CampManagerSeason::selected(),
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
                'link'   => $data['link'],
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

    // The camp's funds when the first season on record began. Each later season starts with
    // whatever the earlier seasons left in the account, so the money carries over.
    const OPENING_BALANCE = 2037.80;

    public function startingBalance(?int $season = null)
    {
        global $wpdb;
        $season = $season ?? CampManagerSeason::selected();
        $earlier = $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger WHERE season < %d", $season));
        return self::OPENING_BALANCE + (float) $earlier;
    }

    public function totalMoneyIn()
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger WHERE amount > 0 AND season = %d", CampManagerSeason::selected())) ?: 0;
    }


    public function totalMoneyOut()
    {
        global $wpdb;
        return abs($wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger WHERE amount < 0 AND season = %d", CampManagerSeason::selected())) ?: 0);
    }

    /** Sum of the line items of one type (or several) on this season's ledger entries. */
    private function sumLineItems(array $types)
    {
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($types), '%s'));
        $sql = "SELECT SUM(li.amount)
                FROM {$wpdb->prefix}mf_ledger_line_items li
                INNER JOIN {$wpdb->prefix}mf_ledger l ON l.id = li.ledger_id
                WHERE l.season = %d AND li.type IN ($placeholders)";
        return $wpdb->get_var($wpdb->prepare($sql, array_merge([CampManagerSeason::selected()], $types))) ?: 0;
    }

    public function totalDonations()
    {
        return $this->sumLineItems(['Donation']);
    }

    public function totalAssetsSold()
    {
        return $this->sumLineItems(['Sold Asset']);
    }

    public function totalCampDues()
    {
        return $this->sumLineItems(['Camp Dues', 'Partial Camp Dues']);
    }

    public function sumUserCampDues($cmid)
    {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT SUM(amount) FROM {$wpdb->prefix}mf_ledger_line_items WHERE cmid = %d AND (type = 'Camp Dues' OR type = 'Partial Camp Dues')", $cmid)) ?: 0;
    }
}
