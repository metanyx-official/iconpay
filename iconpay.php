<?php
/*
 * Plugin Name: ICONPay
 * Plugin URI: https://github.com/metanyx-official/iconpay
 * Description: Pay with ICON
 * Author: Metanyx
 * Author URI: https://metanyx.com/
 * Version: 1.0.2
 *


/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
*/
add_filter( 'woocommerce_payment_gateways', 'ioconpay_add_gateway_class' );
function ioconpay_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_iconpay_Gateway'; // your class name is here
	return $gateways;
}


/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'ioconpay_init_gateway_class' );
function ioconpay_init_gateway_class() {

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
					'label'       => 'Enable ioconpay',
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
			// $order->update_status( $status, __( 'Checkout with ioconpay.', $this->domain ) );

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
add_action( 'add_meta_boxes', 'ioconpay_add_meta_boxes' );
if ( ! function_exists( 'ioconpay_add_meta_boxes' ) ) {
	function ioconpay_add_meta_boxes() {
		add_meta_box( 'txn_hash', __( 'IconPay TxHash', 'woocommerce' ), 'ioconpay_add_other_fields_for_packaging', 'shop_order', 'side', 'core' );
	}
}


// Adding Meta field in the meta container admin shop_order pages
if ( ! function_exists( 'ioconpay_add_other_fields_for_packaging' ) ) {
	function ioconpay_add_other_fields_for_packaging() {
		global $post;

		$meta_field_data = get_post_meta( $post->ID, '_iconpay_txnid_field', true ) ? get_post_meta( $post->ID, '_iconpay_txnid_field', true ) : '';

		echo '<input type="hidden" name="ioconpay_other_meta_field_nonce" value="' . wp_create_nonce() . '"> <p style="border-bottom:solid 1px #eee;padding-bottom:13px;">
            <input type="text" style="width:250px;";" name="txn_hash" placeholder="' . $meta_field_data . '" value="' . $meta_field_data . '"></p>';

	}
}


add_action( 'woocommerce_thankyou', 'iconpay_redirectcustom' );

function iconpay_redirectcustom( $order_id ) {

	$order = wc_get_order( $order_id );

	$url = plugin_dir_url( __FILE__ ) . 'paybyicon.php?order=' . base64_encode( $order_id );

	if ( $order->get_payment_method() == 'iconpay' && $order->has_status( 'pending' ) ) {
			wp_safe_redirect( $url );
		exit;
	}
}

