<?php

namespace Tests\Acceptance;

use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;

// Shares the camp-receipts CI shard (the lightest Camp Manager one). A new group would also need
// a matrix entry in .github/workflows/codeception-test.yml, or no CI job would ever run it.
#[Group('camp-receipts')]
class CampManagerInventoryCest
{
    public function _before(AcceptanceTester $I)
    {
        $I->resetSeedState();
        $I->createTestAdmin();
        $I->signInAs("testadmin", "password123!test");
    }

    /** Two totes; a subwoofer packed in the sound tote, a kettle in the kitchen tote, a loose jug. */
    private function seedInventory(AcceptanceTester $I): array
    {
        $sound = $I->createTote(["name" => "Sound tote", "uid" => "SND01", "status" => "PACKED", "location" => "Sojourner", "weight" => 52.0])['id'];
        $kitchen = $I->createTote(["name" => "Kitchen tote", "uid" => "KIT01", "status" => "READY", "location" => "Garage", "weight" => 12.5, "size" => "Half"])['id'];
        $sub = $I->createInventoryItem(["name" => "DJ Subwoofer", "manufacturer" => "Peavey", "model" => "PV118", "quantity" => 1, "weight" => 30, "category_name" => "Sound", "location" => "Garage", "links" => "https://example.com/sub"])['id'];
        $kettle = $I->createInventoryItem(["name" => "Kettle", "quantity" => 2, "weight" => 2.5, "category_name" => "Kitchen"])['id'];
        $jug = $I->createInventoryItem(["name" => "Water Jug", "quantity" => 14, "category_name" => "Water", "set_name" => "Bar"])['id'];
        $I->createToteInventory($sound, $sub, 1);
        $I->createToteInventory($kitchen, $kettle, 2);
        return compact('sound', 'kitchen', 'sub', 'kettle', 'jug');
    }

    public function AllItemsTabListsTheInventory(AcceptanceTester $I)
    {
        $ids = $this->seedInventory($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-inventory");
        $I->waitForText("Inventory", 10, "h1");

        // The submenu is the three tabs; the add pages are reached from the lists.
        $I->see("All items", "#toplevel_page_camp-manager-inventory .wp-submenu");
        $I->see("Totes", "#toplevel_page_camp-manager-inventory .wp-submenu");
        $I->see("Tote inventory", "#toplevel_page_camp-manager-inventory .wp-submenu");
        $I->dontSee("Add Inventory", "#toplevel_page_camp-manager-inventory .wp-submenu");
        $I->dontSee("Add a Tote", "#toplevel_page_camp-manager-inventory .wp-submenu");

        $I->seeElement("a.page-title-action[href$='/wp-admin/admin.php?page=camp-manager-add-inventory']");
        $I->seeElement(".cm-tabs a.cm-tab.is-active[data-tab='items']");
        $I->seeElement(".cm-tabs a.cm-tab[data-tab='totes']");
        $I->seeElement(".cm-tabs a.cm-tab[data-tab='tote_inventory']");

        foreach (["name" => "Item", "quantity" => "Quantity", "category" => "Category", "location" => "Location", "tote" => "Tote / set", "links" => "Links"] as $id => $label) {
            $I->see($label, "th#$id");
        }
        foreach (["id", "manufacturer", "model", "weight", "category_name", "amp", "set_name", "photo"] as $removed) {
            $I->dontSeeElement("th#$removed");
        }
        $I->seeElement("#cm-filter-category");
        $I->seeElement("#cm-filter-location");
        $I->seeElement("#cm-filter-tote");
        $I->seeElement("#cm-per-page");
        $I->seeOptionIsSelected("#cm-per-page", "20 items per page");

        // Alphabetical: DJ Subwoofer, Kettle, Water Jug.
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 3);
        $I->see("DJ Subwoofer", "table.wp-list-table tbody tr:nth-child(1) .column-name");
        $I->see("Peavey", "table.wp-list-table tbody tr:nth-child(1) .cm-item-meta");
        $I->see("PV118", "table.wp-list-table tbody tr:nth-child(1) .cm-item-meta");
        $I->see("Sound", "table.wp-list-table tbody tr:nth-child(1) .column-category");
        $I->see("Garage", "table.wp-list-table tbody tr:nth-child(1) .column-location");
        $I->seeElement("table.wp-list-table tbody tr:nth-child(1) .column-tote a[href$='page=camp-manager-add-tote&id={$ids['sound']}']");
        $I->see("Sound tote", "table.wp-list-table tbody tr:nth-child(1) .column-tote");
        $I->seeElement("table.wp-list-table tbody tr:nth-child(1) .column-links a[href='https://example.com/sub'][target='_blank']");
        $I->see("View", "table.wp-list-table tbody tr:nth-child(1) .column-links");
        $I->see("Water Jug", "table.wp-list-table tbody tr:nth-child(3) .column-name");
        $I->see("14", "table.wp-list-table tbody tr:nth-child(3) .column-quantity");
        $I->see("Bar", "table.wp-list-table tbody tr:nth-child(3) .column-tote .cm-set");
        $I->seeElement("table.wp-list-table tbody tr:nth-child(3) .column-links .cm-empty");
        $I->seeElement("table.wp-list-table tbody tr:nth-child(3) .column-location .cm-empty");
    }

