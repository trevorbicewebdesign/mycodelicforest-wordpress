<?php

class CampManagerPages
{
    private $receipts;
    private $core;
    private $roster;

    public function __construct(CampManagerReceipts $receipts, CampManagerRoster $roster, CampManagerCore $core)
    {
        $this->receipts = $receipts;
        $this->roster = $roster;
        $this->core = $core;
    }
    
    public function init()
    {
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

            // Submenu: Receipts
            add_submenu_page(
                'camp-manager',
                'View Receipts',
                'View Receipts',
                'manage_options',
                'camp-manager-view-receipts',
                [$this, 'receipts_page']
            );
          
            // need a new hidden menu page for the edit receipt page
            add_submenu_page(
                'camp-manager',
                'Edit Receipt',
                'Edit Receipt',
                'manage_options',
                'camp-manager-edit-receipt',
                [$this, 'render_receipt_form']
            );

            // add a link to the ledger page
            add_submenu_page(
                'camp-manager',
                'Ledger',
                'Ledger',
                'manage_options',
                'camp-manager-ledger',
                [$this, 'render_ledger_page']
            );

                // add page camp-manager-add-ledger
            add_submenu_page(
                'camp-manager',
                'Add Ledger Entry',
                'Add Ledger Entry',
                'manage_options',
                'camp-manager-add-ledger',
                [$this, 'render_add_ledger_page']
            );

            // add a link to the ledger page
            add_submenu_page(
                'camp-manager',
                'Roster',
                'Roster',
                'manage_options',
                'camp-manager-roster',
                [$this, 'render_roster_page']
            );

            add_submenu_page(
                'camp-manager',
                'Budget Items',
                'Budget Items',
                'manage_options',
                'camp-manager-budget-items',
                [$this, 'render_budget_items_page']
            );

            add_submenu_page(
                'camp-manager',
                'Add Budget Item',
                'Add Budget Item',
                'manage_options',
                'camp-manager-add-budget-item',
                [$this, 'render_add_budget_item_page']
            );

            add_submenu_page(
                'camp-manager',
                'Budget Categories',
                'Budget Categories',
                'manage_options',
                'camp-manager-budget-categories',
                [$this, 'render_budget_categories_page']
            );

            add_submenu_page(
                'camp-manager',
                'Add Budget Category',
                'Add Budget Category',
                'manage_options',
                'camp-manager-add-budget-category',
                [$this, 'render_add_budget_page']
            );

            // camp-manager-add-member
            add_submenu_page(
                'camp-manager',
                'Add Member',
                'Add Member',
                'manage_options',
                'camp-manager-add-member',
                [$this, 'render_add_member_page']
            );
        });
    }

    public function render_add_ledger_page()
    {
        // Check if the user has permission to manage options
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Add Ledger Entry</h1>
            <hr/>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="camp_manager_save_ledger_entry">
                <table class="form-table">
                    <tr>
                        <th><label for="ledger_date">Date</label></th>
                        <td>
                            <input type="date" name="ledger_date" id="ledger_date" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ledger_description">Description</label></th>
                        <td>
                            <input type="text" name="ledger_description" id="ledger_description" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ledger_amount">Amount</label></th>
                        <td>
                            <input type="number" name="ledger_amount" id="ledger_amount" class="regular-text" step="0.01" required>
                        </td>
                    </tr>
                </table>
            <?php submit_button('Add Ledger Entry'); ?>
            </form>
        </div>
        <?php   
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
        $budget_item = $budget_item_id ? $this->get_budget_item($budget_item_id) : null;
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
                        <th><label for="budget_item_amount">Amount</label></th>
                        <td>
                            <input type="number" step="0.01" name="budget_item_amount" id="budget_item_amount" value="<?php echo esc_attr($budget_item->amount ?? ''); ?>" required>
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
        $budget = $budget_id ? $this->get_budget($budget_id) : null;
        $is_edit = $budget !== null;
        $budget_id = $is_edit ? intval($budget->id) : 0;
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Budget Category' : 'Add New Budget Category'; ?></h1>

            <hr/>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="camp_manager_save_budget">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="budget_id" value="<?php echo esc_attr($budget_id); ?>">
                <?php endif; ?>
                <table class="form-table">
                    <tr>
                        <th><label for="budget_name">Name</label></th>
                        <td>
                            <input type="text" name="budget_name" id="budget_name" class="regular-text" value="<?php echo esc_attr($budget->name ?? ''); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="budget_description">Description</label></th>
                        <td>
                            <textarea name="budget_description" id="budget_description" rows="4" class="large-text"><?php echo esc_textarea($budget->description ?? ''); ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button($is_edit ? 'Update Budget' : 'Add Budget'); ?>
            </form>
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
        $table = new CampManagerRosterTable();
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
        $table = new CampManagerLedgerTable();
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
            <h1 class="wp-heading-inline">Ledger</h1>
            <a href="<?php echo admin_url('admin.php?page=camp-manager-add-ledger'); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <h3>The current amount is: <?php echo esc_html($table->get_total_amount()); ?></h3>
            <h3>We have collected <?php echo esc_html($table->get_total_camp_dues()); ?> in camp dues</h3>
            <form method="post">
                <?php
                $table->display();
                ?>
            </form>
        </div>
        <?php
    }
    public function edit_receipt_page()
    {
        $receipt_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$receipt_id) {
            wp_die('Invalid receipt ID.');
        }

        $receipt = $this->get_receipt($receipt_id);
        if (!$receipt) {
            wp_die('Receipt not found.');
        }

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Edit Receipt</h1>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="camp_manager_save_receipt">
                <input type="hidden" name="receipt_id" value="<?php echo esc_attr($receipt_id); ?>">
                <table class="form-table">
                    <tr>
                        <th><label for="store">Store</label></th>
                        <td><input type="text" name="store" class="regular-text" value="<?php echo esc_attr(str_replace('/', '', $receipt->store)); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="date">Date</label></th>
                        <td><input type="date" name="date" value="<?php echo esc_attr(date('Y-m-d', strtotime($receipt->date))); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="subtotal">Subtotal</label></th>
                        <td><input type="number" step="0.01" name="subtotal" value="<?php echo esc_attr($receipt->subtotal); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="tax">Tax</label></th>
                        <td><input type="number" step="0.01" name="tax" value="<?php echo esc_attr($receipt->tax); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="shipping">Shipping</label></th>
                        <td><input type="number" step="0.01" name="shipping" value="<?php echo esc_attr($receipt->shipping); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="total">Total</label></th>
                        <td><input type="number" step="0.01" name="total" value="<?php echo esc_attr($receipt->total); ?>"></td>
                    </tr>
                </table>

                <?php submit_button('Save Receipt'); ?>
            </form>
        </div>
        <?php
    }

    public function receipts_page() {
        $table = new CampManagerReceiptsTable();
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
            <h1 class="wp-heading-inline">Receipts</h1>
            <a href="<?php echo admin_url('admin.php?page=camp-manager-upload-receipt'); ?>" class="page-title-action">Add New</a>
            <hr class="wp-header-end">
            <form method="post">
                <?php
                $table->display();
                ?>
            </form>
        </div>
        <?php
    }

        public function render_receipt_form()
    {
        $receipt_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $receipt = $receipt_id ? $this->get_receipt($receipt_id) : null;
        $is_edit = $receipt !== null;
        $receipt_id = $is_edit ? intval($receipt->id) : 0;

        $store = $receipt->store ?? '';
        $date = $receipt->date ?? '';
        $subtotal = $receipt->subtotal ?? '';
        $tax = $receipt->tax ?? '';
        $shipping = $receipt->shipping ?? '';
        $total = $receipt->total ?? '';
        $items = $receipt->items ?? [];

        $form_action = admin_url('admin-post.php');
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo $is_edit ? 'Edit Receipt' : 'Upload Receipt'; ?></h1>

            <form method="post" enctype="multipart/form-data" action="<?php echo esc_url($form_action); ?>" id="receipt-form">
                <input type="hidden" name="action" value="<?php echo $is_edit ? 'camp_manager_save_receipt' : 'camp_manager_upload_and_save_receipt'; ?>">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="receipt_id" value="<?php echo esc_attr($receipt_id); ?>">
                <?php endif; ?>

                <table class="form-table">
                    <tr>
                        <th><label for="receipt_image">Receipt Image</label></th>
                        <td>
                            <input type="file" name="receipt_image" accept="image/*" id="receipt_image">
                            <button type="button" id="analyze-btn" class="button">Analyze Receipt</button>
                            <span id="analyze-spinner" class="spinner" style="float: none;"></span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="store">Store</label></th>
                        <td><input type="text" name="store" class="regular-text" value="<?php echo esc_attr(stripslashes($store)); ?>"></td>
                    </tr>
                    <tr>
                        <th><label for="date">Date</label></th>
                        <td>
                            <input type="text" name="date" value="<?php echo esc_attr($date ? date('m/d/Y', strtotime($date)) : ''); ?>" placeholder="mm/dd/yyyy" pattern="\d{2}/\d{2}/\d{4}">
                        </td>
                    </tr>
                </table>

                <h2 style="margin-top: 40px;">Items</h2>
                <table class="widefat striped" style="margin-bottom: 30px; table-layout: fixed; width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align: right;">Item Name</th>
                            <th style="text-align: right;">Category</th>
                            <th style="text-align: right;">Price</th>
                            <th style="text-align: right;">Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items ?? [] as $i => $item): ?>
                            <tr>
                                <td style="text-align: right;">
                                    <input type="text" name="items[<?php echo $i; ?>][name]" value="<?php echo esc_attr($item->name ?? ''); ?>" style="width: 100%;" />
                                </td>
                                <td style="text-align: right;">
                                    <?php $categories = $this->core->getItemCategories(); ?>
                                    <select name="items[<?php echo $i; ?>][category]" style="width: 100%;">
                                        <option value="">Please select a category</option>
                                        <?php foreach ($categories as $catKey => $catLabel): ?>
                                            <option value="<?php echo esc_attr($catKey); ?>"
                                                <?php selected(($item->category_id ?? '') === $catKey); ?>>
                                                <?php echo esc_html(ucfirst($catKey)); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                </td>
                                <td style="text-align: right;">
                                    <input type="text" name="items[<?php echo $i; ?>][price]" value="<?php echo esc_attr($item->price ?? ''); ?>" style="width: 100%;" />
                                </td>
                                <td style="text-align: right;">
                                    <input type="number" name="items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr($item->quantity ?? 1); ?>" style="width: 100%;" />
                                </td>
                                <td style="text-align: right;">
                                    <input type="text" name="items[<?php echo $i; ?>][subtotal]" value="<?php echo esc_attr($item->subtotal ?? ''); ?>" style="width: 100%;" />
                                </td>
                              
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                
                <!-- Totals -->
                <table style="width: 100%; max-width: 600px; margin-left: auto; font-size: 1.1em;">
                    <tr>
                        <td style="text-align: right; padding: 8px;"><strong>Subtotal:</strong></td>
                        <td style="text-align: right; width: 150px;">
                            <input type="text" name="subtotal" value="<?php echo esc_attr($subtotal ?? ''); ?>" class="small-text" style="width: 100%;" />
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding: 8px;"><strong>Tax:</strong></td>
                        <td style="text-align: right;">
                            <input type="text" name="tax" value="<?php echo esc_attr($tax ?? ''); ?>" class="small-text" style="width: 100%;" />
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding: 8px;"><strong>Shipping:</strong></td>
                        <td style="text-align: right;">
                            <input type="text" name="shipping" value="<?php echo esc_attr($shipping ?? ''); ?>" class="small-text" style="width: 100%;" />
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right; padding: 8px;"><strong>Total:</strong></td>
                        <td style="text-align: right;">
                            <input type="text" name="total" value="<?php echo esc_attr($total ?? ''); ?>" class="small-text" style="width: 100%;" />
                        </td>
                    </tr>
                </table>


                <?php submit_button($is_edit ? 'Update Receipt' : 'Save Receipt'); ?>
            </form>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
    $('#analyze-btn').on('click', function () {
        var fileInput = $('#receipt_image')[0];
        if (!fileInput.files.length) {
            alert('Please choose an image first.');
            return;
        }

        var formData = new FormData();
        formData.append('action', 'camp_manager_analyze_receipt');
        formData.append('receipt_image', fileInput.files[0]);

        $('#analyze-spinner').addClass('is-active');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            processData: false,
            contentType: false,
            data: formData,
            success: function (res) {
                $('#analyze-spinner').removeClass('is-active');
                if (res.success) {
                    populateFormWithReceipt(res.data);
                } else {
                    alert('Error: ' + res.data);
                }
            },
            error: function () {
                $('#analyze-spinner').removeClass('is-active');
                alert('Something went wrong.');
            }
        });
    });

    function populateFormWithReceipt(data) {
        $('input[name="store"]').val(data.store || '');
        $('input[name="date"]').val(data.date || '');
        $('input[name="subtotal"]').val(data.subtotal || '');
        $('input[name="tax"]').val(data.tax || '');
        $('input[name="shipping"]').val(data.shipping || '');
        $('input[name="total"]').val(data.total || '');

        const wrapper = $('#items-wrapper');
        wrapper.empty();

        if (Array.isArray(data.items)) {
            data.items.forEach((item, index) => {
                wrapper.append(renderItemRow(item, index));
            });
        }
    }

    function renderItemRow(item, index) {
        return `
            <div class="item-row">
                <input type="text" name="items[${index}][name]" value="${item.name || ''}" />
                <input type="number" name="items[${index}][price]" value="${item.price || 0}" />
                <input type="number" name="items[${index}][quantity]" value="${item.quantity || 1}" />
                <input type="number" name="items[${index}][subtotal]" value="${item.subtotal || 0}" />
                <select name="items[${index}][category]">
                    <option value="power">Power</option>
                    <option value="sojourner">Sojourner</option>
                    <option value="sound">Sound</option>
                    <option value="misc">Misc</option>
                </select>
                <button type="button" class="remove-item">Remove</button>
            </div>`;
    }

    $(document).on('click', '.remove-item', function () {
        $(this).closest('.item-row').remove();
    });

    // Optionally add an "Add Item" button
});

        </script>
        <?php
    }

    
    public function upload_receipt_page() {
        if (isset($_POST['receipt_submitted'])) {
            echo '<div class="notice notice-success"><p>✅ Receipt submitted successfully!</p></div>';
            echo '<h2>Submitted Data</h2>';
            echo '<pre style="background: #eef; padding: 1em;">';
            print_r($_POST);
            echo '</pre>';
        }

        $response_data = get_transient('camp_manager_last_receipt_data');
        // delete_transient('camp_manager_last_receipt_data');

        $categories = ['power', 'sojourner', 'sound', 'misc'];
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">Upload Receipt</h1>

            <!-- Upload Form -->
            <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="camp_manager_upload_receipt">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="receipt_image">Receipt Image</label></th>
                        <td><input type="file" name="receipt_image" accept="image/*" required></td>
                    </tr>
                </table>
                <?php submit_button('Analyze Receipt'); ?>
            </form>

            <?php if (!empty($response_data) && is_array($response_data)): ?>
                <hr style="margin: 40px 0;">
                <h2>Review & Submit Receipt</h2>

                <form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin-post.php'); ?>">
                    <input type="hidden" name="action" value="camp_manager_save_receipt">
                    <input type="hidden" name="receipt_submitted" value="1">
                    <input type='hidden' name='raw' value='<?php echo esc_attr(json_encode($response_data)); ?>'>

                    <table class="form-table">
                        <tr>
                            <th><label for="store">Store</label></th>
                            <td><input type="text" name="store" class="regular-text" value="<?php echo esc_attr($response_data['store'] ?? ''); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="date">Date</label></th>
                            <td><input type="date" name="date" value="<?php echo esc_attr($response_data['date'] ?? ''); ?>"></td>
                        </tr>
                    </table>

                    <h2 style="margin-top: 40px;">Items</h2>
                    <table class="widefat striped" style="margin-bottom: 30px; table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: right;">Item Name</th>
                                <th style="text-align: right;">Price</th>
                                <th style="text-align: right;">Qty</th>
                                <th style="text-align: right;">Subtotal</th>
                                <th style="text-align: right;">Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($response_data['items'] ?? [] as $i => $item): ?>
                                <tr>
                                    <td style="text-align: right;">
                                        <input type="text" name="items[<?php echo $i; ?>][name]" value="<?php echo esc_attr($item['name'] ?? ''); ?>" style="width: 100%;" />
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="text" name="items[<?php echo $i; ?>][price]" value="<?php echo esc_attr($item['price'] ?? ''); ?>" style="width: 100%;" />
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="number" name="items[<?php echo $i; ?>][quantity]" value="<?php echo esc_attr($item['quantity'] ?? 1); ?>" style="width: 100%;" />
                                    </td>
                                    <td style="text-align: right;">
                                        <input type="text" name="items[<?php echo $i; ?>][subtotal]" value="<?php echo esc_attr($item['subtotal'] ?? ''); ?>" style="width: 100%;" />
                                    </td>
                                    <td style="text-align: right;">
                                        <?php $categories = $this->core->getItemCategories(); ?>
                                        <select name="items[<?php echo $i; ?>][category]" style="width: 100%;">
                                            <option value="">Please select a category</option>
                                            <?php foreach ($categories as $catKey => $catLabel): ?>
                                                <option value="<?php echo esc_attr($catKey); ?>"
                                                    <?php selected(($item['category'] ?? '') === $catKey); ?>>
                                                    <?php echo esc_html(ucfirst($catKey)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Totals -->
                    <table style="width: 100%; max-width: 600px; margin-left: auto; font-size: 1.1em;">
                        <tr>
                            <td style="text-align: right; padding: 8px;"><strong>Subtotal:</strong></td>
                            <td style="text-align: right; width: 150px;">
                                <input type="text" name="subtotal" value="<?php echo esc_attr($response_data['subtotal'] ?? ''); ?>" class="small-text" style="width: 100%;" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 8px;"><strong>Tax:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="tax" value="<?php echo esc_attr($response_data['tax'] ?? ''); ?>" class="small-text" style="width: 100%;" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 8px;"><strong>Shipping:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="shipping" value="<?php echo esc_attr($response_data['shipping'] ?? ''); ?>" class="small-text" style="width: 100%;" />
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: right; padding: 8px;"><strong>Total:</strong></td>
                            <td style="text-align: right;">
                                <input type="text" name="total" value="<?php echo esc_attr($response_data['total'] ?? ''); ?>" class="small-text" style="width: 100%;" />
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 30px;">
                        <?php submit_button('Save Receipt'); ?>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    // Receipts page callback
    public function camp_manager_receipts_page() {
        echo '<div class="wrap"><h1>Receipts</h1><a href="/wp-admin/admin.php?page=camp-manager-upload-receipt" class="page-title-action">Upload Receipt</a>';

        $receipts = $this->get_receipts();

        if ($receipts && count($receipts) > 0) {
            echo '<div class="wp-list-table widefat fixed striped posts">';
            echo '<table>';
            echo '<thead>
                    <tr>
                        <th scope="col" class="manage-column column-title column-primary">Store</th>
                        <th scope="col" class="manage-column">Date</th>
                        <th scope="col" class="manage-column">Subtotal</th>
                        <th scope="col" class="manage-column">Tax</th>
                        <th scope="col" class="manage-column">Shipping</th>
                        <th scope="col" class="manage-column">Total</th>
                    </tr>
                  </thead>
                  <tbody>';
            foreach ($receipts as $receipt) {
                echo '<tr>';
                echo '<td class="column-title has-row-actions column-primary" data-colname="Store">'
                    . '<strong>' . esc_html($receipt->store) . '</strong>'
                    . '<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button>'
                    . '</td>';
                echo '<td data-colname="Date">' . esc_html($receipt->date) . '</td>';
                echo '<td data-colname="Subtotal">' . esc_html($receipt->subtotal) . '</td>';
                echo '<td data-colname="Tax">' . esc_html($receipt->tax) . '</td>';
                echo '<td data-colname="Shipping">' . esc_html($receipt->shipping) . '</td>';
                echo '<td data-colname="Total">' . esc_html($receipt->total) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table></div>';
        } else {
            echo '<p>No receipts found.</p>';
        }
        echo '</div>';
    }
    
    // Dashboard callback (optional)
    public function camp_manager_dashboard() {
        echo '<div class="wrap"><h1>Camp Manager Dashboard</h1></div>';
    }

}