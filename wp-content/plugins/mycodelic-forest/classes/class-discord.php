<?php

class MycodelicForestDiscord
{
    public function __construct()
    {

    }

    public function init()
    {
      
    }

    public function sendMessage($message, $channel = null)
    {

        $channels = [
            'announcements' => getenv('DISCORD_WEBHOOK_ANNOUNCEMENTS'),
            'announcement-test' => getenv('DISCORD_WEBHOOK_ANNOUNCEMENT_TEST'),
        ];

        $url = $channels['announcement-test'];
        $data = array('content' => $message);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
}