<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use \lucatume\WPBrowser\Module\WPWebDriver;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \codeception\Module
{

    public function _before(\Codeception\TestInterface $test)
    {
        /** @var WPWebDriver $I */
        $I = $this->getModule(WPWebDriver::class);
        
        // Set a cookie to identify this as a test request.
        $I->amOnPage('/');
        $I->setCookie('webdriver_test_request', '1');
    }
}
