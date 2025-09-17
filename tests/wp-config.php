<?php
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
define( 'DB_HOST', 'db' );

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
define( 'AUTH_KEY',          '53Kt)}:J&MpBgt$l-VR@M7%=]U?1^Ck5`mULFiP?nv;?`)bp15_cZfAn+x3)*NUn' );
define( 'SECURE_AUTH_KEY',   '$Z<}F=/XTs,4R 0rDMnbZy8b.4~f6W`Noi8Zsx[i%MC~Rz3>{]%OkK0<*FwPS]!J' );
define( 'LOGGED_IN_KEY',     'j*lgBqpL48uU~J/i^$s;k{t}^U>HOsCOPz4wt%(0]o.,3b;F,Xiff,hM(j}5/D,/' );
define( 'NONCE_KEY',         '-]G|?J.e9T+83m2#SJt-H15umzQ!!,4(9zfZs<r#y794AU*tLb{.(;<6,_3`CA-U' );
define( 'AUTH_SALT',         '0e[LQZSy9q,; ]hfdQ[Mx [@7AexV[mWy7,U=G,h:K :f]y{30%4jZu>#eE-<IfK' );
define( 'SECURE_AUTH_SALT',  'CPnI.N;!AUx~ai_je,2f;IY$L{x ktpY^v6~9WiK]P-l4hHWV{!0Op[r/@56aY_1' );
define( 'LOGGED_IN_SALT',    'g5a?iWt`z2T7bXVd|k,j%2~W|vS fjmgs0d5iw&%(qQCF)muCwe{ g$,.^?}%B{(' );
define( 'NONCE_SALT',        '*F A1Y~$aAU8|uuM7e&[xliKFq0u>3c){xc_EzerB0%z^3sh{|rwf1AMn ^>%9Kj' );
define( 'WP_CACHE_KEY_SALT', 'q%4=iq:^l`]~^F<42@p&#{dwi:|-9SWA{&TeeTyEcLBn;_%)IV+pdsXjTu`|nM[?' );


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
define( "WP_SITEURL", "http://wordpress" );
define( 'WP_HOME', 'http://wordpress' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
