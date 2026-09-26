<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * What the three Inventory list tables (items, totes, tote inventory) share: the page size and
 * its "N items per page" picker, the GET form that carries search and filter fields, the sort
 * request, empty cells and the "name plus a grey second line" cell.
 *
 * Filters and the page size travel in the URL (?category=..&per_page=50), so sorting and
 * pagination links keep them. The dropdowns sit in the table's tablenav, inside the POST form
 * used for bulk actions, so they point back at the GET form with the `form` attribute rather
 * than nesting forms, as the roster does.
 */
abstract class CampManagerInventoryListTable extends WP_List_Table
{
    const PER_PAGE_DEFAULT = 20;
    const PER_PAGE_OPTIONS = [20, 50, 100];

    /** Id of the GET form that carries the search box and the filter dropdowns. */
    abstract public function filterFormId(): string;

    /** Rows per page: ?per_page=20|50|100, else the default. */
    public function perPage(): int
    {
        $wanted = isset($_GET['per_page']) ? (int) $_GET['per_page'] : 0;
        return in_array($wanted, self::PER_PAGE_OPTIONS, true) ? $wanted : self::PER_PAGE_DEFAULT;
    }

    /** The search text, if any. */
    protected function searchTerm(): string
    {
        return isset($_GET['s']) ? trim((string) wp_unslash($_GET['s'])) : '';
    }

    /** A dropdown value from the URL: one of $allowed, or '' (a plain text value when $allowed is null). */
    protected function pick(string $key, ?array $allowed): string
    {
        if (!isset($_GET[$key])) {
            return '';
        }
        $value = sanitize_text_field(wp_unslash($_GET[$key]));
        if ($allowed === null) {
            return $value;
        }
        return in_array($value, $allowed, true) ? $value : '';
    }

    /**
     * [orderby, descending] for this request. A column name that is not sortable falls back to
     * $default; $default sorts ascending unless the URL says otherwise.
     */
    protected function sortRequest(string $default): array
    {
        $orderby = isset($_GET['orderby']) ? sanitize_key(wp_unslash($_GET['orderby'])) : '';
        if (!array_key_exists($orderby, $this->get_sortable_columns())) {
            $orderby = $default;
        }
        $descending = isset($_GET['order']) && strtolower(sanitize_key(wp_unslash($_GET['order']))) === 'desc';
        return [$orderby, $descending];
    }

    /** Sorts $rows by $compare (ties broken by id, newest first) and keeps this page of them. */
    protected function paginate(array $rows, callable $compare, bool $descending): void
    {
        usort($rows, static function (array $a, array $b) use ($compare, $descending) {
            $cmp = $compare($a, $b);
            if ($cmp === 0) {
                return (int) $b['id'] <=> (int) $a['id'];
            }
            return $descending ? -$cmp : $cmp;
        });

        $per_page = $this->perPage();
        $total = count($rows);
        $this->items = array_slice($rows, ($this->get_pagenum() - 1) * $per_page, $per_page);
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns(), $this->get_primary_column_name()];
        $this->set_pagination_args([
            'total_items' => $total,
            'per_page'    => $per_page,
            'total_pages' => (int) ceil($total / $per_page),
        ]);
    }

    protected function get_table_classes()
    {
        return array_merge(parent::get_table_classes(), ['cm-inventory-table']);
    }

    /** A dash for a blank cell, read as "None" by screen readers. */
    protected function emptyCell(): string
    {
        return '<span class="cm-empty" aria-hidden="true">&mdash;</span><span class="screen-reader-text">None</span>';
    }

    /**
     * The primary cell: a bold title linking to $url, an optional grey line under it (the
     * non-empty $meta parts joined with a bullet) and the row actions.
     */
    protected function titleCell(string $title, string $url, array $meta = [], array $actions = []): string
    {
        $meta = array_values(array_filter(array_map('trim', $meta), 'strlen'));
        $html = '<strong><a class="row-title" href="' . esc_url($url) . '">' . esc_html($title === '' ? '(no name)' : $title) . '</a></strong>';
        if ($meta) {
            $html .= '<div class="cm-item-meta">' . implode(' <span aria-hidden="true">&bull;</span> ', array_map('esc_html', $meta)) . '</div>';
        }
        return $html . $this->row_actions($actions);
    }

    /** Filter dropdowns for the top tablenav, each bound to the GET form. */
    protected function renderFilterDropdowns(array $dropdowns, array $current): void
    {
        ?>
        <div class="alignleft actions">
            <?php foreach ($dropdowns as $name => [$label, $options]): ?>
                <label class="screen-reader-text" for="cm-filter-<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label>
                <select name="<?php echo esc_attr($name); ?>" id="cm-filter-<?php echo esc_attr($name); ?>" form="<?php echo esc_attr($this->filterFormId()); ?>">
                    <option value=""><?php echo esc_html($label); ?></option>
                    <?php foreach ($options as $value => $text): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected((string) $current[$name], (string) $value); ?>><?php echo esc_html($text); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endforeach; ?>
            <?php submit_button('Filter', '', 'filter_action', false, ['id' => 'cm-filter-submit', 'form' => $this->filterFormId()]); ?>
        </div>
        <?php
    }

    /** The "20 items per page" picker, right before the pagination links. */
    protected function renderPerPage(): void
    {
        ?>
        <div class="cm-per-page">
            <label class="screen-reader-text" for="cm-per-page">Items per page</label>
            <select name="per_page" id="cm-per-page" form="<?php echo esc_attr($this->filterFormId()); ?>" onchange="this.form.requestSubmit ? this.form.requestSubmit() : this.form.submit()">
                <?php foreach (self::PER_PAGE_OPTIONS as $option): ?>
                    <option value="<?php echo (int) $option; ?>" <?php selected($this->perPage(), $option); ?>><?php echo (int) $option; ?> items per page</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /** The hidden fields the GET form needs so its submissions keep the page (and, given, the tote scope). */
    public function filterFormFields(array $keep = []): void
    {
        echo '<input type="hidden" name="page" value="' . esc_attr(sanitize_key($_GET['page'] ?? '')) . '">';
        foreach ($keep as $key) {
            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr(sanitize_text_field(wp_unslash($_GET[$key]))) . '">';
            }
        }
    }

    /** Ids posted for a bulk action under $field, after the bulk nonce check. */
    protected function bulkIds(string $field): array
    {
        check_admin_referer('bulk-' . $this->_args['plural']);
        if (empty($_POST[$field]) || !is_array($_POST[$field])) {
            return [];
        }
        return array_values(array_filter(array_map('intval', $_POST[$field])));
    }
}
