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