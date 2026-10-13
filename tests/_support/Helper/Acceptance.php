<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use Codeception\Exception\ModuleException;
use lucatume\WPBrowser\Module\WPWebDriver;

/**
 * Sends every browser request to the seed database, and refuses to run a test otherwise.
 *
 * wp-config.php picks the seed database when a request carries the X-Test-Request header or the
 * webdriver_test_request cookie, and says so in an X-WP-Test-Kit-Database response header.
 */
class Acceptance extends \Codeception\Module
{
    public function _before(\Codeception\TestInterface $test)
    {
        /** @var WPWebDriver $I */
        $I = $this->getModule(WPWebDriver::class);

        // The header goes on every request the browser makes, the very first one included.
        $I->executeInSelenium(static function ($driver) {
            foreach ([
                ['cmd' => 'Network.enable', 'params' => new \stdClass()],
                ['cmd' => 'Network.setExtraHTTPHeaders', 'params' => ['headers' => ['X-Test-Request' => '1']]],
            ] as $command) {
                $driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', $command);
            }
        });

        // The cookie covers anything that does not go through the browser's network stack the
        // same way (and other browsers, should the suite ever run on one).
        $I->amOnPage('/');
        $I->setCookie('webdriver_test_request', '1');

        $database = $I->executeAsyncJS(
            'var done = arguments[arguments.length - 1];'
            . 'fetch("/wp-login.php", {method: "HEAD", credentials: "same-origin", cache: "no-store"})'
            . '.then(function (r) { done(r.headers.get("X-WP-Test-Kit-Database")); })'
            . '.catch(function () { done(null); });'
        );
        if ($database !== 'seed') {
            throw new ModuleException(
                $this,
                'Browser requests are not reaching the seed database (the server answered "'
                . var_export($database, true) . '"). Refusing to run: a test must never touch the site\'s own database. '
                . 'Check the database switch in wp-config.php.'
            );
        }
    }
}
