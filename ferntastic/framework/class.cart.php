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

class CartError extends LogError {
	private $type = __CLASS__;	
}

Fn()->load_extension('fquery'); //This file makes use of the fQuery lib. 
//this class requires the cart table



/**
 * 
 *
 * @author Stephen Parente (sparente@91ferns.com)
 * @package php_extensions
 * @version 0.1
 *
 * <code>
 * <?php
 *
 * ?>
 * </code>
 *
 */


class Cart {
	
	static $lastcart; 
	private $rowdata;
	public $data;
	
	private function destroy_bad_values() {
		
		//print_r(count($this->data()));
		
		if (count($this->data()) > 0) {
			
			//destroy the bad values
			$x = $this->data();
			if ( (count($x) > 0) and is_array($x)):
				foreach ($x as $k => $data) {
					if (empty($data->name) or empty($data->price) or empty($data->wt)) unset($x[$k]);	
				}
					
				if (md5(serialize($this->data())) != md5(serialize($x))) $this->data->update(array('data' => serialize($x)));
			endif;
			
		}
			
	}
	
	function total($strResult=null) {
		
		$this->destroy_bad_values();
		//print_r($this->data());exit;
		
		if ($strResult==null)$strResult="specific";
		else $strResult="general";
		
		if ($strResult=="specific") {
			if ($this->data() == false) return 0;
			return count($this->data());
		} else {
			$qt = 0;
			if ($this->data() == false) return 0;
			foreach ($this->data() as $data) $qt=$qt+$data->qt;
			return $qt;
			
		}
		
	}
	
	function cost( $tax=null,$shipping=null,$retTax=null ) {
		
		$tax = (float) $tax==null?0:$tax;
		if ($tax>=1) $tax=$tax/100;
		
		$price = 0;
		foreach ($this->data() as $data) $price=$price+($data->price*$data->qt);
		//add shipping
		$shipping = $shipping == null ? 0 : (float) $shipping;
		$price = $price + $shipping;
		$t = ($price*$tax);
		
		if ($retTax==true) return $t;
		
		$price = $price + $t;
		return sprintf("%.2f",$price);
		
	}
	
	function destroy() {
		
		$this->data->update(array('active' => 0));
		$this->__construct();
			
	}
	
	private $ID = 0;
	
	public function ID() {
		return $this->ID;	
	}
	
