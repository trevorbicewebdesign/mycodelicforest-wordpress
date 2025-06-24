<?php

class CampManagerReceipts

{
    public function __construct()
    {

    }

    public function init()
    {
        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'Camp Manager',
                'Camp Manager',
                'manage_options',
                'camp-manager',
                array($this, 'camp_manager_dashboard'),
                'dashicons-admin-site',
                6
            );

            // Submenu: Receipts
            add_submenu_page(
                'camp-manager',
                'Receipts',
                'Receipts',
                'manage_options',
                'camp-manager-receipts',
                array($this, 'camp_manager_receipts_page')
            );

            // Submenu: Add Receipt
            add_submenu_page(
                'camp-manager',
                'Add Receipt',
                'Add Receipt',
                'manage_options',
                'camp-manager-add-receipt',
                array($this, 'camp_manager_add_receipt_page')
            );
        });
    }

      

    // Dashboard callback (optional)
    public function camp_manager_dashboard() {
        echo '<div class="wrap"><h1>Camp Manager Dashboard</h1></div>';
    }

    // Receipts page callback
    public function camp_manager_receipts_page() {
        echo '<div class="wrap"><h1>Receipts</h1>';
        // Here you can list receipts, use your CampManagerReceipts class, etc.
        echo '</div>';
    }

    public function camp_manager_add_receipt_page() {
        echo '<div class="wrap"><h1>Add New Receipt</h1>';

        if (isset($_POST['camp_manager_add_receipt'])) {
            $data = [
                'date' => sanitize_text_field($_POST['receipt_date']),
                'description' => sanitize_text_field($_POST['receipt_description']),
                'amount' => floatval($_POST['receipt_amount']),
            ];

            $result = $this->add_receipt($data);
            if ($result) {
                echo '<div class="notice notice-success"><p>Receipt added successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to add receipt.</p></div>';
            }
        }

        ?>
        <form method="post">
            <table class="form-table">
                <tr>
                    <th><label for="receipt_date">Date</label></th>
                    <td><input type="date" name="receipt_date" required /></td>
                </tr>
                <tr>
                    <th><label for="receipt_description">Description</label></th>
                    <td><input type="text" name="receipt_description" required /></td>
                </tr>
                <tr>
                    <th><label for="receipt_amount">Amount</label></th>
                    <td><input type="number" step="0.01" name="receipt_amount" required /></td>
                </tr>
            </table>
            <p><input type="submit" name="camp_manager_add_receipt" class="button-primary" value="Add Receipt"></p>
        </form>
        </div>
        <?php
    }


    public function get_receipt($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';
        $sql = "SELECT * FROM $table_name WHERE id = %d";
        $receipt = $wpdb->get_row($wpdb->prepare($sql, $id));
        return $receipt;
    }

    public function get_receipt_items($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipt_items';
        $sql = "SELECT * FROM $table_name WHERE receipt_id = %d";
        $items = $wpdb->get_results($wpdb->prepare($sql, $id));
        return $items;
    }
    public function insert_receipt(
        string $store,
        string $date,
        float $subtotal,
        float $tax,
        float $shipping,
        float $total,
        array $items
    )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';
        $data = [
            // 'store'    => sanitize_text_field($store),
            'date'     => sanitize_text_field($date),
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'shipping' => $shipping,
            'total'    => $total
        ];
        $result = $wpdb->insert($table_name, $data);

        if( $result ) {
            $receipt_id = $wpdb->insert_id; // Get the last inserted ID
            // Optionally, you can also insert items related to this receipt
            if (is_array($items)) {
                foreach ($items as $item) {
                    $item_data = [
                        'receipt_id' => $receipt_id,
                        'name' => sanitize_text_field($item['name']),
                        'price' => floatval($item['price']),
                    ];
                    $this->insert_receipt_item($item_data);
                }
            }
        }
        else {
            throw new \Exception("Failed to insert receipt: " . $wpdb->last_error);
        }
        
        return $result;
    }
    public function insert_receipt_item($data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipt_items';
        $result = $wpdb->insert($table_name, $data);
        return $result;
    }



}