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

    public function test_get_remaining_budget_by_category()
    {
        global $wpdb;
        // commit the  database changes
        $wpdb->query('COMMIT');
        // Activate the plugin if not already activated
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }

        $CampManagerBudgets = $this->make('CampManagerBudgets', []);

        $remaining_budget = $CampManagerBudgets->get_remaining_budget_by_category(1, 1);

        codecept_debug("Remainig budget");
        codecept_debug($remaining_budget);
    }

}


