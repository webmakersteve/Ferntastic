<?php

$root_fp = dirname(dirname(dirname(dirname(__FILE__))));
$root_fp .= "/fquery";
define('ABSPATH', $root_fp);

if (!defined('DB_HOST')) define('DB_HOST', "internal-db.s145083.gridserver.com");
if (!defined('DB_USER'))define('DB_USER', 'db145083_writer');
if (!defined('DB_PASSWORD'))define('DB_PASSWORD', '91ferns_writer');
if (!defined('DB_NAME'))define('DB_NAME', 'db145083_91ferns');

$funcFile = $root_fp."/php/functions.php";
include( $funcFile );

if (!is_logged_in()) {
	header("Location: /index.php?continue=".urlencode($_SERVER['REQUEST_URI']));
	exit;	
}

$reference = 'dt';
add_connection( $reference, 'db145083_dtaylor');
use_connection($reference);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : false;
$ajax = isset($_REQUEST['ajax']) ? $_REQUEST['ajax'] : false;

if ($id) {
	
	$sformat = "UPDATE `dt_uploads` SET active = 0 WHERE id = %d LIMIT 1";
	$sql = sprintf($sformat, e( $id ));
	
	$q = query($sql);
	
	if ($q) $response = array('status' => 'ok', 'response' => "Deleted item.");
	else $response = array('status' => 'nok', 'response' => "Couldn't delete item.");
	
} else $response = array('status' => 'nok', 'response' => "ID not set");

die(json_encode($response));

?>