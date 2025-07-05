<?php

class CampManagerBudgetsTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

    protected function _before()
    {
        require_once(ABSPATH . 'wp-content/plugins/camp-manager/classes/class-budgets.php');
        
    }

    public function testAddBudgetCategory()
    {
        global $wpdb;
        // commit the  database changes
        $wpdb->query('COMMIT');
        // Activate the plugin if not already activated
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }

        $CampManagerBudgets = $this->make('CampManagerBudgets', []);

        $results = $CampManagerBudgets->add_budget_category([
            'name' => 'Test Category',
            'description' => 'This is a test category',
            'budget' => 1000.00,
            'season' => 2023,
        ]);

        codecept_debug($results);

        // Check that the category was inserted
        $category = $CampManagerBudgets->get_budget_category($results);
        codecept_debug($category);
        $this->assertNotEmpty($category, 'Budget category was not inserted');
    }

}


