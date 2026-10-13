<?php

declare(strict_types=1);

namespace Tests\Support\Helper;

use lucatume\WPBrowser\Module\WPBrowser;

class Functional extends \Codeception\Module
{
    /** A header of the last response, or null when it was not sent. */
    public function grabResponseHeader(string $name): ?string
    {
        $response = $this->getModule(WPBrowser::class)->client->getInternalResponse();

        return $response ? $response->getHeader($name) : null;
    }

    /**
     * The last response came from the seed database. wp-config.php says which database it
     * picked in a response header; anything else means the site's own database answered.
     */
    public function seeResponseCameFromTheSeedDatabase(): void
    {
        $this->assertSame(
            'seed',
            $this->grabResponseHeader('X-WP-Test-Kit-Database'),
            'The request did not reach the seed database: check the database switch in wp-config.php.'
        );
    }
}
