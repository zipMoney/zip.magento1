var Zip_MageStore_OnestepCheckout = Class.create();

Zip_MageStore_OnestepCheckout.prototype = {
  super: null,  
  _btn: null,
  initialize: function(superClass){
  },
  setup: function(superClass){
    this.super = superClass;    
    this.super._btn = $('onestepcheckout-button-place-order');
    this.super._selectedPaymentCode = $RF(form, 'payment[method]');
    this.super._onComplete = this.onComplete.bind(this);
    this.super._onError = this.onError.bind(this);
    this.super.setupZipPlaceOrderButton();
    this.super._zipBtn.observe('click',this.magestoreCheckout.bind(this));
    this.super._zipBtn.setAttribute('onclick', '');
    this.super.switchButtons();

    var _this = this;

    Ajax.Responders.register({
      onComplete: function(request, transport) {
        // Avoid AJAX callback for internal AJAX request
        if (typeof request.parameters.doNotMakeAjaxCallback == 'undefined') {   
          _this.methodChange();                   
          _this.super.switchButtons();
          if(_this.super._selectedPaymentCode == _this.super.options.methodCode){            
            _this.enablePlaceOrderButton();
          }  
        }
      }
    });
  },
  onComplete: function(response){       
    if(response.state == "approved" || response.state == "referred"){      
      this.super.setOverlay();
      location.href = this.super.options.redirectUrl + "?result=" + response.state + "&checkoutId=" + response.checkoutId;
    } else {    
      this.enablePlaceOrderButton();
    }
  },
  onError: function(args){        
    this.enablePlaceOrderButton();
    this.super.showError(args);
  },
  disablePlaceOrderButton:function(){        
    this.super._zipBtn.removeClassName('onestepcheckout-btn-checkout').addClassName('place-order-loader');
    this.super._zipBtn.disabled = true;    
    $('onestepcheckout-place-order-loading').show();

  }, 
  enablePlaceOrderButton:function(){
    this.super._zipBtn.removeClassName('place-order-loader').addClassName('onestepcheckout-btn-checkout');
    this.super._zipBtn.disabled = false;    
    $('onestepcheckout-place-order-loading').hide();
  },
  magestoreCheckout: function(){    
    var payment_method = $RF(form, 'payment[method]');
    var validator = new Validation('one-step-checkout-form');
    var _this = this;
            
    already_placing_order = false;
    
    if(!already_placing_order && validator.validate() && checkpayment()) {
      already_placing_order = true;       
      this.disablePlaceOrderButton();

      try{
        if (typeof save_address_url != 'undefined') {
          var shipping_method = $RF(form, 'shipping_method');
          var parameters = {shipping_method: shipping_method};

          get_billing_data(parameters);
          get_shipping_data(parameters);
        
          var request = new Ajax.Request(save_address_url, {
            parameters: parameters,
            onSuccess: function (transport) {
              if (transport.status == 200) {
                _this.super.checkout();
              }
            },
            onFailure: function(transport){
              alert("An error occurred while saving the address information");    
            }
          });
        } 
      } catch (e){
        console.log(e);
      }
    }
  },
  methodChange: function(){
    var paymentEls = $$('#checkout-payment-method-load input[name="payment[method]"]');
    var _this = this;
     
    paymentEls.each(function (el) {
      el.observe("click",function(){
        _this.super._selectedPaymentCode = $RF(form, 'payment[method]');
        _this.super.switchButtons();
      });
    });
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_MageStore_OnestepCheckout,'MageStore_OnestepCheckout');
}
