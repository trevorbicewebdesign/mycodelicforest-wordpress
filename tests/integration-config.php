<?php
/**
 * Constants for the integration suite (WPLoader's `configFile`).
 *
 * WPLoader installs WordPress with a configuration of its own and never reads the site's
 * wp-config.php, so a constant your code expects to find there has to be given here. Test
 * values only: nothing in this file is a real key.
 *
 * It is loaded by the test process, not by the separate process that activates the plugins. A
 * plugin that needs wp-config.php itself while it activates (Really Simple SSL writes a key
 * into it) cannot run in this suite: leave it out with setup.sh --no-test.
 */

// defined('MY_PLUGIN_API_URL') || define('MY_PLUGIN_API_URL', 'http://127.0.0.1:9/never-called');
