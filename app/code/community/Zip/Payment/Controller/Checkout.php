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
     * Redirects to the success page or referred page
     */
    protected function redirectToSuccess()
    {
        $this->getHelper()->emptyShoppingCart();

        if($result == Zip_Payment_Model_Api_Checkout::RESULT_REFERRED) {
            $this->_redirect(Zip_Payment_Model_Config::CHECKOUT_REFERRED_URL_ROUTE, array('_secure' => true));
        }
        else {
            $this->_redirect(self::SUCCESS_URL_ROUTE, array('_secure' => true));
        }

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
     * @param string $externalCheckoutId Checkout ID From Url or other external place
     * @param object $result response result
     */
    protected function processResponseResult($result, $externalCheckoutId = null) {

        $response = array(
            'success' => false,
            'error_message' => null
        );

        // place order when result is approved
        if($result == Zip_Payment_Model_Api_Checkout::RESULT_APPROVED) {

            try {

                if($externalCheckoutId) {

                    $apiConfig = $this->getConfig()->getApiConfiguration();
                    // Retrieve Checkout using external checkout id
                    $checkout = Mage::getModel('zip_payment/api_checkout', $apiConfig)
                    ->retrieve($externalCheckoutId);
                    $orderId = $checkout->getCartReference();

                    $quote = Mage::getSingleton('sales/quote')->load($orderId, 'reserved_order_id');

                    if ($quote->getId()) {
                        // load quote into onepage session
                        $quote->setIsActive(true);
                    }
                }

                $this->placeOrder($checkoutId);
                $response['success'] = true;
                
            } catch (Exception $e) {
                throw $e;
            }

        }
        else if($result == Zip_Payment_Model_Api_Checkout::RESULT_REFERRED) {
            $response['success'] = true;
        } 
        else {
            $response['error_message'] = $this->generateMessage($result);
        }

        return $response;
    }

    /**
     * place order
     * @param string $checkoutId Checkout ID
     */
    protected function placeOrder($checkoutId) {

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
                $breadcrumbs->addCrumb('zip_payment', array(
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