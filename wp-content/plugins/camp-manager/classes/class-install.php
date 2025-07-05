<?php

class CampManagerInstall
{
    public function install()
    {
        $this->create_mf_receipts_table();
        $this->create_mf_receipt_items_table();
        $this->create_mf_roster();
        $this->create_mf_budget_table();
        $this->create_mf_ledger_table();
        $this->create_mf_ledger_line_items_table();
    }

    public function create_mf_roster()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_roster';

        $sql = "
        CREATE TABLE `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `wpid` INT(11) NOT NULL,
            `low_income` TINYINT(1) NULL DEFAULT NULL,
            `fully_paid` TINYINT(1) NULL DEFAULT NULL,
            `season` INT(11) NULL DEFAULT NULL,
            `fname` VARCHAR(255) NULL DEFAULT NULL,
            `lname` VARCHAR(255) NULL DEFAULT NULL,
            `playaname` VARCHAR(255) NULL DEFAULT NULL,
            `email` VARCHAR(255) NULL DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_mf_budget_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_budget';

        $sql = "
        CREATE TABLE `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) DEFAULT NULL,
            `description` TEXT DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_mf_budget_items_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_budget_items';

        $sql = "
        CREATE TABLE `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `budget_id` INT(11) NOT NULL,
            `name` VARCHAR(255) NOT NULL DEFAULT '',
            `price` FLOAT NOT NULL DEFAULT 0,
            `quantity` FLOAT NOT NULL DEFAULT 1,
            `subtotal` FLOAT NOT NULL DEFAULT 0,
            `total` FLOAT NOT NULL DEFAULT 0,
            `purchased` TINYINT(1) DEFAULT NULL,
            `level` INT(11) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_mf_ledger_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_ledger';

        $sql = "
        CREATE TABLE `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `amount` FLOAT DEFAULT NULL,
            `date` DATETIME DEFAULT NULL,
            `note` TEXT DEFAULT NULL,
            `link` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_mf_ledger_line_items_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_ledger_line_items';

        $sql = "
        CREATE TABLE `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `ledger_id` INT(11) NOT NULL,
            `receipt_id` INT(11) NULL DEFAULT NULL,
            `cmid` INT(11) DEFAULT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            `note` TEXT DEFAULT NULL,
            `type` ENUM('Expense', 'Camp Dues', 'Partial Camp Dues') NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_mf_receipts_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_receipts';

        $sql = "
        CREATE TABLE `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `store` VARCHAR(255) DEFAULT NULL,
            `date` DATETIME DEFAULT NULL,
            `subtotal` FLOAT DEFAULT NULL,
            `tax` FLOAT DEFAULT NULL,
            `shipping` FLOAT DEFAULT NULL,
            `total` FLOAT DEFAULT NULL,
            `reimbursed` TINYINT(1) DEFAULT NULL,
            `donation` TINYINT(1) DEFAULT NULL,
            `note` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function create_mf_receipt_items_table()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'mf_receipt_items';

        $sql = "
        CREATE TABLE `$table` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `receipt_id` INT(11) NOT NULL,
            `name` VARCHAR(255) NOT NULL DEFAULT '',
            `price` FLOAT NOT NULL DEFAULT 0,
            `quantity` FLOAT NOT NULL DEFAULT 1,
            `subtotal` FLOAT NOT NULL DEFAULT 0,
            `tax` FLOAT NOT NULL DEFAULT 0,
            `total` FLOAT NOT NULL DEFAULT 0,
            `category_id` INT(11) DEFAULT NULL,
            `link` VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}