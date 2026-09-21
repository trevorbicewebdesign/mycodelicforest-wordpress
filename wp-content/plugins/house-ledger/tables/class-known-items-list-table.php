<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class HouseLedgerKnownItemsTable extends WP_List_Table
{
    private $data;
    public $known_items;

    public function __construct(HouseLedgerKnownItems $HouseLedgerKnownItems)
    {
        $this->known_items = $HouseLedgerKnownItems;

        parent::__construct([
            'singular' => 'Item',
            'plural'   => 'Items',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'    => '<input type="checkbox" />', // For bulk actions
            'id'    => 'ID',
            'item_name' => 'Name',
            'category' => 'Category',
            'barcode'  => 'Barcode',
            'weight'   => 'Weight',
            'units'    => 'Units',
            'has_expiration'   => 'Has Expiration',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'    => ['id', true],
            
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'item_name':
                // Display name needs to link to the edit page
                return '<a href="' . esc_url(admin_url('admin.php?page=house-ledger-add-known-item&action=edit&id=' . $item['id'])) . '">' . esc_html($item['item_name']) . '</a>';
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function process_bulk_action()
    {
        
        if ('delete' === $this->current_action()) {
            
            if (!empty($_POST['item']) && is_array($_POST['item'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}hl_known_items";
                $ids = array_map('intval', $_POST['item']);
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table WHERE id IN ($placeholders)", ...$ids
                ));
            }
            
        }            
    }

    public function single_row($item)
    {
        $class = '';
        echo '<tr class="' . esc_attr($class) . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    public function get_bulk_actions()
    {
        return [
            'delete' => 'Delete',
            'duplicate' => 'Duplicate',
        ];
    }

    public function prepare_items()
    {
        global $wpdb;

        $per_page     = 100;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;

        $known_table = "{$wpdb->prefix}hl_known_items";

        // ---- Count ----
        $total_items = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$known_table}");

        // ---- Sorting ----
        $sortable_map = [
            'item_name'      => 'item_name',
            'category'       => 'category',
            'barcode'        => 'barcode',
            'units'          => 'units',
            'weight'         => 'weight',
            'has_expiration' => 'has_expiration',
            'parent'         => 'parent',
            'id'             => 'id',
        ];

        $order_by_key = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'item_name';
        $order_by_sql = $sortable_map[$order_by_key] ?? 'item_name';
        $order        = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        // ---- Query ----
        $sql = $wpdb->prepare(
            "SELECT
                id,
                item_name,
                category,
                barcode,
                units,
                weight,
                has_expiration,
                parent
            FROM {$known_table}
            ORDER BY {$order_by_sql} {$order}
            LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        $rows = $wpdb->get_results($sql, ARRAY_A);

        // ---- Assign to WP_List_Table ----
        $this->items = $rows;
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil($total_items / $per_page),
        ]);
    }

    // Optional: if you want bulk actions with checkboxes
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="item[]" value="%s" />', esc_attr($item['id']));
    }
}
