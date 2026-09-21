<?php

class CampManagerBudgetsTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    protected function _before()
    {
        require_once(ABSPATH . 'wp-content/plugins/camp-manager/classes/class-budgets.php');
    }

    public function testUpsertAndGetBudgetCategory()
    {
        global $wpdb;
        $wpdb->query('COMMIT');
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }

        $budgets = $this->make('CampManagerBudgets', []);

        // Insert
        $id = $budgets->upsertBudgetCategory('Test Category', 'This is a test category');
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id, 'upsertBudgetCategory() should return the new row id');

        $category = $budgets->getBudgetCategory($id);
        $this->assertNotEmpty($category, 'Budget category was not inserted');
        $this->assertSame('Test Category', $category->name);
        $this->assertSame('This is a test category', $category->description);

        // Update in place: same id comes back and the row changes
        $sameId = $budgets->upsertBudgetCategory('Renamed Category', 'Updated description', $id);
        $this->assertSame($id, $sameId);
        $this->assertSame('Renamed Category', $budgets->getBudgetCategory($id)->name);

        // Delete
        $this->assertTrue($budgets->deleteBudgetCategory($id));
        $this->assertNull($budgets->getBudgetCategory($id));
    }
}
