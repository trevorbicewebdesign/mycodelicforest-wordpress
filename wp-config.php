<?php
//Begin Really Simple Security session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple Security cookie settings
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
//var_dump($_COOKIE);
if (
    (isset($_SERVER['HTTP_X_TEST_REQUEST']) && $_SERVER['HTTP_X_TEST_REQUEST'] == 1)
    || (isset($_COOKIE['webdriver_test_request']) && $_COOKIE['webdriver_test_request'] == 1)
    || (php_sapi_name() == 'cli-server')
    || (isset($_SERVER['APPLICATION_ENV']) && $_SERVER['APPLICATION_ENV'] == 'test')
) {
    define( 'DB_NAME', 'seed' );
    $table_prefix = 'wp_';
}
else if (getenv('WORDPRESS_DB_TEST_URL') !== false) {
	define( 'DB_NAME', 'test' );
    $table_prefix = 'test_';
} else {
    define( 'DB_NAME', 'local' );
    $table_prefix = 'wp_';
}

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         ']En])Pg)rGY2?2~{8!=Mb/ww@RYl3j&>XhYj+!Bdd,%P2}hm=h.df2C}!;uJsAN>' );
define( 'SECURE_AUTH_KEY',  'T,cD|Z1PRB4qSj02RzwAS9j$!qS[ln+Z9[cC0ud BYRmcpvLr6g<JhoPT<1/A[_H' );
define( 'LOGGED_IN_KEY',    'K^)G+o4)%Es/mkQ5uAxK>}XR2]L.B-gD&2/gaQ~UEUJDcx(9!M>>p5@r&8wPQBd~' );
define( 'NONCE_KEY',        'fjMK6oBSeRcl! .8Rs@He{e}dfbo$t7t_HJ>>[=1E){;BCUVGM0yc7`v$_I?{2|5' );
define( 'AUTH_SALT',        'EK]-IP_xh_ycCdicCj/O?IRTM90IG[^;8w9:S`_2pv9FAul;mT-,KX&Z`2i986!`' );
define( 'SECURE_AUTH_SALT', 'KR.O1[b5^ K @BB9<3n</M~p.<.B]WGPX]S9Uyx%|c87Q4;u6L.+jj:sb)y@nh}-' );
define( 'LOGGED_IN_SALT',   '2B0l7;4,}0y2hHf=N 7;FNk=Y7QtJsDLSDt_N_ndm5Xc5HnE*H}kim<E7Wp&`9ZX' );
define( 'NONCE_SALT',       'iXN6}Ean09|)h 9%6<}<9+,*WtE|#f:veZFCiv*!?WXJ5LnKEb#>YaPS~m vxTq ' );
/**#@-*/

/* Add any custom values between this line and the "stop editing" line. */

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */

// log errors
@ini_set('log_errors', 'On');
@ini_set('error_log', '/var/log/php_errors.log');
@ini_set('error_reporting', E_ALL);

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true);
define( 'WP_DEBUG_DISPLAY', false );

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
