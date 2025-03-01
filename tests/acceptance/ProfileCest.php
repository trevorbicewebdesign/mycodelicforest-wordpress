<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class ProfileCest
{
    public function _before(AcceptanceTester $I)
    {

    }

    public function profilePageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/profile/");
        $I->see("Profile");
        $I->takeFullPageScreenshot("profile-page");

        $I->see("Name (Required)", "legend.gfield_label");

    }
    
}