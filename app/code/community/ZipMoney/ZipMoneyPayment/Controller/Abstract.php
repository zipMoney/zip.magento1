<?php

abstract class Zipmoney_ZipmoneyPayment_Controller_Abstract extends Mage_Core_Controller_Front_Action
{
	protected $_logger;
	protected $_helper;
	protected $_quote;
	
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
			Mage::throwException(Mage::helper('paypal')->__('Unable to initialize the Checkout.'));
		}

		$this->_checkout = Mage::getSingleton($this->_checkoutType, array('quote'  => $quote));

		return $this->_checkout;
	}

	/**
	 * Return checkout quote object
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	private function _getQuote()
	{ 
		if (!$this->_quote) {
			$this->_quote = $this->_getCheckoutSession()->getQuote();
		}

		return $this->_quote;
	}


	protected function _sendResponse($data, $responseCode = Mage_Api2_Model_Server::HTTP_OK)
	{
			$this->getResponse()->setHttpResponseCode($responseCode);
			$this->getResponse()->setHeader('Content-type', 'application/json');
			$jsonData = Mage::helper('core')->jsonEncode($data);
			$this->getResponse()->setBody($jsonData);
	}

}