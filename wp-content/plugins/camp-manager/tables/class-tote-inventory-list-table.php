<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerToteInventoryTable extends WP_List_Table
{
    private $data;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Tote Inventory Item',
            'plural'   => 'Tote Inventory Items',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'id'           => 'ID',
            'inventory_id' => 'Inventory ID',
            'tote_id'      => 'Tote ID',
            'quantity'     => 'Quantity',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'           => ['id', true],
            'inventory_id' => ['inventory_id', false],
            'tote_id'      => ['tote_id', false],
            'quantity'     => ['quantity', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'inventory_id':
                return esc_html($item['inventory_id']);
            case 'tote_id':
                return esc_html($item['tote_id']);
            case 'quantity':
                return esc_html($item['quantity']);
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function get_primary_column_name()
    {
        return 'id';
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            if (!empty($_POST['inventory-item']) && is_array($_POST['inventory-item'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_tote_inventory";
                $ids = array_map('intval', $_POST['inventory-item']);
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM $table WHERE id IN ($placeholders)", ...$ids
                ));
            }
        }
    }

    public function get_bulk_actions()
    {
        return [
            'delete' => 'Delete',
        ];
    }

    public function prepare_items()
    {
        global $wpdb;

        $per_page     = 100;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;
        $table        = "{$wpdb->prefix}mf_tote_inventory";

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        $order_by = $_GET['orderby'] ?? 'id';
        $order    = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        $sortable_columns = array_keys($this->get_sortable_columns());
        if (!in_array($order_by, $sortable_columns, true)) {
            $order_by = 'id';
        }

        $order_by = esc_sql($order_by);
        $order    = ($order === 'ASC') ? 'ASC' : 'DESC';

        $sql = $wpdb->prepare(
            "SELECT id, inventory_id, tote_id, quantity
             FROM $table
             ORDER BY $order_by $order
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        $this->data = $wpdb->get_results($sql, ARRAY_A);
        $this->items = $this->data;

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="inventory-item[]" value="%s" />', esc_attr($item['id']));
    }
}
