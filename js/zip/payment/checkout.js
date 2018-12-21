if('Zip' in window && Zip.Checkout) {

    Object.extend(Zip.Checkout, {

        saveRedirectUrl: function(redirect){

            if(payment.currentMethod == Zip.Checkout.settings.methodCode) {
        
                if(redirect) {
                    Zip.Checkout.settings.redirectUrl = redirect;
                }

            } 
        
        },

        placeOrder: function(callback) {

            if(payment.currentMethod == Zip.Checkout.settings.methodCode) {
    
                var redirectUrl = Zip.Checkout.settings.redirectUrl;

                // if current display mode is lightbox and redirect url is not same as response url
                if(Zip.Checkout && !Zip.Checkout.settings.isRedirect && redirectUrl.indexOf(Zip.Checkout.settings.responseUrl) == -1) {

                    Zip.Checkout.init({
                        request: 'standard',
                        redirect: Zip.Checkout.settings.isRedirect,
                        checkoutUri: Zip.Checkout.settings.checkoutUrl + '?redirect_url=' + encodeURIComponent(redirectUrl),
                        redirectUri: Zip.Checkout.settings.responseUrl,
                        onComplete: function (data) {
                            
                            var url = Zip.Checkout.settings.responseUrl + data.state;
                            
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
                        logLevel: Zip.Checkout.settings.logLevel
                    });

                }
                else {
                    location.href = redirectUrl;
                }

                return;

            }

            callback();

        }

    });


}