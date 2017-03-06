var Aitoc_Aitcheckout = Class.create();

Aitoc_Aitcheckout.prototype = {
  super: null,  
  _btn: null,
  initialize: function(superClass){
    this.super = superClass;    
    this._btn = $('aitcheckout-place-order');
  },
  setup: function(superClass){
    this.super = superClass;
    var _this = this;
    
    if(payment.currentMethod == this.super.options.methodCode){
      if(this._btn) {
        this._btn.setAttribute('onclick', '');
        this._btn.observe('click',function(){
          _this.super.redirectToZip();
        })
      }
    } 
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Aitoc_Aitcheckout,'Aitoc_Aitcheckout');
}

