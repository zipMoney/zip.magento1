<?php
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */
 
class Zipmoney_ZipmoneyPayment_Model_Config 
{
	/**
	 * Method Code 
   * @const
   */
	const METHOD_CODE = "zipmoneypayment";
	/**
	 * zipMoney Authorised Status
   * @const  
   */
	const STATUS_MAGENTO_AUTHORIZED = "zip_authorised";
	/**
	 * Config Path Payment Active
   * @const 
   */
	const PAYMENT_ZIPMONEY_PAYMENT_ACTIVE	= 'payment/zipmoneypayment/active';
	/**
	 * Config Path API Environment
   * @const 
   */
	const PAYMENT_ZIPMONEY_PAYMENT_ENVIRONMENT = 'payment/zipmoneypayment/environment';
	/**
	 * Config Path API Private Key
   * @const 
   */
	const PAYMENT_ZIPMONEY_PAYMENT_KEY = 'payment/zipmoneypayment/private_key';
	/**
	 * Config Path API Public Key
   * @const 
   */
	const PAYMENT_ZIPMONEY_PAYMENT_PUBLIC_KEY = 'payment/zipmoneypayment/public_key';
	/**
	 * Config Path Payment Method Title
   * @const 
   */
	const PAYMENT_ZIPMONEY_PAYMENT_TITLE = 'payment/zipmoneypayment/title';
	/**
	 * Config Path Payment Action 
   * @const 
   */
	const PAYMENT_ZIPMONEY_PAYMENT_PAYMENT_MODE = 'payment/zipmoneypayment/payment_mode';
	/**
	 * Config Path Product Classification 
   * @const 
   */
//	const PAYMENT_ZIPMONEY_PAYMENT_PRODUCT = 'payment/zipmoneypayment/product';
	/**
	 * Config Path In-Context Checkout 
   * @const 
   */
	const PAYMENT_ZIPMONEY_INCONTEXT_CHECKOUT = 'payment/zipmoneypayment/incontext_checkout';
	/**
	 * Config Path Minimum Order Threshold
   * @const 
   */
	const PAYMENT_ZIPMONEY_MINIMUM_TOTAL = 'payment/zipmoneypayment/minimum_total';
	/**
	 * Config Path Detail Message
   * @const 
   */
	const PAYMENT_ZIPMONEY_DETAIL_MESSAGE = 'payment/zipmoneypayment/detail_message';
	/**
	 * Config Path Display Payment Method Title
   * @const 
   */
	const PAYMENT_ZIPMONEY_DISPLAY_TITLE = 'payment/zipmoneypayment/display_title';
    /**
     * config Path Display Payment Order Status
     */
    const PAYMENT_ZIPMONEY_ORDER_SETTING = 'payment/zipmoneypayment/validation_order_state';
	/**
	 * Config Path Payment Method Logo
   * @const 
   */
	const PAYMENT_METHOD_LOGO_ZIP = "https://d3k1w8lx8mqizo.cloudfront.net/logo/25px/";
	/**
	 * Error Codes Map for Charge Error Codes
   * @var array
   */
	protected $_error_codes_map = array("account_insufficient_funds" => "MG1-0001",
																 "account_inoperative" => "MG1-0002",
																 "account_locked" => "MG1-0003",
																 "amount_invalid" => "MG1-0004",
																 "fraud_check" => "MG1-0005");
	/**
	 * Merchant Public Key
   * @var string
   */
	protected $_merchantPublicKey = null;
	/**
	 * Merchant Private Key
   * @var string
   */
	protected $_merchantPrivateKey = null;
	/**
	 * Api Environment
   * @var string
   */
	protected $_merchantEnv = null;

