<?php 

namespace Tests\Acceptance;

use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;
#[Group('profile')]
class ProfileAdminCest
{
    protected $userId;
    protected $adminId;
    protected $profileIncompleteId;
    public function _before(AcceptanceTester $I)
    {
        $I->resetSeedState();
        $this->adminId = $I->createTestAdmin()['id'];
        $this->userId = $I->createTestUser()['id'];
        $this->profileIncompleteId = $I->createUser("profile-incomplete", "subscriber", [
            "first_name" => "Profile",
            "last_name" => "Incomplete",
            "meta_input" => [
                "first_name" => "Profile",
                "last_name" => "Incomplete",
            ],
        ])['id'];

        
    }

    public function profileAdminEditUserPage(AcceptanceTester $I)
    {
        $I->signInAs("testadmin", "password123!test");
        $I->amOnPage("/wp-admin/user-edit.php?user_id=".$this->userId);
        $I->see("Edit User testuser");
        $I->takeFullPageScreenshot("admin-edit-user-page");

        $I->see("First Name", "label[for='first_name']");
        $I->see("Last Name", "label[for='last_name']");
        $I->see("Email", "label[for='email']");
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
        $I->dontSee("Years Attended", "label[for='years_attended']");

        $I->seeInField("#first_name", "Test");
        $I->seeInField("#last_name", "User");
        $I->scrollTo("#user_phone");
        $I->seeInField("#user_phone", "(123) 456-7890");
        $I->seeInField("#address_1", "123 Main St");
        $I->seeInField("#city", "Anytown");
        $I->seeInField("#state", "CA");
        $I->seeInField("#zip", "12345");
        $I->seeOptionIsSelected("#country", "United States");
        $I->seeInField("#user_about_me", "This is a test.");
        $I->seeInField("#playa_name", "TestBurner");
        $I->seeOptionIsSelected("[name=has_attended_burning_man]", "No");
        $I->selectOption("[name=has_attended_burning_man]", "Yes");
        $I->checkOption("[name='years_attended[]'][value='2024']");
        // seeCheckboxIsChecked() takes one selector and checks the first match, so name the
        // 2024 box itself rather than whichever year happens to come first in the list.
        $I->seeCheckboxIsChecked("[name='years_attended[]'][value='2024']");
        // Who invited them: another member, picked by name.
        $I->see("Sponsor", "label[for='sponsor_user_id']");
        $I->seeOptionIsSelected("#sponsor_user_id", "None");
        $I->selectOption("#sponsor_user_id", "Test Admin (TestBurner)");
        
        $I->click("Update User");
        $I->waitForText("User updated.", 10);
        $I->seeOptionIsSelected("#sponsor_user_id", "Test Admin (TestBurner)");
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->userId, "meta_key" => "sponsor_user_id","meta_value" => (string) $this->adminId]);

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

    public function profileAdminPageIsVisible(AcceptanceTester $I)
    {
        $I->signInAs("testadmin", "password123!test");
        $I->amOnPage("/wp-admin/profile.php");
        $I->see("Profile", "h1");
        $I->takeFullPageScreenshot("admin-profile-page");

        $I->see("First Name", "label[for='first_name']");
        $I->see("Last Name", "label[for='last_name']");
        $I->see("Email", "label[for='email']");
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
        // Years attended should be hidden
        $I->dontSee("Years Attended", "label[for='years_attended']");

        $I->seeInField("#first_name", "Test");
        $I->seeInField("#last_name", "Admin");
        $I->seeInField("#user_phone", "(123) 456-7890");
        $I->seeInField("#address_1", "123 Main St");
        $I->seeInField("#city", "Anytown");
        $I->seeInField("#state", "CA");
        $I->seeInField("#zip", "12345");
        $I->seeOptionIsSelected("#country", "United States");
        $I->seeInField("#user_about_me", "This is a test.");
        $I->seeOptionIsSelected("[name=has_attended_burning_man]", "No");
        $I->selectOption("[name=has_attended_burning_man]", "Yes");
        $I->checkOption("[name='years_attended[]'][value='2024']");
        // seeCheckboxIsChecked() takes one selector and checks the first match, so name the
        // 2024 box itself rather than whichever year happens to come first in the list.
        $I->seeCheckboxIsChecked("[name='years_attended[]'][value='2024']");
        
        $I->click("Update Profile");
        $I->waitForText("Profile updated.", 10);

        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "first_name","meta_value" => "Test"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "last_name","meta_value" => "Admin"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "user_phone","meta_value" => "(123) 456-7890"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "address_1","meta_value" => "123 Main St"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "city","meta_value" => "Anytown"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "state","meta_value" => "CA"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "zip","meta_value" => "12345"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "country","meta_value" => "United States"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "user_about_me","meta_value" => "This is a test."]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "playa_name","meta_value" => "TestBurner"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "has_attended_burning_man","meta_value" => "Yes"]);
        $I->seeInDatabase("wp_usermeta", ["user_id"=>$this->adminId, "meta_key" => "years_attended","meta_value" => '["2024"]']);

    }


}