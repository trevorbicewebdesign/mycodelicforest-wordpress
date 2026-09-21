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

        // Guard against Slider Revolution's broken admin-footer hook (see below).
        add_action('admin_footer', array($this, 'remove_broken_revslider_footer_hook'), 0);

    }

    /**
     * Slider Revolution (7.1.x) registers
     *   add_action('admin_footer', ['RevSliderAdmin', 'add_ajax_footer_functionality'])
     * from add_plugins_page_notices() whenever the plugin is unlicensed AND a newer
     * release is known, but that method does not exist, so wp-admin/plugins.php
     * dies with a TypeError. Remove the callback before WordPress reaches it.
     * Harmless once RevSlider is licensed or ships the method (method_exists check).
     */
    public function remove_broken_revslider_footer_hook()
    {
        if (class_exists('RevSliderAdmin') && !method_exists('RevSliderAdmin', 'add_ajax_footer_functionality')) {
            remove_action('admin_footer', array('RevSliderAdmin', 'add_ajax_footer_functionality'));
        }
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
