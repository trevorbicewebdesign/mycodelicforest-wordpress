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
        $faker = \Faker\Factory::create();
        $I->amOnPage("/register");
        $I->wait(1);
        $I->see("Register");
        $I->takeFullPageScreenshot("register-page");

        $I->see("Username", "#field_3_1 label");
        $I->see("Email", "#field_3_2 label");
        
        $I->see("Password", "#field_3_3 label");
        $I->see("Confirm Password", "#field_3_3 label");

        $I->see("Full Name", "#field_3_4 legend");
        $I->see("First", "#field_3_4 label");
        $I->see("Last", "#field_3_4 label");

        $I->see("Address", "#field_3_9 legend");
        $I->see("Street Address", "#field_3_9 label");
        $I->see("Address Line 2", "#field_3_9 label");
        $I->see("City", "#field_3_9 label");
        $I->see("State", "#field_3_9 label");
        $I->see("Zip", "#field_3_9 label");
        $I->see("Country", "#field_3_9 label");

        $I->see("Phone Number", "#field_3_5 label");
        $I->see("About Me", "#field_3_13 label");

        $I->see("Playa Name", "#field_3_6 label");
        $I->see("Have you been to Burning Man before?", "#field_3_15 legend");
        $I->see("Yes", "#field_3_15 label");
        $I->see("No", "#field_3_15 label");
        $I->dontSee("Years Attended", "#field_3_14 legend");
        // Click yes
        $I->click("#choice_3_15_1");
        $I->see("Years Attended", "#field_3_14 legend");

        $I->fillField("#input_3_1", $faker->userName());
        $I->fillField("#input_3_2", $faker->email());
        $I->fillField("#input_3_3", "password123!test");
        $I->fillField("#input_3_3_2", "password123!test");
        $I->fillField("#input_3_4_3", $faker->firstName());
        $I->fillField("#input_3_4_6", $faker->lastName());
        $I->fillField("#input_3_9_1", $faker->streetAddress());
        $I->fillField("#input_3_9_2", $faker->secondaryAddress());
        $I->fillField("#input_3_9_3", $faker->city());
        $I->fillField("#input_3_9_4", $faker->state());
        $I->fillField("#input_3_9_5", $faker->postcode());

        $I->selectOption("#input_3_9_6", "United States");

        $I->fillField("#input_3_5", $faker->phoneNumber());
        $I->fillField("#input_3_13", $faker->text(200));

        $I->fillField("#input_3_6", $faker->userName());
        
        $I->click("#choice_3_14_1");

        $I->takeFullPageScreenshot("register-page-filled");

        $I->click("Register");

        $I->wait(1);

        $I->takeFullPageScreenshot("register-page-thank-you");

        $I->see("Thank you for registering!");
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
        $I->see("Contact Form Was Submitted");

    }


}