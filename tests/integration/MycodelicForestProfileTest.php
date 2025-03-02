<?php

class MycodelicForestProfileTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

     protected $wpid;
    protected function _before()
    {

        $this->wpid = wp_insert_user([
            'user_login' => 'testuser',
            'user_pass' => 'password',
            'user_email' => 'test.smith@mailinator.com'
        ]);
        
        update_user_meta($this->wpid, 'first_name', 'Test');
        update_user_meta($this->wpid, 'last_name', 'User');
        update_user_meta($this->wpid, 'user_phone', '555-555-5555');
        update_user_meta($this->wpid, 'address_1', '123 Main St');
        update_user_meta($this->wpid, 'city', 'Anytown');
        update_user_meta($this->wpid, 'state', 'CA');
        update_user_meta($this->wpid, 'zip', '12345');
        update_user_meta($this->wpid, 'country', 'United States');
        update_user_meta($this->wpid, 'user_about_me', 'This is a test.');
        update_user_meta($this->wpid, 'playa_name', 'TestBurner');
        update_user_meta($this->wpid, 'has_attended_burning_man', 'Yes');
        update_user_meta($this->wpid, 'years_attended', '["2024"]');

    }

    public function testProfileComplete()
    {
        $MycodelicForestProfile = $this->make('MycodelicForestProfile', []);

        $results = $MycodelicForestProfile->profileComplete($this->wpid);
        codecept_debug($results);

        $this->assertTrue($results);
    }

}


