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
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-receipts");
        $I->see("Receipts", "h1"); // Adjust if needed to match page title

        // Assert that the `Add New` button is present
        $I->seeElement("a.page-title-action", ["href" => "https://local.mycodelicforest.org/wp-admin/admin.php?page=camp-manager-add-budget-item"]);

        // Assert that each table header is present
        $I->see("ID", "th#id");
        $I->see("Store", "th#store");
        $I->see("Date", "th#date");
        $I->see("Subtotal", "th#subtotal");
        $I->see("Tax", "th#tax");
        $I->see("Total", "th#total");

        // Optional: Check for the select-all checkbox label
        $I->see("Select All", "label[for='cb-select-all-1']");

        $I->seeNumberOfElements("table.wp-list-table tbody tr", 3); // Only the header row initially
    }

    public function AddNewReceipt(AcceptanceTester $I)
    {
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-receipt");
        $I->see("Add New Receipt", "h1");

        // Check that the form fields and labels are present
        $I->see("Store", "label[for='receipt_store']");
        $I->see("Date", "label[for='receipt_date']");
        $I->see("Subtotal", "label[for='receipt_subtotal']");
        $I->see("Tax", "label[for='receipt_tax']");
        $I->see("Total", "label[for='receipt_total']");
        $I->see("Category", "label[for='receipt_category']");
        $I->see("Amount", "label[for='receipt_amount']");
        $I->see("Quantity", "label[for='receipt_quantity']");
        $I->see("Subtotal", "label[for='receipt_subtotal']");
        $I->see("Tax", "label[for='receipt_tax']");
        $I->see("Total", "label[for='receipt_total']");
        $I->see("Priority", "label[for='receipt_priority']");

        $I->seeElement("input#receipt_store");
        $I->seeElement("input#receipt_date");
        $I->seeElement("input#receipt_subtotal");
        $I->seeElement("input#receipt_tax");
        $I->seeElement("input#receipt_total");
        $I->seeElement("select#receipt_category");
        $I->seeElement("select#receipt_budget_item_id");
        $I->seeElement("input#receipt_amount");
        $I->seeElement("input#receipt_quantity");
        $I->seeElement("input#receipt_subtotal");
        $I->seeElement("input#receipt_tax");
        $I->seeElement("input#receipt_total");
        $I->seeElement("input#receipt_priority");

        // Fill in the form fields
        $I->fillField("input#receipt_store", "Test Store");
        $I->fillField("input#receipt_date", "2024-01-01");
        $I->fillField("input#receipt_subtotal", "100.00");
        $I->fillField("input#receipt_tax", "10.00");
        $I->fillField("input#receipt_total", "110.00");
        $I->selectOption("select#receipt_category", "Supplies");
        $I->fillField("input#receipt_amount", "100.00");
        $I->fillField("input#receipt_quantity", "2");
        $I->fillField("input#receipt_subtotal", "200.00");
        $I->fillField("input#receipt_tax", "20.00");
        $I->fillField("input#receipt_total", "220.00");
        $I->fillField("input#receipt_priority", "1");

        // Submit the form
        $I->click(['css' => "input[type='submit'][value='Add Budget Item']"]);
        $I->wait("1");
        $I->waitForText("Budget Items", 15, "h1");

        // Check that we are on the view all budget items page
        $I->seeCurrentUrlEquals("/wp-admin/admin.php?page=camp-manager-budget-items&success=item_added");

        $I->seeInDatabase("wp_mf_budget_items", [
            "name" => "Test Budget Item",
            // "description" => "This is a test budget item description.",
            "category_id" => 1, // Assuming 'Power' category has ID 1
            "price" => 100,
            "quantity" => 2,
            "subtotal" => 200,
            "tax" => 20,
            "total" => 220,
            "priority" => 1,
        ]);

        $I->see("Test Budget Item", "table.table-view-list.budgetitems td.name");
        $I->see("Power", "table.table-view-list.budgetitems td.category");
        $I->see("100.00", "table.table-view-list.budgetitems td.price");
        $I->see("2", "table.table-view-list.budgetitems td.quantity");
        $I->see("200.00", "table.table-view-list.budgetitems td.subtotal");
        // $I->see("20.00", "table.table-view-list.budgetitems td.tax");
        $I->see("220.00", "table.table-view-list.budgetitems td.total");
        $I->see("1", "table.table-view-list.budgetitems td.priority");

    }

    public function DeleteReceipt(AcceptanceTester $I)
    {
        $id = $I->haveInDatabase("wp_mf_receipts", [
            "store" => "Test Store",
            "date" => "2024-01-01",
            "subtotal" => 100.00,
            "tax" => 10.00,
            "total" => 110.00,
            "category" => "Supplies",
            "amount" => 100.00,
            "quantity" => 2,
            "priority" => 1,
        ]);
        $item_id = $I->haveInDatabase("wp_mf_receipt_items", [
            "receipt_id" => $id,
            "name" => "Test Receipt Item",
            "price" => 100.00,
            "quantity" => 2,
            "subtotal" => 200.00,
            "tax" => 20.00,
        ]);
        // Navigate to the receipts page
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-receipts");
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