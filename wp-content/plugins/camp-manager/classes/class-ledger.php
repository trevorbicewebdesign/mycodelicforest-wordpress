<?php

class CampManagerLedger
{
  
    public function __construct()
    {
        
    }

    public function init()
    {
        
    }

    public function record_money_in($amount, $description = '', $date = null)
    {

        return [
            'type' => 'money_in',
            'amount' => $amount,
            'description' => $description,
            'date' => $date ?: date('Y-m-d H:i:s'),
        ];
    }

    public function record_money_out($amount, $description = '', $date = null)
    {

        return [
            'type' => 'money_out',
            'amount' => $amount,
            'description' => $description,
            'date' => $date ?: date('Y-m-d H:i:s'),
        ];
    }

}