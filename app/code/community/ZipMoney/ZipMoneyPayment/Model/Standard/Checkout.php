<?php

class Zipmoney_ZipmoneyPayment_Model_Standard_Checkout{

  /**
   * State helper variables
   * @var string
   */
  protected $_redirectUrl = '';
  protected $_checkoutId = '';
  protected $_chargeResult = '';
  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_quote;

  protected $_api;

  protected $_response;

  protected $_helper;

  protected $_requestHelper;
  
  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_customerSession;

  private $_apiClass = null;

  
  const STATUS_MAGENTO_AUTHORIZED = "zip_authorised";

  /**
   * Set quote and config instances
   * @param array $params
   */
  public function __construct($params = array())
  {
    if (isset($params['quote'])) {
      if($params['quote'] instanceof Mage_Sales_Model_Quote){
        $this->_quote = $params['quote'];
      }
      else{
        Mage::throwException('Quote instance is required.');
      }
    } else if (isset($params['order'])) {      
      if($params['order'] instanceof Mage_Sales_Model_Order){
        $this->_order = $params['order'];
      } else {
        Mage::throwException('Order instance is required.');
      }
    }  

    if (isset($params['api_class'])) {
      if(class_exists($params['api_class'])){
        $this->_apiClass = $params['api_class'];
        $this->setApi($this->_apiClass);
      } else {
        Mage::throwException("Invalid Api Class [ ".$params['api_class']." ]");
      }
    } 


    $this->_customerSession = isset($params['session']) && $params['session'] instanceof Mage_Customer_Model_Session
            ? $params['session'] : Mage::getSingleton('customer/session'); 
  
    $this->_helper = Mage::helper("zipmoneypayment");   
    
    $this->_logger = Mage::getSingleton('zipmoneypayment/logger');
    
    $merchant_private_key  = Mage::getSingleton('zipmoneypayment/config')->getMerchantPrivateKey();
    $environment  = Mage::getSingleton('zipmoneypayment/config')->getEnvironment();

    \zipMoney\Configuration::getDefaultConfiguration()->setApiKey('Authorization', "Bearer ".$merchant_private_key);
    \zipMoney\Configuration::getDefaultConfiguration()->setEnvironment($environment);
  }

  /**
   * Prepare quote for guest checkout order submit
   *
   * @return Mage_Paypal_Model_Express_Checkout
   */
  protected function _prepareGuestQuote()
  {
    $quote = $this->_quote;
    $quote->setCustomerId(null)
        ->setCustomerEmail($quote->getBillingAddress()->getEmail())
        ->setCustomerIsGuest(true)
        ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
    return $this;
  }

  /**
   * Checks if customer with email coming from Express checkout exists
   *
   * @return int
   */
  protected function _lookupCustomerId()
  {
    return Mage::getModel('customer/customer')
        ->setWebsiteId(Mage::app()->getWebsite()->getId())
        ->loadByEmail($this->_quote->getCustomerEmail())
        ->getId();
  }

