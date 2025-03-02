<?php

class MycodelicForestProfileTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

    protected function _before()
    {
        //$this->user
    }

    public function testProfileComplete()
    {
        $wpid = 1;

        $MycodelicForestProfile = $this->make('MycodelicForestProfile', []);

        $results = $MycodelicForestProfile->profileComplete($wpid);
        codecept_debug($results);

        $this->assertFalse($results);
    }

}


