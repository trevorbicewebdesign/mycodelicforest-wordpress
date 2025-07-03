<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerLedgerTable extends WP_List_Table
{
    private $data;

    public function __construct()
    {
        parent::__construct([
            'singular' => 'Ledger',
            'plural'   => 'Ledger',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'    => '<input type="checkbox" />', // For bulk actions
            'id'    => 'ID',
            'note' => 'Note',
            'amount' => 'Amount',
            'date'  => 'Date',      
            'receipts' => 'Receipts',    
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'    => ['id', true],
            'amount' => ['amount', false],
            'date'  => ['date', false],
            'note'   => ['note', false],
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'amount':
                return '$' . number_format((float) $item['amount'], 2);
            case 'date':
                return esc_html(date('Y-m-d', strtotime($item['date'])));
            case 'note':
                $edit_url = admin_url('admin.php?page=camp-manager-add-ledger&id=' . intval($item['id']));
                return sprintf(
                    '<a href="%s">%s</a>',
                    esc_url($edit_url),
                    esc_html($item['note'])
                );
            case 'receipts':
                // get all the receipt ids from the ledger line items then display them as links, comma separated
                global $wpdb;
                $table = "{$wpdb->prefix}mf_ledger_line_items";
                $sql = "SELECT receipt_id FROM $table WHERE ledger_id = %d AND receipt_id > 0";
                $sql = $wpdb->prepare($sql, $item['id']);
                $receipts = $wpdb->get_col($sql);
                if ($receipts) {
                    $links = array_map(function($rid) {
                        $url = admin_url('admin.php?page=camp-manager-add-receipt&id=' . intval($rid));
                        return sprintf('<a href="%s">%d</a>', esc_url($url), intval($rid));
                    }, $receipts);
                    return implode(', ', $links);
                }
                return '';
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function get_total_amount()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_ledger";
        $total = $wpdb->get_var("SELECT SUM(amount) FROM $table");
        return $total ? '$' . number_format((float) $total, 2) : '$0.00';
    }

    public function get_total_camp_dues()
    {
        global $wpdb;
        $table = "{$wpdb->prefix}mf_ledger";
        $total = $wpdb->get_var(
            "SELECT SUM(amount) FROM $table WHERE type IN ('Camp Dues', 'Partial Camp Dues')"
        );
        return $total ? '$' . number_format((float) $total, 2) : '$0.00';
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            if (!empty($_POST['ledger']) && is_array($_POST['ledger'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_ledger";
                $ids = array_map('intval', $_POST['ledger']);
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
        $table        = "{$wpdb->prefix}mf_ledger";

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        $order_by = $_GET['orderby'] ?? 'id';
        $order    = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        $sortable_columns = array_keys($this->get_sortable_columns());
        // Default sort order: by date descending
        if (empty($_GET['orderby'])) {
            $order_by = 'date';
            $order = 'DESC';
        }
        if (!in_array($order_by, $sortable_columns, true)) {
            $order_by = 'date';
        }

        $order_by = esc_sql($order_by);
        $order    = ($order === 'ASC') ? 'ASC' : 'DESC';
        

        $sql = $wpdb->prepare(
            "SELECT id, amount, date, note FROM $table ORDER BY $order_by $order LIMIT %d OFFSET %d",
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
        return sprintf('<input type="checkbox" name="ledger[]" value="%s" />', esc_attr($item['id']));
    }
}
