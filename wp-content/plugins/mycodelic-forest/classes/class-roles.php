<?php

// Roles are a custom post type that will be used to manage and define the roles of the users in the system.
class MycodelicForestRoles {

    public function __construct() {
    
    }

    public function init() {
        add_action('init', [$this, 'add_roles']);
        // wp_login passes ($user_login, $user); we need the user object, not the login name.
        add_action('wp_login', [$this, 'onWpLogin'], 10, 2);
        add_action('rtcamp.google_user_logged_in', [$this, 'lastGoogleLogin'], 10, 2);
        // Block subscribers from accessing the admin.
        add_action('admin_init', [$this, 'blockAdminForSubscribers']);
    }

    public function blockAdminForSubscribers() {
        // Only redirect in admin area and not during AJAX requests. Subscribers holding a
        // Camp Manager role this season (see CampManagerRoles) still get into their parts of it.
        if ( is_admin() && ! wp_doing_ajax() && current_user_can('subscriber') && ! current_user_can('edit_posts') && ! current_user_can('camp_manager_access') ) {
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

    public function onWpLogin($user_login, $user)
    {
        if ($user instanceof WP_User) {
            $this->lastLogin($user->ID);
        }
    }

    public function lastGoogleLogin($user_wp, $user)
    {
        if (is_object($user_wp) && !empty($user_wp->ID)) {
            $this->lastLogin($user_wp->ID);
        }
    }

    public function lastLogin($user_id)
    {
        $user_id = (int) $user_id;
        if (!$user_id) {
            return;
        }
        update_user_meta($user_id, 'last_login', time());
        update_user_meta($user_id, 'wfls-last-login', time());
    }
}
