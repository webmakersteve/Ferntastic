<?php
$db = Fn()->site(2)->settings->database;
$p = add_connection('patsys', $db);
use_connection('patsys');

//this script enters the post data for the "add item" form. needs to authenticate data-type and data-origin.
//it is suppose prior to this script that the user is permitted to do the requested action of adding this specific entry to the database. no permission is checked here.

$r = &$response['response'];
$s = &$response['status'];
//validate location

$idstring = '';
foreach ($_POST['items'] as $itemid) {
	$idstring.=$itemid.",";	
}
$idstring = preg_replace("#[,]+$#", "", $idstring);

$sql = "UPDATE `items` SET active = 0 WHERE id IN (%s)";
$sql = sprintf( $sql, $idstring );

query($sql);

$r = "Deleted items.";
$s = "ok";
?>