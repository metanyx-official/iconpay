<?php
/*
 * Plugin Name: ICONPay
 * Plugin URI: https://github.com/metanyx-official/iconpay
 * Description: Pay with ICON
 * Author: Metanyx
 * Author URI: https://metanyx.com/
 * Version: 1.1.1
 *


/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
*/
add_filter( 'woocommerce_payment_gateways', 'iconpay_add_gateway_class' );
function iconpay_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_iconpay_Gateway'; // your class name is here
	return $gateways;
}


/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'iconpay_init_gateway_class' );
function iconpay_init_gateway_class() {

	class WC_iconpay_Gateway extends WC_Payment_Gateway {

		/**
		 * Class constructor, more about it in Step 3
		 */
		public function __construct() {

				$this->id                 = 'iconpay'; // payment gateway plugin ID
				$this->icon               = ''; // URL of the icon that will be displayed on checkout page near your gateway name
				$this->has_fields         = false; // in case you need a custom credit card form
				$this->method_title       = 'ICONPay';
				$this->method_description = 'ICONPay'; // will be displayed on the options page
				$this->keyname            = ''; // will be displayed on the options page

				// gateways can support subscriptions, refunds, saved payment methods,
				// but in this tutorial we begin with simple payments
				$this->supports = array(
					'products',
				);

				// Method with all the options fields
				$this->init_form_fields();

				// Load the settings.
				$this->init_settings();

				$this->title = $this->get_option( 'title' );

				$this->description = $this->get_option( 'description' );

				$this->enabled = $this->get_option( 'enabled' );

				$this->iconpaywallet_address = $this->get_option( 'iconpaywallet_address' );
				$this->cmc_api_key           = $this->get_option( 'cmc_api_key' );

				 // Actions
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
				add_action( 'woocommerce_thankyou_custom', array( $this, 'thankyou_page' ) );

				// Customer Emails
				add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );

		}

		/**
		 * Plugin options, we deal with it in Step 3 too
		 */
		public function init_form_fields() {

			$this->form_fields = array(
				'enabled'               => array(
					'title'       => 'Enable/Disable',
					'label'       => 'Enable ICONPay',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
				'title'                 => array(
					'title'       => 'Title',
					'type'        => 'text',
					'description' => 'This controls the title which the user sees during checkout.',
					'default'     => 'ICONPay',
					'desc_tip'    => true,
				),
				'description'           => array(
					'title'       => 'Description',
					'type'        => 'textarea',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => '',
				),
				'iconpaywallet_address' => array(
					'title'       => 'ICONPay wallet address Account Id',
					'type'        => 'text',
					'description' => 'This controls the description which the user sees during checkout.',
					'default'     => '',
				),
				'cmc_api_key'           => array(
					'title'       => 'CMC API KEY',
					'type'        => 'text',
					'description' => 'This controls the description which the user sees during 	
				checkout.',
					'default'     => '',
				),

			);
		}

		/*
		* Fields validation, more in Step 5
		*/
		public function validate_fields() {

			// if( empty( $_POST[ 'billing_first_name' ]) ) {
			// wc_add_notice(  'First name is required!', 'error' );
				// return false;
			// }

			return true;
		}

		/*
		 * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		 */
		public function payment_scripts() {

		}

		/*
		* We're processing the payments here, everything about it is in Step 5
		*/

		public function process_payment( $order_id ) {

			global $woocommerce;

			$order = wc_get_order( $order_id );

			// $status = 'wc-completed' ;
			// $status = 'wc-completed' ;

			// $order->add_order_note( json_encode( $_POST ) );

			// $this->order_status;
			// update_post_meta( $order_id, '_iconpay_txnid_field', $_POST['txn_hash'] );
			// Set order status
			// $order->update_status( $status, __( 'Checkout with ICONPay.', $this->domain ) );

			// Reduce stock levels
			$order->reduce_order_stock();

			// Remove cart
			WC()->cart->empty_cart();

			// Return thankyou redirect

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}

		/*
		 * In case you need a webhook, like PayPal IPN etc
		 */

		public function webhook() {

			$order = wc_get_order( $_GET['id'] );
			$order->payment_complete();
			$order->reduce_order_stock();

			update_option( 'webhook_debug', $_GET );
		}

	}
}



