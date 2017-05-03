var Zip_Mage_Checkout = Class.create();

Zip_Mage_Checkout.prototype = {
  super: null,  
  _payment:null,
  _transport:null,
  _btn: null,
  initialize: function(superClass){
    this.super = superClass;
    this._btn = $$('.btn-checkout')[0];
  },
  setup: function(superClass){
    this.super = superClass;
    var _this = this;
    Payment.prototype.save = Payment.prototype.save.wrap(function(paymentSave) {
      _this._payment = this;
      var validator = new Validation(this.form);

      if (this.validate() && validator.validate()) {
        if (this.currentMethod=='zipmoneypayment'){
          checkout.setLoadWaiting('payment');
          _this.checkout();
        } else{
          paymentSave(); //return default method
        }
      }
    });
  },
  onSuccess: function(transport,resolve,reject) {

    if (transport && transport.responseText){
      try{
        response = eval('(' + transport.responseText + ')');
      }
      catch (e) {
        response = {};
      }
    }
    /*
     * if there is an error in payment, need to show error message
     */
    if (response.error) {
        if (response.fields) {
            var fields = response.fields.split(',');
            for (var i=0;i<fields.length;i++) {
                var field = null;
                if (field = $(fields[i])) {
                  Validation.ajaxError(field, response.error);
                }
            }
            return;
        }
        if (typeof(response.message) == 'string') {
            alert(response.message);
        } else {
            alert(response.error);
        }
        return;
    } else if(response.redirect_uri){    
      resolve({
        data: {redirect_uri: response.redirect_uri}
      });
    } else {
      reject();
    }

    this._transport = transport;
  },
  onComplete: function(response){    
    this._payment.resetLoadWaiting(this._transport);
    if(response.state == "approved"){
      this._payment.nextStep(this._transport);
    } else if(response.state == "referred"){
      location.href = this.super.options.redirectUrl + "?result=referred&checkoutId=" + response.checkoutId;
    }
  },
  onError: function(response){       
    alert("An error occurred while getting the redirect url from zipMoney");
    this._payment.resetLoadWaiting(this._transport);
  },
  onCheckout: function(resolve, reject, args){
    var _this = this;
    new Ajax.Request(
      this.super.options.checkoutUri,
      {
        method:'post',
        onSuccess: function(response){
          _this.onSuccess(response,resolve,reject).bind(_this);
        },
        onFailure: function(response){
          checkout.ajaxFailure.bind(checkout);
          reject();
        },
        parameters: Form.serialize(this._payment.form) + "&review=true"
      }
     );
  },
  checkout: function(){    
  
    Zip.Checkout.init({
      redirect: this.super.options.redirect,
      checkoutUri: this.super.options.checkoutUri,
      redirectUri: this.super.options.redirectUrl,
      onComplete: this.onComplete.bind(this),
      onError: this.onError.bind(this),
      onCheckout:this.onCheckout.bind(this)
    });
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_Mage_Checkout,'Mage_Checkout');
}

