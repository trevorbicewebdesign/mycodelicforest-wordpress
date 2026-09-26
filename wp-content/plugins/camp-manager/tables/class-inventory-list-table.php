<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . '/class-list-table.php';

/**
 * The "All items" tab of the Inventory admin page: every inventory item with its quantity,
 * category, location, the totes it is packed in (or its set) and its link, searchable and
 * filterable by category, location and tote.
 */
class CampManagerInventoryTable extends CampManagerListTable
{
    const FILTER_FORM = 'inventory-filters';
    /** Value of the tote filter that keeps only items that are not packed in any tote. */
    const TOTE_NONE = 'none';

    private $inventory;

    public function __construct(?CampManagerInventory $inventory = null)
    {
        $this->inventory = $inventory ?: new CampManagerInventory();
        parent::__construct([
            'singular' => 'inventory_item',
            'plural'   => 'inventory_items',
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
            'name'     => 'Item',
            'quantity' => 'Quantity',
            'category' => 'Category',
            'location' => 'Location',
            'tote'     => 'Tote / set',
            'links'    => 'Links',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'name'     => ['name', false],
            'quantity' => ['quantity', false],
            'category' => ['category', false],
            'location' => ['location', false],
            'tote'     => ['tote', false],
            'links'    => ['links', false],
        ];
    }

    public function get_primary_column_name()
    {
        return 'name';
    }

    /** The search text, dropdown filters and page size currently asked for in the URL. */
    public function filters(): array
    {
        $tote = $this->pick('tote', null);
        if ($tote !== self::TOTE_NONE) {
            $tote = (int) $tote > 0 ? (string) (int) $tote : '';
        }
        return [
            'search'   => $this->searchTerm(),
            'category' => $this->pick('category', null),
            'location' => $this->pick('location', null),
            'tote'     => $tote,
            'per_page' => $this->perPage(),
        ];
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'quantity':
                return number_format((float) $item['quantity'], 0);
            case 'category':
                return $item['category_label'] === '' ? $this->emptyCell() : esc_html($item['category_label']);
            case 'location':
                return trim((string) $item['location']) === '' ? $this->emptyCell() : esc_html($item['location']);
            case 'tote':
                if ($item['totes']) {
                    $links = [];
                    foreach ($item['totes'] as $tote_id => $name) {
                        $links[] = '<a href="' . esc_url(admin_url('admin.php?page=camp-manager-add-tote&id=' . (int) $tote_id)) . '">' . esc_html($name) . '</a>';
                    }
                    return implode(', ', $links);
                }
                $set = trim(stripslashes((string) ($item['set_name'] ?? '')));
                return $set === '' ? $this->emptyCell() : '<span class="cm-set">' . esc_html($set) . '</span>';
            case 'links':
                $url = trim((string) ($item['links'] ?? ''));
                if ($url === '' || !wp_http_validate_url($url)) {
                    return $this->emptyCell();
                }
                return '<a class="cm-link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">'
                    . '<span class="dashicons dashicons-external" aria-hidden="true"></span>View'
                    . '<span class="screen-reader-text"> (opens in a new tab)</span></a>';
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function column_name($item)
    {
        $edit_url = admin_url('admin.php?page=camp-manager-add-inventory&id=' . (int) $item['id']);
        $here = base64_encode(admin_url('admin.php?page=camp-manager-inventory'));
        $actions = [
            'edit' => '<a href="' . esc_url($edit_url) . '">Edit</a>',
            'pack' => '<a href="' . esc_url(admin_url('admin.php?page=camp-manager-add-tote-inventory&inventory_id=' . (int) $item['id'] . '&return=' . $here)) . '">Add to tote</a>',
        ];
        return $this->titleCell(
            trim(stripslashes((string) $item['name'])),
            $edit_url,
            [stripslashes((string) ($item['manufacturer'] ?? '')), stripslashes((string) ($item['model'] ?? ''))],
            $actions
        );
    }

    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="inventory[]" value="%d" />', (int) $item['id']);
    }

    public function get_bulk_actions()
    {
        return ['delete' => 'Delete'];
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $this->inventory->deleteInventoryItems($this->bulkIds('inventory'));
        }
    }

    public function no_items()
    {
        echo 'No inventory items found.';
    }

