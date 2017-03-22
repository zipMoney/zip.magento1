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
  protected $_chargeModel = 'zipmoneypayment/charge';
  
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

    // Is result valid ?
    if(!$this->_isResultValid()){            
      $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE);
      return;
    }

    $result = $this->getRequest()->getParam('result');
    
    $this->_logger->debug($this->_helper->__("Result:- %s", $result));

    try {

      // Is checkout id valid?
      if(!$this->getRequest()->getParam('checkoutId')){      
        $err_msg = $this->_helper->__("The checkoutId doesnot exist in the querystring.");
        $this->_logger->error($err_msg);
        Mage::throwException($err_msg);
      }

      // Set the customer quote
      $this->_setCustomerQuote();

      // Initialise the charge
      $this->_initCharge();

      // Set quote to the chekout model
      $this->_charge->setQuote($this->_getQuote());

    } catch (Exception $e) {
      $this->_getCheckoutSession()->addError($this->_helper->__('Unable to complete the checkout.'));
      $this->_logger->debug($e->getMessage());
      $this->_redirectToCart();
      return;
    }  

    // Handle the application result
    switch ($result) {

      case 'approved':
        /**
         * - Create order
         * - Create the charge against the customer using the checkout id
         */
        try {
          
          // Create the Order
          $this->_charge->placeOrder();

          $session = $this->_getCheckoutSession();
          $session->clearHelperData();
          
          $this->_logger->debug($this->_helper->__('Quote Id %s',$this->_getQuote()->getId()));

          // Set "last successful quote"
          if($quoteId = $this->_getQuote()->getId()){
            $session->setLastQuoteId($quoteId)
                    ->setLastSuccessQuoteId($quoteId);
          }

          if($order = $this->_charge->getOrder()) {
            $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
          }
          
          // Create charge for the order
          $this->_charge->charge();
         
          // Redirect to success page
          $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));
          return;
        } catch (Mage_Core_Exception $e) {
          $this->_getCheckoutSession()->addError($e->getMessage());      
          $this->_logger->debug($e->getMessage());
        } catch (ApiException $e) {
          $this->_getCheckoutSession()->addError($this->_helper->__('Unable to complete the checkout.'));
          $this->_logger->debug("Error:-".json_encode($e->getResponseBody()));
        } catch (Exception $e) {
          $this->_getCheckoutSession()->addError($this->_helper->__('Unable to complete the checkout.'));
          $this->_logger->debug($e->getMessage());
        }          

        $this->_redirectToCart();
        break;
      case 'declined':

        $this->_logger->debug($this->_helper->__('Calling declinedAction'));
        $this->_redirectToCart();

        break;
      case 'cancelled':  

        $this->_logger->debug($this->_helper->__('Calling cancelledAction'));
        $this->_redirectToCart();

        break;
      case 'referred':
        
        // Make sure the qoute is active
        $this->_helper->deactivateQuote($this->_getQuote());
        // Dispatch the referred action
        $this->_redirect(self::ZIPMONEY_STANDARD_ROUTE.'/referred');
        break;
      default:       
       // Dispatch the referred action
        $this->_redirectToCart();
        break;
    }
  }


}
?>