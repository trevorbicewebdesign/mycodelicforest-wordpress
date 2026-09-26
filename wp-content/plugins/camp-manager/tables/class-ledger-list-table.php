<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . '/class-list-table.php';

/**
 * The Ledger admin page: the viewed season's entries (or every season's), newest first, with
 * views for money in, money out and entries that need attention, search, and filters for
 * line item type, receipts and month.
 */
class CampManagerLedgerTable extends CampManagerListTable
{
    const FILTER_FORM = 'ledger-filters';
    const PER_PAGE_DEFAULT = 50;
    /** Value of the type filter that keeps only entries without line items. */
    const TYPE_NONE = 'none';
    const FLOWS = ['in', 'out', 'attention'];

    private $ledger;
    /** Every entry of the viewed season with its line items and flags, before filtering. */
    private $rows;

    public function __construct(?CampManagerLedger $ledger = null)
    {
        $this->ledger = $ledger ?: self::defaultLedger();
        parent::__construct([
            'singular' => 'ledger_entry',
            'plural'   => 'ledger_entries',
            'ajax'     => false,
        ]);
    }

    /** A ledger with the collaborators it needs, for templates and tests that have none at hand. */
    public static function defaultLedger(): CampManagerLedger
    {
        $core = new CampManagerCore();
        return new CampManagerLedger(new CampManagerReceipts($core, new CampManagerChatGPT($core)));
    }

    public function filterFormId(): string
    {
        return self::FILTER_FORM;
    }

    public function get_columns()
    {
        $columns = [
            'cb'       => '<input type="checkbox" />',
            'date'     => 'Date',
            'note'     => 'Entry',
            'amount'   => 'Amount',
            'type'     => 'Line items',
            'receipts' => 'Receipts',
            'link'     => 'Link',
            'season'   => 'Season',
        ];
        // The season only tells rows apart when several seasons are listed together.
        if (!CampManagerSeason::viewingAll()) {
            unset($columns['season']);
        }
        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable = [
            'date'     => ['date', false],
            'note'     => ['note', false],
            'amount'   => ['amount', false],
            'type'     => ['type', false],
            'receipts' => ['receipts', false],
            'season'   => ['season', false],
        ];
        if (!CampManagerSeason::viewingAll()) {
            unset($sortable['season']);
        }
        return $sortable;
    }

    public function get_primary_column_name()
    {
        return 'note';
    }

    /** The search text, view, dropdown filters and page size currently asked for in the URL. */
    public function filters(): array
    {
        $month = $this->pick('month', null);
        return [
            'search'   => $this->searchTerm(),
            'flow'     => $this->pick('flow', self::FLOWS),
            'type'     => $this->pick('type', array_merge(CampManagerLedger::LINE_ITEM_TYPES, [self::TYPE_NONE])),
            'receipts' => $this->pick('receipts', ['with', 'without']),
            'month'    => preg_match('/^\d{4}-\d{2}$/', $month) ? $month : '',
            'per_page' => $this->perPage(),
        ];
    }

