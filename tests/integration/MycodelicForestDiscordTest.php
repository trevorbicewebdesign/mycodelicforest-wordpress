<?php

class MycodelicForestDiscordTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */

    protected function _before()
    {
        
    }

    public function testSendDiscordMessage()
    {

        $MycodelicForestDiscord = $this->make('MycodelicForestDiscord', []);

        $results = $MycodelicForestDiscord->sendMessage("This is a message");
        codecept_debug($results);
    }

}


