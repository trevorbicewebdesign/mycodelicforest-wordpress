<?php

class MycodelicForestCore {
    public function __construct()
    {
 
    }

    public function init()
    {
        register_activation_hook(__FILE__, function() {
            flush_rewrite_rules();
        });
        
        register_deactivation_hook(__FILE__, function() {
            flush_rewrite_rules();
        });        

        // enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        
    }

    // include mycodelic-forest/assets/css/messages.css
    public function enqueue_styles()
    {   
        wp_enqueue_style('mycodelic-forest-messages',   '/wp-content/plugins/mycodelic-forest/assets/css/messages.css');
    }

}