function convertPriceToICON( $invoice_amount ) {

	 $symbol = get_option( 'woocommerce_currency' );

	 $myPluginGateway = new WC_iconpay_Gateway();

	// $iconpay_account_id = $myPluginGateway->get_option('iconpay_account_id');

	 $cmc_api_key = $myPluginGateway->get_option( 'cmc_api_key' );

		$url = 'https://pro-api.coinmarketcap.com/v1/tools/price-conversion';

		 $parameters = array(
			 'symbol'  => $symbol,
			 'amount'  => $invoice_amount,
			 'convert' => 'ICX',
		 );

		 $CMC_KEY = $cmc_api_key;
		 $headers = array(
			 'Accepts: application/json',
			 'X-CMC_PRO_API_KEY: ' . $CMC_KEY,
		 );

		 $qs      = http_build_query( $parameters ); // query string encode the parameters
		 $request = "{$url}?{$qs}"; // create the request URL

		 $curl = curl_init(); // Get cURL resource
		 // Set cURL options
		 curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $request,            // set the request URL
				CURLOPT_HTTPHEADER     => $headers,     // set the headers
				CURLOPT_RETURNTRANSFER => 1,         // ask for raw response instead of bool
			)
		);

		$response = curl_exec( $curl ); // Send the request, save the response
		// echo $response;
		// die;
		$response = json_decode( $response, true );

	if ( isset( $response ) ) {

		$iconPrice = $response['data']['quote']['ICX']['price'];

		 $icon_amount = number_format( (float) $iconPrice, 2, '.', '' );
		return $icon_amount;
	}

}

 // Adding Meta container admin shop_order pages
add_action( 'add_meta_boxes', 'iconpay_add_meta_boxes' );
if ( ! function_exists( 'iconpay_add_meta_boxes' ) ) {
	function iconpay_add_meta_boxes() {
		add_meta_box( 'txn_hash', __( 'ICONPay TxHash', 'woocommerce' ), 'iconpay_add_other_fields_for_packaging', 'shop_order', 'side', 'core' );
	}
}


// Adding Meta field in the meta container admin shop_order pages
if ( ! function_exists( 'iconpay_add_other_fields_for_packaging' ) ) {
	function iconpay_add_other_fields_for_packaging() {
		global $post;

		$meta_field_data = get_post_meta( $post->ID, '_iconpay_txnid_field', true ) ? get_post_meta( $post->ID, '_iconpay_txnid_field', true ) : '';

		echo '<input type="hidden" name="iconpay_other_meta_field_nonce" value="' . wp_create_nonce() . '"> <p style="border-bottom:solid 1px #eee;padding-bottom:13px;">
            <input type="text" style="width:250px;";" name="txn_hash" placeholder="' . $meta_field_data . '" value="' . $meta_field_data . '"></p>';

	}
}


// add_action( 'woocommerce_thankyou', 'iconpay_redirectcustom' );

// function iconpay_redirectcustom( $order_id ) {

// 	$order = wc_get_order( $order_id );

// 	$url = plugin_dir_url( __FILE__ ) . 'paybyicon.php?order=' . base64_encode( $order_id );

// 	if ( $order->get_payment_method() == 'iconpay' && $order->has_status( 'pending' ) ) {
// 			wp_safe_redirect( $url );
// 		exit;
// 	}
// }


// add_action( 'woocommerce_thankyou', 'bbloomer_checkout_save_user_meta');
 
// function bbloomer_checkout_save_user_meta( $order_id ) {
    
//   $order = wc_get_order( $order_id );
//   $user_id = $order->get_user_id();
  
//   update_post_meta( $order_id, '_iconpay_txnid_field', $_POST['iconpay_txnid'] );
  
  
//   echo'<pre>';
  
  
  
//   if ( $order->get_total() > 100 ) {  // Define your condition here
//       update_user_meta( $user_id, 'custom_checkbox', 'on');
//   }
 
// }


add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );

