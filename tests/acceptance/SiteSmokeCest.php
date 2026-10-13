<?php

declare(strict_types=1);

namespace Tests\Acceptance;

use Codeception\Attribute\Group;
use Tests\Support\AcceptanceTester;

/**
 * The site in a real browser, against the seed database: every published page renders, an
 * administrator can sign in, and mail reaches Mailpit. Replace or extend with tests that know
 * what the pages should say.
 */
#[Group('site')]
class SiteSmokeCest
{
    public function _before(AcceptanceTester $I): void
    {
        $I->resetSeedState();
    }

    public function theHomePageIsVisible(AcceptanceTester $I): void
    {
        $I->amOnPage('/');
        $I->seeElement('body');
        $this->dontSeeAnError($I);
        $I->takeFullPageScreenshot('home-page');
    }

    public function everyPublishedPageRenders(AcceptanceTester $I): void
    {
        $paths = $I->grabPublishedPagePaths();
        $I->assertNotEmpty($paths, 'the seed holds no published pages');

        foreach ($paths as $id => $path) {
            $I->amOnPage($path);
            $I->seeElement('body');
            $this->dontSeeAnError($I);
            $I->makeScreenshot('page-' . $id);
        }
    }

    public function anAdministratorCanSignIn(AcceptanceTester $I): void
    {
        $admin = $I->createTestAdmin();

        $I->signInAs($admin['login'], $admin['password']);
        $I->amOnPage('/wp-admin/plugins.php');
        $I->see('Plugins', 'h1');
        $this->dontSeeAnError($I);
    }

    public function aPasswordResetMailReachesMailpit(AcceptanceTester $I): void
    {
        // A login no earlier run has used, so the subject search finds this mail only.
        $login = 'reset' . bin2hex(random_bytes(3));
        $I->createUser($login, 'subscriber');

        $I->amOnPage('/wp-login.php?action=lostpassword');
        $I->waitForElement('#lostpasswordform', 20);
        $I->fillField('#user_login', $login);
        $I->click('#wp-submit');
        $I->waitForText('Check your email', 20);

        $I->wait(1);
        $emailId = $I->getLastEmailId();
        $I->assertEmailSubjectContains($emailId, 'Password Reset');
        $I->assertEmailTextContains($emailId, $login);
    }

    private function dontSeeAnError(AcceptanceTester $I): void
    {
        $I->dontSee('There has been a critical error');
        $I->dontSee('Fatal error');
        $I->dontSee('Warning: ');
        $I->dontSee('Error establishing a database connection');
    }
}
