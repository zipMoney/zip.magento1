<?php


class Zip_Payment_Block_Checkout extends Mage_Core_Block_Template
{

    const CONFIG_DISPLAY_MODE_PATH = 'payment/zip_payment/display_mode';
    const CONFIG_CHECKOUT_LOADER_IMAGE_PATH = 'payment/zip_payment/checkout/loader_image';

    /**
     * @var Zip_Payment_Model_Config
     */
    protected $config;

    /**
     * Config instance getter
     * @return Zip_Payment_Model_Config
     */
    public function getConfig()
    {
        if ($this->config == null) {
            $this->config = Mage::getSingleton('zip_payment/config');
        }
        return $this->config;
    }

    /**
     * Retrieve model helper
     *
     * @return Zip_Payment_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('zip_payment');
    }

    public function isActive() {

        return $this->getHelper()->isActive();
    }

    public function getMethodCode() {
        return $this->getConfig()->getMethodCode();
    }

    public function getLoaderImageUrl() {
        return $this->getConfig()->getValue(self::CONFIG_CHECKOUT_LOADER_IMAGE_PATH);
    }

    /**
     * Returns the create url.
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getHelper()->getUrl(Zip_Payment_Model_Config::CHECKOUT_REDIRECT_URL_ROUTE);
    }

     /**
     * Returns the checkout url.
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getHelper()->getUrl(Zip_Payment_Model_Config::CHECKOUT_REDIRECT_URL_ROUTE);
    }

    /**
     * Returns the response url.
     *
     * @return string
     */
    public function getResponseUrl()
    {
        return $this->getHelper()->getUrl(Zip_Payment_Model_Config::CHECKOUT_RESPONSE_URL_ROUTE) . '?' . Zip_Payment_Controller_Checkout::URL_PARAM_RESULT . '=';
    }

    /**
     * Whether to use redirect or not.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return $this->getConfig()->getValue(self::CONFIG_DISPLAY_MODE_PATH) == Zip_Payment_Model_Adminhtml_System_Config_Source_DisplayMode::DISPLAY_MODE_REDIRECT;
    }

    public function getLogLevel() {

        if($this->getConfig()->isDebugEnabled() && $this->getConfig()->isLogEnabled()) {
            
            $logLevel = $this->getConfig()->getLogLevel();

            if($logLevel > Zend_Log::ERR) {
                return 'Information';
            }
            else if($logLevel > Zend_Log::DEBUG) {
                return 'Error';
            }
            else {
                return 'Debug';
            }
        }

        return '';
    }

    public function getCheckoutJsLibUrl() {
        return $this->getHelper()->getCheckoutJsLibUrl();
    }

    public function getCheckoutJs() {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . '/zip/payment/checkout.js';
    }

    public function getOnePageCheckoutJs() {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . '/zip/payment/opcheckout.js';
    }
    
    
}