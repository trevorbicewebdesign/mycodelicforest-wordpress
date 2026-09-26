<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-list-table.php';

/**
 * Tote inventory: which items are packed in which totes, with quantities and weights.
 *
 * On the "Tote inventory" tab it lists every row, searchable and filterable by tote. On a
 * tote's edit page it is scoped to that tote (pass its id; `false` lists nothing, for a tote
 * that is not saved yet) and drops the tote column, the filters and the page size picker.
 */
class CampManagerToteInventoryTable extends CampManagerListTable
{
    const FILTER_FORM = 'tote-inventory-filters';
    /** Rows shown on a tote's edit page, where there is no pagination control to speak of. */
    const EMBEDDED_PER_PAGE = 100;

    private $inventory;
    private $tote_id;

    /** @param int|false|null $tote_id a tote to scope to, false for no rows, null for every tote */
    public function __construct($tote_id = null, ?CampManagerInventory $inventory = null)
    {
        $this->tote_id = $tote_id;
        $this->inventory = $inventory ?: new CampManagerInventory();
        parent::__construct([
            'singular' => 'tote_inventory_item',
            'plural'   => 'tote_inventory_items',
            'ajax'     => false,
        ]);
    }

    public function filterFormId(): string
    {
        return self::FILTER_FORM;
    }

    /** True on a tote's edit page (scoped to one tote), false on the Tote inventory tab. */
    public function isEmbedded(): bool
    {
        return $this->tote_id !== null;
    }

    public function perPage(): int
    {
        return $this->isEmbedded() ? self::EMBEDDED_PER_PAGE : parent::perPage();
    }

    public function get_columns()
    {
        $columns = [
            'cb'             => '<input type="checkbox" />',
            'inventory_name' => 'Item',
            'tote_name'      => 'Tote',
            'quantity'       => 'Quantity',
            'weight'         => 'Unit weight',
            'total_weight'   => 'Total weight',
        ];
        if ($this->isEmbedded()) {
            unset($columns['tote_name']);
        }
        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable = [
            'inventory_name' => ['inventory_name', false],
            'tote_name'      => ['tote_name', false],
            'quantity'       => ['quantity', false],
            'weight'         => ['weight', false],
            'total_weight'   => ['total_weight', false],
        ];
        if ($this->isEmbedded()) {
            unset($sortable['tote_name']);
        }
        return $sortable;
    }

    public function get_primary_column_name()
    {
        return 'inventory_name';
    }

    /** The search text, tote filter and page size currently asked for in the URL. */
    public function filters(): array
    {
        return [
            'search'   => $this->isEmbedded() ? '' : $this->searchTerm(),
            'tote'     => $this->isEmbedded() || (int) $this->pick('tote', null) <= 0 ? '' : (string) (int) $this->pick('tote', null),
            'per_page' => $this->perPage(),
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'quantity':
                return number_format((float) $item['quantity'], 0);
            case 'weight':
                return $item['weight'] === null ? $this->emptyCell() : number_format((float) $item['weight'], 2) . ' lbs';
            case 'total_weight':
                return $item['weight'] === null ? $this->emptyCell() : number_format((float) $item['total_weight'], 2) . ' lbs';
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function column_inventory_name($item)
    {
        $return = base64_encode($this->isEmbedded()
            ? admin_url('admin.php?page=camp-manager-add-tote&id=' . (int) $this->tote_id)
            : admin_url('admin.php?page=camp-manager-view-tote-inventory'));
        $edit_url = admin_url('admin.php?page=camp-manager-add-tote-inventory&id=' . (int) $item['id'] . '&return=' . $return);
        $actions = ['edit' => '<a href="' . esc_url($edit_url) . '">Edit</a>'];
        if ($item['inventory_id']) {
            $actions['item'] = '<a href="' . esc_url(admin_url('admin.php?page=camp-manager-add-inventory&id=' . (int) $item['inventory_id'] . '&return=' . $return)) . '">Edit item</a>';
        }
        return $this->titleCell(stripslashes((string) ($item['inventory_name'] ?? '')), $edit_url, [], $actions);
    }

    public function column_tote_name($item)
    {
        if (!$item['tote_id']) {
            return $this->emptyCell();
        }
        $url = admin_url('admin.php?page=camp-manager-add-tote&id=' . (int) $item['tote_id']);
        return '<a href="' . esc_url($url) . '">' . esc_html(stripslashes((string) $item['tote_name'])) . '</a>';
    }

    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="tote-inventory[]" value="%d" />', (int) $item['id']);
    }

    public function get_bulk_actions()
    {
        return ['delete' => 'Remove from tote'];
    }

    public function process_bulk_action()
    {
        if ('delete' !== $this->current_action()) {
            return;
        }
        $this->inventory->deleteToteInventory($this->bulkIds('tote-inventory'));

        // A tote's edit page posts a return address; go back there so the URL is clean.
        if (!empty($_POST['return_url'])) {
            $return = base64_decode((string) wp_unslash($_POST['return_url']), true);
            if ($return !== false && $return !== '') {
                wp_safe_redirect(esc_url_raw($return));
                exit;
            }
        }
    }

    public function no_items()
    {
        echo $this->isEmbedded() ? 'Nothing packed in this tote yet.' : 'No tote inventory found.';
    }

    protected function extra_tablenav($which)
    {
        if ('top' !== $which || $this->isEmbedded()) {
            return;
        }
        $totes = [];
        foreach ($this->inventory->getAllTotes() as $tote) {
            $totes[(string) (int) $tote->id] = (string) $tote->name;
        }
        $this->renderFilterDropdowns(['tote' => ['All totes', $totes]], $this->filters());
        $this->renderPerPage();
    }

    public function prepare_items()
    {
        global $wpdb;
        $filters = $this->filters();

        $where = ['1=1'];
        $args  = [];
        if ($this->tote_id === false) {
            $where[] = '1=0';
        } elseif ($this->tote_id !== null) {
            $where[] = 'ti.tote_id = %d';
            $args[]  = (int) $this->tote_id;
        } elseif ($filters['tote'] !== '') {
            $where[] = 'ti.tote_id = %d';
            $args[]  = (int) $filters['tote'];
        }
        if ($filters['search'] !== '') {
            $like    = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(i.name LIKE %s OR t.name LIKE %s)';
            array_push($args, $like, $like);
        }

        $sql = "SELECT ti.id, ti.inventory_id, ti.tote_id, ti.quantity,
                       i.name AS inventory_name, i.weight, t.name AS tote_name,
                       (ti.quantity * i.weight) AS total_weight
                FROM {$wpdb->prefix}mf_tote_inventory ti
                LEFT JOIN {$wpdb->prefix}mf_inventory i ON ti.inventory_id = i.id
                LEFT JOIN {$wpdb->prefix}mf_totes t ON ti.tote_id = t.id
                WHERE " . implode(' AND ', $where);
        $rows = $wpdb->get_results($args ? $wpdb->prepare($sql, ...$args) : $sql, ARRAY_A) ?: [];

        [$orderby, $descending] = $this->sortRequest('inventory_name');
        $this->paginate($rows, static function (array $a, array $b) use ($orderby): int {
            switch ($orderby) {
                case 'quantity':
                case 'weight':
                case 'total_weight':
                    return (float) $a[$orderby] <=> (float) $b[$orderby];
                case 'tote_name':
                    return strnatcasecmp(stripslashes((string) $a['tote_name']), stripslashes((string) $b['tote_name']));
                default:
                    return strnatcasecmp(stripslashes((string) $a['inventory_name']), stripslashes((string) $b['inventory_name']));
            }
        }, $descending);
    }
}
