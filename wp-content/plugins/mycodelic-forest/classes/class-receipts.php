<?php
class MycodelicForestProfile
{

    protected $messages;
    protected $civicrm;
    public function __construct(MycodelicForestMessages $messages, MycodelicForestCiviCRM $civicrm)
    {
        $this->messages = $messages;
        $this->civicrm = $civicrm;
    }

    public function init()
    {
    }
    
}