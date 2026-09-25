<?php 

namespace Tests\Acceptance;

use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
#[Group('camp-roster')]
class CampManagerRosterCest
{
    protected $userId;
    protected $adminId;
    public function _before(AcceptanceTester $I)
    {
        $I->resetSeedState();
        $this->adminId = $I->createTestAdmin()['id'];
        $this->userId = $I->createTestUser()['id'];
        $I->signInAs("testadmin", "password123!test");
    }
    public function ViewRoster(AcceptanceTester $I)
    {
        // Seed two members so the list has known content (the CI seed DB has an empty
        // roster). getRosterMembers() filters `WHERE season = <viewed season>`, so a fixed
        // year would only work by coincidence - use whatever season this environment
        // actually resolves to.
        $season = $I->currentCampManagerSeason();
        foreach ([['Alice', 'Anders', 'Ally'], ['Bob', 'Baker', 'Bobcat']] as $m) {
            $I->createRosterMember([
                "wpid" => 0, "season" => $season, "fname" => $m[0], "lname" => $m[1], "playaname" => $m[2],
                "email" => strtolower($m[0]) . "@example.com", "low_income" => 0, "fully_paid" => 1, "status" => "Confirmed",
            ]);
        }
        // Navigate to the roster page (not the add form, to see the table)
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members");
        $I->waitForText("Roster", 10, "h1"); // Adjust if needed to match page title

        // Assert that the `Add New` button is present
        $I->seeElement("a.page-title-action[href$='/wp-admin/admin.php?page=camp-manager-add-member']");

        // Assert that each table header is present
        $I->see("Member", "th#member");
        $I->see("Playa name", "th#playaname");
        $I->see("Camp dues", "th#camp_dues");
        $I->see("Dues category", "th#dues_category");
        $I->see("Payment", "th#payment");
        $I->see("Status", "th#status");
        $I->see("Camp roles", "th#roles");
        // The old split name / flag / id columns are gone.
        foreach (["id", "fname", "lname", "low_income", "fully_paid", "wpid"] as $removed) {
            $I->dontSeeElement("th#$removed");
        }

        // Optional: Check for the select-all checkbox label
        $I->see("Select All", "label[for='cb-select-all-1']");

        $I->seeNumberOfElements("table.wp-list-table tbody tr", 2);
        $I->see("Alice Anders", "table.wp-list-table tbody .column-member");
        $I->see("Ally", "table.wp-list-table tbody");
        $I->see("Bob Baker", "table.wp-list-table tbody .column-member");
        // Both were created fully paid and confirmed.
        $I->see("Paid", "table.wp-list-table tbody .roster-payment--paid");
        $I->see("Confirmed", "table.wp-list-table tbody .column-status");
        $I->see("Standard", "table.wp-list-table tbody .column-dues_category");
    }

    /** Four members: three confirmed (one low income, both of those paid) and one dropped. */
    private function seedSeasonMembers(AcceptanceTester $I): array
    {
        $season = $I->currentCampManagerSeason();
        $paid = $I->createRosterMember(["season" => $season, "fname" => "Pam", "lname" => "Paid", "playaname" => "Payer", "fully_paid" => 1, "low_income" => 0, "status" => "Confirmed"])['id'];
        $low = $I->createRosterMember(["season" => $season, "fname" => "Lou", "lname" => "Lowincome", "playaname" => "", "fully_paid" => 1, "low_income" => 1, "status" => "Confirmed"])['id'];
        $unpaid = $I->createRosterMember(["season" => $season, "fname" => "Una", "lname" => "Unpaid", "playaname" => "Owes", "fully_paid" => 0, "low_income" => 0, "status" => "Confirmed"])['id'];
        $dropped = $I->createRosterMember(["season" => $season, "fname" => "Dan", "lname" => "Dropped", "playaname" => "Gone", "fully_paid" => 0, "low_income" => 0, "status" => "Dropped"])['id'];

        $ledger = $I->createLedgerEntry(["season" => $season])['id'];
        $I->createLedgerLineItem(["ledger_id" => $ledger, "cmid" => $paid, "amount" => 350.00, "type" => "Camp Dues"]);
        $I->createLedgerLineItem(["ledger_id" => $ledger, "cmid" => $low, "amount" => 250.00, "type" => "Camp Dues"]);
        return compact('paid', 'low', 'unpaid', 'dropped');
    }

