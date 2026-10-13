<?php

declare(strict_types=1);

namespace Tests\Functional;

use Tests\Support\FunctionalTester;

/**
 * The site answers over HTTP from the seed database, without a browser.
 */
class SiteSmokeCest
{
    public function requestsAreServedFromTheSeedDatabase(FunctionalTester $I): void
    {
        $I->amOnPage('/wp-login.php');
        $I->seeResponseCodeIs(200);
        // wp-config.php says which database it picked; anything but seed is the site's own.
        $I->seeResponseCameFromTheSeedDatabase();
    }

    public function theHomePageLoads(FunctionalTester $I): void
    {
        $I->amOnPage('/');
        $I->seeResponseCodeIs(200);
        $I->seeResponseCameFromTheSeedDatabase();
        $I->dontSee('There has been a critical error');
        $I->dontSee('Fatal error');
    }

    public function theRestApiAnswers(FunctionalTester $I): void
    {
        $I->amOnPage('/?rest_route=/');
        $I->seeResponseCodeIs(200);
        $I->see('"namespaces"');
    }
}
