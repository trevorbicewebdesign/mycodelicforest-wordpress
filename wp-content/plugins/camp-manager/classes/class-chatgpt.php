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
                    <table class="widefat striped" style="margin-bottom: 30px;">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Item Name</th>
                                <th style="width: 15%;">Price</th>
                                <th style="width: 15%;">Quantity</th>
                                <th style="width: 20%;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($response_data['items'] ?? [] as $i => $item): ?>
                                <tr>
                                    <td>
                                        <input type="text" name="items[<?php echo $i; ?>][name]" value="<?php echo esc_attr($item['name'] ?? ''); ?>" class="regular-text" />
                                    </td>
                                    <td>
                                        <input type="text" name="items[<?php echo $i; ?>][price]" value="<?php echo esc_attr($item['price'] ?? ''); ?>" class="small-text" />
                                    </td>
                                    <td>
                                        <input type="number" name="items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr($item['quantity'] ?? 1); ?>" class="small-text" />
                                    </td>
                                    <td>
                                        <input type="text" name="items[<?php echo $i; ?>][subtotal]" value="<?php echo esc_attr($item['subtotal'] ?? ''); ?>" class="small-text" />
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Totals aligned to right -->
                    <table style="width: 100%; max-width: 600px; margin-left: auto;">
                        <tr>
                            <td style="text-align: right; padding: 5px;"><strong>Subtotal:</strong></td>
                            <td style="text-align: right; width: 120px;">
                                <input type="text" name="subtotal" value="<?php echo esc_attr($response_data['subtotal'] ?? ''); ?>" class="small-text" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 5px;"><strong>Tax:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="tax" value="<?php echo esc_attr($response_data['tax'] ?? ''); ?>" class="small-text" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 5px;"><strong>Shipping:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="shipping" value="<?php echo esc_attr($response_data['shipping'] ?? ''); ?>" class="small-text" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 5px;"><strong>Total:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="total" value="<?php echo esc_attr($response_data['total'] ?? ''); ?>" class="small-text" />
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

    public function systemPrompt()
    {
        return <<<EOT
    You are an API that extracts structured data from receipts and replies with **only valid JSON**.

    Return an object with this exact format:

    {
    "store": "string",
    "date": "YYYY-MM-DD",
    "subtotal": "string (e.g. '123.45')",
    "tax": "string (e.g. '5.00')",
    "shipping": "string (e.g. '10.00')",
    "total": "string (e.g. '138.45')",
    "items": [
        {
        "name": "string",
        "price": "string (e.g. '54.59')",
        "quantity": number (e.g. 1),
        "subtotal": "string (e.g. '54.59')"
        }
    ]
    }

    ⚠️ Only output valid JSON. No extra comments, explanations, or markdown formatting.
    EOT;
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
