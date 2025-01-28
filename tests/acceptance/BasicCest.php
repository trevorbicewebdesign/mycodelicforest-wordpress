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
        $I->wait(1);
        $I->see("FAQs");
        $I->takeFullPageScreenshot("faqs-page");
    }

    public function resourcesPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/resources");
        $I->wait(1);
        $I->see("Resources");
        $I->takeFullPageScreenshot("resources-page");
    }

    public function registerPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/register");
        $I->wait(1);
        $I->see("Register");
        $I->takeFullPageScreenshot("register-page");
    }

    public function contactPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/contact");
        $I->wait(1);
        $I->see("Contact Us");
        $I->takeFullPageScreenshot("contact-page");

        $I->see("Name", "legend");
        $I->see("First", "label");
        $I->see("Last", "label");
        $I->see("Email", "legend");
        $I->see("Subject", "legend");
        $I->see("Message", "legend");
        $I->see("Submit", "button");

        $I->click("Submit");

        $I->see("The First Name field is required.");

        $I->fillField("#input_4_1_3", "John");
        $I->fillField("#input_4_1_6", "Doe");
        $I->fillField("#input_4_5", "test.smith@mailinator.com");
        $I->fillField("#input_4_3", "Test Subject");
        $I->fillField("#input_4_4", "Test Message");
        $I->click("Submit");

        $I->wait(1);

        $I->see("Contact Form Was Submitted");

    }


}