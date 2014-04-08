<?php

interface DatabaseDriver {
	
	public function Insert();
	public function Update();
	public function Delete();
	public function Find();
		
}