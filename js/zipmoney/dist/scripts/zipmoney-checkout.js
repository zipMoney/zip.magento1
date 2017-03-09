var zipCheckout = Class.create();

zipCheckout.prototype = {
  _extensions:[],
  _btn: null,
  _zipBtn: null,
  _zipBtnId : 'zipmoneypayment-place-order',
  _selectedPaymentCode: null,
  options: { 
    methodCode: "zipmoneypayment"
  },
  initialize: function(){},
  setupZipPlaceOrderButton: function(){
    var btnClone = this._btn.clone(true);
    var _this = this;

    btnClone.setAttribute('id', this._zipBtnId);

    this._btn.insert({before: btnClone});
    
    this._zipBtn = btnClone;
  },
  // Displays buttonToShow and hides other
  switchButtons: function(hideAll) {
    var btnToShow = '';
    var submitEl = this._btn;
    var zipBtn = this._zipBtn;
    var buttons = [zipBtn, submitEl];

    if (!hideAll) {
      if (this._selectedPaymentCode == this.options.methodCode) {
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
  redirectToZip: function(){
    Zip.Checkout.init({
      redirect: this.options.redirect,
      checkoutUri: this.options.checkoutUri,
      completeUri: this.options.completeUri
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
    var $this = this, ext, queryParams;

    try {
      this.options = Object.extend(this.options, config );    
      
      ext = $this.getCurrentExtension();

      if(ext!=undefined){
        ext.setup(this);
      }

      queryParams = document.URL.toQueryParams();
      
      if (queryParams['zip-in-context'] != undefined &&  
          queryParams['zip-in-context'] == 'true') {
        this.redirectToZip();
      }
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


var GoMage_Checkout = Class.create();

GoMage_Checkout.prototype = {
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
  window.$zipCheckout.register(GoMage_Checkout,'GoMage_Checkout');
}


var Zip_Idev_OnestepCheckout = Class.create();
Zip_Idev_OnestepCheckout.prototype = {
  super: null,
  initialize: function(superClass){},
  setup: function(superClass){    
    var $this = this;
    this.super = superClass;
    this.super._btn = $('onestepcheckout-place-order');
    this.super._selectedPaymentCode =  payment.currentMethod;

    this.super.setupZipPlaceOrderButton();
    
    this.super._zipBtn.observe('click', function(e){
      _this.checkout(_this,e);
    });

    this.super.switchButtons();
    this.methodChange();

    if($$("onestepcheckout-place-order-loading").length){
      $$("onestepcheckout-place-order-loading").remove();
    }

    /* Disable button to avoid multiple clicks */
    this._btn.removeClassName('grey').addClassName('orange');
    this._btn.disabled = false;

    Ajax.Responders.register({
      onComplete: function(request, transport) {
        // Avoid AJAX callback for internal AJAX request
        if (typeof request.parameters.doNotMakeAjaxCallback == 'undefined') {          
           $this.super.switchButtons();
        }
      }
    });
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
  methodChange: function(){
    var paymentEls = $$('#checkout-payment-method-load input[name="payment[method]"]');
    var _this = this;
    paymentEls.each(function (el) {
      el.observe("click",function(){
        _this.super._selectedPaymentCode = payment.currentMethod;
        _this.super.switchButtons();
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
  _btn: null,
  _zipBtn: null,
  _zipBtnId : 'zipmoneypayment_place_order',
  initialize: function(superClass){},
  setup: function(superClass){    
    var _this = this;
    
    this.super = superClass;
    this.super._btn = $('iwd_opc_place_order_button');

    // Super object is IWD
    if(typeof IWD != 'undefined'){
      IWD.OPC.Plugin.event('saveOrder',function(){
        IWD.OPC.Checkout.saveOrderUrl = null;
        _this.super.redirectToZip();
        return true;
      });
    }

    // For IWD_OPC v6. Super object is OnePage
    if(OnePage !=undefined){     
      PaymentMethod.prototype.saveSection = function () {
        this.showLoader(Singleton.get(OnePage).sectionContainer);
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
            _this.super.redirectToZip();
          default:
            OnePage.prototype.saveSection.apply(this, arguments);
        }
      };
    }
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_IWD_OnestepCheckout,'Iwd_OnestepCheckout');
}

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

