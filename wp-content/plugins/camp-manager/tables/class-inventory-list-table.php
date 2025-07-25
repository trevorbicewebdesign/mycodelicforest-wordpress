<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerInventoryTable extends WP_List_Table
{
    private $data;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Inventory Item',
            'plural'   => 'Inventory Items',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'           => '<input type="checkbox" />',
            'id'           => 'ID',
            'name'         => 'Name',
            'manufacturer' => 'Manufacturer',
            'model'        => 'Model',
            'description'  => 'Description',
            'quantity'     => 'Quantity',
            'location'     => 'Location',
            'weight'       => 'Weight',
            'category'     => 'Category',
            'category_name'=> 'Category Name',
            'links'        => 'Links',
            'amp'          => 'Amp',
            'set_name'     => 'Set Name',
            'photo'        => 'Photo',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'           => ['id', true],
            'name'         => ['name', false],
            'manufacturer' => ['manufacturer', false],
            'model'        => ['model', false],
            'quantity'     => ['quantity', false],
            'location'     => ['location', false],
            'weight'       => ['weight', false],
            'category'     => ['category', false],
            'category_name'=> ['category_name', false],
            'amp'          => ['amp', false],
            'set_name'     => ['set_name', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'name':
                return esc_html(stripslashes($item['name']));
            case 'manufacturer':
                return esc_html($item['manufacturer']);
            case 'model':
                return esc_html($item['model']);
            case 'description':
                return esc_html($item['description']);
            case 'quantity':
                return number_format((float) $item['quantity'], 0);
            case 'location':
                return esc_html($item['location']);
            case 'weight':
                return number_format((float) $item['weight'], 2) . ' kg';
            case 'category':
                return esc_html($item['category']);
            case 'category_name':
                return esc_html($item['category_name']);
            case 'links':
                if (!empty($item['links'])) {
                    $icon = '<span class="dashicons dashicons-admin-links" style="font-size:16px;vertical-align:middle;"></span>';
                    return sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer" title="%s">%s</a>',
                        esc_url($item['links']),
                        esc_attr($item['links']),
                        $icon
                    );
                }
                return '';
            case 'amp':
                return $item['amp'] !== null ? number_format((float) $item['amp'], 2) : '';
            case 'set_name':
                return esc_html($item['set_name']);
            case 'photo':
                if (!empty($item['photo'])) {
                    return sprintf(
                        '<img src="%s" alt="%s" style="max-width:60px;max-height:60px;" />',
                        esc_url($item['photo']),
                        esc_attr($item['name'])
                    );
                }
                return '';
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            if (!empty($_POST['inventory-item']) && is_array($_POST['inventory-item'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_inventory";
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
        $table        = "{$wpdb->prefix}mf_inventory";

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
            "SELECT id, uuid, name, manufacturer, model, description, quantity, photo, location, weight, category, category_name, links, amp, set_name
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
