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
define( 'DB_NAME', 'evetxwmy_WPJAK' );

/** Database username */
define( 'DB_USER', 'evetxwmy_WPJAK' );

/** Database password */
define( 'DB_PASSWORD', '9p<cjJOiccbvjx%bI' );

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
define( 'AUTH_KEY',          ' =ykoQv!fi3mGq9{`9kIG:(hn+Hqr}o:8_PN0%auu<W2l_A_7^ /=$~rI=/Rx1H[' );
define( 'SECURE_AUTH_KEY',   ',& $z/LCF(@Ey=F,~)#}kRH&UcGq;`D+X16P;GJ9kC1A,U}fAP+OMmE1$H2`}=Pe' );
define( 'LOGGED_IN_KEY',     'ZXv^&&/?M-C$%&SCj!+6v[NSGLXXRR@9!AZ&vd*Wy7}6>Mua~uxKX]DWh(F{-v3S' );
define( 'NONCE_KEY',         'efG9?2dqm[e#<vLEt,6l7riS~9tH%?BWjEXmT[U8~*AqjEn8_I8:~%~}+Q$LA.8I' );
define( 'AUTH_SALT',         'E~miD$=+y#i1%W;&p](:+zG0?lbDSobs/62uun[2<T58R8sFDXiLQ:N^tmK2A}o^' );
define( 'SECURE_AUTH_SALT',  'IIwtKD9lYbctA9y _AOYv+ JL[;_gs@<-Bd2pDRome]~#o-#rr?iOtWZ`v`k`<p-' );
define( 'LOGGED_IN_SALT',    'YCEvLPH*e xP8.70?s;Di]3[ApGf#A$_fG8DvuWvl%($KTE/rfvM92:vU[bU=a(,' );
define( 'NONCE_SALT',        '0.#}ZcG8uDbJO`|!^$xCLPF7K.8AK-Aqgy&]e-6Mj#(GG{JObBCnEbE#q#0^f@o}' );
define( 'WP_CACHE_KEY_SALT', 'Io!{$Xh(bnv-j~A?R!Dn5zauYSupQV,1pN8(-1Gh|AJ3E9o3gHoD&J eU-@aZYnF' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'staging_tFW_';

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
define( 'WP_DEBUG', false );


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
