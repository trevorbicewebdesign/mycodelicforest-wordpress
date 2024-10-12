<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;

class BasicCest
{
    public function _before(AcceptanceTester $I)
    {

    }

    public function homepageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/");
        $I->see("Mycodelic Forest");
    }
}