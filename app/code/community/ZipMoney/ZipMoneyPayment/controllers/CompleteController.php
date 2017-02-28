<?php
use \zipMoney\ApiException;

class Zipmoney_ZipmoneyPayment_CompleteController extends Zipmoney_ZipmoneyPayment_Controller_Abstract {
  
  protected $_validResults = array('approved','declined','cancelled','referred');

  protected $_apiClass  = '\zipMoney\Client\Api\ChargesApi';
  protected $_checkoutType = 'zipmoneypayment/standard_checkout';

  protected $_result = null;
  protected $_zipmoneyCid = null;

  const ZIPMONEY_STANDARD_ROUTE = "zipmoneypayment/standard";


  public function indexAction() 
  {
    /* 
     * TODO
     * - Check if the result exists. Check if checkout_id exists.
     * - Validate the checkout id.
     */
    $this->_logger->debug($this->_helper->__("On Callback Controller"));

    if(!$this->_isValidResult())
    {            
      $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/error');
      return;
    }

    $result = $this->getRequest()->getParam('result');
    
    $this->_logger->debug($this->_helper->__("State:- %s", $result));

    // Check the authenticity of the referrer
    switch ($result) {
      case 'approved':
        try {

          // Check if the checkout id is valid
          if(!$this->_isValidCheckoutId())
          {            
            $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/error');
            return;
          }

          // Initialize the checkout model
          if(!$this->_verifyQuote())
          {
            $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/error');
            return;
          }

          // Initialise the charge
          $this->_initCharge();

          // Make sure the qoute is active
          $this->_helper->activateQuote($this->_getQuote());
          
          $this->_checkout->setQuote($this->_getQuote());

          // Create the Order
          $this->_checkout->placeOrder();

          $session = $this->_getCheckoutSession();
          $session->clearHelperData();

          // Set "last successful quote"
          if($quoteId = $this->_getQuote()->getId()){
            $session->setLastQuoteId($quoteId)
                    ->setLastSuccessQuoteId($quoteId);
          }

          if($order = $this->_checkout->getOrder()) {
            $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
          }
          
          // Create charge for the order
          $this->_checkout->charge();

          // Redirect to success page
          $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));
          return;
        } catch (Mage_Core_Exception $e) {
          $this->_getCheckoutSession()->addError($e->getMessage());      
          $this->_logger->debug($e->getMessage());
          $this->getResponse()->setHttpResponseCode(500);
        } catch (ApiException $e) {
          $this->_getCheckoutSession()->addError($this->__('Unable to process the charge'));
          $this->_logger->debug("Error:-".json_encode($e->getResponseBody()));
          $this->getResponse()->setHttpResponseCode($e->getCode());
        } catch (Exception $e) {
          $this->_getCheckoutSession()->addError($this->__('Unable to process the charge'));
          $this->_logger->debug($e->getMessage());
          $this->getResponse()->setHttpResponseCode(500);
        }
        break;
      case 'declined':
        // Dispatch the declined action
        $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/declined');
        break;
      case 'cancelled':  
        // Dispatch the cancelled action
        $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/cancelled');
        break;
      case 'referred':
        // Dispatch the referred action
        $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/referred');
        break;
      default:       
       // Dispatch the referred action
        $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/error');
        break;

        # code...
        break;
    }
  }


}
  
?>