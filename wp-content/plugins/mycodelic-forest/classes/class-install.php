<?php

class MycodelicForestInstall
{
    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        register_activation_hook( __FILE__, 'myplugin_install' );
    }

    public function myplugin_install() 
    {
        global $wpdb;
        
        // Table name with WP prefix
        $table_name = $wpdb->prefix . 'mf_profile';
        
        // Get the proper character set and collation for the table.
        $charset_collate = $wpdb->get_charset_collate();

        // Name
        // Email
        // Playa Name
        // Address 1
        // Address 2
        // City
        // State/Province
        // Zip/Postal Code
        // Country
        // Phone
        // About Me
        // Attended Burning Man
        // Years Attended
        
        // SQL statement to create the table.
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            playa_name varchar(100) NOT NULL,
            address_1 varchar(100) NOT NULL,
            address_2 varchar(100) NOT NULL,
            city varchar(100) NOT NULL,
            state varchar(100) NOT NULL,
            zip varchar(100) NOT NULL,
            country varchar(100) NOT NULL,
            phone varchar(100) NOT NULL,
            bio text NOT NULL,
            attended_burningman varchar(100) NOT NULL,
            years_attended varchar(100) NOT NULL,
            created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Include the file that contains dbDelta()
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        // Execute the query to create/update the table
        dbDelta( $sql );
    
    }
}