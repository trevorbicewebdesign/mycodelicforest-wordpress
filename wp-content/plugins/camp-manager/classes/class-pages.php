<?php

class CampManagerPages
{
    private $receipts;
    private $core;
    private $budgets;
    private $roster;
    private $ledger;

    public function __construct(CampManagerReceipts $receipts, CampManagerBudgets $budgets, CampManagerRoster $roster, CampManagerLedger $ledger, CampManagerCore $core)
    {
        $this->receipts = $receipts;
        $this->budgets = $budgets;
        $this->roster = $roster;
        $this->ledger = $ledger;
        $this->core = $core;
    }
    
    public function init()
    {

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
                array($this, 'camp_manager_dashboard'),
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
                [$this, 'render_budget_items_page'],
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
                [$this, 'render_budget_items_page']
            );

            // Other submenus
            add_submenu_page(
                'camp-manager-budgets',
                'Add New Item',
                'Add New Item',
                'manage_options',
                'camp-manager-add-budget-item',
                [$this, 'render_add_budget_item_page']
            );

            add_submenu_page(
                'camp-manager-budgets',
                'Categories',
                'Categories',
                'manage_options',
                'camp-manager-budget-categories',
                [$this, 'render_budget_categories_page']
            );

            add_submenu_page(
                'camp-manager-budgets',
                'Add New Category',
                'Add New Category',
                'manage_options',
                'camp-manager-add-budget-category',
                [$this, 'render_add_budget_page']
            );
        });


        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'View All Members',     // Page title (shows in browser tab)
                'Roster',               // Menu title (shows in sidebar)
                'manage_options',
                'camp-manager-members',
                [$this, 'render_roster_page'],
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
                [$this, 'render_roster_page']
            );

            // Second submenu item
            add_submenu_page(
                'camp-manager-members',
                'Add a Member',
                'Add a Member',
                'manage_options',
                'camp-manager-add-member',
                [$this, 'render_add_member_page']
            );
        });

        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'View Ledger',     // Page title (shows in browser tab)
                'Ledger',               // Menu title (shows in sidebar)
                'manage_options',
                'camp-manager-ledger',
                [$this, 'render_ledger_page'],
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
                [$this, 'render_ledger_page']
            );

            // Second submenu item
            add_submenu_page(
                'camp-manager-ledger',
                'Add a Ledger Entry',
                'Add a Ledger Entry',
                'manage_options',
                'camp-manager-add-ledger',
                [$this, 'render_add_ledger_page']
            );
        });

        add_action('admin_menu', function () {
            // Top-level menu
            add_menu_page(
                'View Actuals',     // Page title (shows in browser tab)
                'Actuals',               // Menu title (shows in sidebar)
                'manage_options',
                'camp-manager-actuals',
                [$this, 'receipts_page'],
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
                [$this, 'receipts_page']
            );

            // Second submenu item
            add_submenu_page(
                'camp-manager-actuals',
                'Add a Receipt',
                'Add a Receipt',
                'manage_options',
                'camp-manager-add-receipt',
                [$this, 'render_receipt_form']
            );
        });
    }

    public function render_add_member_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/camp_manager_add_member.php');
    }

    public function render_budget_categories_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/camp_manager_categories_page.php');
    }

    public function render_add_budget_item_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/camp_manager_add_budget_item.php');
    }

    public function render_add_budget_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/camp_manager_add_category.php');
    }

    public function render_budget_items_page()
    {
        include(plugin_dir_path(__FILE__) . '../tmpl/camp_manager_budget_items_page.php');
    }

    public function render_roster_page()
    {
        include plugin_dir_path(__FILE__) . '../tmpl/camp_manager_roster_page.php';
    }

    public function render_ledger_page()
    {
        include plugin_dir_path(__FILE__) . '../tmpl/camp_manager_ledger_page.php';
    }

    public function render_add_ledger_page()
    {
        include plugin_dir_path(__FILE__) . '../tmpl/camp_manager_add_ledger.php';
    }

    public function receipts_page() 
    {
        include plugin_dir_path(__FILE__) . '../tmpl/camp_manager_receipts_page.php';
    }

    public function render_receipt_form()
    {
         include plugin_dir_path(__FILE__) . '../tmpl/camp_manager_add_receipt.php';
    }
    
    public function camp_manager_dashboard() {
        include plugin_dir_path(__FILE__) . '../tmpl/camp_manager_dashboard.php';
    }

}