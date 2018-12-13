<?php

class Zip_Payment_Controller_Checkout extends Mage_Core_Controller_Front_Action
{
    const URL_PARAM_RESULT = 'result';
    const URL_PARAM_CHECKOUT_ID = 'checkoutId';

    /**
     * @var Zip_Payment_Model_Logger
     */
    protected $logger = null;

    /**
     * @var Mage_Sales_Model_Quote
     */
    protected $quote;

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
     * Redirects to the cart or error page.
     *
     */
    protected function redirectToCartOrError()
    {
        if ($this->getQuote()->getIsActive()) {
            $this->_redirect('checkout/cart');
        } else {
            $this->_redirect(Zip_Payment_Model_Config::CHECKOUT_ERROR_URL_ROUTE);
        }
    }

    protected function redirectToSuccess()
    {
        $this->_redirect('checkout/onepage/success', array('_secure' => true));
    }

}