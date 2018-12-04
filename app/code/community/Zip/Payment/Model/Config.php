<?php


class Zip_Payment_Model_Config
{
    const METHOD_CODE = 'zip_payment';

    const CONFIG_MERCHANT_COUNTRY_PATH = 'payment/account/merchant_country';
    const CONFIG_ALLOW_SPECIFIC_COUNTRIES_PATH = 'payment/zip_payment/allow_specific_countries';
    const CONFIG_SPECIFIC_COUNTRIES_PATH = 'payment/zip_payment/specific_countries';
    const CONFIG_ALLOWED_CURRENCIES_PATH = 'payment/zip_payment/allowed_currencies';
    const CONFIG_DEBUG_ENABLED_PATH = 'payment/zip_payment/debug/enabled';

    /**
     * Current store id
     *
     * @var int
     */
    protected $storeId = null;
    protected $methodCode = self::METHOD_CODE;
    protected $debug = null;

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

    public function isDebugEnabled() {

        if($this->debug === null) {
            $this->debug = $this->getFlag(self::CONFIG_DEBUG_ENABLED_PATH);
        }
        return $this->debug;
    }

    public function getValue($path) {
        return Mage::getStoreConfig($path, $this->storeId);
    }

    public function getFlag($path) {
        return Mage::getStoreConfigFlag($path, $this->storeId);
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

    /**
     * Check whether method supported for specified country or not
     * Use $_methodCode and merchant country by default
     *
     * @return bool
     */
    protected function isMerchantCountrySupported()
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

}