    protected function get_table_classes()
    {
        return array_merge(parent::get_table_classes(), ['ledger-table']);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'date':
                return $item['has_date'] ? esc_html(date_i18n('M j, Y', strtotime($item['date']))) : $this->emptyCell();
            case 'amount':
                $amount = (float) $item['amount'];
                $class = $amount < 0 ? 'cm-amount--out' : ($amount > 0 ? 'cm-amount--in' : 'cm-amount--zero');
                return '<span class="cm-amount ' . $class . '">' . esc_html(CampManagerDashboard::money($amount)) . '</span>';
            case 'type':
                if (!$item['line_items']) {
                    return $this->emptyCell();
                }
                $labels = array_map('esc_html', $item['types']);
                if ($item['untyped']) {
                    $labels[] = '<span class="cm-flag">Untyped</span>';
                }
                return implode(', ', $labels);
            case 'receipts':
                if (!$item['receipt_ids']) {
                    return $this->emptyCell();
                }
                $links = [];
                foreach ($item['receipt_ids'] as $receipt_id) {
                    $links[] = '<a href="' . esc_url(admin_url('admin.php?page=camp-manager-add-receipt&id=' . (int) $receipt_id)) . '">#' . (int) $receipt_id . '</a>';
                }
                return implode(', ', $links);
            case 'link':
                $url = trim((string) ($item['link'] ?? ''));
                if ($url === '' || !wp_http_validate_url($url)) {
                    return $this->emptyCell();
                }
                return '<a class="cm-link" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">'
                    . '<span class="dashicons dashicons-external" aria-hidden="true"></span>View'
                    . '<span class="screen-reader-text"> (opens in a new tab)</span></a>';
            case 'season':
                return esc_html((string) $item['season']);
            default:
                return isset($item[$column_name]) ? esc_html($item[$column_name]) : '';
        }
    }

    public function column_note($item)
    {
        $edit_url = admin_url('admin.php?page=camp-manager-add-ledger&id=' . (int) $item['id']);
        $actions = ['edit' => '<a href="' . esc_url($edit_url) . '">Edit</a>'];
        $url = trim((string) ($item['link'] ?? ''));
        if ($url !== '' && wp_http_validate_url($url)) {
            $actions['link'] = '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">View link</a>';
        }
        $html = $this->titleCell(trim(stripslashes((string) $item['note'])), $edit_url, [], $actions);
        if ($item['attention']) {
            $flag = '<div class="cm-item-meta cm-flag"><span class="dashicons dashicons-warning" aria-hidden="true"></span> '
                . esc_html(implode(' · ', $item['attention'])) . '</div>';
            // The flag goes under the title, before the row actions.
            $html = str_replace('<div class="row-actions">', $flag . '<div class="row-actions">', $html);
        }
        return $html;
    }

    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="ledger[]" value="%d" />', (int) $item['id']);
    }

    public function get_bulk_actions()
    {
        return ['delete' => 'Delete'];
    }

    public function process_bulk_action()
    {
        if ('delete' === $this->current_action()) {
            $this->ledger->deleteEntries($this->bulkIds('ledger'));
            $this->rows = null;
        }
    }

    public function no_items()
    {
        echo 'No ledger entries found.';
    }

    public function single_row($item)
    {
        echo '<tr class="' . ($item['attention'] ? 'is-attention' : '') . '">';
        $this->single_row_columns($item);
        echo '</tr>';
    }

    /** All / Money in / Money out / Needs attention, each with its count for the viewed season. */
    protected function get_views()
    {
        $counts = $this->ledger->countByFlow();
        $counts['attention'] = count(array_filter($this->rows(), static fn(array $row) => $row['attention']));
        $current = $this->filters()['flow'];
        $base = admin_url('admin.php?page=camp-manager-ledger');
        if (CampManagerSeason::viewingAll()) {
            $base = add_query_arg(CampManagerSeason::VIEW_ALL_PARAM, 'all', $base);
        }

        $views = [];
        foreach (['' => ['All', 'all'], 'in' => ['Money in', 'in'], 'out' => ['Money out', 'out'], 'attention' => ['Needs attention', 'attention']] as $flow => [$label, $key]) {
            if ($flow === 'attention' && !$counts['attention']) {
                continue;
            }
            $url = $flow === '' ? $base : add_query_arg('flow', $flow, $base);
            $views[$flow === '' ? 'all' : $flow] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
                esc_url($url),
                $flow === $current ? ' class="current" aria-current="page"' : '',
                esc_html($label),
                $counts[$key]
            );
        }
        return $views;
    }

    protected function extra_tablenav($which)
    {
        if ('top' !== $which) {
            return;
        }
        $types = array_combine(CampManagerLedger::LINE_ITEM_TYPES, CampManagerLedger::LINE_ITEM_TYPES) + [self::TYPE_NONE => 'No line items'];
        $months = [];
        foreach ($this->rows() as $row) {
            if ($row['has_date']) {
                $months[$row['month']] = date_i18n('F Y', strtotime($row['month'] . '-01'));
            }
        }
        krsort($months);
        $this->renderFilterDropdowns([
            'type'     => ['All types', $types],
            'receipts' => ['Receipts', ['with' => 'With receipts', 'without' => 'Without receipts']],
            'month'    => ['All months', $months],
        ], $this->filters());
        $this->renderPerPage();
    }

    /**
     * Every entry of the viewed season with its line items, the types and receipts among them,
     * and what (if anything) needs attention. Loaded once; a season is at most a few hundred rows.
     */
    private function rows(): array
    {
        if ($this->rows !== null) {
            return $this->rows;
        }
        global $wpdb;
        $rows = $wpdb->get_results(
            "SELECT id, season, amount, date, note, link FROM {$wpdb->prefix}mf_ledger WHERE " . CampManagerSeason::whereSeason(),
            ARRAY_A
        ) ?: [];
        $line_items = $this->ledger->getLineItemsByEntry(array_column($rows, 'id'));
        $order = array_flip(CampManagerLedger::LINE_ITEM_TYPES);

        foreach ($rows as &$row) {
            $items = $line_items[(int) $row['id']] ?? [];
            $types = [];
            $receipts = [];
            $untyped = false;
            foreach ($items as $item) {
                $type = trim((string) ($item['type'] ?? ''));
                if ($type === '') {
                    $untyped = true;
                } else {
                    $types[$type] = $order[$type] ?? PHP_INT_MAX;
                }
                if ((int) $item['receipt_id'] > 0) {
                    $receipts[(int) $item['receipt_id']] = true;
                }
            }
            asort($types);
            $row['line_items']  = $items;
            $row['types']       = array_keys($types);
            $row['untyped']     = $untyped;
            $row['receipt_ids'] = array_keys($receipts);
            $row['has_date']    = $row['date'] !== null && $row['date'] !== '' && strpos((string) $row['date'], '0000-00-00') !== 0;
            $row['month']       = $row['has_date'] ? substr((string) $row['date'], 0, 7) : '';
            $row['attention']   = CampManagerLedger::attentionReasons($row, $items);
        }
        unset($row);
        return $this->rows = $rows;
    }

    public function prepare_items()
    {
        $filters = $this->filters();
        $rows = $this->rows();

        if ($filters['flow'] === 'in') {
            $rows = array_filter($rows, static fn(array $row) => (float) $row['amount'] > 0);
        } elseif ($filters['flow'] === 'out') {
            $rows = array_filter($rows, static fn(array $row) => (float) $row['amount'] < 0);
        } elseif ($filters['flow'] === 'attention') {
            $rows = array_filter($rows, static fn(array $row) => (bool) $row['attention']);
        }
        if ($filters['type'] === self::TYPE_NONE) {
            $rows = array_filter($rows, static fn(array $row) => !$row['line_items']);
        } elseif ($filters['type'] !== '') {
            $rows = array_filter($rows, static fn(array $row) => in_array($filters['type'], $row['types'], true));
        }
        if ($filters['receipts'] === 'with') {
            $rows = array_filter($rows, static fn(array $row) => (bool) $row['receipt_ids']);
        } elseif ($filters['receipts'] === 'without') {
            $rows = array_filter($rows, static fn(array $row) => !$row['receipt_ids']);
        }
        if ($filters['month'] !== '') {
            $rows = array_filter($rows, static fn(array $row) => $row['month'] === $filters['month']);
        }
        if ($filters['search'] !== '') {
            $needle = mb_strtolower($filters['search']);
            $rows = array_filter($rows, static function (array $row) use ($needle) {
                $haystack = [(string) $row['note'], (string) $row['link'], (string) $row['id']];
                foreach ($row['line_items'] as $item) {
                    $haystack[] = (string) $item['note'];
                    $haystack[] = (string) $item['name'];
                }
                return mb_strpos(mb_strtolower(stripslashes(implode("\n", $haystack))), $needle) !== false;
            });
        }

        // Newest first until a column is chosen; a chosen column starts ascending.
        [$orderby, $descending] = $this->sortRequest('date');
        if (!isset($_GET['orderby'])) {
            $descending = true;
        }
        $this->paginate(array_values($rows), static function (array $a, array $b) use ($orderby): int {
            switch ($orderby) {
                case 'note':
                    return strnatcasecmp(stripslashes((string) $a['note']), stripslashes((string) $b['note']));
                case 'amount':
                    return (float) $a['amount'] <=> (float) $b['amount'];
                case 'type':
                    return strnatcasecmp(implode(', ', $a['types']), implode(', ', $b['types']));
                case 'receipts':
                    return count($a['receipt_ids']) <=> count($b['receipt_ids']);
                case 'season':
                    return (int) $a['season'] <=> (int) $b['season'];
                default:
                    return strcmp((string) $a['date'], (string) $b['date']);
            }
        }, $descending);
    }
}
