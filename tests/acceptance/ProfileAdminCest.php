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
                "has_attended_burning_man" => "Yes",
                "years_attended" => '["2024"]',
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
                "has_attended_burning_man" => "Yes",
                "years_attended" => '["2024"]',
            ]
        ]);

        $I->loginAs("testadmin", "password123!test");

    }

    public function profileAdminProfilePageIsVisible(AcceptanceTester $I)
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

        $I->seeInField("#first_name", "Test");
        $I->seeInField("#last_name", "User");
        $I->seeInField("#user_phone", "(123) 456-7890");
        $I->seeInField("#address_1", "123 Main St");
        $I->seeInField("#city", "Anytown");
        $I->seeInField("#state", "CA");
        $I->seeInField("#zip", "12345");
        $I->seeOptionIsSelected("#country", "United States");
        $I->seeInField("#user_about_me", "This is a test.");
        $I->seeInField("#playa_name", "TestBurner");
        
        $I->click("Update User");   

        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "first_name","meta_value" => "Test"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "last_name","meta_value" => "Admin"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "user_phone","meta_value" => "(123) 456-7890"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "address_1","meta_value" => "123 Main St"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "city","meta_value" => "Anytown"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "state","meta_value" => "CA"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "zip","meta_value" => "12345"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "country","meta_value" => "United States"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "user_about_me","meta_value" => "This is a test."]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "playa_name","meta_value" => "TestBurner"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "has_attended_burning_man","meta_value" => "Yes"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "years_attended","meta_value" => '["2024"]']);

    }

    public function profileAdminPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/wp-admin/profile.php");
        $I->see("Profile", "h1");
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

        $I->seeInField("#first_name", "Test");
        $I->seeInField("#last_name", "User");
        $I->seeInField("#user_phone", "(123) 456-7890");
        $I->seeInField("#address_1", "123 Main St");
        $I->seeInField("#city", "Anytown");
        $I->seeInField("#state", "CA");
        $I->seeInField("#zip", "12345");
        $I->seeOptionIsSelected("#country", "United States");
        $I->seeInField("#user_about_me", "This is a test.");
        $I->seeInField("#playa_name", "TestBurner");
        
        $I->click("Update User");   

        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "first_name","meta_value" => "Test"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "last_name","meta_value" => "User"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "user_phone","meta_value" => "(123) 456-7890"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "address_1","meta_value" => "123 Main St"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "city","meta_value" => "Anytown"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "state","meta_value" => "CA"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "zip","meta_value" => "12345"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "country","meta_value" => "United States"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "user_about_me","meta_value" => "This is a test."]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "playa_name","meta_value" => "TestBurner"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "has_attended_burning_man","meta_value" => "Yes"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "years_attended","meta_value" => '["2024"]']);

    }
}