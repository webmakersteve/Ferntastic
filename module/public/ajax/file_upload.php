<?php

$root_fp = $fff = dirname(dirname(dirname(dirname(__FILE__))));
$root_fp .= "/fquery";
define('ABSPATH', $root_fp);

if (!defined('DB_HOST')) define('DB_HOST', "internal-db.s145083.gridserver.com");
if (!defined('DB_USER'))define('DB_USER', 'db145083_writer');
if (!defined('DB_PASSWORD'))define('DB_PASSWORD', '91ferns_writer');
if (!defined('DB_NAME'))define('DB_NAME', 'db145083_91ferns');

$funcFile = $root_fp."/php/functions.php";
include( $funcFile );

if (!is_logged_in()) {header("Location: /?continue=".urlencode($_SERVER['REQUEST_URI']));exit;}
Fn()->load_extension('fquery');

$expl = explode("/",$_SERVER["REQUEST_URI"]);
$app = $expl[2];
define('APP_STR', $app);

$appsList = Fn()->account->apps;
$curr_app = $_SESSION['curr_app'];
//make sure current app is in the apps List

if (!in_array($curr_app, $appsList)) {
	unset($_SESSION['curr_app']);
	unset($_SESSION['curr_site']);	
	header("Location: /");
}

//now we have the app
//get the data from the permalink
$data = fQuery("apps", "id[x=?],*", e($curr_app)); //necessary step. must be fixed
$row = $GLOBALS['curr_app'] = $data->this();
$site = $row->site;

$GLOBALS['site']=Fn()->site($site);

