<?php

interface ResourceDriver extends Driver {
	public function get( $key );
	public function set( $key, $value );
	public function toArray();
	public function LoadResources();
}