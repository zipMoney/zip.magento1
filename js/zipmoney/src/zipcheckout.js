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
  onError: function(response){       
    alert("An error occurred while getting the redirect url from zipMoney");
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