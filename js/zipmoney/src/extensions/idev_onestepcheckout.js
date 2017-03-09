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