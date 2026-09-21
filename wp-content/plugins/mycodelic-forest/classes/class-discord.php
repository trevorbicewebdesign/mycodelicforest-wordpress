<?php

class MycodelicForestDiscord
{
    public function __construct()
    {

    }

    public function init()
    {
      
    }

    /**
     * Post a message to a Discord webhook.
     *
     * @param string $message
     * @param string $channel  'announcements' or 'announcement-test' (default).
     * @return string|WP_Error Response body, or WP_Error on failure / missing webhook.
     */
    public function sendMessage($message, $channel = 'announcement-test')
    {
        $channels = [
            'announcements'     => getenv('DISCORD_CHANNEL_ANNOUNCEMENT'),
            'announcement-test' => getenv('DISCORD_CHANNEL_ANNOUNCEMENT_TEST'),
        ];

        $url = $channels[$channel] ?? '';
        if (empty($url)) {
            return new WP_Error('discord_no_webhook', 'No Discord webhook configured for channel: ' . $channel);
        }

        $response = wp_remote_post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode(['content' => $message]),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return wp_remote_retrieve_body($response);
    }
}
