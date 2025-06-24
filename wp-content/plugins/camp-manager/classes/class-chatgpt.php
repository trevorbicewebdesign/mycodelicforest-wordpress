<?php

class CampManagerGPT {
    private $api_key;

    public function __construct() {
        $this->api_key = defined('CAMP_MANAGER_OPENAI_API_KEY') ? CAMP_MANAGER_OPENAI_API_KEY : '';
    }


    public function init() {
        add_action('admin_menu', function () {
            add_submenu_page(
                'camp-manager',
                'Upload Receipt',
                'Upload Receipt',
                'manage_options',
                'camp-manager-upload-receipt',
                [$this, 'upload_receipt_page']
            );
        });

        add_action('admin_post_camp_manager_upload_receipt', [$this, 'handle_receipt_upload']);
        add_action('admin_post_camp_manager_save_receipt', [$this, 'handle_receipt_save']);
    }

    public function insert_receipt(
        string $store,
        string $date,
        float $subtotal,
        float $tax,
        float $shipping,
        float $total,
        array $items
    ): int {
        global $wpdb;

        if (empty($store) || empty($date) || empty($items)) {
            throw new Exception('Missing required receipt data or items.');
        }

        $receipt_inserted = $wpdb->insert("{$wpdb->prefix}mf_receipts", [
            'store'     => $store,
            'date'      => $date,
            'subtotal'  => $subtotal,
            'tax'       => $tax,
            'shipping'  => $shipping,
            'total'     => $total,
        ]);

        if ($receipt_inserted === false) {
            throw new Exception('Failed to insert receipt.');
        }

        $receipt_id = $wpdb->insert_id;
        $item_categories = $this->getItemCategories();
        $errors = [];

        foreach ($items as $item) {
            $name     = sanitize_text_field($item['name'] ?? '');
            $price    = floatval($item['price'] ?? 0);
            $quantity = floatval($item['quantity'] ?? 1);
            $item_subtotal = floatval($item['subtotal'] ?? 0);
            $category = sanitize_text_field($item['category'] ?? '');

            if (!$name || $price < 0 || $quantity <= 0) {
                $errors[] = $name ?: 'Unnamed item';
                continue;
            }

            $category_id = array_search($category, array_keys($item_categories));
            if ($category_id === false) {
                $category_id = null;
            }

            $item_inserted = $wpdb->insert("{$wpdb->prefix}mf_receipt_items", [
                'receipt_id' => $receipt_id,
                'name'       => $name,
                'price'      => $price,
                'quantity'   => $quantity,
                'subtotal'   => $item_subtotal,
                'tax'        => 0,
                'total'      => $item_subtotal,
            ]);

            if ($item_inserted === false) {
                $errors[] = $name;
            }
        }

        if (!empty($errors)) {
            throw new Exception('Item insert failed: ' . implode(', ', $errors));
        }

        return $receipt_id;
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
                $_POST['items']
            );

            wp_redirect(admin_url('admin.php?page=camp-manager-upload-receipt&saved=1'));
        } catch (Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-upload-receipt&error=' . urlencode($e->getMessage())));
        }
        exit;

    }


    private function extract_json_from_gpt_response($response) {
        if (!is_array($response) || !isset($response['choices'][0]['message']['content'])) {
            return ['error' => 'Invalid response structure'];
        }

        $content = $response['choices'][0]['message']['content'];

        // Strip markdown ```json ... ``` if present
        if (preg_match('/```json(.*?)```/s', $content, $matches)) {
            $json_string = trim($matches[1]);
        } else {
            $json_string = trim($content);
        }

        $data = json_decode($json_string, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'JSON decode failed: ' . json_last_error_msg()];
        }

        return $data;
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
        delete_transient('camp_manager_last_receipt_data');

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

                <form method="post">
                    <input type="hidden" name="receipt_submitted" value="1">

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
                                        <?php $categories = $this->getItemCategories(); ?>
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

        $response = $this->analyze_receipt_with_gpt($base64_image);
        $parsed = $this->extract_json_from_gpt_response($response);
        set_transient('camp_manager_last_receipt_data', $parsed, 60);
        wp_redirect(admin_url('admin.php?page=camp-manager-upload-receipt'));
        exit;
    }
    public function systemPrompt(): string
    {
        $categories = $this->getItemCategories();

        $prompt = "You are an API that extracts structured data from receipts and returns only valid JSON.\n\n";
        $prompt .= "For each line item, assign a category based on the list below:\n\n";

        foreach ($categories as $key => $desc) {
            $prompt .= "- **$key**: $desc\n";
        }

        $prompt .= <<<EOD

        Line Item Logic:
        - If the receipt shows both a quantity and a price, and the price is large (e.g. over \$5), treat the price as a **line total** (not unit price).
        - In that case, calculate unit price as: **unit price = price ÷ quantity** and round to 2 decimals.
        - Set both `price` and `subtotal` fields in the JSON:
        - `price`: unit price
        - `subtotal`: line total

        Return JSON in this format:

        {
            "store": "string",
            "date": "YYYY-MM-DD",
            "subtotal": "string",
            "tax": "string",
            "shipping": "string",
            "total": "string",
            "items": [
                {
                "name": "string",
                "price": "string",        // calculated unit price
                "quantity": number,
                "subtotal": "string",     // total for that item (from receipt)
                "category": "power | sojourner | sound | misc"
                }
            ]
        }

        ⚠️ Only output valid JSON. Do not include markdown, comments, or explanations.

        EOD;

        return $prompt;
    }

    public function getItemCategories(): array
    {
        return [
            'power' => 'Anything related to electricity generation or distribution (e.g. generators, cords, lights, solar, batteries)',
            'sojourner' => 'Items related to our school bus (maintenance, upgrades, fuel, storage, hardware)',
            'sound' => 'Audio/music/DJ gear (speakers, mixers, cables, microphones)',
            'shwag & print' => 'Merchandise, stickers, flyers, posters, etc.',
            'misc' => 'Doesn’t clearly fit the above categories',
        ];
    }


    private function analyze_receipt_with_gpt($base64_image) {
        $url = 'https://api.openai.com/v1/chat/completions';

        $payload = [
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt()],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'Here is the receipt:'],
                        ['type' => 'image_url', 'image_url' => [
                            'url' => 'data:image/jpeg;base64,' . $base64_image
                        ]]
                    ]
                ]
            ],
            'temperature' => 0.2,
        ];

        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ],
            'body' => json_encode($payload),
            'timeout' => 60,
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return $body;
    }
}
