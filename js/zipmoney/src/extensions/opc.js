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
          var request = new Ajax.Request(
                this.saveUrl,
                {
                  method:'post',
                  onSuccess: _this.onSuccess.bind(_this),
                  onFailure: checkout.ajaxFailure.bind(checkout),
                  parameters: Form.serialize(this.form)
                }
             );
        } else{
          save(); //return default method
        }
      }
    });
  },
  onSuccess: function(transport) {

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
    }

    this._transport = transport;
    this.checkout(transport);
  },
  onComplete: function(response){    
    this._payment.resetLoadWaiting(this._transport);
    if(response.state == "approved"){
      this._payment.nextStep(this._transport);
    } else if(response.state == "referred"){
      location.href = this.options.redirectUrl + "?result=referred&checkoutId=" + response.checkoutId;
    }
  },
  onError: function(response){       
    console.log(response);
    alert("An error occurred while getting the redirect url from zipMoney");
    this._payment.resetLoadWaiting(this._transport);
  },
  checkout: function(){
    Zip.Checkout.init({
      redirect: this.options.redirect,
      checkoutUri: this.options.checkoutUri,
      redirectUri: this.options.redirectUrl,
      onComplete: this.onComplete.bind(this),
      onError: this.onError.bind(this)
    });
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_Mage_Checkout,'Mage_Checkout');
}

