var iconService = window['icon-sdk-js'];
    var IconAmount = iconService.IconAmount;
    var IconConverter = iconService.IconConverter;
    var IconBuilder = iconService.IconBuilder;
window.addEventListener("ICONEX_RELAY_RESPONSE", eventHandler, false);

jQuery(function() {
    jQuery('body')
        .on('updated_checkout', function() {




            addIconPayOnClick();
            var wallet_address=jQuery("#wallet_address").val();
            console.log(wallet_address);
            jQuery('input[name="payment_method"]').change(function() {
                console.log("payment method changed");
                
                if(jQuery('form[name="checkout"] input[name="payment_method"]:checked').val() == 'iconpay') {
                    

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
                        }))
                jQuery("#place_order1").attr("onclick", "transfer()");
  } else {
    var $radios = jQuery('input:radio[name=payment_method]');
    if($radios.is(':checked') === true ) {
        $radios.filter('[value=paypal]').prop('checked', true);
        jQuery(".payment_box.payment_method_iconpay").hide();
         jQuery(".payment_box.payment_method_paypal").show();
       
         jQuery('#place_order').attr("disabled", true);
         jQuery('#place_order').removeAttr("onclick");
    }
  }
});


                    

                }else {
                    // alert('not icon');
                    jQuery('#place_order').attr("disabled", true);
         jQuery('#place_order').removeAttr("onclick");
                }
                

            });
        });
});


function addIconPayOnClick() {

    if (jQuery('form[name="checkout"] input[name="payment_method"]:checked').val() == 'iconpay') {

        jQuery("#place_order1").attr("onclick", "transfer()");
        var fromAddress=jQuery("#wallet_address").val();
console.log(fromAddress);
if(fromAddress !=0 ){
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
        switch (type) {
            case "RESPONSE_HAS_ACCOUNT":
                responseHasAccount.innerHTML = "> Result : " + payload.hasAccount + " (" + typeof payload.hasAccount + ")";
                break;
            case "RESPONSE_HAS_ADDRESS":
                responseHasAddress.innerHTML = "> Result : " + payload.hasAddress + " (" + typeof payload.hasAddress + ")";
                break;
            case "RESPONSE_ADDRESS":
                fromAddress = payload;
               jQuery("#wallet_address").val(payload);
               jQuery('#overlay').hide();
               swal("success",'wallet selected',"success");
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

 
                    var checkout_form = jQuery('form.checkout');
                       checkout_form.submit() ;
            event.stopPropagation();
                }else{
                    var msg=payload.message;
                    jQuery('#overlay').hide();
                   swal("error",msg,"error");
                }
                break;
            case "CANCEL_JSON-RPC":
            jQuery('#overlay').hide();
               swal("error",'You have cancelled transaction',"error");
                break;
            case "RESPONSE_SIGNING":
                signingData.value = null;
                responseSigning.innerHTML = "> Signature : " + JSON.stringify(payload);
                break;
            case "CANCEL_SIGNING":
                signingData.value = null;
                responseSigning.value = "> Signature : ";
                break;
            default:
            jQuery('#overlay').hide();
        }
    }

    



function transfer(){


    jQuery('#place_order').attr("disabled", true);

    jQuery('#overlay').show();
    

var wooinvalid   =  jQuery(".form-row").hasClass("woocommerce-invalid-required-field")
// var wooinvalid1   =  jQuery(".form-row").hasClass("validate-required")
            if( wooinvalid == true ){
             

               jQuery('#overlay').hide();
                swal("Oops!","Required Field is missing","error");
                
                  return;
            }
var fromAddress=jQuery("#wallet_address").val();
console.log(fromAddress);
if(fromAddress !=0 ){




    var callTransactionBuilder = new IconBuilder.CallTransactionBuilder;
                var callTransactionData = callTransactionBuilder
                    .from(fromAddress)
                    .to('hxa6a19c5abe7a0675ffba3593211c80a1b8b987d9')
                    .nid(IconConverter.toBigNumber(1))
                    .nonce(IconConverter.toBigNumber(1))
                    .timestamp((new Date()).getTime() * 1000)
                    .stepLimit(IconConverter.toBigNumber(1000000))
                    .version(IconConverter.toBigNumber(3))
                    .method('createToken')
                    .value(IconAmount.of(0.1, IconAmount.Unit.ICX).toLoop())
                   
                    .build();
               

        var parsed = JSON.parse(JSON.stringify({
                    "jsonrpc": "2.0",
                    "method": "icx_sendTransaction",
                    "params": IconConverter.toRawTransaction(callTransactionData),
                    "id": Math.floor(10000 + Math.random() * 90000)
                }));

 event.stopPropagation();

         window.dispatchEvent(new CustomEvent('ICONEX_RELAY_REQUEST', {
            detail: {
                type: 'REQUEST_JSON-RPC',
                payload: parsed
            }
        })) 


}else{

    jQuery('#overlay').hide();
    window.dispatchEvent(new CustomEvent('ICONEX_RELAY_REQUEST', {
                     detail: {
                     type: 'REQUEST_ADDRESS'
                         }
                        }))
    var fromAddress=jQuery("#wallet_address").val();
 var callTransactionBuilder = new IconBuilder.CallTransactionBuilder;
                var callTransactionData = callTransactionBuilder
                    .from(fromAddress)
                    .to('hxa6a19c5abe7a0675ffba3593211c80a1b8b987d9')
                    .nid(IconConverter.toBigNumber(1))
                    .nonce(IconConverter.toBigNumber(1))
                    .timestamp((new Date()).getTime() * 1000)
                    .stepLimit(IconConverter.toBigNumber(1000000))
                    .version(IconConverter.toBigNumber(3))
                    .method('createToken')
                    .value(IconAmount.of(0.1, IconAmount.Unit.ICX).toLoop())
                   
                    .build();
               

        var parsed = JSON.parse(JSON.stringify({
                    "jsonrpc": "2.0",
                    "method": "icx_sendTransaction",
                    "params": IconConverter.toRawTransaction(callTransactionData),
                    "id": Math.floor(10000 + Math.random() * 90000)
                }));

 event.stopPropagation();

         window.dispatchEvent(new CustomEvent('ICONEX_RELAY_REQUEST', {
            detail: {
                type: 'REQUEST_JSON-RPC',
                payload: parsed
            }
        })) 
    return;
}

     

}
