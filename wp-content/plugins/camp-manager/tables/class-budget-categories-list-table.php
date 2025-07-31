<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerBudgetCategoriesTable extends WP_List_Table
{
    private $data;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Budget Category',
            'plural'   => 'Budget Categories',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'    => '<input type="checkbox" />', // For bulk actions
            'id'    => 'ID',
            'name'  => 'Name',
            'description' => 'Description',
            'must_have' => 'Must Have $',
            'should_have' => 'Should Have $',
            'could_have' => 'Could Have $',
            'nice_to_have' => 'Nice to Have $',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'    => ['id', true],
            'name'  => ['name', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'name':
                $name = esc_html(stripslashes($item['name']));
                $url = admin_url('admin.php?page=camp-manager-add-budget-category&id=' . urlencode($item['id']));
                return sprintf('<a href="%s">%s</a>', esc_url($url), $name);
            case 'description':
                return esc_html($item['description']);
            case 'must_have':
                return isset($item['must_have']) ? '$' . number_format((float)$item['must_have'], 2) : '';
            case 'should_have':
                return isset($item['should_have']) ? '$' . number_format((float)$item['should_have'], 2) : '';
            case 'could_have':
                return isset($item['could_have']) ? '$' . number_format((float)$item['could_have'], 2) : '';
            case 'nice_to_have':
                return isset($item['nice_to_have']) ? '$' . number_format((float)$item['nice_to_have'], 2) : '';
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            if (!empty($_POST['budget-categories']) && is_array($_POST['budget-categories'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_budget_category";
                $ids = array_map('intval', $_POST['budget-categories']);
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

        $per_page     = 20;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;
        $table        = "{$wpdb->prefix}mf_budget_category";

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
            "SELECT id, name, description FROM $table ORDER BY $order_by $order LIMIT %d OFFSET %d",
            $per_page,
            $offset
        );

        $this->data = $wpdb->get_results($sql, ARRAY_A);

        

        // Add hardcoded must_have value
        foreach ($this->data as &$item) {
            $CampManagerBudgets = new CampManagerBudgets();
            $item['must_have'] = $CampManagerBudgets->getPriorityTotal($item['id'], '1');
            $item['should_have'] = $CampManagerBudgets->getPriorityTotal($item['id'], '2');
            $item['could_have'] = $CampManagerBudgets->getPriorityTotal($item['id'], '3');
            $item['nice_to_have'] = $CampManagerBudgets->getPriorityTotal($item['id'], '4');
        }
        unset($item);

        $this->items = $this->data;

        // Set required column headers
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }



    public function get_must_have_total()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        // Assuming there is a 'priority' column and '1' means "must have"
        $total = $wpdb->get_var("SELECT SUM(total) FROM $table WHERE priority = 1 AND purchased != 1");
        return $total ? '$' . number_format((float) $total, 2) : '$0.00';
    }

    public function get_should_have_total()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        // Assuming there is a 'priority' column and '2' means "should have"
        $total = $wpdb->get_var("SELECT SUM(total) FROM $table WHERE priority = 2 OR priority = 1 AND purchased != 1");
        return $total ? '$' . number_format((float) $total, 2) : '$0.00';
    }

    public function get_could_have_total()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        // Assuming there is a 'priority' column and '3' means "could have"
        $total = $wpdb->get_var("SELECT SUM(total) FROM $table WHERE priority = 3 OR priority = 2 OR priority = 1 AND purchased != 1");
        return $total ? '$' . number_format((float) $total, 2) : '$0.00';
    }

    public function get_nice_to_have_total()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_budget_items";
        // Assuming there is a 'priority' column and '4' means "nice to have"
        $total = $wpdb->get_var("SELECT SUM(total) FROM $table WHERE priority = 4 OR priority = 3 OR priority = 2 OR priority = 1 AND purchased != 1");
        return $total ? '$' . number_format((float) $total, 2) : '$0.00';
    }

    // Optional: if you want bulk actions with checkboxes
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="budget-categories[]" value="%s" />', esc_attr($item['id']));
    }
}
