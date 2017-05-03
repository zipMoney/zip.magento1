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
	protected $_config = null;
	protected $_button_selector = 'button[type=submit][class~="btn-checkout"]';
		
	public function __construct()
	{
		parent::__construct();
	
	  $this->_config = Mage::getSingleton('zipmoneypayment/config');
	}

	public function getCheckoutUrl()
	{
		return Mage::getUrl("zipmoneypayment/standard/",true);
	}

	public function getRedirectUrl()
	{
		return Mage::getUrl("zipmoneypayment/complete/",true);
	}

	public function isRedirect()
	{
		return (int)!$this->_config->isInContextCheckout();
	}

	public function getPlaceOrderButtonSelector()
	{
		return $this->getButtonSelector() ? $this->getButtonSelector() : $this->_button_selector;
	}

	public function getExtensionName()
	{ 
		return  strtolower(Mage::app()->getRequest()->getControllerModule());
	}

}