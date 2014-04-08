<div class="viewshipment"><?

use_connection('patsys');

//check if there is an ID to use
if (!isset($_GET['id'])) { //there is no ID
	?>No item selected.<?php
} else {

	$id = $_GET['id'];
	Fn()->load_extension('fquery');
	Fn()->load_extension('cart');
	$f = fQuery('patsys_shipments', 'id[x=?]:limit(1),*', $id);
	if ($f->count<1) {
		//no shipment by that ID
		?>We couldn't find that shipment.<?php
	} else {
		//We found the shipment
		$theShipment = $f->this();
		$address = fQuery('patsys_addresses', 'id[x=?],*', $theShipment->ship_to);

		if ($address->count<1) {
			die("Serious error");
		} else {

			$theAddress = $address->this();
			//now get the persons information

			$name = lookup('patsys_users', $theShipment->of, '[firstname] [lastname]');
			$name = $name ? $name : "Unknown";
			$theItems = unserialize($theShipment->data);
			
			$orderDB = fQuery( 'orders', 'id[x=?],paypal_response,return_code,paypal_array:limit(1)', $theShipment->order_num);
			if ($orderDB->count < 1) die("Couldn't find the order");
			$orderDB = $orderDB->this();
			
			$paypalInfo = json_decode($orderDB->paypal_array);
			

			?>
            <div class="shipment-wrapper">
            
                <div class="shipment-to">

                    <div class="shipping-label">Shipping Address</div>

                    <div class="name"><?=$name?></div>

                    <div class="address">

                        <?=$theAddress->street?>

                        <?php if ($theAddress->apt != ""): ?><br><?=$theAddress->apt?><?php endif; ?>

                    </div>

                    <div class="address_meta"><span class="city"><?=$theAddress->city?></span>, <span class="state"><?=$theAddress->state?></span> <span class="zip"><?=$theAddress->zip?></span></div>

                </div>

                <div class="payment-information">

                    <p>This is the payment method used to pay for this order. You can click <a>here</a> to get a link to the server response text to show the proof of purchase.</p>

                    <div class="payment-method credit">

                        <div class="name"><strong>Name:</strong> <?=$name?></div>

                        <div class="cardNum"><strong>CC#:</strong> <?=$paypalInfo->ACCT ?></div>

                        <div class="exp"><strong>Expiration:</strong> <span class="month"><?=$paypalInfo->EXPMONTH?></span> / <span class="year"><?=$paypalInfo->EXPYEAR?></span></div>

                    </div>

                </div>

            </div>

            <h2>Order Information</h2>

            <div class="order-info">

            <?php

			$calcTotal = 0;

			foreach( $theItems as $item) {		

				?><div class="order-row">

                    <div class="item-price-total">$<?=number_format($item->price*$item->qt, 2)?></div>

                    <div class="item-price-one">$<?=number_format($item->price, 2)?></div>

                    <div class="item-qt"><?=$item->qt?></div>

                    <div class="item-name"><?=$item->name?> - <small style="color: gray;"><?=substr($item->desc, 0, 140)?> [...]</small></div>

                </div><?php $calcTotal = $calcTotal + ($item->price*$item->qt);

			}

			?>

            	<div class="order-info-footer">

                	<div class="subtotal"><strong>Subtotal:</strong> $<?=number_format($calcTotal, 2)?></div>
                    <div class="total"><strong>Total Charged:</strong> $<?=number_format($paypalInfo->AMT, 2);?></div>

                </div>

            </div>

            

            <div class="shipment-wrapper" style="padding-top: 10px; clear: both;">

            

            <div class="admin-actions">

            	<div class="box-title">Actions</div>

            	<div class="box-content">

                    <p>This order has been marked as <em><?=$theShipment->shipped ? "shipped" : "awaiting shipment"?></em>. Please select an action you would like from the list below</p>

                    <ul>

                        <li>Print a shipping label.</li>

                        <li>Mark this order as shipped.</li>

                        <li>Correspond with the customer.</li>

                    </ul>

                </div>

            </div>

            

            </div>

            

			<?php

		}	

		

	}

		

}

?></div>