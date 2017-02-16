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
  protected $_customerSession;

  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_quote;

  protected $_api;

  protected $_helper;

  protected $_requestHelper;

  private $_apiClass = "\zipMoney\Client\Api\CheckoutsApi";


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
    // Set the quote object
    $this->_requestHelper->setQuote($this->_quote);


    $this->_logger = Mage::getSingleton('zipmoneypayment/logger');

    \zipMoney\Configuration::getDefaultConfiguration()->setApiKey('Authorization', 'YOUR_API_KEY');
    \zipMoney\Configuration::getDefaultConfiguration()->setEnvironment('mock');

  }


  protected function _getApi()
  {
    if (null === $this->_api) {
      $this->_setApi();
    }

    return $this->_api;
  }

  protected function _setApi($api = null)
  {
    if (null === $api) {
      $this->_api = new $this->_apiClass;
    } else {
      $this->_api = new $api;
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
        throw Mage::exception('Zipmoney_ZipmoneyPayment', Mage::helper('zipmoneypayment')->__('The quote does not exist.'));
      }
      
      //$this->_checkQuoteToken($oQuote);

      if ($this->_quote->getIsMultiShipping()) {
        $this->_quote->setIsMultiShipping(false);
        $this->_quote->removeAllAddresses();
      }

      $customer = Mage::getSingleton('customer/session')->getCustomer();

      $checkoutMethod = $this->_quote->getCheckoutMethod();
      
      if ((!$checkoutMethod || 
          $checkoutMethod != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER) && 
          !Mage::helper('checkout')->isAllowedGuestCheckout($this->_quote, $this->_quote->getStoreId())) 
      {
        $message = Mage::helper('zipmoneypayment')->__('Please log in to proceed to checkout.');
        Mage::throwException($message);
      }

      // Calculate Totals
      $this->_quote->collectTotals();
      
      if (!$this->_quote->getGrandTotal() && !$this->_quote->hasNominalItems()) {
        throw Mage::exception('Zipmoney_ZipmoneyPayment', $this->__('Cannot process the order due to zero amount.'));
      }

      $this->_quote->reserveOrderId()->save(); 

      $request = $this->_requestHelper->prepareCheckout($this->_quote,$checkoutSource);
      
      $this->_logger->debug("Checkout Request:- ".$this->_helper->json_encode($request));    
      
      $response = $this->_getApi()->checkoutsCreate($request);
    
      $this->_logger->debug("Checkout Response:- ".$this->_helper->json_encode($response));

      if(isset($response->error)){
        // Log the error
        throw Mage::exception('Zipmoney_ZipmoneyPayment', $this->__('Cannot get redirect URL from zipMoney.'));
      } 
      // else if(!isset($response['merchant_id'])
      //       || !isset($response['merchant_key'])
      //       || !$oApiHelper->isApiKeysValid($response['merchant_id'], $response['merchant_key'])) {
      //   // response api key are invalid
      //   throw Mage::exception('Zipmoney_ZipmoneyPayment', $this->__('Incorrect API keys in response.'));
      // }

      $this->_checkoutId  = $response->getId();
      $this->_redirectUrl = $response->getUri();
    } catch (Exception $e) {
      Mage::getSingleton('zipmoneypayment/logger')->log($e->getMessage());
      // Handle Exception
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

