<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerRosterTable extends WP_List_Table
{
    private $data;
    private $CampManagerLedger;

    public function __construct(CampManagerLedger $CampManagerLedger)
    {
        $this->CampManagerLedger = $CampManagerLedger;
        
        // Call the parent constructor with the required parameters
        parent::__construct([
            'singular' => 'Roster',
            'plural'   => 'Roster',
            'ajax'     => false,
        ]);
    }

    public function get_columns()
    {
        return [
            'cb'    => '<input type="checkbox" />', // For bulk actions
            'id'    => 'ID',
            'fname' => 'First Name',
            'lname' => 'Last Name',
            'playaname' => 'Playa Name',
            'camp_dues' => 'Camp Dues',
            'low_income' => 'Low Income',
            'fully_paid' => 'Fully Paid',
            'wpid'  => 'WordPress ID',
            'status' => 'Status',
            'sponsor' => 'Sponsor',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'id'    => ['id', true],
            'fname' => ['fname', false],
            'lname'  => ['lname', false],
            'playaname'   => ['playaname', false],
        ];
    }

    public function column_default($item, $column_name)
    {

        $link = "<a href='/wp-admin/admin.php?page=camp-manager-add-member&id=". esc_attr($item['id']) ."'>";
        switch ($column_name) {
            case 'id':
                return esc_html($item['id']);
            case 'fname':
                return $link . esc_html(stripslashes($item['fname']))."</a>";
            case 'lname':
                return $link . esc_html(stripslashes($item['lname']))."</a>";
            case 'playaname':
                $playaname = esc_html(stripslashes($item['playaname']));
                return $link . $playaname . "</a>";
            case 'email':
                return $link . esc_html(stripslashes($item['email']))."</a>";
            case 'wpid':
                return $link . esc_html($item['wpid'])."</a>";
            case 'camp_dues':
                $camp_dues_paid = $this->CampManagerLedger->sumUserCampDues($item['id']);
                return '$' . number_format((float)$camp_dues_paid, 2);
            case 'low_income':
                return !empty($item['low_income']) ? 'Yes' : '';
            case 'fully_paid':
                return !empty($item['fully_paid']) ? 'Yes' : '';
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            if (!empty($_POST['member']) && is_array($_POST['member'])) {
                global $wpdb;
                $table = "{$wpdb->prefix}mf_roster";
                $ids = array_map('intval', $_POST['member']);
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

    public function single_row($item)
    {
        $class = !empty($item['fully_paid']) ? 'paid-row' : 'unpaid-row';
        echo '<tr class="' . esc_attr($class) . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    public function prepare_items()
    {
        global $wpdb;

        $per_page     = 100;
        $current_page = $this->get_pagenum();
        $offset       = ($current_page - 1) * $per_page;
        $table        = "{$wpdb->prefix}mf_roster";

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
            "SELECT * FROM $table ORDER BY $order_by $order LIMIT %d OFFSET %d",
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
        return sprintf('<input type="checkbox" name="member[]" value="%s" />', esc_attr($item['id']));
    }
}
