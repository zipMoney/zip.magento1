<?php

class Zip_Payment_Controller_Checkout extends Mage_Core_Controller_Front_Action
{
    const URL_PARAM_RESULT = 'result';
    const URL_PARAM_CHECKOUT_ID = 'checkoutId';

    const SUCCESS_URL_ROUTE = 'checkout/onepage/success';
    const CART_URL_ROUTE = 'checkout/cart';

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
            $this->_redirect(Zip_Payment_Model_Config::CHECKOUT_FAILURE_URL_ROUTE);
        }
    }

    /**
     * Redirects to the success page
     */
    protected function redirectToSuccess()
    {
        $this->getHelper()->emptyShoppingCart();
        $this->_redirect(self::SUCCESS_URL_ROUTE, array('_secure' => true));
    }

    /**
     * Send Ajax redirect response
     *
     * @return Mage_Checkout_OnepageController
     */
    protected function ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->ajaxRedirectResponse();
            return true;
        }
        $action = strtolower($this->getRequest()->getActionName());
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))
        ) {
            $this->ajaxRedirectResponse();
            return true;
        }
        return false;
    }

    /**
     * Prepare JSON formatted data for response to client
     *
     * @param $response
     * @return Zend_Controller_Response_Abstract
     */
    protected function returnJsonResponse($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

}