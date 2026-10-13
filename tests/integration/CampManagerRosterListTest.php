<?php

/**
 * The admin Roster page: season overview figures, status views, the list table's columns,
 * filters, search and sorting, and the season switcher shown above it.
 */
class CampManagerRosterListTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    private $ledger;
    private $ledgerId;

    protected function _before()
    {
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }
        global $wpdb;
        $wpdb->query('COMMIT');
        // Whole-table assertions below: start from empty tables (other tests COMMIT rows).
        foreach (['mf_role_members', 'mf_ledger_line_items', 'mf_ledger', 'mf_roster'] as $table) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}$table");
        }
        update_option(CampManagerSeason::OPTION_CURRENT, 2027);

        $this->ledger = new CampManagerLedger(new CampManagerReceipts(new CampManagerCore(), new CampManagerChatGPT(new CampManagerCore())));
        $this->ledgerId = $this->tester->haveInDatabase('mf_ledger', ['amount' => 0, 'note' => 'dues', 'date' => '2027-03-01', 'season' => 2027]);
        $GLOBALS['hook_suffix'] = 'toplevel_page_camp-manager';
        set_current_screen('toplevel_page_camp-manager');
    }

    protected function _after()
    {
        $_GET = $_POST = $_REQUEST = [];
    }

    private function member(array $data = []): int
    {
        return $this->tester->haveInDatabase('mf_roster', $data + [
            'wpid' => 0, 'fname' => 'Test', 'lname' => 'Member', 'playaname' => '', 'email' => '',
            'status' => 'Confirmed', 'season' => 2027, 'low_income' => 0, 'fully_paid' => 0,
        ]);
    }

    private function duesPayment(int $memberId, float $amount, string $type = 'Camp Dues'): void
    {
        $this->tester->haveInDatabase('mf_ledger_line_items', [
            'ledger_id' => $this->ledgerId, 'cmid' => $memberId, 'amount' => $amount, 'type' => $type, 'name' => '', 'receipt_id' => 0,
        ]);
    }

    /** A table prepared for a request carrying $query (the URL's query string). */
    private function table(array $query = []): CampManagerRosterTable
    {
        $_GET = $_REQUEST = $query;
        $table = new CampManagerRosterTable($this->ledger);
        $table->prepare_items();
        return $table;
    }

    private function names(CampManagerRosterTable $table): array
    {
        return array_map(static fn($item) => $item['fname'], $table->items);
    }

    // ------------------------------------------------------------------ data behind the page

    public function testSumCampDuesByMemberTotalsDuesAndPartialDuesPerMember()
    {
        $a = $this->member();
        $b = $this->member();
        $none = $this->member();
        $this->duesPayment($a, 100);
        $this->duesPayment($a, 40.5, 'Partial Camp Dues');
        $this->duesPayment($b, 350);
        $this->duesPayment($b, 25, 'Donation'); // not dues

        $totals = $this->ledger->sumCampDuesByMember([$a, $b, $none]);

        $this->assertEquals([$a => 140.5, $b => 350.0], $totals);
        $this->assertSame([], $this->ledger->sumCampDuesByMember([]));
    }

    public function testCountByStatusIsScopedToTheViewedSeason()
    {
        $this->member(['status' => 'Confirmed']);
        $this->member(['status' => 'Confirmed']);
        $this->member(['status' => 'Dropped']);
        $this->member(['status' => 'Confirmed', 'season' => 2025]);

        $this->assertSame(['all' => 3, 'confirmed' => 2, 'dropped' => 1], (new CampManagerRoster())->countByStatus());
    }

    public function testSeasonOverviewFigures()
    {
        // 3 confirmed (one low income, one of those paid), 1 dropped, 1 from another season.
        $paid = $this->member(['fully_paid' => 1]);
        $this->member(['fully_paid' => 1, 'low_income' => 1]);
        $this->member(['fully_paid' => 0]);
        $this->member(['status' => 'Dropped']);
        $this->member(['season' => 2025, 'fully_paid' => 1]);
        $this->duesPayment($paid, 350);

        $overview = (new CampManagerRoster())->seasonOverview($this->ledger);

        $this->assertSame(4, $overview['total']);
        $this->assertSame(3, $overview['confirmed']);
        $this->assertSame(1, $overview['unpaid'], 'Confirmed members who have not paid in full');
        $this->assertEquals(350.0, $overview['dues_collected']);
        // 3 standard at 350 + 1 low income at 250.
        $this->assertEquals(1300.0, $overview['dues_expected']);
        $this->assertSame(1, $overview['low_income']);
        $this->assertSame(1, $overview['low_income_dues_paid']);
    }

    // ------------------------------------------------------------------------- the member form

    public function testMemberFormHasNoSponsorFieldAnyMore()
    {
        // The sponsor (who invited a first-time camper) is a fact about the person, kept on
        // their WordPress profile, not on a season's roster row.
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        // The page checks a Camp Manager capability, granted by a filter the plugin adds on
        // activation, which the test framework drops between test classes.
        if (!has_filter('user_has_cap', [CampManagerRoles::class, 'grantCaps'])) {
            add_filter('user_has_cap', [CampManagerRoles::class, 'grantCaps'], 10, 4);
        }
        $_GET = $_REQUEST = ['page' => 'camp-manager-add-member'];
        $host = new class { public $roster; public function render(): void { include WP_CONTENT_DIR . '/plugins/camp-manager/tmpl/members_add_page.php'; } };
        $host->roster = new CampManagerRoster();
        ob_start();
        $host->render();
        $html = ob_get_clean();

        $this->assertStringContainsString('Add New Member', $html);
        $this->assertStringContainsString('id="member_fname"', $html);
        $this->assertStringNotContainsString('member_sponsor', $html);
        $this->assertStringNotContainsString('Sponsor', $html);
    }

    // ------------------------------------------------------------------------- the list table

    public function testColumnsAreTheRedesignedSet()
    {
        $this->assertSame(
            ['cb', 'member', 'playaname', 'camp_dues', 'dues_category', 'payment', 'status', 'roles'],
            array_keys($this->table()->get_columns())
        );
        $this->assertSame(
            ['member', 'playaname', 'camp_dues', 'dues_category', 'payment', 'status', 'roles'],
            array_keys($this->table()->get_sortable_columns())
        );
    }

    public function testListsOnlyTheViewedSeasonNewestFirstWithDroppedLast()
    {
        $this->member(['fname' => 'First']);
        $this->member(['fname' => 'Dropped', 'status' => 'Dropped']);
        $this->member(['fname' => 'Old', 'season' => 2025]);
        $this->member(['fname' => 'Newest']);

        $this->assertSame(['Newest', 'First', 'Dropped'], $this->names($this->table()));
    }

    public function testRowsCarryDuesCategoryAndPayment()
    {
        $id = $this->member(['fname' => 'Dane', 'low_income' => 1, 'fully_paid' => 1]);
        $this->duesPayment($id, 250);

        [$row] = $this->table()->items;

        $this->assertEquals(250.0, $row['camp_dues']);
        $this->assertSame('Low income', $row['dues_category']);
        $this->assertTrue($row['paid']);
        $this->assertFalse($row['dropped']);
    }

    public function testStatusViewFiltersMembers()
    {
        $this->member(['fname' => 'Kept']);
        $this->member(['fname' => 'Gone', 'status' => 'Dropped']);

        $this->assertSame(['Kept'], $this->names($this->table(['member_status' => 'confirmed'])));
        $this->assertSame(['Gone'], $this->names($this->table(['member_status' => 'dropped'])));
        $this->assertCount(2, $this->table(['member_status' => 'bogus'])->items, 'An unknown view shows everyone');
    }

    public function testPaymentAndDuesCategoryFilters()
    {
        $this->member(['fname' => 'PaidStandard', 'fully_paid' => 1]);
        $this->member(['fname' => 'UnpaidStandard', 'fully_paid' => 0]);
        $this->member(['fname' => 'PaidLow', 'fully_paid' => 1, 'low_income' => 1]);
        $this->member(['fname' => 'UnpaidLow', 'fully_paid' => 0, 'low_income' => 1]);

        $this->assertEqualsCanonicalizing(['PaidStandard', 'PaidLow'], $this->names($this->table(['payment_status' => 'paid'])));
        $this->assertEqualsCanonicalizing(['UnpaidStandard', 'UnpaidLow'], $this->names($this->table(['payment_status' => 'unpaid'])));
        $this->assertEqualsCanonicalizing(['PaidLow', 'UnpaidLow'], $this->names($this->table(['dues_category' => 'low_income'])));
        $this->assertEqualsCanonicalizing(['PaidStandard', 'UnpaidStandard'], $this->names($this->table(['dues_category' => 'standard'])));
        $this->assertSame(['UnpaidLow'], $this->names($this->table(['payment_status' => 'unpaid', 'dues_category' => 'low_income'])));
    }

    public function testSearchMatchesNameFullNamePlayaNameAndEmail()
    {
        $this->member(['fname' => 'Tina', 'lname' => 'Nakamura', 'playaname' => 'GoGo', 'email' => 'tina@seed.test']);
        $this->member(['fname' => 'Carl', 'lname' => 'Martin', 'playaname' => 'Moose', 'email' => 'carl@seed.test']);

        $this->assertSame(['Tina'], $this->names($this->table(['s' => 'nakam'])));
        $this->assertSame(['Carl'], $this->names($this->table(['s' => 'Carl Martin'])), 'Full name, first then last');
        $this->assertSame(['Carl'], $this->names($this->table(['s' => 'moose'])));
        $this->assertSame(['Tina'], $this->names($this->table(['s' => 'tina@seed'])));
        $this->assertSame([], $this->names($this->table(['s' => 'nobody'])));
    }

    public function testSearchTreatsLikeWildcardsAsPlainText()
    {
        $this->member(['fname' => 'Wild']);
        $this->assertSame([], $this->names($this->table(['s' => '%'])));
    }

    public function testSortingByComputedAndPlainColumns()
    {
        $bea = $this->member(['fname' => 'Bea', 'playaname' => 'Zed', 'fully_paid' => 1]);
        $this->member(['fname' => 'Abe', 'playaname' => 'Amp', 'low_income' => 1]);
        $cal = $this->member(['fname' => 'Cal', 'playaname' => 'Mid']);
        $this->duesPayment($bea, 350);
        $this->duesPayment($cal, 100);

        $this->assertSame(['Abe', 'Bea', 'Cal'], $this->names($this->table(['orderby' => 'member'])));
        $this->assertSame(['Cal', 'Bea', 'Abe'], $this->names($this->table(['orderby' => 'member', 'order' => 'desc'])));
        $this->assertSame(['Abe', 'Cal', 'Bea'], $this->names($this->table(['orderby' => 'playaname'])));
        $this->assertSame(['Abe', 'Cal', 'Bea'], $this->names($this->table(['orderby' => 'camp_dues'])));
        $this->assertSame(['Bea', 'Cal', 'Abe'], $this->names($this->table(['orderby' => 'camp_dues', 'order' => 'desc'])));
        $this->assertSame(['Cal', 'Abe', 'Bea'], $this->names($this->table(['orderby' => 'payment'])), 'Unpaid first, ties newest first');
        $this->assertSame(['Cal', 'Bea', 'Abe'], $this->names($this->table(['orderby' => 'dues_category'])), 'Standard first, ties newest first');
    }

    public function testDroppedMembersStayLastWhateverTheSort()
    {
        $this->member(['fname' => 'Aaron', 'status' => 'Dropped']);
        $this->member(['fname' => 'Zoe']);

        $this->assertSame(['Zoe', 'Aaron'], $this->names($this->table(['orderby' => 'member'])));
        $this->assertSame(['Zoe', 'Aaron'], $this->names($this->table(['orderby' => 'member', 'order' => 'desc'])));
    }

    public function testUnknownSortColumnFallsBackToNewestFirst()
    {
        $this->member(['fname' => 'Older']);
        $this->member(['fname' => 'Newer']);

        $this->assertSame(['Newer', 'Older'], $this->names($this->table(['orderby' => 'email; DROP TABLE x'])));
    }

    public function testPaginationAndItemCount()
    {
        for ($i = 1; $i <= CampManagerRosterTable::PER_PAGE + 5; $i++) {
            $this->member(['fname' => sprintf('M%02d', $i)]);
        }

        $first = $this->table(['orderby' => 'member']);
        $this->assertCount(CampManagerRosterTable::PER_PAGE, $first->items);
        $this->assertSame(CampManagerRosterTable::PER_PAGE + 5, $first->get_pagination_arg('total_items'));
        $this->assertSame(2, $first->get_pagination_arg('total_pages'));

        $second = $this->table(['orderby' => 'member', 'paged' => 2]);
        $this->assertSame(['M21', 'M22', 'M23', 'M24', 'M25'], $this->names($second));
    }

    // ------------------------------------------------------------------------- bulk delete

    private function postBulkDelete(array $ids, ?string $nonce): void
    {
        $_POST = ['action' => 'delete', 'member' => $ids];
        $_REQUEST = ['action' => 'delete', 'member' => $ids] + ($nonce ? ['_wpnonce' => $nonce] : []);
        (new CampManagerRosterTable($this->ledger))->process_bulk_action();
    }

    public function testBulkDeleteRemovesTheSelectedMembers()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $keep = $this->member(['fname' => 'Keep']);
        $goneA = $this->member(['fname' => 'GoneA']);
        $goneB = $this->member(['fname' => 'GoneB']);

        $this->postBulkDelete([$goneA, $goneB], wp_create_nonce('bulk-roster'));

        $roster = new CampManagerRoster();
        $this->assertNotNull($roster->getMemberById($keep));
        $this->assertNull($roster->getMemberById($goneA));
        $this->assertNull($roster->getMemberById($goneB));
    }

    public function testBulkDeleteWithoutANonceIsRefused()
    {
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $id = $this->member();

        try {
            $this->postBulkDelete([$id], null);
            $this->fail('A delete without a valid nonce should be refused');
        } catch (\WPDieException $e) {
            $this->assertNotNull((new CampManagerRoster())->getMemberById($id), 'The member must survive');
        } finally {
            $_POST = [];
        }
    }

    // ---------------------------------------------------------------------------- rendering

    private function render(CampManagerRosterTable $table): string
    {
        ob_start();
        $table->views();
        $table->display();
        return ob_get_clean();
    }

    public function testRenderedRowShowsMemberEditLinkPaymentAndDues()
    {
        $adminId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($adminId);
        $linked = self::factory()->user->create();
        $id = $this->member(['fname' => 'Ari', 'lname' => 'Friend', 'playaname' => 'Sparkle', 'fully_paid' => 1, 'wpid' => $linked]);
        $this->duesPayment($id, 240);
        $this->member(['fname' => 'Sasha', 'lname' => 'K']);

        $html = $this->render($this->table());

        $this->assertStringContainsString('page=camp-manager-add-member&#038;id=' . $id, $html);
        $this->assertMatchesRegularExpression('#<a class="row-title" href="[^"]*id=' . $id . '">Ari Friend</a>#', $html);
        $this->assertStringContainsString('Sparkle', $html);
        $this->assertStringContainsString('$240.00', $html);
        $this->assertStringContainsString('roster-payment--paid', $html);
        $this->assertStringContainsString('roster-payment--unpaid', $html);
        // Only the member linked to a WordPress account gets a View action.
        $this->assertSame(1, substr_count($html, "<span class='view'>"));
        $this->assertStringContainsString('user_id=' . $linked, $html);
        $this->assertSame(2, substr_count($html, "<span class='edit'>"));
    }

    public function testRenderedHeadersAreTheNewColumns()
    {
        $this->member();
        $html = $this->render($this->table());

        foreach (['member' => 'Member', 'playaname' => 'Playa name', 'camp_dues' => 'Camp dues', 'dues_category' => 'Dues category',
                  'payment' => 'Payment', 'status' => 'Status', 'roles' => 'Camp roles'] as $id => $label) {
            $this->assertMatchesRegularExpression('#<th[^>]*id=[\'"]' . $id . '[\'"][^>]*>.*' . preg_quote($label, '#') . '#s', $html, "$label header");
        }
        foreach (['fname', 'lname', 'wpid', 'low_income', 'fully_paid'] as $gone) {
            $this->assertStringNotContainsString("id='$gone'", $html, "$gone column was removed");
        }
    }

    public function testStatusViewsShowCountsAndMarkTheCurrentOne()
    {
        $this->member();
        $this->member();
        $this->member(['status' => 'Dropped']);

        $all = $this->render($this->table());
        $this->assertStringContainsString('All <span class="count">(3)</span>', $all);
        $this->assertStringContainsString('Confirmed <span class="count">(2)</span>', $all);
        $this->assertStringContainsString('Dropped <span class="count">(1)</span>', $all);
        $this->assertMatchesRegularExpression('#class="current" aria-current="page">All#', $all);

        $dropped = $this->render($this->table(['member_status' => 'dropped']));
        $this->assertMatchesRegularExpression('#class="current" aria-current="page">Dropped#', $dropped);
        $this->assertStringContainsString('member_status=confirmed', $dropped);
    }

    public function testFilterDropdownsKeepTheirSelection()
    {
        $this->member();
        $html = $this->render($this->table(['payment_status' => 'unpaid', 'dues_category' => 'low_income']));

        $this->assertMatchesRegularExpression('#name="payment_status"[^>]*form="roster-filters".*?<option value="unpaid"\s+selected=#s', $html);
        $this->assertMatchesRegularExpression('#name="dues_category"[^>]*form="roster-filters".*?<option value="low_income"\s+selected=#s', $html);
        $this->assertStringContainsString('id="roster-filter-submit"', $html);
    }

    public function testEmptyResultShowsAMessage()
    {
        $this->assertStringContainsString('No members found.', $this->render($this->table(['s' => 'nobody'])));
    }

    // ------------------------------------------------------------------------- season switcher

    private function switcher(): string
    {
        ob_start();
        CampManagerSeason::renderSwitcher();
        return ob_get_clean();
    }

    public function testSwitcherLabelsTheCurrentSeasonAndShowsNoArchiveNotice()
    {
        set_current_screen('dashboard');
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        $this->member();

        $html = $this->switcher();

        $this->assertMatchesRegularExpression('#camp-manager-season-status[^>]*>Current<#', $html);
        $this->assertStringNotContainsString('archived', $html);
    }

    public function testSwitcherLabelsAnArchivedSeasonAndLinksBackToTheCurrentOne()
    {
        set_current_screen('dashboard');
        $userId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
        $this->member(['season' => 2025]);
        update_user_meta($userId, CampManagerSeason::USER_META_VIEWING, 2025);

        $html = $this->switcher();

        $this->assertMatchesRegularExpression('#camp-manager-season-status[^>]*>Archived<#', $html);
        $this->assertStringContainsString('You are viewing the archived <strong>2025</strong> season.', $html);
        $this->assertStringContainsString('New entries will be saved to <strong>2025</strong>.', $html);
        $this->assertStringContainsString('The current season is <strong>2027</strong>.', $html);
        $this->assertMatchesRegularExpression('#<a href="[^"]*camp_manager_switch_season=2027">View current season\.</a>#', $html);
    }
}
