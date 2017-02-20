<?php

class Zipmoney_ZipmoneyPayment_Model_Standard_Checkout{

  /**
   * State helper variables
   * @var string
   */
  protected $_redirectUrl = '';
  protected $_checkoutId = '';
  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_quote;

  protected $_api;

  protected $_helper;

  protected $_requestHelper;
  
  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_customerSession;

  private $_checkoutApi = "\zipMoney\Client\Api\CheckoutsApi";
  private $_chargeApi = "\zipMoney\Client\Api\CheckoutsApi";

  /**
   * Set quote and config instances
   * @param array $params
   */
  public function __construct($params = array())
  {
    
    if (isset($params['quote']) && $params['quote'] instanceof Mage_Sales_Model_Quote) {
      $this->_quote = $params['quote'];
    } else {
      throw new Exception('Quote instance is required.');
    }   

    $this->_customerSession = isset($params['session']) && $params['session'] instanceof Mage_Customer_Model_Session
            ? $params['session'] : Mage::getSingleton('customer/session'); 
  
    $this->_helper = Mage::helper("zipmoneypayment");   

    $this->_requestHelper = Mage::helper('zipmoneypayment/request');
    
    $this->_logger = Mage::getSingleton('zipmoneypayment/logger');
    
    $merchant_public_key  = Mage::getSingleton('zipmoneypayment/config')->getMerchantPrivateKey();
    $environment  = 'mock';//Mage::getSingleton('zipmoneypayment/config')->getEnvironment();


    \zipMoney\Configuration::getDefaultConfiguration()->setApiKey('Authorization', $merchant_public_key);
    \zipMoney\Configuration::getDefaultConfiguration()->setEnvironment($environment);
  }


  public function getApi()
  {

    if (null === $this->_api) {
      $this->setApi();
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

  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param $quote
   * @return null
   * @throws Mage_Core_Exception
   */
  public function start($checkoutSource)
  {

    if (!$this->_quote || !$this->_quote->getId()) {
      Mage::throwException(Mage::helper('zipmoneypayment')->__('The quote does not exist.'));
    }
    
    //$this->_checkQuoteToken($oQuote);

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
    
    // Set the quote object
    $this->_requestHelper->setQuote($this->_quote);

    $request = $this->_requestHelper->prepareCheckout($this->_quote,$checkoutSource);
    
    $this->_logger->debug("Checkout Request:- ".$this->_helper->json_encode($request)); 

    $this->setApi($this->_checkoutApi);

    $response = $this->getApi()->checkoutsCreate($request);

    $this->_logger->debug("Checkout Response:- ".$this->_helper->json_encode($response));

    if(isset($response->error)){
      Mage::throwException($this->_helper->__('Cannot get redirect URL from zipMoney.'));
    } 

    // else if(!isset($response['merchant_id'])
    //       || !isset($response['merchant_key'])
    //       || !$oApiHelper->isApiKeysValid($response['merchant_id'], $response['merchant_key'])) {
    //   // response api key are invalid
    //   throw Mage::exception('Zipmoney_ZipmoneyPayment', $this->__('Incorrect API keys in response.'));
    // }

    $this->_checkoutId  = $response->getId();
    
    $this->_quote->setZipmoneyCid($this->_checkoutId)->save();

    $this->_redirectUrl = $response->getUri();   

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
      Mage::throwException(Mage::helper('zipmoneypayment')->__('The order does not exist.'));
    }
    
    $this->_requestHelper->setOrder($this->_order);

    $request = $this->_requestHelper->prepareCharge($this->_order);
    
    $this->_logger->debug("Charge Request:- ".$this->_helper->json_encode($request)); 

    $this->setApi($this->_chargeApi);

    $response = $this->getApi()->chargesCreate($request);

    $this->_logger->debug("Charge Response:- ".$this->_helper->json_encode($response));

    if(isset($response->error)){
      Mage::throwException($this->_helper->__('Cannot get redirect URL from zipMoney.'));
    }

  }

  public function getRedirectUrl()
  {
    return $this->_redirectUrl;
  } 


  public function getCheckoutId()
  {
    return $this->_checkoutId;
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

    $customerId = $this->_lookupCustomerId();
    if ($customerId) {
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
    $customer->save();
    $quote->setCustomer($customer);

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
   * Place the order and recurring payment profiles when customer returned from paypal
   * Until this moment all quote data must be valid
   *
   * @param string $token
   * @param string $shippingMethodCode
   */
  public function place()
  {
    $isNewCustomer = false;
    switch ($this->getCheckoutMethod()) {
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

  //  $this->_ignoreAddressValidation();
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
      return;
    }


    switch ($order->getState()) {
      case Mage_Sales_Model_Order::STATE_PENDING_PAYMENT:
        // TODO
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
   * Make sure addresses will be saved without validation errors
   */
  private function _ignoreAddressValidation()
  {
    $this->_quote->getBillingAddress()->setShouldIgnoreValidation(true);
    if (!$this->_quote->getIsVirtual()) {
      $this->_quote->getShippingAddress()->setShouldIgnoreValidation(true);
      if (!$this->_config->requireBillingAddress && !$this->_quote->getBillingAddress()->getEmail()) {
        $this->_quote->getBillingAddress()->setSameAsBilling(1);
      }
    }
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
}