<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class RegistrationCest
{
    public function _before(AcceptanceTester $I)
    {
        // Registering creates a user and a form entry; start from the seed each time.
        $I->resetSeedState();
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
        $I->see("Full Name", "#field_3_4 legend");
        $I->see("First", "#field_3_4 label");
        $I->see("Last", "#field_3_4 label");
        $I->see("Phone Number", "#field_3_5 label");

        $user_login = $faker->userName();

        $I->fillField("#input_3_1", $user_login);
        $I->fillField("#input_3_2", $faker->email());
        $I->fillField("#input_3_4_3", $faker->firstName());
        $I->fillField("#input_3_4_6", $faker->lastName());
        $I->fillField("#input_3_5", $faker->phoneNumber());

        $I->takeFullPageScreenshot("register-page-filled");

        $I->click("Register");
        $I->wait(1);

        $I->takeFullPageScreenshot("register-page-thank-you");
        $I->see("Thanks for registering!");
        $I->wait(1);        
        $email_id = $I->getLastEmailId();
        $email = $I->getEmailById($email_id);
        codecept_debug($email);

        if (preg_match('/[?&]key=([^&]+)/', $email['Text'], $matches)) {
            $user_activation_key = $matches[1];
            echo "Extracted key: " . $user_activation_key;
        } else {
            echo "Key not found.";
        }        

        // Base URL of whatever site the suite is pointed at (CI: http://127.0.0.1).
        $domain = rtrim($I->executeJS('return location.origin;'), '/');
        $text = $email['Text'];
        $I->assertStringContainsString("Hi {$user_login},", $text);
        $I->assertStringContainsString("activate your account and set a new password", $text);
        $I->assertStringContainsString("{$domain}/wp-login.php?action=rp&key={$user_activation_key}&login={$user_login}", $text);
        $I->assertStringContainsString("This link expires in 24 hours", $text);
        $I->assertStringContainsString("If you did not register, please ignore this email.", $text);
    
        $I->amOnPage("/wp-login.php?login=$user_login&key=$user_activation_key&action=rp");
        $I->wait(1);
        $I->takeFullPageScreenshot("register-page-reset-password");
        $I->see("Enter your new password below or generate one.");
        $password = $I->grabAttributeFrom("#pass1", "value");
        $I->click("Save Password");
        $I->wait(1);
        $I->see("Your password has been reset.");
        $I->see("Log in");
        $I->click("Log in");
        $I->wait(1);
        $I->see("Log In");
        $I->fillField("#user_login", $user_login);
        $I->fillField("#user_pass", $password);
        $I->click("#wp-submit");
        $I->wait(1);
        // A freshly registered member has no address/phone yet, so the plugin sends
        // them to the profile page until it is complete.
        $I->seeInCurrentUrl("/profile/");
    }
    
}