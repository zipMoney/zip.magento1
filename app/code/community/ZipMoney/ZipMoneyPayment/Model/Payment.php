<?php
use \zipMoney\ApiException;

class Zipmoney_ZipmoneyPayment_Model_Payment extends Mage_Payment_Model_Method_Abstract {

	protected $_code = 'zipmoneypayment';
	protected $_formBlockType = 'zipmoneypayment/standard_form';
	protected $_isInitializeNeeded = true;
	protected $_url = "";
	protected $_dev = false;

	/**
	 * Availability options
	 */
	protected $_isGateway = false;
	protected $_canOrder = true;
	protected $_canAuthorize = true;
	protected $_canCapture = true;
	protected $_canCapturePartial = true;
	protected $_canRefund = true;
	protected $_canRefundInvoicePartial = true;
	protected $_canVoid = true;
	protected $_canUseInternal = false;
	protected $_canUseCheckout = true;
	protected $_canUseForMultishipping = false;
	protected $_canFetchTransactionInfo = true;
	protected $_canCreateBillingAgreement = true;
	protected $_canReviewPayment = true;
		 
	/**
	 * zipMoney Payment instance
	 *
	 * @var Zipmoney_ZipmoneyPayment_Model_Payment
	 */

	protected $_logger = null;
	protected $_helper = null;
	protected $_checkout  = null;
  protected $_config = null;
  protected $_chargeModel = 'zipmoneypayment/charge';
  protected $_chargesApiClass  = '\zipMoney\Client\Api\ChargesApi';
  protected $_refundsApiClass  = '\zipMoney\Client\Api\RefundsApi';


	/**
	 * Payment additional information key for payment action
	 * @var string
	 */
	protected $_isOrderPaymentActionKey = 'is_order_action';

	/**
	 * Payment additional information key for number of used authorizations
	 * @var string
	 */
	protected $_authorizationCountKey = 'authorization_count';

	function __construct() {
		parent::__construct();

		$this->_helper = Mage::helper("zipmoneypayment");   
    $this->_logger = Mage::getSingleton('zipmoneypayment/logger');
    $this->_config = Mage::getSingleton('zipmoneypayment/config');

 	}


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
	  
	  Mage::throwException($this->_helper->__("Unable to capture the payment."));

		return false;
	}


  public function cancel(Varien_Object $payment)
  {
    $this->_logger->info($this->_helper->__("Cancelling Order"));

    if ($payment && $payment->getOrder()) {
      Mage::getSingleton('zipmoneypayment/storeScope')->setStoreId($payment->getOrder()->getStoreId());
    }

    $orderId = $payment->getOrder()->getIncrementId();
    $order = Mage::getModel('sales/order')
                    ->loadByIncrementId($orderId);

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
	 * Return zipMoney Express redirect url if current request is not savePayment (which works for oneStepCheckout)
	 * @return null|string
	 */
	public function getCheckoutRedirectUrl() 
  {
    $action     = Mage::app()->getRequest()->getActionName();
    $controller = Mage::app()->getRequest()->getControllerName();
    $module     = Mage::app()->getRequest()->getModuleName();
    
    $this->_logger->debug($this->_helper->__("Action: %s Controller: %s Module: %s",$action,$controller,$module));

    if (
        ($module == 'checkout' &&  $controller == 'onepage' &&  $action == 'savePayment') 
      ) {
      $url = null;
    } else {

      if($this->_config->isInContextCheckout()){ 
        /* Return current url with extra param appended, so that it will refresh current page with the  
         * param if the param is present, will popup zipMoney iframe checkout
         */

        $this->_logger->info("In-Context Checkout");
        
        
        if(Mage::app()->getRequest()->isAjax())
          $currentUrl = Mage::helper('checkout/url')->getCheckoutUrl();
        else
          $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        
        //$currentUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB,true)."zipmoneypayment/standard/redirect/";

        if (Mage::app()->getRequest()->getParam('zip-in-context') != 'true') {
          $urlArr = parse_url($currentUrl);
          if (!isset($urlArr['zip-in-context'])) {
            $url = $currentUrl . (parse_url($currentUrl, PHP_URL_QUERY) ? '&' : '?') . 'zip-in-context=true';
          }
        }
      } else {
        // return the zipmoney redirect url
        $url = Mage::getUrl('zipmoneypayment/standard/getredirecturl/');
      }
    }
    
    $this->_logger->info("Payment Redirect Url:- ".$url);

    return $url;
	}

}