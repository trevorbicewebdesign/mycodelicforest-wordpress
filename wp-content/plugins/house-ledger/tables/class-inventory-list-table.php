<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class HouseLedgerInventoryTable extends WP_List_Table
{
    private $data;
    public $inventory;

    public function __construct(HouseLedgerInventory $HouseLedgerInventory)
    {
        $this->inventory = $HouseLedgerInventory;

        parent::__construct([
            'singular' => 'Item',
            'plural'   => 'Items',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'            => '<input type="checkbox" />',
            'id'            => 'ID',
            'display_name'  => 'Name',        // COALESCE(inv.item_name, ki.item_name)
            'category'      => 'Category',    // from known items
            'barcode'       => 'Barcode',     // from known items
            'weight'        => 'Weight',      // from known items
            'units'         => 'Units',       // from known items
            'location_name'      => 'Location',    // from inventory
            'expiration'    => 'Expiration',  // from inventory
            'opened'        => 'Opened',
            'percent_remaining' => '% Remaining',
            'created'       => 'Created',
        ];
    }

    public function get_sortable_columns()
    {
        // Keys here must match your column IDs (left) to be clickable.
        return [
            'id'           => ['id', true],
            'display_name' => ['display_name', true],
            'category'     => ['category', false],
            'barcode'      => ['barcode', false],
            'weight'       => ['weight', false],
            'location_name'     => ['location_name', false],
            'expiration'   => ['expiration', false],
            // 'opened'           => ['opened', false],
            // 'percent_remaining'=> ['percent_remaining', false],
            // 'created'          => ['created', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);

            case 'display_name':
                return '<a href="' . esc_url(admin_url('admin.php?page=house-ledger-add-inventory&action=edit&id=' . (int)$item['id'])) . '">' . esc_html($item['display_name']) . '</a>';

            case 'expiration':
                return !empty($item['expiration']) ? esc_html($item['expiration']) : '—';

            case 'location':
            case 'category':
            case 'barcode':
            case 'weight':
                return isset($item[$column_name]) && $item[$column_name] !== '' ? esc_html($item[$column_name]) : '—';

            // Optional fields if enabled in get_columns():
            case 'opened':
                return isset($item['opened']) ? 'true' : 'false';
            case 'percent_remaining':
                return isset($item['percent_remaining']) && $item['percent_remaining'] !== null
                    ? esc_html(rtrim(rtrim(number_format((float)$item['percent_remaining'], 2, '.', ''), '0'), '.')) . '%'
                    : '—';
            case 'created':
                return !empty($item['created']) ? esc_html($item['created']) : '—';

            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="item[]" value="%s" />', esc_attr($item['id']));
    }

    public function get_bulk_actions()
    {
        return [
            'delete'    => 'Delete',
            'duplicate' => 'Duplicate',
        ];
    }

    public function process_bulk_action()
    {
        // (stubbed for now)
    }

    public function single_row($item)
    {
        echo '<tr>';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    public function prepare_items()
    {
        global $wpdb;

        $per_page     = 100;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $inv_table   = "{$wpdb->prefix}hl_inventory";
        $known_table = "{$wpdb->prefix}hl_known_items";
        $locations_table = "{$wpdb->prefix}hl_locations";

        // Count from inventory (adjust to include WHERE later if you add filters)
        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$inv_table} AS inv");

        // Whitelist of orderable expressions (existing fields/aliases only)
        $sortable_map = [
            'id'             => 'inv.id',
            'display_name'   => 'display_name',
            'category'       => 'ki.category',
            'barcode'        => 'ki.barcode',
            'weight'         => 'ki.weight',
            'location'       => 'inv.location',
            'expiration'     => 'inv.expiration',
            'opened'           => 'inv.opened',
            'percent_remaining'=> 'inv.percent_remaining',
            'created'          => 'inv.created',
            'product_label_id' => 'ki.product_label_id',
            'nutrition_image_id' => 'ki.nutrition_image_id',
            'units'         => 'ki.units',
        ];

        $order_by_key = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'display_name';
        $order_by_sql = $sortable_map[$order_by_key] ?? 'display_name';
        $order        = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        // Select only columns that exist now (plus the display_name alias)
        $sql = $wpdb->prepare(
            "SELECT
                inv.id,
                inv.item_id,
                inv.item_name,
                inv.expiration,
                inv.location,
                -- Uncomment if you want these in the UI:
                inv.opened,
                inv.percent_remaining,
                inv.created,
                COALESCE(inv.item_name, ki.item_name) AS display_name,
                ki.item_name  AS known_item_name,
                ki.category,
                ki.barcode,
                ki.weight,
                ki.product_label_id,
                ki.nutrition_image_id,
                ki.units,
                loc.name AS location_name
            FROM {$inv_table} AS inv
            LEFT JOIN {$known_table} AS ki
              ON ki.id = inv.item_id
            LEFT JOIN {$locations_table} AS loc
                ON loc.id = inv.location
            ORDER BY {$order_by_sql} {$order}
            LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        // Avoid echoing SQL in production; if needed, log with error_log().
        // if (defined('WP_DEBUG') && WP_DEBUG) { error_log($sql); }

        $rows = $wpdb->get_results($sql, ARRAY_A);

        // ✅ WP_List_Table expects this:
        $this->items = $rows;

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil($total_items / $per_page),
        ]);
    }
}
