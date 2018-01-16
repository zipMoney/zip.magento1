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
   * @var Zipmoney_ZipmoneyPayment_Model_Config
   */
  protected $_config;
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
  const ZIPMONEY_ERROR_ROUTE = "zipmoneypayment/standard/error";
 
  /**
   * Instantiate config
   */
	protected function _construct()
	{
		parent::_construct();

    $this->_logger = Mage::getSingleton("zipmoneypayment/logger");
    $this->_config = Mage::getSingleton("zipmoneypayment/config");
		$this->_helper = Mage::helper('zipmoneypayment');
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
   * Get customer session object
   *
   * @return Mage_Customer_Model_Session
   */
  protected function _getCustomerSession()
  {
    return Mage::getSingleton('customer/session');
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

    if(!$quote->getId()){
      Mage::throwException($this->_helper->__('Quote doesnot exist'));
    }

    if (!$quote->hasItems() || $quote->getHasError()) {
      Mage::throwException($this->_helper->__('Quote has error or no items.'));
    }

    $this->_charge = Mage::getModel($this->_chargeModel);
    return $this->_charge;
  }

  /**
   * Sets the Http Headers, Response Code and Responde Body
   * 
   * @param string $data 
   * @param Mage_Api2_Model_Server $responseCode 
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
   * Checks if the Session Quote is valid, if not use the db quote.
   *   
   * @param boolean $forceRetrieveDbQuote 
   * @return boolean
   */
  protected function _retrieveQuote($forceRetrieveDbQuote=false)
  {
    $sessionQuote = $this->_getCheckoutSession()->getQuote();
    $zipMoneyCid  = $this->getRequest()->getParam('checkoutId');
    $use_db_quote = false;

    // Return Session Quote
    if(!$sessionQuote){
      $this->_logger->error($this->_helper->__("Session Quote doesnot exist."));
      $use_db_quote = true;
    } else if($sessionQuote->getZipmoneyCid() != $zipMoneyCid){
      $this->_logger->error($this->_helper->__("Checkout Id doesnot match with the session quote."));
      $use_db_quote = true;
    } else {
      return $sessionQuote;
    }

    //Retrurn DB Quote
    if($use_db_quote){
      $dbQuote = $this->_getDbQuote($zipMoneyCid);
      if(!$dbQuote){
        $this->_logger->warn($this->_helper->__("Quote doesnot exist for the given checkout_id."));
        return false;
      } else {
        $this->_logger->info($this->_helper->__("Loading DB Quote"));
      }
      return $dbQuote;
    }
  }

  /**
   * Checks if the Customer is valid for the quote
   *   
   * @param Mage_Sales_Model_Quote $quote 
   */
  protected function _verifyCustomerForQuote($quote)
  {
    $currentCustomer = null;
    $customerSession =  $this->_getCustomerSession();

    // Get quote customer id
    $quoteCustomerId = $quote->getCustomerId();

    // Get current logged in customer
    if ($customerSession->isLoggedIn()) {
      $currentCustomer = $customerSession->getCustomer();
    }

    $this->_logger->debug(
      $this->_helper->__("Current Customer Id:- %s Quote Customer Id:- %s Quote checkout method:- %s",
        $customerSession->getId(),$quoteCustomerId, $quote->getCheckoutMethod())
    );

    $log_in = false;

    if(isset($currentCustomer)) {
      if( $currentCustomer->getId() != $quoteCustomerId ){
        $customerSession->logout(); // Logout the logged in customer
        $customerSession->renewSession();
        //$log_in = true;
      }
    }
    // else if($quoteCustomerId){
    //   $log_in = true;
    // }

    // if($quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER){
    //   $quoteCustomerId = $this->_helper->lookupCustomerId($quote->getCustomerEmail());
    // }

    // if($quote->getCheckoutMethod() != Mage_Checkout_Model_Type_Onepage::METHOD_GUEST && $log_in){
    //   // if(!$customerSession->loginById($quoteCustomerId)){
    //   //   Mage::throwException("Could not login");
    //   // }
    // }
  }

  /**
   * Sets quote for the customer.
   *   
   * @throws Mage_Core_Exception
   */
  public function _setCustomerQuote()
  {
    // Retrieve a valid quote
    if($quote = $this->_retrieveQuote()){

      // Verify that the customer is a valid customer of the quote
      $this->_verifyCustomerForQuote($quote);
      /* Set the session quote if required.
         Needs to be done after verifying the current customer */
      if($this->_getCheckoutSession()->getQuoteId() != $quote->getId()){
        $this->_logger->debug($this->_helper->__("Setting quote to current session"));
        // Set the quote in the current object
        $this->_setQuote($quote);
        // Set the quote in the session
        $this->_getCheckoutSession()->setQuoteId($quote->getId());
      }

      // Make sure the qoute is active
      $this->_helper->activateQuote($quote);
    } else {
      Mage::throwException("Could not retrieve the quote");
    }
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
   * Redirects to the cart page.
   *
   */
  protected function _redirectToCart()
  {
    $this->_redirect("checkout/cart");
  }

  /**
   * Redirects to the error page.
   *
   */
  protected function _redirectToError()
  {
    $this->_redirect(self::ZIPMONEY_ERROR_ROUTE);
  }

  /**
   * Redirects to the cart or error page.
   *
   */
  protected function _redirectToCartOrError()
  {
    if($this->_getQuote()->getIsActive()){
      $this->_redirectToCart();
    } else {
      $this->_redirectToError();
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
      $this->_getCheckoutSession()->addError($this->_helper->__('An error occurred during redirecting to error page.'));
    }
  }

  /**
   * Get one page checkout model
   *
   * @return Mage_Checkout_Model_Type_Onepage
   */
  public function getOnepage()
  {
    return Mage::getSingleton('checkout/type_onepage');
  }

  /**
   * Send Ajax redirect response
   *
   * @return Zipmoney_ZipmoneyPayment_Controller_Abstract $this
   */
  protected function _ajaxRedirectResponse()
  {
    $this->getResponse()
        ->setHeader('HTTP/1.1', '403 Session Expired')
        ->setHeader('Login-Required', 'true')
        ->sendResponse();
    return $this;
  }
  
  /**
   * Validate ajax request and redirect on failure
   *
   * @return bool
   */
  protected function _expireAjax()
  {
    if (!$this->getOnepage()->getQuote()->hasItems()
        || $this->getOnepage()->getQuote()->getHasError()
        || $this->getOnepage()->getQuote()->getIsMultiShipping()
    ) {
      $this->_ajaxRedirectResponse();
      return true;
    }
    $action = $this->getRequest()->getActionName();
    if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
        && !in_array($action, array('index', 'progress'))
    ) {
        $this->_ajaxRedirectResponse();
      return true;
    }
    return false;
  }
}