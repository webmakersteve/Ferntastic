<?php

interface ResourceDriver extends Driver {
	public function Get( $type ); //returns ResourceCategory
	public function LoadedTypes(); //returns array of loaded types
	
	public function toArray(); //returns Array
	public function LoadResources(); //void return
}

interface ResourceCategory extends Driver {
	public function toObject();
}