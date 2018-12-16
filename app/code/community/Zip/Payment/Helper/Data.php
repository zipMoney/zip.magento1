<?php

class Zip_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CONFIG_ACTIVE_PATH = 'payment/zip_payment/active';

    public function isActive() {
        return Mage::getSingleton('zip_payment/config')->getFlag(self::CONFIG_ACTIVE_PATH);
    }

    /**
     * Retrieves the extension version.
     *
     * @return string
     */
    public function getCurrentVersion()
    {
        return trim((string) Mage::getConfig()->getNode()->modules->Zip_Payment->version);
    }
    

    public function autoLoad() {
        require_once Mage::getBaseDir('lib') . DS . 'Zip' . DS . 'autoload.php';
    }

    /**
     * Get current store url
     *
     * @param $route
     * @param $param
     * @return string
     */
    public function getUrl($route, $param = array())
    {
        return Mage::getUrl($route, $param);
    }

    /**
     * Get session namespace
     *
     * @return Zip_Payment_Model_Session
     */
    public function getZipPaymentSession()
    {
        return Mage::getSingleton('zip_payment/session');
    }

    public function getCheckoutSessionId() {
        return $this->getZipPaymentSession()->getData(Zip_Payment_Model_Config::CHECKOUT_SESSION_ID);
    }

    public function setCheckoutSessionId($id) {
        return $this->getZipPaymentSession()->setData(Zip_Payment_Model_Config::CHECKOUT_SESSION_ID, $id);
    }

    public function unsetCheckoutSessionId() {
        $this->getZipPaymentSession()->unsetData(Zip_Payment_Model_Config::CHECKOUT_SESSION_ID);
    }


    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
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
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    public function getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

}
