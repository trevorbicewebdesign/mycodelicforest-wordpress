<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class ProfileAdminCest
{
    protected $userId;
    public function _before(AcceptanceTester $I)
    {
        $this->adminId = $I->haveUserInDatabase("testadmin", "administrator",[
            "first_name" => "Test",
            "last_name" => "Admin",
            "user_phone" => "123-456-7890",
            "user_pass" => "password123!test",
        ]);
        $this->userId = $I->haveUserInDatabase("testuser", "subscriber",[
            "first_name" => "Test",
            "last_name" => "User",
            "user_phone" => "123-456-7890",
            "user_pass" => "password123!test",
        ]);

        $I->loginAs("testadmin", "password123!test");

    }

    public function profileAdminPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/wp-admin/user-edit.php?user_id=".$this->userId);
        $I->see("Edit User testuser");
        $I->takeFullPageScreenshot("admin-profile-page");

        $I->see("First Name", "label[for='first_name']");
        $I->see("Last Name", "label[for='last_name']");
        $I->see("Email (Required)", "label[for='email']");
        $I->see("Phone Number", "label[for='user_phone']");
        $I->see("Street Address", "label[for='address_1']");
        $I->see("Address Line 2", "label[for='address_2']");
        $I->see("City", "label[for='city']");
        $I->see("State / Province / Region", "label[for='state']");
        $I->see("ZIP / Postal Code", "label[for='zip']");
        $I->see("Country", "label[for='country']");
        $I->see("About Me", "label[for='user_about_me']");
        $I->see("Playa Name", "label[for='playa_name']");

        $I->see("Have you been to Burning Man before?", "label[for='has_attended_burning_man']");
        $I->see("Years Attended", "label[for='years_attended']");

        $I->click("Update User");   
    }
}