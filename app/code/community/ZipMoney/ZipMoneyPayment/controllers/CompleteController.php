<?php
use \zipMoney\ApiException;
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_CompleteController extends Zipmoney_ZipmoneyPayment_Controller_Abstract {
  
  /**
   * Valid Application Results
   *
   * @var array
   */  
  protected $_validResults = array('approved','declined','cancelled','referred');

  /**
   * Charges Api Class
   *
   * @var string
   */  
  protected $_apiClass  = '\zipMoney\Client\Api\ChargesApi';
  
  /**
   * Checkout Model
   *
   * @var string
   */
  protected $_checkoutType = 'zipmoneypayment/standard_checkout';
  
  /**
   * Common Route
   *
   * @const
   */
  const ZIPMONEY_STANDARD_ROUTE = "zipmoneypayment/standard";
  
  /**
   * Error Route
   *
   * @const
   */
  const ZIPMONEY_ERROR_ROUTE = self::ZIPMONEY_STANDARD_ROUTE."/error";


  /**
   * Return from zipMoney and handle the result of the application
   */
  public function indexAction() 
  {
    
    $this->_logger->debug($this->_helper->__("On Callback Controller"));

    // Is result valid?
    if(!$this->_isResultValid()){            
      $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE);
      return;
    }

    $result = $this->getRequest()->getParam('result');
    
    $this->_logger->debug($this->_helper->__("Result:- %s", $result));

    // Handle the application result
    switch ($result) {

      case 'approved':
        /**
         * - Create order
         * - Create the charge against the customer using the checkout id
         */

        try {

          // Is checkout id valid?
          if(!$this->_isCheckoutIdValid()){            
            $this->_redirect(self::ZIPMONEY_ERROR_ROUTE);
            return;
          }

          // Is the quote valid? 
          if(!$this->_verifyQuote()){
            $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE);
            return;
          }

          // Initialise the charge
          $this->_initCharge();

          // Make sure the qoute is active
          $this->_helper->activateQuote($this->_getQuote());

          // Set quote to the chekout model
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
    }
  }
}
?>