<?php


/**
 * User cart. Used to connect the user to a database associated with the cart.
 * This allows data to be assigned to a user and the gives the user the ability to change 
 * Key value pairs. Although generally associated with a cart, it can provide other uses,
 * if one keeps an open mind
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.1
 *
 */

if (!function_exists('Fn')) die();

/**
 * CartError extends LogError
 *
 * CartError is the exception object thrown every time Cart crashes for any reason.
 * The errors return strings and database entries are defined in the errors.xml page
 * loaded using the resource module of Ferntastic library.
 *
 * @see LogError
 *
 */

class USPSError extends LogError {
	private $type = __CLASS__;	
}


/** PAYPAL CONSTANTS 
-------------------------------------------------------------- */

/** END PAYPAL CONSTANTS 
-------------------------------------------------------------- */

class USPS {
  	
	private $url;
	private $_credentials = array('USERID' => "66491FER2259");
	private $destination;
	
	
	public function __construct($type, $destination=10801) {
		if (strtolower($type) == "production") {
			$this->url="http://production.shippingapis.com/ShippingAPI.dll";	
		}
		
		$this->destination=$destination;
		
	}
	
	public function request( $method, $arr=array() ) {
		
		if ($method == "RateV4") {
		
			$xmlStr = array();
			$packageID = 0;
			
			if (count($arr) < 1) return false;
			
			foreach( $arr as $k=>$data) {
				if ($data instanceof Item) {
								
					$packageID++;
					$service = 'PRIORITY';
					$origin = '10019';
					
					$wt = $data->wt;
					
					//pounds and oz
					$lb = floor($wt / 16);
					$oz = $wt % 16;
					
					$size = 'REGULAR';
					$date = '8-Aug-2012';
				
$xmlStr[] = '<Package ID="$packageID">
	<Service>$service</Service>
	<ZipOrigination>$origin</ZipOrigination>
	<ZipDestination>$this->destination</ZipDestination>
	<Pounds>$lb</Pounds>
	<Ounces>$oz</Ounces>
	<Container/>
	<Size>$size</Size>
	<Machinable>true</Machinable>
	<ShipDate>$date</ShipDate>
</Package>';
				}
			}
			
			$xmlStr = implode("\n", $xmlStr);
			$xmlStr = $this->makeXML( $xmlStr );
			
			
			$APIRequest = $url."?API=RateV4&XML=".urlencode($xmlStr, 'RateV4Request'); 
			$s = new SimpleXMLElement( $APIRequest, null, true );
			
			$shippingPrice = 0;
			foreach($s->Package as $packData) {
				$x = (float) $packData->Postage->Rate;
				$shippingPrice=(float) $shippingPrice+$x;
			}
		
		}
		
	}
   
	protected function makeXML( $validXML, $requestType='RateV4Request' ) {
		
		$XMLString = array();
		
		$XMLString[] = '<'.$requestType.' USERID="'.$this->_credentials['USERID'].'">';
		$XMLString[] = '<Revision/>';
		$XMLString[] = $validXML;
		$XMLString[] = '</'.$requestType.'>';
		
		return implode("\n", $XMLString);
			
	}
   
}

?>