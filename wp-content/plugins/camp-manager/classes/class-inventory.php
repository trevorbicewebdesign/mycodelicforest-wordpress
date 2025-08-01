<?php

class CampManagerInventory
{

    public function __construct()
    {
        // Constructor logic if needed
    }

    public function init()
    {
        add_action('admin_post_camp_manager_save_inventory', [$this, 'handle_inventory_save']);
        add_action('admin_post_camp_manager_save_and_close_inventory', [$this, 'handle_inventory_save']);

        add_action('admin_post_camp_manager_save_tote', [$this, 'handle_tote_save']);
        add_action('admin_post_camp_manager_save_and_close_tote', [$this, 'handle_tote_save']);

        add_action('admin_post_camp_manager_save_tote_inventory', [$this, 'handle_tote_inventory_save']);
        add_action('admin_post_camp_manager_save_and_close_tote_inventory', [$this, 'handle_tote_inventory_save_close']);

    }

    public function handle_tote_inventory_save_close()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $tote_id = isset($_POST['tote_id']) ? (int) $_POST['tote_id'] : null;
            $inventory_id = isset($_POST['inventory_id']) ? (int) $_POST['inventory_id'] : null;
            $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

            if ($tote_id && $inventory_id) {
                $tote_inventory_id = $this->upsertToteInventory($tote_id, $inventory_id, $quantity);
            } else {
                throw new Exception('Invalid Tote or Inventory ID');
            }
        } catch (\Exception $e) {

            $redirect_url = isset($_POST['return_url']) && !empty($_POST['return_url'])
                ? esc_url_raw(base64_decode($_POST['return_url']))
                : admin_url('admin.php?page=camp-manager-add-tote-inventory&error=' . urlencode($e->getMessage()));
            wp_redirect($redirect_url);
            exit;
        }

        if (isset($_POST['return_url']) && !empty($_POST['return_url'])) {
            $decoded_url = base64_decode($_POST['return_url']);
            $redirect_url = esc_url_raw($decoded_url);
        } else {
            print_r($_POST);
            die();
            $redirect_url = admin_url("admin.php?page=camp-manager-view-tote-inventory");
        }
        wp_redirect($redirect_url);
        exit;
    }

    public function handle_tote_inventory_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $tote_id = isset($_POST['tote_id']) ? (int) $_POST['tote_id'] : null;
            $inventory_id = isset($_POST['inventory_id']) ? (int) $_POST['inventory_id'] : null;
            $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

            if ($tote_id && $inventory_id) {
                $tote_inventory_id = $this->upsertToteInventory($tote_id, $inventory_id, $quantity);
            } else {
                throw new Exception('Invalid Tote or Inventory ID');
            }
        } catch (\Exception $e) {
            $redirect_url = isset($_POST['return_url']) && !empty($_POST['return_url'])
                ? esc_url_raw(base64_decode($_POST['return_url']))
                : admin_url('admin.php?page=camp-manager-add-tote-inventory&error=' . urlencode($e->getMessage()));
            wp_redirect($redirect_url);
            exit;
        }

        if (isset($_POST['return_url']) && !empty($_POST['return_url'])) {
            $decoded_url = base64_decode($_POST['return_url']);
            $redirect_url = esc_url_raw($decoded_url);
        } else {
            $redirect_url = admin_url("admin.php?page=camp-manager-add-tote-inventory&id={$tote_inventory_id}&success=item_added");
        }
        wp_redirect($redirect_url);
        exit;
    }

    public function handle_tote_save()
    {
        // Handle saving a tote from the admin post request
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $tote_id = $this->upsertTote(
                sanitize_text_field($_POST['tote_name']),
                isset($_POST['tote_description']) ? sanitize_textarea_field($_POST['tote_description']) : '',
                isset($_POST['tote_id']) ? (int) $_POST['tote_id'] : null
            );
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-tote&error=' . urlencode($e->getMessage())));
            exit;
        }
        wp_redirect(admin_url("admin.php?page=camp-manager-add-tote&id={$tote_id}&success=item_added"));
        exit;
    }

    public function handle_inventory_save()
    {
        // Handle saving an inventory item from the admin post request
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        try {
            $item_id = $this->upsertInventoryItem(
                sanitize_text_field($_POST['inventory_name']),
                isset($_POST['inventory_description']) ? sanitize_textarea_field($_POST['inventory_description']) : '',
                isset($_POST['inventory_id']) ? (int) $_POST['inventory_id'] : null
            );
        } catch (\Exception $e) {
            wp_redirect(admin_url('admin.php?page=camp-manager-inventory&error=' . urlencode($e->getMessage())));
            exit;
        }
        wp_redirect(admin_url("admin.php?page=camp-manager-inventory&id={$item_id}&success=item_added"));
        exit;
    }

    public function upsertToteInventory($tote_id, $inventory_id, $quantity): int
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_tote_inventory";

        $data = [
            'tote_id' => (int) $tote_id,
            'inventory_id' => (int) $inventory_id,
            'quantity' => (int) $quantity
        ];

        // Check if a record exists for this tote/inventory combo
        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE tote_id = %d AND inventory_id = %d",
            $tote_id,
            $inventory_id
        ));

        if ($existing_id) {
            $wpdb->update($table, $data, ['id' => $existing_id]);
            return (int) $existing_id;
        } else {
            $wpdb->insert($table, $data);
            return (int) $wpdb->insert_id;
        }
    }


    // Should insert or update an inventory item
    public function upsertInventoryItem($name, $description = '', $item_id = null): int
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_inventory";

        // Prepare all fields, using defaults if not provided
        $data = [
            'name' => sanitize_text_field($name),
            'description' => sanitize_textarea_field($description),
            'manufacturer' => isset($_POST['inventory_manufacturer']) ? sanitize_text_field($_POST['inventory_manufacturer']) : '',
            'model' => isset($_POST['inventory_model']) ? sanitize_text_field($_POST['inventory_model']) : '',
            'quantity' => isset($_POST['inventory_quantity']) ? (int) $_POST['inventory_quantity'] : 1,
            'photo' => isset($_POST['inventory_photo_id']) && $_POST['inventory_photo_id'] !== '' ? intval($_POST['inventory_photo_id']) : 0,
            'location' => isset($_POST['inventory_location']) ? sanitize_text_field($_POST['inventory_location']) : '',
            'weight' => isset($_POST['inventory_weight']) ? floatval($_POST['inventory_weight']) : 0,
            'category' => isset($_POST['inventory_category']) ? sanitize_text_field($_POST['inventory_category']) : '',
            'category_name' => isset($_POST['inventory_category_name']) ? sanitize_text_field($_POST['inventory_category_name']) : '',
            'links' => isset($_POST['inventory_links']) ? sanitize_text_field($_POST['inventory_links']) : '',
            'amp' => isset($_POST['inventory_amp']) && $_POST['inventory_amp'] !== '' ? floatval($_POST['inventory_amp']) : 0,
            'set_name' => isset($_POST['inventory_set_name']) ? sanitize_text_field($_POST['inventory_set_name']) : '',
            'uuid' => isset($_POST['inventory_uuid']) && $_POST['inventory_uuid'] !== '' ? intval($_POST['inventory_uuid']) : 0,
        ];

        // Remove null values for nullable fields
        foreach ($data as $key => $value) {
            if (is_null($value)) {
                unset($data[$key]);
            }
        }

        if ($item_id && $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $item_id))) {
            // Update existing item
            $wpdb->update($table, $data, ['id' => (int) $item_id]);
            return (int) $item_id;
        } else {
            // Insert new item
            $wpdb->insert($table, $data);
            return (int) $wpdb->insert_id;
        }
    }


    public function upsertTote($name, $description = '', $tote_id = null): int
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_totes";

        // Prepare all fields, using defaults if not provided
        $data = [
            'name' => sanitize_text_field($name),
            // 'description' => sanitize_textarea_field($description),
            'weight' => isset($_POST['tote_weight']) ? floatval($_POST['tote_weight']) : 0,
            'uid' => isset($_POST['tote_uid']) ? sanitize_text_field($_POST['tote_uid']) : '',
            'status' => isset($_POST['tote_status']) ? sanitize_text_field($_POST['tote_status']) : '',
            'location' => isset($_POST['tote_location']) ? sanitize_text_field($_POST['tote_location']) : '',
            'size' => isset($_POST['tote_size']) ? sanitize_text_field($_POST['tote_size']) : '',
        ];

        if ($tote_id && $wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE id = %d", $tote_id))) {
            // Update existing tote
            if ($tote_id !== null && is_numeric($tote_id)) {
                $wpdb->update($table, $data, ['id' => (int) $tote_id]);
            } else {
                throw new Exception('Invalid Tote ID for update.');
            }

            return (int) $tote_id;
        } else {
            // Insert new tote
            $wpdb->insert($table, $data);
            return (int) $wpdb->insert_id;
        }
    }

    public function getTote($id)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_totes";
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public function getToteInventoryItem($tote_inventory_item_id)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_tote_inventory";
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $tote_inventory_item_id));
    }

    public function getToteInventoryItems($tote_id)
    {
        global $wpdb;
        $tote_inventory_table = "{$wpdb->prefix}mf_tote_inventory";
        $inventory_table = "{$wpdb->prefix}mf_inventory";
        $totes_table = "{$wpdb->prefix}mf_totes";

        $query = $wpdb->prepare(
            "SELECT ti.*, i.name AS inventory_name, t.name AS tote_name
             FROM $tote_inventory_table ti
             LEFT JOIN $inventory_table i ON ti.inventory_id = i.id
             LEFT JOIN $totes_table t ON ti.tote_id = t.id
             WHERE ti.tote_id = %d",
            $tote_id
        );

        return $wpdb->get_results($query);
    }

    public function getInventoryItem($id)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_inventory";
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id));
    }

    public function getInventoryItems()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_inventory";
        return $wpdb->get_results("SELECT * FROM $table");
    }

    public function getAllTotes()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_totes";
        return $wpdb->get_results("SELECT * FROM $table");
    }

}