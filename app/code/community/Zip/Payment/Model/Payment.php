<?php


class Zip_Payment_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $helper;
    protected $api = null;

    protected $_code = "zip_payment";
    
    protected $_formBlockType = 'zip_payment/standard_form';
    protected $_infoBlockType = 'zip_payment/standard_info';

    /**
     * Payment Method features
     * @var bool
     */
    protected $_isGateway = false;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canCaptureOnce = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_isInitializeNeeded = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canManageRecurringProfiles = false;

    /**
     * We have void capture method but merchant might accidentally void
     * transaction in admin so do not implement this yet
     *
     * @var boolean
     */
    protected $_canCancelInvoice = false;

    /**
     * Debug privacy protection
     *
     * @var array
     */
    protected $_debugReplacePrivateDataKeys = array();

    public function __construct()
    {
        parent::__construct();
        $this->helper = Mage::helper("zip_payment");
    }

    /**
     * lazy load API class once when needed
     *
     * @return Zip_Payment_Model_Api
     */
    public function getApi()
    {
        if ($this->api === null) {
            $this->api = Mage::getSingleton('zip_payment/api');
        }

        return $this->api;
    }

    public function getTitle()
    {
        return $this->getConfigData('title');
    }

    public function getConfigPaymentAction()
    {
        return $this->getConfigData('payment_action');
    }

    public function canUseForCountry($country)
    {
        return parent::canUseForCountry($country);
    }

    /**
     * Limit which order currency we support
     *
     * @param string $currencyCode
     * @return boolean
     */
    public function canUseForCurrency($currencyCode)
    {
        $allowedCurrencyCode = array("AUD");
        if (in_array($currencyCode, $allowedCurrencyCode)) {
            return true;
        }

        return false;
    }

    protected function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    public function order(Varien_Object $payment, $amount)
    {
        if (!$this->canOrder()) {
            Mage::throwException(Mage::helper('payment')->__('Order action is not available.'));
        }

        $order = $payment->getOrder();
        $payAmount = $amount;
        return $this;
    }

    public function authorize(Varien_Object $payment, $amount)
    {
        if (!$this->canAuthorize()) {
            Mage::throwException(Mage::helper('payment')->__('Authorize action is not available.'));
        }
        
        return $this;
    }

    public function capture(Varien_Object $payment, $amount)
    {
        if (!$this->canCapture()) {
            Mage::throwException(Mage::helper('payment')->__('Capture action is not available.'));
        }

        //if already authoriazed
        $authorizationTransaction = $payment->getAuthorizationTransaction();
        $authId = $authorizationTransaction->getTransactionId();
        if ($authId) {
            $resp = $this->getApi()->captureCharge($authId, $amount);
            $chargeId = $resp->id;
            $receipt = $resp->receipt_number;
            $payment->setTransactionId($chargeId)
                ->setIsTransactionApproved(true)
                ->setParentTransactionID($authId)
                ->setIsTransactionClosed(0)
                ->setAdditionalInformation("receipt_number", $receipt);
            return $this;
        }

        $order = $payment->getOrder();
        //get checkout id from magento core session
        $checkout_id = Mage::getSingleton('core/session')->getZipCheckoutId();
        try {
            //potentially allowed token charge and in store using getAuthority($value, $type)
            $authority = $this->getApi()->getAuthority($checkout_id);
            $payload = $this->getApi()->prepareChargeData($order, $amount, $authority, true);
            $resp = $this->getApi()->createCharge($payload);
            $chargeId = $resp->id;
            $receipt = $resp->receipt_number;
            $payment->setTransactionId($chargeId)
                ->setIsTransactionApproved(true)
                ->setIsTransactionClosed(0)
                ->setAdditionalInformation("receipt_number", $receipt);
        } catch (Exception $e) {
            Mage::throwException('Could not capture the payment - ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Set capture transaction ID to invoice for informational purposes
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Payment_Model_Method_Abstract
     */
    public function processInvoice($invoice, $payment)
    {
        $invoice->setTransactionId($payment->getLastTransId());
        return $this;
    }

    public function refund(Varien_Object $payment, $amount)
    {
        if (!$this->canRefund()) {
            Mage::throwException(Mage::helper('payment')->__('Refund action is not available.'));
        }

        return $this;
    }

    public function createCheckout()
    {
        $quote = $this->getOnepage()->getQuote();
        try {
            $payload = $this->getApi()->prepareCheckoutData($quote);
            $resp = $this->getApi()->createCharge($payload);
            return $resp;
        } catch (Exception $e) {
            Mage::throwException('Could not authorize the payment - ' . $e->getMessage());
        }

        return $reponse;
    }

    public function getCheckoutRedirectUrl()
    {
        $checkout_id = Mage::getSingleton('core/session')->getZipCheckoutId();
        if (empty($checkout_id)) {
            $onepage = $this->getOnepage();
            $quote = $onepage->getQuote();
            //reserve the order id to prevent changes
            if (!$quote->getReservedOrderId()) {
                $quote->reserveOrderId()->save();
            }

            $resp = $this->createCheckout();
            if ($resp->id && $resp->uri) {
                Mage::getSingleton('core/session')->setZipCheckoutId($resp->id);
                return $resp->uri;
            } else {
                //error
                throw new Mage_Payment_Exception("Could not redirect to zip checkout page");
            }
        }

        return null;
    }

    public function cancel(Varien_Object $payment)
    {
        return $this;
    }

    public function void(Varien_Object $payment)
    {
        if (!$this->canVoid($payment)) {
            Mage::throwException(Mage::helper('payment')->__('Void action is not available.'));
        }

        return $this;
    }

}