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

class UPSError extends LogError {
	private $type = __CLASS__;	
}





class UPS {
	
/** UPS CONSTANTS 
-------------------------------------------------------------- */
	
const _accessKey = '1CB6F182633EE196';
const _username = '91ferns';
const _password = 'jEqEzayestej9craxEfuwraqu5UtA';
const _account = 'W9582X';

/** UPS PAYPAL CONSTANTS 
-------------------------------------------------------------- */

	private $postData = array();
	private $currPostData = '';
	
	public $packages = array('');
	
	function __construct() {
		
		
		
	}
	
	private $head = '<?xml version="1.0"?>';
	
	private function makeAuth() {
		
		ob_start(); ?>
		<AccessRequest xml:lang="en-US">  
		    <AccessLicenseNumber><?=UPS::_accessKey?></AccessLicenseNumber>  
		    <UserId><?=UPS::_username?></UserId>  
		    <Password><?=UPS::_password?></Password>  
		</AccessRequest>
		
		<?php return $this->head.ob_get_clean();
	}
	
	private function prepareData( $data=null) {
		ob_start(); //start OB buffering
echo $this->makeAuth(); echo $this->head;?>
<RatingServiceSelectionRequest>
	<Request>
		<TransactionReference>
			<CustomerContext>Rating and Service</CustomerContext>
			<XpciVersion>1.0</XpciVersion>
		</TransactionReference>
		<RequestAction>Rate</RequestAction>
		<RequestOption>Rate</RequestOption>
	</Request>
	<Shipment>
		<Shipper>
			<Name>Cola Vita</Name>
			<ShipperNumber>20269F</ShipperNumber>
			<Address>
				<AddressLine1>1 Runyons Ln</AddressLine1>
				<City>Edison</City>
				<StateProvinceCode>NY</StateProvinceCode>
				<PostalCode>08817</PostalCode>
				<CountryCode>US</CountryCode>
			</Address>
		</Shipper>
		<ShipTo>
			<?=$this->makeXML('ShipTo', $data['To']); ?>
		</ShipTo>
		<ShipFrom>
			<?=$this->makeXML( 'ShipFrom' ); ?>
		</ShipFrom>
		<Service>
			<Code>03</Code>
			<Description>Ground</Description>
		</Service>
        <?php if (!isset($data['Packages']) or count($data['Packages']) < 1) throw new UPSError('err_no_packages');
		else foreach($data['Packages'] as $package) {
			echo $this->makeXML( 'Package', $package); } ?>
	</Shipment>
</RatingServiceSelectionRequest><?php
		$contents = ob_get_clean();	
		return $contents;
	}
	
	static $_endpoints = array( 'rate' => "https://www.ups.com/ups.app/xml/Rate" );
	private $responses = array();
	private $last_response;
	
	
	public function request( $data=null ) {
		
		if ($data==null) throw new UPSError('nopkgdata');
		$PostParams = $this->prepareData( $data );
		$ch = curl_init();
		curl_setopt_array( $ch, array(
			CURLOPT_HTTPHEADER => array(
				"Content-type: text/xml",
				"Accept: text/xml",
				"Accept-Language: en_US"
			),
			CURLOPT_HEADER => 1,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_SSL_VERIFYPEER => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $PostParams,
			CURLOPT_URL => UPS::$_endpoints['rate']
		));
		$res = curl_exec( $ch );
		$this->responses[] = $res;
		return $this->parseXMLResponse($res);
	}
	
	private function SimpleXMLToStdClass( $doc ) {
		
		//die($doc->Response->TransactionReference->CustomerContext);
			
	}
	
	private function parseXMLResponse( $UPSResponse ) {
		//since we are grabbing the headers we need to separate the string into two parts.
		//the first part is the headers, which are separated by new line characters
		//the second part is the XML response data
		//this will help us do a more complete interpretation of the presented data
		
		//first we need to separate the string. find the index position of the XML startpoint
		$pos = strpos( $UPSResponse, "<?xml" );
		//now we should have it, but make sure there is a pos
		if ($pos >= 0 ) { //this means we got xml data
			$XML = substr( $UPSResponse, $pos );
			$headers = substr( $UPSResponse, 0, $pos );
		} else {
			$XML = "";
			$headers = $UPSResponse;	
		}
		
		//now that we have separated the two, we can move along to parse the XML and headers.
		//we parse the headers by breaking them by new line chars
		$headers = explode("\n", $headers );
		//now remove null values
		$headers = array_filter( $headers );
		//now we should have the headers all set
		
		
		if ($XML != "") { //if we have some XML Data
			libxml_use_internal_errors();
			$parsed = @simplexml_load_string( $XML );
				
			//now we have to convert this to an object; one day
			//$parsed = $this->SimpleXMLToStdClass( $parsed );
			
		}
		
		$r = array( 'Headers' => $headers, 'Response' => $parsed );
		$this->last_response = $r;
		return $r;
			
			
	}
	public function success() {
		
		//let's check if this was a success
		$xml = $this->last_response['Response'];
		$headers = $this->last_response['Headers'];
		$resCode = $xml->Response->ResponseStatusCode;
		
		if ($resCode == 1) {
			//hooray
			return $xml->RatedShipment->TotalCharges->MonetaryValue;	
		} else {
			$this->parse_errors();
			die('There was an error'.print_r($xml));	
		}
			
	}
	private function parse_errors() {}
	
