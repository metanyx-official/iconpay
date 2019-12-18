var iconService = window['icon-sdk-js'];
    var IconAmount = iconService.IconAmount;
    var IconConverter = iconService.IconConverter;
    var IconBuilder = iconService.IconBuilder;
    
window.addEventListener("ICONEX_RELAY_RESPONSE", eventHandler, false);
 
 
 
function start_process(){
    
jQuery('#overlay').show();
                    swal({
  title: "Please Review",
  text: "Select your wallet first!",
  icon: "warning",
  buttons: true,
  dangerMode: true,
})
.then((willDelete) => {
  if (willDelete) {
      
     window.dispatchEvent(new CustomEvent('ICONEX_RELAY_REQUEST', {
                     detail: {
                     type: 'REQUEST_ADDRESS'
                         }
                        }));
                        
                
  } else {
      
swal({
  title: "Please Review!",
  text: "Are you sure you want to cancel this transaction?!",
  icon: "warning",
  buttons: true,
  dangerMode: true,
})
.then((willDelete) => {
  if (willDelete) {
    //   alert('am clicked1');
						    ajaxcall_cancel();
							//window.location = shop_page_url ; 
							  } else {
							     //  alert('am clicke2d');
								 	location.reload( true ) ;
								//swal("Your imaginary file is safe!");
							  }
                         
					});
    }
  });
}


                    

              

function addIconPayOnClick() {

    if (jQuery('form[name="checkout"] input[name="payment_method"]:checked').val() == 'iconpay') {

        jQuery("#place_order1").attr("onclick", "transfer()");
        var fromAddress=jQuery("#wallet_address").val();
console.log(fromAddress);
if(fromAddress !==0 ){
    transfer();
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
       
        jQuery('#overlay').show();
        var type = event.detail.type;
        var payload = event.detail.payload;
        //alert(type);
        if(typeof type === 'undefined' || type ==='CANCEL_JSON-RPC'){
            
          
            
            // transfer();
            jQuery('.iotpay_statuscode').text('You have cancelled transaction Please Wait.. ');
            	
              	setTimeout(function(){ jQuery('.iotpay_statuscode').text('Please wait while we redirect you.... '); window.location = shop_page_url ;  }, 3000); 
            
           
           
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
                // alert('in transaction');
                var payload=JSON.stringify(payload);

                //if the tracnsction json gives the success then proceed like this
                var payload = JSON.parse(payload);
                 // alert(payload.result);

                if(payload.result){
                    jQuery('#overlay').show();
                    // swal("success",'Payment Sucessfully done ',"success");
                   
                 jQuery('#txn_hash').val(payload.result);  

 
                   icon_updateOrder(payload);
                   
            
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
            	
              	setTimeout(function(){ jQuery('.iotpay_statuscode').text('Please wait while we redirect you.... '); window.location = shop_page_url ;  }, 3000);
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
  setTimeout(function(){ jQuery('.iotpay_statuscode').text('Please wait while we redirect you.... '); window.location = shop_page_url ;  }, 3000);  
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
              	
              	
              	setTimeout(function(){ jQuery('.iotpay_statuscode').text('Please wait while we redirect you.... '); window.location = shop_page_url ;  }, 3000);
						
				 
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
              	
              	
              	setTimeout(function(){ jQuery('.iotpay_statuscode').text('Your transaction cancelled.Please wait while we redirect you.... '); window.location = shop_page_url ;  }, 3000);			
				 
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
	
