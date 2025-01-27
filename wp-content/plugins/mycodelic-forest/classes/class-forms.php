<?php

class MycodelicForestForms {
    public function __construct()
    {
 
    }

    public function init()
    {
        add_action( 'gform_after_submission_4', array( $this, 'contactFormHandler' ), 10, 2 );
    }

    public function contactFormHandler($entry, $form)
    {
        die("Contact Form Was Submitted");
        
    }

}
