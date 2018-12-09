<?php

class Zip_Payment_Controller_Checkout extends Mage_Core_Controller_Front_Action
{
    const URL_PARAM_RESULT = 'result';
    const URL_PARAM_CHECKOUT_ID = 'checkoutId';

    /**
     * Valid Application Results
     *
     * @var array
     */
    const VALID_CHECKOUT_RESULTS = array('approved', 'declined', 'cancelled', 'referred');

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
     * Get session namespace
     *
     * @return Zip_Payment_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('zip_payment/session');
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Checks if the result passed in the query string is valid
     *
     * @return boolean
     */
    protected function isResultValid()
    {
        if (empty($this->getRequest()->getParam('result'))
            || !in_array($this->getRequest()->getParam('result'), self::VALID_CHECKOUT_RESULTS)
        ) {
            $this->getLogger()->error($this->getHelper()->__("Invalid Result"));
            return false;
        }

        return true;
    }

    /**
     * Redirects to the cart or error page.
     *
     */
    protected function redirectToCartOrError()
    {
        if ($this->getQuote()->getIsActive()) {
            $this->_redirect("checkout/cart");
        } else {
            $this->_redirect(Zip_Payment_Model_Config::CHECKOUT_ERROR_URL_ROUTE);
        }
    }

     /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        if ($this->quote === null) {
            $this->quote = $this->getCheckoutSession()->getQuote();
        }

