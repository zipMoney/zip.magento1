<?php
class Zipmoney_ZipmoneyPayment_StandardController extends Zipmoney_ZipmoneyPayment_Controller_Abstract {

  protected $_checkoutType = 'zipmoneypayment/standard_checkout';

  public function indexAction() 
  { 

    try {
      $this->_initCheckout();

      // Start the checkout process
      $this->_checkout->start("checkout");

      if($checkoutId = $this->_checkout->getCheckoutId()) {
        $data = array( 'redirect_uri' => $this->_checkout->getRedirectUrl() ,'error_messages'  => $this->_helper->__('Redirecting to zipMoney.'));
        $this->_sendResponse($data, Mage_Api2_Model_Server::HTTP_OK);
        $this->_logger->info($this->_helper->__('Successful to get redirect url, which is ') . $this->_checkout->getRedirectUrl());
      } else {
        $data = array( 'redirect_url' => '', 'error_messages'  => $this->_helper->__('Can not redirect to zipMoney.'));
        $this->_sendResponse($data, Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        $this->_logger->warn($this->_helper->__('Failed to get redirect url.'));
      }
    } catch (Mage_Core_Exception $e) {
      $this->_getCheckoutSession()->addError($e->getMessage());      
      $this->_logger->debug($e->getMessage());
    } catch (Exception $e) {
      $this->_getCheckoutSession()->addError($this->__('Unable to start Checkout.'));
      $this->_logger->debug($e->getMessage());
    }
  }

}
  
?>