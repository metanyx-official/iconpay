<?php
require_once("../../../wp-load.php");

global $woocommerce ;

	$order_id	=	 $_GET['order'] ;
	$order_id=base64_decode($order_id);
	$order = new WC_Order($order_id);
	

 $myPluginGateway = new WC_iconpay_Gateway();

 $iconpaywallet_address	= $myPluginGateway->get_option('iconpaywallet_address');
 $cart_total			=	$order->get_total();			
 $cart_total			=	convertPriceToICON( $cart_total ) ;
 $order_button_text	 	= __('Place order', 'woocommerce'); 
 $order_received_url	 	= get_site_url()."/checkout/order-received/".$order_id; 

 $shop_page_url = get_permalink( woocommerce_get_page_id( 'shop' ) );

?>
 <form name="paybyicon" id="paybyicon"	>
<input type="hidden" name="wallet_address" id="wallet_address" value="0">
 <input type="hidden" name="txn_hash" id="txn_hash">
 <input type="hidden" name="overalltotal" value="<?php echo $cart_total; ?>" id="overalltotal">
 <input type="hidden" name="ordersuccess" value="<?php echo $order_received_url ?>" id="o_success">
 <input type="hidden" name="shop_wallet" id="shop_wallet" value="<?php echo $iconpaywallet_address; ?>">
 </form>


 <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
 
  <script type="text/javascript" src="<?php echo  plugin_dir_url( __FILE__ ).'js/iconpay_main.js'  ?>"></script>
 <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
 <script src="<?php echo  plugin_dir_url( __FILE__ ).'js/iconpay.js'  ?>"></script>
  <script>
 
 var iconpaywallet_address  = '<?php echo $iconpaywallet_address ;   ?>' ;
 var order_id  = '<?php echo $order_id ;   ?>' ;
 var cart_total  = '<?php echo $cart_total ;   ?>' ;
 var icon_updateOrderURL  = '<?php echo plugin_dir_url( __FILE__ )."updateOrder.php/?order=$order_id" ?>' ;
 var get_cart_url  = '<?php echo $get_cart_url ?>' ;
 var order_received_url  = '<?php echo $order_received_url ?>' ;
 var shop_page_url  = '<?php echo $shop_page_url ?>' ;
jQuery('#overlay').show();
  start_process();

 
 </script>
 
 
 
 
 
 
<div id="overlay" style="display:none;"> <div class="spinner"></div> <br/><h3> <span class="iotpay_statuscode"> We are processing your order... </span> </h3> </div>

<style>
.spinner {
    margin: 0 auto;
    height: 64px;
    width: 64px;
    animation: rotate 0.8s infinite linear;
    border: 5px solid firebrick;
    border-right-color: transparent;
    border-radius: 50%;
}
@keyframes rotate {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
div#overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999999999;
    background: rgba(255,255,255,.5);
    padding: 200px 0;
}


span.iotpay_statuscode {
    text-align: center;
    width: 100%;
    float: left;
}

</style>
