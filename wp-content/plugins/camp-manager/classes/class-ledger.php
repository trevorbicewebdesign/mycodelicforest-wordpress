<?php

class CampManagerLedger
{
  
    public function __construct()
    {
        
    }

    public function init()
    {
        
    }

    public function sumUserCampDues($cmid)
    {
        global $wpdb;

        $query = "
            SELECT SUM(amount) 
            FROM {$wpdb->prefix}mf_ledger 
            WHERE cmid = %d AND (type = 'Camp Dues' OR type = 'Partial Camp Dues')
            "; 
        $query = $wpdb->prepare($query, $cmid);
        $total = $wpdb->get_var($query);

        return $total ? $total : 0;
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