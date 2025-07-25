<?php

class CampManagerBudgets {

    public function __construct()
    {
        // Constructor logic if needed
    }

    public function init()
    {
        // add action camp_manager_save_budget_item
        add_action('admin_post_camp_manager_save_budget_item', [$this, 'handle_budget_item_save']);
        add_action('admin_post_camp_manager_save_budget_category', [$this, 'handle_budget_category_save']);
    }

    public function handle_budget_category_save()
    {
        // Handle saving a budget category from the admin post request
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $this->upsertBudgetCategory(
                sanitize_text_field($_POST['budget_category_name']),
                isset($_POST['budget_category_description']) ? sanitize_textarea_field($_POST['budget_category_description']) : '',
                isset($_POST['budget_category_id']) ? (int)$_POST['budget_category_id'] : null
            );
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-budget&error=' . urlencode($e->getMessage())));
            exit;
        }
        wp_redirect(admin_url('admin.php?page=camp-manager-budget-categories&success=category_added'));
        exit;
    }

    public function handle_budget_item_save()
    {
        // Handle saving a budget item from the admin post request
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $this->updateBudgetItem(
                isset($_POST['budget_item_id']) ? (int)$_POST['budget_item_id'] : 0,
                isset($_POST['budget_item_category']) ? (int)$_POST['budget_item_category'] : 0,
                isset($_POST['budget_item_name']) ? sanitize_text_field($_POST['budget_item_name']) : '',
                isset($_POST['budget_item_price']) ? floatval($_POST['budget_item_price']) : 0.0,
                isset($_POST['budget_item_quantity']) ? floatval($_POST['budget_item_quantity']) : 0.0,
                isset($_POST['budget_item_subtotal']) ? floatval($_POST['budget_item_subtotal']) : 0.0,
                isset($_POST['budget_item_total']) ? floatval($_POST['budget_item_total']) : 0.0,
                isset($_POST['budget_item_priority']) ? (int)$_POST['budget_item_priority'] : 0,
                isset($_POST['budget_item_link']) ? sanitize_text_field($_POST['budget_item_link']) : null,
                isset($_POST['budget_item_tax']) ? floatval($_POST['budget_item_tax']) : 0.0
            );
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-budgets&error=' . urlencode($e->getMessage())));
            exit;
        }
        wp_redirect(admin_url('admin.php?page=camp-manager-budgets&success=item_added'));
        exit;
    }   

    public function getBudgetCategory($category_id): ?object
    {
        // Should get a budget category from the database
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_category";
        $query = $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $category_id);
        return $wpdb->get_row($query);
    }

    public function deleteBudgetCategory($category_id): bool
    {
        // Should delete a budget category from the database
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_category";
        $result = $wpdb->delete($table, ['id' => (int) $category_id]);
        return (bool) $result;
    }

    public function getBudgetItems($category_id = null): array
    {
        // Should get all budget items for a category or all if no category is specified
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        $query = "SELECT * FROM $table";
        if ($category_id !== null) {
            $query .= $wpdb->prepare(" WHERE category_id = %d", $category_id);
        }
        return $wpdb->get_results($query, ARRAY_A);
    }

    public function getBudgetItem($budget_item_id): ?object
    {
        // Should get a budget item from the database
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        $query = $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $budget_item_id);
        return $wpdb->get_row($query);
    }

    // this should take the total receipts for a category and return the remaining budget
    // that way an administrator can see how much is left in the budget for that category
    public function get_remaining_budget_by_category($category_id, $priority): float
    {
        // Should get the remaining budget for a category
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        $query = $wpdb->prepare(
            "SELECT SUM(total) FROM $table WHERE category_id = %d AND priority = %d",
            $category_id, $priority
        );
        $total_budget = $wpdb->get_var($query);
        $total_budget = $total_budget !== null ? (float) $total_budget : 0.0;

        // now get the total receipts that are accounted for in this category (budget_item_id)
        $receipts_table = "{$wpdb->prefix}mf_receipt_items";
        $receipt_query = $wpdb->prepare(
            "SELECT SUM(total) FROM $receipts_table WHERE budget_item_id IN (SELECT id FROM $table WHERE category_id = %d AND priority = %d)",
            $category_id, $priority
        );
        $total_receipts = $wpdb->get_var($receipt_query);
        $total_receipts = $total_receipts !== null ? (float) $total_receipts : 0.0;

        // If no budget items found, return 0.0
        return $total_budget - $total_receipts;
    }

    // Should insert or update a budget category
    public function upsertBudgetCategory($name, $description = '', $category_id = null): int
    {
        // Insert or update a budget category in the database
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_category";
        $data = [
            'name' => sanitize_text_field($name),
            'description' => sanitize_textarea_field($description),
        ];

        if ($category_id && $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $category_id))) {
            // Update existing category
            $wpdb->update($table, $data, ['id' => (int)$category_id]);
            return (int)$category_id;
        } else {
            // Insert new category
            $wpdb->insert($table, $data);
            return (int)$wpdb->insert_id;
        }
    }

    // Insert or update Budget Item
    public function updateBudgetItem($budget_item_id, $category_id, $name, $price, $quantity, $subtotal, $total, $priority = 0, $link = null, $tax = 0.0): bool
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        $data = [
            'category_id' => (int) $category_id,
            'name' => sanitize_text_field($name),
            'price' => (float) $price,
            'quantity' => (float) $quantity,
            'subtotal' => (float) $subtotal,
            'tax' => (float) $tax,
            'total' => (float) $total,
            'priority' => (int) $priority,
            'link' => $link ? sanitize_text_field($link) : null,
        ];

        if ($budget_item_id && $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $budget_item_id))) {
            // Update existing item
            $result = $wpdb->update($table, $data, ['id' => (int)$budget_item_id]);
        } else {
            // Insert new item
            $result = $wpdb->insert($table, $data);
        }
        return (bool)$result;
    }

    public static function getPriorityTotal($category_id, $priority): float
    {
        // Should get all budget items for a category and priority
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        $query = $wpdb->prepare(
            "SELECT SUM(price * quantity) FROM $table WHERE category_id = %d AND priority = %d",
            $category_id, $priority
        );
        $total = $wpdb->get_var($query);

        // If the priority is 1 we should also include all the receipts
        /*
        if ($priority == 1) {
            // Get all the reipt items for this category
            $receipts_table = "{$wpdb->prefix}mf_receipt_items";
            $receipt_query = $wpdb->prepare(
                "SELECT SUM(total) FROM $receipts_table WHERE category_id = %d",
                $category_id
            );
            $receipt_total = $wpdb->get_var($receipt_query);
            $total += $receipt_total ? (float) $receipt_total : 0.0;
        }
        */


        return $total ? (float) $total : 0.0;
    }


}