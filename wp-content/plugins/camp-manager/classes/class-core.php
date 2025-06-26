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

    public function getItemCategories(): array
    {
        // Need to get the categories from the database
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_category";
        $query = "SELECT * FROM $table";
        $categories = $wpdb->get_results($query, ARRAY_A);
        return $categories ? $categories : [];
    }


}
