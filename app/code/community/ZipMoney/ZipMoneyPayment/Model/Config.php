<?php
class Zipmoney_ZipmoneyPayment_Model_Config 
{
	const MODULE_VERSION = "1.0.0";
	const MODULE_PLATFORM = "Magento";

	const STATUS_MAGENTO_AUTHORIZED         = "zip_authorised";
	

	const PAYMENT_ZIPMONEY_PAYMENT_ACTIVE	= 'payment/zipmoneypayment/active';
	const PAYMENT_ZIPMONEY_PAYMENT_ENVIRONMENT = 'payment/zipmoneypayment/environment';
	const PAYMENT_ZIPMONEY_PAYMENT_KEY = 'payment/zipmoneypayment/private_key';
	const PAYMENT_ZIPMONEY_PAYMENT_PUBLIC_KEY = 'payment/zipmoneypayment/public_key';
	const PAYMENT_ZIPMONEY_PAYMENT_TITLE = 'payment/zipmoneypayment/title';
	const PAYMENT_ZIPMONEY_PAYMENT_PAYMENT_ACTION = 'payment/zipmoneypayment/payment_action';
	const PAYMENT_ZIPMONEY_PAYMENT_PRODUCT = 'payment/zipmoneypayment/product';
	const PAYMENT_ZIPMONEY_INCONTEXT_CHECKOUT = 'payment/zipmoneypayment/incontext_checkout';

	const PAYMENT_ZIPMONEY_CHECKOUT_TITLE                               = 'payment/zipmoney_checkout/title';
	const PAYMENT_ZIPMONEY_CHECKOUT_DETAIL_MESSAGE                      = 'payment/zipmoney_checkout/detailmessage';


	const PAYMENT_WIDGET_CONFIGURATION_PRODUCT_ACTIVE                   = 'payment/zipmoney_widgets_configuration/productactive';
	const PAYMENT_WIDGET_CONFIGURATION_CART_ACTIVE                      = 'payment/zipmoney_widgets_configuration/cartactive';
	const PAYMENT_WIDGET_CONFIGURATION_REP_CALC_ACTIVE_PRODUCT          = 'payment/zipmoney_widgets_configuration/rep_calculator_active_product';
	const PAYMENT_WIDGET_CONFIGURATION_REP_CALC_ACTIVE_CART             = 'payment/zipmoney_widgets_configuration/rep_calculator_active_cart';

	const PAYMENT_MARKETING_BANNERS_PREFIX                              = 'payment/zipmoney_';
	const PAYMENT_MARKETING_BANNERS_ACTIVE                              = 'payment/zipmoney_marketing_banners/banner_active';


	const IFRAME_API_URL_LIVE                                           = 'https://account.zipmoney.com.au/scripts/iframe/zipmoney-checkout.js';
	const IFRAME_API_URL_TEST                                           = 'https://account.sandbox.zipmoney.com.au/scripts/iframe/zipmoney-checkout.js';
	const IFRAME_API_URL_DEVELOPMENT                                    = 'http://account.dev1.zipmoney.com.au/scripts/iframe/zipmoney-checkout.js';

	const CHECKOUT_DEFAULT_ICON_NAME                                    = "zipmoney";    
	const CHECKOUT_ICON_PREFIX                                          = "-logo.png";

	protected $_merchantPublicKey = null;
	protected $_merchantPrivateKey = null;
	protected $_merchantEnv = null;
	/**
	 * @param $vPath
	 * @return mixed|null
	 * @throws Mage_Core_Exception
	 */
	public function getConfigByCurrentScope($vPath)
	{
			if (!$vPath) {
					return null;
			}
			$value = null;
			$iStoreId = Mage::getSingleton('zipmoneypayment/storeScope')->getStoreId();
			if ($iStoreId === null) {
					$aScope = Mage::getSingleton('zipmoneypayment/storeScope')->getCurrentScope();
					if (is_array($aScope)) {
							$vScope = isset($aScope['scope']) ? $aScope['scope'] : 'default';
							$iScopeId = isset($aScope['scope_id']) ? $aScope['scope_id'] : 0;

							if ($vScope == 'default') {     // default scope
									$value = Mage::getStoreConfig($vPath);
							} else {                        // websites scope
									$value = Mage::app()->getWebsite($iScopeId)->getConfig($vPath);
							}
					}
			} else {
					// stores scope
					$value = Mage::getStoreConfig($vPath, $iStoreId);
			}
			return $value;
	}

	/**
	 * @param $vPath
	 * @param $value
	 * @param null $iMerchantId
	 * @return $this
	 */
	public function saveConfigByMatchedScopes($vPath, $value, $iMerchantId = null)
	{
			if ($iMerchantId === null) {
					$iMerchantId = Mage::getSingleton('zipmoneypayment/storeScope')->getMerchantId();
			}
			$aMatched = Mage::getSingleton('zipmoneypayment/storeScope')->getMatchedScopes($iMerchantId);
			if (!is_array($aMatched)) {
					return $this;
			}

			/**
			 * save the config to each matched scope
			 */
			foreach ($aMatched as $aScope) {
					if (!is_array($aScope)) {
							continue;
					}
					$vScope = isset($aScope['scope']) ? $aScope['scope'] : 'default';
					$iScopeId = isset($aScope['scope_id']) ? $aScope['scope_id'] : 0;
					Mage::getModel('core/config')->saveConfig($vPath, $value, $vScope, $iScopeId);
			}

			return $this;
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
	 * Check if in-context checkout is active
	 * @return bool
	 */
	public function isInContextCheckout()
	{		
		return (bool)Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_INCONTEXT_CHECKOUT);
	}

}
