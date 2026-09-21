<?php

class HouseLedgerInventory
{
    /** @var HouseLedgerCore */
    private $core;

    public function __construct(HouseLedgerCore $core)
    {
        $this->core = $core;
    }

    public function init()
    {
        // Save (stay on edit)
        add_action('admin_post_house_ledger_save_inventory', [$this, 'handle_save']);

        // Save & Close (go back to list)
        add_action('admin_post_house_ledger_save_and_close_inventory', [$this, 'handle_save_and_close']);
    }

    /**
     * Save, then stay on the edit screen.
     */
    public function handle_save()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'hl'));
        }
        check_admin_referer('hl_inventory_save', 'hl_nonce');

        try {
            $id = $this->do_save_();
        } catch (Exception $e) {
            wp_safe_redirect(add_query_arg([
                'page'  => 'house-ledger-inventory',
                'error' => rawurlencode($e->getMessage()),
            ], admin_url('admin.php')));
            exit;
        }

        // Stay on edit page
        wp_safe_redirect(add_query_arg([
            'page'    => 'house-ledger-add-inventory',
            'id'      => (int) $id,
            'updated' => 1,
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Save, then return to the inventory list.
     */
    public function handle_save_and_close()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'hl'));
        }
        check_admin_referer('hl_inventory_save', 'hl_nonce');

        try {
            $this->do_save_();
        } catch (Exception $e) {
            wp_safe_redirect(add_query_arg([
                'page'  => 'house-ledger-inventory',
                'error' => rawurlencode($e->getMessage()),
            ], admin_url('admin.php')));
            exit;
        }

        // Go back to list
        wp_safe_redirect(add_query_arg([
            'page'    => 'house-ledger-inventory',
            'updated' => 1,
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Shared save routine: sanitize inputs and upsert.
     * Returns the row id.
     */
    private function do_save_(): int
    {
        // Gather + sanitize fields (nullable allowed)
        $id   = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        $item_id = isset($_POST['item_id']) && $_POST['item_id'] !== '' ? (int) $_POST['item_id'] : null;
        $item_name = isset($_POST['item_name']) && $_POST['item_name'] !== '' ? sanitize_text_field($_POST['item_name']) : null;

        $expiration = null;
        if (!empty($_POST['expiration'])) {
            $ts = strtotime(wp_unslash($_POST['expiration']));
            $expiration = $ts ? gmdate('Y-m-d', $ts) : null;
        }

        $location = isset($_POST['location']) && $_POST['location'] !== '' ? sanitize_text_field($_POST['location']) : null;

        $opened = null;
        if (isset($_POST['opened']) && $_POST['opened'] !== '') {
            $opened = (int) $_POST['opened'];
        }

        $percent_remaining = null;
        if (isset($_POST['percent_remaining']) && $_POST['percent_remaining'] !== '') {
            $percent_remaining = (float) $_POST['percent_remaining'];
        }

        // Delegate to your upsert()
        return $this->upsert([
            'id'                => $id ?: null,
            'item_id'           => $item_id,
            'item_name'         => $item_name,
            'expiration'        => $expiration,
            'location'          => $location,
            'opened'            => $opened,
            'percent_remaining' => $percent_remaining,
        ]);
    }

    /**
     * Your existing upsert() from earlier reply fits here unchanged.
     * It should write only the provided keys to {$wpdb->prefix}hl_inventory.
     */
    public function upsert(array $fields): int
    {
        global $wpdb;
        $table = "{$wpdb->prefix}hl_inventory";

        $data = [];
        $format = [];
        $map = [
            'item_id'           => '%d',
            'item_name'         => '%s',
            'expiration'        => '%s',
            'location'          => '%s',
            'opened'            => '%d',
            'percent_remaining' => '%f',
        ];

        foreach ($map as $key => $fmt) {
            if (array_key_exists($key, $fields)) {
                $val = $fields[$key];
                $data[$key]  = ($val === '' || $val === null) ? null : $val;
                $format[]    = $fmt;
            }
        }

        $id = !empty($fields['id']) ? (int)$fields['id'] : 0;

        if ($id > 0) {
            $exists = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE id = %d", $id));
            if (!$exists) {
                throw new Exception(__('Inventory row not found for update.', 'hl'));
            }
            $wpdb->update($table, $data, ['id' => $id], $format, ['%d']);
            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
            return $id;
        } else {
            $wpdb->insert($table, $data, $format);
            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
            return (int) $wpdb->insert_id;
        }
    }
}
