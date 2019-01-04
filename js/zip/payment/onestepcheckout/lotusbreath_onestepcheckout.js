

if('Zip' in window && Zip.Checkout) {

    if('oscObserver' in window) {

        window.oscObserver.register('afterUpdatePaymentMethod', function(response){
            Zip.Checkout.saveRedirectUrl(response.results.payment.redirect);
        });

        window.oscObserver.register('beforeSubmitOrder', function(){
            Zip.Checkout.placeOrder();
        });
    
    }
    

}