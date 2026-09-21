<?php

class HouseLedgerKnownItems
{
    private $core;

    public function __construct(HouseLedgerCore $core)
    {
        $this->core = $core;
    }
    public function init()
    {
        add_action('admin_post_house_ledger_save_known_item',   [$this, 'handle_save']);
        add_action('admin_post_house_ledger_save_and_close_known_item',    [$this, 'handle_save_and_close']);
    }

    public function handle_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'hl'));
        }
        // check_admin_referer('hl_known_item_save', 'hl_nonce');

        try {
            $id = $this->do_save_();
        } catch (Exception $e) {
            $return_id = isset($_POST['id']) ? (int) $_POST['id'] : NULL;
            wp_safe_redirect(add_query_arg([
                'page'  => 'house-ledger-add-known-item',
                'id'    => $return_id,
                'error' => rawurlencode($e->getMessage()),
            ], admin_url('admin.php')));
            exit;
        }

        // Stay on edit page after Save
        wp_safe_redirect(add_query_arg([
            'page'    => 'house-ledger-add-known-item',
            'id'      => (int) $id,
            'updated' => 1,
        ], admin_url('admin.php')));
        exit;
    }

    public function handle_save_and_close()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'hl'));
        }
        // check_admin_referer('hl_known_item_save', 'hl_nonce');

        try {
            $this->do_save_();
        } catch (Exception $e) {
            wp_safe_redirect(add_query_arg([
                'page'  => 'house-ledger-known-items',
                'error' => rawurlencode($e->getMessage()),
            ], admin_url('admin.php')));
            exit;
        }

        // Go back to list after Save & Close
        wp_safe_redirect(add_query_arg([
            'page'    => 'house-ledger-known-items',
            'updated' => 1,
        ], admin_url('admin.php')));
        exit;
    }

    /**
         * Shared save logic. Returns the known_item id.
         */
        private function do_save_()
        {
            global $wpdb;
            $table = "{$wpdb->prefix}hl_known_items";
    
            $id             = isset($_POST['id']) ? (int) $_POST['id'] : NULL;
            $item_name      = isset($_POST['item_name']) ? sanitize_text_field($_POST['item_name']) : '';
            $category       = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : null;
            $barcode        = isset($_POST['barcode']) ? sanitize_text_field($_POST['barcode']) : null;
            $units          = isset($_POST['units']) ? sanitize_text_field($_POST['units']) : null;
            $weight         = isset($_POST['weight']) ? sanitize_text_field($_POST['weight']) : null; // free text per schema
            $has_expiration = isset($_POST['has_expiration']) ? 1 : 0;
            $parent         = (isset($_POST['parent']) && $_POST['parent'] !== '') ? (int) $_POST['parent'] : null;
            $product_label_id = (isset($_POST['product_label_id']) && $_POST['product_label_id'] !== '') ? (int) $_POST['product_label_id'] : null;
            $nutrition_image_id = (isset($_POST['nutrition_image_id']) && $_POST['nutrition_image_id'] !== '') ? (int) $_POST['nutrition_image_id'] : null;
    
            if ($item_name === '') {
                throw new Exception(__('Item Name is required.', 'hl'));
            }
    
            // Prevent self-parenting on update
            if ($id > 0 && $parent && $parent === $id) {
                $parent = null;
            }
    
            // If parent present, ensure it exists
            if ($parent) {
                $exists_parent = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE id = %d", $parent));
                if (!$exists_parent) {
                    $parent = null;
                }
            }
    
            $data = [
                'item_name'      => $item_name,
                'category'       => ($category !== '') ? $category : null,
                'barcode'        => ($barcode  !== '') ? $barcode  : null,
                'units'          => ($units    !== '') ? $units    : null,
                'weight'         => ($weight   !== '') ? $weight   : null,
                'has_expiration' => (int) $has_expiration,
                'parent'         => $parent,
                'product_label_id' => $product_label_id,
                'nutrition_image_id' => $nutrition_image_id,
            ];
    
            $format = [
                '%s', // item_name
                '%s', // category
                '%s', // barcode
                '%s', // units
                '%s', // weight
                '%d', // has_expiration
                '%d', // parent
                '%d', // product_label_id
                '%d', // nutrition_image_id
            ];
    
            if ($id > 0) {
                // Update
                $exists = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE id = %d", $id));
                if (!$exists) {
                    throw new Exception(__('Known item not found for update.', 'hl'));
                }
                $wpdb->update($table, $data, ['id' => $id], $format, ['%d']);
                if ($wpdb->last_error) {
                    throw new Exception($wpdb->last_error);
                }
                return $id;
            } else {
                // Insert
                $wpdb->insert($table, $data, $format);
                if ($wpdb->last_error) {
                    throw new Exception($wpdb->last_error);
                }

                return (int) $wpdb->insert_id;
            }
        }

    /* ---------- Optional helpers ---------- */

    public function get($id)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}hl_known_items";
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id));
    }

    public function list()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}hl_known_items";
        $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY item_name ASC", ARRAY_A);
        return is_array($rows) ? $rows : [];
    }

    public function delete($id)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}hl_known_items";
        $wpdb->delete($table, ['id' => $id], ['%d']);
        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }
        return true;
    }
}
