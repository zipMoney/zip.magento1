var Zip_IWD_OnestepCheckout = Class.create();
Zip_IWD_OnestepCheckout.prototype = {
  super: null,
  _btn: null,
  _zipBtn: null,
  _zipBtnId : 'zipmoneypayment_place_order',
  initialize: function(superClass){},
  setup: function(superClass){    
    var _this = this;
    
    this.super = superClass;

    this.super._btn = $('iwd_opc_place_order_button');
    this.super._onComplete = this.onComplete.bind(this);
    this.super._onError = this.onError.bind(this);

    // Super object is IWD
    if(typeof IWD != 'undefined'){
      if(typeof IWD.OPC != 'undefined'){
        IWD.OPC.Plugin.event('saveOrder',function(){
          IWD.OPC.Checkout.saveOrderUrl = null;
          _this.super.checkout();
          return true;
        });
      }
    }
    // For IWD_OPC v6. Super object is OnePage
    if(OnePage !=undefined){  
      PaymentMethod.prototype.saveSection = function () {
        switch (this.getPaymentMethodCode()) {
          case Singleton.get(PaymentMethodStripe).code:
            Singleton.get(PaymentMethodStripe).originalThis = _this;
            Singleton.get(PaymentMethodStripe).originalArguments = _thisArguments;
            Singleton.get(PaymentMethodStripe).savePayment();
            break;
          case "zipmoneypayment":
            clearTimeout(Singleton.get(OnePage).validateTimeout);
            clearTimeout(Singleton.get(OnePage).blurTimeout);
            Singleton.get(OnePage).showLoader(Singleton.get(OnePage).sectionContainer);
            _this.super.checkout();
            break;
          default:
            OnePage.prototype.saveSection.apply(this, arguments);
        }
      };
    }
  },
  onComplete: function(response){     
    if(response.state == "approved" || response.state == "referred"){
      location.href = this.super.options.redirectUrl + "?result=" + response.state + "&checkoutId=" + response.checkoutId;
    } else {
      Singleton.get(OnePage).hideLoader(Singleton.get(OnePage).sectionContainer);
    }
  },
  onError: function(response){ 
    Singleton.get(OnePage).hideLoader(Singleton.get(OnePage).sectionContainer);
    alert("An error occurred while getting the redirect url from zipMoney");
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_IWD_OnestepCheckout,'Iwd_OnestepCheckout');
}
