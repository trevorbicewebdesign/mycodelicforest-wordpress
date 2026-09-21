<?php
/**
 * Plugin Name: House Ledger
 * Plugin URI: 
 * Version: 0.0.1
 * Description: Keep inventory of the items in your house or pantry.
 * Author: Trevor Bice
 * Author URI: https://webdesign.trevorbice.com
 */


define('HOUSELEDGER_ABS_PATH', WP_CONTENT_DIR . "/plugins/house-ledger/");

// Classes
require_once(HOUSELEDGER_ABS_PATH . 'classes/class-core.php');
require_once(HOUSELEDGER_ABS_PATH . 'classes/class-inventory.php');
require_once(HOUSELEDGER_ABS_PATH . 'classes/class-known-items.php');
require_once(HOUSELEDGER_ABS_PATH . 'classes/class-pages.php');

require_once(HOUSELEDGER_ABS_PATH . 'tables/class-inventory-list-table.php');
require_once(HOUSELEDGER_ABS_PATH . 'tables/class-known-items-list-table.php');

class HouseLedgerInit {
    public $version = '0.0.1';
    public $HouseLedgerCore;
    public $HouseLedgerInventory;
    public $HouseLedgerKnownItems;
    public $HouseLedgerPages;
    public function __construct() {
        $this->HouseLedgerCore = new HouseLedgerCore();
        $this->HouseLedgerPages = new HouseLedgerPages($this->HouseLedgerCore);
        $this->HouseLedgerInventory = new HouseLedgerInventory($this->HouseLedgerCore);
        $this->HouseLedgerKnownItems = new HouseLedgerKnownItems($this->HouseLedgerCore);
    }

    public function init()
    {
        $this->HouseLedgerCore->init();
        $this->HouseLedgerPages->init();
        $this->HouseLedgerInventory->init();
        $this->HouseLedgerKnownItems->init();
    }
}

$HouseLedgerInit = new HouseLedgerInit();
$HouseLedgerInit->init();