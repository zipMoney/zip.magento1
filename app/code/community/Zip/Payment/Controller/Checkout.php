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
        if (empty($this->quote)) {
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
            $this->config = $this->getHelper()->getConfig();
        }
        return $this->config;
    }

    /**
     * process checkout's response result 
     * @param string $checkoutId Checkout ID From Url or other external place which need to validate
     * @param object $state response result / checkout state
     */
    protected function processResponseResult($checkoutId, $state) {

        $response = array(
            'success' => false,
            'message' => null,
            'redirect_url' => ($this->getQuote()->getId() && $this->getQuote()->getIsActive()) ? Zip_Payment_Model_Config::CHECKOUT_CART_URL_ROUTE : Zip_Payment_Model_Config::CHECKOUT_FAILURE_URL_ROUTE
        );

        try {

            // validate checkout id get valid checkout id
            $checkoutId = $this->validateCheckoutId($checkoutId);
            // retrieve checkout from API call
            $checkout = Mage::getModel('zip_payment/api_checkout', $this->getConfig()->getApiConfiguration())
            ->retrieve($checkoutId);

            $this->getLogger()->debug('response: ' . json_encode(array(
                'checkoutid' => $checkoutId,
                'state' => $state
            )));

            switch($state) {
                case Zip_Payment_Model_Api_Checkout::STATE_APPROVED: 
                    $response['success'] = $this->handleApprovedCheckout($checkout);
                    $response['success'] && $response['redirect_url'] =  Zip_Payment_Model_Config::CHECKOUT_SUCCESS_URL_ROUTE;
                break;
                case Zip_Payment_Model_Api_Checkout::STATE_REFERRED:
                    $state = $this->validateCheckoutState($checkout, $state);

                    if($state == Zip_Payment_Model_Api_Checkout::STATE_CREATED) {
                        $response['success'] = $this->handleReferredCheckout($checkout);
                        $response['success'] && $response['redirect_url'] = Zip_Payment_Model_Config::CHECKOUT_REFERRED_URL_ROUTE;
                    }
                    else {
                        $this->handleFailedCheckout($checkout);
                        $response['message'] = $this->generateMessage($state);
                    }
                    
                break;
                default:
                    $this->handleFailedCheckout($checkout);
                    $state = $this->validateCheckoutState($checkout, $state);
                    $response['message'] = $this->generateMessage($state);
                break;
            }
            $this->getLogger()->debug('response: ' . json_encode($response));

        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getHelper()->getCheckoutSession()->addError($e->getMessage());
            $this->redirectAfterResponse($response);
        }

        $this->redirectAfterResponse($response);
       
    }

    /**
     * validate checkout id
     * @param $checkoutId
     */
    protected function validateCheckoutId($checkoutId) {

        $checkoutIdFromSession = $this->getHelper()->getCheckoutIdFromSession();

        // if checkout id exists in session and also checkout id from session is not same as checkout id from url
        if(!empty($checkoutIdFromSession) && $checkoutIdFromSession !== $checkoutId) {
            $this->getLogger()->debug('Checkout id ' . $checkoutIdFromSession . 'from session is not same as checkout id from url ' . $checkoutId);
            // always trust checkout id from session
            $checkoutId = $checkoutIdFromSession;
        }

        if (empty($checkoutId)) {
            Mage::throwException('The checkout Id does not exist');
        }

        $this->getHelper()->saveCheckoutSessionData(array(
            Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY => $checkoutId
        ));

        return $checkoutId;

    }

    /**
     * validate checkout state
     * @param Zip_Payment_Model_Api_Checkout $checkout
     * @param string $state 
     */
    protected function validateCheckoutState($checkout, $state) {

        $checkoutState = $checkout->getState();
        $checkoutAllowedStates = $checkout->getAllowedStates();

        if($checkoutState !== $state) {
            $this->getLogger()->debug('Checkout state from api (' . $checkoutState . ') is not same as checkout state from url (' . $state . ')');
            // always trust checkout state from API
            $state = $checkoutState;
        }

        if(!in_array($state, $checkoutAllowedStates)) {
            Mage::throwException($this->getHelper()->__('Checkout state is not valid'));
        }

        $this->getHelper()->saveCheckoutSessionData(array(
            Zip_Payment_Model_Api_Checkout::CHECKOUT_STATE_KEY => $state
        ));

        return $state;
    }

    /**
     * handle approved checkout
     */
    protected function handleApprovedCheckout($checkout) {

        try {

            $this->getLogger()->debug('Zip_Payment_CheckoutController - handle approved checkout');
            $this->placeOrder($checkout);
            return true;
            
        } catch (Exception $e) {
            throw $e;
        }

        return false;
    }

    /**
     * handle referred checkout
     */
    protected function handleReferredCheckout($checkout) {

        try {

            $this->getLogger()->debug('Zip_Payment_CheckoutController - handle referred checkout');

            $createOrder = $this->getConfig()->getFlag(Zip_Payment_Model_Config::CONFIG_CHECKOUT_REFERRED_ORDER_CREATION_PATH);

            if($createOrder) {
                $this->placeOrder($checkout);
            }

            return true;

        } catch (Exception $e) {
            throw $e;
        }

        return false;
    }

    /**
     * handle failed checkout
     */
    protected function handleFailedCheckout($checkout) {

        try {
            $orderId = $checkout->getOrderReference();
            $order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);

            // cancel order if there is pending order exists for this failed checkout
            if($this->getHelper()->isReferredOrder($order) && $order->canCancel()) {
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->cancel()->save();
                $this->getLogger()->debug('Order ${orderId} has been cancelled');
            }

        }
        catch(Exception $e) {
            throw $e;
        }

    }

    
    /**
     * handle redirect after response been processed
     */
    protected function redirectAfterResponse($response) {

        $this->getHelper()->unsetCheckoutSessionData();
        $this->getHelper()->emptyShoppingCart();

        // if it's an ajax call
        if($this->getRequest()->isAjax()) {
            $response['redirect_url'] = Mage::getUrl($response['redirect_url'], array('_secure' => true));
            $this->returnJsonResponse($response);
        }
        else {
            $this->_redirect($response['redirect_url'], array('_secure' => true));
        }
        
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
    protected function placeOrder($checkout) {

        try {

            $onepage = $this->getHelper()->getOnePage();
            $quote = $onepage->getQuote();
            $orderId = $checkout->getOrderReference();

            $this->getLogger()->debug('Start to place order');

            // if current shopping cart has quote and is valid
            if($quote->getId() && $quote->hasItems()) {
                $this->placeOrderWithCurrentQuote($quote, $onepage);
            }
            else {

                $this->getLogger()->debug('No any valid quote in current shopping cart');
                $order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);

                // if cart session is empty but order exists
                if($order->getId()) {
                    $this->placeOrderWithExistingOrder($order);
                }
                // no cart and order
                else {
                   $this->placeOrderWithExistingQuote($orderId, $onepage);
                }

            }
                    
            $this->getLogger()->debug('Order has been saved successfully');

        } catch(Exception $e) {
            throw $e;
        }

    }

    /**
     * place order for current quote
     */
    protected function placeOrderWithCurrentQuote($quote, $onepage) {

        try {
        
            $this->getLogger()->debug('Place order with valid quote ' . $quote->getId());

            $quote
            ->collectTotals()
            ->save();
            
            $onepage->saveOrder();

        } catch(Exception $e) {
            throw $e;
        }
    }
    

    /**
     * add invoice and process payment for existing order
     * @param $order
     */
    protected function placeOrderWithExistingOrder($order) {

        $orderId = $order->getIncrementId();

        $this->getLogger()->debug('Place order with existing order #' . $orderId);

        // Try to generate invoice and add transaction into existing order

        if(!$this->getHelper()->isReferredOrder($order)) {
            Mage::throwException($this->getHelper()->__('Current order is not a valid referred order. payment can\'t be processed.'));
        }

        if (!$order->canInvoice()) {
            Mage::throwException($this->getHelper()->__('Cannot create an invoice for this order.'));
        }

        try {

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                Mage::throwException(
                    Mage::helper('core')->__('Cannot create an invoice without products.')
                );
            }
            
            $invoice->setRequestedCaptureCase(
                Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE
            );
            
            $invoice->addComment('Invoice generated automatically');
            $invoice->register();

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($order)
                ->save();
            
            $this->getHelper()->getCheckoutSession()
            ->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());

        } catch (Exception $e) {
            throw $e;
        }

        
        try {
            $invoice->sendEmail(true);
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')
                ->addError($this->__('Unable to send the invoice email.'));
        }

    }

    /**
     * place order for an existing quote which is currently loaded in the shopping cart
     */
    protected function placeOrderWithExistingQuote($orderId, $onepage) {

        try {

            // Try to load quote and place a new order
            $quote = Mage::getModel('sales/quote')->load($orderId, 'reserved_order_id');

            if(!$quote->getId()) {
                Mage::throwException('Could not retrieve your shopping cart, payment can\'t be processed.');
            }

            $this->getLogger()->debug('Place order with existing quote #' . $quote->getId());

            $quote
            ->setIsActive(true);

            $this->getHelper()->getCheckoutSession()->replaceQuote($quote);
            $this->getHelper()->getCustomerSession()->setCartWasUpdated(true); 

            $quote->collectTotals()
            ->save();
            

        } catch (Exception $e) {
            throw $e;
        }

    }
    /**
     * generate messages for checkout result
     */
    protected function generateMessage($state) {

        $message = null;
        
        switch($state) {
            case Zip_Payment_Model_Api_Checkout::STATE_DECLINED:
            case Zip_Payment_Model_Api_Checkout::STATE_EXPIRED:
                $message = $this->getHelper()->__('Checkout is ' . $state);
                break;
            case Zip_Payment_Model_Api_Checkout::STATE_CANCELLED:
                $message = $this->getHelper()->__('Checkout has been ' . $state);
                break;
            default:
                $message = self::GENERAL_CHECKOUT_ERROR;
                break;
        }

        $this->getHelper()->getCheckoutSession()->addError($message);
        $this->getLogger()->debug($message);

        return $message;

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

            $isLandingPageEnabled = $this->getConfig()->getFlag(Zip_Payment_Model_Config::CONFIG_LANDING_PAGE_ENABLED_PATH);

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