        return $this->quote;
    }

    /**************************************************************************** */



    /************************** CUSTOMER QUOTE ******************************** */

    /**
     * Sets quote for the customer.
     *
     * @throws Mage_Core_Exception
     */
    public function setCustomerQuote()
    {
        // Retrieve a valid quote
        if ($quote = $this->retrieveQuote()) {
            // Verify that the customer is a valid customer of the quote
            $this->verifyCustomerForQuote($quote);
            /* Set the session quote if required.
             Needs to be done after verifying the current customer */
            if ($this->getCheckoutSession()->getQuoteId() != $quote->getId()) {
                $this->getLogger()->debug($this->getHelper()->__("Setting quote to current session"));
                // Set the quote in the current object
                $this->setQuote($quote);
                // Set the quote in the session
                $this->getCheckoutSession()->setQuoteId($quote->getId());
            }

            // Make sure the qoute is active
            $this->getHelper()->activateQuote($quote);
        } else {
            Mage::throwException("Could not retrieve the quote");
        }
    }

    /**
     * Checks if the Session Quote is valid, if not use the db quote.
     *
     * @param boolean $forceRetrieveDbQuote
     * @return boolean
     */
    protected function retrieveQuote($forceRetrieveDbQuote = false)
    {
        $sessionQuote = $this->getCheckoutSession()->getQuote();
        $checkoutId  = $this->getRequest()->getParam(self::URL_PARAM_CHECKOUT_ID);
        $use_db_quote = false;

        // Return Session Quote
        if (!$sessionQuote) {
            $this->getLogger()->error($this->getHelper()->__("Session Quote does not exist."));
            $use_db_quote = true;
        } else if ($sessionQuote->getCheckoutId() != $checkoutId) {
            $this->getLogger()->error($this->getHelper()->__("Checkout Id does not match with the session quote."));
            $use_db_quote = true;
            //bug fix when param in uri is wrong like co_xxxxx?x_account_id=abc
            if (stripos($checkoutId, "?") !== false) {
                return $sessionQuote;
            }
        } else {
            return $sessionQuote;
        }

        // DB Quote
        if ($use_db_quote && $checkoutId) {

            $dbQuote = Mage::getModel('sales/quote')->getCollection()->addFieldToFilter("zipmoney_cid", $checkoutId)->getFirstItem();

            if (!$dbQuote) {
                $this->getLogger()->warn($this->getHelper()->__("Quote does not exist for the given checkout_id."));
                return false;
            } else {
                $this->getLogger()->info($this->getHelper()->__("Loading DB Quote"));
            }

            return $dbQuote;
        }
    }
    
    /******************************************* */


    /**
     * @var Zip_Payment_Model_Config
     */
    protected $config;
    
    /**
     * @var Zip_Payment_Model_Checkout
     */
    protected $checkout;
    /**
     * @var Zip_Payment_Model_Charge
     */
    protected $charge;
    /**
     * Common Route
     *
     * @const
     */
    const ZIPMONEY_STANDARD_ROUTE = "zipmoneypayment/standard";

   

    /**
     * Get customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    

    /**
     * Sets checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }


    /**
     * Instantiate checkout model and inject the checkout api
     *
     * @return Zip_Payment_Model_Standard_Checkout
     * @throws Mage_Core_Exception
     */
    protected function _initCheckout()
    {
        $quote = $this->getQuote();

        // Check if the quote has items and errors
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
            Mage::throwException($this->getHelper()->__('Unable to initialize the Checkout.'));
        }

        $this->checkout = Mage::getModel($this->checkoutModel, array('quote' => $quote));

        return $this->checkout;
    }

    /**
     * Instantiate checkout model and inject charge api
     *
     * @return Zip_Payment_Model_Standard_Checkout
     * @throws Mage_Core_Exception
     */
    protected function _initCharge()
    {
        $quote = $this->getQuote();

        if (!$quote->getId()) {
            Mage::throwException($this->getHelper()->__('Quote doesnot exist'));
        }

        if (!$quote->hasItems() || $quote->getHasError()) {
            Mage::throwException($this->getHelper()->__('Quote has error or no items.'));
        }

        $this->charge = Mage::getModel($this->chargeModel);
        return $this->charge;
    }

    /**
     * Sets the Http Headers, Response Code and Responde Body
     *
     * @param string $data
     * @param Mage_Api2_Model_Server $responseCode
     */
    protected function _sendResponse($data, $responseCode = Mage_Api2_Model_Server::HTTP_OK)
    {
        $this->getResponse()->setHttpResponseCode($responseCode);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $jsonData = Mage::helper('core')->jsonEncode($data);
        $this->getResponse()->setBody($jsonData);
    }

    

    /**
     * Checks if the Customer is valid for the quote
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    protected function _verifyCustomerForQuote($quote)
    {
        $currentCustomer = null;
        $customerSession = $this->getCustomerSession();

        // Get quote customer id
        $quoteCustomerId = $quote->getCustomerId();

        // Get current logged in customer
        if ($customerSession->isLoggedIn()) {
            $currentCustomer = $customerSession->getCustomer();
        }

        $this->getLogger()->debug(
            $this->getHelper()->__(
                "Current Customer Id:- %s Quote Customer Id:- %s Quote checkout method:- %s",
                $customerSession->getId(), $quoteCustomerId, $quote->getCheckoutMethod()
            )
        );

        $log_in = false;

        if (isset($currentCustomer)) {
            if ($currentCustomer->getId() != $quoteCustomerId) {
                $customerSession->logout(); // Logout the logged in customer
                $customerSession->renewSession();
                //$log_in = true;
            }
        }

        // else if($quoteCustomerId){
        //   $log_in = true;
        // }

        // if($quote->getCheckoutMethod() == Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER){
        //   $quoteCustomerId = $this->getHelper()->lookupCustomerId($quote->getCustomerEmail());
        // }

        // if($quote->getCheckoutMethod() != Mage_Checkout_Model_Type_Onepage::METHOD_GUEST && $log_in){
        //   // if(!$customerSession->loginById($quoteCustomerId)){
        //   //   Mage::throwException("Could not login");
        //   // }
        // }
    }

    

    /**
     * Redirects to the referred page.
     *
     * @return boolean
     */
    public function referredAction()
    {
        $this->getLogger()->debug($this->getHelper()->__('Calling referredAction'));
        try {
            $this->loadLayout()->_initLayoutMessages('checkout/session')->_initLayoutMessages('catalog/session')->_initLayoutMessages('customer/session');
            $this->renderLayout();
            $this->getLogger()->info($this->getHelper()->__('Successful to redirect to referred page.'));
        } catch (Exception $e) {
            $this->getLogger()->error(json_encode($this->getRequest()->getParams()));
            $this->getLogger()->error($e->getMessage());
            $this->getCheckoutSession()->addError($this->_('An error occurred during redirecting to referred page.'));
        }
    }

    /**
     * Redirects to the error page.
     *
     */
    protected function _redirectToError()
    {
        $this->_redirect(self::ZIPMONEY_ERROR_ROUTE);
    }

    

    /**
     * Redirects to the error page.
     *
     * @return boolean
     */
    public function errorAction()
    {
        $this->getLogger()->debug($this->getHelper()->__('Calling errorAction'));
        try {
            $this->loadLayout()->_initLayoutMessages('checkout/session')->_initLayoutMessages('catalog/session')->_initLayoutMessages('customer/session');
            $this->renderLayout();
            $this->getLogger()->info($this->getHelper()->__('Successful to redirect to error page.'));
        } catch (Exception $e) {
            $this->getLogger()->error(json_encode($this->getRequest()->getParams()));
            $this->getCheckoutSession()->addError($this->getHelper()->__('An error occurred during redirecting to error page.'));
        }
    }

    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Send Ajax redirect response
     *
     * @return Zip_Payment_Controller_Abstract $this
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired')->setHeader('Login-Required', 'true')->sendResponse();
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->ajaxRedirectResponse();
            return true;
        }

        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))
        ) {
            $this->ajaxRedirectResponse();
            return true;
        }

        return false;
    }
}