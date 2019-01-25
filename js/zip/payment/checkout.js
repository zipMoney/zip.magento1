if('Zip' in window && Zip.Checkout) {

    Object.extend(Zip.Checkout, {

        showOverlay: function() {
            document.getElementById('zip_payment_overlay').setAttribute('class', 'active');
        },
        hideOverlay: function() {
            document.getElementById('zip_payment_overlay').removeAttribute('class');
        },
        redirectTo: function(url) {
            location.href = url;
        },

        placeOrder: function(callback) {

            if(payment.currentMethod == Zip.Checkout.settings.methodCode) {

                Zip.Checkout.showOverlay();

                // if current display mode is lightbox and redirect url is not same as response url
                if(Zip.Checkout) {

                    Zip.Checkout.init({
                        request: 'standard',
                        redirect: Zip.Checkout.settings.isRedirect,
                        checkoutUri: Zip.Checkout.settings.checkoutUrl,
                        redirectUri: Zip.Checkout.settings.responseUrl,
                        logLevel: Zip.Checkout.settings.logLevel,
                        onComplete: function (data) {
                            
                            var url = Zip.Checkout.settings.responseUrl + data.state;

                            switch(data.state) {
                                case 'approved': 
                                    Zip.Checkout.redirectTo(url);
                                break;
                                case 'cancelled':
                                    Zip.Checkout.hideOverlay();
                                    return;
                                default:
                                    $j.ajax({
                                        url: url,
                                        type: 'GET',
                                        success: function(resp) {

                                            if(resp.error_message) {
                                                Zip.Checkout.hideOverlay();
                                                alert(resp.error_message);
                                            }
                                            else if(resp.redirect_url) {
                                                Zip.Checkout.redirectTo(resp.redirect_url);
                                            }
                                            
                                        }
                                    });
                                break;

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
                
                return;

            }

            callback();

        }

    });


}