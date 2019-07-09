<?php

/**
 * Block model for checkout script
 *
 * @package Zip_Payment
 * @author  Zip Co - Plugin Team
 **/

class Zip_Payment_Block_Checkout_Script extends Mage_Core_Block_Template
{

    const CHECKOUT_JS_PATH = '/zip/payment/checkout.js';
    const ONEPAGE_CHECKOUT_JS_PATH = '/zip/payment/opcheckout.js';

    /**
     * is current payment active
     *
     * @return boolean
     */
    public function isActive()
    {
        return Mage::helper('zip_payment')->isActive();
    }

    /**
     * get zip payment's method code
     *
     * @return string
     */
    public function getMethodCode()
    {
        return Mage::helper('zip_payment')
            ->getConfig()
            ->getMethodCode();
    }

    /**
     * Returns the checkout url.
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return Mage::helper('zip_payment')
        ->getUrl(Zip_Payment_Model_Config::CHECKOUT_START_URL_ROUTE);
    }

    /**
     * Returns the response url.
     *
     * @return string
     */
    public function getResponseUrl()
    {
        return Mage::helper('zip_payment')
            ->getUrl(Zip_Payment_Model_Config::CHECKOUT_RESPONSE_URL_ROUTE) .
            '?' . Zip_Payment_Model_Config::URL_PARAM_RESULT . '=';
    }

    /**
     * get url of checkout js library
     */
    public function getCheckoutJsLibUrl()
    {
        return Mage::helper('zip_payment')
            ->getConfig()
            ->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_JS_LIB_PATH);
    }

    /**
     * get a list of script urls for supporting specific kind of checkout
     */
    public function getCheckoutScriptList()
    {
        $scriptBaseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS);
        $baseScript = $scriptBaseUrl . self::CHECKOUT_JS_PATH;
        $scriptList = array($baseScript);

        if (Mage::helper('zip_payment')->isOnePageCheckout()) {
            array_push($scriptList, $scriptBaseUrl . self::ONEPAGE_CHECKOUT_JS_PATH);
        } else {
            $customScript = Mage::helper('zip_payment')
                ->getConfig()
                ->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_CUSTOM_SCRIPT_PATH);
            if ($customScript) {
                array_push($scriptList, $customScript);
            }
        }

        return $scriptList;
    }


    /**
     * Whether to use redirect or not.
     *
     * @return bool
     */
    public function isRedirect()
    {
        return Mage::helper('zip_payment')->isRedirectCheckoutDisplayModel();
    }

    /**
     * get log level for checkout JS
     */
    public function getLogLevel()
    {
        $config = Mage::helper('zip_payment')->getConfig();

        if ($config->isDebugEnabled() && $config->isLogEnabled()) {
            $logLevel = $config->getLogLevel();

            if ($logLevel > Zend_Log::ERR) {
                return 'Information';
            } else if ($logLevel > Zend_Log::DEBUG) {
                return 'Error';
            } else {
                return 'Debug';
            }
        }

        return '';
    }

}
