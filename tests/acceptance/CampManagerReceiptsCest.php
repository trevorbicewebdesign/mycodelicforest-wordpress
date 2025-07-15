<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class CampManagerReceiptsCest
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
        $I->loginAs("testadmin", "password123!test");
    }
    public function ViewReceipts(AcceptanceTester $I)
    {
        // Navigate to the receipts page (not the add form, to see the table)
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-actuals");
        $I->see("Receipts", "h1"); // Adjust if needed to match page title

        // Assert that the `Add New` button is present
        $I->seeElement("a.page-title-action", ["href" => "https://local.mycodelicforest.org/wp-admin/admin.php?page=camp-manager-add-receipt"]);

        // Assert that each table header is present
        $I->see("ID", "th#id");
        $I->see("User", "th#cmid");
        $I->see("Store", "th#store");
        $I->see("Date", "th#date");
        $I->see("Subtotal", "th#subtotal");
        $I->see("Tax", "th#tax");
        $I->see("Shipping", "th#shipping");
        $I->see("Total", "th#total");
        $I->see("Reimbursed", "th#reimbursed");
        $I->see("Ledger ID", "th#ledger_id");

        // Optional: Check for the select-all checkbox label
        $I->see("Select All", "label[for='cb-select-all-1']");

        // $I->seeNumberOfElements("table.wp-list-table tbody tr", 3);
    }

    public function AddNewReceipt(AcceptanceTester $I)
    {
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-receipt");
        $I->see("Add New Receipt", "h1");

        // Check that the form fields and labels are present
        $I->see("Store", "label[for='store']");
        $I->see("Date", "label[for='date']");
        $I->see("Purchaser", "label[for='purchaser']");
        $I->see("Link", "label[for='link']");
        $I->see("Subtotal", "label[for='subtotal']");
        $I->see("Tax", "label[for='tax']");
        $I->see("Shipping", "label[for='shipping']");
        $I->see("Total", "label[for='total']");

        $I->seeElement("input#store");
        $I->seeElement("input#date");
        $I->seeElement("select#purchaser");
        $I->seeElement("input#link");
        $I->seeElement("input#subtotal");
        $I->seeElement("input#tax");
        $I->seeElement("input#shipping");
        $I->seeElement("input#total");

        $I->see("Item Name", "table thead tr th");
        $I->see("Category", "table thead tr th");
        $I->see("Item", "table thead tr th");
        $I->see("Price", "table thead tr th");
        $I->see("Qty", "table thead tr th");
        $I->see("Subtotal", "table thead tr th");
        $I->see("Tax", "table thead tr th");
        $I->see("Shipping", "table thead tr th");
        $I->see("Total", "table thead tr th");

        $I->seeElement("input[name='items[0][name]']");
        $I->seeElement("select[name='items[0][category]']");
        $I->seeElement("select[name='items[0][budget_item_id]']");
        $I->seeElement("input[name='items[0][price]']");
        $I->seeElement("input[name='items[0][quantity]']");
        $I->seeElement("input[name='items[0][subtotal]']");
        $I->seeElement("input[name='items[0][tax]']");
        $I->seeElement("input[name='items[0][shipping]']");
        $I->seeElement("input[name='items[0][total]']");

        // Fill in the form fields for one row
        $I->fillField("input#store", "Test Store");
        $I->fillField("input#date", "01/01/2024");
        $I->selectOption("select#purchaser", "Trevor Bice");
        $I->fillField("input#subtotal", "200.00");
        $I->fillField("input#tax", "10.00");
        $I->fillField("input#shipping", "20.00");
        $I->fillField("input#total", "230.00");
        
        // Fill in the first item row
        $I->fillField("input[name='items[0][name]']", "Test Budget Item");
        $I->selectOption("select[name='items[0][category]']", "Power");
        $I->selectOption("select[name='items[0][budget_item_id]']", "1");
        $I->fillField("input[name='items[0][price]']", "100");
        $I->fillField("input[name='items[0][quantity]']", "2");
        $I->fillField("input[name='items[0][subtotal]']", "200");
        $I->fillField("input[name='items[0][tax]']", "10");
        $I->fillField("input[name='items[0][shipping]']", "20");
        $I->fillField("input[name='items[0][total]']", "230");

        // Submit the form
        $I->click(['css' => "input[type='submit'][value='Save Receipt']"]);
        $I->wait("1");
        $I->waitForText("Edit Receipt", 15, "h1");

        $I->seeInDatabase("wp_mf_receipts", [
            "store" => "Test Store",
            "date" => "2024-01-01",
            'cmid' => 1,
            "subtotal" => 200.00,
            "tax" => 10.00,
            "shipping" => 20.00,
            "total" => 230.00,
        ]);

        $receipt_id = $I->grabFromDatabase("wp_mf_receipts", 'id',[
            "store" => "Test Store",
            "date" => "2024-01-01 00:00:00",
            'cmid' => 1,
            "subtotal" => 200.00,
            "tax" => 10.00,
            "shipping" => 20.00,
            "total" => 230.00,
        ]);

        $I->seeInDatabase("wp_mf_receipt_items", [
            "receipt_id" => $receipt_id,
            "category_id" => "1",
            "name" => "Test Budget Item",
            "price" => 100.00,
            "quantity" => 2,
            "subtotal" => 200.00,
            "tax" => 10.00,
            "shipping" => 20.00,
            "total" => 230.00,
        ]);
    }

    public function DeleteReceipt(AcceptanceTester $I)
    {
        $id = $I->haveInDatabase("wp_mf_receipts", [
            "cmid" => 1,
            "store" => "Test Store",
            "date" => "2025-08-01",
            "subtotal" => 100.00,
            "tax" => 10.00,
            "shipping" => 0.00,
            "total" => 110.00,
        ]);
        $item_id = $I->haveInDatabase("wp_mf_receipt_items", [
            "receipt_id" => $id,
            "category_id" => 1,
            "name" => "Test Receipt Item",
            "price" => 100.00,
            "quantity" => 2,
            "subtotal" => 200.00,
            "tax" => 20.00,
        ]);
        // Navigate to the receipts page
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-actuals");
        $I->see("Receipts", "h1");

        // Delete is a bulk action, so we need to select an item first
        $I->checkOption("input[name=\"receipt[]\"][value=\"$id\"]");
        $I->click("select[name=\"action\"]");
        $I->selectOption("select[name=\"action\"]", "Delete");
        $I->click("Apply");
        $I->wait("1");
        $I->see("Receipts", "h1");

        $I->dontSeeInDatabase("wp_mf_receipts", [
            "id" => $id,
        ]);
    }
}