    public function ItemsCanBeFilteredSearchedAndSorted(AcceptanceTester $I)
    {
        $ids = $this->seedInventory($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-inventory");
        $I->waitForText("Inventory", 10, "h1");

        $I->selectOption("#cm-filter-category", "Kitchen");
        $I->click("#cm-filter-submit");
        // The filtered page marks the chosen option; the page before it did not.
        $I->waitForElement("#cm-filter-category option[value='Kitchen'][selected]", 10);
        $I->seeInCurrentUrl("category=Kitchen");
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);
        $I->seeOptionIsSelected("#cm-filter-category", "Kitchen");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-inventory&tote=none");
        $I->waitForText("Water Jug", 10, "table.wp-list-table tbody");
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);
        $I->seeOptionIsSelected("#cm-filter-tote", "Not in a tote");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-inventory&tote={$ids['sound']}");
        $I->waitForText("DJ Subwoofer", 10, "table.wp-list-table tbody");
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-inventory");
        $I->waitForText("Inventory", 10, "h1");
        $I->fillField("#inventory-search-search-input", "peavey");
        $I->click("#search-submit");
        $I->waitForElement("#inventory-search-search-input[value='peavey']", 10);
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-inventory&orderby=quantity&order=desc");
        $I->waitForText("Inventory", 10, "h1");
        $I->see("Water Jug", "table.wp-list-table tbody tr:nth-child(1) .column-name");
        $I->see("DJ Subwoofer", "table.wp-list-table tbody tr:nth-child(3) .column-name");

        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-inventory&per_page=50");
        $I->waitForText("Inventory", 10, "h1");
        $I->seeOptionIsSelected("#cm-per-page", "50 items per page");
    }

    public function TotesTabShowsTheOverviewAndTotes(AcceptanceTester $I)
    {
        $ids = $this->seedInventory($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-inventory");
        $I->waitForText("Inventory", 10, "h1");
        $I->click(".cm-tabs a[data-tab='totes']");
        $I->waitForElement(".cm-tabs a.cm-tab.is-active[data-tab='totes']", 10);
        $I->seeInCurrentUrl("page=camp-manager-totes");
        $I->seeElement("a.page-title-action[href$='/wp-admin/admin.php?page=camp-manager-add-tote']");

        $stat = fn(string $key) => $I->grabTextFrom("#totes-overview [data-stat='$key'] .cm-stat__value");
        $I->assertSame("2", $stat("total"));
        $I->assertSame("1", $stat("packed"));
        $I->assertSame("1", $stat("ready"));
        $I->assertSame("52.0 lbs", $stat("packed_weight"));
        $I->assertSame("52.0 lbs", $stat("sojourner_weight"));

        foreach (["name" => "Tote", "size" => "Size", "status" => "Status", "location" => "Location", "weight" => "Weight", "items" => "Items"] as $id => $label) {
            $I->see($label, "th#$id");
        }
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 2);
        $I->see("Kitchen tote", "table.wp-list-table tbody tr:nth-child(1) .column-name");
        $I->see("UID KIT01", "table.wp-list-table tbody tr:nth-child(1) .cm-item-meta");
        $I->see("Half", "table.wp-list-table tbody tr:nth-child(1) .column-size");
        $I->see("Ready", "table.wp-list-table tbody tr:nth-child(1) .column-status");
        $I->see("12.50 lbs", "table.wp-list-table tbody tr:nth-child(1) .column-weight");
        $I->see("1", "table.wp-list-table tbody tr:nth-child(1) .column-items");
        $I->see("2 pieces", "table.wp-list-table tbody tr:nth-child(1) .column-items .cm-item-meta");
        $I->see("Sound tote", "table.wp-list-table tbody tr:nth-child(2) .column-name");
        $I->see("Packed", "table.wp-list-table tbody tr:nth-child(2) .column-status");

        $I->selectOption("#cm-filter-status", "Packed");
        $I->click("#cm-filter-submit");
        $I->waitForElement("#cm-filter-status option[value='PACKED'][selected]", 10);
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);
        $I->see("Sound tote", "table.wp-list-table tbody .column-name");

        // The overview collapses and stays collapsed for this user. The filter has just
        // reloaded the page: wait until core's postbox script has bound the toggle (its
        // footer scripts, one from a CDN, can still be loading when the table is already there).
        $I->waitForJS("return !!(window.jQuery && jQuery._data(jQuery('#totes-overview .handlediv')[0], 'events'));", 10);
        $I->click("#totes-overview .handlediv");
        $I->waitForElement("#totes-overview.closed", 10);
        // The closed state is saved for this user with an ajax call.
        $I->wait(1);
        $I->reloadPage();
        $I->waitForText("Inventory", 10, "h1");
        $I->seeElement("#totes-overview.closed");
    }

    public function ToteInventoryTabListsWhatIsPacked(AcceptanceTester $I)
    {
        $ids = $this->seedInventory($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-view-tote-inventory");
        $I->waitForText("Inventory", 10, "h1");
        $I->seeElement(".cm-tabs a.cm-tab.is-active[data-tab='tote_inventory']");
        $I->seeElement("a.page-title-action[href$='/wp-admin/admin.php?page=camp-manager-add-tote-inventory']");

        $stat = fn(string $key) => $I->grabTextFrom("#tote-inventory-overview [data-stat='$key'] .cm-stat__value");
        $I->assertSame("2", $stat("items"));
        $I->assertSame("3", $stat("quantity"));
        $I->assertSame("35.0 lbs", $stat("weight"));
        $I->assertSame("2", $stat("totes"));

        foreach (["inventory_name" => "Item", "tote_name" => "Tote", "quantity" => "Quantity", "weight" => "Unit weight", "total_weight" => "Total weight"] as $id => $label) {
            $I->see($label, "th#$id");
        }
        $I->dontSeeElement("th#id");
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 2);
        $I->see("DJ Subwoofer", "table.wp-list-table tbody tr:nth-child(1) .column-inventory_name");
        $I->see("Sound tote", "table.wp-list-table tbody tr:nth-child(1) .column-tote_name");
        $I->see("Kettle", "table.wp-list-table tbody tr:nth-child(2) .column-inventory_name");
        $I->see("2", "table.wp-list-table tbody tr:nth-child(2) .column-quantity");
        $I->see("2.50 lbs", "table.wp-list-table tbody tr:nth-child(2) .column-weight");
        $I->see("5.00 lbs", "table.wp-list-table tbody tr:nth-child(2) .column-total_weight");

        $I->selectOption("#cm-filter-tote", "Kitchen tote");
        $I->click("#cm-filter-submit");
        $I->waitForText("This tote", 10, "#tote-inventory-overview");
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);
        $I->see("Kettle", "table.wp-list-table tbody .column-inventory_name");
        $I->assertSame("1", $stat("totes"));
    }

    public function ToteEditPageListsWhatIsPackedInIt(AcceptanceTester $I)
    {
        $ids = $this->seedInventory($I);
        $I->amOnPage("/wp-admin/admin.php?page=camp-manager-add-tote&id={$ids['kitchen']}");
        $I->waitForText("Edit Tote", 10, "h1");
        // The Inventory menu, and its Totes entry, stay highlighted on the edit page.
        $I->seeElement("#toplevel_page_camp-manager-inventory.wp-has-current-submenu");
        $I->seeElement("#toplevel_page_camp-manager-inventory .wp-submenu li.current a[href$='page=camp-manager-totes']");

        $I->see("Packed in this tote", "h2");
        $I->seeElement("a.page-title-action[href*='page=camp-manager-add-tote-inventory&tote_id={$ids['kitchen']}']");
        $I->seeNumberOfElements("table.wp-list-table tbody tr", 1);
        $I->see("Kettle", "table.wp-list-table tbody .column-inventory_name");
        $I->dontSeeElement("table.wp-list-table th#tote_name");
        $I->see("5.00 lbs", "table.wp-list-table tbody .column-total_weight");
    }
}
