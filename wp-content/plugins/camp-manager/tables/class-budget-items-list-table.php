<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerBudgetItemsTable extends WP_List_Table
{
    private $data;
    private $category_id;

    public function __construct($category_id = null)
    {
        // If a category ID is provided, we can use it to filter items
        if ($category_id) {
            $this->category_id = (int) $category_id;
        } else {
            $this->category_id = null;
        }

        parent::__construct([
            'singular' => 'Budget Items',
            'plural'   => 'Budget Items',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'    => '<input type="checkbox" />', // For bulk actions
            'id'    => 'ID',
            'name' => 'Name',
            'category' => 'Category',
            'price'  => 'Price',
            'quantity' => 'Quantity',
            'subtotal' => 'Subtotal',
            'total' => 'Total', 
            'purchased' => 'Purchased',
            'priority' => 'Priority',
            'link' => 'Link',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'    => ['id', true],
            'name' => ['name', false],
            'category' => ['category', false],
            'price'  => ['price', false],
            'quantity'   => ['quantity', false],
            'subtotal' => ['subtotal', false],
            'total' => ['total', false],
            'purchased' => ['purchased', false],
            'priority' => ['priority', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'name':
                $name = esc_html(stripslashes($item['name']));
                $url = admin_url('admin.php?page=camp-manager-add-budget-item&id=' . urlencode($item['id']));
                return sprintf('<a href="%s">%s</a>', esc_url($url), $name);
            case 'price':
                return '$' . number_format((float) $item['price'], 2);
            case 'quantity':
                return number_format((float) $item['quantity'], 0);
            case 'subtotal':
                return '$' . number_format((float) $item['subtotal'], 2);
            case 'total':
                return '$' . number_format((float) $item['total'], 2);
            case 'purchased':
                // check if the receipt_item_id is set, that would represent a purchase
                // If the item is purchased, it will have a receipt_item_id set
                // we also need to link to the receipt if it exist
                if (!empty($item['receipt_item_id']) && !empty($item['receipt_id'])) {
                    $receipt_url = admin_url('admin.php?page=camp-manager-add-receipt&id=' . urlencode($item['receipt_id']));
                    return sprintf('<a href="%s">Yes</a>', esc_url($receipt_url));
                }
                return "No";
            case 'priority':
                return esc_html($item['priority']);
            case 'link':
                if (!empty($item['link'])) {
                    $icon = '<span class="dashicons dashicons-admin-links" style="font-size:16px;vertical-align:middle;"></span>';
                    return sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer" title="%s">%s</a>',
                        esc_url($item['link']),
                        esc_attr($item['link']),
                        $icon
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
            if (!empty($_POST['budget-item']) && is_array($_POST['budget-item'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_budget_items";
                $ids = array_map('intval', $_POST['budget-item']);
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

        $per_page     = 100;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;
        $table        = "{$wpdb->prefix}mf_budget_items";

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        $order_by = $_GET['orderby'] ?? 'id';
        $order    = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        $sortable_columns = array_keys($this->get_sortable_columns());
        if (!in_array($order_by, $sortable_columns, true)) {
            $order_by = 'id';
        }

        $order_by = esc_sql($order_by);
        $order    = ($order === 'ASC') ? 'ASC' : 'DESC';

        // Join with the categories table to get the category name
        $categories_table = "{$wpdb->prefix}mf_budget_category";

        if ($this->category_id !== null) {
            $sql = $wpdb->prepare(
            "SELECT bi.id, c.name AS category, bi.name, bi.price, bi.quantity, bi.subtotal, bi.tax, bi.total, bi.priority, bi.link, bi.receipt_item_id, bi.receipt_id, bi.link
             FROM $table AS bi
             LEFT JOIN $categories_table AS c ON bi.category_id = c.id
             WHERE bi.category_id = %d
             ORDER BY $order_by $order
             LIMIT %d OFFSET %d",
            $this->category_id,
            $per_page,
            $offset
            );
            // Update total_items for pagination when filtering
            $total_items = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE category_id = %d", $this->category_id)
            );
        } else {
            $sql = $wpdb->prepare(
            "SELECT bi.id, c.name AS category, bi.name, bi.price, bi.quantity, bi.subtotal, bi.tax, bi.total, bi.priority, bi.link, bi.receipt_item_id, bi.receipt_id, bi.link
             FROM $table AS bi
             LEFT JOIN $categories_table AS c ON bi.category_id = c.id
             ORDER BY $order_by $order
             LIMIT %d OFFSET %d",
            $per_page,
            $offset
            );
        }

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
        return sprintf('<input type="checkbox" name="budget-item[]" value="%s" />', esc_attr($item['id']));
    }
}
