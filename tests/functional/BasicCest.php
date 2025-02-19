<?php 

namespace Tests\Acceptance;

use Tests\Support\FunctionalTester;
class BasicCest
{
    public function _before(FunctionalTester $I)
    {

    }
    public function homePageIsVisible(FunctionalTester $I)
    {
        $I->amOnPage("/");
        $I->see("Mycodelic Forest", "h1");
    }
}