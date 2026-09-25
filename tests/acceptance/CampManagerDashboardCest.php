<?php

namespace Tests\Acceptance;

use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;

// Shares the camp-ledger CI shard (it is a financial page). A new group would also need a matrix
// entry in .github/workflows/codeception-test.yml, or no CI job would ever run it.
#[Group('camp-ledger')]
class CampManagerDashboardCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->resetSeedState();
        $I->createTestAdmin();
        $I->signInAs("testadmin", "password123!test");
    }

    /**
     * Two confirmed members (one paid), $350 of dues collected, $1,000 in and $200 out on the
     * ledger, and one receipt still awaiting reimbursement.
     */
    private function seedSeason(AcceptanceTester $I): void
    {
        $season = $I->currentCampManagerSeason();
        $paid = $I->createRosterMember(["season" => $season, "fname" => "Pam", "lname" => "Paid", "fully_paid" => 1, "low_income" => 0, "status" => "Confirmed"])['id'];
        $I->createRosterMember(["season" => $season, "fname" => "Una", "lname" => "Unpaid", "fully_paid" => 0, "low_income" => 0, "status" => "Confirmed"]);

        $in = $I->createLedgerEntry(["season" => $season, "amount" => 1000.00])['id'];
        $I->createLedgerEntry(["season" => $season, "amount" => -200.00]);
        $I->createLedgerLineItem(["ledger_id" => $in, "cmid" => $paid, "amount" => 350.00, "type" => "Camp Dues"]);

        $I->createReceipt(["season" => $season, "total" => 350.00, "reimbursed" => 0]);
    }

    public function DashboardShowsTheSeasonFigures(AcceptanceTester $I)
    {
        $this->seedSeason($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");
        $I->waitForText("Camp Manager Dashboard", 10, "h1");

        $stat = fn(string $key) => $I->grabTextFrom("#dashboard-overview [data-stat='$key'] .cm-stat__value");
        $I->assertSame('$350.00', $stat("dues"));
        $I->assertSame("2", $stat("members"));
        $I->assertSame('$350.00', $stat("receipts"));
        $I->see('of $700.00 expected', "#dashboard-overview [data-stat='dues']");
        $I->see("1 fully paid", "#dashboard-overview [data-stat='members']");

        $I->see('$350.00 in receipts awaiting reimbursement.', "#dashboard-overview [data-callout='receipts']");

        // Money in $1,000, out $200, receipts owed $350: $450 left once receipts are paid.
        $I->see("Total revenue", "#dashboard-financial");
        $I->see('$1,000.00', "#dashboard-financial tr.cm-total");
        $I->see('$800.00', "#dashboard-financial tr.cm-total");
        $I->see('$450.00', "#dashboard-ledger tr.cm-total");

        $I->see("2 members · 1 paid · $350.00 dues remaining", "#dashboard-membership");
        $I->see('$700.00', "#dashboard-membership tr[data-dues='total']");
        $I->see('Collected revenue: $1,000.00', "#dashboard-membership .cm-dues-summary");
    }

    public function LinksGoToTheRelatedPages(AcceptanceTester $I)
    {
        $this->seedSeason($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");

        $I->click("View roster", "#dashboard-membership");
        $I->waitForText("Season overview", 10, "#roster-overview");
        $I->seeInCurrentUrl("page=camp-manager-members");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");
        $I->click("View ledger", "#dashboard-ledger");
        $I->seeInCurrentUrl("page=camp-manager-ledger");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");
        $I->click("View receipts", "#dashboard-overview [data-callout='receipts']");
        $I->seeInCurrentUrl("page=camp-manager-actuals");
    }

    public function NoReceiptsCalloutWhenNothingIsOwed(AcceptanceTester $I)
    {
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");
        $I->waitForText("Season overview", 10, "#dashboard-overview");
        $I->dontSeeElement("[data-callout='receipts']");
        $I->see('$0.00', "#dashboard-overview [data-stat='receipts']");
    }

    public function BoxesCollapseAndStayCollapsed(AcceptanceTester $I)
    {
        $this->seedSeason($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");
        $I->waitForElementVisible("#dashboard-financial .cm-summary", 10);

        $I->click("#dashboard-financial .handlediv");
        $I->waitForElement("div#dashboard-financial.closed", 5);
        $I->dontSeeElement("#dashboard-financial .cm-summary");
        // The other boxes are unaffected.
        $I->seeElement("#dashboard-ledger .cm-summary");

        // The closed state is saved for this user (core saves it with an ajax call).
        $I->wait(1);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");
        $I->waitForElement("div#dashboard-financial.closed", 10);
        $I->dontSeeElement("#dashboard-financial .cm-summary");

        $I->click("#dashboard-financial .handlediv");
        $I->waitForElement("div#dashboard-financial:not(.closed)", 5);
        $I->seeElement("#dashboard-financial .cm-summary");
    }

    public function SeasonSettingsStartsANewSeason(AcceptanceTester $I)
    {
        $season = $I->currentCampManagerSeason();
        $I->createRosterMember(["season" => $season, "fname" => "Pam", "lname" => "Paid", "status" => "Confirmed"]);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");
        $I->waitForText("Current", 10, ".camp-manager-season-status");

        // Hidden until the button is used.
        $I->dontSeeElement("#cm-season-settings");
        $I->click("#cm-season-settings-toggle");
        $I->waitForElementVisible("#cm-season-settings", 5);
        $I->see("Start a new season", "#cm-season-settings");

        $next = $season + 1;
        $I->fillField("#camp-manager-new-season", (string) $next);
        $I->click("Start season", "#cm-season-settings");
        $I->acceptPopup(); // "Make this the current season?"
        $I->waitForText("Camp Manager Dashboard", 10, "h1");

        $I->seeOptionInDatabase(["option_name" => "camp_manager_season", "option_value" => (string) $next]);
        $I->see("Current", ".camp-manager-season-status");
        $I->see((string) $next, "#camp-manager-season");
    }

    public function ArchivedSeasonDashboardShowsTheNoticeAndThatSeasonsFigures(AcceptanceTester $I)
    {
        $I->haveOptionInDatabase("camp_manager_season", 2027);
        $I->createRosterMember(["season" => 2027, "fname" => "Cur", "lname" => "Rent", "status" => "Confirmed"]);
        $I->createRosterMember(["season" => 2025, "fname" => "Arc", "lname" => "Hived", "status" => "Confirmed"]);
        $I->createRosterMember(["season" => 2025, "fname" => "Bea", "lname" => "Hived", "status" => "Confirmed"]);

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager");
        $I->assertSame("1", $I->grabTextFrom("#dashboard-overview [data-stat='members'] .cm-stat__value"));

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager&camp_manager_switch_season=2025");
        $I->waitForText("Archived", 10, ".camp-manager-season-status");
        $I->seeElement(".camp-manager-archived-notice");
        $I->assertSame("2", $I->grabTextFrom("#dashboard-overview [data-stat='members'] .cm-stat__value"));

        $I->click("View current season.", ".camp-manager-archived-notice");
        $I->waitForText("Current", 10, ".camp-manager-season-status");
        $I->assertSame("1", $I->grabTextFrom("#dashboard-overview [data-stat='members'] .cm-stat__value"));
    }
}
