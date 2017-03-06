var Zip_IWD_OnestepCheckout = Class.create();
Zip_IWD_OnestepCheckout.prototype = {
  super: null,
  initialize: function(superClass){},
  setup: function(superClass){    
    var _this = this;
    this.super = superClass;

    IWD.OPC.Plugin.event('saveOrder',function(){
      IWD.OPC.Checkout.saveOrderUrl = null;
      _this.super.redirectToZip();
      return true;
    });
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_IWD_OnestepCheckout,'Iwd_OnestepCheckout');
}