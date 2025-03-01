<?php
class MycodelicForestMessages
{
    public function __construct()
    {
        
    }

    public function init()
    {
        add_action('init', [$this, 'start_session']);
        add_filter('the_content', [$this, 'display_message']);
    }

    // Start PHP session if not started
    public function start_session()
    {
        if (!session_id()) {
            session_start();
        }
    }

    // Set a session message
    public static function set_message($message, $type = 'success')
    {
        $_SESSION['mycodelic_message'] = [
            'content' => $message,
            'type' => $type
        ];
    }


    // Display the stored message above the content
    public function display_message($content)
    {

        $message_data = $_SESSION['mycodelic_message'];

        if(empty($message_data)) {
            return $content;
        }

        // Message styles based on type
        $styles = [
            'success' => 'background: #d4edda; border: 1px solid #c3e6cb; color: #155724;',
            'error'   => 'background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24;',
            'warning' => 'background: #fff3cd; border: 1px solid #ffeeba; color: #856404;',
            'info'    => 'background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460;',
        ];

        // Get the appropriate style
        $style = $styles[$message_data['type']] ?? $styles['info'];

        // Build the message HTML
        $message_html = '<div class="notice" style="padding: 10px; margin-bottom: 10px; ' . esc_attr($style) . ' text-align: center;">
            ' . esc_html($message_data['content']) . '
        </div>';

        // Clear message after displaying
        unset($_SESSION['mycodelic_message']);
        return $message_html . $content;

    }
}