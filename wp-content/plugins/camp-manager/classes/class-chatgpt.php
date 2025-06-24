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

    public function upload_receipt_page() {
    $response_data = get_transient('camp_manager_last_receipt_data');
    delete_transient('camp_manager_last_receipt_data'); // Clean up after showing

    ?>
    <div class="wrap">
        <h1>Upload Receipt</h1>

        <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="camp_manager_upload_receipt">
            <input type="file" name="receipt_image" accept="image/*" required>
            <?php submit_button('Analyze Receipt'); ?>
        </form>

        <?php if ($response_data): ?>
            <h2>API Response</h2>
            <pre style="background: #f5f5f5; padding: 1em; border: 1px solid #ccc; overflow: auto;">
<?php echo esc_html(is_string($response_data) ? $response_data : print_r($response_data, true)); ?>
            </pre>

            <?php
            // Attempt to extract JSON even if it's fenced in markdown
            if (is_string($response_data)) {
                if (preg_match('/```json(.*?)```/s', $response_data, $matches)) {
                    $json_string = trim($matches[1]);
                } else {
                    $json_string = trim($response_data);
                }

                $decoded = json_decode($json_string, true);
                if ($decoded && json_last_error() === JSON_ERROR_NONE): ?>
                    <h2>Extracted Data</h2>
                    <pre style="background: #e7f7e7; padding: 1em; border: 1px solid #7ad07a; overflow: auto;"><?php echo esc_html(print_r($decoded, true)); ?></pre>
                <?php else: ?>
                    <p style="color: red;"><strong>⚠️ Failed to parse response as JSON.</strong></p>
                <?php endif;
            }
            ?>
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

        // Store in transient for next page load or redirect with query args
        set_transient('camp_manager_last_receipt_data', $response, 60);
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
