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

require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-core.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-receipts.php');
require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-chatgpt.php');
//require_once(CAMPMANAGER_CORE_ABS_PATH . 'classes/class-googleapi.php');
 
class CampManagerInit {
    public $version = '0.0.1';
    public $CampManagerCore;
    public $CampManagerReceipts;
    public $CampManagerChatGPT;
    public $CampManagerGoogleAPI;

    public function __construct() {
        $this->CampManagerCore = new CampManagerCore();
        $this->CampManagerReceipts = new CampManagerReceipts();
        $this->CampManagerChatGPT = new CampManagerGPT();
        //$this->CampManagerGoogleAPI = new CampManagerGoogleAPI();
    }

    public function init()
    {
        $this->CampManagerCore->init();
        $this->CampManagerReceipts->init();
        $this->CampManagerChatGPT->init();
        //$this->CampManagerGoogleAPI->init();

    }
}

$CampManagerInit = new CampManagerInit();
$CampManagerInit->init();