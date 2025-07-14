<?php

class CampManagerRosterTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

    protected function _before()
    {
        require_once(ABSPATH . 'wp-content/plugins/camp-manager/classes/class-roster.php');
        
    }

    public function testAddMember()
    {
        global $wpdb;
        // commit the  database changes
        $wpdb->query('COMMIT');
        // Activate the plugin if not already activated
        if (!is_plugin_active('camp-manager/camp-manager.php')) {
            activate_plugin('camp-manager/camp-manager.php');
        }   

        $CampManagerRoster = $this->make('CampManagerRoster', []);

        $results = $CampManagerRoster->addMember([
            'wpid' => get_current_user_id(), // Current user ID
            'low_income' => 0, // Example value, adjust as needed
            'fully_paid' => 1, // Example value, adjust as needed
            'season' => 2023, // Example season, adjust as needed
            'fname' => 'John', // First name
            'lname' => 'Doe', // Last name
            'playaname' => 'Johnny', // Playa name
            'email' => 'john.doe@example.com', // Email
        ]);

        codecept_debug($results);

        $member = $CampManagerRoster->getMemberById($results);
        codecept_debug($member);

    }

}


