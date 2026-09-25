<?php

/**
 * The Camp Manager dashboard: the figures it shows, the collapsible boxes it is made of, and
 * the template that puts them together.
 */
class CampManagerDashboardTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    private $ledger;
    private $roster;
    private $receipts;

    protected function _before()
    {
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }
        global $wpdb;
        $wpdb->query('COMMIT');
        // Whole-table figures below: start from empty tables (other tests COMMIT rows).
        foreach (['mf_ledger_line_items', 'mf_ledger', 'mf_receipts', 'mf_roster'] as $table) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}$table");
        }
        update_option(CampManagerSeason::OPTION_CURRENT, 2027);

        $core = new CampManagerCore();
        $this->receipts = new CampManagerReceipts($core, new CampManagerChatGPT($core));
        $this->ledger = new CampManagerLedger($this->receipts);
        $this->roster = new CampManagerRoster();
        set_current_screen('toplevel_page_camp-manager');
    }

    private function dashboard(): CampManagerDashboard
    {
        return new CampManagerDashboard($this->ledger, $this->roster, $this->receipts);
    }

    private function member(array $data = []): int
    {
        return $this->tester->haveInDatabase('mf_roster', $data + [
            'wpid' => 0, 'fname' => 'Test', 'lname' => 'Member', 'playaname' => '', 'email' => '',
            'status' => 'Confirmed', 'season' => 2027, 'low_income' => 0, 'fully_paid' => 0,
        ]);
    }

    private function ledgerEntry(float $amount, int $season = 2027): int
    {
        return $this->tester->haveInDatabase('mf_ledger', ['amount' => $amount, 'note' => 'entry', 'date' => "$season-03-01", 'season' => $season]);
    }

    private function lineItem(int $ledgerId, string $type, float $amount, int $cmid = 0): void
    {
        $this->tester->haveInDatabase('mf_ledger_line_items', [
            'ledger_id' => $ledgerId, 'cmid' => $cmid, 'amount' => $amount, 'type' => $type, 'name' => '', 'receipt_id' => 0,
        ]);
    }

    private function receipt(float $total, ?int $reimbursed = 0): void
    {
        $this->tester->haveInDatabase('mf_receipts', ['store' => 'Store', 'date' => '2027-05-01', 'total' => $total, 'season' => 2027, 'reimbursed' => $reimbursed]);
    }

    /** The dashboard template's output, as the given kind of user sees it. */
    private function renderPage(string $role): string
    {
        wp_set_current_user(self::factory()->user->create(['role' => $role]));
        ob_start();
        (function () {
            include WP_CONTENT_DIR . '/plugins/camp-manager/tmpl/dashboard_page.php';
        })();
        return ob_get_clean();
    }

    // ------------------------------------------------------------------------------ figures

    public function testAnEmptySeasonStartsAtTheOpeningBalance()
    {
        $d = $this->dashboard()->summary();

        $this->assertEquals(CampManagerLedger::OPENING_BALANCE, $d['paypal_balance']);
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE, $d['starting_balance']);
        foreach (['money_in', 'money_out', 'camp_dues', 'donations', 'other_revenue', 'unpaid_receipts', 'funds_remaining', 'members', 'dues_expected', 'dues_remaining'] as $key) {
            $this->assertEquals(0, $d[$key], $key);
        }
    }

    public function testMoneyAndRevenueFigures()
    {
        $in = $this->ledgerEntry(600);
        $this->ledgerEntry(-150);
        $this->ledgerEntry(9999, 2025); // another season
        $this->lineItem($in, 'Camp Dues', 350);
        $this->lineItem($in, 'Partial Camp Dues', 50);
        $this->lineItem($in, 'Donation', 120);
        $this->lineItem($in, 'Sold Asset', 80);
        $this->receipt(200, 0);
        $this->receipt(75, null);
        $this->receipt(500, 1); // reimbursed, so not owed

        $d = $this->dashboard()->summary();

        $this->assertEquals(600, $d['money_in']);
        $this->assertEquals(150, $d['money_out']);
        $this->assertEquals(450, $d['funds_remaining']);
        $this->assertEquals(400, $d['camp_dues'], 'Camp dues and partial camp dues');
        $this->assertEquals(120, $d['donations']);
        $this->assertEquals(80, $d['other_revenue']);
        $this->assertEquals(275, $d['unpaid_receipts'], 'Receipts not yet reimbursed');
        $this->assertEquals(450 - 275, $d['ledger_remaining']);
        // Balance carries over from earlier seasons: opening + 2025's 9999, then this season in and out.
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE + 9999, $d['starting_balance']);
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE + 9999 + 450, $d['paypal_balance']);
    }

    public function testLedgerRemainingGoesNegativeWhenReceiptsExceedFunds()
    {
        $this->receipt(350);

        $d = $this->dashboard()->summary();

        $this->assertEquals(-350, $d['ledger_remaining']);
    }

    public function testMembershipAndDuesFigures()
    {
        // Confirmed: two standard (one paid), two low income (one paid). Dropped and other-season members don't count.
        $this->member(['fully_paid' => 1]);
        $this->member();
        $this->member(['low_income' => 1, 'fully_paid' => 1]);
        $this->member(['low_income' => 1]);
        $this->member(['status' => 'Dropped']);
        $this->member(['season' => 2025]);

        $d = $this->dashboard()->summary();

        $this->assertSame(4, $d['members']);
        $this->assertSame(2, $d['members_paid']);
        $this->assertSame(2, $d['regular_members']);
        $this->assertSame(1, $d['regular_paid']);
        $this->assertSame(2, $d['low_members']);
        $this->assertSame(1, $d['low_paid']);
        $this->assertEquals(700, $d['regular_expected']);
        $this->assertEquals(500, $d['low_expected']);
        $this->assertEquals(1200, $d['dues_expected']);
        // What the two unpaid confirmed members still owe: one standard, one low income.
        $this->assertEquals(600, $d['dues_remaining']);
    }

    public function testEstimatedRevenueRemainingAddsOutstandingDuesToMoneyInLessMoneyOut()
    {
        $this->ledgerEntry(1000);
        $this->ledgerEntry(-300);
        $this->member(); // owes 350

        $this->assertEquals(1000 + 350 - 300, $this->dashboard()->summary()['estimated_revenue_remaining']);
    }

    public function testMoneyFormatting()
    {
        $this->assertSame('$0.00', CampManagerDashboard::money(0));
        $this->assertSame('$1,234.50', CampManagerDashboard::money(1234.5));
        $this->assertSame('-$350.00', CampManagerDashboard::money(-350));
    }

    // ---------------------------------------------------------------------------- postboxes

    private function box(string $screen = 'camp-manager-test', array $args = []): string
    {
        ob_start();
        CampManagerPostbox::open('test-box', 'Test box', $screen, $args);
        echo 'contents';
        CampManagerPostbox::close();
        return ob_get_clean();
    }

    public function testPostboxRendersTitleToggleAndContents()
    {
        $html = $this->box();

        $this->assertStringContainsString('id="test-box"', $html);
        $this->assertStringContainsString('<h2 class="hndle is-non-sortable">Test box</h2>', $html);
        $this->assertStringContainsString('aria-expanded="true"', $html);
        $this->assertStringContainsString('Toggle panel: Test box', $html);
        $this->assertStringContainsString('contents', $html);
        $this->assertStringNotContainsString(' closed', $html);
        $this->assertSame(substr_count($html, '<div'), substr_count($html, '</div>'), 'Every div the box opens is closed');
    }

    public function testPostboxStartsClosedWhenTheUserLeftItClosed()
    {
        $userId = self::factory()->user->create(['role' => 'administrator']);
        wp_set_current_user($userId);
        update_user_option($userId, 'closedpostboxes_camp-manager-test', ['test-box']);

        $html = $this->box();

        $this->assertMatchesRegularExpression('#class="postbox cm-postbox[^"]* closed"#', $html);
        $this->assertStringContainsString('aria-expanded="false"', $html);
        // Closed state is per screen.
        $this->assertStringNotContainsString(' closed', $this->box('another-screen'));
    }

    public function testPostboxHeaderExtraAndPadding()
    {
        $html = $this->box('camp-manager-test', ['header' => '<a href="/roster/">View roster</a>', 'padded' => true, 'class' => 'extra']);

        $this->assertStringContainsString('<a href="/roster/">View roster</a>', $html);
        $this->assertStringContainsString('cm-postbox--padded', $html);
        $this->assertStringContainsString('extra', $html);
    }

    public function testSwitcherShowsTrailingHtmlOnTheSeasonRow()
    {
        set_current_screen('dashboard');
        ob_start();
        CampManagerSeason::renderSwitcher(false, '<button type="button" id="extra-button">Season settings</button>');
        $html = ob_get_clean();

        $this->assertMatchesRegularExpression('#camp-manager-season-trailing[^>]*><button type="button" id="extra-button">Season settings</button>#', $html);

        ob_start();
        CampManagerSeason::renderSwitcher();
        $this->assertStringNotContainsString('camp-manager-season-trailing', ob_get_clean());
    }

    // ---------------------------------------------------------------------------- the page

    public function testPageShowsTheFiguresAndLinks()
    {
        $in = $this->ledgerEntry(1000);
        $this->lineItem($in, 'Camp Dues', 350);
        $this->member(['fully_paid' => 1]);
        $this->member();
        $this->receipt(350);

        $html = $this->renderPage('administrator');

        foreach (['Season overview', 'Financial summary', 'Ledger summary', 'Membership & dues'] as $title) {
            $this->assertStringContainsString('>' . str_replace('&', '&amp;', $title) . '</h2>', $html, $title);
        }
        $this->assertMatchesRegularExpression('#data-stat="dues".*?\$350\.00.*?of \$700\.00 expected#s', $html);
        $this->assertMatchesRegularExpression('#data-stat="members".*?<span class="cm-stat__value">2</span>.*?1 fully paid#s', $html);
        $this->assertMatchesRegularExpression('#data-stat="receipts".*?\$350\.00.*?Review receipts#s', $html);
        $this->assertStringContainsString('2 members &middot;', $html);
        $this->assertStringContainsString('$350.00 dues remaining', $html);
        $this->assertStringContainsString('Collected revenue: <strong>$1,000.00</strong>', $html);
        $this->assertStringContainsString('page=camp-manager-actuals', $html);
        $this->assertStringContainsString('page=camp-manager-ledger', $html);
        $this->assertStringContainsString('page=camp-manager-members', $html);
        $this->assertStringContainsString('View financial details', $html);
        $this->assertStringContainsString('View ledger', $html);
        $this->assertStringContainsString('View roster', $html);
    }

    public function testPageFlagsReceiptsAwaitingReimbursementOnlyWhenThereAreSome()
    {
        $this->assertStringNotContainsString('data-callout="receipts"', $this->renderPage('administrator'));

        $this->receipt(350);
        $html = $this->renderPage('administrator');

        $this->assertStringContainsString('data-callout="receipts"', $html);
        $this->assertStringContainsString('$350.00 in receipts awaiting reimbursement.', $html);
    }

    public function testNegativeLedgerRemainingIsMarkedRed()
    {
        $this->receipt(350);

        $html = $this->renderPage('administrator');

        $this->assertMatchesRegularExpression('#<th>Remaining</th><td class="cm-negative">-\$350\.00</td>#', $html);
    }

    public function testDuesTableHasFullLowIncomeAndTotalRows()
    {
        $this->member();
        $this->member(['low_income' => 1, 'fully_paid' => 1]);

        $html = $this->renderPage('administrator');

        $this->assertMatchesRegularExpression('#data-dues="full">.*?\(\$350\).*?>1<.*?>0<.*?\$350\.00#s', $html);
        $this->assertMatchesRegularExpression('#data-dues="low">.*?\(\$250\).*?>1<.*?>1<.*?\$250\.00#s', $html);
        $this->assertMatchesRegularExpression('#data-dues="total">.*?Total.*?>2<.*?>1<.*?\$600\.00#s', $html);
    }

    public function testSeasonSettingsOnlyForAdministrators()
    {
        $admin = $this->renderPage('administrator');
        $this->assertStringContainsString('id="cm-season-settings-toggle"', $admin);
        $this->assertStringContainsString('Start a new season', $admin);
        $this->assertMatchesRegularExpression('#id="cm-season-settings"[^>]*\bhidden\b#', $admin, 'Hidden until the button is used');

        $editor = $this->renderPage('editor');
        $this->assertStringNotContainsString('cm-season-settings-toggle', $editor);
        $this->assertStringNotContainsString('Start a new season', $editor);
    }
}
