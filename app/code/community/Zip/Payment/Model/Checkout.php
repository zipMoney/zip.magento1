<?php

/**
 * Checkout model
 *
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Model_Checkout
{

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
    protected function getConfig()
    {
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
    public function handleResponse($checkoutId, $state)
    {
        $response = array(
            'success' => false,
            'message' => null,
            'redirect_url' => ($this->getQuote()->getId() && $this->getQuote()->getIsActive()) ? Zip_Payment_Model_Config::CHECKOUT_CART_URL_ROUTE : Zip_Payment_Model_Config::CHECKOUT_FAILURE_URL_ROUTE
        );

        try {
            $checkoutIdFromSession = $this->getHelper()->getCheckoutIdFromSession();
            $isReferred = !empty($checkoutId) && (empty($checkoutIdFromSession) || $checkoutIdFromSession != $checkoutId);
            empty($checkoutIdFromSession) ?: $checkoutId = $checkoutIdFromSession;

            if (empty($checkoutId)) {
                Mage::throwException('The checkout Id does not exist');
            }

            // save checkout id and state into session
            $this->getHelper()->saveCheckoutSessionData(
                array(
                Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY => $checkoutId,
                Zip_Payment_Model_Api_Checkout::CHECKOUT_STATE_KEY => $state
                )
            );

            $this->getLogger()->debug(
                'response: ' . json_encode(
                    array(
                    'checkoutid' => $checkoutId,
                    'state' => $state
                    )
                )
            );

            switch($state) {
                // State is Approved
                case Zip_Payment_Model_Api_Checkout::STATE_APPROVED:

                    $this->getLogger()->debug('Handle approved ' . ($isReferred ? 'referred' : '') . 'checkout');
                    $isReferred ? $this->placeReferredOrder($checkoutId) : $this->placeOrder();

                    $response['success'] = true;
                    $response['redirect_url'] = Zip_Payment_Model_Config::CHECKOUT_SUCCESS_URL_ROUTE;
                    break;
                // State is referred
                case Zip_Payment_Model_Api_Checkout::STATE_REFERRED:

                    $this->getLogger()->debug('Handle referred checkout');

                    if($this->getConfig()->getFlag(Zip_Payment_Model_Config::CONFIG_CHECKOUT_REFERRED_ORDER_CREATION_PATH)) {
                        $this->placeOrder();
                    }

                    $response['success'] = true;
                    $response['redirect_url'] = Zip_Payment_Model_Config::CHECKOUT_REFERRED_URL_ROUTE;
                    break;
                // Other states
                default:

                    $this->getLogger()->debug("Handle {$state} checkout");

                    if($isReferred) {
                        $this->cancelReferredOrder($checkoutId);
                    }

                    $response['message'] = $this->generateMessage($state);

                    break;
            }

            $this->getLogger()->debug('response: ' . json_encode($response));
        } catch (Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $this->getHelper()->getCheckoutSession()->addError($e->getMessage());
            return $response;
        }

        return $response;

    }

    /**
     * place order
     */
    protected function placeOrder()
    {
        try {
            $onepage = $this->getHelper()->getOnePage();

            $quote = $onepage->getQuote();

            $this->getLogger()->debug("Place order for Current Cart - quote #{$quote->getId()}");

            $quote
                ->collectTotals()
                ->save();

            $onepage->saveOrder();

            $this->getLogger()->debug('Order has been saved successfully');
        } catch(Exception $e) {
            throw $e;
        }

    }

    /**
     * place order for referred application
     */
    protected function placeReferredOrder($checkoutId)
    {
        try {
            // retrieve checkout from API call
            $checkout = Mage::getModel('zip_payment/api_checkout')
            ->retrieve($checkoutId);

            $orderId = $checkout->getOrderReference();

            $this->getLogger()->debug("Place order for Referred Application - order id: {$orderId}");
            $order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);

            // if order exists
            if($order->getId()) {
                $this->placeOrderWithExistingOrder($order);
            }
            else {
                $this->placeOrderWithExistingQuote($orderId);
            }
        } catch(Exception $e) {
            throw $e;
        }

    }


    /**
     * cancel failed referred order
     */
    protected function cancelReferredOrder($checkoutId)
    {
        try {
            $checkout = Mage::getModel('zip_payment/api_checkout')
            ->retrieve($checkoutId);

            $orderId = $checkout->getOrderReference();
            $order = Mage::getSingleton('sales/order')->loadByIncrementId($orderId);

            // cancel order if there is pending order exists for this failed checkout
            if($this->getHelper()->isReferredOrder($order) && $order->canCancel()) {
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->cancel()->save();
                $this->getLogger()->debug("Order #{$orderId} has been cancelled");
            }
        }
        catch(Exception $e) {
            throw $e;
        }

    }

    /**
     * add invoice and process payment for existing order
     * @param $order
     */
    protected function placeOrderWithExistingOrder($order)
    {
        $orderId = $order->getIncrementId();
        $this->getLogger()->debug("Place order with existing order #{$orderId}");

        // if the order state is not pending
        if(!$this->getHelper()->isReferredOrder($order)) {
            Mage::throwException($this->getHelper()->__('Current order is not a valid referred order. payment can\'t be processed.'));
        }

        // if the order can be invoiced
        if (!$order->canInvoice()) {
            Mage::throwException($this->getHelper()->__('Cannot create an invoice for this order.'));
        }

        try {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

            if (!$invoice->getTotalQty()) {
                Mage::throwException(
                    $this->getHelper()->__('Cannot create an invoice without products.')
                );
            }

            $invoice->setRequestedCaptureCase(
                Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE
            );

            $invoice
                ->addComment('Invoice generated by Zip Payment - Referred Application automatically')
                ->register()
                ->sendEmail(true);

            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($order)
                ->save();

            $session = $this->getHelper()->getCheckoutSession();
            $session->setLastQuoteId($order->getQuoteId())
                ->setLastSuccessQuoteId($order->getQuoteId())
                ->clearHelperData();

            $session
                ->setLastOrderId($order->getId())
                ->setLastRealOrderId($order->getIncrementId());
        } catch (Exception $e) {
            throw $e;
        }

    }

    /**
     * place order for an existing quote which is currently loaded in the shopping cart
     */
    protected function placeOrderWithExistingQuote($orderId)
    {
        try {
            $onepage = $this->getHelper()->getOnePage();

            // Try to load quote and place a new order
            $quote = Mage::getModel('sales/quote')->load($orderId, 'reserved_order_id');

            if(!$quote->getId()) {
                $this->getLogger()->debug("Could not find any quote for order #{$orderId}");
                Mage::throwException('Could not retrieve your shopping cart, payment can\'t be processed.');
            }

            $this->getLogger()->debug("Place order with existing quote #{$quote->getId()}");

            $quote
            ->setIsActive(true);

            $this->getHelper()->getCheckoutSession()
                ->replaceQuote($quote)
                ->setCartWasUpdated(true);

            $quote
                ->collectTotals()
                ->save();

            $onepage->saveOrder();
        } catch (Exception $e) {
            throw $e;
        }

    }

    /**
     * generate messages for checkout result
     */
    protected function generateMessage($state)
    {
        $message = null;

        switch($state) {
            case Zip_Payment_Model_Api_Checkout::STATE_DECLINED:
            case Zip_Payment_Model_Api_Checkout::STATE_EXPIRED:
                $message = $this->getHelper()->__("Checkout is {$state}");
                break;
            case Zip_Payment_Model_Api_Checkout::STATE_CANCELLED:
                $message = $this->getHelper()->__("Checkout has been {$state}");
                break;
            default:
                $message = self::GENERAL_CHECKOUT_ERROR;
                break;
        }

        $this->getHelper()->getCheckoutSession()->addError($message);
        $this->getLogger()->debug($message);

        return $message;

    }


}