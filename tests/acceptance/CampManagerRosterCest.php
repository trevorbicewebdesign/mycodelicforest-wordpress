<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class CampManagerRosterCest
{
    protected $userId;
    protected $adminId;
    public function _before(AcceptanceTester $I)
    {
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
        $I->loginAs("testadmin", "password123!test");
    }
    public function ViewRoster(AcceptanceTester $I)
    {
        // Navigate to the roster page (not the add form, to see the table)
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-members");
        $I->see("Roster", "h1"); // Adjust if needed to match page title

        // Assert that the `Add New` button is present
        $I->seeElement("a.page-title-action", ["href" => "https://local.mycodelicforest.org/wp-admin/admin.php?page=camp-manager-add-member"]);

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

        $I->seeNumberOfElements("table.wp-list-table tbody tr", 28); // Only the header row initially
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
        $I->checkOption("#member_low_income");
        $I->checkOption("#member_fully_paid");

        // Submit the form
        $I->click("Add Camp Member");
        $I->wait(1);

        $I->seeInDatabase("wp_mf_roster", [
            "fname" => "John",
            "lname" => "Doe",
            "playaname" => "BurnerJohn",
            'season' => 2025,
            "low_income" => 0,
            "fully_paid" => 0
        ]);
    }

}