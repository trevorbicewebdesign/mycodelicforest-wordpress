<?php

class CampManagerBudgets {

    public function __construct()
    {
        // Constructor logic if needed
    }

    public function init()
    {
        // Initialization logic for the budgets
        // This could include setting up database connections, loading necessary libraries, etc.
    }

    public static function getPriortyTotal($category_id, $priority): float
    {
        // Should get all budget items for a category and priority
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        $query = $wpdb->prepare(
            "SELECT SUM(price * quantity) FROM $table WHERE category_id = %d AND priority = %s",
            $category_id, $priority
        );
        $total = $wpdb->get_var($query);
        return $total ? (float) $total : 0.0;
    }
}