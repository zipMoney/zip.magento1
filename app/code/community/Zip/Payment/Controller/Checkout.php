<?php

/**
 * Checkout controller model
 * 
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Controller_Checkout extends Mage_Core_Controller_Front_Action
{
    const URL_PARAM_RESULT = 'result';
    const URL_PARAM_CHECKOUT_ID = 'checkoutId';

    const SUCCESS_URL_ROUTE = 'checkout/onepage/success';
    const CART_URL_ROUTE = 'checkout/cart';

    const GENERAL_CHECKOUT_ERROR = 'Something wrong while processing checkout';

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

    /**
     * process checkout's response result 
     */
    protected function processResponseResult($result) {

        $response = array(
            'success' => false,
            'error_message' => null
        );

        // place order when result is approved
        if($result == Zip_Payment_Model_Api_Checkout::RESULT_APPROVED) {

            try {

                $this->saveOrder();
                $response['success'] = true;
                
            } catch (Exception $e) {
                throw $e;
            }

        } else {
            $response['error_message'] = $this->generateErrorMessage($result);
        }

        return $response;
    }

    /**
     * save order
     */
    protected function saveOrder() {

        $onepage = $this->getHelper()->getOnepage();

        if($onepage) {
            $onepage->getQuote()->collectTotals();
            $onepage->saveOrder();
        }
        else {

        }

        $this->getLogger()->debug('Order has been saved successfully');
    }

    /**
     * generate error message for checkout result
     */
    protected function generateErrorMessage($result) {

        $errorMessage = $this->getHelper()->__('Checkout has been ' . $result);
        $this->getHelper()->getCheckoutSession()->addError($errorMessage);

        $additionalErrorMessage = $this->getHelper()->__($this->getConfig()->getValue(Zip_Payment_Model_Config::CONFIG_CHECKOUT_ERROR_PATH_PREFIX . $result));
        if($additionalErrorMessage) {
            $this->getHelper()->getCheckoutSession()->addError($additionalErrorMessage);
        }

        $this->getLogger()->debug($errorMessage);

        switch($result) {
            case Zip_Payment_Model_Api_Checkout::RESULT_DECLINED:
            case Zip_Payment_Model_Api_Checkout::RESULT_REFERRED: 
                return $errorMessage;
            case Zip_Payment_Model_Api_Checkout::RESULT_CANCELLED:
                return null;
            default:
                return self::GENERAL_CHECKOUT_ERROR;
        }

    }

}