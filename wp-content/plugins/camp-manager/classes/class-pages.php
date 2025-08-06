<?php

class CampManagerPages
{
    private $receipts;
    private $core;
    private $budgets;
    private $roster;
    private $ledger;

    private $inventory;

    public function __construct(CampManagerReceipts $receipts, CampManagerBudgets $budgets, CampManagerRoster $roster, CampManagerLedger $ledger, CampManagerCore $core, CampManagerInventory $inventory)
    {
        $this->receipts = $receipts;
        $this->budgets = $budgets;
        $this->roster = $roster;
        $this->ledger = $ledger;
        $this->core = $core;
        $this->inventory = $inventory;
    }
    
    public function init()
    {

        add_action('admin_enqueue_scripts', function ($hook) {
            if (strpos($hook, 'camp-manager') !== false) {
                wp_enqueue_media();
                wp_enqueue_script(
                    'camp-manager-media-uploader',
                    plugin_dir_url(__FILE__) . 'js/media-uploader.js',
                    ['jquery'],
                    null,
                    true
                );
            }
        });

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
                'Camp Manager',
                'Camp Manager',
                'manage_options',
                'camp-manager',
                array($this, 'render_dashboard_page'),
                'dashicons-admin-site',
                6
            );
        });

        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'View All Items',       // Page title
                'Budgets',                // Menu title in the sidebar
                'manage_options',
                'camp-manager-budgets',
                [$this, 'render_budget_items_view_all_page'],
                'dashicons-admin-site',
                6
            );

            // Override default submenu label
            add_submenu_page(
                'camp-manager-budgets',
                'View All Budgets',       // Page title
                'View All Budgets',       // Submenu label
                'manage_options',
                'camp-manager-budgets',   // Same slug as top-level
                [$this, 'render_budget_items_view_all_page']
            );

            // Other submenus
            add_submenu_page(
                'camp-manager-budgets',
                'Add New Item',
                'Add New Item',
                'manage_options',
                'camp-manager-add-budget-item',
                [$this, 'render_budget_item_add_page']
            );

            add_submenu_page(
                'camp-manager-budgets',
                'Categories',
                'Categories',
                'manage_options',
                'camp-manager-budget-categories',
                [$this, 'render_budget_category_view_all_page']
            );

            add_submenu_page(
                'camp-manager-budgets',
                'Add New Category',
                'Add New Category',
                'manage_options',
                'camp-manager-add-budget-category',
                [$this, 'render_budget_category_add_page']
            );
        });


        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'View All Members',     // Page title (shows in browser tab)
                'Roster',               // Menu title (shows in sidebar)
                'manage_options',
                'camp-manager-members',
                [$this, 'render_members_view_all_page'],
                'dashicons-admin-site',
                6
            );

            // First submenu item (linked to top-level page)
            add_submenu_page(
                'camp-manager-members',
                'View All Members',     // Page title
                'View All Members',     // Submenu title
                'manage_options',
                'camp-manager-members', // Must match parent slug to override default
                [$this, 'render_members_view_all_page']
            );

            // Second submenu item
            add_submenu_page(
                'camp-manager-members',
                'Add a Member',
                'Add a Member',
                'manage_options',
                'camp-manager-add-member',
                [$this, 'render_members_add_page']
            );
        });

        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'View Ledger',     // Page title (shows in browser tab)
                'Ledger',               // Menu title (shows in sidebar)
                'manage_options',
                'camp-manager-ledger',
                [$this, 'render_ledger_view_all_page'],
                'dashicons-admin-site',
                6
            );

            // First submenu item (linked to top-level page)
            add_submenu_page(
                'camp-manager-ledger',
                'View Ledger',     // Page title
                'View Ledger',     // Submenu title
                'manage_options',
                'camp-manager-ledger', // Must match parent slug to override default
                [$this, 'render_ledger_view_all_page']
            );

            // Second submenu item
            add_submenu_page(
                'camp-manager-ledger',
                'Add a Ledger Entry',
                'Add a Ledger Entry',
                'manage_options',
                'camp-manager-add-ledger',
                [$this, 'render_ledger_add_page']
            );
        });

        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'View Actuals',     // Page title (shows in browser tab)
                'Actuals',               // Menu title (shows in sidebar)
                'manage_options',
                'camp-manager-actuals',
                [$this, 'render_receipts_view_all_page'],
                'dashicons-admin-site',
                6
            );

            // First submenu item (linked to top-level page)
            add_submenu_page(
                'camp-manager-actuals',
                'View Actuals',     // Page title
                'View Actuals',     // Submenu title
                'manage_options',
                'camp-manager-actuals', // Must match parent slug to override default
                [$this, 'render_receipts_view_all_page']
            );

            // First submenu item (linked to top-level page)
            add_submenu_page(
                'camp-manager-actuals',
                'Summary',     // Page title
                'Summary',     // Submenu title
                'manage_options',
                'camp-manager-actuals-summary', // Must match parent slug to override default
                function() {
                    include plugin_dir_path(__FILE__) . '../tmpl/receipt_summary_page.php';
                }
            );

            // Second submenu item
            add_submenu_page(
                'camp-manager-actuals',
                'Add a Receipt',
                'Add a Receipt',
                'manage_options',
                'camp-manager-add-receipt',
                function() {
                    include plugin_dir_path(__FILE__) . '../tmpl/receipt_add_page.php';
                }   
            );
        });

        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'View Inventory', 
                'Inventory',
                'manage_options',
                'camp-manager-inventory',
                function() {
                    include(plugin_dir_path(__FILE__) . '../tmpl/inventory_view_all_page.php');
                },
                'dashicons-admin-site',
                6
            );
            add_submenu_page(
                'camp-manager-inventory',
                'Add Inventory',
                'Add Inventory',
                'manage_options',
                'camp-manager-add-inventory',
                function() {
                    include plugin_dir_path(__FILE__) . '../tmpl/inventory_add_page.php';
                }   
            );

            add_submenu_page(
                'camp-manager-inventory',
                'View Totes',
                'View Totes',
                'manage_options',
                'camp-manager-totes',
                function() {
                    include plugin_dir_path(__FILE__) . '../tmpl/totes_view_all_page.php';
                }
            );

            add_submenu_page(
                'camp-manager-inventory',
                'Add a Tote',
                'Add a Tote',
                'manage_options',
                'camp-manager-add-tote',
                function() {
                    include plugin_dir_path(__FILE__) . '../tmpl/totes_add_page.php';
                }
            );

            add_submenu_page(
                'camp-manager-inventory',
                'Add Tote Inventory',
                'Add Tote Inventory',
                'manage_options',
                'camp-manager-add-tote-inventory',
                function() {
                    include plugin_dir_path(__FILE__) . '../tmpl/tote_inventory_add_page.php';
                }
            );

            add_submenu_page(
                'camp-manager-inventory',
                'View Tote Inventory',
                'View Tote Inventory',
                'manage_options',
                'camp-manager-view-tote-inventory',
                function() {
                    include plugin_dir_path(__FILE__) . '../tmpl/tote_inventory_view_all_page.php';
                }
            );

        });

         add_action('admin_enqueue_scripts', function($hook) {
            if (strpos($hook, 'camp-manager') !== false) { // adjust this as needed for your page
                // Use CDN for latest version, or use WP core's select2 for older look
                wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
                wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
            }
        });

    }

    public function render_members_add_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/members_add_page.php');
    }

    public function render_budget_category_view_all_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/budget_category_view_all_page.php');
    }

    public function render_budget_item_add_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/budget_item_add_page.php');
    }

    public function render_budget_category_add_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/budget_category_add_page.php');
    }

    public function render_budget_items_view_all_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/budget_items_view_all_page.php');
    }

    public function render_members_view_all_page()
    {
        include plugin_dir_path(__FILE__) . '../tmpl/members_view_all_page.php';
    }

    public function render_ledger_view_all_page()
    {
        include plugin_dir_path(__FILE__) . '../tmpl/ledger_view_all_page.php';
    }

    public function render_ledger_add_page()
    {
        include plugin_dir_path(__FILE__) . '../tmpl/ledger_add_page.php';
    }

    public function render_receipts_view_all_page() 
    {
        include plugin_dir_path(__FILE__) . '../tmpl/receipts_view_all_page.php';
    }

    public function render_receipt_add_page()
    {
         include plugin_dir_path(__FILE__) . '../tmpl/receipt_add_page.php';
    }
    
    public function render_dashboard_page() {
        include plugin_dir_path(__FILE__) . '../tmpl/dashboard_page.php';
    }

}