	function __construct( $userid=null ) {
		
		//construct the cart
		//after we construct the cart we need to destroy bad values
		
		if ($userid==null) {
			
			//let's try cookie based carts
			if ( count( $_COOKIE ) > 0) {
				//this means we can use cookie based carts
				
				//check if the cookie exists
				if ( isset( $_COOKIE['tracker-id'] ) ) { //no tracker-id cookie
					//this means we have a trackerid. The of category in the cart will then refer to the tracker id
					$f = fQuery( 'carts', 'of[x=?],type[x="tracker"],active[x=1],*', e($_COOKIE['tracker-id']) );
					if ($f->count < 1) { //nothing found
						$trackerID = $_COOKIE['tracker-id'];
						$sqlnew = "INSERT INTO `carts` (of,type,active,time,data) VALUES ";
						$sqlnew .= sprintf( "('%s', '%s', 1, %d, '')", e($trackerID), "tracker", time()); 
						
						$this->ID = $trackerID;
						
						query( $sqlnew );
							
						$f->reload();
						
						if ($f->count==0) throw new CartError( 'progerror' );
						
					}
//					die(var_dump($f));
					$this->data=$f;
					$this->rowdata = $f->this();
					return $this;
					
				} else {
					//this means we need to generate a tracker ID and use it
					$trackerID = "Tracker";
					$trackerID .= time();
					$trackerID .= rand(500000,999999);
					$trackerID .= $_SERVER[ 'REMOTE_ADDR' ];
					$trackerID .= "fernsissalty";
					
					$trackerID = sha1( $trackerID );
					
					//now we have it, so let's set it
					
					setcookie( 'tracker-id', $trackerID, time()+525948 );
					//now the cookie is set with the tracker, we need to add it into the database
					
					$sqlnew = "INSERT INTO `carts` (of,type,active,time,data) VALUES ";
					$sqlnew .= sprintf( "('%s', '%s', 1, %d, '')", e($trackerID), "tracker", time());
					
					$this->ID = $trackerID;
					
					query( $sqlnew );
					$fnew = fQuery( 'carts', 'id[x=?],*', insert_id());
						
					if ($fnew->count==0) throw new CartError( 'progerror' );
					$this->rowdata = $fnew->this();
					$this->data=$fnew;
					
					//Header() go back
					
					return $this;
					
				}
				
					
			} else {
				
				//now I guess we can try to use SESSION based carts
				if ( count( $_SESSION ) > 0 ) {
					
					//let's try to use session carts now
					if ( isset( $_SESSION['tracker-id'] ) ) {
					
						//this means we have a trackerid. The of category in the cart will then refer to the tracker id
						$f = fQuery( 'carts', 'of[x=?], type[x="tracker"],active[x=1],*', $_SESSION['tracker-id'] );
						if ($f->count < 1) {
						
							$trackerID = $_SESSION['tracker-id'];
							$sqlnew = "INSERT INTO `carts` (of,type,active,time,data) VALUES ";
							$sqlnew .= sprintf( "('%s', '%s', 1, %d, '')", e($trackerID), "tracker", time()); 
							
							$this->ID = $trackerID;
							
							query( $sqlnew );
								
							$f->reload();
							
							if ($f->count==0) throw new CartError( 'progerror' );
							
						}
						$this->data=$f;
						$this->rowdata = $f->this();
						return $this;
						
					} else {
						
						//this means we need to generate a tracker ID and use it
						$trackerID = "Tracker";
						$trackerID .= time();
						$trackerID .= rand(500000,999999);
						$trackerID .= $_SERVER[ 'REMOTE_ADDR' ];
						$trackerID .= "fernsissalty";
						
						$trackerID = sha1( $trackerID );
						
						//now we have it, so let's set it
						
						$_SESSION['tracker-id'] = $trackerID;
						//now the cookie is set with the tracker, we need to add it into the database
						
						$sqlnew = "INSERT INTO `carts` (of,type,active,time,data) VALUES ";
						$sqlnew .= sprintf( "'%s', '%s', %d, 1, '')", e($trackerID), "tracker", time()); 
						
						$this->ID = $trackerID;
						
						query( $sqlnew );
						$f = fQuery( 'carts', 'id[x=?],*', insert_id());
						
						if ($f->count==0) throw new CartError( 'progerror' );
						
						$this->data=$f;
						$this->rowdata=$f->this();
						
						return $this;
							
					}
					
				} else return false;
				
				//the only other option is to use GET carts but I personally don't think it is worth it, so let's return false
				
			}
			
		} 
		
		$f = fQuery( 'carts', "of[x=?],type[x='accounts'],active[x=1],time:order('desc'),*:limit(1)", $userid );
		
		if ($f->count<1) {
		
			//we're gonna put one in
			$sql = sprintf("INSERT INTO `carts` (of,type,active,data,time) VALUES ('%s', 'accounts', 1, '', %d)", $userid, time());
			query($sql);
			
			if (affected_rows() > 0) $f->reload();
			else throw new CartError('messedupaccount');
		
		}
		
		$this->rowdata = $f->this(); //lol easter egg
		$this->data = $f;
		//The row data is saved into the variable now but there are a few other things to do. This will load a DB based cart system. This will only load a cart by a logged in user. There also needs to be an ability to make a cart save to a non-logged-in user via cookies.
		
		self::$lastcart = $this;
		return $this;
		
	}
	
	public function data() {
		
		if (!is_object( $this->rowdata )) return array();
		$otherwise = (array) (unserialize($this->rowdata->row('data')));
		$otherwise = array_filter($otherwise);
		return $otherwise;
		
	}
	
	const MAX = 99;
	 
