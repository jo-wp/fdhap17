<?php

define('WP_ENV', 'production');

/**
 * Credentials for CToutVert API
 */
define('CTOUTVERT_USERNAME', 'redpanda');
define('CTOUTVERT_PASSWORD', 'MAf#$ma$kECQt');
define('CTOUTVERT_ID_ENGINE', WP_ENV === 'development' ? 1704 : 1704);

/***
 * Credentials for APIDAE API
 */
define('APIDAE_KEY', 'JwunT6yN');
define('APIDAE_PROJECT_ID', '8926');


/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'fdhpa17' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '@6G:bQ_],jd:hO9a i00R]JX*b6!iOK0z2t4*^ou[YqrciLYlg}X5QfE}O}:*r;V' );
define( 'SECURE_AUTH_KEY',  '&T~jQ6Cvwv)?lt|u2;IGeq;=,k Yz=r?*ndMI+LutI(OZ3U,W7S);[vC>c67}P+L' );
define( 'LOGGED_IN_KEY',    'yAW=/)2l`IeAqzB}`__eUW7=S~)3<:SFQr#WL8<Qj Db&rlnHxftEOUED~wd!]jq' );
define( 'NONCE_KEY',        'iQ5b8C1k[=dljj8vQ-Fel*wJ|R#nrD>/V0aG)7Y3``28)#%r(nz]{,yJ{Y(DQ~`}' );
define( 'AUTH_SALT',        '>Dy7#4 THL12FwVrMWvGh6$WTg;V%9|7b;Jv][76`Qmxo]^5@i2O:tV+;e2yM-9D' );
define( 'SECURE_AUTH_SALT', 'Hh3|QsH26u}MV`}4vI<:,BLzx|W_CpK45sHC:ZW( }:7pn?B`q4g{4ktNhYT4DM!' );
define( 'LOGGED_IN_SALT',   '~kx)4RdZI0 Rg1OKSlyZhL1+U41u/)W%8@Uq#)-3ixqZ-`VAz;KgR]5z4I4NO&&)' );
define( 'NONCE_SALT',       'C,uG2a9!**uukc?jE;d0d6x${;uX :]V4~q&MnD_q&{^e@|noa-y%~^za039pBO2' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true );

define('AUTOMATIC_UPDATER_DISABLED', true);
define('WP_AUTO_UPDATE_CORE', false);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
