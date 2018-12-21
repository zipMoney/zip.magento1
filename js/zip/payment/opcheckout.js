

if('Zip' in window && Zip.Checkout) {

    if('payment' in window) {

        Object.extend(payment, {
    
            paymentOnSave: payment.onSave,
    
            onSave: function(transport){
    
                if (!transport) return;

                var response = transport.responseJSON || transport.responseText.evalJSON(true) || {};

                // save redirect url from response
                Zip.Checkout.saveRedirectUrl(response.redirect);

                payment.paymentOnSave(transport);
            
            }
    
        });
    
    }
    
    if('Review' in window) {
    
        Object.extend(Review.prototype, {
    
            reviewSave: Review.prototype.save,
    
            save: function() {
                
                // support Zip Payment order placement
                Zip.Checkout.placeOrder(this.reviewSave);

            }
    
        });
    
    }

}