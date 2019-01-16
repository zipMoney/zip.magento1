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
     * process checkout's response result 
     * @param string $checkoutId Checkout ID From Url or other external place which need to validate
     * @param object $result response result
     */
    protected function processResponseResult($result, $checkoutId = null) {

        $response = array(
            'success' => false,
            'error_message' => null,
            'redirect_url' => null
        );

        // save result into checkout session
        $this->getHelper()->saveCheckoutSessionData(array(
            Zip_Payment_Model_Api_Checkout::CHECKOUT_RESULT_KEY => $result
        ));

        switch($result) {
            case Zip_Payment_Model_Api_Checkout::RESULT_APPROVED: 
                $response['success'] = $this->handleApprovedCheckout();
                $response['redirect_url'] = Mage::getUrl(Zip_Payment_Model_Config::CHECKOUT_SUCCESS_URL_ROUTE, array('_secure' => true));
            break;
            case Zip_Payment_Model_Api_Checkout::RESULT_REFERRED:
                $response['success'] = $this->handleReferredCheckout();
                $response['redirect_url'] = Mage::getUrl(Zip_Payment_Model_Config::CHECKOUT_REFERRED_URL_ROUTE, array('_secure' => true));
            break;
            default:
                $response['error_message'] = $this->generateMessage($result);
                // Redirects to the cart or error page.
                $response['redirect_url'] = $this->getQuote()->getIsActive() ? Mage::getUrl(Zip_Payment_Model_Config::CHECKOUT_CART_URL_ROUTE, array('_secure' => true)) : Mage::getUrl(Zip_Payment_Model_Config::CHECKOUT_FAILURE_URL_ROUTE, array('_secure' => true));
            break;
        }

        // if it's an ajax call
        if($this->getRequest()->isAjax()) {
            $this->returnJsonResponse($response);
        }
        else {
            $this->_redirect($response['redirect_url']);
        }
    }

    /**
     * handle approved checkout
     */
    protected function handleApprovedCheckout() {

        try {

            $this->getLogger()->debug($this->getHelper()->__('Zip_Payment_CheckoutController - handle approved checkout'));

            // if a checkout id has been received
            if($checkoutId) {

                $apiConfig = $this->getConfig()->getApiConfiguration();
                // Retrieve Checkout using external checkout id
                $checkout = Mage::getModel('zip_payment/api_checkout', $apiConfig)
                ->retrieve($checkoutId);
                $orderId = $checkout->getCartReference();

                $quote = Mage::getSingleton('sales/quote')->load($orderId, 'reserved_order_id');

                if ($quote->getId()) {
                    // load quote into onepage session
                    $quote->setIsActive(true);
                }
            }

            $this->placeOrder();
            return true;
            
        } catch (Exception $e) {
            throw $e;
        }

        return false;
    }

    /**
     * handle referred checkout
     */
    protected function handleReferredCheckout() {

        try {

            $this->getLogger()->debug($this->getHelper()->__('Zip_Payment_CheckoutController - handle referred checkout'));

            $createOrder = $this->getConfig()->getFlag(Zip_Payment_Model_Config::CONFIG_CHECKOUT_REFERRED_ORDER_CREATION_PATH);

            if($createOrder) {
                $this->placeOrder();
            }

            return true;

        } catch (Exception $e) {
            throw $e;
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
     * place order
     */
    protected function placeOrder() {

        $onepage = $this->getHelper()->getOnepage();

        if($onepage) {
            $onepage->getQuote()->collectTotals();
            $onepage->saveOrder();
        }

        $this->getLogger()->debug('Order has been saved successfully');
    }

    /**
     * generate messageS for checkout result
     */
    protected function generateMessage($result) {

        $errorMessage = $this->getHelper()->__('Checkout has been ' . $result);
        $this->getHelper()->getCheckoutSession()->addError($errorMessage);
        $this->getLogger()->debug($errorMessage);

        switch($result) {
            case Zip_Payment_Model_Api_Checkout::RESULT_DECLINED:
                return $errorMessage;
            case Zip_Payment_Model_Api_Checkout::RESULT_CANCELLED:
                return null;
            default:
                return self::GENERAL_CHECKOUT_ERROR;
        }

    }

    /**
     * create breadcrumb for checkout pages
     */
    protected function createBreadCrumbs($key, $label) {

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

        if($breadcrumbs) {

            $breadcrumbs->addCrumb('home', array(
                'label' => $this->__('Home'),
                'title' => $this->__('Home'),
                'link'  => Mage::getBaseUrl()
            ));

            $isLandingPageEnabled = Mage::getSingleton('zip_payment/config')->getFlag(Zip_Payment_Model_Config::CONFIG_LANDING_PAGE_ENABLED_PATH);

            if($isLandingPageEnabled) {
                $breadcrumbs->addCrumb(Zip_Payment_Model_Config::LANDING_PAGE_URL_IDENTIFIER, array(
                    'label' => $this->__('About Zip Payment'),
                    'title' => $this->__('About Zip Payment'),
                    'link'  => $this->getHelper()->getUrl(Zip_Payment_Model_Config::LANDING_PAGE_URL_ROUTE)
                ));
            }

            $breadcrumbs->addCrumb($key, array(
                'label' => $this->__($label),
                'title' => $this->__($label)
            ));

        }
    }
}