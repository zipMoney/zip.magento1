var Zip_Mage_Checkout = Class.create();

Zip_Mage_Checkout.prototype = {
  super: null,
  _btn: null,
  initialize: function(superClass){
    this.super = superClass;
    this._btn = $$('.btn-checkout')[0];
  },
  setup: function(superClass){
    this.super = superClass;
    var _this = this;

    // if(payment.currentMethod == this.super.options.methodCode){
    //   if(this._btn) {
    //     this._btn.setAttribute('onclick', '');
    //     this._btn.observe('click',function(){
    //       _this.super.redirectToZip();
    //     })
    //   }
    // }

    document.observe("dom:loaded", function() {
      Payment.prototype.save = Payment.prototype.save.wrap(function(save) {
          var _payment = this;
          var validator = new Validation(this.form);

              if (this.validate() && validator.validate()) {

                if (payment.currentMethod=='zipmoneypayment'){
                    checkout.setLoadWaiting('payment');
                    var request = new Ajax.Request(
                           this.saveUrl,
                           {
                             method:'post',
                             onComplete: function(){},//remove default method like placeholder
                             onSuccess: function(transport){
                              _this.redirectZip(transport,_payment);
                             },//remove default method like placeholder
                             onFailure: checkout.ajaxFailure.bind(checkout),
                             parameters: Form.serialize(this.form)
                           }
                       );
                } else{
                  save(); //return default method
                }
              }

      });
    });
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_Mage_Checkout,'Mage_Checkout');
}