if (is_logged_in()) {

	/**
	 * Handle file uploads via XMLHttpRequest
	 */
	
	function log_upload($path, $for = null) {
		global $site;
		$settings = $site->settings;
		$reference = 'dt';

		add_connection( $reference, $settings->database);
		use_connection($reference);
		
		if ($for == null) $for = isset($_GET['for']) ? $_GET['for'] : 0;
		$sformat = "INSERT INTO `".$settings->storage_table."` (mime, item, size, time, active, src) VALUES ('%s', %d, %d, %d, 1, '%s')";
		
		//get some information from the path of the upload
		$mime = mime_content_type($path);
		$size = 0;
		$time = time();
		
		$sql = sprintf($sformat, e($mime), e($for), e($size), $time, e($path));
		$q = query($sql);
		
		override_default_connection();
		return $q;
			
	}
	
	if(!function_exists('mime_content_type')) {
	
		function mime_content_type($filename) {
	
			$mime_types = array(
	
				'txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'php' => 'text/html',
				'css' => 'text/css',
				'js' => 'application/javascript',
				'json' => 'application/json',
				'xml' => 'application/xml',
				'swf' => 'application/x-shockwave-flash',
				'flv' => 'video/x-flv',
	
				// images
				'png' => 'image/png',
				'jpe' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'gif' => 'image/gif',
				'bmp' => 'image/bmp',
				'ico' => 'image/vnd.microsoft.icon',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'svg' => 'image/svg+xml',
				'svgz' => 'image/svg+xml',
	
				// archives
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed',
				'exe' => 'application/x-msdownload',
				'msi' => 'application/x-msdownload',
				'cab' => 'application/vnd.ms-cab-compressed',
	
				// audio/video
				'mp3' => 'audio/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',
	
				// adobe
				'pdf' => 'application/pdf',
				'psd' => 'image/vnd.adobe.photoshop',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',
	
				// ms office
				'doc' => 'application/msword',
				'rtf' => 'application/rtf',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',
	
				// open office
				'odt' => 'application/vnd.oasis.opendocument.text',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
			);
	
			$ext = strtolower(array_pop(explode('.',$filename)));
			if (array_key_exists($ext, $mime_types)) {
				return $mime_types[$ext];
			}
			elseif (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME);
				$mimetype = finfo_file($finfo, $filename);
				finfo_close($finfo);
				return $mimetype;
			}
			else {
				return 'application/octet-stream';
			}
		}
	}
	
	class qqUploadedFileXhr {
		/**
		 * Save the file to the specified path
		 * @return boolean TRUE on success
		 */
		function save($path) {    
			$input = fopen("php://input", "r");
			$temp = tmpfile();
			$realSize = stream_copy_to_stream($input, $temp);
			fclose($input);
			
			if ($realSize != $this->getSize()){            
				return false;
			}
			
			$target = fopen($path, "w");        
			fseek($temp, 0, SEEK_SET);
			stream_copy_to_stream($temp, $target);
			fclose($target);
			
			log_upload($path);
			
			return true;
			
		}
		function getName() {
			return $_GET['qqfile'];
		}
		function getSize() {
			if (isset($_SERVER["CONTENT_LENGTH"])){
				return (int)$_SERVER["CONTENT_LENGTH"];            
			} else {
				throw new Exception('Getting content length is not supported.');
			}      
		}   
	}
	
	/**
	 * Handle file uploads via regular form post (uses the $_FILES array)
	 */
	class qqUploadedFileForm {  
		/**
		 * Save the file to the specified path
		 * @return boolean TRUE on success
		 */
		function save($path) {
			$up1 = move_uploaded_file($_FILES['qqfile']['tmp_name'], $path); //uploaded first file		
			
			log_upload($path);
			
			return ($up1);
			
		}
		function getName() {
			return $_FILES['qqfile']['name'];
		}
		function getSize() {
			return $_FILES['qqfile']['size'];
		}
	}
	
	class qqFileUploader {
		private $allowedExtensions = array();
		private $sizeLimit = 10485760;
		private $file;
	
		function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
			$allowedExtensions = array_map("strtolower", $allowedExtensions);
				
			$this->allowedExtensions = $allowedExtensions;        
			$this->sizeLimit = $sizeLimit;
			
			$this->checkServerSettings();       
	
			if (isset($_GET['qqfile'])) {
				$this->file = new qqUploadedFileXhr();
			} elseif (isset($_FILES['qqfile'])) {
				$this->file = new qqUploadedFileForm();
			} else {
				$this->file = false; 
			}
		}
		
		private function checkServerSettings(){        
			$postSize = $this->toBytes(ini_get('post_max_size'));
			$uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
			
			if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
				$size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
				die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
			}        
		}
		
		private function toBytes($str){
			$val = trim($str);
			$last = strtolower($str[strlen($str)-1]);
			switch($last) {
				case 'g': $val *= 1024;
				case 'm': $val *= 1024;
				case 'k': $val *= 1024;        
			}
			return $val;
		}
		
		/**
		 * Returns array('success'=>true) or array('error'=>'error message')
		 */
		function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
			if (!is_writable($uploadDirectory)){
				return array('error' => "Server error. Upload directory isn't writable: ".$uploadDirectory);
			}
			
			if (!$this->file){
				return array('error' => 'No files were uploaded.');
			}
			
			$size = $this->file->getSize();
			
			if ($size == 0) {
				return array('error' => 'File is empty');
			}
			
			if ($size > $this->sizeLimit) {
				return array('error' => 'File is too large');
			}
			
			$pathinfo = pathinfo($this->file->getName());
			$filename = $pathinfo['filename'];
			
			$ext = $pathinfo['extension'];
			
			$SafeFile = (trim($filename));
			$SafeFile = str_replace("#", "No.", $SafeFile);
			$SafeFile = str_replace("$", "Dollar", $SafeFile);
			$SafeFile = str_replace("%", "Percent", $SafeFile);
			$SafeFile = str_replace("^", "", $SafeFile);
			$SafeFile = str_replace("&", "and", $SafeFile);
			$SafeFile = str_replace("*", "", $SafeFile);
			$SafeFile = str_replace("?", "", $SafeFile); 
			$SafeFile = str_replace("=", "", $SafeFile); 
			
			$SafeFile = basename($SafeFile);
			//$salt = '-'.time();
			$salt = '';
			//get extension
			
			$SafeFile = $SafeFile.$salt;
			
			$filename = $SafeFile;
			
			$file = $uploadDirectory . $SafeFile;
	
			if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
				$these = implode(', ', $this->allowedExtensions);
				return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
			}
			
			if(!$replaceOldFile){
				/// don't overwrite previous files that were uploaded
				while (file_exists($uploadDirectory . $filename . '.' . $ext)) {
					$filename .= rand(10, 99);
				}
			}
			
			
			
			if ($this->file->save($uploadDirectory . $filename . '.' . $ext)){
				return array('success'=>true, 'filename' => $uploadDirectory . $filename . '.' . $ext);
			} else {
				return array('error'=> 'Could not save uploaded file.' .
					'The upload was cancelled, or server error encountered');
			}
			
		}    
	}
	
	// list of valid extensions, ex. array("jpeg", "xml", "bmp")
	$allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'pdf', 'txt', 'rtf', 'html', 'xml', 'tiff');
	// max file size in bytes
	$sizeLimit = 10 * 1024 * 1024;
	
	if (!is_logged_in()) {
		
		$results = array('error' => 'You are not logged in', 'return' => 'https://secure.91ferns.com');
		
	} else {
	
		$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
		$result = $uploader->handleUpload($fff."/db.dennistaylortrucking.com/files/");
	
	}

} else {
	$result['status'] = "NOLOG";
	$result['response'] = "You are not logged in.";	
}

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
?>