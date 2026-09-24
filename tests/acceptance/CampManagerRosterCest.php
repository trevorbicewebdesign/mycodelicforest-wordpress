<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class CampManagerRosterCest
{
    protected $userId;
    protected $adminId;
    public function _before(AcceptanceTester $I)
    {
         // Earlier tests leave their own "testadmin"/"testuser" rows behind (WPDb cleanup is off);
         // duplicates make wp_signon() log in the oldest one, so start clean.
         foreach (["testadmin", "testuser"] as $login) {
             // dontHaveUserInDatabase($login) removes only the first match; remove every row.
             foreach ($I->grabColumnFromDatabase($I->grabPrefixedTableNameFor("users"), "ID", ["user_login" => $login]) as $staleId) {
                 $I->dontHaveUserInDatabase((int) $staleId);
             }
         }
         $this->adminId = $I->haveUserInDatabase("testadmin", "administrator",[
            "first_name" => "Test",
            "last_name" => "Admin",
            "user_pass" => "password123!test",
            "meta_input" => [
                "first_name" => "Test",
                "last_name" => "Admin",
                "user_phone" => "(123) 456-7890",
                "address_1" => "123 Main St",
                "city" => "Anytown",
                "state" => "CA",
                "zip" => "12345",
                "country" => "United States",
                "user_about_me" => "This is a test.",
                "playa_name" => "TestBurner",
                "has_attended_burning_man" => "No",
                 // "years_attended" => '["2024"]',
            ]
        ]);
        $this->userId = $I->haveUserInDatabase("testuser", "subscriber",[
            "first_name" => "Test",
            "last_name" => "User",
            "user_pass" => "password123!test",
            "meta_input" => [
                "first_name" => "Test",
                "last_name" => "User",
                "user_phone" => "(123) 456-7890",
                "address_1" => "123 Main St",
                "city" => "Anytown",
                "state" => "CA",
                "zip" => "12345",
                "country" => "United States",
                "user_about_me" => "This is a test.",
                "playa_name" => "TestBurner",
                "has_attended_burning_man" => "No",
                // "years_attended" => '["2024"]',
            ]
        ]);
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
            $I->haveInDatabase("wp_mf_roster", [
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
        $I->see("ID", "th#id");
        $I->see("First Name", "th#fname");
        $I->see("Last Name", "th#lname");
        $I->see("Playa Name", "th#playaname");
        $I->see("Camp Dues", "th#camp_dues");
        $I->see("Low Income", "th#low_income");
        $I->see("Fully Paid", "th#fully_paid");
        $I->see("WordPress ID", "th#wpid");
        
        // Optional: Check for the select-all checkbox label
        $I->see("Select All", "label[for='cb-select-all-1']");

        $I->seeNumberOfElements("table.wp-list-table tbody tr", 2);
        $I->see("Alice", "table.wp-list-table tbody");
        $I->see("Bob", "table.wp-list-table tbody");
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
        $member_id = $I->haveInDatabase("wp_mf_roster", [
            "wpid" => $this->userId,
            "low_income" => 0,
            "fully_paid" => 0,
            "season" => 2025,
            "fname" => "John",
            "lname" => "Doe",
            "playaname" => "BurnerJohn",
            "email" => "john.doe@example.com"
        ]);
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
        $member_id = $I->haveInDatabase("wp_mf_roster", [
            "wpid" => 0,
            "low_income" => 0,
            "fully_paid" => 0,
            "season" => 2025,
            "fname" => "Wanda",
            "lname" => "Ward",
            "playaname" => "Wander",
            "email" => "wanda.ward@example.com"
        ]);
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
        $I->assertContains("Test User (testuser - " . $I->grabFromDatabase("wp_users", "user_email", ["ID" => $this->userId]) . ")", $names);

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