	private function makeXML( $XMLToMake='default', $data=null ) {
		
		/* PACKAGE CODES
			Valid values:
			00 = UNKNOWN;
			01 = UPS Letter;
			02 = Package;
			03 = Tube;
			04 = Pak;
			21 = Express Box;
			24 = 25KG Box;
			25 = 10KG Box;
			30 = Pallet;
			2a = Small Express Box;
			2b = Medium Express Box;
			2c = Large Express Box
			*/
		
		ob_start();
		
		switch ($XMLToMake) {
			
			case 'Package':  ?>
			<Package>
                <PackagingType><Code>02</Code></PackagingType>
                <Dimensions>
                	<UnitOfMeasurement><Code>IN</Code></UnitOfMeasurement>
                    <Length><?=$data->Length?></Length>
                    <Width><?=$data->Width?></Width>
                    <Height><?=$data->Height?></Height>
                </Dimensions>
                <PackageWeight>
                	<UnitOfMeasurement><Code>LBS</Code></UnitOfMeasurement>
                    <Weight><?=$data->Weight?></Weight>
                </PackageWeight>
           	</Package>
			<?php break;
			
			case 'ShipFrom': ?>
			<CompanyName>Cola Vita</CompanyName>
            <Address>
                <AddressLine1>1 Runyons Ln</AddressLine1>
                <City>Edison</City>
                <StateProvinceCode>NJ</StateProvinceCode>
                <PostalCode>08817</PostalCode>
                <CountryCode>US</CountryCode>
            </Address>
			<?php break;
		
			default:
			case 'ShipTo':
			if (!$data['Line1']) throw new UPSError("invaddl1");
			if (!$data['City']) throw new UPSError('ups_err_no_city');
			if (!$data['State']) throw new UPSError('ups_err_no_state');
			if (!$data['ZIP']) throw new UPSError('ups_err_no_zip');
			?>
            <Address>
            	<AddressLine1><?=$data['Line1'];?></AddressLine1>
				<?php if (isset($data['Line2'])): ?>
                <AddressLine2><?=$data['Line2']?></AddressLine2>
                <?php endif; ?>
                <City><?=$data['City']?></City>
                <StateProvinceCode><?=$data['State']?></StateProvinceCode>
                <PostalCode><?=$data['ZIP']?></PostalCode>
                <CountryCode>US</CountryCode>
                <ResidentialAddressIndicator />
                
			</Address><?php
			break;	
			
			
			
		}
		
		return ob_get_clean();
			
	}
		
}

Fn::add('ups', function() {
	return new UPS();
});

function CartToPackageData( $CartData ) {

	
	if ($CartData instanceof Cart) $CartData = $CartData->data();
	if (!is_array($CartData)) return false;
	if (count($CartData) < 1 ) return false;
	
	//now we have gotten that out of the way. Let's start the conversion process!
	
	$return = array();
	foreach($CartData as $ItemData) {
		$temp = array();
		$l = isset($ItemData->itemData->length) ? $ItemData->itemData->length : 1;
		$w = isset($ItemData->itemData->width) ? $ItemData->itemData->width : 1;
		$h = isset($ItemData->itemData->height) ? $ItemData->itemData->height : 1;
		$we = isset($ItemData->wt) ? $ItemData->wt : 1;
		
		//let's fix the weight
		$we = number_format( ($we/16), 2 );
		
		$temp['Length'] = $l;
		$temp['Width'] = $w;
		$temp['Height'] = $h;
		$temp['Weight'] = $we; //we need the weight in pounds
		
		$return[] = (object) $temp;
		
	}
	
	return $return;
			
}