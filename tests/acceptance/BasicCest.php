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
        $I->see("Mycodelic Forest", "h1");
        $I->takeFullPageScreenshot("home-page");
    }

    public function aboutPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/about-us");
        $I->see("About Us");
        $I->takeFullPageScreenshot("about-page");
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

    public function calendarPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/calendar");
        $I->wait(1);
        $I->see("Calendar");
        $I->takeFullPageScreenshot("calendar-page");
    }

    public function contactPageIsVisible(AcceptanceTester $I)
    {
        $I->amOnPage("/contact");
        $I->wait(1);
        $I->see("Contact");
        $I->takeFullPageScreenshot("contact-page");

        $I->see("Name", "#gform_4 legend");
        $I->see("First", "#gform_4 label");
        $I->see("Last", "#gform_4 label");
        $I->see("Email", "#gform_4 label");
        $I->see("Subject", "#gform_4 label");
        $I->see("Message", "#gform_4 label");
        $I->seeElement("#gform_4 input[type=submit]");

        $I->click("Submit");
        $I->takeFullPageScreenshot("contact-page-errors");
        // $I->wait(1);
        // $I->see("There was a problem with your submission. Please review the fields below.");

        $I->fillField("#input_4_1_3", "John");
        $I->fillField("#input_4_1_6", "Doe");
        $I->fillField("#input_4_5", "test.smith@mailinator.com");
        $I->fillField("#input_4_3", "Test Subject");
        $I->fillField("#input_4_4", "Test Message");
        $I->click("Submit");

        $I->wait(1);
        $I->takeFullPageScreenshot("contact-page-thank-you");
        $I->see("Thanks for contacting us! We will get in touch with you shortly.");
    }
}