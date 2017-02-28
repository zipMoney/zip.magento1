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

	// protected $_client =  Zipmoney_ZipmoneyPayment_Model_Config::MODULE_VERSION;
	// protected $_platform = Zipmoney_ZipmoneyPayment_Model_Config::MODULE_PLATFORM;
			 
	/**
	 * zipMoney Payment instance
	 *
	 * @var Zipmoney_ZipmoneyPayment_Model_Payment
	 */

	protected $_logger = null;
	protected $_helper = null;
	protected $_checkout  = null;
  protected $_config = null;
  protected $_checkoutType = 'zipmoneypayment/standard_checkout';
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

		$this->_checkout = Mage::getModel($this->_checkoutType, array('api_class' => $this->_chargesApiClass));
    $this->_checkout->setOrder($order);

    try {
	    
	    if(!$amount){
	    	Mage::throwException($this->_helper->__("Please provide the capture amount"));
	    }

    	$this->_checkout->captureCharge($amount);
 		 
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

		$this->_checkout = Mage::getModel($this->_checkoutType, array('api_class' => $this->_refundsApiClass));    
    $this->_checkout->setOrder($order);

    try {
	    
	    if(!$amount){
	    	Mage::throwException($this->_helper->__("Please provide the capture amount"));
	    }

    	$refund = $this->_checkout->refund($amount,$reason);
 		  
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
	
	/**
	 * Return Order place redirect url
	 *
	 * @return string
	 */
	public function getOrderPlaceRedirectUrl() {
			/**
			 * Will be called when placing order
			 * see app/code/core/Mage/Checkout/Model/Type/Onepage.php:808
			 * and app/code/core/Mage/Paypal/Model/Standard.php:108
			 */
			return Mage::getUrl('zipmoneypayment/standard/redirect', array('_secure' => true));
	}

 

	/**
	 * Return zipMoney Express redirect url if current request is not savePayment (which works for oneStepCheckout)
	 * @return null|string
	 */
	public function getCheckoutRedirectUrl() 
  {
    return null;
    // if($this->_config->isInContextCheckout()){      
    //   $this->_logger->info("In-Context Checkout");
    //   return null;
    // }
    
    // return Mage::getUrl('zipmoneypayment/standard');
	}

}