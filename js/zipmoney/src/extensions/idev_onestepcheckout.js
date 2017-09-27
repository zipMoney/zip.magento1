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
  saveBilling: function(url, set_methods_url, update_payments){
        var form = $('onestepcheckout-form');
        var items = exclude_unchecked_checkboxes($$('input[name^=billing]').concat($$('select[name^=billing]')));
        var names = items.pluck('name');
        var values = items.pluck('value');
        var parameters = {
            shipping_method: $RF(form, 'shipping_method')
        };


        var street_count = 0;
        for(var x=0; x < names.length; x++)    {
            if(names[x] != 'payment[method]')    {

                var current_name = names[x];

                if(names[x] == 'billing[street][]')    {
                    current_name = 'billing[street][' + street_count + ']';
                    street_count = street_count + 1;
                }

                parameters[current_name] = values[x];
            }
        }

        var use_for_shipping = $('billing:use_for_shipping_yes');

        if(use_for_shipping && use_for_shipping.getValue() != '1')    {
            var items = $$('input[name^=shipping]').concat($$('select[name^=shipping]'));
            var shipping_names = items.pluck('name');
            var shipping_values = items.pluck('value');
            var shipping_parameters = {};
            var street_count = 0;

            for(var x=0; x < shipping_names.length; x++)    {
                if(shipping_names[x] != 'shipping_method')    {
                    var current_name = shipping_names[x];
                    if(shipping_names[x] == 'shipping[street][]')    {
                        current_name = 'shipping[street][' + street_count + ']';
                        street_count = street_count + 1;
                    }

                    parameters[current_name] = shipping_values[x];
                }
            }
        }


      return new Ajax.Request(url, {
        method: 'post',
        parameters: parameters
      });
  },
  idevCheckout:function(e){
    var form = new VarienForm('onestepcheckout-form');

    already_placing_order = false;
  
    var url_save_billing = this.super.options.baseUrl + 'onestepcheckout/ajax/save_billing';
    var url_set_methods = this.super.options.baseUrl + 'onestepcheckout/ajax/set_methods_separate';

    if(!form.validator.validate())  {
      Event.stop(e);
    } else if(!already_placing_order && $$('.loading-ajax').length <= 0 ) {
      already_placing_order = true;       
      this.showLoader();
      this.disablePlaceOrderButton();    
      var xhr = this.saveBilling(url_save_billing, url_set_methods, false);
      var self = this;
      xhr.options.onSuccess = function(){      
        self.super.checkout();
      }
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