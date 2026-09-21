<?php
/**
 * One-shot notices shown above page content after a redirect.
 *
 * Messages are stored in a short-lived transient keyed by user ID instead of a PHP
 * session, so no session is started on every request (which broke page caching and
 * caused "headers already sent" warnings).
 */
class MycodelicForestMessages
{
    const TTL = 120; // seconds

    public function __construct()
    {

    }

    public function init()
    {
        add_filter('the_content', [$this, 'display_message']);
    }

    protected static function transient_key()
    {
        $user_id = get_current_user_id();
        return $user_id ? 'mycodelic_message_' . $user_id : '';
    }

    // Set a one-shot message for the current logged-in user.
    public static function set_message($message, $type = 'success')
    {
        $key = self::transient_key();
        if (!$key) {
            return;
        }
        set_transient($key, [
            'content' => $message,
            'type'    => $type,
        ], self::TTL);
    }

    // Display the stored message above the main content, once.
    public function display_message($content)
    {
        if (!is_main_query() || !in_the_loop()) {
            return $content;
        }

        $key = self::transient_key();
        if (!$key) {
            return $content;
        }

        $message_data = get_transient($key);
        if (empty($message_data) || !is_array($message_data)) {
            return $content;
        }

        // Message styles based on type
        $styles = [
            'success' => 'background: #d4edda; border: 1px solid #c3e6cb; color: #155724;',
            'error'   => 'background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;',
            'warning' => 'background: #fff3cd; border: 1px solid #ffeeba; color: #856404;',
            'info'    => 'background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460;',
        ];

        $style = $styles[$message_data['type'] ?? 'info'] ?? $styles['info'];

        $message_html = '<div class="notice" style="padding: 10px; margin-bottom: 10px; ' . esc_attr($style) . ' text-align: center;">
            ' . esc_html($message_data['content'] ?? '') . '
        </div>';

        // Clear message after displaying
        delete_transient($key);
        return $message_html . $content;
    }
}
