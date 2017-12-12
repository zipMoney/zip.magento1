/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

var zipCheckout = Class.create();

zipCheckout.prototype = {
  _extensions:[],
  _btn: null,
  _zipBtn: null,
  _zipBtnId : 'zipmoneypayment-place-order',
  _selectedPaymentCode: null,
  _onComplete: null,
  _onError: null,
  options: { 
    methodCode: "zipmoneypayment"
  },
  initialize: function(){
    this._onComplete = this.onComplete.bind(this);
    this._onError = this.onError.bind(this);
  },
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
  onComplete: function(response){    
    if(response.state == "approved" || response.state == "referred"){
      location.href = this.options.redirectUrl + "?result=" + response.state + "&checkoutId=" + response.checkoutId;
    }
  },
  showError:function(args) {
    var error = "An error occurred while trying to checkout with zip.";
    var response = args.detail.response.evalJSON();

    // Check if the response object has the error text
    if(response.error){
      error = response.error;
    }

    alert(error);    
  },
  onError: function(args){       
   this.showError(args);
  },
  checkout: function(){
    Zip.Checkout.init({
      redirect: this.options.redirect,
      checkoutUri: this.options.checkoutUri,
      redirectUri: this.options.redirectUrl,
      onComplete: this._onComplete,
      onError: this._onError
    });
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
      console.log("Initializing zipMoney Checkout ... [ Checkout Extension :- " + config.checkoutExtension + " " + (ext!=undefined? 1 : 0 )  + " ] ");

      if(ext!=undefined){
        ext.setup(this);
      }
      queryParams = document.URL.toQueryParams();
      
      if (queryParams['zip-in-context'] != undefined &&  
          queryParams['zip-in-context'] == 'true') {
        this.checkout();
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
          _this.super.checkout();
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
          _this.super.checkout();
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
    this.super._onComplete = this.onComplete.bind(this);
    this.super._onError = this.onError.bind(this);
    this.super.setupZipPlaceOrderButton();
    this.super._zipBtn.observe('click',this.idevCheckout.bind(this));
    this.super.switchButtons();
    this.removeLoader();
    /* Disable button to avoid multiple clicks */
    this.super._btn.removeClassName('grey').addClassName('orange');
    this.super._btn.disabled = false;

    Ajax.Responders.register({
      onComplete: function(request, transport) {
        // Avoid AJAX callback for internal AJAX request
        if (typeof request.parameters.doNotMakeAjaxCallback == 'undefined') {   
          $this.methodChange();       
          $this.super.switchButtons();
        }
      }
    });
  },
  showLoader:function(){
    var submitEl = this.super._zipBtn;
    var loaderEl = new Element('div').
        addClassName('onestepcheckout-place-order-loading').
        update('<img src="' + this.super.options.loaderImageUrl + '" />&nbsp;&nbsp;Please wait, processing your order...');
    
    submitEl.parentNode.appendChild(loaderEl);
  },
  removeLoader:function(){
    var loadingEl = this.super._zipBtn.parentNode.select("div.onestepcheckout-place-order-loading");
    if(loadingEl.length){
      loadingEl[0].remove();
    }
  },
  disablePlaceOrderButton:function(){        
    this.super._zipBtn.removeClassName('orange').addClassName('grey');
    this.super._zipBtn.disabled = true;
  }, 
  enablePlaceOrderButton:function(){
    this.super._zipBtn.removeClassName('grey').addClassName('orange');
    this.super._zipBtn.disabled = false;
  },
  onComplete: function(response){       
    if(response.state == "approved" || response.state == "referred"){
      location.href = this.super.options.redirectUrl + "?result=" + response.state + "&checkoutId=" + response.checkoutId;
    } else {    
      this.removeLoader();
      this.enablePlaceOrderButton();
    }
  },
  onError: function(args){        
    this.removeLoader();
    this.enablePlaceOrderButton();
    this.super.showError(args);

  },
  idevCheckout:function(e){
    var form = new VarienForm('onestepcheckout-form');
    
    already_placing_order = false;
    review = false;
    reviewmodal = false;

    if(!form.validator.validate())  {
      Event.stop(e);
    } else if(!already_placing_order && $$('.loading-ajax').length <= 0 ) {
      already_placing_order = true;       
      this.showLoader();
      this.disablePlaceOrderButton();
      this.super.checkout();
    }
  }, 
  methodChange: function(){
    var paymentEls = $$('.payment-methods #checkout-payment-method-load input[name="payment[method]"]');
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
var Zip_IWD_OPC = Class.create();
Zip_IWD_OPC.prototype = {
  super: null,
  _btn: null,
  _zipBtn: null,
  _isV6:null,
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
        this._isV6 = false;
        IWD.OPC.Plugin.event('saveOrder',function(){
          if(window.payment.currentMethod == "zipmoneypayment"){
            IWD.OPC.Checkout.saveOrderUrl = null;
            _this.super.checkout();
            Event.stop(e);
            return true;
          }
        });
      }
    }

    // For IWD_OPC v6. Super object is OnePage
    if(typeof OnePage != 'undefined'){  
      this._isV6 = true;
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
      if(this._isV6){
        Singleton.get(OnePage).hideLoader(Singleton.get(OnePage).sectionContainer);
      } else {
        IWD.OPC.Checkout.hideLoader();
        IWD.OPC.Checkout.unlockPlaceOrder();
        IWD.OPC.saveOrderStatus = false;
      }
    }
  },
  onError: function(args){ 
    
    if(this._isV6){
      Singleton.get(OnePage).hideLoader(Singleton.get(OnePage).sectionContainer);
    } else {
      IWD.OPC.Checkout.hideLoader();
      IWD.OPC.Checkout.unlockPlaceOrder();
      IWD.OPC.saveOrderStatus = false;
    }
    
    this.super.showError(args);
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_IWD_OPC,'IWD_OPC');
}

