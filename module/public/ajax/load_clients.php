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
add_connection( $reference, 'db145083_dtaylor');
use_connection($reference);

$client = (!isset($_GET['clients']) || $_GET['clients'] < -1) ? 0 : $_GET['clients'];
$sqls = array();
$sformat_pre = 'SELECT id,firstname,lastname, address_l1 FROM `dt_users` WHERE';

/*$ignore = isset($_GET['ignore']) ? $_GET['ignore'] : '';
$ignore = explode(";", $ignore);
$ignore = implode(",", $ignore);*/

if ($client == -1) {

	$query = isset($_GET['query']) ? $_GET['query'] : '';
	$sform = "SELECT name, id from `dt_places` WHERE (name LIKE '%s' OR id = %d) AND active = 1 ORDER BY name DESC LIMIT 5";
	$sql = sprintf($sform, e("%".$query."%"), $query);

} else {

	if (isset($_GET['query'])) {

		$sform_special = ' OR CONCAT_WS(\' \', dt_users.firstname, dt_users.lastname) LIKE \'%%%2$s%%\' OR CONCAT_WS(\', \', dt_users.lastname, dt_users.firstname) LIKE \'%%%2$s%%\'';
		$sform = $sformat_pre.' client = %1$d AND active = 1 AND (lastname LIKE \'%%%2$s%%\' OR id = %2$d OR firstname LIKE \'%%%2$s%%\''.$sform_special.') ORDER BY lastname DESC';
		$query = $_GET['query'];
		$sql = sprintf( $sform, e($client), e($query) );

	} else {
		$sql = sprintf( $sformat_pre.' client = %d AND active = 1 ORDER BY lastname DESC', e($client));
	}

}

query($sql);

if (num_rows() > 0):

	$user_selects = array();
	while ($values = assoc() ) {

		$fullname = ($client != -1) ? $values['lastname'] . ", " . $values['firstname'] : $values['name'];

		if (isset($values['address_l1']) and !empty($values['address_l1'])) $fullname .= " - ".$values['address_l1'];

		$id = $values['id'];
		$user_selects[] = array('value' => $id, 'description' => $fullname);

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