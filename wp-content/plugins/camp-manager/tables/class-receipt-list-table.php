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
    private $roster;

    public function __construct(CampManagerRoster $CampManagerRoster)
    {
        $this->roster = $CampManagerRoster;

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
            'cmid' => 'User',
            'store' => 'Store',
            'date'  => 'Date',
            'subtotal' => 'Subtotal',
            'tax'   => 'Tax',
            'shipping' => 'Shipping',
            'total' => 'Total',
            'reimbursed' => 'Reimbursed',
            'ledger_id' => 'Ledger ID',
            'link' => 'Link',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'    => ['id', true],
            'store' => ['store', false],
            'date'  => ['date', false],
            'tax'   => ['tax', false],
            'subtotal' => ['subtotal', false],
            'shipping' => ['shipping', false],
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
                $url = admin_url('admin.php?page=camp-manager-add-receipt&id=' . urlencode($item['id']));
                return sprintf('<a href="%s">%s</a>', esc_url($url), $store);
            case 'date':
                return esc_html(date('Y-m-d', strtotime($item['date'])));
            case 'total':
                return '$' . number_format((float) $item['total'], 2);
            case 'subtotal':
                return '$' . number_format((float) $item['subtotal'], 2);
            case 'shipping':
                return '$' . number_format((float) $item['shipping'], 2);
            case 'reimbursed':
                return !empty($item['reimbursed']) ? 'Yes' : 'No';
            case 'cmid':
                $user_id = intval($item['cmid']);
                $user = $this->roster->getMemberById( $user_id);
                if ($user) {
                    return sprintf('<a href="%s">%s</a>', esc_url(admin_url('user-edit.php?user_id=' . $user_id)), esc_html($user->fname . ' ' . $user->lname));
                } else {
                    return 'Treasury';
                }
            
            case 'ledger_id':
                // Grab the ledger by the receipt ID
                global $wpdb;

                $table = "{$wpdb->prefix}mf_ledger_line_items";
                $sql =  "SELECT ledger_id FROM $table WHERE receipt_id = %d";
                $sql = $wpdb->prepare($sql, $item['id']);
                $ledger = $wpdb->get_row($sql, ARRAY_A);
                if ($ledger) {
                    return sprintf('<a href="%s">%s</a>', esc_url(admin_url('admin.php?page=camp-manager-add-ledger&id=' . $ledger['ledger_id'])), esc_html($ledger['ledger_id']));
                } else {
                    return '';
                }
            case 'link':
                if (!empty($item['link'])) {
                    return sprintf('<a href="%s" target="_blank" rel="noopener noreferrer">View</a>', esc_url($item['link']));
                }
                return '';
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
        else if('duplicate' === $this->current_action()) {
            if (!empty($_POST['receipt']) && is_array($_POST['receipt'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_receipts";
                $items_table = "{$wpdb->prefix}mf_receipt_items";
                foreach ($_POST['receipt'] as $id) {
                    $id = intval($id);
                    // Duplicate receipt
                    $receipt = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id), ARRAY_A);
                    if ($receipt) {
                        unset($receipt['id']); // Remove ID to insert as new
                        $wpdb->insert($table, $receipt);
                        $new_receipt_id = $wpdb->insert_id;

                        // Duplicate receipt items
                        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $items_table WHERE receipt_id = %d", $id), ARRAY_A);
                        foreach ($items as $item) {
                            unset($item['id']); // Remove item ID
                            $item['receipt_id'] = $new_receipt_id; // Link to new receipt
                            $wpdb->insert($items_table, $item);
                        }
                    }
                }
            }
        }
    }

    public function single_row($item)
    {
        $class = !empty($item['reimbursed']) ? 'reimbursed-row' : '';
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
        $table        = "{$wpdb->prefix}mf_receipts";

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table");

        $order_by = $_GET['orderby'] ?? 'date';
        $order    = (isset($_GET['order']) && strtolower($_GET['order']) === 'asc') ? 'ASC' : 'DESC';

        $sortable_columns = array_keys($this->get_sortable_columns());
        if (!in_array($order_by, $sortable_columns, true)) {
            $order_by = 'date';
        }

        $order_by = esc_sql($order_by);
        $order    = ($order === 'ASC') ? 'ASC' : 'DESC';

        $sql = $wpdb->prepare(
            "SELECT id, date, total, store, subtotal, shipping, tax, reimbursed, cmid, link FROM $table ORDER BY $order_by $order LIMIT %d OFFSET %d",
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