function my_custom_checkout_field_update_order_meta( $order_id ) {
   
    if ( ! empty( $_POST['txn_hash'] ) ) {
        update_post_meta( $order_id, '_iconpay_txnid_field', $_POST['txn_hash'] );
       $order = wc_get_order( $order_id );
       $order->update_status('completed');
      // update_post_meta( $order_id, 'txn_hash', sanitize_text_field( $_POST['txn_hash'] ) );
    }
}



//add hidden filed to the form 
add_action( 'woocommerce_after_order_notes', 'my_custom_checkout_field' );

function my_custom_checkout_field( $checkout ) {

    

    woocommerce_form_field( 'txn_hash', array(
        'type'          => 'text',
        'class'         => array('txn_hash'),
        'value'         =>'demoval'
        ), $checkout->get_value( 'txn_hash' ));

   

}

//checkout js integartion 

add_action('woocommerce_checkout_after_order_review','icon_pay_btn');
function icon_pay_btn(){
    
    echo '<style>button.sdfsdfsdfs {
    float: right;
}</style><button type="button" class="sdfsdfsdfs" style="display:none">Proceed to pay</button>';
}


add_action('wp_footer','icon_pay_js');
function icon_pay_js(){
    
    $myPluginGateway = new WC_iconpay_Gateway();

 $iconpaywallet_address = $myPluginGateway->get_option( 'iconpaywallet_address' );
 $shop_page_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
 
    ?>
    <!--//include scripts in here -->
     <script type="text/javascript" src="<?php echo plugin_dir_url( __FILE__ ) . 'js/iconpay_main.js'; ?>"></script>
 <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
 <input type="hidden" name="wallet_address" id="wallet_address" value="0">
 <input type="hidden" name="overalltotal" value="<?php echo convertPriceToICON( WC()->cart->cart_contents_total ); ?>" id="overalltotal">
 <style>
     #txn_hash{
      display:none;   
     }
 </style>
 <input type="hidden" name="shop_wallet" id="shop_wallet" value="<?php echo $iconpaywallet_address; ?>">
     <script>
     jQuery(document).ready(function($){
       $( document ).ajaxSuccess(function( event, xhr, settings ) {
          
           console.log(settings.url);
           var string = settings.url;
           var ajax_action = string.split('?');
           console.log(ajax_action);
               if ( ajax_action[1] == "wc-ajax=update_order_review" ) {
                  if($('.sdfsdfsdfs').hasClass('icon_pay')){
                      $('button#place_order').hide();
                      $('.sdfsdfsdfs').show();
                  }
               }
            });

     });
     
     
     
     var shop_page_url  = '<?php echo $shop_page_url; ?>' ;
         jQuery(document).ready(function($){
             $('.sdfsdfsdfs').hide(); 
            $('#order_review').on('click','li',function(){
                   if($(this).find('label').attr('for') == 'payment_method_iconpay'){
                           $('button#place_order').hide();
                       $('.sdfsdfsdfs').show(); 
                   }else{
                       $('button#place_order').show();
                       $('.sdfsdfsdfs').hide(); 
                   }
                    
            });
            
             $('form.checkout.woocommerce-checkout').on('click','.sdfsdfsdfs',function(shop_page_url){ 
                 $(this).addClass('icon_pay');
                 var $count  = 1;
                 var flag = true;
                 $('form.checkout.woocommerce-checkout .validate-required input, form.checkout.woocommerce-checkout .validate-required select, form.checkout.woocommerce-checkout .validate-required textarea').each(function(){
                     console.log($count++);
                     if($(this).val() == ''){
                         flag = false;
                         $(this).css({'border':'1px solid #f00'});
                         
                     }
                     
                 });
                 
                 if(flag == true){
                    //  alert('in flag');
                     // icon pay
                     var iconService = window['icon-sdk-js'];
    var IconAmount = iconService.IconAmount;
    var IconConverter = iconService.IconConverter;
    var IconBuilder = iconService.IconBuilder;
    
window.addEventListener("ICONEX_RELAY_RESPONSE", eventHandler, false);
 start_process();
 
 
function start_process(){
    
 window.dispatchEvent(new CustomEvent('ICONEX_RELAY_REQUEST', {
                     detail: {
                     type: 'REQUEST_ADDRESS'
                         }
                        }));
                        
}


                    

              

function addIconPayOnClick() {

    if (jQuery('form[name="checkout"] input[name="payment_method"]:checked').val() == 'iconpay') {

        jQuery("#place_order1").attr("onclick", "transfer()");
        var fromAddress=jQuery("#wallet_address").val();
console.log(fromAddress);
if(fromAddress !==0 ){
    
}
//alert('inif');
    } else {
//alert('inelse');
        jQuery("#place_order1").removeAttr("onclick")
        jQuery("#place_order1").removeAttr("disabled")

    }

}





// type and payload are in event.detail
    function eventHandler(event) {
       
       
        var type = event.detail.type;
        var payload = event.detail.payload;
        console.log(type);
        if(typeof type === 'undefined' || type ==='CANCEL_JSON-RPC'){
            
          
            
             transfer();
            
           
           
        }else{
            
        switch (type) {
            case "RESPONSE_HAS_ACCOUNT":
                // responseHasAccount.innerHTML = "> Result : " + payload.hasAccount + " (" + typeof payload.hasAccount + ")";
                break;
            case "RESPONSE_HAS_ADDRESS":
                // responseHasAddress.innerHTML = "> Result : " + payload.hasAddress + " (" + typeof payload.hasAddress + ")";
                break;
            case "RESPONSE_ADDRESS":
                
                if(typeof payload === 'undefined'){
                    transfer();
                }
                fromAddress = payload;
               
                    
               jQuery("#wallet_address").val(payload);
               
               
               
                transfer();
               
               
                break;
            case "RESPONSE_JSON-RPC":
                //  alert('in transaction');
                var payload=JSON.stringify(payload);

                //if the tracnsction json gives the success then proceed like this
                var payload = JSON.parse(payload);
                //   alert(payload.result);

                if(payload.result){
                    jQuery('#overlay').show();
                     swal("success",'Payment Sucessfully done ',"success");
                   
                 jQuery('#txn_hash').val(payload.result);  
                    
                    
                    
                    jQuery("form[name='checkout']").submit();
 
                  
                   
            
                }else{
                    var msg=payload.message;
                    jQuery('#overlay').show();
                   jQuery('.iotpay_statuscode').text(payload.message);
                   ajaxcall_pending(payload);
                }
                // event.stopPropagation();
                
                
                break;
            case "CANCEL_JSON-RPC":
                jQuery('#overlay').show();
                 swal('error','You have cancelled transaction Please Wait..');
            jQuery('.iotpay_statuscode').text('You have cancelled transaction Please Wait.. ');
              	ajaxcall_cancel();
            //   event.stopPropagation();
               
               
                break;
            case "RESPONSE_SIGNING":
                // signingData.value = null;
                // responseSigning.innerHTML = "> Signature : " + JSON.stringify(payload);
                // event.stopPropagation();
                break;
            case "CANCEL_SIGNING":
                // signingData.value = null;
                // responseSigning.value = "> Signature : ";
                // event.stopPropagation();
                break;
            default:
                // event.stopPropagation();
                //alert('in defult');
            	jQuery('.iotpay_statuscode').text('You have cancelled transaction Please Wait.. ');
            	
              	setTimeout(function(){ jQuery('.iotpay_statuscode').text('Please wait while we redirect you.... ');  }, 3000);
        }
        
        }
    }

    



function transfer(){


    

    jQuery('#overlay').show();
    


var fromAddress=jQuery("#wallet_address").val();
console.log(fromAddress);
if(fromAddress !==0 ){
var toadd =jQuery('#shop_wallet').val();
var amount=jQuery('#overalltotal').val();
//var amount =0.1;
if(amount > 0){
    var callTransactionBuilder = new IconBuilder.CallTransactionBuilder();
                var callTransactionData = callTransactionBuilder
                    .from(fromAddress)
                    .to(toadd)
                    .nid(IconConverter.toBigNumber(1))
                    .nonce(IconConverter.toBigNumber(1))
                    .timestamp((new Date()).getTime() * 1000)
                    .stepLimit(IconConverter.toBigNumber(1000000))
                    .version(IconConverter.toBigNumber(3))
                    .method('createToken')
                    .value(IconAmount.of(amount, IconAmount.Unit.ICX).toLoop())
                   
                    .build();
               

        var parsed = JSON.parse(JSON.stringify({
                    "jsonrpc": "2.0",
                    "method": "icx_sendTransaction",
                    "params": IconConverter.toRawTransaction(callTransactionData),
                    "id": Math.floor(10000 + Math.random() * 90000)
                }));

 

         window.dispatchEvent(new CustomEvent('ICONEX_RELAY_REQUEST', {
            detail: {
                type: 'REQUEST_JSON-RPC',
                payload: parsed
            }
        })) 
        return;
}else{
  jQuery('.iotpay_statuscode').text('Amount cannot be 0');
  setTimeout(function(){ jQuery('.iotpay_statuscode').text('Please wait while we redirect you.... ');   }, 3000);  
}

}else{

start_process();
                       
    
}

     

}


// if transaction cancelled by user it self 

function ajaxcall_cancel(){
    //alert('am called');
jQuery('#overlay').show();
	 
	   var iconpay_txnid='Cancelled by user';
	   var status_code='wc-cancelled';
					
	jQuery.ajax({
            url: icon_updateOrderURL,
            cache: false,
            type: "POST",
            data: {
             iconpay_txnid : iconpay_txnid,
			 orderStatus : status_code,
			 message : '{"reason":"cancelled by user"}'
            },
		success: function( resp ) {
				 jQuery('.iotpay_statuscode').text('You have cancelled transaction Please Wait.. ');
              	
              	
              	setTimeout(function(){ jQuery('.iotpay_statuscode').text('Please wait while we redirect you.... ');   }, 3000);
						
				 
			}			
		})
	};  



//if transaction called by due to some error
function ajaxcall_pending(payload){

	jQuery('#overlay').show();   
	   var iconpay_txnid=payload.message;
	   var status_code='wc-cancelled';
					
	jQuery.ajax({
            url: icon_updateOrderURL,
            cache: false,
            type: "POST",
            data: {
             iconpay_txnid : iconpay_txnid,
			 orderStatus : status_code,
			 message : payload
            },
		success: function( resp ) {
						
					
			 jQuery('.iotpay_statuscode').text(iconpay_txnid);
              	
              	
              	setTimeout(function(){ jQuery('.iotpay_statuscode').text('Your transaction cancelled.Please wait while we redirect you.... ');   }, 3000);			
				 
			}			
		})
	}; 
	
	
	

function icon_updateOrder( payload ) {
jQuery('#overlay').show();
	   var iconpay_txnid=payload.result
	   var status_code='wc-processing';
					
	jQuery.ajax({
            url: icon_updateOrderURL,
            cache: false,
            type: "POST",
            data: {
             iconpay_txnid : iconpay_txnid,
			 orderStatus : status_code,
			 message : payload
            },
		success: function( resp ) {
				console.log('am here'+resp);		
				// 	swal({
    //                     title: "Iwallet",
    //                     text : 'Payment Completed Successfully',
    //                     type : "success"
    //                 }).then((willDelete) => {

				// 		window.location = order_received_url ;
				// 	});
				   var obj = JSON.parse(resp);
				   var s=order_received_url+'/?key='+obj.order_key+'&success=1';
			     //	var success_url=jQuery("#o_success").val(s);
					
					
					//alert(s);
					//console.log(s);
					
					jQuery('.iotpay_statuscode').text('Payment Completed Successfully Please Wait.. ');
              	
               	 jQuery('.iotpay_statuscode').text('Please wait while we redirect you.... ');
               	 var new_url=s;
               	window.location =new_url;
               	
					return;	
						
						
				 
			}			
		})
	}
	
                 }else{
                     $('html, body').animate({ scrollTop:400 }, 800);
                 }
                 
                 
                 
             });
             
         });
     </script>
    <?php
}

