<?php

/**
 * This file contains data for the configuration of the suite.
 * E.g. database data, resource data.
 * It then extrapolates it to variables and functions to be used later down the line
 *
 * @package 91ferns
 */

define('DB_HOST', "localhost");
define('DB_USER', 'root');
define('DB_PASSWORD', 'j3551c412');
define('DB_NAME', '91ferns');

define('DB_CHARSET', 'utf8');

define('AUTH_KEY',         '8WgV<7_vp{.D?C(i6NG?SdFUdMrSlscq;+-Y>{0?j){H4#SLE%|W)-:cg+S{>+|D');
define('SECURE_AUTH_KEY',  'L%o{WVYL5v+}VsK}d-^+DP67Z KzGKHWj ^_cd]j-aS?9Ol+DJWAQ/UB/Nj+|P*-');
define('LOGGED_IN_KEY',    'U,edG%S]x45qR#3,uz|x#<|^viH9jV|%]6TNmgX*jk e&+S2wxtPIsD!-.sjn(Ht');
define('NONCE_KEY',        '/Mti)F>p/[hW`dMdlHoIY *b c/2[]ZK-$|G6ysbhH3, Jj_h5NaT8cY4:>/ozws');
define('AUTH_SALT',        'Zoo+7+ZCC;d=slyzwB+JMJ>2x|OcrC3C.B{qPbAc0:d;9KNMNS0qt-0)3c2,B)lh');
define('SECURE_AUTH_SALT', 'k-F H2/],x?j{B/4ie+CoJ;Fr-Q.vO;QgZ@K9G DIa=~K%iO 1i.,%*tVWfG73C ');
define('LOGGED_IN_SALT',   'G[,>),ZT>_-9s1$SxwQ/@oZ_-.D]lR-XkM&hiV~sNL}o,?xw?_#eno@Aqu->7X+P');
define('NONCE_SALT',       'G|Y@|$|&awr9Z}6?~DJSV^&RI-`Pu# A(#Pl7zx{T4`A[-;_7G&w7/&?~77JAwLQ');

define('LOGIN_COOKIE_LENGTH', 604800);
define('PASSWORD_REQUEST_EXPIRE', 60*60*12);

date_default_timezone_set('America/New_York');


//Now we need to get the FernPath, i.e. directory with the resources and fQuery
define( "FRAMEWORKS", ABSPATH . "ferntastic/" );
define( "INC", ABSPATH . "includes/" );

require( ABSPATH . "settings.php" );