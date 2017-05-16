<?php
use \zipMoney\ApiException;
/**
 * @category  zipMoney
 * @package   zipmoney
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_Payment extends Mage_Payment_Model_Method_Abstract 
{
  /**
   * Method Code
   * @var string
   */ 
	protected $_code = Zipmoney_ZipmoneyPayment_Model_Config::METHOD_CODE;
  /**
   * Payment Option Form Block Type
   * @var string
   */
	protected $_formBlockType = 'zipmoneypayment/standard_form';
	/**   
   * @var boolean
   */
  protected $_isInitializeNeeded = true;
	/**
   * @var string
   */
  protected $_url = "";
	/**
   * @var boolean
   */
  protected $_dev = false;
	/**
   * @var boolean
	 */
	protected $_isGateway = false;
  /**
   * @var boolean
   */
	protected $_canOrder = true;
  /**
   * @var boolean
   */
	protected $_canAuthorize = true;
  /**
   * @var boolean
   */
	protected $_canCapture = true;
  /**
   * @var boolean
   */
	protected $_canCapturePartial = true;
  /**
   * @var boolean
   */
	protected $_canRefund = true;
  /**
   * @var boolean
   */
	protected $_canRefundInvoicePartial = true;
	/**
   * @var boolean
   */
  protected $_canVoid = true;
	/**
   * @var boolean
   */
  protected $_canUseInternal = false;
	/**
   * @var boolean
   */
  protected $_canUseCheckout = true;
	/**
   * @var boolean
   */
  protected $_canUseForMultishipping = false;
	/**
   * @var boolean
   */
  protected $_canFetchTransactionInfo = true;
	/**
   * @var boolean
   */
  protected $_canCreateBillingAgreement = true;
	/**
   * @var boolean
   */
  protected $_canReviewPayment = true;
	/**	
	 * @var Zipmoney_ZipmoneyPayment_Model_Logger
	 */
	protected $_logger = null;
  /**  
   * @var Zipmoney_ZipmoneyPayment_Helper_Data
   */
	protected $_helper = null;  
  /**  
   * @var Zipmoney_ZipmoneyPayment_Model_Config
   */
  protected $_config = null;
	 /**  
   * @var Zipmoney_ZipmoneyPayment_Model_Charge
   */
  protected $_chargeModel = 'zipmoneypayment/charge';

  /**  
   * Sets the helper, logger, config classes
   */
	public function __construct() {
		parent::__construct();

		$this->_helper = Mage::helper("zipmoneypayment");
    $this->_logger = Mage::getSingleton('zipmoneypayment/logger');
    $this->_config = Mage::getSingleton('zipmoneypayment/config');

 	}

  /**  
   * Captures the charge
   *
   * @param Varien_Object $payment
   * @param float $amount
   * @throws Mage_Core_Exception
   * @return boolean
   */
	public function capture(Varien_Object $payment, $amount)
	{
    if ($payment && $payment->getOrder()) {
      Mage::getSingleton('zipmoneypayment/storeScope')->setStoreId($payment->getOrder()->getStoreId());
    }

    $orderId = $payment->getOrder()->getIncrementId();
    $order = Mage::getModel('sales/order')
            				->loadByIncrementId($orderId);

		$this->_charge = Mage::getModel($this->_chargeModel);
    $this->_charge->setOrder($order);

    try {

	    if(!$amount){
	    	Mage::throwException($this->_helper->__("Please provide the capture amount"));
	    }

    	$this->_charge->captureCharge($amount);

 		  $this->_logger->info($this->_helper->__("Payment for Order [ %s ] was captured successfully",$orderId));

 		  return $this;
 		} catch (Mage_Core_Exception $e) {
      $this->_logger->debug($e->getMessage());
    } catch (ApiException $e) {
      $this->_logger->debug("Errors:-".json_encode($e->getResponseBody()));
    } catch (Exception $e) {
      $this->_logger->debug($e->getMessage());
    }

    Mage::throwException($this->_helper->__("Unable to capture the payment."));

    return false;
	}

  /**  
   * Refund the charge
   *
   * @param Varien_Object $payment
   * @param float $amount
   * @throws Mage_Core_Exception
   * @return boolean
   */
	public function refund(Varien_Object $payment, $amount)
	{
		if ($payment && $payment->getOrder()) {
      Mage::getSingleton('zipmoneypayment/storeScope')->setStoreId($payment->getOrder()->getStoreId());
    }

   	$param = Mage::app()->getRequest()->getParam('creditmemo');
    $reason = $param['comment_text'];

    if (!$reason) {
      $reason = 'N/A';
    }

    $orderId = $payment->getOrder()->getIncrementId();
    $order = Mage::getModel('sales/order')
            				->loadByIncrementId($orderId);

		$this->_charge = Mage::getModel($this->_chargeModel, array('api_class' => $this->_refundsApiClass));
    $this->_charge->setOrder($order);

    try {

	    if(!$amount){
	    	Mage::throwException($this->_helper->__("Please provide the capture amount"));
	    }

    	$refund = $this->_charge->refundCharge($amount,$reason);

 		  $this->_logger->info($this->_helper->__("Refund for Order [ %s ] for amount %s was successfull",$orderId, $amount));

 	    $payment->setTransactionId($refund->getId());
	    $payment->setIsTransactionClosed(true);
	    $payment->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_VOID);    // Handle refund response
    	return $this;
 		} catch (Mage_Core_Exception $e) {
      $this->_logger->debug($e->getMessage());
    } catch (ApiException $e) {
      $this->_logger->debug("Errors:-".json_encode($e->getResponseBody()));
    } catch (Exception $e) {
      $this->_logger->debug($e->getMessage());
    }

	  Mage::throwException($this->_helper->__("Unable to refund the payment."));

		return false;
	}

  /**  
   * Cancels the charge
   *
   * @param Varien_Object $payment
   * @throws Mage_Core_Exception
   * @return boolean
   */
  public function cancel(Varien_Object $payment)
  {
    $this->_logger->info($this->_helper->__("Cancelling Order"));

    if ($payment && $payment->getOrder()) {
      Mage::getSingleton('zipmoneypayment/storeScope')->setStoreId($payment->getOrder()->getStoreId());
    }

    $orderId = $payment->getOrder()->getIncrementId();
    $order = Mage::getModel('sales/order')
                    ->loadByIncrementId($orderId);

    if($order->getPayment()->getMethod() != "zipmoneypayment" )
    {
      return;
    }

    $this->_charge = Mage::getModel($this->_chargeModel);
    $this->_charge->setOrder($order);

    try {
      $this->_charge->cancelCharge();
      $this->_logger->info($this->_helper->__("Cancel request Order [ %s ] was successfull",$orderId));
      return $this;
    } catch (Mage_Core_Exception $e) {
      $this->_logger->debug($e->getMessage());
    } catch (ApiException $e) {
      $this->_logger->debug("Errors:-".json_encode($e->getResponseBody()));
    } catch (Exception $e) {
      $this->_logger->debug($e->getMessage());
    }

    Mage::throwException($this->_helper->__("Unable to cancel the order in zipMoney."));
    return false;
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
   * Return quote object
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
   * Returns the url to redirect after placing the order
   *
   * @return string
   */
  public function getOrderPlaceRedirectUrl()
  {
    return $this->_helper->getUrl("zipmoneypayment/complete/charge");
  }

	/**
	 * Return zipMoney checkout redirect url by appending 'zip-in-context=true' to the url for onestepcheckout extensions
   *
	 * @return null|string
	 */
	public function getCheckoutRedirectUrl()
  {
    $action     = Mage::app()->getRequest()->getActionName();
    $controller = Mage::app()->getRequest()->getControllerName();
    $module     = strtolower(Mage::app()->getRequest()->getControllerModule());

    $this->_logger->debug($this->_helper->__("Action: %s Controller: %s Module: %s",$action,$controller,$module));
    
    $url = null;

    if (
        ($module == 'mage_checkout' &&  $controller == 'onepage' &&  $action == 'savePayment')
      ) {
      $url = null;
    } else {

      if($this->_config->isInContextCheckout()){
        

        $this->_logger->info("In-Context Checkout");

        /* Return current url with extra param appended, so that it will refresh current page with the
         * param if the param is present, will popup zipMoney iframe checkout
         */

        if ($module == 'magestore_onestepcheckout' &&  $controller == 'index' &&  $action == 'saveOrder')
        {        $this->_logger->info("In-Context Checkout");

          $currentUrl = Mage::helper('checkout/url')->getCheckoutUrl();
        }  
        else if ($module == 'iwd_opc' &&  $controller == 'json' &&  $action == 'savePayment')
        {      
          $currentUrl = null;
        } 
         else {
          if(Mage::app()->getRequest()->isAjax())
            $currentUrl = Mage::helper('checkout/url')->getCheckoutUrl();
          else
            $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        }

        //$currentUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB,true)."zipmoneypayment/standard/redirect/";

        if (Mage::app()->getRequest()->getParam('zip-in-context') != 'true' && isset($currentUrl)) {  
          $urlArr = parse_url($currentUrl);
          if (!isset($urlArr['zip-in-context'])) {
            $url = $currentUrl . (parse_url($currentUrl, PHP_URL_QUERY) ? '&' : '?') . 'zip-in-context=true';
          }
        }
        $this->_logger->info($currentUrl);

      } else {

        $quote = $this->_getQuote();

        // Check if the quote has items and errors
        if (!$quote->hasItems() || $quote->getHasError()) {
          Mage::throwException($this->_helper->__('Unable to initialize the Checkout.'));
        }

        $checkout = Mage::getModel('zipmoneypayment/checkout', array('quote'=> $quote));
        $checkout->start();

        if($url = $checkout->getRedirectUrl()) {
          $this->_logger->info($this->_helper->__('Successful to get redirect url [ %s ] ', $redirectUrl));
        } else {
          Mage::throwException("Failed to get redirect url.");
        }
      }
    }

    $this->_logger->info("Payment Redirect Url:- ".$url);

    return $url;
	}

  /**
   * Check method for processing with base currency
   *
   * @param string $currencyCode
   * @return bool
   */
  public function canUseForCurrency($currencyCode)
  {
    if (!in_array($currencyCode, array("AUD","NZD"))) {
      return false;
    }
    return true;
  }

  /**
   * Can use for order threshold
   *
   * @param Mage_Sales_Model_Quote $quote
   * @return bool
   */
  public function canUseForQuoteThreshold($quote)
  {
    $total = $quote->getBaseGrandTotal();
    $minTotal = $this->_config->getOrderTotalMinimum();

    if (!empty($minTotal) && $total < $minTotal ){
      return false;
    }

    return true;
  }

  /**
   * Check zero total
   *
   * @param Mage_Sales_Model_Quote $quote
   * @return bool
   */
  public function canUseForZeroTotal($quote)
  {
    if ($quote->getBaseGrandTotal() < 0.0001 && $this->getCode() != 'free' ){
      return false;
    }
    return true;
  }

  /**
   * Check if method available.
   *
   * @param Mage_Sales_Model_Quote $quote
   * @return bool
   */
  public function isAvailable($quote = null)
  {
    return $this->isAvailableForQuote($quote) && parent::isAvailable($quote);
  }

  /**
   * Added if the payment method is available for quote.
   *
   * @param Mage_Sales_Model_Quote $quote
   * @return bool
   */
  public function isAvailableForQuote($quote = null)
  {
    if ($quote) {

      $shipToCountry = $quote->getShippingAddress()->getCountry();
      $billToCountry = $quote->getBillingAddress()->getCountry();
      if (!empty($shipToCountry) && !$this->canUseForCountry($shipToCountry) && !$this->canUseForCountry($billToCountry)) {
        $this->_logger->info($this->_helper->__("%s or %s is not supported.",$shipToCountry,$billToCountry));
        return false;
      }
      if (!$this->canUseForCurrency($quote->getStore()->getBaseCurrencyCode())) {
        $this->_logger->info($this->_helper->__("%s is not supported.",$quote->getStore()->getBaseCurrencyCode()));
        return false;
      }
      if (!$this->canUseCheckout()) {
        $this->_logger->info($this->_helper->__("Cannot use for checkout"));
        return false;
      }
      if (!$this->canUseForQuoteThreshold($quote)) {
        $this->_logger->info($this->_helper->__("Cannot use outside the order threshold"));
        return false;
      }
      if (!$this->canUseForZeroTotal($quote)) {
        $this->_logger->info($this->_helper->__("Cannot use for zero total"));
        return false;
      }

    }
    return true;
  }

}