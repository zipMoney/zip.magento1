<?php


class Zip_Payment_Block_Checkout extends Mage_Core_Block_Template
{

    /**
     * @var Zip_Payment_Model_Config
     */
    protected $_config;
    /**
     * @var Zip_Payment_Helper_Data
     */
    protected $_helper;
    /**
     * @var string
     */
    protected $_button_selector = 'button[type=submit][class~="btn-checkout"]';

    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::helper("zipmoneypayment");

        $this->_config = Mage::getSingleton('zipmoneypayment/config');
    }

    /**
     * Returns the checkout url.
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->_helper->getUrl("zipmoneypayment/checkout/");
    }

    /**
     * Returns the redirect url.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_helper->getUrl("zipmoneypayment/complete/");
    }

    /**
     * Whether to redirect or not.
     *
     * @return int
     */
    public function isRedirect()
    {
        return (int) !$this->_config->isInContextCheckout();
    }

    /**
     * Returns the place order button selector
     *
     * @return string
     */
    public function getPlaceOrderButtonSelector()
    {
        return $this->getButtonSelector() ? $this->getButtonSelector() : $this->_button_selector;
    }

    /**
     * Returns the extension name if specified in the config otherwise picks up from the request
     *
     * @return string
     */
    public function getExtensionName()
    {
        /** Check if extension name has been set explicitly in the zipmoneypayment.xml in the appropriate layout handle for the checkout page
         * E.g.
         * <action method="setData">
         *    <name>extension_name</name>
         *     <value>Mage_Checkout</value>
         *  </action>
         *
         */
        if ($extension = $this->getData('extension_name')) {
            return $extension;
        } else {
            return strtolower(Mage::app()->getRequest()->getControllerModule());
        }
    }

    /**
     * Returns the extension name if specified in the config otherwise picks up from the request
     *
     * @return string
     */
    public function getRedirectAfterPayment()
    {
        /** Check if extension name has been set explicitly in the zipmoneypayment.xml in the appropriate layout handle for the checkout page
         * E.g.
         * <action method="setData">
         *    <name>redirect_after_payment</name>
         *     <value>1</value>
         *  </action>
         *
         */
        return (int) $this->getData('redirect_after_payment');
    }
}