<?php
/**
 * The wp-config.php CI uses: .github/workflows/codeception-test.yml copies it to the web root.
 *
 * The site's real wp-config.php is never committed (it holds the salts and whatever keys the
 * plugins wrote into it). This one holds nothing secret: the database is the runner's own
 * MySQL, and the salts only ever sign cookies for the synthetic users tests create.
 */

// Same three-way switch as the site's wp-config.php; see CLAUDE.md.
if (
    (isset($_SERVER['HTTP_X_TEST_REQUEST']) && $_SERVER['HTTP_X_TEST_REQUEST'] == 1)
    || (isset($_COOKIE['webdriver_test_request']) && $_COOKIE['webdriver_test_request'] == 1)
    || (php_sapi_name() == 'cli-server')
    || (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] == 'test')
) {
    define('DB_NAME', 'seed');
    $table_prefix = 'wp_';
    // Tests check this header and refuse to run without it.
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        header('X-WP-Test-Kit-Database: seed');
    }
} elseif (getenv('APPLICATION_ENV') == 'test' || getenv('WORDPRESS_DB_TEST_URL') !== false) {
    define('DB_NAME', 'test');
    $table_prefix = 'test_';
} else {
    // There is no developer site in CI; anything not marked as a test request still gets seed.
    define('DB_NAME', 'seed');
    $table_prefix = 'wp_';
}

define('DB_USER', 'root');
define('DB_PASSWORD', 'root');
define('DB_HOST', '127.0.0.1');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

define('AUTH_KEY',         'ci-only-auth-key');
define('SECURE_AUTH_KEY',  'ci-only-secure-auth-key');
define('LOGGED_IN_KEY',    'ci-only-logged-in-key');
define('NONCE_KEY',        'ci-only-nonce-key');
define('AUTH_SALT',        'ci-only-auth-salt');
define('SECURE_AUTH_SALT', 'ci-only-secure-auth-salt');
define('LOGGED_IN_SALT',   'ci-only-logged-in-salt');
define('NONCE_SALT',       'ci-only-nonce-salt');

define('WP_HOME', 'http://127.0.0.1');
define('WP_SITEURL', 'http://127.0.0.1');

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('WP_ENVIRONMENT_TYPE', 'local');
define('FS_METHOD', 'direct');
define('AUTOMATIC_UPDATER_DISABLED', true);
define('DISABLE_WP_CRON', true);

// WordPress builds its default sender from the host name, and "wordpress@127.0.0.1" is not an
// address PHPMailer accepts, so no mail would leave. Hooks can be registered before WordPress
// loads by filling $wp_filter; WordPress turns these entries into real hooks on boot.
$GLOBALS['wp_filter']['wp_mail_from'][10]['ci_sender'] = [
    'function'      => static function ($from) {
        return preg_match('/@[\d.]+$|@localhost$/', (string) $from) ? 'wordpress@seed.test' : $from;
    },
    'accepted_args' => 1,
];

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
