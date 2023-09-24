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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'evetxwmy_WPJAK');

/** Database username */
define('DB_USER', 'evetxwmy_WPJAK');

/** Database password */
define('DB_PASSWORD', '9p<cjJOiccbvjx%bI');

/** Database hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY', 'bd825dc9f9f651b3aa93b35e2dfd1d12697bd7ac1d22cddcf6376f55612deb8e');
define('SECURE_AUTH_KEY', '49014c0ff27345950d194ac283f9382ecd8528eae6bc0a88fe91f5391c64c66d');
define('LOGGED_IN_KEY', 'b5b3aae5e2a535a695fb814b22111f22a8fed9af01ec36e5dd2e2144e2cee2b2');
define('NONCE_KEY', '6455dfb62ee79072f041341c875a0375ce4ff976b286d171c78f6cae21a6aea1');
define('AUTH_SALT', '518b8c6fc78f16b956492aa15aef177c6c331afc193a5dd419d7689524b1b619');
define('SECURE_AUTH_SALT', '72a9508444c0548d71b4a51655d7ea7e65a2159be132c4f4867bd945c4247e6d');
define('LOGGED_IN_SALT', '3b8055866d72ed020c2ba58c8017709a8b11837a35c341b1caab3938016b71bb');
define('NONCE_SALT', '2e5164aacbdf3a30bbaf49dfefc8608bd086818f1a6de993a3e30c71bbfbf3db');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'tFW_';
define('WP_CRON_LOCK_TIMEOUT', 120);
define('AUTOSAVE_INTERVAL', 300);
define('WP_POST_REVISIONS', 20);
define('EMPTY_TRASH_DAYS', 7);
define('WP_AUTO_UPDATE_CORE', true);

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
