<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class ProfileCest
{
    protected $userId;
    protected $profileIncompleteId;
    public function _before(AcceptanceTester $I)
    {
        $this->userId = $I->haveUserInDatabase("testuser", "subscriber",[
            "first_name" => "Test",
            "last_name" => "User",
            "user_phone" => "123-456-7890",
            "user_pass" => "password123!test",
        ]);
    }

    public function profilePageIsVisible(AcceptanceTester $I)
    {
        $I->loginAs("testuser", "password123!test");
        $I->amOnPage("/profile/");
        $I->see("Profile");
        $I->takeFullPageScreenshot("profile-page");

        $I->see("Name (Required)", "legend.gfield_label");
        $I->see("First", "label[for='input_6_16_3']");
        $I->see("Last", "label[for='input_6_16_6']");
        $I->see("Email (Required)", "label[for='input_6_18']");
        $I->see("Phone Number", "label[for='input_6_5']");
        $I->see("Street Address", "label[for='input_6_9_1']");
        $I->see("Address Line 2", "label[for='input_6_9_2']");
        $I->see("City", "label[for='input_6_9_3']");
        $I->see("State / Province / Region", "label[for='input_6_9_4']");
        $I->see("ZIP / Postal Code", "label[for='input_6_9_5']");
        $I->see("Country", "label[for='input_6_9_6']");
        $I->see("About Me", "label[for='input_6_13']");
        $I->see("Playa Name", "label[for='input_6_6']");
        $I->see("Burner Profile", "h3.gsection_title");
        $I->see("Have you been to Burning Man before?", "legend.gfield_label");
        $I->see("Yes", "label[for='choice_6_19_0']");
        $I->see("No", "label[for='choice_6_19_1']");

        $I->click("Save");
        $I->wait(2);
        $I->see("There was a problem with your submission, Please review the fields below,");
        $I->takeFullPageScreenshot("profile-page-errors");

        $I->fillField("#input_6_16_3", "Test");
        $I->fillField("#input_6_16_6", "User");
        $I->fillField("#input_6_5", "555-555-5555");
        $I->fillField("#input_6_9_1", "123 Main St");
        $I->fillField("#input_6_9_3", "Anytown");
        $I->fillField("#input_6_9_4", "CA");
        $I->fillField("#input_6_9_5", "12345");
        $I->selectOption("#input_6_9_6", ["value" => "United States"]);
        $I->fillField("#input_6_13", "This is a test.");
        $I->fillField("#input_6_6", "TestBurner");
        $I->click("#choice_6_19_1");
        $I->click("Save");
        $I->wait(2);

        $I->takeFullPageScreenshot("profile-page-saved");

        $I->seeInField("#input_6_16_3", "Test");
        $I->seeInField("#input_6_16_6", "User");
        $I->seeInFIeld("#input_6_18", "testuser@example.com");
        $I->seeInField("input[name=input_5]", "(555) 555-5555");
        $I->seeInField("#input_6_9_1", "123 Main St");
        $I->seeInField("#input_6_9_3", "Anytown");
        $I->seeInField("#input_6_9_4", "CA");
        $I->seeInField("#input_6_9_5", "12345");
        $I->seeOptionIsSelected("#input_6_9_6", "United States");
        $I->seeInField("#input_6_13", "This is a test.");
        $I->seeInField("#input_6_6", "TestBurner");
        $I->seeCheckboxIsChecked("#choice_6_19_1");

        $I->see("Profile updated successfully!");

        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "first_name","meta_value" => "Test"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "last_name","meta_value" => "User"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "user_phone","meta_value" => "(555) 555-5555"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "address_1","meta_value" => "123 Main St"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "city","meta_value" => "Anytown"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "state","meta_value" => "CA"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "zip","meta_value" => "12345"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "country","meta_value" => "United States"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "user_about_me","meta_value" => "This is a test."]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "playa_name","meta_value" => "TestBurner"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "has_attended_burning_man","meta_value" => "No"]);

        $I->seeInDatabase("wp_users", ["ID" => $this->userId, "user_email" => "testuser@example.com"]);

   
    }

    public function profileIncompleteFrontEndRedirect(AcceptanceTester $I)
    {
        $I->loginAs("testuser", "password123!test");
        $I->amOnPage("/");
        $I->wait(1);
        $I->seeInCurrentUrl("/profile");
        $I->see("Profile", "h1");
    }
}