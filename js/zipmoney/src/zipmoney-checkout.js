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