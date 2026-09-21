<?php

class HouseLedgerPages
{
    private $core;
    public function __construct(HouseLedgerCore $core)
    {
        $this->core = $core;
    }
    
    public function init()
    {
        /*
        add_action('admin_enqueue_scripts', function ($hook) {
            if (strpos($hook, 'house-ledger') !== false) {
                wp_enqueue_media();
                wp_enqueue_script(
                    'house-ledger-media-uploader',
                    plugin_dir_url(__FILE__) . 'js/media-uploader.js',
                    ['jquery'],
                    null,
                    true
                );
            }
        });
        */

        add_action('admin_menu', function () {
            global $menu;

            $separator_position = 5; // position in the menu array

            // Insert separator
            $menu[$separator_position] = [
                '',                            // Menu title
                'read',                        // Capability
                'separator-custom-top',        // Slug
                '',                            // Function (none)
                'wp-menu-separator'           // CSS class
            ];

            ksort($menu); // Reorder to maintain structure
        }, 999); // Run late to avoid being overwritten

        add_action('admin_menu', function () {
            global $menu;

            $separator_position = 7; // position in the menu array

            // Insert separator
            $menu[$separator_position] = [
                '',                            // Menu title
                'read',                        // Capability
                'separator-custom-top',        // Slug
                '',                            // Function (none)
                'wp-menu-separator'           // CSS class
            ];

            ksort($menu); // Reorder to maintain structure
        }, 999); // Run late to avoid being overwritten

        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'House Ledger',
                'House Ledger',
                'manage_options',
                'house-ledger',
                array($this, 'render_items_view_all_page'),
                'dashicons-admin-site',
                6
            );
        });

        add_action('admin_menu', function () {
            

            // Override default submenu label
            add_submenu_page(
                'house-ledger',
                'View All Inventory',       // Page title
                'View All Inventory',       // Submenu label
                'manage_options',
                'house-ledger-inventory',   // Same slug as top-level
                [$this, 'render_inventory_view_all_page']
            );

            add_submenu_page(
                'house-ledger',
                'Add New Inventory',       // Page title
                'Add New Inventory',       // Submenu label
                'manage_options',
                'house-ledger-add-inventory',   // Same slug as top-level
                [$this, 'render_inventory_add_page']
            );

             add_submenu_page(
                'house-ledger',
                'View All Known Items',       // Page title
                'View All Known Items',       // Submenu label
                'manage_options',
                'house-ledger-known-items',   // Same slug as top-level
                [$this, 'render_known_items_view_all_page']
            );

            add_submenu_page(
                'house-ledger',
                'Add New Known Item',       // Page title
                'Add New Known Item',       // Submenu label
                'manage_options',
                'house-ledger-add-known-item',   // Same slug as top-level
                [$this, 'render_known_items_add_page']
            );

        });

    }

    public function render_inventory_view_all_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/inventory_view_all_page.php');
    }

    public function render_inventory_add_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/inventory_add_page.php');
    }

    public function render_known_items_view_all_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/known_items_view_all_page.php');
    }

    public function render_known_items_add_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/known_items_add_page.php');
    }


}