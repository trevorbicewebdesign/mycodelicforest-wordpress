<?php 

namespace Tests\Acceptance;

use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
#[Group('camp-ledger')]
class CampManagerLedgerCest
{
    protected $userId;
    protected $adminId;
    protected $season;
    protected $ledger;
    public function _before(AcceptanceTester $I)
    {
        $I->resetSeedState();
        $this->adminId = $I->createTestAdmin()['id'];
        $this->userId = $I->createTestUser()['id'];

        // getLedger()/totals filter `WHERE season = <viewed season>`; a season-less fixture
        // row would be invisible everywhere the app actually renders ledger data.
        $this->season = $I->currentCampManagerSeason();
        $this->ledger = $I->createLedgerEntry([
            "note" => "Test Ledger Item",
            "amount" => 200.00,
        ]);
        $I->wait(1);

        $I->signInAs("testadmin", "password123!test");
    }
    public function ViewLedgerItems(AcceptanceTester $I)
    {
        $I->createLedgerLineItem(["ledger_id" => $this->ledger['id'], "amount" => 200.00, "type" => "Camp Dues"]);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-ledger");
        $I->waitForText("Ledger", 10, "h1");

        $I->seeElement("a.page-title-action[href$='/wp-admin/admin.php?page=camp-manager-add-ledger']");

        $I->see("Date", "th#date");
        $I->see("Entry", "th#note");
        $I->see("Amount", "th#amount");
        $I->see("Line items", "th#type");
        $I->see("Receipts", "th#receipts");
        $I->see("Link", "th#link");
        // The id column is gone, and the season column only shows when viewing every season.
        $I->dontSeeElement("th#id");
        $I->dontSeeElement("th#season");

        $I->see("Select All", "label[for='cb-select-all-1']");
        $I->dontSee("No ledger entries found.");

        $I->see("Test Ledger Item", "table.wp-list-table tbody tr:nth-child(1) .column-note .row-title");
        $I->see("$200.00", "table.wp-list-table tbody tr:nth-child(1) td.column-amount .cm-amount--in");
        $I->see(date("M j, Y"), "table.wp-list-table tbody tr:nth-child(1) td.column-date");
        $I->see("Camp Dues", "table.wp-list-table tbody tr:nth-child(1) td.column-type");
        $I->seeElement("table.wp-list-table tbody tr:nth-child(1) td.column-receipts .cm-empty");
        $I->see("View", "table.wp-list-table tbody tr:nth-child(1) td.column-link");
        $I->seeElement("table.wp-list-table tbody tr:nth-child(1) td.column-link a[href='{$this->ledger["link"]}'][target='_blank']");
        $I->dontSeeElement("table.wp-list-table tbody tr:nth-child(1).is-attention");

        // Search, filters and the page size picker are there.
        $I->seeElement("#ledger-search-search-input");
        $I->seeElement("#cm-filter-type");
        $I->seeElement("#cm-filter-receipts");
        $I->seeElement("#cm-filter-month");
        $I->seeOptionIsSelected("#cm-per-page", "50 items per page");
    }

    /** Dues in, fuel out with a receipt, and a payment whose line items do not add up. */
    private function seedLedger(AcceptanceTester $I): array
    {
        $dues = $I->createLedgerEntry(["note" => "Tina camp dues", "amount" => 350.00, "date" => "{$this->season}-06-01 00:00:00"])['id'];
        $fuel = $I->createLedgerEntry(["note" => "Fuel", "amount" => -832.57, "date" => "{$this->season}-07-07 00:00:00", "link" => "https://www.paypal.com/activity/payment/FUEL000000000000"])['id'];
        $short = $I->createLedgerEntry(["note" => "Generator sale", "amount" => 359.79, "date" => "{$this->season}-06-29 00:00:00"])['id'];
        $I->createLedgerLineItem(["ledger_id" => $dues, "amount" => 350.00, "type" => "Camp Dues"]);
        $I->createLedgerLineItem(["ledger_id" => $fuel, "amount" => 832.57, "type" => "Expense", "receipt_id" => 96, "note" => "diesel"]);
        $I->createLedgerLineItem(["ledger_id" => $short, "amount" => 370.88, "type" => "Sold Asset"]);
        return compact('dues', 'fuel', 'short');
    }

    public function OverviewViewsAndFiltersSummariseTheSeason(AcceptanceTester $I)
    {
        $this->seedLedger($I);
        // _before's $200 entry has no line items, so it needs attention too.
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-ledger");
        $I->waitForText("Ledger", 10, "h1");

        $stat = fn(string $key) => $I->grabTextFrom("#ledger-overview [data-stat='$key'] .cm-stat__value");
        // 200 + 350 + 359.79 in, 832.57 out, from the opening balance of 2,037.80.
        $I->assertSame('$2,037.80', $stat("starting"));
        $I->assertSame('$909.79', $stat("in"));
        $I->assertSame('$832.57', $stat("out"));
        $I->assertSame('$77.22', $stat("net"));
        $I->assertSame('$2,115.02', $stat("ending"));
        $I->see('Camp dues $350.00', "#ledger-overview .cm-stat-footer");
        $I->see('Assets sold $370.88', "#ledger-overview .cm-stat-footer");
        $I->see("4 entries", "#ledger-overview .cm-stat-footer");

        $I->see("All (4)", ".subsubsub .all");
        $I->see("Money in (3)", ".subsubsub .in");
        $I->see("Money out (1)", ".subsubsub .out");
        $I->see("Needs attention (2)", ".subsubsub .attention");
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 4);
        // Newest first.
        $I->see("Fuel", "table.wp-list-table tbody tr:nth-child(1) .row-title");
        $I->see("#96", "table.wp-list-table tbody tr:nth-child(1) td.column-receipts a");
        $I->see("Generator sale", "table.wp-list-table tbody tr:nth-child(2) .row-title");
        $I->see("Line items total $370.88", "table.wp-list-table tbody tr:nth-child(2).is-attention .cm-flag");

