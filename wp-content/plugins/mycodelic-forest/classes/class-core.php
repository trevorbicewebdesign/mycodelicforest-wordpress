<?php

class MycodelicForestCore {
    public function __construct()
    {
 
    }

    public function init()
    {
        // Activation hooks must reference the main plugin file, not this class file.
        register_activation_hook(MYCO_CORE_PLUGIN_FILE, function() {
            flush_rewrite_rules();
        });
        
        register_deactivation_hook(MYCO_CORE_PLUGIN_FILE, function() {
            flush_rewrite_rules();
        });        

        // enqueue styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        
    }

    // include mycodelic-forest/assets/css/messages.css
    public function enqueue_styles()
    {   
        wp_enqueue_style(
            'mycodelic-forest-messages',
            plugins_url('assets/css/messages.css', MYCO_CORE_PLUGIN_FILE),
            [],
            MYCO_CORE_VERSION
        );
    }

}
