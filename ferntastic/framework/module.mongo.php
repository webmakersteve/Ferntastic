<?php

$GLOBALS['mongodb'] = new Mongo();
$mdb = &$GLOBALS['mongodb'];

$collection = $mdb->test;
$c = $collection->find();

foreach ($c as $ce) {
	print_r($ce);
}

?>