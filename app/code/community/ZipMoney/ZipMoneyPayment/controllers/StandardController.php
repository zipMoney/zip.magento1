<?php
use \zipMoney\ApiException;

class Zipmoney_ZipmoneyPayment_StandardController extends Zipmoney_ZipmoneyPayment_Controller_Abstract {

  protected $_checkoutType = 'zipmoneypayment/standard_checkout';
  protected $_apiClass = '\zipMoney\Client\Api\CheckoutsApi';

  public function indexAction() 
  { 
    try {        
      $this->_logger->info($this->_helper->__('Starting Checkout'));
      // Initialize the checkout model
      $this->_initCheckout();
      // Start the checkout process
      $this->_checkout->start();

      if($redirectUrl = $this->_checkout->getRedirectUrl()) { // If the redirect url is returned
      
        $this->_logger->info($this->_helper->__('Successful to get redirect url [ %s ] ', $redirectUrl));
        $isInContextCheckout = Mage::getSingleton("zipmoneypayment/config")->isInContextCheckout();
           
        $data = array( 'redirect_uri' => $redirectUrl ,'error_messages'  => $this->_helper->__('Redirecting to zipMoney.'));
        $this->_sendResponse($data, Mage_Api2_Model_Server::HTTP_OK);
      } else {
        $data = array( 'error_messages'  => $this->_helper->__('Can not redirect to zipMoney.'));
        $this->_sendResponse($data, Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        $this->_logger->warn($this->_helper->__('Failed to get redirect url.'));
      }

    } catch (Mage_Core_Exception $e) {
      $this->_getCheckoutSession()->addError($e->getMessage());      
      $this->_logger->debug($e->getMessage());
      $this->getResponse()->setHttpResponseCode(500);
    } catch (ApiException $e) {
      $this->_getCheckoutSession()->addError($this->__('Unable to checkout.'));
      $this->_logger->debug("Errors:-".json_encode($e->getResponseBody()));
      $this->getResponse()->setHttpResponseCode($e->getCode());
    } catch (Exception $e) {
      $this->_getCheckoutSession()->addError($this->__('Unable to checkout.'));
      $this->_logger->debug($e->getMessage());
      $this->getResponse()->setHttpResponseCode(500);
    }
  }

  
}
  
?>