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
            'announcement-test' => 'https://discordapp.com/api/webhooks/1339765096884342804/NVis4YMB4RxPda2pBFefvjc2zHupI_dfTN2MUGwpfsLbklcWfTxBv7lGeAFziOEP7OC3',
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