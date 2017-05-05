<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Zipmoney_ZipmoneyPayment_Block_Checkout extends Mage_Core_Block_Template
{ 
  /**  
   * @var Zipmoney_ZipmoneyPayment_Model_Config
   */ 
  protected $_config;  
  /**  
   * @var Zipmoney_ZipmoneyPayment_Helper_Data
   */ 
  protected $_helper;
  /**
   * @var string
   */ 
	protected $_button_selector = 'button[type=submit][class~="btn-checkout"]';
		
	public function __construct()
	{
		parent::__construct();
    $this->_helper = Mage::helper("zipmoneypayment");
	
	  $this->_config = Mage::getSingleton('zipmoneypayment/config');
	}

	/**
   * Returns the checkout url.
   *
   * @return string
   */
	public function getCheckoutUrl()
	{
		return $this->_helper->getUrl("zipmoneypayment/standard/");
	}

	/**
   * Returns the redirect url.
   *
   * @return string
   */
	public function getRedirectUrl()
	{
		return $this->_helper->getUrl("zipmoneypayment/complete/");
	}

	/**
   * Whether to redirect or not.
   *
   * @return int
   */
	public function isRedirect()
	{
		return (int)!$this->_config->isInContextCheckout();
	}
	/**
   * Returns the place order button selector
   *
   * @return string
   */
	public function getPlaceOrderButtonSelector()
	{
		return $this->getButtonSelector() ? $this->getButtonSelector() : $this->_button_selector;
	}
  
	/**
   * Returns the extension name if specified in the config otherwise picks up from the request
   *
   * @return string
   */
	public function getExtensionName()
	{ 
    /** Check if extension name has been set explicitly in the zipmoneypayment.xml in the appropriate layout handle for the checkout page
     * E.g. 
     * <action method="setData">
     *    <name>extension_name</name>
     *     <value>Mage_Checkout</value>
     *  </action>
     *
     */
    if($extension = $this->getData('extension_name')){
      return $extension;
    } else {
		  return  strtolower(Mage::app()->getRequest()->getControllerModule());
    }
	}
}