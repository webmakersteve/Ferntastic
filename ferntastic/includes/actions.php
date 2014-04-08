<?php

/**
 * Below are the functions that will be called in the themes. They involve setting titles, 
 * adding scripts, styles, etc. Is modeled after the wordpress framework.
 *
 * Actions are assigned using associative arrays. The list is as follows:
 * 
 */

$GLOBALS['actions'] = array(
				'init' => array(),
				'admin_init' => array(),
				'theme_init' => array(),
				'widgets_init' => array(),
				'plugins_init' => array(),
				'before_content_wrapper' => array(),
				'before_header' => array(),
				'header' => array(),
				'after_header' => array(),
				'before_container' => array(),
				'before_content' => array(),
				'content' => array(),
				'after_content' => array(),
				'after_container' => array(),
				'after_content_wrapper' => array(),
				'after_document' => array(),
				'before_document' => array(),
				'in_head' => array(),
				'shutdown' => array()
				);

/**
 * add_action sets an action in a function to a particular action string
 * $action is the action string
 * $function is a callable function
 * $prio is the order. Lower numbers first
 *
 */

function add_action( $action, $function, $prio=10 ) {
	global $actions;
	try {
		if (!is_callable( $function )) throw new LogError('add_action_nocall');
		if (!array_key_exists( $action, $actions )) 
			$actions[ $action ][$prio][] = $function;
		else 
			$actions[$action][$prio][] = $function;
	} catch (LogError $e) {
		$e->handleMe();
		return false;
	}
	return true;
}

/**
 * call_action is used in the template so that things can be organized. It is called by using the action string
 * @arg $action The string used to identify the called action. If it doesn't exist we can make it it
 */

function call_action( $action ) {
	
	global $actions;
	try {
		if (!array_key_exists( $action, $actions )) throw new LogError('call_action_unsetaction');
		$curr = $actions[$action]; //this will be the action array. It may have any number of indexes. We need to get an array of the keys
		if (count( $curr ) < 1) return true;
		$max = max(array_keys( $curr )); //max array int key
		$min = min (array_keys( $curr )); //min array int key
		for ($i = $min; $i<=$max; $i++) {
			if (isset($curr[$i]) and count($curr[$i]) > 0) {
				foreach ( $curr[$i] as $function ) {
					call_user_func( $function ); //Call the function attributed to the action
				}
			}
		}
		return true;
	} catch (NoLogError $e) {
		$e->handleMe();
		return false;	
	}
}

/* Now let's make a wrapper function for the shutdown action */
function actions_shutdown() {
	call_action( 'shutdown' );	
}