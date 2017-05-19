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

class Zipmoney_ZipmoneyPayment_Model_Checkout_Abstract
{
 
  /**
   * @var Mage_Sales_Model_Quote
   */
  protected $_quote;
  /**
   * @var object
   */
  protected $_api;
  /**
   * @var Zipmoney_ZipmoneyPayment_Model_Config
   */
  protected $_config;
  /**
   * @var object
   */
  protected $_response;
  /**
   * @var Zipmoney_ZipmoneyPayment_Helper_Data
   */
  protected $_helper;
  /**
   * @var Zipmoney_ZipmoneyPayment_Helper_Payload
   */
  protected $_payload;
  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_customerSession;
  /**
   * @var string
   */
  private $_apiClass = null;
  /**
   * @const 
   */
  const STATUS_MAGENTO_AUTHORIZED = "zip_authorised";

  /**
   * Initializes different classes
   *
   * @param array $params
   */
  public function __construct($params = array())
  {
    $this->_customerSession = isset($params['session']) && $params['session'] instanceof Mage_Customer_Model_Session
            ? $params['session'] : Mage::getSingleton('customer/session');

    $this->_config = Mage::getSingleton('zipmoneypayment/config');

    $this->_helper = Mage::helper("zipmoneypayment");

    $this->_logger = Mage::getSingleton('zipmoneypayment/logger');

    $this->_payload = Mage::helper('zipmoneypayment/payload');

    $apiConfig = \zipMoney\Configuration::getDefaultConfiguration();
    
    $apiConfig->setApiKey('Authorization', $this->_config->getMerchantPrivateKey())
              ->setApiKeyPrefix('Authorization', 'Bearer')
              ->setEnvironment($this->_config->getEnvironment())
              ->setPlatform("Magento/".Mage::getVersion()." Zipmoney_ZipmoneyPayment/".$this->_helper->getExtensionVersion());
  }

  /**
   * Checks if customer exists by email
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
   * Handles the api exception
   *
   * @param  ApiException $e
   * @return string
   */
  protected function _handleException($e)
  {
    if($e instanceof ApiException){
      $apiError = '';
      $message = $this->_helper->__("Could not process the payment");
      switch($e->getCode()){
        case 0:
          $logMessage = "ApiError:- ".$e->getMessage();
        break;
        default:
          $logMessage = "ApiError:-".$e->getCode().$e->getMessage()."-".json_encode($e->getResponseBody());
          if($e->getCode() == 402 && 
            $mapped_error_code = $this->_config->getMappedErrorCode($e->getResponseObject()->getError()->getCode())){
            $message = $this->_helper->__('The payment was declined by Zip.(%s)',$mapped_error_code);
          }
          $apiError = $e->getResponseObject()->getError()->getMessage();
        break;
      }      

      $this->_logger->debug($logMessage);  

      return array($apiError,$message,$logMessage);             
    }
    return null;
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
  
  /**
   * Retrieves the charge object object.
   *
   * @return string
   */
  public function getCharge()
  {
    return $this->_charge;
  }

  /**
   * Returns the api object.
   *
   * @return string
   * @throws Mage_Core_Exception
   */
  public function getApi()
  {
    if(null === $this->_api){
      Mage::exception("Mage_Core",$this->_helper->__('Api class has not been set.'));
    }

    return $this->_api;
  }

  /**
   * Sets the api object.
   *
   * @param string | object $api
   * @return Zipmoney_ZipmoneyPayment_Model_Checkout_Abstract
   */
  public function setApi($api)
  {
    if(is_object($api)) {
      $this->_api =  $api;
    } else if(is_string($api)) {
      $this->_api = new $api;
    }
    return $this;
  }

  /**
   * Returns the quote object.
   *
   * @return Mage_Sales_Model_Quote
   */
  public function getQuote()
  {
    return $this->_quote;
  }

  /**
   * Sets the quote object.
   *
   * @param Mage_Sales_Model_Quote $quote
   * @return Zipmoney_ZipmoneyPayment_Model_Checkout_Abstract
   */
  public function setQuote($quote)
  {
    if ($quote) {
      $this->_quote = $quote;
    }
    return $this;
  }

  /**
   * Returns the order object.
   *
   * @return Mage_Sales_Model_Order
   */
  public function getOrder()
  {
    return $this->_order;
  }

  /**
   * Sets the order object.
   *
   * @param Mage_Sales_Model_Order $order
   * @return Zipmoney_ZipmoneyPayment_Model_Checkout_Abstract
   */
  public function setOrder($order)
  {
    if ($order) {
      $this->_order = $order;
    }
    return $this;
  }

  /**
   * Generates the unique id.
   *
   * @return string
   */
  public function genIdempotencyKey()
  {
    return uniqid();
  }

 
}