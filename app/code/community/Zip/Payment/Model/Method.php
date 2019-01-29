<?php

/**
 * Core method model for Zip Payment
 *
 * @package     Zip_Payment
 * @author      Zip Co - Plugin Team
 *
 **/

class Zip_Payment_Model_Method extends Mage_Payment_Model_Method_Abstract
{

    const AUTHORIZE_TRANSACTION_ID_PREFIX = 'AUTH_';

    protected $_code = Zip_Payment_Model_Config::METHOD_CODE;
    
    protected $_formBlockType = 'zip_payment/method_form';
    protected $_infoBlockType = 'zip_payment/method_info';

    /**
     * Config instance
     * @var Zip_Payment_Model_Config
     */
    protected $config = null;
    protected $logger = null;
    protected $quote = null;
    protected $paymentAction = null;
    
    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway                   = false;
    protected $_canOrder                    = true;
    protected $_canAuthorize                = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = false;
    protected $_canCaptureOnce              = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;
    protected $_canVoid                     = true;
    protected $_canUseInternal              = false;
    protected $_canUseCheckout              = true;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = false;
    protected $_canFetchTransactionInfo     = true;
    protected $_canReviewPayment            = true;
    protected $_canCreateBillingAgreement   = true;
    protected $_canManageRecurringProfiles  = false;

    /**
     * We have void capture method but merchant might accidentally void
     * transaction in admin so do not implement this yet
     *
     * @var boolean
     */
    protected $_canCancelInvoice            = false;

