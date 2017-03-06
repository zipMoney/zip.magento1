var zipCheckout = Class.create();

zipCheckout.prototype = {
  _extensions:[],
  options: { 
    methodCode: "zipmoneypayment"
  },
  initialize: function(){},
  redirectToZip: function(){
    Zip.Checkout.init({
      redirect: this.options.redirect,
      checkoutUri: this.options.checkoutUri,
      completeUri: this.options.completeUri,
    });
  },
  onError: function(msg){
    console.log(msg);
  },  
  getCurrentExtension: function(){
    var $this = this;
    var extObj = null;
    
    this._extensions.each(function(extension){
      if(extension.name.toLowerCase() == $this.options.checkoutExtension.toLowerCase()){
        extObj = new extension.class;
      }
    });

    return extObj;
  },
  setup:function(config){
    var $this = this;

    try {
      this.options = Object.extend(this.options, config );      
      var ext = $this.getCurrentExtension();
      ext.setup(this);
    } catch (e){
      console.log(e);
    }
  },
  register: function(eClass,eName){
    // Add to list of valid widgets
    this._extensions.push({class:eClass,name:eName});
  },
}

window.$zipCheckout = new zipCheckout();
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


var Zip_Idev_OnestepCheckout = Class.create();
Zip_Idev_OnestepCheckout.prototype = {
  super: null,
  _btn: null,
  _zipBtn: null,
  _zipBtnId : 'zipmoneypayment-place-order',
  initialize: function(superClass){
    this._btn = $('onestepcheckout-place-order');
  },
  setup: function(superClass){    
    var $this = this;
    this.super = superClass;

    this.setupZipButton();        
    this.switchButtons();
    this.methodChange();

    Ajax.Responders.register({
      onComplete: function(request, transport) {
        // Avoid AJAX callback for internal AJAX request
        if (typeof request.parameters.doNotMakeAjaxCallback == 'undefined') {          
           $this.switchButtons();
        }
      }
    });
  },
  setupZipButton: function(){
    var btnClone = this._btn.clone(true);

    btnClone.setAttribute('id', this._zipBtnId);

    this._btn.insert({before: btnClone});
    var _this = this;
    btnClone.observe('click', function(e){
      _this.checkout(_this,e);
    });

    this._zipBtn = btnClone;
  },
  checkout:function(_this,e){
    var form = new VarienForm('onestepcheckout-form');
    
    already_placing_order = false;
    review = false;
    reviewmodal = false;

    if(!form.validator.validate())  {
      Event.stop(e);
    } else {

      if(!already_placing_order && $$('.loading-ajax').length <= 0 ) {
        already_placing_order = true;

        var submitEl = _this._zipBtn;
        var loaderEl = new Element('div').
            addClassName('onestepcheckout-place-order-loading').
            update('<img src="' + _this.super.options.loaderImageUrl + '" />&nbsp;&nbsp;Please wait, processing your order...');
        
        submitEl.parentNode.appendChild(loaderEl);
        submitEl.removeClassName('orange').addClassName('grey');
        submitEl.disabled = true;
        _this.super.redirectToZip();
      }
    }
  }, 
  // Displays buttonToShow and hides other
  switchButtons: function(hideAll) {
    var btnToShow = '';
    var submitEl = this._btn;
    var zipBtn = this._zipBtn;
    var buttons = [zipBtn, submitEl];

    if($$("onestepcheckout-place-order-loading").length){
      $$("onestepcheckout-place-order-loading").remove();
    }

    /* Disable button to avoid multiple clicks */
    submitEl.removeClassName('grey').addClassName('orange');
    submitEl.disabled = false;

    if (!hideAll) {
      if (payment.currentMethod == this.super.options.methodCode) {
        btnToShow = zipBtn;
      } else {
        btnToShow = submitEl;
      }
    } 

    buttons.each(function(elem){
      if (elem) {
        if (elem == btnToShow) {
          elem.show();
        } else {
          elem.hide();
        }
      }
    });
  },
  methodChange: function(){
    var paymentEls = $$('#checkout-payment-method-load input[name="payment[method]"]');
    var _this = this;

    paymentEls.each(function (el) {
      el.observe("click",function(){
        _this.switchButtons();
      });
    });

  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_Idev_OnestepCheckout,'Idev_OnestepCheckout');
}
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
var Zip_Mage_Checkout = Class.create();

Zip_Mage_Checkout.prototype = {
  super: null,  
  _btn: null,
  initialize: function(superClass){
    this.super = superClass;    
    this._btn = $('place-order');
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
  window.$zipCheckout.register(Zip_Mage_Checkout,'Mage_Checkout');
}

