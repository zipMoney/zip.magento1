<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_Checkout_Abstract{
  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_quote;

  protected $_api;

  protected $_response;

  protected $_helper;

  protected $_payloadHelper;
  
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

  public function getCheckoutId()
  {
    return $this->_checkoutId;
  } 

  public function getCharge()
  {
    return $this->_charge;
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