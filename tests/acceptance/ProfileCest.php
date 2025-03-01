<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class ProfileCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->haveUserInDatabase("testuser", "subscriber",[
            "first_name" => "Test",
            "last_name" => "User",
            "user_phone" => "123-456-7890",
            "user_pass" => "password123!test",
        ]);

        $I->loginAs("testuser", "password123!test");

    }

    public function profilePageIsVisible(AcceptanceTester $I)
    {
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
        $I->selectOption("#input_6_9_6", ["value" => "US"]);
        $I->fillField("#input_6_13", "This is a test.");
        $I->fillField("#input_6_6", "TestBurner");
        $I->selectOption("#input_6_19_0", ["value" => "No"]);
        $I->click("Save");
        $I->wait(2);
        $I->see("Profile updated successfully.");

    }
    
}