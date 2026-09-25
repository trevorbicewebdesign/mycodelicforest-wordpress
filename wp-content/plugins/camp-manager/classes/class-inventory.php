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
        add_action('admin_post_camp_manager_save_and_close_inventory', [$this, 'handle_inventory_save_close']);

        add_action('admin_post_camp_manager_save_tote', [$this, 'handle_tote_save']);
        add_action('admin_post_camp_manager_save_and_close_tote', [$this, 'handle_tote_save']);

        add_action('admin_post_camp_manager_save_tote_inventory', [$this, 'handle_tote_inventory_save']);
        add_action('admin_post_camp_manager_save_and_close_tote_inventory', [$this, 'handle_tote_inventory_save_close']);

    }

    public function handle_inventory_save_close()
    {
        if (!current_user_can(CampManagerRoles::cap('inventory'))) {
            wp_die('Unauthorized');
        }

        try {
            $item_id = isset($_POST['inventory_id']) ? (int) $_POST['inventory_id'] : null;
            $this->upsertInventoryItem(
                sanitize_text_field($_POST['inventory_name']),
                isset($_POST['inventory_description']) ? sanitize_textarea_field($_POST['inventory_description']) : '',
                $item_id
            );
        } catch (\Exception $e) {
            $redirect_url = isset($_POST['return_url']) && !empty($_POST['return_url'])
                ? esc_url_raw(base64_decode($_POST['return_url']))
                : admin_url('admin.php?page=camp-manager-add-inventory&error=' . urlencode($e->getMessage()));
            wp_redirect($redirect_url);
            exit;
        }

        if (isset($_POST['return_url']) && !empty($_POST['return_url'])) {
            $decoded_url = base64_decode($_POST['return_url']);
            $redirect_url = esc_url_raw($decoded_url);
        } else {
            $redirect_url = admin_url("admin.php?page=camp-manager-inventory");
        }
        wp_redirect($redirect_url);
        exit;
    }

    public function handle_tote_inventory_save_close()
    {
        if (!current_user_can(CampManagerRoles::cap('inventory'))) {
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
            $redirect_url = admin_url("admin.php?page=camp-manager-view-tote-inventory");
        }
        wp_redirect($redirect_url);
        exit;
    }

    public function generateQRToteUrl($tote_code)
    {
        $base_url = admin_url('admin.php?page=camp-manager-add-tote');
        $query_args = [
            'tote_code' => $tote_code
        ];
        return add_query_arg($query_args, $base_url);

    }

    public function getToteByCode($tote_code)
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_totes";
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE uid = %s", $tote_code));
    }

    public function handle_tote_inventory_save()
    {
        if (!current_user_can(CampManagerRoles::cap('inventory'))) {
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
        if (!current_user_can(CampManagerRoles::cap('inventory'))) {
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
        if (!current_user_can(CampManagerRoles::cap('inventory'))) {
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

    public function sumPackedTotes(): float
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_totes";
        $query = "SELECT SUM(weight) FROM $table WHERE status = 'PACKED'";
        return (float) $wpdb->get_var($query);
    }

    public function sumSojournerTotes(): float
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_totes";
        $query = "SELECT SUM(weight) FROM $table WHERE location = 'Sojourner'";
        return (float) $wpdb->get_var($query);
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

    // ------------------------------------------------------------- the admin list pages

    /** The three sections of the Inventory admin area: slug => [tab label, admin page]. */
    public static function sections(): array
    {
        return [
            'items'          => ['All items', 'camp-manager-inventory'],
            'totes'          => ['Totes', 'camp-manager-totes'],
            'tote_inventory' => ['Tote inventory', 'camp-manager-view-tote-inventory'],
        ];
    }

    /**
     * The top of every Inventory list page: the heading, its Add New button and the tabs that
     * switch between items, totes and tote inventory. Prints the pages' shared styles once.
     */
    public static function renderPageHeader(string $current, string $add_url): void
    {
        self::pageStyles();
        ?>
        <h1 class="wp-heading-inline">Inventory</h1>
        <a href="<?php echo esc_url($add_url); ?>" class="page-title-action">Add New</a>
        <hr class="wp-header-end">
        <nav class="cm-tabs" aria-label="Inventory sections">
            <?php foreach (self::sections() as $slug => [$label, $page]): ?>
                <?php printf(
                    '<a href="%s" class="cm-tab%s" data-tab="%s"%s>%s</a>',
                    esc_url(admin_url('admin.php?page=' . $page)),
                    $slug === $current ? ' is-active' : '',
                    esc_attr($slug),
                    $slug === $current ? ' aria-current="page"' : '',
                    esc_html($label)
                ); ?>
            <?php endforeach; ?>
        </nav>
        <?php
    }

    /** The styles the list tables and tabs rely on; printed once per request. */
    public static function pageStyles(): void
    {
        static $printed = false;
        if ($printed) {
            return;
        }
        $printed = true;
        ?>
<style>
    .cm-inventory-page .cm-tabs { display: flex; flex-wrap: wrap; gap: 0 28px; margin: 12px 0 16px; border-bottom: 1px solid #c3c4c7; }
    .cm-inventory-page .cm-tab { display: inline-block; padding: 6px 0 9px; margin-bottom: -1px; font-size: 14px; line-height: 1.4; text-decoration: none; border-bottom: 2px solid transparent; }
    .cm-inventory-page .cm-tab:hover { color: #135e96; }
    .cm-inventory-page .cm-tab.is-active { color: #1d2327; font-weight: 600; border-bottom-color: #1d2327; }
    .cm-inventory-page .cm-tab:focus { box-shadow: none; outline: 2px solid #2271b1; outline-offset: -2px; }
    .cm-inventory-page .cm-postbox { margin-bottom: 16px; }
    .cm-inventory-page .search-box { margin-bottom: 8px; }

    /* One flex row: bulk actions and filters on the left, the page size and the pagination on the right. */
    .cm-inventory-page .tablenav { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; height: auto; }
    .cm-inventory-page .tablenav .bulkactions,
    .cm-inventory-page .tablenav .actions { float: none; padding: 0; }
    .cm-inventory-page .tablenav .tablenav-pages { float: none; margin: 0 0 0 auto; }
    .cm-inventory-page .tablenav .cm-per-page { margin-left: auto; }
    .cm-inventory-page .tablenav .cm-per-page + .tablenav-pages { margin-left: 0; }
    .cm-inventory-page .tablenav .clear { display: none; }
    .cm-inventory-page .tablenav .actions select { max-width: 170px; }
    /* The count sits with the bottom pagination; up top the page size picker takes its place. */
    .cm-inventory-page .tablenav.top .displaying-num { display: none; }
    @media screen and (max-width: 782px) {
        .cm-inventory-page .tablenav .cm-per-page { display: none; }
    }

    .cm-inventory-table td,
    .cm-inventory-table th.check-column { vertical-align: middle; }
    .cm-inventory-table .column-cb { width: 2.2em; }
    .cm-inventory-table .cm-item-meta { color: #646970; margin-top: 2px; }
    .cm-inventory-table .cm-empty { color: #787c82; }
    .cm-inventory-table .column-quantity,
    .cm-inventory-table .column-weight,
    .cm-inventory-table .column-total_weight { width: 110px; text-align: right; }
    .cm-inventory-table td.column-quantity,
    .cm-inventory-table td.column-weight,
    .cm-inventory-table td.column-total_weight { padding-right: 24px; }
    .cm-inventory-table td.column-items .cm-item-meta { display: block; }
    .cm-inventory-table .cm-link { display: inline-flex; align-items: center; gap: 6px; }
    .cm-inventory-table .cm-link .dashicons { font-size: 16px; width: 16px; height: 16px; }
    /* Row actions keep their line whether shown or not, so hovering never changes a row's height. */
    .cm-inventory-table .row-actions { position: relative; }
    .cm-inventory-table tr:not(:hover):not(:focus-within) .row-actions { left: -9999em; }
    .cm-inventory-table tr:hover .row-actions,
    .cm-inventory-table tr:focus-within .row-actions { left: 0; }
</style>
        <?php
    }

    // ------------------------------------------------------------------ items (mf_inventory)

    /** Every distinct category in use (category_name, else category), alphabetically. */
    public function itemCategories(): array
    {
        global $wpdb;
        return $wpdb->get_col(
            "SELECT DISTINCT COALESCE(NULLIF(category_name, ''), category) AS c
             FROM {$wpdb->prefix}mf_inventory
             WHERE COALESCE(NULLIF(category_name, ''), category) <> ''
             ORDER BY c"
        ) ?: [];
    }

    /** Every distinct item location in use, alphabetically. */
    public function itemLocations(): array
    {
        global $wpdb;
        return $wpdb->get_col("SELECT DISTINCT location FROM {$wpdb->prefix}mf_inventory WHERE location <> '' ORDER BY location") ?: [];
    }

    /** The totes each item is packed in: [inventory_id => [tote_id => tote name]], by tote name. */
    public function getToteNamesByItem(array $inventory_ids): array
    {
        $inventory_ids = array_values(array_filter(array_map('intval', $inventory_ids)));
        if (!$inventory_ids) {
            return [];
        }
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($inventory_ids), '%d'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT ti.inventory_id, t.id AS tote_id, t.name
             FROM {$wpdb->prefix}mf_tote_inventory ti
             JOIN {$wpdb->prefix}mf_totes t ON t.id = ti.tote_id
             WHERE ti.inventory_id IN ($placeholders)
             ORDER BY t.name",
            ...$inventory_ids
        ), ARRAY_A) ?: [];

        $totes = [];
        foreach ($rows as $row) {
            $totes[(int) $row['inventory_id']][(int) $row['tote_id']] = stripslashes((string) $row['name']);
        }
        return $totes;
    }

    /** Deletes items and the tote inventory rows that packed them. */
    public function deleteInventoryItems(array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mf_tote_inventory WHERE inventory_id IN ($placeholders)", ...$ids));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mf_inventory WHERE id IN ($placeholders)", ...$ids));
    }

    // ----------------------------------------------------------------------- totes (mf_totes)

    /** Every distinct tote status in use, alphabetically. */
    public function toteStatuses(): array
    {
        global $wpdb;
        return $wpdb->get_col("SELECT DISTINCT status FROM {$wpdb->prefix}mf_totes WHERE status IS NOT NULL AND status <> '' ORDER BY status") ?: [];
    }

    /** Every distinct tote location in use, alphabetically. */
    public function toteLocations(): array
    {
        global $wpdb;
        return $wpdb->get_col("SELECT DISTINCT location FROM {$wpdb->prefix}mf_totes WHERE location IS NOT NULL AND location <> '' ORDER BY location") ?: [];
    }

    /** Figures for the Totes overview: how many totes, how many are packed, and the weights. */
    public function totesOverview(): array
    {
        global $wpdb;
        $row = $wpdb->get_row(
            "SELECT COUNT(*) AS total,
                    SUM(status = 'PACKED') AS packed,
                    SUM(status = 'READY') AS ready,
                    COALESCE(SUM(weight), 0) AS total_weight,
                    COALESCE(SUM(CASE WHEN status = 'PACKED' THEN weight END), 0) AS packed_weight,
                    COALESCE(SUM(CASE WHEN location = 'Sojourner' THEN weight END), 0) AS sojourner_weight
             FROM {$wpdb->prefix}mf_totes",
            ARRAY_A
        );
        return [
            'total'            => (int) ($row['total'] ?? 0),
            'packed'           => (int) ($row['packed'] ?? 0),
            'ready'            => (int) ($row['ready'] ?? 0),
            'total_weight'     => (float) ($row['total_weight'] ?? 0),
            'packed_weight'    => (float) ($row['packed_weight'] ?? 0),
            'sojourner_weight' => (float) ($row['sojourner_weight'] ?? 0),
        ];
    }

    /** What each tote holds: [tote_id => ['items' => distinct items, 'quantity' => pieces]]. */
    public function countToteItemsByTote(array $tote_ids): array
    {
        $tote_ids = array_values(array_filter(array_map('intval', $tote_ids)));
        if (!$tote_ids) {
            return [];
        }
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($tote_ids), '%d'));
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT tote_id, COUNT(*) AS items, COALESCE(SUM(quantity), 0) AS quantity
             FROM {$wpdb->prefix}mf_tote_inventory
             WHERE tote_id IN ($placeholders)
             GROUP BY tote_id",
            ...$tote_ids
        ), ARRAY_A) ?: [];

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['tote_id']] = ['items' => (int) $row['items'], 'quantity' => (int) $row['quantity']];
        }
        return $counts;
    }

    /** Deletes totes and their tote inventory rows. */
    public function deleteTotes(array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mf_tote_inventory WHERE tote_id IN ($placeholders)", ...$ids));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mf_totes WHERE id IN ($placeholders)", ...$ids));
    }

    // ------------------------------------------------------ tote inventory (mf_tote_inventory)

    /**
     * Figures for the Tote inventory overview, for one tote or all of them: packed rows,
     * distinct items, pieces, their weight and the totes in use.
     */
    public function toteInventoryOverview(?int $tote_id = null): array
    {
        global $wpdb;
        $where = $tote_id === null ? '' : $wpdb->prepare('WHERE ti.tote_id = %d', $tote_id);
        $row = $wpdb->get_row(
            "SELECT COUNT(*) AS rows_count,
                    COUNT(DISTINCT ti.inventory_id) AS items,
                    COALESCE(SUM(ti.quantity), 0) AS quantity,
                    COALESCE(SUM(ti.quantity * i.weight), 0) AS weight,
                    COUNT(DISTINCT ti.tote_id) AS totes
             FROM {$wpdb->prefix}mf_tote_inventory ti
             LEFT JOIN {$wpdb->prefix}mf_inventory i ON i.id = ti.inventory_id
             $where",
            ARRAY_A
        );
        return [
            'rows'     => (int) ($row['rows_count'] ?? 0),
            'items'    => (int) ($row['items'] ?? 0),
            'quantity' => (int) ($row['quantity'] ?? 0),
            'weight'   => (float) ($row['weight'] ?? 0),
            'totes'    => (int) ($row['totes'] ?? 0),
        ];
    }

    /** Deletes tote inventory rows (takes items out of totes; the items themselves stay). */
    public function deleteToteInventory(array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));
        if (!$ids) {
            return;
        }
        global $wpdb;
        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}mf_tote_inventory WHERE id IN ($placeholders)", ...$ids));
    }
}
