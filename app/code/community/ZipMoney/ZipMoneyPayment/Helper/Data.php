<?php

class Zipmoney_ZipmoneyPayment_Helper_Data extends Zipmoney_ZipmoneyPayment_Helper_Abstract {

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
    //print_r($object);
    return json_encode(\zipMoney\ObjectSerializer::sanitizeForSerialization($object));
  }

  /**
   * Get current store url
   *
   * @param $route
   * @param $param
   * @return string
   */
  public function getUrl($route, $param)
  {
    $storeId = Mage::getSingleton('zipmoneypayment/storeScope')->getStoreId();
    if ($storeId !== null) {
      $store = Mage::app()->getStore($storeId);
      $url = $oStore->getUrl($route, $param);
    } else {
      $url = Mage::getUrl($route, $param);
    }
    return $url;
  }


  /**
   * @param $oQuote
   * @return bool
   * @throws Mage_Core_Exception
   */
  public function activateQuote($quote)
  {
    if ($quote && $quote->getId()) {
      if (!$quote->getIsActive()) {
          
        $orderIncId = $quote->getReservedOrderId();
        
        if ($orderIncId) {
          $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncId);
          if ($order && $order->getId()) {
            $vMessage = $this->__('Can not activate the quote. It has already been converted to order.');
            throw Mage::exception('Zipmoney_ZipmoneyPayment', $vMessage);
          }
        }

        $quote->setIsActive(1)
              ->save();
        $this->_logger->warn($this->__('Activated quote ' . $quote->getId() . '.'));
        return true;
      }
    }
    return false;
  }

  /**
   * @param $quote
   * @return bool
   */
  public function deactivateQuote($quote)
  {
    if ($quote && $quote->getId()) {
      if ($quote->getIsActive()) {
        $quote->setIsActive(0)->save();
        $this->_logger->warn($this->__('Deactivated quote ' . $quote->getId() . '.'));
        return true;
      }
    }
    return false;
  }



  
}
