<?php
use \zipMoney\ApiException;
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_Charge extends Zipmoney_ZipmoneyPayment_Model_Checkout_Abstract{

  /**
   * State helper variables
   * @var string
   */
  protected $_chargeResult = '';

  protected $_apiClass = '\zipMoney\Api\ChargesApi';

  protected $_response = null;

  /**
   * Set quote and config instances
   * @param array $params
   */
  public function __construct($params = array())
  {   

    parent::__construct($params);

    if (isset($params['order'])) {
      if($params['order'] instanceof Mage_Sales_Model_Order){
        $this->_order = $params['order'];
      } else {
        Mage::throwException('Order instance is required.');
      }
    }
    parent::__construct($params);

    if (isset($params['api_class'])) {
      if(class_exists($params['api_class'])){
        $this->_apiClass = $params['api_class'];
        $this->setApi($this->_apiClass);
      } else {
        Mage::throwException("Invalid Api Class [ ".$params['api_class']." ]");
      }
    }
  }

  /**
   * Prepare quote for guest checkout order submit
   *
   * @return Zipmoney_ZipmoneyPayment_Model_Charge
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

    $this->_logger->info($this->_helper->__('Creating new customer with email %s', $quote->getCustomerEmail()));

    $customerId = $this->_helper->lookupCustomerId($quote->getCustomerEmail());
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

    $customer->setEmail($quote->getCustomerEmail())
             ->setPrefix($quote->getCustomerPrefix())
             ->setFirstname($quote->getCustomerFirstname())
             ->setMiddlename($quote->getCustomerMiddlename())
             ->setLastname($quote->getCustomerLastname())
             ->setSuffix($quote->getCustomerSuffix())
             ->setPassword($customer->decryptPassword($quote->getPasswordHash()))
             ->setPasswordHash($customer->hashPassword($customer->getPassword()))
             ->save();

    $quote->setCustomer($customer);

    $this->_logger->debug($this->_helper->__('Customer password is %s ', ($customer->getPassword() ? 'not empty' : 'empty')));

    $this->_logger->debug($this->_helper->__('Customer password_hash is %s ', ($customer->getPasswordHash() ? 'not empty' : 'empty')));

    $this->_logger->info($this->_helper->__('The new customer has been created successfully. Customer id: %s', $customer->getId()));

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


    if($this->getCustomerSession()->isLoggedIn()){
      $this->_logger->debug($this->_helper->__('Load customer from session.'));
      $customer = $this->getCustomerSession()->getCustomer();
      $this->_logger->debug($this->_helper->__("Creating Order as Logged in Customer "));
    } else {
      $this->_logger->debug($this->_helper->__('Load customer from db.'));
      $customer = Mage::getSingleton("customer/customer")->load($quote->getCustomerId());
      $this->_logger->debug($this->_helper->__("Creating Order on behalf of Customer %s",$quote->getCustomerId()));
    }

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
   * Involve new customer to system
   *
   * @return Zipmoney_ZipmoneyPayment_Model_Express_Checkout
   */
  protected function _involveNewCustomer()
  {
      $customer = $this->_quote->getCustomer();
      if ($customer->isConfirmationRequired()) {
        $customer->sendNewAccountEmail('confirmation');
        $url = Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail());
        $this->getCustomerSession()->addSuccess(
            Mage::helper('customer')->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.', $url)
        );
      } else {
        $customer->sendNewAccountEmail();
          //$this->getCustomerSession()->loginById($customer->getId());
      }
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

    $this->_logger->info($this->_helper->__("Payment Authorised"));

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

    $this->_logger->info($this->_helper->__("Payment Captured"));

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


  protected function _chargeResponse($charge, $isAuthAndCapture)
  {
    switch ($charge->getState()) {
      case 'captured':
        /*
         * Capture Payment
         */
        $this->_capture($charge->getId(), $isAuthAndCapture);

        break;
      case 'authorised':
        /*
         * Authorise Payment
         */
        $this->_authorise($charge->getId());

        break;
      default:
        # code...
        break;
    }

    return $charge;
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

    $payload = $this->_payload->getChargePayload($this->_order);

    $this->_logger->debug("Charge Payload:- ".$this->_helper->json_encode($payload));

    try {
      $charge = $this->getApi()
                     ->chargesCreate($payload,$this->genIdempotencyKey());

      $this->_logger->debug("Charge Response:- ".$this->_helper->json_encode($charge));

      if(isset($response->error)){
        Mage::throwException($this->_helper->__('Could not create the charge'));
      }

      if(!$charge->getState() || !$charge->getId()){
        Mage::throwException($this->_helper->__('Invalid Charge'));
      }

      $this->_logger->debug($this->_helper->__("Charge State:- %s",$charge->getState()));

      if($charge->getId()){
        $this->_order->getPayment()
                     ->setZipmoneyChargeId($charge->getId())
                     ->save();
      }

      $this->_chargeResponse($charge,false);

    } catch(ApiException $e){
      $this->_logger->debug("Error:-".$e->getCode()."-".json_encode($e->getResponseBody()));

      $message = $this->_helper->__("Could not process the payment");

      if($e->getCode() == 402 && 
        $mapped_error_code = $this->_config->getMappedErrorCode($e->getResponseObject()->getError()->getCode())){
        $message = $this->_helper->__('The payment was declined by Zip.(%s)',$mapped_error_code);
      }

      // Cancel the order
      $this->_helper->cancelOrder($this->_order,$e->getResponseObject()->getError()->getMessage());
      Mage::throwException($message);

    } 

    return $charge;
  }


  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param $quote
   * @return null
   * @throws Mage_Core_Exception
   */
  public function refundCharge($amount, $reason)
  {
    if (!$this->_order || !$this->_order->getId()) {
      Mage::throwException($this->_helper->__('The order does not exist.'));
    }

    if (!$amount) {
      Mage::throwException($this->_helper->__('Please provide the refund amount.'));
    }

    $payload = $this->_payload->getRefundPayload($this->_order, $amount, $reason);

    $this->_logger->debug("Refund Payload:- " . $this->_helper->json_encode($payload));
    
    try {
        $refund = $this->getApi()
                       ->refundsCreate($payload,$this->genIdempotencyKey());

        $this->_logger->debug("Refund Response:- ".$this->_helper->json_encode($refund));

        if(isset($response->error)){
          Mage::throwException($this->_helper->__('Could not create the refund'));
        }

        if(!$refund->getId()){
          Mage::throwException($this->_helper->__('Invalid Refund'));
        }
    } catch(ApiException $e){
      $this->_logger->debug("Error:-".$e->getCode()."-".json_encode($e->getResponseBody()));

      $message = $this->_helper->__("Could not process the payment");

      if($e->getCode() == 402 && 
        $mapped_error_code = $this->_config->getMappedErrorCode($e->getResponseObject()->getError()->getCode())){
        $message = $this->_helper->__('The payment was declined by Zip.(%s)',$mapped_error_code);
      }

      $order->addStatusHistoryComment($e->getResponseObject()->getError()->getMessage())
            ->save();
      // Cancel the order
      Mage::throwException($message);
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

    $payload = $this->_payload->getCapturePayload($this->_order, $amount);

    $this->_logger->debug("Capture Charge Payload:- ".$this->_helper->json_encode($payload));
   
    try {

        $charge = $this->getApi()
                       ->chargesCapture($this->_order->getPayment()->getZipmoneyChargeId(),$payload,$this->genIdempotencyKey());

        $this->_logger->debug("Capture Charge Response:- ".$this->_helper->json_encode($charge));

        if(isset($response->error)){
          Mage::throwException($this->_helper->__('Could not capture the charge'));
        }

        if(!$charge->getState()){
          Mage::throwException($this->_helper->__('Invalid Charge'));
        }

        $this->_logger->debug($this->_helper->__("Charge State:- %s",$charge->getState()));
    } catch(ApiException $e){
      $this->_logger->debug("Error:-".$e->getCode()."-".json_encode($e->getResponseBody()));

      $message = $this->_helper->__("Could not process the payment");

      if($e->getCode() == 402 && 
        $mapped_error_code = $this->_config->getMappedErrorCode($e->getResponseObject()->getError()->getCode())){
        $message = $this->_helper->__('The payment was declined by Zip.(%s)',$mapped_error_code);
      }
      
      // Cancel the order
      $order->addStatusHistoryComment($e->getResponseObject()->getError()->getMessage())
            ->save();   

      // Cancel the order
      Mage::throwException($message);
    } 


    return $charge;
  }


  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param $quote
   * @return null
   * @throws Mage_Core_Exception
   */
  public function cancelCharge()
  {
    if (!$this->_order || !$this->_order->getId()) {
      Mage::throwException($this->_helper->__('The order does not exist.'));
    }

    $this->_logger->debug("Cancel Charge For Order:- ".$this->_order->getId());
    try {

      $charge = $this->getApi()
                     ->chargesCancel($this->_order->getPayment()->getZipmoneyChargeId(),$this->genIdempotencyKey());

      $this->_logger->debug("Cancel Charge Response:- ".$this->_helper->json_encode($charge));

      if(isset($response->error)){
        Mage::throwException($this->_helper->__('Could not cancel the charge'));
      }

      if(!$charge->getState()){
        Mage::throwException($this->_helper->__('Invalid Charge Cancel'));
      }

      $this->_logger->debug($this->_helper->__("Charge State:- %s",$charge->getState()));
    } catch(ApiException $e){
      $this->_logger->debug("Error:-".$e->getCode()."-".json_encode($e->getResponseBody()));

      $message = $this->_helper->__("Could not process the payment");

      if($e->getCode() == 402 && 
        $mapped_error_code = $this->_config->getMappedErrorCode($e->getResponseObject()->getError()->getCode())){
        $message = $this->_helper->__('The payment was declined by Zip.(%s)',$mapped_error_code);
      }

      $order->addStatusHistoryComment($e->getResponseObject()->getError()->getMessage())
            ->save();
      // Cancel the order
      Mage::throwException($message);
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
    // $order = Mage::getSingleton("sales/order")->loadByIncrementId("145000246");
    // $this->_order = $order;
    // return $order;
    $checkoutMethod = $this->getCheckoutMethod();

    $this->_logger->debug(
      $this->_helper->__('Quote Grand Total:- %s Quote Customer Id:- %s Checkout Method:- %s', $this->_quote->getGrandTotal(),$this->_quote->getCustomerId(),$checkoutMethod)
    );


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

    $session = Mage::getSingleton('checkout/session');
    $session->setLastQuoteId($this->_quote->getId())
            ->setLastSuccessQuoteId($this->_quote->getId())
            ->clearHelperData();

    $session->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());
  
    return $order;
  }

}