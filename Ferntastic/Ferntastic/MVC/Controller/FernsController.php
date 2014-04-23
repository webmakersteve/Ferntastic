<?php

namespace Ferntastic\MVC\Controller;

class FernsController extends Controller {
	protected $db;
	function __construct() {
		parent::__construct();
		/*
		$this->db = new MySQLEngine(
			DB_HOST,
			DB_USER,
			DB_PASSWORD,
			DB_NAME );	*/
	}
}