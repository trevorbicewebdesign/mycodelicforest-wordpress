<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerReceiptsTable extends WP_List_Table
{
    private $data;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Receipt',
            'plural'   => 'Receipts',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'    => '<input type="checkbox" />', // For bulk actions
            'id'    => 'ID',
            'store' => 'Store',
            'date'  => 'Date',
            'subtotal' => 'Subtotal',
            'tax'   => 'Tax',
            'total' => 'Total',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'    => ['id', true],
            'store' => ['store', false],
            'date'  => ['date', false],
            'tax'   => ['tax', false],
            'total' => ['total', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'store':
                $store = esc_html(stripslashes($item['store']));
                $url = admin_url('admin.php?page=camp-manager-edit-receipt&id=' . urlencode($item['id']));
                return sprintf('<a href="%s">%s</a>', esc_url($url), $store);
            case 'date':
                return esc_html(date('Y-m-d', strtotime($item['date'])));
            case 'total':
                return '$' . number_format((float) $item['total'], 2);
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            if (!empty($_POST['receipt']) && is_array($_POST['receipt'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_receipts";
                $ids = array_map('intval', $_POST['receipt']);
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
            // 'mark_reviewed' => 'Mark as Reviewed',
        ];
    }

    public function prepare_items()
    {
        global $wpdb;

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;
        $table        = "{$wpdb->prefix}mf_receipts";

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
            "SELECT id, date, total, store, subtotal, tax FROM $table ORDER BY $order_by $order LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        $this->data = $wpdb->get_results($sql, ARRAY_A);
        $this->items = $this->data;

        // Set required column headers
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    // Optional: if you want bulk actions with checkboxes
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="receipt[]" value="%s" />', esc_attr($item['id']));
    }
}
