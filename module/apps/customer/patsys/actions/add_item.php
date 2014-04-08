<?php

$db = Fn()->site(2)->settings->database;
$p = add_connection('patsys', $db);
use_connection('patsys');

//this script enters the post data for the "add item" form. needs to authenticate data-type and data-origin.
//it is suppose prior to this script that the user is permitted to do the requested action of adding this specific entry to the database. no permission is checked here.

$r = &$response['response'];
$s = &$response['status'];

$name = isset($_POST['Name']) ? $_POST['Name'] : false;
$category = isset($_POST['ItemEntry-Cat']) ? $_POST['ItemEntry-Cat'] : false;
$price = isset($_POST['ItemPrice']) ? $_POST['ItemPrice'] : false;
$wt = isset($_POST['Wt']) ? $_POST['Wt'] : 0;
$quantity = isset($_POST['Qt']) ? $_POST['Qt'] : false;
$description = isset($_POST['Desc']) ? $_POST['Desc'] : false;
$taxable = (isset($_POST['Tx']) and $_POST['tx'] == 1) ? $_POST['Tx'] : 0;
$ship = isset($_POST['Shp']) ? $_POST['Shp'] : 1;

//length width and height
$len = isset($_POST['Len']) ? $_POST['Len'] : 0;
$wid = isset($_POST['Wid']) ? $_POST['Wid'] : 0;
$ht = isset($_POST['Height']) ? $_POST['Height'] : 0;

$ItemData = array( 'width' => $wid, 'height' => $ht, 'length' => $len, 'wt' => $wt );
$ItemData = json_encode( $ItemData );

$r = "";

if (!$name or strlen($name) < 3) {
	$r = "Please enter a valid name";
} elseif (!$category) {
	$r = "Category not found.";
} else { //other than name and client
	//client validated up to here (in existing)
	
	if ((int) $category != $category) {	
		$sql = sprintf( $sform_clients_string, 0, e($client) );		
	} else {
		$sql = sprintf( "SELECT * FROM `patsys_item_taxonomy` WHERE active = 1 and id = %d LIMIT 1", $category );		
	}
	
	$q = query($sql);
	if (num_rows() < 1) {
		$r = "Category not found";
	} else {
		$assoc = assoc();
		$id = $assoc['id'];
		$category = $id;	
	}
	
	//client has thus been validated. Subclient must go through the same process
	if (strlen($r) < 1) { //if there were no problems found
		
		if (strlen($r) < 1):
			  
			if (!$quantity or (int) $quantity != $quantity or (int) $quantity < 1) {
				$r = "Please enter a quantity greater than one.";
			}
		
		endif;
	}
	
}

if (strlen($r) > 1) $s = "nok"; else {
	
	//manage uploads
	$uploads = isset($_POST['uploads']) ? $_POST['uploads'] : array();
	
	if (count($uploads)>0) {
		
		//we need to get the item ids from the uploads table
		$f = fQuery('patsys_uploads', "id,src[x&=?],item", $uploads);
		$IMAGES = array();
		if ($f->count>0) {
			$f->each(function($d) use (&$IMAGES) {
				$IMAGES[] = $d->id;
			});
			$IMAGES = array_unique($IMAGES);
		}
		
	} else $IMAGES = '';
	
	$s = "ok";
	//validated past this point
	//do the file stuff too
	$end_sql_format = "INSERT INTO `items` (name,description,price,entered,owned,imgs,categories,taxable,active,npb,weight,shipping_coefficient, data) VALUES ('%s', '%s', %f, %d, %d, '%s', %d, %d, 1, 0, %f, %.2f, %s)";
	$sql = sprintf($end_sql_format, e($name), e($description), $price, time(), $quantity, $IMAGES, $category, $taxable, $wt, $ship, $ItemData);
	
	query($sql);
	$id = insert_id();
	
	if ($f->count>0) $f->update(array('item' => $id));
	
	$r = "Success";
	
}

?>