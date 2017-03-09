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
    var _this = this;
    var validator = new Validation('one-step-checkout-form');

    if(payment.currentMethod == this.super.options.methodCode){
      if(this._btn) {
        this._btn.setAttribute('onclick', '');
        this._btn.observe('click',function(){
          if (validator.validate() && checkpayment()) {
            this.disabled = true;
            disable_payment();
            _this.super.redirectToZip();
          }
        })
      }
    } 
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_MageStore_OnestepCheckout,'MageStore_OnestepCheckout');
}

