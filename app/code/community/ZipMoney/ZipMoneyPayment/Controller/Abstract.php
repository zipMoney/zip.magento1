<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */
abstract class Zipmoney_ZipmoneyPayment_Controller_Abstract extends Mage_Core_Controller_Front_Action
{ 
  /**
   * @var Zipmoney_ZipmoneyPayment_Model_Logger
   */ 
	protected $_logger;

	/**
   * @var Zipmoney_ZipmoneyPayment_Helper_Data
   */ 
  protected $_helper;

  /**
   * @var Mage_Sales_Model_Quote
   */ 
	protected $_quote;
	/**
   * @var Zipmoney_ZipmoneyPayment_Model_Checkout
   */
  protected $_checkout;
  /**
   * @var Zipmoney_ZipmoneyPayment_Model_Charge
   */
  protected $_charge;
  /**
   * Instantiate config
   */
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
   * Return checkout quote object from database
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

		$this->_checkout = Mage::getModel($this->_checkoutModel, array('quote'=> $quote));
    
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

    $this->_charge = Mage::getModel($this->_chargeModel);

    return $this->_charge;
  }
  
  /**
   * Sets the Http Headers, Response Code and Responde Body
   */
	protected function _sendResponse($data, $responseCode = Mage_Api2_Model_Server::HTTP_OK)
	{
		$this->getResponse()->setHttpResponseCode($responseCode);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$jsonData = Mage::helper('core')->jsonEncode($data);
		$this->getResponse()->setBody($jsonData);
	}

  /**
   * Checks if the result passed in the query string is valid
   *
   * @return boolean
   */
  protected function _isResultValid()
  {
    if(!$this->getRequest()->getParam('result') || 
       !in_array($this->getRequest()->getParam('result'), $this->_validResults)){
      $this->_logger->error($this->_helper->__("Invalid Result"));
      return false;
    }
    return true;
  }

  /**
   * Checks if the Checkout Id passed in the query string is valid
   *
   * @return boolean
   */
  protected function _isCheckoutIdValid()
  {
    if(!$this->getRequest()->getParam('checkout_id')){
      $this->_logger->error($this->_helper->__("Could not find the checkout id in the querystring"));
      return true;
    }
    return true;
  }

  /**
   * Checks if the Session Quote is valid, if not use the db quote.
   *
   * @return boolean
   */
  protected function _verifyQuote()
  {
    $sessionQuote = $this->_getQuote();
    $zipMoneyCid = $this->getRequest()->getParam('checkout_id');
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

  /**
   * Redirects to the cart page.
   *
   * @return boolean
   */
  public function declinedAction()
  {    
    $this->_logger->debug($this->_helper->__('Calling declinedAction'));

    $this->_redirect('checkout/cart');
  }

  /**
   * Redirects to the cart page.
   *
   * @return boolean
   */
  public function cancelledAction()
  {    
    $this->_logger->debug($this->_helper->__('Calling cancelledAction'));

    $this->_redirect('checkout/cart');
  }

  /**
   * Redirects to the referred page.
   *
   * @return boolean
   */
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
  
  /**
   * Redirects to the error page.
   *
   * @return boolean
   */
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