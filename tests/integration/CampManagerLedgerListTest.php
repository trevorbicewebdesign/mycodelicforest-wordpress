<?php

/**
 * The admin Ledger page: the overview figures, the views (all / in / out / needs attention),
 * the list table's columns, filters, search, sorting and page size, the attention checks, bulk
 * delete, and the page template.
 */
class CampManagerLedgerListTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /** @var CampManagerLedger */
    private $ledger;

    protected function _before()
    {
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }
        global $wpdb;
        $wpdb->query('COMMIT');
        // Whole-table assertions below: start from empty tables (other tests COMMIT rows).
        foreach (['mf_ledger_line_items', 'mf_ledger'] as $table) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}$table");
        }
        update_option(CampManagerSeason::OPTION_CURRENT, 2027);
        $this->ledger = CampManagerLedgerTable::defaultLedger();
        $GLOBALS['hook_suffix'] = 'toplevel_page_camp-manager-ledger';
        set_current_screen('toplevel_page_camp-manager-ledger');
    }

    protected function _after()
    {
        $_GET = $_POST = $_REQUEST = [];
    }

    // ------------------------------------------------------------------------------ fixtures

    /** Inserted through wpdb so a null date stays NULL. */
    private function entry(array $data = []): int
    {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'mf_ledger', $data + ['amount' => 100, 'note' => 'Entry', 'date' => '2027-06-15 00:00:00', 'link' => '', 'season' => 2027]);
        return (int) $wpdb->insert_id;
    }

    private function line(int $ledgerId, float $amount, ?string $type = 'Expense', array $data = []): int
    {
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'mf_ledger_line_items', $data + ['ledger_id' => $ledgerId, 'amount' => $amount, 'type' => $type, 'receipt_id' => 0, 'name' => '', 'note' => '']);
        return (int) $wpdb->insert_id;
    }

    /** A table prepared for a request carrying $query (the URL's query string). */
    private function table(array $query = []): CampManagerLedgerTable
    {
        $_GET = $_REQUEST = $query;
        $table = new CampManagerLedgerTable($this->ledger);
        $table->prepare_items();
        return $table;
    }

    private function notes(CampManagerLedgerTable $table): array
    {
        return array_map(static fn($item) => $item['note'], $table->items);
    }

    private function renderPage(array $query = []): string
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $_GET = $_REQUEST = $query + ['page' => 'camp-manager-ledger'];
        ob_start();
        (static function () {
            include WP_CONTENT_DIR . '/plugins/camp-manager/tmpl/ledger_view_all_page.php';
        })();
        return ob_get_clean();
    }

    // ------------------------------------------------------------------------------ figures

    public function testOverviewFiguresForTheSeasonAndForAllSeasons()
    {
        $this->entry(['amount' => 500, 'season' => 2025]); // an earlier season carries over
        $in = $this->entry(['amount' => 1000]);
        $out = $this->entry(['amount' => -250]);
        $this->line($in, 350, 'Camp Dues');
        $this->line($in, 100, 'Partial Camp Dues');
        $this->line($in, 150, 'Donation');
        $this->line($in, 400, 'Sold Asset');
        $this->line($out, 250, 'Expense');

        $o = $this->ledger->overview();
        $this->assertSame(2, $o['entries']);
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE + 500, $o['starting_balance']);
        $this->assertEquals(1000.0, $o['money_in']);
        $this->assertEquals(250.0, $o['money_out']);
        $this->assertEquals(750.0, $o['net']);
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE + 500 + 750, $o['ending_balance']);
        $this->assertEquals(450.0, $o['camp_dues'], 'Full and partial dues together');
        $this->assertEquals(150.0, $o['donations']);
        $this->assertEquals(400.0, $o['assets_sold']);
        $this->assertEquals(250.0, $o['expenses']);
        $this->assertSame(['all' => 2, 'in' => 1, 'out' => 1], $this->ledger->countByFlow());

        $_GET = [CampManagerSeason::VIEW_ALL_PARAM => 'all'];
        $o = $this->ledger->overview();
        $this->assertSame(3, $o['entries']);
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE, $o['starting_balance'], 'Every season together starts at the opening balance');
        $this->assertEquals(1500.0, $o['money_in']);
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE + 1250, $o['ending_balance']);
        $this->assertSame(['all' => 3, 'in' => 2, 'out' => 1], $this->ledger->countByFlow());
    }

    public function testLineItemsByEntryAndDeleteEntries()
    {
        $a = $this->entry();
        $b = $this->entry();
        $none = $this->entry();
        $this->line($a, 10);
        $this->line($a, 20, 'Donation');
        $this->line($b, 30);

        $items = $this->ledger->getLineItemsByEntry([$a, $b, $none]);
        $this->assertSame([$a, $b], array_keys($items));
        $this->assertEquals(['10.00', '20.00'], array_column($items[$a], 'amount'));
        $this->assertSame([], $this->ledger->getLineItemsByEntry([]));

        $this->ledger->deleteEntries([$a]);
        global $wpdb;
        $this->assertEquals([$b, $none], $wpdb->get_col("SELECT id FROM {$wpdb->prefix}mf_ledger ORDER BY id"));
        $this->assertEquals([$b], $wpdb->get_col("SELECT ledger_id FROM {$wpdb->prefix}mf_ledger_line_items"));
    }

    public function testAttentionReasons()
    {
        $ok = ['amount' => -370.88, 'date' => '2027-07-02 00:00:00'];
        $this->assertSame([], CampManagerLedger::attentionReasons($ok, [['amount' => '370.88', 'type' => 'Expense']]));
        $this->assertSame([], CampManagerLedger::attentionReasons($ok, [['amount' => '-370.88', 'type' => 'Expense']]), 'Signs do not matter');
        $this->assertSame([], CampManagerLedger::attentionReasons($ok, [['amount' => '300.88', 'type' => 'Expense'], ['amount' => '70', 'type' => 'Expense']]));
        $this->assertSame(['No line items'], CampManagerLedger::attentionReasons($ok, []));
        $this->assertSame(['Line items total $359.79'], CampManagerLedger::attentionReasons($ok, [['amount' => '359.79', 'type' => 'Expense']]));
        $this->assertSame(['Untyped line item'], CampManagerLedger::attentionReasons($ok, [['amount' => '370.88', 'type' => '']]));
        $this->assertSame(['No date', 'No line items'], CampManagerLedger::attentionReasons(['amount' => 0, 'date' => '0000-00-00 00:00:00'], []));
        $this->assertSame(['No date', 'No line items'], CampManagerLedger::attentionReasons(['amount' => 0, 'date' => null], []));
    }

    // ------------------------------------------------------------------------- the list table

    public function testColumnsShowTheSeasonOnlyWhenViewingAllSeasons()
    {
        $table = $this->table();
        $this->assertSame(['cb', 'date', 'note', 'amount', 'type', 'receipts', 'link'], array_keys($table->get_columns()));
        $this->assertSame(['date', 'note', 'amount', 'type', 'receipts'], array_keys($table->get_sortable_columns()));
        $this->assertSame('note', $table->get_primary_column_name());
        $this->assertSame(50, $table->perPage());

        $table = $this->table([CampManagerSeason::VIEW_ALL_PARAM => 'all']);
        $this->assertSame(['cb', 'date', 'note', 'amount', 'type', 'receipts', 'link', 'season'], array_keys($table->get_columns()));
        $this->assertArrayHasKey('season', $table->get_sortable_columns());
    }

    public function testListsTheViewedSeasonNewestFirstAndSortsByAnyColumn()
    {
        $this->entry(['note' => 'Old season', 'season' => 2025, 'date' => '2025-08-01 00:00:00']);
        $this->entry(['note' => 'June', 'date' => '2027-06-01 00:00:00', 'amount' => 50]);
        $this->entry(['note' => 'August', 'date' => '2027-08-01 00:00:00', 'amount' => -300]);
        $this->entry(['note' => 'July', 'date' => '2027-07-01 00:00:00', 'amount' => 200]);

        $this->assertSame(['August', 'July', 'June'], $this->notes($this->table()), 'Newest first by default');
        $this->assertSame(['June', 'July', 'August'], $this->notes($this->table(['orderby' => 'date'])), 'A chosen column starts ascending');
        $this->assertSame(['August', 'July', 'June'], $this->notes($this->table(['orderby' => 'date', 'order' => 'desc'])));
        $this->assertSame(['August', 'June', 'July'], $this->notes($this->table(['orderby' => 'amount'])));
        $this->assertSame(['August', 'July', 'June'], $this->notes($this->table(['orderby' => 'note'])));
        $this->assertSame(['August', 'July', 'June', 'Old season'], $this->notes($this->table([CampManagerSeason::VIEW_ALL_PARAM => 'all'])));
        // Ties (one season) fall back to newest entry first, as everywhere else.
        $this->assertSame(['Old season', 'July', 'August', 'June'], $this->notes($this->table([CampManagerSeason::VIEW_ALL_PARAM => 'all', 'orderby' => 'season'])));
    }

    public function testViewsFiltersAndSearch()
    {
        $dues = $this->entry(['note' => 'Tina camp dues', 'amount' => 350, 'date' => '2027-06-01 00:00:00', 'link' => 'https://www.paypal.com/activity/payment/ABC']);
        $fuel = $this->entry(['note' => 'Fuel', 'amount' => -832.57, 'date' => '2027-07-07 00:00:00']);
        $short = $this->entry(['note' => 'Short', 'amount' => 359.79, 'date' => '2027-06-29 00:00:00']);
        $bare = $this->entry(['note' => 'Bare', 'amount' => -250, 'date' => '2027-07-31 00:00:00']);
        $this->line($dues, 350, 'Camp Dues');
        $this->line($fuel, 832.57, 'Expense', ['receipt_id' => 96, 'note' => 'diesel']);
        $this->line($short, 370.88, 'Sold Asset');

        $this->assertSame(['Bare', 'Fuel', 'Short', 'Tina camp dues'], $this->notes($this->table()));
        $this->assertSame(['Short', 'Tina camp dues'], $this->notes($this->table(['flow' => 'in'])));
        $this->assertSame(['Bare', 'Fuel'], $this->notes($this->table(['flow' => 'out'])));
        $this->assertSame(['Bare', 'Short'], $this->notes($this->table(['flow' => 'attention'])), 'No line items, and line items that do not add up');
        $this->assertSame(['Tina camp dues'], $this->notes($this->table(['type' => 'Camp Dues'])));
        $this->assertSame(['Bare'], $this->notes($this->table(['type' => 'none'])));
        $this->assertSame(['Fuel'], $this->notes($this->table(['receipts' => 'with'])));
        $this->assertSame(['Bare', 'Short', 'Tina camp dues'], $this->notes($this->table(['receipts' => 'without'])));
        $this->assertSame(['Bare', 'Fuel'], $this->notes($this->table(['month' => '2027-07'])));
        $this->assertSame(4, $this->table(['month' => 'July'])->get_pagination_arg('total_items'), 'A month that is not YYYY-MM is ignored');
        $this->assertSame(['Fuel'], $this->notes($this->table(['s' => 'DIESEL'])), 'Line item notes are searched');
        $this->assertSame(['Tina camp dues'], $this->notes($this->table(['s' => 'paypal'])), 'Links are searched');
        $this->assertSame(['Tina camp dues'], $this->notes($this->table(['s' => 'tina'])));
        $this->assertSame(['Fuel'], $this->notes($this->table(['flow' => 'out', 'receipts' => 'with'])), 'Filters combine');
        $this->assertSame(4, $this->table(['flow' => 'sideways', 'type' => 'Bribe', 'receipts' => 'maybe'])->get_pagination_arg('total_items'), 'Unknown values are ignored');
    }

    public function testPageSizeDefaultsToFiftyAndComesFromTheUrl()
    {
        for ($i = 1; $i <= 55; $i++) {
            $this->entry(['note' => sprintf('Entry %02d', $i), 'date' => sprintf('2027-06-%02d 00:00:00', ($i % 28) + 1)]);
        }
        $table = $this->table();
        $this->assertCount(50, $table->items);
        $this->assertSame(55, $table->get_pagination_arg('total_items'));
        $this->assertSame(2, $table->get_pagination_arg('total_pages'));
        $this->assertCount(20, $this->table(['per_page' => '20'])->items);
        $this->assertCount(55, $this->table(['per_page' => '100'])->items);
        $this->assertCount(5, $this->table(['paged' => '2'])->items);
    }

    public function testCellsShowDatesAmountsTypesReceiptsLinksAndFlags()
    {
        $fuel = $this->entry(['note' => 'Fuel', 'amount' => -832.57, 'date' => '2027-07-07 00:00:00', 'link' => 'https://www.paypal.com/activity/payment/XYZ']);
        $short = $this->entry(['note' => 'Short', 'amount' => 359.79, 'date' => '2027-06-29 00:00:00', 'link' => 'not a url']);
        $blank = $this->entry(['note' => 'Blank', 'amount' => 0, 'date' => '0000-00-00 00:00:00']);
        $this->line($fuel, 800, 'Expense', ['receipt_id' => 96]);
        $this->line($fuel, 32.57, 'Expense', ['receipt_id' => 97]);
        $this->line($short, 370.88, 'Sold Asset');
        $this->line($short, 0, '');
        $table = $this->table();
        $row = static fn(array $items, string $note) => current(array_filter($items, static fn($i) => $i['note'] === $note));

        $this->assertSame('Jul 7, 2027', $table->column_default($row($table->items, 'Fuel'), 'date'));
        $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Blank'), 'date'));
        $this->assertSame('<span class="cm-amount cm-amount--out">-$832.57</span>', $table->column_default($row($table->items, 'Fuel'), 'amount'));
        $this->assertSame('<span class="cm-amount cm-amount--in">$359.79</span>', $table->column_default($row($table->items, 'Short'), 'amount'));
        $this->assertSame('<span class="cm-amount cm-amount--zero">$0.00</span>', $table->column_default($row($table->items, 'Blank'), 'amount'));
        $this->assertSame('Expense', $table->column_default($row($table->items, 'Fuel'), 'type'));
        $this->assertSame('Sold Asset, <span class="cm-flag">Untyped</span>', $table->column_default($row($table->items, 'Short'), 'type'));
        $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Blank'), 'type'));
        $receipts = $table->column_default($row($table->items, 'Fuel'), 'receipts');
        $this->assertStringContainsString('page=camp-manager-add-receipt&#038;id=96">#96</a>', $receipts);
        $this->assertStringContainsString('#97</a>', $receipts);
        $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Short'), 'receipts'));
        $link = $table->column_default($row($table->items, 'Fuel'), 'link');
        $this->assertStringContainsString('href="https://www.paypal.com/activity/payment/XYZ"', $link);
        $this->assertStringContainsString('dashicons-external', $link);
        $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Short'), 'link'), 'Only real URLs become links');

        $cell = $table->column_note($row($table->items, 'Fuel'));
        $this->assertStringContainsString('class="row-title"', $cell);
        $this->assertStringContainsString('page=camp-manager-add-ledger&#038;id=' . $fuel, $cell);
        $this->assertStringContainsString('>View link</a>', $cell);
        $this->assertStringNotContainsString('cm-flag', $cell, 'An entry in order carries no flag');
        $cell = $table->column_note($row($table->items, 'Short'));
        $this->assertStringContainsString('dashicons-warning', $cell);
        $this->assertStringContainsString('Line items total $370.88 · Untyped line item', $cell);
        $this->assertStringNotContainsString('View link', $cell);
        $this->assertStringContainsString('No date · No line items', $table->column_note($row($table->items, 'Blank')));

        ob_start();
        $table->single_row($row($table->items, 'Short'));
        $this->assertStringStartsWith('<tr class="is-attention">', ob_get_clean());
        ob_start();
        $table->single_row($row($table->items, 'Fuel'));
        $this->assertStringStartsWith('<tr class="">', ob_get_clean());
    }

    public function testDeletingInBulkRemovesLineItemsAndNeedsTheNonce()
    {
        $gone = $this->entry(['note' => 'Gone']);
        $kept = $this->entry(['note' => 'Kept']);
        $this->line($gone, 100);
        $this->line($kept, 100);

        $_POST = $_REQUEST = ['action' => 'delete', 'ledger' => [(string) $gone], '_wpnonce' => wp_create_nonce('bulk-ledger_entries')];
        (new CampManagerLedgerTable($this->ledger))->process_bulk_action();
        $this->assertSame(['Kept'], $this->notes($this->table()));
        global $wpdb;
        $this->assertEquals([$kept], $wpdb->get_col("SELECT ledger_id FROM {$wpdb->prefix}mf_ledger_line_items"));

        $_POST = $_REQUEST = ['action' => 'delete', 'ledger' => [(string) $kept], '_wpnonce' => 'wrong'];
        try {
            (new CampManagerLedgerTable($this->ledger))->process_bulk_action();
            $this->fail('A bad nonce must stop the delete');
        } catch (\WPDieException $e) {
            $this->assertSame(['Kept'], $this->notes($this->table()));
        }
    }

    // -------------------------------------------------------------------------------- the page

    public function testPageShowsTheOverviewViewsFiltersAndTable()
    {
        $in = $this->entry(['note' => 'Tina camp dues', 'amount' => 350, 'date' => '2027-06-01 00:00:00']);
        $this->entry(['note' => 'Fuel', 'amount' => -832.57, 'date' => '2027-07-07 00:00:00']);
        $this->line($in, 350, 'Camp Dues');

        $html = $this->renderPage();

        $this->assertStringContainsString('<h1 class="wp-heading-inline">Ledger</h1>', $html);
        $this->assertStringContainsString('page=camp-manager-add-ledger" class="page-title-action">Add New</a>', $html);
        $this->assertStringContainsString('<option value="all" >All seasons</option>', $html, 'The switcher offers every season together');
        $this->assertStringContainsString('<h2 class="hndle is-non-sortable">Season overview</h2>', $html);
        $stat = static fn(string $key) => preg_match('#data-stat="' . $key . '">.*?<span class="cm-stat__value">([^<]*)</span>#s', $html, $m) ? $m[1] : null;
        $this->assertSame('$2,037.80', $stat('starting'));
        $this->assertSame('$350.00', $stat('in'));
        $this->assertSame('$832.57', $stat('out'));
        $this->assertSame('-$482.57', $stat('net'));
        $this->assertSame('$1,555.23', $stat('ending'));
        $this->assertStringContainsString('class="cm-stat cm-stat--negative" data-stat="net"', $html);
        $this->assertStringContainsString('Camp dues $350.00', $html);
        $this->assertStringContainsString('Expenses $0.00', $html);
        $this->assertStringContainsString('2 entries', $html);

        $this->assertMatchesRegularExpression('#<li class=\'all\'><a href="[^"]*page=camp-manager-ledger" class="current" aria-current="page">All <span class="count">\(2\)</span></a>#', $html);
        $this->assertMatchesRegularExpression('#<li class=\'in\'><a href="[^"]*flow=in">Money in <span class="count">\(1\)</span></a>#', $html);
        $this->assertMatchesRegularExpression('#<li class=\'attention\'><a href="[^"]*flow=attention">Needs attention <span class="count">\(1\)</span></a>#', $html);
        $this->assertStringContainsString('<form method="get" id="ledger-filters">', $html);
        $this->assertStringContainsString('Search Ledger', $html);
        foreach (['type' => 'All types', 'receipts' => 'Receipts', 'month' => 'All months'] as $name => $label) {
            $this->assertMatchesRegularExpression('#<select name="' . $name . '" id="cm-filter-' . $name . '" form="ledger-filters">\s*<option value="">' . $label . '</option>#', $html);
        }
        $this->assertStringContainsString('<option value="2027-07" >July 2027</option>', $html);
        $this->assertStringContainsString('<option value="none" >No line items</option>', $html);
        $this->assertStringContainsString('<option value="50"  selected=\'selected\'>50 items per page</option>', $html);
        foreach (['date' => 'Date', 'note' => 'Entry', 'amount' => 'Amount', 'type' => 'Line items', 'receipts' => 'Receipts'] as $id => $label) {
            $this->assertMatchesRegularExpression('#<th scope="col" id=\'' . $id . '\' class=\'[^\']*sortable[^\']*\'.*?<span>' . $label . '</span>#s', $html, $label);
        }
        $this->assertStringNotContainsString("id='season'", $html);
        $this->assertStringContainsString('Tina camp dues', $html);
        $this->assertStringContainsString('cm-amount--out">-$832.57', $html);

        // The attention view keeps its season scope and the flag shows on the row.
        $html = $this->renderPage(['flow' => 'attention']);
        $this->assertMatchesRegularExpression('#<li class=\'attention\'><a href="[^"]*flow=attention" class="current"#', $html);
        $this->assertStringContainsString('<input type="hidden" name="flow" value="attention">', $html);
        $this->assertStringContainsString('No line items', $html);
        $this->assertStringNotContainsString('Tina camp dues', $html);

        $html = $this->renderPage([CampManagerSeason::VIEW_ALL_PARAM => 'all']);
        $this->assertStringContainsString('<h2 class="hndle is-non-sortable">All seasons</h2>', $html);
        $this->assertStringContainsString('Balance now', $html);
        $this->assertStringContainsString("id='season'", $html);
        $this->assertStringContainsString('<input type="hidden" name="season_view" value="all">', $html);
    }
}
