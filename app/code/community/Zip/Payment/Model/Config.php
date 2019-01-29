<?php

/**
 * Model for configuration                                                                                 
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Model_Config
{
    const METHOD_CODE = 'zip_payment';
    const LEGACY_METHOD_CODE = 'zipmoneypayment';

    /**
     * basic configuration paths
     */
    const CONFIG_ACTIVE_PATH = 'payment/zip_payment/active';
    const CONFIG_CUSTOM_NODE_NAME = 'custom';
    const CONFIG_LOGO_PATH = 'payment/zip_payment/logo';
    const CONFIG_TITLE_PATH = 'payment/zip_payment/title';

    /**
     * country and currency
     */
    const CONFIG_MERCHANT_COUNTRY_PATH = 'payment/account/merchant_country';
    const CONFIG_ALLOW_SPECIFIC_COUNTRIES_PATH = 'payment/zip_payment/country_currency/allow_specific_countries';
    const CONFIG_SPECIFIC_COUNTRIES_PATH = 'payment/zip_payment/country_currency/specific_countries';
    const CONFIG_ALLOWED_CURRENCIES_PATH = 'payment/zip_payment/country_currency/allowed_currencies';
    const CONFIG_SUPPORTED_COUNTRIES_PATH = 'payment/zip_payment/country_currency/supported_countries';
    const CONFIG_SUPPORTED_CURRENCIES_PATH = 'payment/zip_payment/country_currency/supported_currencies';

    /**
     * debug config
     */
    const CONFIG_DEVELOPER_LOG_ACTIVE_PATH = 'dev/log/active';
    const CONFIG_DEBUG_ENABLED_PATH = 'payment/zip_payment/debug/enabled';
    const CONFIG_DEBUG_LOG_LEVEL_PATH = 'payment/zip_payment/debug/log_level';
    const CONFIG_DEBUG_LOG_FILE_PATH = 'payment/zip_payment/debug/log_file';
    const DEFAULT_LOG_FILE_NAME = 'zip_payment.log';

    /**
     * api config
     */
    const CONFIG_ENVIRONMENT_PATH = 'payment/zip_payment/environment';
    const CONFIG_PRIVATE_KEY_PATH = 'payment/zip_payment/private_key';
    const CONFIG_PUBLIC_KEY_PATH = 'payment/zip_payment/public_key';
    const CONFIG_API_TIMEOUT_PATH = 'payment/zip_payment/api/timeout';

    /**
     * landing page
     */
    const CONFIG_LANDING_PAGE_ENABLED_PATH = 'payment/zip_payment/widgets/landing_page/enabled';
    const LANDING_PAGE_URL_IDENTIFIER = 'about_zip_payment';
    const LANDING_PAGE_URL_ROUTE = 'about_zip_payment';

    /**
     * checkout
     */
    const CHECKOUT_START_URL_ROUTE = 'zip_payment/checkout/start';
    const CHECKOUT_RESPONSE_URL_ROUTE = 'zip_payment/checkout/response';
    const CHECKOUT_FAILURE_URL_ROUTE = 'zip_payment/checkout/failure';
    const CHECKOUT_REFERRED_URL_ROUTE = 'zip_payment/checkout/referred';
    const CHECKOUT_SUCCESS_URL_ROUTE = 'checkout/onepage/success';
    const CHECKOUT_CART_URL_ROUTE = 'checkout/cart';

    const CHECKOUT_SESSION_KEY = 'zip_payment_checkout';
    const ONEPAGE_CHECKOUT_IDENTIFIER = 'checkout_onepage_index';

    const CONFIG_CHECKOUT_GENERAL_ERROR_PATH = 'payment/zip_payment/checkout/error/general';
    const CONFIG_CHECKOUT_JS_LIB_PATH = 'payment/zip_payment/checkout/js_lib';
    const CONFIG_CEHCKOUT_DISPLAY_MODE_PATH = 'payment/zip_payment/checkout/display_mode';
    const CONFIG_CHECKOUT_CUSTOM_SCRIPT_PATH = 'payment/zip_payment/checkout/custom_script';
    const CONFIG_CHECKOUT_ONESTEPCHECKOUTS_PATH = 'payment/zip_payment/checkout/onestepcheckouts';

    const CONFIG_CHECKOUT_REFERRED_ORDER_CREATION_PATH = 'payment/zip_payment/checkout/referred/order_creation';
    const CONFIG_CHECKOUT_REFERRED_ORDER_STATUS_PATH = 'payment/zip_payment/checkout/referred/order_status';

    /**
     * Response 
     */
    const URL_PARAM_RESULT = 'result';
    const URL_PARAM_CHECKOUT_ID = 'checkoutId';

    /**
     * Charge
     */
    const PAYMENT_RECEIPT_NUMBER_KEY = 'receipt_number';

    /**
     * Admin Notification
     */
    const CONFIG_NOTIFICATION_ENABLED_PATH = 'payment/zip_payment/admin_notification/enabled';

    protected $methodCode = self::METHOD_CODE;
    protected $debugEnabled = null;
    protected $logEnabled = null;
    protected $logLevel = null;
    protected $logFile = null;
    protected $storeId = null;

    protected $apiConfig = null;

    public function __construct($options)
    {
        if (isset($options['store_id']) && !empty($options['store_id'])) {
            $this->storeId = $options['store_id'];
        } else {
            $this->storeId = Mage::app()->getStore()->getId();
        }
    }

     /**
     * Method code setter
     *
     * @param string|Mage_Payment_Model_Method_Abstract $method
     * @return Mage_Paypal_Model_Config
     */
    public function setMethod($method)
    {
        if ($method instanceof Mage_Payment_Model_Method_Abstract) {
            $this->methodCode = $method->getCode();
        } elseif (is_string($method)) {
            $this->methodCode = $method;
        }
        return $this;
    }


    /**
     * Payment method instance code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->methodCode;
    }

    /*************************** BASIC **********************************/

    public function getLogo() {
        return $this->getValue(Zip_Payment_Model_Config::CONFIG_LOGO_PATH);
    }

    public function getTitle() {
        return $this->getValue(Zip_Payment_Model_Config::CONFIG_TITLE_PATH);
    }


    /*************************** DEBUG & LOG **********************************/

    public function isDebugEnabled() {

        if($this->debugEnabled === null) {
            $this->debugEnabled = $this->getFlag(self::CONFIG_DEBUG_ENABLED_PATH);
        }
        return $this->debugEnabled;
    }

    /**
     * Returns the log level
     *
     * @return int
     */
    public function getLogLevel()
    {
        if ($this->logLevel === null) {
            $this->logLevel = (int)$this->getValue(self::CONFIG_DEBUG_LOG_LEVEL_PATH);
        }
        
        return $this->logLevel;
    }

    /**
     * is log been enabled
     */
    public function isLogEnabled() {

        if($this->logEnabled === null) {

            $this->logEnabled = false;
            
            if($this->isDebugEnabled()) {

                $isDeveloperLogActive = $this->getFlag(self::CONFIG_DEVELOPER_LOG_ACTIVE_PATH);       
                $logLevel = $this->getLogLevel();

                $this->logEnabled = ($isDeveloperLogActive && $logLevel >= 0);

            }
        }

        return $this->logEnabled;
    }

    /**
     * Returns the log file
     *
     * @return string
     */
    public function getLogFile()
    {
        if ($this->logFile === null) {

            $logFile = $this->getValue(self::CONFIG_DEBUG_LOG_FILE_PATH);

            if (empty($logFile)) {
                $logFileName = self::DEFAULT_LOG_FILE_NAME;
            }

            $this->logFile = $logFile;
        }

        return $this->logFile;
    }

    /*************************** COUNTRY AND CURRENCY **********************************/

    /**
     * Check whether method supported for specified country or not
     * Use $_methodCode and merchant country by default
     *
     * @return bool
     */
    public function isMerchantCountrySupported()
    {
        if($this->getFlag(self::CONFIG_ALLOW_SPECIFIC_COUNTRIES_PATH)){

            $merchantCountryCode = $this->getMerchantCountry();
            $supportedCountries = explode(',', (string)$this->getValue(self::CONFIG_SPECIFIC_COUNTRIES_PATH));
            return in_array($merchantCountryCode, $supportedCountries);
        }
        return true;
    }

    /**
     * Return merchant country code, use default country if it not specified in General settings
     *
     * @return string
     */
    protected function getMerchantCountry()
    {
        $countryCode = $this->getValue(self::CONFIG_MERCHANT_COUNTRY_PATH);
        $storeId = Mage::app()->getStore()->getId();

        if (!$countryCode) {
            $countryCode = Mage::helper('core')->getDefaultCountry($storeId);
        }
        return $countryCode;
    }

    /**
     * Check whether specified currency code is supported
     *
     * @param string $currencyCode
     * @return bool
     */
    public function isCurrencySupported($currencyCode)
    {
        $supportedCurrencies = (string)$this->getValue(self::CONFIG_ALLOWED_CURRENCIES_PATH);
        return in_array($currencyCode, explode(',', $supportedCurrencies));
    }


    /*************************** GET VALUE **********************************/

    /**
     * Get configuration value
     * @param string $path
     * @return string
     */
    public function getValue($path) {

        $value = (string) Mage::getConfig()->getNode(self::CONFIG_CUSTOM_NODE_NAME . '/' . $path);

        if(empty($value)) {
            $value = Mage::getStoreConfig($path, $this->storeId);
        }

        return $value;
    }

    /**
     * Get configuration flag value
     * @param string $path
     * @return bool
     */
    public function getFlag($path) {

        $value = $this->getValue($path);
        return !empty($value) && 'false' !== $value;

    }

    /*************************** PAYMENT **********************************/

    /**
     * Check whether method active in configuration
     *
     * @param string $method Method code
     * @return bool
     */
    public function isMethodAvailable($methodCode = null)
    {
        if ($methodCode === null) {
            $methodCode = $this->getMethodCode();
        }
        
        return $this->getFlag("payment/{$methodCode}/active") && $this->isMerchantCountrySupported();
    }

}