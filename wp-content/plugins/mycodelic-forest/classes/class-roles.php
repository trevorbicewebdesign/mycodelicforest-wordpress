<?php

// Roles are a custom post type that will be used to manage and define the roles of the users in the system.
class MycodelicForestRoles {

    public function __construct() {
    
    }

    public function init() {
        add_action('init', array($this, 'add_roles'));
    }

    public function add_roles()
    {

    }
}