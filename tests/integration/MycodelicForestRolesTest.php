<?php

class MycodelicForestRolesTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

    protected function _before()
    {
        
    }

    public function testLastLogin()
    {
        $wpid = 1;

        $MycodelicForestRoles = $this->make('MycodelicForestRoles', []);

        $results = $MycodelicForestRoles->lastLogin($wpid);
        codecept_debug($results);
    }

}