var Zip_MageStore_OnestepCheckout = Class.create();

Zip_MageStore_OnestepCheckout.prototype = {
  super: null,  
  _btn: null,
  initialize: function(superClass){
  },
  setup: function(superClass){
    this.super = superClass;    
    this.super._btn = $('onestepcheckout-button-place-order');
    this.super._selectedPaymentCode = $RF(form, 'payment[method]');
    this.super._onComplete = this.onComplete.bind(this);
    this.super._onError = this.onError.bind(this);
    this.super.setupZipPlaceOrderButton();
    this.super._zipBtn.observe('click',this.magestoreCheckout.bind(this));
    this.super._zipBtn.setAttribute('onclick', '');
    this.super.switchButtons();

    var _this = this;

    Ajax.Responders.register({
      onComplete: function(request, transport) {
        // Avoid AJAX callback for internal AJAX request
        if (typeof request.parameters.doNotMakeAjaxCallback == 'undefined') {   
          _this.methodChange();                   
          _this.super.switchButtons();
          if(_this.super._selectedPaymentCode == _this.super.options.methodCode){            
            _this.enablePlaceOrderButton();
          }  
        }
      }
    });
  },
  onComplete: function(response){       
    if(response.state == "approved" || response.state == "referred"){
      location.href = this.super.options.redirectUrl + "?result=" + response.state + "&checkoutId=" + response.checkoutId;
    } else {    
      this.enablePlaceOrderButton();
    }
  },
  onError: function(args){        
    this.enablePlaceOrderButton();
    this.super.showError(args);
  },
  disablePlaceOrderButton:function(){        
    this.super._zipBtn.removeClassName('onestepcheckout-btn-checkout').addClassName('place-order-loader');
    this.super._zipBtn.disabled = true;
  }, 
  enablePlaceOrderButton:function(){
    this.super._zipBtn.removeClassName('place-order-loader').addClassName('onestepcheckout-btn-checkout');
    this.super._zipBtn.disabled = false;
  },
  magestoreCheckout: function(){    
    var payment_method = $RF(form, 'payment[method]');
    var validator = new Validation('one-step-checkout-form');
    var _this = this;
            
    already_placing_order = false;
    
    if(!already_placing_order && validator.validate() && checkpayment()) {
      already_placing_order = true;       
      this.disablePlaceOrderButton();
      this.super.checkout();
    }
  },
  methodChange: function(){
    var paymentEls = $$('#checkout-payment-method-load input[name="payment[method]"]');
    var _this = this;
     
    paymentEls.each(function (el) {
      el.observe("click",function(){
        _this.super._selectedPaymentCode = $RF(form, 'payment[method]');
        _this.super.switchButtons();
      });
    });
  }
}

if(window.$zipCheckout != undefined){
  window.$zipCheckout.register(Zip_MageStore_OnestepCheckout,'MageStore_OnestepCheckout');
}

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

      if (validator.validate()) {
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
      if(this.super.options.redirectAfterPayment == 1)
      {
        this.setOverlay();
        location.href = this.super.options.redirectUrl + "?result=approved&checkoutId=" + response.checkoutId;
      } else {
        this._payment.nextStep(this._transport);
      }
    } else if(response.state == "referred"){        
      this.setOverlay();
      location.href = this.super.options.redirectUrl + "?result=referred&checkoutId=" + response.checkoutId;
    }
  },
  setOverlay:function(){
    var myDiv = new Element('div');
    myDiv.update("<br/>Please wait. You are being redirected...")
    myDiv.addClassName("zipmoneypayment-overlay");
    $(document.body).insert(myDiv);
  },
  onError: function(args){       
    var error = "An error occurred while trying to checkout with zip.";
    
    // Check if the response object has the error text
    if(args.detail.responseJSON.error){
      error = args.detail.responseJSON.error;
    }

    alert(error);    

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
          reject(response);
        },
        parameters: Form.serialize(this._payment.form)
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