	public function remove( $itemid, $qt = 1 ) {
		
		//we have the data that is in it already, now we need to add an item of a certain quantity into it. First, let's make sure that item isn't already in the cart
		$row = $this->rowdata;
		if (!is_object( $row ) ) return false;
		$cartData = unserialize( $row->row( 'data' ) );		
		
		if (!is_array($cartData) or count($cartData) < 1) $cartData=array();
		
		/**
		  * The cart is structed as follows:
		  * $cartData = array( 'itemid' => ItemObj );
		  * Within item data is the quantity and necessary information for the checkout
		  * Checkout data is like the name, permalink, and description
		  */
		
		if (array_key_exists( $itemid, $cartData )) {
			//this means there is an item in there already, so we just need to increase quantity
			$ret = $cartData[$itemid]->remove( $qt ); //this adds that amount of items to the itemData
			
		} else {
			
			return false; //that item isn't in the cart
			
		}
		
		if (!$ret) unset($cartData[$itemid]);
		
		//cartData is done being modified. Place it back in and update the DB
		$cartData = e(serialize( $cartData ));
		
		//now we need to put this in the DB
		$this->data->update( array( 'data' => $cartData ) );
		$this->rowdata=$this->data->this();
		
		return true;
		
		
	}
	 
	public function add( $itemid, $qt = 1 ) {
		
		if ($qt < 0) {
			//delete the item
			$this->remove( $itemid, self::MAX );
			return true;
		}
		
		//we have the data that is in it already, now we need to add an item of a certain quantity into it. First, let's make sure that item isn't already in the cart
		$row = $this->rowdata;

		if (!is_object( $row ) ) return false;
		$cartData = unserialize( $row->row( 'data' ) );		
		
		if (!is_array($cartData) or count($cartData) < 1) $cartData=array();
		
		/**
		  * The cart is structed as follows:
		  * $cartData = array( 'itemid' => ItemObj );
		  * Within item data is the quantity and necessary information for the checkout
		  * Checkout data is like the name, permalink, and description
		  */
		
		if (array_key_exists( $itemid, $cartData )) {
			//this means there is an item in there already, so we just need to increase quantity
			$cartData[$itemid]->add( $qt ); //this adds that amount of items to the itemData
			
		} else {
			
			//let's add it
			$cartData[$itemid] = new Item( $itemid );
			//this instantiates the Item object. Now let's make sure the quantity is correct. The quantity defaults to 1, so we just need to see if the quantity is more than one
				
			if ($qt > 1) $cartData[$itemid]->add( $qt-1 );
			
		}
		
		//cartData is done being modified. Place it back in and update the DB
		$cartData = e(serialize( $cartData ));
		
		//now we need to put this in the DB
		$this->data->update( array( 'data' => $cartData ) );
		
		$this->rowdata=$this->data->this();
		
		return true;
		
		
	}
	 
}

class Item { //Items class will store the necessary data so it doesn't have to be loaded next time. This necessary data includes item name, item price, item description.
	
	public $id, $qt;
	
	public $name, $price, $desc, $wt, $itemData, $taxable, $shipping_coefficient;
	
	function __construct( $id ) {
		$this->id=$id;
		$this->qt=1;
		
		$query = fQuery('items', 'name,price,weight,description,taxable,shipping_coefficient,data,id[x=?]:limit(1)', $id);
		if ($query->count < 1) return false;
		else {
			$data = $query->this();
			
			$this->name=$data->row('name');
			$this->shipping_coefficient=$data->row('shipping_coefficient');
			$this->taxable=$data->row('taxable');
			$this->price=$data->row('price');
			$this->desc=$data->row('description');
			$this->wt=$data->row('weight');
			$this->itemData = (strlen($data->row('data')) > 0) ? json_decode($data->row('data')) : array();
			
			return $this;
		}
	}
	
	function add( $num ) {
		
		$num=(int)$num;
		if (($this->qt+$num) <= Cart::MAX) $this->qt=$this->qt+$num;
		else $this->qt=Cart::MAX;
		
	}
	
	function remove( $num ) {
		
		$num=(int)$num;
		if (($this->qt-$num) > 0) {
			$this->qt=$this->qt-$num;
			return true;
		} else {
			$this->qt=0;
			return false;	
		}
		
	}
	
}

/**
 * Add the cart to the Fn object. 
 */

if (false and function_exists('is_logged_in') and is_logged_in() and isset(Fn()->account->id)) {
	Fn::add('cart', new Cart( Fn()->account->id ));
} else {
	Fn::add('cart', new Cart(null));	
}

?>