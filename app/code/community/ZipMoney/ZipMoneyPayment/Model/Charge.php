<?php

class Zipmoney_ZipmoneyPayment_Model_Charge{

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

  protected $_apiRequestHelper;

  private $_apiClass = "\zipMoney\Client\Api\ChargesApi";


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
  
    $this->_helper = Mage::helper("zipmoneypayment");   
    $this->_apiRequestHelper = Mage::helper('zipmoneypayment/request');
    // Set the quote object
    $this->_apiRequestHelper->setQuote($this->_quote);

    $this->_logger = Mage::getSingleton('zipmoneypayment/logger');
    
    $merchant_public_key  = Mage::getSingleton('zipmoneypayment/config')->getMerchantPrivateKey();
    $environment          = Mage::getSingleton('zipmoneypayment/config')->getEnvironment();

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

  public function setApi($api = null)
  {
    if (null === $api) {
      $this->_api = new $this->_apiClass;
    } else if(is_object($api)) {
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
    try {

      if (!$this->_quote || !$this->_quote->getId()) {
        Mage::throwException(Mage::helper('zipmoneypayment')->__('The quote does not exist.'));
      }
      
      //$this->_checkQuoteToken($oQuote);

      if ($this->_quote->getIsMultiShipping()) {
        $this->_quote->setIsMultiShipping(false);
        $this->_quote->removeAllAddresses();
      }

      $checkoutMethod = $this->_quote->getCheckoutMethod();

      if ((!$checkoutMethod || 
          $checkoutMethod != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) && 
          !Mage::helper('checkout')->isAllowedGuestCheckout($this->_quote, $this->_quote->getStoreId())) 
      {
        Mage::throwException(Mage::helper('zipmoneypayment')->__('Please log in to proceed to checkout.'));
      }

      // Calculate Totals
      $this->_quote->collectTotals();

      if (!$this->_quote->getGrandTotal() && !$this->_quote->hasNominalItems()) {
        //Mage::throwException($this->_helper->__('Cannot process the order due to zero amount.'));
        Mage::throwException($this->_helper->__('Cannot process the order due to zero amount.'));
      }

      $this->_quote->reserveOrderId()->save(); 

      $request = $this->_apiRequestHelper->prepareCheckout($this->_quote,$checkoutSource);
      
      $this->_logger->debug("Checkout Request:- ".$this->_helper->json_encode($request)); 

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
      $this->_redirectUrl = $response->getUri();
      return true;
    } catch (Exception $e) {
      Mage::getSingleton('zipmoneypayment/logger')->log($e->getMessage());
      throw new Exception($this->_helper->__($e->getMessage()));
    }

    return  false;
  }

  public function getRedirectUrl()
  {
    return $this->_redirectUrl;
  } 

  public function getCheckoutId()
  {
    return $this->_checkoutId;
  } 
}

