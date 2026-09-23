<?php

declare(strict_types=1);

namespace Tests\Support;
use \lucatume\WPBrowser\Module\WPWebDriver;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Define custom actions here
     */

    /**
     * Logs in through wp-login.php and waits until the session is really established.
     *
     * This drives the login form itself rather than using WPWebDriver::loginAs(). That
     * helper reads the auth cookies the instant it clicks "Log In"; when the POST is still in
     * flight on a cold CI runner it sees none, navigates back to wp-login.php and submits
     * again. The new navigation cancels the request that was about to succeed, and the
     * browser ends up on a bare login form with no error while the test waits for the admin
     * bar (CI runs 35684724624 and 35916011873: every failure had this shape). Here each
     * attempt waits for the page the login redirects to; if that wait times out, a fresh
     * request for /wp-admin/ shows whether the slow submit did land before anything is
     * retried.
     *
     * @param string $login         User login.
     * @param string $password      User password.
     * @param string $readySelector Element that only exists once logged in.
     * @param int    $attempts      Maximum login attempts.
     */
    public function signInAs(string $login, string $password, string $readySelector = '#wpadminbar', int $attempts = 3): void
    {
        $I = $this;

        for ($attempt = 1; ; $attempt++) {
            // Start from no session: a cookie left by an earlier test (whose user has since
            // been deleted) would send wp-login.php somewhere other than the form.
            $I->executeInSelenium(function ($webDriver) {
                $webDriver->manage()->deleteAllCookies();
            });
            $I->amOnPage('/wp-login.php');
            $I->waitForElement('#loginform', 20);
            $I->fillField('#user_login', $login);
            $I->fillField('#user_pass', $password);
            $I->click('#wp-submit');

            try {
                // Let the login redirect finish before the test navigates, or the
                // redirect wins and lands on the Dashboard.
                $I->waitForElement($readySelector, 30);
                return;
            } catch (\Throwable $e) {
                // The submit may have completed after all; a fresh admin request tells.
                try {
                    $I->amOnPage('/wp-admin/');
                    $I->waitForElement($readySelector, 15);
                    return;
                } catch (\Throwable $e2) {
                    // fall through to retry below
                }
                if ($attempt >= $attempts) {
                    throw $e;
                }
                $I->comment("Login as {$login} did not complete (attempt {$attempt}/{$attempts}); retrying.");
            }
        }
    }

    /**
     * Mirrors CampManagerSeason::current()'s fallback chain (the `camp_manager_season`
     * option, else MAX(season) on mf_roster, else this year) - app code doesn't run in
     * this process, so fixtures that need to land in a season the app will actually
     * display have to replicate the same resolution instead of guessing a fixed year.
     */
    public function currentCampManagerSeason(): int
    {
        $I = $this;

        $option = (int) $I->grabOptionFromDatabase('camp_manager_season');
        if ($option) {
            return $option;
        }

        $latest = (int) $I->grabFromDatabase($I->grabPrefixedTableNameFor('mf_roster'), 'MAX(season)');
        return $latest ?: (int) gmdate('Y');
    }

     /**
     * Captures a full-page screenshot by resizing the browser to the entire page height,
     * taking the screenshot, then restoring the original window size.
     *
     * @param string $filename Name or path for the screenshot (without .png extension).
     */
    public function takeFullPageScreenshot(string $filename = 'fullpage'): void
    {
        $I = $this;

        // 1. Remember the current window size so we can restore it
        $originalWidth = $I->executeInSelenium(static function (\Facebook\WebDriver\Remote\RemoteWebDriver $driver) {
            return $driver->manage()->window()->getSize()->getWidth();
        });
        $originalHeight = $I->executeInSelenium(static function (\Facebook\WebDriver\Remote\RemoteWebDriver $driver) {
            return $driver->manage()->window()->getSize()->getHeight();
        });

        // 2. Get the full page height
        $pageHeight = $I->executeJS("
            return Math.max(
                document.body.scrollHeight,
                document.documentElement.scrollHeight,
                document.body.offsetHeight,
                document.documentElement.offsetHeight,
                document.body.clientHeight,
                document.documentElement.clientHeight
            );
        ");

        // 3. Resize the browser window to match the full page height
        //    (use a fixed width, e.g., 1920 - adjust as needed)
        $I->resizeWindow(1920, $pageHeight);

        // 4. Wait briefly for the resize to take effect
        $I->wait(1);

        // 5. Capture screenshot
        $I->makeScreenshot($filename);

        // 6. Restore the original window size
        $I->resizeWindow($originalWidth, $originalHeight);
    }
}
