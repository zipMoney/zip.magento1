<?php
class Zipmoney_ZipmoneyPayment_Model_Config 
{
	const MODULE_VERSION = "1.0.0";
	const MODULE_PLATFORM = "Magento";
	const METHOD_CODE = "zipmoneypayment";

	const STATUS_MAGENTO_AUTHORIZED = "zip_authorised";
	
	const PAYMENT_ZIPMONEY_PAYMENT_ACTIVE	= 'payment/zipmoneypayment/active';
	const PAYMENT_ZIPMONEY_PAYMENT_ENVIRONMENT = 'payment/zipmoneypayment/environment';
	const PAYMENT_ZIPMONEY_PAYMENT_KEY = 'payment/zipmoneypayment/private_key';
	const PAYMENT_ZIPMONEY_PAYMENT_PUBLIC_KEY = 'payment/zipmoneypayment/public_key';
	const PAYMENT_ZIPMONEY_PAYMENT_TITLE = 'payment/zipmoneypayment/title';
	const PAYMENT_ZIPMONEY_PAYMENT_PAYMENT_ACTION = 'payment/zipmoneypayment/payment_action';
	const PAYMENT_ZIPMONEY_PAYMENT_PRODUCT = 'payment/zipmoneypayment/product';
	const PAYMENT_ZIPMONEY_INCONTEXT_CHECKOUT = 'payment/zipmoneypayment/incontext_checkout';
	const PAYMENT_ZIPMONEY_MINIMUM_TOTAL = 'payment/zipmoneypayment/minimum_total';
	const PAYMENT_ZIPMONEY_MAXIMUM_TOTAL = 'payment/zipmoneypayment/maximum_total';
	const PAYMENT_ZIPMONEY_ORDER_TOTAL_OUTSIDE_THRESHOLD_NOTICE = 'payment/zipmoneypayment/order_total_outside_threshold_notice';
	const PAYMENT_ZIPMONEY_ORDER_TOTAL_OUTSIDE_THRESHOLD_ACTION = 'payment/zipmoneypayment/order_total_outside_threshold_action';
	const PAYMENT_ZIPMONEY_DISPLAY_DETAIL_MESSAGE = 'payment/zipmoneypayment/display_detail_message';
	const PAYMENT_ZIPMONEY_DETAIL_MESSAGE = 'payment/zipmoneypayment/detail_message';
	const PAYMENT_ZIPMONEY_DISPLAY_TITLE = 'payment/zipmoneypayment/display_title';
	
	const PAYMENT_WIDGET_CONFIGURATION_PRODUCT_ACTIVE = 'payment/zipmoney_widgets_configuration/productactive';
	const PAYMENT_WIDGET_CONFIGURATION_CART_ACTIVE = 'payment/zipmoney_widgets_configuration/cartactive';
	const PAYMENT_WIDGET_CONFIGURATION_REP_CALC_ACTIVE_PRODUCT = 'payment/zipmoney_widgets_configuration/rep_calculator_active_product';
	const PAYMENT_WIDGET_CONFIGURATION_REP_CALC_ACTIVE_CART = 'payment/zipmoney_widgets_configuration/rep_calculator_active_cart';

	const PAYMENT_MARKETING_BANNERS_PREFIX = 'payment/zipmoney_';
	const PAYMENT_MARKETING_BANNERS_ACTIVE = 'payment/zipmoney_marketing/banner_active';

	const IFRAME_API_URL_LIVE = 'https://account.zipmoney.com.au/scripts/iframe/zipmoney-checkout.js';
	const IFRAME_API_URL_TEST = 'https://account.sandbox.zipmoney.com.au/scripts/iframe/zipmoney-checkout.js';
	const IFRAME_API_URL_DEVELOPMENT = 'http://account.dev1.zipmoney.com.au/scripts/iframe/zipmoney-checkout.js';

