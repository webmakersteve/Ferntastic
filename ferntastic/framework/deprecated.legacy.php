<?php

if (!function_exists('load_extension')){
function load_extension() {
	
		foreach (func_get_args() as $arg) {
			Fn()->load_extension( $arg );	
		}
		
	log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		
}
}
if (!function_exists('load')){
function load() {
	
		foreach (func_get_args() as $arg) {
			Fn()->load_extension( $arg );	
		}
		
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
	
}
}

if (!function_exists('set_error')) {
function set_error($val) {
		Fn()->sessions->set_error($val);		
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
}
}
if (!function_exists('last_post')) {
function last_post() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->last_post();		

}
}
if (!function_exists('clear_error')) {
function clear_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->clear_error();
}
}
if (!function_exists('is_error')) {
function is_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->is_error();		
}
}
if (!function_exists('echo_error')) {
function echo_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->echo_error();		
}
}


//end of ERRORS

if (!defined('DB_HOST')) define('DB_HOST', "localhost");
if (!defined('DB_USER'))define('DB_USER', '91ferns_db');
if (!defined('DB_PASSWORD'))define('DB_PASSWORD', '91ferns_admin');
if (!defined('DB_NAME'))define('DB_NAME', '91ferns_db');

//OMG USE KEYWORD

Fn::add('defaultsql', new MySQLEngine( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME ));

if (!function_exists('override_default_connection')) {
function override_default_connection( $dbname = DB_NAME, $dbuser = DB_USER, $dbpassword = DB_PASSWORD, $dbhost = DB_HOST ) {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->defaultsql = new MySQLEngine( $dbhost, $dbuser, $dbpassword, $dbname );		
}
}

if (!function_exists('echo_error')) {
function echo_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->echo_error();		
}
}

if (!function_exists('echo_error')) {
function echo_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->echo_error();		
}
}

if (!function_exists('echo_error')) {
function echo_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->echo_error();		
}
}

if (!function_exists('echo_error')) {
function echo_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->echo_error();		
}
}

if (!function_exists('echo_error')) {
function echo_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->echo_error();		
}
}
if (!function_exists('echo_error')) {
function echo_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->echo_error();		
}
}

if (!function_exists('echo_error')) {
function echo_error() {
		log_notice('deprecated', array('func'=>__FUNCTION__, 'vars'=>func_get_args()));
		return Fn()->sessions->echo_error();		
}
}
