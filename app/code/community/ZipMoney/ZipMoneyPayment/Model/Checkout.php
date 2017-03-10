<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Model_Standard_Checkout extends Zipmoney_ZipmoneyPayment_Model_Checkout_Abstract{

  /**
   * State helper variables
   * @var string
   */
  protected $_redirectUrl = '';
  protected $_checkoutId = '';
  
  /**
   * zipMoney Checkouts Api Class
   *
   * @var string
   */  
  protected $_apiClass = '\zipMoney\Client\Api\CheckoutsApi';

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
    } 

    $this->setApi($this->_apiClass);

    parent::__construct($params);

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

    $this->_logger->debug("Checkout Response:- ".$this->_helper->json_encode($checkout));

    $this->_checkoutId  = $checkout->getId();
    
    $this->_logger->debug("Checkout Id:- ".$this->_checkoutId);

    $this->_quote->setZipmoneyCid($this->_checkoutId)
                 ->save();

    $this->_redirectUrl = $checkout->getUri();   
    $this->_logger->debug("Redirect Uri:- ".$this->_redirectUrl);

    return $checkout;
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