    protected function extra_tablenav($which)
    {
        if ('top' !== $which) {
            return;
        }
        $filters = $this->filters();
        $totes = [self::TOTE_NONE => 'Not in a tote'];
        foreach ($this->inventory->getAllTotes() as $tote) {
            $totes[(string) (int) $tote->id] = (string) $tote->name;
        }
        $this->renderFilterDropdowns([
            'category' => ['All categories', array_combine($this->inventory->itemCategories(), $this->inventory->itemCategories())],
            'location' => ['All locations', array_combine($this->inventory->itemLocations(), $this->inventory->itemLocations())],
            'tote'     => ['All totes', $totes],
        ], $filters);
        $this->renderPerPage();
    }

    public function prepare_items()
    {
        global $wpdb;
        $table   = "{$wpdb->prefix}mf_inventory";
        $filters = $this->filters();

        $where = ['1=1'];
        $args  = [];
        if ($filters['category'] !== '') {
            $where[] = "COALESCE(NULLIF(category_name, ''), category) = %s";
            $args[]  = $filters['category'];
        }
        if ($filters['location'] !== '') {
            $where[] = 'location = %s';
            $args[]  = $filters['location'];
        }
        if ($filters['search'] !== '') {
            $like    = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where[] = '(name LIKE %s OR manufacturer LIKE %s OR model LIKE %s OR description LIKE %s OR category_name LIKE %s OR category LIKE %s OR location LIKE %s OR set_name LIKE %s)';
            array_push($args, $like, $like, $like, $like, $like, $like, $like, $like);
        }

        $sql = "SELECT id, uuid, name, manufacturer, model, description, quantity, photo, location, weight,
                       category, category_name, links, amp, set_name
                FROM $table WHERE " . implode(' AND ', $where);
        $rows = $wpdb->get_results($args ? $wpdb->prepare($sql, ...$args) : $sql, ARRAY_A) ?: [];

        // Which totes hold each item is looked up separately, so the tote filter and the
        // "Tote / set" sort happen here rather than in SQL. The whole inventory is a few
        // hundred rows, so this is one page's worth of work.
        $totes = $this->inventory->getToteNamesByItem(array_column($rows, 'id'));
        foreach ($rows as &$row) {
            $row['totes'] = $totes[(int) $row['id']] ?? [];
            $row['category_label'] = trim(stripslashes((string) ($row['category_name'] !== '' && $row['category_name'] !== null ? $row['category_name'] : $row['category'])));
            $row['tote_label'] = $row['totes'] ? implode(', ', $row['totes']) : trim(stripslashes((string) ($row['set_name'] ?? '')));
        }
        unset($row);

        if ($filters['tote'] === self::TOTE_NONE) {
            $rows = array_values(array_filter($rows, static fn(array $row) => !$row['totes']));
        } elseif ($filters['tote'] !== '') {
            $tote_id = (int) $filters['tote'];
            $rows = array_values(array_filter($rows, static fn(array $row) => isset($row['totes'][$tote_id])));
        }

        [$orderby, $descending] = $this->sortRequest('name');
        $this->paginate($rows, function (array $a, array $b) use ($orderby, $descending): int {
            switch ($orderby) {
                case 'quantity':
                    return (float) $a['quantity'] <=> (float) $b['quantity'];
                case 'category':
                    return $this->compareText($a['category_label'], $b['category_label'], $descending);
                case 'location':
                    return $this->compareText((string) $a['location'], (string) $b['location'], $descending);
                case 'tote':
                    return $this->compareText($a['tote_label'], $b['tote_label'], $descending);
                case 'links':
                    return $this->compareText((string) ($a['links'] ?? ''), (string) ($b['links'] ?? ''), $descending);
                default:
                    return strnatcasecmp(stripslashes((string) $a['name']), stripslashes((string) $b['name']));
            }
        }, $descending);
    }

    /**
     * Natural, case-insensitive order. Blank values go last whichever way the column sorts, so
     * a blank-versus-value result is pre-flipped for a descending sort (paginate() negates it).
     */
    private function compareText(string $a, string $b, bool $descending): int
    {
        $a = trim($a);
        $b = trim($b);
        if (($a === '') !== ($b === '')) {
            $blankLast = $a === '' ? 1 : -1;
            return $descending ? -$blankLast : $blankLast;
        }
        return strnatcasecmp($a, $b);
    }
}
