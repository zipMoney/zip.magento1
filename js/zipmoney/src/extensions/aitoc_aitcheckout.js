var Zip_Aitoc_Aitcheckout = Class.create();

Zip_Aitoc_Aitcheckout.prototype = {
  super: null,
  initialize: function(superClass){},
  setup: function(superClass){    
    var $this = this;
    this.super = superClass;
    this.super._btn = $('aitcheckout-place-order');
    this.super._selectedPaymentCode =  payment.currentMethod;
    this.super._onComplete = this.onComplete.bind(this);
    this.super._onError = this.onError.bind(this);
    this.super.setupZipPlaceOrderButton();
    this.super._zipBtn.setAttribute('onclick', '');
    this.super._zipBtn.observe('click',this.checkout.bind(this));
    this.super.switchButtons();
    this.resetLoadWaiting();
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
 
  setLoadWaiting: function(){
    var container = $('checkout-buttons-container');
    container.addClassName('disabled');
    container.setStyle({opacity:.8});
    this._disableEnableAll(container, true);
    Element.show('checkout-please-wait');
  },
  resetLoadWaiting: function(){
    var container = $('checkout-buttons-container');
    container.removeClassName('disabled');
    container.setStyle({opacity:1});
    this._disableEnableAll(container, false);
    Element.hide('checkout-please-wait');
  },
  _disableEnableAll: function(element, isDisabled) {
  var descendants = element.descendants();
    for (var k in descendants) {
        descendants[k].disabled = isDisabled;
    }
    element.disabled = isDisabled;
  },


  onComplete: function(response){       
    if(response.state == "approved" || response.state == "referred"){
      location.href = this.super.options.redirectUrl + "?result=" + response.state + "&checkoutId=" + response.checkoutId;
    } else {    
      this.resetLoadWaiting();
    }
  },
  onError: function(args){        
    this.resetLoadWaiting();
    this.super.showError(args);

  },
  checkout:function(e){

    var validator = new Validation(aitCheckout.getForm());
    if (validator && validator.validate()){
        this.setLoadWaiting();
        if (0 < Ajax.activeRequestCount)
        {
            aitCheckout.runSaveAfterUpdate = true;
            return;
        } else {
            aitCheckout.runSaveAfterUpdate = false;
        }

        var params = Form.serialize(aitCheckout.getForm());
        if (this.agreementsForm) {
            params += '&'+Form.serialize(this.agreementsForm);
        }
        params.save = true;
        this.super.checkout();

  
    } else {
        if (0 < Ajax.activeRequestCount)
        {
            aitCheckout.valid = false;
        }
    }


  }, 
  methodChange: function(){
    var paymentEls = $$('#opc-payment #checkout-payment-method-load input[name="payment[method]"]');
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
Zip_Aitoc_Aitcheckout}