  /**
   * Prepare quote for customer registration and customer order submit
   * and restore magento customer data from quote
   *
   * @return Mage_Paypal_Model_Express_Checkout
   */
  protected function _prepareNewCustomerQuote()
  {
    $quote      = $this->_quote;
    $billing    = $quote->getBillingAddress();
    $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();
    
    $this->_logger->info($this->_helper->__('Prepare to create new customer with email %s', $quote->getCustomerEmail()));

    $customerId = $this->_lookupCustomerId();
    if ($customerId) {            
      $this->_logger->info($this->_helper->__('The email has already been used for customer (id: %s) ', $customerId));
      $this->getCustomerSession()->loginById($customerId);
      return $this->_prepareCustomerQuote();
    }

    $customer = $quote->getCustomer();
    /** @var $customer Mage_Customer_Model_Customer */
    $customerBilling = $billing->exportCustomerAddress();
    $customer->addAddress($customerBilling);
    $billing->setCustomerAddress($customerBilling);
    $customerBilling->setIsDefaultBilling(true);
    if ($shipping && !$shipping->getSameAsBilling()) {
        $customerShipping = $shipping->exportCustomerAddress();
        $customer->addAddress($customerShipping);
        $shipping->setCustomerAddress($customerShipping);
        $customerShipping->setIsDefaultShipping(true);
    } elseif ($shipping) {
        $customerBilling->setIsDefaultShipping(true);
    }
    /**
     * @todo integration with dynamica attributes customer_dob, customer_taxvat, customer_gender
     */
    if ($quote->getCustomerDob() && !$billing->getCustomerDob()) {
        $billing->setCustomerDob($quote->getCustomerDob());
    }

    if ($quote->getCustomerTaxvat() && !$billing->getCustomerTaxvat()) {
        $billing->setCustomerTaxvat($quote->getCustomerTaxvat());
    }

    if ($quote->getCustomerGender() && !$billing->getCustomerGender()) {
        $billing->setCustomerGender($quote->getCustomerGender());
    }

    Mage::helper('core')->copyFieldset('checkout_onepage_billing', 'to_customer', $billing, $customer);
    $customer->setEmail($quote->getCustomerEmail());
    $customer->setPrefix($quote->getCustomerPrefix());
    $customer->setFirstname($quote->getCustomerFirstname());
    $customer->setMiddlename($quote->getCustomerMiddlename());
    $customer->setLastname($quote->getCustomerLastname());
    $customer->setSuffix($quote->getCustomerSuffix());
    $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
    $customer->setPasswordHash($customer->hashPassword($customer->getPassword()));

    $this->_logger->debug($this->_helper->__('Customer password is %s ', ($oCustomer->getPassword() ? 'not empty' : 'empty')));
    $this->_logger->debug($this->_helper->__('Customer password_hash is %s ', ($oCustomer->getPasswordHash() ? 'not empty' : 'empty')));

    $customer->save();
    $quote->setCustomer($customer);
    $this->_logger->info($this->_helper->__('The new customer has been created successfully. Customer id: %s', $oCustomer->getId()));

    return $this;
  }

  /**
   * Prepare quote for customer order submit
   *
   * @return Mage_Paypal_Model_Express_Checkout
   */
  protected function _prepareCustomerQuote()
  {
    $quote      = $this->_quote;
    $billing    = $quote->getBillingAddress();
    $shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();

    $customer = $this->getCustomerSession()->getCustomer();
    if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
    }
    if ($shipping && ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling())
        || (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {
        $customerShipping = $shipping->exportCustomerAddress();
        $customer->addAddress($customerShipping);
        $shipping->setCustomerAddress($customerShipping);
    }

    if (isset($customerBilling) && !$customer->getDefaultBilling()) {
        $customerBilling->setIsDefaultBilling(true);
    }
    if ($shipping && isset($customerBilling) && !$customer->getDefaultShipping() && $shipping->getSameAsBilling()) {
        $customerBilling->setIsDefaultShipping(true);
    } elseif ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
        $customerShipping->setIsDefaultShipping(true);
    }
    $quote->setCustomer($customer);

