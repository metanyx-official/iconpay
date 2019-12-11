<?php
require_once("../../../wp-load.php");

		$order_id	=	$_REQUEST['order'] ;
        

	if( isset( $order_id ) ){
		
		global $woocommerce;
		
		$orderStatus =		$_POST['orderStatus'] ;
		$message =		$_POST['message'] ;
			
		$order = wc_get_order( $order_id );

		$status = $orderStatus ; 
			


	 update_post_meta( $order_id, '_iconpay_txnid_field', $_POST[ 'iconpay_txnid' ] );
			
// 		 $order->update_status( $status, __( 'Checkout with IconPay.', $this->domain ) );
		 $order->update_status( $orderStatus, 'order_note');
		
		//get wc_order_sjkhdkshdk
		 $order_key = $order->get_order_key();
		   $array_resp = 
					array(
                        'status_code' => isset( $orderStatus ) ? $orderStatus : '' ,
                        'message' => isset( $message ) ? $message : '' ,
                        'order_key'=>$order_key
                       )        ;
                       
		echo json_encode( $array_resp ) ;
		
		
	}