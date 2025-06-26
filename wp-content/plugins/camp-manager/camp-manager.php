<?php
/**
 * Plugin Name: Camp Manager Plugin
 * Plugin URI: 
 * Version: 0.0.1
 * Description: Manager your burning man theme camp.
 * Author: Trevor Bice
 * Author URI: https://webdesign.trevorbice.com
 */


define('CAMPMANAGER_CORE_ABS_PATH', WP_CONTENT_DIR . "/plugins/camp-manager/");

// Classes
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-core.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-receipts.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-chatgpt.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-ledger.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-budgets.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-pages.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-roster.php');

// Tables
require_once(CAMPMANAGER_CORE_ABS_PATH . 'tables/class-receipt-list-table.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'tables/class-ledger-list-table.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'tables/class-roster-list-table.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'tables/class-budget-items-list-table.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'tables/class-budget-categories-list-table.php');
 
register_activation_hook(__FILE__, function () {
    require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-install.php');
    $installer = new CampManagerInstall();
    $installer->install();
});
class CampManagerInit {
    public $version = '0.0.1';
    public $CampManagerCore;
    public $CampManagerReceipts;
    public $CampManagerLedger;
    public $CampManagerChatGPT;

    public $CampManagerBudgets;
    public $CampManagerGoogleAPI;
    public $CampManagerRoster;

    public $CampManagerPages;

    public function __construct() {
        $this->CampManagerCore = new CampManagerCore();
        $this->CampManagerChatGPT = new CampManagerChatGPT($this->CampManagerCore);
        $this->CampManagerReceipts = new CampManagerReceipts($this->CampManagerCore, $this->CampManagerChatGPT);
        //$this->CampManagerGoogleAPI = new CampManagerGoogleAPI();
        $this->CampManagerLedger = new CampManagerLedger();
        $this->CampManagerBudgets = new CampManagerBudgets();
        $this->CampManagerRoster = new CampManagerRoster();
        $this->CampManagerPages = new CampManagerPages($this->CampManagerReceipts, $this->CampManagerRoster, $this->CampManagerCore);
        
    }

    public function init()
    {
        $this->CampManagerCore->init();
        $this->CampManagerReceipts->init();
        $this->CampManagerChatGPT->init();
        $this->CampManagerLedger->init();
        $this->CampManagerBudgets->init();
        $this->CampManagerPages->init();
        $this->CampManagerRoster->init();
    }
}

$CampManagerInit = new CampManagerInit();
$CampManagerInit->init();