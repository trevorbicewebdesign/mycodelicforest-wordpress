<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerTotesTable extends WP_List_Table
{
    private $data;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Tote',
            'plural'   => 'Totes',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'       => '<input type="checkbox" />',
            'id'       => 'ID',
            'name'     => 'Name',
            'weight'   => 'Weight',
            'uid'      => 'UID',
            'status'   => 'Status',
            'location' => 'Location',
            'size'     => 'Size',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'       => ['id', true],
            'name'     => ['name', false],
            'weight'   => ['weight', false],
            'uid'      => ['uid', false],
            'status'   => ['status', false],
            'location' => ['location', false],
            'size'     => ['size', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'name':
                $edit_url = admin_url('admin.php?page=camp-manager-add-tote&id=' . urlencode($item['id']));
                $description = isset($item['description']) ? '<div class="description">' . esc_html($item['description']) . '</div>' : '';
                return sprintf(
                    '<a href="%s">%s</a>%s',
                    esc_url($edit_url),
                    esc_html(stripslashes($item['name'])),
                    $description
                );
            case 'weight':
                return $item['weight'] !== null ? number_format((float) $item['weight'], 2) . ' lbs' : '';
            case 'uid':
                return esc_html($item['uid']);
            case 'status':
                return esc_html($item['status']);
            case 'location':
                return esc_html($item['location']);
            case 'size':
                return esc_html($item['size']);
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function get_primary_column_name() {
        return 'name';
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            if (!empty($_POST['tote-item']) && is_array($_POST['tote-item'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_totes";
                $ids = array_map('intval', $_POST['tote-item']);
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
        $table        = "{$wpdb->prefix}mf_totes";

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
            "SELECT id, name, weight, uid, status, location, size
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
        return sprintf('<input type="checkbox" name="tote-item[]" value="%s" />', esc_attr($item['id']));
    }
}
