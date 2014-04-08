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
	header("Location: /index.php?continue=".urlencode($_SERVER['PHP_SELF']));
	exit;	
}

$reference = 'dt';
add_connection( $reference, 'db145083_patsys');
use_connection($reference);

$client = (!isset($_GET['clients']) || $_GET['clients'] < -1) ? 0 : $_GET['clients'];
$sqls = array();
$sformat_pre = 'SELECT id,displayname FROM `patsys_menu_categories` WHERE';

/*$ignore = isset($_GET['ignore']) ? $_GET['ignore'] : '';
$ignore = explode(";", $ignore);
$ignore = implode(",", $ignore);*/

if (false) {

} else {

	if (isset($_GET['query'])) {

		$sform = $sformat_pre.' active = 1 AND (displayname LIKE \'%%%1$s%%\') ORDER BY displayname DESC';
		$query = $_GET['query'];
		$sql = sprintf( $sform,  e($query) );

	} else {
		$sql = sprintf( $sformat_pre.' active = 1 ORDER BY displayname DESC');
	}

}

query($sql);

if (num_rows() > 0):

	$user_selects = array();
	while ($values = assoc() ) {

		$id = $values['id'];
		$user_selects[] = array('value' => $id, 'description' => $values['displayname']);

	}

else:

	die(
		json_encode(
			array(
				'status' => 'ok',
				'response' => array(
								array('value' => -1, 'description' => 'novals')
							)
				)
		)
	);

endif;



die(json_encode(array('status' => 'ok', 'response' => $user_selects)));





?>