        $I->click(".subsubsub .attention a");
        $I->waitForElement(".subsubsub .attention a.current", 10);
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 2);
        $I->seeNumberOfElements("table.wp-list-table tbody tr.is-attention", 2);
        $I->see("No line items", "table.wp-list-table tbody .cm-flag");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-ledger");
        $I->waitForText("Ledger", 10, "h1");
        $I->selectOption("#cm-filter-type", "Expense");
        $I->click("#cm-filter-submit");
        $I->waitForElement("#cm-filter-type option[value='Expense'][selected]", 10);
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);
        $I->see("Fuel", "table.wp-list-table tbody .row-title");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-ledger");
        $I->waitForText("Ledger", 10, "h1");
        $I->fillField("#ledger-search-search-input", "diesel");
        $I->click("#search-submit");
        $I->waitForElement("#ledger-search-search-input[value='diesel']", 10);
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);
        $I->see("Fuel", "table.wp-list-table tbody .row-title");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-ledger&flow=out");
        $I->waitForText("Ledger", 10, "h1");
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);
        $I->see('-$832.57', "table.wp-list-table tbody td.column-amount .cm-amount--out");
    }

    public function AddLedger(AcceptanceTester $I)
    {
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-ledger");
        $I->waitForText("Add Ledger Entry", 10, "h1");

        $I->see("Note", "label[for='ledger_note']");
        $I->see("Amount", "label[for='ledger_amount']");
        $I->see("Date", "label[for='ledger_date']");
        $I->see("Link", "label[for='ledger_link']");

        $I->see("ID", "table thead tr th");
        $I->see("Receipt", "table thead tr th");
        $I->see("Note", "table thead tr th");
        $I->see("Amount", "table thead tr th");
        $I->see("Type", "table thead tr th");

        // Fill in the form
        $I->fillField("input[name=\"ledger_note\"]", "Test Ledger Item");
        $I->fillField("input[name=\"ledger_date\"]", date("m/d/Y"));
        $I->fillField("input[name=\"ledger_amount\"]", "-200.00");
        $randomNumber = rand(1000000000000000, 9999999999999999);
        $I->fillField("input[name=\"ledger_link\"]", "https://www.paypal.com/activity/payment/" . $randomNumber);

        $I->fillField("input[name=\"ledger_line_item_note[]\"]", "Test Ledger Line Item Note");
        $I->fillField("input[name=\"ledger_line_item_amount[]\"]", "200.00");
        $I->selectOption("select[name=\"ledger_line_item_type[]\"]", "Expense");

        // Submit the form
        $I->click("Save Ledger");
        // The handler redirects to the entry's edit form; wait for it before reading the DB.
        $I->waitForText("Edit Ledger Entry", 10, "h1");

        // Verify the item was added
        $I->seeInDatabase("wp_mf_ledger", [
            "note" => "Test Ledger Item",
            "amount" => -200.00,
            "link" => "https://www.paypal.com/activity/payment/$randomNumber",
        ]);

        $ledger_id = $I->grabFromDatabase("wp_mf_ledger", "id", [
            "note" => "Test Ledger Item",
            "amount" => -200.00,
            "link" => "https://www.paypal.com/activity/payment/$randomNumber",
        ]);

        $I->seeInDatabase("wp_mf_ledger_line_items", [
            "ledger_id" => $ledger_id,
            "amount" => 200.0,
            "note" => "Test Ledger Line Item Note",
            "type" => "Expense",
        ]);
    }

    public function DeleteLedger(AcceptanceTester $I)
    {

        $ledger_id = $I->createLedgerEntry([
            "note" => "Test Ledger Item",
            "amount" => 200.00,
        ])['id'];

        $ledger_line_item_id = $I->createLedgerLineItem([
            "ledger_id" => $ledger_id,
            "receipt_id" => 0,
            "name" => "",
            "amount" => 200.00,
            "cmid" => $this->userId,
            "note" => "Test Ledger Line Item Note",
            "type" => "Expense",
        ])['id'];
        
        // Navigate to the ledger page
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-ledger");
        $I->waitForText("Ledger", 10, "h1");

        // Delete is a bulk action, so we need to select an item first
        $I->checkOption("input[name=\"ledger[]\"][value=\"$ledger_id\"]");
        $I->click("select[name=\"action\"]");
        $I->selectOption("select[name=\"action\"]", "Delete");
        $I->click("Apply");
        $I->wait("1");
        $I->see("Ledger", "h1");

        $I->dontSeeInDatabase("wp_mf_ledger", [
            "id" => $ledger_id,
        ]);
        $I->dontSeeInDatabase("wp_mf_ledger_line_items", [
            "id" => $ledger_line_item_id,
        ]);
    }
}