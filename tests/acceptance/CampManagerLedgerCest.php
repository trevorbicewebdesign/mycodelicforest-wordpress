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

    public function AddLedger(AcceptanceTester $I)
    {
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-ledger");
        $I->see("Add Ledger Entry", "h1");

        $I->see("Note", "label[for='ledger_note']");
        $I->see("Amount", "label[for='ledger_amount']");
        $I->see("Date", "label[for='ledger_date']");
        $I->see("Link", "label[for='ledger_link']");

        $I->see("ID", "table thead tr th");
        $I->see("Receipt", "table thead tr th");
        $I->see("Note", "table thead tr th");
        $I->see("Amount", "table thead tr th");
        $I->see("Type", "table thead tr th");

        // Fill in the form
        $I->fillField("input[name=\"ledger_note\"]", "Test Ledger Item");
        $I->fillField("input[name=\"ledger_date\"]", date("m/d/Y"));
        $I->fillField("input[name=\"ledger_amount\"]", "-200.00");
        $randomNumber = rand(1000000000000000, 9999999999999999);
        $I->fillField("input[name=\"ledger_link\"]", "https://www.paypal.com/activity/payment/" . $randomNumber);

        $I->fillField("input[name=\"ledger_line_item_note[]\"]", "Test Ledger Line Item Note");
        $I->fillField("input[name=\"ledger_line_item_amount[]\"]", "200.00");
        $I->selectOption("select[name=\"ledger_line_item_type[]\"]", "Expense");

        // Submit the form
        $I->click("Save Ledger");

        // Verify the item was added
        $I->seeInDatabase("wp_mf_ledger", [
            "note" => "Test Ledger Item",
            "amount" => -200.00,
            "link" => "https://www.paypal.com/activity/payment/$randomNumber",
        ]);

        $ledger_id = $I->grabFromDatabase("wp_mf_ledger", "id", [
            "note" => "Test Ledger Item",
            "amount" => -200.00,
            "link" => "https://www.paypal.com/activity/payment/$randomNumber",
        ]);

        $I->seeInDatabase("wp_mf_ledger_line_items", [
            "ledger_id" => $ledger_id,
            "amount" => 200.0,
            "note" => "Test Ledger Line Item Note",
            "type" => "Expense",
        ]);
    }

    public function DeleteLedger(AcceptanceTester $I)
    {

        $ledger_id = $I->haveInDatabase("wp_mf_ledger", [
            "note" => "Test Ledger Item",
            "amount" => 200.00,
            "date" => date("Y-m-d H:i:s"),
            "link" => "https://www.paypal.com/activity/payment/76U3343887368243K",
        ]);

        $ledger_line_item_id = $I->haveInDatabase("wp_mf_ledger_line_items", [
            "ledger_id" => $ledger_id,
            "receipt_id" => 0,
            "name" => "",
            "amount" => 200.00,
            "cmid" => $this->userId,
            "note" => "Test Ledger Line Item Note",
            "type" => "Expense",
        ]);
        
        // Navigate to the ledger page
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-ledger");
        $I->see("Ledger", "h1");

        // Delete is a bulk action, so we need to select an item first
        $I->checkOption("input[name=\"ledger[]\"][value=\"$ledger_id\"]");
        $I->click("select[name=\"action\"]");
        $I->selectOption("select[name=\"action\"]", "Delete");
        $I->click("Apply");
        $I->wait("1");
        $I->see("Ledger", "h1");

        $I->dontSeeInDatabase("wp_mf_ledger", [
            "id" => $ledger_id,
        ]);
        $I->dontSeeInDatabase("wp_mf_ledger_line_items", [
            "id" => $ledger_line_item_id,
        ]);
    }
}