    /**
     * Config instance getter
     * @return Zip_Payment_Model_Config
     */
    public function getConfig()
    {
        if ($this->config == null) {
            $this->config = $this->_getHelper()->getConfig();
            $this->config->setMethod($this->getCode());
        }
        return $this->config;
    }

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
    protected function _getHelper()
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
            $this->quote = $this->_getHelper()->getCheckoutSession()->getQuote();
        }

        return $this->quote;
    }

      /**
     * Log debug data to file
     *
     * @param mixed $debugData
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            $this->getLogger()->log($debugData);

        }
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     */
    public function getDebugFlag()
    {
        return $this->getConfig()->isDebugEnabled();
    }

    /**
     * Check whether payment method can be used
     * @param Mage_Sales_Model_Quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return parent::isAvailable($quote) && $this->getConfig()->isMethodAvailable();
    }

     /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getConfig()->isCurrencySupported($currencyCode);
    }

    /**
     * Custom getter for payment configuration
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        $configValue = null;

        // set order status as pending for referred checkout
        if($this->_getHelper()->isReferredCheckout()) {
            $configValue = $this->getConfig()->getValue("payment/{$this->getCode()}/checkout/referred/{$field}");
        }

        if(is_null($configValue)) {
            $configValue = $this->getConfig()->getValue("payment/{$this->getCode()}/{$field}");
        }
        
        return $configValue;
    }

    /**
     * Get config payment action url
     * change payment action to order for referred checkout
     *
     * @return string
     */
    public function getConfigPaymentAction()
    {
        $this->paymentAction || $this->paymentAction = $this->_getHelper()->isReferredCheckout() ? self::ACTION_ORDER : parent::getConfigPaymentAction();

        return $this->paymentAction;
    }

    /**
     * Create Checkout and get Checkout redirect URL
     *
     * @see Mage_Checkout_OnepageController::savePaymentAction()
     * @see Mage_Sales_Model_Quote_Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        $checkout = $this->createCheckout(); 

        if($checkout) {
            $redirectUrl = $checkout->getRedirectUrl();
        }

        // return redirect url for one step checkout
        if($redirectUrl && !$this->_getHelper()->isOnepageCheckout()) {
            return $redirectUrl;
        }

        return parent::getCheckoutRedirectUrl();

    }


    /******************* Payment Actions *****************/

    /**
     * Returns checkout object after checkout been created
     *
     * @return Zip_Payment_Model_Api_Checkout
     */
    protected function createCheckout()
    {
        $this->getLogger()->debug('Zip_Payment_Model_Method - Create Checkout');

        $this->getQuote()->collectTotals();

        if (!$this->getQuote()->getGrandTotal() && !$this->getQuote()->hasNominalItems()) {
            Mage::throwException($this->_getHelper()->__('Does not support processing orders with zero amount.'));
        }

        try {

            $quote = $this->getQuote()->reserveOrderId()->save();

            if (!$quote->hasItems() || $quote->getHasError()) {
                Mage::throwException($this->_getHelper()->__('Unable to initialize the Checkout.'));
            }

            // Create Checkout
            $checkout = Mage::getModel('zip_payment/api_checkout')
            ->create();

            $this->_getHelper()->saveCheckoutSessionData(array(
                Zip_Payment_Model_Api_Checkout::CHECKOUT_ID_KEY => $checkout->getId(),
            ));

            return $checkout;

        } catch (Exception $e) {
            Mage::throwException($this->_getHelper()->__('Failed to process checkout - ' . $e->getMessage()));
        }

        return null;
    }

    /**
     * Order payment abstract method
     *
     * @param Varien_Object $payment
     * @param float $amount
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function order(Varien_Object $payment, $amount)
    {
        $this->getLogger()->debug('Zip_Payment_Model_Method - Order');

        if (!$this->canOrder()) {
            Mage::throwException(Mage::helper('payment')->__('Order action is not available.'));
        }
        
        return $this;
    }


    /**
     * authorize action
     *
     * @return Zip_Payment_Model_Method
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        $this->getLogger()->debug($this->_getHelper()->__("Zip_Payment_Model_Method - Authorize"));

        if (!$this->canAuthorize()) {
            Mage::throwException($this->_getHelper()->__('Authorize action is not available.'));
        }
        
        try {

            $order = $payment->getOrder();
            $charge = Mage::getModel('zip_payment/api_charge');

            if($this->_getHelper()->getCheckoutIdFromSession()) {
                // Create Charge
                $charge = $charge->create($order, $this->getConfigPaymentAction());
            }


        } catch (Exception $e) {
            $this->_getHelper()->unsetCheckoutSessionData();
            Mage::throwException($this->_getHelper()->__('Could not authorize the payment - ' . $e->getMessage()));
        }

        // update payment
        if($charge->getId()) {

            $payment
            ->setTransactionId(self::AUTHORIZE_TRANSACTION_ID_PREFIX . $charge->getId())
            ->setIsTransactionClosed(0)
            ->setAdditionalInformation(
                array(
                    Zip_Payment_Model_Config::PAYMENT_RECEIPT_NUMBER_KEY => $charge->getReceiptNumber()
                )
            );
            
        }

        return $this;
    }

     /**
     * capture payment
     *
     * @return Zip_Payment_Model_Method
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $this->getLogger()->debug($this->_getHelper()->__("Zip_Payment_Model_Method - Capture"));

        if (!$payment->canCapture()) {
            Mage::throwException($this->_getHelper()->__('Capture action is not available.'));
        }

        $checkoutState = $this->_getHelper()->getCheckoutStateFromSession();

        if(!$checkoutState) {
            Mage::throwException($this->_getHelper()->__('Invalid Checkout state, payment will not be processing.'));
        }else if ($checkoutState !== Zip_Payment_Model_Api_Checkout::STATE_APPROVED) {
            Mage::throwException($this->_getHelper()->__('Checkout is %s, payment will not be processing.', $checkoutState));
        }

        $authorizationTransaction = $payment->getAuthorizationTransaction();
        $authId = null;

        try {

            $storeId = $payment->getOrder()->getStoreId();
            $charge = Mage::getModel('zip_payment/api_charge', array('store_id' => $storeId));
                
            // if the payment has been authorized before
            if($authorizationTransaction) {

                if (!$authorizationTransaction->getId()) {
                    Mage::throwException($this->_helper->__('Cannot find payment authorization transaction.'));
                }

                if ($authorizationTransaction->getTxnType() != Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH) {
                    Mage::throwException($this->_helper->__('Incorrect payment transaction type.'));
                }

                $authId = $authorizationTransaction->getTxnId();

                if($authId) {
                    // Capture Charge
                    $chargeId = preg_replace('/^' . self::AUTHORIZE_TRANSACTION_ID_PREFIX . '/i', '', $authId);
                    $charge = $charge->capture($chargeId, $amount);
                }

            } 
            else {

                $checkoutId = $this->_getHelper()->getCheckoutIdFromSession();

                $this->getLogger()->debug($this->_getHelper()->__("Checkout ID: " . $checkoutId));

                if($checkoutId) {

                    // Create Charge
                    $charge = $charge->create($payment->getOrder(), $this->getConfigPaymentAction());
                }

            }

        } catch (Exception $e) {
            $this->_getHelper()->unsetCheckoutSessionData();
            Mage::throwException($this->_getHelper()->__('Could not capture the payment - ' . $e->getMessage()));
        }

        // update payment
        if($charge->getId()) {

            $receiptNumber = $charge->getReceiptNumber();

            $payment
            ->setTransactionId($charge->getId() . '_rn_' . $receiptNumber)
            ->setIsTransactionApproved(true)
            ->setIsTransactionClosed(0)
            ->setAdditionalInformation(
                array(
                    Zip_Payment_Model_Config::PAYMENT_RECEIPT_NUMBER_KEY => $receiptNumber
                )
            );
            
            if($authId) {
                $payment->setParentTransactionID($authId);
            }
        }

        return $this;
        
    }

     /**
     * process refund
     *
     * @return Zip_Payment_Model_Method
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $this->getLogger()->debug($this->_getHelper()->__("Zip_Payment_Model_Method - Refund"));

        if (!$this->canRefund()) {
            Mage::throwException($this->_getHelper()->__('Refund action is not available.'));
        }

        $creditmemo = Mage::app()->getRequest()->getParam('creditmemo');
        $reason = isset($param['comment_text']) && !empty($param['comment_text']) ? $param['comment_text'] : 'N/A';

        $transactionID = $payment->getParentTransactionID();

        try {

            if (!$transactionID) {
                Mage::throwException($this->_getHelper()->__('Could not get payment transaction ID'));
            }

            if (!$amount) {
                Mage::throwException($this->_getHelper()->__('Please provide refund amount'));
            }

            $orderId = $payment->getOrder()->getIncrementId();
            $storeId = $payment->getOrder()->getStoreId();
            $chargeId = preg_replace('/_rn_[0-9]+?$/i', '', $transactionID);

            // Create refund
            $this->getLogger()->debug('Refund store id: ' . $storeId);
            $refund = Mage::getModel('zip_payment/api_refund', array('store_id' => $storeId))
            ->create($chargeId, $amount, $reason);

            $this->getLogger()->info($this->_getHelper()->__("Refund for Order [ %s ] for amount %s was successful", $orderId, $amount));

            $payment
            ->setTransactionId($refund->getId())
            ->setIsTransactionClosed(true)
            ->setStatus(Mage_Payment_Model_Method_Abstract::STATUS_VOID);
            
            
        } catch (Exception $e) {
            $this->_getHelper()->unsetCheckoutSessionData();
            Mage::throwException($this->_getHelper()->__('Could not refund the payment - ' . $e->getMessage()));
        }

        return $this;
    }

    /**
     * cancel a payment
     *
     * @return Zip_Payment_Model_Method
     */
    public function cancel(Varien_Object $payment)
    {

        if (!$payment->getOrder()->getInvoiceCollection()->count()) {
            $this->void($payment);
        }

        return $this;
    }

    /**
     * void a payment
     *
     * @return Zip_Payment_Model_Method
     */
    public function void(Varien_Object $payment)
    {
        if (!$this->canVoid($payment)) {
            Mage::throwException($this->_getHelper()->__('Void action is not available.'));
        }

        $orderId = $payment->getOrder()->getIncrementId();
        $chargeId = $payment->getParentTransactionID();
        $storeId = $payment->getOrder()->getStoreId();

        $this->getLogger()->debug('Cancel Charge For Order: ' . $orderId);

        try {

            // Cancel Charge
            $charge = Mage::getModel('zip_payment/api_charge', array('store_id' => $storeId))
            ->cancel($chargeId);

            if (isset($charge->error)) {
                Mage::throwException($this->_getHelper()->__('Could not cancel the charge'));
            }

            if (!$charge->getState()) {
                Mage::throwException($this->_getHelper()->__('Invalid Charge Cancel'));
            }

        } catch (ApiException $e) {
            Mage::throwException($this->_getHelper()->__('Could not void the payment - ' . $e->getMessage()));
        }

        return $this;
    } 

}