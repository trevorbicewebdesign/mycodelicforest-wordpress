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
        // Check if the user has permission to manage options
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if($id > 0) {
            $member = $this->roster->getMemberById($id);
        }

        // we need to determine if we are editing or adding a new member
        $is_edit = $id > 0;

        // Prefill values if editing
        $fname = $is_edit && isset($member->fname) ? esc_attr($member->fname) : '';
        $lname = $is_edit && isset($member->lname) ? esc_attr($member->lname) : '';
        $playaname = $is_edit && isset($member->playaname) ? esc_attr($member->playaname) : '';
        $email = $is_edit && isset($member->email) ? esc_attr($member->email) : '';
        $wpid = $is_edit && isset($member->wpid) ? esc_attr($member->wpid) : '';

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Member' : 'Add New Member'; ?></h1>
            <hr/>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="camp_manager_save_member">
            <?php if ($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
            <?php endif; ?>
            <table class="form-table">
                <tr>
                <th><label for="member_fname">First Name</label></th>
                <td>
                    <input type="text" name="member_fname" id="member_fname" class="regular-text" required value="<?php echo $fname; ?>">
                </td>
                </tr>
                <tr>
                <th><label for="member_lname">Last Name</label></th>
                <td>
                    <input type="text" name="member_lname" id="member_lname" class="regular-text" required value="<?php echo $lname; ?>">
                </td>
                </tr>
                <tr>
                <th><label for="member_playaname">Playa Name</label></th>
                <td>
                    <input type="text" name="member_playaname" id="member_playaname" class="regular-text" value="<?php echo $playaname; ?>">
                </td>
                </tr>
                <tr>
                <th><label for="member_email">Email</label></th>
                <td>
                    <input type="email" name="member_email" id="member_email" class="regular-text" required value="<?php echo $email; ?>">
                </td>
                </tr>
                <tr>
                <th><label for="wpid">wpid</label></th>
                <td>
                    <?php
                    // Get all WordPress users
                    $users = get_users([
                    'fields' => ['ID', 'display_name', 'user_login', 'user_email']
                    ]);
                    ?>
                    <select name="wpid" id="wpid" class="regular-text" required>
                    <option value="">Select a WordPress user</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($wpid == $user->ID); ?>>
                        <?php echo esc_html($user->display_name . " ({$user->user_login} - {$user->user_email})"); ?>
                        </option>
                    <?php endforeach; ?>
                    </select>
                </td>
                </tr>
            </table>
            <?php submit_button($is_edit ? 'Edit Camp Member' : 'Add Camp Member'); ?>
            </form>
        </div>
        <?php
    }

    public function render_budget_categories_page()
    {
        $table = new CampManagerBudgetCategoriesTable();
        $table->process_bulk_action();
        $table->prepare_items();
        ?>
        <style>
           
        </style>
        <div class="wrap">
            <h1 class="wp-heading-inline">Budget Categories</h1>
            <a href="<?php echo admin_url('admin.php?page=camp-manager-add-budget-category'); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <h3>Must Have: <?php echo $table->get_must_have_total(); ?></h3>
            <h3>Should Have: <?php echo $table->get_should_have_total(); ?></h3>
            <h3>Could Have: <?php echo $table->get_could_have_total(); ?></h3>
            <h3>Nice to Have: <?php echo $table->get_nice_to_have_total(); ?></h3>
            <form method="post">
                <?php
                $table->display();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_add_budget_item_page()
    {
        $budget_item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $budget_item = $budget_item_id ? $this->budgets->getBudgetItem($budget_item_id) : null;
        $is_edit = $budget_item !== null;
        $budget_item_id = $is_edit ? intval($budget_item->id) : 0;
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Budget Item' : 'Add New Budget Item'; ?></h1>
            <hr/>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="camp_manager_save_budget_item">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="budget_item_id" value="<?php echo esc_attr($budget_item_id); ?>">
                <?php endif; ?>
                <table class="form-table">
                    <tr>
                        <th><label for="budget_item_name">Name</label></th>
                        <td>
                            <input type="text" name="budget_item_name" id="budget_item_name" class="regular-text" value="<?php echo esc_attr($budget_item->name ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_item_description">Description</label></th>
                        <td>
                            <textarea name="budget_item_description" id="budget_item_description" rows="4" class="large-text"><?php echo esc_textarea($budget_item->description ?? ''); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_item_category">Category</label></th>
                        <td>
                            <?php $categories = $this->core->getItemCategories(); ?>
                            <select name="budget_item_category" id="budget_item_category" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat_id => $cat): ?>
                                    <option value="<?php echo esc_attr($cat['id']); ?>" <?php selected(($budget_item->category_id ?? '') == $cat_id); ?>>
                                        <?php echo esc_html($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_item_price">Price</label></th>
                        <td>
                            <input type="number" step="0.01" name="budget_item_price" id="budget_item_price" value="<?php echo esc_attr($budget_item->price ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_item_quantity">Quantity</label></th>
                        <td>
                            <input type="number" step="1" name="budget_item_quantity" id="budget_item_quantity" value="<?php echo esc_attr($budget_item->quantity ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_item_subtotal">Subtotal</label></th>
                        <td>
                            <input type="number" step="0.01" name="budget_item_subtotal" id="budget_item_subtotal" value="<?php echo esc_attr($budget_item->subtotal ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_item_tax">Tax</label></th>
                        <td>
                            <input type="number" step="0.01" name="budget_item_tax" id="budget_item_tax" value="<?php echo esc_attr($budget_item->tax ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_item_total">Total</label></th>
                        <td>
                            <input type="number" step="0.01" name="budget_item_total" id="budget_item_total" value="<?php echo esc_attr($budget_item->total ?? ''); ?>" required>
                        </td>
                    </tr>

                    <tr>
                        <th><label for="budget_item_priority">Priority</label></th>
                        <td>
                            <input type="number" step="1" name="budget_item_priority" id="budget_item_priority" value="<?php echo esc_attr($budget_item->priority ?? ''); ?>" required>
                        </td>
                    </tr>
                    
                </table>

                <?php submit_button($is_edit ? 'Edit Budget Item' : 'Add Budget Item'); ?>
            </form>
        </div>
        <?php
    }

    public function render_add_budget_page()
    {
        $budget_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $budget = $budget_id ? $this->budgets->getBudgetCategory($budget_id) : null;

        $is_edit = $budget !== null;
        $budget_id = $is_edit ? intval($budget->id) : 0;
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Budget Category' : 'Add New Budget Category'; ?></h1>

            <hr/>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="camp_manager_save_budget_category">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="budget_id" value="<?php echo esc_attr($budget_id); ?>">
                <?php endif; ?>
                <table class="form-table">
                    <tr>
                        <th><label for="budget_category_name">Name</label></th>
                        <td>
                            <input type="text" name="budget_category_name" id="budget_category_name" class="regular-text" value="<?php echo esc_attr($budget->name ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_category_description">Description</label></th>
                        <td>
                            <textarea name="budget_category_description" id="budget_category_description" rows="4" class="large-text"><?php echo esc_textarea($budget->description ?? ''); ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button($is_edit ? 'Update Budget Category' : 'Add Budget Category'); ?>
            </form>

            <hr>

            <h2>Budget Items in this Category</h2>
            <?php
            $table = new CampManagerBudgetItemsTable($budget_id);
            $table->process_bulk_action();
            $table->prepare_items();
            ?>
            <style>
                .wp-list-table .column-store       { width: 40%; }
                .wp-list-table .column-date        { width: 15%; }
                .wp-list-table .column-total       { width: 10%; text-align: right; }
                .wp-list-table .column-subtotal    { width: 10%; text-align: right; }
                .wp-list-table .column-tax         { width: 10%; text-align: right; }
                .wp-list-table .column-shipping    { width: 15%; text-align: right; }
            </style>

            <a href="<?php echo admin_url('admin.php?page=camp-manager-add-budget-item'); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <form method="post">
                <?php
                $table->display();
                ?>

        </div>
        <?php
    }

    public function render_budget_items_page()
    {
        $table = new CampManagerBudgetItemsTable();
        $table->process_bulk_action();
        $table->prepare_items();
        ?>
        <style>
            .wp-list-table .column-store       { width: 40%; }
            .wp-list-table .column-date        { width: 15%; }
            .wp-list-table .column-total       { width: 10%; text-align: right; }
            .wp-list-table .column-subtotal    { width: 10%; text-align: right; }
            .wp-list-table .column-tax         { width: 10%; text-align: right; }
            .wp-list-table .column-shipping    { width: 15%; text-align: right; }
        </style>
        <div class="wrap">
            <h1 class="wp-heading-inline">Budget Items</h1>
            <a href="<?php echo admin_url('admin.php?page=camp-manager-add-budget-item'); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <form method="post">
                <?php
                $table->display();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_roster_page()
    {
        $table = new CampManagerRosterTable($this->ledger);
        $table->process_bulk_action();
        $table->prepare_items();
        ?>
        <style>
            .wp-list-table .column-store       { width: 40%; }
            .wp-list-table .column-date        { width: 15%; }
            .wp-list-table .column-total       { width: 10%; text-align: right; }
            .wp-list-table .column-subtotal    { width: 10%; text-align: right; }
            .wp-list-table .column-tax         { width: 10%; text-align: right; }
            .wp-list-table .column-shipping    { width: 15%; text-align: right; }
        </style>
        <div class="wrap">
            <h1 class="wp-heading-inline">Roster</h1>
            <a href="<?php echo admin_url('admin.php?page=camp-manager-add-member'); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <form method="post">
                <?php
                $table->display();
                ?>
            </form>
        </div>
        <?php
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