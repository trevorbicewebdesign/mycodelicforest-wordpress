<?php

class CampManagerCore {
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
        
    }

}
