<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . '/class-list-table.php';

/**
 * The "Totes" tab of the Inventory admin page: every tote with its size, status, location,
 * weight and how many items it holds, searchable and filterable by status, location and size.
 */
class CampManagerTotesTable extends CampManagerListTable
{
    const FILTER_FORM = 'totes-filters';
    const SIZES = ['Full', 'Half'];

    private $inventory;

    public function __construct(?CampManagerInventory $inventory = null)
    {
        $this->inventory = $inventory ?: new CampManagerInventory();
        parent::__construct([
            'singular' => 'tote',
            'plural'   => 'totes',
            'ajax'     => false,
        ]);
    }

    public function filterFormId(): string
    {
        return self::FILTER_FORM;
    }

    public function get_columns()
    {
        return [
            'cb'       => '<input type="checkbox" />',
            'name'     => 'Tote',
            'size'     => 'Size',
            'status'   => 'Status',
            'location' => 'Location',
            'weight'   => 'Weight',
            'items'    => 'Items',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'name'     => ['name', false],
            'size'     => ['size', false],
            'status'   => ['status', false],
            'location' => ['location', false],
            'weight'   => ['weight', false],
            'items'    => ['items', false],
        ];
    }

    public function get_primary_column_name()
    {
        return 'name';
    }

    /** The search text, dropdown filters and page size currently asked for in the URL. */
    public function filters(): array
    {
        return [
            'search'   => $this->searchTerm(),
            'status'   => $this->pick('status', null),
            'location' => $this->pick('location', null),
            'size'     => $this->pick('size', self::SIZES),
            'per_page' => $this->perPage(),
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'size':
                return trim((string) $item['size']) === '' ? $this->emptyCell() : esc_html($item['size']);
            case 'status':
                $status = trim((string) ($item['status'] ?? ''));
                return $status === '' ? $this->emptyCell() : esc_html(ucfirst(strtolower($status)));
            case 'location':
                return trim((string) ($item['location'] ?? '')) === '' ? $this->emptyCell() : esc_html($item['location']);
            case 'weight':
                return $item['weight'] === null || $item['weight'] === '' ? $this->emptyCell() : number_format((float) $item['weight'], 2) . ' lbs';
            case 'items':
                if (!$item['items']) {
                    return $this->emptyCell();
                }
                return sprintf(
                    '%s<span class="cm-item-meta">%s</span>',
                    number_format($item['items']),
                    esc_html(number_format($item['pieces']) . ' ' . ($item['pieces'] === 1 ? 'piece' : 'pieces'))
                );
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function column_name($item)
    {
        $edit_url = admin_url('admin.php?page=camp-manager-add-tote&id=' . (int) $item['id']);
        $return = base64_encode($edit_url);
        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Edit</a>',
            'pack' => '<a href="' . esc_url(admin_url('admin.php?page=camp-manager-add-tote-inventory&tote_id=' . (int) $item['id'] . '&return=' . $return)) . '">Add item</a>',
        ];
        $uid = trim((string) ($item['uid'] ?? ''));
        return $this->titleCell(trim(stripslashes((string) $item['name'])), $edit_url, [$uid === '' ? '' : 'UID ' . $uid], $actions);
    }

    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="tote[]" value="%d" />', (int) $item['id']);
    }

    public function get_bulk_actions()
    {
        return ['delete' => 'Delete'];
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $this->inventory->deleteTotes($this->bulkIds('tote'));
        }
    }

    public function no_items()
    {
        echo 'No totes found.';
    }

    protected function extra_tablenav($which)
    {
        if ('top' !== $which) {
            return;
        }
        $statuses = [];
        foreach ($this->inventory->toteStatuses() as $status) {
            $statuses[$status] = ucfirst(strtolower($status));
        }
        $this->renderFilterDropdowns([
            'status'   => ['All statuses', $statuses],
            'location' => ['All locations', array_combine($this->inventory->toteLocations(), $this->inventory->toteLocations())],
            'size'     => ['All sizes', array_combine(self::SIZES, self::SIZES)],
        ], $this->filters());
        $this->renderPerPage();
    }

    public function prepare_items()
    {
        global $wpdb;
        $table   = "{$wpdb->prefix}mf_totes";
        $filters = $this->filters();

        $where = ['1=1'];
        $args  = [];
        if ($filters['status'] !== '') {
            $where[] = 'status = %s';
            $args[]  = $filters['status'];
        }
        if ($filters['location'] !== '') {
            $where[] = 'location = %s';
            $args[]  = $filters['location'];
        }
        if ($filters['size'] !== '') {
            $where[] = 'size = %s';
            $args[]  = $filters['size'];
        }
        if ($filters['search'] !== '') {
            $like    = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(name LIKE %s OR uid LIKE %s OR status LIKE %s OR location LIKE %s)';
            array_push($args, $like, $like, $like, $like);
        }

        $sql  = "SELECT id, name, weight, uid, status, location, size FROM $table WHERE " . implode(' AND ', $where);
        $rows = $wpdb->get_results($args ? $wpdb->prepare($sql, ...$args) : $sql, ARRAY_A) ?: [];

        $counts = $this->inventory->countToteItemsByTote(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $row['items']  = $counts[(int) $row['id']]['items'] ?? 0;
            $row['pieces'] = $counts[(int) $row['id']]['quantity'] ?? 0;
        }
        unset($row);

        [$orderby, $descending] = $this->sortRequest('name');
        $this->paginate($rows, static function (array $a, array $b) use ($orderby): int {
            switch ($orderby) {
                case 'weight':
                    return (float) $a['weight'] <=> (float) $b['weight'];
                case 'items':
                    return $a['items'] <=> $b['items'];
                case 'size':
                case 'status':
                case 'location':
                    return strnatcasecmp((string) $a[$orderby], (string) $b[$orderby]);
                default:
                    return strnatcasecmp(stripslashes((string) $a['name']), stripslashes((string) $b['name']));
            }
        }, $descending);
    }
}
