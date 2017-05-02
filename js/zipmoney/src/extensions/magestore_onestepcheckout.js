var Zip_MageStore_OnestepCheckout = Class.create();

Zip_MageStore_OnestepCheckout.prototype = {
  super: null,  
  _btn: null,
  initialize: function(superClass){
    this.super = superClass;    
    this._btn = $('onestepcheckout-button-place-order');
  },
  setup: function(superClass){
    this.super = superClass;

    this.switchButtons();       

    Ajax.Responders.register({
      onComplete: function(request, transport) {
        // Avoid AJAX callback for internal AJAX request
        if (typeof request.parameters.doNotMakeAjaxCallback == 'undefined') {   
          _this.methodChange();       
        }
      }
    });
   
  },
  switchButtons: function(){    
    var payment_method = $RF(form, 'payment[method]');
    var validator = new Validation('one-step-checkout-form');
    var _this = this;

    if(payment_method == this.super.options.methodCode){
      if(this._btn) {
        this._btn.setAttribute('onclick', '');
        this._btn.observe('click',function(){
          if (validator.validate() && checkpayment()) {
            this.disabled = true;
            disable_payment();
            _this.super.checkout();
          }
        })
      }
    } else {
      if(this._btn) {
        console.log(2222);
        this._btn.setAttribute('onclick', 'oscPlaceOrder(this);');
      }
    }
  },
  methodChange: function(){
    var paymentEls = $$('#checkout-payment-method-load input[name="payment[method]"]');
    var _this = this;
    
    paymentEls.each(function (el) {
      el.observe("click",function(){
        console.log(4444)
        _this.super._selectedPaymentCode = $RF(form, 'payment[method]');
        _this.switchButtons();
      });
    });
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_MageStore_OnestepCheckout,'MageStore_OnestepCheckout');
}

