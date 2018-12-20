<?php

use Zip\Configuration;

class Zip_Payment_Model_Config
{
    const METHOD_CODE = 'zip_payment';

    const CONFIG_CUSTOM_NODE_NAME = 'custom';
    const CONFIG_LOGO_PATH = 'payment/zip_payment/logo';
    const CONFIG_TITLE_PATH = 'payment/zip_payment/title';

    /**
     * country and currency
     */
    const CONFIG_MERCHANT_COUNTRY_PATH = 'payment/account/merchant_country';
    const CONFIG_ALLOW_SPECIFIC_COUNTRIES_PATH = 'payment/zip_payment/allow_specific_countries';
    const CONFIG_SPECIFIC_COUNTRIES_PATH = 'payment/zip_payment/specific_countries';
    const CONFIG_ALLOWED_CURRENCIES_PATH = 'payment/zip_payment/allowed_currencies';
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
    const CONFIG_LANDING_PAGE_ENABLED_PATH = 'payment/zip_payment/pages/landing_page';
    const LANDING_PAGE_URL_ROUTE = 'zip_payment';

    /**
     * checkout
     */
    const CHECKOUT_REDIRECT_URL_ROUTE = 'zip_payment/checkout/redirect';
    const CHECKOUT_RESPONSE_URL_ROUTE = 'zip_payment/checkout/response';
    const CHECKOUT_FAILURE_URL_ROUTE = 'zip_payment/checkout/failure';
    const CHECKOUT_SESSION_ID = 'zip_payment_checkout_id';

    const CONFIG_CHECKOUT_GENERAL_ERROR_PATH = 'payment/zip_payment/checkout/error/general';
    const CONFIG_CHECKOUT_ERROR_CONTACT_PATH = 'payment/zip_payment/checkout/error/contact';
    const CONFIG_CHECKOUT_ERROR_PATH_PREFIX = 'payment/zip_payment/checkout/error/';

    /**
     * Charge
     */
    const PAYMENT_RECEIPT_NUMBER_KEY = 'receipt_number';

    /**
     * Current store id
     *
     * @var int
     */
    protected $storeId = null;
    protected $methodCode = self::METHOD_CODE;

    protected $debugEnabled = null;
    protected $logEnabled = null;
    protected $logLevel = null;
    protected $logFile = null;

    protected $apiConfig = null;

    /**
     * Set store id, if specified
     */
    public function __construct($storeId = null)
    {
        if ($storeId == null) {
            $storeId = Mage::app()->getStore()->getId();;
        }

        $this->setStoreId($storeId);
    }

    /**
     * Store ID setter
     *
     * @param int $storeId
     * @return Zip_Payment_Model_Config
     */
    public function setStoreId($storeId)
    {
        $this->storeId = (int)$storeId;
        return $this;
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

        if (!$countryCode) {
            $countryCode = Mage::helper('core')->getDefaultCountry($this->storeId);
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

    
    public function getApiConfiguration($storeId = null) {

        if($this->apiConfig === null || ($storeId ? $this->storeId !== $storeId : true)) {

            $this->setStoreId($storeId);

            Mage::helper('zip_payment')->autoLoad();

            $apiConfig = Configuration::getDefaultConfiguration();
            $magentoVersion = Mage::getVersion();
            $extensionVersion = Mage::helper('zip_payment')->getCurrentVersion();
            
            $apiConfig
            ->setApiKey('Authorization', Mage::helper('core')->decrypt($this->getValue(self::CONFIG_PRIVATE_KEY_PATH)))
            ->setEnvironment($this->getValue(self::CONFIG_ENVIRONMENT_PATH))
            ->setApiKeyPrefix('Authorization', 'Bearer')
            ->setPlatform("Magento/{$magentoVersion} Zip_Payment/{$extensionVersio}")
            ->setCurlTimeout((int)$this->getValue(self::CONFIG_API_TIMEOUT_PATH));

            if($this->isDebugEnabled() && $this->isLogEnabled() && $this->getLogLevel() >= Zend_Log::DEBUG) {

                $apiConfig
                ->setDebug($this->getValue(self::CONFIG_API_TIMEOUT_PATH))
                ->setDebugFile(Mage::getBaseDir('log') . DS .$this->getLogFile());
            }
            
            $apiConfig->setDefaultHeaders();
            $this->apiConfig = $apiConfig;
        }

        return $this->apiConfig;
    }

    

}