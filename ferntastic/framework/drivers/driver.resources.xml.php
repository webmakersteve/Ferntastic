<?php

/**
if (!is_dir( $the_path) ) throw new ResourceError("Couldn't read directory: ".$the_path);
			if ($dir = opendir($the_path)) {
				
				while (($file = readdir($dir)) !== false) {
					if ( !preg_match("#[.]xml$#i", $file) ) {
						//these are for non xml files
					} else {
						
						//this gives us the new dirs
						//now we need to find the widget with the identifying 
						//now we have the data inside the widgets folder
						
						//load the xml sheet
						$xmlfp = $the_path . DIRECTORY_SEPARATOR . $file;
						
						if (file_exists($xmlfp)) {
				
							$contents = file_get_contents($xmlfp);
							if (!$contents) continue;
							$doc = new SimpleXMLElement($contents);
							
							$catname = preg_replace('#[.]xml$#i', '', basename($xmlfp));
							$t = &$tres->$catname;
							$atts = &$tres->attr->$catname;
							
							foreach ($doc->children() as $k => $child) {
								
								if ($k == "array") {
									
									$name = (string) $child->attributes()->name;
									$attr = (array) $child->attributes();
									
									//we need to find what type it is to get the next tags
									@$type = $attr['type'];
									$type = $attr['@attributes']['type'];
									
									$value = array();
									
									foreach ($child->$type as $subchild):
										$k = (string) $subchild->attributes()->key;
										$value[$k] = (string) $subchild;
									endforeach;
									
									$attributes = (object) $attr['@attributes'];
									$resValue = $value;
									
								} else {
									
									$name = (string) $child->attributes()->name;
									$attr = (array) $child->attributes();
									$value = (string) $child;
									
									$attributes = (object) $attr['@attributes'];
									$resValue = (string) $value;
									
								}
								
								$t->$name = $resValue;
								//supressing warning because I know it can be blank. But the check is then empty instead of isset which is better
								@$atts->$name = (object) $attributes;
								
							}
							
						} else throw new ResourceError("Couldn't read ".basename($xmlfp));
							
					}//end checking the folders
					
				} //end reading the directory
				
				if ($dir) closedir($dir);
				$this->pathsTried[] = $the_path;
				$this->resources[md5($the_path)] = $tres; //we want to use a token of the key to do this so we can reference the specific file later, too
				
			} else throw new ResourceError("Couldn't Read Resources"); //end opening the directory
			*/

class ResourceXMLDriver extends DefaultDriver implements ResourceDriver {
	
	public function get( $key );
	public function set();
	public function toArray();
		
}