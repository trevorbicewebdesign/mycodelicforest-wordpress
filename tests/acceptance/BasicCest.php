<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class BasicCest
{
    public function _before(AcceptanceTester $I)
    {

    }
    public function homePageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/");
        $I->see("Mycodelic Forest");
        $I->takeFullPageScreenshot("home-page");
    }

    public function aboutPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/contact");
        $I->see("Contact Us");
        $I->takeFullPageScreenshot("contact-page");
    }

    public function faqsPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/faqs");
        $I->see("FAQs");
        $I->takeFullPageScreenshot("faqs-page");
    }

    public function resourcesPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/resources");
        $I->see("Resources");
        $I->takeFullPageScreenshot("resources-page");
    }

    public function registerPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/register");
        $I->see("Register");
        $I->takeFullPageScreenshot("register-page");
    }

    public function contactPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/contact");
        $I->see("Contact Us");
        $I->takeFullPageScreenshot("contact-page");
    }


}