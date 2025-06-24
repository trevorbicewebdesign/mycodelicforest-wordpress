<?php

class CampManagerChatGPT {
    private $api_key;
    private $core;
    public function __construct(CampManagerCore $core) 
    {
        $this->api_key = defined('CAMP_MANAGER_OPENAI_API_KEY') ? CAMP_MANAGER_OPENAI_API_KEY : '';
        $this->core = $core;
    }

    public function init() {
        
    }

    public function extract_json_from_gpt_response($response) {

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

    

   
    public function systemPrompt(): string
    {
        $categories = $this->core->getItemCategories();

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

    public function analyze_receipt_with_gpt($base64_image) {
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
