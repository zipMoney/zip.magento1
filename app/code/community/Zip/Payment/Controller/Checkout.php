<?php

class Zip_Payment_Controller_Checkout extends Mage_Core_Controller_Front_Action
{
    const URL_PARAM_RESULT = 'result';
    const URL_PARAM_CHECKOUT_ID = 'checkoutId';

    const SUCCESS_URL_ROUTE = 'checkout/onepage/success';
    const CART_URL_ROUTE = 'checkout/cart';
    const CHECKOUT_ERROR_URL_ROUTE = 'zip_payment/checkout/error';

    /**
     * @var Zip_Payment_Model_Logger
     */
    protected $logger = null;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

    /**
     * @var Zip_Payment_Model_config
     */
    protected $config = null;

    /**
     * Get logger object
     * @return Zip_Payment_Model_Logger
     */
    public function getLogger()
    {
        if ($this->logger == null) {
            $this->logger = Mage::getSingleton('zip_payment/logger');
        }
        return $this->logger;
    }

    /**
     * Retrieve model helper
     *
     * @return Zip_Payment_Helper_Data
     */
    protected function getHelper()
    {
        return Mage::helper('zip_payment');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->getHelper()->getCheckoutSession()->getQuote();
        }

        return $this->quote;
    }

    /**
     * Get config
     * @return Zip_Payment_Model_config
     */
    protected function getConfig() {
        if($this->config == null) {
            $this->config = Mage::getSingleton('zip_payment/config');
        }
        return $this->config;
    }



    /**
     * Redirects to the cart or error page.
     * @param $result
     */
    protected function redirectToCartOrError()
    {
        if ($this->getQuote()->getIsActive()) {
            $this->_redirect(self::CART_URL_ROUTE);
        } else {
            $this->_redirect(Zip_Payment_Model_Config::CHECKOUT_ERROR_URL_ROUTE);
        }
    }

    /**
     * Redirects to the success page
     */
    protected function redirectToSuccess()
    {
        $this->_redirect(self::SUCCESS_URL_ROUTE, array('_secure' => true));
    }

}