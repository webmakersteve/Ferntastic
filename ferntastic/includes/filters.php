<?php

$GLOBALS['filters'] = array();

function add_filter( $name, $callback, $prio=10 ) {
	global $filters;
	try {
		if (!is_callable( $callback )) throw new LogError('add_filter_nocall');
		if (!array_key_exists( $filters, $actions )) 
			$filters[ $name ][$prio][] = $function;
		else 
			$filters[ $name ] = array();
			$filters[ $name ][$prio][] = $function;
	} catch (LogError $e) {
		$e->handleMe();
		return false;
	}
	return true;
}

function filter_exists( $name ) {
	global $filters;
	if ( isset( $filters[ $name ] ) and count( $filters[ $name ] ) > 0) return true;
	else return false;
}

function clear_filters( $name ) {
	global $filters;
	unset( $filters[ $name ] );
	$filters[ $name ] = array();
	return true;
}

function apply_filters( $name, $args ) {
	global $filters;	
	
	if (filter_exists( $name )) {
		$curr = $filters[ $name ];
		$max = max(array_keys( $curr )); //max array int key
		$min = min (array_keys( $curr )); //min array int key
		for ($i = $min; $i<=$max; $i++) {
			if (isset($curr[$i]) and count($curr[$i]) > 0) {
				foreach ( $curr[$i] as $function ) {
					//now we start output buffering
					ob_start();
					$return = call_user_func( $function, $args ); //Call the function attributed to the action
					echo $return;
					$returnString = ob_get_clean();
					ob_end_flush();
					if ( is_string( $return ) or is_null( $return ) ) $args = $returnString;
					else $args = $return;
				}
			}
		}
		return $args;	
	} else {
		return $args;	
	}
	
}
