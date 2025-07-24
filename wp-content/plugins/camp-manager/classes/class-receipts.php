<?php

use PhpParser\Node\Expr\Isset_;

class CampManagerReceipts
{
    private $CampManagerChatGPT;
    private $core;
    public function __construct(CampManagerCore $core, CampManagerChatGPT $CampManagerChatGPT)
    {
        $this->CampManagerChatGPT = $CampManagerChatGPT;
        $this->core = $core;
    }

    public function init()
    {
        add_action('admin_post_camp_manager_save_receipt', array($this, 'handle_receipt_save'));
        add_action('admin_post_camp_manager_save_and_close_receipt', array($this, 'handle_receipt_save'));
        // camp_manager_analyze_receipt
        add_action('wp_ajax_camp_manager_analyze_receipt', [$this, 'handle_receipt_analyze']);

        add_action('wp_ajax_camp_manager_get_receipt_total', [$this, 'handle_get_receipt_total']);
    }

    
    public function handle_get_receipt_total()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $receipt_id = intval($_POST['receipt_id'] ?? 0);
        if (!$receipt_id) {
            wp_send_json_error('Invalid receipt ID');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mf_receipts';
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT total FROM $table WHERE id = %d",
            $receipt_id
        ));

        wp_send_json_success(['total' => round(floatval($total), 2)]);
    }

    public function getUnreimbursedReceipts()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';
        $sql = "SELECT * FROM {$table_name} WHERE reimbursed != 1 OR reimbursed IS NULL";
        $receipts = $wpdb->get_results($sql);

        return $receipts;
    }
    
    public function handle_receipt_save() {

        try {
            $receipt_id = isset($_POST['receipt_id']) ? intval($_POST['receipt_id']) : 0;

            // Validate required fields
            $required_fields = ['store', 'date', 'subtotal', 'tax', 'shipping', 'total', 'items'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field])) {
                    throw new Exception("Missing required field: " . $field);
                }
            }

            $store = sanitize_text_field($_POST['store']);
            $cmid = isset($_POST['purchaser']) ? intval($_POST['purchaser']) : 0;
            $date = sanitize_text_field($_POST['date']);
            $subtotal = floatval($_POST['subtotal']);
            $tax = floatval($_POST['tax']);
            $shipping = floatval($_POST['shipping']);
            $total = floatval($_POST['total']);
            $items = is_array($_POST['items']) ? $_POST['items'] : [];
            $raw = isset($_POST['raw']) ? sanitize_text_field($_POST['raw']) : '';
            $link = isset($_POST['link']) ? esc_url_raw($_POST['link']) : null;

            if ($receipt_id) {
                $receipt_id = $this->update_receipt(
                    $receipt_id,
                    $cmid,
                    $store,
                    $date,
                    $subtotal,
                    $tax,
                    $shipping,
                    $total,
                    $items,
                    $raw,
                    $link
                );
            } else {
                $receipt_id = $this->insert_receipt(
                    $cmid,
                    $store,
                    $date,
                    $subtotal,
                    $tax,
                    $shipping,
                    $total,
                    $items,
                    $raw,
                    $link
                );
            }

            wp_redirect(admin_url('admin.php?page=camp-manager-add-receipt&id=' . $receipt_id . '&receipt_submitted=1'));
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-actuals&error=' . urlencode($e->getMessage())));
        }
        exit;

    }

    public function handle_receipt_analyze() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Check if a file was uploaded via $_FILES
        if (!isset($_FILES['receipt_image']) || !is_uploaded_file($_FILES['receipt_image']['tmp_name'])) {
            wp_send_json_error(['error' => 'no_image']);
        }

        $image_path = $_FILES['receipt_image']['tmp_name'];
        $base64_image = base64_encode(file_get_contents($image_path));

        $response = $this->CampManagerChatGPT->analyze_receipt_with_gpt($base64_image);
        $parsed = $this->CampManagerChatGPT->extract_json_from_gpt_response($response);
        set_transient('camp_manager_last_receipt_data', $parsed, 60);

        wp_send_json_success($parsed);
    }

    public function get_total_receipts_by_category($category_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipt_items';
        $sql = "SELECT SUM(total) FROM $table_name WHERE category_id = %d";
        return (int) $wpdb->get_var($wpdb->prepare($sql, $category_id));
    }

    public function get_total_receipts()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';
        $sql = "SELECT SUM(total) FROM $table_name";
        return (int) $wpdb->get_var($sql);
    }
    public function get_receipts()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';
        $sql = "SELECT * FROM $table_name ORDER BY date DESC";
        $receipts = $wpdb->get_results($sql);
        return $receipts;
    }

    public function get_receipt_items($receipt_id = null)
    {
        global $wpdb;
        $table_name = "{$wpdb->prefix}mf_receipt_items";
        if ($receipt_id) {
            // Join with receipts table to order by receipt date
            $receipts_table = "{$wpdb->prefix}mf_receipts";
            $sql = $wpdb->prepare(
                "SELECT items.* 
                 FROM {$table_name} AS items
                 INNER JOIN {$receipts_table} AS receipts ON items.receipt_id = receipts.id
                 WHERE items.receipt_id = %d
                 ORDER BY receipts.date DESC, items.id ASC",
                $receipt_id
            );
        } else {
            // Join with receipts table to order by receipt date for all items
            $receipts_table = "{$wpdb->prefix}mf_receipts";
            $sql = "SELECT items.* 
                    FROM {$table_name} AS items
                    INNER JOIN {$receipts_table} AS receipts ON items.receipt_id = receipts.id
                    ORDER BY receipts.date DESC, items.id ASC";
        }
        $items = $wpdb->get_results($sql);
        return $items;
    }

    public function get_receipt($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';
        $sql = "SELECT * FROM $table_name WHERE id = %d";
        $receipt = $wpdb->get_row($wpdb->prepare($sql, $id));

        // get the receipt items and add them to the receipt object
        if ($receipt) {
            $receipt->items = $this->get_receipt_items($id);
        } else {
            return null; // or throw an exception
        }   

        return $receipt;
    }

    public function checkForDuplicateReceipt($store, $date, $total, $items = [])
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';

        // 1. Find receipts with same date and total
        $sql = "SELECT * FROM $table_name WHERE date = %s AND total = %f";
        $possible = $wpdb->get_results($wpdb->prepare($sql, $date, $total));

        // 2. If store is provided, filter further
        if ($store && !empty($possible)) {
            $possible = array_filter($possible, function($r) use ($store) {
                return strtolower($r->store) === strtolower($store);
            });
        }

        // 3. If items are provided, check for matching item names/totals
        if (!empty($items) && !empty($possible)) {
            $item_names = array_map(function($item) {
                return strtolower(trim($item['name'] ?? ''));
            }, $items);
            $item_subtotals = array_map(function($item) {
                return floatval($item['subtotal'] ?? 0);
            }, $items);

            $filtered = [];
            foreach ($possible as $receipt) {
                $receipt_items = $this->get_receipt_items($receipt->id);
                $receipt_item_names = array_map(function($item) {
                    return strtolower(trim($item->name ?? ''));
                }, $receipt_items);
                $receipt_item_subtotals = array_map(function($item) {
                    return floatval($item->subtotal ?? 0);
                }, $receipt_items);

                // Check for at least one matching item name and subtotal
                $name_match = array_intersect($item_names, $receipt_item_names);
                $subtotal_match = array_intersect($item_subtotals, $receipt_item_subtotals);

                if (!empty($name_match) || !empty($subtotal_match)) {
                    $filtered[] = $receipt;
                }
            }
            $possible = $filtered;
        }

        // Return possible duplicates (could be empty array)
        return array_values($possible);
    }

    public function update_receipt(
        int $receipt_id,
        $cmid,
        string $store,
        string $date,
        float $subtotal,
        float $tax,
        float $shipping,
        float $total,
        array $items,
        string $raw,
        string $link = null
    ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';

        $date = date("Y-m-d H:i:s", strtotime(sanitize_text_field($date)));

        $data = [
            'store'    => sanitize_text_field($store),
            'cmid'     => $cmid ? intval($cmid) : null,
            'date'     => $date,
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'shipping' => $shipping,
            'total'    => $total,
            'raw'      => $raw,
            'link'     => $link ? esc_url_raw($link) : null
        ];

        $where = ['id' => $receipt_id];
        $result = $wpdb->update($table_name, $data, $where);

        if ($result === false) {
            throw new \Exception("Failed to update receipt: " . $wpdb->last_error);
        }

        // Delete existing items
        $wpdb->delete($wpdb->prefix . 'mf_receipt_items', ['receipt_id' => $receipt_id]);

        // Re-insert updated items
        if (is_array($items)) {
            foreach ($items as $item) {
                // Skip empty rows (no name and no subtotal)
                $name = trim($item['name'] ?? '');
                $subtotal = floatval($item['subtotal'] ?? 0);
                if ($name === '' || $subtotal === 0) {
                    continue;
                }

                $item_data = [
                    'receipt_id' => $receipt_id,
                    'name' => sanitize_text_field($item['name']),
                    'price' => floatval($item['price']),
                    'quantity' => $item['quantity'],
                    'subtotal' => floatval($item['subtotal']),
                    'tax' => isset($item['tax']) ? floatval($item['tax']) : 0.0,
                    'shipping' => isset($item['shipping']) ? floatval($item['shipping']) : 0.0,
                    'total' => floatval($item['total'] ?? 0.0),
                    'category_id' => isset($item['category']) ? intval($item['category']) : null,
                    'link' => isset($item['link']) ? sanitize_text_field($item['link']) : null,
                    'budget_item_id' => isset($item['budget_item_id']) ? intval($item['budget_item_id']) : null,
                ];
                $receipt_item = $this->insert_receipt_item($item_data);
                if (!$receipt_item) {
                    throw new \Exception("Failed to insert receipt item: " . $wpdb->last_error);
                }
            }
        }

        return $receipt_id;
    }

    public function insert_receipt(
        $cmid,
        string $store,
        string $date,
        float $subtotal,
        float $tax,
        float $shipping,
        float $total,
        array $items,
        string $raw,
        string $link = null,
    )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';

        $date = date("Y-m-d H:i:s", strtotime(sanitize_text_field($date)));

        $data = [
            'cmid'     => $cmid ? intval($cmid) : null,
            'store'    => sanitize_text_field($store),
            'date'     => $date,
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'shipping' => $shipping,
            'total'    => $total,
            'raw'      => $raw,
            'link'     => $link ? esc_url_raw($link) : null,
        ];
        $result = $wpdb->insert($table_name, $data);

        if( $result ) {
            $receipt_id = $wpdb->insert_id; // Get the last inserted ID
            // Optionally, you can also insert items related to this receipt
            if (is_array($items)) {
                foreach ($items as $item) {
                    // Skip empty rows (no name and no subtotal)
                    $name = trim($item['name'] ?? '');
                    $subtotal = floatval($item['subtotal'] ?? 0);
                    if ($name === '' && $subtotal === 0) {
                        continue;
                    }

                    $item_data = [
                        'receipt_id' => $receipt_id,
                        'name' => sanitize_text_field($item['name']),
                        'price' => floatval($item['price']),
                        'budget_item_id' => isset($item['budget_item_id']) ? intval($item['budget_item_id']) : null,
                        'quantity' => $item['quantity'],
                        'subtotal' => floatval($item['subtotal']),
                        'tax' => isset($item['tax']) ? floatval($item['tax']) : 0.0,
                        'shipping' => isset($item['shipping']) ? floatval($item['shipping']) : 0.0,
                        'total' => floatval($item['total'] ?? 0.0),
                        'category_id' => isset($item['category']) ? intval($item['category']) : null,
                        'link' => isset($item['link']) ? sanitize_text_field($item['link']) : null
                    ];
                    $receipt_item = $this->insert_receipt_item($item_data);
                    if (!$receipt_item) {
                        throw new \Exception("Failed to insert receipt item: " . $wpdb->last_error);
                    }
                }
            }
        }
        else {
            throw new \Exception("Failed to insert receipt: " . $wpdb->last_error);
        }
        
        return $receipt_id;
    }
    public function insert_receipt_item($data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipt_items';
        $result = $wpdb->insert($table_name, $data);
        return $result;
    }



}