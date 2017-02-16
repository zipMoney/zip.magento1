<?php

class Zipmoney_ZipmoneyPayment_Model_Payment extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'zipmoneypayment';
    protected $_formBlockType = 'zipmoneypayment/standard_form';
    protected $_isInitializeNeeded = true;
    protected $_url = "";
    protected $_dev = false;

    /**
     * Availability options
     */
    protected $_isGateway = false;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canCreateBillingAgreement = true;
    protected $_canReviewPayment = true;
	
    // protected $_client =  Zipmoney_ZipmoneyPayment_Model_Config::MODULE_VERSION;
    // protected $_platform = Zipmoney_ZipmoneyPayment_Model_Config::MODULE_PLATFORM;
    	   
    /**
     * zipMoney Payment instance
     *
     * @var Zipmoney_ZipmoneyPayment_Model_Payment
     */

    /**
     * Payment additional information key for payment action
     * @var string
     */
    protected $_isOrderPaymentActionKey = 'is_order_action';

    /**
     * Payment additional information key for number of used authorizations
     * @var string
     */
    protected $_authorizationCountKey = 'authorization_count';

    function __construct() {
        parent::__construct();
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl() {
        /**
         * Will be called when placing order
         * see app/code/core/Mage/Checkout/Model/Type/Onepage.php:808
         * and app/code/core/Mage/Paypal/Model/Standard.php:108
         */
        return Mage::getUrl('zipmoneypayment/standard/redirect', array('_secure' => true));
    }

   

    /**
     * Return zipMoney Express redirect url if current request is not savePayment (which works for oneStepCheckout)
     * @return null|string
     */
    public function getCheckoutRedirectUrl() {
       return null;
    }

    
}