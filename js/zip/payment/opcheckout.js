if('payment' in window) {

    Object.extend(payment, {

        paymentOnSave: payment.onSave,

        onSave: function(transport){

            if (!transport) return;
        
            if(payment.currentMethod == Zip.Checkout.config.methodCode) {

                var response = transport.responseJSON || transport.responseText.evalJSON(true) || {};
        
                if(response.redirect) {
                    Zip.Checkout.config.redirectUrl = response.redirect;
                }

            }
        
            payment.paymentOnSave(transport);
        
        }

    });

}

if('Review' in window) {

    Object.extend(Review.prototype, {

        reviewSave: Review.prototype.save,

        save: function() {
            
            if(payment.currentMethod == Zip.Checkout.config.methodCode) {

                var redirectUrl = Zip.Checkout.config.redirectUrl;

                // if current disaply mode is lightbox and redirect url is not same as response url
                if(!Zip.Checkout.config.isRedirect && redirectUrl.indexOf(Zip.Checkout.config.responseUrl) == -1) {

                    Zip.Checkout.init({
                        request: 'standard',
                        redirect: Zip.Checkout.config.isRedirect,
                        checkoutUri: Zip.Checkout.config.checkoutUrl + '?redirect_url=' + encodeURIComponent(redirectUrl),
                        redirectUri: Zip.Checkout.config.responseUrl,
                        onComplete: function (data) {
                            
                            var url = Zip.Checkout.config.responseUrl + data.state;
                            
                            if(data.state == 'approved') {
                                location.href = url;
                            }
                            else {

                                $j.ajax({
                                    url: url,
                                    type: 'GET',
                                    success: function(resp) {
                                        resp = JSON.parse(resp);

                                        if(resp.error_message) {
                                            alert(resp.error_message);
                                        }
                                        
                                    }
                                });

                            }
                        },
                        onError: function (data) {
                            alert('Something wrong while processing your checkout. Checkout has been ' + data.state);
                        },
                        logLevel: Zip.Checkout.config.logLevel
                    });

                }
                else {
                    location.href = redirectUrl;
                }

                return;

            }

            this.reviewSave();

        }

    });

}