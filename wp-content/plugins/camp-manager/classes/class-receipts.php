<?php

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
        add_action('admin_post_camp_manager_upload_receipt', array($this, 'handle_receipt_upload'));
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
                'View Receipts',
                'View Receipts',
                'manage_options',
                'camp-manager-view-receipts',
                [$this, 'receipts_page']
            );
            
            add_submenu_page(
                'camp-manager',
                'Upload Receipt',
                'Upload Receipt',
                'manage_options',
                'camp-manager-upload-receipt',
                [$this, 'upload_receipt_page']
            );

            // need a new hidden menu page for the edit receipt page
            add_submenu_page(
                'camp-manager',
                'Edit Receipt',
                'Edit Receipt',
                'manage_options',
                'camp-manager-edit-receipt',
                [$this, 'edit_receipt_page']
            );
        });
    }

    public function edit_receipt_page()
    {
        $receipt_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$receipt_id) {
            wp_die('Invalid receipt ID.');
        }

        $receipt = $this->get_receipt($receipt_id);
        if (!$receipt) {
            wp_die('Receipt not found.');
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Edit Receipt</h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="camp_manager_save_receipt">
                <input type="hidden" name="receipt_id" value="<?php echo esc_attr($receipt_id); ?>">
                <table class="form-table">
                    <tr>
                        <th><label for="store">Store</label></th>
                        <td><input type="text" name="store" class="regular-text" value="<?php echo esc_attr(str_replace('/', '', $receipt->store)); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="date">Date</label></th>
                        <td><input type="date" name="date" value="<?php echo esc_attr(date('Y-m-d', strtotime($receipt->date))); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="subtotal">Subtotal</label></th>
                        <td><input type="number" step="0.01" name="subtotal" value="<?php echo esc_attr($receipt->subtotal); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="tax">Tax</label></th>
                        <td><input type="number" step="0.01" name="tax" value="<?php echo esc_attr($receipt->tax); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="shipping">Shipping</label></th>
                        <td><input type="number" step="0.01" name="shipping" value="<?php echo esc_attr($receipt->shipping); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="total">Total</label></th>
                        <td><input type="number" step="0.01" name="total" value="<?php echo esc_attr($receipt->total); ?>"></td>
                    </tr>
                </table>

                <?php submit_button('Save Receipt'); ?>
            </form>
        </div>
        <?php
    }

    public function receipts_page() {
        $table = new CampManagerReceiptsTable();
        $table->process_bulk_action();
        $table->prepare_items();
        ?>
        <style>
            .wp-list-table .column-store       { width: 40%; }
            .wp-list-table .column-date        { width: 15%; }
            .wp-list-table .column-total       { width: 10%; text-align: right; }
            .wp-list-table .column-subtotal    { width: 10%; text-align: right; }
            .wp-list-table .column-tax         { width: 10%; text-align: right; }
            .wp-list-table .column-shipping    { width: 15%; text-align: right; }
        </style>
        <div class="wrap">
            <h1 class="wp-heading-inline">Receipts</h1>
            <a href="<?php echo admin_url('admin.php?page=camp-manager-upload-receipt'); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <form method="post">
                <?php
                $table->display();
                ?>
            </form>
        </div>
        <?php
    }

    public function handle_receipt_save() {
        try {
            $this->insert_receipt(
                sanitize_text_field($_POST['store']),
                sanitize_text_field($_POST['date']),
                floatval($_POST['subtotal']),
                floatval($_POST['tax']),
                floatval($_POST['shipping']),
                floatval($_POST['total']),
                $_POST['items'],
                $_POST['raw']
            );

            wp_redirect(admin_url('admin.php?page=camp-manager-view-receipts&receipt_submitted=1'));
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-upload-receipt&error=' . urlencode($e->getMessage())));
        }
        exit;

    }

     public function handle_receipt_upload() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        if (!isset($_FILES['receipt_image'])) {
            wp_redirect(admin_url('admin.php?page=camp-manager-upload-receipt&error=no_image'));
            exit;
        }

        $image_path = $_FILES['receipt_image']['tmp_name'];
        $base64_image = base64_encode(file_get_contents($image_path));

        $response = $this->CampManagerChatGPT->analyze_receipt_with_gpt($base64_image);
        $parsed = $this->CampManagerChatGPT->extract_json_from_gpt_response($response);
        set_transient('camp_manager_last_receipt_data', $parsed, 60);
        wp_redirect(admin_url('admin.php?page=camp-manager-upload-receipt'));
        exit;
    }

    // Dashboard callback (optional)
    public function camp_manager_dashboard() {
        echo '<div class="wrap"><h1>Camp Manager Dashboard</h1></div>';
    }

    
    public function upload_receipt_page() {
        if (isset($_POST['receipt_submitted'])) {
            echo '<div class="notice notice-success"><p>✅ Receipt submitted successfully!</p></div>';
            echo '<h2>Submitted Data</h2>';
            echo '<pre style="background: #eef; padding: 1em;">';
            print_r($_POST);
            echo '</pre>';
        }

        $response_data = get_transient('camp_manager_last_receipt_data');
        // delete_transient('camp_manager_last_receipt_data');

        $categories = ['power', 'sojourner', 'sound', 'misc'];
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Upload Receipt</h1>

            <!-- Upload Form -->
            <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="camp_manager_upload_receipt">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="receipt_image">Receipt Image</label></th>
                        <td><input type="file" name="receipt_image" accept="image/*" required></td>
                    </tr>
                </table>
                <?php submit_button('Analyze Receipt'); ?>
            </form>

            <?php if (!empty($response_data) && is_array($response_data)): ?>
                <hr style="margin: 40px 0;">
                <h2>Review & Submit Receipt</h2>

                <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="camp_manager_save_receipt">
                    <input type="hidden" name="receipt_submitted" value="1">
                    <input type='hidden' name='raw' value='<?php echo esc_attr(json_encode($response_data)); ?>'>

                    <table class="form-table">
                        <tr>
                            <th><label for="store">Store</label></th>
                            <td><input type="text" name="store" class="regular-text" value="<?php echo esc_attr($response_data['store'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="date">Date</label></th>
                            <td><input type="date" name="date" value="<?php echo esc_attr($response_data['date'] ?? ''); ?>"></td>
                        </tr>
                    </table>

                    <h2 style="margin-top: 40px;">Items</h2>
                    <table class="widefat striped" style="margin-bottom: 30px; table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: right;">Item Name</th>
                                <th style="text-align: right;">Price</th>
                                <th style="text-align: right;">Qty</th>
                                <th style="text-align: right;">Subtotal</th>
                                <th style="text-align: right;">Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($response_data['items'] ?? [] as $i => $item): ?>
                                <tr>
                                    <td style="text-align: right;">
                                        <input type="text" name="items[<?php echo $i; ?>][name]" value="<?php echo esc_attr($item['name'] ?? ''); ?>" style="width: 100%;" />
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="text" name="items[<?php echo $i; ?>][price]" value="<?php echo esc_attr($item['price'] ?? ''); ?>" style="width: 100%;" />
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="number" name="items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr($item['quantity'] ?? 1); ?>" style="width: 100%;" />
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="text" name="items[<?php echo $i; ?>][subtotal]" value="<?php echo esc_attr($item['subtotal'] ?? ''); ?>" style="width: 100%;" />
                                    </td>
                                    <td style="text-align: right;">
                                        <?php $categories = $this->core->getItemCategories(); ?>
                                        <select name="items[<?php echo $i; ?>][category]" style="width: 100%;">
                                            <option value="">Please select a category</option>
                                            <?php foreach ($categories as $catKey => $catLabel): ?>
                                                <option value="<?php echo esc_attr($catKey); ?>"
                                                    <?php selected(($item['category'] ?? '') === $catKey); ?>>
                                                    <?php echo esc_html(ucfirst($catKey)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <table style="width: 100%; max-width: 600px; margin-left: auto; font-size: 1.1em;">
                        <tr>
                            <td style="text-align: right; padding: 8px;"><strong>Subtotal:</strong></td>
                            <td style="text-align: right; width: 150px;">
                                <input type="text" name="subtotal" value="<?php echo esc_attr($response_data['subtotal'] ?? ''); ?>" class="small-text" style="width: 100%;" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 8px;"><strong>Tax:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="tax" value="<?php echo esc_attr($response_data['tax'] ?? ''); ?>" class="small-text" style="width: 100%;" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 8px;"><strong>Shipping:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="shipping" value="<?php echo esc_attr($response_data['shipping'] ?? ''); ?>" class="small-text" style="width: 100%;" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 8px;"><strong>Total:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="total" value="<?php echo esc_attr($response_data['total'] ?? ''); ?>" class="small-text" style="width: 100%;" />
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 30px;">
                        <?php submit_button('Save Receipt'); ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    // Receipts page callback
    public function camp_manager_receipts_page() {
        echo '<div class="wrap"><h1>Receipts</h1><a href="/wp-admin/admin.php?page=camp-manager-upload-receipt" class="page-title-action">Upload Receipt</a>';

        $receipts = $this->get_receipts();

        if ($receipts && count($receipts) > 0) {
            echo '<div class="wp-list-table widefat fixed striped posts">';
            echo '<table>';
            echo '<thead>
                    <tr>
                        <th scope="col" class="manage-column column-title column-primary">Store</th>
                        <th scope="col" class="manage-column">Date</th>
                        <th scope="col" class="manage-column">Subtotal</th>
                        <th scope="col" class="manage-column">Tax</th>
                        <th scope="col" class="manage-column">Shipping</th>
                        <th scope="col" class="manage-column">Total</th>
                    </tr>
                  </thead>
                  <tbody>';
            foreach ($receipts as $receipt) {
                echo '<tr>';
                echo '<td class="column-title has-row-actions column-primary" data-colname="Store">'
                    . '<strong>' . esc_html($receipt->store) . '</strong>'
                    . '<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>'
                    . '</td>';
                echo '<td data-colname="Date">' . esc_html($receipt->date) . '</td>';
                echo '<td data-colname="Subtotal">' . esc_html($receipt->subtotal) . '</td>';
                echo '<td data-colname="Tax">' . esc_html($receipt->tax) . '</td>';
                echo '<td data-colname="Shipping">' . esc_html($receipt->shipping) . '</td>';
                echo '<td data-colname="Total">' . esc_html($receipt->total) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table></div>';
        } else {
            echo '<p>No receipts found.</p>';
        }
        echo '</div>';
    }

    public function get_receipts()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';
        $sql = "SELECT * FROM $table_name";
        $receipts = $wpdb->get_results($sql);
        return $receipts;
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

    public function get_receipt_items($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipt_items';
        $sql = "SELECT * FROM $table_name WHERE receipt_id = %d";
        $items = $wpdb->get_results($wpdb->prepare($sql, $id));
        return $items;
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

    public function insert_receipt(
        string $store,
        string $date,
        float $subtotal,
        float $tax,
        float $shipping,
        float $total,
        array $items,
        string $raw
    )
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipts';
        $data = [
            'store'    => sanitize_text_field($store),
            'date'     => sanitize_text_field($date),
            'subtotal' => $subtotal,
            'tax'      => $tax,
            'shipping' => $shipping,
            'total'    => $total,
            'raw'      => $raw // Store raw items data as JSON    
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
        
        return ['id'=>$receipt_id];
    }
    public function insert_receipt_item($data)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mf_receipt_items';
        $result = $wpdb->insert($table_name, $data);
        return $result;
    }



}