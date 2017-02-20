<?php
error_reporting(E_ALL);

class Zipmoney_ZipmoneyPayment_CallbackController extends Zipmoney_ZipmoneyPayment_Controller_Abstract {
  
  const VALID_STATES = array('approved','declined','cancelled','referred');
  
  protected $_chargeApiClass = 'zipmoneypayment/charge';
  protected $_checkoutType = 'zipmoneypayment/standard_checkout';

  public function indexAction() 
  {
    
    $state       = $this->getRequest()->getParam('state');
    $checkout_id = $this->getRequest()->getParam('checkout_id');

    /* 
     * TODO
     * - Check the authenticity of the referrer
     * - Validate the checkout id
     * - Validate the states against the available state
     */

    switch ($state) {
      case 'approved':
        # code...

        try {
          $this->_checkout = $this->_initCheckout();

          Mage::helper("zipmoneypayment")->activateQuote($this->_getQuote());

          $order = $this->_checkout->place();

          $this->_checkout->charge();

          // Rewrite Address 
          //$this->_forward('approvedAction');
          return;
        } catch (Mage_Core_Exception $e) {
          Mage::getSingleton('checkout/session')->addError($e->getMessage());
          $this->_logger->debug($e->getMessage());
        }
        catch (Exception $e) {
          Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
          $this->_logger->debug($e->getMessage());
        }
       //$this->_redirect('checkout/cart');
        // Dispatch the approved action
        break;

      case 'declined':
        // Dispatch the decline action
      case 'cancelled':  
        // Dispatch the referred action
      case 'referred':
      
      default:
        # code...
        break;
    }

  }

  public function approvedAction()
  {}

  public function declinedAction()
  {}

  public function cancelledAction()
  {}

  public function referredAction()
  {}

}
  
?>