    /**
     * Order Status
     * @var array
     */
	protected $_orderStatus = null;
	/**
	 * Retrieves the config value by scope
	 *
	 * @param $path
	 * @return mixed|null
	 */
	public function getConfigByCurrentScope($path)
	{
		if (!$path) {
			return null;
		}

		$value = null;
		$storeId = Mage::getSingleton('zipmoneypayment/storeScope')->getStoreId();
		if ($storeId === null) {
			$scopeArr = Mage::getSingleton('zipmoneypayment/storeScope')->getCurrentScope();
			if (is_array($scopeArr)) {
				$scope = isset($scopeArr['scope']) ? $scopeArr['scope'] : 'default';
				$scopeId = isset($scopeArr['scope_id']) ? $scopeArr['scope_id'] : 0;
				if ($scope == 'default') {     // default scope
					$value = Mage::getStoreConfig($scope);
				} else {                        // websites scope
					$value = Mage::app()->getWebsite($scopeId)->getConfig($path);
				}
			}
		} else {
			// stores scope
			$value = Mage::getStoreConfig($path, $storeId);
		}
		return $value;
	}

	/**
	 * Returns the  merchant private key
	 *
	 * @return string
	 */
	public function getMerchantPrivateKey()
	{
		if(!$this->_merchantPrivateKey) {
			$this->_merchantPrivateKey = trim(Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_PAYMENT_KEY));
		}
		return $this->_merchantPrivateKey;
	}
    /**
     *
     */
    public function getOrderState()
    {
        if(!$this->_orderStatus) {
            $this->_orderStatus = trim(Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_ORDER_SETTING));
        }
        return $this->_orderStatus;
    }
	/**
	 * Returns the  merchant public key
	 *
	 * @return string
	 */
	public function getMerchantPublicKey()
	{
		if(!$this->_merchantPublicKey) {
			$this->_merchantPublicKey = trim(Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_PAYMENT_PUBLIC_KEY));
		}
		return $this->_merchantPublicKey;
	}

	/**
	 * Returns the  merchant public key
	 *
	 * @return string
	 */
	public function getEnvironment()
	{		
		if(!$this->_merchantEnv) {
			$this->_merchantEnv = trim(Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_PAYMENT_ENVIRONMENT));
		}
		return $this->_merchantEnv;
	}

	/**
	 * Checks if charge is set to true
	 *
	 * @return bool
	 */
	public function isCharge()
	{		
		return trim(Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_PAYMENT_PAYMENT_MODE)) === "capture";
	}

	/**
	 * Returns the product classification(zipMoney|zipPay)
	 *
	 * @return string
	 */
	public function getProduct()
	{		
		return "zippay";
	}

	/**
	 * Check if in-context checkout is active
	 *
	 * @return bool
	 */
	public function isInContextCheckout()
	{		
		return (bool)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_INCONTEXT_CHECKOUT);
	}

	/**
	 * Returns the minimum order total
	 *
	 * @return float
	 */
	public function getOrderTotalMinimum()
	{		
		return (float)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_MINIMUM_TOTAL);
	}

	/**
	 * Returns the detail message
	 *
	 * @return string
	 */
	public function getDetailMessage()
	{		
		return (string)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_DETAIL_MESSAGE);
	}

	/**
	 * Returns the method logo
	 *
	 * @return string
	 */
	public function getMethodLogo()
	{		
		return  self::PAYMENT_METHOD_LOGO_ZIP.strtolower($this->getProduct()).".png";
	}

	/**
	 * Returns the Display Method Title setting
	 *
	 * @return boolean
	 */
	public function getDisplayMethodTitle()
	{
		return (bool)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_DISPLAY_TITLE);
	}

	/**
	 * Returns the method code
	 *
	 * @return boolean
	 */
	public function getMethodCode()
	{
		return self::METHOD_CODE;
	}

	/**
	 * Returns the mapped error code
	 *
	 * @return boolean
	 */
	public function getMappedErrorCode($errorCode)
	{
		if(!in_array($errorCode, array_keys($this->_error_codes_map))){
			return false;
		}
		return $this->_error_codes_map[$errorCode];
	}
}