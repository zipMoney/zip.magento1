if('Zip' in window && Zip.Checkout) {

    Object.extend(Zip.Checkout, {

        redirectTo: function(url) {
            document.getElementById('zip_payment_overlay').setAttribute('class', 'active');
            location.href = url;
        },

        placeOrder: function(callback) {

            if(payment.currentMethod == Zip.Checkout.settings.methodCode) {

                // if current display mode is lightbox and redirect url is not same as response url
                if(Zip.Checkout && !Zip.Checkout.settings.isRedirect) {

                    Zip.Checkout.init({
                        request: 'standard',
                        redirect: Zip.Checkout.settings.isRedirect,
                        checkoutUri: Zip.Checkout.settings.checkoutUrl,
                        redirectUri: Zip.Checkout.settings.responseUrl,
                        logLevel: Zip.Checkout.settings.logLevel,
                        onComplete: function (data) {
                            
                            var url = Zip.Checkout.settings.responseUrl + data.state;
                            
                            if(data.state == 'approved') {
                                Zip.Checkout.redirectTo(url);
                            }
                            else {

                                $j.ajax({
                                    url: url,
                                    type: 'GET',
                                    success: function(resp) {

                                        if(resp.error_message) {
                                            alert(resp.error_message);
                                        }
                                        else if(resp.redirect_url) {
                                            Zip.Checkout.redirectTo(resp.redirect_url);
                                        }
                                        
                                    }
                                });

                            }
                        },
                        onError: function (data) {
                            if(data.state) {
                                alert('Something wrong while processing your checkout. Checkout has been ' + data.state);
                            }
                            else {
                                // redirect to cart if status is not available
                                Zip.Checkout.redirectTo('/checkout/cart');
                            }
                            
                        },
                        logLevel: Zip.Checkout.settings.logLevel
                    });

                }
                else {
                    Zip.Checkout.redirectTo(redirectUrl);
                }

                return;

            }

            callback();

        }

    });


}