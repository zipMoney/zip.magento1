<?php

abstract class Zipmoney_ZipmoneyPayment_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
	protected $_logger;
	protected $_helper;
	protected $_quote;
	protected $_checkout;
  protected $_charge;
	
	protected function _construct()
	{
		parent::_construct();

		$this->_logger = Mage::getSingleton("zipmoneypayment/logger");
		$this->_helper = Mage::helper('zipmoneypayment');

	}

	/**
	 * Get checkout session model instance
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getSession()
	{
			return Mage::getSingleton('checkout/session');
	}


	/**
	 * Return checkout session object
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getCheckoutSession()
	{
			return Mage::getSingleton('checkout/session');
	}


	/**
	 * Return checkout quote object
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote()
	{ 
		if (!$this->_quote) {
			$this->_quote = $this->_getCheckoutSession()->getQuote();
		}

		return $this->_quote;
	}

	/**
	 * Instantiate quote and checkout
	 *
	 * @return Mage_Paypal_Model_Express_Checkout
	 * @throws Mage_Core_Exception
	 */
	protected function _initCheckout()
	{
		$quote = $this->_getQuote();

   	if (!$quote->hasItems() || $quote->getHasError()) {
			$this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
			Mage::throwException($this->_helper->__('Unable to initialize the Checkout.'));
		}

		$this->_checkout = Mage::getSingleton($this->_checkoutType,array('quote'=>$quote));

		return $this->_checkout;
	}

  /**
   * Instantiate quote and checkout
   *
   * @return Mage_Paypal_Model_Express_Checkout
   * @throws Mage_Core_Exception
   */
  protected function _initCharge()
  {
    $quote = $this->_getQuote();

    if (!$quote->hasItems() || $quote->getHasError()) {
      $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
      Mage::throwException($this->_helper->__('Unable to initialize the Checkout.'));
    }

    $this->_checkout = Mage::getSingleton($this->_checkoutType, array('quote' => array()));

    return $this->_checkout;
  }


	protected function _sendResponse($data, $responseCode = Mage_Api2_Model_Server::HTTP_OK)
	{
		$this->getResponse()->setHttpResponseCode($responseCode);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$jsonData = Mage::helper('core')->jsonEncode($data);
		$this->getResponse()->setBody($jsonData);
	}


  /**
   * Submit the order
   */
  public function placeOrder()
  {
    try {

      $this->_checkout->place();

      // prepare session to success or cancellation page
      $session = $this->_getCheckoutSession();
      $session->clearHelperData();

      // "last successful quote"
      $quoteId = $this->_getQuote()->getId();
      $session->setLastQuoteId($quoteId)
      				->setLastSuccessQuoteId($quoteId);

      // an order may be created
      $order = $this->_checkout->getOrder();

      if ($order) {
        $session->setLastOrderId($order->getId())
             	  ->setLastRealOrderId($order->getIncrementId());
      }


      $this->_initToken(false); // no need in token anymore
      $this->_redirect('checkout/onepage/success');
      return;
    } catch (Mage_Paypal_Model_Api_ProcessableException $e) {
      $this->_processPaypalApiError($e);
    } catch (Mage_Core_Exception $e) {
      Mage::helper('checkout')->sendPaymentFailedEmail($this->_getQuote(), $e->getMessage());
      $this->_getSession()->addError($e->getMessage());
      $this->_redirect('*/*/review');
    } catch (Exception $e) {
      Mage::helper('checkout')->sendPaymentFailedEmail(
          $this->_getQuote(),
          $this->__('Unable to place the order.')
      );
      $this->_getSession()->addError($this->__('Unable to place the order.'));
      Mage::logException($e);
      $this->_redirect('*/*/review');
    }
  }

  
}