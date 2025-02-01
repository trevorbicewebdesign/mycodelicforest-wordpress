<?php
/**
 * Plugin Name: Mycodelic Forest Core Plugin
 * Plugin URI: 
 * Version: 0.0.1
 * Description: Plugin for handling custom functionality for Mycodelic Forest
 * Author: Trevor Bice
 * Author URI: https://webdesign.trevorbice.com
 */


define('MYCO_CORE_ABS_PATH', WP_CONTENT_DIR . "/plugins/mycodelic-forest/");

require_once(MYCO_CORE_ABS_PATH . 'classes/class-core.php');
require_once(MYCO_CORE_ABS_PATH . 'classes/class-discord.php');
require_once(MYCO_CORE_ABS_PATH . 'classes/class-forms.php');
require_once(MYCO_CORE_ABS_PATH . 'classes/class-install.php');
require_once(MYCO_CORE_ABS_PATH . 'classes/class-profile.php');
require_once(MYCO_CORE_ABS_PATH . 'classes/class-roles.php');
require_once(MYCO_CORE_ABS_PATH . 'classes/class-shortcodes.php');
 
class MycodelicForestInit {
    public $version = '0.0.1';
    public $MycodelicForestCore;
    public $MycodelicForestDiscord;
    public $MycodelicForestForms;
    public $MycodelicForestInstall;
    public $MycodelicForestProfile;
    public $MycodelicForestRoles;
    public $MycodelicForestShortcodes;

    public function __construct() {
        $this->MycodelicForestCore = new MycodelicForestCore();
        $this->MycodelicForestDiscord = new MycodelicForestDiscord();
        $this->MycodelicForestForms = new MycodelicForestForms();
        $this->MycodelicForestInstall = new MycodelicForestInstall();
        $this->MycodelicForestProfile = new MycodelicForestProfile();
        $this->MycodelicForestRoles = new MycodelicForestRoles();
        $this->MycodelicForestShortcodes = new MycodelicForestShortcodes();
    }

    public function init()
    {
        $this->MycodelicForestCore->init();
        $this->MycodelicForestDiscord->init();
        $this->MycodelicForestForms->init();
        $this->MycodelicForestInstall->init();
        $this->MycodelicForestProfile->init();
        $this->MycodelicForestRoles->init();
        $this->MycodelicForestShortcodes->init();
    }
}

$MycodelicForestInit = new MycodelicForestInit();
$MycodelicForestInit->init();