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
   * Sets checkout quote object
   *
   * @return Mage_Sales_Model_Quote
   */
  protected function _setQuote($quote)
  { 
    $this->_quote = $quote;

    return $this;
  }

  /**
   * Return checkout quote object
   *
   * @return Mage_Sales_Model_Quote
   */
  protected function _getDbQuote($zipMoneyCid)
  {    
    if ($zipMoneyCid) {
      $this->_quote = Mage::getModel('sales/quote')
                            ->getCollection()
                            ->addFieldToFilter("zipmoney_cid", $zipMoneyCid)
                            ->getFirstItem();
      return $this->_quote;
    }
  }

	/**
	 * Instantiate checkout model and inject the checkout api
	 *
	 * @return Zipmoney_ZipmoneyPayment_Model_Standard_Checkout
	 * @throws Mage_Core_Exception
	 */
	protected function _initCheckout()
	{
		$quote = $this->_getQuote();

    // Check if the quote has items and errors
   	if (!$quote->hasItems() || $quote->getHasError()) {
			$this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
			Mage::throwException($this->_helper->__('Unable to initialize the Checkout.'));
		}

		$this->_checkout = Mage::getModel($this->_checkoutType, array('quote'=>$quote,'api_class' => $this->_apiClass));
    
		return $this->_checkout;
	}

  /**
   * Instantiate checkout model and inject charge api
   *
   * @return Zipmoney_ZipmoneyPayment_Model_Standard_Checkout
   * @throws Mage_Core_Exception
   */
  protected function _initCharge()
  {
    $quote = $this->_getQuote();

    if (!$quote->hasItems() || $quote->getHasError()) {
      $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
      Mage::throwException($this->_helper->__('Unable to initialize the Checkout.'));
    }

    $this->_checkout = Mage::getModel($this->_checkoutType, array('api_class' => $this->_apiClass));

    return $this->_checkout;
  }

	protected function _sendResponse($data, $responseCode = Mage_Api2_Model_Server::HTTP_OK)
	{
		$this->getResponse()->setHttpResponseCode($responseCode);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$jsonData = Mage::helper('core')->jsonEncode($data);
		$this->getResponse()->setBody($jsonData);
	}


  protected function _isValidResult()
  {

    if(!$this->getRequest()->getParam('result') || 
       !in_array($this->getRequest()->getParam('result'), $this->_validResults)){
      $this->_logger->error($this->_helper->__("Invalid Result"));
      return false;
    }

    return true;
  }


  protected function _isValidCheckoutId()
  {

    if(!$this->getRequest()->getParam('checkoutId')){
      $this->_logger->error($this->_helper->__("Could not find the checkout id in the querystring"));
      return true;
    }

    return true;
  }

  protected function _verifyQuote()
  {
    $sessionQuote = $this->_getQuote();
    $zipMoneyCid = $this->getRequest()->getParam('checkoutId');
    $use_db_quote = false;

    if(!$sessionQuote){
      $this->_logger->error($this->_helper->__("Session Quote doesnot exist."));
      $use_db_quote = true;
    } else if($sessionQuote->getZipmoneyCid() != $zipMoneyCid){      
      $this->_logger->error($this->_helper->__("Checkout Id doesnot match with the session quote."));      
      $use_db_quote = true;
    } 

    if($use_db_quote){
      $dbQuote = $this->_getDbQuote($zipMoneyCid);

      if(!$dbQuote){
        $this->_logger->warn($this->_helper->__("Quote doesnot exist for the given checkout_id."));
        return false;
      } else {
        $this->_logger->info($this->_helper->__("Loading DB Quote"));
      }

      $this->_setQuote($dbQuote);
    }

    return true;
  }

  public function declinedAction()
  {    
    $this->_logger->debug($this->_helper->__('Calling declinedAction'));

    $this->_redirect('checkout/cart');
  }

  public function cancelledAction()
  {    
    $this->_logger->debug($this->_helper->__('Calling cancelledAction'));

    $this->_redirect('checkout/cart');

  }

  public function referredAction()
  {

    $this->_logger->debug($this->_helper->__('Calling referredAction'));
    try {
      $this->loadLayout()
          ->_initLayoutMessages('checkout/session')
          ->_initLayoutMessages('catalog/session')
          ->_initLayoutMessages('customer/session');
      $this->renderLayout();
      $this->_logger->info($this->_helper->__('Successful to redirect to referred page.'));
    } catch (Exception $e) {
      $this->_logger->error(json_encode($this->getRequest()->getParams()));
      $this->_logger->error($e->getMessage());
      $this->_getCheckoutSession()->addError($this->__('An error occurred during redirecting to referred page.'));
    }

  }
  
  public function errorAction()
  { 
    $this->_logger->debug($this->_helper->__('Calling errorAction'));
    try {
      $this->loadLayout()
          ->_initLayoutMessages('checkout/session')
          ->_initLayoutMessages('catalog/session')
          ->_initLayoutMessages('customer/session');
      $this->renderLayout();
      $this->_logger->info($this->_helper->__('Successful to redirect to error page.'));
    } catch (Exception $e) {
      $this->_logger->error(json_encode($this->getRequest()->getParams()));
      $this->_getCheckoutSession()->addError($this->__('An error occurred during redirecting to error page.'));
    }
    
  }
}