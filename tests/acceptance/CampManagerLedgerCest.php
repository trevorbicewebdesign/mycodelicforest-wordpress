<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class CampManagerLedgerCest
{
    protected $userId;
    protected $adminId;
    public function _before(AcceptanceTester $I)
    {
         $this->adminId = $I->haveUserInDatabase("testadmin", "administrator",[
            "first_name" => "Test",
            "last_name" => "Admin",
            "user_pass" => "password123!test",
            "meta_input" => [
                "first_name" => "Test",
                "last_name" => "Admin",
                "user_phone" => "(123) 456-7890",
                "address_1" => "123 Main St",
                "city" => "Anytown",
                "state" => "CA",
                "zip" => "12345",
                "country" => "United States",
                "user_about_me" => "This is a test.",
                "playa_name" => "TestBurner",
                "has_attended_burning_man" => "No",
                 // "years_attended" => '["2024"]',
            ]
        ]);
        $this->userId = $I->haveUserInDatabase("testuser", "subscriber",[
            "first_name" => "Test",
            "last_name" => "User",
            "user_pass" => "password123!test",
            "meta_input" => [
                "first_name" => "Test",
                "last_name" => "User",
                "user_phone" => "(123) 456-7890",
                "address_1" => "123 Main St",
                "city" => "Anytown",
                "state" => "CA",
                "zip" => "12345",
                "country" => "United States",
                "user_about_me" => "This is a test.",
                "playa_name" => "TestBurner",
                "has_attended_burning_man" => "No",
                // "years_attended" => '["2024"]',
            ]
        ]);

        $I->haveInDatabase("wp_mf_ledger", [
            "note" => "Test Ledger Item",
            "amount" => 200.00,
            "date" => date("Y-m-d H:i:s"),
            "link" => "https://www.paypal.com/activity/payment/76U3343887368243K",
        ]);
        $I->wait(1);

        $I->loginAs("testadmin", "password123!test");
    }
    public function ViewLedgerItems(AcceptanceTester $I)
    {

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-ledger");
        $I->see("Ledger", "h1");

        $I->seeElement("a.page-title-action", ["href" => "https://local.mycodelicforest.org/wp-admin/admin.php?page=camp-manager-add-ledger"]);

        $I->see("ID", "th#id");
        $I->see("Note", "th#note");
        $I->see("Amount", "th#amount");
        $I->see("Date", "th#date");
        $I->see("Receipts", "th#receipts");
        $I->see("Link", "th#link");

        $I->see("Select All", "label[for='cb-select-all-1']");

        $I->dontSee("No items found.");

        $I->see("Test Ledger Item", "table.wp-list-table tbody tr:nth-child(1) td.note");
        $I->see("200.00", "table.wp-list-table tbody tr:nth-child(1) td.amount");
        $I->see(date("Y-m-d"), "table.wp-list-table tbody tr:nth-child(1) td.date");
        $I->see("", "table.wp-list-table tbody tr:nth-child(1) td.receipts");
        $I->see("View", "table.wp-list-table tbody tr:nth-child(1) td.link");
        $I->seeLink("View", "https://www.paypal.com/activity/payment/76U3343887368243K");  
    }
}