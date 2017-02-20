<?php
class Zipmoney_ZipmoneyPayment_Model_Config 
{
	const MODULE_VERSION = "2.1.3";
	const MODULE_PLATFORM = "Magento";
	const STATUS_MAGENTO_NEW                = "zip_pending";
	const STATUS_MAGENTO_AUTHORIZED         = "zip_authorised";
	const STATUS_MAGENTO_PROCESSING         = "zip_captured";
	const STATUS_MAGENTO_CANCELLED          = "zip_cancelled";
	const STATUS_MAGENTO_REFUND             = "zip_refund";
	const STATUS_MAGENTO_AUTHORIZED_REVIEW  = "zip_authorise_under_review";
	const STATUS_MAGENTO_ORDER_CANCELLED    = "zip_order_cancelled";
	const STATUS_MAGENTO_ORDER_DECLINED     = "zip_declined";
	const STATUS_MAGENTO_CAPTURE_PENDING    = "zip_capture_pending";


	const PAYMENT_ZIPMONEY_PAYMENT_ACTIVE                               = 'payment/zipmoneypayment/active';
	const PAYMENT_ZIPMONEY_PAYMENT_ENVIRONMENT                          = 'payment/zipmoneypayment/environment';
	const PAYMENT_ZIPMONEY_PAYMENT_ID                                   = 'payment/zipmoneypayment/id';
	const PAYMENT_ZIPMONEY_PAYMENT_KEY                                  = 'payment/zipmoneypayment/key';
	const PAYMENT_ZIPMONEY_PAYMENT_PUBLIC_KEY                           = 'payment/zipmoneypayment/public_key';
	const PAYMENT_ZIPMONEY_PAYMENT_HASH                                 = 'payment/zipmoneypayment/hash';
	const PAYMENT_ZIPMONEY_PAYMENT_UPDATE_FLAG                          = 'payment/zipmoneypayment/update_flag';
	const PAYMENT_ZIPMONEY_PAYMENT_ASSETS                               = 'payment/zipmoneypayment/assets';
	const PAYMENT_ZIPMONEY_PAYMENT_ASSET_VALUES                         = 'payment/zipmoneypayment/asset_values';
	const PAYMENT_ZIPMONEY_PAYMENT_TITLE                                = 'payment/zipmoneypayment/title';
	const PAYMENT_ZIPMONEY_PAYMENT_CAPTURE_METHOD                       = 'payment/zipmoneypayment/capture_method';
	const PAYMENT_ZIPMONEY_PAYMENT_PAYMENT_ACTION                       = 'payment/zipmoneypayment/payment_action';
	const PAYMENT_ZIPMONEY_PAYMENT_PRODUCT                              = 'payment/zipmoneypayment/product';

	const PAYMENT_EXPRESS_CHECKOUT_EXPRESS_CHECKOUT_ACTIVE              = 'payment/zipmoney_express_checkout/express_checkout_active';
	const PAYMENT_EXPRESS_CHECKOUT_IFRAME_CHECKOUT_ACTIVE               = 'payment/zipmoney_express_checkout/iframe_checkout_active';
	const PAYMENT_EXPRESS_CHECKOUT_CART_EXPRESS_BUTTON_ACTIVE           = 'payment/zipmoney_express_checkout/cart_express_button_active';
	const PAYMENT_EXPRESS_CHECKOUT_PRODUCT_EXPRESS_BUTTON_ACTIVE        = 'payment/zipmoney_express_checkout/product_express_button_active';

	const PAYMENT_WIDGET_CONFIGURATION_PRODUCT_ACTIVE                   = 'payment/zipmoney_widgets_onfiguration/productactive';
	const PAYMENT_WIDGET_CONFIGURATION_CART_ACTIVE                      = 'payment/zipmoney_widgets_onfiguration/cartactive';
	const PAYMENT_WIDGET_CONFIGURATION_REP_CALC_ACTIVE_PRODUCT          = 'payment/zipmoney_widgets_onfiguration/rep_calculator_active_product';
	const PAYMENT_WIDGET_CONFIGURATION_REP_CALC_ACTIVE_CART             = 'payment/zipmoney_widgets_onfiguration/rep_calculator_active_cart';

	const PAYMENT_ZIPMONEY_CHECKOUT_TITLE                               = 'payment/zipmoney_checkout/title';
	const PAYMENT_ZIPMONEY_CHECKOUT_DETAIL_MESSAGE                      = 'payment/zipmoney_checkout/detailmessage';

	const PAYMENT_MARKETING_BANNERS_PREFIX                              = 'payment/zipmoney_';
	const PAYMENT_MARKETING_BANNERS_ACTIVE                              = 'payment/zipmoney_marketing_banners/banner_active';

	const PAYMENT_ZIPMONEY_HANDLING_SERVICE_UNAVAILABLE_HEADING         = 'payment/zipmoney_handling/service_unavailable_heading';
	const PAYMENT_ZIPMONEY_HANDLING_SERVICE_UNAVAILABLE_BODY            = 'payment/zipmoney_handling/service_unavailable_body';

	const MARKETING_BANNERS_TYPE_STRIP                                  = 'strip';
	const MARKETING_BANNERS_TYPE_SIDE                                   = 'side';

	const MARKETING_BANNERS_POSITION_TOP                                = 'top';
	const MARKETING_BANNERS_POSITION_BOTTOM                             = 'bottom';
	const MARKETING_BANNERS_POSITION_LEFT_TOP                           = 'left-top';
	const MARKETING_BANNERS_POSITION_LEFT_BOTTOM                        = 'left-bottom';
	const MARKETING_BANNERS_POSITION_RIGHT_TOP                          = 'right-top';
	const MARKETING_BANNERS_POSITION_RIGHT_BOTTOM                       = 'right-bottom';

	//TODO: to be modified and used in the future
	const MARKETING_BANNERS_STRIP_SIZE_1                                = '196*193';
	const MARKETING_BANNERS_STRIP_SIZE_2                                = '160*155';
	const MARKETING_BANNERS_SIDE_SIZE_1                                 = '800*50';
	const MARKETING_BANNERS_SIDE_SIZE_2                                 = '850*60';

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
	 * Get current store url
	 * @return bool
	 */
	public function isCharge()
	{		
		$payment_action = trim(Mage::getStoreConfig(self::PAYMENT_ZIPMONEY_PAYMENT_PAYMENT_ACTION));
		
		if($payment_action == 'capture')
			return true;
		else
			return false;
	}

}
