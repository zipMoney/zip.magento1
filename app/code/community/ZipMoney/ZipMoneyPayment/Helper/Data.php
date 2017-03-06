<?php

class Zipmoney_ZipmoneyPayment_Helper_Data extends Zipmoney_ZipmoneyPayment_Helper_Abstract {


  public function getZipMoneyCheckoutLib()
  {
    return '<script src="https://static.zipmoney.com.au/checkout/checkout-v1.min.js"></script>';
  }
  
  public function getCheckoutJsLibUrl(){

    return '<script src="https://static.zipmoney.com.au/checkout/checkout-v1.min.js"></script><script src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS).'zipmoney/dist/scripts/zipmoney-checkout.js?v1.0.'.time().'"></script>';
  }

  public function json_encode($object)
  {
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
            Mage::throwException($this->__('Can not activate the quote. It has already been converted to order.'));
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
