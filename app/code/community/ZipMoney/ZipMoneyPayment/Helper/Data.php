<?php

class Zipmoney_ZipmoneyPayment_Helper_Data extends Mage_Core_Helper_Abstract {

  public function getCheckoutJs(){
    
    return '<script src="http://local.zipmoney.com.au/zipmoney.web.checkout/dist/checkout-v1.js?v2"></script>
    <script src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS)."zipmoney/zipmoney-checkout.js?v1" . '"></script>';

  }

  public function getZipMoneyCheckoutLib()
  {

    return '<script src="http://local.zipmoney.com.au/zipmoney.web.checkout/dist/checkout-v1.js"></script>';
  }
  
  public function getIframeLibUrl(){

  }

  public function json_encode($object)
  {
    return json_encode(\zipMoney\ObjectSerializer::sanitizeForSerialization($object));
  }
}