    return $this;
  }

  /**
   * Make sure addresses will be saved without validation errors
   */
  private function _ignoreAddressValidation()
  {
    $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
    if (!$this->_quote->getIsVirtual()) {
      $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
      if (!$this->_quote->getBillingAddress()->getEmail()) {
        $this->_quote->getBillingAddress()->setSameAsBilling(1);
      }
    }
  }

  protected function _verifyOrderState()
  {
    $currentState = $this->_order->getState();
    
    if ($currentState != Mage_Sales_Model_Order::STATE_NEW) {
      Mage::throwException($this->_helper->__('Invalid order state.'));
    }

  }
  

  protected function _checkTransactionExists($txnId)
  {
    $payment = $this->_order->getPayment();
   
    if ($payment && $payment->getId()) {
      $transaction = $payment->getTransaction($txnId);
      if ($transaction && $transaction->getId()) {
        Mage::throwException($this->_helper->__('The payment transaction already exists.'));
      }
    }
  }


  protected function _authorise($txnId)
  {
    // Check if order has valid state
    $this->_verifyOrderState();
    // Check if the transaction exists
    $this->_checkTransactionExists($txnId);

    $amount  = $this->_order->getBaseTotalDue();

    $payment = $this->_order->getPayment();

    // Authorise the payment
    $payment->setTransactionId($txnId)
            ->setIsTransactionClosed(0)
            ->registerAuthorizationNotification($amount);

    $this->_order->setStatus(self::STATUS_MAGENTO_AUTHORIZED)
                 ->save();

    if (!$this->_order->getEmailSent()) {
      $this->_order->sendNewOrderEmail();
    }

  }

  protected function _capture($txnId, $isAuthAndCapture = false)
  {
    
    /* If the capture has a corresponding authorisation before
     * authorise -> capture
     */
    if($isAuthAndCapture){

      // Check if order has valid state and status
      $orderStatus = $this->_order->getStatus();
      $orderState = $this->_order->getState();
      
      if (($orderState != Mage_Sales_Model_Order::STATE_PROCESSING && 
           $orderState != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) ||
          ($orderStatus != self::STATUS_MAGENTO_AUTHORIZED)) {
        Mage::throwException($this->_helper->__('Invalid order state or status.'));
      }

    } else {    
      // Check if order has valid state and status
      $this->_verifyOrderState();
    }

    // Check if the transaction exists
    $this->_checkTransactionExists($txnId);

    $payment = $this->_order->getPayment();
    
    $parentTxnId = null;

    /* If the capture has a corresponding authorisation before
     * authorise -> capture
     */
    if($isAuthAndCapture){

      $authorizationTransaction = $payment->getAuthorizationTransaction();

      if (!$authorizationTransaction || !$authorizationTransaction->getId()) {
        Mage::throwException($this->_helper->__('Cannot find payment authorization transaction.'));
      }

      if ($authorizationTransaction->getTxnType() != Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH) {
        Mage::throwException($this->_helper->__('Incorrect payment transaction type.'));
      }
      $parentTxnId = $authorizationTransaction->getTxnId();
    }

    if (!$this->_order->canInvoice()) {
      Mage::throwException($this->_helper->__('Cannot create invoice for the order.'));
    }
    

    $amount = $this->_order->getBaseTotalDue();
    

    if($parentTxnId) {
      $payment->setParentTransactionId($parentTxnId);
      $payment->setShouldCloseParentTransaction(true);
    }

    // Capture
    $payment->setTransactionId($txnId)
            ->setPreparedMessage('')
            ->setIsTransactionClosed(0)
            ->registerCaptureNotification($amount);

    $this->_order->save();

    // Invoice 
    $invoice = $payment->getCreatedInvoice();
    
    if ($invoice && !$this->_order->getEmailSent()) {
      $this->_order->sendNewOrderEmail()
                   ->addStatusHistoryComment($this->_helper->__('Notified customer about invoice #%s.', $invoice->getIncrementId()))
                   ->setIsCustomerNotified(true)
                   ->save();
    }
  }


  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param $quote
   * @return null
   * @throws Mage_Core_Exception
   */
  public function start()
  {

    if (!$this->_quote || !$this->_quote->getId()) {
      Mage::throwException(Mage::helper('zipmoneypayment')->__('The quote does not exist.'));
    }
  
    if ($this->_quote->getIsMultiShipping()) {
      $this->_quote->setIsMultiShipping(false);
      $this->_quote->removeAllAddresses();
    }

    $checkoutMethod = $this->getCheckoutMethod();

    if ((!$checkoutMethod || 
        $checkoutMethod != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) && 
        !Mage::helper('checkout')->isAllowedGuestCheckout($this->_quote, $this->_quote->getStoreId())) 
    {
      Mage::throwException(Mage::helper('zipmoneypayment')->__('Please log in to proceed to checkout.'));
    }

    // Calculate Totals
    $this->_quote->collectTotals();

    if (!$this->_quote->getGrandTotal() && !$this->_quote->hasNominalItems()) {
      Mage::throwException($this->_helper->__('Cannot process the order due to zero amount.'));
    }

    $this->_quote->reserveOrderId()->save(); 


    $request = Mage::helper('zipmoneypayment/request')->prepareCheckout($this->_quote);

    $this->_logger->debug("Checkout Request:- ".$this->_helper->json_encode($request)); 

    $checkout = $this->getApi()->checkoutsCreate($request);           

    $this->_logger->debug("Checkout Response:- ".$this->_helper->json_encode($checkout));

    if(isset($checkout->error)){
      Mage::throwException($this->_helper->__('Cannot get redirect URL from zipMoney.'));
    } 

    $this->_checkoutId  = $checkout->getId();
    
    $this->_quote->setZipmoneyCid($this->_checkoutId)
                 ->save();

    $this->_redirectUrl = $checkout->getUri();   

    return $checkout;
  }


  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param $quote
   * @return null
   * @throws Mage_Core_Exception
   */
  public function charge()
  {
    if (!$this->_order || !$this->_order->getId()) {      
      Mage::throwException($this->_helper->__('The order does not exist.'));
    }
    
    $request = Mage::helper('zipmoneypayment/request')->prepareCharge($this->_order);
    
    $this->_logger->debug("Charge Request:- ".$this->_helper->json_encode($request)); 

    $charge = $this->getApi()
                     ->chargesCreate($request);
    
    $this->_logger->debug("Charge Response:- ".$this->_helper->json_encode($charge));
    
    if(isset($response->error)){
      Mage::throwException($this->_helper->__('Could not create the charge'));
    }

    if(!$charge->getState() || !$charge->getId()){
      Mage::throwException($this->_helper->__('Invalid Charge'));
    }
    
  //  $charge->setState("authorised");

    $this->_logger->debug($this->_helper->__("Charge State:- %s",$charge->getState()));

    if($charge->getId()){
      $this->_order->getPayment()
                   ->setZipmoneyChargeId($charge->getId())
                   ->save();
    }

    $this->_chargeResponse($charge,false);
  }


  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param $quote
   * @return null
   * @throws Mage_Core_Exception
   */
  public function refund($amount, $reason)
  {
    if (!$this->_order || !$this->_order->getId()) {      
      Mage::throwException($this->_helper->__('The order does not exist.'));
    }
    
    if (!$amount) {      
      Mage::throwException($this->_helper->__('Please provide the refund amount.'));
    }
    
    $request = Mage::helper('zipmoneypayment/request')->prepareRefund($this->_order, $amount, $reason);
    
    $this->_logger->debug("Refund Request:- " . $this->_helper->json_encode($request)); 

    $refund = $this->getApi()
                   ->refundsCreate($request);
    
    $this->_logger->debug("Refund Response:- ".$this->_helper->json_encode($refund));
    
    if(isset($response->error)){
      Mage::throwException($this->_helper->__('Could not create the refund'));
    }

    if(!$refund->getId()){
      Mage::throwException($this->_helper->__('Invalid Refund'));
    }
    return $refund;
  }




  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param $quote
   * @return null
   * @throws Mage_Core_Exception
   */
  public function captureCharge($amount)
  {
    if (!$this->_order || !$this->_order->getId()) {      
      Mage::throwException($this->_helper->__('The order does not exist.'));
    }
    
    $request = Mage::helper('zipmoneypayment/request')->prepareCaptureCharge($this->_order, $amount);
    
    $this->_logger->debug("Capture Charge Request:- ".$this->_helper->json_encode($request)); 

    $charge = $this->getApi()
                   ->chargesCapture($this->_order->getPayment()->getZipmoneyChargeId(),$request);
    
    $this->_logger->debug("Capture Charge Response:- ".$this->_helper->json_encode($charge));
    
    if(isset($response->error)){
      Mage::throwException($this->_helper->__('Could not capture the charge'));
    }

    if(!$charge->getState()){
      Mage::throwException($this->_helper->__('Invalid Charge'));
    }

    $this->_logger->debug($this->_helper->__("Charge State:- %s",$charge->getState()));

    return $charge;
  }


  protected function _chargeResponse($charge, $isAuthAndCapture)
  {

    switch ($charge->getState()) {
      case 'captured':
        /* 
         * Capture Payment 
         */
        $this->_logger->info($this->_helper->__("Capture Payment"));
          
        $this->_capture($charge->getId(), $isAuthAndCapture);
        
        break;
      case 'authorised':
        /* 
         * Authorise Payment 
         */   
        $this->_logger->info($this->_helper->__("Authorise Payment"));

        $this->_authorise($charge->getId());

        break;
      default:
        # code...
        break;
    }
    
    return $charge;
  }


  /**
   * Place the order and recurring payment profiles when customer returned from paypal
   * Until this moment all quote data must be valid
   *
   * @param string $token
   * @param string $shippingMethodCode
   */
  public function placeOrder()
  {
    // $this->_order =  Mage::getSingleton("sales/order")->load(287);
    // return $this->_order;

    $this->_logger->debug($this->_helper->__('Quote Grand Total:- %s', $this->_quote->getGrandTotal()));
    $this->_logger->debug($this->_helper->__('Quote Checkout Method:- %s', $this->_quote->getCheckoutMethod()));
    $this->_logger->debug($this->_helper->__('Quote Customer Id:- %s', $this->_quote->getCutomerId()));
    
    $checkoutMethod = $this->getCheckoutMethod();
    
    $this->_logger->debug($this->_helper->__('Quote Checkout Method:- %s', $checkoutMethod));
    
    $isNewCustomer = false;
    switch ($checkoutMethod) {
      case Mage_Checkout_Model_Type_Onepage::METHOD_GUEST:
        $this->_prepareGuestQuote();
        break;
      case Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER:
        $this->_prepareNewCustomerQuote();
        $isNewCustomer = true;
        break;
      default:
        $this->_logger->debug($this->_helper->__('Load customer from session.'));
        $this->_prepareCustomerQuote();
        break;
    }

    $this->_ignoreAddressValidation();
    $this->_quote->collectTotals();
    $service = Mage::getModel('sales/service_quote', $this->_quote);
    $service->submitAll();
    $this->_quote->save();

    if ($isNewCustomer) {
      try {
        $this->_involveNewCustomer();
      } catch (Exception $e) {
        Mage::logException($e);
      }
    }

    $order = $service->getOrder();

    if (!$order) {
      $this->_logger->info($this->_helper->__('Couldnot place the order'));
      return false;
    } 

    $this->_logger->info($this->_helper->__('Successfull to place the order'));

    switch ($order->getState()) {
      case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
        break;
      // regular placement, when everything is ok
      case Mage_Sales_Model_Order::STATE_PROCESSING:
      case Mage_Sales_Model_Order::STATE_COMPLETE:
      case Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW:
          $order->queueNewOrderEmail();
          break;
    }
    $this->_order = $order;
    return $order;
  }
  
  /**
   * Get checkout method
   *
   * @return string
   */
  public function getCheckoutMethod()
  {
    if ($this->getCustomerSession()->isLoggedIn()) {
      return Mage_Checkout_Model_Type_Onepage::METHOD_CUSTOMER;
    }
    if (!$this->_quote->getCheckoutMethod()) {
      if (Mage::helper('checkout')->isAllowedGuestCheckout($this->_quote)) {
        $this->_quote->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
      } else {
        $this->_quote->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
      }
    }
    return $this->_quote->getCheckoutMethod();
  }

  /**
   * Get customer session object
   *
   * @return Mage_Customer_Model_Session
   */
  public function getCustomerSession()
  {
    return $this->_customerSession;
  }

  public function getRedirectUrl()
  {
    return $this->_redirectUrl;
  } 

  public function getCheckout()
  {
    return $this->_checkout;
  } 

  public function getCharge()
  {
    return $this->_charge;
  } 

  public function getCheckoutId()
  {
    return $this->_checkoutId;
  } 

  public function getApi()
  {
    if(null === $this->_api){
      Mage::throwException($this->_helper->__('Api class has not been set.'));
    }

    return $this->_api;
  }


  public function setApi($api)
  {
    if(is_object($api)) {
      $this->_api =  $api;
    } else if(is_string($api)) {
      $this->_api = new $api;
    }

    return $this;
  }

  public function getQuote()
  {
    return $this->_quote;
  }

  public function setQuote($quote)
  {
    if ($quote) {
      $this->_quote = $quote;
    }
  }

  public function getOrder()
  {
    return $this->_order;
  }


  public function setOrder($order)
  {
    if ($order) {
      $this->_order = $order;
    }
  }


}