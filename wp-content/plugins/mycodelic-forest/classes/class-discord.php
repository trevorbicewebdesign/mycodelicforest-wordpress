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
            'announcements' => 'https://discordapp.com/api/webhooks/1082841714479333376/Fo-ftkWpsgJ_vJgimZSTUrDM1i9lYHYd4JER2EC07hfDfTGNQ-mrpv9bRvVoT5sWHMzP',
        ];

        $url = $channels['announcements'];
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