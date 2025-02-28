<?php

// Roles are a custom post type that will be used to manage and define the roles of the users in the system.
class MycodelicForestRoles {

    public function __construct() {
    
    }

    public function init() {
        add_action('init', [$this, 'add_roles']);
        add_action('wp_login', [$this, 'lastLogin']);
        add_action('rtcamp.google_user_logged_in', [$this, 'lastGoogleLogin'], 10, 2);
        // Block subscribers from accessing the admin.
        add_action('admin_init', [$this, 'blockAdminForSubscribers']);
    }

    public function blockAdminForSubscribers() {
        // Only redirect in admin area and not during AJAX requests.
        if ( is_admin() && ! defined('DOING_AJAX') && current_user_can('subscriber') ) {
            wp_redirect( home_url() );
            exit;
        }
    }

    public function add_roles()
    {
       // $this->addRoleMycodelicForestMember();

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

    public function lastGoogleLogin($user_wp, $user)
    {
        $this->lastLogin($user_wp->ID);
    }

    public function lastLogin($user_id)
    {
        update_user_meta($user_id, 'last_login', time());
        update_user_meta($user_id, 'wfls-last-login', time());
    }
}