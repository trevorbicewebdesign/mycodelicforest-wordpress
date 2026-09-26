<?php

/**
 * The Inventory admin pages: the items, totes and tote inventory list tables (columns, search,
 * filters, sorting, page size, bulk delete), the figures in their overviews, the menu they hang
 * off and the tabs and forms the page templates render.
 */
class CampManagerInventoryListTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /** @var CampManagerInventory */
    private $inventory;

    protected function _before()
    {
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }
        global $wpdb;
        $wpdb->query('COMMIT');
        // Whole-table assertions below: start from empty tables (other tests COMMIT rows).
        foreach (['mf_tote_inventory', 'mf_totes', 'mf_inventory'] as $table) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}$table");
        }
        $this->inventory = new CampManagerInventory();
        $GLOBALS['hook_suffix'] = 'toplevel_page_camp-manager-inventory';
        set_current_screen('toplevel_page_camp-manager-inventory');
    }

    protected function _after()
    {
        $_GET = $_POST = $_REQUEST = [];
    }

    // ------------------------------------------------------------------------------ fixtures

    private function item(array $data = []): int
    {
        return $this->tester->haveInDatabase('mf_inventory', $data + [
            'uuid' => 0, 'name' => 'Item', 'manufacturer' => '', 'model' => '', 'description' => '', 'quantity' => 1,
            'photo' => '', 'location' => '', 'weight' => 0, 'category' => '', 'category_name' => '', 'links' => '',
            'amp' => 0, 'set_name' => '',
        ]);
    }

    private static $toteCount = 0;

    /** Inserted through wpdb so that null status, location, uid and weight stay NULL. */
    private function tote(array $data = []): int
    {
        self::$toteCount++;
        global $wpdb;
        $wpdb->insert($wpdb->prefix . 'mf_totes', $data + [
            'name' => 'Tote ' . self::$toteCount, 'weight' => 0, 'uid' => 'UID' . self::$toteCount, 'status' => 'PACKED', 'location' => '', 'size' => 'Full',
        ]);
        return (int) $wpdb->insert_id;
    }

    private function pack(int $toteId, int $itemId, int $quantity = 1): int
    {
        return $this->tester->haveInDatabase('mf_tote_inventory', ['tote_id' => $toteId, 'inventory_id' => $itemId, 'quantity' => $quantity]);
    }

    /** An items table prepared for a request carrying $query (the URL's query string). */
    private function items(array $query = []): CampManagerInventoryTable
    {
        $_GET = $_REQUEST = $query;
        $table = new CampManagerInventoryTable($this->inventory);
        $table->prepare_items();
        return $table;
    }

    private function totes(array $query = []): CampManagerTotesTable
    {
        $_GET = $_REQUEST = $query;
        $table = new CampManagerTotesTable($this->inventory);
        $table->prepare_items();
        return $table;
    }

    private function toteInventory(array $query = [], $toteId = null): CampManagerToteInventoryTable
    {
        $_GET = $_REQUEST = $query;
        $table = new CampManagerToteInventoryTable($toteId, $this->inventory);
        $table->prepare_items();
        return $table;
    }

    private function names(WP_List_Table $table, string $key = 'name'): array
    {
        return array_map(static fn($item) => $item[$key], $table->items);
    }

    /** A page template's output, rendered as an administrator. */
    private function renderPage(string $template): string
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        ob_start();
        (static function () use ($template) {
            include WP_CONTENT_DIR . '/plugins/camp-manager/tmpl/' . $template;
        })();
        return ob_get_clean();
    }

    // ------------------------------------------------------------------------------ items

    public function testItemColumnsAreTheRedesignedSet()
    {
        $table = $this->items();
        $this->assertSame(['cb', 'name', 'quantity', 'category', 'location', 'tote', 'links'], array_keys($table->get_columns()));
        $this->assertSame(['name', 'quantity', 'category', 'location', 'tote', 'links'], array_keys($table->get_sortable_columns()));
        $this->assertSame('name', $table->get_primary_column_name());
    }

    public function testItemsListAlphabeticallyAndSortByAnyColumn()
    {
        $this->item(['name' => 'Water Jug', 'quantity' => 14, 'category_name' => 'Water']);
        $this->item(['name' => 'coffee maker', 'quantity' => 1, 'category_name' => 'Kitchen']);
        $this->item(['name' => 'DJ Monitors', 'quantity' => 2, 'category' => 'Sound']);

        $this->assertSame(['coffee maker', 'DJ Monitors', 'Water Jug'], $this->names($this->items()), 'A to Z, ignoring case, until a column is chosen');
        $this->assertSame(['Water Jug', 'DJ Monitors', 'coffee maker'], $this->names($this->items(['orderby' => 'name', 'order' => 'desc'])));
        $this->assertSame(['coffee maker', 'DJ Monitors', 'Water Jug'], $this->names($this->items(['orderby' => 'quantity'])));
        $this->assertSame(['Water Jug', 'DJ Monitors', 'coffee maker'], $this->names($this->items(['orderby' => 'quantity', 'order' => 'desc'])));
        // Category is category_name, else category.
        $this->assertSame(['coffee maker', 'DJ Monitors', 'Water Jug'], $this->names($this->items(['orderby' => 'category'])));
        $this->assertSame(['coffee maker', 'DJ Monitors', 'Water Jug'], $this->names($this->items(['orderby' => 'not-a-column'])), 'Unknown columns fall back to the name');
    }

    public function testBlankValuesSortLastWhicheverWayTheColumnGoes()
    {
        $this->item(['name' => 'Blank', 'location' => '']);
        $this->item(['name' => 'Garage', 'location' => 'Garage']);
        $this->item(['name' => 'Attic', 'location' => 'Attic']);

        $this->assertSame(['Attic', 'Garage', 'Blank'], $this->names($this->items(['orderby' => 'location'])));
        $this->assertSame(['Garage', 'Attic', 'Blank'], $this->names($this->items(['orderby' => 'location', 'order' => 'desc'])));
    }

    public function testItemsSearchMatchesNameMakerModelDescriptionCategoryLocationAndSet()
    {
        $this->item(['name' => 'Pioneer DJ', 'manufacturer' => 'Pioneer', 'model' => 'XDJ-RX2']);
        $this->item(['name' => 'Speakers', 'manufacturer' => 'Peavey', 'description' => 'the big pair']);
        $this->item(['name' => 'Kettle', 'category_name' => 'Kitchen', 'location' => 'Treehouse', 'set_name' => 'Bar']);

        $this->assertSame(['Pioneer DJ'], $this->names($this->items(['s' => 'xdj'])));
        $this->assertSame(['Speakers'], $this->names($this->items(['s' => 'peavey'])));
        $this->assertSame(['Speakers'], $this->names($this->items(['s' => 'big pair'])));
        $this->assertSame(['Kettle'], $this->names($this->items(['s' => 'kitch'])));
        $this->assertSame(['Kettle'], $this->names($this->items(['s' => 'treehouse'])));
        $this->assertSame(['Kettle'], $this->names($this->items(['s' => 'bar'])));
        $this->assertSame([], $this->names($this->items(['s' => 'nothing like this'])));
        $this->assertSame(3, $this->items(['s' => ''])->get_pagination_arg('total_items'));
    }

    public function testItemsFilterByCategoryLocationAndTote()
    {
        $sub = $this->item(['name' => 'Subwoofer', 'category_name' => 'Sound', 'category' => 'Sound', 'location' => 'Garage']);
        $mon = $this->item(['name' => 'Monitors', 'category' => 'Sound', 'location' => 'Garage']);
        $jug = $this->item(['name' => 'Jug', 'category_name' => 'Water', 'location' => 'Treehouse']);
        $loose = $this->item(['name' => 'Loose']);
        $soundTote = $this->tote(['name' => 'Sound tote']);
        $otherTote = $this->tote(['name' => 'Other tote']);
        $this->pack($soundTote, $sub);
        $this->pack($soundTote, $mon);
        $this->pack($otherTote, $jug);

        $this->assertSame(['Monitors', 'Subwoofer'], $this->names($this->items(['category' => 'Sound'])), 'category_name or category');
        $this->assertSame(['Jug'], $this->names($this->items(['category' => 'Water'])));
        $this->assertSame(['Monitors', 'Subwoofer'], $this->names($this->items(['location' => 'Garage'])));
        $this->assertSame(['Monitors', 'Subwoofer'], $this->names($this->items(['tote' => (string) $soundTote])));
        $this->assertSame(['Loose'], $this->names($this->items(['tote' => 'none'])));
        $this->assertSame(['Subwoofer'], $this->names($this->items(['tote' => (string) $soundTote, 's' => 'sub'])), 'Filters combine');
        $this->assertSame(4, $this->items(['tote' => 'bogus'])->get_pagination_arg('total_items'), 'A tote that is not a number or "none" is ignored');
        $this->assertSame(['category' => 'Sound', 'location' => '', 'tote' => (string) $soundTote], array_intersect_key($this->items(['category' => 'Sound', 'tote' => (string) $soundTote])->filters(), ['category' => 1, 'location' => 1, 'tote' => 1]));

        $this->assertSame(['Sound', 'Water'], $this->inventory->itemCategories());
        $this->assertSame(['Garage', 'Treehouse'], $this->inventory->itemLocations());
    }

    public function testPageSizeComesFromTheUrlAndDefaultsToTwenty()
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->item(['name' => sprintf('Item %02d', $i)]);
        }

        $table = $this->items();
        $this->assertSame(20, $table->perPage());
        $this->assertCount(20, $table->items);
        $this->assertSame(25, $table->get_pagination_arg('total_items'));
        $this->assertSame(2, $table->get_pagination_arg('total_pages'));
        $this->assertSame('Item 01', $table->items[0]['name']);

        $this->assertSame(['Item 21', 'Item 22', 'Item 23', 'Item 24', 'Item 25'], $this->names($this->items(['paged' => '2'])));
        $this->assertCount(25, $this->items(['per_page' => '50'])->items);
        $this->assertSame(1, $this->items(['per_page' => '100'])->get_pagination_arg('total_pages'));
        $this->assertSame(20, $this->items(['per_page' => '7'])->perPage(), 'Only the offered sizes count');
    }

    public function testItemCellsShowMakerAndModelTotesSetsAndLinks()
    {
        $tote = $this->tote(['name' => 'Sound tote']);
        $packed = $this->item(['name' => 'Pioneer DJ', 'manufacturer' => 'Pioneer', 'model' => 'XDJ-RX2', 'links' => 'https://example.com/xdj', 'category_name' => 'Sound', 'location' => 'Garage']);
        $inSet = $this->item(['name' => 'Arch piece', 'set_name' => 'Archway', 'links' => 'not a url']);
        $bare = $this->item(['name' => 'Bare']);
        $this->pack($tote, $packed);
        $table = $this->items();
        $row = static fn(array $items, string $name) => current(array_filter($items, static fn($i) => $i['name'] === $name));

        $cell = $table->column_name($row($table->items, 'Pioneer DJ'));
        $this->assertStringContainsString('class="row-title"', $cell);
        $this->assertStringContainsString('page=camp-manager-add-inventory&#038;id=' . $packed, $cell);
        $this->assertStringContainsString('<div class="cm-item-meta">Pioneer <span aria-hidden="true">&bull;</span> XDJ-RX2</div>', $cell);
        $this->assertStringContainsString('>Edit</a>', $cell);
        $this->assertStringContainsString('page=camp-manager-add-tote-inventory&#038;inventory_id=' . $packed, $cell, 'Add to tote row action');
        $this->assertStringNotContainsString('cm-item-meta', $table->column_name($row($table->items, 'Bare')), 'No second line without maker or model');

        $this->assertStringContainsString('page=camp-manager-add-tote&#038;id=' . $tote, $table->column_default($row($table->items, 'Pioneer DJ'), 'tote'));
        $this->assertStringContainsString('>Sound tote</a>', $table->column_default($row($table->items, 'Pioneer DJ'), 'tote'));
        $this->assertSame('<span class="cm-set">Archway</span>', $table->column_default($row($table->items, 'Arch piece'), 'tote'), 'The set when not in a tote');
        $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Bare'), 'tote'));

        $this->assertSame('Sound', $table->column_default($row($table->items, 'Pioneer DJ'), 'category'));
        $this->assertSame('Garage', $table->column_default($row($table->items, 'Pioneer DJ'), 'location'));
        $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Bare'), 'category'));
        $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Bare'), 'location'));

        $links = $table->column_default($row($table->items, 'Pioneer DJ'), 'links');
        $this->assertStringContainsString('href="https://example.com/xdj"', $links);
        $this->assertStringContainsString('target="_blank"', $links);
        $this->assertStringContainsString('dashicons-external', $links);
        $this->assertStringContainsString('View', $links);
        $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Arch piece'), 'links'), 'Only real URLs become links');
    }

    public function testDeletingItemsInBulkAlsoUnpacksThemAndNeedsTheNonce()
    {
        $tote = $this->tote();
        $gone = $this->item(['name' => 'Gone']);
        $kept = $this->item(['name' => 'Kept']);
        $this->pack($tote, $gone);
        $this->pack($tote, $kept);

        $_POST = $_REQUEST = ['action' => 'delete', 'inventory' => [(string) $gone], '_wpnonce' => wp_create_nonce('bulk-inventory_items')];
        (new CampManagerInventoryTable($this->inventory))->process_bulk_action();

        $this->assertSame(['Kept'], $this->names($this->items()));
        global $wpdb;
        $this->assertEquals([$kept], $wpdb->get_col("SELECT inventory_id FROM {$wpdb->prefix}mf_tote_inventory"));

        $_POST = $_REQUEST = ['action' => 'delete', 'inventory' => [(string) $kept], '_wpnonce' => 'wrong'];
        try {
            (new CampManagerInventoryTable($this->inventory))->process_bulk_action();
            $this->fail('A bad nonce must stop the delete');
        } catch (\WPDieException $e) {
            $this->assertSame(['Kept'], $this->names($this->items()));
        }
    }

    // ------------------------------------------------------------------------------ totes

    public function testToteColumnsSortingSearchAndFilters()
    {
        $packed = $this->tote(['name' => 'Coffee Supplies', 'uid' => 'R3AKB', 'status' => 'PACKED', 'location' => 'Sojourner', 'weight' => 23.7, 'size' => 'Full']);
        $ready = $this->tote(['name' => 'Big Ropes', 'uid' => 'DTMBR', 'status' => 'READY', 'location' => 'Garage', 'weight' => 5, 'size' => 'Half']);
        $blank = $this->tote(['name' => 'Decorations', 'uid' => null, 'status' => null, 'location' => null, 'weight' => null]);

        $table = $this->totes();
        $this->assertSame(['cb', 'name', 'size', 'status', 'location', 'weight', 'items'], array_keys($table->get_columns()));
        $this->assertSame(['name', 'size', 'status', 'location', 'weight', 'items'], array_keys($table->get_sortable_columns()));
        $this->assertSame(['Big Ropes', 'Coffee Supplies', 'Decorations'], $this->names($table));
        $this->assertSame(['Decorations', 'Big Ropes', 'Coffee Supplies'], $this->names($this->totes(['orderby' => 'weight'])));

        $this->assertSame(['Coffee Supplies'], $this->names($this->totes(['s' => 'r3akb'])), 'UIDs are searchable');
        $this->assertSame(['Coffee Supplies'], $this->names($this->totes(['status' => 'PACKED'])));
        $this->assertSame(['Big Ropes'], $this->names($this->totes(['location' => 'Garage'])));
        $this->assertSame(['Big Ropes'], $this->names($this->totes(['size' => 'Half'])));
        $this->assertSame(3, $this->totes(['size' => 'Huge'])->get_pagination_arg('total_items'), 'Unknown sizes are ignored');
        $this->assertSame(['PACKED', 'READY'], $this->inventory->toteStatuses());
        $this->assertSame(['Garage', 'Sojourner'], $this->inventory->toteLocations());

        $row = static fn(array $items, string $name) => current(array_filter($items, static fn($i) => $i['name'] === $name));
        $cell = $table->column_name($row($table->items, 'Coffee Supplies'));
        $this->assertStringContainsString('page=camp-manager-add-tote&#038;id=' . $packed, $cell);
        $this->assertStringContainsString('<div class="cm-item-meta">UID R3AKB</div>', $cell);
        $this->assertStringContainsString('page=camp-manager-add-tote-inventory&#038;tote_id=' . $packed, $cell, 'Add item row action');
        $this->assertSame('Packed', $table->column_default($row($table->items, 'Coffee Supplies'), 'status'));
        $this->assertSame('23.70 lbs', $table->column_default($row($table->items, 'Coffee Supplies'), 'weight'));
        foreach (['status', 'location', 'weight', 'items'] as $column) {
            $this->assertStringContainsString('cm-empty', $table->column_default($row($table->items, 'Decorations'), $column), $column);
        }
    }

    public function testTotesCountTheirItemsAndSortByThem()
    {
        $full = $this->tote(['name' => 'Full tote']);
        $empty = $this->tote(['name' => 'Empty tote']);
        $a = $this->item(['name' => 'A']);
        $b = $this->item(['name' => 'B']);
        $this->pack($full, $a, 3);
        $this->pack($full, $b, 1);

        $this->assertSame([$full => ['items' => 2, 'quantity' => 4]], $this->inventory->countToteItemsByTote([$full, $empty]));
        $this->assertSame([], $this->inventory->countToteItemsByTote([]));

        $table = $this->totes(['orderby' => 'items', 'order' => 'desc']);
        $this->assertSame(['Full tote', 'Empty tote'], $this->names($table));
        $this->assertSame('2<span class="cm-item-meta">4 pieces</span>', $table->column_default($table->items[0], 'items'));
    }

    public function testTotesOverviewFigures()
    {
        $this->tote(['status' => 'PACKED', 'location' => 'Sojourner', 'weight' => 52]);
        $this->tote(['status' => 'PACKED', 'location' => 'Sojourner', 'weight' => 23.5]);
        $this->tote(['status' => 'READY', 'location' => 'Garage', 'weight' => 10]);
        $this->tote(['status' => null, 'location' => null, 'weight' => null]);

        $this->assertEquals([
            'total' => 4, 'packed' => 2, 'ready' => 1, 'total_weight' => 85.5, 'packed_weight' => 75.5, 'sojourner_weight' => 75.5,
        ], $this->inventory->totesOverview());
        $this->assertEquals(75.5, $this->inventory->sumPackedTotes(), 'The older helpers still agree');
    }

    public function testDeletingTotesInBulkAlsoUnpacksThem()
    {
        $gone = $this->tote(['name' => 'Gone']);
        $kept = $this->tote(['name' => 'Kept']);
        $item = $this->item();
        $this->pack($gone, $item);
        $this->pack($kept, $item);

        $_POST = $_REQUEST = ['action' => 'delete', 'tote' => [(string) $gone], '_wpnonce' => wp_create_nonce('bulk-totes')];
        (new CampManagerTotesTable($this->inventory))->process_bulk_action();

        $this->assertSame(['Kept'], $this->names($this->totes()));
        global $wpdb;
        $this->assertEquals([$kept], $wpdb->get_col("SELECT tote_id FROM {$wpdb->prefix}mf_tote_inventory"));
        $this->assertSame(1, (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mf_inventory"), 'The item itself stays');
    }

    // ---------------------------------------------------------------------- tote inventory

    public function testToteInventoryListsEveryToteOrJustOne()
    {
        $sound = $this->tote(['name' => 'Sound tote']);
        $kitchen = $this->tote(['name' => 'Kitchen tote']);
        $sub = $this->item(['name' => 'Subwoofer', 'weight' => 30]);
        $kettle = $this->item(['name' => 'Kettle', 'weight' => 2.5]);
        $this->pack($sound, $sub, 1);
        $this->pack($kitchen, $kettle, 4);
        $this->pack($kitchen, $sub, 1);

        $all = $this->toteInventory();
        $this->assertSame(['cb', 'inventory_name', 'tote_name', 'quantity', 'weight', 'total_weight'], array_keys($all->get_columns()));
        $this->assertSame('inventory_name', $all->get_primary_column_name());
        $this->assertFalse($all->isEmbedded());
        $this->assertSame(['Kettle', 'Subwoofer', 'Subwoofer'], $this->names($all, 'inventory_name'));
        $this->assertSame(3, $all->get_pagination_arg('total_items'));
        $this->assertSame(['Kettle'], $this->names($this->toteInventory(['tote' => (string) $kitchen, 's' => 'kett']), 'inventory_name'));
        $this->assertSame(['Kettle', 'Subwoofer'], $this->names($this->toteInventory(['tote' => (string) $kitchen]), 'inventory_name'));
        $this->assertSame(['Subwoofer', 'Kettle'], $this->names($this->toteInventory(['orderby' => 'total_weight', 'order' => 'desc', 'tote' => (string) $kitchen]), 'inventory_name'));

        // Scoped to a tote (its edit page): only that tote's rows are counted, and no tote column.
        $scoped = $this->toteInventory([], $kitchen);
        $this->assertTrue($scoped->isEmbedded());
        $this->assertSame(['cb', 'inventory_name', 'quantity', 'weight', 'total_weight'], array_keys($scoped->get_columns()));
        $this->assertArrayNotHasKey('tote_name', $scoped->get_sortable_columns());
        $this->assertSame(2, $scoped->get_pagination_arg('total_items'));
        $this->assertSame(CampManagerToteInventoryTable::EMBEDDED_PER_PAGE, $scoped->perPage());
        $this->assertSame(0, $this->toteInventory([], false)->get_pagination_arg('total_items'), 'An unsaved tote lists nothing');

        $row = $scoped->items[0];
        $this->assertSame('Kettle', $row['inventory_name']);
        $this->assertSame('4', $scoped->column_default($row, 'quantity'));
        $this->assertSame('2.50 lbs', $scoped->column_default($row, 'weight'));
        $this->assertSame('10.00 lbs', $scoped->column_default($row, 'total_weight'));
        $this->assertStringContainsString('page=camp-manager-add-tote-inventory&#038;id=' . $row['id'], $scoped->column_inventory_name($row));
        $this->assertStringContainsString('page=camp-manager-add-inventory&#038;id=' . $kettle, $scoped->column_inventory_name($row), 'Edit item row action');
        $this->assertStringContainsString('page=camp-manager-add-tote&#038;id=' . $kitchen, $all->column_tote_name($row));
    }

    public function testToteInventoryOverviewFigures()
    {
        $sound = $this->tote(['name' => 'Sound tote']);
        $kitchen = $this->tote(['name' => 'Kitchen tote']);
        $sub = $this->item(['name' => 'Subwoofer', 'weight' => 30]);
        $kettle = $this->item(['name' => 'Kettle', 'weight' => 2.5]);
        $this->pack($sound, $sub, 1);
        $this->pack($kitchen, $kettle, 4);
        $this->pack($kitchen, $sub, 1);

        $this->assertEquals(['rows' => 3, 'items' => 2, 'quantity' => 6, 'weight' => 70.0, 'totes' => 2], $this->inventory->toteInventoryOverview());
        $this->assertEquals(['rows' => 2, 'items' => 2, 'quantity' => 5, 'weight' => 40.0, 'totes' => 1], $this->inventory->toteInventoryOverview($kitchen));
        $this->assertEquals(['rows' => 0, 'items' => 0, 'quantity' => 0, 'weight' => 0.0, 'totes' => 0], $this->inventory->toteInventoryOverview(999999));
    }

    public function testRemovingFromAToteInBulkKeepsTheItem()
    {
        $tote = $this->tote();
        $item = $this->item();
        $rowId = $this->pack($tote, $item, 2);

        $_POST = $_REQUEST = ['action' => 'delete', 'tote-inventory' => [(string) $rowId], '_wpnonce' => wp_create_nonce('bulk-tote_inventory_items')];
        (new CampManagerToteInventoryTable(null, $this->inventory))->process_bulk_action();

        $this->assertSame(0, $this->toteInventory()->get_pagination_arg('total_items'));
        $this->assertSame(1, $this->items()->get_pagination_arg('total_items'));
        $this->assertSame(1, $this->totes()->get_pagination_arg('total_items'));
    }

    // --------------------------------------------------------------------------- the menu

    public function testInventoryMenuListsTheThreeTabsAndKeepsTheEditPagesReachable()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        // The plugin's hooks are registered when it is activated, inside whichever test did
        // that, and the test framework drops them again after it. Register the pages here.
        remove_all_actions('admin_menu');
        remove_all_actions('admin_head');
        remove_all_filters('submenu_file');
        if (!has_filter('user_has_cap', [CampManagerRoles::class, 'grantCaps'])) {
            add_filter('user_has_cap', [CampManagerRoles::class, 'grantCaps'], 10, 4);
        }
        $core = new CampManagerCore();
        $receipts = new CampManagerReceipts($core, new CampManagerChatGPT($core));
        (new CampManagerPages($receipts, new CampManagerBudgets(), new CampManagerRoster(), new CampManagerLedger($receipts), $core, $this->inventory))->init();

        global $menu, $submenu, $_registered_pages, $_parent_pages, $admin_page_hooks;
        $menu = $submenu = $_registered_pages = $_parent_pages = $admin_page_hooks = [];
        do_action('admin_menu', '');

        $slugs = static fn() => array_column($GLOBALS['submenu']['camp-manager-inventory'], 2);
        $hidden = ['camp-manager-add-inventory', 'camp-manager-add-tote', 'camp-manager-add-tote-inventory'];
        $this->assertSame(['camp-manager-inventory', 'camp-manager-totes', 'camp-manager-view-tote-inventory', ...$hidden], $slugs());
        $this->assertSame(['All items', 'Totes', 'Tote inventory'], array_slice(array_column($submenu['camp-manager-inventory'], 0), 0, 3));
        $this->assertSame('Edit tote', $submenu['camp-manager-inventory'][4][3], 'Edit pages keep a title');
        foreach ($hidden as $slug) {
            $this->assertArrayHasKey(get_plugin_page_hookname($slug, 'camp-manager-inventory'), $_registered_pages, "$slug renders");
        }
        // The menu and its first entry share the page hook: one callback, so the page runs once.
        $this->assertSame(1, count($GLOBALS['wp_filter']['toplevel_page_camp-manager-inventory']->callbacks[10]), 'The items page renders once');

        // Opening an edit page: WordPress files it under Inventory, its tab is highlighted, and
        // once the head is done it is no longer in the submenu that is about to be drawn.
        $GLOBALS['plugin_page'] = 'camp-manager-add-tote';
        $GLOBALS['pagenow'] = 'admin.php';
        $GLOBALS['parent_file'] = null;
        $this->assertSame('camp-manager-inventory', get_admin_page_parent());
        $this->assertSame('camp-manager-totes', apply_filters('submenu_file', '', 'camp-manager-inventory'));
        $GLOBALS['plugin_page'] = 'camp-manager-add-inventory';
        $this->assertSame('camp-manager-inventory', apply_filters('submenu_file', '', 'camp-manager-inventory'));
        $GLOBALS['plugin_page'] = 'camp-manager-members';
        $this->assertSame('other', apply_filters('submenu_file', 'other', 'x'));

        do_action('admin_head');
        $this->assertSame(['camp-manager-inventory', 'camp-manager-totes', 'camp-manager-view-tote-inventory'], $slugs(), 'Only the tabs are listed');
        // menu-header.php resolves the parent again before drawing: the page is no longer
        // listed, so the parent resolved earlier stands and the Inventory menu stays open.
        $GLOBALS['plugin_page'] = 'camp-manager-add-tote';
        get_admin_page_parent();
        $this->assertSame('camp-manager-inventory', $GLOBALS['parent_file']);
        unset($GLOBALS['plugin_page'], $GLOBALS['parent_file']);
    }

    // -------------------------------------------------------------------------- the pages

    public function testItemsPageRendersTabsSearchFiltersAndPageSize()
    {
        $tote = $this->tote(['name' => 'Sound tote']);
        $this->item(['name' => 'Subwoofer', 'category_name' => 'Sound', 'location' => 'Garage']);
        $_GET = $_REQUEST = ['page' => 'camp-manager-inventory'];

        $html = $this->renderPage('inventory_view_all_page.php');

        $this->assertStringContainsString('<h1 class="wp-heading-inline">Inventory</h1>', $html);
        $this->assertStringContainsString('page=camp-manager-add-inventory" class="page-title-action">Add New</a>', $html);
        $this->assertMatchesRegularExpression('#<a href="[^"]*page=camp-manager-inventory" class="cm-tab is-active" data-tab="items" aria-current="page">All items</a>#', $html);
        $this->assertMatchesRegularExpression('#<a href="[^"]*page=camp-manager-totes" class="cm-tab" data-tab="totes">Totes</a>#', $html);
        $this->assertMatchesRegularExpression('#<a href="[^"]*page=camp-manager-view-tote-inventory" class="cm-tab" data-tab="tote_inventory">Tote inventory</a>#', $html);
        $this->assertStringContainsString('<form method="get" id="inventory-filters">', $html);
        $this->assertStringContainsString('<input type="hidden" name="page" value="camp-manager-inventory">', $html);
        $this->assertStringContainsString('Search Inventory', $html);
        foreach (['category' => 'All categories', 'location' => 'All locations', 'tote' => 'All totes'] as $name => $label) {
            $this->assertMatchesRegularExpression('#<select name="' . $name . '" id="cm-filter-' . $name . '" form="inventory-filters">\s*<option value="">' . $label . '</option>#', $html);
        }
        $this->assertStringContainsString('<option value="Sound" >Sound</option>', $html);
        $this->assertStringContainsString('<option value="Garage" >Garage</option>', $html);
        $this->assertStringContainsString('<option value="none" >Not in a tote</option>', $html);
        $this->assertStringContainsString('<option value="' . $tote . '" >Sound tote</option>', $html);
        $this->assertStringContainsString('<select name="per_page" id="cm-per-page" form="inventory-filters"', $html);
        $this->assertStringContainsString('<option value="20"  selected=\'selected\'>20 items per page</option>', $html);
        $this->assertStringContainsString('id="cm-filter-submit"', $html);
        foreach (['name' => 'Item', 'quantity' => 'Quantity', 'category' => 'Category', 'location' => 'Location', 'tote' => 'Tote / set', 'links' => 'Links'] as $id => $label) {
            $this->assertMatchesRegularExpression('#<th scope="col" id=\'' . $id . '\' class=\'[^\']*sortable[^\']*\'.*?<span>' . preg_quote($label, '#') . '</span>#s', $html, $label);
        }
        $this->assertStringContainsString('class="row-title"', $html);
    }

    public function testTotesPageRendersTheOverviewAndTable()
    {
        $this->tote(['name' => 'Coffee Supplies', 'status' => 'PACKED', 'location' => 'Sojourner', 'weight' => 23.5]);
        $this->tote(['name' => 'Big Ropes', 'status' => 'READY', 'location' => 'Garage', 'weight' => 6.5]);
        $_GET = $_REQUEST = ['page' => 'camp-manager-totes'];

        $html = $this->renderPage('totes_view_all_page.php');

        $this->assertStringContainsString('<h1 class="wp-heading-inline">Inventory</h1>', $html);
        $this->assertStringContainsString('page=camp-manager-add-tote" class="page-title-action">Add New</a>', $html);
        $this->assertStringContainsString('data-tab="totes" aria-current="page">Totes</a>', $html);
        $this->assertStringContainsString('id="totes-overview"', $html);
        $this->assertStringContainsString('<h2 class="hndle is-non-sortable">Totes overview</h2>', $html);
        $stat = static fn(string $key) => preg_match('#data-stat="' . $key . '">.*?<span class="cm-stat__value">([^<]*)</span>#s', $html, $m) ? $m[1] : null;
        $this->assertSame('2', $stat('total'));
        $this->assertSame('1', $stat('packed'));
        $this->assertSame('1', $stat('ready'));
        $this->assertSame('23.5 lbs', $stat('packed_weight'));
        $this->assertSame('23.5 lbs', $stat('sojourner_weight'));
        $this->assertStringContainsString('All totes together weigh 30.0 lbs.', $html);
        $this->assertStringContainsString('<form method="get" id="totes-filters">', $html);
        $this->assertStringContainsString('Search Totes', $html);
        $this->assertStringContainsString('<option value="PACKED" >Packed</option>', $html);
        $this->assertStringContainsString('<option value="Half" >Half</option>', $html);
        $this->assertStringContainsString('Big Ropes', $html);
    }

    public function testToteInventoryPageRendersTheOverviewAndTable()
    {
        $tote = $this->tote(['name' => 'Kitchen tote']);
        $kettle = $this->item(['name' => 'Kettle', 'weight' => 2.5]);
        $this->pack($tote, $kettle, 4);
        $_GET = $_REQUEST = ['page' => 'camp-manager-view-tote-inventory'];

        $html = $this->renderPage('tote_inventory_view_all_page.php');

        $this->assertStringContainsString('data-tab="tote_inventory" aria-current="page">Tote inventory</a>', $html);
        $this->assertStringContainsString('page=camp-manager-add-tote-inventory" class="page-title-action">Add New</a>', $html);
        $this->assertStringContainsString('<h2 class="hndle is-non-sortable">Packed overview</h2>', $html);
        $stat = static fn(string $key) => preg_match('#data-stat="' . $key . '">.*?<span class="cm-stat__value">([^<]*)</span>#s', $html, $m) ? $m[1] : null;
        $this->assertSame('1', $stat('items'));
        $this->assertSame('4', $stat('quantity'));
        $this->assertSame('10.0 lbs', $stat('weight'));
        $this->assertSame('1', $stat('totes'));
        $this->assertStringContainsString('<form method="get" id="tote-inventory-filters">', $html);
        $this->assertStringContainsString('<option value="' . $tote . '" >Kitchen tote</option>', $html);
        $this->assertMatchesRegularExpression('#<th scope="col" id=\'tote_name\'#', $html);
        $this->assertStringContainsString('Kettle', $html);

        $_GET = $_REQUEST = ['page' => 'camp-manager-view-tote-inventory', 'tote' => (string) $tote];
        $this->assertStringContainsString('<h2 class="hndle is-non-sortable">This tote</h2>', $this->renderPage('tote_inventory_view_all_page.php'));
    }
}
