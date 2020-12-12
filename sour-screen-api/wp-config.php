<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'sour_screen' );

/** MySQL database username */
define( 'DB_USER', 'robertguttersohn' );

/** MySQL database password */
define( 'DB_PASSWORD', 'sourscreen' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '+xKPI;70!=+Wg ?r~ThI^o)CF-G,aq1(/T=Rd/Gy&?6:cfb#U}$!x=c4ba#Q/d3a' );
define( 'SECURE_AUTH_KEY',  'IUsOVT#aw_%.dy&~`R4`j3Cj$:r^C_#MwXPb6sAMU$k6}lh~pmg>F:{T%VlmtLij' );
define( 'LOGGED_IN_KEY',    '~-#7!qGZ9eTY~UJBgqoJi[R$R2r7xq36H?EjDw&izbBd*.DdBA_Mx!dI5;tj)Rt|' );
define( 'NONCE_KEY',        'vC{|Vi.l-s;8}?n*!50A:/24kbdB_jBxtqG!1;4jPg<{[_C^1$Fdcsz!?$R9<AU(' );
define( 'AUTH_SALT',        '%B(.;/!CFdD&IK!><thX5WkCHVK@[T&=:Wm4!Q|H#:&e7;Y*I/}~MI0l%`BD/*oc' );
define( 'SECURE_AUTH_SALT', 'ZvoMUSu&Cluf suL^B@(vt7nyrq(%dNeF|qXm.p~J6;J_P!X?Dl~7/>xz~y7gSMi' );
define( 'LOGGED_IN_SALT',   '&s+.u(|N$5sE^>g6308QA2[Qr|uqw.&s*r`;85*WG<>mhn&KNE3Td}8Z;T!Ezv-5' );
define( 'NONCE_SALT',       'ZX=aF<tb#~7,Bi:+/l2R|@wN,9njd5pnCyl6661t3lSmL|##p&!kMVpp{G9dJ8q[' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
