<?php

/**
 * Block model for checkout
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Block_Checkout extends Mage_Core_Block_Template
{

    const CONFIG_DISPLAY_MODE_PATH = 'payment/zip_payment/display_mode';
    const CONFIG_CHECKOUT_LOADER_IMAGE_PATH = 'payment/zip_payment/checkout/loader_image';
    const CHECKOUT_JS_PATH = '/zip/payment/checkout.js';
    const ONE_PAGE_CHECKOUT_JS_PATH = '/zip/payment/opcheckout.js';

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

    /**
     * is current payment active
     * 
     * @return boolean
     */
    public function isActive() {

        return $this->getHelper()->isActive();
    }

    /**
     * get zip payment's method code
     * 
     * @return string
     */
    public function getMethodCode() {
        return $this->getConfig()->getMethodCode();
    }

    /**
     * get loader images
     * 
     * @return string
     */
    public function getLoaderImageUrl() {
        return $this->getConfig()->getValue(self::CONFIG_CHECKOUT_LOADER_IMAGE_PATH);
    }


     /**
     * Returns the checkout url.
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getHelper()->getUrl(Zip_Payment_Model_Config::CHECKOUT_START_URL_ROUTE);
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
     * get url of checkout js library
     */
    public function getCheckoutJsLibUrl() {
        return $this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_JS_LIB_PATH);
    }

    /**
     * get url of checkout js
     */
    public function getCheckoutJs() {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . self::CHECKOUT_JS_PATH;
    }

    /**
     * get url of one page checkout js
     */
    public function getOnePageCheckoutJs() {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . self::ONE_PAGE_CHECKOUT_JS_PATH;
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

    /**
     * get log level for checkout JS
     */
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

    
}