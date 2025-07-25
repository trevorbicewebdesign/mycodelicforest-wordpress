<?php

class CampManagerInventory {

    public function __construct()
    {
        // Constructor logic if needed
    }

    public function init()
    {

    }

    public function handle_inventory_save()
    {
        // Handle saving an inventory item from the admin post request
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $this->upsertInventoryItem(
                sanitize_text_field($_POST['inventory_name']),
                isset($_POST['inventory_description']) ? sanitize_textarea_field($_POST['inventory_description']) : '',
                isset($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : null
            );
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-inventory&error=' . urlencode($e->getMessage())));
            exit;
        }
        wp_redirect(admin_url('admin.php?page=camp-manager-inventory&success=item_added'));
        exit;
    }


    // Should insert or update an inventory item
    public function upsertInventoryItem($name, $description = '', $item_id = null): int
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_inventory";

        // Prepare all fields, using defaults if not provided
        $data = [
            'name'         => sanitize_text_field($name),
            'description'  => sanitize_textarea_field($description),
            'manufacturer' => isset($_POST['inventory_manufacturer']) ? sanitize_text_field($_POST['inventory_manufacturer']) : '',
            'model'        => isset($_POST['inventory_model']) ? sanitize_text_field($_POST['inventory_model']) : '',
            'quantity'     => isset($_POST['inventory_quantity']) ? (int)$_POST['inventory_quantity'] : 1,
            'photo'        => isset($_POST['inventory_photo']) ? esc_url_raw($_POST['inventory_photo']) : '',
            'location'     => isset($_POST['inventory_location']) ? sanitize_text_field($_POST['inventory_location']) : '',
            'weight'       => isset($_POST['inventory_weight']) ? floatval($_POST['inventory_weight']) : 0,
            'category'     => isset($_POST['inventory_category']) ? sanitize_text_field($_POST['inventory_category']) : '',
            'category_name'=> isset($_POST['inventory_category_name']) ? sanitize_text_field($_POST['inventory_category_name']) : '',
            'links'        => isset($_POST['inventory_links']) ? sanitize_text_field($_POST['inventory_links']) : null,
            'amp'          => isset($_POST['inventory_amp']) ? floatval($_POST['inventory_amp']) : null,
            'set_name'     => isset($_POST['inventory_set_name']) ? sanitize_text_field($_POST['inventory_set_name']) : null,
            'uuid'         => isset($_POST['inventory_uuid']) ? intval($_POST['inventory_uuid']) : null,
        ];

        // Remove null values for nullable fields
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                unset($data[$key]);
            }
        }

        if ($item_id && $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $item_id))) {
            // Update existing item
            $wpdb->update($table, $data, ['id' => (int)$item_id]);
            return (int)$item_id;
        } else {
            // Insert new item
            $wpdb->insert($table, $data);
            return (int)$wpdb->insert_id;
        }
    }

}