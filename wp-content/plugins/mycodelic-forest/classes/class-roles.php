<?php

// Roles are a custom post type that will be used to manage and define the roles of the users in the system.
class MycodelicForestRoles {

    public function __construct() {
    
    }

    public function init() {
        add_action('init', array($this, 'add_roles'));
        add_action('wp_login', array($this, 'lastLogin'));
    }

    public function add_roles()
    {
       $this->addRoleMycodelicForestMember();

    }

    public function addRoleMycodelicForestMember()
    {
        add_role(
            'mycodelic_forest_member',
            __('Mycodelic Forest Member', 'mycodelic-forest'),
            array(
                'read' => true,
                'edit_posts' => true,
                'delete_posts' => true,
                'upload_files' => true,
            )
        );
    }

    public function lastLogin($user_id)
    {
        update_user_meta($user_id, 'last_login', time());
    }
}