	const PAYMENT_METHOD_LOGO_ZIP = "http://d3k1w8lx8mqizo.cloudfront.net/logo/25px/";

	protected $_error_codes_map = array("account_insufficient_funds" => "MG1-0001",
																 "account_inoperative" => "MG1-0002",
																 "account_locked" => "MG1-0003",
																 "amount_invalid" => "MG1-0004",
																 "fraud_check" => "MG1-0005");

	protected $_merchantPublicKey = null;
	protected $_merchantPrivateKey = null;
	protected $_merchantEnv = null;

	/**
	 * @param $vPath
	 * @return mixed|null
	 * @throws Mage_Core_Exception
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

	
	public function getMerchantPrivateKey($forceUpdate = false)
	{
		$path = self::PAYMENT_ZIPMONEY_PAYMENT_KEY;
		
		if($forceUpdate || $this->_merchantPrivateKey === null) {
			$this->_merchantPrivateKey = trim(Mage::getStoreConfig($path));
		}

		return $this->_merchantPrivateKey;
	}
	
	public function getMerchantPublicKey($forceUpdate = false)
	{
		$path = self::PAYMENT_ZIPMONEY_PAYMENT_PUBLIC_KEY;

		if($forceUpdate) {
			$this->_merchantPublicKey = trim(Mage::getStoreConfig($path));
		} else {
			if($this->_merchantPublicKey === null) {
				$this->_merchantPublicKey = trim(Mage::getStoreConfig($path));
			}
		}
		
		return $this->_merchantPublicKey;
	}

	public function getEnvironment($forceUpdate = false)
	{
		$path = self::PAYMENT_ZIPMONEY_PAYMENT_ENVIRONMENT;
		
		if($forceUpdate) {
			$this->_merchantEnv = trim(Mage::getStoreConfig($path));
		} else {
			if($this->_merchantEnv === null) {
				$this->_merchantEnv = trim(Mage::getStoreConfig($path));
			}
		}
		return $this->_merchantEnv;
	}

	/**
	 * Get store charge settings
	 * @return bool
	 */
	public function isCharge()
	{		
		return trim(Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_PAYMENT_PAYMENT_ACTION)) === "capture";
	}

	/**
	 * Get store charge settings
	 * @return bool
	 */
	public function getProduct()
	{		
		$product = Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_PAYMENT_PRODUCT);
		return $product ? $product : "zipmoney";
	}

	/**
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function isInContextCheckout()
	{		
		return (bool)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_INCONTEXT_CHECKOUT);
	}

	/**
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function getOrderTotalMinimum()
	{		
		return (float)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_MINIMUM_TOTAL);
	}

	/**
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function getOrderTotalMaximum()
	{		
		return (float)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_MAXIMUM_TOTAL);
	}

	/**
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function getOrderTotalOutsideThresholdAction()
	{		
		return Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_ORDER_TOTAL_OUTSIDE_THRESHOLD_ACTION);
	}

	/**
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function getOrderTotalOutsideThresholdNotice()
	{		
		return (string)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_ORDER_TOTAL_OUTSIDE_THRESHOLD_NOTICE);
	}

	/**
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function getDisplayDetailMessage()
	{		
		return (bool)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_DISPLAY_DETAIL_MESSAGE);
	}
	
	/**
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function getDetailMessage()
	{		
		return (string)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_DETAIL_MESSAGE);
	}

	/**
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function getMethodLogo()
	{		
		return  self::PAYMENT_METHOD_LOGO_ZIP.strtolower($this->getProduct()).".png";
	}

	public function getDisplayMethodTitle()
	{
		return (bool)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_DISPLAY_TITLE);
	}

	public function getMethodCode()
	{
		return self::METHOD_CODE;
	}

	public function getMappedErrorCode($errorCode)
	{
		if(!in_array($errorCode, array_keys($this->_error_codes_map)))
		{
			return false;
		}

		return $this->_error_codes_map[$errorCode];
	}

}