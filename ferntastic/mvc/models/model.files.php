<?php

class File extends Model {
	
	protected $dataSource = 'filesystem';
	protected $dir = null;
	
	function find( $filename ) {
		$dir = DATA_PATH . DS . 'files' . DS;
		
		//now let's find that file
		$path = $dir . $filename;
		if (file_exists($path)):
			$mime = $this->get_mime( $filename );
			if (isset($_GET['w']) and preg_match("#jpeg#i", $mime)) {
				$w = floor($_GET['w']);
				list($width, $height) = getimagesize($path);
				
				if ($width > $w) {
					//this means we can resize it
					//we need to use this to get the new height
					$ratio = $height/$width;
					$h = floor($ratio * $w); //new height
					$img = imagecreatetruecolor($w, $h);
					$image = imagecreatefromjpeg($path);
					
					imagecopyresampled($img,$image,0,0,0,0,$w,$h,$width,$height);
					
					$contents = imagejpeg($img, null, 100);
					
				} else $contents = file_get_contents($path);
				   
			} else $contents = file_get_contents($path);
			
			//we need to now return it
			return $contents;
			
		else:
			return false;
		endif;
			
		return true;
		
	}
	
	function get_mime( $filename ) {
		$dir = DATA_PATH . DS . 'files' . DS;
		
		//now let's find that file
		$path = $dir . $filename;
		if (file_exists($path)) {
			$result = new finfo();
			$x = $result->file($path, FILEINFO_MIME_TYPE);
			return $x;
		} else return false;
	}
	
		
}