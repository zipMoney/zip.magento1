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
   * Charge Model
   *
   * @var string
   */
  protected $_chargeModel = 'zipmoneypayment/charge';
  
  /**
   * Return from zipMoney and handle the result of the application
   *
   * @throws Mage_Core_Exception
   */
  public function indexAction() 
  {
    $this->_logger->debug($this->_helper->__("On Complete Controller"));

    try {
      // Is result valid ?
      if(!$this->_isResultValid()){            
        $this->_redirectToCartOrError();
        return;
      }
      $result = $this->getRequest()->getParam('result');
      $this->_logger->debug($this->_helper->__("Result:- %s", $result));
      // Is checkout id valid?
      if(!$this->getRequest()->getParam('checkoutId')){      
        Mage::throwException($this->_helper->__("The checkoutId doesnot exist in the querystring."));
      }
      // Set the customer quote
      $this->_setCustomerQuote();
      // Initialise the charge
      $this->_initCharge();
      // Set quote to the chekout model
      $this->_charge->setQuote($this->_getQuote());
    } catch (Exception $e) {
      $this->_getCheckoutSession()->addError($this->_helper->__('Unable to complete the checkout.'));
      $this->_logger->error($e->getMessage());
      $this->_redirectToCartOrError();
      return;
    }  

    $order_status_history_comment = '';

    /* Handle the application result */
    switch ($result) {

      case 'approved':
        /**
         * - Create order
         * - Charge the customer using the checkout id
         */
        try {
          // Create the Order
          $order = $this->_charge->placeOrder();

          $this->_charge->charge();
          // Redirect to success page
          return $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));
        } catch (Mage_Core_Exception $e) {
          
          $this->_getCheckoutSession()->addError($e->getMessage());      
          $this->_logger->debug($e->getMessage());
       
        } catch (Exception $e) {
          
          $this->_getCheckoutSession()->addError($this->_helper->__('An error occurred while to completing the checkout.'));
          $this->_logger->debug($e->getMessage());          
        }

        $this->_redirectToCartOrError();
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
        $this->_redirectToCartOrError();
        break;
    }
  }
  /**
   * Return from zipMoney and handle the result of the application
   */
  public function chargeAction()
  {          
    $session = $this->_getCheckoutSession();

    $orderId = $session->getLastOrderId();
    $quoteId = $session->getLastQuoteId();

    $order = Mage::getSingleton("sales/order")->load($orderId);
    $quote = Mage::getSingleton("sales/quote")->load($quoteId);
    
    $this->_logger->debug($this->_helper->__("On Charge Order Action"));
    
    try {
        // Check if the quote exists
      if(!$quote->getId()){
        Mage::throwException($this->_helper->__("The quote doesnot exist."));
      }  
      if(!$order->getId()){
        Mage::throwException($this->_helper->__("The order doesnot exist."));
      }
      // Check if the zipMoney Checkout Id Exists
      if(!$quote->getZipmoneyCid()){
        Mage::throwException($this->_helper->__("The order has not been approved by zipMoney or the zipMoney Checkout Id doesnot exist."));
      }
      // Check if the Order Has been charged
      if($order->getPayment()->getZipmoneyChargeId()){
        Mage::throwException($this->_helper->__("The order has already been charged."));
      }
      // Initialise the charge
      $this->_charge = Mage::getSingleton('zipmoneypayment/charge');
      // Set quote to the chekout model
      $this->_charge->setOrder($order)
                    ->charge();
      return $this->getResponse()->setRedirect(Mage::getUrl('checkout/onepage/success'));
    } catch (Mage_Core_Exception $e) {
      $this->_getCheckoutSession()->addError($e->getMessage());      
      $this->_logger->debug($e->getMessage());
    } catch (Exception $e) {
      $this->_logger->debug($e->getMessage());
      $this->_getCheckoutSession()->addError($this->_helper->__('An error occurred while to trying to complete the checkout.'));
    }
    $this->_redirectToCartOrError();
  }
}
