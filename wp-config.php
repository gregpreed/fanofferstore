<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache



// ** MySQL settings ** //
define('DB_NAME', 'bitnami_wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'libero00');

/** MySQL hostname */
define('DB_HOST', 'bitnamiwordpress.czjhfofnqmdk.us-west-2.rds.amazonaws.com:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('AUTH_KEY',         ';cuS<nud]!ptCx|%^;s- x#iSY8<o0?(j|_ueCVZBuH%|7%bHoL6@+{[FY5kXq}@');
define('SECURE_AUTH_KEY',  'S}D|dg4O.}U,!E@`99TEg.E-j*<,WJm&FbLNC H>lvC :w=Om4eCvKGq9?63h)v ');
define('LOGGED_IN_KEY',    '/vPT&kD9evkdD}4,Pg{oFs@;Cn~hx{]Qp]1bqgv!ug>Q@r-Z@gS||nPpME *<XI=');
define('NONCE_KEY',        'Sx#fz@OQC>Q? ?CqE^h|0qpsU-t~<A~L-|Iso$/ #kB3%xyvH)dag}hj2K^}0x$t');
define('AUTH_SALT',        '-Q{)%f/#+^:W.O)Ef_e+98xEMTmjRrPi[/SW-=>z{%yAyqzju&dVotx94ftuzFDH');
define('SECURE_AUTH_SALT', '1=n+@+,S_X99r{Vy[Mt+1 g+6X_U}Gb&L)P[ZWGKwYggxgCaZv/tPA4$`@q:.%;Q');
define('LOGGED_IN_SALT',   '+kS%}~ W?*:itRmJ059aE(V!S_v[lO.,[2DDt:Ac8T:<Kb,j2;<8b<QO,96uqr64');
define('NONCE_SALT',       'U!e+VY2Hb|hr_nxmfB?MbmUwBHWU o|`2i+,?HQ/EHh  n5GMwpm+IWSLue0BHCm');


$table_prefix = 'wp_10_';


 

define('WP_DEBUG', false); 



/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
