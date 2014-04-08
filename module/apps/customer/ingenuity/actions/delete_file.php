<?php

$db = Fn()->site(2)->settings->database;
$p = add_connection('patsys', $db);
use_connection('patsys');
Fn()->load_extension('fquery');
//this script enters the post data for the "add item" form. needs to authenticate data-type and data-origin.
//it is suppose prior to this script that the user is permitted to do the requested action of adding this specific entry to the database. no permission is checked here.

$r = &$response['response'];
$s = &$response['status'];

//get the $_POST data
$fileid = isset($_POST['fileId']) ? $_POST['fileId'] : false;
if (!$fileid) {
	//0 or false
	$r = "There seems to have been a forum error.";
	$s = "nok";	
} else {
	
	$f = fQuery( 'patsys_uploads', 'id[x=?]:active[x=1]:limit(1)', $fileid );
	if ($f->count<1) {
		$r = "That item has already been deleted.";
		$s = "nok";	
	} else {
		$s = "ok";
		$r = "Successfully deleted item.";	
		
		$f->update(array('active' => 0));
		
	}
	
}


?>