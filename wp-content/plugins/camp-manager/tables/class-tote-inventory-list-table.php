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

    private $tote_id;

    public function __construct($tote_id = null)
    {
        $this->tote_id = $tote_id;
        parent::__construct([
            'singular' => 'Tote Inventory Item',
            'plural' => 'Tote Inventory Items',
            'ajax' => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'inventory_name' => 'Inventory Name',
            'tote_name' => 'Tote Name',
            'quantity' => 'Quantity',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id' => ['id', true],
            'inventory_name' => ['inventory_name', false],
            'tote_name' => ['tote_name', false],
            'quantity' => ['quantity', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        return match ($column_name) {
            'id' => esc_html($item['id']),
            'inventory_name' => esc_html($item['inventory_name']),
            'tote_name' => esc_html($item['tote_name']),
            'quantity' => esc_html($item['quantity']),
            default => isset($item[$column_name]) ? esc_html($item[$column_name]) : '',
        };
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
                    "DELETE FROM $table WHERE id IN ($placeholders)",
                    ...$ids
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

        $per_page = 100;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;
        $table = "{$wpdb->prefix}mf_tote_inventory";

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        $order_by = $_GET['orderby'] ?? 'id';
        $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        $sortable_columns = array_keys($this->get_sortable_columns());
        if (!in_array($order_by, $sortable_columns, true)) {
            $order_by = 'id';
        }

        $order_by = esc_sql($order_by);
        $order = ($order === 'ASC') ? 'ASC' : 'DESC';

        // Build WHERE clause if tote_id is provided
        $where = '';
        $params = [$per_page, $offset];
        if (!empty($this->tote_id)) {
            $where = 'WHERE ti.tote_id = %d';
            array_unshift($params, $this->tote_id);
        }

        $sql = $wpdb->prepare(
            "SELECT ti.id, ti.inventory_id, ti.tote_id, ti.quantity, i.name AS inventory_name, t.name AS tote_name
             FROM $table ti
             LEFT JOIN {$wpdb->prefix}mf_inventory i ON ti.inventory_id = i.id
             LEFT JOIN {$wpdb->prefix}mf_totes t ON ti.tote_id = t.id
             $where
             ORDER BY $order_by $order
             LIMIT %d OFFSET %d",
            ...$params
        );

        $this->data = $wpdb->get_results($sql, ARRAY_A);
        $this->items = $this->data;

        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="inventory-item[]" value="%s" />', esc_attr($item['id']));
    }

    public function column_tote_name($item)
    {
        $url = admin_url("admin.php?page=camp-manager-add-tote&id={$item['tote_id']}");
        return '<a href="' . esc_url($url) . '">' . esc_html($item['tote_name']) . '</a>';
    }

    public function column_inventory_name($item)
    {
        $url = admin_url("admin.php?page=camp-manager-add-tote-inventory&id={$item['inventory_id']}");
        return '<a href="' . esc_url($url) . '">' . esc_html($item['inventory_name']) . '</a>';
    }
}
