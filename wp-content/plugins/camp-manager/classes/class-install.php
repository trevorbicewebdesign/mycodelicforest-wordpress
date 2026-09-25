<?php

/**
 * Creates / upgrades the camp-manager tables.
 *
 * Schema is kept identical to production (generated from SHOW CREATE TABLE on
 * 2026-09-21). dbDelta() is idempotent: it adds missing tables and columns and
 * leaves existing data alone, so this runs safely on activation every time.
 *
 * `season` on the roster, ledger, receipts and budget category tables was added after that
 * snapshot (see CampManagerSeason::upgrade(), which also runs this and backfills old rows),
 * as were the mf_roles / mf_role_members tables (created by upgrade() for db version 3).
 */
class CampManagerInstall
{
    public function install()
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $this->create_mf_roster_table();
        $this->create_mf_budget_table();
        $this->create_mf_budget_category_table();
        $this->create_mf_budget_items_table();
        $this->create_mf_camp_dues_table();
        $this->create_mf_inventory_table();
        $this->create_mf_totes_table();
        $this->create_mf_tote_inventory_table();
        $this->create_mf_ledger_table();
        $this->create_mf_ledger_line_items_table();
        $this->create_mf_receipts_table();
        $this->create_mf_receipt_items_table();
        $this->create_mf_roles_table();
        $this->create_mf_role_members_table();
    }

    public function create_mf_roles_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_roles';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            season int NOT NULL,
            name varchar(255) NOT NULL DEFAULT '',
            description text,
            permissions varchar(255) NOT NULL DEFAULT '',
            sort_order int NOT NULL DEFAULT '0',
            lineage_id int DEFAULT NULL,
            parent_id int DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY season (season),
            KEY lineage_id (lineage_id),
            KEY parent_id (parent_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_role_members_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_role_members';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            role_id int NOT NULL,
            roster_id int NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY role_roster (role_id,roster_id),
            KEY roster_id (roster_id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_budget_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_budget';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT NULL,
            description text,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_budget_category_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_budget_category';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT NULL,
            description varchar(255) DEFAULT NULL,
            season int DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_budget_items_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_budget_items';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            category_id int DEFAULT NULL,
            name varchar(255) NOT NULL DEFAULT '',
            price float NOT NULL DEFAULT '0',
            quantity float NOT NULL DEFAULT '0',
            subtotal float NOT NULL DEFAULT '0',
            tax float NOT NULL DEFAULT '0',
            total float NOT NULL DEFAULT '0',
            priority int NOT NULL DEFAULT '0',
            link text,
            receipt_id int DEFAULT NULL,
            purchased int NOT NULL DEFAULT '0',
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_camp_dues_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_camp_dues';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            cmid int DEFAULT NULL,
            amount float DEFAULT NULL,
            platform varchar(255) DEFAULT NULL,
            date datetime DEFAULT NULL,
            transaction_id varchar(255) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_inventory_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_inventory';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            uuid int DEFAULT NULL,
            name varchar(255) NOT NULL DEFAULT '',
            manufacturer varchar(255) DEFAULT NULL,
            model varchar(255) DEFAULT '',
            description varchar(255) NOT NULL DEFAULT '',
            quantity int NOT NULL DEFAULT '1',
            photo varchar(255) NOT NULL DEFAULT '',
            location varchar(255) NOT NULL DEFAULT '',
            weight float NOT NULL DEFAULT '0',
            category varchar(255) NOT NULL DEFAULT '',
            category_name varchar(255) NOT NULL DEFAULT '',
            links varchar(255) DEFAULT NULL,
            amp float DEFAULT NULL,
            set_name varchar(255) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_ledger_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_ledger';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            amount float DEFAULT NULL,
            date datetime DEFAULT NULL,
            note text,
            link varchar(255) DEFAULT NULL,
            season int DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_ledger_line_items_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_ledger_line_items';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            ledger_id int NOT NULL,
            receipt_id int DEFAULT NULL,
            name varchar(255) DEFAULT NULL,
            cmid int DEFAULT NULL,
            amount decimal(10,2) NOT NULL,
            note text,
            type enum('Expense','Camp Dues','Partial Camp Dues','Donation','Sold Asset') DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_receipt_items_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_receipt_items';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            receipt_id int NOT NULL,
            name varchar(255) NOT NULL DEFAULT '',
            price float NOT NULL DEFAULT '0',
            quantity float NOT NULL DEFAULT '1',
            subtotal float NOT NULL DEFAULT '0',
            tax float DEFAULT '0',
            shipping float DEFAULT NULL,
            total float NOT NULL DEFAULT '0',
            category_id int DEFAULT NULL,
            link varchar(255) DEFAULT NULL,
            budget_item_id int DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_receipts_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_receipts';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            budget_item_id int DEFAULT NULL,
            date datetime DEFAULT NULL,
            subtotal float DEFAULT NULL,
            tax float DEFAULT NULL,
            shipping float DEFAULT NULL,
            total float DEFAULT NULL,
            reimbursed tinyint(1) DEFAULT NULL,
            cmid int DEFAULT NULL,
            donation tinyint(1) DEFAULT NULL,
            note varchar(255) DEFAULT NULL,
            store varchar(255) DEFAULT NULL,
            raw longtext,
            link varchar(255) DEFAULT NULL,
            season int DEFAULT NULL,
            PRIMARY KEY  (id),
            CONSTRAINT {$table}_chk_raw_json CHECK (json_valid(raw))
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_roster_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_roster';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            wpid int NOT NULL,
            low_income tinyint(1) DEFAULT NULL,
            ticket tinyint(1) DEFAULT NULL,
            fully_paid tinyint(1) DEFAULT NULL,
            season int DEFAULT NULL,
            fname varchar(255) DEFAULT NULL,
            lname varchar(255) DEFAULT NULL,
            playaname varchar(255) DEFAULT NULL,
            status enum('Confirmed','Very Maybe','Maybe','No','Dropped') DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            rsvp int DEFAULT NULL,
            sponsor_cmid int DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($sql);
    }

    public function create_mf_tote_inventory_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_tote_inventory';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            inventory_id int NOT NULL,
            tote_id int NOT NULL,
            quantity int NOT NULL DEFAULT '1',
            PRIMARY KEY  (id),
            KEY inventory_id (inventory_id),
            KEY tote_id (tote_id)
        ) $charset_collate;";
        // NOTE: production carries ON DELETE CASCADE foreign keys from this table to
        // mf_inventory/mf_totes. They are deliberately not declared here: dbDelta()
        // never adds constraints to existing tables, and the WordPress test framework
        // creates plugin tables as TEMPORARY tables, which cannot carry foreign keys
        // ("Cannot add foreign key constraint").
        dbDelta($sql);
    }

    public function create_mf_totes_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_totes';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table (
            id int NOT NULL AUTO_INCREMENT,
            name varchar(255) DEFAULT NULL,
            weight float DEFAULT NULL,
            uid varchar(50) DEFAULT NULL,
            status varchar(255) DEFAULT NULL,
            location varchar(255) DEFAULT NULL,
            size enum('Full','Half') DEFAULT 'Full',
            PRIMARY KEY  (id),
            UNIQUE KEY name (name),
            UNIQUE KEY uid (uid)
        ) $charset_collate;";
        dbDelta($sql);
    }
}
