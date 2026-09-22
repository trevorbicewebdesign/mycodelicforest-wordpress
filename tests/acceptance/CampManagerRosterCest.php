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
        // Seed two members so the list has known content (the CI seed DB has an empty roster).
        foreach ([['Alice', 'Anders', 'Ally'], ['Bob', 'Baker', 'Bobcat']] as $m) {
            $I->haveInDatabase("wp_mf_roster", [
                "wpid" => 0, "season" => 2025, "fname" => $m[0], "lname" => $m[1], "playaname" => $m[2],
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
            'season' => 2025,
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
            "email" => "sam.smith@example.com"
        ]);
    }

}