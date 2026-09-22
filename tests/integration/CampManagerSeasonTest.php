<?php

class CampManagerSeasonTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function _before()
    {
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }
        global $wpdb;
        $wpdb->query('COMMIT');
        // Whole-table assertions below: start from empty tables (other tests COMMIT rows).
        foreach (['mf_ledger_line_items', 'mf_ledger', 'mf_receipts', 'mf_budget_category', 'mf_roster'] as $table) {
            $wpdb->query("DELETE FROM {$wpdb->prefix}$table");
        }
        update_option(CampManagerSeason::OPTION_CURRENT, 2027);
    }

    public function testCurrentSeasonIsTheOneStarted()
    {
        $this->assertSame(2027, CampManagerSeason::current());
        // Outside wp-admin (and for anyone not browsing an archive) selected() is the current season.
        $this->assertSame(2027, CampManagerSeason::selected());
    }

    public function testAvailableListsEverySeasonWithData()
    {
        $this->tester->haveInDatabase('mf_ledger', ['amount' => 1, 'note' => 'old', 'date' => '2025-01-01', 'season' => 2025]);
        $this->assertSame([2027, 2025], CampManagerSeason::available());
    }

    public function testRosterIsScopedToTheSeason()
    {
        $roster = new CampManagerRoster();
        foreach ([2025, 2025, 2027] as $i => $season) {
            $this->tester->haveInDatabase('mf_roster', [
                'wpid' => 0, 'fname' => "Camper$i", 'lname' => 'Test', 'status' => 'Confirmed', 'season' => $season,
            ]);
        }

        $this->assertEquals(1, $roster->countRosterMembers(), 'Only the current season is counted');
        $this->assertCount(1, $roster->getRosterMembers());
        $this->assertEquals(1, $roster->countConfirmedRosterMembers());
    }

    public function testNewMemberDefaultsToTheViewedSeason()
    {
        $roster = new CampManagerRoster();
        $id = $roster->updateMember(['fname' => 'New', 'lname' => 'Camper', 'playaname' => '', 'email' => '']);
        $this->assertEquals(2027, $roster->getMemberById($id)->season);

        // An explicit season wins, and an update without one leaves the season alone.
        $roster->updateMember(['id' => $id, 'season' => 2029, 'fname' => 'New', 'lname' => 'Camper', 'playaname' => '', 'email' => '']);
        $this->assertEquals(2029, $roster->getMemberById($id)->season);
        $roster->updateMember(['id' => $id, 'fname' => 'New', 'lname' => 'Camper', 'playaname' => '', 'email' => '']);
        $this->assertEquals(2029, $roster->getMemberById($id)->season);
    }

    public function testLedgerTotalsAreScopedAndMoneyCarriesOver()
    {
        $ledger = new CampManagerLedger(new CampManagerReceipts(new CampManagerCore(), new CampManagerChatGPT(new CampManagerCore())));

        $this->tester->haveInDatabase('mf_ledger', ['amount' => 1000, 'note' => '2025 in', 'date' => '2025-03-01', 'season' => 2025]);
        $this->tester->haveInDatabase('mf_ledger', ['amount' => -400, 'note' => '2025 out', 'date' => '2025-04-01', 'season' => 2025]);
        $this->tester->haveInDatabase('mf_ledger', ['amount' => 50, 'note' => '2027 in', 'date' => '2027-03-01', 'season' => 2027]);

        $this->assertEquals(50, $ledger->totalMoneyIn(), 'Only the current season');
        $this->assertEquals(0, $ledger->totalMoneyOut());
        // 2027 starts with what 2025 left over: opening balance + 1000 - 400.
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE + 600, $ledger->startingBalance());
        $this->assertEquals(CampManagerLedger::OPENING_BALANCE, $ledger->startingBalance(2025));
    }

    public function testNewLedgerEntriesAndReceiptsAreFiledUnderTheSeason()
    {
        global $wpdb;
        $ledger = new CampManagerLedger(new CampManagerReceipts(new CampManagerCore(), new CampManagerChatGPT(new CampManagerCore())));
        $ledger_id = $ledger->saveLedger(['amount' => 5, 'note' => 'n', 'date' => '2027-05-05', 'link' => '']);
        $this->assertEquals(2027, $wpdb->get_var("SELECT season FROM {$wpdb->prefix}mf_ledger WHERE id = " . (int) $ledger_id));

        $receipts = new CampManagerReceipts(new CampManagerCore(), new CampManagerChatGPT(new CampManagerCore()));
        $receipt_id = $receipts->upsert_receipt(0, null, 'Store', '2027-05-05', 0, 1.0, 0.0, 0.0, 1.0, [], '{}');
        $this->assertEquals(2027, $wpdb->get_var("SELECT season FROM {$wpdb->prefix}mf_receipts WHERE id = " . (int) $receipt_id));
        $this->assertCount(1, $receipts->get_receipts());
        $this->assertCount(0, $receipts->get_receipts(2025));
    }

    public function testBudgetCategoriesAreScopedToTheSeason()
    {
        $budgets = new CampManagerBudgets();
        $core = new CampManagerCore();
        $this->tester->haveInDatabase('mf_budget_category', ['name' => 'Old', 'season' => 2025]);

        $this->assertSame([], $core->getItemCategories(), 'A new season starts without categories');
        $this->assertSame(2025, $budgets->previousSeasonWithCategories());

        $budgets->upsertBudgetCategory('Kitchen');
        $names = array_column($core->getItemCategories(), 'name');
        $this->assertSame(['Kitchen'], $names);
    }

    public function testUpgradeFilesOldRowsUnderTheLegacySeason()
    {
        global $wpdb;
        $this->tester->haveInDatabase('mf_ledger', ['amount' => 1, 'note' => 'legacy', 'date' => '2025-01-01']);
        $wpdb->query('COMMIT');
        delete_option(CampManagerSeason::OPTION_DB_VERSION);

        CampManagerSeason::upgrade();

        $this->assertEquals(CampManagerSeason::LEGACY_SEASON, $wpdb->get_var("SELECT season FROM {$wpdb->prefix}mf_ledger WHERE note = 'legacy'"));
        $this->assertEquals(CampManagerSeason::DB_VERSION, (int) get_option(CampManagerSeason::OPTION_DB_VERSION));
    }
}
