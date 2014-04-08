<?php

class FilesController extends Controller {
	
	function index() {
		header("HTTP/10.4.4 403 Forbidden");
			die("Directory listing is unauthorized");
	}
	
	function get( $id=null ) {
		//we want no index page. In fact, we want it to throw a 404 but not implement the 404 page
		//this is a file and doing that would potentially cause significant problems
		//with lost file loading
		
		if ($id != null) {
		
			if ($fileContents = $this->File->find( $id )) {
				//this means it found the file
				//let's get the mime
				
				$mime = $this->File->get_mime( $id );
				$this->set( 'mime', $mime );
				$this->set( 'contents', $fileContents );
				#header("Cache-Control: public");
				#header(sprintf('Content-Type: %s', $mime));
				
				#header("Content-Description: File Transfer");
				#header("Content-Disposition: attachment; filename=$filename");
				#header("Content-Transfer-Encoding: binary");

				$this->getView( dirname(dirname(__FILE__)) . DS . 'views' . DS . 'view.file.index.php' );
				
				return true;	
			} else {
				header("HTTP/10.4.5 404 Not Found");
				die("File not found");	
			}
		}
		
		if ($id==null) {
			header("HTTP/10.4.5 404 Not Found");
			die("File not found");
		}
	}
	
		
}