<?php

class MycodelicForestForms
{
    public function __construct()
    {

    }

    public function init()
    {
        add_action('gform_after_submission_4', array($this, 'contactFormHandler'), 10, 2);
        add_action('gform_after_submission_3', array($this, 'registrationFormHandler'), 10, 2);
    }

    public function contactFormHandler($entry, $form)
    {
        die("Contact Form Was Submitted");

    }

    public function registrationFormHandler($entry, $form)
    {
        die("Registration Form Was Submitted");

    }

}
