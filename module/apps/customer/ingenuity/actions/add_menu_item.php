<?php

$db = Fn()->site(2)->settings->database;
$p = add_connection('patsys', $db);
use_connection('patsys');



//this script enters the post data for the "add item" form. needs to authenticate data-type and data-origin.
//it is suppose prior to this script that the user is permitted to do the requested action of adding this specific entry to the database. no permission is checked here.

$r = &$response['response'];
$s = &$response['status'];

$name = isset($_POST['ItemName']) ? $_POST['ItemName'] : false;
$desc = isset($_POST['ItemDescription']) ? $_POST['ItemDescription'] : false;
$cat = isset($_POST['Category']) ? $_POST['Category'] : false;
$price = isset($_POST['ItemPrice']) ? $_POST['ItemPrice'] : false;

$r = "";

if (!$name or strlen($name) < 3) {
	$r = "Please enter a valid name";
} elseif (!$desc) {
	$r = "Please write a description for that item.";
} elseif (!$price or floatval($price) <= 0.0) {
	$r = "Please enter a valid price";
} else { //other than name and client
	//we good? sort of
	//look up the category
	
	Fn()->load_extension('fquery');
	$f = fQuery('patsys_menu_categories', 'id,displayname[x=?]:limit(1)', e($cat));
	
	if ($f->count > 0) {
		$data = $f->this();
		$catID = $data->id;
	} else {
		//let's add this category
		$sql = "INSERT INTO `patsys_menu_categories` (nicename,displayname,active) VALUES (";
		
		//we need to nicify cat
		$nice = str_replace(" ", "_", strtolower($cat));
		$sql .= sprintf( "'%s', '%s', 1", e($cat), e($nice) );
		
		$sql .= ")";
		
		die($sql);
		
		query($sql);
		$catID = insert_id();
		
	}
	
	//we can only accomodate the first upload
	if (isset($_POST['uploads'])) {
		$uploads = $_POST['uploads'];
		$daUPLOAD = basename($uploads[0]);
	} else {
		$daUPLOAD = "";	
	}
	
	//lets add this to the db at this point methinks
	$sql = "INSERT INTO `patsys_menu_items` (price,description,name,category,imglink,active) VALUES (";
	$sql .= sprintf( " %.2f, '%s', '%s', %d, '%s', 1 )", 
					floatval( $price ),
					e($desc),
					e($name), 
					$catID,
					e($daUPLOAD) );
					
	query( $sql );
	
	$r = "Successfully added that menu item.";
	$s = "ok";
	
}

?>