    public function SeasonOverviewSummarisesTheSeason(AcceptanceTester $I)
    {
        $this->seedSeasonMembers($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members");
        $I->waitForText("Season overview", 10, "#roster-overview");

        $stat = fn(string $key) => $I->grabTextFrom("#roster-overview [data-stat='$key'] .roster-overview__value");
        $I->assertSame("4", $stat("total"));
        $I->assertSame("3", $stat("confirmed"));
        $I->assertSame("1", $stat("unpaid"));
        $I->assertSame('$600.00', $stat("collected"));
        // Three standard (incl. the dropped member) at $350 plus one low income at $250.
        $I->assertSame('$1,300.00', $stat("expected"));
        $I->see("Low-income members: 1", "#roster-overview .roster-overview__footer");
        $I->see("Low-income dues paid: 1", "#roster-overview .roster-overview__footer");
    }

    public function SeasonOverviewCollapses(AcceptanceTester $I)
    {
        $this->seedSeasonMembers($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members");
        $I->waitForElementVisible("#roster-overview .roster-overview__stats", 10);

        $I->click("#roster-overview .handlediv");
        $I->waitForElement("div#roster-overview.closed", 5);
        $I->dontSeeElement("#roster-overview .roster-overview__stats");

        $I->click("#roster-overview .handlediv");
        $I->waitForElement("div#roster-overview:not(.closed)", 5);
        $I->seeElement("#roster-overview .roster-overview__stats");
    }

    public function StatusViewsFilterTheRoster(AcceptanceTester $I)
    {
        $this->seedSeasonMembers($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members");

        $I->see("All (4)", ".subsubsub");
        $I->see("Confirmed (3)", ".subsubsub");
        $I->see("Dropped (1)", ".subsubsub");
        $I->seeNumberOfElements("table.wp-list-table tbody tr.roster-row", 4);
        // Dropped members are listed last and muted.
        $I->seeElement("table.wp-list-table tbody tr.roster-row:last-child.is-dropped");

        $I->click("Dropped", ".subsubsub");
        $I->waitForText("Dan Dropped", 10, "table.wp-list-table");
        $I->seeNumberOfElements("table.wp-list-table tbody tr.roster-row", 1);
        $I->seeElement(".subsubsub a.current[href*='member_status=dropped']");

        $I->click("Confirmed", ".subsubsub");
        $I->waitForText("Pam Paid", 10, "table.wp-list-table");
        $I->seeNumberOfElements("table.wp-list-table tbody tr.roster-row", 3);
        $I->dontSee("Dan Dropped", "table.wp-list-table");
    }

    public function FilterByPaymentAndDuesCategory(AcceptanceTester $I)
    {
        $this->seedSeasonMembers($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members");

        $I->selectOption("#roster-filter-payment_status", "Unpaid");
        $I->click("#roster-filter-submit");
        $I->waitForText("Una Unpaid", 10, "table.wp-list-table");
        $I->seeInCurrentUrl("payment_status=unpaid");
        $I->seeNumberOfElements("table.wp-list-table tbody tr.roster-row", 2); // Una and the dropped Dan
        $I->see("Unpaid", "table.wp-list-table tbody .roster-payment--unpaid");
        $I->dontSee("Pam Paid", "table.wp-list-table");

        $I->selectOption("#roster-filter-payment_status", "Payment status");
        $I->selectOption("#roster-filter-dues_category", "Low income");
        $I->click("#roster-filter-submit");
        $I->waitForText("Lou Lowincome", 10, "table.wp-list-table");
        $I->seeInCurrentUrl("dues_category=low_income");
        $I->seeNumberOfElements("table.wp-list-table tbody tr.roster-row", 1);
        $I->see("Low income", "table.wp-list-table tbody .column-dues_category");
    }

    public function SearchMembers(AcceptanceTester $I)
    {
        $this->seedSeasonMembers($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members");

        $I->fillField("#roster-search-search-input", "Owes");
        $I->click("#search-submit");
        $I->waitForText("Una Unpaid", 10, "table.wp-list-table");
        $I->seeNumberOfElements("table.wp-list-table tbody tr.roster-row", 1);

        $I->fillField("#roster-search-search-input", "zzz-nobody");
        $I->click("#search-submit");
        $I->waitForText("No members found.", 10, "table.wp-list-table");
    }

    public function SortByCampDues(AcceptanceTester $I)
    {
        $this->seedSeasonMembers($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members&orderby=camp_dues&order=desc");

        // Pam paid $350, Lou $250, Una nothing; the dropped member stays last.
        $I->see("Pam Paid", "table.wp-list-table tbody tr.roster-row:nth-child(1) .column-member");
        $I->see("Lou Lowincome", "table.wp-list-table tbody tr.roster-row:nth-child(2) .column-member");
        $I->see("Dan Dropped", "table.wp-list-table tbody tr.roster-row:nth-child(4) .column-member");
        $I->seeElement("th#camp_dues.sorted.desc");
    }

    public function ArchivedSeasonShowsNoticeAndLinksBackToCurrent(AcceptanceTester $I)
    {
        $I->haveOptionInDatabase("camp_manager_season", 2027);
        $I->createRosterMember(["season" => 2027, "fname" => "Cur", "lname" => "Rent", "status" => "Confirmed"]);
        $I->createRosterMember(["season" => 2025, "fname" => "Arc", "lname" => "Hived", "status" => "Confirmed"]);

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members");
        $I->see("Current", ".camp-manager-season-status");
        $I->dontSeeElement(".camp-manager-archived-notice");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members&camp_manager_switch_season=2025");
        $I->waitForText("Arc Hived", 10, "table.wp-list-table");
        $I->dontSee("Cur Rent", "table.wp-list-table");
        $I->see("Archived", ".camp-manager-season-status");
        $I->see("You are viewing the archived 2025 season. New entries will be saved to 2025. The current season is 2027.", ".camp-manager-archived-notice");

        $I->click("View current season.", ".camp-manager-archived-notice");
        $I->waitForText("Cur Rent", 10, "table.wp-list-table");
        $I->dontSee("Arc Hived", "table.wp-list-table");
        $I->dontSeeElement(".camp-manager-archived-notice");
    }

    public function AddMember(AcceptanceTester $I)
    {
        // Navigate to the add member page
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-member");
        $I->waitForText("Add New Member", 10, "h1");

        // The season field is prefilled from CampManagerSeason::selected(), which depends on
        // ambient roster data (it falls back to the current year when the table is empty) -
        // read whatever it actually shows rather than assuming a fixed year.
        $season = (int) $I->grabValueFrom("#season");

        // Fill in the form fields
        $I->fillField("#member_fname", "John");
        $I->fillField("#member_lname", "Doe");
        $I->fillField("#member_playaname", "BurnerJohn");
        $I->fillField("#member_email", "john.doe@example.com");
        $I->checkOption("#member_low_income");
        $I->checkOption("#member_fully_paid");

        // Submit the form
        $I->click("Save Member");
        // Saving a new member lands on that member's edit form.
        $I->waitForText("Edit Member", 10, "h1");

        $I->seeInDatabase("wp_mf_roster", [
            "fname" => "John",
            "lname" => "Doe",
            "playaname" => "BurnerJohn",
            'season' => $season,
            "low_income" => 1,   // both boxes were ticked above
            "fully_paid" => 1,
            "wpid" => 0,         // no WordPress user selected
            "email" => "john.doe@example.com"
        ]);
    }

    public function UpdateMember(AcceptanceTester $I)
    {
        $member_id = $I->createRosterMember([
            "wpid" => $this->userId,
            "low_income" => 0,
            "fully_paid" => 0,
            "season" => 2025,
            "fname" => "John",
            "lname" => "Doe",
            "playaname" => "BurnerJohn",
            "email" => "john.doe@example.com"
        ])['id'];
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-member&id=$member_id");
        $I->waitForText("Edit Member", 10, "h1");

        // The wpid select should already reflect the member's stored WordPress user.
        $I->assertEquals((string) $this->userId, $I->grabValueFrom("#wpid"));

        // Fill in the form fields
        $I->fillField("#member_fname", "Same");
        $I->fillField("#member_lname", "Smith");
        $I->fillField("#member_playaname", "SamSmith");
        $I->fillField("#member_email", "sam.smith@example.com");

        // Submit the form
        $I->click("Save Member");
        $I->wait(1);

        $I->seeInDatabase("wp_mf_roster", [
            "fname" => "Same",
            "lname" => "Smith",
            "playaname" => "SamSmith",
            "email" => "sam.smith@example.com",
            // Editing unrelated fields must not wipe out the previously selected WordPress user.
            "wpid" => $this->userId,
        ]);
    }

    public function UpdateMemberWpid(AcceptanceTester $I)
    {
        // A user without a complete profile (no address, phone, ...) is not offered.
        $I->haveUserInDatabase("halfdone", "subscriber", [
            "display_name" => "Half Done",
            "meta_input" => ["first_name" => "Half", "last_name" => "Done", "playa_name" => "Halfway"],
        ]);
        $member_id = $I->createRosterMember([
            "wpid" => 0,
            "low_income" => 0,
            "fully_paid" => 0,
            "season" => 2025,
            "fname" => "Wanda",
            "lname" => "Ward",
            "playaname" => "Wander",
            "email" => "wanda.ward@example.com"
        ])['id'];
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-member&id=$member_id");
        $I->waitForText("Edit Member", 10, "h1");

        // No WordPress user selected yet.
        $I->assertEquals("", $I->grabValueFrom("#wpid"));

        // The user list is a searchable select2 box, sorted by display name.
        $I->seeElement("#select2-wpid-container");
        // (select2 hides the native select, and WebDriver reads hidden elements' text as empty.)
        $names = $I->executeJS('return jQuery("#wpid option").map(function () { return jQuery(this).text().trim(); }).get();');
        array_shift($names); // the "Select a WordPress user" placeholder
        $sorted = $names;
        usort($sorted, 'strcasecmp');
        $I->assertEquals($sorted, $names, "WordPress users should be listed alphabetically");
        // Options read "Display name (login - e-mail)"; the fixture user's display name is its login.
        $I->assertNotEmpty(preg_grep('/\(testuser - /', $names), "the complete test user should be offered");
        $I->assertEmpty(preg_grep('/halfdone/', $names), "users with an incomplete profile should not be offered");

        // The picker is a select2 box (the native select is hidden, so selectOption() can't
        // click its options) and choosing a user opens a confirm() offering to fill the form
        // from their profile. Pick through the select's value with the prompt answered.
        $pick = 'window.confirm = function () { return %s; }; jQuery("#wpid").val(arguments[0]).trigger("change");';

        // Declining keeps what the roster already has, and still links the user.
        $I->executeJS(sprintf($pick, 'false'), [(string) $this->userId]);
        $I->assertEquals((string) $this->userId, $I->grabValueFrom("#wpid"));
        $I->seeInField("#member_fname", "Wanda");
        $I->seeInField("#member_playaname", "Wander");

        // Accepting fills the name, playa name and e-mail from the WordPress profile.
        $I->executeJS('jQuery("#wpid").val("").trigger("change");');
        $I->executeJS(sprintf($pick, 'true'), [(string) $this->userId]);
        $I->seeInField("#member_fname", "Test");
        $I->seeInField("#member_lname", "User");
        $I->seeInField("#member_playaname", "TestBurner");

        $I->click("Save Member");
        $I->wait(1);

        $I->seeInDatabase("wp_mf_roster", [
            "id" => $member_id,
            "wpid" => $this->userId,
            "fname" => "Test",
            "playaname" => "TestBurner",
        ]);

        // Reload and confirm the selection survived the round trip.
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-member&id=$member_id");
        $I->waitForText("Edit Member", 10, "h1");
        $I->assertEquals((string) $this->userId, $I->grabValueFrom("#wpid"));
    }

}