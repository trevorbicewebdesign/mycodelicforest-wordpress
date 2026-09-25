<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CampManagerRosterTable extends WP_List_Table
{
    const PER_PAGE = 20;
    // Id of the GET form that carries the search box and the filter dropdowns. The dropdowns
    // sit in the table's tablenav, inside the POST form used for bulk actions, so they point
    // back at this form with the `form` attribute rather than nesting forms.
    const FILTER_FORM = 'roster-filters';

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
            'cb'            => '<input type="checkbox" />', // For bulk actions
            'member'        => 'Member',
            'playaname'     => 'Playa name',
            'camp_dues'     => 'Camp dues',
            'dues_category' => 'Dues category',
            'payment'       => 'Payment',
            'status'        => 'Status',
            'roles'         => 'Camp roles',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'member'        => ['member', false],
            'playaname'     => ['playaname', false],
            'camp_dues'     => ['camp_dues', false],
            'dues_category' => ['dues_category', false],
            'payment'       => ['payment', false],
            'status'        => ['status', false],
            'roles'         => ['roles', false],
        ];
    }

    protected function get_table_classes()
    {
        return array_merge(parent::get_table_classes(), ['roster-table']);
    }

    /** The search text, status view and dropdown filters currently asked for in the URL. */
    public function filters(): array
    {
        $pick = static function (string $key, array $allowed): string {
            $value = isset($_GET[$key]) ? sanitize_key(wp_unslash($_GET[$key])) : '';
            return in_array($value, $allowed, true) ? $value : '';
        };

        return [
            'search'         => isset($_GET['s']) ? trim((string) wp_unslash($_GET['s'])) : '',
            'member_status'  => $pick('member_status', ['confirmed', 'dropped']),
            'payment_status' => $pick('payment_status', ['paid', 'unpaid']),
            'dues_category'  => $pick('dues_category', ['standard', 'low_income']),
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'playaname':
                $playaname = trim(stripslashes($item['playaname'] ?? ''));
                return $playaname === '' ? $this->empty_cell() : esc_html($playaname);
            case 'camp_dues':
                return '$' . number_format((float) $item['camp_dues'], 2);
            case 'dues_category':
                return esc_html($item['dues_category']);
            case 'payment':
                return $item['paid']
                    ? '<span class="roster-payment roster-payment--paid"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>Paid</span>'
                    : '<span class="roster-payment roster-payment--unpaid"><span class="roster-payment__ring" aria-hidden="true"></span>Unpaid</span>';
            case 'status':
                $status = ucfirst(strtolower(trim((string) ($item['status'] ?? ''))));
                return $status === '' ? $this->empty_cell() : esc_html($status);
            case 'roles':
                return $item['roles'] ? esc_html(implode(', ', $item['roles'])) : $this->empty_cell();
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function column_member($item)
    {
        $name = trim(stripslashes($item['fname'] ?? '') . ' ' . stripslashes($item['lname'] ?? ''));
        $edit_url = admin_url('admin.php?page=camp-manager-add-member&id=' . (int) $item['id']);

        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Edit</a>',
        ];
        // Members with a WordPress account can be opened from here (the account's profile).
        $wpid = (int) ($item['wpid'] ?? 0);
        if ($wpid && current_user_can('edit_user', $wpid)) {
            $actions['view'] = '<a href="' . esc_url(get_edit_user_link($wpid)) . '">View</a>';
        }

        return '<strong><a class="row-title" href="' . esc_url($edit_url) . '">'
            . esc_html($name === '' ? '(no name)' : $name)
            . '</a></strong>'
            . $this->row_actions($actions);
    }

    private function empty_cell(): string
    {
        return '<span class="roster-empty" aria-hidden="true">&mdash;</span><span class="screen-reader-text">None</span>';
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            check_admin_referer('bulk-' . $this->_args['plural']);
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

    /** All / Confirmed / Dropped, each with its head count for the viewed season. */
    protected function get_views()
    {
        $counts = (new CampManagerRoster())->countByStatus();
        $current = $this->filters()['member_status'];
        $base = admin_url('admin.php?page=camp-manager-members');

        $views = [];
        foreach (['' => ['All', 'all'], 'confirmed' => ['Confirmed', 'confirmed'], 'dropped' => ['Dropped', 'dropped']] as $status => [$label, $count]) {
            $url = $status === '' ? $base : add_query_arg('member_status', $status, $base);
            $views[$status === '' ? 'all' : $status] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
                esc_url($url),
                $status === $current ? ' class="current" aria-current="page"' : '',
                esc_html($label),
                $counts[$count]
            );
        }
        return $views;
    }

    protected function extra_tablenav($which)
    {
        if ('top' !== $which) {
            return;
        }
        $filters = $this->filters();
        $dropdowns = [
            'payment_status' => ['Payment status', ['paid' => 'Paid', 'unpaid' => 'Unpaid']],
            'dues_category'  => ['Dues category', ['standard' => 'Standard', 'low_income' => 'Low income']],
        ];
        ?>
        <div class="alignleft actions">
            <?php foreach ($dropdowns as $name => [$label, $options]): ?>
                <label class="screen-reader-text" for="roster-filter-<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label>
                <select name="<?php echo esc_attr($name); ?>" id="roster-filter-<?php echo esc_attr($name); ?>" form="<?php echo esc_attr(self::FILTER_FORM); ?>">
                    <option value=""><?php echo esc_html($label); ?></option>
                    <?php foreach ($options as $value => $text): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($filters[$name], $value); ?>><?php echo esc_html($text); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endforeach; ?>
            <?php submit_button('Filter', '', 'filter_action', false, ['id' => 'roster-filter-submit', 'form' => self::FILTER_FORM]); ?>
        </div>
        <?php
    }

    public function no_items()
    {
        echo 'No members found.';
    }

    public function single_row($item)
    {
        $classes = ['roster-row'];
        if ($item['dropped']) {
            $classes[] = 'is-dropped';
        }

        echo '<tr class="' . esc_attr(implode(' ', $classes)) . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    public function prepare_items()
    {
        global $wpdb;

        $per_page     = self::PER_PAGE;
        $current_page = $this->get_pagenum();
        $table        = "{$wpdb->prefix}mf_roster";
        $filters      = $this->filters();

        $where = ['season = %d'];
        $args  = [CampManagerSeason::selected()];
        if ($filters['member_status']) {
            $where[] = 'status = %s';
            $args[]  = ucfirst($filters['member_status']);
        }
        if ($filters['payment_status'] === 'paid') {
            $where[] = 'fully_paid = 1';
        } elseif ($filters['payment_status'] === 'unpaid') {
            $where[] = '(fully_paid IS NULL OR fully_paid = 0)';
        }
        if ($filters['dues_category'] === 'low_income') {
            $where[] = 'low_income = 1';
        } elseif ($filters['dues_category'] === 'standard') {
            $where[] = '(low_income IS NULL OR low_income = 0)';
        }
        if ($filters['search'] !== '') {
            $like    = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = "(fname LIKE %s OR lname LIKE %s OR playaname LIKE %s OR email LIKE %s OR CONCAT_WS(' ', fname, lname) LIKE %s)";
            array_push($args, $like, $like, $like, $like, $like);
        }

        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE " . implode(' AND ', $where), ...$args),
            ARRAY_A
        ) ?: [];

        // Camp dues and roles are worked out per member, so sorting happens here rather than
        // in SQL. A season's roster is small enough that this is one page's worth of work.
        $ids   = array_column($rows, 'id');
        $dues  = $this->CampManagerLedger->sumCampDuesByMember($ids);
        $roles = (new CampManagerRoles())->getRoleNamesByMember($ids);
        foreach ($rows as &$member) {
            $member['camp_dues']     = $dues[(int) $member['id']] ?? 0.0;
            $member['roles']         = $roles[$member['id']] ?? [];
            $member['paid']          = !empty($member['fully_paid']);
            $member['dues_category'] = !empty($member['low_income']) ? 'Low income' : 'Standard';
            $member['dropped']       = strtolower(trim((string) ($member['status'] ?? ''))) === 'dropped';
        }
        unset($member);

        $orderby = isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : '';
        if (!array_key_exists($orderby, $this->get_sortable_columns())) {
            $orderby = '';
        }
        // Newest first until a column is chosen; a chosen column starts ascending.
        $descending = $orderby === ''
            ? true
            : (isset($_GET['order']) && strtolower(sanitize_key(wp_unslash($_GET['order']))) === 'desc');

        usort($rows, function (array $a, array $b) use ($orderby, $descending) {
            // Always put Dropped at the end
            if ($a['dropped'] !== $b['dropped']) {
                return $a['dropped'] <=> $b['dropped'];
            }
            $cmp = $orderby === '' ? 0 : $this->compare($a, $b, $orderby);
            if ($cmp === 0) {
                return (int) $b['id'] <=> (int) $a['id'];
            }
            return $descending ? -$cmp : $cmp;
        });

        $total_items = count($rows);
        $this->items = $this->data = array_slice($rows, ($current_page - 1) * $per_page, $per_page);

        // Set required column headers
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil($total_items / $per_page),
        ]);
    }

    private function compare(array $a, array $b, string $orderby): int
    {
        switch ($orderby) {
            case 'member':
                return strnatcasecmp(
                    trim(stripslashes($a['fname'] ?? '') . ' ' . stripslashes($a['lname'] ?? '')),
                    trim(stripslashes($b['fname'] ?? '') . ' ' . stripslashes($b['lname'] ?? ''))
                );
            case 'playaname':
                return strnatcasecmp(stripslashes($a['playaname'] ?? ''), stripslashes($b['playaname'] ?? ''));
            case 'camp_dues':
                return $a['camp_dues'] <=> $b['camp_dues'];
            case 'dues_category':
                return (int) !empty($a['low_income']) <=> (int) !empty($b['low_income']);
            case 'payment':
                return (int) $a['paid'] <=> (int) $b['paid'];
            case 'status':
                return strcasecmp((string) ($a['status'] ?? ''), (string) ($b['status'] ?? ''));
            case 'roles':
                return strnatcasecmp(implode(', ', $a['roles']), implode(', ', $b['roles']));
        }
        return 0;
    }

    // Optional: if you want bulk actions with checkboxes
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="member[]" value="%s" />', esc_attr($item['id']));
    }
}
