<?php

/**
 * Twitter extension library. It tweets on behalf of 91ferns and receives tweets.
 * It uses the twitter API and abstracts it for easy use. Configuration in the beginning of the file.
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.1
 *
 */

//configuration
define( 'TWITTER_USER', '' );
define( 'TWITTER_PASSWORD', '' );
define( 'TWITTER_KEY', '' );
define( 'TWITTER_API_URL', '' );
//end configuration

class TwitterAccount {
	
	function __construct($user = TWITTER_USER, $password = TWITTER_PASSWORD, $key = TWITTER_KEY, $url = TWITTER_API_URL) {
		
	}
		
}

?>