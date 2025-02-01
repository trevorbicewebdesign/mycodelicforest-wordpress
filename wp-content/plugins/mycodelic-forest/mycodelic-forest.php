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
require_once(MYCO_CORE_ABS_PATH . 'classes/class-shortcodes.php');
require_once(MYCO_CORE_ABS_PATH . 'classes/class-profile.php');
 
class MycodelicForestInit {
    public $version = '0.0.1';
    public $MycodelicForestCore;
    public $MycodelicForestDiscord;
    public $MycodelicForestForms;
    public $MycodelicForestShortcodes;
    public $MycodelicForestProfile;

    public function __construct() {
        $this->MycodelicForestCore = new MycodelicForestCore();
        $this->MycodelicForestDiscord = new MycodelicForestDiscord();
        $this->MycodelicForestForms = new MycodelicForestForms();
        $this->MycodelicForestShortcodes = new MycodelicForestShortcodes();
        $this->MycodelicForestProfile = new MycodelicForestProfile();
    }

    public function init()
    {
        $this->MycodelicForestCore->init();
        $this->MycodelicForestDiscord->init();
        $this->MycodelicForestForms->init();
        $this->MycodelicForestShortcodes->init();
        $this->MycodelicForestProfile->init();
    }
}

$MycodelicForestInit = new MycodelicForestInit();
$MycodelicForestInit->init();