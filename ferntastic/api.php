<?php

$root_fp = dirname(dirname(dirname(__FILE__)));
$root_fp .= "/fquery";
define('ABSPATH', $root_fp);

if (!defined('ABSPATH')) exit;
if (!defined('LOADABLE')) define('LOADABLE', true);

if (!defined('DB_HOST')) define('DB_HOST', "internal-db.s145083.gridserver.com");
if (!defined('DB_USER'))define('DB_USER', 'db145083_writer');
if (!defined('DB_PASSWORD'))define('DB_PASSWORD', '91ferns_writer');
if (!defined('DB_NAME'))define('DB_NAME', 'db145083_91ferns');

$funcFile = $root_fp."/php/functions.php";

define('RESOURCE_FP', '/nfs/c10/h02/mnt/145083/domains/testing.91ferns.com/res');

require( $funcFile );

if (!is_logged_in()) {header("Location: /?continue=".urlencode($_SERVER['REQUEST_URI']));exit;}
Fn()->load_extension('fquery');


//the Ferntastic Framework has been loaded! Now we need to do something with the API

$f = fQuery( 'accounts' );

$GLOBALS['response'] = array();

$t = $f->query('*')->each(function($data) {
	global $response;
	
	$h = $data->to_array();
	$response[]=$h;
	
});

if (isset($_GET['callback'])) die( $_GET['callback'] . "(" . json_encode($GLOBALS['response']) . ");" );

?>