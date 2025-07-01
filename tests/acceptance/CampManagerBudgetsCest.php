<?php 

namespace Tests\Acceptance;

use Tests\Support\AcceptanceTester;
class CampManagerBudgetsCest
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
    public function ViewBudgetItems(AcceptanceTester $I)
    {
        // Navigate to the budget items page (not the add form, to see the table)
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-budget-items");
        $I->see("Budget Items", "h1"); // Adjust if needed to match page title

        // Assert that the `Add New` button is present
        $I->seeElement("a.page-title-action", ["href" => "https://local.mycodelicforest.org/wp-admin/admin.php?page=camp-manager-add-budget-item"]);

        // Assert that each table header is present
        $I->see("ID", "th#id");
        $I->see("Name", "th#name");
        $I->see("Price", "th#price");
        $I->see("Quantity", "th#quantity");
        $I->see("Subtotal", "th#subtotal");
        $I->see("Total", "th#total");
        $I->see("Purchased", "th#purchased");
        $I->see("Priority", "th#priority");

        // Optional: Check for the select-all checkbox label
        $I->see("Select All", "label[for='cb-select-all-1']");

        $I->seeNumberOfElements("table.wp-list-table tbody tr", 20); // Only the header row initially
    }

    public function AddNewBudgetItem(AcceptanceTester $I)
    {
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-budget-item");
        $I->see("Add New Budget Item", "h1"); 

        // Check that the form fields and labels are present
        $I->see("Name", "label[for='budget_item_name']");
        $I->see("Description", "label[for='budget_item_description']");
        $I->see("Category", "label[for='budget_item_category']");
        $I->see("Amount", "label[for='budget_item_amount']");
        $I->see("Quantity", "label[for='budget_item_quantity']");
        $I->see("Subtotal", "label[for='budget_item_subtotal']");
        $I->see("Tax", "label[for='budget_item_tax']");
        $I->see("Total", "label[for='budget_item_total']");
        $I->see("Priority", "label[for='budget_item_priority']");

        $I->seeElement("input#budget_item_name");
        $I->seeElement("textarea#budget_item_description");
        $I->seeElement("select#budget_item_category");
        $I->seeElement("input#budget_item_amount");
        $I->seeElement("input#budget_item_quantity");
        $I->seeElement("input#budget_item_subtotal");
        $I->seeElement("input#budget_item_tax");
        $I->seeElement("input#budget_item_total");
        $I->seeElement("input#budget_item_priority");

        // Fill in the form fields
        $I->fillField("input#budget_item_name", "Test Budget Item");
        $I->fillField("textarea#budget_item_description", "This is a test budget item description.");
        $I->selectOption("select#budget_item_category", "Power");
        $I->fillField("input#budget_item_amount", "100.00");
        $I->fillField("input#budget_item_quantity", "2");
        $I->fillField("input#budget_item_subtotal", "200.00");
        $I->fillField("input#budget_item_tax", "20.00");
        $I->fillField("input#budget_item_total", "220.00");
        $I->fillField("input#budget_item_priority", "1");

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
        // $I->see("Power", "table.table-view-list.budgetitems td.category");
        $I->see("100.00", "table.table-view-list.budgetitems td.price");
        $I->see("2", "table.table-view-list.budgetitems td.quantity");
        $I->see("200.00", "table.table-view-list.budgetitems td.subtotal");
        // $I->see("20.00", "table.table-view-list.budgetitems td.tax");
        $I->see("220.00", "table.table-view-list.budgetitems td.total");
        $I->see("1", "table.table-view-list.budgetitems td.priority");

    }

    public function deleteBudgetItem(AcceptanceTester $I)
    {
        $id = $I->haveInDatabase("wp_mf_budget_items", [
            "name" => "Test Budget Item",
            "category_id" => 1, // Assuming 'Power' category has ID 1
            "price" => 100,
            "quantity" => 2,
            "subtotal" => 200,
            "tax" => 20,
            "total" => 220,
            "priority" => 1,
        ]);
        // Navigate to the budget items page
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-budget-items");
        $I->see("Budget Items", "h1");

        // Delete is a bulk action, so we need to select an item first
        $I->checkOption("input[name='item[]'][value='$id']"); //
    }


}