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
    }

    public function handle_budget_item_save()
    {
        // Handle saving a budget item from the admin post request
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $this->insertBudgetItem(
                (int)$_POST['budget_item_category'],
                sanitize_text_field($_POST['name']),
                floatval($_POST['price']),
                floatval($_POST['quantity']),
                isset($_POST['priority']) ? (int)$_POST['priority'] : 0,
                isset($_POST['link']) ? sanitize_text_field($_POST['link']) : null,
                floatval($_POST['tax'])
            );
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-budget&error=' . urlencode($e->getMessage())));
            exit;
        }
        wp_redirect(admin_url('admin.php?page=camp-manager-budget&success=item_added'));
        exit;
    }   

    public function insertBudgetItem($category_id, $name, $price, $quantity, $priority = 0, $link = null, $tax = 0.0): int
    {
        // Should insert a budget item into the database
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        $price = (float) $price;
        $quantity = (float) $quantity;
        $tax = (float) $tax;
        $subtotal = $price * $quantity;
        $total = $subtotal + $tax;

        $data = [
            'category_id' => (int) $category_id,
            'name' => sanitize_text_field($name),
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'priority' => (int) $priority,
            'link' => $link ? sanitize_text_field($link) : null,
        ];
        $wpdb->insert($table, $data);
        return (int) $wpdb->insert_id;
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